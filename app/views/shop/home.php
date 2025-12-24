<?php

use App\Models\ProductPhoto;

$productPhotoModel = new ProductPhoto();

$title = 'Welcome';
$toiletProducts = $toiletProducts ?? [];
$accessoryProducts = $accessoryProducts ?? [];
$otherProducts = $otherProducts ?? [];
$categories = $categories ?? [];
$allPhotos = $allPhotos ?? [];
?>

<section class="store-top-nav">
    <div class="store-top-nav-inner">
        <h1 class="store-top-title">Welcome.</h1>
        <div class="store-top-shell">
            <button class="home-products-nav home-products-nav-left" type="button" aria-label="Previous categories">
                ‹
            </button>

            <div class="store-top-row">
                <?php foreach ($categories as $category): ?>
                    <a class="store-top-item"
                        href="?module=shop&action=catalog&category_id=<?= $category['id']; ?>">
                        <div class="store-top-icon">
                            <?php if (!empty($category['image_url'])): ?>
                                <img src="<?= encode($category['image_url']); ?>" alt="<?= encode($category['name']); ?>">
                            <?php else: ?>
                                <span style="color: #999; font-size: 2rem;">📁</span>
                            <?php endif; ?>
                        </div>
                        <span class="store-top-label"><?= encode($category['name']); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>

            <button class="home-products-nav home-products-nav-right" type="button" aria-label="Next categories">
                ›
            </button>
        </div>
    </div>
</section>

<?php if (!empty($topSellers)): ?>
<section class="home-products">
    <h2 class="home-products-heading">
        <span class="home-products-heading-highlight" style="color: #d97706;">Top 5 Sellers.</span>
        <span>&nbsp;Most popular this month.</span>
    </h2>

    <div class="home-products-shell">
        <button class="home-products-nav home-products-nav-left" type="button">‹</button>

        <div class="home-products-row" id="home-products-row-top-sellers">
            <?php foreach ($topSellers as $index => $product): ?>
                <?php
                $primaryPhoto = $productPhotoModel->getPrimaryPhoto($product['id']);
                $photoSrc = $primaryPhoto['photo_path'] ?? 'https://placehold.co/600x420';
                ?>
                <article class="home-product-card" style="position: relative;">
                    <div style="position:absolute;top:20px;right:20px;background:#000;color:#fff;
                        width:30px;height:30px;border-radius:50%;display:flex;
                        align-items:center;justify-content:center;font-weight:bold;">
                        <?= $index + 1; ?>
                    </div>

                    <div class="home-product-body">
                        <h3 class="home-product-title"><?= encode($product['name']); ?></h3>
                        <p class="home-product-subtitle">
                            From RM <?= number_format($product['price'], 2); ?>
                        </p>
                    </div>

                    <div class="home-product-media">
                        <img src="<?= encode($photoSrc); ?>" alt="<?= encode($product['name']); ?>">
                    </div>

                    <div class="home-product-footer">
                        <a class="btn secondary home-product-cta"
                           href="?module=shop&action=detail&id=<?= $product['id']; ?>">
                            View details
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <button class="home-products-nav home-products-nav-right" type="button">›</button>
    </div>
</section>
<?php endif; ?>

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
                <?php if (($product['status'] ?? '') !== 'active') continue; ?>
                <?php
                $primaryPhoto = $productPhotoModel->getPrimaryPhoto($product['id']);
                $photoSrc = $primaryPhoto['photo_path'] ?? 'https://placehold.co/600x420';
                ?>
                <article class="home-product-card">
                    <div class="home-product-body">
                        <h3 class="home-product-title"><?= encode($product['name']); ?></h3>
                        <p class="home-product-subtitle">From RM <?= number_format($product['price'], 2); ?></p>
                    </div>
                    <div class="home-product-media">
                        <img src="<?= encode($photoSrc); ?>" alt="<?= encode($product['name']); ?>">
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
            <span class="home-products-heading-highlight">Cleaning Essentials.</span>
            <span>&nbsp;Brushes, cleaners & more.</span>
        </h2>

        <div class="home-products-shell">
            <button class="home-products-nav home-products-nav-left" type="button" aria-label="Previous accessories">
                ‹
            </button>

            <div class="home-products-row" id="home-products-row-accessories">
                <?php foreach ($accessoryProducts as $product): ?>
                    <?php
                    $photoSrc = isset($allPhotos[$product['id']]) && !empty($allPhotos[$product['id']]['photo_path'])
                        ? $allPhotos[$product['id']]['photo_path']
                        : 'https://placehold.co/600x420';
                    ?>
                    <article class="home-product-card">
                        <div class="home-product-body">
                            <h3 class="home-product-title"><?= encode($product['name']); ?></h3>
                            <p class="home-product-subtitle">From RM <?= number_format($product['price'], 2); ?></p>
                        </div>
                        <div class="home-product-media">
                            <img src="<?= encode($photoSrc); ?>" alt="<?= encode($product['name']); ?>">
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

<?php if (!empty($otherProducts)): ?>
    <section class="home-products">
        <h2 class="home-products-heading">
            <span class="home-products-heading-highlight">More essentials.</span>
            <span>&nbsp;Bidets, seats & mats.</span>
        </h2>

        <div class="home-products-shell">
            <button class="home-products-nav home-products-nav-left" type="button" aria-label="Previous essentials">
                ‹
            </button>

            <div class="home-products-row" id="home-products-row-other">
                <?php foreach ($otherProducts as $product): ?>
                    <?php
                    $photoSrc = isset($allPhotos[$product['id']]) && !empty($allPhotos[$product['id']]['photo_path'])
                        ? $allPhotos[$product['id']]['photo_path']
                        : 'https://placehold.co/600x420';
                    ?>
                    <article class="home-product-card">
                        <div class="home-product-body">
                            <h3 class="home-product-title"><?= encode($product['name']); ?></h3>
                            <p class="home-product-subtitle">From RM <?= number_format($product['price'], 2); ?></p>
                        </div>
                        <div class="home-product-media">
                            <img src="<?= encode($photoSrc); ?>" alt="<?= encode($product['name']); ?>">
                        </div>
                        <div class="home-product-footer">
                            <a class="btn secondary home-product-cta" href="?module=shop&action=detail&id=<?= $product['id']; ?>">
                                View details
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <button class="home-products-nav home-products-nav-right" type="button" aria-label="Next essentials">
                ›
            </button>
        </div>
    </section>
<?php endif; ?>