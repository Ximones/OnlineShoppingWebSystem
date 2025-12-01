<?php $title = 'My Profile'; ?>
<section class="grid profile-grid">
    <article class="card">
        <h2>Account Info</h2>
        <form method="post" action="?module=profile&action=update">
            <label for="name">Name</label>
            <input type="text" name="name" value="<?= encode($user['name']); ?>" required>
            <?php err('name'); ?>

            <label for="phone">Phone</label>
            <input type="text" name="phone" value="<?= encode($user['phone']); ?>" required>
            <?php err('phone'); ?>

            <label for="address">Address</label>
            <textarea name="address" required><?= encode($user['address']); ?></textarea>
            <?php err('address'); ?>

            <button class="btn primary">Update Profile</button>
        </form>
    </article>

    <article class="card">
        <h2>Change Password</h2>
        <form method="post" action="?module=profile&action=password">
            <label for="current_password">Current Password</label>
            <input type="password" name="current_password" required>
            <?php err('current_password'); ?>

            <label for="password">New Password</label>
            <input type="password" name="password" required>
            <?php err('password'); ?>

            <label for="confirm_password">Confirm Password</label>
            <input type="password" name="confirm_password" required>
            <?php err('confirm_password'); ?>

            <button class="btn secondary">Update Password</button>
        </form>
    </article>

    <article class="card">
        <h2>Profile Photo</h2>
        <img class="avatar" src="<?= encode($user['avatar'] ?? 'https://placehold.co/200x200'); ?>" alt="<?= encode($user['name']); ?>">
        <form method="post" action="?module=profile&action=photo" enctype="multipart/form-data">
            <input type="file" name="avatar" accept="image/*" required>
            <button class="btn secondary">Upload</button>
        </form>
    </article>
</section>

