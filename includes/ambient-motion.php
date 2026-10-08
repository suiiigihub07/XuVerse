<?php
// Kept separate so theme restoration and this motion layer can evolve independently.
foreach (['css' => 'ambient-motion.css', 'js' => 'ambient-motion.js'] as $type => $name) {
    $path = 'assets/' . $type . '/' . $name;
    $separator = defined('XUVERSE_RELEASE_ID') && XUVERSE_RELEASE_ID ? '&' : '?';
    $url = xuverse_url($path) . $separator . 'v=' . substr(hash_file('sha256', dirname(__DIR__) . '/' . $path), 0, 12);
    if ($type === 'css') {
        echo '<link rel="stylesheet" href="' . e($url) . '">' . "\n";
    } else {
        echo '<script src="' . e($url) . '" defer></script>' . "\n";
    }
}
