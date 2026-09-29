<?php

require_once '../../includes/functions.php';
xuverse_session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once '../../includes/db.php';
require_once '../../includes/functions.php';
require_once '../../includes/music_helpers.php';

$error = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $title = trim($_POST['title'] ?? '');
        $artist = trim($_POST['artist'] ?? '');
        $playlist = trim($_POST['playlist'] ?? 'XuVerse') ?: 'XuVerse';
        $displayOrder = (int)($_POST['display_order'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($title === '') {
            $error = 'Title is required.';
        }

        $filePath = $error === '' ? xuverse_upload_audio('audio', $error, true) : '';

        if ($error === '' && $filePath !== '') {
            $stmt = $conn->prepare(
                "INSERT INTO music_tracks
                (title, artist, playlist, file_path, display_order, is_active)
                VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("ssssii", $title, $artist, $playlist, $filePath, $displayOrder, $isActive);
            $stmt->execute();
            $message = 'Track uploaded.';
        }
    }

    if ($action === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $artist = trim($_POST['artist'] ?? '');
        $playlist = trim($_POST['playlist'] ?? 'XuVerse') ?: 'XuVerse';
        $displayOrder = (int)($_POST['display_order'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        $stmt = $conn->prepare("SELECT file_path FROM music_tracks WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $track = $stmt->get_result()->fetch_assoc();

        if (!$track) {
            $error = 'Track not found.';
        } elseif ($title === '') {
            $error = 'Title is required.';
        } else {
            $newFilePath = xuverse_upload_audio('audio', $error, false);

            if ($error === '') {
                $filePath = $newFilePath ?: $track['file_path'];
                $stmt = $conn->prepare(
                    "UPDATE music_tracks
                     SET title=?, artist=?, playlist=?, file_path=?, display_order=?, is_active=?
                     WHERE id=?"
                );
                $stmt->bind_param("ssssiii", $title, $artist, $playlist, $filePath, $displayOrder, $isActive, $id);
                $stmt->execute();

                if ($newFilePath) {
                    xuverse_delete_local_file($track['file_path']);
                }

                $message = 'Track updated.';
            }
        }
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $conn->prepare("SELECT file_path FROM music_tracks WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $track = $stmt->get_result()->fetch_assoc();

        if ($track) {
            $stmt = $conn->prepare("DELETE FROM music_tracks WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            xuverse_delete_local_file($track['file_path']);
            $message = 'Track deleted.';
        }
    }
}

$tracks = $conn->query(
    "SELECT * FROM music_tracks
     ORDER BY playlist, display_order, id"
);

$pageTitle = 'Music Library - XuVerse';
$pageDescription = 'Manage XuVerse local music tracks and playlists.';

require_once '../../includes/functions.php';
$pageTitle = $pageTitle ?? 'Music Library - XuVerse';
$robots = xuverse_noindex();
header('Cache-Control: no-store');
include '../../includes/header.php';
include '../../includes/navbar.php';

?>

<section class="section admin-page">
<div class="container">
<a class="text-link admin-back-link" href="../dashboard.php">Back to dashboard</a>

<div class="section-heading with-actions">
<div>
<p class="page-kicker">Sound System</p>
<h1>Music Library</h1>
<p>Upload local tracks, organize playlists and control the XuVerse floating player.</p>
</div>
<a href="../dashboard.php" class="btn secondary-btn">Dashboard</a>
</div>

<?php if($error !== ''): ?>
<p class="error" role="alert"><?= e($error) ?></p>
<?php endif; ?>

<?php if($message !== ''): ?>
<p class="form-help"><?= e($message) ?></p>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="form-panel admin-form music-admin-form">
<input type="hidden" name="csrf_token" value="<?= e(xuverse_csrf_token()) ?>">
<input type="hidden" name="action" value="create">

<div class="input-grid">
<div class="input-group">
<label for="field-title-1">Title</label>
<input id="field-title-1" type="text" name="title" required>
</div>

<div class="input-group">
<label for="field-artist-2">Artist</label>
<input id="field-artist-2" type="text" name="artist" placeholder="B K Suraj">
</div>

<div class="input-group">
<label for="field-playlist-3">Playlist</label>
<input id="field-playlist-3" type="text" name="playlist" value="Ambient">
</div>

<div class="input-group">
<label for="field-display-order-4">Order</label>
<input id="field-display-order-4" type="number" name="display_order" value="0">
</div>
</div>

<div class="input-group">
<label for="field-audio-5">Audio File</label>
<input id="field-audio-5" type="file" name="audio" accept=".mp3,.wav,.ogg,.m4a,audio/*" required>
</div>

<label class="check-row">
<input type="checkbox" name="is_active" checked>
Active in player
</label>

<button type="submit" class="btn">Upload Track</button>
</form>

<div class="admin-list music-track-list">
<?php if($tracks && $tracks->num_rows > 0): ?>
<?php while($track = $tracks->fetch_assoc()): ?>
<article class="admin-row music-row">
<form method="POST" enctype="multipart/form-data" class="music-update-form">
<input type="hidden" name="csrf_token" value="<?= e(xuverse_csrf_token()) ?>">
<input type="hidden" name="action" value="update">
<input type="hidden" name="id" value="<?= (int)$track['id'] ?>">

<div class="music-row-grid">
<div class="input-group">
<label for="field-title-6-<?= (int)$track['id'] ?>">Title</label>
<input id="field-title-6-<?= (int)$track['id'] ?>" type="text" name="title" value="<?= e($track['title']) ?>" required>
</div>

<div class="input-group">
<label for="field-artist-7-<?= (int)$track['id'] ?>">Artist</label>
<input id="field-artist-7-<?= (int)$track['id'] ?>" type="text" name="artist" value="<?= e($track['artist']) ?>">
</div>

<div class="input-group">
<label for="field-playlist-8-<?= (int)$track['id'] ?>">Playlist</label>
<input id="field-playlist-8-<?= (int)$track['id'] ?>" type="text" name="playlist" value="<?= e($track['playlist']) ?>">
</div>

<div class="input-group">
<label for="field-display-order-9-<?= (int)$track['id'] ?>">Order</label>
<input id="field-display-order-9-<?= (int)$track['id'] ?>" type="number" name="display_order" value="<?= (int)$track['display_order'] ?>">
</div>
</div>

<div>
<p class="form-help"><?= e($track['file_path']) ?></p>
<div class="input-group">
<label for="field-audio-10-<?= (int)$track['id'] ?>">Replace Audio</label>
<input id="field-audio-10-<?= (int)$track['id'] ?>" type="file" name="audio" accept=".mp3,.wav,.ogg,.m4a,audio/*">
</div>

<label class="check-row">
<input type="checkbox" name="is_active" <?= (int)$track['is_active'] === 1 ? 'checked' : '' ?>>
Active
</label>
</div>

<button type="submit" class="btn secondary-btn">Save</button>
</form>

<form method="POST" class="music-delete-form" data-confirm="Delete this track?">
<input type="hidden" name="csrf_token" value="<?= e(xuverse_csrf_token()) ?>">
<input type="hidden" name="action" value="delete">
<input type="hidden" name="id" value="<?= (int)$track['id'] ?>">
<button type="submit" class="btn danger-btn">Delete</button>
</form>
</article>

<?php endwhile; ?>
<?php else: ?>
<div class="empty-state">
<h2>No music yet</h2>
<p>Upload a local audio file to activate the XuVerse player.</p>
</div>
<?php endif; ?>
</div>

</div>
</section>

<?php include '../../includes/footer.php'; ?>
