<?php $title = 'Order #' . $order['id']; ?>
<section class="panel">
    <h2>Order Information</h2>
    <ul class="detail-list">
        <li><strong>Status:</strong> <?= encode(ucfirst($order['status'])); ?></li>
        <li><strong>Total:</strong> RM <?= number_format($order['total_amount'], 2); ?></li>
        <li><strong>Shipping:</strong> <?= encode($order['shipping_name']); ?>, <?= encode($order['shipping_phone']); ?></li>
        <li><strong>Address:</strong> <?= encode($order['shipping_address']); ?></li>
        <li><strong>Placed At:</strong> <?= encode($order['created_at']); ?></li>
    </ul>
</section>

<section class="panel">
    <h2>Items</h2>
    <table class="table">
        <thead>
        <tr>
            <th>Product</th>
            <th>Quantity</th>
            <th>Unit Price</th>
            <th>Total</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($order['items'] as $item): ?>
            <tr>
                <td><?= encode($item['name']); ?></td>
                <td><?= $item['quantity']; ?></td>
                <td>RM <?= number_format($item['unit_price'], 2); ?></td>
                <td>RM <?= number_format($item['unit_price'] * $item['quantity'], 2); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>

