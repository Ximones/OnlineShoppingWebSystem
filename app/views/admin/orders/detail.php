<?php $title = 'Admin – Order #' . $order['id']; ?>
<?php
$statusSteps = ['pending', 'processing', 'shipped', 'completed'];
$currentStatus = strtolower($order['status']);
if ($currentStatus === 'cancelled' && !in_array('cancelled', $statusSteps, true)) {
    $statusSteps[] = 'cancelled';
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
            <form method="post" action="?module=admin&resource=orders&action=updateStatus" style="display:flex; gap:0.4rem; align-items:center;">
                <input type="hidden" name="order_id" value="<?= $order['id']; ?>">
                <select name="status" style="font-size: 0.85rem; padding: 0.15rem 0.3rem;">
                    <?php foreach ($GLOBALS['_order_statuses'] as $code => $text): ?>
                        <option value="<?= $code; ?>" <?= strtolower($order['status']) === $code ? 'selected' : ''; ?>>
                            <?= $text; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button class="btn small secondary" type="submit">Update</button>
            </form>
        </div>
    </div>

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

    <?php if (!empty($order['tracking'])): ?>
        <div class="order-tracking-summary">
            <?php $latestTracking = end($order['tracking']); ?>
            <div class="order-info-row">
                <span class="order-info-label">Latest update</span>
                <span class="order-info-value">
                    <?= encode($latestTracking['status']); ?>
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

<?php if (!empty($order['tracking'])): ?>
<section class="panel">
    <h2 style="margin-top: 0;">Tracking History (Admin)</h2>
    <div class="order-shipping-info">
        <?php foreach ($order['tracking'] as $tracking): ?>
            <div class="order-info-row">
                <span class="order-info-label"><?= encode($tracking['tracking_date']); ?></span>
                <span class="order-info-value">
                    <strong><?= encode($tracking['status']); ?></strong>
                    <?php if (!empty($tracking['location'])): ?>
                        • <?= encode($tracking['location']); ?>
                    <?php endif; ?>
                    <?php if (!empty($tracking['remarks'])): ?>
                        <span style="display:block; font-size:0.85rem; color:var(--color-text-muted);">
                            <?= nl2br(encode($tracking['remarks'])); ?>
                        </span>
                    <?php endif; ?>
                </span>
                <form method="post" action="?module=admin&resource=orders&action=deleteTracking" style="margin-top:0.25rem;">
                    <input type="hidden" name="order_id" value="<?= $order['id']; ?>">
                    <input type="hidden" name="tracking_id" value="<?= $tracking['id']; ?>">
                    <button type="submit" class="btn small secondary" onclick="return confirm('Delete this tracking entry?');">
                        Delete
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>

    <form method="post" action="?module=admin&resource=orders&action=addTracking" style="margin-top: 1rem;">
        <input type="hidden" name="order_id" value="<?= $order['id']; ?>">
        <h3 style="margin-top: 0;">Add Tracking Entry</h3>
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
</section>
<?php endif; ?>


