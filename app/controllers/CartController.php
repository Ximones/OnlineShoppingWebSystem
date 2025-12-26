<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\SavedAddress;
use App\Models\User;
use App\Models\Payment;
use App\Models\UserVoucher;
use App\Services\StripeService;

require_once __DIR__ . '/../lib/mail/mail_helper.php';

class CartController extends Controller
{
    private const CHECKOUT_SESSION_KEY = 'checkout_context';

    // Model instances
    private Cart $cart;
    private Product $products;
    private Order $orders;
    private User $users;
    private SavedAddress $savedAddresses;
    private UserVoucher $userVouchers;
    private Payment $payments;
    /**
     * Simple shipping options: code => [label, fee]
     * Fee is in RM.
     */
    private array $shippingOptions = [
        'standard' => ['label' => 'Standard Shipping (3-5 days)', 'fee' => 10.0],
        'express'  => ['label' => 'Express Shipping (1-2 days)', 'fee' => 25.0],
        'pickup'   => ['label' => 'Self Pickup (Free)', 'fee' => 0.0],
    ];

    // Initialize model instances
    public function __construct()
    {
        $this->cart = new Cart();
        $this->products = new Product();
        $this->orders = new Order();
        $this->users = new User();
        $this->savedAddresses = new SavedAddress();
        $this->userVouchers = new UserVoucher();
        $this->payments = new Payment();
    }

    // Display cart items
    public function index(): void
    {
        $this->requireAuth();
        $cartId = $this->cart->activeCartId(auth_id());
        $items = $this->cart->items($cartId);
        $user = $this->users->find(auth_id());
        $this->render('shop/cart', compact('items', 'user'));
    }

    // Add product to cart
    public function add(): void
    {
        $this->requireAuth();
        if (is_post()) {
            $productId = (int) post('product_id');
            $quantity = max(1, (int) post('quantity', 1));
            $product = $this->products->find($productId);
            if (!$product) {
                flash('danger', 'Product not found.');
                redirect('?module=shop&action=catalog');
            }
            $cartId = $this->cart->activeCartId(auth_id());
            $this->cart->addItem($cartId, $productId, $quantity);
            flash('success', 'Product added to cart.');
        }
        redirect('?module=cart&action=index');
    }

    // Buy now - add to cart and go directly to checkout
    public function buy_now(): void
    {
        $this->requireAuth();
        if (is_post()) {
            $productId = (int) post('product_id');
            $quantity = max(1, (int) post('quantity', 1));
            $product = $this->products->find($productId);
            if (!$product) {
                flash('danger', 'Product not found.');
                redirect('?module=shop&action=catalog');
            }
            $cartId = $this->cart->activeCartId(auth_id());
            $this->cart->addItem($cartId, $productId, $quantity);
            unset($_SESSION[self::CHECKOUT_SESSION_KEY]);
        }
        redirect('?module=cart&action=checkout');
    }

    // Update cart item quantities
    public function update(): void
    {
        $this->requireAuth();
        foreach (post('items', []) as $itemId => $quantity) {
            $this->cart->updateItem((int) $itemId, max(1, (int) $quantity));
        }
        flash('success', 'Cart updated.');
        redirect('?module=cart&action=index');
    }

    // Remove single item from cart
    public function remove(): void
    {
        $this->requireAuth();
        $itemId = (int) post('item_id');
        $this->cart->removeItem($itemId);
        flash('success', 'Item removed.');
        redirect('?module=cart&action=index');
    }

    // Remove multiple items from cart
    public function batchRemove(): void
    {
        $this->requireAuth();
        $itemIds = array_map('intval', post('item_ids', []));
        if (empty($itemIds)) {
            flash('danger', 'No items selected.');
            redirect('?module=cart&action=index');
        }
        $this->cart->removeItems($itemIds);
        flash('success', count($itemIds) . ' item(s) removed.');
        redirect('?module=cart&action=index');
    }

    // Checkout process - handle order creation and payment
    public function checkout(): void
    {
        $this->requireAuth();
        $userId = auth_id();
        $cartId = $this->cart->activeCartId($userId);
        $items = $this->cart->items($cartId);
        if (empty($items)) {
            flash('danger', 'Cart is empty.');
            redirect('?module=shop&action=catalog');
        }

        // Get selected items from session context
        $context = $_SESSION[self::CHECKOUT_SESSION_KEY] ?? null;
        $selectedIds = $context['selected_item_ids'] ?? array_column($items, 'id');
        $selectedIds = array_map('intval', $selectedIds);
        if ($selectedIds) {
            $items = array_values(array_filter($items, fn($item) => in_array((int) $item['id'], $selectedIds, true)));
        }
        if (empty($items)) {
            flash('danger', 'Please select at least one item to checkout.');
            redirect('?module=cart&action=index');
        }

        // Check stock availability before proceeding
        foreach ($items as $item) {
            $product = $this->products->find($item['product_id']);
            if (!$product) {
                flash('danger', 'Product "' . $item['name'] . '" not found.');
                redirect('?module=cart&action=index');
            }
            if ($product['stock'] < $item['quantity']) {
                flash('danger', 'Insufficient stock for "' . $item['name'] . '". Available: ' . $product['stock'] . ', Requested: ' . $item['quantity']);
                redirect('?module=cart&action=index');
            }
        }

        // Determine use of reward points (allow toggle on checkout page)
        $usePoints = !empty($context['use_points']);
        if (is_post() && post('action') !== 'save_address') {
            $usePoints = post('use_points') === '1';
        }

        // Determine shipping & voucher from current request or stored context
        $shippingMethod = post('shipping_method', $context['shipping_method'] ?? 'standard');
        $voucherCode = trim((string) post('voucher_code', $context['voucher_code'] ?? ''));

        // Handle saving address from modal
        if (is_post() && post('action') === 'save_address') {
            if (validate([
                'label' => ['required' => 'Address label is required.'],
                'name' => ['required' => 'Recipient name is required.'],
                'phone' => ['required' => 'Phone is required.'],
                'address' => ['required' => 'Address is required.'],
            ])) {
                $this->savedAddresses->create([
                    'user_id' => $userId,
                    'label' => post('label'),
                    'name' => post('name'),
                    'phone' => post('phone'),
                    'address' => post('address'),
                    'is_default' => post('is_default') ? 1 : 0,
                ]);
                flash('success', 'Address saved.');
                redirect('?module=cart&action=checkout');
            }
        }

        // Handle order creation
        $checkoutStep = post('checkout_step', '');

        if (is_post() && post('action') !== 'save_address' && $checkoutStep !== 'update_pricing' && validate([
            'shipping_name' => ['required' => 'Shipping name is required.'],
            'shipping_phone' => ['required' => 'Phone is required.'],
            'shipping_address' => ['required' => 'Address is required.'],
        ])) {
            // Final stock check before creating order
            foreach ($items as $item) {
                $product = $this->products->find($item['product_id']);
                if (!$product || $product['stock'] < $item['quantity']) {
                    flash('danger', 'Stock availability changed. Please review your cart.');
                    redirect('?module=cart&action=index');
                }
            }

            $user = $this->users->find($userId);
            $userVouchers = $this->userVouchers->activeForUser($userId);
            $orderCount = $this->orders->countByUser($userId);
            $selectedVoucher = null;
            if ($voucherCode !== '') {
                foreach ($userVouchers as $uv) {
                    if (strcasecmp($uv['code'], $voucherCode) === 0) {
                        $selectedVoucher = $uv;
                        break;
                    }
                }
            }

            $pricingSummary = $this->calculatePricingSummary($items, $user, $usePoints, $shippingMethod, $selectedVoucher, $orderCount);

            if ($pricingSummary['payable_total'] > 999999.99) {
                flash('danger', 'Orders exceed standard purchase. Please contact support to arrange a wire transfer.');
                redirect('?module=cart&action=index');
                return;
            }

            $paymentMethod = post('payment_method', 'Stripe');
            $paylaterTenure = (int) post('paylater_tenure', 3);
            if (!in_array($paylaterTenure, [3, 6, 12], true)) {
                $paylaterTenure = 3;
            }

            // Stripe payments are 'pending' until payment is confirmed, PayLater is 'paid' immediately
            $orderStatus = ($paymentMethod === 'Stripe') ? 'pending' : 'paid';

            // Compute PayLater charges and enforce credit limit
            $amount = (float) $pricingSummary['payable_total'];
            $interestRate = 0.0;
            $paylaterTotal = $amount;
            if ($paymentMethod === 'PayLater' && $amount > 0) {
                if ($paylaterTenure === 6) {
                    $interestRate = 0.015;
                } elseif ($paylaterTenure === 12) {
                    $interestRate = 0.025;
                } else {
                    $interestRate = 0.0;
                }
                $paylaterTotal = round($amount * (1 + $interestRate), 2);

                // Get user's credit limit from database
                $user = $this->users->find($userId);
                $creditLimit = $user ? (float)($user['paylater_credit_limit'] ?? 10000.0) : 10000.0;

                // Use credit limit only on principal (amount), not interest
                $outstandingPrincipal = $this->payments->outstandingPayLaterPrincipal($userId);
                $availablePrincipal = max(0.0, $creditLimit - $outstandingPrincipal);
                $principalUsed = min($amount, $availablePrincipal);
                if ($principalUsed <= 0) {
                    flash('danger', 'Your PayLater credit limit of RM ' . number_format($creditLimit, 2) . ' is fully used. Please pay existing bills first.');
                    redirect('?module=cart&action=checkout');
                }

                $interestTotal = $principalUsed * $interestRate;
                $principalImmediate = max(0.0, $amount - $principalUsed);

                // Total financed via PayLater (principal + interest on that principal)
                $paylaterTotal = round($principalUsed + $interestTotal, 2);

                // Compute per-instalment principal & full payment
                $principalPerMonth = floor(($principalUsed / $paylaterTenure) * 100) / 100;
                $principalLast = round($principalUsed - $principalPerMonth * ($paylaterTenure - 1), 2);

                $paymentPerMonth = floor(($paylaterTotal / $paylaterTenure) * 100) / 100;
                $paymentLast = round($paylaterTotal - $paymentPerMonth * ($paylaterTenure - 1), 2);
            }

            $orderId = $this->orders->createFromCart($userId, $cartId, [
                'name' => post('shipping_name'),
                'phone' => post('shipping_phone'),
                'address' => post('shipping_address'),
            ], [
                'item_ids' => array_column($items, 'id'),
                'points_redeemed' => $pricingSummary['points_redeemed'],
                'points_discount' => $pricingSummary['points_discount'],
                'shipping_fee' => $pricingSummary['shipping_fee'],
                'shipping_method' => $shippingMethod,
                'voucher_discount' => $pricingSummary['voucher_discount'],
                'voucher_code' => $pricingSummary['voucher_code'],
                'order_status' => $orderStatus,
            ]);

            // Reduce stock for each product in the order
            foreach ($items as $item) {
                $this->products->reduceStock($item['product_id'], $item['quantity']);
            }

            // Mark voucher as used if one was applied
            if ($selectedVoucher && !empty($pricingSummary['voucher_code'])) {
                $this->userVouchers->markUsed($selectedVoucher['id'], $orderId);
            }

            // Record payment(s)
            if ($amount > 0) {
                if ($paymentMethod === 'PayLater') {
                    // Create PayLater instalments (principal + interest)
                    $startDate = new \DateTimeImmutable('today');
                    for ($i = 1; $i <= $paylaterTenure; $i++) {
                        $isLast = $i === $paylaterTenure;
                        $instPrincipal = $isLast ? $principalLast : $principalPerMonth;
                        $instAmount = $isLast ? $paymentLast : $paymentPerMonth;
                        $dueDate = $startDate->modify('+' . $i . ' month')->format('Y-m-d');
                        $this->payments->create(
                            $orderId,
                            'PayLater',
                            $instAmount,
                            $instPrincipal,
                            'pending', // PayLater instalments are pending until user pays them
                            null,
                            $paylaterTenure,
                            $interestRate * 100,
                            $dueDate
                        );
                    }

                    // If principal exceeds available credit, remaining part must be paid immediately
                    if (!empty($principalImmediate) && $principalImmediate > 0) {
                        $upfrontMethod = post('paylater_upfront_method', 'Stripe');
                        $this->payments->create(
                            $orderId,
                            $upfrontMethod,
                            $principalImmediate,
                            $principalImmediate,
                            'completed'
                        );
                    }
                } elseif ($paymentMethod === 'Stripe') {
                    // 1. Prepare Items
                    $lineItems = [];
                    foreach ($items as $item) {
                        $lineItems[] = [
                            'price_data' => [
                                'currency' => 'myr',
                                'product_data' => ['name' => $item['name']],
                                'unit_amount' => $amount * 100,
                            ],
                            'quantity' => (int)$item['quantity'],
                        ];
                    }

                    // 2. Use the Service
                    $stripe = new StripeService();
                    $session = $stripe->createCheckoutSession(
                        $lineItems,
                        url('?module=cart&action=stripe_success&session_id={CHECKOUT_SESSION_ID}&order_id=' . $orderId),
                        url('?module=cart&action=stripe_cancel&order_id=' . $orderId)
                    );

                    // 3. Redirect
                    header("HTTP/1.1 303 See Other");
                    header("Location: " . $session->url);
                    exit();
                } else {
                    // Fallback for any other payment method (shouldn't happen with current design)
                    $this->payments->create($orderId, $paymentMethod, $amount, $amount, 'completed');
                }
            }

            $this->payments->create($orderId, $paymentMethod, $amount, $amount, 'completed');
            
            $orderModel = new Order();
            $orderDetail = $orderModel->detail($orderId);
            $html = render_ereceipt($orderDetail, $user);

            $this->sendOrderReceipt($orderId);

            unset($_SESSION[self::CHECKOUT_SESSION_KEY]);
            flash('success', 'Order placed successfully. Please check your email for your e-receipt.');
            redirect("?module=orders&action=detail&id=$orderId");
        }

        // Render View
        $user = $this->users->find($userId);
        $savedAddresses = $this->savedAddresses->findByUser($userId);
        $userVouchers = $this->userVouchers->activeForUser($userId);
        $orderCount = $this->orders->countByUser($userId);
        $outstandingPayLater = $this->payments->outstandingPayLaterPrincipal($userId);

        // Find selected voucher row (if any) for current user
        $selectedVoucher = null;
        if ($voucherCode !== '') {
            foreach ($userVouchers as $uv) {
                if (strcasecmp($uv['code'], $voucherCode) === 0) {
                    $selectedVoucher = $uv;
                    break;
                }
            }
        }

        $pricingSummary = $this->calculatePricingSummary($items, $user, $usePoints, $shippingMethod, $selectedVoucher, $orderCount);
        // Use effective voucher code from pricing summary (empty if not applicable)
        $voucherCode = $pricingSummary['voucher_code'] ?? $voucherCode;

        // Persist latest context for subsequent requests
        $_SESSION[self::CHECKOUT_SESSION_KEY] = [
            'selected_item_ids' => $selectedIds,
            'use_points' => $usePoints,
            'shipping_method' => $shippingMethod,
            'voucher_code' => $voucherCode,
        ];

        $shippingOptions = $this->shippingOptions;
        $this->render('shop/checkout', compact(
            'items',
            'user',
            'savedAddresses',
            'pricingSummary',
            'shippingOptions',
            'shippingMethod',
            'voucherCode',
            'userVouchers',
            'orderCount',
            'outstandingPayLater'
        ));
    }

    // Prepare checkout - update cart and save selected items to session
    public function prepare_checkout(): void
    {
        $this->requireAuth();

        // Update cart quantities if provided
        if (post('items')) {
            foreach (post('items', []) as $itemId => $quantity) {
                $this->cart->updateItem((int) $itemId, max(1, (int) $quantity));
            }
        }

        // Save selected items and preferences to session
        $selected = array_filter(array_map('intval', post('selected_items', [])));
        if (empty($selected)) {
            flash('danger', 'Select at least one item to proceed.');
            redirect('?module=cart&action=index');
        }
        $_SESSION[self::CHECKOUT_SESSION_KEY] = [
            'selected_item_ids' => $selected,
            'use_points' => post('use_points') === '1',
            'shipping_method' => 'standard',
            'voucher_code' => '',
        ];
        redirect('?module=cart&action=checkout');
    }

    // Calculate pricing summary including discounts, shipping, and vouchers
    private function calculatePricingSummary(array $items, ?array $user, bool $usePoints, string $shippingMethod, ?array $voucher, int $orderCount): array
    {
        $subtotal = array_reduce($items, fn($carry, $item) => $carry + ($item['price'] * $item['quantity']), 0.0);
        $availablePoints = (int) floor((float) ($user['reward_points'] ?? 0));
        $maxRedeemableRm = min($subtotal, (int) floor($availablePoints / 10));
        $pointsRedeemed = $usePoints ? $maxRedeemableRm * 10 : 0;
        $pointsDiscount = $pointsRedeemed / 10;

        // Shipping
        if (!isset($this->shippingOptions[$shippingMethod])) {
            $shippingMethod = 'standard';
        }
        $shippingFee = (float) $this->shippingOptions[$shippingMethod]['fee'];

        // Voucher from database / claimed list
        $voucherDiscountMerch = 0.0;
        $shippingDiscount = 0.0;
        $appliedVoucher = '';

        if ($voucher && !empty($voucher['code'])) {
            $code = strtoupper($voucher['code']);
            $type = $voucher['type'];
            $value = (float) $voucher['value'];
            $minSubtotal = (float) ($voucher['min_subtotal'] ?? 0);
            $maxDiscount = $voucher['max_discount'] !== null ? (float) $voucher['max_discount'] : null;
            $isShippingOnly = !empty($voucher['is_shipping_only']);
            $isFirstOrderOnly = !empty($voucher['is_first_order_only']);

            $eligible = true;
            if ($minSubtotal > 0 && $subtotal < $minSubtotal) {
                $eligible = false;
            }
            if ($isFirstOrderOnly && $orderCount > 0) {
                $eligible = false;
            }

            if ($eligible) {
                if ($type === 'amount') {
                    $voucherDiscountMerch = min($value, $subtotal);
                } elseif ($type === 'percent') {
                    $voucherDiscountMerch = round($subtotal * ($value / 100), 2);
                } elseif ($type === 'shipping_amount') {
                    $shippingDiscount = min($value, $shippingFee);
                } elseif ($type === 'free_shipping') {
                    $shippingDiscount = $shippingFee;
                }

                if ($maxDiscount !== null) {
                    if ($isShippingOnly) {
                        $shippingDiscount = min($shippingDiscount, $maxDiscount);
                    } else {
                        $voucherDiscountMerch = min($voucherDiscountMerch, $maxDiscount);
                    }
                }

                $appliedVoucher = $code;
            }
        }

        $baseTotal = max(0.0, $subtotal - $pointsDiscount - $voucherDiscountMerch);
        $effectiveShippingFee = max(0.0, $shippingFee - $shippingDiscount);
        $payable = $baseTotal + $effectiveShippingFee;
        $totalVoucherDiscount = $voucherDiscountMerch + $shippingDiscount;

        return [
            'subtotal' => $subtotal,
            'available_points' => $availablePoints,
            'max_redeemable_rm' => $maxRedeemableRm,
            'points_redeemed' => $pointsRedeemed,
            'points_discount' => $pointsDiscount,
            'voucher_code' => $appliedVoucher,
            'voucher_discount' => $voucherDiscountMerch, // Only merchandise discount, not shipping
            'voucher_shipping_discount' => $shippingDiscount, // Separate shipping discount
            'shipping_method' => $shippingMethod,
            'shipping_fee' => $effectiveShippingFee,
            'payable_total' => $payable,
            'use_points' => $usePoints,
        ];
    }

    // Handle successful Stripe payment
    public function stripe_success(): void
    {
        $this->requireAuth();
        $sessionId = get('session_id');
        $orderId = get('order_id');

        $stripe = new StripeService();

        if ($stripe->isPaid($sessionId)) {
            // Record Payment
            $amountPaid = $stripe->getAmount($sessionId);
            $methodLabel = $stripe->getPaymentMethodLabel($sessionId);
            $this->payments->create($orderId, $methodLabel, $amountPaid, $amountPaid, 'completed');

            // Update Order Status
            $this->orders->updateStatus($orderId, 'paid');

            // Send E-Receipt Email
            $this->sendOrderReceipt($orderId);

            // Clear Session
            unset($_SESSION[self::CHECKOUT_SESSION_KEY]);

            flash('success', 'Payment successful! Order confirmed.');
            redirect("?module=orders&action=detail&id=$orderId");
        } else {
            flash('danger', 'Payment verification failed.');
            redirect('?module=cart&action=checkout');
        }
    }

    // Handle cancelled Stripe payment - restore stock and cart items
    public function stripe_cancel(): void
    {
        $this->requireAuth();
        $orderId = get('order_id');
        $userId = auth_id();

        // Cancel order and restore stock
        $this->orders->updateStatus($orderId, 'cancelled');
        $order = $this->orders->detail($orderId);
        if ($order && $order['user_id'] == $userId) {
            foreach ($order['items'] as $item) {
                $this->products->restoreStock((int)$item['product_id'], (int)$item['quantity']);
            }
        }

        // Restore Items to Cart (so user can try again)
        $order = $this->orders->detail($orderId);
        if ($order && $order['user_id'] == $userId) {
            $cartId = $this->cart->activeCartId($userId);
            foreach ($order['items'] as $item) {
                $this->cart->addItem($cartId, (int)$item['product_id'], (int)$item['quantity']);
            }
        }

        flash('warning', 'Payment was cancelled. Your items have been restored to the cart.');
        redirect('?module=cart&action=checkout');
    }

    private function sendOrderReceipt(int $orderId): void
    {
        // 1. Fetch full order details (items, shipping, etc.)
        $order = $this->orders->detail($orderId);
        if (!$order) {
            error_log("Receipt Error: Order #$orderId not found.");
            return;
        }

        // 2. Fetch User to get current email
        $user = $this->users->find($order['user_id']);
        if (!$user) {
            error_log("Receipt Error: User ID {$order['user_id']} not found.");
            return;
        }

        // 3. Render HTML
        // Ensure render_ereceipt exists (from mail_helper.php)
        $html = render_ereceipt($order, $user);

        // 4. Send Email
        try {
            $mail = get_mail();
            $mail->addAddress($user['email'], $user['name']);
            $mail->Subject = 'Your E-Receipt (Order #' . $orderId . ')';
            $mail->Body = $html;
            $mail->send();
        } catch (\Throwable $e) {
            error_log('Receipt email failed: ' . $e->getMessage());
        }
    }
}
