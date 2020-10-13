<script>
    $(document).ready(function(){
        let linkedItemsCount = $('.linked-listing-container .linked-list-item.is-selected').length;

        if(linkedItemsCount >= 10){
            $('#linked-listing-create-new-listing').hide();
        }

        $('.linked-listing-container').sortable({
            items: '.linked-list-item',
            containment: '.linked-listing-container',
            cursor: 'move',
            cancel: '.linked-list-item:not(.is-selected), .linked-list-item .fa-close',
            tolerance: 'pointer'
        });

        $('button[type="submit"]').on('click', function (event) {
            event.preventDefault();

            submitLinkedListings();
        });

        $(document).on('click', '.linked-list-item:not(.is-selected)', function(e){
            let $el = $(this);

            if(e.target != e.currentTarget){
                return false;
            }

            $('#modal-linked-listing').modal('show');

            $(document).on('click', '#modal-linked-listing .addListing', $.proxy(addListing, null, $el));
        });

        $(document).on('click', '.remove-linked-listing, .removeListing', function() {
            let itemReference = $(this).data('id');
            let linkedListingItem = $('.linked-list-item[data-ref="' + itemReference + '"]');

            linkedListingItem.removeClass('is-selected');
            linkedListingItem.removeData('ref');
            linkedListingItem.html(`<i class="fa fa-plus-circle"></i> <?=LANG_ADD_LISTING?>`);

            $('.addListing[data-id="' + itemReference + '"]').show();
            $('.removeListing[data-id="' + itemReference + '"]').hide();

            linkedItemsCount = $('.linked-listing-container .linked-list-item.is-selected').length;

            if(linkedItemsCount < 10){
                $('#linked-listing-create-new-listing').show();
            }

            $('#modal-linked-listing').modal('hide');

            $(document).off('click', '#modal-linked-listing .addListing', addListing);
        });

        let addListing = function($el){
            $(document).off('click', '#modal-linked-listing .addListing', addListing);

            let elementValue = $(this).data('title');
            let elementReference = $(this).data('id');

            $el.addClass('is-selected');
            $el.attr('data-ref', elementReference);
            $el.html(`
                        <span>${elementValue}</span>
                        <a href="javascript:void(0)" class="remove-linked-listing" data-id="${elementReference}"><i class="fa fa-close"></i></a>
                    `);

            $(this).hide();
            $(this).next('.removeListing').show();

            $('#modal-linked-listing').modal('hide');
        }
    });

    let typingTimer;
    let doneTypingInterval = 500;

    let doneTypingEvent = function () {
        clearTimeout(typingTimer);
        let linkedListingSearch = $('#linkedlisting-search');
        let value = linkedListingSearch.val();

        typingTimer = setTimeout(function () {
            searchListingByTerm(value)
        }, doneTypingInterval);
    };

    $('#linkedlisting-search').on('keyup', doneTypingEvent);

    function searchListingByTerm(val) {
        let listingSearch = $('.listing-search');
        let listingContainer = $('#listing-container');
        listingContainer.empty();

        listingContainer.append('<div id="loading-item" is-loading="true">' +
                        '              <img src="/sitemgr/assets/img/loading-32.gif">' +
                        '      </div>');

        let keyupfunctSearchListingByTerm = function () {
            listingSearch.off('keyup', keyupfunctSearchListingByTerm);
            listingSearch.on('keyup', doneTypingEvent);
            xhrSearchListingByTerm.abort();
            doneTypingEvent();
        };

        let addedListings = [];

        $('.linked-list-item.is-selected').each(function() {
            let dataRef = $(this).data('ref');
            if(dataRef !== undefined) {
                addedListings.push(dataRef);
            }
        });

        let accountId = $('#linkedlisting-search').attr('data-accountId');

        let data = {
            action: 'search',
            term: val,
            addedListings: addedListings,
            accountId : accountId,
            domain_id : <?=SELECTED_DOMAIN_ID?>
        };

        let xhrSearchListingByTerm = $.get('<?=DEFAULT_URL?>/includes/code/linkedListingActionAjax.php', data).done(function (data) {
            listingSearch.off('keyup', keyupfunctSearchListingByTerm);

            let objData = jQuery.parseJSON(data);

            $('#loading-item').remove();

            if (objData.success === 'true') {
                listingContainer.append(objData.template);
            }

            listingSearch.on('keyup', doneTypingEvent);
        });

        listingSearch.off('keyup', doneTypingEvent);
        listingSearch.on('keyup', keyupfunctSearchListingByTerm);
    }

    function submitLinkedListings() {
        let listing_id = $('#listingId').val();

        let linkedIds = [];
        $('.linked-list-item.is-selected').each(function() {
            let dataRef = $(this).data('ref');
            if(dataRef !== undefined) {
                linkedIds.push(dataRef);
            }
        });

        let fieldId = $('#listingFieldId').val();

        let data = {
            action : "saveLinkedListings",
            listing_id : listing_id,
            linked_listings : linkedIds,
            fieldId : fieldId,
            domain_id : <?=SELECTED_DOMAIN_ID?>
        };

        delete linkedIds;

        $.post('<?=DEFAULT_URL?>/includes/code/linkedListingActionAjax.php', data).done(function (response) {
            let objData = jQuery.parseJSON(response);

            if(objData.success) {
                let submitButton = $('#submitButton');

                btn = $('.action-save');
                btn.button('reset');
                notify.success(objData.message);
            } else {
                notify.error(objData.message);
            }
        });
    }
</script>
