<?php $title = 'Welcome'; ?>

<section class="store-top-nav">
    <div class="store-top-nav-inner">
        <h1 class="store-top-title">Store</h1>
        <div class="store-top-row">
            <?php foreach ($categories as $category): ?>
                <?php
                $slug = strtolower(str_replace(' ', '-', $category['name']));
                $iconPath = 'public/images/store/' . $slug . '.png';
                ?>
                <a class="store-top-item"
                   href="?module=shop&action=catalog&category_id=<?= $category['id']; ?>">
                    <div class="store-top-icon">
                        <img src="<?= asset($iconPath); ?>" alt="<?= encode($category['name']); ?>">
                    </div>
                    <span class="store-top-label"><?= encode($category['name']); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="home-products">
    <h2 class="home-products-heading">
        <span class="home-products-heading-highlight">The latest.</span>
        <span>&nbsp;Fresh toilet bowls.</span>
    </h2>

    <div class="home-products-shell">
        <button class="home-products-nav home-products-nav-left" type="button" aria-label="Previous toilet bowls">
            ‹
        </button>

        <div class="home-products-row" id="home-products-row-toilets">
            <?php foreach ($toiletProducts as $product): ?>
                <article class="home-product-card">
                    <div class="home-product-body">
                        <h3 class="home-product-title"><?= encode($product['name']); ?></h3>
                        <p class="home-product-subtitle">From RM <?= number_format($product['price'], 2); ?></p>
                    </div>
                    <div class="home-product-media">
                        <img src="<?= encode($product['photo'] ?? 'https://placehold.co/600x420'); ?>" alt="<?= encode($product['name']); ?>">
                    </div>
                    <div class="home-product-footer">
                        <a class="btn secondary home-product-cta" href="?module=shop&action=detail&id=<?= $product['id']; ?>">
                            View details
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <button class="home-products-nav home-products-nav-right" type="button" aria-label="Next toilet bowls">
            ›
        </button>
    </div>
</section>

<?php if (!empty($accessoryProducts)): ?>
<section class="home-products">
    <h2 class="home-products-heading">
        <span class="home-products-heading-highlight">Accessories.</span>
        <span>&nbsp;Brushes, cleaners & more.</span>
    </h2>

    <div class="home-products-shell">
        <button class="home-products-nav home-products-nav-left" type="button" aria-label="Previous accessories">
            ‹
        </button>

        <div class="home-products-row" id="home-products-row-accessories">
            <?php foreach ($accessoryProducts as $product): ?>
                <article class="home-product-card">
                    <div class="home-product-body">
                        <h3 class="home-product-title"><?= encode($product['name']); ?></h3>
                        <p class="home-product-subtitle">From RM <?= number_format($product['price'], 2); ?></p>
                    </div>
                    <div class="home-product-media">
                        <img src="<?= encode($product['photo'] ?? 'https://placehold.co/600x420'); ?>" alt="<?= encode($product['name']); ?>">
                    </div>
                    <div class="home-product-footer">
                        <a class="btn secondary home-product-cta" href="?module=shop&action=detail&id=<?= $product['id']; ?>">
                            View details
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <button class="home-products-nav home-products-nav-right" type="button" aria-label="Next accessories">
            ›
        </button>
    </div>
</section>
<?php endif; ?>

