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

// Public editorial records have one source; legacy rows stay private and untouched.
$script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
if (preg_match('#/admin/(articles|projects|media|experience|education|skills|highlights)/#', $script)
    || preg_match('#/admin/settings/(index|edit|create|delete)\.php$#', $script)) {
    if (!empty($_SESSION['user_id'])) {
        require_once __DIR__ . '/content.php';
        $pageTitle = 'Versioned public content | XuVerse';
        $robots = xuverse_noindex();
        header('Cache-Control: no-store');
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') { http_response_code(405); }
        include __DIR__ . '/header.php';
        include __DIR__ . '/navbar.php';
        echo '<section class="section container"><h1>Public content is versioned</h1><p>Edit the JSON and Markdown files in content/ in the local checkout, then run Publish. This keeps the local preview, GitHub and hosted site on the same revision.</p><p>Historical database records are retained privately. These public-content fields are read-only in this CMS.</p><p>Account, password, avatar and music tools remain available.</p></section>';
        echo '<p class="container"><a href="' . e(xuverse_url('admin/dashboard.php')) . '">Back to dashboard</a></p>';
        include __DIR__ . '/footer.php';
        exit;
    }
}
