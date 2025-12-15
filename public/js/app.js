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

    // Home products rows: each section's arrows control its own row
    $('.home-products-shell').each(function () {
        const $shell = $(this);
        const $row = $shell.find('.home-products-row');
        if (!$row.length) return;
        const rowEl = $row.get(0);

        const scrollByCard = function (direction) {
            const cardWidth = $row.find('.home-product-card').first().outerWidth(true) || 320;
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
});

