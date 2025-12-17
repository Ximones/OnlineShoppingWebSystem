<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;

class BillController extends Controller
{
    private Payment $payments;
    private Order $orders;
    private User $users;

    public function __construct()
    {
        $this->payments = new Payment();
        $this->orders = new Order();
        $this->users = new User();
    }

    public function index(): void
    {
        $this->requireAuth();
        $userId = auth_id();
        $bills = $this->payments->pendingPayLaterForUser($userId);
        $history = $this->payments->completedPayLaterForUser($userId);
        
        // Get user's credit limit from database
        $user = $this->users->find($userId);
        $originalLimit = $user ? (float)($user['paylater_credit_limit'] ?? 10000.0) : 10000.0;
        
        $usedPrincipal = $this->payments->outstandingPayLaterPrincipal($userId);
        $availableLimit = max(0.0, $originalLimit - $usedPrincipal);

        $this->render('bills/index', [
            'bills' => $bills,
            'history' => $history,
            'originalLimit' => $originalLimit,
            'usedPrincipal' => $usedPrincipal,
            'availableLimit' => $availableLimit,
        ]);
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


