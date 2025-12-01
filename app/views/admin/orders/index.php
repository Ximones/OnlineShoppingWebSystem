<?php $title = 'Order Maintenance'; ?>
<section class="panel">
    <form method="get" class="form-inline">
        <input type="hidden" name="module" value="orders">
        <input type="hidden" name="action" value="admin">
        <input type="text" name="keyword" value="<?= encode($filters['keyword']); ?>" placeholder="Search name/email/order#">
        <select name="status">
            <option value="">Any Status</option>
            <?php foreach ($GLOBALS['_order_statuses'] as $code => $text): ?>
                <option value="<?= $code; ?>" <?= ($filters['status'] ?? '') === $code ? 'selected' : ''; ?>><?= $text; ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn primary">Filter</button>
    </form>
</section>

<section class="panel">
    <table class="table">
        <thead>
        <tr>
            <th>#</th>
            <th>Member</th>
            <th>Total</th>
            <th>Status</th>
            <th>Created</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($orders as $order): ?>
            <tr>
                <td><?= $order['id']; ?></td>
                <td><?= encode($order['member_name']); ?></td>
                <td>RM <?= number_format($order['total_amount'], 2); ?></td>
                <td><?= encode(ucfirst($order['status'])); ?></td>
                <td><?= encode($order['created_at']); ?></td>
                <td><a class="btn small" href="?module=orders&action=detail&id=<?= $order['id']; ?>">View</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>

