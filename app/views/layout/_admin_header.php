<header class="site-header">
    <div class="container header-flex">
        <a href="<?= asset('?module=admin&action=dashboard'); ?>" class="logo">
            <img src="<?= asset('app/logo/dblogo.png'); ?>" alt="<?= APP_NAME; ?>" class="logo-img">
        </a>

        <nav class="nav">
            <a href="?module=admin&action=dashboard">Dashboard</a>
            <a href="?module=admin&resource=products&action=index">Products</a>
            <a href="?module=admin&resource=categories&action=index">Categories</a>
            <a href="?module=admin&resource=members&action=index">Members</a>
            <a href="?module=admin&resource=vouchers&action=index">Vouchers</a>
            <a href="?module=admin&resource=orders&action=index">Orders</a>
            <a href="?module=admin&resource=paylater&action=index">PayLater</a>
            <a href="?module=shop&action=home">View Store</a>
            <a href="?module=auth&action=logout">Logout</a>
        </nav>
    </div>
</header>

