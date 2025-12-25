<?php $title = 'Reset Password'; ?>
<section class="auth-card">
    <h1 style="text-align: center;">Create New Password</h1>
    <p style="text-align: center; color: #666; margin-bottom: 1.5rem;">
        Enter your new password below. Make it strong and unique.
    </p>

    <form method="post">
        <input type="hidden" name="token" value="<?= encode($token); ?>">

        <label for="password">New Password</label>
        <?php html_password('password', [
            'required' => 'required',
            'placeholder' => 'At least 8 characters',
            'minlength' => '8'
        ]); ?>
        <?php err('password'); ?>
        <small style="color: #666;">
            Use a mix of uppercase, lowercase, numbers, and special characters for security.
        </small>

        <label for="confirm_password" style="margin-top: 1rem;">Confirm Password</label>
        <?php html_password('confirm_password', [
            'required' => 'required',
            'placeholder' => 'Re-enter your password',
            'minlength' => '8'
        ]); ?>
        <?php err('confirm_password'); ?>

        <button type="submit" class="btn primary" style="width: 100%; margin-top: 1.5rem;">Update Password</button>
    </form>

    <div style="margin-top: 1.5rem; text-align: center; border-top: 1px solid #ddd; padding-top: 1rem;">
        <p style="font-size: 0.9rem; color: #666;">
            <a href="?module=auth&action=login" style="text-decoration: none;">Back to Login</a>
        </p>
    </div>
</section>