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
            $itemId = $this->cart->addItem($cartId, $productId, $quantity);
            
            // Set session context to only include this newly added item for checkout
            // This ensures "buy now" only checks out this product, not all cart items
            $_SESSION[self::CHECKOUT_SESSION_KEY] = [
                'selected_item_ids' => [$itemId], // Only the newly added item
                'use_points' => false,
                'shipping_method' => 'standard',
                'voucher_code' => '',
            ];
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
        $voucherIdFromPost = post('voucher_id') ? (int) post('voucher_id') : null;

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
                // If voucher_id is provided from form, use that to find the exact voucher instance
                if ($voucherIdFromPost) {
                    foreach ($userVouchers as $uv) {
                        if ((int) $uv['id'] === $voucherIdFromPost && strcasecmp($uv['code'], $voucherCode) === 0) {
                            $selectedVoucher = $uv;
                            break;
                        }
                    }
                }
                // Fallback to finding by code only (for backwards compatibility)
                if (!$selectedVoucher) {
                    foreach ($userVouchers as $uv) {
                        if (strcasecmp($uv['code'], $voucherCode) === 0) {
                            $selectedVoucher = $uv;
                            break;
                        }
                    }
                }
            }

            $pricingSummary = $this->calculatePricingSummary($items, $user, $usePoints, $shippingMethod, $selectedVoucher, $orderCount);

            // Re-find selectedVoucher using the effective voucher code from pricing summary
            // This ensures we have the correct voucher that was actually applied
            $effectiveVoucherCode = $pricingSummary['voucher_code'] ?? '';
            if (!empty($effectiveVoucherCode)) {
                // Re-find the voucher that was actually applied
                // First, try to use the voucher_id from POST if it matches the effective code
                $foundVoucher = null;
                if ($voucherIdFromPost) {
                    foreach ($userVouchers as $uv) {
                        if ((int) $uv['id'] === $voucherIdFromPost && strcasecmp($uv['code'], $effectiveVoucherCode) === 0) {
                            $foundVoucher = $uv;
                            break;
                        }
                    }
                }
                // If not found by ID, or if ID wasn't provided, find by code
                // If multiple vouchers with same code exist, prefer the most recently claimed one
                if (!$foundVoucher) {
                    $mostRecentClaimed = null;
                    foreach ($userVouchers as $uv) {
                        if (strcasecmp($uv['code'], $effectiveVoucherCode) === 0) {
                            if (!$foundVoucher) {
                                $foundVoucher = $uv;
                                $mostRecentClaimed = $uv['claimed_at'] ?? null;
                            } else {
                                // If this voucher was claimed more recently, use it instead
                                $thisClaimed = $uv['claimed_at'] ?? null;
                                if ($thisClaimed && (!$mostRecentClaimed || $thisClaimed > $mostRecentClaimed)) {
                                    $foundVoucher = $uv;
                                    $mostRecentClaimed = $thisClaimed;
                                }
                            }
                        }
                    }
                }
                $selectedVoucher = $foundVoucher;
            } else {
                // No voucher was applied, clear selectedVoucher
                $selectedVoucher = null;
            }

            // Ensure selectedVoucher is valid - if we couldn't find it, clear it
            if ($selectedVoucher && empty($selectedVoucher['id'])) {
                $selectedVoucher = null;
            }

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


            $amount = (float) $pricingSummary['payable_total'];

            if ($amount <= 0) {
                $orderStatus = 'paid';
            } else {
                $orderStatus = ($paymentMethod === 'Stripe') ? 'pending' : 'paid';
            }

            // Compute PayLater charges and enforce credit limit
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
                'shipping_fee' => $pricingSummary['shipping_fee'], // Effective shipping fee (after discount)
                'shipping_method' => $shippingMethod,
                'voucher_discount' => $pricingSummary['voucher_discount'], // Merchandise discount only
                'voucher_shipping_discount' => $pricingSummary['voucher_shipping_discount'] ?? 0, // Shipping discount
                'voucher_code' => $pricingSummary['voucher_code'],
                'voucher_id' => $selectedVoucher ? $selectedVoucher['id'] : null, // Store user_voucher id for later
                'order_status' => $orderStatus,
            ]);

            // Reduce stock for each product in the order
            foreach ($items as $item) {
                $this->products->reduceStock($item['product_id'], $item['quantity']);
            }

            if ($amount <= 0) {
                // Create a completed payment record for RM 0
                $this->payments->create($orderId, 'Points/Voucher', 0, 0, 'completed');

                // Send Receipt
                $this->sendOrderReceipt($orderId);

                // Clear Cart & Success
                unset($_SESSION[self::CHECKOUT_SESSION_KEY]);
                flash('success', 'Order confirmed! (Fully paid via points/voucher)');
                redirect("?module=orders&action=detail&id=$orderId");
                return; // Stop here, do not go to Stripe
            }

            // Mark voucher as used if one was applied (only for PayLater, Stripe will be handled after payment)
            if ($paymentMethod !== 'Stripe' && !empty($pricingSummary['voucher_code'])) {
                // Use code-based marking which is more reliable
                try {
                    $marked = $this->userVouchers->markUsedByCode($userId, $pricingSummary['voucher_code'], $orderId);
                    if (!$marked) {
                        error_log("Warning: Could not mark voucher '{$pricingSummary['voucher_code']}' as used for order #$orderId. Voucher may have been used or doesn't exist.");
                    }
                } catch (\RuntimeException $e) {
                    error_log("Error marking voucher '{$pricingSummary['voucher_code']}' as used for order #$orderId: " . $e->getMessage());
                    // Continue without marking - voucher discount was already applied
                }
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
                    try {
                        \Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

                        // A. Prepare Items (Original Price)
                        $lineItems = [];
                        foreach ($items as $item) {
                            $lineItems[] = [
                                'price_data' => [
                                    'currency' => 'myr',
                                    'product_data' => ['name' => $item['name']],
                                    'unit_amount' => (int)($item['price'] * 100), // Original Price
                                ],
                                'quantity' => (int)$item['quantity'],
                            ];
                        }

                        // B. Prepare Shipping (Original Fee)
                        $shippingDiscount = $pricingSummary['voucher_shipping_discount'] ?? 0;
                        $fullShippingFee = $pricingSummary['shipping_fee'] + $shippingDiscount;

                        if ($fullShippingFee > 0) {
                            $lineItems[] = [
                                'price_data' => [
                                    'currency' => 'myr',
                                    'product_data' => ['name' => 'Shipping Fee (' . $shippingMethod . ')'],
                                    'unit_amount' => (int)($fullShippingFee * 100),
                                ],
                                'quantity' => 1,
                            ];
                        }

                        // C. Prepare Discount (Coupon)
                        $totalDiscount = $pricingSummary['points_discount'] + $pricingSummary['voucher_discount'] + $shippingDiscount;
                        $discountsArray = [];

                        if ($totalDiscount > 0) {
                            $coupon = \Stripe\Coupon::create([
                                'amount_off' => (int)($totalDiscount * 100),
                                'currency' => 'myr',
                                'duration' => 'once',
                                'name' => 'Total Discount (Points/Voucher)',
                            ]);
                            $discountsArray[] = ['coupon' => $coupon->id];
                        }

                        // D. Create Session Object
                        $session = \Stripe\Checkout\Session::create([
                            'payment_method_types' => ['card', 'fpx', 'grabpay'],
                            'mode' => 'payment',
                            'client_reference_id' => $orderId,
                            'line_items' => $lineItems,       // Input: Items + Shipping
                            'discounts' => $discountsArray,   // Input: Coupon ID
                            'success_url' => url('?module=cart&action=stripe_success&session_id={CHECKOUT_SESSION_ID}&order_id=' . $orderId),
                            'cancel_url' => url('?module=cart&action=stripe_cancel&order_id=' . $orderId),
                        ]);

                        header("HTTP/1.1 303 See Other");
                        header("Location: " . $session->url);
                        exit();
                    } catch (\Exception $e) {
                        flash('danger', 'Stripe Error: ' . $e->getMessage());
                        redirect('?module=cart&action=checkout');
                    }
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
        $effectiveVoucherCode = $pricingSummary['voucher_code'] ?? '';

        // Re-find selectedVoucher using the effective voucher code (in case it changed)
        // This ensures we have the correct voucher that was actually applied
        $selectedVoucherId = null;
        if (!empty($effectiveVoucherCode)) {
            foreach ($userVouchers as $uv) {
                if (strcasecmp($uv['code'], $effectiveVoucherCode) === 0) {
                    $selectedVoucher = $uv;
                    $selectedVoucherId = $uv['id']; // Store user_voucher.id
                    break;
                }
            }
        }
        $_SESSION[self::CHECKOUT_SESSION_KEY] = [
            'selected_item_ids' => $selectedIds,
            'use_points' => $usePoints,
            'shipping_method' => $shippingMethod,
            'voucher_code' => $voucherCode,
            'voucher_id' => $selectedVoucherId, // Store user_voucher.id for Stripe payments
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

            // Process points deduction and voucher marking (deferred until payment succeeds)
            // Get order details to find the voucher code that was actually applied
            $orderDetail = $this->orders->detail($orderId);
            $voucherCode = null;
            $userVoucherId = null;

            // Try to get voucher info from order (if stored) or session
            // First, check if we can get it from the order's pricing calculation
            // The order doesn't store voucher_code directly, so we need to get it from session
            $sessionData = $_SESSION[self::CHECKOUT_SESSION_KEY] ?? [];
            $voucherCode = $sessionData['voucher_code'] ?? null;
            $userVoucherId = isset($sessionData['voucher_id']) ? (int) $sessionData['voucher_id'] : null;

            // If we have a voucher code, find the correct voucher instance
            if ($voucherCode) {
                $userId = auth_id();

                // If we have voucher_id from session, verify it matches the code and is still active
                if ($userVoucherId) {
                    $userVouchers = $this->userVouchers->activeForUser($userId);
                    $found = false;
                    foreach ($userVouchers as $uv) {
                        if ((int) $uv['id'] === $userVoucherId && strcasecmp($uv['code'], $voucherCode) === 0) {
                            $found = true;
                            break;
                        }
                    }
                    // If the voucher_id from session doesn't match or is no longer active, clear it
                    if (!$found) {
                        $userVoucherId = null;
                    }
                }

                // If we don't have a valid voucher_id, find by code
                if (!$userVoucherId) {
                    $userVouchers = $this->userVouchers->activeForUser($userId);
                    foreach ($userVouchers as $uv) {
                        if (strcasecmp($uv['code'], $voucherCode) === 0) {
                            $userVoucherId = $uv['id'];
                            break;
                        }
                    }
                }
            }

            $this->orders->processPointsAndVouchers($orderId, $voucherCode, $userVoucherId);

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
