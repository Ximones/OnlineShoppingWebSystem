<?php $title = 'Scan QR Code - Order #' . $order['id']; ?>

<section class="panel" style="max-width: 800px; margin: 2rem auto; width: 100%; box-sizing: border-box;">
    <div style="text-align: center; margin-bottom: 2rem;">
        <h2 style="margin-top: 0;">
            <i class="fas fa-qrcode" style="color: var(--color-accent); margin-right: 0.5rem;"></i>
            Order Pickup - Order #<?= $order['id']; ?>
        </h2>
        <p style="color: var(--color-text-muted);">
            Customer: <?= encode($order['customer_name']); ?>
        </p>
    </div>

    <?php
    $status = strtolower($order['status'] ?? '');
    $isPickupReady = in_array($status, ['paid', 'processing', 'picked_up'], true);
    $isCompleted = $status === 'completed';
    $isPickedUp = $status === 'picked_up';
    ?>

    <?php if ($isCompleted): ?>
        <div style="background: rgba(52, 199, 89, 0.15); border: 1px solid #34c759; border-radius: var(--radius-md); padding: 1.5rem; margin-bottom: 1.5rem; text-align: center;">
            <i class="fas fa-check-circle" style="font-size: 3rem; color: #34c759; margin-bottom: 1rem;"></i>
            <h3 style="margin: 0; color: #34c759;">Order Already Completed</h3>
            <p style="margin: 0.5rem 0 0; color: var(--color-text-muted);">This order has been picked up and completed.</p>
        </div>
    <?php elseif ($isPickedUp): ?>
        <div style="background: rgba(255, 149, 0, 0.15); border: 1px solid #ff9500; border-radius: var(--radius-md); padding: 1.5rem; margin-bottom: 1.5rem; text-align: center;">
            <i class="fas fa-box-open" style="font-size: 3rem; color: #ff9500; margin-bottom: 1rem;"></i>
            <h3 style="margin: 0; color: #ff9500;">Items Picked Up - Ready to Complete</h3>
            <p style="margin: 0.5rem 0 0; color: var(--color-text-muted);">Customer has picked up items. Confirm to complete the order.</p>
        </div>
    <?php elseif (!$isPickupReady): ?>
        <div style="background: rgba(255, 59, 48, 0.15); border: 1px solid #ff3b30; border-radius: var(--radius-md); padding: 1.5rem; margin-bottom: 1.5rem; text-align: center;">
            <i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: #ff3b30; margin-bottom: 1rem;"></i>
            <h3 style="margin: 0; color: #ff3b30;">Order Not Ready for Pickup</h3>
            <p style="margin: 0.5rem 0 0; color: var(--color-text-muted);">Current status: <?= ucfirst($status); ?></p>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;" class="pickup-info-grid">
        <!-- Order Information -->
        <div style="background: var(--color-bg-soft); border-radius: var(--radius-md); padding: 1.5rem; min-width: 0; box-sizing: border-box;">
            <h3 style="margin-top: 0; margin-bottom: 1rem; font-size: 1.1rem;">
                <i class="fas fa-shopping-bag" style="color: var(--color-accent); margin-right: 0.5rem;"></i>
                Order Information
            </h3>
            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <div>
                    <strong style="display: block; color: var(--color-text-muted); font-size: 0.85rem; margin-bottom: 0.25rem;">Order ID</strong>
                    <span style="font-size: 1.1rem; font-weight: 600;">#<?= $order['id']; ?></span>
                </div>
                <div>
                    <strong style="display: block; color: var(--color-text-muted); font-size: 0.85rem; margin-bottom: 0.25rem;">Status</strong>
                    <span class="order-status-badge order-status-<?= $status; ?>" style="display: inline-block;">
                        <?= ucfirst($status); ?>
                    </span>
                </div>
                <div>
                    <strong style="display: block; color: var(--color-text-muted); font-size: 0.85rem; margin-bottom: 0.25rem;">Total Amount</strong>
                    <span style="font-size: 1.2rem; font-weight: 600; color: var(--color-accent);">RM <?= number_format($order['total_amount'], 2); ?></span>
                </div>
                <div>
                    <strong style="display: block; color: var(--color-text-muted); font-size: 0.85rem; margin-bottom: 0.25rem;">Order Date</strong>
                    <span><?= encode($order['created_at']); ?></span>
                </div>
            </div>
        </div>

        <!-- Customer Information -->
        <div style="background: var(--color-bg-soft); border-radius: var(--radius-md); padding: 1.5rem; min-width: 0; box-sizing: border-box;">
            <h3 style="margin-top: 0; margin-bottom: 1rem; font-size: 1.1rem;">
                <i class="fas fa-user" style="color: var(--color-accent); margin-right: 0.5rem;"></i>
                Customer Information
            </h3>
            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <div>
                    <strong style="display: block; color: var(--color-text-muted); font-size: 0.85rem; margin-bottom: 0.25rem;">Name</strong>
                    <span><?= encode($order['customer_name']); ?></span>
                </div>
                <div>
                    <strong style="display: block; color: var(--color-text-muted); font-size: 0.85rem; margin-bottom: 0.25rem;">Email</strong>
                    <span><?= encode($order['customer_email']); ?></span>
                </div>
                <div>
                    <strong style="display: block; color: var(--color-text-muted); font-size: 0.85rem; margin-bottom: 0.25rem;">Phone</strong>
                    <span><?= encode($order['customer_phone']); ?></span>
                </div>
                <div>
                    <strong style="display: block; color: var(--color-text-muted); font-size: 0.85rem; margin-bottom: 0.25rem;">Recipient</strong>
                    <span><?= encode($order['shipping_name']); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Item Location Information -->
    <div style="background: linear-gradient(135deg, #f8fbff 0%, #ffffff 60%, #eef3ff 100%); border: 1px solid rgba(0, 113, 227, 0.15); border-radius: var(--radius-md); padding: 1.5rem; margin-bottom: 2rem;">
        <h3 style="margin-top: 0; margin-bottom: 1rem; font-size: 1.1rem;">
            <i class="fas fa-map-marker-alt" style="color: var(--color-accent); margin-right: 0.5rem;"></i>
            Item Location
        </h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;" class="pickup-location-grid">
            <div style="min-width: 0; box-sizing: border-box;">
                <strong style="display: block; color: var(--color-text-muted); font-size: 0.85rem; margin-bottom: 0.5rem;">Pickup Location</strong>
                <p style="margin: 0; font-size: 1rem; word-wrap: break-word; overflow-wrap: break-word;">
                    <i class="fas fa-store" style="color: var(--color-accent); margin-right: 0.5rem;"></i>
                    <?= encode($order['pickup_location'] ?? '12 Jalan Tanjungyew, Kuala Lumpur'); ?>
                </p>
            </div>
            <div style="min-width: 0; box-sizing: border-box;">
                <strong style="display: block; color: var(--color-text-muted); font-size: 0.85rem; margin-bottom: 0.5rem;">Warehouse Location</strong>
                <p style="margin: 0; font-size: 1rem; font-weight: 600; color: var(--color-accent); word-wrap: break-word; overflow-wrap: break-word;">
                    <i class="fas fa-warehouse" style="margin-right: 0.5rem;"></i>
                    <?= encode($order['item_location'] ?? 'Warehouse 1 Rack 1'); ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Order Items -->
    <div style="margin-bottom: 2rem;">
        <h3 style="margin-bottom: 1rem; font-size: 1.1rem;">
            <i class="fas fa-list" style="color: var(--color-accent); margin-right: 0.5rem;"></i>
            Order Items
        </h3>
        <div class="order-items-list">
            <?php foreach ($order['items'] as $item): ?>
                <div class="order-detail-item">
                    <?php if (!empty($item['photo'])): ?>
                        <img src="<?= encode($item['photo']); ?>" 
                             alt="<?= encode($item['name']); ?>"
                             class="order-detail-thumbnail">
                    <?php else: ?>
                        <div class="order-detail-thumbnail order-item-placeholder">
                            <i class="fas fa-box" style="font-size: 2rem; color: var(--color-text-muted);"></i>
                        </div>
                    <?php endif; ?>
                    <div class="order-detail-item-info">
                        <div class="order-detail-item-name"><?= encode($item['name']); ?></div>
                        <div class="order-detail-item-meta">
                            <span>Quantity: <?= $item['quantity']; ?></span>
                            <span>•</span>
                            <span>RM <?= number_format($item['unit_price'], 2); ?> each</span>
                        </div>
                    </div>
                    <div class="order-detail-item-total">
                        RM <?= number_format($item['unit_price'] * $item['quantity'], 2); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Confirm Pickup Form (for admin staff only) -->
    <?php if (is_admin() && ($isPickupReady || $isPickedUp)): ?>
        <div style="background: #ffffff; border: 2px solid var(--color-accent); border-radius: var(--radius-md); padding: 1.5rem;">
            <h3 style="margin-top: 0; margin-bottom: 1rem; font-size: 1.1rem; color: var(--color-accent);">
                <i class="fas fa-check-circle" style="margin-right: 0.5rem;"></i>
                Confirm Pickup
            </h3>
            <form method="post" action="?module=pickup&action=confirm_pickup" onsubmit="return confirm('Confirm pickup for Order #<?= $order['id']; ?>? This will mark the order as completed.');">
                <input type="hidden" name="order_id" value="<?= $order['id']; ?>">
                <input type="hidden" name="token" value="<?= encode($token); ?>">
                
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">
                    Remarks (optional)
                </label>
                <textarea name="remarks" rows="3" placeholder="Add any remarks about the pickup..." style="width: 100%; padding: 0.75rem; border-radius: var(--radius-xs); border: 1px solid var(--color-border-soft); margin-bottom: 1rem; resize: vertical;"></textarea>
                
                <button type="submit" class="btn primary" style="width: 100%; padding: 1rem; font-size: 1.1rem;">
                    <i class="fas fa-check"></i>
                    <?= $isPickedUp ? 'Complete Order' : 'Confirm Pickup'; ?>
                </button>
            </form>
        </div>
    <?php elseif (!is_admin()): ?>
        <div style="background: rgba(255, 149, 0, 0.15); border: 1px solid #ff9500; border-radius: var(--radius-md); padding: 1.5rem; text-align: center;">
            <i class="fas fa-lock" style="font-size: 2rem; color: #ff9500; margin-bottom: 0.5rem;"></i>
            <h3 style="margin: 0 0 0.5rem 0; color: #ff9500;">Admin Login Required</h3>
            <p style="margin: 0 0 1rem 0; color: var(--color-text-muted);">You must be logged in as an admin to confirm pickup</p>
            <a href="?module=auth&action=login&redirect=<?= urlencode('?module=pickup&action=scan&token=' . urlencode($token ?? '')); ?>" class="btn primary">
                <i class="fas fa-sign-in-alt"></i> Login as Admin
            </a>
        </div>
    <?php endif; ?>

    <div style="margin-top: 2rem; text-align: center;">
        <a href="?module=shop&action=home" class="btn secondary">
            <i class="fas fa-arrow-left"></i> Back to Home
        </a>
    </div>
</section>

<style>
/* Responsive design for pickup scan page */
.pickup-info-grid {
    grid-template-columns: 1fr 1fr;
}

.pickup-location-grid {
    grid-template-columns: 1fr 1fr;
}

@media (max-width: 640px) {
    .pickup-info-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .pickup-location-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    section.panel {
        padding: 1rem;
        margin: 1rem auto;
    }
    
    /* Ensure text doesn't overflow */
    .pickup-info-grid > div,
    .pickup-location-grid > div {
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    
    /* Fix order items on mobile */
    .order-items-list {
        width: 100%;
        overflow-x: hidden;
    }
    
    .order-detail-item {
        flex-wrap: wrap;
        padding: 0.75rem;
    }
}
</style>
