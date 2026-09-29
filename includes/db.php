<?php

require_once __DIR__ . '/functions.php';

if (xuverse_is_production() && session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.cookie_secure', '1');
}

try {
    $production = xuverse_is_production();
    $dbHost = xuverse_config('DB_HOST', 'localhost');
    $dbUser = xuverse_config('DB_USER', $production ? null : 'root');
    $dbPassword = xuverse_config('DB_PASSWORD', $production ? null : '');
    $dbName = xuverse_config('DB_NAME', $production ? null : 'xuverse');
    if ($production && (!$dbUser || !$dbName || $dbPassword === null || $dbPassword === '')) {
        throw new RuntimeException('Production database configuration is incomplete.');
    }
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $conn = new mysqli($dbHost, $dbUser, $dbPassword, $dbName);
    $conn->set_charset('utf8mb4');
} catch (Throwable $error) {
    // Do not log connection arguments, SQL data or credentials.
    error_log('XuVerse database unavailable (' . get_class($error) . ').');
    http_response_code(503);
    exit('XuVerse is temporarily unavailable. Please try again shortly.');
}

// Revalidate privileges on each protected request, including an existing session.
if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['user_id'])) {
    $authorization = $conn->prepare('SELECT role FROM users WHERE id=?');
    $authorization->bind_param('i', $_SESSION['user_id']);
    $authorization->execute();
    $role = $authorization->get_result()->fetch_assoc()['role'] ?? '';
    if ($role !== 'admin') {
        $_SESSION = [];
        http_response_code(403);
        exit('Administrator access is required.');
    }
}

if (
    session_status() === PHP_SESSION_ACTIVE
    && !empty($_SESSION['user_id'])
    && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'
    && !xuverse_verify_csrf()
) {
    http_response_code(403);
    exit('Your session could not be verified. Refresh the page and try again.');
}
?>
