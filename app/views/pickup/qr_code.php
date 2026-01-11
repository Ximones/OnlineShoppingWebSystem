<?php $title = 'Pickup QR Code - Order #' . $order['id']; ?>

<section class="panel" style="max-width: 600px; margin: 2rem auto;">
    <div style="text-align: center;">
        <h2 style="margin-top: 0;">Self-Pickup QR Code</h2>
        <p style="color: var(--color-text-muted); margin-bottom: 1.5rem;">
            Order #<?= $order['id']; ?> • <?= encode($order['shipping_name']); ?>
        </p>

        <?php if (!empty($qrCodeDataUrl)): ?>
            <div style="background: white; padding: 1.5rem; border-radius: var(--radius-md); display: inline-block; margin-bottom: 1.5rem; box-shadow: var(--shadow-subtle); border: 1px solid var(--color-border-soft);">
                <img src="<?= $qrCodeDataUrl; ?>" alt="QR Code for Order #<?= $order['id']; ?>" style="width: 300px; height: 300px; display: block;">
            </div>
        <?php else: ?>
            <div style="padding: 2rem; background: var(--color-bg-soft); border-radius: var(--radius-md); margin-bottom: 1.5rem;">
                <p style="color: var(--color-danger);">QR code could not be generated. Please contact support.</p>
            </div>
        <?php endif; ?>

        <div style="background: linear-gradient(135deg, #f8fbff 0%, #ffffff 60%, #eef3ff 100%); border: 1px solid rgba(0, 113, 227, 0.15); border-radius: var(--radius-md); padding: 1.5rem; margin-bottom: 1.5rem; text-align: left;">
            <h3 style="margin-top: 0; margin-bottom: 1rem; font-size: 1.1rem;">Pickup Information</h3>
            
            <div style="margin-bottom: 1rem;">
                <strong style="display: block; color: var(--color-text-muted); font-size: 0.9rem; margin-bottom: 0.25rem;">Pickup Location</strong>
                <p style="margin: 0; font-size: 1rem;">
                    <i class="fas fa-map-marker-alt" style="color: var(--color-accent); margin-right: 0.5rem;"></i>
                    <?= encode($order['pickup_location'] ?? '12 Jalan Tanjungyew, Kuala Lumpur'); ?>
                </p>
            </div>

            <div style="margin-bottom: 0;">
                <strong style="display: block; color: var(--color-text-muted); font-size: 0.9rem; margin-bottom: 0.25rem;">Item Location</strong>
                <p style="margin: 0; font-size: 1rem;">
                    <i class="fas fa-warehouse" style="color: var(--color-accent); margin-right: 0.5rem;"></i>
                    <?= encode($order['item_location'] ?? 'Warehouse 1 Rack 1'); ?>
                </p>
            </div>
        </div>

        <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 1rem; border-radius: var(--radius-xs); margin-bottom: 1.5rem; text-align: left;">
            <strong style="display: block; margin-bottom: 0.5rem; color: #856404;">
                <i class="fas fa-info-circle" style="margin-right: 0.5rem;"></i>
                Instructions
            </strong>
            <ul style="margin: 0; padding-left: 1.5rem; color: #856404; font-size: 0.9rem;">
                <li>Present this QR code to our staff at the pickup location</li>
                <li>Staff will scan the code to retrieve your order</li>
                <li>Please bring a valid ID for verification</li>
                <li>Order status will be updated to "Completed" after pickup</li>
            </ul>
        </div>

        <div style="display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap;">
            <a href="?module=orders&action=detail&id=<?= $order['id']; ?>" class="btn secondary">
                <i class="fas fa-arrow-left"></i> Back to Order
            </a>
            <button onclick="window.print()" class="btn primary">
                <i class="fas fa-print"></i> Print QR Code
            </button>
        </div>
    </div>
</section>

<style>
@media print {
    .site-header,
    .site-footer,
    .btn,
    .mobile-menu-toggle,
    .nav {
        display: none !important;
    }
    
    .panel {
        box-shadow: none;
        border: none;
        page-break-inside: avoid;
    }
}
</style>
