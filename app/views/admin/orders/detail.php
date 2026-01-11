<?php $title = 'Admin – Order #' . $order['id']; ?>
<?php
$statusSteps = ['pending', 'processing', 'shipped', 'completed'];
$currentStatus = strtolower($order['status']);
$isPickupOrder = stripos($order['shipping_method'] ?? '', 'pickup') !== false;

// For pickup orders, replace 'shipped' with 'picked_up' in the status steps
if ($isPickupOrder) {
    $statusSteps = ['pending', 'processing', 'picked_up', 'completed'];
}

if ($currentStatus === 'cancelled' && !in_array('cancelled', $statusSteps, true)) {
    $statusSteps[] = 'cancelled';
}

// If status is picked_up but not in steps, add it
if ($currentStatus === 'picked_up' && !in_array('picked_up', $statusSteps, true)) {
    array_splice($statusSteps, 2, 0, 'picked_up'); // Insert after processing
}
?>

<section class="panel order-detail-header">
    <div class="order-detail-title-row">
        <div>
            <h2 style="margin-top: 0; margin-bottom: 0.25rem;">Order #<?= $order['id']; ?></h2>
            <p style="margin: 0; color: var(--color-text-muted); font-size: 0.9rem;">
                Placed on <?= encode($order['created_at']); ?> by <?= encode($order['shipping_name']); ?>
            </p>
        </div>
        <div class="order-detail-header-actions">
            <div class="order-status-badge order-status-<?= strtolower($order['status']); ?>">
                <?= encode(ucfirst($order['status'])); ?>
            </div>
            <div class="order-detail-actions-mobile">
                <a href="?module=admin&resource=orders&action=download_receipt&id=<?= $order['id']; ?>" class="btn secondary small">
                    <i class="fas fa-download"></i> Download Receipt
                </a>
                <form method="post" action="?module=admin&resource=orders&action=reorder&id=<?= $order['id']; ?>" style="display:inline-block;">
                    <button type="submit" class="btn secondary small">Order Again</button>
                </form>
                <form method="post" action="?module=admin&resource=orders&action=updateStatus" class="order-status-update-form">
                    <input type="hidden" name="order_id" value="<?= $order['id']; ?>">
                    <select name="status" class="order-status-select">
                        <?php foreach ($GLOBALS['_order_statuses'] as $code => $text): ?>
                            <option value="<?= $code; ?>" <?= strtolower($order['status']) === $code ? 'selected' : ''; ?>>
                                <?= $text; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn small secondary" type="submit">Update Status</button>
                </form>
            </div>
        </div>
    </div>

    <div class="order-status-timeline-wrapper">
        <div class="order-status-timeline">
            <?php foreach ($statusSteps as $step): ?>
                <?php
                $stepLabel = ucfirst($step);
                $stepClass = 'upcoming';
                if ($step === $currentStatus) {
                    $stepClass = 'current';
                } elseif (array_search($step, $statusSteps, true) < array_search($currentStatus, $statusSteps, true)) {
                    $stepClass = 'completed';
                }
                ?>
                <div class="order-status-step order-status-step-<?= $stepClass; ?>">
                    <div class="order-status-dot"></div>
                    <div class="order-status-text"><?= encode($stepLabel); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if (!empty($order['tracking'])): ?>
        <div class="order-tracking-summary">
            <?php $latestTracking = end($order['tracking']); ?>
            <div class="order-info-row">
                <span class="order-info-label">Latest update</span>
                <span class="order-info-value">
                    <?= encode(ucfirst(str_replace('_', ' ', $latestTracking['status']))); ?>
                    <?php if (!empty($latestTracking['location'])): ?>
                        • <?= encode($latestTracking['location']); ?>
                    <?php endif; ?>
                    <span style="display:block; font-size:0.8rem; color:var(--color-text-muted);">
                        <?= encode($latestTracking['tracking_date']); ?>
                    </span>
                </span>
            </div>
        </div>
    <?php endif; ?>
</section>

<?php include __DIR__ . '/../../orders/detail.php'; ?>

<?php if ($isPickupOrder): ?>
<?php
    $isPickupReady = in_array($currentStatus, ['paid', 'processing', 'picked_up'], true);
    $isCompleted = $currentStatus === 'completed';
    $isPickedUp = $currentStatus === 'picked_up';
?>
<section class="panel" style="margin-top: 3rem;">
    <h2 style="margin-top: 0;">Pickup Confirmation</h2>
    <div class="order-shipping-info">
        <?php if ($isCompleted): ?>
            <div class="order-info-row">
                <span class="order-info-label">Status</span>
                <span class="order-info-value">
                    <span style="color: #34c759; font-weight: 500;">
                        <i class="fas fa-check-circle" style="margin-right: 0.5rem;"></i>
                        Order Completed
                    </span>
                    <span style="display:block; font-size:0.85rem; color:var(--color-text-muted); margin-top: 0.25rem;">
                        This order has been picked up and completed.
                    </span>
                </span>
            </div>
        <?php elseif (!$isPickupReady): ?>
            <div class="order-info-row">
                <span class="order-info-label">Status</span>
                <span class="order-info-value">
                    <span style="color: #ff3b30; font-weight: 500;">
                        <i class="fas fa-exclamation-triangle" style="margin-right: 0.5rem;"></i>
                        Order Not Ready for Pickup
                    </span>
                    <span style="display:block; font-size:0.85rem; color:var(--color-text-muted); margin-top: 0.25rem;">
                        Current status: <?= ucfirst($currentStatus); ?>
                    </span>
                </span>
            </div>
        <?php else: ?>
            <?php if ($isPickedUp): ?>
                <div class="order-info-row">
                    <span class="order-info-label">Status</span>
                    <span class="order-info-value">
                        <span style="color: #ff9500; font-weight: 500;">
                            <i class="fas fa-box-open" style="margin-right: 0.5rem;"></i>
                            Items Picked Up
                        </span>
                        <span style="display:block; font-size:0.85rem; color:var(--color-text-muted); margin-top: 0.25rem;">
                            Customer has picked up items. Ready to complete the order.
                        </span>
                    </span>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($order['qr_code_token'])): ?>
                <div class="order-info-row">
                    <span class="order-info-label">QR Code Token</span>
                    <span class="order-info-value" style="font-family: 'Courier New', monospace; font-size: 0.9rem; word-break: break-all;">
                        <?= encode($order['qr_code_token']); ?>
                    </span>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($order['item_location'])): ?>
                <div class="order-info-row">
                    <span class="order-info-label">Item Location</span>
                    <span class="order-info-value">
                        <i class="fas fa-warehouse" style="color: var(--color-accent); margin-right: 0.5rem;"></i>
                        <?= encode($order['item_location']); ?>
                    </span>
                </div>
            <?php endif; ?>
            
            <div class="order-info-row" style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--color-border-soft);">
                <span class="order-info-label"></span>
                <span class="order-info-value">
                    <form method="post" action="?module=pickup&action=confirm_pickup" onsubmit="return confirm('Confirm that customer has picked up Order #<?= $order['id']; ?>? This will mark the order as <?= $isPickedUp ? 'completed' : 'picked up'; ?>.');" style="margin: 0;">
                        <input type="hidden" name="order_id" value="<?= $order['id']; ?>">
                        <input type="hidden" name="token" value="<?= encode($order['qr_code_token'] ?? ''); ?>">
                        
                        <div style="margin-bottom: 1rem;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500; font-size: 0.9rem;">
                                Remarks (optional)
                            </label>
                            <textarea name="remarks" rows="2" placeholder="Add any remarks about the pickup..." style="width: 100%; padding: 0.75rem; border-radius: var(--radius-xs); border: 1px solid var(--color-border-soft); resize: vertical; font-size: 0.9rem;"></textarea>
                        </div>
                        
                        <button type="submit" class="btn primary" style="width: 100%; padding: 0.85rem 1.5rem; font-size: 1rem;">
                            <i class="fas fa-check-circle" style="margin-right: 0.5rem;"></i>
                            <?= $isPickedUp ? 'Complete Order' : 'Customer Picked Up'; ?>
                        </button>
                    </form>
                </span>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($order['tracking'])): ?>
<section class="panel tracking-history-section">
    <div class="tracking-history-header">
        <h2 style="margin-top: 0; margin-bottom: 0;">Tracking History (Admin)</h2>
        <button class="tracking-toggle-btn mobile-only" aria-label="Toggle tracking history" aria-expanded="false">
            <i class="fas fa-chevron-down"></i>
        </button>
    </div>
    <div class="tracking-history-content">
        <div class="order-shipping-info">
            <?php foreach ($order['tracking'] as $tracking): ?>
                <div class="order-info-row tracking-entry">
                    <span class="order-info-label"><?= encode($tracking['tracking_date']); ?></span>
                    <span class="order-info-value">
                        <strong><?= encode(ucfirst(str_replace('_', ' ', $tracking['status']))); ?></strong>
                        <?php if (!empty($tracking['location'])): ?>
                            • <?= encode($tracking['location']); ?>
                        <?php endif; ?>
                        <?php if (!empty($tracking['remarks'])): ?>
                            <span style="display:block; font-size:0.85rem; color:var(--color-text-muted); margin-top: 0.25rem;">
                                <?= nl2br(encode($tracking['remarks'])); ?>
                            </span>
                        <?php endif; ?>
                    </span>
                    <form method="post" action="?module=admin&resource=orders&action=deleteTracking" class="tracking-delete-form">
                        <input type="hidden" name="order_id" value="<?= $order['id']; ?>">
                        <input type="hidden" name="tracking_id" value="<?= $tracking['id']; ?>">
                        <button type="submit" class="btn small secondary" onclick="return confirm('Delete this tracking entry?');">
                            Delete
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>

        <form method="post" action="?module=admin&resource=orders&action=addTracking" class="tracking-add-form">
            <input type="hidden" name="order_id" value="<?= $order['id']; ?>">
            <h3 style="margin-top: 1rem; margin-bottom: 0.75rem;">Add Tracking Entry</h3>
            <div class="order-shipping-info">
                <div class="order-info-row">
                    <span class="order-info-label">Status</span>
                    <span class="order-info-value">
                        <input type="text" name="tracking_status" required>
                    </span>
                </div>
                <div class="order-info-row">
                    <span class="order-info-label">Location</span>
                    <span class="order-info-value">
                        <input type="text" name="tracking_location">
                    </span>
                </div>
                <div class="order-info-row">
                    <span class="order-info-label">Remarks</span>
                    <span class="order-info-value">
                        <textarea name="tracking_remarks" rows="2"></textarea>
                    </span>
                </div>
            </div>
            <button type="submit" class="btn secondary small">Add Tracking</button>
        </form>
    </div>
</section>
<?php endif; ?>


