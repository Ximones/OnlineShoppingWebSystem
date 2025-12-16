<?php $title = 'Order History'; ?>

<?php if (empty($orders)): ?>
    <section class="panel">
        <p style="color: var(--color-text-muted);">You have no orders yet.</p>
    </section>
<?php else: ?>
    <div class="orders-grid">
        <?php foreach ($orders as $order): ?>
            <div class="order-card">
                <div class="order-card-header">
                    <div class="order-card-info">
                        <div class="order-card-title">Order #<?= $order['id']; ?></div>
                        <div class="order-card-date"><?= encode($order['created_at']); ?></div>
                    </div>
                    <div class="order-status-badge order-status-<?= strtolower($order['status']); ?>">
                        <?= encode(ucfirst($order['status'])); ?>
                    </div>
                </div>

                <div class="order-card-items">
                    <?php if (!empty($order['items'])): ?>
                        <div class="order-items-preview">
                            <?php foreach ($order['items'] as $item): ?>
                                <div class="order-item-preview">
                                    <?php if (!empty($item['photo'])): ?>
                                        <img src="<?= encode($item['photo']); ?>" 
                                             alt="<?= encode($item['name']); ?>"
                                             class="order-item-thumbnail">
                                    <?php else: ?>
                                        <div class="order-item-thumbnail order-item-placeholder">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M20 6H4C2.9 6 2 6.9 2 8V16C2 17.1 2.9 18 4 18H20C21.1 18 22 17.1 22 16V8C22 6.9 21.1 6 20 6Z" stroke="currentColor" stroke-width="2"/>
                                            </svg>
                                        </div>
                                    <?php endif; ?>
                                    <span class="order-item-name"><?= encode($item['name']); ?></span>
                                    <?php if ($item['quantity'] > 1): ?>
                                        <span class="order-item-qty">×<?= $item['quantity']; ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                            <?php if (($order['total_items'] ?? 0) > 3): ?>
                                <div class="order-item-more">+<?= ($order['total_items'] ?? 0) - 3; ?> more</div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="order-card-footer">
                    <div class="order-card-total">
                        <span class="order-total-label">Total</span>
                        <span class="order-total-value">RM <?= number_format($order['total_amount'], 2); ?></span>
                    </div>
                    <a href="?module=orders&action=detail&id=<?= $order['id']; ?>" class="btn secondary btn small">
                        View Details
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

