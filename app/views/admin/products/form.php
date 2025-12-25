<?php $title = isset($product) ? 'Edit Product' : 'New Product'; ?>

<?php
// Display stock alert if stock is 10 or below
$showStockAlert = isset($product) && !empty($product) && $product['stock'] <= 10;
?>

<?php if ($showStockAlert): ?>
    <section class="panel" style="background-color: <?= $product['stock'] == 0 ? '#f8d7da' : '#fff3cd'; ?>; border-left: 4px solid <?= $product['stock'] == 0 ? '#dc3545' : '#ffc107'; ?>; padding: 15px; margin-bottom: 20px;">
        <div style="display: flex; align-items: center; gap: 15px;">
            <div style="font-size: 24px;">
                <?= $product['stock'] == 0 ? '🚫' : '⚠️'; ?>
            </div>
            <div>
                <strong style="color: <?= $product['stock'] == 0 ? '#721c24' : '#856404'; ?>; font-size: 16px;">
                    <?= $product['stock'] == 0 ? 'Out of Stock' : 'Low Stock Alert'; ?>
                </strong>
                <p style="margin: 5px 0 0 0; color: <?= $product['stock'] == 0 ? '#721c24' : '#856404'; ?>; font-size: 14px;">
                    <?php if ($product['stock'] == 0): ?>
                        This product is currently out of stock, 0 units remaining. Please restock immediately.
                    <?php else: ?>
                        Current stock is <strong><?= $product['stock']; ?> units</strong>. Please consider restocking soon.
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </section>
<?php endif; ?>

<section class="panel">
    <h2><?= $title; ?></h2>
    <form method="post" enctype="multipart/form-data">

        <!-- Basic Information Section -->
        <div class="form-section">
            <h3 class="form-section-title">Basic Information</h3>

            <label for="name">Product Name</label>
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
            <textarea name="description" rows="4"><?= encode($product['description'] ?? ''); ?></textarea>
            <?php err('description'); ?>

            <label for="price">Price (RM)</label>
            <input type="number" step="0.01" name="price" value="<?= encode($product['price'] ?? ''); ?>" required>
            <?php err('price'); ?>

            <label for="stock">Stock <span style="color: #dc3545;">*</span></label>
            <input type="number" name="stock" id="stockInput" value="<?= encode($product['stock'] ?? ''); ?>" required onchange="updateStockWarning()">
            <small style="color: #666; margin-top: 5px; display: block;">
                <span id="alertNote"></span>
            </small>
            <?php err('stock'); ?>

            <label for="status">Status</label>
            <select name="status" id="statusSelect">
                <option value="active" <?= ($product['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="inactive" <?= ($product['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
            </select>
        </div>

        <!-- Technical Specifications Section -->
        <div class="form-section">
            <h3 class="form-section-title">Technical Specifications</h3>

            <div class="form-grid">
                <div class="form-group">
                    <label for="color">Color</label>
                    <input type="text" name="color" value="<?= encode($product['color'] ?? ''); ?>" placeholder="e.g., Pure White">
                    <?php err('color'); ?>
                </div>

                <div class="form-group">
                    <label for="size">Dimensions (L × W × H)</label>
                    <input type="text" name="size" value="<?= encode($product['size'] ?? ''); ?>" placeholder="e.g., 660mm × 380mm × 780mm">
                    <?php err('size'); ?>
                </div>

                <div class="form-group">
                    <label for="pit_spacing">Rough-In / Pit Spacing</label>
                    <input type="text" name="pit_spacing" value="<?= encode($product['pit_spacing'] ?? ''); ?>" placeholder="e.g., 305mm">
                    <?php err('pit_spacing'); ?>
                </div>

                <div class="form-group">
                    <label for="installation_type">Installation Type</label>
                    <select name="installation_type">
                        <option value="">Select Type</option>
                        <option value="Floor-Mounted" <?= ($product['installation_type'] ?? '') === 'Floor-Mounted' ? 'selected' : ''; ?>>Floor-Mounted</option>
                        <option value="Wall-Mounted" <?= ($product['installation_type'] ?? '') === 'Wall-Mounted' ? 'selected' : ''; ?>>Wall-Mounted</option>
                    </select>
                    <?php err('installation_type'); ?>
                </div>

                <div class="form-group">
                    <label for="flushing_method">Flushing System</label>
                    <input type="text" name="flushing_method" value="<?= encode($product['flushing_method'] ?? ''); ?>" placeholder="e.g., Dual Flush (3L/6L)">
                    <?php err('flushing_method'); ?>
                </div>

                <div class="form-group">
                    <label for="bowl_shape">Bowl Shape</label>
                    <select name="bowl_shape">
                        <option value="">Select Shape</option>
                        <option value="Round" <?= ($product['bowl_shape'] ?? '') === 'Round' ? 'selected' : ''; ?>>Round</option>
                        <option value="Elongated" <?= ($product['bowl_shape'] ?? '') === 'Elongated' ? 'selected' : ''; ?>>Elongated</option>
                    </select>
                    <?php err('bowl_shape'); ?>
                </div>

                <div class="form-group">
                    <label for="material">Material</label>
                    <input type="text" name="material" value="<?= encode($product['material'] ?? ''); ?>" placeholder="e.g., High-Grade Vitreous China">
                    <?php err('material'); ?>
                </div>
            </div>
        </div>

        <!-- Photos Section -->
        <div class="form-section">
            <h3 class="form-section-title">Product Photos</h3>

            <label for="photos">Upload Photos</label>
            <input type="file" id="photoInput" name="photos[]" accept="image/*" multiple>
            <div class="photo-note">
                <small>You can select multiple photos at once.</small>
            </div>

            <?php if (!empty($photos)): ?>
                <div class="photos-grid">
                    <h4>Current Photos (Click "Set Primary" to change main photo)</h4>
                    <?php foreach ($photos as $photo): ?>
                        <div class="photo-item <?= $photo['is_primary'] ? 'primary' : ''; ?>">
                            <div class="photo-wrapper">
                                <img src="<?= encode($photo['photo_path']); ?>" alt="Product photo" class="thumb">
                                <?php if ($photo['is_primary']): ?>
                                    <span class="badge primary-badge">Primary</span>
                                <?php endif; ?>
                            </div>
                            <div class="photo-actions">
                                <button type="button" class="setPrimaryBtn btn small btn-primary-action" onclick="setPrimaryPhoto(<?= $photo['id']; ?>, <?= $product['id']; ?>)">
                                    Set Primary
                                </button>
                                <button type="button" class="photosDanger btn-small" onclick="deletePhoto(<?= $photo['id']; ?>, <?= $product['id']; ?>)">
                                    Delete
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn primary submit-product-btn"><?= isset($product) ? 'Update Product' : 'Create Product'; ?></button>
    </form>
</section>

<form id="deletePhotoForm" method="post" action="?module=admin&resource=products&action=deletePhoto" style="display:none;">
    <input type="hidden" name="photo_id" id="deletePhotoId">
    <input type="hidden" name="product_id" id="deleteProductId">
</form>

<form id="setPrimaryPhotoForm" method="post" action="?module=admin&resource=products&action=setPrimaryPhoto" style="display:none;">
    <input type="hidden" name="photo_id" id="setPrimaryPhotoId">
    <input type="hidden" name="product_id" id="setPrimaryProductId">
</form>

<script>
    function deletePhoto(photoId, productId) {
        if (confirm('Are you sure you want to delete this photo?')) {
            document.getElementById('deletePhotoId').value = photoId;
            document.getElementById('deleteProductId').value = productId;
            document.getElementById('deletePhotoForm').submit();
        }
    }

    function setPrimaryPhoto(photoId, productId) {
        document.getElementById('setPrimaryPhotoId').value = photoId;
        document.getElementById('setPrimaryProductId').value = productId;
        document.getElementById('setPrimaryPhotoForm').submit();
    }

    function updateStockWarning() {
        const stockInput = document.getElementById('stockInput');
        const alertNote = document.getElementById('alertNote');
        const stock = parseInt(stockInput.value) || 0;

        if (stock === 0) {
            alertNote.textContent = '⚠️ Out of stock.';
            alertNote.style.color = '#dc3545';
            alertNote.style.fontWeight = 'bold';
        } else if (stock <= 10) {
            alertNote.textContent = '⚠️ Low stock warning (10 units or below)';
            alertNote.style.color = '#856404';
            alertNote.style.fontWeight = 'normal';
        } else {
            alertNote.textContent = '';
        }
    }

    document.getElementById('photoInput')?.addEventListener('change', function(e) {
        const files = e.target.files;
        if (files.length > 0) {
            let fileList = 'Selected ' + files.length + ' file(s):\n';
            for (let i = 0; i < files.length; i++) {
                fileList += (i + 1) + '. ' + files[i].name + '\n';
            }
            console.log(fileList);
        }
    });

    // Initialize status display on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateStockWarning();
    });
</script>