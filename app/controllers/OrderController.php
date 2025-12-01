<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Order;

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
        $orders = $this->orders->history(auth_id());
        $this->render('orders/history', compact('orders'));
    }

    public function detail(): void
    {
        $this->requireAuth();
        $order = $this->orders->detail((int) get('id'));
        if (!$order || (!is_admin() && $order['user_id'] != auth_id())) {
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
}


