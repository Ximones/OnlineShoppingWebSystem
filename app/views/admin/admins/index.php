<?php $title = 'Admin Directory'; ?>
<section class="panel">
    <form method="get" class="form-inline">
        <input type="hidden" name="module" value="admin">
        <input type="hidden" name="resource" value="admins">
        <input type="hidden" name="action" value="index">
        <input type="text" name="keyword" value="<?= encode($keyword); ?>" placeholder="Search name/email">
        <button class="btn primary">Search</button>
        <a class="btn secondary" href="?module=admin&resource=admins&action=create">+ New Admin</a>
    </form>
</section>

<section class="panel">
    <div style="margin-bottom: 15px;">
        <form method="post" action="?module=admin&resource=admins&action=batchDelete" id="batch-delete-form-admins" style="display: inline;">
            <input type="hidden" name="ids[]" id="batch-delete-ids" value="">
            <button type="button" class="btn danger" id="batch-delete-btn-admins" style="display: none;" onclick="confirmBatchDeleteAdmins()">Delete Selected</button>
        </form>
    </div>
    <table class="table">
        <thead>
        <tr>
            <th style="width: 40px;">
                <input type="checkbox" id="select-all-admins" onchange="toggleAllAdmins(this)">
            </th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Role</th>
            <th>Status</th>
            <th>Created</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($admins as $admin): ?>
            <?php
            $isBlocked = ($admin['status'] ?? 'active') === 'blocked';
            $isSuperadmin = ($admin['role'] ?? '') === 'superadmin';
            ?>
            <tr style="<?= $isBlocked ? 'background-color: #fee; opacity: 0.8;' : ''; ?>">
                <td>
                    <?php if (!$isSuperadmin): ?>
                        <input type="checkbox" name="ids[]" value="<?= $admin['id']; ?>" class="admin-checkbox" onchange="updateBatchDeleteBtnAdmins()">
                    <?php endif; ?>
                </td>
                <td><?= encode($admin['name']); ?></td>
                <td><?= encode($admin['email']); ?></td>
                <td><?= encode($admin['phone']); ?></td>
                <td>
                    <?php if ($isSuperadmin): ?>
                        <span class="badge" style="background-color: #9c27b0; color: white;">Super Admin</span>
                    <?php else: ?>
                        <span class="badge" style="background-color: #2196f3; color: white;">Admin</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($isBlocked): ?>
                        <span class="badge" style="background-color: #dc3545; color: white;">Blocked</span>
                    <?php else: ?>
                        <span class="badge" style="background-color: #28a745; color: white;">Active</span>
                    <?php endif; ?>
                </td>
                <td><?= encode($admin['created_at']); ?></td>
                <td>
                    <a class="btn small" href="?module=admin&resource=admins&action=show&id=<?= $admin['id']; ?>">View</a>
                    <?php if (!$isSuperadmin): ?>
                        <?php if ($isBlocked): ?>
                            <form method="post" action="?module=admin&resource=admins&action=unblock" style="display: inline;">
                                <input type="hidden" name="id" value="<?= $admin['id']; ?>">
                                <button type="submit" class="btn secondary small">Unblock</button>
                            </form>
                        <?php else: ?>
                            <form method="post" action="?module=admin&resource=admins&action=block" onsubmit="return confirm('Block this admin? They will not be able to login.');" style="display: inline;">
                                <input type="hidden" name="id" value="<?= $admin['id']; ?>">
                                <button type="submit" class="btn small" style="background-color: #ff9800; color: white;">Block</button>
                            </form>
                        <?php endif; ?>
                        <form method="post" action="?module=admin&resource=admins&action=delete" onsubmit="return confirm('Delete this admin?');" style="display: inline;">
                            <input type="hidden" name="id" value="<?= $admin['id']; ?>">
                            <button type="submit" class="btn danger small">Delete</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>

<script>
function toggleAllAdmins(checkbox) {
    const checkboxes = document.querySelectorAll('.admin-checkbox');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
    updateBatchDeleteBtnAdmins();
}

function updateBatchDeleteBtnAdmins() {
    const checked = document.querySelectorAll('.admin-checkbox:checked');
    const btn = document.getElementById('batch-delete-btn-admins');
    if (btn) {
        btn.style.display = checked.length > 0 ? 'inline-block' : 'none';
    }
}

function confirmBatchDeleteAdmins() {
    const checked = document.querySelectorAll('.admin-checkbox:checked');
    if (checked.length === 0) {
        alert('Please select admins to delete.');
        return;
    }
    if (confirm('Are you sure you want to delete ' + checked.length + ' selected admin(s)? Superadmin users cannot be deleted.')) {
        const ids = Array.from(checked).map(cb => cb.value);
        const form = document.getElementById('batch-delete-form-admins');
        const idsInput = document.getElementById('batch-delete-ids');
        // Create hidden inputs for each ID
        idsInput.remove();
        ids.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = id;
            form.appendChild(input);
        });
        form.submit();
    }
}
</script>

