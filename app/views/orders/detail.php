<?php $title = 'Order #' . $order['id']; ?>

<section class="panel order-detail-header">
    <div class="order-detail-title-row">
        <div>
            <h2 style="margin-top: 0; margin-bottom: 0.25rem;">Order #<?= $order['id']; ?></h2>
            <p style="margin: 0; color: var(--color-text-muted); font-size: 0.9rem;">Placed on <?= encode($order['created_at']); ?></p>
        </div>
        <div class="order-status-badge order-status-<?= strtolower($order['status']); ?>">
            <?= encode(ucfirst($order['status'])); ?>
        </div>
    </div>
</section>

<section class="panel">
    <h2 style="margin-top: 0;">Order Items</h2>
    <div class="order-items-list">
        <?php foreach ($order['items'] as $item): ?>
            <div class="order-detail-item">
                <?php if (!empty($item['photo'])): ?>
                    <img src="<?= encode($item['photo']); ?>" 
                         alt="<?= encode($item['name']); ?>"
                         class="order-detail-thumbnail">
                <?php else: ?>
                    <div class="order-detail-thumbnail order-item-placeholder">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20 6H4C2.9 6 2 6.9 2 8V16C2 17.1 2.9 18 4 18H20C21.1 18 22 17.1 22 16V8C22 6.9 21.1 6 20 6Z" stroke="currentColor" stroke-width="2"/>
                        </svg>
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
</section>

<section class="panel">
    <h2 style="margin-top: 0;">Shipping Information</h2>
    <div class="order-shipping-info">
        <div class="order-info-row">
            <span class="order-info-label">Recipient</span>
            <span class="order-info-value"><?= encode($order['shipping_name']); ?></span>
        </div>
        <div class="order-info-row">
            <span class="order-info-label">Phone</span>
            <span class="order-info-value"><?= encode($order['shipping_phone']); ?></span>
        </div>
        <div class="order-info-row">
            <span class="order-info-label">Address</span>
            <span class="order-info-value"><?= nl2br(encode($order['shipping_address'])); ?></span>
        </div>
    </div>
</section>

<section class="panel order-total-panel">
    <div class="order-total-row">
        <span class="order-total-label">Order Total</span>
        <span class="order-total-value-large">RM <?= number_format($order['total_amount'], 2); ?></span>
    </div>
</section>

