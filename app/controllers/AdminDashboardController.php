<?php

namespace App\Controllers;

use App\Core\AdminController;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Voucher;
use function db;

class AdminDashboardController extends AdminController
{
    private Order $orders;
    private Product $products;
    private User $users;
    private Voucher $vouchers;

    public function __construct()
    {
        $this->orders = new Order();
        $this->products = new Product();
        $this->users = new User();
        $this->vouchers = new Voucher();
    }

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

        // Get recent orders
        $recentOrders = $this->orders->adminList(['status' => '']);
        $recentOrders = array_slice($recentOrders, 0, 5);

        // Get revenue stats
        $revenueQuery = $db->query('SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status IN ("completed", "shipped")');
        $stats['total_revenue'] = (float) $revenueQuery->fetchColumn();

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
        $membersByMonth = [];
        while ($row = $membersByMonthQuery->fetch(\PDO::FETCH_ASSOC)) {
            $membersByMonth[] = $row;
        }

        $this->render('admin/dashboard', compact(
            'stats', 
            'recentOrders',
            'revenueByMonth',
            'ordersByStatus',
            'topProducts',
            'ordersOverTime',
            'membersByMonth'
        ));
    }
}

