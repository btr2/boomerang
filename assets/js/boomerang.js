jQuery(document).ready(function ($) {
    $('.boomerang_select.select2').select2({
        tags: true,
        tokenSeparators: [',', ' '],
    });

    $( "body" ).on(
        "click",
        "#boomerang-form-wrapper #bf-submit",
        function (e) {
            e.preventDefault();

            let $button = $( this );
            let $form = $button.closest('#boomerang-form');
            let nonce          = $form.attr( "data-nonce" );
            let title = $form.find('#title').val();
            let content = $form.find('#content').val();
            let tags = $form.find('#tags').val();

            $.ajax(
                {
                    type: "POST",
                    dataType: "JSON",
                    url: settings.ajaxurl,
                    data: {
                        title: title,
                        content: content,
                        tags: tags,
                        boomerang_form_nonce: nonce,
                        action: "save_boomerang",
                    },
                    beforeSend: function () {
                        $( "#bf-spinner" ).show();
                    },
                    success: function (response) {
                        $( "#bf-spinner" ).hide();
                        let result = $( "#bf-result" );

                        if ( ! response.success) {
                            console.log( response.data[0].code );
                            result.removeClass( "success" ).addClass( "error" );
                            result.text( response.data[0].message );
                            result.show();
                            setTimeout(
                                function () {
                                    result.fadeOut();
                                },
                                3000
                            );
                        } else {
                            $button.width( $button.width() ).text(settings.success);
                            result.removeClass( "error" ).addClass( "success" );
                            result.text( response.data.message );
                            result.show();
                            $form.trigger('reset');
                            $form.find($('.boomerang_select')).val('').trigger('change');
                            setTimeout(
                                function () {
                                    result.fadeOut();
                                },
                                3000
                            );
                            if ( $(".boomerang-directory").length ) {
                                $(".boomerang-directory").html(response.data.content);
                            }
                        }
                    },
                }
            );
        }
    );

});