<?php
session_start();

// Temporarily show errors (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/includes/db.php';
$pdo = getDb();

$stmt    = $pdo->query("
    SELECT id, name, dob, gender, bio, image_filename, available
    FROM kittens
    ORDER BY created_at DESC
");
$kittens = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Our Maine Coon Kittens</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <?php include __DIR__ . '/header.php'; ?>

    <main>
        <h1>Available Kittens</h1>

        <div class="kitten-grid">

            <?php if (empty($kittens)): ?>
                <p class="empty-state">
                    No kittens are available right now. Please check back soon!
                </p>
            <?php else: ?>
                <?php foreach ($kittens as $kitten):
                    // Image or placeholder
                    $fn   = $kitten['image_filename'];
                    $path = __DIR__ . "/images/kittens/$fn";
                    $img  = ($fn && file_exists($path))
                          ? "/images/kittens/" . htmlspecialchars($fn)
                          : "/images/kitten-placeholder.png";

                    // 100-char bio excerpt
                    $bio = $kitten['bio'] ?? '';
                    if (function_exists('mb_strimwidth')) {
                        $excerpt = mb_strimwidth($bio, 0, 100, '…');
                    } else {
                        $excerpt = (strlen($bio) > 100)
                                   ? substr($bio, 0, 100) . '…'
                                   : $bio;
                    }
                ?>
                <div class="kitten-card">
                    <a href="kitten.php?id=<?php echo $kitten['id']; ?>">
                        <img
                            src="<?php echo $img; ?>"
                            alt="<?php echo htmlspecialchars($kitten['name']); ?>"
                        >
                    </a>
                    <h2><?php echo htmlspecialchars($kitten['name']); ?></h2>
                    <p><?php echo htmlspecialchars($excerpt); ?></p>
                    <p class="status <?php echo $kitten['available'] ? 'in-stock' : 'sold-out'; ?>">
                        <?php echo $kitten['available'] ? 'Available' : 'Reserved'; ?>
                    </p>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>
    </main>

    <?php include __DIR__ . '/footer.php'; ?>

</body>
</html>
