<?php $title = 'Member Detail'; ?>
<section class="panel">
    <h2><?= encode($member['name']); ?></h2>
    <ul class="detail-list">
        <li><strong>Email:</strong> <?= encode($member['email']); ?></li>
        <li><strong>Phone:</strong> <?= encode($member['phone']); ?></li>
        <li><strong>Address:</strong> <?= encode($member['address']); ?></li>
        <li><strong>Role:</strong> <?= encode($member['role']); ?></li>
        <li><strong>Status:</strong> <?= encode($member['status']); ?></li>
    </ul>
    <a class="btn secondary" href="?module=admin&resource=members&action=index">Back</a>
</section>

