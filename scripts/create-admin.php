<?php
// Receive JSON on stdin so passwords never appear in command arguments/history.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../includes/db.php';
if ($conn->query("SELECT id FROM users WHERE role='admin' LIMIT 1")->num_rows) {
    exit("An administrator already exists. Use the CMS password page.\n");
}
$input = json_decode(stream_get_contents(STDIN), true);
$name = $input['name'] ?? '';
$email = $input['email'] ?? '';
$password = $input['password'] ?? '';
if (!is_string($name) || trim($name) === '' || strlen($name) > 100
    || !is_string($email) || strlen($email) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)
    || !is_string($password) || strlen($password) < 14 || strlen($password) > 72) {
    fwrite(STDERR, "Provide name, email and a unique password of 14–72 bytes as JSON on stdin.\n");
    exit(1);
}
$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, 'admin')");
$stmt->bind_param('sss', $name, $email, $hash);
$stmt->execute();
echo "Administrator created. Sign in at /login on this installation.\n";
