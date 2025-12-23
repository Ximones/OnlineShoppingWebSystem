<?php 
$productPhotoModel = new \App\Models\ProductPhoto(); 
?>

<div id="product-grid">
    <?php foreach ($products as $product): ?>
        <?php if (($product['status'] ?? '') !== 'active') continue; ?>
        <?php
        $primaryPhoto = $productPhotoModel->getPrimaryPhoto($product['id']);
        $photoSrc = $primaryPhoto['photo_path'] ?? 'https://placehold.co/400x250';
        ?>
        <article class="card product-card">
            <img src="<?= encode($photoSrc); ?>" alt="<?= encode($product['name']); ?>">
            <h3><?= encode($product['name']); ?></h3>
            <p><?= encode(substr($product['description'], 0, 100)); ?>...</p>
            <p class="price">RM <?= number_format($product['price'], 2); ?></p>

            <?php 
                $isFavorited = $product['is_favorited'] ?? false;
                $iconType = $isFavorited ? 'fas' : 'far';
                $colorClass = $isFavorited ? 'red-filled-heart' : 'black-outline-heart';
            ?>
            <span class="favorite-toggle" data-product-id="<?= $product['id']; ?>" data-is-favorited="<?= $isFavorited ? 'true' : 'false' ?>">
                <i class="<?= $iconType ?> fa-heart <?= $colorClass ?>"></i>
            </span>
            <a class="btn secondary" href="?module=shop&action=detail&id=<?= $product['id']; ?>">Details</a>
        </article>
    <?php endforeach; ?>
</div>

<div id="pagination-ajax-target">
    <?php if ($totalPages > 1): ?>
        <div class="pagination-container"> 
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="#" class="page-link" data-page="<?= $page - 1; ?>">&laquo; Prev</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="#" class="page-link <?= $i == $page ? 'is-active' : ''; ?>" data-page="<?= $i; ?>">
                        <?= $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="#" class="page-link" data-page="<?= $page + 1; ?>">Next &raquo;</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('click', function(e) {
    if(e.target.closest('.page-link')) {
        e.preventDefault();
        const page = e.target.closest('.page-link').dataset.page;
        
        fetch(`?module=shop&action=ajaxCatalog&page=${page}`)
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
        
                const newGrid = doc.querySelector('#product-grid').innerHTML;
                document.querySelector('#product-grid').innerHTML = newGrid;
             
                const newPagination = doc.querySelector('#pagination-ajax-target').innerHTML;
                document.querySelector('#pagination-ajax-target').innerHTML = newPagination;
            });
    }
});
</script>
