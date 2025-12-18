<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\StripeService;

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
        
        $payment = $this->payments->findForUser($userId, $paymentId);
        if (!$payment) {
            redirect('?module=bills&action=index');
        }

        // Use the Service
        $stripe = new StripeService();

        $lineItems = [[
            'price_data' => [
                'currency' => 'myr',
                'product_data' => [
                    'name' => 'PayLater Instalment #' . $paymentId . ' (Order #' . $payment['order_id'] . ')',
                ],
                'unit_amount' => (int)($payment['amount'] * 100),
            ],
            'quantity' => 1,
        ]];

        $session = $stripe->createCheckoutSession(
            $lineItems,
            url('?module=bills&action=stripe_success&session_id={CHECKOUT_SESSION_ID}&payment_id=' . $paymentId),
            url('?module=bills&action=stripe_cancel')
        );

        header("HTTP/1.1 303 See Other");
        header("Location: " . $session->url);
        exit();

    }

    public function stripe_success(): void
    {
        $this->requireAuth();
        $sessionId = get('session_id');
        $paymentId = (int) get('payment_id');

        $stripe = new StripeService();

        if ($stripe->isPaid($sessionId)) {
            $transactionRef = 'Stripe-' . $sessionId; // You can just use session ID as ref
            $this->payments->markPaid($paymentId, $transactionRef);

            // Optional: Update Order Status if needed
            $payment = $this->payments->findForUser(auth_id(), $paymentId);
            if ($payment) {
                $stm = db()->prepare('UPDATE orders SET status = "paid" WHERE id = ?');
                $stm->execute([$payment['order_id']]);
            }

            flash('success', 'Bill paid successfully.');
        } else {
            flash('danger', 'Payment failed or invalid.');
        }

        redirect('?module=bills&action=index');
    }

    public function stripe_cancel(): void
    {
        $this->requireAuth();
        flash('warning', 'Bill payment was cancelled.');
        redirect('?module=bills&action=index');
    }
}
