<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #000;
            line-height: 1.6;
        }

        .container {
            max-width: 700px;
            margin: 0 auto;
            padding: 20px;
        }

        h2 {
            margin-bottom: 6px;
            font-size: 22px;
        }

        .order-info {
            margin-bottom: 20px;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 14px;
        }

        th,
        td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            font-weight: bold;
            background-color: #f7f7f7;
        }

        .text-right {
            text-align: right;
        }

        .summary-row td {
            font-weight: bold;
        }

        .discount-row td {
            color: #0e3d73;
        }

        .shipping-info {
            margin-top: 16px;
            font-size: 14px;
        }

        .payment-info {
            margin-top: 16px;
            font-size: 14px;
            padding: 10px;
            background-color: #f9f9f9;
            border-left: 4px solid #0e3d73;
        }

        .footer-message {
            margin-top: 30px;
            font-size: 16px;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <div class="container">

    <h1>🚽 Daily Bowls</h1>

        <h2>Thank you for your order, <?= htmlspecialchars($user['name']) ?>!</h2>

        <div class="order-info">
            <strong>Order ID:</strong> <?= $order['id'] ?><br>
            <strong>Date:</strong> <?= $order['created_at'] ?>
        </div>

        <h3>Order Items</h3>

        <table>
            <tr>
                <th>Item</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Price</th>
                <th class="text-right">Total</th>
            </tr>

            <?php foreach ($order['items'] as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td class="text-right"><?= $item['quantity'] ?></td>
                    <td class="text-right">RM <?= number_format($item['unit_price'], 2) ?></td>
                    <td class="text-right">
                        RM <?= number_format($item['unit_price'] * $item['quantity'], 2) ?>
                    </td>
                </tr>
            <?php endforeach; ?>

            <tr class="summary-row">
                <td colspan="3" class="text-right">Subtotal</td>
                <td class="text-right">RM <?= number_format($order['subtotal'], 2) ?></td>
            </tr>

            <?php if (!empty($order['points_discount']) && $order['points_discount'] > 0): ?>
                <tr class="discount-row">
                    <td colspan="3" class="text-right"><strong>Reward Points Discount</strong></td>
                    <td class="text-right">-RM <?= number_format($order['points_discount'], 2) ?></td>
                </tr>
            <?php endif; ?>

            <?php if (!empty($order['voucher_discount']) && $order['voucher_discount'] > 0): ?>
                <tr class="discount-row">
                    <td colspan="3" class="text-right"><strong>Voucher Discount</strong></td>
                    <td class="text-right">-RM <?= number_format($order['voucher_discount'], 2) ?></td>
                </tr>
            <?php endif; ?>

            <?php if (!empty($order['shipping_voucher_discount']) && $order['shipping_voucher_discount'] > 0): ?>
                <tr class="discount-row">
                    <td colspan="3" class="text-right"><strong>Shipping Discount</strong></td>
                    <td class="text-right">-RM <?= number_format($order['shipping_voucher_discount'], 2) ?></td>
                </tr>
            <?php endif; ?>

            <?php if (!empty($order['shipping_fee']) && $order['shipping_fee'] > 0): ?>
                <tr class="summary-row">
                    <td colspan="3" class="text-right">Shipping Fee</td>
                    <td class="text-right">RM <?= number_format($order['shipping_fee'], 2) ?></td>
                </tr>
            <?php endif; ?>

            <tr class="summary-row">
                <td colspan="3" class="text-right">Total Paid</td>
                <td class="text-right">RM <?= number_format($order['total_amount'], 2) ?></td>
            </tr>
        </table>

        <br>

        <div class="shipping-info">
            <strong>Shipping Method:</strong> <?= htmlspecialchars($order['shipping_method']) ?><br>
            <strong>Shipping Address:</strong><br>
            <?= nl2br(htmlspecialchars($order['shipping_address'])) ?>
        </div>
        
        <div class="footer-message">
            We sincerely appreciate your purchase and the trust you have placed in us.
            <br>
            Thank you for choosing our store.
        </div>

    </div>

</body>

</html>