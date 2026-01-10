<?php $title = 'Admin Detail'; ?>
<section class="panel">
    <h2><?= encode($admin['name']); ?></h2>
    <ul class="detail-list">
        <li><strong>Email:</strong> <?= encode($admin['email']); ?></li>
        <li><strong>Phone:</strong> <?= encode($admin['phone']); ?></li>
        <li><strong>Address:</strong> <?= encode($admin['address'] ?? 'N/A'); ?></li>
        <li><strong>Role:</strong> 
            <?php if ($admin['role'] === 'superadmin'): ?>
                <span class="badge" style="background-color: #9c27b0; color: white;">Super Admin</span>
            <?php else: ?>
                <span class="badge" style="background-color: #2196f3; color: white;">Admin</span>
            <?php endif; ?>
        </li>
        <li><strong>Status:</strong> 
            <?php if (($admin['status'] ?? 'active') === 'blocked'): ?>
                <span class="badge" style="background-color: #dc3545; color: white;">Blocked</span>
            <?php else: ?>
                <span class="badge" style="background-color: #28a745; color: white;">Active</span>
            <?php endif; ?>
        </li>
        <li><strong>Created:</strong> <?= encode($admin['created_at']); ?></li>
    </ul>
    <a class="btn secondary" href="?module=admin&resource=admins&action=index">Back</a>
</section>

