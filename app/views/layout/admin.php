<?php
$title = $title ?? 'Admin Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= encode($title); ?> | Admin | <?= APP_NAME; ?></title>
    <link rel="icon" type="image/png" href="<?= asset('app/logo/dblogosmall.png'); ?>">
    <link rel="shortcut icon" type="image/png" href="<?= asset('app/logo/dblogosmall.png'); ?>">
    <link rel="apple-touch-icon" href="<?= asset('app/logo/dblogosmall.png'); ?>">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" 
      referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="<?= asset('public/css/main.css'); ?>">
</head>
<body>
<div class="page-wrapper">
    <?php include __DIR__ . '/_admin_header.php'; ?>

    <div id="toast-container">
        <?php if (!empty($flashSuccess)): ?>
            <div class="alert alert-success"><?= encode($flashSuccess); ?></div>
        <?php endif; ?>
        <?php if (!empty($flashDanger)): ?>
            <div class="alert alert-danger"><?= encode($flashDanger); ?></div>
        <?php endif; ?>
    </div>

    <main class="container">
        <?= $content ?? ''; ?>
    </main>
    
    <?php include __DIR__ . '/_footer.php'; ?>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="<?= asset('public/js/app.js'); ?>"></script>
</body>
</html>

