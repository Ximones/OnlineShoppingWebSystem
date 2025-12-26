<?php

namespace App\Controllers;

use App\Core\AdminController;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Voucher;
use App\Models\Payment;
use function db;

class AdminDashboardController extends AdminController
{
    // Model instances
    private Order $orders;
    private Product $products;
    private User $users;
    private Voucher $vouchers;
    private Payment $payments;

    // Initialize model instances
    public function __construct()
    {
        $this->orders = new Order();
        $this->products = new Product();
        $this->users = new User();
        $this->vouchers = new Voucher();
        $this->payments = new Payment();
    }

    // Display admin dashboard with statistics and charts
    public function dashboard(): void
    {
        $this->requireAdmin();

        $db = db();

        // Get counts
        $stats = [
            'total_members' => (int) $db->query('SELECT COUNT(*) FROM users WHERE role = "member"')->fetchColumn(),
            'total_products' => (int) $db->query('SELECT COUNT(*) FROM products')->fetchColumn(),
            'active_products' => (int) $db->query('SELECT COUNT(*) FROM products WHERE status = "active"')->fetchColumn(),
            'total_orders' => (int) $db->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
            'pending_orders' => (int) $db->query('SELECT COUNT(*) FROM orders WHERE status = "pending"')->fetchColumn(),
            'total_vouchers' => (int) $db->query('SELECT COUNT(*) FROM vouchers')->fetchColumn(),
            'active_vouchers' => (int) $db->query('SELECT COUNT(*) FROM vouchers WHERE is_active = 1')->fetchColumn(),
        ];

        // Get recent orders (limit to 5)
        $recentOrders = $this->orders->adminList(['status' => '']);
        $recentOrders = array_slice($recentOrders, 0, 5);

        // Calculate total revenue from completed/shipped orders
        $revenueQuery = $db->query('SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status IN ("completed", "shipped")');
        $stats['total_revenue'] = (float) $revenueQuery->fetchColumn();

        // PayLater statistics
        $paylaterPendingQuery = $db->query('SELECT COUNT(*) FROM payments WHERE payment_method = "PayLater" AND status = "pending"');
        $stats['paylater_pending'] = (int) $paylaterPendingQuery->fetchColumn();
        
        // Calculate outstanding PayLater amount
        $paylaterTotalQuery = $db->query('SELECT COALESCE(SUM(amount), 0) FROM payments WHERE payment_method = "PayLater" AND status = "pending"');
        $stats['paylater_outstanding'] = (float) $paylaterTotalQuery->fetchColumn();
        
        // Calculate collected PayLater amount
        $paylaterCompletedQuery = $db->query('SELECT COALESCE(SUM(amount), 0) FROM payments WHERE payment_method = "PayLater" AND status = "completed"');
        $stats['paylater_collected'] = (float) $paylaterCompletedQuery->fetchColumn();
        
        // Calculate PayLater interest revenue (difference between amount and principal)
        $paylaterInterestQuery = $db->query('
            SELECT COALESCE(SUM(amount - principal_amount), 0) 
            FROM payments 
            WHERE (payment_method = "PayLater" OR billing_due_date IS NOT NULL OR tenure_months IS NOT NULL) 
            AND status = "completed"
        ');
        $stats['paylater_interest_revenue'] = (float) $paylaterInterestQuery->fetchColumn();

        // Chart Data: Revenue over last 12 months
        $revenueByMonthQuery = $db->query("
            SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                COALESCE(SUM(total_amount), 0) as revenue
            FROM orders 
            WHERE status IN ('completed', 'shipped') 
                AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month ASC
        ");
        // Build revenue by month array
        $revenueByMonth = [];
        while ($row = $revenueByMonthQuery->fetch(\PDO::FETCH_ASSOC)) {
            $revenueByMonth[] = $row;
        }

        // Chart Data: Orders by status
        $ordersByStatusQuery = $db->query("
            SELECT status, COUNT(*) as count
            FROM orders
            GROUP BY status
        ");
        // Build orders by status array
        $ordersByStatus = [];
        while ($row = $ordersByStatusQuery->fetch(\PDO::FETCH_ASSOC)) {
            $ordersByStatus[] = $row;
        }

        // Chart Data: Top selling products (last 30 days)
        $topProductsQuery = $db->query("
            SELECT 
                p.id,
                p.name,
                SUM(oi.quantity) as total_sold,
                SUM(oi.quantity * oi.unit_price) as total_revenue
            FROM order_items oi
            INNER JOIN orders o ON o.id = oi.order_id
            INNER JOIN products p ON p.id = oi.product_id
            WHERE o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                AND o.status IN ('completed', 'shipped')
            GROUP BY p.id, p.name
            ORDER BY total_sold DESC
            LIMIT 10
        ");
        // Build top products array
        $topProducts = [];
        while ($row = $topProductsQuery->fetch(\PDO::FETCH_ASSOC)) {
            $topProducts[] = $row;
        }

        // Chart Data: Orders over last 30 days
        $ordersOverTimeQuery = $db->query("
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as count
            FROM orders
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ");
        // Build orders over time array
        $ordersOverTime = [];
        while ($row = $ordersOverTimeQuery->fetch(\PDO::FETCH_ASSOC)) {
            $ordersOverTime[] = $row;
        }

        // Chart Data: New members over last 12 months
        $membersByMonthQuery = $db->query("
            SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                COUNT(*) as count
            FROM users 
            WHERE role = 'member' 
                AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month ASC
        ");
        // Build members by month array
        $membersByMonth = [];
        while ($row = $membersByMonthQuery->fetch(\PDO::FETCH_ASSOC)) {
            $membersByMonth[] = $row;
        }

        // PayLater outstanding amount by month (last 12 months)
        $paylaterOutstandingByMonthQuery = $db->query("
            SELECT 
                DATE_FORMAT(p.payment_date, '%Y-%m') as month,
                COALESCE(SUM(p.amount), 0) as outstanding
            FROM payments p
            WHERE p.payment_method = 'PayLater'
                AND p.status = 'pending'
                AND p.payment_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(p.payment_date, '%Y-%m')
            ORDER BY month ASC
        ");
        // Build PayLater outstanding by month array
        $paylaterOutstandingByMonth = [];
        while ($row = $paylaterOutstandingByMonthQuery->fetch(\PDO::FETCH_ASSOC)) {
            $paylaterOutstandingByMonth[] = $row;
        }

        // Chart Data: PayLater by Status
        $paylaterByStatusQuery = $db->query("
            SELECT status, COUNT(*) as count, COALESCE(SUM(amount), 0) as total_amount
            FROM payments
            WHERE payment_method = 'PayLater'
            GROUP BY status
        ");
        // Build PayLater by status array
        $paylaterByStatus = [];
        while ($row = $paylaterByStatusQuery->fetch(\PDO::FETCH_ASSOC)) {
            $paylaterByStatus[] = $row;
        }

        // PayLater collected amount by month (last 12 months)
        $paylaterCollectedByMonthQuery = $db->query("
            SELECT 
                DATE_FORMAT(p.payment_date, '%Y-%m') as month,
                COALESCE(SUM(CASE WHEN p.status = 'completed' THEN p.amount ELSE 0 END), 0) as collected
            FROM payments p
            WHERE p.payment_method = 'PayLater'
                AND p.payment_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(p.payment_date, '%Y-%m')
            ORDER BY month ASC
        ");
        // Build PayLater collected by month array
        $paylaterCollectedByMonth = [];
        while ($row = $paylaterCollectedByMonthQuery->fetch(\PDO::FETCH_ASSOC)) {
            $paylaterCollectedByMonth[] = $row;
        }

        // Chart Data: PayLater by Tenure
        $paylaterByTenureQuery = $db->query("
            SELECT 
                COALESCE(tenure_months, 0) as tenure,
                COUNT(*) as count,
                COALESCE(SUM(amount), 0) as total_amount
            FROM payments
            WHERE payment_method = 'PayLater'
            GROUP BY tenure_months
            ORDER BY tenure ASC
        ");
        // Build PayLater by tenure array
        $paylaterByTenure = [];
        while ($row = $paylaterByTenureQuery->fetch(\PDO::FETCH_ASSOC)) {
            $paylaterByTenure[] = $row;
        }

        // Render dashboard view with all data
        $this->render('admin/dashboard', compact(
            'stats', 
            'recentOrders',
            'revenueByMonth',
            'ordersByStatus',
            'topProducts',
            'ordersOverTime',
            'membersByMonth',
            'paylaterOutstandingByMonth',
            'paylaterByStatus',
            'paylaterCollectedByMonth',
            'paylaterByTenure'
        ));
    }
}

