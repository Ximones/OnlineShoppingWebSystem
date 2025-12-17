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

        <label for="photos">Photos</label>
        <input type="file" id="photoInput" name="photos[]" accept="image/*" multiple>
        <div class="photo-note">
            <small>You can select multiple photos at once.</small>
        </div>

        <?php if (!empty($photos)): ?>
            <div class="photos-grid">
                <h4>Current Photos: (Click to set as primary)</h4>
                <?php foreach ($photos as $photo): ?>
                    <div class="photo-item <?= $photo['is_primary'] ? 'primary' : ''; ?>">
                        <div class="photo-wrapper">
                            <img src="<?= encode($photo['photo_path']); ?>" alt="Product photo" class="thumb">
                            <?php if ($photo['is_primary']): ?>
                                <span class="badge primary-badge">Primary</span>
                            <?php endif; ?>
                        </div>
                        <div class="photo-actions">
                            <button type="button" class="btn small btn-primary-action" onclick="setPrimaryPhoto(<?= $photo['id']; ?>, <?= $product['id']; ?>)">
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

        <button type="submit" class="btn primary mt-3"><?= isset($product) ? 'Update' : 'Create'; ?></button>
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
        if (confirm('Delete this photo?')) {
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
</script>