<?php

namespace App\Controllers;

use App\Core\AdminController;
use App\Models\Payment;
use App\Models\User;
use function db;

class AdminPayLaterController extends AdminController
{
    private Payment $payments;
    private User $users;

    public function __construct()
    {
        $this->payments = new Payment();
        $this->users = new User();
    }

    public function index(): void
    {
        $this->requireAdmin();
        
        $filters = [
            'status' => get('status', ''),
            'keyword' => get('keyword', ''),
        ];
        
        $bills = $this->payments->allPayLater($filters);
        
        // Get summary stats
        $db = db();
        $stats = [
            'total_pending' => (int) $db->query('SELECT COUNT(*) FROM payments WHERE payment_method = "PayLater" AND status = "pending"')->fetchColumn(),
            'total_outstanding' => (float) $db->query('SELECT COALESCE(SUM(amount), 0) FROM payments WHERE payment_method = "PayLater" AND status = "pending"')->fetchColumn(),
            'total_collected' => (float) $db->query('SELECT COALESCE(SUM(amount), 0) FROM payments WHERE payment_method = "PayLater" AND status = "completed"')->fetchColumn(),
            'total_overdue' => (int) $db->query('SELECT COUNT(*) FROM payments WHERE payment_method = "PayLater" AND status = "pending" AND billing_due_date < CURDATE()')->fetchColumn(),
            'overdue_amount' => (float) $db->query('SELECT COALESCE(SUM(amount), 0) FROM payments WHERE payment_method = "PayLater" AND status = "pending" AND billing_due_date < CURDATE()')->fetchColumn(),
        ];
        
        $this->render('admin/paylater/index', compact('bills', 'filters', 'stats'));
    }

    public function updateCreditLimit(): void
    {
        $this->requireAdmin();
        
        if (!is_post()) {
            redirect('?module=admin&resource=paylater&action=index');
        }
        
        $userId = (int) post('user_id');
        $creditLimit = (float) post('credit_limit');
        
        if ($creditLimit < 0) {
            flash('danger', 'Credit limit cannot be negative.');
            redirect('?module=admin&resource=paylater&action=index');
        }
        
        $user = $this->users->find($userId);
        if (!$user) {
            flash('danger', 'User not found.');
            redirect('?module=admin&resource=paylater&action=index');
        }
        
        $stm = db()->prepare('UPDATE users SET paylater_credit_limit = ? WHERE id = ?');
        $stm->execute([$creditLimit, $userId]);
        
        flash('success', 'Credit limit updated successfully.');
        redirect('?module=admin&resource=paylater&action=index');
    }
}

