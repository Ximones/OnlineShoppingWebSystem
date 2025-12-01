<?php $title = 'Set New Password'; ?>
<section class="auth-card">
    <h1>Set New Password</h1>
    <form method="post">
        <input type="hidden" name="token" value="<?= encode($token); ?>">
        <label for="password">New Password</label>
        <?php html_password('password', ['required' => 'required']); ?>
        <?php err('password'); ?>

        <label for="confirm_password">Confirm Password</label>
        <?php html_password('confirm_password', ['required' => 'required']); ?>
        <?php err('confirm_password'); ?>

        <button class="btn primary">Update Password</button>
    </form>
</section>

