<?php
require_once 'includes/functions.php';
$db = getDb();
$galleryImages = $db->prepare("SELECT filename, caption FROM media WHERE placement = 'gallery' ORDER BY uploaded_at DESC");
$galleryImages->execute();
$images = $galleryImages->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kitten Gallery</title>
    <link rel="stylesheet" href="/css/styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #fff;
            margin: 0;
            padding: 0;
        }
        main {
            padding: 40px;
            max-width: 1200px;
            margin: auto;
        }
        h2 {
            text-align: center;
            margin-bottom: 30px;
        }
        .gallery {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }
        .card {
            width: 220px;
            background: #f9f9f9;
            border-radius: 6px;
            box-shadow: 0 0 6px rgba(0,0,0,0.1);
            padding: 10px;
            text-align: center;
        }
        .card img {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
        }
        .caption {
            margin-top: 8px;
            font-weight: bold;
            color: #333;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main>
        <h2>Kitten Gallery</h2>
        <div class="gallery">
            <?php foreach ($images as $img): ?>
                <div class="card">
                    <img src="/media/<?php echo htmlspecialchars($img['filename']); ?>" alt="Kitten">
                    <div class="caption"><?php echo htmlspecialchars($img['caption']); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
