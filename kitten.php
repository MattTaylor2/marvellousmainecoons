<?php
session_start();

// Temporarily enable error display (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Load our DB helper
require_once __DIR__ . '/includes/db.php';
$pdo = getDb();

// Validate and fetch `id` parameter
$kittenId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$kittenId) {
    header('Location: /kittens.php');
    exit;
}

// Fetch the single kitten
$stmt = $pdo->prepare("
    SELECT id, name, dob, gender, bio, image_filename, available, created_at
    FROM kittens
    WHERE id = :id
");
$stmt->execute([':id' => $kittenId]);
$kitten = $stmt->fetch();

if (!$kitten) {
    header('Location: /kittens.php');
    exit;
}

// Determine image URL
$fn   = $kitten['image_filename'];
$path = __DIR__ . "/images/kittens/$fn";
$img  = ($fn && file_exists($path))
      ? "/images/kittens/" . htmlspecialchars($fn)
      : "/images/kitten-placeholder.png";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($kitten['name']); ?> — Marvellous Maine Coons</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <?php include __DIR__ . '/header.php'; ?>

    <main class="kitten-detail">
        <a href="/kittens.php" class="back-link">&larr; Back to all kittens</a>

        <div class="detail-card">
            <img src="<?php echo $img; ?>"
                 alt="<?php echo htmlspecialchars($kitten['name']); ?>"
                 class="detail-image" />

            <div class="detail-info">
                <h1><?php echo htmlspecialchars($kitten['name']); ?></h1>

                <?php if ($kitten['dob']): 
                    $dob      = new DateTime($kitten['dob']);
                    $today    = new DateTime();
                    $age      = $dob->diff($today);
                    $ageText  = $age->y
                              ? $age->y . ' year' . ($age->y > 1 ? 's' : '')
                              : $age->m . ' month' . ($age->m > 1 ? 's' : '');
                ?>
                    <p><strong>Born:</strong>
                        <?php echo $dob->format('j M Y'); ?>
                        (<?php echo $ageText; ?> old)
                    </p>
                <?php endif; ?>

                <p><strong>Gender:</strong>
                    <?php echo htmlspecialchars($kitten['gender']); ?>
                </p>

                <p><?php echo nl2br(htmlspecialchars($kitten['bio'])); ?></p>

                <p class="status <?php echo $kitten['available'] ? 'in-stock' : 'sold-out'; ?>">
                    <?php echo $kitten['available'] ? 'Available' : 'Reserved'; ?>
                </p>

                <?php if ($kitten['available']): ?>
                    <a href="/reserve.php?kitten_id=<?php echo $kitten['id']; ?>"
                       class="btn reserve-btn">
                        Reserve This Kitten
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/footer.php'; ?>

</body>
</html>
