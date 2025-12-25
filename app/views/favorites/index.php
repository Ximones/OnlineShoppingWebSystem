<?php $title = $title ?? 'My Favorites'; ?>

<section class="page-header">
    <h1><i class="fas fa-heart"></i> <?= $title ?></h1>
    <p>All the products you've saved for later.</p>
</section>

<div id="pagination-ajax-target">
    <?php if (empty($favoriteProducts)): ?>
        <div class="empty-state">
            <p>You haven't added any products to your favorites yet.</p>
            <a href="?module=shop&action=catalog" class="btn primary">Start Browsing Products</a>
        </div>
    <?php else: ?>
        <section id="product-grid" class="grid product-catalog-grid">
            <?php foreach ($favoriteProducts as $product): ?>
                <article class="card product-card">
                    <img
                        src="<?= encode($product['photo_path'] ?? 'https://placehold.co/400x250'); ?>"
                        alt="<?= encode($product['name']); ?>">

                    <h3><?= encode($product['name']); ?></h3>
                    <p class="price">RM <?= number_format($product['price'], 2); ?></p>

                    <?php
                    $isFavorited = true;
                    $iconType = 'fas';
                    $colorClass = 'red-filled-heart';
                    ?>

                    <div class="product-card-footer">
                        <!-- Add to Cart -->
                        <form method="post" action="?module=cart&action=add" style="margin:0;">
                            <input type="hidden" name="product_id" value="<?= $product['id']; ?>">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="btn primary btn small add-to-cart-btn">
                                Add to Cart
                            </button>
                        </form>

                        <!-- Details -->
                        <a
                            class="btn secondary btn small"
                            href="?module=shop&action=detail&id=<?= $product['id']; ?>">
                            Details
                        </a>

                        <!-- Favorite Heart -->
                        <span
                            class="favorite-toggle"
                            data-product-id="<?= $product['id']; ?>"
                            data-is-favorited="true"
                            title="Remove from Favorites">
                            <i class="<?= $iconType ?> fa-heart <?= $colorClass ?>"></i>
                        </span>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
        
        <?php if ($totalPages > 1): ?>
            <div class="pagination-container">
                <div class="pagination">
                    <a href="#"
                        class="page-link <?= ($page <= 1) ? 'disabled' : ''; ?>"
                        data-page="<?= $page - 1; ?>">
                        &laquo; Prev
                    </a>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="#"
                            class="page-link <?= ($i == $page) ? 'is-active' : ''; ?>"
                            data-page="<?= $i; ?>">
                            <?= $i; ?>
                        </a>
                    <?php endfor; ?>

                    <a href="#"
                        class="page-link <?= ($page >= $totalPages) ? 'disabled' : ''; ?>"
                        data-page="<?= $page + 1; ?>">
                        Next &raquo;
                    </a>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<form id="filter-form" style="display:none;"></form>