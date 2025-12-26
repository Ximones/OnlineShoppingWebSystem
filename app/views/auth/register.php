<?php $title = 'Register'; ?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<section class="auth-card">
    <h1>Create Account</h1>
    <form method="post">
        <label for="name">Full Name</label>
        <?php html_text('name', ['required' => 'required']); ?>
        <?php err('name'); ?>

        <label for="email">Email</label>
        <?php html_email('email', ['required' => 'required']); ?>
        <?php err('email'); ?>

        <label for="phone">Phone</label>
        <?php html_text('phone', ['required' => 'required']); ?>
        <?php err('phone'); ?>

        <label for="password">Password</label>
        <?php html_password('password', ['required' => 'required']); ?>
        <?php err('password'); ?>

        <label for="confirm_password">Confirm Password</label>
        <?php html_password('confirm_password', ['required' => 'required']); ?>
        <?php err('confirm_password'); ?>

        <div style="margin: 15px 0;">
            <div class="g-recaptcha" data-sitekey="<?= $_ENV['RECAPTCHA_SITE_KEY'] ?>"></div>
            <?php err('captcha'); ?>
        </div>

        <button class="btn primary" style="margin-top: 3%;">Register</button>
        <p>
            Already have an account? <a href="?module=auth&action=login">Login</a>
        </p>
    </form>
</section>

