<?php $title = 'Vouchers'; ?>
<?php
$claimedByUser = $claimedByUser ?? [];
$claimCounts = $claimCounts ?? [];
?>

<section class="panel">
    <h2 style="margin-top: 0;">Available Vouchers</h2>
    <?php if (empty($allVouchers)): ?>
        <p style="color: var(--color-text-muted);">No vouchers available at the moment.</p>
    <?php else: ?>
        <div class="vouchers-grid">
            <?php foreach ($allVouchers as $voucher): ?>
                <?php
                $voucherId = (int) $voucher['id'];
                $userStatus = $claimedByUser[$voucherId] ?? null;
                $totalClaims = $claimCounts[$voucherId] ?? 0;
                $maxClaims = $voucher['max_claims'] !== null ? (int) $voucher['max_claims'] : null;

                $isClaimed = $userStatus !== null;
                $isSoldOut = $maxClaims !== null && $totalClaims >= $maxClaims;

                if ($isSoldOut) {
                    $btnLabel = 'Fully Redeemed';
                    $btnClass = 'btn is-disabled';
                    $btnDisabled = true;
                } elseif ($isClaimed) {
                    $btnLabel = 'Claimed';
                    $btnClass = 'btn is-disabled';
                    $btnDisabled = true;
                } else {
                    $btnLabel = 'Claim Voucher';
                    $btnClass = 'btn primary';
                    $btnDisabled = false;
                }
                ?>
                <div class="voucher-card-new">
                    <div class="voucher-card-header">
                        <div class="voucher-card-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M20 6H4C2.9 6 2 6.9 2 8V16C2 17.1 2.9 18 4 18H20C21.1 18 22 17.1 22 16V8C22 6.9 21.1 6 20 6Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M2 10H22" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <path d="M8 14H16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <div class="voucher-card-title"><?= encode($voucher['name']); ?></div>
                    </div>
                    <div class="voucher-card-body">
                        <div class="voucher-card-code"><?= encode($voucher['code']); ?></div>
                        <?php if (!empty($voucher['description'])): ?>
                            <p class="voucher-card-description"><?= nl2br(encode($voucher['description'])); ?></p>
                        <?php endif; ?>
                        <div class="voucher-card-details">
                            <?php if ($voucher['type'] === 'amount'): ?>
                                <div class="voucher-detail-item">
                                    <span class="voucher-detail-label">Discount</span>
                                    <span class="voucher-detail-value">RM <?= number_format($voucher['value'], 2); ?></span>
                                </div>
                            <?php elseif ($voucher['type'] === 'percent'): ?>
                                <div class="voucher-detail-item">
                                    <span class="voucher-detail-label">Discount</span>
                                    <span class="voucher-detail-value"><?= number_format($voucher['value'], 0); ?>% off</span>
                                </div>
                            <?php elseif ($voucher['type'] === 'shipping_amount'): ?>
                                <div class="voucher-detail-item">
                                    <span class="voucher-detail-label">Shipping</span>
                                    <span class="voucher-detail-value">RM <?= number_format($voucher['value'], 2); ?> off</span>
                                </div>
                            <?php else: ?>
                                <div class="voucher-detail-item">
                                    <span class="voucher-detail-label">Shipping</span>
                                    <span class="voucher-detail-value">Free</span>
                                </div>
                            <?php endif; ?>
                            <?php if ($voucher['min_subtotal'] > 0): ?>
                                <div class="voucher-detail-item">
                                    <span class="voucher-detail-label">Min. spend</span>
                                    <span class="voucher-detail-value">RM <?= number_format($voucher['min_subtotal'], 2); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <form method="post" action="?module=vouchers&action=claim" class="voucher-card-footer">
                        <input type="hidden" name="voucher_id" value="<?= $voucher['id']; ?>">
                        <button type="submit"
                                class="<?= $btnClass; ?>"
                                <?= $btnDisabled ? 'disabled' : ''; ?>>
                            <?= encode($btnLabel); ?>
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section class="panel">
    <h2 style="margin-top: 0;">My Vouchers</h2>
    <?php if (empty($userVouchers)): ?>
        <p style="color: var(--color-text-muted);">You have not claimed any vouchers yet.</p>
    <?php else: ?>
        <?php
        $activeVouchers = [];
        $usedVouchers = [];
        foreach ($userVouchers as $uv) {
            if (strtolower($uv['status']) === 'used') {
                $usedVouchers[] = $uv;
            } else {
                $activeVouchers[] = $uv;
            }
        }
        ?>

        <div class="paylater-tabs" style="margin-bottom: 1rem;">
            <button type="button" class="paylater-tab-btn is-active" data-tab="my-vouchers-active">Active</button>
            <button type="button" class="paylater-tab-btn" data-tab="my-vouchers-used">Used history</button>
        </div>

        <div class="paylater-tabs-panel">
            <div id="my-vouchers-active" class="paylater-tab-content is-active">
                <?php if (empty($activeVouchers)): ?>
                    <p style="color: var(--color-text-muted);">You don't have any active vouchers.</p>
                <?php else: ?>
                    <div class="vouchers-grid">
                        <?php foreach ($activeVouchers as $uv): ?>
                            <div class="voucher-card-new voucher-card-owned">
                                <div class="voucher-card-header">
                                    <div class="voucher-card-icon">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M20 6H4C2.9 6 2 6.9 2 8V16C2 17.1 2.9 18 4 18H20C21.1 18 22 17.1 22 16V8C22 6.9 21.1 6 20 6Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M2 10H22" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                    </div>
                                    <div class="voucher-card-title"><?= encode($uv['name']); ?></div>
                                </div>
                                <div class="voucher-card-body">
                                    <div class="voucher-card-code"><?= encode($uv['code']); ?></div>
                                    <div class="voucher-card-status">
                                        <span class="voucher-status-badge voucher-status-<?= strtolower($uv['status']); ?>">
                                            <?= encode(ucfirst($uv['status'])); ?>
                                        </span>
                                    </div>
                                    <div class="voucher-card-meta">
                                        <div class="voucher-meta-item">
                                            <span class="voucher-meta-label">Claimed</span>
                                            <span class="voucher-meta-value"><?= encode($uv['claimed_at']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div id="my-vouchers-used" class="paylater-tab-content">
                <?php if (empty($usedVouchers)): ?>
                    <p style="color: var(--color-text-muted);">You have not used any vouchers yet.</p>
                <?php else: ?>
                    <div class="vouchers-grid">
                        <?php foreach ($usedVouchers as $uv): ?>
                            <div class="voucher-card-new voucher-card-owned is-used">
                                <div class="voucher-card-header">
                                    <div class="voucher-card-icon">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>
                                    <div class="voucher-card-title"><?= encode($uv['name']); ?></div>
                                </div>
                                <div class="voucher-card-body">
                                    <div class="voucher-card-code"><?= encode($uv['code']); ?></div>
                                    <div class="voucher-card-status">
                                        <span class="voucher-status-badge voucher-status-used">
                                            Used
                                        </span>
                                    </div>
                                    <div class="voucher-card-meta">
                                        <div class="voucher-meta-item">
                                            <span class="voucher-meta-label">Claimed</span>
                                            <span class="voucher-meta-value"><?= encode($uv['claimed_at']); ?></span>
                                        </div>
                                        <?php if (!empty($uv['used_at'])): ?>
                                            <div class="voucher-meta-item">
                                                <span class="voucher-meta-label">Used</span>
                                                <span class="voucher-meta-value"><?= encode($uv['used_at']); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabButtons = document.querySelectorAll('.paylater-tab-btn[data-tab^=\"my-vouchers-\"]');
    const tabContents = document.querySelectorAll('#my-vouchers-active, #my-vouchers-used');

    tabButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const targetId = this.getAttribute('data-tab');

            tabButtons.forEach(function (b) { b.classList.remove('is-active'); });
            this.classList.add('is-active');

            tabContents.forEach(function (panel) {
                panel.classList.toggle('is-active', panel.id === targetId);
            });
        });
    });
});
</script>
