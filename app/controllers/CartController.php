<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

class CartController extends Controller
{
    private Cart $cart;
    private Product $products;
    private Order $orders;
    private User $users;

    public function __construct()
    {
        $this->cart = new Cart();
        $this->products = new Product();
        $this->orders = new Order();
        $this->users = new User();
    }

    public function index(): void
    {
        $this->requireAuth();
        $cartId = $this->cart->activeCartId(auth_id());
        $items = $this->cart->items($cartId);
        $this->render('shop/cart', compact('items'));
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
        $cartId = $this->cart->activeCartId(auth_id());
        $items = $this->cart->items($cartId);
        if (empty($items)) {
            flash('danger', 'Cart is empty.');
            redirect('?module=shop&action=catalog');
        }

        if (is_post() && validate([
            'shipping_name' => ['required' => 'Shipping name is required.'],
            'shipping_phone' => ['required' => 'Phone is required.'],
            'shipping_address' => ['required' => 'Address is required.'],
        ])) {
            $orderId = $this->orders->createFromCart(auth_id(), $cartId, [
                'name' => post('shipping_name'),
                'phone' => post('shipping_phone'),
                'address' => post('shipping_address'),
            ]);
            flash('success', 'Order created.');
            redirect("?module=orders&action=detail&id=$orderId");
        }

        $user = $this->users->find(auth_id());
        $this->render('shop/checkout', compact('items', 'user'));
    }
}


