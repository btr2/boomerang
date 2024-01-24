jQuery(document).ready(function ($) {
    if ( $('.boomerang-poll').length > 0 ) {
        $('.boomerang-poll .csf-cloneable-item').not('.boomerang-poll .csf-cloneable-item.csf-cloneable-hidden').each(function( index ) {
            let id = $(this).find('.poll_id input').val();
            let result = $(this).find('[data-id=' + id + ']');
            result.show();
        });
    }
});