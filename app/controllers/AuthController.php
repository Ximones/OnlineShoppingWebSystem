<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\PasswordReset;
use App\Models\User;

require_once __DIR__ . '/../lib/mail/mail_helper.php';

class AuthController extends Controller
{
    private User $users;
    private PasswordReset $resets;

    public function __construct()
    {
        $this->users = new User();
        $this->resets = new PasswordReset();
    }

    public function login(): void
    {
        if (is_post() && validate([
            'email' => ['required' => 'Email is required.', 'email' => 'Email format invalid.'],
            'password' => ['required' => 'Password is required.'],
        ])) {

            $user = $this->users->findByEmail(post('email'));

            if ($user) {

                // Check if they have verified their email
                if (empty($user['email_verified_at'])) {
                    flash('danger', 'Please verify your email address before logging in.');
                    $this->render('auth/login');
                    return;
                }

                // Check if they are banned/blocked
                if (($user['status'] ?? 'active') === 'blocked') {
                    flash('danger', 'Your account has been blocked. Please contact support.');
                    $this->render('auth/login');
                    return;
                }

                // Check Lockout (Anti-Spam/Brute Force)
                if ($user['lockout_until'] && strtotime($user['lockout_until']) > time()) {
                    $remaining = strtotime($user['lockout_until']) - time();
                    flash('danger', "Too many attempts. Locked for another $remaining seconds.");
                    $this->render('auth/login');
                    return;
                }

                // Verify Password
                if (password_verify(post('password'), $user['password_hash'])) {
                    $this->users->resetLockout($user['id']);
                    auth_login($user);
                    flash('success', 'Welcome back, ' . $user['name'] . '!');
                    redirect('?module=shop&action=home');
                } else {
                    // Wrong password logic
                    $newAttempts = $user['login_attempts'] + 1;
                    $lockoutTime = null;

                    if ($newAttempts >= 3) {
                        $lockoutTime = date('Y-m-d H:i:s', strtotime('+1 minute'));
                        flash('danger', 'Account locked for 1 minute due to 3 failed attempts.');
                    } else {
                        $remaining = 3 - $newAttempts;
                        flash('danger', "Invalid credentials. $remaining attempts remaining.");
                    }

                    $this->users->updateLockout($user['id'], $newAttempts, $lockoutTime);
                }
            } else {
                // Send unregistered user to register page
                flash('danger', 'Account not found. Please register first.');
                redirect('?module=auth&action=register');
                return;
            }
        }

        $this->render('auth/login');
    }

    public function logout(): void
    {
        auth_logout();
        flash('success', 'You have been logged out.');
        redirect('?module=shop&action=home');
    }

    public function register(): void
    {
        if (is_post() && validate([
            'name' => ['required' => 'Name is required.'],
            'email' => ['required' => 'Email is required.', 'email' => 'Email format invalid.'],
            'phone' => ['required' => 'Phone is required.'],
            'password' => ['required' => 'Password is required.', 'min:8' => 'Password must be at least 8 characters.'],
            'confirm_password' => ['same:password' => 'Passwords do not match.'],
        ])) {

            $email = post('email');
            $existingUser = $this->users->findByEmail($email);

            // Handling of existing emails
            if ($existingUser) {
                // Case 1: User is already fully verified.
                if (!empty($existingUser['email_verified_at'])) {
                    flash('danger', 'Email already registered. Please log in.');
                    $this->render('auth/register');
                    return;
                }

                // Case 2: User exists but NEVER verified.
                // Assume this is a "dead" account. Delete it and create a fresh one.
                $this->users->delete($existingUser['id']);
            }

            // Proceed to create the new (or replaced) user
            $id = $this->users->create([
                'name' => post('name'),
                'email' => post('email'),
                'phone' => post('phone'),
                'password_hash' => password_hash(post('password'), PASSWORD_DEFAULT),
                'role' => 'member',
            ]);

            // Generate and save verification token
            $token = bin2hex(random_bytes(32));
            $this->users->saveVerificationToken($id, $token);

            // Send email
            $user = $this->users->find($id);
            $this->sendVerificationEmail($user, $token);

            flash('success', 'Registration successful! Please check your email to verify your account.');
            redirect('?module=auth&action=login');
        }

        $this->render('auth/register');
    }

    // VERIFY FUNCTION: Handles the link click
    public function verify(): void
    {
        $token = get('token', '');

        if (empty($token)) {
            flash('danger', 'Invalid verification link.');
            redirect('?module=auth&action=login');
        }

        $user = $this->users->findByVerificationToken($token);

        if (!$user) {
            flash('danger', 'Invalid or expired verification link.');
            redirect('?module=auth&action=login');
        }

        // Mark as verified
        $this->users->markEmailAsVerified($user['id']);

        flash('success', 'Email verified! You can now log in.');
        redirect('?module=auth&action=login');
    }

    // HELPER: Sends the email
    private function sendVerificationEmail(array $user, string $token): void
    {
        try {
            $verifyLink = url('?module=auth&action=verify&token=' . urlencode($token));

            $mail = get_mail();
            $mail->addAddress($user['email'], $user['name']);
            $mail->Subject = 'Verify Your Email - Daily Bowls';
            // You can create a view for this, but simple HTML works for now
            $mail->Body = "
                <h1>Welcome to Daily Bowls!</h1>
                <p>Hi " . htmlspecialchars($user['name']) . ",</p>
                <p>Please click the link below to verify your email address and activate your account:</p>
                <p><a href='" . $verifyLink . "'>Verify Email Address</a></p>
                <p>If you did not create an account, no further action is required.</p>
            ";
            $mail->AltBody = "Please copy this link to verify your account: " . $verifyLink;
            $mail->send();
        } catch (\Throwable $e) {
            error_log('Verification email failed: ' . $e->getMessage());
        }
    }

    public function forgot(): void
    {
        // If already logged in, redirect to home
        if (auth_id()) {
            redirect('?module=shop&action=catalog');
        }

        if (is_post()) {
            $email = trim((string) post('email', ''));

            if (empty($email)) {
                flash('danger', 'Please enter your email address.');
                redirect('?module=auth&action=forgot');
            }

            $user = $this->users->findByEmail($email);

            // For security, always show success message even if email doesn't exist
            // This prevents email enumeration attacks
            if ($user) {
                // Generate a unique reset token
                $resetToken = bin2hex(random_bytes(32));
                $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

                // Store token in database
                $this->users->saveResetToken($user['id'], $resetToken, $expiresAt);

                // Send reset email
                $this->sendPasswordResetEmail($user, $resetToken);
            }

            flash('success', 'If an account exists with this email, a password reset link has been sent. Please check your inbox.');
            redirect('?module=auth&action=login');
        }

        $this->render('auth/forgot');
    }

    public function reset(): void
    {
        // If already logged in, redirect to home
        if (auth_id()) {
            redirect('?module=shop&action=catalog');
        }

        $token = get('token', '');

        if (empty($token)) {
            flash('danger', 'Invalid or missing reset token.');
            redirect('?module=auth&action=forgot');
        }

        // Verify token exists and hasn't expired
        $resetRecord = $this->users->getResetToken($token);
        if (!$resetRecord) {
            flash('danger', 'Reset link has expired. Please request a new one.');
            redirect('?module=auth&action=forgot');
        }

        if (is_post()) {
            $password = post('password', '');
            $confirmPassword = post('confirm_password', '');

            if (validate([
                'password' => [
                    'required' => 'Password is required.',
                    'min_length' => ['Password must be at least 8 characters.', 8]
                ],
                'confirm_password' => ['required' => 'Please confirm your password.']
            ])) {
                if ($password !== $confirmPassword) {
                    flash('danger', 'Passwords do not match.');
                    redirect('?module=auth&action=reset&token=' . encode($token));
                }

                // Update user password
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                $this->users->updatePassword($resetRecord['user_id'], $hashedPassword);

                // Delete reset token
                $this->users->deleteResetToken($token);

                flash('success', 'Your password has been reset successfully. Please log in with your new password.');
                redirect('?module=auth&action=login');
            } else {
                redirect('?module=auth&action=reset&token=' . encode($token));
            }
        }

        $this->render('auth/reset', compact('token'));
    }

    private function sendPasswordResetEmail(array $user, string $token): void
    {
        try {
            $resetLink = url('?module=auth&action=reset&token=' . urlencode($token));

            $mail = get_mail();
            $mail->addAddress($user['email'], $user['name']);
            $mail->Subject = 'Password Reset Request - Daily Bowls';
            $mail->Body = $this->renderPasswordResetEmail($user, $resetLink);
            $mail->AltBody = "Click here to reset your password: " . $resetLink . "\n\nThis link expires in 1 hour.";
            $mail->send();
        } catch (\Throwable $e) {
            error_log('Password reset email failed: ' . $e->getMessage());
        }
    }

    private function renderPasswordResetEmail(array $user, string $resetLink): string
    {
        ob_start();
        extract(['user' => $user, 'resetLink' => $resetLink]);
        require __DIR__ . '/../views/auth/reset_mail.php';
        return ob_get_clean();
    }
}
