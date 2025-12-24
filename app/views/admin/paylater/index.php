<?php $title = 'PayLater Management'; ?>

<section class="panel">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="margin: 0;">PayLater Bills</h2>
    </div>
</section>

<!-- Summary Statistics -->
<section class="panel">
    <h3 style="margin-top: 0; margin-bottom: 15px;">Summary</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
        <div style="padding: 15px; background: #f8f9fa; border-radius: 8px;">
            <div style="font-size: 24px; font-weight: bold; color: #ff9800;"><?= number_format($stats['total_pending']); ?></div>
            <div style="font-size: 14px; color: #666;">Pending Bills</div>
        </div>
        <div style="padding: 15px; background: #f8f9fa; border-radius: 8px;">
            <div style="font-size: 24px; font-weight: bold; color: #dc3545;">RM <?= number_format($stats['total_outstanding'], 2); ?></div>
            <div style="font-size: 14px; color: #666;">Outstanding Amount</div>
        </div>
        <div style="padding: 15px; background: #f8f9fa; border-radius: 8px;">
            <div style="font-size: 24px; font-weight: bold; color: #28a745;">RM <?= number_format($stats['total_collected'], 2); ?></div>
            <div style="font-size: 14px; color: #666;">Total Collected</div>
        </div>
        <div style="padding: 15px; background: #fff3cd; border-radius: 8px; border: 1px solid #ffc107;">
            <div style="font-size: 24px; font-weight: bold; color: #856404;"><?= number_format($stats['total_overdue']); ?></div>
            <div style="font-size: 14px; color: #856404;">Overdue Bills</div>
            <div style="font-size: 12px; color: #856404; margin-top: 5px;">RM <?= number_format($stats['overdue_amount'], 2); ?></div>
        </div>
    </div>
</section>

<!-- Filters -->
<section class="panel">
    <form method="get" class="form-inline">
        <input type="hidden" name="module" value="admin">
        <input type="hidden" name="resource" value="paylater">
        <input type="hidden" name="action" value="index">
        <input type="text" name="keyword" value="<?= encode($filters['keyword']); ?>" placeholder="Search user name/email/order ID">
        <select name="status">
            <option value="">All Status</option>
            <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
            <option value="completed" <?= ($filters['status'] ?? '') === 'completed' ? 'selected' : ''; ?>>Completed</option>
        </select>
        <button class="btn primary">Filter</button>
        <a class="btn secondary" href="?module=admin&resource=paylater&action=index">Clear</a>
    </form>
</section>

<!-- Bills Table -->
<section class="panel">
    <?php if (empty($bills)): ?>
        <p style="color: #999; text-align: center; padding: 40px;">No PayLater bills found.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Bill ID</th>
                    <th>Order ID</th>
                    <th>User</th>
                    <th>Amount</th>
                    <th>Principal</th>
                    <th>Tenure</th>
                    <th>Interest Rate</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Credit Limit</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bills as $bill): ?>
                    <?php
                    $isOverdue = $bill['status'] === 'pending' && $bill['billing_due_date'] && strtotime($bill['billing_due_date']) < time();
                    $userModel = new \App\Models\User();
                    $user = $userModel->find($bill['user_id']);
                    $creditLimit = $user ? (float)($user['paylater_credit_limit'] ?? 10000.0) : 10000.0;
                    ?>
                    <tr style="<?= $isOverdue ? 'background-color: #fff3cd;' : ''; ?>">
                        <td>#<?= $bill['id']; ?></td>
                        <td>
                            <a href="?module=admin&resource=orders&action=detail&id=<?= $bill['order_id']; ?>">
                                #<?= $bill['order_id']; ?>
                            </a>
                        </td>
                        <td>
                            <div>
                                <div><?= encode($bill['user_name']); ?></div>
                                <div style="font-size: 0.85rem; color: #666;"><?= encode($bill['user_email']); ?></div>
                            </div>
                        </td>
                        <td>RM <?= number_format($bill['amount'], 2); ?></td>
                        <td>RM <?= number_format($bill['principal_amount'], 2); ?></td>
                        <td><?= $bill['tenure_months'] ? $bill['tenure_months'] . ' months' : '-'; ?></td>
                        <td><?= $bill['interest_rate'] ? number_format($bill['interest_rate'], 2) . '%' : '0%'; ?></td>
                        <td>
                            <?php if ($bill['billing_due_date']): ?>
                                <?= encode($bill['billing_due_date']); ?>
                                <?php if ($isOverdue): ?>
                                    <span style="color: #dc3545; font-weight: bold; margin-left: 5px;">(Overdue)</span>
                                <?php endif; ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge <?= $bill['status']; ?>">
                                <?= encode(ucfirst($bill['status'])); ?>
                            </span>
                        </td>
                        <td>
                            <form method="post" action="?module=admin&resource=paylater&action=updateCreditLimit" style="display: inline-flex; gap: 0.25rem; align-items: center;">
                                <input type="hidden" name="user_id" value="<?= $bill['user_id']; ?>">
                                <input type="number" name="credit_limit" value="<?= number_format($creditLimit, 2, '.', ''); ?>" step="0.01" min="0" style="width: 100px; padding: 0.25rem; font-size: 0.85rem;">
                                <button type="submit" class="btn small secondary" title="Update credit limit">Update</button>
                            </form>
                        </td>
                        <td>
                            <a class="btn small" href="?module=admin&resource=orders&action=detail&id=<?= $bill['order_id']; ?>">View Order</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

