'use strict';
$(document).ready(function () {
    //On click of the "plus" button to add a new widget
    $(document).on('click', '.widget-add-before', function () {
        $.widgetPosition = $(this).parent('div');
    });

    $(document).on('click', '.listing-tabs-item', function () {
        let tabItem = $(this).data('tab');

        $('.listing-tabs-item.is-selected').removeClass('is-selected');
        $(this).addClass('is-selected');

        $('.listing-widget-body.is-active').removeClass('is-active');
        $('#' + tabItem).addClass('is-active');
    });

    $(document).on('click', '.addListingWidget', function (e) {
        e.preventDefault();

        addListingWidgetToDom(
            $(this).data('widgetid'),
            $(this).data('section'),
            $(this).data('listingtemplateid'),
            $(this).data('tabid')
        );
    });

    //Nova estrutura de modal de adicionar widgets
    const animateCSS = (element, animation, prefix = 'animate__',callback) => {
        new Promise((resolve) => {
            const animationName = `${prefix}${animation}`;

            $(element).addClass(`${prefix}animated ${animationName}`);

            function handleAnimationEnd() {
                $(element).removeClass(`${prefix}animated ${animationName}`);
                $(element).off('animationend', handleAnimationEnd);
                if(callback){
                    callback();
                }
                resolve('Animation ended');
            }

            $(element).on('animationend', handleAnimationEnd);
        });
    };

    function alertEditModal() {
        if($("input[name='templateWidgetId']").val()!=""){
            // show modal
            if($('.alertChange').hasClass('hide')){
                $('.alertChange').removeClass('hide');
                $('.buttonSave').prop( "disabled", true );
            }
        }
    }

    $(document).off('input', '.editInformation');
    $(document).on('input', '.editInformation', function () { alertEditModal();});
    $(document).off('click', '.showAlert');
    $(document).on('click', '.showAlert', function () { alertEditModal();});
    $(document).on('click', '.confirm-Edit-Custom-Widget', function () {
        if($("input[name='templateWidgetId']").val()!=""){
            $('.buttonSave').prop( "disabled", false );
            $('.alert-widgets-action-button').fadeOut();
        }
    });

    $(document).on('click', '.add-template-widget-button', function () {
        let widget = $(this).attr('data-widget');
        let customWidgetSaveSelectorString = '#CustomWidgetSave';
        let button = $(customWidgetSaveSelectorString);
        let tabId = $(this).data('tabid');
        let content = $(this).data('content');

        let widgetDictionary = {
            'specialties': {
                'name': 'specialties',
                'genericInputs': false
            },
            'call-to-action': {
                'name': 'calltoaction',
                'genericInputs': true
            },
            'range': {
                'name': 'range',
                'genericInputs': true
            },
            'description': {
                'name': 'description',
                'genericInputs': true
            },
            'more-details': {
                'name': 'moredetails',
                'genericInputs': false
            },
            'linked-listings': {
                'name': 'linkedlistings',
                'genericInputs': true
            },
            'related-listings': {
                'name': 'relatedlistings',
                'genericInputs': false
            },
            'check-list': {
                'name': 'checklist',
                'genericInputs': false
            }
        };

        button.attr('data-widgetid', $(this).data('widgetid'));
        button.attr('data-section', $(this).data('section'));
        button.attr('data-tabid', tabId);
        $(document).off('click', customWidgetSaveSelectorString);
        $(document).on('click', customWidgetSaveSelectorString, function () { saveListingWidget(widgetDictionary[widget].name , widgetDictionary[widget].genericInputs, true); });

        $('#editWidgetLabel').text($(this).data('title'));
        let modalHeaderActionSelector = $('.modal-header-action');
        if(content.required){
            let title =  toTitleCase($(this).data('title')).replace(/\s+/g, '');
            title = title.charAt(0).toLowerCase() + title.slice(1);
            let toggleSelector = $('.required-toggle');
            toggleSelector.off('click');
            toggleSelector.on('click',function (){ toggleItem(this,title+'Required'); });

            if(modalHeaderActionSelector.hasClass('hide')){
                modalHeaderActionSelector.removeClass('hide');
            }
        }else{
            if(!(modalHeaderActionSelector.hasClass('hide'))){
                modalHeaderActionSelector.addClass('hide');
            }
        }
        $('.modal-header-widget-custom .back-button').attr('data-widgetRef', widget);

        $('.modal-header-default').fadeOut(function () {
            $('.modal-header-widget-custom').css('display', 'flex');
            animateCSS('.modal-header-widget-custom', 'fadeInRight');
        });

        let formWidget = $('#' + widget);

        $('.list-widget-templates').fadeOut(function () {
            formWidget.show();
            formWidget.find('.selectize > select').selectize();
            animateCSS('#' + widget, 'fadeInRight','animate__',function () {
                if(formWidget.find('[data-toggle="tooltip"]')){
                    formWidget.find('[data-toggle="tooltip"]').tooltip();
                }
            });
        });

        $('.modal-footer-default').fadeOut(function () {
            $('.modal-footer-widget-custom').show();
            animateCSS('.modal-footer-widget-custom', 'fadeInRight');
        });

        formWidget.find('input[name="tabId"]').val(tabId);
    });

    $(document).on('click', '.modal-header-widget-custom .back-button', function () {
        let widgetRef = $(this).attr('data-widgetRef');

        $('.modal-header-widget-custom').fadeOut(function () {
            $('.modal-header-default').show();
            animateCSS('.modal-header-default', 'fadeInLeft');
        });

        $('#' + widgetRef).fadeOut(function () {
            $('.list-widget-templates').show();
            animateCSS('.list-widget-templates', 'fadeInLeft');
        });

        $('.modal-footer-widget-custom').fadeOut(function () {
            $('.modal-footer-default').show();
            animateCSS('.modal-footer-default', 'fadeInLeft');
        });
    });

    /*
     * Please always use the form id (in the modal file) this way:
     * "form_" + modalArr[1]
     * modalArr[1] will be always a reference to which modal is opened
     */
    $(document).on('click', '.editListingWidgetButton', function (e) {
        e.preventDefault();

        $.widgetDivId = $(this).data('divid');

        let modalFullName = $(this).data('modal'),
            divId = $(this).data('divid'),
            tabId = $(this).data('tab'),
            editInfo = $('#' + divId + ' .edit-info');

        // REQUEST INFO
        let modalArr = modalFullName.split('-'),
            templateWidgetId = editInfo.data('templatewidget') ? editInfo.data('templatewidget') : $( '#' + divId + ' #listingTemplateListingWidgetIdInput' ).val(),
            widgetId = $(this).data('widget'),
            selectedDomainId = $('#selectedDomainId').val(),
            url = DEFAULT_URL + '/includes/code/listingWidgetGetAjax.php',
            data =
                '?templateWidgetId=' +
                templateWidgetId +
                '&modal=' +
                modalArr[1] +
                '&widgetId=' +
                widgetId +
                '&domain_id=' +
                selectedDomainId +
                '&modalFullName=' +
                modalFullName +
                '&tab=' +
                tabId +
                '&action=edit';

        $('#loading_ajax').fadeIn('fast');
        let editWidgetModalSelector = $('#edit-widget-modal');
        editWidgetModalSelector
            .show('modal')
            .load(url + data, function () {
                $('[data-toggle="tooltip"]').tooltip();
                //Set data divdid on save button
                $('#edit-widget-modal .btn-primary').data('divid', divId);
                editWidgetModalSelector
                    .find('[id^=messageAlert]')
                    .removeClass('alert-danger')
                    .hide();

                $('#openWidgetId').val(widgetId);

                //Initialize sortables
                $(function () {
                    $(document).find('#sortableNav').sortable({
                        items: '> .sortableNav',
                    });
                    $(document)
                        .find('#sortableSlider')
                        .sortable()
                        .disableSelection()
                        .off('click', '.click-area');
                    $(document)
                        .find('#sortableSlider')
                        .sortable()
                        .disableSelection()
                        .on('click', '.click-area', function () {
                            let divId = $(this).data('divid');
                            changeActiveSlide(divId);
                            hideSliderInfo();
                            showSlideInfo(divId);
                        });
                });
                let textAreaCounterSelector = $('.textarea-counter');
                if (textAreaCounterSelector.length) {
                    textAreaCounterSelector.each(function () {
                        let options = {
                            maxCharacterSize: $(this).attr('data-chars'),
                            displayFormat:
                                '<p class="help-block text-right">#left ' +
                                $(this).attr('data-msg') +
                                '</p>',
                        };
                        $(this).textareaCount(options);
                    });
                }

                editWidgetModalSelector.find('.selectize > select').selectize();

                editWidgetModalSelector.modal({ show: true });
                $('#loading_ajax').fadeOut('fast');
            });
        $.widgetDivId = undefined;
    });

    $(document).on('keyup', '#widget-search', function (e) {
        let filterValue = e.target.value.toUpperCase();

        $('.widget-list .widget-item').each(function () {
            let itemTitle = $(this).find('.widget-title').html().toUpperCase();

            if (itemTitle) {
                if (itemTitle.indexOf(filterValue) > -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            }
        });
    });

    $(document).on('click', '.resetListingTemplateButton', function (e) {
        e.preventDefault();

        $('#modal-reset-listingtemplate').modal('show');
    });

    $(document).on('click', '.removeListingWidgetButton', function (e) {
        e.preventDefault();

        let divId = $(this).data('divid');
        let tabRef = $(this).data('tabRef');
        let editInfo = $('#' + divId);
        let listingTemplateWidgetId = editInfo.data('widgetid') ? editInfo.data('widgetid') : $( '#' + divId + ' #listingTemplateListingWidgetIdInput' ).val();
        $(document).off('click', '#remove-widget-modal .confirmRemoval');
        $(document).on('click', '#remove-widget-modal .confirmRemoval', function() {
            if(listingTemplateWidgetId==""){
                listingTemplateWidgetId=null;
            }
            removeListingWidget(divId, listingTemplateWidgetId, tabRef);
        });
    });

    $(document).on('click', '.removeTabButton', function (e) {
        e.preventDefault();

        let tabId = $(this).data('id');
        $(document).off('click', '#remove-tab-modal .confirmRemoval');
        $(document).on('click', '#remove-tab-modal .confirmRemoval', function() { removeTab(tabId); });
    });

    $(document).on('click', '#listing-save', function () {
        serializeListingTemplate();
        $('#form_widgets').find('[type="submit"]').trigger('click');
    });

    $(document).on('keypress', function (e) {
        if(e.keyCode === 13){
            e.preventDefault();
            $(e.target).trigger('blur');
        }
    });

    $(document).on('click', '#new-tab', function () {
        $('#loading_ajax').fadeIn('fast');

        let data = {
            action: 'createTab',
            listingtemplate: $('#listingTemplateId').val()
        };

        $.post("/includes/code/listingTemplateActionAjax.php", data).done(function (response) {
            if (response) {
                data = JSON.parse(response);

                if (data.success) {

                    let tabId;
                    let listingTemplateId = $('#listingTemplateId').val();
                    if($('input[name="newTab"][value="true"]').length > 0) {
                        let listingTabsItemLast = $('.listing-tabs-item:last');
                        tabId = parseInt(listingTabsItemLast.find('input[name=tabId]').val()) + 1;
                    } else {
                        tabId = data.tabId;
                    }

                    let tabsWidgets = $('#tabs-widgets');
                    let selectorRef = `.listing-widget-body#tab-${tabId}`;

                    $('.is-selected.listing-tabs-item').removeClass('is-selected');
                    $('.is-active.listing-widget-body').removeClass('is-active');

                    $('.removeTabButton').show();

                    let listingTabsItemUpdated = tabsWidgets.append(
                        `
                    <li class="listing-tabs-item is-selected" data-tab="tab-${tabId}">
                        <input type="hidden" name="tabId" value="${tabId}">
                        <input type="hidden" name="newTab" value="true">
                        <input type="text" class="tabs-title" name="tabTitle" data-value="` + LANG_NEW_TAB + `" value="` + LANG_NEW_TAB + `">
                        <a href="javascript:;" data-dismiss="modal" class="tabs-remove removeTabButton"
                            data-toggle="modal"
                            data-target="#remove-tab-modal"
                            data-id="${tabId}">
                            <i class="fa fa-times"></i>
                        </a>
                    </li>`);

                    $('#tab-widgets').append(
                        `
                        <div class="listing-widget-body is-active" id="tab-${tabId}">
                            <div class="listing-widget-container main-widgets">
                                <div class="listing-widget-list">
                                </div>
                                <a href="javascript:;" data-url="` + DEFAULT_URL + `/` + SITEMGR_ALIAS + `/content/listing/template/add-main-widget.php?listingTemplateId=${listingTemplateId}&listingTemplateTabId=${tabId}" class="listing-widget-placeholder btn-new-widget">
                                    <i class="fa fa-plus-circle"></i>
                                    ` +
                            LANG_ADD_WIDGET +
                            `
                                </a>
                            </div>
                            <div class="listing-widget-container sidebar-widgets">
                                <div class="listing-widget-list">
                                </div>
                                <a href="javascript:;" data-url="` + DEFAULT_URL + `/` + SITEMGR_ALIAS + `/content/listing/template/add-sidebar-widget.php?listingTemplateId=${listingTemplateId}&listingTemplateTabId=${tabId}" class="listing-widget-placeholder btn-new-widget">
                                    <i class="fa fa-plus-circle"></i>
                                    ` +
                            LANG_ADD_WIDGET +
                            `
                                </a>
                            </div>
                        </div>
                    </div>`);

                    $('.header-widgets .listing-widget-list').sortable({
                        axis: 'y',
                        containment: '.header-widgets .listing-widget-list',
                        cursor: 'move',
                        tolerance: 'pointer',
                    });

                    $(`${selectorRef} .main-widgets .listing-widget-list:visible`).sortable({
                        axis: 'y',
                        containment: `${selectorRef} .main-widgets .listing-widget-list`,
                        cursor: 'move',
                        tolerance: 'pointer',
                    });

                    $(`${selectorRef} .sidebar-widgets .listing-widget-list:visible`).sortable({
                        axis: 'y',
                        containment: `${selectorRef} .sidebar-widgets .listing-widget-list`,
                        cursor: 'move',
                        tolerance: 'pointer',
                    });

                    tabsWidgets.sortable({
                        axis: 'x',
                        containment: '#tabs-widgets',
                        cursor: 'move',
                        tolerance: 'pointer',
                        cancel: '.tabs-title, .tabs-remove',
                    });


                    let newTab = listingTabsItemUpdated.find('.listing-tabs-item:last');
                    newTab
                        .find('input[name=tabTitle]')
                        .focus()
                        .select();
                    $('#tab-' + tabId).addClass('is-selected');
                }
            }

            $('#loading_ajax').fadeOut('fast');
        });
    });

    $(document).on('change', 'input[name=tabTitle]', function () {
        const value = $(this).val().trim();

        if (value) {
            $(this).data('value', $(this).val());
        } else {
            $(this).val($(this).data('value'));
        }
    });

    $('.header-widgets .listing-widget-list').sortable({
        axis: 'y',
        containment: '.header-widgets .listing-widget-list',
        cursor: 'move',
        tolerance: 'pointer',
    });

    $('.widget-tabs-content .listing-tabs-item').each(function(){
        let tabRefId = $(this).data('tab');
        let selectorRef = `.listing-widget-body#${tabRefId}`;

        $(`${selectorRef} .main-widgets .listing-widget-list`).sortable({
            axis: 'y',
            containment: `${selectorRef} .main-widgets .listing-widget-list`,
            cursor: 'move',
            tolerance: 'pointer',
        });

        $(`${selectorRef} .sidebar-widgets .listing-widget-list`).sortable({
            axis: 'y',
            containment: `${selectorRef} .sidebar-widgets .listing-widget-list`,
            cursor: 'move',
            tolerance: 'pointer',
        });
    });

    $('#tabs-widgets').sortable({
        axis: 'x',
        containment: '#tabs-widgets',
        cursor: 'move',
        tolerance: 'pointer',
        cancel: '.tabs-title, .tabs-remove',
    });

    $('.hide-label-section-tooltip').tooltip({
        animated: 'fade',
        placement: 'right',
        html: true,
    });
});

function removeListingWidget(divId, listingTemplateWidgetId) {

    let // REQUEST INFO
        url = DEFAULT_URL + '/includes/code/listingWidgetActionAjax.php',
        selectedDomainId = $('#selectedDomainId').val(),
        data =
            'listingTemplateWidgetId=' +
            listingTemplateWidgetId +
            '&removeWidget=1' +
            '&domain_id=' +
            selectedDomainId,
        type = 'POST',
        request;

    request = widgetActionsAjax(url, type, data, false);

    request.done(function (data) {
        let objData = jQuery.parseJSON(data);

        if (objData.success) {
            $('#' + divId).fadeOut(function () {
                $(this).remove();
            });
            let msgSuccess = objData.message,
                successAlert = $('#successAlert');

            $('#remove-widget-modal').modal('toggle');
            $(document).off('click', '#remove-widget-modal .confirmRemoval');

            if (msgSuccess) {
                notify.success(msgSuccess);
            }

            if (divId.indexOf('sidebar') > -1) {
                if ($('.listing-widget-body.is-active .listing-widget-container.sidebar-widgets .listing-widget-item').length <= 1) {
                    $('.listing-widget-container.main-widgets, .listing-widget-container.sidebar-widgets').addClass('resized-sidebar');
                }
            }

            if (divId.indexOf('header') > -1) {
                $('#addHeader').show();
            }
        }else{
            let msg = objData.errorMessage;
            $('#remove-widget-modal').modal('toggle');
            if (msg) {
                notify.error(msg, '', { fadeOut: 0 });
            }
        }
    });
}

function removeTab(tabId) {
    let // REQUEST INFO
        url = DEFAULT_URL + '/includes/code/listingTemplateActionAjax.php',
        selectedDomainId = $('#selectedDomainId').val(),
        data = 'tabId=' + tabId + '&action=removeTab' + '&domain_id=' + selectedDomainId,
        type = 'POST',
        request;

    request = widgetActionsAjax(url, type, data, false);

    request.done(function (data) {
        let objData = jQuery.parseJSON(data);

        if (objData.success) {
            $('#remove-tab-modal').modal('toggle');
            $(document).off('click', '#remove-tab-modal .confirmRemoval');

            $('.listing-tabs-item[data-tab="tab-' + tabId + '"]').remove();
            $('#tab-' + tabId).remove();

            let listingTabs = $('.listing-tabs-item');
            let firstTab = listingTabs.first();
            let removeTabButton = $('.removeTabButton[data-id="' + firstTab.find('input[name="tabId"]').val() + '"]');

            $('#' + firstTab.data('tab')).addClass('is-active');

            firstTab.addClass('is-selected');

            if(listingTabs.length === 1) {
                removeTabButton.hide();
            }

            notify.success(objData.message);
        } else {
            notify.error(objData.message);
        }
    });
}

function serializeListingTemplate() {
    let tabs = $('.listing-tabs-item'),
        headerElementsTabs = [],
        mainElementsTabs = [],
        sidebarElementsTabs = [],
        tabContent = [],
        headerContent = [],
        content = [];

    $('.header-widgets .listing-widget-list').each(function () {
        headerElementsTabs.push($(this).sortable('toArray'));
    });

    $('.main-widgets .listing-widget-list').each(function () {
        mainElementsTabs.push($(this).sortable('toArray'));
    });

    $('.sidebar-widgets .listing-widget-list').each(function () {
        sidebarElementsTabs.push($(this).sortable('toArray'));
    });

    serializeListingTab(headerElementsTabs, headerContent);

    serializeListingTab(mainElementsTabs, content);

    serializeListingTab(sidebarElementsTabs, content);

    $('#serializedPost').val(JSON.stringify(content));

    if (tabs.length > 0) {
        tabs.each(function (index, element) {
            let inputs = {};
            $(element)
                .find('input')
                .each(function () {
                    inputs[this.name] = this.value;
                });
            tabContent.push(inputs);
        });
    }

    $('#serializedTabs').val(JSON.stringify(tabContent));

    $('#serializedHeader').val(JSON.stringify(headerContent));
}

function serializeListingTab(sectionTabs, content) {
    if (sectionTabs.length > 0) {
        sectionTabs.forEach(function (sectionElements) {
            if (sectionElements.length > 0) {
                sectionElements.forEach(function (element) {
                    if (element !== '') {
                        let inputs = {};
                        $('div #' + element)
                            .find('input')
                            .each(function () {
                                inputs[this.name] = this.value;
                            });
                        content.push(inputs);
                    }
                });
            }
        });
    }
}

function addListingWidgetToDom(
    widgetId,
    section,
    listingTemplateId,
    tabId,
    content,
    templateWidgetId = false
) {
    let url = DEFAULT_URL + '/includes/code/listingWidgetGetAjax.php',
        selectedDomainId = $('#selectedDomainId').val(),
        type = 'GET',
        data =
            'widgetId=' +
            widgetId +
            '&section=' +
            section +
            '&domain_id=' +
            selectedDomainId +
            '&action=add' +
            '&listingTemplateId=' +
            listingTemplateId +
            '&tabId=' +
            tabId;

    content = content || {};

    let request = widgetActionsAjax(url, type, data, false, false);
    request.done(function (data) {
        let $item = $(data);

        if (content) {
            if (content.widgetTitle) {
                $item.find('[data-widget-title]').text(content.widgetTitle);
            }
        }

        if(templateWidgetId) {
            $item.find('#listingTemplateListingWidgetIdInput').val(
                templateWidgetId
            );
            $item.find('.edit-info').data(
                'templatewidget',
                templateWidgetId
            );
            $item.find('.editListingWidgetButton').data(
                'templatewidget',
                templateWidgetId
            );

            $('#loading_ajax').fadeOut('fast');
        }

        //to insert in a specific position
        if ($.widgetPosition !== undefined) {
            $.widgetPosition.before($item);
            delete $.widgetPosition;
        } else {
            let height;

            if (section === 'main') {
                //insert at the end of the page
                $(".main-widgets .listing-widget-list:visible").append($item);

                //Scroll page to the item added
                height = $('.main-widgets:visible').get(0).scrollHeight;
                $('main').animate({ scrollTop: height + 'px' }, 500);
            } else if (section === 'sidebar') {
                $('.sidebar-widgets .listing-widget-list:visible').append($item);
                let listingWidgetContainerSelector = $('.listing-widget-container');
                if (listingWidgetContainerSelector.hasClass('resized-sidebar')) {
                    listingWidgetContainerSelector.removeClass('resized-sidebar');
                }
                //Scroll page to the item added
                height = $('.sidebar-widgets:visible').get(0).scrollHeight;
                $('main').animate({ scrollTop: height + 'px' }, 500);
            } else if (section === 'header') {
                $('.header-widgets .listing-widget-list:visible').append(
                    $item
                );

                //Scroll page to the item added
                height = $('.header-widgets:visible').get(0).scrollHeight;
                $('main').animate({ scrollTop: height + 'px' }, 500);
            }
        }

        $('#add-new-widget-modal').modal('hide');
    });
}

function saveListingWidget(modal, genericInputs = false, newWidget = false) {
    let serializedContent;
    let serializedOptions = [];
    let url = DEFAULT_URL + '/includes/code/listingWidgetActionAjax.php';
    let type = 'POST';
    let divId = $.widgetDivId;
    let templateId = $('#listingTemplateId').val();
    let widgetId;
    let selectedDomainId = $('#selectedDomainId').val();
    if(newWidget) {
        let widget = $('#CustomWidgetSave');

        widgetId = widget.data('widgetid');
    } else {
        widgetId = $('#openWidgetId').val();
    }

    let serializeOptionsPush = function (index, obj) {
        let title = $(obj).find('input[name="title"]').val();
        let placeholder = $(obj)
            .find('input[name="placeholder"]')
            .val();
        let object = { title: title, placeholder: placeholder };
        serializedOptions.push(object);
    };

    if (genericInputs) {
        let form = $('#form_' + modal);
        let formValid = validateCustomWidget(form);

        if (formValid) {
            serializedContent = serializeForm('form_' + modal, true);
        } else {
            return;
        }
    } else if (modal === 'checklist') {
        let formchecklist = $('#form_checklist');
        let checklistValid = validateCustomWidget(formchecklist);

        let formchecklistoptions = $('#form_checklist_options');
        let checklistOptionsValid = validateCustomWidget(formchecklistoptions);

        if (checklistValid && checklistOptionsValid) {
            serializedContent = serializeForm('form_checklist', false);
            formchecklistoptions
                .find('.checklist-option')
                .each(serializeOptionsPush);

            serializedContent.push({
                name: 'groupFields',
                value: serializedOptions,
            });
            serializedContent = JSON.stringify(serializedContent);
        } else {
            return;
        }
    } else if (modal === 'moredetails') {
        let formMoreDetails = $('#form_moredetails');
        let moreDetaildValid = validateCustomWidget(formMoreDetails);

        let formMoreDetailsFields = $('#form_moredetailsfields');
        let moreDetaildFieldsValid = validateCustomWidget(formMoreDetailsFields);

        if (moreDetaildValid && moreDetaildFieldsValid) {
            serializedContent = serializeForm('form_moredetails', false);

            formMoreDetailsFields
                .find('.field-option')
                .each(serializeOptionsPush);

            serializedContent.push({
                name: 'groupFields',
                value: serializedOptions,
            });
            serializedContent = JSON.stringify(serializedContent);
        } else {
            return;
        }
    } else if (modal === 'specialties') {
        let formSpecialties = $('#form_specialties');
        let formSpecialtiesOption = $('#form_specialtiesoptions');
        let specialtiesValid = validateCustomWidget(formSpecialties);
        let specialtiesOptionValid = validateCustomWidget(
            formSpecialtiesOption
        );

        if (specialtiesValid && specialtiesOptionValid) {
            serializedContent = serializeForm('form_specialties', false);

            formSpecialtiesOption
                .find('.specialties-option')
                .each(function (index, obj) {
                    let value = $(obj).find('input[name="value"]').val();
                    let object = { value: value };
                    serializedOptions.push(object);
                });

            serializedContent.push({
                name: 'dropdownOptions',
                value: serializedOptions,
            });
            serializedContent = JSON.stringify(serializedContent);
        } else {
            return;
        }
    } else if (modal === 'relatedlistings') {
        let form = $('#form_' + modal);
        let formValid = validateCustomWidget(form);
        let displayCount = form.find('#card_itens_count');
        if(displayCount.val()<0 || displayCount.val()>10){
            notify.error(LANG_DISPLAY_MAX_ITEM, '', { fadeOut: 0 });
            if(!displayCount.hasClass('has-error')){
                displayCount.addClass('has-error')
            }
            if(formValid){
                formValid = !formValid;
            }
        }
        if (formValid) {
            serializedContent = serializeForm('form_' + modal, false);

            form.find('input[name="level"]:checked').each(function (index, obj) {
                let value = $(obj).val();
                serializedOptions.push(value);
            });

            let custom = {
                'order1': form.find('select[name="order1"]').val(),
                'order2': form.find('select[name="order2"]').val(),
                'columns': form.find('input[name="columns"]').val(),
                'quantity': form.find('input[name="quantity"]').val(),
                'filter': form.find('select[name="filter"]').val(),
                'level' : serializedOptions
            };

            serializedContent.push({name: 'custom', value: custom});

            serializedContent.push({name: 'level', value: serializedOptions});
            serializedContent = JSON.stringify(serializedContent);
        } else {
            return;
        }
    }

    let data = new FormData();
    data.append('contentArr', serializedContent);
    data.append('templateId', templateId);
    data.append('widgetId', widgetId);
    data.append("domain_id", selectedDomainId);

    let request = widgetActionsAjax(url, type, data, true, true, newWidget);

    request.done(function (data) {
        let objData = jQuery.parseJSON(data),
            msgSuccess = '',
            msgError = '',
            msgErrorAux = '',
            successAlert = $('#successAlert'),
            errorAlert = $('#errorAlert');

        if(newWidget) {
            let widget = $('#CustomWidgetSave');

            addListingWidgetToDom(
                widgetId,
                widget.data('section'),
                templateId,
                widget.data('tabid'),
                null,
                objData.newWidgetId
            );
        }

        if (objData.success) {
            msgSuccess = objData.message;
        }

        if (objData.errorMessage) {
            msgError = '<ul><li>';
            msgErrorAux = objData.errorMessage.join('</li><li>');
            msgError = msgError + msgErrorAux + '</li></ul>';
            errorAlert.children('div').html(msgError).alert();
            errorAlert.fadeTo(3000, 500).slideUp(500, function () {
                errorAlert.slideUp(500);
            });
        }

        if (msgSuccess) {
            successAlert.children('div').html(msgSuccess).alert();
            successAlert.fadeTo(3000, 500).slideUp(500, function () {
                successAlert.slideUp(500);
            });
        }

        if(divId !== undefined) {
            if (objData.isNewWidget && objData.newWidgetId) {
                $('#' + divId + ' #listingTemplateListingWidgetIdInput').val(
                    objData.newWidgetId
                );
                $('#' + divId + ' .edit-info').data(
                    'templatewidget',
                    objData.newWidgetId
                );
                $('#' + divId + ' .editListingWidgetButton').data(
                    'templatewidget',
                    objData.newWidgetId
                );
            } else {
                $('#' + divId + ' [data-widget-title]').text(
                    objData.widgetTitle
                );
            }
        }

        let addNewWidgetModal = $('#add-new-widget-modal');

        if ((addNewWidgetModal.data('bs.modal') || {}).isShown) {
            addNewWidgetModal.modal('toggle');
        } else {
            $('#edit-widget-modal').modal('toggle');
        }
        $.widgetDivId = undefined;
    })
        .always(function () {
            resetSaveButton();
        });

    return serializedContent;
}

function changeIcons() {
    let checkListIconSelected = $('.check-list-icon-selected');
    checkListIconSelected.empty();
    checkListIconSelected.append('<i class="fa ' + $('select#icon')[0].selectize.getValue() + '"></i>');
}

function validateCustomWidget(form) {
    let isValid = true;
    let requiredInputs = form[0].querySelectorAll('[required]');

    for (let i = 0; i < requiredInputs.length; i++) {
        let input = requiredInputs[i];
        let $input = $(input);
        let value = input.value.trim();

        if (!value) {
            $input.addClass('has-error');

            isValid = false;
        }
    }

    return isValid;
}

//capitalize first letter of each word
function toTitleCase(str) {
    return str.replace(/\w\S*/g, function(txt){
        return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
    });
}
