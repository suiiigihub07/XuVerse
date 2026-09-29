<?php

require_once '../../includes/functions.php';
xuverse_session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

require_once '../../includes/db.php';
require_once '../../includes/media_helpers.php';

$id = (int)($_GET['id'] ?? 0);
$error = '';

$stmt = $conn->prepare(
    "SELECT * FROM projects WHERE id=?"
);

$stmt->bind_param("i", $id);
$stmt->execute();
$project = $stmt->get_result()->fetch_assoc();

if (!$project) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $github = trim($_POST['github'] ?? '');
    $demo = trim($_POST['demo'] ?? '');
    $isPublished = isset($_POST['is_published']) ? 1 : 0;
    $displayOrder = (int)($_POST['display_order'] ?? 0);
    $projectStatus = trim($_POST['project_status'] ?? '');
    $projectScope = trim($_POST['project_scope'] ?? '');
    $projectFocus = trim($_POST['project_focus'] ?? '');
    $projectEvidence = trim($_POST['project_evidence'] ?? '');
    $caseStudy = trim($_POST['case_study'] ?? '');
    foreach ([$github, $demo] as $projectUrl) {
        if ($projectUrl !== '' && (!filter_var($projectUrl, FILTER_VALIDATE_URL) || !in_array(strtolower((string)parse_url($projectUrl, PHP_URL_SCHEME)), ['http', 'https'], true))) {
            $error = 'Repository and demo links must be valid HTTP or HTTPS URLs.';
        }
    }

    $imagePath = $project['image_path'];

    if ($title === '') {
        $error = "Project title is required.";
    }

    $newImagePath = null;

    if ($error === '') {
        $newImagePath = xuverse_upload_image('project_image', $error, false, 'projects');

        if ($newImagePath) {
            $imagePath = $newImagePath;
        }
    }

    if ($error === '') {
        $stmt = $conn->prepare(
            "UPDATE projects
             SET title=?,
                 description=?,
                 image_path=?,
                 github_link=?,
                 demo_link=?, is_published=?, display_order=?, project_status=?,
                 project_scope=?, project_focus=?, project_evidence=?, case_study=?
             WHERE id=?"
        );

        $stmt->bind_param("sssssiisssssi", $title, $description, $imagePath, $github, $demo, $isPublished, $displayOrder, $projectStatus, $projectScope, $projectFocus, $projectEvidence, $caseStudy, $id);
        $stmt->execute();

        if ($newImagePath) {
            xuverse_delete_uploaded_file($project['image_path']);
        }

        header("Location: index.php");
        exit;
    }
}

require_once '../../includes/functions.php';
$pageTitle = $pageTitle ?? 'Edit Project - XuVerse';
$robots = xuverse_noindex();
header('Cache-Control: no-store');
include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<section class="section admin-page">
<div class="container">
<a class="text-link admin-back-link" href="index.php">Back to projects</a>

<h1>Edit Project</h1>

<?php if($error !== ''): ?>
<p class="error" role="alert"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="admin-form">
<input type="hidden" name="csrf_token" value="<?= e(xuverse_csrf_token()) ?>">

<div class="input-group">
<label for="field-title-1">Title</label>
<input id="field-title-1"
type="text"
name="title"
value="<?= htmlspecialchars($_POST['title'] ?? $project['title']) ?>"
required>
</div>

<div class="input-group">
<label for="field-description-2">Description</label>
<textarea id="field-description-2"
name="description"
rows="6"><?= htmlspecialchars($_POST['description'] ?? $project['description']) ?></textarea>
</div>

<?php if(!empty($project['image_path'])): ?>
<div class="media-preview">
<img
src="../../<?= htmlspecialchars($project['image_path']) ?>"
alt="<?= htmlspecialchars($project['title']) ?>">
</div>
<?php endif; ?>

<div class="input-group">
<label for="field-project-image-3">Replace Project Image</label>
<input id="field-project-image-3"
type="file"
name="project_image"
accept=".jpg,.jpeg,.png">
</div>

<div class="input-group">
<label for="field-github-4">Repository URL (optional)</label>
<input id="field-github-4"
type="url"
name="github"
value="<?= htmlspecialchars($_POST['github'] ?? $project['github_link']) ?>">
</div>

<div class="input-group">
<label for="field-demo-5">Live demo URL (optional)</label>
<input id="field-demo-5"
type="url"
name="demo"
value="<?= htmlspecialchars($_POST['demo'] ?? $project['demo_link']) ?>">
</div>

<div class="form-panel">
<h2>Case Study</h2>
<p class="admin-help">These fields appear on the public project page. Add only accurate context and outcomes; empty sections stay hidden.</p>
<div class="input-group">
<label for="field-project-status-6">Status</label>
<input id="field-project-status-6" type="text" name="project_status" value="<?= e($_POST['project_status'] ?? ($project['project_status'] ?? '')) ?>" maxlength="100">
</div>
<div class="input-group">
<label for="field-project-scope-7">Scope or your role</label>
<input id="field-project-scope-7" type="text" name="project_scope" value="<?= e($_POST['project_scope'] ?? ($project['project_scope'] ?? '')) ?>">
</div>
<div class="input-group">
<label for="field-project-focus-8">Focus</label>
<input id="field-project-focus-8" type="text" name="project_focus" value="<?= e($_POST['project_focus'] ?? ($project['project_focus'] ?? '')) ?>">
</div>
<div class="input-group">
<label for="field-project-evidence-9">Evidence or results</label>
<input id="field-project-evidence-9" type="text" name="project_evidence" value="<?= e($_POST['project_evidence'] ?? ($project['project_evidence'] ?? '')) ?>">
</div>
<div class="input-group">
<label for="field-case-study-10">Case study</label>
<textarea id="field-case-study-10" name="case_study" rows="12" aria-describedby="case-study-help"><?= e($_POST['case_study'] ?? ($project['case_study'] ?? '')) ?></textarea>
<p id="case-study-help" class="admin-help">Use a blank line between paragraphs and start section headings with ## followed by a space. Explain the context, your contribution, decisions and results.</p>
</div>
</div>
<div class="form-panel">
<h2>Publishing</h2>
<div class="input-group">
<label for="field-display-order-11">Display order</label>
<input id="field-display-order-11" type="number" name="display_order" value="<?= (int)($_POST['display_order'] ?? ($project['display_order'] ?? 0)) ?>" min="0" step="1">
<p class="admin-help">Lower numbers appear first.</p>
</div>
<label class="check-row"><input type="checkbox" name="is_published" value="1" <?= ($_SERVER['REQUEST_METHOD'] === 'POST' ? isset($_POST['is_published']) : !empty($project['is_published'])) ? 'checked' : '' ?>> Publish this project</label>
<p class="admin-help">Keep this unchecked while preparing the project. Published projects are visible to everyone.</p>
</div>

<div class="admin-form-actions">
<button class="btn" type="submit">
Update Project
</button>
<a href="index.php" class="btn secondary-btn">Cancel</a>
</div>
</form>

</div>
</section>

<?php include '../../includes/footer.php'; ?>
