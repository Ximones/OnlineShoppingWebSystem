<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Order;
use App\Models\Cart;
use App\Models\Review;

class OrderController extends Controller
{
    private Order $orders;

    public function __construct()
    {
        $this->orders = new Order();
    }

    public function history(): void
    {
        $this->requireAuth();
        $orders = $this->orders->historyWithItems(auth_id());
        $this->render('orders/history', compact('orders'));
    }

    public function detail(): void
    {
        $this->requireAuth();
        $order = $this->orders->detail((int) get('id'));
        if (!$order || $order['user_id'] != auth_id()) {
            flash('danger', 'Order not found.');
            redirect('?module=orders&action=history');
        }
        $this->render('orders/detail', compact('order'));
    }


    public function cancel(): void
    {
        $this->requireAuth();
        $orderId = (int) get('id');
        $order = $this->orders->detail($orderId);

        if (!$order || $order['user_id'] != auth_id()) {
            flash('danger', 'Order not found.');
            redirect('?module=orders&action=history');
        }

        $nonCancellable = ['shipped', 'completed', 'cancelled'];
        if (in_array(strtolower($order['status']), $nonCancellable, true)) {
            flash('danger', 'This order can no longer be cancelled.');
            redirect("?module=orders&action=detail&id=$orderId");
        }

        $this->orders->updateStatus($orderId, 'cancelled');
        $this->orders->addTracking($orderId, 'Cancelled', null, 'Order cancelled by customer');

        flash('success', 'Your order has been cancelled.');
        redirect("?module=orders&action=detail&id=$orderId");
    }

    public function reorder(): void
    {
        $this->requireAuth();
        $orderId = (int) get('id');
        $order = $this->orders->detail($orderId);

        if (!$order || $order['user_id'] != auth_id()) {
            flash('danger', 'Order not found.');
            redirect('?module=orders&action=history');
        }

        $cart = new Cart();
        $cartId = $cart->activeCartId(auth_id());

        foreach ($order['items'] as $item) {
            $cart->addItem($cartId, (int) $item['product_id'], (int) $item['quantity']);
        }

        flash('success', 'Items from this order have been added to your cart.');
        redirect('?module=cart&action=checkout');
    }


    public function add_review(): void
    {
        $this->requireAuth();
        if (!is_post()) {
            redirect('?module=orders&action=history');
        }

        $orderId = (int) post('order_id');
        $productId = (int) post('product_id');
        $rating = (int) post('rating', 5);
        $comment = trim((string) post('comment', ''));

        $order = $this->orders->detail($orderId);
        if (!$order || $order['user_id'] != auth_id()) {
            flash('danger', 'Order not found.');
            redirect('?module=orders&action=history');
        }

        if (strtolower($order['status']) !== 'completed') {
            flash('danger', 'You can only review products from completed orders.');
            redirect("?module=orders&action=detail&id=$orderId");
        }

        $hasItem = false;
        foreach ($order['items'] as $item) {
            if ((int) $item['product_id'] === $productId) {
                $hasItem = true;
                break;
            }
        }
        if (!$hasItem) {
            flash('danger', 'Product not found in this order.');
            redirect("?module=orders&action=detail&id=$orderId");
        }

        if ($rating < 1) $rating = 1;
        if ($rating > 5) $rating = 5;
        if ($comment === '') {
            flash('danger', 'Comment cannot be empty.');
            redirect("?module=orders&action=detail&id=$orderId");
        }

        $reviewModel = new Review();
        $reviewModel->upsert(auth_id(), $productId, $orderId, $rating, $comment);

        flash('success', 'Thank you for reviewing this product!');
        redirect("?module=orders&action=detail&id=$orderId");
    }
}


