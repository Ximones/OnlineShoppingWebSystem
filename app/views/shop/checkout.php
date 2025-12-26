<?php
$title = 'Checkout';
$pricingSummary = $pricingSummary ?? [
    'subtotal' => 0,
    'available_points' => 0,
    'max_redeemable_rm' => 0,
    'points_redeemed' => 0,
    'points_discount' => 0,
    'voucher_code' => '',
    'voucher_discount' => 0,
    'shipping_method' => 'standard',
    'shipping_fee' => 0,
    'payable_total' => 0,
    'use_points' => false,
];
$userVouchers = $userVouchers ?? [];
$orderCount = $orderCount ?? 0;
$outstandingPayLater = $outstandingPayLater ?? 0.0;
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
            <input type="hidden" name="checkout_step" id="checkout-step" value="">
            <input type="text" id="shipping_name" name="shipping_name" value="<?= encode($displayAddress['name'] ?? ''); ?>" required>
            <input type="text" id="shipping_phone" name="shipping_phone" value="<?= encode($displayAddress['phone'] ?? ''); ?>" required>
            <textarea id="shipping_address" name="shipping_address" required><?= encode($displayAddress['address'] ?? ''); ?></textarea>
        </form>
    <?php else: ?>
        <form method="post" id="checkout-form">
            <input type="hidden" name="checkout_step" id="checkout-step" value="">
            <label for="shipping_name">Recipient Name</label>
            <input type="text" id="shipping_name" name="shipping_name" value="<?= encode(post('shipping_name', $user['name'] ?? '')); ?>" required>
            <?php err('shipping_name'); ?>

            <label for="shipping_phone">Phone</label>
            <input type="text" id="shipping_phone" name="shipping_phone" value="<?= encode(post('shipping_phone', $user['phone'] ?? '')); ?>" required>
            <?php err('shipping_phone'); ?>

            <label for="shipping_address">Address</label>
            <textarea id="shipping_address" name="shipping_address" required><?= encode(post('shipping_address', $user['address'] ?? '')); ?></textarea>
            <?php err('shipping_address'); ?>
        </form>
    <?php endif; ?>
</section>

<section class="panel">
    <h2>Order Summary</h2>
    <ul class="summary order-summary-list">
        <?php $grand = 0; ?>
        <?php foreach ($items as $item): ?>
            <?php $total = $item['price'] * $item['quantity']; $grand += $total; ?>
            <li class="order-summary-item">
                <div class="order-summary-thumb">
                    <img src="<?= encode($item['photo'] ?? 'https://placehold.co/80x80'); ?>" alt="<?= encode($item['name']); ?>">
                </div>
                <div class="order-summary-info">
                    <div class="order-summary-name"><?= encode($item['name']); ?></div>
                    <div class="order-summary-meta">
                        Qty <?= (int) $item['quantity']; ?> • RM <?= number_format($item['price'], 2); ?> each
                    </div>
                </div>
                <div class="order-summary-total">RM <?= number_format($total, 2); ?></div>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php $currentMethod = $shippingMethod ?? ($pricingSummary['shipping_method'] ?? 'standard'); ?>
    <div class="checkout-cards">
        <div class="checkout-card-group">
            <div class="checkout-card-header">Shipping method</div>
            <div class="shipping-options">
                <?php foreach ($shippingOptions as $code => $opt): ?>
                    <?php $checked = $code === $currentMethod; ?>
                    <label class="shipping-card <?= $checked ? 'is-selected' : ''; ?>">
                        <input type="radio"
                               name="shipping_method"
                               value="<?= $code; ?>"
                               form="checkout-form"
                               <?= $checked ? 'checked' : ''; ?>>
                        <span class="shipping-name"><?= encode($opt['label']); ?></span>
                        <span class="shipping-fee">RM <?= number_format($opt['fee'], 2); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="checkout-card-group">
            <div class="checkout-card-header">Vouchers</div>
            <?php $currentVoucher = $voucherCode ?? $pricingSummary['voucher_code'] ?? ''; ?>
            <div class="voucher-chips">
                <label class="voucher-chip <?= $currentVoucher === '' ? 'is-selected' : ''; ?>">
                    <input type="radio"
                           name="voucher_code"
                           value=""
                           form="checkout-form"
                           <?= $currentVoucher === '' ? 'checked' : ''; ?>>
                    <span>No voucher</span>
                </label>

                <?php foreach ($userVouchers as $uv): ?>
                    <?php
                    $eligible = true;
                    if (!empty($uv['min_subtotal']) && $pricingSummary['subtotal'] < (float) $uv['min_subtotal']) {
                        $eligible = false;
                    }
                    if (!empty($uv['is_first_order_only']) && $orderCount > 0) {
                        $eligible = false;
                    }

                    $statusText = '';
                    if (!empty($uv['status']) && strtolower($uv['status']) === 'used') {
                        $eligible = false;
                        $statusText = ' (Used)';
                    } elseif (!$eligible) {
                        $statusText = ' (Not applicable)';
                    }

                    $selected = $uv['code'] === $currentVoucher && $eligible;
                    ?>
                    <label class="voucher-chip <?= $selected ? 'is-selected' : ''; ?> <?= !$eligible ? 'is-disabled' : ''; ?>">
                        <input type="radio"
                               name="voucher_code"
                               value="<?= encode($uv['code']); ?>"
                               form="checkout-form"
                               <?= $selected ? 'checked' : ''; ?>
                               <?= !$eligible ? 'disabled' : ''; ?>>
                        <span><?= encode($uv['code']); ?></span>
                        <small><?= encode($uv['name'] . $statusText); ?></small>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php
    $availablePoints = (int) ($pricingSummary['available_points'] ?? 0);
    $maxRedeemableRm = (int) ($pricingSummary['max_redeemable_rm'] ?? 0);
    ?>
    <div class="checkout-card-group">
        <div class="checkout-card-header">Reward points</div>
        <div style="padding: 1.25rem; background: #ffffff; border-radius: var(--radius-sm); border: 1px solid var(--color-border-soft);">
            <label style="display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; cursor: pointer; <?= $maxRedeemableRm <= 0 ? 'opacity: 0.6;' : ''; ?>">
                <div style="flex: 1;">
                    <strong style="display: block; margin-bottom: 0.25rem;">Use reward points</strong>
                    <small style="color: var(--color-text-muted);">
                        You have <?= number_format($availablePoints, 0); ?> pts (up to RM <?= number_format($maxRedeemableRm, 2); ?> off)
                    </small>
                </div>
                <input type="checkbox"
                       name="use_points"
                       form="checkout-form"
                       value="1"
                       <?= !empty($pricingSummary['use_points']) ? 'checked' : ''; ?>
                       <?= $maxRedeemableRm <= 0 ? 'disabled' : ''; ?>
                       style="margin-top: 0.25rem; cursor: pointer; width: 18px; height: 18px;">
            </label>
        </div>
    </div>

    <div class="checkout-card-group" style="margin-top: 1.5rem;">
        <div class="checkout-card-header">Payment method</div>
        <?php $currentPayment = post('payment_method', 'Stripe'); ?>
        <div class="payment-options">
            <label class="payment-card <?= $currentPayment === 'Stripe' ? 'is-selected' : ''; ?>">
                <input type="radio"
                       name="payment_method"
                       value="Stripe"
                       form="checkout-form"
                       <?= $currentPayment === 'Stripe' ? 'checked' : ''; ?>>
                <span class="payment-name">Pay with Stripe</span>
                <span class="payment-note">Secure payment via Stripe checkout</span>
            </label>

            <label class="payment-card <?= $currentPayment === 'PayLater' ? 'is-selected' : ''; ?>">
                <input type="radio"
                       name="payment_method"
                       value="PayLater"
                       form="checkout-form"
                       <?= $currentPayment === 'PayLater' ? 'checked' : ''; ?>>
                <span class="payment-name">PayLater</span>
                <?php
                $principal = (float) ($pricingSummary['payable_total'] ?? 0);
                $creditLimit = $user ? (float)($user['paylater_credit_limit'] ?? 10000.0) : 10000.0;
                $availableLimit = max(0.0, $creditLimit - (float) $outstandingPayLater);
                ?>
                <span class="payment-note">
                    Pay later via Bills • Available limit RM <?= number_format($availableLimit, 2); ?>
                </span>
            </label>
        </div>

        <?php if ($currentPayment === 'PayLater'): ?>
            <?php
            $principal = (float) ($pricingSummary['payable_total'] ?? 0);
            $tenureOptions = [
                3 => 0.0,
                6 => 0.015,
                12 => 0.025,
            ];
            $currentTenure = (int) post('paylater_tenure', 3);
            if (!isset($tenureOptions[$currentTenure])) {
                $currentTenure = 3;
            }
            $creditLimit = $user ? (float)($user['paylater_credit_limit'] ?? 10000.0) : 10000.0;
            $availableLimit = max(0.0, $creditLimit - (float) $outstandingPayLater);
            ?>
            <div style="margin-top: 0.75rem;">
                <div style="font-size: 0.9rem; margin-bottom: 0.25rem;">Choose tenure</div>
                <div class="tenure-options">
                    <?php foreach ($tenureOptions as $months => $rate): ?>
                        <?php
                        $totalWithInterest = $principal * (1 + $rate);
                        $monthly = $months > 0 ? $totalWithInterest / $months : 0;
                        $selected = $currentTenure === $months;
                        $financedPrincipal = min($principal, $availableLimit);
                        $interestTotal = $financedPrincipal * $rate;
                        $financedTotal = $financedPrincipal + $interestTotal;
                        $monthly = $months > 0 ? $financedTotal / $months : 0;
                        $selected = $currentTenure === $months;
                        $remainingNow = max(0.0, $principal - $financedPrincipal);
                        ?>
                        <label class="tenure-chip <?= $selected ? 'is-selected' : ''; ?>">
                            <input type="radio"
                                   name="paylater_tenure"
                                   value="<?= $months; ?>"
                                   form="checkout-form"
                                   <?= $selected ? 'checked' : ''; ?>>
                            <span class="tenure-main"><?= $months; ?> months</span>
                            <span class="tenure-sub">
                                RM <?= number_format($monthly, 2); ?> / month
                                (<?= $rate > 0 ? ($rate * 100) . '% interest' : '0% interest'; ?>)
                            </span>
                            <?php if ($remainingNow > 0): ?>
                                <span class="tenure-remaining">
                                    RM <?= number_format($remainingNow, 2); ?> to pay now with other method
                                </span>
                            <?php endif; ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Moved summary breakdown just above total -->
    <p class="grand" style="margin-top: 1rem;">Subtotal: RM <?= number_format($pricingSummary['subtotal'], 2); ?></p>
    <?php if (!empty($pricingSummary['points_redeemed'])): ?>
        <p class="grand" style="color: #0e3d73;">
            Points Applied (<?= number_format($pricingSummary['points_redeemed'], 0); ?> pts): -RM <?= number_format($pricingSummary['points_discount'], 2); ?>
        </p>
    <?php endif; ?>
    <?php if (!empty($pricingSummary['voucher_code'])): ?>
        <p class="grand" style="color: #0e3d73;">
            Voucher "<?= encode($pricingSummary['voucher_code']); ?>" applied: -RM <?= number_format($pricingSummary['voucher_discount'], 2); ?>
        </p>
    <?php endif; ?>
    <p class="grand">Shipping: RM <?= number_format($pricingSummary['shipping_fee'], 2); ?></p>

    <p class="grand"><strong>Total Payable: RM <?= number_format($pricingSummary['payable_total'], 2); ?></strong></p>
</section>

<section class="panel" style="text-align: right;">
    <?php if ($displayAddress && !$hasErrors): ?>
        <button type="button" id="place-order-btn" class="btn primary">Place Order</button>
    <?php else: ?>
        <button type="submit" form="checkout-form" class="btn primary">Place Order</button>
    <?php endif; ?>
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
            function rememberScroll() {
                try {
                    var y = window.scrollY || window.pageYOffset || 0;
                    localStorage.setItem('checkoutScroll', String(y));
                } catch (e) {}
            }

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

            // Handle Place Order button click (auto-filled address mode)
            $('#place-order-btn').on('click', function() {
                rememberScroll();
                $('#checkout-step').val('');
                $('#checkout-form').submit();
            });

            // Also remember scroll when the form is submitted directly
            $(document).on('submit', '#checkout-form', function() {
                rememberScroll();
            });

            // Auto-update pricing when shipping, voucher, or points change
            function attachPricingAutoUpdate() {
                var $form = $('#checkout-form');
                if (!$form.length) return;

                function submitUpdate() {
                    rememberScroll();
                    $('#checkout-step').val('update_pricing');
                    $form.submit();
                }

                $('input[name="shipping_method"][form="checkout-form"]').on('change', submitUpdate);
                $('input[name="voucher_code"][form="checkout-form"]').on('change', submitUpdate);
                $('input[name="use_points"][form="checkout-form"]').on('change', submitUpdate);
                $('input[name="payment_method"][form="checkout-form"]').on('change', submitUpdate);
                $('input[name="paylater_tenure"][form="checkout-form"]').on('change', submitUpdate);
            }

            attachPricingAutoUpdate();

            // Update card selection styles on change
            $(document).on('change', 'input[name="shipping_method"][form="checkout-form"]', function() {
                $('.shipping-card').removeClass('is-selected');
                $(this).closest('.shipping-card').addClass('is-selected');
            });

            $(document).on('change', 'input[name="voucher_code"][form="checkout-form"]', function() {
                $('.voucher-chip').removeClass('is-selected');
                $(this).closest('.voucher-chip').addClass('is-selected');
            });

            $(document).on('change', 'input[name="payment_method"][form="checkout-form"]', function() {
                $('.payment-card').removeClass('is-selected');
                $(this).closest('.payment-card').addClass('is-selected');
            });

            $(document).on('change', 'input[name="paylater_tenure"][form="checkout-form"]', function() {
                $('.tenure-chip').removeClass('is-selected');
                $(this).closest('.tenure-chip').addClass('is-selected');
            });

            // Restore scroll position if saved
            try {
                var savedY = localStorage.getItem('checkoutScroll');
                if (savedY !== null) {
                    window.scrollTo(0, parseInt(savedY, 10) || 0);
                    localStorage.removeItem('checkoutScroll');
                }
            } catch (e) {}
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

