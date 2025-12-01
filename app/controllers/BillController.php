<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Order;
use App\Models\Payment;

class BillController extends Controller
{
    private Payment $payments;
    private Order $orders;

    public function __construct()
    {
        $this->payments = new Payment();
        $this->orders = new Order();
    }

    public function index(): void
    {
        $this->requireAuth();
        $userId = auth_id();
        $bills = $this->payments->pendingPayLaterForUser($userId);
        $this->render('bills/index', compact('bills'));
    }

    public function pay(): void
    {
        $this->requireAuth();
        $userId = auth_id();
        $paymentId = (int) post('payment_id');
        if (!$paymentId) {
            redirect('?module=bills&action=index');
        }

        $payment = $this->payments->findForUser($userId, $paymentId);
        if (!$payment) {
            flash('danger', 'Bill not found.');
            redirect('?module=bills&action=index');
        }

        $this->payments->markPaid($paymentId, 'MANUAL-' . date('YmdHis'));

        // Optionally update order status
        $stm = db()->prepare('UPDATE orders SET status = "paid" WHERE id = ?');
        $stm->execute([$payment['order_id']]);

        flash('success', 'Bill paid successfully.');
        redirect('?module=bills&action=index');
    }
}


