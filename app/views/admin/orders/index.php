<?php $title = 'Order Maintenance'; ?>
<section class="panel">
    <form method="get" class="form-inline">
        <input type="hidden" name="module" value="admin">
        <input type="hidden" name="resource" value="orders">
        <input type="hidden" name="action" value="index">
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
            <th>Actions</th>
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
                <td>
                    <a class="btn small" href="?module=admin&resource=orders&action=detail&id=<?= $order['id']; ?>">View</a>
                    <form method="post" action="?module=admin&resource=orders&action=updateStatus" style="display: inline-flex; gap: 0.25rem; align-items: center; margin-left: 0.5rem;">
                        <input type="hidden" name="order_id" value="<?= $order['id']; ?>">
                        <select name="status" style="font-size: 0.8rem; padding: 0.15rem 0.3rem;">
                            <?php foreach ($GLOBALS['_order_statuses'] as $code => $text): ?>
                                <option value="<?= $code; ?>" <?= strtolower($order['status']) === $code ? 'selected' : ''; ?>>
                                    <?= $text; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn small secondary" type="submit">Update</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>

