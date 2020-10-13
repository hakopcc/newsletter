<script src="<?=DEFAULT_URL?>/scripts/jquery/auto_upload/js/file_uploads.js"></script>

<script>
    $(document).ready(function(){

        // hack to fix ckeditor/bootstrap compatibility bug when ckeditor appears in a bootstrap modal dialog
        // References:
        // http://stackoverflow.com/questions/14420300/bootstrap-with-ckeditor-equals-problems/18554395#18554395
        // http://jsfiddle.net/pvkovalev/4PACy/
        $.fn.modal.Constructor.prototype.enforceFocus = function () {
            modal_this = this
            $(document).on('focusin.modal', function (e) {
                if (modal_this.$element[0] !== e.target && !modal_this.$element.has(e.target).length
                    // add whatever conditions you need here:
                    &&
                    !$(e.target.parentNode).hasClass('cke_dialog_ui_input_select') && !$(e.target.parentNode).hasClass('cke_dialog_ui_input_text')) {
                    modal_this.$element.focus()
                }
            })
        };

        let categoryModule = $('#module').val();

        let categoryLimit;

        if(categoryModule === 'listing') {
            categoryLimit = '<?=LISTING_MAX_CATEGORY_ALLOWED?>';
        } else {
            categoryLimit = '<?=MAX_CATEGORY_ALLOWED?>';
        }

        //Pre-fill page title
        $('#title').blur(function() {
            $('#page_title').val($('#title').val());
        });

        $(".categoryImageDeleteButton").click(function(){
            let clickedItem = $(this);

            let data = {
                action : "ajax",
                type : "removeImage",
                module : categoryModule,
                id : clickedItem.data("id"),
                domain_id : <?=SELECTED_DOMAIN_ID?>
            };

            $.post('<?=DEFAULT_URL?>/includes/code/CategoryActionAjax.php', data).done(function (response) {
                if (response) {
                    try {
                        let data = JSON.parse(response);
                        if( typeof(data.exception) === 'undefined' ) {
                            if (data && data.status) {
                                $("#image-thumb").find("img").fadeOut(function(){
                                    $(this).remove()
                                });
                                clickedItem.addClass("hidden");
                                $('[name=image_id]').val(0);
                                $('#category-thumbnail').show();
                            }
                        } else {
                            throw data;
                        }
                    } catch (exceptionData){
                        let message = 'Unexpected error';
                        let frontMessage = message;
                        let stackTrace = '';
                        if(typeof(exceptionData.exceptionMessage) !== 'undefined'){
                            message = exceptionData.exceptionMessage;
                        }
                        if(typeof(exceptionData.exceptionStackTrace) !== 'undefined'){
                            stackTrace = exceptionData.exceptionStackTrace;
                        }
                        if(window.notify!==undefined) {
                            window.notify.error(frontMessage, '', {fadeOut: 0});
                        }
                        console.error('Error: ' + message + '; StackTrace: ' +  stackTrace);
                    }
                }
            });
        });

        $(".iconImageDeleteButton").click(function(){
            let clickedItem = $(this);

            let data = {
                action : "ajax",
                type : "removeIcon",
                module : categoryModule,
                id : clickedItem.data("id"),
                domain_id : <?=SELECTED_DOMAIN_ID?>
            };

            $.post('<?=DEFAULT_URL?>/includes/code/CategoryActionAjax.php', data).done(function (response) {
                if (response) {
                    try{
                        let data = JSON.parse(response);
                        if( typeof(data.exception) === 'undefined' ) {

                            if (data && data.status) {
                                $("#icon-thumb").find("img").fadeOut(function(){
                                    $(this).remove()
                                });
                                clickedItem.addClass("hidden");
                                $('[name=icon_id]').val(0);
                                $('#category-icon').show();
                            }
                        } else {
                            throw data;
                        }
                    } catch (exceptionData) {
                        let message = 'Unexpected error';
                        let frontMessage = message;
                        let stackTrace = '';
                        if(typeof(exceptionData.exceptionMessage) !== 'undefined'){
                            message = exceptionData.exceptionMessage;
                        }
                        if(typeof(exceptionData.exceptionStackTrace) !== 'undefined'){
                            stackTrace = exceptionData.exceptionStackTrace;
                        }
                        if(window.notify!==undefined) {
                            window.notify.error(frontMessage, '', {fadeOut: 0});
                        }
                        console.error('Error: ' + message + '; StackTrace: ' +  stackTrace);
                    }
                }
            });
        });

        let loadSubCategoriesEvent = function() {
            let el = $(this);

            let elParent = el.parent();

            let categorieBlock = el.closest('.categories-block');
            let subCategorie = categorieBlock.next('.categories-sub-block');
            let selectParent = elParent.data('selectparent');

            subCategorie.slideToggle();
            el.toggleClass('is-active');

            loadSubCategories(el, elParent, selectParent);
        };

        $('.categories-block:not(.is-last) .categories-name').on('click', loadSubCategoriesEvent);

        $('.categories-dropdown-button').on('click', function(){
            $(this).next('.categories-dropdown-block').fadeToggle();
        });

        let dropDownCategoryOpen = false;

        let loadParentCategoriesEvent = function() {
            $('.button-browse-categories').off('click', loadParentCategoriesEvent);
            <?php if(string_strpos($_SERVER['PHP_SELF'], '/order_') !== false) { ?>
            $('.sponsors-category-dropdown').fadeToggle();
            <?php } else { ?>
            $('.sitemgr-category-dropdown').fadeToggle();
            <?php } ?>
            dropDownCategoryOpen = true;

            let categoriesContainer = $('#categories-container');

            let onlyParents = $(this).data('onlyparents');

            let selectParents;

            selectParents = onlyParents;

            loadParentCategories(categoriesContainer, selectParents, onlyParents);
        };

        let loadParentCategoriesModalEvent = function() {
            $('.open-create-categories').off('click', loadParentCategoriesModalEvent);
            const ref = $(this).data('ref');

            $(this).toggleClass('is-open');
            $('#'+ref).slideToggle();

            let categoriesContainer = $('#categories-wrap');

            loadParentCategories(categoriesContainer, true);
        };

        $('.button-browse-categories').on('click', loadParentCategoriesEvent);

        $(document).click(function(e){
            if (dropDownCategoryOpen && !$(e.target).closest('.custom-members-category').length) {
                <?php if(string_strpos($_SERVER['PHP_SELF'], '/order_') !== false) { ?>
                $('.sponsors-category-dropdown').fadeOut();
                <?php } else { ?>
                $('.sitemgr-category-dropdown').fadeOut();
                <?php } ?>
                dropDownCategoryOpen = false;
            }
        });

        $('.custom-input-tree').on('click', function(){
            $(this).next('.categories-tree-new').slideToggle();
        });

        $('.categories-tree-new .btn-add').on('click', function () {
            let value = $(this).parent().prev().html();
            $('.custom-input-tree').val(value);
            $('.categories-tree-new').slideUp();
            $('.categories-tree-new .categories-sub-block').slideUp();
        });

        let typingTimer;
        let doneTypingInterval = 500;

        $('.open-create-categories').on('click', loadParentCategoriesModalEvent);

        $('.advanced-options-title').on('click', function(){
            $(this).toggleClass('is-open');
            $(this).next().slideToggle();
        });

        let modalAddCategoryEvent = function() {
            let elParent = $(this).parent();

            let itemText = elParent.text().trim();
            let itemId = elParent.data('id');

            $('#modal-category-search').hide();

            $('#parentCategory').empty().show().append('<div class="input-categories-item">' + itemText +
                ' <span class="remove-categories-item" data-id="' + itemId+ '">' +
                '<i class="fa fa-close">' +
                '</i>' +
                '</span>' +
                '</div>');

            $('.open-create-categories[data-ref="categories-wrap"]').removeClass('is-open').addClass('is-disabled');

            let categoriesContainer = $('#categories-wrap');
            categoriesContainer.empty();
            categoriesContainer.removeClass('loaded');
            categoriesContainer.slideToggle();
            $('#parent_id').val(itemId);

            $('#parentCategory .remove-categories-item').on('click', modalRemoveCategoryEvent);
        };

        $('#modal-create-categories .addCategory').on('click', modalAddCategoryEvent);

        let addCategoryEvent = function () {
            let elParent = $(this).parent();

            let itemText = elParent.text().trim();
            let itemId = elParent.data('id');
            let categories = $('#categories');
            let returnCategories = $('input[name="return_categories"]');
            if($('#categories').find("[data-id='all']").parent().length>0){
                $('#categories').find("[data-id='all']").parent().remove()
            }
            categories.append('<div class="input-categories-item">' + itemText +
                ' <span class="remove-categories-item" data-id="' + itemId+ '">' +
                '<i class="fa fa-close">' +
                '</i>' +
                '</span>' +
                '</div>');

            $(this).parent().append('<a href="javascript:void(0)" class="removeCategory" data-id="' + $(this).parent().data('id') + '">' +
                '<i class="fa fa-minus"></i>' +
                '</a>');

            let categoriesContainerRemoveCategory = $('#categories-container .removeCategory');

            categoriesContainerRemoveCategory.off('click', removeCategoryEvent);
            categoriesContainerRemoveCategory.on('click', removeCategoryEvent);

            $(this).remove();

            let categoriesArray;

            if(returnCategories.val()) {
                categoriesArray = returnCategories.val().split(',');
            } else {
                categoriesArray = [];
            }

            if(!categoriesArray.includes(itemId)) {
                categoriesArray.push(itemId);
            }

            returnCategories.val(categoriesArray.join());

            $('#categories .remove-categories-item, #categories-container .removeCategory').on('click', removeCategoryEvent);

            <?php if(!$isListingTemplate) { ?>
            if(categoriesArray.length >= parseInt(categoryLimit)) {
                let categoriesListBlock = $('.categories-list-block');

                let categoriesContainer = $('#categories-container');

                categoriesContainer.empty();
                categoriesContainer.removeClass('loaded');

                categoriesListBlock.fadeToggle();
                $('#limitAlert').show();
                $('#browse-categories').addClass('is-disabled');
                $('.button-browse-categories').off('click', loadParentCategoriesEvent);
            }
            <?php } ?>
        };

        $('#categories-container .addCategory').on('click', addCategoryEvent);

        $('#add-categories').on('click', function () {
            clearFields();
        });

        let deleteCategoryEvent = function () {
            $('#loading_ajax').fadeIn('fast');

            let data = {
                module: categoryModule,
                action: 'delete',
                id: $(this).data('id'),
                domain_id : <?=SELECTED_DOMAIN_ID?>
            };

            $.post("<?=DEFAULT_URL?>/includes/code/CategoryActionAjax.php", data).done(function (result) {
                try{
                    let data = JSON.parse(result);
                    if( typeof(data.exception) === 'undefined' ) {
                        notify.success(data.message);
                        resetCategoryContainer();
                    } else {
                        throw data;
                    }
                } catch (exceptionData){
                    let message = 'Unexpected error';
                    let frontMessage = message;
                    let stackTrace = '';
                    if(typeof(exceptionData.exceptionMessage) !== 'undefined'){
                        message = exceptionData.exceptionMessage;
                    }
                    if(typeof(exceptionData.exceptionStackTrace) !== 'undefined'){
                        stackTrace = exceptionData.exceptionStackTrace;
                    }
                    if(window.notify!==undefined) {
                        window.notify.error(frontMessage, '', {fadeOut: 0});
                    }
                    console.error('Error: ' + message + '; StackTrace: ' +  stackTrace);
                }
                $('#loading_ajax').fadeOut('fast');
                $('#modal-delete').modal('hide');
            });
        };
        let openModalDeleteCategory = function () {
            $('.deleteCategory').data('id',$(this).data('id'));
            $('#modal-delete').modal('show');
        };
        $('.deleteCategory').on('click', deleteCategoryEvent);
        $('.deleteModal').on('click', openModalDeleteCategory);
        let editCategoryEvent = function () {
            clearFields();

            $('#loading_ajax').fadeIn('fast');
            if($('.categories-list-header').length > 0 && !$('.categories-list-header').hasClass('hide')){
                $('.categories-list-header').addClass('hide');
            }
            let modal = $('#modal-create-categories');

            let data = {
                module: categoryModule,
                action: 'retrieve',
                id: $(this).data('id'),
                domain_id : <?=SELECTED_DOMAIN_ID?>
            };

            let level = $(this).data('tree');

            $.get("<?=DEFAULT_URL?>/includes/code/CategoryGetAjax.php", data).done(function (result) {
                try{
                    let data = JSON.parse(result);
                    if( typeof(data.exception) === 'undefined' ) {
                        $('#category_id').val(data.id);
                        modal.find('input[name="title"]').val(data.title);
                        modal.find('input[name="page_title"]').val(data.page_title);
                        $('#btn-delete-image').attr('data-id', data.id);
                        $('#btn-delete-icon').attr('data-id', data.id);

                        if(level !== 0) {
                            modal.find('[data-featured]').hide();
                            modal.find('#thumbnail').hide();
                            modal.find('#icon').hide();
                        }

                        if(data.keywords) {
                            var words = data.keywords.split(",");
                            for (var i=0;i<words.length;i++){
                                modal.find('input[name="keywords"]')[0].selectize.addOption({
                                    value: words[i],
                                    text: words[i]
                                });
                                modal.find('input[name="keywords"]')[0].selectize.addItem(words[i]);
                            }
                        }

                        if(data.seo_keywords) {
                            var words = data.seo_keywords.split(",");
                            for (var i=0;i<words.length;i++){
                                modal.find('input[name="seo_keywords"]')[0].selectize.addOption({
                                    value: words[i],
                                    text: words[i]
                                });
                                modal.find('input[name="seo_keywords"]')[0].selectize.addItem(words[i]);
                            }
                        }
                        modal.find('textarea[name="seo_description"]').val(data.seo_description);
                        modal.find('input[name="friendly_url"]').val(data.friendly_url);

                        if(data.enabled === 'n') {
                            modal.find('input[name="clickToDisable"]').prop("checked", true);
                        }

                        if(data.featured === 'y') {
                            modal.find('input[name="featured"]').prop("checked", true);
                        }

                        if(data.category_id) {
                            $('#modal-category-search').hide();
                            modal.find('input[name="category_id"]').val(data.category_id);
                            let categoryName = $('.categories-block[data-id="'+ data.category_id + '"]').find('span').text();
                            $('#parentCategory').empty().show().append('<div class="input-categories-item">' + categoryName +
                                ' <span class="remove-categories-item" data-id="' + data.category_id+ '">' +
                                '<i class="fa fa-close">' +
                                '</i>' +
                                '</span>' +
                                '</div>');

                            $('#parentCategory .remove-categories-item').on('click', modalRemoveCategoryEvent);

                            $('.open-create-categories[data-ref="categories-wrap"]').removeClass('is-open').addClass('is-disabled');
                        }

                        if(data.image !== undefined) {
                            $("#image-thumb").hide().fadeIn('slow').html("<input type=hidden name=\"image_id\" value=\"" + data.image.id + "\"' >" +
                                "<img class=\"img-responsive\" src=\"" + data.image.url + "\">");
                            $("#btn-delete-image").removeClass("hidden");
                            $("#category-thumbnail").hide();
                        } else {
                            $("#image-thumb").hide();
                            $("#btn-delete-image").addClass("hidden");
                            $("#category-thumbnail").show();
                        }

                        if(data.icon !== undefined) {
                            $("#icon-thumb").hide().fadeIn('slow').html("<input type=hidden name=\"icon_id\" value=\"" + data.icon.id + "\"' >" +
                                "<img class=\"img-responsive\" src=\"" + data.icon.url + "\">");
                            $("#btn-delete-icon").removeClass("hidden");
                            $("#category-icon").hide();
                        } else {
                            $("#icon-thumb").hide();
                            $("#btn-delete-icon").addClass("hidden");
                            $("#category-icon").show();
                        }

                        if(CKEDITOR !== undefined) {
                            CKEDITOR.instances['category_content'].setData(data.content);
                        }
                    } else {
                        throw data;
                    }
                    $('#loading_ajax').fadeOut('fast');
                    modal.modal('show');
                } catch (exceptionData){
                    let message = 'Unexpected error';
                    let frontMessage = message;
                    let stackTrace = '';
                    if(typeof(exceptionData.exceptionMessage) !== 'undefined'){
                        message = exceptionData.exceptionMessage;
                    }
                    if(typeof(exceptionData.exceptionStackTrace) !== 'undefined'){
                        stackTrace = exceptionData.exceptionStackTrace;
                    }
                    if(window.notify!==undefined) {
                        window.notify.error(frontMessage, '', {fadeOut: 0});
                    }
                    $('#loading_ajax').fadeOut('fast');
                    console.error('Error: ' + message + '; StackTrace: ' +  stackTrace);
                }
            });
        };

        $('.editCategory').on('click', editCategoryEvent);

        let addSubCategoryEvent = function() {
            clearFields();

            let modal = $('#modal-create-categories');

            let parentId = $(this).data('id');
            let parentTitle = $(this).data('title');

            $('#modal-category-search').hide();
            modal.find('input[name="category_id"]').val(parentId);
            $('#parentCategory').empty().show().append('<div class="input-categories-item">' + parentTitle +
                '<span class="remove-categories-item" data-id="' + parentId + '">' +
                '<i class="fa fa-close">' +
                '</i>' +
                '</span>' +
                '</div>');

            $('#parentCategory .remove-categories-item').on('click', modalRemoveCategoryEvent);

            $('.open-create-categories[data-ref="categories-wrap"]').removeClass('is-open').addClass('is-disabled');

            modal.modal('show');
        };

        $('.addSubCategory').on('click', addSubCategoryEvent);

        let removeCategoryEvent = function() {
            let itemId = $(this).data('id');

            let removeCategoryItem = $('.remove-categories-item[data-id="' + itemId + '"]');

            if(itemId !== undefined) {
                let returnCategories = $('input[name="return_categories"]');

                let categoriesArray = returnCategories.val().split(',');

                if (!categoriesArray.includes(itemId)) {
                    categoriesArray = categoriesArray.filter(function (value) {
                        return parseInt(value) !== parseInt(itemId);
                    });
                }

                returnCategories.val(categoriesArray.join());

                removeCategoryItem.parent().remove();

                <?php if(!$isListingTemplate) { ?>
                if(categoriesArray.length < parseInt(categoryLimit)) {
                    $('#limitAlert').hide();
                    $('#browse-categories').removeClass('is-disabled');

                    let buttonBrowseCategories = $('.button-browse-categories');
                    buttonBrowseCategories.off('click', loadParentCategoriesEvent);
                    buttonBrowseCategories.on('click', loadParentCategoriesEvent);
                }
                <?php } ?>
            } else {
                $('.remove-categories-item').parent().remove();
            }

            let categoriesContainerRemoveCategory = $('#categories-container .removeCategory[data-id="' + itemId + '"]');

            categoriesContainerRemoveCategory.parent().append('<a href="javascript:void(0)" class="addCategory">' +
                '<i class="fa fa-plus"></i>' +
                '</a>');

            let categoriesContainerAddCategory = $('#categories-container .addCategory');

            categoriesContainerAddCategory.off('click', addCategoryEvent);
            categoriesContainerAddCategory.on('click', addCategoryEvent);

            categoriesContainerRemoveCategory.remove();
            removeCategoryItem.remove();
        };

        $('#categories .remove-categories-item, #categories-container .removeCategory').on('click', removeCategoryEvent);

        let modalRemoveCategoryEvent = function() {
            let itemId = $(this).data('id');

            if(itemId !== undefined) {
                let parentId = $('#parent_id');

                parentId.val('');
            }

            $('.open-create-categories[data-ref="categories-wrap"]').removeClass('is-open').addClass('is-disabled');

            let categoriesContainer = $('#categories-wrap');

            if(categoriesContainer.is(':visible')) {
                categoriesContainer.slideToggle();
                categoriesContainer.empty();
                categoriesContainer.removeClass('loaded');
            }

            $('.remove-categories-item[data-id="' + itemId + '"]').parent().remove();

            $('#parentCategory').hide();
            let modalCategorySearch = $('#modal-category-search');
            modalCategorySearch.val('');
            modalCategorySearch.show();

            $('.open-create-categories[data-ref="categories-wrap"]').removeClass('is-disabled');
        };

        $('#add-all').on('click', function () {
            let categories = $('#categories');

            categories.empty();

            $('input[name="return_categories"]').val('');

            categories.append('<div class="input-categories-item">' +
                LANG_ALL +
                ' <span class="remove-categories-item" data-id="all">' +
                '<i class="fa fa-close">' +
                '</i>' +
                '</span>' +
                '</div>');

            let categoriesContainer = $('#categories-container');
            let categoriesListBlock = $('.categories-list-block');
            if(categoriesListBlock.is(':visible')) {
                categoriesListBlock.fadeToggle();
                categoriesContainer.empty();
                categoriesContainer.removeClass('loaded');
            }

            $('#categories .remove-categories-item').on('click', removeCategoryEvent);
        });

        $('#modal-create-categories .remove-categories-item').on('click', function () {
            $('#parent_id').val('');

            $(this).parent().remove();

            $('#parentCategory').hide();
            $('#modal-category-search').show();

            $('.open-create-categories[data-ref="categories-wrap"]').removeClass('is-disabled');
        });

        $('#saveCategory').on('click', function () {
            let form = $('#category');

            if(validate(form)) {
                let data = form.serializeArray();
                let button = $(this);

                if(CKEDITOR !== undefined) {
                    let content = CKEDITOR.instances['category_content'].getData();
                    data.push({name: 'content', value: content});
                }

                let categoryModule = $('#module').val();
                let listingTemplate = 0;
                if(categoryModule === 'listing') {
                    if($('#listingtemplate_id').val() !== undefined) {
                        listingTemplate = $('#listingtemplate_id').val();
                    } else if($('#listingTemplate').val() !== undefined) {
                        listingTemplate = $('#listingTemplate').val();
                    }
                }

                data.push({name: 'action', value: 'save'});
                data.push({name: 'module', value: categoryModule});
                data.push({name: 'table_category', value: categoryModule.charAt(0).toUpperCase() + categoryModule.slice(1)+"Category"});
                data.push({name: 'listingTemplate', value: listingTemplate});
                data.push({name: 'domain_id', value: <?=SELECTED_DOMAIN_ID?>});

                $.post('<?=DEFAULT_URL?>/includes/code/CategoryActionAjax.php', data).done(function (response) {
                    if (response) {
                        data = JSON.parse(response);

                        $(this).removeClass('disabled');
                        $(this).text($(this).data('content'));

                        if (data && data.status) {
                            $('#modal-create-categories').modal('hide');
                            notify.success(data.message);
                            clearFields();
                            button.removeClass('disabled');
                            button.text($(this).data('content'));

                            if($("#manageCategory").length) {
                                resetCategoryContainer();
                            }
                            if($('input[name="return_categories"]').length > 0){
                                let returnCategories = $('input[name="return_categories"]');
                                let categoriesArray = returnCategories.val().split(',');
                                <?php if(!$isListingTemplate) { ?>
                                if(categoriesArray.length<=parseInt(categoryLimit)-1) {
                                    if($('#categories').length){

                                        if($('#categories').find("[data-id='all']").parent().length>0){
                                            $('#categories').find("[data-id='all']").parent().remove()
                                        }
                                        var categories = $('#categories');
                                        categories.append('<div class="input-categories-item">' + data.name +
                                            ' <span class="remove-categories-item" data-id="' + data.id+ '">' +
                                            '<i class="fa fa-close">' +
                                            '</i>' +
                                            '</span>' +
                                            '</div>');
                                        $('#categories-container .addCategory').on('click', addCategoryEvent);

                                        let returnCategories = $('input[name="return_categories"]');
                                        let categoriesArray;

                                        if(returnCategories.val()) {
                                            categoriesArray = returnCategories.val().split(',');
                                        } else {
                                            categoriesArray = [];
                                        }

                                        if(!categoriesArray.includes(data.id)) {
                                            categoriesArray.push(data.id);
                                        }

                                        returnCategories.val(categoriesArray.join());

                                        $('#categories .remove-categories-item, #categories-container .removeCategory').on('click', removeCategoryEvent);

                                        if(categoriesArray.length >= parseInt(categoryLimit)){
                                            let categoriesListBlock = $('.categories-list-block');

                                            let categoriesContainer = $('#categories-container');

                                            categoriesContainer.empty();
                                            categoriesContainer.removeClass('loaded');

                                            $('#limitAlert').show();
                                            $('#browse-categories').addClass('is-disabled');
                                            $('.button-browse-categories').off('click', loadParentCategoriesEvent);
                                        }
                                    }
                                }
                                <?php } ?>
                            }
                        } else {
                            notify.error(data.message, '', { fadeOut: 0 });
                        }

                        btn = $('.action-save');
                        btn.button('reset');
                    }
                });
            } else {
                let categoryModal = $('#modal-create-categories');

                $(categoryModal).animate({
                    scrollTop: $("input.has-error").offset().top
                }, 500);
                btn = $('.action-save');
                btn.button('reset');
            }
        });

        let doneTypingEvent = function () {
            clearTimeout(typingTimer);
            let categorySearch = $('.category-search:visible');
            let value = categorySearch.val();
            let reference = categorySearch.data('ref');
            let selectParent = categorySearch.data('selectparent');

            $('.button-browse-categories').off('click', loadParentCategoriesEvent);

            typingTimer = setTimeout(function () {
                doneTyping(value, reference, selectParent)
            }, doneTypingInterval);
        };

        $('.category-search').on('keyup', doneTypingEvent);

        function loadSubCategories(el, elParent, selectParent) {
            if(el.hasClass('is-active') && !el.hasClass('loaded')) {
                $(elParent).after('<div class="categories-sub-block" is-active="true"></div>');

                $(elParent).next('.categories-sub-block').append('<div class="categories-block" is-loading="true" id="loading-item">' +
                    '                     <img src="/sitemgr/assets/img/loading-32.gif">' +
                    '                 </div>');

                let categoryModule = $('#module').val();

                let categories;

                if(selectParent) {
                    categories = [];
                    categories.push($('#parent_id').val());
                } else if($('input[name="return_categories"]').val()) {
                    categories = $('input[name="return_categories"]').val().split(',');
                } else {
                    categories = [];
                }

                let data = {
                    module: categoryModule,
                    action: 'load',
                    level: (parseInt(elParent.attr('tree')) + 1),
                    id: elParent.data('id'),
                    categories: categories,
                    domain_id : <?=SELECTED_DOMAIN_ID?>
                };

                if(selectParent) {
                    data.selectParent = 'true';
                }

                if(elParent.data('managecategories')) {
                    data.manageCategories = 'true';
                }

                $.get('<?=DEFAULT_URL?>/includes/code/CategoryGetAjax.php', data).done(function (data) {
                    let objData = jQuery.parseJSON(data);

                    $('#loading-item').remove();

                    if (objData.success === 'true') {
                        $(elParent).next('.categories-sub-block').append(objData.template);
                        el.addClass('loaded');

                        registerEvents();
                    }
                });
            }
        }

        function loadParentCategories(categoriesContainer, selectParent = false, onlyParents = false, manageCategories = false) {
            if(!$(categoriesContainer).hasClass('loaded')) {
                $(categoriesContainer).append('<div class="categories-item" id="loading-item">' +
                    '          <div class="categories-block" is-loading="true">' +
                    '              <img src="/sitemgr/assets/img/loading-32.gif">' +
                    '          </div>' +
                    '      </div>');

                let categoryModule = $('#module').val();
                let listingTemplate = 0;
                if(categoryModule === 'listing') {
                    if($('#listingtemplate_id').val() !== undefined) {
                        listingTemplate = $('#listingtemplate_id').val();
                    } else if($('#listingTemplate').val() !== undefined) {
                        listingTemplate = $('#listingTemplate').val();
                    }
                }

                let categories;

                if(selectParent || onlyParents) {
                    categories = [];
                    $('#categories').find($(".remove-categories-item")).each(function (index) {
                        categories.push(($( this ).data('id')))
                    })
                } else if($('input[name="return_categories"]').val()) {
                    categories = $('input[name="return_categories"]').val().split(',');
                } else {
                    categories = [];
                }
                let data = {
                    module: categoryModule,
                    action: 'load',
                    listingTemplate: listingTemplate,
                    categories: categories,
                    domain_id : <?=SELECTED_DOMAIN_ID?>
                };

                if(selectParent) {
                    data.selectParent = 'true';
                }

                if(onlyParents) {
                    data.onlyParents = 'true';
                }

                if(manageCategories) {
                    data.manageCategories = 'true';
                }

                $.get('<?=DEFAULT_URL?>/includes/code/CategoryGetAjax.php', data).done(function (data) {
                    if(!selectParent || onlyParents) {
                        $('.button-browse-categories').on('click', loadParentCategoriesEvent);
                    } else {
                        $('.open-create-categories').on('click', loadParentCategoriesModalEvent);
                    }

                    let objData = jQuery.parseJSON(data);

                    $('#loading-item').remove();

                    if (objData.success === 'true') {
                        $(categoriesContainer).append(objData.template);

                        $(categoriesContainer).addClass('loaded');

                        registerEvents();
                    }
                });
            } else {
                if(!selectParent || onlyParents) {
                    $('.button-browse-categories').on('click', loadParentCategoriesEvent);
                } else {
                    $('.open-create-categories').on('click', loadParentCategoriesModalEvent);
                }
            }
        }

        function searchCategoryByTerm(categoriesContainer, val, selectParent) {
            let categorySearch = $('.category-search');

            let buttonBrowseCategories = $('.button-browse-categories');

            buttonBrowseCategories.off('click', loadParentCategoriesEvent);

            $(categoriesContainer).append('<div class="categories-item" id="loading-item">' +
                '          <div class="categories-block" is-loading="true">' +
                '              <img src="/sitemgr/assets/img/loading-32.gif">' +
                '          </div>' +
                '      </div>');

            let keyupfunctSearchCategoryByTerm = function () {
                categorySearch.off('keyup', keyupfunctSearchCategoryByTerm);
                categorySearch.on('keyup', doneTypingEvent);
                xhrSearchCategoryByTerm.abort();
                doneTypingEvent();
            };

            let categoryModule = $('#module').val();
            let listingTemplate = 0;
            if (categoryModule === 'listing') {
                if($('#listingtemplate_id').val() !== undefined) {
                    listingTemplate = $('#listingtemplate_id').val();
                } else if($('#listingTemplate').val() !== undefined) {
                    listingTemplate = $('#listingTemplate').val();
                }
            }

            let categories;

            if(selectParent) {
                categories = [];
                categories.push($('#parent_id').val());
            } else if($('input[name="return_categories"]').val()) {
                categories = $('input[name="return_categories"]').val().split(',');
            } else {
                categories = [];
            }

            let data = {
                module: categoryModule,
                action: 'search',
                term: val,
                listingTemplate: listingTemplate,
                categories: categories,
                domain_id : <?=SELECTED_DOMAIN_ID?>
            };

            if(selectParent) {
                data.selectParent = 'true';
            }

            let xhrSearchCategoryByTerm = $.get('<?=DEFAULT_URL?>/includes/code/CategoryGetAjax.php', data).done(function (data) {
                categorySearch.off('keyup', keyupfunctSearchCategoryByTerm);

                let objData = jQuery.parseJSON(data);

                $('#loading-item').remove();

                if (objData.success === 'true') {
                    $(categoriesContainer).append(objData.template);

                    $(categoriesContainer).addClass('loaded');

                    let categoriesContainerAddCategory = $('#categories-container .addCategory');
                    let categoriesWrapAddCategory = $('#categories-wrap .addCategory');

                    categoriesContainerAddCategory.off('click', addCategoryEvent);
                    categoriesContainerAddCategory.on('click', addCategoryEvent);
                    categoriesWrapAddCategory.off('click', modalAddCategoryEvent);
                    categoriesWrapAddCategory.on('click', modalAddCategoryEvent);
                }

                categorySearch.on('keyup', doneTypingEvent);
            });

            categorySearch.off('keyup', doneTypingEvent);
            categorySearch.on('keyup', keyupfunctSearchCategoryByTerm);
            buttonBrowseCategories.on('click', loadParentCategoriesEvent);
        }

        function doneTyping(val, reference, selectParent) {
            let categoriesContainer = $('#' + reference);

            categoriesContainer.empty();

            categoriesContainer.removeClass('loaded');

            if(reference === 'categories-wrap' && !categoriesContainer.is(":visible")) {
                categoriesContainer.slideToggle();
            }

            if(val === '') {
                loadParentCategories(categoriesContainer, selectParent);
            } else {
                searchCategoryByTerm(categoriesContainer, val, selectParent);
            }
        }

        function resetCategoryContainer() {
            let categoriesContainer = $('#categoryContainer');

            categoriesContainer.empty();
            categoriesContainer.removeClass('loaded');

            loadParentCategories(categoriesContainer, false, false, true);

            $('#loading_ajax').fadeOut('fast');
        }

        function registerEvents() {
            //Load sub categories event
            let categoriesName = $('.categories-block:not(.is-last) .categories-name');
            categoriesName.off('click', loadSubCategoriesEvent);
            categoriesName.on('click', loadSubCategoriesEvent);

            //Add category events
            let categoriesContainerAddCategory = $('#categories-container .addCategory');
            categoriesContainerAddCategory.off('click', addCategoryEvent);
            categoriesContainerAddCategory.on('click', addCategoryEvent);

            let modalCreateCategoriesAddCategory = $('#modal-create-categories .addCategory');
            modalCreateCategoriesAddCategory.off('click', modalAddCategoryEvent);
            modalCreateCategoriesAddCategory.on('click', modalAddCategoryEvent);

            //Remove category events
            let removeCategory = $('#categories .remove-categories-item, #categories-container .removeCategory');
            removeCategory.off('click', removeCategoryEvent);
            removeCategory.on('click', removeCategoryEvent);

            let modalRemoveCategory = $('#categories-wrap .removeCategory');
            modalRemoveCategory.off('click', modalRemoveCategoryEvent);
            modalRemoveCategory.on('click', modalRemoveCategoryEvent);

            //Delete category event
            let deleteCategory = $('.deleteCategory');
            deleteCategory.off('click', deleteCategoryEvent);
            deleteCategory.on('click', deleteCategoryEvent);

            //show modal event
            let modalDeleteCategory = $('.deleteModal');
            modalDeleteCategory.off('click', openModalDeleteCategory);
            modalDeleteCategory.on('click', openModalDeleteCategory);

            //Edit category event
            let editCategory = $('.editCategory');
            editCategory.off('click', editCategoryEvent);
            editCategory.on('click', editCategoryEvent);

            //Add sub category event
            $('.addSubCategory').on('click', addSubCategoryEvent);
        }
        if($('input[name="return_categories"]').length > 0){
            let returnCategories = $('input[name="return_categories"]');
            let categoriesArray;
            if(returnCategories.val()) {
                categoriesArray = returnCategories.val().split(',');
            } else {
                categoriesArray = [];
            }
            <?php if(!$isListingTemplate) { ?>
            if(categoriesArray.length >= parseInt(categoryLimit)) {
                let categoriesListBlock = $('.categories-list-block');

                let categoriesContainer = $('#categories-container');

                categoriesContainer.empty();
                categoriesContainer.removeClass('loaded');

                $('#limitAlert').show();
                $('#browse-categories').addClass('is-disabled');
                $('.button-browse-categories').off('click', loadParentCategoriesEvent);
            }
            <?php } ?>
        }
    });

    function validate(form) {
        let isValid = true;
        let requiredInputs = form[0].querySelectorAll('[required]');

        for (let i = 0; i < requiredInputs.length; i++) {
            let input = requiredInputs[i];
            let id = input.id;
            let $input = $('#' + id);
            let value = input.value.trim();
            let hide = $input.parents('.form-group').hasClass('hide');

            if (!hide && (value === null || value === '')) {
                if ($input.parents('.selectize-input').length > 0) {
                    $input.parents('.selectize-input').addClass('has-error');
                } else if ($input.siblings('.selectize-control').length > 0) {
                    $input.siblings('.selectize-control').find('.selectize-input').addClass('has-error');
                } else {
                    $input.addClass('has-error');
                }

                isValid = false;
                continue;
            }

            if ($input.parents('.selectize-input').length > 0) {
                $input.parents('.selectize-input').removeClass('has-error');
            } else if ($input.siblings('.selectize-control').length > 0) {
                $input.siblings('.selectize-control').find('.selectize-input').removeClass('has-error');
            } else {
                $input.removeClass('has-error');
            }
        }

        return isValid;
    }

    function clearFields() {
        let categoryModal = $('#modal-create-categories');

        categoryModal.find('#saveCategory').text($('#saveCategory').data('content'));

        categoryModal.find('input[name="title"]').val('');
        categoryModal.find('#parent_id').val('');
        categoryModal.find('#category_id').val('');
        categoryModal.find('input[name="page_title"]').val('');
        categoryModal.find('input[name="friendly_url"]').val('');

        let keywords = categoryModal.find('input[name="keywords"]');

        if(keywords[0] !== undefined) {
            keywords[0].selectize.clear();
        }

        let seoKeywords = categoryModal.find('input[name="seo_keywords"]');

        if(seoKeywords[0] !== undefined) {
            seoKeywords[0].selectize.clear();
        }
        categoryModal.find('textarea[name="seo_description"]').val('');

        categoryModal.find("#image-thumb").find("img").fadeOut(function(){
            $(this).remove();
        });
        categoryModal.find('.categoryImageDeleteButton').addClass("hidden");
        categoryModal.find('[name=image_id]').val(0);

        categoryModal.find("#icon-thumb").find("img").fadeOut(function(){
            $(this).remove();
        });
        categoryModal.find('.iconImageDeleteButton').addClass("hidden");
        categoryModal.find('[name=icon_id]').val(0);
        categoryModal.find('#category-icon').show();

        categoryModal.find('#category-thumbnail').show();

        let clickToDisable = categoryModal.find('input[name="clickToDisable"]');

        if(clickToDisable.is(':checked')) {
            clickToDisable.prop("checked", false);
        }

        let featured = categoryModal.find('input[name="featured"]');

        if(featured.is(':checked')) {
            featured.prop("checked", false);
        }

        let advancedOptions = $('.advanced-options-title');

        if(advancedOptions.hasClass('is-open')) {
            advancedOptions.toggleClass('is-open');
            advancedOptions.next().slideToggle();
        }

        if(CKEDITOR !== undefined) {
            CKEDITOR.instances['category_content'].setData('');
        }

        let modalCategorySearch = $('#modal-category-search');
        modalCategorySearch.val('');
        modalCategorySearch.removeClass('is-disabled');
        modalCategorySearch.show();

        let parentCategory = $('#parentCategory');
        parentCategory.empty();
        parentCategory.hide();

        let categoriesWrap = $('#categories-wrap');
        categoriesWrap.empty();
        categoriesWrap.removeClass('loaded');

        let openCategories = $('.open-create-categories[data-ref="categories-wrap"]');

        openCategories.removeClass('is-disabled');

        if(openCategories.hasClass('is-open')) {
            openCategories.toggleClass('is-open');
            categoriesWrap.slideToggle();
        }

        let categoriesContainer = $('#categories-container');
        categoriesContainer.empty();
        categoriesContainer.removeClass('loaded');

        categoryModal.find('[data-featured]').show();
        categoryModal.find('#thumbnail').show();
        categoryModal.find('#icon').show();
        if($('.categories-list-header').length > 0 && $('.categories-list-header').hasClass('hide')){
            $('.categories-list-header').removeClass('hide');
        }
        if($('#category').length > 0){
            let form = $('#category');
            let requiredInputs = form[0].querySelectorAll('[required]');
            for (let i = 0; i < requiredInputs.length; i++) {
                let input = requiredInputs[i];
                let id = input.id;
                let $input = $('#' + id);
                if ($input.parents('.selectize-input').length > 0 && $input.parents('.selectize-input').hasClass('has-error')) {
                    $input.parents('.selectize-input').removeClass('has-error');
                } else if ($input.siblings('.selectize-control').length > 0 && $input.siblings('.selectize-control').find('.selectize-input').hasClass('has-error')) {
                    $input.siblings('.selectize-control').find('.selectize-input').removeClass('has-error');
                } else if($input.hasClass('has-error')){
                    $input.removeClass('has-error');
                }
            }
        }
    }

    function sendCategoryImage(form_id, path, action) {

        let returnMessage = $("#returnMessage");
        let fileInput = $("#"+form_id+' input[type="file"][name="image"]');
        let actionType = "upload";

        if(fileInput.length && fileInput.prop('files')[0].size > 2000000) {
            fileInput.val('');
            returnMessage.removeClass("alert-success");
            returnMessage.removeClass("alert-warning");
            returnMessage.addClass("alert-warning");
            returnMessage.html(LANG_JS_MAXIMUM_FILE_SIZE);
            returnMessage.show();
            return;
        }

        $("#"+form_id).vPB({
            url: "<?=DEFAULT_URL?>/includes/code/CategoryActionAjax.php?action=ajax&type=" + action + "&domain_id=" + <?=SELECTED_DOMAIN_ID?>,
            data: {
                actionType: actionType,
                type: action,
                module: $('#module').val()
            },
            success: function(response)
            {
                let strReturn = response.split("||");

                if (strReturn[0] === "ok") {
                    returnMessage.hide();
                    $("#image-thumb").hide().fadeIn('slow').html(strReturn[1]);
                    $("#btn-delete-image").removeClass("hidden");
                    $("#category-thumbnail").hide();
                } else {
                    returnMessage.removeClass("alert-success");
                    returnMessage.removeClass("alert-warning");
                    returnMessage.addClass("alert-warning");
                    returnMessage.html(strReturn[1]);
                    returnMessage.show();
                }

                let btn = $('.action-save');
                btn.button('reset');
            }
        }).submit();
    }

    function sendCategoryIcon(form_id, path, action) {

        let returnMessage = $("#returnMessage");
        let iconFile = $("#"+form_id+' input[type="file"][name="icon"]');
        let actionType = "upload";

        if(iconFile.length &&
            iconFile.prop('files')[0].size > 2000000) {
            iconFile.val('');
            returnMessage.removeClass("alert-success");
            returnMessage.removeClass("alert-warning");
            returnMessage.addClass("alert-warning");
            returnMessage.html(LANG_JS_MAXIMUM_FILE_SIZE);
            returnMessage.show();
            return;
        }

        $("#"+form_id).vPB({
            url: "<?=DEFAULT_URL?>/includes/code/CategoryActionAjax.php?action=ajax&type=" + action + "&domain_id=" + <?=SELECTED_DOMAIN_ID?>,
            data: {
                actionType: actionType,
                type: action,
                module: $('#module').val()
            },
            success: function(response)
            {
                let strReturn = response.split("||");

                if (strReturn[0] === "ok") {
                    returnMessage.hide();
                    $("#icon-thumb").hide().fadeIn('slow').html(strReturn[1]);
                    $("#btn-delete-icon").removeClass("hidden");
                    $("#category-icon").hide();
                } else {
                    returnMessage.removeClass("alert-success");
                    returnMessage.removeClass("alert-warning");
                    returnMessage.addClass("alert-warning");
                    returnMessage.html(strReturn[1]);
                    returnMessage.show();
                }

                let btn = $('.action-save');
                btn.button('reset');
            }
        }).submit();
    }
</script>
