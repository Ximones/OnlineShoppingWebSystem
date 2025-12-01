<?php $title = 'Create Member'; ?>
<section class="panel">
    <h2>New Member</h2>
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

        <button class="btn primary">Create</button>
    </form>
</section>

