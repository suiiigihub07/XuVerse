<?php

require_once 'includes/functions.php';

xuverse_session_start();

require_once __DIR__ . '/includes/login-throttle.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!xuverse_verify_csrf()) {
        http_response_code(403);
        exit('Refresh the sign-in page and try again.');
    }
    if (!xuverse_allow_login_attempt()) {
        http_response_code(429);
        $error = 'Too many sign-in attempts. Please wait 15 minutes and try again.';
    } else {

        require_once 'includes/db.php';

    $email = is_string($_POST['email'] ?? null) ? trim($_POST['email']) : '';
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

    $stmt = $conn->prepare(
        "SELECT * FROM users WHERE email=? AND role='admin'"
    );

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {

        if (
            password_verify(
                $password,
                $user['password']
            )
        ) {

            session_regenerate_id(true);

            $_SESSION['user_id'] =
                $user['id'];

            $_SESSION['name'] =
                $user['full_name'];

            $_SESSION['role'] =
                $user['role'];

            header(
                'Location: ' . xuverse_url('admin/dashboard.php')
            );

            exit;
        }
    }

        $error =
            "Invalid email or password.";
    }
}

$pageTitle = 'Login - XuVerse';
$pageDescription = 'Secure XuVerse admin login.';
$canonicalUrl = xuverse_absolute_url('login');
$robots = xuverse_noindex();

include 'includes/header.php';
include 'includes/navbar.php';

?>

<section class="login-page">

<div class="login-card">

<h1>Sign in</h1>

<p>
Manage XuVerse.
</p>

<?php if(!empty($error)): ?>

<div class="error">
    <?= htmlspecialchars($error) ?>
</div>

<?php endif; ?>

<form method="POST">
<input type="hidden" name="csrf_token" value="<?= e(xuverse_csrf_token()) ?>">

<div class="input-group">

<label for="login-email">Email</label>

<input
id="login-email"
type="email"
name="email"
autocomplete="username"
required>

</div>

<div class="input-group">

<label for="login-password">Password</label>

<input
id="login-password"
type="password"
name="password"
autocomplete="current-password"
required>

</div>

<button
type="submit"
class="btn">

Login

</button>

</form>

</div>

</section>

<?php include 'includes/footer.php'; ?>
