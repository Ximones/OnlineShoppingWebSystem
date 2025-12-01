<?php $title = 'Reset Password'; ?>
<section class="auth-card">
    <h1>Password Reset</h1>
    <form method="post">
        <label for="email">Registered Email</label>
        <?php html_email('email', ['required' => 'required']); ?>
        <?php err('email'); ?>
        <button class="btn primary">Send Reset Link</button>
    </form>
    <p>We will show the generated token for demo purposes.</p>
</section>

