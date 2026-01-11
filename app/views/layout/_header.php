<header class="site-header">
    <div class="container header-flex">
        <a href="<?= asset('?module=shop&action=home'); ?>" class="logo">
            <img src="<?= asset('app/logo/dblogo.png'); ?>" alt="<?= APP_NAME; ?>" class="logo-img">
        </a>

        <button class="mobile-menu-toggle" aria-label="Toggle menu" aria-expanded="false">
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
        </button>

        <nav class="nav">
            <a href="?module=shop&action=catalog" class="<?= $this->is_active('shop'); ?>">Products</a>

            <?php if (auth_user()): ?>
                <a href="?module=cart&action=index" class="<?= $this->is_active('cart'); ?>">Cart</a>
                <a href="?module=orders&action=history" class="<?= $this->is_active('orders'); ?>">Orders</a>
                <a href="?module=game&action=points" class="<?= $this->is_active('game'); ?>">Points</a>
                <a href="?module=bills&action=index" class="<?= $this->is_active('bills'); ?>">PayLater</a>
                <a href="?module=vouchers&action=index" class="<?= $this->is_active('vouchers'); ?>">Vouchers</a>
                <a href="?module=favorites&action=index" class="<?= $this->is_active('favorites'); ?>">Favorites</a>
                <a href="?module=profile&action=index" class="<?= $this->is_active('profile'); ?>">Profile</a>

                <?php if (is_admin()): ?>
                    <a href="?module=admin&action=dashboard" class="switch-mode-link <?= $this->is_active('admin'); ?>">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Admin Dashboard</span>
                    </a>
                <?php endif; ?>

                <a href="?module=auth&action=logout">Logout</a>
            <?php else: ?>
                <a href="?module=auth&action=login" class="<?= $this->is_active('auth'); ?>">Login</a>
                <a href="?module=auth&action=register">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>