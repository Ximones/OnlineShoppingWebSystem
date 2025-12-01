<?php
$title = $title ?? APP_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= encode($title); ?> | <?= APP_NAME; ?></title>
    <link rel="stylesheet" href="<?= asset('public/css/main.css'); ?>">
</head>
<body>
<?php include __DIR__ . '/_header.php'; ?>
<main class="container">
    <?php if (!empty($flashSuccess)): ?>
        <div class="alert alert-success"><?= encode($flashSuccess); ?></div>
    <?php endif; ?>
    <?php if (!empty($flashDanger)): ?>
        <div class="alert alert-danger"><?= encode($flashDanger); ?></div>
    <?php endif; ?>

    <?= $content ?? ''; ?>
</main>
<?php include __DIR__ . '/_footer.php'; ?>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="<?= asset('public/js/app.js'); ?>"></script>
</body>
</html>

