
<?php
require_once __DIR__ . '/../includes/auth.php';

admin_require_role(['super_admin', 'content_manager']);

$pdo = get_db();
$admin = admin_current_user();

$uploadRoot = dirname(__DIR__) . '/uploads/media';
$maxFileSize = 8 * 1024 * 1024;

$allowedTypes = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    'image/gif'  => 'gif',
];

$message = '';
$error = '';

function media_escape(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function media_redirect(): void
{
    header('Location: ' . SITE_URL . '/admin/media.php');
    exit;
}

function media_create_directory(string $directory): void
{
    if (!is_dir($directory) &&
        !mkdir($directory, 0755, true) &&
        !is_dir($directory)) {
        throw new RuntimeException('Unable to create the media directory.');
    }
}

function media_verify_csrf(): bool
{
    return admin_verify_csrf($_POST['csrf_token'] ?? null);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!media_verify_csrf()) {
        $error = 'Your session has expired. Refresh the page and try again.';
    } else {
        $action = $_POST['action'] ?? '';

        try {
            if ($action === 'upload') {

                if (
                    !isset($_FILES['media_file']) ||
                    $_FILES['media_file']['error'] !== UPLOAD_ERR_OK
                ) {
                    throw new RuntimeException('Please select a valid image to upload.');
                }

                $file = $_FILES['media_file'];

                if ($file['size'] <= 0 || $file['size'] > $maxFileSize) {
                    throw new RuntimeException('Images must be no larger than 8 MB.');
                }

                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($file['tmp_name']);

                if (!isset($allowedTypes[$mime])) {
                    throw new RuntimeException('Only JPEG, PNG, WebP and GIF images are allowed.');
                }

                $imageInfo = getimagesize($file['tmp_name']);

                if ($imageInfo === false || $imageInfo['mime'] !== $mime) {
                    throw new RuntimeException('The uploaded file is not a valid image.');
                }

                $category = $_POST['category'] ?? 'general';

                $directories = [
                    'portfolio' => 'portfolio',
                    'gallery' => 'gallery',
                    'general' => 'general',
                ];

                if (!isset($directories[$category])) {
                    throw new RuntimeException('Invalid media category.');
                }

                $targetDirectory = $uploadRoot . '/' . $directories[$category];
                media_create_directory($targetDirectory);

                $extension = $allowedTypes[$mime];
                $filename = bin2hex(random_bytes(20)) . '.' . $extension;
                $absolutePath = $targetDirectory . '/' . $filename;

                $relativePath = 'uploads/media/' . $directories[$category] . '/' . $filename;

                $originalName = basename($file['name']);
                $altText = trim($_POST['alt_text'] ?? '');
                $caption = trim($_POST['caption'] ?? '');

                if (!move_uploaded_file($file['tmp_name'], $absolutePath)) {
                    throw new RuntimeException('Unable to save the uploaded image.');
                }

                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO media_assets (
                            uploaded_by_admin_id,
                            original_filename,
                            stored_filename,
                            relative_path,
                            mime_type,
                            file_extension,
                            file_size_bytes,
                            width_px,
                            height_px,
                            alt_text,
                            caption,
                            usage_context,
                            approval_status,
                            is_active
                        ) VALUES (
                            :admin_id,
                            :original_name,
                            :stored_name,
                            :relative_path,
                            :mime,
                            :extension,
                            :file_size,
                            :width,
                            :height,
                            :alt_text,
                            :caption,
                            :usage_context,
                            'approved',
                            1
                        )
                    ");

                    $stmt->execute([
                        'admin_id' => $admin['id'],
                        'original_name' => $originalName,
                        'stored_name' => $filename,
                        'relative_path' => $relativePath,
                        'mime' => $mime,
                        'extension' => $extension,
                        'file_size' => $file['size'],
                        'width' => $imageInfo[0],
                        'height' => $imageInfo[1],
                        'alt_text' => $altText,
                        'caption' => $caption,
                        'usage_context' => $category,
                    ]);

                } catch (Throwable $e) {
                    if (is_file($absolutePath)) {
                        unlink($absolutePath);
                    }
                    throw $e;
                }

                $_SESSION['media_flash'] = 'Image uploaded successfully.';
                media_redirect();
            }

            if ($action === 'update') {
                $id = filter_input(INPUT_POST, 'media_id', FILTER_VALIDATE_INT);

                if (!$id) {
                    throw new RuntimeException('Invalid media record.');
                }

                $stmt = $pdo->prepare("
                    UPDATE media_assets
                    SET alt_text = :alt_text,
                        caption = :caption,
                        usage_context = :usage_context
                    WHERE id = :id AND is_active = 1
                ");

                $category = $_POST['category'] ?? 'general';

                if (!in_array($category, ['portfolio', 'gallery', 'general'], true)) {
                    throw new RuntimeException('Invalid media category.');
                }

                $stmt->execute([
                    'alt_text' => trim($_POST['alt_text'] ?? ''),
                    'caption' => trim($_POST['caption'] ?? ''),
                    'usage_context' => $category,
                    'id' => $id,
                ]);

                $_SESSION['media_flash'] = 'Image details updated.';
                media_redirect();
            }

            if ($action === 'deactivate') {
                $id = filter_input(INPUT_POST, 'media_id', FILTER_VALIDATE_INT);

                if (!$id) {
                    throw new RuntimeException('Invalid media record.');
                }

                $stmt = $pdo->prepare("
                    UPDATE media_assets
                    SET is_active = 0
                    WHERE id = :id
                ");

                $stmt->execute(['id' => $id]);

                $_SESSION['media_flash'] = 'Image deactivated. The original file was preserved.';
                media_redirect();
            }

            throw new RuntimeException('Unknown request.');

        } catch (Throwable $e) {
            error_log('Media Library error: ' . $e->getMessage());
            $error = $e instanceof RuntimeException
                ? $e->getMessage()
                : 'The operation could not be completed. Please try again.';
        }
    }
}

if (!empty($_SESSION['media_flash'])) {
    $message = $_SESSION['media_flash'];
    unset($_SESSION['media_flash']);
}

$stmt = $pdo->query("
    SELECT *
    FROM media_assets
    WHERE is_active = 1
    ORDER BY created_at DESC
");

$mediaItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalMedia = count($mediaItems);

$categoryCounts = [
    'portfolio' => 0,
    'gallery' => 0,
    'general' => 0,
];

foreach ($mediaItems as $item) {
    if (isset($categoryCounts[$item['usage_context']])) {
        $categoryCounts[$item['usage_context']]++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>Media Library | Verve Creative Studio</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">

<style>
:root {
    --bg:#0B0A09;
    --sidebar:#100F0D;
    --panel:#141210;
    --line:#2A2620;
    --gold:#C9A24B;
    --gold-light:#E8C873;
    --cream:#F2EDE4;
    --muted:#A8A29A;
    --danger:#E58A82;
    --green:#8CB99A;
}
*{box-sizing:border-box}
body{margin:0;background:var(--bg);color:var(--cream);font-family:'Jost',sans-serif}
.layout{min-height:100vh;display:grid;grid-template-columns:245px minmax(0,1fr)}
aside{padding:30px 20px;background:var(--sidebar);border-right:1px solid var(--line)}
.brand{padding:5px 10px 30px;border-bottom:1px solid var(--line)}
.brand-name{margin:0;color:var(--gold-light);font-family:'Cormorant Garamond',serif;font-size:30px;letter-spacing:4px}
.brand-subtitle{color:var(--muted);font-size:9px;letter-spacing:2.5px;text-transform:uppercase}
.nav-label{margin:30px 10px 12px;color:#777067;font-size:10px;letter-spacing:2px;text-transform:uppercase}
.nav-item{display:block;padding:12px 13px;margin-bottom:5px;border-radius:3px;color:var(--muted);font-size:13px;text-decoration:none}
.nav-item.active{border-left:2px solid var(--gold);background:rgba(201,162,75,.09);color:var(--gold-light)}
main{min-width:0;padding:30px 38px 50px}
.topbar{display:flex;justify-content:space-between;align-items:center;padding-bottom:23px;border-bottom:1px solid var(--line)}
.topbar-label{color:var(--muted);font-size:12px}
.account{font-size:12px;text-align:right}
.account span{display:block;margin-top:3px;color:var(--muted);font-size:10px}
.logout{color:var(--gold);text-decoration:none}
.heading{margin:34px 0 25px}
.eyebrow{color:var(--gold);font-size:10px;letter-spacing:2.5px;text-transform:uppercase}
h1,h2{font-family:'Cormorant Garamond',serif;font-weight:500}
h1{margin:7px 0;font-size:38px}
.intro{color:var(--muted);font-size:13px}
.stats{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:28px}
.stat,.upload-card,.media-card{background:var(--panel);border:1px solid var(--line);border-radius:4px}
.stat{padding:17px 20px}
.stat span{color:var(--muted);font-size:11px}
.stat strong{display:block;margin-top:7px;font-family:'Cormorant Garamond',serif;font-size:29px;font-weight:500}
.upload-card{padding:24px;margin-bottom:30px}
h2{margin:0 0 18px;font-size:25px}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:17px}
.field{margin-bottom:15px}
label{display:block;margin-bottom:7px;color:var(--muted);font-size:11px}
input,select{width:100%;min-height:43px;padding:10px 12px;border:1px solid var(--line);border-radius:3px;background:#0D0C0B;color:var(--cream);font:13px 'Jost',sans-serif}
input[type=file]{padding:8px}
input:focus,select:focus{outline:1px solid var(--gold)}
.hint{color:#777067;font-size:10px;margin-top:7px}
.button{display:inline-block;padding:12px 20px;border:1px solid var(--gold);border-radius:3px;background:var(--gold);color:#17130B;font:600 11px 'Jost',sans-serif;letter-spacing:1px;text-transform:uppercase;cursor:pointer}
.button:hover{background:var(--gold-light)}
.button.secondary{background:transparent;color:var(--gold)}
.button.danger{border-color:#75413B;background:transparent;color:var(--danger)}
.notice{padding:13px 16px;margin-bottom:20px;border:1px solid var(--line);font-size:12px}
.notice.success{border-color:rgba(140,185,154,.4);color:var(--green)}
.notice.error{border-color:rgba(229,138,130,.4);color:var(--danger)}
.library-heading{display:flex;justify-content:space-between;align-items:center;gap:15px;margin-bottom:17px}
.library-heading h2{margin:0}
.media-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}
.media-card{overflow:hidden}
.media-card img{display:block;width:100%;height:190px;object-fit:cover;background:#0D0C0B}
.media-info{padding:15px}
.media-name{overflow-wrap:anywhere;font-size:12px}
.media-meta{margin-top:6px;color:var(--muted);font-size:10px}
.media-card form{margin-top:13px}
.media-card .field{margin-bottom:10px}
.media-actions{display:flex;gap:8px;flex-wrap:wrap}
.media-actions button{font-size:10px;padding:9px 12px}
.empty{padding:40px 20px;border:1px dashed var(--line);color:var(--muted);text-align:center;font-size:13px}
@media(max-width:1050px){.media-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:700px){
.layout{grid-template-columns:1fr}
aside{padding:18px 20px}
.brand{padding-bottom:14px}
.nav-label,aside .nav-item{display:none}
main{padding:22px 17px 40px}
.stats{grid-template-columns:1fr 1fr}
.media-grid{grid-template-columns:1fr 1fr}
.form-grid{grid-template-columns:1fr}
h1{font-size:32px}
}
@media(max-width:430px){.media-grid{grid-template-columns:1fr}}
</style>
</head>
<body>

<div class="layout">
<aside>
    <div class="brand">
        <p class="brand-name">VERVE</p>
        <div class="brand-subtitle">Creative Studio</div>
    </div>
    <div class="nav-label">Workspace</div>
    <a class="nav-item" href="<?=media_escape(SITE_URL)?>/admin/">Dashboard</a>
    <div class="nav-label">Management</div>
    <a class="nav-item" href="<?=media_escape(SITE_URL)?>/admin/">Bookings</a>
    <a class="nav-item" href="<?=media_escape(SITE_URL)?>/admin/">Project enquiries</a>
    <a class="nav-item" href="<?=media_escape(SITE_URL)?>/admin/">Portfolio</a>
    <a class="nav-item" href="<?=media_escape(SITE_URL)?>/admin/">Gallery</a>
    <a class="nav-item active" href="<?=media_escape(SITE_URL)?>/admin/media.php">Media library</a>
    <a class="nav-item" href="<?=media_escape(SITE_URL)?>/admin/">Payments</a>
</aside>

<main>
<header class="topbar">
    <div class="topbar-label">Studio administration / Media library</div>
    <div class="account">
        <?=media_escape($admin['name'])?>
        <span><?=media_escape($admin['role_name'])?> · <a class="logout" href="<?=media_escape(SITE_URL)?>/admin/logout.php">Sign out</a></span>
    </div>
</header>

<section class="heading">
    <div class="eyebrow">Content management</div>
    <h1>Media Library</h1>
    <p class="intro">Upload, organise and manage your studio's visual assets.</p>
</section>

<?php if($message): ?>
<div class="notice success"><?=media_escape($message)?></div>
<?php endif; ?>

<?php if($error): ?>
<div class="notice error"><?=media_escape($error)?></div>
<?php endif; ?>

<section class="stats">
    <div class="stat"><span>Active images</span><strong><?=number_format($totalMedia)?></strong></div>
    <div class="stat"><span>Portfolio assets</span><strong><?=number_format($categoryCounts['portfolio'])?></strong></div>
    <div class="stat"><span>Gallery assets</span><strong><?=number_format($categoryCounts['gallery'])?></strong></div>
</section>

<section class="upload-card">
    <h2>Upload new images</h2>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?=media_escape(admin_csrf_token())?>">
        <input type="hidden" name="action" value="upload">

        <div class="form-grid">
            <div class="field">
                <label for="media_file">Choose image *</label>
                <input type="file" name="media_file" id="media_file" accept="image/jpeg,image/png,image/webp,image/gif" required>
                <div class="hint">JPEG, PNG, WebP or GIF. Maximum 8 MB.</div>
            </div>

            <div class="field">
                <label for="category">Media category *</label>
                <select name="category" id="category" required>
                    <option value="general">General</option>
                    <option value="portfolio">Portfolio</option>
                    <option value="gallery">Gallery</option>
                </select>
            </div>

            <div class="field">
                <label for="alt_text">Alternative text</label>
                <input type="text" name="alt_text" id="alt_text" maxlength="500" placeholder="Describe the image">
            </div>

            <div class="field">
                <label for="caption">Caption</label>
                <input type="text" name="caption" id="caption" maxlength="500" placeholder="Optional image caption">
            </div>
        </div>

        <button class="button" type="submit">Upload image</button>
    </form>
</section>

<section>
    <div class="library-heading">
        <h2>Uploaded images</h2>
        <span class="media-meta"><?=number_format($totalMedia)?> active assets</span>
    </div>

    <?php if(!$mediaItems): ?>
        <div class="empty">
            Your media library is empty.<br>
            Upload your first image to get started.
        </div>
    <?php else: ?>
        <div class="media-grid">
        <?php foreach($mediaItems as $item): ?>
            <article class="media-card">
                <img
                    src="<?=media_escape(SITE_URL . '/' . $item['relative_path'])?>"
                    alt="<?=media_escape($item['alt_text'])?>"
                    loading="lazy"
                >

                <div class="media-info">
                    <div class="media-name"><?=media_escape($item['original_filename'])?></div>
                    <div class="media-meta">
                        <?=media_escape(strtoupper($item['file_extension']))?>
                        · <?=number_format((int)$item['file_size_bytes']/1024,1)?> KB
                        · <?=media_escape($item['usage_context'])?>
                    </div>

                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?=media_escape(admin_csrf_token())?>">
                        <input type="hidden" name="media_id" value="<?=(int)$item['id']?>">
                        <input type="hidden" name="action" value="update">

                        <div class="field">
                            <label>Alternative text</label>
                            <input type="text" name="alt_text" maxlength="500" value="<?=media_escape($item['alt_text'])?>">
                        </div>

                        <div class="field">
                            <label>Caption</label>
                            <input type="text" name="caption" maxlength="500" value="<?=media_escape($item['caption'])?>">
                        </div>

                        <div class="field">
                            <label>Category</label>
                            <select name="category">
                                <option value="general" <?=$item['usage_context']==='general'?'selected':''?>>General</option>
                                <option value="portfolio" <?=$item['usage_context']==='portfolio'?'selected':''?>>Portfolio</option>
                                <option value="gallery" <?=$item['usage_context']==='gallery'?'selected':''?>>Gallery</option>
                            </select>
                        </div>

                        <div class="media-actions">
                            <button class="button secondary" type="submit">Save details</button>
                        </div>
                    </form>

                    <form method="POST" onsubmit="return confirm('Deactivate this image? The original file will be preserved.');">
                        <input type="hidden" name="csrf_token" value="<?=media_escape(admin_csrf_token())?>">
                        <input type="hidden" name="media_id" value="<?=(int)$item['id']?>">
                        <input type="hidden" name="action" value="deactivate">
                        <button class="button danger" type="submit">Deactivate</button>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
</main>
</div>
</body>
</html>
