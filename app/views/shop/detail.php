<?php $title = $product['name']; ?>

<section class="product-detail">
    <div class="product-photo-view">
        <?php if (!empty($photos)): ?>
            <div class="product-photo-view__main">
                <img
                    id="productMainPhoto"
                    src="<?= encode($photos[0]['photo_path']); ?>"
                    alt="<?= encode($product['name']); ?>"
                >
            </div>

            <?php if (count($photos) > 1): ?>
                <div class="product-photo-view__thumbs">
                    <?php foreach ($photos as $index => $photo): ?>
                        <img
                            src="<?= encode($photo['photo_path']); ?>"
                            alt="<?= encode($product['name']); ?>"
                            class="product-photo-view__thumb <?= $index === 0 ? 'is-active' : '' ?>"
                            onclick="changeMainPhoto(this)"
                        >
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <img src="<?= encode($product['photo'] ?? 'https://placehold.co/500x400'); ?>" alt="<?= encode($product['name']); ?>">
        <?php endif; ?>
    </div>
    
    <div class="product-detail__info">
        <h1><?= encode($product['name']); ?></h1>
        
        <?php 
            $isFavorited = $isFavorited ?? false; 
            $iconType = $isFavorited ? 'fas' : 'far';
            $colorClass = $isFavorited ? 'red-filled-heart' : 'black-outline-heart';
        ?>
        
        <p class="price-line">
            <span class="price">RM <?= number_format($product['price'], 2); ?></span>
        </p>
        
        <p><?= nl2br(encode($product['description'])); ?></p>
        
        <form method="post" action="?module=cart&action=add" class="add-to-cart-form">
            <input type="hidden" name="product_id" value="<?= $product['id']; ?>">
            
            <div class="quantity-wrapper"> 
                <label for="quantity-input">Quantity</label>
                <input type="number" id="quantity-input" name="quantity" min="1" value="1" class="quantity-input-field">
            </div>
            
            <div class="action-buttons-wrapper action-buttons-full-width"> 
                <button type="submit" class="btn primary add-to-cart-btn">Add to Cart</button>
                
                <div 
                    class="btn secondary favorite-toggle-btn favorite-toggle" 
                    data-product-id="<?= $product['id']; ?>"
                    data-is-favorited="<?= $isFavorited ? 'true' : 'false'; ?>"
                >
                    <i class="<?= $iconType ?> fa-heart <?= $colorClass ?>"></i>
                    <span class="favorite-text">Add to Favorite</span>
                </div>
            </div>
        </form>
    </div>
</section>

<script>
function changeMainPhoto(thumb) {
    document.getElementById('productMainPhoto').src = thumb.src;
    document.querySelectorAll('.product-photo-view__thumb').forEach(t => t.classList.remove('is-active'));
    thumb.classList.add('is-active');
}
</script>