<header class="site-header">
    <div class="container header-flex">
        <a href="<?= asset('?module=shop&action=home'); ?>" class="logo">
            <img src="<?= asset('app/logo/dblogo.png'); ?>" alt="<?= APP_NAME; ?>" class="logo-img">
        </a>

        <nav class="nav">
            <a href="?module=shop&action=catalog">Products</a>

            <?php if (auth_user()): ?>
                <a href="?module=cart&action=index">Cart</a>
                <a href="?module=orders&action=history">Orders</a>
                <a href="?module=game&action=points">Points</a>
                <a href="?module=bills&action=index">PayLater</a>
                <a href="?module=vouchers&action=index">Vouchers</a>
                <a href="?module=favorites&action=index">Favorites</a>
                <a href="?module=profile&action=index">Profile</a>

                <?php if (is_admin()): ?>
                    <a href="?module=admin&action=dashboard" class="switch-mode-link">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Admin Dashboard</span>
                    </a>
                <?php endif; ?>

                <a href="?module=auth&action=logout">Logout</a>
            <?php else: ?>
                <a href="?module=auth&action=login">Login</a>
                <a href="?module=auth&action=register">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
