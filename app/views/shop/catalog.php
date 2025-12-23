<?php
$title = 'Product Catalog';

use App\Models\ProductPhoto;

$productPhotoModel = new ProductPhoto();
?>

<section class="panel">
    <form id="filter-form" method="get" class="search-panel">
        <input type="hidden" name="module" value="shop">
        <input type="hidden" name="action" value="catalog">

        <div class="input-group full-width-input">
            <input
                type="text"
                id="keyword-search"
                name="keyword"
                placeholder="Search keyword"
                value="<?= encode($filters['keyword'] ?? ''); ?>"
            >
        </div>

        <div class="input-group full-width-input">
            <select name="category_id" id="category-filter">
                <option value="">All Categories</option>
                <?php foreach ($categories as $category): ?>
                    <option
                        value="<?= $category['id']; ?>"
                        <?= ($filters['category_id'] ?? '') == $category['id'] ? 'selected' : ''; ?>
                    >
                        <?= encode($category['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="price-range-line">
            <div class="price-input-wrapper">
                <span class="currency-prefix">RM</span>
                <input
                    type="number"
                    class="price-input"
                    name="min_price"
                    placeholder="Min"
                    value="<?= encode($filters['min_price'] ?? ''); ?>"
                    min="0"
                >
            </div>

            <span class="price-separator">-</span>

            <div class="price-input-wrapper">
                <span class="currency-prefix">RM</span>
                <input
                    type="number"
                    class="price-input"
                    name="max_price"
                    placeholder="Max"
                    value="<?= encode($filters['max_price'] ?? ''); ?>"
                    min="0"
                >
            </div>
        </div>

        <div class="input-group full-width-input">
            <select name="sort" id="sort-filter">
                <option value="">Sort By: Default</option>
                <option value="price_asc" <?= ($filters['sort'] ?? '') == 'price_asc' ? 'selected' : ''; ?>>
                    Price: Low to High
                </option>
                <option value="price_desc" <?= ($filters['sort'] ?? '') == 'price_desc' ? 'selected' : ''; ?>>
                    Price: High to Low
                </option>
            </select>
        </div>

        <button class="btn primary search-btn full-width-button">Search</button>
    </form>
</section>

<section id="product-grid" class="grid">
    <?php if (!empty($products)): ?>
        <?php foreach ($products as $product): ?>
            <?php if (($product['status'] ?? '') !== 'active') continue; ?>
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
                    $isFavorited = $product['is_favorited'] ?? false;
                    $iconType = $isFavorited ? 'fas' : 'far';
                    $colorClass = $isFavorited ? 'red-filled-heart' : 'black-outline-heart';
                ?>
                <span class="favorite-toggle" data-product-id="<?= $product['id']; ?>" data-is-favorited="<?= $isFavorited ? 'true' : 'false'; ?>">
                    <i class="<?= $iconType ?> fa-heart <?= $colorClass ?>"></i>
                </span>
                <a class="btn secondary" href="?module=shop&action=detail&id=<?= $product['id']; ?>">Details</a>
            </article>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No products found.</p>
    <?php endif; ?>
</section>

<div id="pagination-ajax-target">
    <?php if ($totalPages > 1): ?>
        <div class="pagination-container">
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="#" class="page-link" data-page="<?= $page - 1; ?>">&laquo; Prev</a>
                <?php else: ?>
                    <span class="page-link disabled">&laquo; Prev</span>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="#" class="page-link <?= ($i == $page) ? 'is-active' : ''; ?>" data-page="<?= $i; ?>">
                        <?= $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="#" class="page-link" data-page="<?= $page + 1; ?>">Next &raquo;</a>
                <?php else: ?>
                    <span class="page-link disabled">Next &raquo;</span>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
</section>
