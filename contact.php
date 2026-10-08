<?php
require_once 'includes/content.php';

$copy = xuverse_content('copy');
$links = xuverse_content('links');
$email = substr($links['email'], 7);
$configuredRecipient = trim((string)xuverse_config('CONTACT_TO', ''));
$recipient = filter_var($configuredRecipient, FILTER_VALIDATE_EMAIL) ? $configuredRecipient : $email;
$configuredFrom = trim((string)xuverse_config('CONTACT_FROM', ''));
$contactMailReady = filter_var($recipient, FILTER_VALIDATE_EMAIL) && filter_var($configuredFrom, FILTER_VALIDATE_EMAIL);
$mailto = $links['email'] . '?subject=' . rawurlencode('Hello from XuVerse')
    . '&body=' . rawurlencode('Hi ' . $copy['name'] . ",\n\n");
$allowedTypes = ['Software project', 'Media or photography', 'Campus or community', 'Employment opportunity', 'Something else'];
$formValues = ['name' => '', 'reply_email' => '', 'opportunity_type' => '', 'message' => ''];
$formStatus = '';
$formStatusType = '';

if ($contactMailReady || ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    xuverse_session_start();
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    foreach (array_keys($formValues) as $field) {
        $formValues[$field] = trim((string)($_POST[$field] ?? ''));
    }
    $errors = [];
    if (!xuverse_verify_csrf()) {
        $errors[] = 'Your session expired. Refresh the page and try again.';
    }
    if (trim((string)($_POST['website'] ?? '')) !== '') {
        $errors[] = 'The message could not be sent. Please use the email button instead.';
    }
    if (strlen($formValues['name']) < 2 || strlen($formValues['name']) > 80) {
        $errors[] = 'Enter your name using 2 to 80 characters.';
    }
    if (!filter_var($formValues['reply_email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }
    if (!in_array($formValues['opportunity_type'], $allowedTypes, true)) {
        $errors[] = 'Choose the type of conversation.';
    }
    if (strlen($formValues['message']) < 20 || strlen($formValues['message']) > 2000) {
        $errors[] = 'Write a message between 20 and 2,000 characters.';
    }
    $lastInquiry = (int)($_SESSION['last_contact_submission'] ?? 0);
    if ($lastInquiry > 0 && time() - $lastInquiry < 30) {
        $errors[] = 'Please wait briefly before sending another message.';
    }
    if (!$contactMailReady) {
        $errors[] = 'Please use the email button to send your message.';
    }

    if (!$errors) {
        $safeName = str_replace(["\r", "\n"], ' ', $formValues['name']);
        $safeReplyTo = str_replace(["\r", "\n"], '', $formValues['reply_email']);
        $subject = '[XuVerse] ' . $formValues['opportunity_type'] . ' from ' . $safeName;
        $message = "Name: {$safeName}\nEmail: {$safeReplyTo}\n"
            . 'Type: ' . $formValues['opportunity_type'] . "\n\n" . $formValues['message'];
        $headers = ['Content-Type: text/plain; charset=UTF-8', 'From: ' . $configuredFrom, 'Reply-To: ' . $safeReplyTo];
        if (@mail($recipient, $subject, $message, implode("\r\n", $headers))) {
            $_SESSION['last_contact_submission'] = time();
            $formStatus = 'Message sent. I will reply by email.';
            $formStatusType = 'success';
            $formValues = array_fill_keys(array_keys($formValues), '');
        } else {
            $formStatus = 'The server could not send the message. Please use the email button instead.';
            $formStatusType = 'error';
        }
    } else {
        $formStatus = implode(' ', $errors);
        $formStatusType = 'error';
    }
}

$socials = [
    ['label' => 'GitHub', 'url' => $links['github'], 'meta' => 'developer profile'],
    ['label' => 'LinkedIn', 'url' => $links['linkedin'], 'meta' => 'professional profile'],
    ['label' => 'YouTube', 'url' => $links['youtube'], 'meta' => 'video work'],
    ['label' => 'Instagram', 'url' => $links['instagram'], 'meta' => 'photos / life'],
    ['label' => 'TikTok', 'url' => $links['tiktok'], 'meta' => 'short-form video'],
    ['label' => 'Facebook', 'url' => $links['facebook'], 'meta' => 'community profile'],
];

xuverse_public_start('Connect', $copy['contact_intro'], 'contact');
?>

<section class="page-hero contact-hero">
<div class="container">
<p class="page-kicker">Connect</p>
<h1><?= e($copy['contact_heading']) ?></h1>
<p><?= e($copy['contact_intro']) ?></p>
</div>
</section>

<section class="section connect-section">
<div class="container connect-layout">
<div class="connect-email reveal">
<div>
<p class="page-kicker"><?= e($copy['location']) ?></p>
<h2>Start a conversation</h2>
<p><?= e($copy['availability']) ?></p>
</div>
<a href="<?= e($mailto) ?>" class="email-address" data-email="<?= e($email) ?>">
<?= e($email) ?>
</a>
<div class="connect-actions">
<a href="<?= e($mailto) ?>" class="vx-btn primary email-action"
   data-mailto="<?= e($mailto) ?>" data-email="<?= e($email) ?>">Email me</a>
<button type="button" class="vx-btn" data-copy-email="<?= e($email) ?>" hidden>Copy email</button>
</div>
<p class="mail-status" role="status" aria-live="polite"></p>
<?php if ($formStatus !== '' && !$contactMailReady): ?>
<p class="form-status is-error" role="alert"><?= e($formStatus) ?></p>
<?php endif; ?>
</div>

<nav class="social-orbit" aria-label="Connect on social media">
<?php foreach ($socials as $social): ?>
<a href="<?= e($social['url']) ?>" target="_blank" rel="noopener noreferrer"
   class="social-card magnetic-card reveal">
<span><?= e($social['meta']) ?></span>
<strong><?= e($social['label']) ?></strong>
</a>
<?php endforeach; ?>
</nav>

<?php if ($contactMailReady): ?>
<section class="inquiry-panel reveal" aria-labelledby="inquiry-title">
<div class="inquiry-intro">
<p class="page-kicker">Project inquiry</p>
<h2 id="inquiry-title">Tell me what you have in mind.</h2>
<p>Use the form for an introduction, or email directly if that is easier.</p>
</div>
<?php if ($formStatus !== ''): ?>
<p class="form-status <?= $formStatusType === 'success' ? 'is-success' : 'is-error' ?>"
   role="<?= $formStatusType === 'success' ? 'status' : 'alert' ?>"><?= e($formStatus) ?></p>
<?php endif; ?>
<form method="post" action="<?= e(xuverse_url('contact')) ?>#inquiry-title" class="inquiry-form">
<input type="hidden" name="csrf_token" value="<?= e(xuverse_csrf_token()) ?>">
<div class="form-field">
<label for="contact-name">Name</label>
<input id="contact-name" name="name" type="text" autocomplete="name" minlength="2" maxlength="80" required value="<?= e($formValues['name']) ?>">
</div>
<div class="form-field">
<label for="contact-email">Email</label>
<input id="contact-email" name="reply_email" type="email" autocomplete="email" maxlength="160" required value="<?= e($formValues['reply_email']) ?>">
</div>
<div class="form-field form-field-wide">
<label for="opportunity-type">What is this about?</label>
<select id="opportunity-type" name="opportunity_type" required>
<option value="">Choose one</option>
<?php foreach ($allowedTypes as $type): ?>
<option value="<?= e($type) ?>" <?= $formValues['opportunity_type'] === $type ? 'selected' : '' ?>><?= e($type) ?></option>
<?php endforeach; ?>
</select>
</div>
<div class="form-field form-field-wide">
<label for="contact-message">Message</label>
<textarea id="contact-message" name="message" rows="6" minlength="20" maxlength="2000" required><?= e($formValues['message']) ?></textarea>
<small>20–2,000 characters.</small>
</div>
<div class="form-trap" aria-hidden="true">
<label for="contact-website">Website</label>
<input id="contact-website" name="website" type="text" tabindex="-1" autocomplete="off">
</div>
<div class="form-field-wide inquiry-submit">
<button type="submit" class="vx-btn primary">Send inquiry</button>
<p>Prefer email? <a href="<?= e($mailto) ?>">Write to <?= e($email) ?></a>.</p>
</div>
</form>
</section>
<?php endif; ?>
</div>

<?php if (empty($_SESSION['user_id'])): ?>
<div class="container contact-admin-access">
<a href="<?= e(xuverse_url('login')) ?>">Admin login</a>
</div>
<?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>
