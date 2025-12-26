<?php $title = 'Order #' . $order['id']; ?>
<?php
$statusSteps = ['pending', 'processing', 'shipped', 'completed'];
$currentStatus = strtolower($order['status']);
if ($currentStatus === 'cancelled' && !in_array('cancelled', $statusSteps, true)) {
    $statusSteps[] = 'cancelled';
}
?>

<div class="order-detail-grid">
<?php if (req('action') !== 'admin_detail'): ?>
<section class="panel order-detail-header">
    <div class="order-detail-title-row">
        <div>
            <h2 style="margin-top: 0; margin-bottom: 0.25rem;">Order #<?= $order['id']; ?></h2>
            <p style="margin: 0; color: var(--color-text-muted); font-size: 0.9rem;">Placed on <?= encode($order['created_at']); ?></p>
        </div>
        <div class="order-detail-header-actions">
            <div class="order-status-badge order-status-<?= strtolower($order['status']); ?>">
                <?= encode(ucfirst($order['status'])); ?>
            </div>
            <div class="order-detail-buttons">
                <?php if (!is_admin()): ?>
                    <form method="post" action="?module=orders&action=reorder&id=<?= $order['id']; ?>" style="display:inline-block; margin-left: 0.5rem;">
                        <button type="submit" class="btn secondary small">Order Again</button>
                    </form>
                    <?php if (in_array($currentStatus, ['pending', 'processing'], true)): ?>
                        <form method="post" action="?module=orders&action=cancel&id=<?= $order['id']; ?>" style="display:inline-block; margin-left: 0.25rem;" onsubmit="return confirm('Cancel this order?');">
                            <button type="submit" class="btn small">Cancel Order</button>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
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
<?php endif; ?>

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
    <h2 style="margin-top: 0;">Order Summary</h2>
    <div class="order-summary-breakdown">
        <div class="order-summary-row">
            <span class="order-summary-label">Subtotal</span>
            <span class="order-summary-value">RM <?= number_format($order['subtotal'] ?? 0, 2); ?></span>
        </div>
        
        <?php 
        // Calculate total discounts to show
        $totalDiscounts = ($order['voucher_discount'] ?? 0) + ($order['points_discount'] ?? 0);
        $subtotalAfterDiscounts = ($order['subtotal'] ?? 0) - $totalDiscounts;
        ?>
        
        <?php if (($order['voucher_discount'] ?? 0) > 0): ?>
            <div class="order-summary-row order-summary-discount">
                <span class="order-summary-label">
                    Voucher Discount
                    <?php if (!empty($order['voucher'])): ?>
                        <span class="order-voucher-code">(<?= encode($order['voucher']['code']); ?>)</span>
                    <?php endif; ?>
                </span>
                <span class="order-summary-value">- RM <?= number_format($order['voucher_discount'], 2); ?></span>
            </div>
        <?php endif; ?>
        
        <?php if (($order['points_discount'] ?? 0) > 0): ?>
            <div class="order-summary-row order-summary-discount">
                <span class="order-summary-label">Reward Points Discount</span>
                <span class="order-summary-value">- RM <?= number_format($order['points_discount'], 2); ?></span>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($order['voucher']) && ($order['voucher']['type'] === 'shipping_amount' || $order['voucher']['type'] === 'free_shipping')): ?>
            <div class="order-summary-row order-summary-discount">
                <span class="order-summary-label">
                    Shipping Discount
                    <span class="order-voucher-code">(<?= encode($order['voucher']['code']); ?>)</span>
                </span>
                <span class="order-summary-value">- RM <?= number_format($order['shipping_voucher_discount'] ?? 0, 2); ?></span>
            </div>
        <?php endif; ?>
        
        <div class="order-summary-row">
            <span class="order-summary-label">
                Shipping
                <span class="order-shipping-method">(<?= encode($order['shipping_method'] ?? 'Standard Shipping'); ?>)</span>
            </span>
            <span class="order-summary-value">RM <?= number_format($order['shipping_fee'] ?? 0, 2); ?></span>
        </div>
        
        <div class="order-summary-divider"></div>
        
        <div class="order-summary-row order-summary-total">
            <span class="order-summary-label">Total</span>
            <span class="order-summary-value-large">RM <?= number_format($order['total_amount'], 2); ?></span>
        </div>
    </div>
</section>

<section class="panel">
    <h2 style="margin-top: 0;">Payment Method</h2>
    <div class="order-shipping-info">
                <?php 
                $payLaterPayments = [];
                $otherPayments = [];
                foreach ($order['payments'] as $payment) {
                    // Detect PayLater payments by checking for PayLater-specific fields
                    // This works even if payment_method was updated after payment
                    if ($payment['payment_method'] === 'PayLater' || 
                        !empty($payment['billing_due_date']) || 
                        !empty($payment['tenure_months'])) {
                        $payLaterPayments[] = $payment;
                    } else {
                        $otherPayments[] = $payment;
                    }
                }
                ?>
                
                <?php if (!empty($otherPayments)): ?>
                    <?php foreach ($otherPayments as $payment): ?>
                        <div class="order-info-row">
                            <span class="order-info-label">Payment Method</span>
                            <span class="order-info-value">
                                <?php 
                                // Simplify Stripe payment methods to just "Stripe"
                                $method = $payment['payment_method'];
                                if (strpos($method, 'Stripe') === 0) {
                                    echo 'Stripe';
                                } else {
                                    echo encode($method);
                                }
                                ?>
                            </span>
                        </div>
                        <div class="order-info-row">
                            <span class="order-info-label">Amount</span>
                            <span class="order-info-value">RM <?= number_format($payment['amount'], 2); ?></span>
                        </div>
                        <?php if (!empty($payment['transaction_ref'])): ?>
                            <div class="order-info-row">
                                <span class="order-info-label">Transaction Reference</span>
                                <span class="order-info-value"><?= encode($payment['transaction_ref']); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if ($payment !== end($otherPayments)): ?>
                            <div style="height: 1px; background: var(--color-border); margin: 0.75rem 0;"></div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <?php if (!empty($payLaterPayments)): ?>
                    <?php if (!empty($otherPayments)): ?>
                        <div style="height: 1px; background: var(--color-border); margin: 0.75rem 0;"></div>
                    <?php endif; ?>
                    <div class="order-info-row">
                        <span class="order-info-label">Payment Method</span>
                        <span class="order-info-value">PayLater</span>
                    </div>
                    <div style="margin-top: 1rem;">
                        <a href="?module=bills&action=index" class="btn secondary">See more in PayLater</a>
                    </div>
                <?php endif; ?>
    </div>
    </section>

<section class="panel">
    <h2 style="margin-top: 0;">Comment</h2>
    <?php if (strtolower($order['status']) !== 'completed'): ?>
        <div class="order-shipping-info">
            <div class="order-info-row">
                <span class="order-info-value">
                    <em>You can leave product ratings and comments once this order is completed.</em>
                </span>
            </div>
        </div>
    <?php else: ?>
        <div class="order-shipping-info">
            <?php foreach ($order['items'] as $item): ?>
                <div class="order-info-row">
                    <span class="order-info-label"><?= encode($item['name']); ?></span>
                    <span class="order-info-value">
                        <?php if (!empty($item['user_review'])): ?>
                            <div class="order-review-existing">
                                <span class="rating-stars readonly">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star<?= $i <= (int)$item['user_review']['rating'] ? ' filled' : ''; ?>"></i>
                                    <?php endfor; ?>
                                </span>
                                <p style="margin: 0.25rem 0 0;"><?= nl2br(encode($item['user_review']['comment'])); ?></p>
                            </div>
                        <?php else: ?>
                            <form method="post" action="?module=orders&action=add_review" class="order-review-form">
                                <input type="hidden" name="order_id" value="<?= $order['id']; ?>">
                                <input type="hidden" name="product_id" value="<?= $item['product_id']; ?>">
                                <?php $baseId = 'rating-' . $order['id'] . '-' . $item['product_id']; ?>
                                <div class="rating-stars">
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                        <?php $inputId = $baseId . '-' . $i; ?>
                                        <input type="radio" id="<?= $inputId; ?>" name="rating" value="<?= $i; ?>" <?= $i === 5 ? 'checked' : ''; ?>>
                                        <label for="<?= $inputId; ?>"><i class="fas fa-star"></i></label>
                                    <?php endfor; ?>
                                </div>
                                <label style="margin-top:0.35rem; display:block;">
                                    <span style="display:block; font-size:0.85rem; margin-bottom:0.25rem;">Comment</span>
                                    <textarea name="comment" rows="2" required></textarea>
                                </label>
                                <button type="submit" class="btn small secondary" style="margin-top:0.35rem;">Submit review</button>
                            </form>
                        <?php endif; ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php if (!empty($order['tracking']) && req('action') !== 'admin_detail'): ?>
<section class="panel">
    <h2 style="margin-top: 0;">Tracking History</h2>
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
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>
</div>

