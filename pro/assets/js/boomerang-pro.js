jQuery(document).ready(function ($) {

    $("body").on(
        "change",
        ".private-note-toggle input",
        function (e) {
            let form = $(this).parents('#commentform');
            if ($(this).is(':checked')) {
                console.log('clicked');
                form.find('#submit').val(settings.note);
                form.toggleClass('private-note');

            } else {
                form.find('#submit').val(settings.comment);
                form.toggleClass('private-note');
            }
        }
    );

    if ($('.private-note-toggle input').is(':checked')) {
        console.log('clicked');
    }

    if ($('.boomerang-attachments').length) {
        if (typeof SimpleLightbox === 'function') {
            var lightbox = new SimpleLightbox('.boomerang-image-attachments a', { /* options */});
        }
    }

    if ($('.boomerang-suggested-ideas-container').length) {
        $("body").on(
            "input",
            "#boomerang-form #boomerang-title",
            function (e) {
                processSuggested($(this));
            }
        );
    }

    function processSuggested(e) {
        let board = e.attr('data-board');
        let nonce = e.attr("data-nonce");
        let value = e.val();

        $.ajax(
            {
                type: "POST",
                url: settings.ajaxurl,
                data: {
                    action: 'find_suggested_ideas',
                    nonce: nonce,
                    board: board,
                    value: value,
                    dataType: 'json',
                },
                success: function (response) {
                    if (!response.success) {

                    } else {
                        if (response.data.content.length) {
                            $('.boomerang-suggested-ideas-list').html(response.data.content);
                            if ( value.length > 1 ) {
                                $('.boomerang-suggested-ideas-container').show();
                            } else {
                                $('.boomerang-suggested-ideas-container').hide();
                            }

                        } else {
                            $('.boomerang-suggested-ideas-container').hide();
                        }
                    }
                },
            }
        );
    }

    $("body").on(
        "click",
        ".boomerang-suggested-ideas-container header",
        function (e) {
            $(this).next('.boomerang-suggested-ideas-list').slideToggle({
                start: function () {
                    $(this).css({
                        display: "flex"
                    })
                }
            });

            $(this).parent().toggleClass('open');
        }
    );

    $("body").on(
        "click",
        ".boomerang-admin-area #boomerang-crowdfunding-product-submit",
        function (e) {
            e.preventDefault();

            let $button = $(this);
            let container = $button.closest('.boomerang-admin-area');
            let post_id = container.attr("data-id");
            let nonce = container.attr("data-nonce");
            let product = container.find('#boomerang-crowdfunding-products-dropdown').val();

            $.ajax(
                {
                    type: "POST",
                    url: settings.ajaxurl,
                    data: {
                        action: 'process_crowdfunding_product_submit',
                        post_id: post_id,
                        nonce: nonce,
                        dataType: 'json',
                        product_id: product,
                    },
                    success: function (response) {
                        if (!response.success) {

                        } else {
                            location.reload();
                        }
                    },
                }
            );
        }
    );

    $("body").on(
        "click",
        ".boomerang .boomerang-edit-link",
        function (e) {
            e.preventDefault();
            $('#boomerang-edit-screen-modal').addClass( 'active' );
        }
    );

    if ($('.boomerang_edit_form_tags').length) {

        var edit_tags_select2 = $('.boomerang_edit_form_tags').select2({
            tags: true,
            tokenSeparators: [',', ' '],
        });

        let current_tags = $('#boomerang-edit-screen #boomerang-tags').attr('data-selected');
        edit_tags_select2.val(JSON.parse(current_tags));
        edit_tags_select2.trigger('change');
    }

    $("body").on(
        "click",
        "#boomerang-edit-screen-modal form #cancel",
        function (e) {
            e.preventDefault();
            $('#boomerang-edit-screen-modal').removeClass( 'active' );
        }
    );

    $("body").on(
        "click",
        "#boomerang-edit-screen-modal form #submit",
        function (e) {
            e.preventDefault();
            edit_boomerang($(this))
        }
    );

    function edit_boomerang(e,token = false) {
        let $button = $(e);
        let $form = $button.closest('#boomerang-edit-screen #boomerang-edit-form');

        let nonce = $form.attr("data-nonce");
        let id = $form.attr("data-id");
        let title = $form.find('#boomerang-title').val();
        let content = $form.find('#boomerang-content').val();
        let tags;
        let board = $form.attr("data-board");

        let fd = new FormData();

        if ($form.find('#boomerang-tags').length ) {
            tags = $form.find('#boomerang-tags').val();
            fd.append("tags", tags);
        }

        fd.append("ID", id);
        fd.append("title", title);
        fd.append("content", content);
        fd.append("boomerang_edit_nonce", nonce);
        fd.append("board", board);
        fd.append('action', 'edit_boomerang');

        $.ajax(
            {
                type: "POST",
                url: settings.ajaxurl,
                data: fd,
                processData: false,
                contentType: false,
                cache: false,

                success: function (response) {
                    if (!response.success) {

                    } else {
                        $('.boomerang-single-content header .entry-title').text(response.data.post.post_title);
                        $('.boomerang-single-content .entry-content-inner').html(response.data.post.post_content);
                        $('.boomerang-single-content .boomerang-tags-container').html(response.data.tags);
                        $('#boomerang-edit-screen-modal').removeClass( 'active' );
                    }
                },
            }
        );


    };

    $("body").on(
        "click",
        ".boomerang-admin-area #boomerang_mark_as_bug",
        function (e) {
            e.preventDefault();

            let button = $(this);
            let container = button.closest('.boomerang-admin-area');
            let post_id = container.attr("data-id");
            let nonce = container.attr("data-nonce");

            $.ajax(
                {
                    type: "POST",
                    url: settings.ajaxurl,
                    data: {
                        action: 'process_mark_as_bug',
                        post_id: post_id,
                        nonce: nonce,
                        dataType: 'json',
                    },
                    success: function (response) {
                        if (!response.success) {
                            console.log(response);
                        } else {
                            location.reload();
                        }
                    },
                }
            );
        }
    );

    $("body").on(
        "click",
        ".boomerang-poll .close-button, .boomerang-poll .poll-success-close-button",
    function (e) {
        let wrapper = $(this).closest('.boomerang-poll-wrapper');
        wrapper.hide();
    });

    $("body").on(
        "click",
        ".boomerang-poll-submit",
        function (e) {
            e.preventDefault();

            let button = $(this);
            let wrapper = button.closest('.boomerang-poll-wrapper');
            let poll_id = wrapper.attr("data-id");
            let nonce = wrapper.attr("data-nonce");
            let board = wrapper.attr("data-board");
            let value = wrapper.find('.poll-option:checked').val();

            $.ajax(
                {
                    type: "POST",
                    url: settings.ajaxurl,
                    data: {
                        action: 'poll_handler',
                        poll_id: poll_id,
                        nonce: nonce,
                        board: board,
                        value: value,
                        dataType: 'json',
                    },
                    success: function (response) {
                        if (!response.success) {
                            console.log(response);
                        } else {
                            wrapper.addClass( 'success' );
                        }
                    },
                }
            );
        }
    );

    $("body").on(
        "click",
        ".boomerang .boomerang-merge-button",
        function (e) {
            e.preventDefault();
            $('#boomerang-merge-screen-modal').addClass( 'active' );
        }
    );

    $('.boomerang-merge-select').select2({
        placeholder: "Search for a Boomerang...",
        width: '100%',
    });

    $("body").on(
        "click",
        "#boomerang-merge-screen-modal form #cancel",
        function (e) {
            e.preventDefault();
            $('#boomerang-merge-screen-modal').removeClass( 'active' );
        }
    );

    $("body").on(
        "click",
        "#boomerang-merge-screen-modal form #submit",
        function (e) {
            e.preventDefault();
            console.log(e);
            merge_boomerang($(this))
        }
    );

    function merge_boomerang(e) {
        let button = $(e);
        let form = button.closest('#boomerang-merge-screen #boomerang-merge-form');
        let nonce = form.attr("data-nonce");
        let id = form.attr("data-id");
        let primary = form.find('.boomerang-merge-select').val();

        $.ajax(
            {
                type: "POST",
                url: settings.ajaxurl,
                data: {
                    action: 'merge_boomerang',
                    nonce: nonce,
                    ID: id,
                    primary: primary,
                    dataType: 'json',
                },
                success: function (response) {
                    if (!response.success) {
                        console.log(response);
                        form.find('.merge-result').text(response.data[0].message)
                    } else {
                        console.log(response);
                        form.find('.merge-result').addClass('success')
                        form.find('.merge-result').text(response.data.message)
                        setTimeout(
                            function () {
                                $('#boomerang-merge-screen-modal').removeClass( 'active' );
                                form.find('.merge-result').removeClass('success')
                                form.find('.merge-result').text('')
                                // Clears the Select2 dropdown.
                                form.find('.boomerang-merge-select').val(null).trigger('change');
                            },
                            1500
                        );

                    }
                },
            }
        );
    }


});