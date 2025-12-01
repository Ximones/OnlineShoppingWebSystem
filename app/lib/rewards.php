<?php

/**
 * Reward Points System
 * RM100 = 1 point
 */

/**
 * Calculate reward points from order amount
 * @param float $amount Order total amount
 * @return float Reward points earned
 */
function calculate_reward_points(float $amount): float
{
    return floor($amount / 100);
}

/**
 * Determine reward tier based on total points
 * @param float $points Total reward points
 * @return string Tier name (platinum, diamond, gold, silver, bronze)
 */
function calculate_reward_tier(float $points): string
{
    if ($points >= 10000) {
        return 'platinum';
    } elseif ($points >= 5000) {
        return 'diamond';
    } elseif ($points >= 2000) {
        return 'gold';
    } elseif ($points >= 500) {
        return 'silver';
    } else {
        return 'bronze';
    }
}

/**
 * Get tier display name
 * @param string $tier Tier code
 * @return string Display name
 */
function get_tier_name(string $tier): string
{
    $tiers = [
        'platinum' => 'Platinum',
        'diamond' => 'Diamond',
        'gold' => 'Gold',
        'silver' => 'Silver',
        'bronze' => 'Bronze',
    ];
    return $tiers[$tier] ?? 'Bronze';
}

/**
 * Get tier color for display
 * @param string $tier Tier code
 * @return string CSS color
 */
function get_tier_color(string $tier): string
{
    $colors = [
        'platinum' => '#e5e4e2',
        'diamond' => '#b9f2ff',
        'gold' => '#ffd700',
        'silver' => '#c0c0c0',
        'bronze' => '#cd7f32',
    ];
    return $colors[$tier] ?? '#cd7f32';
}

