<?php $title = 'Category Management'; ?>
<section class="panel">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="margin: 0;">Categories</h2>
        <a class="btn primary" href="?module=admin&resource=categories&action=create">+ New Category</a>
    </div>
</section>

<section class="panel">
    <?php if (empty($categories)): ?>
        <p style="color: #999; text-align: center; padding: 40px;">No categories yet. <a href="?module=admin&resource=categories&action=create">Create one</a>.</p>
    <?php else: ?>
        <form method="post" action="?module=admin&resource=categories&action=batchDelete" id="batch-delete-form-categories">
            <div style="margin-bottom: 15px;">
                <button type="button" class="btn danger" id="batch-delete-btn-categories" style="display: none;" onclick="confirmBatchDeleteCategories()">Delete Selected</button>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
                <?php foreach ($categories as $category): ?>
                    <div class="panel" style="padding: 20px; display: flex; flex-direction: column; position: relative;">
                        <div style="position: absolute; top: 15px; left: 15px; z-index: 10;">
                            <input type="checkbox" name="ids[]" value="<?= $category['id']; ?>" class="category-checkbox" onchange="updateBatchDeleteBtnCategories()">
                        </div>
                        <?php if (!empty($category['image_url'])): ?>
                            <div style="width: 100%; height: 200px; margin-bottom: 15px; overflow: hidden; border-radius: 8px; background: #f5f5f5; display: flex; align-items: center; justify-content: center;">
                                <img src="<?= encode($category['image_url']); ?>" alt="<?= encode($category['name']); ?>" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                            </div>
                        <?php else: ?>
                            <div style="width: 100%; height: 200px; margin-bottom: 15px; border-radius: 8px; background: #f5f5f5; display: flex; align-items: center; justify-content: center; color: #999;">
                                No Image
                            </div>
                        <?php endif; ?>
                        
                        <h3 style="margin: 0 0 10px 0; font-size: 20px;"><?= encode($category['name']); ?></h3>
                        
                        <?php if (!empty($category['description'])): ?>
                            <p style="color: #666; margin: 0 0 15px 0; flex-grow: 1;"><?= encode($category['description']); ?></p>
                        <?php endif; ?>
                        
                        <div style="display: flex; gap: 10px; margin-top: auto;">
                            <a class="btn small" href="?module=admin&resource=categories&action=edit&id=<?= $category['id']; ?>">Edit</a>
                            <form method="post" action="?module=admin&resource=categories&action=delete" onsubmit="return confirm('Delete this category? This cannot be undone if there are products in this category.');" style="display: inline;">
                                <input type="hidden" name="id" value="<?= $category['id']; ?>">
                                <button class="btn danger small" type="submit">Delete</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </form>
    <?php endif; ?>
</section>

<script>
function updateBatchDeleteBtnCategories() {
    const checked = document.querySelectorAll('.category-checkbox:checked');
    const btn = document.getElementById('batch-delete-btn-categories');
    if (btn) {
        btn.style.display = checked.length > 0 ? 'inline-block' : 'none';
    }
}

function confirmBatchDeleteCategories() {
    const checked = document.querySelectorAll('.category-checkbox:checked');
    if (checked.length === 0) {
        alert('Please select categories to delete.');
        return;
    }
    if (confirm('Are you sure you want to delete ' + checked.length + ' selected category(ies)? Categories with products cannot be deleted.')) {
        document.getElementById('batch-delete-form-categories').submit();
    }
}
</script>

