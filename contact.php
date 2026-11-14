<?php
require_once __DIR__ . '/includes/content.php';

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
        $value = $_POST[$field] ?? '';
        $formValues[$field] = is_string($value) ? trim($value) : '';
    }
    $errors = [];
    if (!xuverse_verify_csrf()) {
        $errors[] = 'Your session expired. Refresh the page and try again.';
    }
    $honeypot = $_POST['website'] ?? '';
    if (!is_string($honeypot) || trim($honeypot) !== '') {
        $errors[] = 'The message could not be sent. Please use the email link instead.';
    }
    $nameLength = mb_strlen($formValues['name']);
    if ($nameLength < 2 || $nameLength > 80) {
        $errors[] = 'Enter your name using 2 to 80 characters.';
    }
    if (!filter_var($formValues['reply_email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }
    if (!in_array($formValues['opportunity_type'], $allowedTypes, true)) {
        $errors[] = 'Choose the type of conversation.';
    }
    $messageLength = mb_strlen($formValues['message']);
    if ($messageLength < 20 || $messageLength > 2000) {
        $errors[] = 'Write a message between 20 and 2,000 characters.';
    }
    $lastInquiry = (int)($_SESSION['last_contact_submission'] ?? 0);
    if ($lastInquiry > 0 && time() - $lastInquiry < 30) {
        $errors[] = 'Please wait briefly before sending another message.';
    }
    if (!$contactMailReady) {
        $errors[] = 'Please use the email link to send your message.';
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
            $formStatus = 'The server could not send the message. Please use the email link instead.';
            $formStatusType = 'error';
        }
    } else {
        $formStatus = implode(' ', $errors);
        $formStatusType = 'error';
    }
}

$socials = [
    ['label' => 'GitHub', 'url' => $links['github']],
    ['label' => 'LinkedIn', 'url' => $links['linkedin']],
    ['label' => 'YouTube', 'url' => $links['youtube']],
    ['label' => 'Instagram', 'url' => $links['instagram']],
    ['label' => 'TikTok', 'url' => $links['tiktok']],
    ['label' => 'Facebook', 'url' => $links['facebook']],
];

$contactIntro = $copy['contact_intro'];
$socials = array_filter($socials, fn($social) => trim($social['url']) !== '');

xuverse_public_start('Connect', $contactIntro, 'contact');
?>

<section class="personal-page personal-contact xu-tone-dark" aria-labelledby="contact-title">
    <div class="container">
        <header class="personal-contact-header reveal">
            <h1 class="personal-title" id="contact-title"><?= e($copy['contact_heading']) ?></h1>
            <p class="personal-lead"><?= e($contactIntro) ?></p>
        </header>

        <div class="personal-contact-layout<?= $contactMailReady ? ' has-inquiry' : '' ?>">
            <section class="personal-contact-email reveal" aria-labelledby="email-title">
                <h2 id="email-title">Email</h2>
                <p class="personal-contact-address"><?= e($email) ?></p>
                <div class="personal-actions">
                    <a href="<?= e($mailto) ?>" class="xu-primary email-action"
                        data-mailto="<?= e($mailto) ?>" data-email="<?= e($email) ?>">Email me</a>
                    <button type="button" class="xu-link xu-flat-action" data-copy-email="<?= e($email) ?>" hidden>Copy email</button>
                </div>
                <p class="mail-status" role="status"></p>
                <?php if ($formStatus !== '' && !$contactMailReady): ?>
                <p class="form-status is-error" role="alert"><?= e($formStatus) ?></p>
                <?php endif; ?>
            </section>

            <?php if ($socials): ?>
            <nav class="personal-socials reveal" aria-label="Contact social links">
                <ul>
                <?php foreach ($socials as $social): ?>
                    <li><a href="<?= e($social['url']) ?>" target="_blank" rel="noopener noreferrer"><span><?= e($social['label']) ?></span></a></li>
                <?php endforeach; ?>
                </ul>
            </nav>
            <?php endif; ?>

            <?php if ($contactMailReady): ?>
            <section class="personal-contact-inquiry reveal" aria-labelledby="inquiry-title">
                <h2 id="inquiry-title">Send a message</h2>
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
                        <textarea id="contact-message" name="message" rows="6" minlength="20" maxlength="2000" aria-describedby="contact-message-length" required><?= e($formValues['message']) ?></textarea>
                        <small id="contact-message-length">20–2,000 characters.</small>
                    </div>
                    <div class="form-trap" aria-hidden="true">
                        <label for="contact-website">Website</label>
                        <input id="contact-website" name="website" type="text" tabindex="-1" autocomplete="off">
                    </div>
                    <div class="form-field-wide">
                        <button type="submit" class="xu-primary">Send message</button>
                    </div>
                </form>
            </section>
            <?php endif; ?>
        </div>

        <?php if (empty($_SESSION['user_id'])): ?>
        <p class="personal-admin-access"><a href="<?= e(xuverse_url('login')) ?>">Admin login</a></p>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
