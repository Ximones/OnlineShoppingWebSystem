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

    // Home products row: scroll with nav buttons instead of scrollbar
    const initHomeRow = function (rowSelector, leftSelector, rightSelector) {
        const $row = $(rowSelector);
        if (!$row.length) return;
        const rowEl = $row.get(0);

        const scrollByCard = function (direction) {
            const cardWidth = $row.find('.home-product-card').first().outerWidth(true) || 320;
            rowEl.scrollBy({
                left: direction * cardWidth,
                behavior: 'smooth',
            });
        };

        $(leftSelector).on('click', function () {
            scrollByCard(-1);
        });

        $(rightSelector).on('click', function () {
            scrollByCard(1);
        });
    };

    initHomeRow('#home-products-row-toilets', '.home-products:nth-of-type(1) .home-products-nav-left', '.home-products:nth-of-type(1) .home-products-nav-right');
    initHomeRow('#home-products-row-accessories', '.home-products:nth-of-type(2) .home-products-nav-left', '.home-products:nth-of-type(2) .home-products-nav-right');
});

