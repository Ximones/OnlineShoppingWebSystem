<?php
$title = 'Checkout';
$pointsSummary = $pointsSummary ?? [
    'subtotal' => 0,
    'available_points' => 0,
    'max_redeemable_rm' => 0,
    'points_redeemed' => 0,
    'discount' => 0,
    'payable_total' => 0,
    'use_points' => false,
];
?>
<section class="panel">
    <h2>Delivery Details</h2>
    <?php 
    global $_err;
    $hasErrors = !empty($_err['shipping_name']) || !empty($_err['shipping_phone']) || !empty($_err['shipping_address']);
    $hasSavedAddresses = !empty($savedAddresses);
    $defaultAddress = null;
    if ($hasSavedAddresses) {
        foreach ($savedAddresses as $addr) {
            if ($addr['is_default']) {
                $defaultAddress = $addr;
                break;
            }
        }
        if (!$defaultAddress) {
            $defaultAddress = $savedAddresses[0];
        }
    }
    $displayAddress = $defaultAddress ?? ($user['address'] ? ['name' => $user['name'], 'phone' => $user['phone'], 'address' => $user['address']] : null);
    
    if ($displayAddress && !$hasErrors): ?>
        <?php if ($hasSavedAddresses): ?>
            <div style="margin-bottom: 15px;">
                <label for="saved-address-select"><strong>Select Saved Address:</strong></label>
                <select id="saved-address-select" style="width: 100%; padding: 0.5rem; margin-top: 0.5rem; border-radius: 6px; border: 1px solid #ccc;">
                    <?php foreach ($savedAddresses as $addr): ?>
                        <option value="<?= $addr['id']; ?>" data-name="<?= encode($addr['name']); ?>" data-phone="<?= encode($addr['phone']); ?>" data-address="<?= encode($addr['address']); ?>" <?= ($addr['id'] == $defaultAddress['id']) ? 'selected' : ''; ?>>
                            <?= encode($addr['label']); ?> <?= $addr['is_default'] ? '(Default)' : ''; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>
        <ul class="detail-list" id="delivery-details-list">
            <li><strong>Recipient Name:</strong> <span id="display-name"><?= encode($displayAddress['name'] ?? ''); ?></span></li>
            <li><strong>Phone:</strong> <span id="display-phone"><?= encode($displayAddress['phone'] ?? ''); ?></span></li>
            <li><strong>Address:</strong> <span id="display-address"><?= encode($displayAddress['address'] ?? ''); ?></span></li>
        </ul>
        <div style="margin-top: 15px;" id="address-actions">
            <button type="button" id="use-different-address" class="btn secondary">Use Different Address</button>
        </div>
        <form method="post" id="checkout-form" style="display: none;">
            <input type="text" id="shipping_name" name="shipping_name" value="<?= encode($displayAddress['name'] ?? ''); ?>" required>
            <input type="text" id="shipping_phone" name="shipping_phone" value="<?= encode($displayAddress['phone'] ?? ''); ?>" required>
            <textarea id="shipping_address" name="shipping_address" required><?= encode($displayAddress['address'] ?? ''); ?></textarea>
        </form>
        <div style="margin-top: 15px;">
            <button type="button" id="place-order-btn" class="btn primary">Place Order</button>
        </div>
    <?php else: ?>
        <form method="post" id="checkout-form">
            <label for="shipping_name">Recipient Name</label>
            <input type="text" id="shipping_name" name="shipping_name" value="<?= encode(post('shipping_name', $user['name'] ?? '')); ?>" required>
            <?php err('shipping_name'); ?>

            <label for="shipping_phone">Phone</label>
            <input type="text" id="shipping_phone" name="shipping_phone" value="<?= encode(post('shipping_phone', $user['phone'] ?? '')); ?>" required>
            <?php err('shipping_phone'); ?>

            <label for="shipping_address">Address</label>
            <textarea id="shipping_address" name="shipping_address" required><?= encode(post('shipping_address', $user['address'] ?? '')); ?></textarea>
            <?php err('shipping_address'); ?>

            <button type="submit" class="btn primary">Place Order</button>
        </form>
    <?php endif; ?>
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
    <p class="grand">Subtotal: RM <?= number_format($pointsSummary['subtotal'], 2); ?></p>
    <?php if (!empty($pointsSummary['points_redeemed'])): ?>
        <p class="grand" style="color: #0e3d73;">
            Points Applied (<?= number_format($pointsSummary['points_redeemed'], 0); ?> pts): -RM <?= number_format($pointsSummary['discount'], 2); ?>
        </p>
    <?php endif; ?>
    <p class="grand"><strong>Total Payable: RM <?= number_format($pointsSummary['payable_total'], 2); ?></strong></p>
</section>

<!-- Modal for different address -->
<div class="modal-overlay" id="different-address-modal">
    <div class="modal">
        <div class="modal-header">
            <h3>Enter Different Address</h3>
            <button type="button" class="modal-close" id="close-modal">&times;</button>
        </div>
        <form id="different-address-form">
            <label for="modal_address_label">Address Label (e.g., Home, Office)</label>
            <input type="text" id="modal_address_label" name="modal_address_label" placeholder="Home" required>
            <?php err('modal_address_label'); ?>

            <label for="modal_shipping_name">Recipient Name</label>
            <input type="text" id="modal_shipping_name" name="modal_shipping_name" required>
            <?php err('modal_shipping_name'); ?>

            <label for="modal_shipping_phone">Phone</label>
            <input type="text" id="modal_shipping_phone" name="modal_shipping_phone" required>
            <?php err('modal_shipping_phone'); ?>

            <label for="modal_shipping_address">Address</label>
            <textarea id="modal_shipping_address" name="modal_shipping_address" required></textarea>
            <?php err('modal_shipping_address'); ?>

            <label style="display: flex; align-items: center; margin-top: 1rem;">
                <input type="checkbox" id="modal_is_default" name="modal_is_default" style="width: auto; margin-right: 0.5rem;">
                Set as default address
            </label>

            <div style="margin-top: 1.5rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                <button type="button" class="btn secondary" id="cancel-different-address">Cancel</button>
                <button type="button" class="btn secondary" id="save-address-only">Save Address</button>
                <button type="button" class="btn primary" id="save-different-address">Save & Use This Address</button>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    function initCheckout() {
        if (typeof jQuery === 'undefined') {
            setTimeout(initCheckout, 50);
            return;
        }

        jQuery(function($) {
            // Handle saved address selection
            $('#saved-address-select').on('change', function() {
                var $option = $(this).find('option:selected');
                var name = $option.data('name');
                var phone = $option.data('phone');
                var address = $option.data('address');

                $('#display-name').text(name);
                $('#display-phone').text(phone);
                $('#display-address').text(address);

                $('#shipping_name').val(name);
                $('#shipping_phone').val(phone);
                $('#shipping_address').val(address);
            });

            $('#use-different-address').on('click', function() {
                $('#different-address-modal').addClass('show');
            });

            $('#close-modal, #cancel-different-address').on('click', function() {
                $('#different-address-modal').removeClass('show');
            });

            // Close modal when clicking outside
            $('#different-address-modal').on('click', function(e) {
                if ($(e.target).is('#different-address-modal')) {
                    $(this).removeClass('show');
                }
            });

            function validateModalFields() {
                var label = $('#modal_address_label').val().trim();
                var name = $('#modal_shipping_name').val().trim();
                var phone = $('#modal_shipping_phone').val().trim();
                var address = $('#modal_shipping_address').val().trim();

                if (!label || !name || !phone || !address) {
                    alert('Please fill in all fields.');
                    return false;
                }
                return {label: label, name: name, phone: phone, address: address};
            }

            function updateDisplayAddress(data) {
                $('#display-name').text(data.name);
                $('#display-phone').text(data.phone);
                $('#display-address').text(data.address);

                $('#shipping_name').val(data.name);
                $('#shipping_phone').val(data.phone);
                $('#shipping_address').val(data.address);
            }

            // Save address only (without using it)
            $('#save-address-only').on('click', function() {
                var data = validateModalFields();
                if (!data) return;

                var form = $('<form>', {
                    method: 'POST',
                    action: '?module=cart&action=checkout'
                });
                form.append($('<input>', {type: 'hidden', name: 'action', value: 'save_address'}));
                form.append($('<input>', {type: 'hidden', name: 'label', value: data.label}));
                form.append($('<input>', {type: 'hidden', name: 'name', value: data.name}));
                form.append($('<input>', {type: 'hidden', name: 'phone', value: data.phone}));
                form.append($('<input>', {type: 'hidden', name: 'address', value: data.address}));
                if ($('#modal_is_default').is(':checked')) {
                    form.append($('<input>', {type: 'hidden', name: 'is_default', value: '1'}));
                }
                $('body').append(form);
                form.submit();
            });

            // Save and use address
            $('#save-different-address').on('click', function() {
                var data = validateModalFields();
                if (!data) return;

                // Update display immediately
                updateDisplayAddress(data);

                // Save the address via form submission
                var form = $('<form>', {
                    method: 'POST',
                    action: '?module=cart&action=checkout',
                    style: 'display: none;'
                });
                form.append($('<input>', {type: 'hidden', name: 'action', value: 'save_address'}));
                form.append($('<input>', {type: 'hidden', name: 'label', value: data.label}));
                form.append($('<input>', {type: 'hidden', name: 'name', value: data.name}));
                form.append($('<input>', {type: 'hidden', name: 'phone', value: data.phone}));
                form.append($('<input>', {type: 'hidden', name: 'address', value: data.address}));
                if ($('#modal_is_default').is(':checked')) {
                    form.append($('<input>', {type: 'hidden', name: 'is_default', value: '1'}));
                }
                $('body').append(form);
                
                // Close modal and clear form
                $('#different-address-modal').removeClass('show');
                $('#modal_address_label').val('');
                $('#modal_shipping_name').val('');
                $('#modal_shipping_phone').val('');
                $('#modal_shipping_address').val('');
                $('#modal_is_default').prop('checked', false);
                
                // Submit form to save
                form.submit();
            });

            // Handle Place Order button click
            $('#place-order-btn').on('click', function() {
                $('#checkout-form').submit();
            });
        });
    }

    // Start initialization
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCheckout);
    } else {
        initCheckout();
    }
})();
</script>

