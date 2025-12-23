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

    public function admin(): void
    {
        $this->requireAdmin();
        $filters = [
            'status' => get('status', ''),
            'keyword' => get('keyword', ''),
        ];
        $orders = $this->orders->adminList($filters);
        $this->render('admin/orders/index', compact('orders', 'filters'));
    }

    public function admin_detail(): void
    {
        $this->requireAdmin();
        $order = $this->orders->detail((int) get('id'));
        if (!$order) {
            flash('danger', 'Order not found.');
            redirect('?module=orders&action=admin');
        }
        $this->render('admin/orders/detail', compact('order'));
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

    public function update_status(): void
    {
        $this->requireAdmin();
        if (!is_post()) {
            redirect('?module=orders&action=admin');
        }

        $orderId = (int) post('order_id');
        $status = strtolower(post('status', ''));
        $allowed = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];

        if (!in_array($status, $allowed, true)) {
            flash('danger', 'Invalid status selected.');
            redirect('?module=orders&action=admin');
        }

        $this->orders->updateStatus($orderId, $status);

        // Optional tracking note when marking as shipped
        if ($status === 'shipped') {
            $location = trim((string) post('tracking_location', ''));
            $remarks = trim((string) post('tracking_remarks', ''));
            if ($location !== '' || $remarks !== '') {
                $this->orders->addTracking($orderId, 'Shipped', $location, $remarks);
            }
        }

        flash('success', 'Order status updated.');
        redirect('?module=orders&action=admin');
    }

    public function add_tracking(): void
    {
        $this->requireAdmin();
        if (!is_post()) {
            redirect('?module=orders&action=admin');
        }

        $orderId = (int) post('order_id');
        $status = trim((string) post('tracking_status', ''));
        $location = trim((string) post('tracking_location', ''));
        $remarks = trim((string) post('tracking_remarks', ''));

        if ($status === '') {
            flash('danger', 'Tracking status is required.');
            redirect("?module=orders&action=detail&id=$orderId");
        }

        $this->orders->addTracking($orderId, $status, $location, $remarks);
        flash('success', 'Tracking entry added.');
        redirect("?module=orders&action=detail&id=$orderId");
    }

    public function delete_tracking(): void
    {
        $this->requireAdmin();
        if (!is_post()) {
            redirect('?module=orders&action=admin');
        }

        $orderId = (int) post('order_id');
        $trackingId = (int) post('tracking_id');

        $this->orders->deleteTracking($trackingId, $orderId);
        flash('success', 'Tracking entry removed.');
        redirect("?module=orders&action=detail&id=$orderId");
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


