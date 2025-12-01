<?php $title = 'My Bills'; ?>

<section class="panel">
    <h2>Outstanding Bills</h2>

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
</section>


