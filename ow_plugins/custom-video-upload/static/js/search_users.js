function CustomSearchUserList(params)
{
    var listOptions = {
        'ajaxResponder': '',
        'userId': 0,
        'translations': '',
        'inputUserNameSearch': 'input-user-name-search',
        'userListSearchResult': 'user-list-search-result',
        'loadingContent': 'loading-content',
        'scrollLiveSearchResult': 'scroll-live-search-result',
        'userItemAdded': 'user_item_added',
        'removeUserLabel': 'remove_user_label',
        'userSearchInput': 'custom_user_search_input'
    };

    listOptions = $.extend({}, listOptions, params);

    var self = this;
    var timeOut = null;

    var getAddedUsers = function () {
        var addedUsers = [];

        $('.' + listOptions.userItemAdded).each(function () {
            addedUsers.push($(this)[0].dataset.user);
        });

        return addedUsers;
    }

    var getAddedUsersStr = function () {
        var addedUsers = getAddedUsers();

        var str = '';

        if ( addedUsers.length > 0 ) {
            addedUsers.forEach(function (val) {
                str = str + val + ','
            });

            if ( str.length > 0 ) {
                str = str.substring(0, str.length - 1);
            }
        }

        return str;
    }

    var initAddedUsers = function () {

        var str = '';

        if ( getAddedUsersStr().length > 0 ) {
            str = getAddedUsersStr();
        }

        if (getAddedUsersStr().length == 0) {
            $(".selected_users_wrapper").addClass('ow_hidden');
        }

        $('input[name="' + listOptions.userSearchInput + '"]').val(str);
    }

    var processInput = function (inputElement) {

        if ( inputElement[0] === undefined )
        {
            $('#' + listOptions.scrollLiveSearchResult).eq(0).html('');

            return;
        }

        inputElement = inputElement[0];

        if (!inputElement.value || inputElement.value.length < 3) {

            $('#' + listOptions.scrollLiveSearchResult).eq(0).html('');

            return;
        }

        $('#' + listOptions.loadingContent).eq(0).show();

        // clear search results if inputs new user name
        $('#' + listOptions.scrollLiveSearchResult).eq(0).html('');

        // send and process request
        $.ajax({
            url: listOptions.ajaxResponder,
            type: 'POST',
            data: {
                searchVal: inputElement.value,
                addedUser: getAddedUsers()
            },
            dataType: 'json',
            success: function( response ) {
                // check response
                if (response && response.success === true) {
                    // remove old scroll
                    OW.removeScroll($('#' + listOptions.scrollLiveSearchResult).eq(0));

                    // add html by page number (if page is 1, set new html)
                    $('#' + listOptions.scrollLiveSearchResult).eq(0).html(response.content);

                    $('#' + listOptions.loadingContent).eq(0).hide();

                    // process user mouseenter event
                    $(".user_item_to_add").on('mouseenter', function () {
                        var addUserElement = $(".add_user_label");

                        $(this).css('background-color', '#daedf7');
                        $(this).find(addUserElement).show();
                    });

                    // process user mouseleave event
                    $(".user_item_to_add").on('mouseleave', function () {
                        var addUserElement = $(".add_user_label");

                        $(this).css('background-color', '#f2f2f2');
                        $(this).find(addUserElement).show().hide();
                    });

                    // add user to added user list
                    $(".add_user_label").on('click', function () {

                        if ($(this)[0].className.indexOf('added_user_label') !== -1) {
                            return;
                        }

                        // add user
                        var userItemElement = $(this).closest('.user_item_to_add');

                        var newElement = userItemElement.clone().appendTo($("#user-list-search-result")[0]);
                        newElement.removeClass('user_item_to_add').addClass(listOptions.userItemAdded).addClass('');
                        //newElement.css('background-color', '#ffffff');

                        newElement.find('.add_user_label').removeClass('add_user_label').addClass(listOptions.removeUserLabel);

                        var removeUserLabel = newElement.find('.' + listOptions.removeUserLabel);
                        removeUserLabel[0].innerText = listOptions.translations.remove_user_button_label;
                        // removeUserLabel.hide();

                        // $(newElement).on('mouseenter', function () {
                        //     removeUserLabel.show();
                        //     $(this).css('background-color', '#ededed');
                        // });

                        // $(newElement).on('mouseleave', function () {
                        //     removeUserLabel.hide();
                        //     $(this).css('background-color', '#ffffff');
                        // });

                        $(".selected_users_wrapper").removeClass('ow_hidden');

                        $(removeUserLabel).on('click', function () {
                            $(this).closest('.' + listOptions.userItemAdded).remove();
                            initAddedUsers();
                        });

                        $(this).removeClass('add_user_label').addClass('added_user_label');
                        $(this)[0].innerText = listOptions.translations.added_user_label;

                        initAddedUsers();
                    });

                    // add scroll
                    OW.addScroll($('#' + listOptions.scrollLiveSearchResult).eq(0), {contentWidth: '0px'});

                    // stop mouse click event (to exclude cleaning search results)
                    $('#' + listOptions.scrollLiveSearchResult).on('click', function (e) {
                        if ( $(e.target).closest($(this)) ) {
                            e.stopPropagation();
                        }
                    });
                }
            }
        });
    }



    /**
     * Init
     *
     * @returns void
     */
    this.init = function() {

        var inputUserNameSearch = $('#' + listOptions.inputUserNameSearch);
        var userItemAdded = $('.' + listOptions.userItemAdded);

        // clean users search result by click outer place
        $('.ow_page').click( function () {
            $('#' + listOptions.scrollLiveSearchResult).eq(0).html('');
        });

        $(this).find('.' + listOptions.removeUserLabel).show();

        // userItemAdded.on('mouseenter', function () {
        //     $(this).find('.' + listOptions.removeUserLabel).show();
        //     $(this).css('background-color', '#ededed');
        // });
        //
        // userItemAdded.on('mouseleave', function () {
        //     $(this).find('.' + listOptions.removeUserLabel).hide();
        //     $(this).css('background-color', '#ffffff');
        // });

        $('.' + listOptions.removeUserLabel).on('click', function () {
            $(this).closest('.' + listOptions.userItemAdded).remove();

            initAddedUsers();
        });

        // process user name input
        inputUserNameSearch.on('input', function () {
            // check time out delay between inputs text
            if (timeOut) {
                clearTimeout(timeOut);
            }

            // set time out delay and run process input
            timeOut = setTimeout(() => processInput($(this), 1), 500);
        });

        // stop mouse click event (to exclude cleaning search results)
        inputUserNameSearch.on('click', function (e) {
            e.stopPropagation();
        });

        // stop mouse click event (to exclude cleaning search results)
        $('#' + listOptions.scrollLiveSearchResult).on('click', function (e) {
            e.stopPropagation();
        });
    }
}