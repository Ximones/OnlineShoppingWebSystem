<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use RuntimeException;

class GameController extends Controller
{
    private User $users;

    public function __construct()
    {
        $this->users = new User();
    }

    public function points(): void
    {
        $this->requireAuth();

        $userId = auth_id();
        $user   = $this->users->find($userId);
        if (!$user) {
            throw new RuntimeException('User not found.');
        }

        $points = (float) ($user['reward_points'] ?? 0.0);

        // Daily check-in data
        $checkInPoints = [
            1 => 1,
            2 => 5,
            3 => 10,
            4 => 15,
            5 => 20,
            6 => 25,
            7 => 100,
        ];
        $streak = max(0, (int) ($user['check_in_streak'] ?? 0));
        $lastCheckInAt = $user['last_check_in_at'] ?? null;
        $today = new \DateTimeImmutable('today');
        $lastCheckInDate = $lastCheckInAt ? (new \DateTimeImmutable($lastCheckInAt))->setTime(0, 0) : null;
        $checkedInToday = $lastCheckInDate && $lastCheckInDate->format('Y-m-d') === $today->format('Y-m-d');
        $nextStreakForReward = $streak ? min(7, $streak + 1) : 1;
        $currentReward = $checkInPoints[$nextStreakForReward];

        // Set prize probabilities as specified (displayed as "1 in X")
        // Store original values - show integers when whole, decimals when needed
        $prizeProbabilities = [
            0 => 10,      // No prize: 10% (1/10) - integer
            10 => 2,      // Common: 50% (1/2) - integer
            30 => 2.5,    // Uncommon: 40% (1/2.5) - decimal
            80 => 3.33,   // Rare: 30% (1/3.33) - decimal
            150 => 5,     // Epic: 20% (1/5) - integer
            500 => 100,   // Jackpot: 1% (1/100) - integer
            1000 => 1000, // Mega: 0.1% (1/1000) - integer
            5000 => 10000, // Ultimate: 0.01% (1/10000) - integer
        ];

        $this->render('game/points', [
            'user'         => $user,
            'points'       => $points,
            'playCost'     => 50.0,
            'maxDailyPlay' => 50,
            'checkInPoints' => $checkInPoints,
            'streak' => $streak,
            'checkedInToday' => $checkedInToday,
            'nextStreakForReward' => $nextStreakForReward,
            'currentReward' => $currentReward,
            'prizeProbabilities' => $prizeProbabilities,
        ]);
    }

    public function check_in(): void
    {
        $this->requireAuth();
        $userId = auth_id();
        $result = $this->users->recordDailyCheckIn($userId);

        if ($result['status'] === 'already_checked_in') {
            flash('danger', 'You have already checked in today.');
        } elseif ($result['status'] === 'checked_in') {
            flash('success', "Checked in! You earned {$result['points']} points. Streak: {$result['streak']} days.");
        } else {
            flash('danger', 'Unable to check in. Please try again.');
        }

        redirect('?module=game&action=points');
    }

    public function play_scratch(): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $userId = auth_id();
        $cost   = 50.0;

        $spend = $this->users->spendRewardPoints($userId, $cost);
        if (!$spend['success']) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'reason'  => $spend['reason'],
                'balance' => $spend['balance'],
                'message' => $spend['reason'] === 'insufficient'
                    ? 'Not enough points to scratch.'
                    : 'Unable to use points. Please try again.',
            ]);
            return;
        }

        // Weights calculated to match displayed probabilities exactly
        // Displayed: No prize 1/10, Common 1/2, Uncommon 1/3, Rare 1/3, Epic 1/5, Jackpot 1/100, Mega 1/1000, Ultimate 1/10000
        // To get probability P = weight/total, we solve: total = sum(weights) where each weight = total * P
        // Since displayed probabilities sum to 147.71%, we normalize: weight = (total * displayed_P) / 1.4771
        // Using total = 10000 for clean numbers:
        $prizes = [
            ['label' => 'No prize', 'type' => 'none',   'points' => 0,    'weight' => 677],   // 1/10 normalized: 10%/147.71% = 6.77%
            ['label' => 'Common +10 pts', 'type' => 'points', 'points' => 10,   'weight' => 3385], // 1/2 normalized: 50%/147.71% = 33.85%
            ['label' => 'Uncommon +30 pts', 'type' => 'points', 'points' => 30,   'weight' => 2256], // 1/3 normalized: 33.33%/147.71% = 22.56%
            ['label' => 'Rare +80 pts', 'type' => 'points', 'points' => 80,   'weight' => 2256], // 1/3 normalized: 33.33%/147.71% = 22.56%
            ['label' => 'Epic +150 pts', 'type' => 'points', 'points' => 150,  'weight' => 1354],  // 1/5 normalized: 20%/147.71% = 13.54%
            ['label' => 'Jackpot +500 pts',  'type' => 'points', 'points' => 500,  'weight' => 68],   // 1/100 normalized: 1%/147.71% = 0.68%
            ['label' => 'Mega Jackpot +1000 pts', 'type' => 'points', 'points' => 1000, 'weight' => 7],  // 1/1000 normalized: 0.1%/147.71% = 0.068%
            ['label' => 'Ultimate Jackpot +5000 pts', 'type' => 'points', 'points' => 5000, 'weight' => 1],   // 1/10000 normalized: 0.01%/147.71% = 0.0068%
        ];
        // Total weight = 10000, probabilities match displayed values when normalized

        $prize = $this->pickPrize($prizes);

        $pointsWon = 0.0;
        if ($prize['type'] === 'points' && $prize['points'] > 0) {
            $pointsWon = (float) $prize['points'];
            // Don't add prize points yet - user must claim first
        }

        $currentBalance = $this->users->getRewardPoints($userId);

        header('Content-Type: application/json');
        echo json_encode([
            'success'       => true,
            'prize'         => $prize,
            'cost'          => $cost,
            'points_won'    => $pointsWon,
            'net_points'    => -$cost, // Only cost deducted, prize not added yet
            'balance'       => $currentBalance,
        ]);
    }

    public function claim_scratch_prize(): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $userId = auth_id();
        $pointsWon = (float) ($_POST['points_won'] ?? 0.0);

        if ($pointsWon > 0) {
            $this->users->addRewardPoints($userId, $pointsWon);
        }

        $currentBalance = $this->users->getRewardPoints($userId);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'points_won' => $pointsWon,
            'balance' => $currentBalance,
        ]);
    }

    public function get_balance(): void
    {
        $this->requireAuth();
        
        $userId = auth_id();
        $balance = $this->users->getRewardPoints($userId);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'balance' => $balance,
        ]);
    }

    /**
     * @param array<int,array{label:string,type:string,points:float|int,weight:int}> $prizes
     * @return array{label:string,type:string,points:float|int,weight:int}
     */
    private function pickPrize(array $prizes): array
    {
        $totalWeight = 0;
        foreach ($prizes as $p) {
            $totalWeight += (int) ($p['weight'] ?? 0);
        }

        if ($totalWeight <= 0) {
            return $prizes[0];
        }

        $rand = mt_rand(1, $totalWeight);
        $acc  = 0;
        foreach ($prizes as $p) {
            $acc += (int) ($p['weight'] ?? 0);
            if ($rand <= $acc) {
                return $p;
            }
        }

        return end($prizes) ?: $prizes[0];
    }
}


