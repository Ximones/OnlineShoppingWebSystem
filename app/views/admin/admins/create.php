<?php $title = 'Create Admin'; ?>
<section class="panel">
    <h2>New Admin</h2>
    <form method="post">
        <label for="name">Name</label>
        <?php html_text('name'); ?>
        <?php err('name'); ?>

        <label for="email">Email</label>
        <?php html_email('email'); ?>
        <?php err('email'); ?>

        <label for="phone">Phone</label>
        <?php html_text('phone'); ?>
        <?php err('phone'); ?>

        <label for="password">Password</label>
        <?php html_password('password'); ?>
        <?php err('password'); ?>

        <button class="btn primary">Create Admin</button>
        <a class="btn secondary" href="?module=admin&resource=admins&action=index">Cancel</a>
    </form>
</section>

