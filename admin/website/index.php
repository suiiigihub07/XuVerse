<?php
require_once '../../includes/dashboard-content.php';
xuverse_session_start();
require_once '../../includes/db.php';
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') { header('Location: ' . xuverse_url('login')); exit; }
header('Cache-Control: no-store');
$editorWritable = xuverse_editor_is_writable();
$sections = xuverse_editor_sections();
$collection = is_string($_GET['collection'] ?? null) ? $_GET['collection'] : 'media';
if (!isset($sections[$collection])) { http_response_code(404); exit('Unknown content section.'); }
$data = xuverse_editor_read($collection);
$itemKey = is_string($_GET['item'] ?? null) ? $_GET['item'] : null;
if ($collection === 'media' && $itemKey === 'new' && ($_GET['type'] ?? '') === 'card series') {
    header('Location: ' . xuverse_url('admin/website/index.php') . '?collection=published&item=new', true, 302);
    exit;
}
$isList = xuverse_editor_is_list($collection);
$editing = !$isList || $itemKey !== null;
$entity = $data; $body = ''; $error = ''; $review = null;
$revision = xuverse_editor_revision($collection, $itemKey ?? '');
if ($isList && $editing) {
    $entity = null;
    if ($itemKey === 'new') {
        $type = $_GET['type'] ?? 'photograph';
        if (!in_array($type,['card series','photograph','video'],true)) $type='card series';
        $entity = xuverse_editor_template($collection,$type);
    } else foreach ($data as $entry) if ($entry['slug'] === $itemKey) { $entity = $entry; break; }
    if (!$entity) { http_response_code(404); exit('Entry not found.'); }
    if ($itemKey !== 'new') $entity['publication_status'] ??= 'published';
    if (in_array($collection,['media','published','projects'],true)) $entity = array_merge(xuverse_editor_template($collection,$entity['type'] ?? ''),$entity);
    if ($collection === 'media') $entity = xuverse_editor_media_defaults($entity);
    if ($collection === 'articles') {
        if ($itemKey !== 'new') $body = xuverse_body($entity);
        $entity = array_merge(xuverse_editor_template($collection),$entity);
        if (!isset($entity['source_note']) || $entity['source_note'] === '') $entity['source_note'] = !empty($entity['references']) ? xuverse_content('site')['research_source_note'] : '';
    }
}
if ($collection === 'profile') {
    $entity['roles'] = array_map(static fn($role) => $role + ['description' => ''], $entity['roles']);
}
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    try {
        if (!xuverse_verify_csrf()) throw new RuntimeException('Your session expired. Refresh the page before saving.');
        $action = $_POST['action'] ?? 'edit';
        if ($action === 'review') {
            xuverse_editor_assert_writable();
            unset($_SESSION['xuverse_change_review']);
            $file = $_FILES['change_file'] ?? [];
            if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'] ?? '')) throw new RuntimeException('Choose the downloaded XuVerse change file.');
            if (filesize($file['tmp_name']) > XUVERSE_EDITOR_BUNDLE_LIMIT) throw new RuntimeException('Use a XuVerse change file under 8 MB.');
            $bytes = file_get_contents($file['tmp_name']);
            [$bundle, $images, $preview] = xuverse_editor_inspect_bundle($bytes);
            $token = bin2hex(random_bytes(20));
            $_SESSION['xuverse_change_review'] = ['token' => $token, 'expires' => time() + 1800, 'bytes' => $bytes];
            $review = ['token' => $token, 'bundle' => $bundle, 'images' => count($images), 'changes' => xuverse_editor_bundle_changes($bundle, $preview)];
        } elseif ($action === 'apply') {
            xuverse_editor_assert_writable();
            $pending = $_SESSION['xuverse_change_review'] ?? [];
            if (($pending['expires'] ?? 0) < time() || !is_string($_POST['review_token'] ?? null) || !hash_equals($pending['token'] ?? '', $_POST['review_token'])) throw new RuntimeException('The review has expired. Choose the change file again.');
            [$bundle, $images] = xuverse_editor_inspect_bundle($pending['bytes']);
            $savedSlug = xuverse_editor_save($bundle['collection'],$bundle['item'],$bundle['values'],$bundle['writing'],$bundle['base'],[],$images);
            unset($_SESSION['xuverse_change_review']);
            header('Location: ' . xuverse_url('admin/website/index.php') . '?collection=' . urlencode($bundle['collection']) . ($savedSlug !== '' ? '&item=' . urlencode($savedSlug) : '') . '&saved=1'); exit;
        } elseif ($action === 'undo') {
            xuverse_editor_undo_last();
            header('Location: ' . xuverse_url('admin/website/index.php') . '?collection=' . urlencode($collection) . '&undone=1'); exit;
        } elseif ($action === 'edit') {
            if (!$editing || !is_string($_POST['payload'] ?? null) || strlen($_POST['payload']) > 2000000) throw new RuntimeException('Invalid content submission.');
            $submitted = json_decode($_POST['payload'],true,64,JSON_THROW_ON_ERROR);
            if (!is_array($submitted)) throw new RuntimeException('Invalid content submission.');
            $body = is_string($_POST['body'] ?? null) ? $_POST['body'] : '';
            $expected = is_string($_POST['revision'] ?? null) ? $_POST['revision'] : '';
            $images = $_FILES['images'] ?? [];
            if (!$editorWritable) {
                $change = xuverse_editor_export($collection,$itemKey ?? '',$submitted,$body,$expected,$images);
                header('Content-Type: application/json; charset=UTF-8');
                header('X-Content-Type-Options: nosniff');
                header('Content-Disposition: attachment; filename="xuverse-' . $collection . '-change.json"');
                echo $change;
                exit;
            }
            $savedSlug = xuverse_editor_save($collection,$itemKey ?? '',$submitted,$body,$expected,$images);
            header('Location: ' . xuverse_url('admin/website/index.php') . '?collection=' . urlencode($collection) . ($isList ? '&item=' . urlencode($savedSlug) : '') . '&saved=1'); exit;
        } else { throw new RuntimeException('Unknown editor action.'); }
    } catch (Throwable $exception) {
        $error = $exception instanceof JsonException ? 'This change could not be decoded. Check the source fields or choose the original downloaded file.' : $exception->getMessage();
        if (isset($submitted) && is_array($submitted)) {
            $entity = $submitted + $entity;
            $revision = $expected ?? $revision;
        }
    }
}
$pageTitle = 'Website content - XuVerse'; $robots = xuverse_noindex();
include '../../includes/header.php'; include '../../includes/navbar.php';
$editorAsset = 'assets/js/website-editor.js'; $styleAsset='assets/css/website-editor.css';
?>
<link rel="stylesheet" href="<?= e(xuverse_url($styleAsset)) ?><?= defined('XUVERSE_RELEASE_ID') && XUVERSE_RELEASE_ID ? '&amp;' : '?' ?>v=<?= substr(hash_file('sha256','../../'.$styleAsset),0,12) ?>">
<section class="section admin-page"><div class="container">
<div class="dashboard-heading"><div><a class="text-link" href="<?= e(xuverse_url('admin/dashboard.php')) ?>">Dashboard</a><h1>Website content</h1><p><?= $editorWritable ? 'Save changes directly to the local preview, or review a change file downloaded from the hosted editor.' : 'Edit these fields and download a change file. Import and review it in the local dashboard, then use Publish to put the reviewed result online.' ?></p></div><a class="btn secondary-btn" href="<?= e(xuverse_url()) ?>">Home</a></div>
<nav class="cms-section-nav" aria-label="Content sections"><?php foreach ($sections as $key=>$label): ?><a class="<?= $key === $collection ? 'active' : '' ?>"<?= $key === $collection ? ' aria-current="page"' : '' ?> href="?collection=<?= e($key) ?>"><?= e($label) ?></a><?php endforeach; ?></nav>
<?php if ($error): ?><p class="error" role="alert"><?= e($error) ?></p><?php endif; ?>
<?php if ($editorWritable && isset($_GET['saved']) && !$error): ?><p class="success" role="status">Saved. The local website now uses your changes.</p><?php endif; ?>
<?php if ($editorWritable && isset($_GET['undone']) && !$error): ?><p class="success" role="status">Restored the local content from before your last save.</p><?php endif; ?>
<p class="cms-publish-note"><?= $editorWritable ? 'Saving creates a private local backup. Resolve any draft entries and use Publish when ready to sync localhost, GitHub and the hosted site.' : 'Downloading does not change the live website or store a draft on the host. Keep the change file private until you review it in the local dashboard.' ?></p>
<?php if ($editorWritable && !$review): ?>
<details class="cms-publish-note"<?= ($_POST['action'] ?? '') === 'review' ? ' open' : '' ?>><summary>Import a hosted change file</summary>
<form class="cms-import-form" method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= e(xuverse_csrf_token()) ?>">
    <input type="hidden" name="action" value="review">
    <label for="website-change-file">Choose the downloaded JSON file</label>
    <input id="website-change-file" type="file" name="change_file" accept=".json,application/json" required>
    <button class="btn secondary-btn" type="submit">Review change</button>
</form></details>
<?php if (isset($_SESSION['xuverse_last_local_save'])): ?>
<form class="cms-undo" method="post">
    <input type="hidden" name="csrf_token" value="<?= e(xuverse_csrf_token()) ?>">
    <input type="hidden" name="action" value="undo">
    <button class="btn secondary-btn" type="submit">Undo last local save</button>
</form>
<?php endif; ?>
<?php endif; ?>
<?php if ($review): ?>
<section class="cms-review" aria-labelledby="website-change-review">
<h2 id="website-change-review">Review changes to <?= e($sections[$review['bundle']['collection']]) ?></h2>
<p><?= $review['bundle']['item'] === 'new' ? 'New entry.' : ($review['bundle']['item'] !== '' ? 'Entry: ' . e($review['bundle']['item']) . '.' : 'Section settings.') ?> <?= $review['images'] ? e($review['images'] . ' new image(s) included.') : '' ?></p>
<?php if (!$review['changes']): ?><p>The change file makes no content changes.</p><?php endif; ?>
<?php foreach ($review['changes'] as $field => [$before, $after]): ?>
<details class="cms-publish-note"><summary><?= e(ucfirst(str_replace('_',' ',$field))) ?></summary>
<div class="cms-review-values"><?php foreach (['Current' => $before, 'Proposed' => $after] as $label => $value): ?>
<div><strong><?= e($label) ?></strong><pre><?= e(is_string($value) ? $value : ($value === null ? '—' : json_encode($value, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE))) ?></pre></div>
<?php endforeach; ?></div></details>
<?php endforeach; ?>
<form method="post" class="admin-form-actions">
    <input type="hidden" name="csrf_token" value="<?= e(xuverse_csrf_token()) ?>">
    <input type="hidden" name="action" value="apply">
    <input type="hidden" name="review_token" value="<?= e($review['token']) ?>">
    <button class="btn" type="submit"<?= $review['changes'] ? '' : ' disabled' ?>>Apply to local preview</button>
    <a class="btn secondary-btn" href="?collection=<?= e($collection) ?>">Cancel</a>
</form>
</section>
<?php elseif (!$editing): ?>
<div class="section-heading with-actions"><h2><?= e($sections[$collection]) ?></h2><div class="project-buttons">
<?php if ($collection === 'media'): ?>
<a class="btn" href="?collection=media&amp;item=new&amp;type=photograph">New photo</a><a class="btn secondary-btn" href="?collection=media&amp;item=new&amp;type=video">New video</a>
<?php elseif ($collection === 'published'): ?><a class="btn" href="?collection=published&amp;item=new">New post</a>
<?php else: ?><a class="btn" href="?collection=<?= e($collection) ?>&amp;item=new">New entry</a><?php endif; ?>
</div></div>
<div class="admin-list"><?php foreach ($data as $entry): ?><article class="card cms-entry"><div><p class="page-kicker"><?= e(($entry['publication_status'] ?? 'published') . (isset($entry['type']) ? ' · '.$entry['type'] : '')) ?></p><h2><?= e($entry['title']) ?></h2></div><a class="btn secondary-btn" href="?collection=<?= e($collection) ?>&amp;item=<?= e(urlencode($entry['slug'])) ?>">Edit</a></article><?php endforeach; ?></div>
<?php else: ?>
<?php if ($isList): ?><a class="text-link" href="?collection=<?= e($collection) ?>">Back to entries</a><?php endif; ?>
<h2><?= e($isList ? ($itemKey === 'new' ? 'New entry' : $entity['title']) : $sections[$collection]) ?></h2>
<?php if ($collection === 'published'): ?><p class="admin-help">Create ANCHOR, news or other published work here. Combine up to 10 images, YouTube videos and article text in one post. The public listing shows one cover and your heading; readers open the heading for the full post.</p><?php endif; ?>
<form class="admin-form cms-form" method="post" enctype="multipart/form-data" data-content-editor>
<input type="hidden" name="csrf_token" value="<?= e(xuverse_csrf_token()) ?>">
<input type="hidden" name="revision" value="<?= e($revision) ?>">
<input type="hidden" name="action" value="edit">
<input type="hidden" name="payload" value="<?= e(json_encode($entity,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)) ?>">
<script type="application/json" data-editor-config><?= json_encode(['collection'=>$collection,'existing'=>$itemKey !== 'new','data'=>$entity,'imageBase'=>xuverse_url(),'choices'=>$collection === 'site' ? xuverse_editor_site_choices() : [],'rowTemplates'=>['roles'=>['title'=>'','organisation'=>'','dates'=>'','category'=>'leadership','description'=>''],'resume_areas'=>['title'=>'','items'=>[]],'references'=>['citation'=>'','url'=>'','access'=>'']]],JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE) ?></script>
<div data-editor-fields></div>
<?php if ($collection === 'articles'): ?><div class="input-group"><label for="article-body">Article body</label><textarea id="article-body" name="body" rows="24" required><?= e($body) ?></textarea><p class="admin-help">Use paragraphs, ## headings, - lists, **bold** text and links. After you apply the changes locally, published writing updates its PDF from the same text.</p></div><?php endif; ?>
<?php if (in_array($collection, ['site', 'projects', 'published', 'media', 'articles'], true)):
    $multipleImages = $collection === 'published' || ($collection === 'media' && ($entity['type'] ?? '') === 'card series');
    $videoPoster = $collection === 'media' && ($entity['type'] ?? '') === 'video';
    $uploadLabel = $multipleImages ? 'Add images to this post' : ($videoPoster ? 'Upload a video poster' : 'Replace image');
?>
<div class="input-group"><label for="content-images"><?= e($uploadLabel) ?></label><input id="content-images" type="file" name="images[]" accept="image/jpeg,image/png,image/webp" <?= $multipleImages ? 'multiple' : '' ?>><p class="admin-help">JPG, PNG or WebP, up to 4 MB each. <?php if ($multipleImages): ?>Gallery posts hold up to 10 images in total. Adjust the order or remove existing images above. <?php elseif ($videoPoster): ?>Use a 16:9 still from the video. <?php endif; ?><?= $editorWritable ? '' : 'The downloaded change file must be smaller than 8 MB. ' ?>Server total upload limit: <?= e(ini_get('post_max_size')) ?>.</p></div>
<?php endif; ?>
<div class="admin-form-actions"><button class="btn" type="submit"><?= $editorWritable ? 'Save changes' : 'Download change file' ?></button><a class="btn secondary-btn" href="<?= e(xuverse_url('admin/dashboard.php')) ?>">Back to dashboard</a></div>
<noscript><p class="error">Enable JavaScript to use the structured content editor.</p></noscript>
</form>
<script src="<?= e(xuverse_url($editorAsset)) ?><?= defined('XUVERSE_RELEASE_ID') && XUVERSE_RELEASE_ID ? '&amp;' : '?' ?>v=<?= substr(hash_file('sha256','../../'.$editorAsset),0,12) ?>" defer></script>
<?php endif; ?>
</div></section>
<?php include '../../includes/footer.php'; ?>
