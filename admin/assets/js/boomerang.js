jQuery(document).ready(function ($) {

    $("body").on(
        "click",
        "#boomerang_board_options .csf-nav-metabox ul li a",
        function (e) {
            sessionStorage.setItem("tab", e.target.id);
        });

    if (sessionStorage.getItem("tab")) {
        tab = sessionStorage.getItem("tab");
        if ( tab ) {
            $('#boomerang_board_options .csf-nav-metabox ul li').find('a#' + tab).trigger('click');
        }
    }

    $('.boomerang-color-picker').wpColorPicker({
        change: function(event, ui){
            var rgb = ui.color.toCSS( 'rgb' ).replace(')', ', 0.17)').replace('rgb', 'rgba');;
            $('#background-color').val(rgb);
        },
        // a callback to fire when the input is emptied or an invalid color
        clear: function() {},
    });

    $('.csf-field-better_accordion .csf-accordion-item').each(function(index) {
        var $titles = $(this).find('.csf-accordion-title');

        $titles.on('click', function() {

            var $title   = $(this),
                $icon    = $title.find('.csf-accordion-icon'),
                $content = $title.next();

            if ( $icon.hasClass('fa-angle-right') ) {
                $icon.removeClass('fa-angle-right').addClass('fa-angle-down');
            } else {
                $icon.removeClass('fa-angle-down').addClass('fa-angle-right');
            }

            if ( !$content.data( 'opened' ) ) {

                $content.csf_reload_script();
                $content.data( 'opened', true );

            }

            $content.toggleClass('csf-accordion-open');

        });
    });
});