$(function () {
    // Mobile menu toggle
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const nav = document.querySelector('.nav');

    if (mobileMenuToggle && nav) {
        mobileMenuToggle.addEventListener('click', function() {
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', !isExpanded);
            nav.classList.toggle('mobile-open');
            
            // Prevent body scroll when menu is open
            if (!isExpanded) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        });

        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (mobileMenuToggle && nav && 
                !nav.contains(e.target) && 
                !mobileMenuToggle.contains(e.target)) {
                mobileMenuToggle.setAttribute('aria-expanded', 'false');
                nav.classList.remove('mobile-open');
                document.body.style.overflow = '';
            }
        });

        // Close menu when clicking on a nav link
        nav.addEventListener('click', function(e) {
            if (e.target.tagName === 'A') {
                mobileMenuToggle.setAttribute('aria-expanded', 'false');
                nav.classList.remove('mobile-open');
                document.body.style.overflow = '';
            }
        });

            // Close menu on window resize (if resizing to desktop)
        window.addEventListener('resize', function() {
            if (window.innerWidth > 968) {
                mobileMenuToggle.setAttribute('aria-expanded', 'false');
                nav.classList.remove('mobile-open');
                document.body.style.overflow = '';
            }
        });
    }

    // Mobile table optimization: Add data-label attributes to table cells
    function optimizeTablesForMobile() {
        const isMobile = window.innerWidth <= 640;
        
        document.querySelectorAll('.table').forEach(function(table) {
            if (isMobile) {
                // Remove mobile-scroll class if it exists
                table.classList.remove('mobile-scroll');
                
                const headers = Array.from(table.querySelectorAll('thead th'));
                if (headers.length === 0) return;
                
                const headerTexts = headers.map(function(th) {
                    return th.textContent.trim().replace(/[^\w\s]/gi, '');
                });

                table.querySelectorAll('tbody tr').forEach(function(row) {
                    const cells = Array.from(row.querySelectorAll('td'));
                    cells.forEach(function(cell, index) {
                        // Check if cell contains only a checkbox
                        const hasOnlyCheckbox = cell.querySelector('input[type="checkbox"]') && 
                                                cell.children.length === 1 && 
                                                cell.textContent.trim() === '';
                        
                        if (hasOnlyCheckbox) {
                            // Mark checkbox cells to hide labels
                            cell.classList.add('checkbox-cell');
                            // Don't add data-label for checkbox-only cells
                        } else if (headerTexts[index] && !cell.hasAttribute('data-label')) {
                            cell.setAttribute('data-label', headerTexts[index]);
                        }
                    });
                });
            } else {
                // On desktop, remove data-label attributes and checkbox-cell class if needed
                table.querySelectorAll('td[data-label]').forEach(function(cell) {
                    cell.removeAttribute('data-label');
                });
                table.querySelectorAll('td.checkbox-cell').forEach(function(cell) {
                    cell.classList.remove('checkbox-cell');
                });
            }
        });
    }

    // Run on load and resize with debounce
    let resizeTimeout;
    function handleResize() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(optimizeTablesForMobile, 150);
    }

    optimizeTablesForMobile();
    window.addEventListener('resize', handleResize);
    
    // Also optimize tables after AJAX content loads
    $(document).ajaxComplete(function() {
        optimizeTablesForMobile();
    });

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

    // Collapsible tracking history on mobile
    const trackingToggle = document.querySelector('.tracking-toggle-btn');
    const trackingSection = document.querySelector('.tracking-history-section');
    
    if (trackingToggle && trackingSection) {
        trackingToggle.addEventListener('click', function() {
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', !isExpanded);
            trackingSection.classList.toggle('collapsed');
        });
    }

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

