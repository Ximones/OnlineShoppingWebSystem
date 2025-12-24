$(function () {
    $('.dropdown-toggle').on('click', function (e) {
        e.preventDefault();
        $(this).next('.dropdown-menu').toggleClass('show');
    });

    $(document).on('click', function (e) {
        if (!$(e.target).closest('.dropdown').length) {
            $('.dropdown-menu').removeClass('show');
        }
    });

    // Toast alerts: auto-show and auto-dismiss flash messages
    const $alerts = $('#toast-container .alert');
    $alerts.each(function (index) {
        const $alert = $(this);

        // Staggered show for multiple toasts
        setTimeout(function () {
            $alert.addClass('alert-show');
        }, 100 * index);

        const hide = function () {
            $alert.removeClass('alert-show');
            setTimeout(function () {
                $alert.remove();
            }, 200);
        };

        // Auto-dismiss after 4 seconds
        setTimeout(hide, 4000 + index * 300);

        // Allow click to dismiss immediately
        $alert.on('click', hide);
    });

    // Generic horizontal scroll shells (home products + store top bar)
    const initScrollShell = function (shellSelector, rowSelector, cardSelector, stepFallback) {
        $(shellSelector).each(function () {
            const $shell = $(this);
            const $row = $shell.find(rowSelector);
            if (!$row.length) return;
            const rowEl = $row.get(0);

            const scrollByCard = function (direction) {
                const $card = $row.find(cardSelector).first();
                const cardWidth = $card.length ? $card.outerWidth(true) : stepFallback;
                rowEl.scrollBy({
                    left: direction * cardWidth,
                    behavior: 'smooth',
                });
            };

            $shell.find('.home-products-nav-left').on('click', function () {
                scrollByCard(-1);
            });

            $shell.find('.home-products-nav-right').on('click', function () {
                scrollByCard(1);
            });
        });
    };

    initScrollShell('.home-products-shell', '.home-products-row', '.home-product-card', 320);
    initScrollShell('.store-top-shell', '.store-top-row', '.store-top-item', 200);

    // PayLater tabs (upcoming vs history)
    $(document).on('click', '.paylater-tab-btn', function () {
        const targetId = $(this).data('target');
        const $panel = $(this).closest('.paylater-tabs-panel');

        $panel.find('.paylater-tab-btn').removeClass('is-active');
        $(this).addClass('is-active');

        $panel.find('.paylater-tab-content').removeClass('is-active');
        $panel.find('#' + targetId).addClass('is-active');
    });

    // favorites toggle 
    const initFavoriteToggle = function() {
   
    const toggleButtons = document.querySelectorAll('.favorite-toggle');

    toggleButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault();
            
            const productId = this.dataset.productId;
            const iconElement = this.querySelector('i.fa-heart');

            fetch('?module=favorites&action=toggle', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ product_id: productId })
            })
            .then(response => {

                if (response.status === 401) {
                    
                    window.location.href = '?module=auth&action=login'; 
                    return Promise.reject('Login required');
                }
                

                if (!response.ok) {
                    return response.json().then(error => Promise.reject(error.message));
                }
                
                return response.json();
            })
            .then(data => {
                if (data.success) {
          
                    const newIsFavorited = data.action === 'added';
                    
                    this.dataset.isFavorited = newIsFavorited ? 'true' : 'false';

                    iconElement.classList.toggle('fas', newIsFavorited);
                    iconElement.classList.toggle('far', !newIsFavorited);
                    
                    iconElement.classList.toggle('red-filled-heart', newIsFavorited);
                    iconElement.classList.toggle('black-outline-heart', !newIsFavorited);
                }
            })
            .catch(error => {
                console.error('Favorite toggle error:', error);
          
            });
        });
    });
    };
    initFavoriteToggle();


    //  product photos slider with fade
    let currentPhotoIndex = 0;
    const $thumbnails = $('.product-photo-view__thumb');
    const $mainPhoto = $('#productMainPhoto');

    window.changeMainPhoto = function(thumbElement, index) {
    currentPhotoIndex = index;
    const newSrc = $(thumbElement).attr('src');
    const $mainPhoto = $('#productMainPhoto'); 

    $mainPhoto.addClass('photo-fade-out');

    setTimeout(function() {
     
        $mainPhoto.attr('src', newSrc);  
        $thumbnails.removeClass('is-active');
        $(thumbElement).addClass('is-active');

        setTimeout(function() {
            $mainPhoto.removeClass('photo-fade-out');
        }, 50);
    }, 350); 
 };
    window.moveSlider = function(step) {
        if ($thumbnails.length <= 1) return;
        currentPhotoIndex += step;

        if (currentPhotoIndex >= $thumbnails.length) {
            currentPhotoIndex = 0;
        } else if (currentPhotoIndex < 0) {
            currentPhotoIndex = $thumbnails.length - 1;
        }
        const targetThumb = $thumbnails.get(currentPhotoIndex);
        window.changeMainPhoto(targetThumb, currentPhotoIndex);
    };

    
    // live search filtering, category selection, pagination without page reload
$(function () {
    const $productGrid = $('#product-grid');
    const $paginationTarget = $('#pagination-ajax-target'); 
    let debounceTimer;

    const updateProducts = (page = 1) => {
    const $filterForm = $('#filter-form');
    const $productGrid = $('#product-grid');
    const $paginationTarget = $('#pagination-ajax-target');


    const urlParams = new URLSearchParams(window.location.search);
    const module = urlParams.get('module') || 'shop';
    const action = urlParams.get('action') || 'catalog';

    let formData = $filterForm.length ? $filterForm.serializeArray() : [];
    
 
    formData.push({ name: 'module', value: module });
    formData.push({ name: 'action', value: action });
    formData.push({ name: 'ajax', value: '1' });
    formData.push({ name: 'page', value: page });
    
    const queryString = $.param(formData);

    $productGrid.css('opacity', '0.5');

    fetch(`?${queryString}`)
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newGrid = doc.querySelector('#product-grid');
            const newPagination = doc.querySelector('#pagination-ajax-target');

            if (newGrid && $productGrid.length) {
                $productGrid.html(newGrid.innerHTML);
            }

            if (newPagination && $paginationTarget.length) {
                $paginationTarget.html(newPagination.innerHTML);
            } else {
                console.warn("Pagination target not found in server response. Check PHP controller.");
            }

            $productGrid.css('opacity', '1');
            
            if (typeof initFavoriteToggle === "function") {
                initFavoriteToggle();
            }

            const cleanQuery = queryString.replace('&ajax=1', '');
            window.history.pushState({}, '', `?${cleanQuery}`);

            window.scrollTo({ top: $productGrid.offset().top - 100, behavior: 'smooth' });
        })
        .catch(error => {
            console.error('Error fetching results:', error);
            $productGrid.css('opacity', '1');
        });
};

    // Auto search listener
    $('#keyword-search, #category-filter, #sort-filter, .price-input').on('input change', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => updateProducts(1), 500); 
    });

    // Paging click listener
    $(document).on('click', '.page-link', function (e) {
        e.preventDefault();
        const page = $(this).data('page');
        if (page && !$(this).hasClass('disabled') && !$(this).hasClass('is-active')) {
            updateProducts(page);
        }
    });
}); 
});

