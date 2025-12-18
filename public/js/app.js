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

    // --- FAVORITES TOGGLE LOGIC ---
    const initFavoriteToggle = function() {
    // Select all favorite toggle elements on the page
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

});

