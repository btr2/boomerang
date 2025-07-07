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

            if ( typeof google_recaptcha == "undefined") {
                save_boomerang($(this))
            } else {
                do_google($(this))
            }
        });

    function do_google(e) {
        grecaptcha.ready(function () {
            grecaptcha.execute(google_recaptcha.key, {action: 'save_boomerang'}).then(function (token) {
                save_boomerang(e,token);
            });
        });
    }

    function save_boomerang(e,token = false) {
            let $button = $(e);
            let $form = $button.closest('#boomerang-form');

            let nonce = $form.attr("data-nonce");
            let title = $form.find('#boomerang-title').val();
            let content = $form.find('#boomerang-content').val();
            let guest_name,guest_email,tags,featured_image,hp;
            let board = $form.find('#boomerang-board').val();

            let fd = new FormData();

        if ($form.find('#boomerang-tags').length ) {
            tags = $form.find('#boomerang-tags').val();
            fd.append("tags", tags);
        }

        if ($form.find('#boomerang-guest-name').length) {
            guest_name = $form.find('#boomerang-guest-name').val();
            fd.append("guest_name", guest_name);
        }

        if ($form.find('#boomerang-guest-email').length) {
            guest_email = $form.find('#boomerang-guest-email').val();
            fd.append("guest_email", guest_email);
        }

            if ($form.find('#boomerang_image_upload').length ) {
                featured_image = $form.find('#boomerang_image_upload').prop('files')[0];
                fd.append("boomerang_image_upload", featured_image);
            }

            if ($form.find('#boomerang_hp').length ) {
                hp = $form.find('#boomerang_hp').val();
                fd.append("boomerang_hp", hp);
            }

            if ($form.find('.acf-fields').length ) {
                let a = {};
                $('.acf-field').each(function (index) {
                    index = $(this);
                    let field_key = index.attr('data-key');
                    var field = acf.getField(field_key);
                    var value = field.val();
                    a[field_key] = value;
                })
                let arr = JSON.stringify(a);
                fd.append( 'acf', arr );
            }

            fd.append( 'g-recaptcha-response', token)
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
                            if ($form.find('.acf-fields').length ) {
                                var fields = acf.getFields();
                                fields.forEach(function (field) {
                                    field.val('');

                                });
                            }
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
                            $(window).unbind('beforeunload');
                        }
                    },
                }
            );


        };

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
                        let boomerang = $(e).parents('.boomerang');
                        $(e).parents('.votes-container-outer').html(response.data.content);
                        let result = boomerang.find('.boomerang-messages-container');
                        result.html(response.data.message);
                        result.show();
                        setTimeout(
                            function () {
                                result.fadeOut();
                            },
                            3000
                        );
                    }
                }
            }
        );
    }

    $("body").on(
        "click",
        ".boomerang-admin-area-heading",
        function (e) {
            const mediaQuery = window.matchMedia('(max-width: 860px)')
            if (mediaQuery.matches) {
                $(this).next('.boomerang-admin-area-inner').slideToggle({
                start: function () {
                    $(this).css({
                        display: "flex"
                    })
                }
            });

            $(this).toggleClass('open');
            }
        }
    );

    $("body").on(
        "click",
        ".boomerang-admin-area .control-header",
        function (e) {
            $('.boomerang-admin-area .control-content').not(this).slideUp();
            $('.boomerang-admin-area .control-header').not(this).removeClass('open');
            $(this).toggleClass('open');
            if ($(this).hasClass('open')) {
                $(this).next('.control-content').slideToggle({
                    start: function () {
                        $(this).css({
                            display: "flex"
                        })
                    }
                });
            }
        }
    );

    $("body").on(
        "click",
        ".boomerang-admin-area #boomerang-status-submit",
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
                                // $('.boomerang').find('.boomerang-status').hide();

                                $(".boomerang").removeClass (function (index, className) {
                                    return (className.match (/(^|\s)boomerang_status-\S+/g) || []).join(' ');
                                });
                                $('.boomerang').find('.boomerang-single-content .boomerang-meta .boomerang-status').hide();
                                container.find('.boomerang-status .control-header').removeClass('open');
                                container.find('.boomerang-status .control-content').slideToggle();
                            } else {
                                $(".boomerang").removeClass (function (index, className) {
                                    return (className.match (/(^|\s)boomerang_status-\S+/g) || []).join(' ');
                                });
                                $('.boomerang').addClass('boomerang_status-' + response.data.term);
                                $('.boomerang').find('.boomerang-single-content .boomerang-meta .boomerang-status').text(response.data.content).show();
                                container.find('.boomerang-status .control-header').removeClass('open');
                                container.find('.boomerang-status .control-content').slideToggle();
                            }
                        }
                    },
                }
            );
        }
    );

    $("body").on(
        "click",
        ".boomerang-admin-area #boomerang-post-status-submit",
        function (e) {
            e.preventDefault();

            let $button = $(this);
            let container = $button.closest('.boomerang-admin-area');
            let post_id = container.attr("data-id");
            let nonce = container.attr("data-nonce");
            let the_action = $button.attr( 'data-action' );

            $.ajax(
                {
                    type: "POST",
                    url: settings.ajaxurl,
                    data: {
                        action: 'process_post_status_submit',
                        post_id: post_id,
                        nonce: nonce,
                        dataType: 'json',
                        the_action: the_action,
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

    /**
     * Handle when a filter is selected.
     */
    $("body").on(
        "change",
        "#boomerang-board-filters select",
        function (e) {
            // Reset infinite scroll state when filters change
            let paginationType = null;
            if (typeof boomerangPaginationType !== 'undefined' && boomerangPaginationType.type) {
                paginationType = boomerangPaginationType.type;
            } else if ($(".boomerang-directory").hasClass('boomerang-infinite-scroll')) {
                paginationType = 'infinite';
            }
            if (paginationType === 'infinite') {
                $(window).off('scroll.boomerangInfinite');
            }
            getBoomerangs();

            return false;
        }
    );

    /**
     * Handle when a search term is entered.
     */
    $("body").on(
        "input",
        "#boomerang-board-filters #boomerang-search",
        function (e) {
            // Reset infinite scroll state when search changes
            let paginationType = null;
            if (typeof boomerangPaginationType !== 'undefined' && boomerangPaginationType.type) {
                paginationType = boomerangPaginationType.type;
            } else if ($(".boomerang-directory").hasClass('boomerang-infinite-scroll')) {
                paginationType = 'infinite';
            }
            if (paginationType === 'infinite') {
                $(window).off('scroll.boomerangInfinite');
            }
            getBoomerangs();
        }
    );

    /**
     * Handle when a tag inside a single archive item is clicked.
     */
    $("body").on(
        "click",
        ".boomerang-directory .boomerang .boomerang-tag",
        function (e) {
            let tag = $(this).attr( 'data-id' );
            $('#boomerang-board-filters').find('#boomerang-tags').val(tag);
            // Reset infinite scroll state when tag filter is applied
            let paginationType = null;
            if (typeof boomerangPaginationType !== 'undefined' && boomerangPaginationType.type) {
                paginationType = boomerangPaginationType.type;
            } else if ($(".boomerang-directory").hasClass('boomerang-infinite-scroll')) {
                paginationType = 'infinite';
            }
            if (paginationType === 'infinite') {
                $(window).off('scroll.boomerangInfinite');
            }
            getBoomerangs();
        }
    );

    /**
     * Handle when an archive is loaded fresh as a daisy.
     */
    $( document ).ready(
        function () {
            if ($( ".boomerang-directory" ).length > 0) {
                getBoomerangs();
                
                // Initialize infinite scroll if enabled
                initInfiniteScroll();
            }
        }
    );

    /**
     * Initialize infinite scroll functionality
     */
    function initInfiniteScroll() {
        let directory = $(".boomerang-directory");
        let board = directory.attr('data-board');
        
        // Fallback: if boomerangPaginationType is not defined, check if the directory has the infinite scroll class
        let paginationType = null;
        if (typeof boomerangPaginationType !== 'undefined' && boomerangPaginationType.type) {
            paginationType = boomerangPaginationType.type;
        } else if (directory.hasClass('boomerang-infinite-scroll')) {
            paginationType = 'infinite';
        }
        
        // Check if infinite scroll is enabled for this board
        if (board && paginationType === 'infinite') {
            // Remove any existing scroll handlers to prevent duplicates
            $(window).off('scroll.boomerangInfinite');
            
            let isLoading = false;
            let currentPage = 1;
            let hasMore = true;
            
            // Add scroll event listener with namespace
            $(window).on('scroll.boomerangInfinite', function() {
                if (isLoading || !hasMore) return;
                
                let scrollTop = $(window).scrollTop();
                let windowHeight = $(window).height();
                let documentHeight = $(document).height();
                
                // Load more when user is near bottom (within 200px)
                if (scrollTop + windowHeight >= documentHeight - 200) {
                    loadMoreBoomerangs();
                }
            });
            
            /**
             * Load more boomerangs for infinite scroll
             */
            function loadMoreBoomerangs() {
                if (isLoading || !hasMore) return;
                
                isLoading = true;
                currentPage++;
                
                let container = $(".boomerang-directory-list");
                let loadingIndicator = $('<div class="boomerang-infinite-loading"><span class="spinner"></span> Loading more...</div>');
                
                // Add loading indicator
                container.append(loadingIndicator);
                
                let data = {
                    action: 'get_boomerangs',
                    dataType: 'json',
                    nonce: directory.attr('data-nonce'),
                    board: directory.attr('data-board'),
                    base: directory.attr('data-base'),
                    page: currentPage,
                };

                if ($("#boomerang-board-filters").length > 0) {
                    let filters = $("#boomerang-board-filters");
                    data.order = filters.find('#boomerang-order').val();
                    data.status = filters.find('#boomerang-status').val();
                    data.tags = filters.find('#boomerang-tags').val();
                    data.search = filters.find('#boomerang-search').val();
                }

                $.ajax({
                    type: "POST",
                    url: settings.ajaxurl,
                    data: data,
                    success: function (response) {
                        loadingIndicator.remove();
                        
                        if (!response.success) {
                            console.error('Failed to load more boomerangs');
                            return;
                        }
                        
                        // Handle infinite scroll response
                        if (response.data.hasOwnProperty('content')) {
                            // Append new content
                            container.append(response.data.content);
                            hasMore = response.data.has_more;
                            currentPage = response.data.current_page;
                        } else {
                            // Fallback for non-infinite scroll response
                            container.append(response.data);
                        }
                        
                        isLoading = false;
                    },
                    error: function() {
                        loadingIndicator.remove();
                        isLoading = false;
                        console.error('Error loading more boomerangs');
                    }
                });
            }
        }
    }

    /**
     * Handle when a pagination item is clicked.
     */
    $( ".boomerang-directory" ).on(
        "click",
        ".page-numbers a",
        function (e) {
            if (e.preventDefault) {
                e.preventDefault();
            }

            // Reset infinite scroll state when pagination is manually clicked
            let paginationType = null;
            if (typeof boomerangPaginationType !== 'undefined' && boomerangPaginationType.type) {
                paginationType = boomerangPaginationType.type;
            } else if ($(".boomerang-directory").hasClass('boomerang-infinite-scroll')) {
                paginationType = 'infinite';
            }
            if (paginationType === 'infinite') {
                $(window).off('scroll.boomerangInfinite');
            }

            page = parseInt($(this).html());
            getBoomerangs(page);
        }
    );

    /**
     * Get our Boomerangs.
     *
     * @param page
     */
    function getBoomerangs(page) {
        let container = $( ".boomerang-directory-list" );
        let directory = $( ".boomerang-directory" );

        let data = {
            action: 'get_boomerangs',
            dataType: 'json',
            nonce: directory.attr('data-nonce'),
            board: directory.attr('data-board'),
            base: directory.attr('data-base'),
            page: page ?? 1,
        };

        if ($( "#boomerang-board-filters" ).length > 0) {
            let filters = $( "#boomerang-board-filters" );

            data.order = filters.find('#boomerang-order').val();
            data.status = filters.find('#boomerang-status').val();
            data.tags = filters.find('#boomerang-tags').val();
            data.search = filters.find('#boomerang-search').val();
        }

        $.ajax(
            {
                type: "POST",
                url: settings.ajaxurl,
                data: data,
                beforeSend: function () {
                    container.html( '<div class="boomerang-directory-spinner"><span class="spinner"></span></div>' );
                },
                success: function (response) {
                    if (!response.success) {

                    } else {
                        // Handle infinite scroll response
                        if (response.data.hasOwnProperty('content')) {
                            container.html(response.data.content);
                        } else {
                            container.html(response.data);
                        }
                        
                        // Re-initialize infinite scroll after content update
                        initInfiniteScroll();
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
        "click",
        ".boomerang-banner .banner-action-link.approve-now-link",
        function (e) {
            e.preventDefault();

            let link = $(this);
            let post_id = link.attr("data-id");
            let nonce = link.attr("data-nonce");

            $.ajax(
                {
                    type: "POST",
                    url: settings.ajaxurl,
                    data: {
                        action: 'process_approve_now',
                        post_id: post_id,
                        nonce: nonce,
                        dataType: 'json',
                    },
                    success: function (response) {
                        if (!response.success) {

                        } else {
                            console.log(response);
                            link.addClass('success');
                            link.text(response.data.message);
                            setTimeout(
                                function () {
                                    link.parent().fadeOut();
                                },
                                500
                            );
                        }
                    },
                }
            );
        }
    );

});