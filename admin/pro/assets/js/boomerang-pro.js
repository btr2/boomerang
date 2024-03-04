jQuery(document).ready(function ($) {
    if ( $('.boomerang-poll').length > 0 ) {
        $('.boomerang-poll .csf-cloneable-item').not('.boomerang-poll .csf-cloneable-item.csf-cloneable-hidden').each(function( index ) {
            let id = $(this).find('.poll_id input').val();
            let result = $(this).find('[data-id=' + id + ']');
            result.show();
        });
    }

    $("body").on(
        "click",
        "#simple-feature-requests #import-button-sfr",
        function (e) {
            e.preventDefault();

            let button = $(this);

            let container = button.closest('.accordion-item');
            let result = container.find('.import-result');
            let nonce = container.attr("data-nonce");
            let board = container.find('#board').val();
            let comments = container.find('#move-comments-sfr');
            let move_comments = 'no';
            if ( comments.is(":checked") ) {
                move_comments = 'yes';
            }

            $.ajax(
                {
                    type: "POST",
                    url: ajaxurl,
                    data: {
                        action: 'process_sfr',
                        nonce: nonce,
                        board: board,
                        move_comments: move_comments,
                    },
                    success: function (response) {
                        if (!response.success) {

                        } else {
                            result.text(response.data.message);
                            setTimeout(
                                function () {
                                    result.fadeOut();
                                },
                                2000
                            );

                        }
                    },
                }
            );
        }
    );
});