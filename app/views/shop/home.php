<?php $title = 'Welcome'; ?>
<section class="hero">
    <div>
        <h1>Shop smarter with <?= APP_NAME; ?></h1>
        <p>Discover curated products, manage your cart, and track your orders effortlessly.</p>
        <a class="btn primary" href="?module=shop&action=catalog">Browse Catalog</a>
    </div>
</section>

<section class="grid">
    <?php foreach (array_slice($products, 0, 6) as $product): ?>
        <article class="card product-card">
            <img src="<?= encode($product['photo'] ?? 'https://placehold.co/400x250'); ?>" alt="<?= encode($product['name']); ?>">
            <h3><?= encode($product['name']); ?></h3>
            <p class="price">RM <?= number_format($product['price'], 2); ?></p>
            <a class="btn secondary" href="?module=shop&action=detail&id=<?= $product['id']; ?>">View</a>
        </article>
    <?php endforeach; ?>
</section>

