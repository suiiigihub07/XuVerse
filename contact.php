<?php

require_once 'includes/db.php';
require_once 'includes/functions.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    xuverse_session_start();
}

$settings = $conn->query(
    "SELECT * FROM settings
     LIMIT 1"
)->fetch_assoc();

$email = xuverse_setting($settings, 'contact_email', 'besuraj8459@gmail.com');
$configuredRecipient = trim((string)xuverse_config('CONTACT_TO', ''));
$recipient = filter_var($configuredRecipient, FILTER_VALIDATE_EMAIL) ? $configuredRecipient : $email;
$configuredFrom = trim((string)xuverse_config('CONTACT_FROM', ''));
$contactMailReady = filter_var($recipient, FILTER_VALIDATE_EMAIL) && filter_var($configuredFrom, FILTER_VALIDATE_EMAIL);
$subject = 'Hello from XuVerse';
$body = 'Hi ' . xuverse_person_name($conn) . ",\n\n";
$mailto = 'mailto:' . $email
    . '?subject=' . rawurlencode($subject)
    . '&body=' . rawurlencode($body);

$formStatus = '';
$formStatusType = '';
$formValues = [
    'name' => '',
    'reply_email' => '',
    'opportunity_type' => '',
    'message' => ''
];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    foreach(array_keys($formValues) as $field) {
        $formValues[$field] = trim((string)($_POST[$field] ?? ''));
    }

    $honeypot = trim((string)($_POST['website'] ?? ''));
    $lastInquiry = (int)($_SESSION['last_contact_submission'] ?? 0);
    $errors = [];

    if (!xuverse_verify_csrf()) {
        $errors[] = 'Your session expired. Refresh the page and try again.';
    }

    if ($honeypot !== '') {
        $formStatus = 'Thanks. Your message has been received.';
        $formStatusType = 'success';
    } else {
        if (strlen($formValues['name']) < 2 || strlen($formValues['name']) > 80) {
            $errors[] = 'Enter your name using 2 to 80 characters.';
        }

        if (!filter_var($formValues['reply_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Enter a valid email address.';
        }

        $allowedTypes = ['Software project', 'Media or photography', 'Campus or community', 'Employment opportunity', 'Something else'];
        if (!in_array($formValues['opportunity_type'], $allowedTypes, true)) {
            $errors[] = 'Choose the type of conversation.';
        }

        if (strlen($formValues['message']) < 20 || strlen($formValues['message']) > 2000) {
            $errors[] = 'Write a message between 20 and 2,000 characters.';
        }

        if ($lastInquiry > 0 && time() - $lastInquiry < 30) {
            $errors[] = 'Please wait briefly before sending another message.';
        }

        if (!$errors && !$contactMailReady) {
            $errors[] = 'Secure email delivery is not configured yet. Please use the email button instead.';
        }

        if (!$errors) {
            $safeName = str_replace(["\r", "\n"], ' ', $formValues['name']);
            $safeReplyTo = str_replace(["\r", "\n"], '', $formValues['reply_email']);
            $mailSubject = '[XuVerse] ' . $formValues['opportunity_type'] . ' from ' . $safeName;
            $mailBody = "Name: {$safeName}\n"
                . "Email: {$safeReplyTo}\n"
                . "Type: {$formValues['opportunity_type']}\n\n"
                . $formValues['message'];
            $headers = [
                'Content-Type: text/plain; charset=UTF-8',
                'From: ' . $configuredFrom,
                'Reply-To: ' . $safeReplyTo
            ];

            if (@mail($recipient, $mailSubject, $mailBody, implode("\r\n", $headers))) {
                $_SESSION['last_contact_submission'] = time();
                $formStatus = 'Message sent. I will reply by email.';
                $formStatusType = 'success';
                $formValues = array_fill_keys(array_keys($formValues), '');
            } else {
                $formStatus = 'The server could not send the message. Please use the email button instead.';
                $formStatusType = 'error';
            }
        } elseif($formStatus === '') {
            $formStatus = implode(' ', $errors);
            $formStatusType = 'error';
        }
    }
}

$socials = [
    ['label' => 'GitHub', 'url' => $settings['github_url'] ?? '', 'meta' => 'developer profile'],
    ['label' => 'LinkedIn', 'url' => $settings['linkedin_url'] ?? '', 'meta' => 'professional profile'],
    ['label' => 'YouTube', 'url' => $settings['youtube_url'] ?? '', 'meta' => 'video work'],
    ['label' => 'Instagram', 'url' => $settings['instagram_url'] ?? '', 'meta' => 'photos / life'],
    ['label' => 'TikTok', 'url' => $settings['tiktok_url'] ?? '', 'meta' => 'short-form video'],
    ['label' => 'Facebook', 'url' => $settings['facebook_url'] ?? '', 'meta' => 'community profile'],
];

$pageTitle = 'Connect | ' . xuverse_person_name($conn);
$pageDescription = xuverse_setting($settings, 'contact_intro', 'Email, socials and current traces.');
$canonicalUrl = xuverse_base_url() . '/contact';

include 'includes/header.php';
include 'includes/navbar.php';

?>

<section class="page-hero contact-hero">
<div class="container">
<p class="page-kicker">Connect</p>
<h1>Let's connect</h1>
<p><?= e(xuverse_setting($settings, 'contact_intro', 'Get in touch about ideas, projects and collaborations.')) ?></p>
</div>
</section>

<section class="section connect-section">
<div class="container connect-layout">

<div class="connect-email reveal">
<div>
<p class="page-kicker"><?= e(xuverse_setting($settings, 'location_text', 'Email')) ?></p>
<h2><?= e(xuverse_setting($settings, 'cta_title', 'Send a note')) ?></h2>
<p><?= e(xuverse_setting($settings, 'cta_text', 'I would love to hear from you.')) ?></p>
</div>
<a
href="<?= e($mailto) ?>"
class="email-address"
data-mailto="<?= e($mailto) ?>"
data-email="<?= e($email) ?>">
<?= e($email) ?>
</a>

<div class="connect-actions">
<a
href="<?= e($mailto) ?>"
class="vx-btn primary email-action"
data-mailto="<?= e($mailto) ?>"
data-email="<?= e($email) ?>">
Start a conversation
</a>
<button type="button" class="vx-btn" data-copy-email="<?= e($email) ?>" hidden>Copy email</button>
</div>

<p class="mail-status" aria-live="polite"></p>
</div>

<div class="social-orbit reveal">
<?php foreach($socials as $social): ?>
<?php if(!empty($social['url'])): ?>
<a
href="<?= e($social['url']) ?>"
target="_blank"
rel="noopener noreferrer"
class="social-card magnetic-card">
<span><?= e($social['meta']) ?></span>
<strong><?= e($social['label']) ?></strong>
</a>
<?php endif; ?>
<?php endforeach; ?>
</div>

<?php if ($contactMailReady): ?>
<section class="inquiry-panel reveal" aria-labelledby="inquiry-title">
<div class="inquiry-intro">
<p class="page-kicker">Project inquiry</p>
<h2 id="inquiry-title">Tell me what you have in mind.</h2>
<p>Use the form for a structured introduction, or email directly if that is easier. Your message is sent only to the portfolio email address.</p>
</div>

<?php if($formStatus !== ''): ?>
<p class="form-status <?= $formStatusType === 'success' ? 'is-success' : 'is-error' ?>" role="<?= $formStatusType === 'success' ? 'status' : 'alert' ?>">
<?= e($formStatus) ?>
</p>
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
<?php foreach(['Software project', 'Media or photography', 'Campus or community', 'Employment opportunity', 'Something else'] as $type): ?>
<option value="<?= e($type) ?>" <?= $formValues['opportunity_type'] === $type ? 'selected' : '' ?>><?= e($type) ?></option>
<?php endforeach; ?>
</select>
</div>
<div class="form-field form-field-wide">
<label for="contact-message">Message</label>
<textarea id="contact-message" name="message" rows="6" minlength="20" maxlength="2000" required><?= e($formValues['message']) ?></textarea>
<small>20–2,000 characters. Do not include passwords or sensitive information.</small>
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

<?php if(empty($_SESSION['user_id'])): ?>
<div class="container contact-admin-access">
<a href="<?= e(xuverse_url('login')) ?>">Admin login</a>
</div>
<?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>
