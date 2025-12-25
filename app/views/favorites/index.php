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
                <?php
                $isOutOfStock = $product['stock'] == 0;
                $isFavorited = true;
                $iconType = 'fas';
                $colorClass = 'red-filled-heart';
                ?>
                <article class="card product-card <?= $isOutOfStock ? 'out-of-stock-card' : ''; ?>" style="<?= $isOutOfStock ? 'opacity: 0.7; position: relative;' : ''; ?>">
                    <div style="position: relative;">
                        <img
                            src="<?= encode($product['photo_path'] ?? 'https://placehold.co/400x250'); ?>"
                            alt="<?= encode($product['name']); ?>"
                            style="<?= $isOutOfStock ? 'opacity: 0.5;' : ''; ?>">

                        <?php if ($isOutOfStock): ?>
                            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; z-index: 10;">
                                <div style="background: rgba(0, 0, 0, 0.5); color: white; padding: 15px 25px; border-radius: 8px; font-weight: bold; font-size: 16px;">
                                    OUT OF STOCK
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <h3><?= encode($product['name']); ?></h3>
                    <p class="price">RM <?= number_format($product['price'], 2); ?></p>

                    <div class="product-card-footer">
                        <?php if ($isOutOfStock): ?>
                            <button class="btn primary btn small" disabled style="opacity: 0.6; cursor: not-allowed;">
                                Add to Cart
                            </button>
                            <a class="btn secondary btn small" href="?module=shop&action=detail&id=<?= $product['id']; ?>">Details</a>
                        <?php else: ?>
                            <!-- Add to Cart -->
                            <form method="post" action="?module=cart&action=add" style="margin:0;">
                                <input type="hidden" name="product_id" value="<?= $product['id']; ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="btn primary btn small add-to-cart-btn">
                                    Add to Cart
                                </button>
                            </form>

                            <!-- Details -->

                            class="btn secondary btn small"
                            href="?module=shop&action=detail&id=<?= $product['id']; ?>">
                            Details
                            </a>
                        <?php endif; ?>

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