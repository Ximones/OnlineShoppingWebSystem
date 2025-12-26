<?php $title = 'Shopping Cart'; ?>
<section class="panel">
    <?php if (empty($items)): ?>
        <p>Your cart is empty. <a href="?module=shop&action=catalog">Continue shopping</a>.</p>
    <?php else: ?>
        <?php
        $availablePoints = (int) floor((float)($user['reward_points'] ?? 0));
        $maxRmFromPoints = (int) floor($availablePoints / 10);
        ?>
        <form method="post" action="?module=cart&action=update" id="cart-form">
            <table class="table">
                <thead>
                <tr>
                    <th style="width: 40px;">
                        <label style="display: flex; align-items: center; gap: 0.25rem;">
                            <input type="checkbox" id="select-all-items" checked>
                        </label>
                    </th>
                    <th>Product</th>
                    <th style="width: 140px;">Quantity</th>
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
                        <td>
                            <input
                                type="checkbox"
                                class="item-select"
                                name="selected_items[]"
                                value="<?= $item['id']; ?>"
                                data-price="<?= $item['price']; ?>"
                                data-input-id="qty-<?= $item['id']; ?>"
                                checked
                            >
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <?php if (!empty($item['photo'])): ?>
                                    <img src="<?= encode($item['photo']); ?>" 
                                         alt="<?= encode($item['name']); ?>"
                                         class="cart-item-thumbnail">
                                <?php endif; ?>
                                <span><?= encode($item['name']); ?></span>
                            </div>
                        </td>
                        <td>
                            <input type="number" id="qty-<?= $item['id']; ?>" name="items[<?= $item['id']; ?>]" value="<?= $item['quantity']; ?>" min="1">
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

            <div class="cart-summary-panel">
                <div class="cart-summary-row">
                    <span>Selected Total</span>
                    <strong>RM <span id="selected-total" data-initial="<?= number_format($grand, 2, '.', ''); ?>"><?= number_format($grand, 2); ?></span></strong>
                </div>
                <div class="cart-summary-row reward-row">
                    <div class="reward-toggle">
                        <input type="hidden" name="use_points" value="0">
                        <label class="reward-checkbox">
                            <input type="checkbox" id="use-points-toggle" name="use_points" value="1" <?= $maxRmFromPoints > 0 ? '' : 'disabled'; ?>>
                            <span class="reward-label-main">Use reward points · You have <?= number_format($availablePoints, 0); ?> pts</span>
                        </label>
                    </div>
                    <strong class="reward-deduction">- RM <span id="points-deduction">0.00</span> (<span id="points-used">0</span> pts)</strong>
                </div>
                <div class="cart-summary-row total-due">
                    <span>Payable Total</span>
                    <strong>RM <span id="payable-total"><?= number_format($grand, 2); ?></span></strong>
                </div>
            </div>

            <div class="cart-actions">
                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
                    <button class="btn secondary" type="submit">Update Cart</button>
                    <button
                        class="btn primary"
                        type="submit"
                        formaction="?module=cart&action=prepare_checkout"
                        formmethod="post"
                    >
                        Checkout Selected
                    </button>
                    <button
                        class="btn danger"
                        type="button"
                        id="batch-delete-btn"
                        style="display: none;"
                        onclick="batchDeleteItems()"
                    >
                        Delete Selected
                    </button>
                </div>
            </div>
        </form>
        
        <form id="batch-delete-form" method="post" action="?module=cart&action=batchRemove" style="display: none;">
        </form>
        <input type="hidden" id="available-points" value="<?= $availablePoints; ?>">
    <?php endif; ?>
</section>

<script>
(function() {
    const selectAll = document.getElementById('select-all-items');
    const pointToggle = document.getElementById('use-points-toggle');
    const availablePoints = parseInt(document.getElementById('available-points')?.value || '0', 10);
    const selectedTotalEl = document.getElementById('selected-total');
    const payableTotalEl = document.getElementById('payable-total');
    const deductionEl = document.getElementById('points-deduction');
    const pointsUsedEl = document.getElementById('points-used');

    if (!selectedTotalEl || !payableTotalEl || !deductionEl) {
        return;
    }

    function getItemCheckboxes() {
        return Array.from(document.querySelectorAll('.item-select'));
    }

    function updateLineTotals() {
        getItemCheckboxes().forEach(function(cb) {
            const inputId = cb.dataset.inputId;
            const quantityInput = document.getElementById(inputId);
            const price = parseFloat(cb.dataset.price || '0');
            const quantity = parseInt(quantityInput?.value || '0', 10);
            const lineTotal = price * (isNaN(quantity) ? 0 : quantity);
            cb.dataset.total = lineTotal.toFixed(2);
        });
    }

    function calculateSelectedTotal() {
        let total = 0;
        getItemCheckboxes().forEach(function(cb) {
            if (cb.checked) {
                total += parseFloat(cb.dataset.total || '0');
            }
        });
        return total;
    }

    function calculateDeduction(selectedTotal) {
        const maxRedeemableRm = Math.min(selectedTotal, Math.floor(availablePoints / 10));
        if (!pointToggle || !pointToggle.checked) {
            return {discount: 0, pointsUsed: 0};
        }
        return {
            discount: maxRedeemableRm,
            pointsUsed: maxRedeemableRm * 10
        };
    }

    function refreshSummary() {
        updateLineTotals();
        const selectedTotal = calculateSelectedTotal();
        selectedTotalEl.textContent = selectedTotal.toFixed(2);
        const {discount, pointsUsed} = calculateDeduction(selectedTotal);
        deductionEl.textContent = discount.toFixed(2);
        if (pointsUsedEl) {
            pointsUsedEl.textContent = (pointsUsed || 0).toLocaleString();
        }
        payableTotalEl.textContent = Math.max(0, selectedTotal - discount).toFixed(2);
        if (selectAll) {
            selectAll.checked = getItemCheckboxes().every(function(cb) { return cb.checked; });
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            const checked = this.checked;
            getItemCheckboxes().forEach(function(cb) {
                cb.checked = checked;
            });
            refreshSummary();
        });
    }

    function updateBatchDeleteButton() {
        const checked = document.querySelectorAll('.item-select:checked');
        const batchDeleteBtn = document.getElementById('batch-delete-btn');
        if (batchDeleteBtn) {
            batchDeleteBtn.style.display = checked.length > 0 ? 'inline-block' : 'none';
        }
    }

    getItemCheckboxes().forEach(function(cb) {
        cb.addEventListener('change', function() {
            if (!this.checked && selectAll) {
                selectAll.checked = false;
            }
            refreshSummary();
            updateBatchDeleteButton();
        });
    });

    document.querySelectorAll('input[type="number"][id^="qty-"]').forEach(function(input) {
        input.addEventListener('input', refreshSummary);
    });

    if (pointToggle) {
        pointToggle.addEventListener('change', refreshSummary);
    }

    refreshSummary();
})();

// Batch delete functionality
function batchDeleteItems() {
    const checkedBoxes = document.querySelectorAll('.item-select:checked');
    const itemIds = Array.from(checkedBoxes).map(cb => cb.value);
    
    if (itemIds.length === 0) {
        alert('Please select items to delete.');
        return;
    }
    
    if (confirm('Are you sure you want to delete ' + itemIds.length + ' selected item(s)?')) {
        const form = document.getElementById('batch-delete-form');
        
        // Clear any existing hidden inputs
        form.innerHTML = '';
        
        // Create multiple hidden inputs for each ID
        itemIds.forEach(id => {
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'item_ids[]';
            hidden.value = id;
            form.appendChild(hidden);
        });
        
        form.submit();
    }
}

// Initialize batch delete button visibility
(function() {
    function updateBatchDeleteButton() {
        const checked = document.querySelectorAll('.item-select:checked');
        const batchDeleteBtn = document.getElementById('batch-delete-btn');
        if (batchDeleteBtn) {
            batchDeleteBtn.style.display = checked.length > 0 ? 'inline-block' : 'none';
        }
    }
    
    // Update on page load
    updateBatchDeleteButton();
})();
</script>
