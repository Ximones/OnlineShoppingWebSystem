<?php $title = 'Manage Vouchers'; ?>
<?php
$claimCounts = $claimCounts ?? [];
?>

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
        <form method="post" action="?module=admin&resource=vouchers&action=batchDelete" id="batch-delete-form-vouchers">
            <div style="margin-bottom: 15px;">
                <button type="button" class="btn danger" id="batch-delete-btn-vouchers" style="display: none;" onclick="confirmBatchDeleteVouchers()">Delete Selected</button>
            </div>
            <table class="table">
                <thead>
                <tr>
                    <th style="width: 40px;">
                        <input type="checkbox" id="select-all-vouchers" onchange="toggleAllVouchers(this)">
                    </th>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Value</th>
                    <th>Min Spend</th>
                    <th>Shipping Only</th>
                    <th>First Order Only</th>
                    <th>Available Qty</th>
                    <th>Remaining</th>
                    <th>Status</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($vouchers as $v): ?>
                    <tr>
                        <td>
                            <input type="checkbox" name="ids[]" value="<?= $v['id']; ?>" class="voucher-checkbox" onchange="updateBatchDeleteBtnVouchers()">
                        </td>
                        <td><?= encode($v['code']); ?></td>
                        <td><?= encode($v['name']); ?></td>
                        <td><?= encode($v['type']); ?></td>
                        <td>RM <?= number_format($v['value'], 2); ?></td>
                        <td>RM <?= number_format($v['min_subtotal'], 2); ?></td>
                        <td><?= $v['is_shipping_only'] ? 'Yes' : 'No'; ?></td>
                        <td><?= $v['is_first_order_only'] ? 'Yes' : 'No'; ?></td>
                        <td><?= $v['max_claims'] !== null ? (int) $v['max_claims'] : 'Unlimited'; ?></td>
                        <td>
                            <?php
                            if ($v['max_claims'] !== null) {
                                $totalClaims = $claimCounts[$v['id']] ?? 0;
                                $remaining = max(0, (int) $v['max_claims'] - $totalClaims);
                                echo $remaining;
                            } else {
                                echo 'Unlimited';
                            }
                            ?>
                        </td>
                        <td><?= $v['is_active'] ? 'Active' : 'Inactive'; ?></td>
                        <td>
                            <a class="btn small" href="?module=admin&resource=vouchers&action=edit&id=<?= $v['id']; ?>">Edit</a>
                            <form method="post" action="?module=admin&resource=vouchers&action=delete" onsubmit="return confirm('Delete this voucher?');" style="display: inline;">
                                <input type="hidden" name="id" value="<?= $v['id']; ?>">
                                <button class="btn danger small">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </form>
    <?php endif; ?>

<script>
function toggleAllVouchers(checkbox) {
    const checkboxes = document.querySelectorAll('.voucher-checkbox');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
    updateBatchDeleteBtnVouchers();
}

function updateBatchDeleteBtnVouchers() {
    const checked = document.querySelectorAll('.voucher-checkbox:checked');
    const btn = document.getElementById('batch-delete-btn-vouchers');
    if (btn) {
        btn.style.display = checked.length > 0 ? 'inline-block' : 'none';
    }
}

function confirmBatchDeleteVouchers() {
    const checked = document.querySelectorAll('.voucher-checkbox:checked');
    if (checked.length === 0) {
        alert('Please select vouchers to delete.');
        return;
    }
    if (confirm('Are you sure you want to delete ' + checked.length + ' selected voucher(s)?')) {
        document.getElementById('batch-delete-form-vouchers').submit();
    }
}
</script>
</section>


