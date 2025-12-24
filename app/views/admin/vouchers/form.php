<?php $title = isset($voucher) ? 'Edit Voucher' : 'Create Voucher'; ?>

<section class="panel">
    <h2><?= isset($voucher) ? 'Edit Voucher' : 'Create Voucher'; ?></h2>
    <form method="post">
        <label for="code">Code</label>
        <input type="text" id="code" name="code" value="<?= encode(post('code', $voucher['code'] ?? '')); ?>" required>
        <?php err('code'); ?>

        <label for="name">Name</label>
        <input type="text" id="name" name="name" value="<?= encode(post('name', $voucher['name'] ?? '')); ?>" required>
        <?php err('name'); ?>

        <label for="description">Description</label>
        <textarea id="description" name="description"><?= encode(post('description', $voucher['description'] ?? '')); ?></textarea>

        <label for="type">Type</label>
        <select id="type" name="type" required>
            <?php $type = post('type', $voucher['type'] ?? 'amount'); ?>
            <option value="amount" <?= $type === 'amount' ? 'selected' : ''; ?>>Amount (RM)</option>
            <option value="percent" <?= $type === 'percent' ? 'selected' : ''; ?>>Percent (%)</option>
            <option value="shipping_amount" <?= $type === 'shipping_amount' ? 'selected' : ''; ?>>Shipping Amount (RM)</option>
            <option value="free_shipping" <?= $type === 'free_shipping' ? 'selected' : ''; ?>>Free Shipping</option>
        </select>
        <?php err('type'); ?>

        <label for="value">Value</label>
        <input type="number" step="0.01" id="value" name="value" value="<?= encode(post('value', $voucher['value'] ?? '0')); ?>" required>
        <?php err('value'); ?>

        <label for="min_subtotal">Minimum Subtotal (RM)</label>
        <input type="number" step="0.01" id="min_subtotal" name="min_subtotal" value="<?= encode(post('min_subtotal', $voucher['min_subtotal'] ?? '0')); ?>">

        <label for="max_discount">Maximum Discount (RM, optional)</label>
        <input type="number" step="0.01" id="max_discount" name="max_discount" value="<?= encode(post('max_discount', $voucher['max_discount'] ?? '')); ?>">

        <label for="max_claims">Available Quantity (optional)</label>
        <input type="number" step="1" min="1" id="max_claims" name="max_claims" value="<?= encode(post('max_claims', $voucher['max_claims'] ?? '')); ?>">

        <label style="margin-top: 1rem;">
            <input type="checkbox" name="is_shipping_only" value="1" <?= post('is_shipping_only', $voucher['is_shipping_only'] ?? 0) ? 'checked' : ''; ?>>
            Applies to shipping fee only
        </label>

        <label for="start_at">Start At (optional)</label>
        <?php 
        $startAt = post('start_at', $voucher['start_at'] ?? '');
        if ($startAt && strpos($startAt, ' ') !== false) {
            $startAt = date('Y-m-d\TH:i', strtotime($startAt));
        }
        ?>
        <input type="datetime-local" id="start_at" name="start_at" value="<?= encode($startAt); ?>">

        <label for="end_at">End At (optional)</label>
        <?php 
        $endAt = post('end_at', $voucher['end_at'] ?? '');
        if ($endAt && strpos($endAt, ' ') !== false) {
            $endAt = date('Y-m-d\TH:i', strtotime($endAt));
        }
        ?>
        <input type="datetime-local" id="end_at" name="end_at" value="<?= encode($endAt); ?>">

        <label style="margin-top: 1rem;">
            <input type="checkbox" name="is_first_order_only" value="1" <?= post('is_first_order_only', $voucher['is_first_order_only'] ?? 0) ? 'checked' : ''; ?>>
            Only for first-time orders (0 order history)
        </label>

        <label style="margin-top: 0.5rem;">
            <input type="checkbox" name="is_active" value="1" <?= post('is_active', $voucher['is_active'] ?? 1) ? 'checked' : ''; ?>>
            Active
        </label>

        <div style="margin-top: 1.5rem;">
            <button type="submit" class="btn primary"><?= isset($voucher) ? 'Update' : 'Create'; ?></button>
            <a href="?module=admin&resource=vouchers&action=index" class="btn secondary">Cancel</a>
        </div>
    </form>
</section>


