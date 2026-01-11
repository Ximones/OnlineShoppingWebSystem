<?php $title = 'Invalid QR Code'; ?>

<section class="panel" style="max-width: 500px; margin: 4rem auto; text-align: center;">
    <div style="background: rgba(255, 59, 48, 0.1); border: 2px solid #ff3b30; border-radius: 50%; width: 120px; height: 120px; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem;">
        <i class="fas fa-times-circle" style="font-size: 4rem; color: #ff3b30;"></i>
    </div>
    
    <h2 style="margin-top: 0; color: #ff3b30;">Invalid QR Code</h2>
    <p style="color: var(--color-text-muted); margin-bottom: 1rem;">
        The QR code you scanned is invalid or the order could not be found.
        Please verify the QR code and try again.
    </p>
    <?php if (!empty($token ?? '')): ?>
        <p style="color: var(--color-text-muted); font-size: 0.9rem; margin-bottom: 2rem; font-family: 'Courier New', monospace; background: var(--color-bg-soft); padding: 0.75rem; border-radius: var(--radius-xs);">
            Token: <?= encode($token); ?>
        </p>
    <?php endif; ?>
    
    <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
        <a href="?module=pickup&action=scan" class="btn primary">
            <i class="fas fa-qrcode"></i> Try Again
        </a>
        <a href="?module=shop&action=home" class="btn secondary">
            <i class="fas fa-home"></i> Go to Home
        </a>
    </div>
</section>
