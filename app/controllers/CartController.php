<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\SavedAddress;
use App\Models\User;
use App\Models\UserVoucher;

class CartController extends Controller
{
    private const CHECKOUT_SESSION_KEY = 'checkout_context';

    private Cart $cart;
    private Product $products;
    private Order $orders;
    private User $users;
    private SavedAddress $savedAddresses;
    private UserVoucher $userVouchers;
    /**
     * Simple shipping options: code => [label, fee]
     * Fee is in RM.
     */
    private array $shippingOptions = [
        'standard' => ['label' => 'Standard Shipping (3-5 days)', 'fee' => 10.0],
        'express'  => ['label' => 'Express Shipping (1-2 days)', 'fee' => 25.0],
        'pickup'   => ['label' => 'Self Pickup (Free)', 'fee' => 0.0],
    ];

    public function __construct()
    {
        $this->cart = new Cart();
        $this->products = new Product();
        $this->orders = new Order();
        $this->users = new User();
        $this->savedAddresses = new SavedAddress();
        $this->userVouchers = new UserVoucher();
    }

    public function index(): void
    {
        $this->requireAuth();
        $cartId = $this->cart->activeCartId(auth_id());
        $items = $this->cart->items($cartId);
        $user = $this->users->find(auth_id());
        $this->render('shop/cart', compact('items', 'user'));
    }

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

    public function update(): void
    {
        $this->requireAuth();
        foreach (post('items', []) as $itemId => $quantity) {
            $this->cart->updateItem((int) $itemId, max(1, (int) $quantity));
        }
        flash('success', 'Cart updated.');
        redirect('?module=cart&action=index');
    }

    public function remove(): void
    {
        $this->requireAuth();
        $itemId = (int) post('item_id');
        $this->cart->removeItem($itemId);
        flash('success', 'Item removed.');
        redirect('?module=cart&action=index');
    }

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

        $context = $_SESSION[self::CHECKOUT_SESSION_KEY] ?? null;
        $selectedIds = $context['selected_item_ids'] ?? array_column($items, 'id');
        $selectedIds = array_map('intval', $selectedIds);
        if ($selectedIds) {
            $items = array_values(array_filter($items, fn ($item) => in_array((int) $item['id'], $selectedIds, true)));
        }
        if (empty($items)) {
            flash('danger', 'Please select at least one item to checkout.');
            redirect('?module=cart&action=index');
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
            $pricingSummary = $this->calculatePricingSummary($items, $this->users->find($userId), $usePoints, $shippingMethod, $voucherCode);
            $orderId = $this->orders->createFromCart($userId, $cartId, [
                'name' => post('shipping_name'),
                'phone' => post('shipping_phone'),
                'address' => post('shipping_address'),
            ], [
                'item_ids' => array_column($items, 'id'),
                'points_redeemed' => $pricingSummary['points_redeemed'],
                'shipping_fee' => $pricingSummary['shipping_fee'],
                'voucher_discount' => $pricingSummary['voucher_discount'],
                'voucher_code' => $pricingSummary['voucher_code'],
            ]);
            unset($_SESSION[self::CHECKOUT_SESSION_KEY]);
            flash('success', 'Order created.');
            redirect("?module=orders&action=detail&id=$orderId");
        }

        $user = $this->users->find($userId);
        $savedAddresses = $this->savedAddresses->findByUser($userId);
        $userVouchers = $this->userVouchers->activeForUser($userId);
        $orderCount = $this->orders->countByUser($userId);
        $pricingSummary = $this->calculatePricingSummary($items, $user, $usePoints, $shippingMethod, $voucherCode);

        // Persist latest context for subsequent requests
        $_SESSION[self::CHECKOUT_SESSION_KEY] = [
            'selected_item_ids' => $selectedIds,
            'use_points' => $usePoints,
            'shipping_method' => $shippingMethod,
            'voucher_code' => $voucherCode,
        ];

        $shippingOptions = $this->shippingOptions;
        $this->render('shop/checkout', compact('items', 'user', 'savedAddresses', 'pricingSummary', 'shippingOptions', 'shippingMethod', 'voucherCode', 'userVouchers', 'orderCount'));
    }

    public function prepare_checkout(): void
    {
        $this->requireAuth();
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

    private function calculatePricingSummary(array $items, ?array $user, bool $usePoints, string $shippingMethod, string $voucherCode): array
    {
        $subtotal = array_reduce($items, fn ($carry, $item) => $carry + ($item['price'] * $item['quantity']), 0.0);
        $availablePoints = (int) floor((float) ($user['reward_points'] ?? 0));
        $maxRedeemableRm = min($subtotal, (int) floor($availablePoints / 100));
        $pointsRedeemed = $usePoints ? $maxRedeemableRm * 100 : 0;
        $pointsDiscount = $pointsRedeemed / 100;

        // Shipping
        if (!isset($this->shippingOptions[$shippingMethod])) {
            $shippingMethod = 'standard';
        }
        $shippingFee = (float) $this->shippingOptions[$shippingMethod]['fee'];

        // Simple voucher implementation (flat amount or percentage)
        $voucherCode = strtoupper(trim($voucherCode));
        $voucherDiscount = 0.0;
        $appliedVoucher = '';
        if ($voucherCode !== '') {
            $vouchers = [
                // code => [type, value, min_subtotal]
                'SAVE10' => ['type' => 'amount', 'value' => 10.0, 'min' => 100.0],
                'SAVE50' => ['type' => 'amount', 'value' => 50.0, 'min' => 500.0],
                'OFF5'   => ['type' => 'percent', 'value' => 5.0, 'min' => 0.0],
            ];
            if (isset($vouchers[$voucherCode]) && $subtotal >= $vouchers[$voucherCode]['min']) {
                $config = $vouchers[$voucherCode];
                if ($config['type'] === 'amount') {
                    $voucherDiscount = min($config['value'], $subtotal);
                } else {
                    $voucherDiscount = round($subtotal * ($config['value'] / 100), 2);
                }
                $appliedVoucher = $voucherCode;
            }
        }

        $baseTotal = max(0.0, $subtotal - $pointsDiscount - $voucherDiscount);
        $payable = $baseTotal + $shippingFee;

        return [
            'subtotal' => $subtotal,
            'available_points' => $availablePoints,
            'max_redeemable_rm' => $maxRedeemableRm,
            'points_redeemed' => $pointsRedeemed,
            'points_discount' => $pointsDiscount,
            'voucher_code' => $appliedVoucher,
            'voucher_discount' => $voucherDiscount,
            'shipping_method' => $shippingMethod,
            'shipping_fee' => $shippingFee,
            'payable_total' => $payable,
            'use_points' => $usePoints,
        ];
    }
}


