<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/media_helpers.php';
require_once __DIR__ . '/../includes/music_helpers.php';

function check($condition, $name) {
    if (!$condition) { throw new RuntimeException('FAIL: ' . $name); }
    echo 'PASS: ' . $name . PHP_EOL;
}
putenv('XUVERSE_APP_PATH'); putenv('APP_PATH');
putenv('XUVERSE_BASE_URL'); putenv('BASE_URL');
foreach (['/index.php' => '', '/login.php' => '', '/admin/projects/edit.php' => '',
          '/xuverse/index.php' => '/xuverse', '/xuverse/admin/projects/edit.php' => '/xuverse'] as $script => $expected) {
    $_SERVER['SCRIPT_NAME'] = $script;
    check(xuverse_app_path() === $expected, 'path ' . $script);
    check(xuverse_url('projects/7') === $expected . '/projects/7', 'detail URL ' . $script);
}
putenv('XUVERSE_BASE_URL=https://portfolio.example.com');
putenv('XUVERSE_APP_PATH=/');
check(xuverse_absolute_url('assets/images/test.webp') === 'https://portfolio.example.com/assets/images/test.webp', 'root asset');
putenv('XUVERSE_BASE_URL=https://portfolio.example.com/nested/site');
putenv('XUVERSE_APP_PATH=/nested/site');
check(xuverse_absolute_url('/nested/site/projects/7') === 'https://portfolio.example.com/nested/site/projects/7', 'nested canonical');
check(xuverse_asset('uploads/media/a b.webp') === '/nested/site/uploads/media/a%20b.webp', 'uploaded URL');
$_SESSION['csrf_token'] = 'expected';
$_POST['csrf_token'] = ['malformed'];
check(!xuverse_verify_csrf(), 'array CSRF rejected');
$_POST['csrf_token'] = 'expected';
check(xuverse_verify_csrf(), 'valid CSRF');
if (!is_dir(dirname(__DIR__) . '/tmp')) { mkdir(dirname(__DIR__) . '/tmp', 0700, true); }
$sentinel = tempnam(dirname(__DIR__) . '/tmp', 'delete-boundary-');
try {
    xuverse_delete_uploaded_file('uploads/../tmp/' . basename($sentinel));
    xuverse_delete_local_file('tmp/' . basename($sentinel));
    check(is_file($sentinel), 'upload deletion cannot escape uploads');
} finally { unlink($sentinel); }
echo "Portability checks complete.\n";
