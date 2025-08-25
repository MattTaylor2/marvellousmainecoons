<?php
session_start();
if (empty($_SESSION['authenticated']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/audit.php';

$csrf_token = generate_csrf_token();
$uploadDir  = realpath(__DIR__ . '/../uploads');
$files      = array_diff(scandir($uploadDir), ['.','..']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Media Manager</title>
  <link rel="stylesheet" href="../assets/css/styles.css">
  <style>
    img { max-width: 120px; margin: 8px; }
    .thumb { display: inline-block; text-align: center; }
  </style>
</head>
<body>
  <?php include __DIR__ . '/../includes/admin-header.php'; ?>

  <h1>Media Manager</h1>

  <form id="upload-form" method="post" action="upload_image.php" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?=htmlspecialchars($csrf_token)?>">
    <input type="file" name="image" accept="image/*" required>
    <button type="submit">Upload</button>
  </form>

  <hr>

  <h2>Existing Images</h2>
  <div id="existing-images">
    <?php foreach ($files as $file): ?>
      <div class="thumb">
        <img src="../uploads/<?=urlencode($file)?>" alt="">
        <form method="post" action="delete_image.php" style="margin-top:4px;">
          <input type="hidden" name="csrf_token" value="<?=htmlspecialchars($csrf_token)?>">
          <input type="hidden" name="filename" value="<?=htmlspecialchars($file)?>">
          <button>Delete</button>
        </form>
      </div>
    <?php endforeach; ?>
  </div>

  <p><a href="dashboard.php">← Back to Dashboard</a></p>

  <?php include __DIR__ . '/../includes/footer.php'; ?>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      console.log('⏳ DOM ready – wiring upload handler');
      const uploadForm = document.getElementById('upload-form');

      uploadForm.addEventListener('submit', async e => {
        e.preventDefault();
        console.log('🚀 Submitting via AJAX…');
        const formData = new FormData(uploadForm);

        try {
          const res  = await fetch(uploadForm.action, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
          });
          const json = await res.json();
          console.log('📤 Server response:', json);

          if (!json.success) {
            alert(json.error);
            return;
          }

          // on success, reload so the new image appears
          window.location.reload();
        }
        catch (err) {
          console.error('❌ Upload error:', err);
          alert('Upload failed – see console for details');
        }
      });
    });
  </script>
</body>
</html>
