<?php $title = $product['name']; ?>

<section class="product-detail">
    <div class="product-photo-view">
        <?php if (!empty($photos)): ?>
            <div class="product-photo-view__main">
                <button class="slider-arrow prev" onclick="moveSlider(-1)">&#10094;</button>

                <img
                    id="productMainPhoto"
                    src="<?= encode($photos[0]['photo_path']); ?>"
                    alt="<?= encode($product['name']); ?>">

                <button class="slider-arrow next" onclick="moveSlider(1)">&#10095;</button>
            </div>

            <?php if (count($photos) > 1): ?>
                <div class="product-photo-view__thumbs">
                    <?php foreach ($photos as $index => $photo): ?>
                        <img
                            src="<?= encode($photo['photo_path']); ?>"
                            alt="<?= encode($product['name']); ?>"
                            class="product-photo-view__thumb <?= $index === 0 ? 'is-active' : '' ?>"
                            onclick="changeMainPhoto(this)">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <img src="<?= encode($product['photo'] ?? 'https://placehold.co/500x400'); ?>" alt="<?= encode($product['name']); ?>">
        <?php endif; ?>
    </div>

    <div class="product-detail__info">
        <div class="product-header-section">
            <h1><?= encode($product['name']); ?></h1>
            <?php if (!empty($product['sku'])): ?>
                <p class="product-sku"><?= encode($product['sku']); ?></p>
            <?php endif; ?>
        </div>

        <p class="price-line">
            <span class="detailPrice">RM <?= number_format($product['price'], 2); ?></span>
        </p>

        <!-- Stock Status Alert -->
        <?php if ($product['stock'] == 0): ?>
            <div class="stock-alert out-of-stock-alert" style="background-color: #f8d7da; border-left: 4px solid #dc3545; padding: 12px 15px; margin-bottom: 10px; border-radius: 4px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 20px;">🚫</span>
                    <div>
                        <strong style="font-size: 14px; color: #721c24; display: block;">Out of Stock - This product is currently unavailable.</strong>
                    </div>
                </div>
            </div>
        <?php elseif ($product['stock'] <= 10): ?>
            <div class="stock-alert low-stock-alert" style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 12px 15px; margin-bottom: 10px; border-radius: 4px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 20px;">⚠️</span>
                    <div>
                        <strong style="font-size: 14px; color: #856404; display: block;">Low Stock - Only <?= $product['stock']; ?> unit(s) remaining</strong>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($product['description'])): ?>
            <div class="product-description-box">
                <p class="product-description"><?= nl2br(encode($product['description'])); ?></p>
            </div>
        <?php endif; ?>

        <!-- Purchase Section -->
        <form method="post" action="?module=cart&action=add" class="add-to-cart-form" id="addToCartForm">
            <input type="hidden" name="product_id" value="<?= $product['id']; ?>">

            <div class="quantity-section">
                <label for="quantity-input" class="quantity-label">Quantity</label>
                <input
                    type="number"
                    id="quantity-input"
                    name="quantity"
                    min="1"
                    value="1"
                    class="quantity-input-field"
                    max="<?= $product['stock']; ?>"
                    <?= $product['stock'] == 0 ? 'disabled' : ''; ?>>
                <small id="quantityError" style="color: #dc3545; display: none; margin-top: 5px;"></small>
                <small style="color: #666; margin-top: 5px; display: block;">Remaining stock: <?= $product['stock']; ?> unit<?= $product['stock'] !== 1 ? 's' : ''; ?></small>
            </div>

            <div class="purchase-controls">
                <button
                    type="submit"
                    class="btn primary add-to-cart-btn"
                    id="addToCartBtn"
                    <?= $product['stock'] == 0 ? 'disabled style="opacity: 0.6; cursor: not-allowed;"' : ''; ?>>
                    <i class="fas fa-shopping-cart"></i>
                    Add to Cart
                </button>
                <button
                    type="submit"
                    class="btn secondary buy-now-btn"
                    id="buyNowBtn"
                    formaction="?module=cart&action=buy_now"
                    <?= $product['stock'] == 0 ? 'disabled style="opacity: 0.6; cursor: not-allowed;"' : ''; ?>>
                    <i class="fas fa-bolt"></i>
                    Buy Now
                </button>

                <?php
                $isFavorited = $isFavorited ?? false;
                $iconType = $isFavorited ? 'fas' : 'far';
                $colorClass = $isFavorited ? 'red-filled-heart' : 'black-outline-heart';
                ?>

                <span class="favorite-toggle" data-product-id="<?= $product['id']; ?>" data-is-favorited="<?= $isFavorited ? 'true' : 'false'; ?>">
                    <i class="<?= $iconType ?> fa-heart <?= $colorClass ?>"></i>
                </span>
            </div>
        </form>

        <!-- Technical Specifications -->
        <div class="product-specifications">
            <h3 class="specs-title">Technical Specifications</h3>

            <div class="specs-grid">
                <?php if (!empty($product['color'])): ?>
                    <div class="spec-item">
                        <span class="spec-label">Color</span>
                        <span class="spec-value"><?= encode($product['color']); ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($product['size'])): ?>
                    <div class="spec-item">
                        <span class="spec-label">Dimensions</span>
                        <span class="spec-value"><?= encode($product['size']); ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($product['pit_spacing'])): ?>
                    <div class="spec-item">
                        <span class="spec-label">Rough-In / Pit Spacing</span>
                        <span class="spec-value"><?= encode($product['pit_spacing']); ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($product['installation_type'])): ?>
                    <div class="spec-item">
                        <span class="spec-label">Installation Type</span>
                        <span class="spec-value"><?= encode($product['installation_type']); ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($product['flushing_method'])): ?>
                    <div class="spec-item">
                        <span class="spec-label">Flushing System</span>
                        <span class="spec-value"><?= encode($product['flushing_method']); ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($product['bowl_shape'])): ?>
                    <div class="spec-item">
                        <span class="spec-label">Bowl Shape</span>
                        <span class="spec-value"><?= encode($product['bowl_shape']); ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($product['material'])): ?>
                    <div class="spec-item">
                        <span class="spec-label">Material</span>
                        <span class="spec-value"><?= encode($product['material']); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Additional Information -->
        <div class="product-features">
            <div class="feature-badge">
                <i class="fas fa-shield-alt"></i>
                <span><?= $product['warranty_years'] ?? 2; ?>-Year Warranty</span>
            </div>
            <div class="feature-badge">
                <i class="fas fa-tools"></i>
                <span>Professional Installation Available</span>
            </div>
        </div>

        <?php if (!empty($reviews)): ?>
            <div class="product-reviews">
                <h3 class="specs-title">Customer Reviews</h3>
                <?php foreach ($reviews as $review): ?>
                    <div class="product-review-item">
                        <div class="product-review-header">
                            <strong><?= encode($review['user_name']); ?></strong>
                            <span class="product-review-rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?= $i <= (int)$review['rating'] ? '★' : '☆'; ?>
                                <?php endfor; ?>
                            </span>
                        </div>
                        <p class="product-review-comment"><?= nl2br(encode($review['comment'])); ?></p>
                        <div class="product-review-meta">
                            <?= encode($review['created_at']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
    function changeMainPhoto(thumb) {
        const mainPhoto = document.getElementById('productMainPhoto');
        mainPhoto.classList.add('photo-fade-out');

        setTimeout(() => {
            mainPhoto.src = thumb.src;
            mainPhoto.classList.remove('photo-fade-out');
        }, 200);

        document.querySelectorAll('.product-photo-view__thumb').forEach(t => t.classList.remove('is-active'));
        thumb.classList.add('is-active');
    }

    let currentPhotoIndex = 0;
    const photoThumbs = Array.from(document.querySelectorAll('.product-photo-view__thumb'));

    function moveSlider(direction) {
        if (photoThumbs.length === 0) return;

        currentPhotoIndex += direction;

        if (currentPhotoIndex < 0) {
            currentPhotoIndex = photoThumbs.length - 1;
        } else if (currentPhotoIndex >= photoThumbs.length) {
            currentPhotoIndex = 0;
        }

        photoThumbs[currentPhotoIndex].click();
    }

    // Stock validation
    const quantityInput = document.getElementById('quantity-input');
    const addToCartForm = document.getElementById('addToCartForm');
    const addToCartBtn = document.getElementById('addToCartBtn');
    const buyNowBtn = document.getElementById('buyNowBtn');
    const quantityError = document.getElementById('quantityError');
    const maxStock = <?= $product['stock']; ?>;

    quantityInput?.addEventListener('input', function() {
        const quantity = parseInt(this.value) || 0;

        if (quantity > maxStock) {
            quantityError.textContent = `Cannot exceed available stock (${maxStock} unit${maxStock !== 1 ? 's' : ''})`;
            quantityError.style.display = 'block';
            addToCartBtn.disabled = true;
            buyNowBtn.disabled = true;
        } else if (quantity < 1) {
            quantityError.textContent = 'Quantity must be at least 1';
            quantityError.style.display = 'block';
            addToCartBtn.disabled = true;
            buyNowBtn.disabled = true;
        } else {
            quantityError.style.display = 'none';
            addToCartBtn.disabled = false;
            buyNowBtn.disabled = false;
        }
    });

    addToCartForm?.addEventListener('submit', function(e) {
        const quantity = parseInt(quantityInput.value) || 0;

        if (quantity > maxStock) {
            e.preventDefault();
            alert(`Cannot add ${quantity} units. Only ${maxStock} unit${maxStock !== 1 ? 's' : ''} available in stock.`);
            return false;
        }

        if (quantity < 1) {
            e.preventDefault();
            alert('Please enter a valid quantity');
            return false;
        }
    });
</script>