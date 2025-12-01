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
    <table class="table">
        <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Created</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($members as $member): ?>
            <tr>
                <td><?= encode($member['name']); ?></td>
                <td><?= encode($member['email']); ?></td>
                <td><?= encode($member['phone']); ?></td>
                <td><?= encode($member['created_at']); ?></td>
                <td>
                    <a class="btn small" href="?module=admin&resource=members&action=show&id=<?= $member['id']; ?>">View</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>

