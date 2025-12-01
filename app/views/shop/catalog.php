<?php $title = 'Product Catalog'; ?>
<section class="panel">
    <form method="get" class="form-inline">
        <input type="hidden" name="module" value="shop">
        <input type="hidden" name="action" value="catalog">
        <input type="text" name="keyword" placeholder="Search keyword" value="<?= encode($filters['keyword'] ?? ''); ?>">
        <select name="category_id">
            <option value="">All Categories</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= $category['id']; ?>" <?= ($filters['category_id'] ?? '') == $category['id'] ? 'selected' : ''; ?>>
                    <?= encode($category['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button class="btn primary">Search</button>
    </form>
</section>

<section class="grid">
    <?php foreach ($products as $product): ?>
        <article class="card product-card">
            <img src="<?= encode($product['photo'] ?? 'https://placehold.co/400x250'); ?>" alt="<?= encode($product['name']); ?>">
            <h3><?= encode($product['name']); ?></h3>
            <p><?= encode(substr($product['description'], 0, 100)); ?>...</p>
            <p class="price">RM <?= number_format($product['price'], 2); ?></p>
            <a class="btn secondary" href="?module=shop&action=detail&id=<?= $product['id']; ?>">Details</a>
        </article>
    <?php endforeach; ?>
</section>

