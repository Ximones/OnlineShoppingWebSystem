<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Order;

class PickupController extends Controller
{
    private Order $orders;

    public function __construct()
    {
        $this->orders = new Order();
    }

    /**
     * Display QR code for customer (requires authentication and order ownership)
     */
    public function qr_code(): void
    {
        $this->requireAuth();
        $orderId = (int) req('id');
        
        if (empty($orderId)) {
            flash('danger', 'Order ID is required.');
            redirect('?module=orders&action=history');
            return;
        }

        $order = $this->orders->detail($orderId);
        
        if (!$order) {
            flash('danger', 'Order not found.');
            redirect('?module=orders&action=history');
            return;
        }

        // Check if user owns this order
        if ((int)$order['user_id'] !== auth_id()) {
            flash('danger', 'You do not have permission to view this order.');
            redirect('?module=orders&action=history');
            return;
        }

        // Check if it's a pickup order
        $shippingMethod = $order['shipping_method'] ?? '';
        if (stripos($shippingMethod, 'pickup') === false) {
            flash('danger', 'This order is not a self-pickup order.');
            redirect("?module=orders&action=detail&id={$orderId}");
            return;
        }

        // Check if QR code exists, if not generate it
        if (empty($order['qr_code_token'])) {
            $this->orders->generateQrCodeIfNeeded($orderId);
            $order = $this->orders->detail($orderId); // Refresh order data
        }

        // Only show QR code for paid/processing/picked_up orders
        $status = strtolower($order['status'] ?? '');
        if (!in_array($status, ['paid', 'processing', 'picked_up', 'completed'], true)) {
            flash('danger', 'QR code is only available for paid orders.');
            redirect("?module=orders&action=detail&id={$orderId}");
            return;
        }

        require_once __DIR__ . '/../lib/qr_code.php';
        
        $this->render('pickup/qr_code', [
            'order' => $order,
            'qrCodeDataUrl' => get_qr_code_data_url($orderId, $order['qr_code_token']),
        ]);
    }

    /**
     * Staff scanning page - scan QR code to view order details
     * Handles both QR code scanning (with token) and manual token entry (no token)
     */
    public function scan(): void
    {
        $token = req('token');
        $token = trim($token);
        
        // If no token provided, show input form for manual entry
        if (empty($token)) {
            $this->render('pickup/scan_input', []);
            return;
        }

        // Extract token from URL if the scanned value is a full URL
        if (strpos($token, 'http') === 0 || strpos($token, '?module=pickup') !== false) {
            // Try to extract token from URL
            $urlParts = parse_url($token);
            if (!empty($urlParts['query'])) {
                parse_str($urlParts['query'], $params);
                if (!empty($params['token'])) {
                    $token = $params['token'];
                }
            } else if (preg_match('/[?&]token=([^&]+)/', $token, $matches)) {
                $token = urldecode($matches[1]);
            }
        }

        // Token provided - lookup order
        $order = $this->orders->findByQrToken($token);
        
        if (!$order) {
            flash('danger', 'Invalid QR code. Order not found.');
            $this->render('pickup/scan_error', [
                'token' => $token,
            ]);
            return;
        }

        // Check if order is in valid state for pickup
        $status = strtolower($order['status'] ?? '');
        if (!in_array($status, ['paid', 'processing', 'picked_up'], true)) {
            flash('danger', 'This order is not ready for pickup. Current status: ' . ucfirst($status));
        }

        $this->render('pickup/scan', [
            'order' => $order,
            'token' => $token,
        ]);
    }

    /**
     * Handle manual token lookup (POST from scan_input form)
     */
    public function lookup(): void
    {
        if (!is_post()) {
            redirect('?module=pickup&action=scan');
            return;
        }

        $token = post('token', '');
        $token = trim($token);
        
        if (empty($token)) {
            flash('danger', 'Please enter or scan a QR code token.');
            redirect('?module=pickup&action=scan');
            return;
        }

        // Extract token from URL if the scanned value is a full URL
        if (strpos($token, 'http') === 0 || strpos($token, '?module=pickup') !== false) {
            // Try to extract token from URL
            $urlParts = parse_url($token);
            if (!empty($urlParts['query'])) {
                parse_str($urlParts['query'], $params);
                if (!empty($params['token'])) {
                    $token = $params['token'];
                }
            } else if (preg_match('/[?&]token=([^&]+)/', $token, $matches)) {
                $token = urldecode($matches[1]);
            }
        }

        // Redirect to scan page with token
        redirect('?module=pickup&action=scan&token=' . urlencode($token));
    }

    /**
     * Confirm pickup (staff action - requires admin authentication)
     */
    public function confirm_pickup(): void
    {
        $this->requireAdmin();
        
        if (!is_post()) {
            flash('danger', 'Invalid request method.');
            redirect('?module=shop&action=home');
            return;
        }

        $orderId = (int) post('order_id');
        $token = post('token');
        $remarks = post('remarks', '');

        if (empty($orderId) || empty($token)) {
            flash('danger', 'Order ID and token are required.');
            redirect('?module=shop&action=home');
            return;
        }

        // Verify token matches order
        $order = $this->orders->detail($orderId);
        if (!$order || ($order['qr_code_token'] ?? '') !== $token) {
            flash('danger', 'Invalid token for this order.');
            redirect('?module=shop&action=home');
            return;
        }

        try {
            $this->orders->confirmPickup($orderId, $remarks);
            flash('success', 'Pickup confirmed successfully. Order status updated to completed.');
            redirect("?module=pickup&action=scan&token={$token}");
        } catch (\RuntimeException $e) {
            flash('danger', $e->getMessage());
            redirect("?module=pickup&action=scan&token={$token}");
        }
    }
}
