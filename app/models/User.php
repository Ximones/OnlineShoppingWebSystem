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

    public function saveVerificationToken(int $userId, string $token): void
    {
        $stmt = $this->db->prepare("UPDATE users SET verification_token = ? WHERE id = ?");
        $stmt->execute([$token, $userId]);
    }

    public function findByVerificationToken(string $token): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE verification_token = ? LIMIT 1");
        $stmt->execute([$token]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public function markEmailAsVerified(int $id): void
    {
        $stmt = $this->db->prepare("UPDATE users SET email_verified_at = NOW(), verification_token = NULL WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function find(int $id): ?array
    {
        $stm = $this->db->prepare('SELECT * FROM users WHERE id = ?');
        $stm->execute([$id]);
        return $stm->fetch() ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public function saveResetToken(int $userId, string $token, string $expiresAt): void
    {
        $stmt = $this->db->prepare("
        UPDATE users 
        SET reset_token = ?, reset_token_expires_at = ? 
        WHERE id = ?
    ");
        $stmt->execute([$token, $expiresAt, $userId]);
    }

    public function getResetToken(string $token): ?array
    {
        $stmt = $this->db->prepare("
        SELECT id as user_id, reset_token 
        FROM users 
        WHERE reset_token = ? 
        AND reset_token_expires_at > NOW() 
        LIMIT 1
    ");
        $stmt->execute([$token]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public function deleteResetToken(string $token): void
    {
        $stmt = $this->db->prepare("
        UPDATE users 
        SET reset_token = NULL, reset_token_expires_at = NULL 
        WHERE reset_token = ?
    ");
        $stmt->execute([$token]);
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

    public function getRewardPoints(int $id): float
    {
        $stm = $this->db->prepare('SELECT reward_points FROM users WHERE id = ?');
        $stm->execute([$id]);
        $balance = $stm->fetchColumn();
        return $balance !== false ? (float) $balance : 0.0;
    }

    /**
     * Atomically spend reward points for a user.
     *
     * @return array{success:bool,reason:string,balance:float}
     */
    public function spendRewardPoints(int $id, float $points): array
    {
        $points = max(0.0, $points);
        if ($points <= 0.0) {
            return ['success' => true, 'reason' => 'no_cost', 'balance' => $this->getRewardPoints($id)];
        }

        $this->db->beginTransaction();

        $stm = $this->db->prepare('SELECT reward_points FROM users WHERE id = ? FOR UPDATE');
        $stm->execute([$id]);
        $current = $stm->fetchColumn();

        if ($current === false) {
            $this->db->rollBack();
            return ['success' => false, 'reason' => 'not_found', 'balance' => 0.0];
        }

        $currentBalance = (float) $current;
        if ($currentBalance < $points) {
            $this->db->rollBack();
            return ['success' => false, 'reason' => 'insufficient', 'balance' => $currentBalance];
        }

        $newBalance = $currentBalance - $points;

        $stm = $this->db->prepare('UPDATE users SET reward_points = ?, updated_at = NOW() WHERE id = ?');
        $stm->execute([$newBalance, $id]);

        $this->db->commit();

        $this->updateRewardTier($id);

        return ['success' => true, 'reason' => 'ok', 'balance' => $newBalance];
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

    public function updateLockout(int $userId, int $attempts, ?string $lockoutUntil = null): void
    {
        $stmt = $this->db->prepare("UPDATE users SET login_attempts = ?, lockout_until = ? WHERE id = ?");
        $stmt->execute([$attempts, $lockoutUntil, $userId]);
    }

    public function resetLockout(int $userId): void
    {
        $stmt = $this->db->prepare("UPDATE users SET login_attempts = 0, lockout_until = NULL WHERE id = ?");
        $stmt->execute([$userId]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function deleteMember(int $id): void
    {
        $user = $this->find($id);
        if ($user && $user['role'] === 'admin') {
            throw new \RuntimeException('Cannot delete admin users.');
        }
        $stm = $this->db->prepare('DELETE FROM users WHERE id = ?');
        $stm->execute([$id]);
    }

    public function batchDelete(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }
        $failed = [];
        foreach ($ids as $id) {
            try {
                $this->deleteMember($id);
            } catch (\RuntimeException $e) {
                $failed[] = ['id' => $id, 'error' => $e->getMessage()];
            }
        }
        return $failed;
    }

    public function block(int $id): void
    {
        $user = $this->find($id);
        if (!$user) {
            throw new \RuntimeException('User not found.');
        }
        if ($user['role'] === 'admin') {
            throw new \RuntimeException('Cannot block admin users.');
        }
        $stm = $this->db->prepare('UPDATE users SET status = "blocked", updated_at = NOW() WHERE id = ?');
        $stm->execute([$id]);
    }

    public function unblock(int $id): void
    {
        $user = $this->find($id);
        if (!$user) {
            throw new \RuntimeException('User not found.');
        }
        $stm = $this->db->prepare('UPDATE users SET status = "active", updated_at = NOW() WHERE id = ?');
        $stm->execute([$id]);
    }
}
