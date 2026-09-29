<?php

require_once __DIR__ . '/config.php';

function e($value)
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function xuverse_setting($settings, $key, $fallback = '')
{
    if (!is_array($settings) || !array_key_exists($key, $settings) || $settings[$key] === null || $settings[$key] === '') {
        return $fallback;
    }

    return $settings[$key];
}

function xuverse_excerpt($text, $length = 180)
{
    $plain = preg_replace('/^\s*(?:#{2,3}|[-*])\s+/mu', '', strip_tags((string)$text));
    $clean = trim(preg_replace('/\s+/u', ' ', $plain));

    if (function_exists('mb_strlen') && mb_strlen($clean, 'UTF-8') <= $length) {
        return $clean;
    }

    if (!function_exists('mb_strlen') && strlen($clean) <= $length) {
        return $clean;
    }

    $excerpt = function_exists('mb_substr')
        ? mb_substr($clean, 0, $length - 1, 'UTF-8')
        : substr($clean, 0, $length - 1);

    return rtrim($excerpt) . '...';
}

function xuverse_app_path()
{
    $configuredPath = trim((string)xuverse_config('APP_PATH', ''));

    if ($configuredPath !== '') {
        $normalizedPath = trim($configuredPath, '/');

        return $normalizedPath === '' ? '' : '/' . $normalizedPath;
    }

    $configuredUrl = trim((string)xuverse_config('BASE_URL', ''));
    if ($configuredUrl !== '') {
        $path = trim((string)(parse_url($configuredUrl, PHP_URL_PATH) ?? ''), '/');

        return $path === '' ? '' : '/' . $path;
    }

    $scriptName = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? ''));
    if (strpos($scriptName, '/admin/') !== false) {
        return substr($scriptName, 0, strpos($scriptName, '/admin/'));
    }
    $directory = trim(str_replace('\\', '/', dirname($scriptName)), '/.');

    return $directory === '' ? '' : '/' . $directory;
}

function xuverse_base_url()
{
    $configuredUrl = trim((string)xuverse_config('BASE_URL', ''));

    if ($configuredUrl !== '') {
        return rtrim($configuredUrl, '/');
    }

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = (string)($_SERVER['HTTP_HOST'] ?? 'localhost');
    $host = preg_match('/^[A-Za-z0-9.-]+(?::[0-9]{1,5})?$/', $host) ? $host : 'localhost';

    return $scheme . '://' . $host . xuverse_app_path();
}

function xuverse_url($path = '')
{
    $path = ltrim((string)$path, '/');
    $basePath = xuverse_app_path();

    if ($path === '') {
        return ($basePath === '' ? '' : $basePath) . '/';
    }

    return ($basePath === '' ? '' : $basePath) . '/' . $path;
}

function xuverse_is_production()
{
    return filter_var(xuverse_config('PRODUCTION', false), FILTER_VALIDATE_BOOLEAN);
}

function xuverse_person_name($conn)
{
    static $name;
    if ($name === null) {
        $row = $conn->query('SELECT hero_title FROM settings LIMIT 1')->fetch_assoc();
        $name = xuverse_setting($row, 'hero_title', 'B K Suraj');
    }
    return $name;
}

// A small, escaped text format shared by articles and project case studies.
// Authors can use paragraphs, ## / ### headings and - bullet lists.
function xuverse_prose($text)
{
    $blocks = preg_split('/\R\s*\R/u', trim((string)$text));
    $html = '';
    foreach ($blocks as $block) {
        $lines = preg_split('/\R/u', $block);
        $paragraph = [];
        $listOpen = false;
        $flush = function () use (&$paragraph, &$html) {
            if ($paragraph) {
                $html .= '<p>' . implode('<br>', array_map('e', $paragraph)) . '</p>';
                $paragraph = [];
            }
        };
        foreach ($lines as $line) {
            if (preg_match('/^(#{2,3})\s+(.+)$/u', trim($line), $match)) {
                $flush();
                if ($listOpen) { $html .= '</ul>'; $listOpen = false; }
                $level = strlen($match[1]);
                $html .= '<h' . $level . '>' . e($match[2]) . '</h' . $level . '>';
            } elseif (preg_match('/^[-*]\s+(.+)$/u', trim($line), $match)) {
                $flush();
                if (!$listOpen) { $html .= '<ul>'; $listOpen = true; }
                $html .= '<li>' . e($match[1]) . '</li>';
            } else {
                if ($listOpen) { $html .= '</ul>'; $listOpen = false; }
                if (trim($line) !== '') { $paragraph[] = $line; }
            }
        }
        $flush();
        if ($listOpen) { $html .= '</ul>'; }
    }
    return $html;
}

function xuverse_csrf_token()
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return '';
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function xuverse_session_start()
{
    if (session_status() === PHP_SESSION_ACTIVE) { return; }
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => xuverse_is_production() || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'path' => xuverse_url()
    ]);
    ini_set('session.use_strict_mode', '1');
    session_start();
}

function xuverse_verify_csrf()
{
    $provided = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    $expected = $_SESSION['csrf_token'] ?? '';

    return is_string($provided) && is_string($expected) && $provided !== '' && $expected !== '' && hash_equals($expected, $provided);
}

function xuverse_noindex()
{
    return 'noindex, nofollow, noarchive';
}

function xuverse_absolute_url($path)
{
    $path = trim((string)$path);

    if ($path === '') {
        return xuverse_base_url() . '/';
    }

    if (preg_match('/^(https?:)?\/\//', $path)) {
        return $path;
    }

    $appPath = xuverse_app_path();
    if ($appPath !== '' && (strpos($path, $appPath . '/') === 0 || $path === $appPath)) {
        return preg_replace('#' . preg_quote($appPath, '#') . '$#', '', xuverse_base_url()) . $path;
    }

    return xuverse_base_url() . '/' . ltrim($path, '/');
}

function xuverse_current_url()
{
    $path = $_SERVER['REQUEST_URI'] ?? xuverse_url('index.php');

    return preg_replace('#\?.*$#', '', xuverse_absolute_url($path));
}

function xuverse_estimated_reading_time($text)
{
    $words = str_word_count(strip_tags((string)$text));

    return max(1, (int)ceil($words / 220));
}

function xuverse_table_exists($conn, $table)
{
    $table = $conn->real_escape_string($table);
    $result = $conn->query("SHOW TABLES LIKE '{$table}'");

    return $result && $result->num_rows > 0;
}

function xuverse_asset($path)
{
    $path = trim((string)$path);

    if ($path === '') {
        return '';
    }

    if (preg_match('/^(https?:)?\/\//', $path)) {
        return $path;
    }

    $appPath = xuverse_app_path();
    if ($appPath !== '' && strpos($path, $appPath . '/') === 0) {
        return str_replace(' ', '%20', $path);
    }

    return xuverse_url(str_replace(' ', '%20', ltrim($path, '/')));
}

function xuverse_image_attributes($path)
{
    $path = trim((string)$path);

    if ($path === '') {
        return '';
    }

    if (preg_match('#^https://i\.ytimg\.com/#', $path)) {
        return 'width="480" height="360"';
    }

    if (preg_match('/^(https?:)?\/\//', $path)) {
        return '';
    }

    $relative = preg_replace('#^' . preg_quote(trim(xuverse_app_path(), '/'), '#') . '/#', '', ltrim($path, '/'));
    $root = realpath(dirname(__DIR__));
    $file = realpath(dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative));

    if (!$root || !$file || strpos($file, $root) !== 0 || !is_file($file)) {
        return '';
    }

    $size = @getimagesize($file);

    if (!$size) {
        return '';
    }

    return 'width="' . (int)$size[0] . '" height="' . (int)$size[1] . '"';
}

function xuverse_responsive_image_attributes($path, $sizes = '(max-width: 720px) 94vw, 40vw')
{
    $path = trim((string)$path);

    if ($path === '' || preg_match('/^(https?:)?\/\//', $path) || !preg_match('/\.webp$/i', $path)) {
        return '';
    }

    $relative = preg_replace('#^' . preg_quote(trim(xuverse_app_path(), '/'), '#') . '/#', '', ltrim($path, '/'));
    $root = dirname(__DIR__);
    $candidates = [];

    foreach ([480, 800] as $variant) {
        $variantRelative = preg_replace('/\.webp$/i', '-' . $variant . '.webp', $relative);
        $variantFile = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $variantRelative);

        if (!is_file($variantFile)) {
            continue;
        }

        $dimensions = @getimagesize($variantFile);
        if ($dimensions) {
            $candidates[] = xuverse_asset($variantRelative) . ' ' . (int)$dimensions[0] . 'w';
        }
    }

    $originalFile = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    $originalDimensions = is_file($originalFile) ? @getimagesize($originalFile) : false;

    if ($originalDimensions) {
        $candidates[] = xuverse_asset($relative) . ' ' . (int)$originalDimensions[0] . 'w';
    }

    if (count($candidates) < 2) {
        return '';
    }

    return 'srcset="' . e(implode(', ', $candidates)) . '" sizes="' . e($sizes) . '"';
}

function xuverse_avif_source($path, $sizes = '(max-width: 720px) 94vw, 40vw')
{
    $path = trim((string)$path);

    if ($path === '' || preg_match('/^(https?:)?\/\//', $path) || !preg_match('/\.webp$/i', $path)) {
        return '';
    }

    $relative = preg_replace('#^' . preg_quote(trim(xuverse_app_path(), '/'), '#') . '/#', '', ltrim($path, '/'));
    $root = dirname(__DIR__);
    $candidates = [];

    foreach ([480, 800] as $variant) {
        $variantRelative = preg_replace('/\.webp$/i', '-' . $variant . '.avif', $relative);
        $variantFile = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $variantRelative);

        if (!is_file($variantFile)) {
            continue;
        }

        $dimensions = @getimagesize($variantFile);
        if ($dimensions) {
            $candidates[] = xuverse_asset($variantRelative) . ' ' . (int)$dimensions[0] . 'w';
        }
    }

    $originalRelative = preg_replace('/\.webp$/i', '.avif', $relative);
    $originalFile = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $originalRelative);
    $originalDimensions = is_file($originalFile) ? @getimagesize($originalFile) : false;

    if ($originalDimensions) {
        $candidates[] = xuverse_asset($originalRelative) . ' ' . (int)$originalDimensions[0] . 'w';
    }

    if (!$candidates) {
        return '';
    }

    return '<source type="image/avif" srcset="' . e(implode(', ', $candidates)) . '" sizes="' . e($sizes) . '">';
}

function xuverse_fetch_highlights($conn, $category, $limit = null)
{
    $sql = "SELECT * FROM highlights WHERE category=? AND is_active=1 ORDER BY display_order, id";

    if ($limit !== null) {
        $sql .= " LIMIT " . (int)$limit;
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $category);
    $stmt->execute();

    return $stmt->get_result();
}

function xuverse_youtube_embed($url)
{
    $parts = parse_url(trim((string)$url));
    if (!$parts || !in_array(strtolower($parts['scheme'] ?? ''), ['https', 'http'], true)) {
        return '';
    }
    $host = strtolower($parts['host'] ?? '');
    $path = trim($parts['path'] ?? '', '/');
    $id = '';
    if (in_array($host, ['youtu.be', 'www.youtu.be'], true)) {
        $id = explode('/', $path)[0];
    } elseif (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtube-nocookie.com', 'www.youtube-nocookie.com'], true)) {
        parse_str($parts['query'] ?? '', $query);
        if ($path === 'watch') {
            $id = $query['v'] ?? '';
        } elseif (preg_match('#^(?:embed|shorts|live)/([^/]+)$#', $path, $match)) {
            $id = $match[1];
        }
    }
    return is_string($id) && preg_match('/^[A-Za-z0-9_-]{11}$/', $id)
        ? 'https://www.youtube-nocookie.com/embed/' . $id
        : '';
}
