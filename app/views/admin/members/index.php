<?php $title = 'Member Directory'; ?>
<section class="panel">
    <form method="get" class="form-inline">
        <input type="hidden" name="module" value="admin">
        <input type="hidden" name="resource" value="members">
        <input type="hidden" name="action" value="index">
        <input type="text" name="keyword" value="<?= encode($keyword); ?>" placeholder="Search name/email">
        <button class="btn primary">Search</button>
        <a class="btn secondary" href="?module=admin&resource=members&action=create">+ New Member</a>
    </form>
</section>

<section class="panel">
    <div style="margin-bottom: 15px;">
        <form method="post" action="?module=admin&resource=members&action=batchDelete" id="batch-delete-form-members" style="display: inline;">
            <input type="hidden" name="ids[]" id="batch-delete-ids" value="">
            <button type="button" class="btn danger" id="batch-delete-btn-members" style="display: none;" onclick="confirmBatchDeleteMembers()">Delete Selected</button>
        </form>
    </div>
    <table class="table">
        <thead>
        <tr>
            <th style="width: 40px;">
                <input type="checkbox" id="select-all-members" onchange="toggleAllMembers(this)">
            </th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Status</th>
            <th>Created</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($members as $member): ?>
            <?php
            $isBlocked = ($member['status'] ?? 'active') === 'blocked';
            ?>
            <tr style="<?= $isBlocked ? 'background-color: #fee; opacity: 0.8;' : ''; ?>">
                <td>
                    <input type="checkbox" name="ids[]" value="<?= $member['id']; ?>" class="member-checkbox" onchange="updateBatchDeleteBtnMembers()">
                </td>
                <td><?= encode($member['name']); ?></td>
                <td><?= encode($member['email']); ?></td>
                <td><?= encode($member['phone']); ?></td>
                <td>
                    <?php if ($isBlocked): ?>
                        <span class="badge" style="background-color: #dc3545; color: white;">Blocked</span>
                    <?php else: ?>
                        <span class="badge" style="background-color: #28a745; color: white;">Active</span>
                    <?php endif; ?>
                </td>
                <td><?= encode($member['created_at']); ?></td>
                <td>
                    <a class="btn small" href="?module=admin&resource=members&action=show&id=<?= $member['id']; ?>">View</a>
                    <?php if ($isBlocked): ?>
                        <form method="post" action="?module=admin&resource=members&action=unblock" style="display: inline;">
                            <input type="hidden" name="id" value="<?= $member['id']; ?>">
                            <button type="submit" class="btn secondary small">Unblock</button>
                        </form>
                    <?php else: ?>
                        <form method="post" action="?module=admin&resource=members&action=block" onsubmit="return confirm('Block this member? They will not be able to login.');" style="display: inline;">
                            <input type="hidden" name="id" value="<?= $member['id']; ?>">
                            <button type="submit" class="btn small" style="background-color: #ff9800; color: white;">Block</button>
                        </form>
                    <?php endif; ?>
                    <form method="post" action="?module=admin&resource=members&action=delete" onsubmit="return confirm('Delete this member?');" style="display: inline;">
                        <input type="hidden" name="id" value="<?= $member['id']; ?>">
                        <button type="submit" class="btn danger small">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>

<script>
function toggleAllMembers(checkbox) {
    const checkboxes = document.querySelectorAll('.member-checkbox');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
    updateBatchDeleteBtnMembers();
}

function updateBatchDeleteBtnMembers() {
    const checked = document.querySelectorAll('.member-checkbox:checked');
    const btn = document.getElementById('batch-delete-btn-members');
    if (btn) {
        btn.style.display = checked.length > 0 ? 'inline-block' : 'none';
    }
}

function confirmBatchDeleteMembers() {
    const checked = document.querySelectorAll('.member-checkbox:checked');
    if (checked.length === 0) {
        alert('Please select members to delete.');
        return;
    }
    if (confirm('Are you sure you want to delete ' + checked.length + ' selected member(s)? Admin users cannot be deleted.')) {
        const ids = Array.from(checked).map(cb => cb.value);
        const form = document.getElementById('batch-delete-form-members');
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

