<?php

namespace App\Controllers;

use App\Core\AdminController;
use App\Models\Order;

class AdminOrderController extends AdminController
{
    private Order $orders;

    public function __construct()
    {
        $this->orders = new Order();
    }

    public function index(): void
    {
        $this->requireAdmin();
        $filters = [
            'status' => get('status', ''),
            'keyword' => get('keyword', ''),
        ];
        $orders = $this->orders->adminList($filters);
        $this->render('admin/orders/index', compact('orders', 'filters'));
    }

    public function detail(): void
    {
        $this->requireAdmin();
        $order = $this->orders->detail((int) get('id'));
        if (!$order) {
            flash('danger', 'Order not found.');
            redirect('?module=admin&resource=orders&action=index');
        }
        $this->render('admin/orders/detail', compact('order'));
    }

    public function updateStatus(): void
    {
        $this->requireAdmin();
        if (!is_post()) {
            redirect('?module=admin&resource=orders&action=index');
        }

        $orderId = (int) post('order_id');
        $status = strtolower(post('status', ''));
        $allowed = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];

        if (!in_array($status, $allowed, true)) {
            flash('danger', 'Invalid status selected.');
            redirect('?module=admin&resource=orders&action=index');
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
        redirect('?module=admin&resource=orders&action=index');
    }

    public function addTracking(): void
    {
        $this->requireAdmin();
        if (!is_post()) {
            redirect('?module=admin&resource=orders&action=index');
        }

        $orderId = (int) post('order_id');
        $status = trim((string) post('tracking_status', ''));
        $location = trim((string) post('tracking_location', ''));
        $remarks = trim((string) post('tracking_remarks', ''));

        if ($status === '') {
            flash('danger', 'Tracking status is required.');
            redirect("?module=admin&resource=orders&action=detail&id=$orderId");
        }

        $this->orders->addTracking($orderId, $status, $location, $remarks);
        flash('success', 'Tracking entry added.');
        redirect("?module=admin&resource=orders&action=detail&id=$orderId");
    }

    public function deleteTracking(): void
    {
        $this->requireAdmin();
        if (!is_post()) {
            redirect('?module=admin&resource=orders&action=index');
        }

        $orderId = (int) post('order_id');
        $trackingId = (int) post('tracking_id');

        $this->orders->deleteTracking($trackingId, $orderId);
        flash('success', 'Tracking entry removed.');
        redirect("?module=admin&resource=orders&action=detail&id=$orderId");
    }
}

