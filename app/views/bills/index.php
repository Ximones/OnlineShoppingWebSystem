<?php $title = 'PayLater'; ?>

<section class="panel paylater-summary">
    <h2 style="margin-top: 0; margin-bottom: 0.75rem;">PayLater overview</h2>
    <p style="margin-top: 0; color: var(--color-text-muted); font-size: 0.9rem;">
        Track your PayLater credit limit and upcoming instalments.
    </p>

    <div class="paylater-summary-grid">
        <div class="paylater-summary-card">
            <div class="paylater-label">Total credit limit</div>
            <div class="paylater-value">RM <?= number_format($originalLimit ?? 0, 2); ?></div>
        </div>
        <div class="paylater-summary-card">
            <div class="paylater-label">Used (outstanding principal)</div>
            <div class="paylater-value">RM <?= number_format($usedPrincipal ?? 0, 2); ?></div>
        </div>
        <div class="paylater-summary-card">
            <div class="paylater-label">Available to spend</div>
            <div class="paylater-value">RM <?= number_format($availableLimit ?? 0, 2); ?></div>
        </div>
    </div>
</section>

<section class="panel paylater-tabs-panel">
    <div class="paylater-tabs">
        <button type="button"
                class="paylater-tab-btn is-active"
                data-target="paylater-tab-upcoming">
            Upcoming
        </button>
        <button type="button"
                class="paylater-tab-btn"
                data-target="paylater-tab-history">
            History
        </button>
    </div>

    <div id="paylater-tab-upcoming" class="paylater-tab-content is-active">
        <?php if (empty($bills)): ?>
            <p>You have no outstanding PayLater bills.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                <tr>
                    <th>Order #</th>
                    <th>Payment Method</th>
                    <th>Amount</th>
                    <th>Billing Date</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($bills as $bill): ?>
                    <tr>
                        <td><?= (int) $bill['order_id']; ?></td>
                        <td><?= encode($bill['payment_method']); ?></td>
                        <td>RM <?= number_format($bill['amount'], 2); ?></td>
                        <td><?= encode($bill['billing_due_date'] ?? ''); ?></td>
                        <td><?= encode(ucfirst($bill['status'])); ?></td>
                        <td><?= encode($bill['payment_date']); ?></td>
                        <td>
                            <form method="post" action="?module=bills&action=pay" style="margin: 0;">
                                <input type="hidden" name="payment_id" value="<?= (int) $bill['id']; ?>">
                                <button type="submit" class="btn primary btn small">Pay Now</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div id="paylater-tab-history" class="paylater-tab-content">
        <?php if (empty($history)): ?>
            <p>You have no completed PayLater payments yet.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                <tr>
                    <th>Order #</th>
                    <th>Payment Method</th>
                    <th>Amount</th>
                    <th>Billing Date</th>
                    <th>Status</th>
                    <th>Paid At</th>
                    <th>Transaction Ref</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($history as $bill): ?>
                    <tr>
                        <td><?= (int) $bill['order_id']; ?></td>
                        <td><?= encode($bill['payment_method']); ?></td>
                        <td>RM <?= number_format($bill['amount'], 2); ?></td>
                        <td><?= encode($bill['billing_due_date'] ?? ''); ?></td>
                        <td><?= encode(ucfirst($bill['status'])); ?></td>
                        <td><?= encode($bill['payment_date']); ?></td>
                        <td><?= encode($bill['transaction_ref'] ?? '-'); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</section>

