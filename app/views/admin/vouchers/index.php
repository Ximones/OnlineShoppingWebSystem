<?php $title = 'Manage Vouchers'; ?>

<section class="panel">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h2>Vouchers</h2>
        <a href="?module=admin&resource=vouchers&action=create" class="btn primary">Add Voucher</a>
    </div>

    <form method="get" style="margin-bottom: 1rem;">
        <input type="hidden" name="module" value="admin">
        <input type="hidden" name="resource" value="vouchers">
        <input type="hidden" name="action" value="index">
        <input type="text" name="keyword" placeholder="Search code or name" value="<?= encode($search ?? ''); ?>" style="padding: 0.4rem 0.6rem; border-radius: 6px; border: 1px solid #ccc; width: 220px;">
        <button type="submit" class="btn secondary btn-small">Search</button>
    </form>

    <?php if (empty($vouchers)): ?>
        <p>No vouchers found.</p>
    <?php else: ?>
        <table class="table">
            <thead>
            <tr>
                <th>Code</th>
                <th>Name</th>
                <th>Type</th>
                <th>Value</th>
                <th>Min Subtotal</th>
                <th>Shipping Only</th>
                <th>First Order Only</th>
                <th>Available Qty</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($vouchers as $v): ?>
                <tr>
                    <td><?= encode($v['code']); ?></td>
                    <td><?= encode($v['name']); ?></td>
                    <td><?= encode($v['type']); ?></td>
                    <td>RM <?= number_format($v['value'], 2); ?></td>
                    <td>RM <?= number_format($v['min_subtotal'], 2); ?></td>
                    <td><?= $v['is_shipping_only'] ? 'Yes' : 'No'; ?></td>
                    <td><?= $v['is_first_order_only'] ? 'Yes' : 'No'; ?></td>
                    <td><?= $v['max_claims'] !== null ? (int) $v['max_claims'] : 'Unlimited'; ?></td>
                    <td><?= $v['is_active'] ? 'Active' : 'Inactive'; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>


