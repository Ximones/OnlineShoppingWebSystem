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

<?php if (!empty($order['payments'])): ?>
<section class="panel">
    <h2 style="margin-top: 0;">Payment Information</h2>
    <div class="order-shipping-info">
        <?php 
        $payLaterPayments = [];
        $otherPayments = [];
        foreach ($order['payments'] as $payment) {
            if ($payment['payment_method'] === 'PayLater') {
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
                        <?= encode($payment['payment_method']); ?>
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
            <?php if (count($payLaterPayments) > 1 && !empty($payLaterPayments[0]['tenure_months'])): ?>
                <div class="order-info-row">
                    <span class="order-info-label">PayLater Plan</span>
                    <span class="order-info-value">
                        <?= count($payLaterPayments); ?> instalments (<?= $payLaterPayments[0]['tenure_months']; ?> months)
                    </span>
                </div>
            <?php elseif (count($payLaterPayments) === 1 && !empty($payLaterPayments[0]['tenure_months'])): ?>
                <div class="order-info-row">
                    <span class="order-info-label">PayLater Plan</span>
                    <span class="order-info-value">
                        1 instalment (<?= $payLaterPayments[0]['tenure_months']; ?> month)
                    </span>
                </div>
            <?php else: ?>
                <div class="order-info-row">
                    <span class="order-info-label">PayLater Plan</span>
                    <span class="order-info-value">
                        <?= count($payLaterPayments); ?> instalment<?= count($payLaterPayments) > 1 ? 's' : ''; ?>
                    </span>
                </div>
            <?php endif; ?>
            <div style="margin-top: 1rem;">
                <a href="?module=bills&action=index" class="btn secondary">See more in PayLater</a>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

