<?php

namespace App\Services;

use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;

class StripeService
{
    public function __construct()
    {
        // Automatically set the API key whenever this class is used
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);
    }

    /**
     * Create a Stripe Checkout Session
     */
    public function createCheckoutSession(array $lineItems, string $successUrl, string $cancelUrl): Session
    {
        return Session::create([
            'line_items' => $lineItems,
            'mode' => 'payment',
            'payment_method_types' => ['card', 'fpx'], // Support Card & FPX by default
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
        ]);
    }

    /**
     * Verify if a session was paid
     */
    public function isPaid(string $sessionId): bool
    {
        try {
            $session = Session::retrieve($sessionId);
            return $session->payment_status === 'paid';
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get total amount from session (useful for recording payment)
     */
    public function getAmount(string $sessionId): float
    {
        $session = Session::retrieve($sessionId);
        return $session->amount_total / 100; // Convert cents to currency unit
    }

    /**
     * Get a human-friendly payment method label (e.g. "Stripe (Card)", "Stripe (Online Banking FPX)")
     */
    public function getPaymentMethodLabel(string $sessionId): string
    {
        try {
            $session = Session::retrieve($sessionId);
            if (!$session->payment_intent) {
                return 'Stripe';
            }

            /** @var PaymentIntent $intent */
            $intent = PaymentIntent::retrieve($session->payment_intent);
            $charges = $intent->charges->data ?? [];
            if (empty($charges)) {
                return 'Stripe';
            }

            $details = $charges[0]->payment_method_details;
            $type = $details->type ?? null;

            return match ($type) {
                'card' => 'Stripe (Card)',
                'fpx'  => 'Stripe (Online Banking FPX)',
                default => 'Stripe',
            };
        } catch (\Throwable $e) {
            return 'Stripe';
        }
    }
}