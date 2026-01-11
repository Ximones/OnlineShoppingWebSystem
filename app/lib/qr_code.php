<?php

/**
 * QR Code Helper Functions
 * Generates QR codes for self-pickup orders
 */

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;

/**
 * Generate a QR code token for an order
 * Returns a unique token that can be used to identify the order
 */
function generate_qr_code_token(int $orderId): string
{
    // Generate a secure token combining order ID with timestamp and random string
    $timestamp = time();
    $random = bin2hex(random_bytes(12));
    $token = base64_encode("order_{$orderId}_{$timestamp}_{$random}");
    // Remove any special characters that might cause issues and ensure uniqueness
    $token = preg_replace('/[^A-Za-z0-9]/', '', $token);
    // Add order ID prefix for easier debugging
    return 'ORD' . $orderId . '_' . substr($token, 0, 32);
}

/**
 * Get the base URL for QR codes that will work when scanned from mobile devices
 * Uses the current URL's host (works with ngrok, localhost, or any domain)
 * Can be overridden with QR_CODE_BASE_URL environment variable
 */
function get_qr_code_base_url(): string
{
    // Allow manual override via environment variable (useful for production or when auto-detection fails)
    if (!empty($_ENV['QR_CODE_BASE_URL'])) {
        return rtrim($_ENV['QR_CODE_BASE_URL'], '/');
    }

    // Use the current request URL (works with ngrok, localhost, IP addresses, etc.)
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || 
                (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
                (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
                ? 'https' : 'http';
    
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
    
    // Build the base URL using current host (this will work with ngrok URLs)
    $baseUrl = $protocol . '://' . $host;
    
    return $baseUrl;
}

/**
 * Get the local network IP address for QR code generation
 * Returns the first non-localhost IPv4 address (prefers 192.168.x.x)
 */
function get_local_ip_address(): ?string
{
    // Try to get local IP from network interfaces
    if (PHP_OS_FAMILY === 'Windows') {
        $output = [];
        exec('ipconfig | findstr /i "IPv4"', $output);
        $preferredIp = null;
        $fallbackIp = null;
        
        foreach ($output as $line) {
            if (preg_match('/\b(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})\b/', $line, $matches)) {
                $ip = $matches[1];
                // Skip loopback and link-local addresses
                if ($ip !== '127.0.0.1' && !str_starts_with($ip, '169.254.')) {
                    // Prefer 192.168.x.x addresses for local networks
                    if (str_starts_with($ip, '192.168.')) {
                        $preferredIp = $ip;
                        break; // Found preferred IP, use it
                    } else {
                        // Store as fallback (e.g., 10.x.x.x)
                        $fallbackIp = $ip;
                    }
                }
            }
        }
        
        return $preferredIp ?? $fallbackIp;
    } else {
        // Linux/Mac
        $output = [];
        exec("hostname -I 2>/dev/null | awk '{print $1}'", $output);
        if (!empty($output[0])) {
            return trim($output[0]);
        }
    }
    
    // Fallback: try SERVER_ADDR if available (works if server bound to specific IP)
    if (!empty($_SERVER['SERVER_ADDR']) && $_SERVER['SERVER_ADDR'] !== '127.0.0.1') {
        return $_SERVER['SERVER_ADDR'];
    }
    
    // Last resort: try to get from REMOTE_ADDR context (not always reliable)
    if (!empty($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] !== '127.0.0.1' && 
        filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
        // This is a private IP, might be usable
        return $_SERVER['REMOTE_ADDR'];
    }
    
    return null;
}

/**
 * Generate QR code image data (base64 encoded) for an order
 * @param int $orderId Order ID
 * @param string $token QR code token
 * @return string Base64 encoded PNG image data (without data:image/png;base64, prefix)
 */
function generate_qr_code_image(int $orderId, string $token): string
{
    $baseUrl = get_qr_code_base_url();
    $qrCodeUrl = $baseUrl . '/index.php?module=pickup&action=scan&token=' . urlencode($token);
    
    try {
        $result = Builder::create()
            ->writer(new PngWriter())
            ->data($qrCodeUrl)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
            ->size(400)
            ->margin(10)
            ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->build();
        
        // Return base64 encoded image
        return base64_encode($result->getString());
    } catch (\Exception $e) {
        error_log("QR Code generation error: " . $e->getMessage());
        return '';
    }
}

/**
 * Get QR code data URL for embedding in HTML
 * @param int $orderId Order ID
 * @param string $token QR code token
 * @return string Data URL ready for <img src="...">
 */
function get_qr_code_data_url(int $orderId, string $token): string
{
    $imageData = generate_qr_code_image($orderId, $token);
    if (empty($imageData)) {
        return '';
    }
    return 'data:image/png;base64,' . $imageData;
}
