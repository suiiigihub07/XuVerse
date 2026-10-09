<?php
require_once __DIR__ . '/content.php';
require_once __DIR__ . '/auth.php';

function xuverse_editor_is_writable() {
    $root = dirname(__DIR__);
    if (defined('XUVERSE_RELEASE_ID') || xuverse_is_production()
        || (!is_dir($root . '/.git') && !is_file($root . '/.git'))) return false;
    if (PHP_SAPI === 'cli') return true;
    $address = $_SERVER['REMOTE_ADDR'] ?? '';
    if ($address === '::1') return true;
    if (str_starts_with(strtolower($address), '::ffff:')) $address = substr($address, 7);
    return filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)
        && str_starts_with($address, '127.');
}

function xuverse_editor_assert_writable() {
    if (xuverse_editor_is_writable()) return;
    if (PHP_SAPI !== 'cli' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        http_response_code(405);
        header('Allow: GET');
    }
    throw new RuntimeException('This website snapshot is read-only. Edit the local source checkout, then use Publish to update GitHub and the hosted site.');
}

function xuverse_editor_sections() {
    return ['media'=>'Photographs & videos','published'=>'Published work & news','copy'=>'Page writing','profile'=>'Profile, education & experience','projects'=>'Projects','articles'=>'Articles & research','skills'=>'Skills','links'=>'Contact & social links','site'=>'Branding, images & page headings'];
}

function xuverse_editor_template($collection, $type = '') {
    if ($collection === 'published') return ['title'=>'','slug'=>'','summary'=>'','description'=>'','article_body'=>'','cards'=>[],'video_urls'=>[],'alt'=>'','credit_note'=>'','publication_status'=>'draft'];
    if ($collection === 'media') {
        $entry = ['title'=>'','slug'=>'','type'=>$type ?: 'card series','summary'=>'','description'=>'','alt'=>'','credit_note'=>'','publication_status'=>'draft'];
        if ($type === 'video') { $entry['url'] = ''; }
        elseif ($type === 'photograph') { $entry['image'] = ''; }
        else { $entry['cards'] = []; }
        return $entry;
    }
    if ($collection === 'projects') return ['title'=>'','slug'=>'','summary'=>'','description'=>'','technology'=>[],'role'=>'','status'=>'','repository'=>null,'live'=>null,'learning'=>'','image'=>'','publication_status'=>'draft'];
    if ($collection === 'articles') return ['title'=>'','subtitle'=>'','slug'=>'','author'=>xuverse_content('copy')['name'],'date_display'=>'','written_date'=>'','date_precision'=>'year','category'=>'','language'=>'en','summary'=>'','tags'=>[],'publication_status'=>'draft','source_note'=>'','references'=>[]];
    return [];
}

function xuverse_editor_media_defaults($entry) {
    if (!isset($entry['description']) || (is_string($entry['description']) && trim($entry['description']) === '')) $entry['description'] = $entry['summary'] ?? '';
    if (!isset($entry['alt']) || (is_string($entry['alt']) && trim($entry['alt']) === '')) $entry['alt'] = $entry['title'] ?? '';
    return $entry;
}

function xuverse_editor_read($collection) {
    if (!isset(xuverse_editor_sections()[$collection])) throw new RuntimeException('Unknown content section.');
    return xuverse_content($collection, true);
}

function xuverse_editor_revision($collection) {
    $bytes = file_get_contents(dirname(__DIR__) . '/content/' . $collection . '.json');
    if ($collection === 'articles') foreach (glob(dirname(__DIR__) . '/content/writing/*.md') as $path) $bytes .= "\0" . basename($path) . "\0" . file_get_contents($path);
    return hash('sha256', $bytes);
}

function xuverse_editor_asset_valid($path) {
    if (!is_string($path) || !preg_match('#^(assets/images|uploads)/[A-Za-z0-9_./-]+\.(webp|png|jpg|jpeg|avif)$#i', $path) || str_contains($path, '..')) return false;
    $base = str_starts_with($path, 'uploads/') && defined('XUVERSE_STORAGE_ROOT') ? XUVERSE_STORAGE_ROOT : dirname(__DIR__);
    return is_file($base . '/' . $path);
}

function xuverse_editor_validate($collection, $data) {
    if (!is_array($data) || count($data) > 500) throw new RuntimeException('This section has too many entries.');
    $shape = function($expected,$value) use (&$shape) {
        if (is_array($expected)) {
            if (!is_array($value)) throw new RuntimeException('Keep the structure of the content fields.');
            if (array_is_list($expected) && !array_is_list($value)) throw new RuntimeException('Keep list fields as lists.');
            if (!array_is_list($expected)) foreach ($expected as $key=>$child) {
                if (!array_key_exists($key,$value)) throw new RuntimeException('A required content field is missing.');
                $shape($child,$value[$key]);
            }
            elseif ($expected) foreach ($value as $child) $shape($expected[0],$child);
        } elseif ($expected !== null && gettype($expected) !== gettype($value)) throw new RuntimeException('A content field has the wrong value type.');
    };
    if (!in_array($collection,['media','published','projects','articles'],true)) $shape(xuverse_editor_read($collection),$data);
    $walk = function ($value, $key = '', $parent = '') use (&$walk,$collection) {
        if (is_array($value)) { foreach ($value as $k=>$child) $walk($child, (string)$k, $key); return; }
        if (!is_scalar($value) && $value !== null) throw new RuntimeException('Invalid field value.');
        if (is_string($value) && strlen($value) > 250000) throw new RuntimeException('A field is too long.');
        $linkField = in_array($key, ['url','repository','live'], true)
            || ($collection === 'links' && $parent === '' && in_array($key, ['github','linkedin','instagram','youtube','tiktok','facebook'], true));
        if ($value && $linkField) {
            if (!filter_var($value, FILTER_VALIDATE_URL) || !in_array(parse_url($value, PHP_URL_SCHEME), ['https','http'], true)) throw new RuntimeException('Use a valid HTTP or HTTPS link.');
        }
    };
    $walk($data);
    if (in_array($collection, ['media','published','projects','articles'], true)) {
        if (!array_is_list($data)) throw new RuntimeException('Invalid collection.');
        $slugs = [];
        foreach ($data as $item) {
            foreach (['slug','title','summary'] as $field) if (!is_string($item[$field] ?? null) || ($field !== 'summary' && trim($item[$field]) === '')) throw new RuntimeException(ucfirst($field) . ' is required.');
            if (!preg_match('/^[a-z][a-z0-9-]{0,99}$/', $item['slug']) || isset($slugs[$item['slug']])) throw new RuntimeException('Use a unique address with lowercase letters, numbers and hyphens.');
            $slugs[$item['slug']] = true;
            if (!in_array($item['publication_status'] ?? 'published', ['draft','published'], true)) throw new RuntimeException('Invalid publication status.');
            foreach (['description','article_body','alt','credit_note','author','subtitle','date_display','source_note'] as $key) if (isset($item[$key]) && !is_string($item[$key])) throw new RuntimeException('Use text for ' . str_replace('_',' ',$key) . '.');
            foreach (['technology','tags','related_links'] as $key) if (isset($item[$key])) {
                if (!is_array($item[$key]) || !array_is_list($item[$key])) throw new RuntimeException('Invalid list: ' . $key);
                foreach ($item[$key] as $value) if (!is_string($value)) throw new RuntimeException('Use text for each ' . $key . ' item.');
            }
            if (isset($item['references']) && (!is_array($item['references']) || !array_is_list($item['references']))) throw new RuntimeException('References must be a list.');
            foreach ($item['references'] ?? [] as $reference) foreach (['citation','url','access'] as $key) if (!is_string($reference[$key] ?? null)) throw new RuntimeException('A reference needs citation, URL and access details.');
            if ($collection === 'media') {
                if (!in_array($item['type'] ?? '', ['video','photograph','card series'], true)) throw new RuntimeException('Choose a supported media type.');
                if ($item['type'] === 'video') {
                    if (xuverse_youtube_embed($item['url'] ?? '') === '') throw new RuntimeException('Enter a valid YouTube video link.');
                } else {
                    $images = $item['type'] === 'card series' ? ($item['cards'] ?? []) : [$item['image'] ?? ''];
                    if (!is_array($images) || !array_is_list($images) || count($images) < 1 || count($images) > 10) throw new RuntimeException('A post must contain between 1 and 10 images.');
                    foreach ($images as $image) if (!xuverse_editor_asset_valid($image)) throw new RuntimeException('Choose an existing image or upload a JPG, PNG or WebP image.');
                }
            }
            if ($collection === 'projects' && !empty($item['image']) && !xuverse_editor_asset_valid($item['image'])) throw new RuntimeException('The project image was not found.');
            if ($collection === 'published') {
                $images = $item['cards'] ?? [];
                $videos = $item['video_urls'] ?? [];
                foreach (['images'=>$images,'videos'=>$videos] as $kind=>$items) {
                    if (!is_array($items) || !array_is_list($items) || count($items) > 10) throw new RuntimeException('Use a list of up to 10 ' . $kind . '.');
                }
                foreach ($images as $image) if (!xuverse_editor_asset_valid($image)) throw new RuntimeException('Choose an existing image or upload a JPG, PNG or WebP image.');
                foreach ($videos as $url) if (!is_string($url) || xuverse_youtube_embed($url) === '') throw new RuntimeException('Enter a valid YouTube link for each video.');
                if (!$images && !$videos && trim($item['article_body'] ?? '') === '' && trim($item['description'] ?? '') === '') throw new RuntimeException('Add images, a YouTube video, or article text to this published post.');
            }
            if ($collection === 'articles') {
                if (!in_array($item['date_precision'] ?? '', ['year','month','day'], true) || trim($item['author'] ?? '') === '' || trim($item['date_display'] ?? '') === '') throw new RuntimeException('An author, displayed date and date precision are required.');
                $datePatterns = ['year'=>'/^\d{4}$/','month'=>'/^\d{4}-(0[1-9]|1[0-2])$/','day'=>'/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])$/'];
                if (!preg_match($datePatterns[$item['date_precision']], $item['written_date'] ?? '')) throw new RuntimeException('Use a written date matching its precision: YYYY, YYYY-MM or YYYY-MM-DD.');
                if ($item['date_precision'] === 'day') { [$year,$month,$day] = array_map('intval',explode('-',$item['written_date'])); if (!checkdate($month,$day,$year)) throw new RuntimeException('Choose a valid calendar date.'); }
                if (($item['pdf_path'] ?? '') !== 'assets/downloads/' . $item['slug'] . '.pdf') throw new RuntimeException('Invalid article download path.');
            }
        }
    }
    if ($collection === 'copy') {
        if (count($data['hero_actions']) !== 3) throw new RuntimeException('Keep all three home action labels.');
        foreach ($data['hero_actions'] as $label) if (trim($label) === '') throw new RuntimeException('Home action labels cannot be empty.');
    }
    if ($collection === 'skills') foreach ($data as $items) {
        if (!is_array($items) || !array_is_list($items)) throw new RuntimeException('Each skill category must contain a list.');
        foreach ($items as $item) if (!is_string($item) || trim($item) === '') throw new RuntimeException('Use a nonempty label for each skill.');
    }
    if ($collection === 'links' && (!str_starts_with($data['email'] ?? '', 'mailto:') || !filter_var(substr($data['email'],7), FILTER_VALIDATE_EMAIL))) throw new RuntimeException('The email link must be mailto: followed by a valid email address.');
    if ($collection === 'site') foreach (['portrait','portrait_alt','site_title'] as $key) if (empty($data[$key])) throw new RuntimeException('Site title, portrait and portrait description are required.');
    if ($collection === 'site' && !xuverse_editor_asset_valid($data['portrait'])) throw new RuntimeException('The portrait image was not found.');
}

function xuverse_editor_write($path, $bytes) {
    xuverse_editor_assert_writable();
    $temporary = $path . '.tmp-' . bin2hex(random_bytes(5));
    if (file_put_contents($temporary, $bytes, LOCK_EX) === false) throw new RuntimeException('The content folder is not writable.');
    if (!rename($temporary, $path)) { @unlink($temporary); throw new RuntimeException('Could not save the content file.'); }
}

function xuverse_editor_upload($files) {
    xuverse_editor_assert_writable();
    if (!isset($files['name']) || !is_array($files['name'])) return [];
    $selected = array_keys(array_filter($files['error'], fn($error) => $error !== UPLOAD_ERR_NO_FILE));
    if (count($selected) > 10) throw new RuntimeException('Choose up to 10 images.');
    $validated = [];
    foreach ($selected as $i) {
        if ($files['error'][$i] !== UPLOAD_ERR_OK || !is_uploaded_file($files['tmp_name'][$i])) throw new RuntimeException('An image upload failed. Please choose it again.');
        if ($files['size'][$i] > 4*1024*1024) throw new RuntimeException('Each image must be 4 MB or smaller.');
        $info = @getimagesize($files['tmp_name'][$i]);
        if (!$info || $info[0]*$info[1] > 20000000 || !in_array($info['mime'], ['image/jpeg','image/png','image/webp'], true) || (new finfo(FILEINFO_MIME_TYPE))->file($files['tmp_name'][$i]) !== $info['mime']) throw new RuntimeException('Use a valid JPG, PNG or WebP image, up to 20 megapixels.');
        $validated[] = [$files['tmp_name'][$i],$info['mime']];
    }
    $saved = [];
    try {
        foreach ($validated as [$temporary,$mime]) {
            $image = match($mime) {'image/jpeg'=>imagecreatefromjpeg($temporary),'image/png'=>imagecreatefrompng($temporary),default=>imagecreatefromwebp($temporary)};
            if (!$image) throw new RuntimeException('Could not process the image.');
            if ($mime === 'image/jpeg' && function_exists('exif_read_data')) { $exif = @exif_read_data($temporary,'IFD0'); $image = xuverse_orient_image($image,$exif['Orientation'] ?? 1); }
            $optimized = xuverse_resize_upload_image($image);
            $relative = 'assets/images/editor/' . bin2hex(random_bytes(16)) . '.webp';
            $folder = dirname(__DIR__) . '/assets/images/editor';
            if (!is_dir($folder) && !mkdir($folder,0755,true)) throw new RuntimeException('The image folder is not writable.');
            $ok = imagewebp($optimized, dirname(__DIR__) . '/' . $relative, 88);
            imagedestroy($optimized); imagedestroy($image);
            if (!$ok) throw new RuntimeException('Could not save the image.');
            $saved[] = $relative;
        }
    } catch (Throwable $error) { foreach ($saved as $path) @unlink(dirname(__DIR__) . '/' . $path); throw $error; }
    return $saved;
}

function xuverse_editor_save($collection, $itemKey, $entity, $body, $expected, $files = []) {
    xuverse_editor_assert_writable();
    $root = dirname(__DIR__);
    $private = (defined('XUVERSE_STORAGE_ROOT') ? XUVERSE_STORAGE_ROOT : $root) . '/output/content-backups';
    if (!is_dir($private) && !mkdir($private,0700,true)) throw new RuntimeException('The private backup folder is not writable.');
    $lock = fopen($private . '/editor.lock','c');
    if (!$lock || !flock($lock,LOCK_EX)) throw new RuntimeException('Another save is in progress. Please retry.');
    $uploaded = []; $backup = []; $newPaths = [];
    try {
        if (!hash_equals(xuverse_editor_revision($collection), $expected)) throw new RuntimeException('This content changed since you opened it. Reload before saving to protect the newer edits.');
        $data = xuverse_editor_read($collection);
        $isList = array_is_list($data);
        $position = null;
        if ($isList && $itemKey !== 'new') {
            foreach ($data as $i=>$existing) if ($existing['slug'] === $itemKey) { $position=$i; break; }
            if ($position === null) throw new RuntimeException('This entry no longer exists.');
            $entity['slug'] = $data[$position]['slug'];
            if (isset($data[$position]['legacy_ids'])) $entity['legacy_ids'] = $data[$position]['legacy_ids'];
            if ($collection === 'media') $entity['type'] = $data[$position]['type'];
        }
        if ($collection === 'articles') {
            if (trim($body) === '') throw new RuntimeException('Write the article body before saving.');
            $entity['pdf_path'] = 'assets/downloads/' . ($entity['slug'] ?? '') . '.pdf';
            $entity['related_links'] ??= [];
            $entity['word_count'] = str_word_count(strip_tags($body));
            $entity['reading_time_minutes'] = max(1,(int)ceil($entity['word_count']/200));
            unset($entity['published_at']);
            if ($position !== null && isset($data[$position]['published_at'])) $entity['published_at'] = $data[$position]['published_at'];
        }
        if ($collection === 'media') $entity = xuverse_editor_media_defaults($entity);
        $existingImages = $entity['cards'] ?? [];
        $uploadCount = isset($files['error']) && is_array($files['error']) ? count(array_filter($files['error'], fn($error) => $error !== UPLOAD_ERR_NO_FILE)) : 0;
        $multiple = $collection === 'published' || ($collection === 'media' && ($entity['type'] ?? '') === 'card series');
        if (($multiple && count($existingImages)+$uploadCount > 10) || (!$multiple && $uploadCount > 1)) throw new RuntimeException($multiple ? 'A gallery can hold up to 10 images. Remove an image before adding more.' : 'Choose one image for this entry.');
        require_once __DIR__ . '/media_helpers.php';
        $uploaded = xuverse_editor_upload($files);
        if ($uploaded) {
            if ($multiple) $entity['cards'] = array_merge($existingImages,$uploaded);
            elseif ($collection === 'site') $entity['portrait'] = $uploaded[0];
            else $entity['image'] = $uploaded[0];
        }
        if ($isList) { if ($position === null) $data[]=$entity; else $data[$position]=$entity; } else $data=$entity;
        xuverse_editor_validate($collection,$data);
        $paths = [$root . '/content/' . $collection . '.json',$root . '/content/manifest.json'];
        if ($collection === 'articles') $paths[] = $root . '/content/writing/' . $entity['slug'] . '.md';
        if (in_array($collection,['articles','copy','site'],true)) {
            foreach ($collection === 'articles' ? $data : xuverse_content('articles') as $article) $paths[] = $root . '/' . $article['pdf_path'];
        }
        $backupDir = $private . '/' . gmdate('Ymd-His') . '-' . bin2hex(random_bytes(4));
        if (!mkdir($backupDir,0700)) throw new RuntimeException('Could not create a private backup.');
        foreach (array_unique($paths) as $path) {
            if (is_file($path)) {
                $relative = str_replace('\\','/',substr($path,strlen($root)+1));
                $destination = $backupDir . '/' . $relative;
                if (!is_dir(dirname($destination))) mkdir(dirname($destination),0700,true);
                if (!copy($path,$destination)) throw new RuntimeException('Could not back up content before saving.');
                $backup[$path] = $destination;
            } else $newPaths[]=$path;
        }
        xuverse_editor_write($paths[0],json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR)."\n");
        if ($collection === 'articles') {
            $source = '# ' . $entity['title'] . "\n\n" . ($entity['subtitle'] ?: $entity['title']) . "\n\n" . $entity['author'] . ' · ' . $entity['date_display'] . "\n\n" . trim($body) . "\n";
            xuverse_editor_write($root . '/content/writing/' . $entity['slug'] . '.md',$source);
            require_once __DIR__ . '/content-build.php';
            xuverse_build_content(false);
        } elseif (in_array($collection,['copy','site'],true)) {
            xuverse_content($collection,true);
            require_once __DIR__ . '/content-build.php';
            xuverse_build_content(false);
        } else {
            xuverse_editor_refresh_manifest();
        }
        return $entity['slug'] ?? '';
    } catch (Throwable $error) {
        foreach ($backup as $path=>$source) copy($source,$path);
        foreach ($newPaths as $path) if (is_file($path)) @unlink($path);
        foreach ($uploaded as $path) @unlink($root . '/' . $path);
        throw $error;
    } finally { flock($lock,LOCK_UN); fclose($lock); }
}

function xuverse_editor_refresh_manifest() {
    xuverse_editor_assert_writable();
    $root = dirname(__DIR__); $manifest = xuverse_content('manifest',true);
    $manifest['files'] = []; $hash = hash_init('sha256');
    $paths = array_merge(glob($root . '/content/*.json'),glob($root . '/content/writing/*.md')); sort($paths);
    foreach ($paths as $path) {
        if (basename($path) === 'manifest.json') continue;
        $relative = str_replace('\\','/',substr($path,strlen($root)+1)); $bytes=file_get_contents($path);
        $manifest['files'][$relative] = hash('sha256',$bytes); hash_update($hash,$relative."\0".$bytes."\0");
    }
    $manifest['content_sha256'] = hash_final($hash);
    xuverse_editor_write($root . '/content/manifest.json',json_encode($manifest,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)."\n");
}
