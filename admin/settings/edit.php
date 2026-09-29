<?php

require_once '../../includes/functions.php';
xuverse_session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once '../../includes/db.php';
require_once '../../includes/functions.php';

$id = (int)($_GET['id'] ?? 1);
$columns = [];
$fieldLimits = [];
$columnResult = $conn->query("SHOW COLUMNS FROM settings");

while ($column = $columnResult->fetch_assoc()) {
    $columns[] = $column['Field'];
    if (preg_match('/^varchar\((\d+)\)/i', $column['Type'], $matches)) {
        $fieldLimits[$column['Field']] = (int)$matches[1];
    }
}

$fields = [
    'site_title',
    'meta_description',
    'resume_headline',
    'resume_summary',
    'nav_cta_label',
    'location_text',
    'availability_text',
    'hero_kicker',
    'hero_title',
    'hero_subtitle',
    'hero_description',
    'hero_primary_cta',
    'hero_secondary_cta',
    'current_focus',
    'media_intro',
    'contact_intro',
    'cta_title',
    'cta_text',
    'footer_tagline',
    'footer_text',
    'contact_email',
    'github_url',
    'linkedin_url',
    'instagram_url',
    'tiktok_url',
    'facebook_url',
    'youtube_url'
];

$editableFields = array_values(array_filter($fields, function ($field) use ($columns) {
    return in_array($field, $columns, true);
}));

$stmt = $conn->prepare(
    "SELECT * FROM settings
     WHERE id=?"
);

$stmt->bind_param("i", $id);
$stmt->execute();
$settings = $stmt->get_result()->fetch_assoc();

if (!$settings) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values = [];
    $assignments = [];
    $types = '';

    foreach ($editableFields as $field) {
        $value = trim($_POST[$field] ?? ($settings[$field] ?? ''));
        if (isset($fieldLimits[$field]) && mb_strlen($value) > $fieldLimits[$field]) {
            $error = ucwords(str_replace('_', ' ', $field)) . ' must be ' . $fieldLimits[$field] . ' characters or fewer.';
        }
        if (in_array($field, ['site_title', 'hero_title', 'hero_subtitle', 'hero_description'], true) && $value === '') {
            $error = 'Site title, display name, homepage headline and description are required.';
        }
        if ($field === 'contact_email' && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $error = 'Enter a valid contact email address.';
        }
        if (substr($field, -4) === '_url' && $value !== '' && (!filter_var($value, FILTER_VALIDATE_URL) || !in_array(strtolower((string)parse_url($value, PHP_URL_SCHEME)), ['http', 'https'], true))) {
            $error = 'Social links must be valid HTTP or HTTPS URLs, or left blank.';
        }
        $values[] = $value;
        $assignments[] = "{$field}=?";
        $types .= 's';
    }

    $values[] = $id;
    $types .= 'i';

    if ($error === '') {
        $sql = "UPDATE settings SET " . implode(', ', $assignments) . " WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$values);
        $stmt->execute();

        header("Location: index.php");
        exit;
    }
}

function settings_field($settings, $field)
{
    return e($_POST[$field] ?? ($settings[$field] ?? ''));
}

require_once '../../includes/functions.php';
$pageTitle = $pageTitle ?? 'Edit Site Settings - XuVerse';
$robots = xuverse_noindex();
header('Cache-Control: no-store');
include '../../includes/header.php';
include '../../includes/navbar.php';

?>

<section class="section admin-page">
<div class="container">
<a class="text-link admin-back-link" href="index.php">Back to settings</a>

<div class="section-heading">
<p class="page-kicker">CMS</p>
<h1>Edit Site Settings</h1>
<p>These fields control the public website copy, calls to action, social links and footer.</p>
</div>

<form method="POST" class="admin-form settings-form">
<input type="hidden" name="csrf_token" value="<?= e(xuverse_csrf_token()) ?>">
<?php if ($error !== ''): ?><p class="error" role="alert"><?= e($error) ?></p><?php endif; ?>

<div class="form-panel">
<h2>Identity</h2>

<div class="input-group">
<label for="field-site-title-1">Site Title</label>
<input id="field-site-title-1" type="text" name="site_title" value="<?= settings_field($settings, 'site_title') ?>" required>
</div>

<div class="input-group">
<label for="field-meta-description-2">Meta Description</label>
<textarea id="field-meta-description-2" name="meta_description" rows="3"><?= settings_field($settings, 'meta_description') ?></textarea>
</div>

<div class="input-grid">
<div class="input-group">
<label for="field-nav-cta-label-3">Navigation CTA Label</label>
<input id="field-nav-cta-label-3" type="text" name="nav_cta_label" value="<?= settings_field($settings, 'nav_cta_label') ?>">
</div>

<div class="input-group">
<label for="field-location-text-4">Location</label>
<input id="field-location-text-4" type="text" name="location_text" value="<?= settings_field($settings, 'location_text') ?>">
</div>
</div>

<div class="input-group">
<label for="field-availability-text-5">Availability</label>
<input id="field-availability-text-5" type="text" name="availability_text" value="<?= settings_field($settings, 'availability_text') ?>">
</div>
</div>

<div class="form-panel">
<h2>Homepage Hero</h2>

<div class="input-group">
<label for="field-hero-kicker-6">Hero Kicker</label>
<input id="field-hero-kicker-6" type="text" name="hero_kicker" value="<?= settings_field($settings, 'hero_kicker') ?>">
</div>

<div class="input-group">
<label for="field-hero-title-7">Display name</label>
<input id="field-hero-title-7" type="text" name="hero_title" value="<?= settings_field($settings, 'hero_title') ?>" required>
</div>

<div class="input-group">
<label for="field-hero-subtitle-8">Homepage headline</label>
<input id="field-hero-subtitle-8" type="text" name="hero_subtitle" value="<?= settings_field($settings, 'hero_subtitle') ?>" required>
</div>

<div class="input-group">
<label for="field-hero-description-9">Hero Description</label>
<textarea id="field-hero-description-9" name="hero_description" rows="5" required><?= settings_field($settings, 'hero_description') ?></textarea>
</div>

<div class="input-grid">
<div class="input-group">
<label for="field-hero-primary-cta-10">Primary CTA</label>
<input id="field-hero-primary-cta-10" type="text" name="hero_primary_cta" value="<?= settings_field($settings, 'hero_primary_cta') ?>">
</div>

<div class="input-group">
<label for="field-hero-secondary-cta-11">Secondary CTA</label>
<input id="field-hero-secondary-cta-11" type="text" name="hero_secondary_cta" value="<?= settings_field($settings, 'hero_secondary_cta') ?>">
</div>
</div>
</div>

<div class="form-panel">
<h2>Resume</h2>
<div class="input-group">
<label for="field-resume-headline-12">Resume headline</label>
<input id="field-resume-headline-12" type="text" name="resume_headline" maxlength="255" value="<?= settings_field($settings, 'resume_headline') ?>">
</div>
<div class="input-group">
<label for="field-resume-summary-13">Resume summary</label>
<textarea id="field-resume-summary-13" name="resume_summary" rows="5"><?= settings_field($settings, 'resume_summary') ?></textarea>
</div>
</div>

<div class="form-panel">
<h2>Section Copy</h2>

<div class="input-group">
<label for="field-current-focus-14">Current Focus</label>
<textarea id="field-current-focus-14" name="current_focus" rows="4"><?= settings_field($settings, 'current_focus') ?></textarea>
</div>

<div class="input-group">
<label for="field-media-intro-15">Media Intro</label>
<textarea id="field-media-intro-15" name="media_intro" rows="3"><?= settings_field($settings, 'media_intro') ?></textarea>
</div>

</div>

<div class="form-panel">
<h2>Contact and Footer</h2>

<div class="input-group">
<label for="field-contact-intro">Contact intro</label>
<textarea id="field-contact-intro" name="contact_intro" rows="3"><?= settings_field($settings, 'contact_intro') ?></textarea>
</div>

<div class="input-group">
<label for="field-cta-title-16">CTA Title</label>
<input id="field-cta-title-16" type="text" name="cta_title" value="<?= settings_field($settings, 'cta_title') ?>">
</div>

<div class="input-group">
<label for="field-cta-text-17">CTA Text</label>
<textarea id="field-cta-text-17" name="cta_text" rows="3"><?= settings_field($settings, 'cta_text') ?></textarea>
</div>

<div class="input-group">
<label for="field-footer-tagline-18">Footer Tagline</label>
<input id="field-footer-tagline-18" type="text" name="footer_tagline" value="<?= settings_field($settings, 'footer_tagline') ?>">
</div>

<div class="input-group">
<label for="field-footer-text-19">Footer Text</label>
<textarea id="field-footer-text-19" name="footer_text" rows="4"><?= settings_field($settings, 'footer_text') ?></textarea>
</div>

<div class="input-group">
<label for="field-contact-email-20">Contact Email</label>
<input id="field-contact-email-20" type="email" name="contact_email" value="<?= settings_field($settings, 'contact_email') ?>">
</div>

<div class="input-group">
<label for="field-github-url-21">GitHub URL</label>
<input id="field-github-url-21" type="url" name="github_url" value="<?= settings_field($settings, 'github_url') ?>">
</div>

<div class="input-group">
<label for="field-linkedin-url-22">LinkedIn URL</label>
<input id="field-linkedin-url-22" type="url" name="linkedin_url" value="<?= settings_field($settings, 'linkedin_url') ?>">
</div>

<div class="input-group">
<label for="field-instagram-url-23">Instagram URL</label>
<input id="field-instagram-url-23" type="url" name="instagram_url" value="<?= settings_field($settings, 'instagram_url') ?>">
</div>

<?php if(in_array('tiktok_url', $editableFields, true)): ?>
<div class="input-group">
<label for="field-tiktok-url-24">TikTok URL</label>
<input id="field-tiktok-url-24" type="url" name="tiktok_url" value="<?= settings_field($settings, 'tiktok_url') ?>">
</div>
<?php endif; ?>

<?php if(in_array('facebook_url', $editableFields, true)): ?>
<div class="input-group">
<label for="field-facebook-url-25">Facebook URL</label>
<input id="field-facebook-url-25" type="url" name="facebook_url" value="<?= settings_field($settings, 'facebook_url') ?>">
</div>
<?php endif; ?>

<div class="input-group">
<label for="field-youtube-url-26">YouTube URL</label>
<input id="field-youtube-url-26" type="url" name="youtube_url" value="<?= settings_field($settings, 'youtube_url') ?>">
</div>
</div>

<div class="admin-form-actions">
<button class="btn" type="submit">Save Settings</button>
<a href="index.php" class="btn secondary-btn">Cancel</a>
</div>
</form>

</div>
</section>

<?php include '../../includes/footer.php'; ?>
