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

        $prizes = [
            ['label' => 'No prize', 'type' => 'none',   'points' => 0,    'weight' => 4500000],
            ['label' => 'Common +10 pts', 'type' => 'points', 'points' => 10,   'weight' => 2500000],
            ['label' => 'Uncommon +30 pts', 'type' => 'points', 'points' => 30,   'weight' => 1500000],
            ['label' => 'Rare +80 pts', 'type' => 'points', 'points' => 80,   'weight' => 800000],
            ['label' => 'Epic +150 pts', 'type' => 'points', 'points' => 150,  'weight' => 500000],
            ['label' => 'Jackpot +500 pts',  'type' => 'points', 'points' => 500,  'weight' => 200000],
            ['label' => 'Mega Jackpot +1000 pts', 'type' => 'points', 'points' => 1000, 'weight' => 10000],
            ['label' => 'Ultimate Jackpot +5000 pts', 'type' => 'points', 'points' => 5000, 'weight' => 1000],
        ];

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


