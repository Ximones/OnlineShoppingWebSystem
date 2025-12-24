<?php $title = isset($category) ? 'Edit Category' : 'New Category'; ?>
<section class="panel">
    <h2><?= $title; ?></h2>
    <form method="post" enctype="multipart/form-data">
        
        <!-- Basic Information Section -->
        <div class="form-section">
            <h3 class="form-section-title">Basic Information</h3>
            
            <label for="name">Category Name *</label>
            <input type="text" name="name" id="name" value="<?= encode($category['name'] ?? ''); ?>" required>
            <?php err('name'); ?>

            <label for="description">Description</label>
            <textarea name="description" id="description" rows="4"><?= encode($category['description'] ?? ''); ?></textarea>
            <?php err('description'); ?>
        </div>

        <!-- Image Section -->
        <div class="form-section">
            <h3 class="form-section-title">Category Image</h3>
            
            <?php if (!empty($category['image_url'])): ?>
                <div style="margin-bottom: 20px;">
                    <label>Current Image</label>
                    <div style="width: 100%; max-width: 400px; height: 300px; margin-top: 10px; border-radius: 8px; overflow: hidden; background: #f5f5f5; display: flex; align-items: center; justify-content: center;">
                        <img src="<?= encode($category['image_url']); ?>" alt="Category image" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                    </div>
                    <small style="color: #666; display: block; margin-top: 10px;">Upload a new image to replace the current one.</small>
                </div>
            <?php endif; ?>
            
            <label for="image"><?= isset($category) ? 'Upload New Image' : 'Upload Image'; ?></label>
            <input type="file" id="imageInput" name="image" accept="image/*">
            <div class="photo-note">
                <small>Recommended: Square image (e.g., 400x400px) for best display.</small>
            </div>

            <div id="imagePreview" style="margin-top: 15px; display: none;">
                <label>Image Preview</label>
                <div style="width: 100%; max-width: 400px; height: 300px; margin-top: 10px; border-radius: 8px; overflow: hidden; background: #f5f5f5; display: flex; align-items: center; justify-content: center;">
                    <img id="previewImg" src="" alt="Preview" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 15px; margin-top: 30px;">
            <button type="submit" class="btn primary"><?= isset($category) ? 'Update Category' : 'Create Category'; ?></button>
            <a href="?module=admin&resource=categories&action=index" class="btn secondary">Cancel</a>
        </div>
    </form>
</section>

<script>
document.getElementById('imageInput')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        preview.style.display = 'none';
    }
});
</script>

