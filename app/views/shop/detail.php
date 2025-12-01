<?php $title = $product['name']; ?>
<section class="product-detail">
    <img src="<?= encode($product['photo'] ?? 'https://placehold.co/500x400'); ?>" alt="<?= encode($product['name']); ?>">
    <div>
        <h1><?= encode($product['name']); ?></h1>
        <p><?= nl2br(encode($product['description'])); ?></p>
        <p class="price">RM <?= number_format($product['price'], 2); ?></p>
        <form method="post" action="?module=cart&action=add">
            <input type="hidden" name="product_id" value="<?= $product['id']; ?>">
            <label>Quantity</label>
            <input type="number" name="quantity" min="1" value="1">
            <button class="btn primary">Add to Cart</button>
        </form>
    </div>
</section>

