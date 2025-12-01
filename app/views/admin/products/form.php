<?php $title = isset($product) ? 'Edit Product' : 'New Product'; ?>
<section class="panel">
    <h2><?= $title; ?></h2>
    <form method="post" enctype="multipart/form-data">
        <label for="name">Name</label>
        <input type="text" name="name" value="<?= encode($product['name'] ?? ''); ?>" required>
        <?php err('name'); ?>

        <label for="sku">SKU</label>
        <input type="text" name="sku" value="<?= encode($product['sku'] ?? ''); ?>" required>
        <?php err('sku'); ?>

        <label for="category_id">Category</label>
        <select name="category_id" required>
            <option value="">Select One</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= $category['id']; ?>" <?= ($product['category_id'] ?? '') == $category['id'] ? 'selected' : ''; ?>>
                    <?= encode($category['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php err('category_id'); ?>

        <label for="description">Description</label>
        <textarea name="description"><?= encode($product['description'] ?? ''); ?></textarea>

        <label for="price">Price (RM)</label>
        <input type="number" step="0.01" name="price" value="<?= encode($product['price'] ?? ''); ?>" required>
        <?php err('price'); ?>

        <label for="stock">Stock</label>
        <input type="number" name="stock" value="<?= encode($product['stock'] ?? ''); ?>" required>
        <?php err('stock'); ?>

        <label for="status">Status</label>
        <select name="status">
            <option value="active" <?= ($product['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
            <option value="inactive" <?= ($product['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
        </select>

        <label for="photo">Photo</label>
        <input type="file" name="photo" accept="image/*">
        <?php if (!empty($product['photo'])): ?>
            <img src="<?= encode($product['photo']); ?>" alt="<?= encode($product['name']); ?>" class="thumb">
        <?php endif; ?>

        <button class="btn primary"><?= isset($product) ? 'Update' : 'Create'; ?></button>
    </form>
</section>

