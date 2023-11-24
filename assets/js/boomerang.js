jQuery(document).ready(function ($) {
    $('.boomerang_select.select2').select2({
        tags: true,
        tokenSeparators: [',', ' '],
    });

    $("body").on(
        "click",
        "#boomerang-form-wrapper #bf-submit",
        function (e) {
            e.preventDefault();

            let $button = $(this);
            let $form = $button.closest('#boomerang-form');

            let nonce = $form.attr("data-nonce");
            let title = $form.find('#boomerang-title').val();
            let content = $form.find('#boomerang-content').val();
            let tags,featured_image;
            let board = $form.find('#boomerang-board').val();

            let fd = new FormData();

            if ($form.find('#boomerang-tags').length ) {
                tags = $form.find('#boomerang-tags').val();
                fd.append("tags", tags);
            }

            if ($form.find('#boomerang_image_upload').length ) {
                featured_image = $form.find('#boomerang_image_upload').prop('files')[0];
                fd.append("boomerang_image_upload", featured_image);
            }

            fd.append("title", title);
            fd.append("content", content);
            fd.append("boomerang_form_nonce", nonce);
            fd.append("board", board);
            fd.append('action', 'save_boomerang');

            $.ajax(
                {
                    type: "POST",
                    url: settings.ajaxurl,
                    data: fd,
                    processData: false,
                    contentType: false,
                    cache: false,

                    beforeSend: function () {
                        $("#bf-spinner").css('display', 'inline-block');
                    },
                    success: function (response) {
                        $("#bf-spinner").hide();
                        let result = $("#bf-result");

                        if (!response.success) {
                            console.log(response.data[0].code);
                            result.removeClass("success").addClass("error");
                            result.text(response.data[0].message);
                            result.show();
                            setTimeout(
                                function () {
                                    result.fadeOut();
                                },
                                3000
                            );
                        } else {
                            result.removeClass("error").addClass("success");
                            result.text(response.data.message);
                            result.show();
                            $form.trigger('reset');
                            $form.find($('.boomerang_select')).val('').trigger('change');
                            setTimeout(
                                function () {
                                    result.fadeOut();
                                },
                                3000
                            );
                            if ($(".boomerang-directory").length) {
                                $(".boomerang-directory-list").html(response.data.content);
                            }
                        }
                    },
                }
            );
        }
    );

    $("body").on(
        "click",
        ".boomerang .votes-container .boomerang-vote",
        function (e) {
            // let e = $( this );
            processVote($(this));

            return false;
        }
    );


    function processVote(e) {
        let nonce = $(e).parents('.votes-container').attr("data-nonce");
        let post_id = $(e).parents('.votes-container').attr("data-id");
        let modifier;

        if ($(e).hasClass('vote-up')) {
            modifier = '1';
        } else if ($(e).hasClass('vote-down')) {
            modifier = '-1';
        }

        $.ajax(
            {
                type: "POST",
                url: settings.ajaxurl,
                data: {
                    boomerang_process_vote: nonce,
                    post_id: post_id,
                    modifier: modifier,
                    action: "process_vote",
                },
                success: function (response) {
                    if (!response.success) {

                    } else {
                        $(e).parents('.votes-container').html(response.data.content);
                    }
                }
            }
        );
    }

    $("body").on(
        "click",
        ".boomerang-admin-area-heading",
        function (e) {
            $(this).next('.boomerang-admin-area-inner').slideToggle({
                start: function () {
                    $(this).css({
                        display: "flex"
                    })
                }
            });

            $(this).toggleClass('open');
        }
    );

    $("body").on(
        "click",
        ".boomerang-admin-area .control-header",
        function (e) {
            $(this).next('.control-content').slideToggle({
                start: function () {
                    $(this).css({
                        display: "flex"
                    })
                }
            });

            $(this).toggleClass('open');
        }
    );

    $("body").on(
        "click",
        ".boomerang-admin-area #boomerang-admin-area-submit",
        function (e) {
            e.preventDefault();

            let $button = $(this);
            let container = $button.closest('.boomerang-admin-area');
            let post_id = container.attr("data-id");
            let nonce = container.attr("data-nonce");
            let status = container.find('#boomerang_status').val();

            $.ajax(
                {
                    type: "POST",
                    url: settings.ajaxurl,
                    data: {
                        action: 'process_admin_action',
                        post_id: post_id,
                        nonce: nonce,
                        dataType: 'json',
                        status: status,
                    },
                    success: function (response) {
                        if (!response.success) {

                        } else {
                            if (null === response.data.content) {
                                $('.boomerang').find('.boomerang-status').hide();
                            } else {
                                $(".boomerang").removeClass (function (index, className) {
                                    return (className.match (/(^|\s)boomerang_status-\S+/g) || []).join(' ');
                                });
                                $('.boomerang').addClass('boomerang_status-' + response.data.term);
                                $('.boomerang').find('.boomerang-status').text(response.data.content).show();
                            }
                        }
                    },
                }
            );
        }
    );

    $("body").on(
        "change",
        "#boomerang-board-filters select",
        function (e) {
            processFilter($(this));

            return false;
        }
    );

    $("body").on(
        "input",
        "#boomerang-board-filters #boomerang-search",
        function (e) {
            processFilter($(this));
        }
    );

    function processFilter(e) {
        let filters = e.parents('#boomerang-board-filters');
        let board = filters.parents('.boomerang-directory').attr('data-board');
        let nonce = filters.attr("data-nonce");

        let boomerang_order = filters.find('#boomerang-order').val();
        let boomerang_status = filters.find('#boomerang-status').val();
        let boomerang_tags = filters.find('#boomerang-tags').val();
        let boomerang_search = filters.find('#boomerang-search').val();

        $.ajax(
            {
                type: "POST",
                url: settings.ajaxurl,
                data: {
                    action: 'process_filter',
                    nonce: nonce,
                    board: board,
                    boomerang_order: boomerang_order,
                    boomerang_status: boomerang_status,
                    boomerang_tags: boomerang_tags,
                    boomerang_search: boomerang_search,
                    dataType: 'json',
                },
                success: function (response) {
                    if (!response.success) {

                    } else {
                        filters.next().html(response.data.content);
                    }
                },
            }
        );
    }

    $("body").on(
        "click",
        ".boomerang-directory .boomerang .boomerang-tag",
        function (e) {
            processTag($(this));
        }
    );

    function processTag(e) {
        // let filters = e.parents('#boomerang-board-filters');
        let board = e.parents('.boomerang-directory').attr('data-board');
        let nonce = e.parent().attr("data-nonce");

        let boomerang_order  = $('#boomerang-board-filters').find('#boomerang-order').val();
        let boomerang_status = $('#boomerang-board-filters').find('#boomerang-status').val();
        let boomerang_tags   = e.attr("data-id");
        let boomerang_search = $('#boomerang-board-filters').find('#boomerang-search').val();

        console.log($('#boomerang-board-filters'));

        $.ajax(
            {
                type: "POST",
                url: settings.ajaxurl,
                data: {
                    action: 'process_tag',
                    nonce: nonce,
                    board: board,
                    boomerang_order: boomerang_order,
                    boomerang_status: boomerang_status,
                    boomerang_tags: boomerang_tags,
                    boomerang_search: boomerang_search,
                    dataType: 'json',
                },
                success: function (response) {
                    if (!response.success) {

                    } else {
                        e.parents('.boomerang-directory-list').html(response.data.content);
                        $('#boomerang-board-filters').find('#boomerang-tags').val(boomerang_tags);
                    }
                },
            }
        );
    }

    if ($('#boomerang-dropcontainer').length) {
        const dropContainer = document.getElementById("boomerang-dropcontainer")
        const fileInput = document.getElementById("boomerang_image_upload")

        dropContainer.addEventListener("dragover", (e) => {
            // prevent default to allow drop
            e.preventDefault()
        }, false)

        dropContainer.addEventListener("dragenter", () => {
            dropContainer.classList.add("drag-active")
        })

        dropContainer.addEventListener("dragleave", () => {
            dropContainer.classList.remove("drag-active")
        })

        dropContainer.addEventListener("drop", (e) => {
            e.preventDefault()
            dropContainer.classList.remove("drag-active")
            fileInput.files = e.dataTransfer.files
        })
    }

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


});