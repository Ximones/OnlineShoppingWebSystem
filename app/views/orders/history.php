<?php $title = 'Order History'; ?>
<section class="panel">
    <table class="table">
        <thead>
        <tr>
            <th>#</th>
            <th>Total</th>
            <th>Status</th>
            <th>Placed At</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($orders as $order): ?>
            <tr>
                <td><?= $order['id']; ?></td>
                <td>RM <?= number_format($order['total_amount'], 2); ?></td>
                <td><?= encode(ucfirst($order['status'])); ?></td>
                <td><?= encode($order['created_at']); ?></td>
                <td><a class="btn small" href="?module=orders&action=detail&id=<?= $order['id']; ?>">Detail</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>

