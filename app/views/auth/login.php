<?php $title = 'Login'; ?>
<section class="auth-card">
    <h1>Login</h1>
    <form method="post">
        <label for="email">Email</label>
        <?php html_email('email', ['required' => 'required']); ?>
        <?php err('email'); ?>

        <label for="password">Password</label>
        <?php html_password('password', ['required' => 'required']); ?>
        <?php err('password'); ?>

        <button class="btn primary" style="margin-top: 3%;">Login</button>
        <p>
            <a href="?module=auth&action=forgot">Forgot password?</a>
        </p>
    </form>
</section>

