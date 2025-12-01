<?php $title = 'Checkout'; ?>
<section class="panel">
    <h2>Delivery Details</h2>
    <?php if ($user['address'] ?? null): ?>
        <div style="margin-bottom: 15px;">
            <button type="button" id="use-different-address" class="btn secondary">Use Different Address</button>
        </div>
    <?php endif; ?>
    <form method="post">
        <label for="shipping_name">Recipient Name</label>
        <input type="text" id="shipping_name" name="shipping_name" value="<?= encode($user['name'] ?? ''); ?>" required>
        <?php err('shipping_name'); ?>

        <label for="shipping_phone">Phone</label>
        <input type="text" id="shipping_phone" name="shipping_phone" value="<?= encode($user['phone'] ?? ''); ?>" required>
        <?php err('shipping_phone'); ?>

        <label for="shipping_address">Address</label>
        <textarea id="shipping_address" name="shipping_address" required><?= encode($user['address'] ?? ''); ?></textarea>
        <?php err('shipping_address'); ?>

        <button class="btn primary">Place Order</button>
    </form>
</section>

<section class="panel">
    <h2>Order Summary</h2>
    <ul class="summary">
        <?php $grand = 0; ?>
        <?php foreach ($items as $item): ?>
            <?php $total = $item['price'] * $item['quantity']; $grand += $total; ?>
            <li><?= encode($item['name']); ?> x <?= $item['quantity']; ?> — RM <?= number_format($total, 2); ?></li>
        <?php endforeach; ?>
    </ul>
    <p class="grand">Grand Total: RM <?= number_format($grand, 2); ?></p>
</section>

<?php if ($user['address'] ?? null): ?>
<script>
$(function() {
    $('#use-different-address').on('click', function() {
        if (confirm('Do you want to clear the current address and enter a different one?')) {
            $('#shipping_name').val('').focus();
            $('#shipping_phone').val('');
            $('#shipping_address').val('');
        }
    });
});
</script>
<?php endif; ?>

