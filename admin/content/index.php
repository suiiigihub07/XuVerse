<?php
require_once '../../includes/dashboard-content.php';
xuverse_session_start();
require_once '../../includes/db.php';
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') { header('Location: ' . xuverse_url('login')); exit; }
header('Cache-Control: no-store');
$editorWritable = xuverse_editor_is_writable();
$sections = xuverse_editor_sections();
$collection = $_GET['collection'] ?? 'media';
if (!isset($sections[$collection])) { http_response_code(404); exit('Unknown content section.'); }
$data = xuverse_editor_read($collection);
$itemKey = $_GET['item'] ?? null;
$isList = array_is_list($data);
$editing = !$isList || $itemKey !== null;
$entity = $data; $body = ''; $error = '';
$revision = (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && is_string($_POST['revision'] ?? null)) ? $_POST['revision'] : xuverse_editor_revision($collection);
if ($isList && $editing) {
    $entity = null;
    if ($itemKey === 'new') {
        $type = $_GET['type'] ?? 'card series';
        if (!in_array($type,['card series','photograph','video'],true)) $type='card series';
        $entity = xuverse_editor_template($collection,$type);
    } else foreach ($data as $entry) if ($entry['slug'] === $itemKey) { $entity = $entry; break; }
    if (!$entity) { http_response_code(404); exit('Entry not found.'); }
    if ($itemKey !== 'new') $entity['publication_status'] ??= 'published';
    if (in_array($collection,['media','projects'],true)) $entity = array_merge(xuverse_editor_template($collection,$entity['type'] ?? ''),$entity);
    if ($collection === 'media') $entity = xuverse_editor_media_defaults($entity);
    if ($collection === 'articles') {
        if ($itemKey !== 'new') $body = xuverse_body($entity);
        $entity = array_merge(xuverse_editor_template($collection),$entity);
        if (!isset($entity['source_note']) || $entity['source_note'] === '') $entity['source_note'] = !empty($entity['references']) ? xuverse_content('site')['research_source_note'] : '';
    }
}
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!$editorWritable) {
        http_response_code(405);
        header('Allow: GET');
        $error = 'This website snapshot is read-only. Open the local source checkout to save changes, then use Publish.';
    } else try {
        if (!xuverse_verify_csrf()) throw new RuntimeException('Your session expired. Refresh the page before saving.');
        if (!$editing || !is_string($_POST['payload'] ?? null) || strlen($_POST['payload']) > 2000000) throw new RuntimeException('Invalid content submission.');
        $submitted = json_decode($_POST['payload'],true,64,JSON_THROW_ON_ERROR);
        if (!is_array($submitted)) throw new RuntimeException('Invalid content submission.');
        $body = (string)($_POST['body'] ?? '');
        $savedSlug = xuverse_editor_save($collection,$itemKey ?? '',$submitted,$body,(string)($_POST['revision'] ?? ''),$_FILES['images'] ?? []);
        header('Location: ' . xuverse_url('admin/content/index.php') . '?collection=' . urlencode($collection) . ($isList ? '&item=' . urlencode($savedSlug) : '') . '&saved=1'); exit;
    } catch (Throwable $exception) { $error = $exception->getMessage(); if (isset($submitted) && is_array($submitted)) $entity=$submitted; }
}
$pageTitle = 'Website content - XuVerse'; $robots = xuverse_noindex();
include '../../includes/header.php'; include '../../includes/navbar.php';
$editorAsset = 'assets/js/content-editor.js'; $styleAsset='assets/css/content-editor.css';
?>
<link rel="stylesheet" href="<?= e(xuverse_url($styleAsset)) ?><?= defined('XUVERSE_RELEASE_ID') && XUVERSE_RELEASE_ID ? '&amp;' : '?' ?>v=<?= substr(hash_file('sha256','../../'.$styleAsset),0,12) ?>">
<section class="section admin-page"><div class="container">
<div class="dashboard-heading"><div><a class="text-link" href="<?= e(xuverse_url('admin/dashboard.php')) ?>">← Dashboard</a><h1>Website content</h1><p><?= $editorWritable ? 'Edit the content used by your public pages. Saves update this local preview and create a private backup.' : 'This hosted or shared preview is read-only. Review its content here and make changes in the local source checkout.' ?></p></div><a class="btn secondary-btn" href="<?= e(xuverse_url()) ?>">Home</a></div>
<nav class="cms-section-nav" aria-label="Content sections"><?php foreach ($sections as $key=>$label): ?><a class="<?= $key === $collection ? 'active' : '' ?>" href="?collection=<?= e($key) ?>"><?= e($label) ?></a><?php endforeach; ?></nav>
<?php if ($error): ?><p class="error" role="alert"><?= e($error) ?></p><?php endif; ?>
<?php if ($editorWritable && isset($_GET['saved']) && !$error): ?><p class="success" role="status">Saved. The local website now uses your changes.</p><?php endif; ?>
<p class="cms-publish-note">Drafts stay in the local source checkout. Review all entries before using Publish to sync GitHub and the hosting site; Publish requires drafts to be resolved first. Saving here does not run Publish or set a publication date.</p>
<?php if (!$editing): ?>
<div class="section-heading with-actions"><h2><?= e($sections[$collection]) ?></h2><div class="project-buttons">
<?php if ($editorWritable): ?>
<?php if ($collection === 'media'): ?>
<a class="btn" href="?collection=media&amp;item=new&amp;type=card%20series">New ANCHOR / gallery post</a><a class="btn secondary-btn" href="?collection=media&amp;item=new&amp;type=photograph">New photo</a><a class="btn secondary-btn" href="?collection=media&amp;item=new&amp;type=video">New video</a>
<?php else: ?><a class="btn" href="?collection=<?= e($collection) ?>&amp;item=new">New entry</a><?php endif; ?>
<?php endif; ?>
</div></div>
<div class="admin-list"><?php foreach ($data as $entry): ?><article class="card cms-entry"><div><p class="page-kicker"><?= e(($entry['publication_status'] ?? 'published') . (isset($entry['type']) ? ' · '.$entry['type'] : '')) ?></p><h2><?= e($entry['title']) ?></h2></div><a class="btn secondary-btn" href="?collection=<?= e($collection) ?>&amp;item=<?= e(urlencode($entry['slug'])) ?>"><?= $editorWritable ? 'Edit' : 'View' ?></a></article><?php endforeach; ?></div>
<?php else: ?>
<?php if ($isList): ?><a class="text-link" href="?collection=<?= e($collection) ?>">← Back to entries</a><?php endif; ?>
<h2><?= e($isList ? ($itemKey === 'new' ? 'New entry' : $entity['title']) : $sections[$collection]) ?></h2>
<form class="admin-form cms-form" method="post" enctype="multipart/form-data" data-content-editor>
<?php if (!$editorWritable): ?><fieldset disabled><legend>Read-only content</legend><?php endif; ?>
<input type="hidden" name="csrf_token" value="<?= e(xuverse_csrf_token()) ?>">
<input type="hidden" name="revision" value="<?= e($revision) ?>">
<input type="hidden" name="payload" value="<?= e(json_encode($entity,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)) ?>">
<script type="application/json" data-editor-config><?= json_encode(['collection'=>$collection,'existing'=>$itemKey !== 'new','data'=>$entity,'imageBase'=>xuverse_url(),'rowTemplates'=>['roles'=>['title'=>'','organisation'=>'','dates'=>''],'references'=>['citation'=>'','url'=>'','access'=>'']]],JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE) ?></script>
<div data-editor-fields></div>
<?php if ($collection === 'articles'): ?><div class="input-group"><label for="article-body">Article body</label><textarea id="article-body" name="body" rows="24" required><?= e($body) ?></textarea><p class="admin-help">Write the full article here. Paragraphs, ## headings, - lists, **bold** text and links are supported. Saving a published article updates its PDF from the same writing. Draft writing does not create a new public PDF.</p></div><?php endif; ?>
<?php if ($collection === 'site' || $collection === 'projects' || ($collection === 'media' && ($entity['type'] ?? '') !== 'video')): ?>
<div class="input-group"><label for="content-images"><?= $collection === 'media' && ($entity['type'] ?? '') === 'card series' ? 'Add images to this post' : 'Replace image' ?></label><input id="content-images" type="file" name="images[]" accept="image/jpeg,image/png,image/webp" <?= $collection === 'media' && ($entity['type'] ?? '') === 'card series' ? 'multiple' : '' ?>><p class="admin-help">JPG, PNG or WebP, up to 4 MB each. Gallery posts hold up to 10 images in total. Reorder or remove existing images above before saving. Server total upload limit: <?= e(ini_get('post_max_size')) ?>.</p></div>
<?php endif; ?>
<div class="admin-form-actions"><button class="btn" type="submit"<?= $editorWritable ? '' : ' disabled' ?>>Save changes</button><a class="btn secondary-btn" href="<?= e(xuverse_url('admin/dashboard.php')) ?>">Back to dashboard</a></div>
<noscript><p class="error">Enable JavaScript to use the structured content editor.</p></noscript>
<?php if (!$editorWritable): ?></fieldset><?php endif; ?>
</form>
<script src="<?= e(xuverse_url($editorAsset)) ?><?= defined('XUVERSE_RELEASE_ID') && XUVERSE_RELEASE_ID ? '&amp;' : '?' ?>v=<?= substr(hash_file('sha256','../../'.$editorAsset),0,12) ?>" defer></script>
<?php endif; ?>
</div></section>
<?php include '../../includes/footer.php'; ?>
