<?php
$title = $product['name'];
?>

<section class="product-detail">

    <div class="product-photo-view">

        <?php if (!empty($photos)): ?>

            <!-- Main Photo -->
            <div class="product-photo-view__main">
                <img
                    id="productMainPhoto"
                    src="<?= encode($photos[0]['photo_path']); ?>"
                    alt="<?= encode($product['name']); ?>"
                >
            </div>

            <!-- Thumbnails -->
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

            <img
                src="https://placehold.co/500x400"
                alt="<?= encode($product['name']); ?>"
            >

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
            <span 
                class="favorite-toggle" 
                data-product-id="<?= $product['id']; ?>" 
                data-is-favorited="<?= $isFavorited ? 'true' : 'false' ?>"
            >
                <i class="<?= $iconType ?> fa-heart <?= $colorClass ?>"></i>
            </span>
        </p>
        <p><?= nl2br(encode($product['description'])); ?></p>
        <p class="price">RM <?= number_format($product['price'], 2); ?></p>

        <form method="post" action="?module=cart&action=add">
            <input type="hidden" name="product_id" value="<?= $product['id']; ?>">
            <label>Quantity</label>
            <input type="number" name="quantity" min="1" value="1">
            <button class="detailBtn btn primary">Add to Cart</button>
        </form>
    </div>

</section>

<script>
function changeMainPhoto(thumb) {
    document.getElementById('productMainPhoto').src = thumb.src;

    document
        .querySelectorAll('.product-photo-view__thumb')
        .forEach(t => t.classList.remove('is-active'));

    thumb.classList.add('is-active');
}
</script>
