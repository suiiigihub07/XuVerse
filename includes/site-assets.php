<?php

(static function ($admin) {
    $styles = ['media-gallery.css', 'rounded.css', 'refinements.css'];
    if (!$admin) {
        array_push($styles, 'editorial.css', 'editorial-work.css', 'editorial-pages.css', 'editorial-resume.css');
    }
    $assetUrl = static function ($path) {
        $separator = defined('XUVERSE_RELEASE_ID') && XUVERSE_RELEASE_ID ? '&' : '?';
        return xuverse_url($path) . $separator . 'v=' . substr(hash_file('sha256', dirname(__DIR__) . '/' . $path), 0, 12);
    };
    foreach ($styles as $stylesheet) {
        echo '<link rel="stylesheet" href="' . e($assetUrl('assets/css/' . $stylesheet)) . '">' . "\n";
    }
    if (!$admin) {
        echo '<script src="' . e($assetUrl('assets/js/media-gallery.js')) . '" defer></script>' . "\n";
    }
})($isAdminPage ?? false);
