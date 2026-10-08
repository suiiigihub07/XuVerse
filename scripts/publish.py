"""One Publish command: validate, build PDFs, check, commit/push, stage and activate.
Private credentials are read outside the checkout. FTPS certificate checks stay enabled.
"""
from pathlib import Path, PurePosixPath
import argparse, contextlib, datetime, ftplib, hashlib, io, json, os, ssl, subprocess, sys
from concurrent.futures import ThreadPoolExecutor, as_completed
import threading
import secrets

ROOT = Path(__file__).resolve().parents[1]
PHP = os.environ.get('XUVERSE_PHP', r'C:\xampp\php\php.exe' if os.name == 'nt' else 'php')
DEFAULT_CONFIG = Path.home()/'.codex/private-config/xuverse-deploy.json'

def run(*args, capture=False):
    return subprocess.check_output(args, cwd=ROOT, text=True).rstrip() if capture else subprocess.run(args,cwd=ROOT,check=True)

def allowed(name):
    p=PurePosixPath(name)
    if '__pycache__' in p.parts or p.suffix in {'.pyc','.zip','.sql','.log','.bak','.pem','.key'}: return False
    return (p.parts[0] in {'admin','includes','assets','content','scripts'} or name in {'.htaccess','.gitignore','.gitattributes','README.md','DEPLOYMENT.md','PUBLISH.md','composer.json','composer.lock'} or (len(p.parts)==1 and p.suffix=='.php' and not name.startswith('config.') and 'backup' not in name))

def release_files(revision):
    names=run('git','ls-tree','-r','--name-only',revision,capture=True).splitlines()
    return [name for name in names if PurePosixPath(name).parts[0] in {'admin','includes','assets','content'} or name=='.htaccess' or (len(PurePosixPath(name).parts)==1 and name.endswith('.php') and not name.startswith('config.') and 'backup' not in name)]

def blob(revision,name):
    return subprocess.check_output(['git','show',revision+':'+name],cwd=ROOT)

class Host:
    def __init__(self,config):
        self.config=config
        self.ftp=ftplib.FTP_TLS(context=ssl.create_default_context(),timeout=45)
        self.ftp.connect(config.get('connect_ip',config['host']),21)
        self.ftp.host=config['host'] # TLS SNI/hostname validation still uses the documented hostname.
        self.ftp.login(config['user'],config['password']); self.ftp.prot_p()
        self.root=config['root'].rstrip('/'); self.dirs=set()
    def close(self):
        with contextlib.suppress(Exception): self.ftp.quit()
    def reconnect(self):
        self.close(); self.__init__(self.config)
    def read(self,path):
        b=io.BytesIO()
        try: self.ftp.retrbinary('RETR '+path,b.write)
        except ftplib.error_perm as e:
            if str(e).startswith('550'): return None
            raise
        return b.getvalue()
    def mkdir(self,path):
        if path in self.dirs: return
        parent=str(PurePosixPath(path).parent)
        if parent not in {'/','.'}: self.mkdir(parent)
        try: self.ftp.mkd(path)
        except ftplib.error_perm:
            old=self.ftp.pwd(); self.ftp.cwd(path); self.ftp.cwd(old)
        self.dirs.add(path)
    def put(self,path,content):
        parent=str(PurePosixPath(path).parent)
        self.mkdir(parent)
        temporary=parent+'/.xuverse-upload-'+secrets.token_hex(12)
        self.ftp.storbinary('STOR '+temporary,io.BytesIO(content))
        self.ftp.rename(temporary,path)
    def pointer(self,revision):
        tmp=self.root+'/.xuverse-active-next'
        self.put(tmp,(revision+'\n').encode()); self.ftp.rename(tmp,self.root+'/.xuverse-active')
        if self.read(self.root+'/.xuverse-active').decode().strip()!=revision: raise RuntimeError('Pointer verification failed')
    def backup_runtime(self,destination):
        destination.mkdir(parents=True,exist_ok=True)
        for name in ['.htaccess','config.local.php','xuverse-release.php','.xuverse-active']:
            data=self.read(self.root+'/'+name)
            if data is not None: (destination/name).write_bytes(data)
        def copy_tree(remote,local):
            local.mkdir(parents=True,exist_ok=True)
            for name,info in self.ftp.mlsd(remote):
                if name in {'.','..'}: continue
                if info['type']=='dir': copy_tree(remote+'/'+name,local/name)
                elif info['type']=='file': (local/name).write_bytes(self.read(remote+'/'+name))
        copy_tree(self.root+'/uploads',destination/'uploads')
        print('Private host config/upload backup:',destination)

def stage(host,revision,fail_after=0):
    target=host.root+'/.xuverse-releases/'+revision
    names=release_files(revision)
    manifest={'revision':revision,'files':{}}
    def upload_one(connection,name):
        content=blob(revision,name)
        if len(content)>10*1024*1024: raise RuntimeError('Host file limit exceeded: '+name)
        remote=target+'/'+name
        expected=hashlib.sha256(content).hexdigest()
        actual=connection.read(remote)
        if actual is None or hashlib.sha256(actual).hexdigest()!=expected:
            connection.put(remote,content)
            actual=connection.read(remote)
        if actual is None or hashlib.sha256(actual).hexdigest()!=expected: raise RuntimeError('Upload hash mismatch: '+name)
        return name,expected
    if fail_after:
        for name in names[:fail_after]: upload_one(host,name)
        raise RuntimeError('Intentional staging failure; active release has not changed')
    connections=[]; local=threading.local(); lock=threading.Lock()
    def upload(name):
        if not hasattr(local,'connection'):
            local.connection=Host(host.config)
            with lock: connections.append(local.connection)
        return upload_one(local.connection,name)
    try:
        with ThreadPoolExecutor(max_workers=4) as pool:
            futures=[pool.submit(upload,name) for name in names]
            for i,future in enumerate(as_completed(futures),1):
                name,digest=future.result(); manifest['files'][name]=digest
                if i%25==0: print('Verified staged files:',i,'/',len(names),flush=True)
    finally:
        for connection in connections: connection.close()
    host.reconnect() # Long staged uploads can outlast the host's idle-control timeout.
    host.put(target+'/release.json',json.dumps(manifest).encode())
    host.put(target+'/.ready',revision.encode())
    return manifest

def install_router(host,revision):
    # Both files are staged and read back before the atomic routing switch.
    router=blob(revision,'scripts/release-router.php')
    existing=host.read(host.root+'/xuverse-release.php')
    if existing is not None and existing!=router:
        active=(host.read(host.root+'/.xuverse-active') or b'').decode().strip()
        if active: raise RuntimeError('Router upgrade needs separate review; existing active router left unchanged')
        # Initial routing has not been accepted; original root code is still active.
        host.put(host.root+'/xuverse-release.php',router)
    if existing is None: host.put(host.root+'/xuverse-release.php',router)
    if host.read(host.root+'/xuverse-release.php')!=router: raise RuntimeError('Router upload mismatch')
    rules=blob(revision,'scripts/hosting.htaccess')
    old=host.read(host.root+'/.htaccess')
    if old!=rules:
        host.put(host.root+'/.xuverse-original-htaccess',old or b'')
        host.put(host.root+'/.htaccess-next',rules)
        if host.read(host.root+'/.htaccess-next')!=rules: raise RuntimeError('Routing upload mismatch')
        # Until pointer activation, router serves the original root code.
        host.ftp.rename(host.root+'/.htaccess-next',host.root+'/.htaccess')

def main():
    p=argparse.ArgumentParser(); p.add_argument('--message',default='Publish XuVerse content'); p.add_argument('--prepare',action='store_true'); p.add_argument('--deploy',metavar='REVISION'); p.add_argument('--rollback',metavar='REVISION'); p.add_argument('--fail-after',type=int,default=0); p.add_argument('--config',type=Path,default=DEFAULT_CONFIG); p.add_argument('--reuse-backup',type=Path); args=p.parse_args()
    if not args.deploy and not args.rollback:
        run(PHP,'scripts/build-content.php'); run(PHP,'scripts/verify-content.php')
        changed=run('git','status','--porcelain','--untracked-files=all',capture=True).splitlines()
        names=[line[3:] for line in changed if allowed(line[3:])]
        for name in names: run('git','add','--',name)
        staged=run('git','diff','--cached','--name-only',capture=True).splitlines()
        if any(not allowed(name) for name in staged): raise RuntimeError('Unrelated staged files; review staging before Publish')
        if args.prepare: print('Validated and staged; no commit, push or deployment'); return
        if staged: run('git','commit','-m',args.message)
        (ROOT/'.xuverse-local-revision').write_text(run('git','rev-parse','HEAD',capture=True)+'\n')
        run('git','push','origin','HEAD:main')
    revision=run('git','rev-parse',args.rollback or args.deploy or 'HEAD',capture=True)
    if len(revision)!=40: raise RuntimeError('Expected full commit SHA')
    # Deploy only a revision confirmed at origin/main, or a retained ancestor for rollback.
    remote=run('git','ls-remote','origin','refs/heads/main',capture=True).split()[0]
    if not args.rollback and remote!=revision: raise RuntimeError('GitHub main does not match requested revision')
    config=json.loads(args.config.read_text()); host=Host(config)
    try:
        previous=(host.read(host.root+'/.xuverse-active') or b'').decode().strip()
        if args.rollback:
            ready=host.read(host.root+'/.xuverse-releases/'+revision+'/.ready')
            if ready!=revision.encode(): raise RuntimeError('Rollback target is not a complete retained release')
            manifest=json.loads(host.read(host.root+'/.xuverse-releases/'+revision+'/release.json'))
            connections=[]; local=threading.local(); lock=threading.Lock()
            def verify(item):
                if not hasattr(local,'connection'):
                    local.connection=Host(config)
                    with lock: connections.append(local.connection)
                name,digest=item; actual=local.connection.read(host.root+'/.xuverse-releases/'+revision+'/'+name)
                if actual is None or hashlib.sha256(actual).hexdigest()!=digest: raise RuntimeError('Rollback target hash mismatch')
            try:
                with ThreadPoolExecutor(max_workers=4) as pool: list(pool.map(verify,manifest['files'].items()))
            finally:
                for connection in connections: connection.close()
            host.reconnect()
            host.pointer(revision)
        else:
            backup=Path.home()/'.codex/private-backups'/('xuverse-host-'+datetime.datetime.now(datetime.timezone.utc).strftime('%Y%m%dT%H%M%SZ'))
            if not previous:
                if args.reuse_backup:
                    if not (args.reuse_backup/'uploads').is_dir(): raise RuntimeError('Incomplete prior backup')
                    for name in ['.htaccess','config.local.php']:
                        if (args.reuse_backup/name).read_bytes()!=host.read(host.root+'/'+name): raise RuntimeError('Host changed since prior backup')
                    print('Reusing the explicitly selected private pre-release backup:',args.reuse_backup,flush=True)
                else: host.backup_runtime(backup)
            else:
                backup.mkdir(parents=True,exist_ok=True); (backup/'previous-release.txt').write_text(previous+'\n')
            stage(host,revision,args.fail_after); install_router(host,revision); host.pointer(revision)
        receipt={'revision':revision,'previous_revision':previous,'activated_at':datetime.datetime.now(datetime.timezone.utc).isoformat(timespec='seconds'),'content_sha256':json.loads(blob(revision,'content/manifest.json'))['content_sha256'],'activation':'FTPS pointer read-back passed','live_http_verification':'pending browser acceptance'}
        (ROOT/'output').mkdir(exist_ok=True); (ROOT/'output/publish-receipt.json').write_text(json.dumps(receipt,indent=2))
        print(json.dumps(receipt,indent=2)); print('Verify release.json and rendered pages in the authorised browser before claiming publication success.')
    finally: host.close()

if __name__=='__main__':
    try: main()
    except Exception as error:
        print('Publish stopped:',type(error).__name__,str(error),file=sys.stderr); sys.exit(1)
