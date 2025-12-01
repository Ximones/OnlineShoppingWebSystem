<?php

namespace App\Models;

use DateTimeImmutable;
use PDO;
use function db;

class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = db();
    }

    public function find(int $id): ?array
    {
        $stm = $this->db->prepare('SELECT * FROM users WHERE id = ?');
        $stm->execute([$id]);
        return $stm->fetch() ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stm = $this->db->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stm->execute([$email]);
        return $stm->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $stm = $this->db->prepare('INSERT INTO users (name, email, phone, password_hash, role, status) VALUES (?, ?, ?, ?, ?, ?)');
        $stm->execute([
            $data['name'],
            $data['email'],
            $data['phone'],
            $data['password_hash'],
            $data['role'] ?? 'member',
            $data['status'] ?? 'active',
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function updateProfile(int $id, array $data): void
    {
        $stm = $this->db->prepare('UPDATE users SET name = ?, phone = ?, address = ?, updated_at = NOW() WHERE id = ?');
        $stm->execute([
            $data['name'],
            $data['phone'],
            $data['address'],
            $id,
        ]);
    }

    public function updatePassword(int $id, string $hash): void
    {
        $stm = $this->db->prepare('UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?');
        $stm->execute([$hash, $id]);
    }

    public function updateAvatar(int $id, string $path): void
    {
        $stm = $this->db->prepare('UPDATE users SET avatar = ?, updated_at = NOW() WHERE id = ?');
        $stm->execute([$path, $id]);
    }

    /**
     * Handle daily check-in logic and reward distribution.
     *
     * @return array{status:string,streak:int,points:int}
     */
    public function recordDailyCheckIn(int $id): array
    {
        $user = $this->find($id);
        if (!$user) {
            return [
                'status' => 'not_found',
                'streak' => 0,
                'points' => 0,
            ];
        }

        $pointsMap = [
            1 => 1,
            2 => 5,
            3 => 10,
            4 => 15,
            5 => 20,
            6 => 25,
            7 => 100,
        ];

        $today = new DateTimeImmutable('today');
        $lastCheckIn = !empty($user['last_check_in_at'])
            ? (new DateTimeImmutable($user['last_check_in_at']))->setTime(0, 0)
            : null;

        if ($lastCheckIn && $lastCheckIn->format('Y-m-d') === $today->format('Y-m-d')) {
            return [
                'status' => 'already_checked_in',
                'streak' => (int) $user['check_in_streak'],
                'points' => 0,
            ];
        }

        $streak = 1;
        if ($lastCheckIn) {
            $diffDays = (int) $lastCheckIn->diff($today)->days;
            if ($diffDays === 1) {
                $streak = min(7, (int) $user['check_in_streak'] + 1);
            }
        }

        $pointsEarned = $pointsMap[$streak] ?? 1;

        $stm = $this->db->prepare('UPDATE users SET check_in_streak = ?, last_check_in_at = NOW(), reward_points = reward_points + ?, updated_at = NOW() WHERE id = ?');
        $stm->execute([$streak, $pointsEarned, $id]);

        $this->updateRewardTier($id);

        return [
            'status' => 'checked_in',
            'streak' => $streak,
            'points' => $pointsEarned,
        ];
    }

    public function addRewardPoints(int $id, float $points): void
    {
        $stm = $this->db->prepare('UPDATE users SET reward_points = reward_points + ?, updated_at = NOW() WHERE id = ?');
        $stm->execute([$points, $id]);
        $this->updateRewardTier($id);
    }

    public function updateRewardTier(int $id): void
    {
        $user = $this->find($id);
        if (!$user) {
            return;
        }

        require_once __DIR__ . '/../lib/rewards.php';
        $newTier = calculate_reward_tier((float)$user['reward_points']);
        
        $stm = $this->db->prepare('UPDATE users SET reward_tier = ?, updated_at = NOW() WHERE id = ?');
        $stm->execute([$newTier, $id]);
    }

    public function listMembers(string $keyword = ''): array
    {
        $sql = 'SELECT * FROM users WHERE role = "member"';
        $params = [];
        if ($keyword) {
            $sql .= ' AND (name LIKE ? OR email LIKE ?)';
            $params = ["%$keyword%", "%$keyword%"];
        }
        $sql .= ' ORDER BY created_at DESC';

        $stm = $this->db->prepare($sql);
        $stm->execute($params);
        return $stm->fetchAll();
    }
}


