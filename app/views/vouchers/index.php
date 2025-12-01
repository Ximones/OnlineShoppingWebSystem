<?php $title = 'Vouchers'; ?>

<section class="panel">
    <h2>Available Vouchers</h2>
    <?php if (empty($allVouchers)): ?>
        <p>No vouchers available at the moment.</p>
    <?php else: ?>
        <div class="grid">
            <?php foreach ($allVouchers as $voucher): ?>
                <div class="card">
                    <h3 style="margin-top: 0;"><?= encode($voucher['name']); ?></h3>
                    <p style="margin: 0.25rem 0; font-weight: bold;"><?= encode($voucher['code']); ?></p>
                    <p style="margin: 0.25rem 0;"><?= nl2br(encode($voucher['description'] ?? '')); ?></p>
                    <p style="margin: 0.25rem 0; font-size: 0.9rem; color: #555;">
                        <?php if ($voucher['type'] === 'amount'): ?>
                            Discount: RM <?= number_format($voucher['value'], 2); ?>
                        <?php elseif ($voucher['type'] === 'percent'): ?>
                            Discount: <?= number_format($voucher['value'], 0); ?>% off
                        <?php elseif ($voucher['type'] === 'shipping_amount'): ?>
                            Shipping Discount: RM <?= number_format($voucher['value'], 2); ?>
                        <?php else: ?>
                            Free Shipping
                        <?php endif; ?>
                        <?php if ($voucher['min_subtotal'] > 0): ?>
                            <br>Min spend: RM <?= number_format($voucher['min_subtotal'], 2); ?>
                        <?php endif; ?>
                    </p>
                    <form method="post" action="?module=vouchers&action=claim">
                        <input type="hidden" name="voucher_id" value="<?= $voucher['id']; ?>">
                        <button type="submit" class="btn primary btn small">Claim</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section class="panel">
    <h2>My Vouchers</h2>
    <?php if (empty($userVouchers)): ?>
        <p>You have not claimed any vouchers yet.</p>
    <?php else: ?>
        <table class="table">
            <thead>
            <tr>
                <th>Code</th>
                <th>Name</th>
                <th>Status</th>
                <th>Claimed At</th>
                <th>Used At</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($userVouchers as $uv): ?>
                <tr>
                    <td><?= encode($uv['code']); ?></td>
                    <td><?= encode($uv['name']); ?></td>
                    <td><?= encode($uv['status']); ?></td>
                    <td><?= encode($uv['claimed_at']); ?></td>
                    <td><?= encode($uv['used_at'] ?? '-'); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>


