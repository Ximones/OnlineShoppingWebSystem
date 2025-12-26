<?php $title = isset($voucher) ? 'Edit Voucher' : 'Create Voucher'; ?>

<section class="panel">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2><?= isset($voucher) ? 'Edit Voucher' : 'Create Voucher'; ?></h2>
        <a href="?module=admin&resource=vouchers&action=index" class="btn secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>

    <form method="post" id="voucherForm">
        <!-- Basic Information Section -->
        <div class="form-section">
            <h3 class="form-section-title">Basic Information</h3>

            <div class="form-grid">
                <div class="form-group">
                    <label for="code">
                        Voucher Code <span style="color: #dc3545;">*</span>
                        <small style="display: block; color: var(--color-text-muted); font-weight: normal; margin-top: 0.25rem;">
                            Unique code customers will enter (e.g., SAVE20)
                        </small>
                    </label>
                    <input type="text" id="code" name="code" 
                           value="<?= encode(post('code', $voucher['code'] ?? '')); ?>" 
                           required 
                           style="text-transform: uppercase;"
                           placeholder="e.g., SAVE20">
                    <?php err('code'); ?>
                </div>

                <div class="form-group">
                    <label for="name">
                        Voucher Name <span style="color: #dc3545;">*</span>
                        <small style="display: block; color: var(--color-text-muted); font-weight: normal; margin-top: 0.25rem;">
                            Display name for this voucher
                        </small>
                    </label>
                    <input type="text" id="name" name="name" 
                           value="<?= encode(post('name', $voucher['name'] ?? '')); ?>" 
                           required
                           placeholder="e.g., Summer Sale 20% Off">
                    <?php err('name'); ?>
                </div>
            </div>

            <label for="description">
                Description
                <small style="display: block; color: var(--color-text-muted); font-weight: normal; margin-top: 0.25rem;">
                    Additional details about this voucher (optional)
                </small>
            </label>
            <textarea id="description" name="description" rows="4" 
                      placeholder="Enter voucher description..."><?= encode(post('description', $voucher['description'] ?? '')); ?></textarea>
        </div>

        <!-- Discount Configuration Section -->
        <div class="form-section">
            <h3 class="form-section-title">Discount Configuration</h3>

            <div class="form-grid">
                <div class="form-group">
                    <label for="type">
                        Discount Type <span style="color: #dc3545;">*</span>
                        <small style="display: block; color: var(--color-text-muted); font-weight: normal; margin-top: 0.25rem;">
                            How the discount will be applied
                        </small>
                    </label>
                    <select id="type" name="type" required onchange="updateVoucherType()">
                        <?php $type = post('type', $voucher['type'] ?? 'amount'); ?>
                        <option value="amount" <?= $type === 'amount' ? 'selected' : ''; ?>>Fixed Amount (RM)</option>
                        <option value="percent" <?= $type === 'percent' ? 'selected' : ''; ?>>Percentage (%)</option>
                        <option value="shipping_amount" <?= $type === 'shipping_amount' ? 'selected' : ''; ?>>Shipping Discount (RM)</option>
                        <option value="free_shipping" <?= $type === 'free_shipping' ? 'selected' : ''; ?>>Free Shipping</option>
                    </select>
                    <?php err('type'); ?>
                </div>

                <div class="form-group" id="value-group">
                    <label for="value">
                        Discount Value <span style="color: #dc3545;">*</span>
                        <small style="display: block; color: var(--color-text-muted); font-weight: normal; margin-top: 0.25rem;" id="value-hint">
                            Enter the discount amount
                        </small>
                    </label>
                    <input type="number" step="0.01" min="0" id="value" name="value" 
                           value="<?= encode(post('value', $voucher['value'] ?? '0')); ?>" 
                           required
                           placeholder="0.00">
                    <?php err('value'); ?>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="min_subtotal">
                        Minimum Subtotal (RM)
                        <small style="display: block; color: var(--color-text-muted); font-weight: normal; margin-top: 0.25rem;">
                            Minimum order amount required to use this voucher
                        </small>
                    </label>
                    <input type="number" step="0.01" min="0" id="min_subtotal" name="min_subtotal" 
                           value="<?= encode(post('min_subtotal', $voucher['min_subtotal'] ?? '0')); ?>"
                           placeholder="0.00">
                </div>

                <div class="form-group" id="max_discount-group">
                    <label for="max_discount">
                        Maximum Discount (RM)
                        <small style="display: block; color: var(--color-text-muted); font-weight: normal; margin-top: 0.25rem;">
                            Maximum discount cap (for percentage vouchers)
                        </small>
                    </label>
                    <input type="number" step="0.01" min="0" id="max_discount" name="max_discount" 
                           value="<?= encode(post('max_discount', $voucher['max_discount'] ?? '')); ?>"
                           placeholder="Leave empty for no limit">
                </div>
            </div>
        </div>

        <!-- Availability & Restrictions Section -->
        <div class="form-section">
            <h3 class="form-section-title">Availability & Restrictions</h3>

            <div class="form-grid">
                <div class="form-group">
                    <label for="max_claims">
                        Available Quantity
                        <small style="display: block; color: var(--color-text-muted); font-weight: normal; margin-top: 0.25rem;">
                            Maximum number of times this voucher can be claimed
                        </small>
                    </label>
                    <input type="number" step="1" min="1" id="max_claims" name="max_claims" 
                           value="<?= encode(post('max_claims', $voucher['max_claims'] ?? '')); ?>"
                           placeholder="Leave empty for unlimited">
                </div>

                <div class="form-group">
                    <label for="start_at">
                        Start Date & Time
                        <small style="display: block; color: var(--color-text-muted); font-weight: normal; margin-top: 0.25rem;">
                            When the voucher becomes available
                        </small>
                    </label>
                    <?php 
                    $startAt = post('start_at', $voucher['start_at'] ?? '');
                    if ($startAt && strpos($startAt, ' ') !== false) {
                        $startAt = date('Y-m-d\TH:i', strtotime($startAt));
                    }
                    ?>
                    <input type="datetime-local" id="start_at" name="start_at" value="<?= encode($startAt); ?>">
                </div>

                <div class="form-group">
                    <label for="end_at">
                        End Date & Time
                        <small style="display: block; color: var(--color-text-muted); font-weight: normal; margin-top: 0.25rem;">
                            When the voucher expires
                        </small>
                    </label>
                    <?php 
                    $endAt = post('end_at', $voucher['end_at'] ?? '');
                    if ($endAt && strpos($endAt, ' ') !== false) {
                        $endAt = date('Y-m-d\TH:i', strtotime($endAt));
                    }
                    ?>
                    <input type="datetime-local" id="end_at" name="end_at" value="<?= encode($endAt); ?>">
                </div>
            </div>

            <div style="margin-top: 1.5rem; padding: 1.25rem; background: var(--color-bg-soft); border-radius: var(--radius-sm); border: 1px solid var(--color-border-soft);">
                <h4 style="margin: 0 0 1rem 0; font-size: 1rem; font-weight: 600; color: var(--color-text);">Restrictions</h4>

                <div class="toggle-list">
                    <label class="toggle-row">
                        <span class="toggle-control">
                            <input type="checkbox" name="is_shipping_only" value="1"
                                   <?= post('is_shipping_only', $voucher['is_shipping_only'] ?? 0) ? 'checked' : ''; ?>>
                            <span class="toggle-slider"></span>
                        </span>
                        <span class="toggle-text">
                            <strong>Applies to shipping fee only</strong>
                            <small>Discount will only apply to shipping costs, not product prices</small>
                        </span>
                    </label>

                    <label class="toggle-row">
                        <span class="toggle-control">
                            <input type="checkbox" name="is_first_order_only" value="1"
                                   <?= post('is_first_order_only', $voucher['is_first_order_only'] ?? 0) ? 'checked' : ''; ?>>
                            <span class="toggle-slider"></span>
                        </span>
                        <span class="toggle-text">
                            <strong>First-time customers only</strong>
                            <small>Only customers with 0 order history can use this voucher</small>
                        </span>
                    </label>

                    <label class="toggle-row">
                        <span class="toggle-control">
                            <input type="checkbox" name="is_active" value="1"
                                   <?= post('is_active', $voucher['is_active'] ?? 1) ? 'checked' : ''; ?>>
                            <span class="toggle-slider"></span>
                        </span>
                        <span class="toggle-text">
                            <strong>Active</strong>
                            <small>Enable or disable this voucher</small>
                        </span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div style="display: flex; gap: 1rem; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--color-border-soft);">
            <button type="submit" class="btn primary submit-product-btn">
                <i class="fas fa-<?= isset($voucher) ? 'save' : 'plus'; ?>"></i>
                <?= isset($voucher) ? 'Update Voucher' : 'Create Voucher'; ?>
            </button>
            <a href="?module=admin&resource=vouchers&action=index" class="btn secondary" style="padding-top: 0.86rem; padding-bottom: 0.86rem;">
                Cancel
            </a>
        </div>
    </form>
</section>

<script>
function updateVoucherType() {
    const type = document.getElementById('type').value;
    const valueGroup = document.getElementById('value-group');
    const valueHint = document.getElementById('value-hint');
    const valueInput = document.getElementById('value');
    const maxDiscountGroup = document.getElementById('max_discount-group');
    
    // Update hint text based on voucher type
    switch(type) {
        case 'amount':
            valueHint.textContent = 'Enter the fixed discount amount in RM';
            valueInput.placeholder = '0.00';
            maxDiscountGroup.style.display = 'none';
            break;
        case 'percent':
            valueHint.textContent = 'Enter the percentage discount (e.g., 20 for 20%)';
            valueInput.placeholder = '0';
            valueInput.step = '1';
            valueInput.max = '100';
            maxDiscountGroup.style.display = 'block';
            break;
        case 'shipping_amount':
            valueHint.textContent = 'Enter the shipping discount amount in RM';
            valueInput.placeholder = '0.00';
            valueInput.step = '0.01';
            valueInput.max = '';
            maxDiscountGroup.style.display = 'none';
            break;
        case 'free_shipping':
            valueHint.textContent = 'Value is not required for free shipping';
            valueInput.placeholder = '0';
            valueInput.step = '0.01';
            valueInput.max = '';
            valueInput.required = false;
            maxDiscountGroup.style.display = 'none';
            break;
    }
    
    // For free shipping, make value optional
    if (type === 'free_shipping') {
        valueInput.removeAttribute('required');
        valueInput.value = '0';
    } else {
        valueInput.setAttribute('required', 'required');
    }
}

// Auto-uppercase voucher code
document.getElementById('code')?.addEventListener('input', function(e) {
    e.target.value = e.target.value.toUpperCase();
});

// Validate date range
document.getElementById('end_at')?.addEventListener('change', function() {
    const startAt = document.getElementById('start_at').value;
    const endAt = this.value;
    if (startAt && endAt && new Date(endAt) <= new Date(startAt)) {
        alert('End date must be after start date.');
        this.value = '';
    }
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateVoucherType();
});
</script>


