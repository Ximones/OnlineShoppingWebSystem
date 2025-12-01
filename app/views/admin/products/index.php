<?php $title = 'Product Maintenance'; ?>
<section class="panel">
    <form method="get" class="form-inline">
        <input type="hidden" name="module" value="admin">
        <input type="hidden" name="resource" value="products">
        <input type="hidden" name="action" value="index">
        <input type="text" name="keyword" value="<?= encode($filters['keyword']); ?>" placeholder="Search products">
        <select name="category_id">
            <option value="">All Categories</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= $category['id']; ?>" <?= ($filters['category_id'] ?? '') == $category['id'] ? 'selected' : ''; ?>>
                    <?= encode($category['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select name="status">
            <option value="">Any Status</option>
            <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
            <option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
        </select>
        <button class="btn primary">Filter</button>
        <a class="btn secondary" href="?module=admin&resource=products&action=create">+ New Product</a>
    </form>
</section>

<section class="panel">
    <table class="table">
        <thead>
        <tr>
            <th>SKU</th>
            <th>Name</th>
            <th>Category</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Status</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($products as $product): ?>
            <tr>
                <td><?= encode($product['sku']); ?></td>
                <td><?= encode($product['name']); ?></td>
                <td><?= encode($product['category_name']); ?></td>
                <td>RM <?= number_format($product['price'], 2); ?></td>
                <td><?= encode($product['stock']); ?></td>
                <td><span class="badge <?= $product['status']; ?>"><?= encode(ucfirst($product['status'])); ?></span></td>
                <td>
                    <a class="btn small" href="?module=admin&resource=products&action=edit&id=<?= $product['id']; ?>">Edit</a>
                    <form method="post" action="?module=admin&resource=products&action=delete" onsubmit="return confirm('Delete product?');">
                        <input type="hidden" name="id" value="<?= $product['id']; ?>">
                        <button class="btn danger small">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>

