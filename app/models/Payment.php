<?php

namespace App\Models;

use PDO;
use function db;

class Payment
{
    private PDO $db;

    public function __construct()
    {
        $this->db = db();
    }

    public function create(
        int $orderId,
        string $method,
        float $amount,
        float $principalAmount = null,
        string $status = 'pending',
        ?string $transactionRef = null,
        ?int $tenureMonths = null,
        ?float $interestRate = null,
        ?string $billingDueDate = null
    ): int
    {
        if ($principalAmount === null) {
            $principalAmount = $amount;
        }
        $stm = $this->db->prepare(
            'INSERT INTO payments (order_id, payment_method, amount, principal_amount, status, transaction_ref, billing_due_date, tenure_months, interest_rate)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stm->execute([$orderId, $method, $amount, $principalAmount, $status, $transactionRef, $billingDueDate, $tenureMonths, $interestRate]);
        return (int) $this->db->lastInsertId();
    }

    public function markPaid(int $paymentId, ?string $transactionRef = null): void
    {
        $stm = $this->db->prepare(
            'UPDATE payments SET status = "completed", transaction_ref = COALESCE(?, transaction_ref), updated_at = NOW() WHERE id = ?'
        );
        $stm->execute([$transactionRef, $paymentId]);
    }

    public function updateMethodLabel(int $paymentId, string $label): void
    {
        $stm = $this->db->prepare('UPDATE payments SET payment_method = ? WHERE id = ?');
        $stm->execute([$label, $paymentId]);
    }

    public function pendingPayLaterForUser(int $userId): array
    {
        $sql = 'SELECT p.*, o.user_id, o.status AS order_status
                FROM payments p
                INNER JOIN orders o ON o.id = p.order_id
                WHERE o.user_id = ? AND p.payment_method = "PayLater" 
                AND p.status = "pending" 
                AND o.status != "cancelled"
                AND (p.billing_due_date IS NULL OR p.billing_due_date >= CURDATE())
                ORDER BY p.billing_due_date ASC, p.payment_date DESC';
        $stm = $this->db->prepare($sql);
        $stm->execute([$userId]);
        return $stm->fetchAll();
    }

    public function outstandingPayLaterPrincipal(int $userId): float
    {
        $sql = 'SELECT SUM(p.principal_amount)
                FROM payments p
                INNER JOIN orders o ON o.id = p.order_id
                WHERE o.user_id = ? AND p.payment_method = "PayLater" 
                AND p.status = "pending" 
                AND o.status != "cancelled"
                AND (p.billing_due_date IS NULL OR p.billing_due_date >= CURDATE())';
        $stm = $this->db->prepare($sql);
        $stm->execute([$userId]);
        return (float) $stm->fetchColumn();
    }

    public function findForUser(int $userId, int $paymentId): ?array
    {
        $sql = 'SELECT p.*, o.user_id
                FROM payments p
                INNER JOIN orders o ON o.id = p.order_id
                WHERE p.id = ? AND o.user_id = ?';
        $stm = $this->db->prepare($sql);
        $stm->execute([$paymentId, $userId]);
        return $stm->fetch() ?: null;
    }

    public function completedPayLaterForUser(int $userId): array
    {
        $sql = 'SELECT p.*, o.user_id, o.status AS order_status
                FROM payments p
                INNER JOIN orders o ON o.id = p.order_id
                WHERE o.user_id = ?
                  AND (p.payment_method = "PayLater" OR p.billing_due_date IS NOT NULL OR p.tenure_months IS NOT NULL)
                  AND p.status = "completed"
                ORDER BY p.updated_at DESC, p.payment_date DESC';
        $stm = $this->db->prepare($sql);
        $stm->execute([$userId]);
        return $stm->fetchAll();
    }

    public function findByOrderId(int $orderId): array
    {
        $stm = $this->db->prepare('SELECT * FROM payments WHERE order_id = ? ORDER BY payment_date ASC');
        $stm->execute([$orderId]);
        return $stm->fetchAll();
    }

    public function allPayLater(array $filters = []): array
    {
        $sql = 'SELECT p.*, o.user_id, u.name AS user_name, u.email AS user_email, o.total_amount AS order_total
                FROM payments p
                INNER JOIN orders o ON o.id = p.order_id
                INNER JOIN users u ON u.id = o.user_id
                WHERE p.payment_method = "PayLater"';
        
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= ' AND p.status = ?';
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['keyword'])) {
            $sql .= ' AND (u.name LIKE ? OR u.email LIKE ? OR o.id = ?)';
            $keyword = '%' . $filters['keyword'] . '%';
            $params[] = $keyword;
            $params[] = $keyword;
            $params[] = (int) $filters['keyword'];
        }

        $sql .= ' ORDER BY p.billing_due_date ASC, p.payment_date DESC';
        
        $stm = $this->db->prepare($sql);
        $stm->execute($params);
        return $stm->fetchAll();
    }

    /**
     * Cancel all PayLater payments for an order (set status to 'cancelled')
     * This should be called when an order is cancelled
     */
    public function cancelPayLaterForOrder(int $orderId): void
    {
        $stm = $this->db->prepare(
            'UPDATE payments 
             SET status = "cancelled", updated_at = NOW() 
             WHERE order_id = ? 
             AND payment_method = "PayLater" 
             AND status = "pending"'
        );
        $stm->execute([$orderId]);
    }
}


