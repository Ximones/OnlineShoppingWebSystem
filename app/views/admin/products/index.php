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

<section class="panel" style="padding: 10px; text-align: right;">
    <div class="view-toggle" style="display: flex; gap: 8px; justify-content: flex-end;">
        <button id="tableViewBtn" class="btn small" title="Table View" onclick="switchView('table')" style="background-color: #007bff; color: white;">
            ≡ Table
        </button>
        <button id="photoViewBtn" class="btn small" title="Photo View" onclick="switchView('photo')" style="background-color: #f0f0f0; color: #333;">
            ⊞ Photo
        </button>
    </div>
</section>

<!-- Table View -->
<section id="tableViewSection" class="panel">
    <form method="post" action="?module=admin&resource=products&action=batchDelete" id="batch-delete-form-products">
        <div style="margin-bottom: 15px;">
            <button type="button" class="btn danger" id="batch-delete-btn-products" style="display: none;" onclick="confirmBatchDelete('products')">Delete Selected</button>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 40px;">
                        <input type="checkbox" id="select-all-products" onchange="toggleAllProducts(this)">
                    </th>
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
                        <td>
                            <input type="checkbox" name="ids[]" value="<?= $product['id']; ?>" class="product-checkbox" onchange="updateBatchDeleteBtn('products')">
                        </td>
                        <td><?= encode($product['sku']); ?></td>
                        <td><?= encode($product['name']); ?></td>
                        <td><?= encode($product['category_name']); ?></td>
                        <td>RM <?= number_format($product['price'], 2); ?></td>
                        <td><?= encode($product['stock']); ?></td>
                        <td><span class="badge <?= $product['status']; ?>"><?= encode(ucfirst($product['status'])); ?></span></td>
                        <td>
                            <a class="btn small" href="?module=admin&resource=products&action=edit&id=<?= $product['id']; ?>">Edit</a>
                            <form method="post" action="?module=admin&resource=products&action=delete" onsubmit="return confirm('Delete product?');" style="display: inline;">
                                <input type="hidden" name="id" value="<?= $product['id']; ?>">
                                <button class="btn danger small">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </form>
</section>

<!-- Photo View -->
<section id="photoViewSection" class="panel" style="display: none;">
    <form method="post" action="?module=admin&resource=products&action=batchDelete" id="batch-delete-form-products-photo">
        <div style="margin-bottom: 15px;">
            <button type="button" class="btn danger" id="batch-delete-btn-products-photo" style="display: none;" onclick="confirmBatchDelete('products-photo')">Delete Selected</button>
        </div>
        <div class="photo-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">
            <?php foreach ($products as $product): ?>
                <div class="photo-card" style="border: 1px solid #ddd; border-radius: 8px; overflow: hidden; background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: transform 0.2s; display: flex; flex-direction: column; height: 100%; position: relative;">
                    <div style="position: absolute; top: 10px; left: 10px; z-index: 10;">
                        <input type="checkbox" name="ids[]" value="<?= $product['id']; ?>" class="product-checkbox-photo" onchange="updateBatchDeleteBtn('products-photo')">
                    </div>
                    <div class="photo-container" style="width: 100%; height: 200px; background-color: #f5f5f5; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0;">
                        <?php
                        $primaryPhoto = null;
                        // Note: In your actual view, you'll need to fetch photos per product
                        // For now, assuming photos are preloaded
                        if (isset($product['primary_photo_path'])):
                        ?>
                            <img src="<?= encode($product['primary_photo_path']); ?>" alt="<?= encode($product['name']); ?>" style="width: 100%; height: 100%; object-fit: contain; padding: 8px;">
                        <?php else: ?>
                            <div style="color: #999; font-size: 14px;">No Image</div>
                        <?php endif; ?>
                    </div>
                    <div class="photo-info" style="padding: 15px;">
                        <h3 style="margin: 0 0 8px 0; font-size: 16px; color: #333;">
                            <?= encode($product['name']); ?>
                        </h3>
                        <p style="margin: 0 0 15px 0; font-size: 18px; font-weight: bold; color: #007bff;">
                            RM <?= number_format($product['price'], 2); ?>
                        </p>
                        <div class="photo-actions" style="display: flex; gap: 8px;">
                            <a class="btn small" href="?module=admin&resource=products&action=edit&id=<?= $product['id']; ?>" style="flex: 1; text-align: center;">Edit</a>
                            <form method="post" action="?module=admin&resource=products&action=delete" onsubmit="return confirm('Delete product?');" style="flex: 1;">
                                <input type="hidden" name="id" value="<?= $product['id']; ?>">
                                <button class="btn danger small" style="width: 100%;">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </form>
</section>

<script>
    function switchView(view) {
        const tableView = document.getElementById('tableViewSection');
        const photoView = document.getElementById('photoViewSection');
        const tableBtn = document.getElementById('tableViewBtn');
        const photoBtn = document.getElementById('photoViewBtn');

        if (view === 'table') {
            tableView.style.display = 'block';
            photoView.style.display = 'none';
            tableBtn.style.backgroundColor = '#007bff';
            tableBtn.style.color = 'white';
            photoBtn.style.backgroundColor = '#f0f0f0';
            photoBtn.style.color = '#333';
            localStorage.setItem('productViewMode', 'table');
        } else if (view === 'photo') {
            tableView.style.display = 'none';
            photoView.style.display = 'block';
            tableBtn.style.backgroundColor = '#f0f0f0';
            tableBtn.style.color = '#333';
            photoBtn.style.backgroundColor = '#007bff';
            photoBtn.style.color = 'white';
            localStorage.setItem('productViewMode', 'photo');
        }
    }

    // Initialize view based on saved preference
    document.addEventListener('DOMContentLoaded', function() {
        const savedView = localStorage.getItem('productViewMode') || 'table';
        switchView(savedView);
    });

    // Batch delete functions
    function toggleAllProducts(checkbox) {
        const checkboxes = document.querySelectorAll('#tableViewSection .product-checkbox');
        checkboxes.forEach(cb => cb.checked = checkbox.checked);
        updateBatchDeleteBtn('products');
    }

    function updateBatchDeleteBtn(viewType) {
        const suffix = viewType === 'products-photo' ? '-photo' : '';
        const checkboxes = document.querySelectorAll(`#${viewType === 'products-photo' ? 'photoViewSection' : 'tableViewSection'} .product-checkbox${suffix}`);
        const checked = document.querySelectorAll(`#${viewType === 'products-photo' ? 'photoViewSection' : 'tableViewSection'} .product-checkbox${suffix}:checked`);
        const btn = document.getElementById(`batch-delete-btn-${viewType}`);
        if (btn) {
            btn.style.display = checked.length > 0 ? 'inline-block' : 'none';
        }
    }

    function confirmBatchDelete(viewType) {
        const suffix = viewType === 'products-photo' ? '-photo' : '';
        const checked = document.querySelectorAll(`#${viewType === 'products-photo' ? 'photoViewSection' : 'tableViewSection'} .product-checkbox${suffix}:checked`);
        if (checked.length === 0) {
            alert('Please select products to delete.');
            return;
        }
        if (confirm('Are you sure you want to delete ' + checked.length + ' selected product(s)?')) {
            document.getElementById(`batch-delete-form-${viewType}`).submit();
        }
    }
</script>