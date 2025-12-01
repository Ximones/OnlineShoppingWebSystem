<?php $title = 'Shopping Cart'; ?>
<section class="panel">
    <?php if (empty($items)): ?>
        <p>Your cart is empty. <a href="?module=shop&action=catalog">Continue shopping</a>.</p>
    <?php else: ?>
        <form method="post" action="?module=cart&action=update">
            <table class="table">
                <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php $grand = 0; ?>
                <?php foreach ($items as $item): ?>
                    <?php $total = $item['price'] * $item['quantity']; $grand += $total; ?>
                    <tr>
                        <td><?= encode($item['name']); ?></td>
                        <td>
                            <input type="number" name="items[<?= $item['id']; ?>]" value="<?= $item['quantity']; ?>" min="1">
                        </td>
                        <td>RM <?= number_format($item['price'], 2); ?></td>
                        <td>RM <?= number_format($total, 2); ?></td>
                        <td>
                            <button class="btn danger" formaction="?module=cart&action=remove" formmethod="post" name="item_id" value="<?= $item['id']; ?>">Remove</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <div class="cart-actions">
                <p class="grand">Grand Total: RM <?= number_format($grand, 2); ?></p>
                <div>
                    <button class="btn secondary">Update Cart</button>
                    <a class="btn primary" href="?module=cart&action=checkout">Checkout</a>
                </div>
            </div>
        </form>
    <?php endif; ?>
</section>

