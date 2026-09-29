<?php

// Server-side limits cannot be reset by deleting the browser's session cookie.
function xuverse_allow_login_attempt()
{
    $directory = dirname(__DIR__) . '/tmp/login-attempts';
    if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
        return false;
    }
    $key = hash('sha256', (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    $file = fopen($directory . '/' . $key . '.json', 'c+');
    if (!$file) { return false; }
    if (!flock($file, LOCK_EX)) { fclose($file); return false; }
    $attempts = json_decode(stream_get_contents($file), true) ?: [];
    $attempts = array_values(array_filter($attempts, static fn($time) => $time > time() - 900));
    $allowed = count($attempts) < 5;
    if ($allowed) { $attempts[] = time(); }
    rewind($file);
    ftruncate($file, 0);
    fwrite($file, json_encode($attempts));
    fflush($file);
    flock($file, LOCK_UN);
    fclose($file);
    return $allowed;
}
