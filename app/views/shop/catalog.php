<?php
$title = 'Product Catalog';

use App\Models\ProductPhoto;

$productPhotoModel = new ProductPhoto();
?>

<section class="panel">
    <form method="get" class="search-panel">
        <input type="hidden" name="module" value="shop">
        <input type="hidden" name="action" value="catalog">

        <div class="input-group full-width-input">
            <input type="text" name="keyword" placeholder="Search keyword" value="<?= encode($filters['keyword'] ?? ''); ?>">
        </div>

        <div class="input-group full-width-input">
            <select name="category_id">
                <option value="">All Categories</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id']; ?>" <?= ($filters['category_id'] ?? '') == $category['id'] ? 'selected' : ''; ?>>
                        <?= encode($category['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="price-range-line">

            <div class="price-input-wrapper">
                <span class="currency-prefix">RM</span>
                <input type="number" name="min_price" placeholder="Min"
                    value="<?= encode($filters['min_price'] ?? ''); ?>" min="0">
            </div>

            <span class="price-separator">-</span>

            <div class="price-input-wrapper">
                <span class="currency-prefix">RM</span>
                <input type="number" name="max_price" placeholder="Max"
                    value="<?= encode($filters['max_price'] ?? ''); ?>" min="0">
            </div>

        </div>

        <button class="btn primary search-btn full-width-button">Search</button>

    </form>
</section>

<section class="grid">
    <?php foreach ($products as $product): ?>
        <?php
        $primaryPhoto = $productPhotoModel->getPrimaryPhoto($product['id']);
        $photoSrc = $primaryPhoto['photo_path'] ?? 'https://placehold.co/400x250';
        ?>
        <article class="card product-card">
            <img src="<?= encode($photoSrc); ?>" alt="<?= encode($product['name']); ?>">
            <h3><?= encode($product['name']); ?></h3>
            <p><?= encode(substr($product['description'], 0, 100)); ?>...</p>
            <p class="price">RM <?= number_format($product['price'], 2); ?></p>
            
            <?php 
                // NOTE: You MUST update your controller that generates $products
                // to include a new boolean field 'is_favorited' for EACH product.
                $isFavorited = $product['is_favorited'] ?? false;
                $iconType = $isFavorited ? 'fas' : 'far';
                $colorClass = $isFavorited ? 'red-filled-heart' : 'black-outline-heart';
            ?>
            <span 
                class="favorite-toggle" 
                data-product-id="<?= $product['id']; ?>" 
                data-is-favorited="<?= $isFavorited ? 'true' : 'false' ?>"
            >
                <i class="<?= $iconType ?> fa-heart <?= $colorClass ?>"></i>
            </span>
            <a class="btn secondary" href="?module=shop&action=detail&id=<?= $product['id']; ?>">Details</a>
        </article>
    <?php endforeach; ?>
</section>