<?php $title = 'Forgot Password'; ?>
<section class="auth-card">
    <h1 style="text-align: center;">Forgot Your Password?</h1>
    <p style="text-align: center; color: #666; margin-bottom: 1.5rem;">
        Enter your email address and we'll send you a link to reset your password.
    </p>

    <form method="post">
        <label for="email">Email Address</label>
        <?php html_email('email', ['required' => 'required', 'placeholder' => 'your@email.com']); ?>
        <?php err('email'); ?>

        <button class="btn primary" style="width: 100%; margin-top: 1rem;">Send Reset Link</button>
    </form>

    <div style="margin-top: 1.5rem; text-align: center; border-top: 1px solid #ddd; padding-top: 1rem;">
        <p style="font-size: 0.9rem; color: #666;">
            Remember your password? <a href="?module=auth&action=login" style="text-decoration: none;">Back to Login</a>
        </p>
    </div>
</section>