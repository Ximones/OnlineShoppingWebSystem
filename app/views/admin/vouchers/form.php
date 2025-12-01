<?php $title = 'Create Voucher'; ?>

<section class="panel">
    <h2>Create Voucher</h2>
    <form method="post">
        <label for="code">Code</label>
        <input type="text" id="code" name="code" value="<?= encode(post('code', '')); ?>" required>
        <?php err('code'); ?>

        <label for="name">Name</label>
        <input type="text" id="name" name="name" value="<?= encode(post('name', '')); ?>" required>
        <?php err('name'); ?>

        <label for="description">Description</label>
        <textarea id="description" name="description"><?= encode(post('description', '')); ?></textarea>

        <label for="type">Type</label>
        <select id="type" name="type" required>
            <?php $type = post('type', 'amount'); ?>
            <option value="amount" <?= $type === 'amount' ? 'selected' : ''; ?>>Amount (RM)</option>
            <option value="percent" <?= $type === 'percent' ? 'selected' : ''; ?>>Percent (%)</option>
            <option value="shipping_amount" <?= $type === 'shipping_amount' ? 'selected' : ''; ?>>Shipping Amount (RM)</option>
            <option value="free_shipping" <?= $type === 'free_shipping' ? 'selected' : ''; ?>>Free Shipping</option>
        </select>
        <?php err('type'); ?>

        <label for="value">Value</label>
        <input type="number" step="0.01" id="value" name="value" value="<?= encode(post('value', '0')); ?>" required>
        <?php err('value'); ?>

        <label for="min_subtotal">Minimum Subtotal (RM)</label>
        <input type="number" step="0.01" id="min_subtotal" name="min_subtotal" value="<?= encode(post('min_subtotal', '0')); ?>">

        <label for="max_discount">Maximum Discount (RM, optional)</label>
        <input type="number" step="0.01" id="max_discount" name="max_discount" value="<?= encode(post('max_discount', '')); ?>">

        <label for="max_claims">Available Quantity (optional)</label>
        <input type="number" step="1" min="1" id="max_claims" name="max_claims" value="<?= encode(post('max_claims', '')); ?>">

        <label style="margin-top: 1rem;">
            <input type="checkbox" name="is_shipping_only" value="1" <?= post('is_shipping_only') ? 'checked' : ''; ?>>
            Applies to shipping fee only
        </label>

        <label for="start_at">Start At (optional)</label>
        <input type="datetime-local" id="start_at" name="start_at" value="<?= encode(post('start_at', '')); ?>">

        <label for="end_at">End At (optional)</label>
        <input type="datetime-local" id="end_at" name="end_at" value="<?= encode(post('end_at', '')); ?>">

        <label style="margin-top: 1rem;">
            <input type="checkbox" name="is_first_order_only" value="1" <?= post('is_first_order_only') ? 'checked' : ''; ?>>
            Only for first-time orders (0 order history)
        </label>

        <label style="margin-top: 0.5rem;">
            <input type="checkbox" name="is_active" value="1" <?= post('is_active', '1') ? 'checked' : ''; ?>>
            Active
        </label>

        <div style="margin-top: 1.5rem;">
            <button type="submit" class="btn primary">Save</button>
            <a href="?module=admin&resource=vouchers&action=index" class="btn secondary">Cancel</a>
        </div>
    </form>
</section>


