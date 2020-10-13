$(document).ready(function () {
    'use strict';
    let hasFormStructureOrDataErrors = false;
    let somethinWrongUnableToSave = (document.DDM_PLUGIN_TRANSLATED_SOMETHING_WRONG_UNABLE_TO_SAVE!==undefined)?document.DDM_PLUGIN_TRANSLATED_SOMETHING_WRONG_UNABLE_TO_SAVE:'Something went wrong. You are unable to save any changes in this page.';
    let selectizeHasOption = function(selectizeObj, optionValue){
        let returnValue = false;
        if(selectizeObj.hasOwnProperty('options')) {
            let optionValues = Object.keys(selectizeObj.options);
            if(optionValues.length>0){
                returnValue = optionValues.includes(optionValue);
            }
        }
        return returnValue;
    };

    let ddmPluginUpdateDropdownSelects = function(id,action,itemLabel) {
        let currentSelector = $('ul#ddmPluginSortableNavigation>li#' + id);
        let currentTargetOrder = $('#ddm_plugin_navigation_order_' + id);
        let currentTargetOrderValue = currentTargetOrder.val();
        let currentLinktoSelectSelector = $('#ddm_plugin_navigation_linkto_' + id);
        let currentLinktoSelectValue = currentLinktoSelectSelector[0].selectize.getValue();
        if (currentLinktoSelectValue === 'dropdown') {
            if (currentTargetOrderValue !== undefined) {
                //Run through all dropdowns elements
                let currentLiChildSiblings = currentSelector.siblings('li').find('select[id^="ddm_plugin_navigation_parent_"]>option[value="'+ currentTargetOrderValue +'"][selected="selected"]');
                currentLiChildSiblings.each(function(index, selectedOption){
                    $(selectedOption).closest('li').addClass('ddm-plugin-hide');
                    let currentItemInputDeletedSelector = $(selectedOption).closest('li').find('input[id^="ddm_plugin_navigation_deleted_"]');
                    if(currentItemInputDeletedSelector.length>0) {
                        currentItemInputDeletedSelector.val('1');
                    }
                });
                $('select[id^="ddm_plugin_navigation_parent_"]').filter(':not([id$="LI_ID"])').each(function () {
                    let thisSelectize = this.selectize;
                    if (action === 'remove') {
                        thisSelectize.removeOption(currentTargetOrderValue);
                        let optionsCount = Object.keys(thisSelectize.options).length;
                        if (optionsCount <= 1) {
                            thisSelectize.disable();
                        }
                    } else if (action === 'update') {
                        if (itemLabel !== undefined) {
                            thisSelectize.updateOption(currentTargetOrderValue, {
                                'value': currentTargetOrderValue,
                                'text': itemLabel
                            });
                        }
                    } else if (action === 'add') {
                        if (itemLabel !== undefined) {
                            thisSelectize.addOption({'value': currentTargetOrderValue, 'text': itemLabel});
                            let optionsCount = Object.keys(thisSelectize.options).length;
                            if (optionsCount > 1) {
                                thisSelectize.enable();
                            }
                        }
                    }
                });
            }
        }
    };

    let updateLinksToDropdownSelectionOptionAfterDetach = function(currentItemSelector){
        let currentItemSelectLinkToSelector = currentItemSelector.find('select[id^="ddm_plugin_navigation_linkto_"]');
        if(currentItemSelectLinkToSelector.length>0 && currentItemSelectLinkToSelector[0].selectize!==undefined){
            let currentItemSelectLinkToSelectizeObj = currentItemSelectLinkToSelector[0].selectize;
            let linksToDropdownText = (document.DDM_PLUGIN_TRANSLATED_LINKSTO_DROPDOWN_TEXT!==undefined)?document.DDM_PLUGIN_TRANSLATED_LINKSTO_DROPDOWN_TEXT:'Menu with sub menus';
            if(!selectizeHasOption(currentItemSelectLinkToSelectizeObj,'dropdown')){
                currentItemSelectLinkToSelectizeObj.addOption({
                    'value': 'dropdown',
                    'text': linksToDropdownText
                });
            }
        }
    };

    let updateMenuWithSubMenusLinkToAndDetachAllAfterDetachOrDelete = function(currentItemSelector, previousVisibleItemSelector){
        let selectParentSelector = currentItemSelector.find('select[id^="ddm_plugin_navigation_parent_"]');
        let selectParentValue = selectParentSelector[0].selectize.getValue();
        let otherSubMenuSiblings = currentItemSelector.siblings('li').find('select[id^="ddm_plugin_navigation_parent_"]>option[value="' + selectParentValue + '"][selected="selected"]');
        if(otherSubMenuSiblings.length===0){
            let previousVisibleItemInputAreaSelector = previousVisibleItemSelector.find('input[id^="ddm_plugin_navigation_area_"]');
            let previousVisibleItemInputAreaValue = previousVisibleItemInputAreaSelector.val();
            let previousVisibleItemSelectLinkToSelector = previousVisibleItemSelector.find('select[id^="ddm_plugin_navigation_linkto_"]');
            let previousVisibleItemOrderSelector = previousVisibleItemSelector.find('input[id^="ddm_plugin_navigation_order_"]');
            let previousVisibleItemOrderValue = previousVisibleItemOrderSelector.val();
            if(previousVisibleItemInputAreaValue!==undefined && previousVisibleItemOrderValue!==undefined && previousVisibleItemSelectLinkToSelector.length>0) {
                let previousVisibleItemSelectLinkToSelectizeObj = previousVisibleItemSelectLinkToSelector[0].selectize;
                if(previousVisibleItemSelectLinkToSelectizeObj!==undefined) {
                    let previousVisibleItemSelectLinkToValue = previousVisibleItemSelectLinkToSelectizeObj.getValue();
                    if (previousVisibleItemInputAreaValue === 'header_dropdown' && previousVisibleItemSelectLinkToValue === 'dropdown' && previousVisibleItemOrderValue === selectParentValue) {
                        let dettachAllSubMenusButtonSelector = previousVisibleItemSelector.find('button.btn.ddm-plugin-detach-all-sub-menus');
                        let hideOnFrontEndHiddenIcon = previousVisibleItemSelector.find('i.ddm-plugin-hidden-on-frontend-icon.ddm-plugin-hide');
                        if(hideOnFrontEndHiddenIcon.length>0){
                            hideOnFrontEndHiddenIcon.removeClass('ddm-plugin-hide');
                        }
                        dettachAllSubMenusButtonSelector.prop('disabled', true);
                        previousVisibleItemSelectLinkToSelectizeObj.enable();
                    }
                }
            }
        }
        selectParentSelector[0].selectize.setValue('NULL',true);
        let inputSelectedParentSelector = currentItemSelector.find('input[id^="ddm_plugin_navigation_selected_parent_"]');
        inputSelectedParentSelector.val('');
    };

    let ddmPluginRemoveItemNavigation = function (id) {
        let currentNavigationLabel = $('#ddm_plugin_navigation_label_' + id);
        let currentNavigationLabelValue = currentNavigationLabel.val();
        let confirmationMessage = (document.DDM_PLUGIN_TRANSLATED_CONFIRM_NAVIGATION_ITEM_REMOVE!==undefined)?document.DDM_PLUGIN_TRANSLATED_CONFIRM_NAVIGATION_ITEM_REMOVE:'Are you sure you would like to remove this menu item, including all sub menus related to it: ';
        if(window.bootbox!==undefined) {
            window.bootbox.confirm(confirmationMessage + (currentNavigationLabelValue?currentNavigationLabelValue:('#' + id)) + '?', function (result) {
                if (result) {
                    let currentItemSelector = $('li#' + id);
                    let currentItemInputDeletedSelector = currentItemSelector.find('input[id^="ddm_plugin_navigation_deleted_"]');
                    if(currentItemInputDeletedSelector.length>0) {
                        //Remove item
                        currentItemSelector.addClass('ddm-plugin-hide');
                        currentItemSelector.removeAttr('style');//This is needed to avoid a bug caused by sortable cancel
                        let currentItemInputAreaSelector = currentItemSelector.find('input[id^="ddm_plugin_navigation_area_"]');
                        let currentItemInputAreaValue = currentItemInputAreaSelector.val();
                        let currentItemSelectParentSelector = currentItemSelector.find('select[id^="ddm_plugin_navigation_parent_"]');
                        if (currentItemSelectParentSelector.length > 0 && currentItemInputAreaValue !== undefined) {
                            if (currentItemInputAreaValue === 'header_dropdown' && currentItemSelectParentSelector[0].selectize.getValue() !== 'NULL') {
                                let previousVisibleItemSelector = currentItemSelector.prevAll('li:not(.ddm-plugin-hide):first');
                                ddmPluginUpdateLastSubMenuIcon(currentItemSelector, previousVisibleItemSelector);
                                updateMenuWithSubMenusLinkToAndDetachAllAfterDetachOrDelete(currentItemSelector, previousVisibleItemSelector);
                            }
                        }
                        currentItemInputDeletedSelector.val('1');
                        //Validate available options
                        ddmPluginUpdateDropdownSelects(id, 'remove');
                    }
                }
            });
        }
    };

    let ddmPluginSortableRemoveItemClickHandler = function(){
        let thisObj = $(this);
        let currentItemIndex = thisObj.data('current-item-index');
        ddmPluginRemoveItemNavigation(currentItemIndex);
    };

    let ddmPluginInputNavigationLabelFocusHandler = function() {
        let focusedObj = $(this);

        let focusedNavigationLabelValue = focusedObj.val();
        let currentItemSelector = focusedObj.closest('li');
        let currentItemLinktoSelectSelector = currentItemSelector.find('select[id^="ddm_plugin_navigation_linkto_"]');
        let currentTargetOrder = currentItemSelector.find('input[id^="ddm_plugin_navigation_order_"]');
        let currentTargetOrderValue = currentTargetOrder.val();
        if (currentItemSelector.length === 0 || currentItemLinktoSelectSelector.length === 0 || currentTargetOrder.length === 0 || currentTargetOrderValue === undefined || currentItemLinktoSelectSelector[0].selectize === undefined || focusedNavigationLabelValue === undefined) {
            registerAndNotifyInternalCriticalError();
            return;
        }
        let currentItemLinktoSelectValue = currentItemLinktoSelectSelector[0].selectize.getValue();

        let wasChanged = false;
        let ddmPluginInputNavigationLabelChangeHandler = function () {
            let changedObj = $(this);
            let changedNavigationLabelValue = changedObj.val();
            wasChanged = changedNavigationLabelValue !== focusedNavigationLabelValue;
        };

        let ddmPluginInputNavigationLabelBlurHandler = function () {
            let bluredObj = $(this);
            bluredObj.off('change', ddmPluginInputNavigationLabelChangeHandler);
            bluredObj.off('blur', ddmPluginInputNavigationLabelBlurHandler);
            if (wasChanged) {
                let bluredNavigationLabelValue = bluredObj.val();
                let notAssociatedString = (document.DDM_PLUGIN_TRANSLATED_NOT_ASSOCIATED!==undefined)?document.DDM_PLUGIN_TRANSLATED_NOT_ASSOCIATED:'Not associated';
                if (bluredNavigationLabelValue.trim() === '') {
                    bluredObj.val(focusedNavigationLabelValue);
                    if (window.notify !== undefined) {
                        let emptyLabelNotAllowedString = (document.DDM_PLUGIN_TRANSLATED_EMPTY_LABEL_NOT_ALLOWED!==undefined)?document.DDM_PLUGIN_TRANSLATED_EMPTY_LABEL_NOT_ALLOWED:'Empty menu label is not allowed. The last valid value has been restored.';
                        window.notify.warning(emptyLabelNotAllowedString, '');
                    }
                } else if (bluredNavigationLabelValue.trim().toLowerCase() === notAssociatedString.trim().toLowerCase()) {
                    bluredObj.val(focusedNavigationLabelValue);
                    if (window.notify !== undefined) {
                        let notAssociatedLabelNotAllowedString = (document.DDM_PLUGIN_TRANSLATED_NOT_ASSOCIATED_LABEL_NOT_ALLOWED!==undefined)?document.DDM_PLUGIN_TRANSLATED_NOT_ASSOCIATED_LABEL_NOT_ALLOWED:'\'Not associated\' menu label is not allowed. The last valid value has been restored.';
                        window.notify.warning(notAssociatedLabelNotAllowedString, '');
                    }
                } else {
                    if (currentItemLinktoSelectValue === 'dropdown') {
                        //Run through all dropdowns elements
                        let currentLiChildSiblings = currentItemSelector.siblings('li').find('select[id^="ddm_plugin_navigation_parent_"]');
                        currentLiChildSiblings.each(function (index, selectedOption) {
                            if (!hasFormStructureOrDataErrors) {
                                let selectParentSelector = $(selectedOption);
                                let currentItemSelector = selectParentSelector.closest('li');
                                let dettachSubMenuButtonSelector = currentItemSelector.find('button.ddm-plugin-detach-sub-menu');
                                let dettachSubMenuButtonAssociationLabelData = dettachSubMenuButtonSelector.data('associationlabel');
                                if (selectParentSelector.length === 0 || selectParentSelector[0].selectize === undefined || currentItemSelector.length===0 || dettachSubMenuButtonSelector.length===0 || dettachSubMenuButtonAssociationLabelData===undefined) {
                                    registerAndNotifyInternalCriticalError();
                                    return;
                                }
                                let selectParentSelectizeObj = selectParentSelector[0].selectize;
                                selectParentSelectizeObj.updateOption(currentTargetOrderValue, {
                                    'value': currentTargetOrderValue,
                                    'text': bluredNavigationLabelValue
                                });
                                let selectParentSelectizeObjValue = selectParentSelectizeObj.getValue();
                                if(selectParentSelectizeObjValue===currentTargetOrderValue){
                                    dettachSubMenuButtonSelector.attr('data-associationlabel',bluredNavigationLabelValue);
                                }
                            }
                        });
                    }
                }
            }
        };
        focusedObj.on('blur', ddmPluginInputNavigationLabelBlurHandler);
        focusedObj.on('change', ddmPluginInputNavigationLabelChangeHandler);
    };

    let ddmPluginSelectParentSelectizeFocusHandler = function() {
        let focusedSelectizeObj = this;
        let focusedObjSelector = $(focusedSelectizeObj.$input);

        let focusedObjItemSelector = focusedObjSelector.closest('li');
        let focusedObjItemInputNavigationAreaSelector = focusedObjItemSelector.find('input[id^="ddm_plugin_navigation_area_"]');
        let focusedObjItemDettachSubMenusButtonSelector = focusedObjItemSelector.find('button.ddm-plugin-detach-sub-menu');

        let focusedObjItemDropdownIconSelector = focusedObjItemSelector.find('i.ddm-plugin-dropdown-icon');
        let focusedObjItemHideOnFrontEndIcon = focusedObjItemSelector.find('i.ddm-plugin-hidden-on-frontend-icon');
        let focusedObjItemSelectParentContainerSelector = focusedObjItemSelector.find('span[id^="ddm_plugin_navigation_parent_"][id$="_container"]');
        let focusedObjItemInputSelectedParentSelector = focusedObjItemSelector.find('input[id^="ddm_plugin_navigation_selected_parent_"]');
        let focusedObjItemItemSelectLinkToSelector = focusedObjItemSelector.find('select[id^="ddm_plugin_navigation_linkto_"]');

        let focusedObjItemInputNavigationAreaValue = focusedObjItemInputNavigationAreaSelector.val();
        let focusedObjItemInputSelectedParentValue = focusedObjItemInputSelectedParentSelector.val();

        let focusedObjItemDettachSubMenusButtonAssociationLabelData = focusedObjItemDettachSubMenusButtonSelector.data('associationlabel');

        if(focusedObjSelector.length===0 || focusedObjItemItemSelectLinkToSelector.length===0 || focusedObjItemHideOnFrontEndIcon.length===0 || focusedObjItemSelector.length ===0 || focusedObjItemInputNavigationAreaSelector.length===0 || focusedObjItemDettachSubMenusButtonSelector.length===0 || focusedObjItemDropdownIconSelector.length===0 || focusedObjItemSelectParentContainerSelector.length===0 || focusedObjItemInputSelectedParentSelector.length===0 || focusedObjItemInputNavigationAreaValue===undefined || focusedObjItemInputSelectedParentValue===undefined || focusedObjItemItemSelectLinkToSelector[0].selectize===undefined||focusedObjItemDettachSubMenusButtonAssociationLabelData===undefined) {
            registerAndNotifyInternalCriticalError();
            return;
        }
        let focusedObjItemItemSelectLinkToSelectizeObj = focusedObjItemItemSelectLinkToSelector[0].selectize;
        let focusedParentSelectValue = focusedSelectizeObj.getValue();

        let ddmPluginSelectParentSelectizeBlurHandler = function () {
            let bluredSelectizeObj = this;
            bluredSelectizeObj.off('blur', ddmPluginSelectParentSelectizeBlurHandler);
            let bluredParentSelectValue = bluredSelectizeObj.getValue();
            if (bluredParentSelectValue !== focusedParentSelectValue) {
                let targetInputOrderSelector = focusedObjItemSelector.siblings('li:not(.ddm-plugin-hide)').find('input[id^="ddm_plugin_navigation_order_"][value="' + bluredParentSelectValue + '"]');
                if(targetInputOrderSelector.length===0) {
                    registerAndNotifyInternalCriticalError();
                    return;
                }
                if(targetInputOrderSelector.length>1) {
                    targetInputOrderSelector = $(targetInputOrderSelector[0]);
                }
                let targetItemSelector = targetInputOrderSelector.closest('li');
                let targetItemHideOnFrontEndIcon = targetItemSelector.find('i.ddm-plugin-hidden-on-frontend-icon');
                let targetItemDettachAllSubMenusButtonSelector = targetItemSelector.find('button.btn.ddm-plugin-detach-all-sub-menus');
                let targetItemSelectLinkToSelector = targetItemSelector.find('select[id^="ddm_plugin_navigation_linkto_"]');
                if(targetItemHideOnFrontEndIcon.length===0 || targetItemDettachAllSubMenusButtonSelector===0 || targetItemSelectLinkToSelector===0 || targetItemSelectLinkToSelector[0].selectize===undefined){
                    registerAndNotifyInternalCriticalError();
                    return;
                }
                let targetItemSelectLinkToSelectizeObj = targetItemSelectLinkToSelector[0].selectize;
                let targetLiChildSiblings = targetItemSelector.siblings('li:not(.ddm-plugin-hide)').find('select[id^="ddm_plugin_navigation_parent_"]>option[value="' + bluredParentSelectValue + '"][selected="selected"]');
                if(targetLiChildSiblings.length>0){
                    let lastTargetLiChildSiblingSelectParentOption = targetLiChildSiblings.last();
                    let lastTargetLiChildSibling = lastTargetLiChildSiblingSelectParentOption.closest('li');
                    let lastTargetLiChildSiblingDropdownIconSelector = lastTargetLiChildSibling.find('i.ddm-plugin-dropdown-icon');
                    if(lastTargetLiChildSibling.length===0 || lastTargetLiChildSiblingDropdownIconSelector.length===0){
                        registerAndNotifyInternalCriticalError();
                        return;
                    }
                    focusedObjItemSelector.detach().insertAfter(lastTargetLiChildSibling);
                    lastTargetLiChildSiblingDropdownIconSelector.removeClass('ddm-plugin-dropdown-last-child');
                } else {
                    focusedObjItemSelector.detach().insertAfter(targetItemSelector);
                    targetItemSelectLinkToSelectizeObj.disable();
                }
                if(focusedObjItemHideOnFrontEndIcon.hasClass('ddm-plugin-hide') && !targetItemHideOnFrontEndIcon.hasClass('ddm-plugin-hide')){
                    targetItemHideOnFrontEndIcon.addClass('ddm-plugin-hide');
                }

                if(selectizeHasOption(focusedObjItemItemSelectLinkToSelectizeObj,'dropdown')){
                    focusedObjItemItemSelectLinkToSelectizeObj.removeOption('dropdown');
                }

                focusedObjItemDropdownIconSelector.addClass('ddm-plugin-dropdown-last-child');
                if(focusedObjItemDropdownIconSelector.hasClass('ddm-plugin-hide')){
                    focusedObjItemDropdownIconSelector.removeClass('ddm-plugin-hide');
                }
                focusedObjItemInputNavigationAreaSelector.val('header_dropdown');
                if(targetItemDettachAllSubMenusButtonSelector.prop('disabled')===true)
                {
                    targetItemDettachAllSubMenusButtonSelector.prop('disabled',false);
                }
                if(focusedObjItemDettachSubMenusButtonSelector.hasClass('ddm-plugin-hide')){
                    let focusedSelectizeObjSelectedOption = focusedSelectizeObj.getOption(bluredParentSelectValue);
                    focusedObjItemDettachSubMenusButtonSelector.attr('data-associationlabel',focusedSelectizeObjSelectedOption.context.textContent);
                    focusedObjItemDettachSubMenusButtonSelector.removeClass('ddm-plugin-hide');
                }
                if(!focusedObjItemSelectParentContainerSelector.hasClass('ddm-plugin-hide'))
                {
                    focusedObjItemSelectParentContainerSelector.addClass('ddm-plugin-hide');
                }
                focusedObjItemInputSelectedParentSelector.val(bluredParentSelectValue);
            }
        };

        focusedSelectizeObj.on('blur', ddmPluginSelectParentSelectizeBlurHandler);
    };

    let ddmPluginInputNavigationLinkFocusHandler = function() {
        let focusedObj = $(this);
        let focusedNavigationLinkValue = focusedObj.val();
        let currentItemSelector = focusedObj.closest('li');
        let currentItemInputNavigationCustomSelector = currentItemSelector.find('input[id^="ddm_plugin_navigation_custom_"]');
        let currentItemIHiddenOnFrontEndIconSelector = currentItemSelector.find('i.ddm-plugin-hidden-on-frontend-icon');
        let currentItemInputNavigationCustomValue = currentItemInputNavigationCustomSelector.val();
        if(currentItemSelector.length===0||currentItemInputNavigationCustomSelector.length===0||currentItemIHiddenOnFrontEndIconSelector.length===0||currentItemInputNavigationCustomValue===undefined){
            registerAndNotifyInternalCriticalError();
            return;
        }

        let wasChanged = false;
        let ddmPluginInputNavigationLinkChangeHandler = function () {
            let changedObj = $(this);
            let changedNavigationLinkValue = changedObj.val();
            wasChanged = changedNavigationLinkValue !== focusedNavigationLinkValue;
        };

        let ddmPluginInputNavigationLinkBlurHandler = function () {
             let bluredObj = $(this);
             let bluredNavigationLinkValue = bluredObj.val();
             bluredObj.off('change', ddmPluginInputNavigationLinkChangeHandler);
             bluredObj.off('blur', ddmPluginInputNavigationLinkBlurHandler);
             if (wasChanged) {
                 let lastValueBeforeDisable = bluredObj.data('last-value-before-disable');
                 if(lastValueBeforeDisable!==undefined){
                     bluredObj.removeData('last-value-before-disable');
                 }

                 if(currentItemInputNavigationCustomValue==='1'){
                     if(bluredNavigationLinkValue.trim()===''){
                         currentItemIHiddenOnFrontEndIconSelector.removeClass('ddm-plugin-hide');
                     } else {
                         currentItemIHiddenOnFrontEndIconSelector.addClass('ddm-plugin-hide');
                     }
                 }
             }
        };
        focusedObj.on('blur', ddmPluginInputNavigationLinkBlurHandler);
        focusedObj.on('change', ddmPluginInputNavigationLinkChangeHandler);
    };

    let ddmPluginSelectLinkToSelectizeFocusHandler = function() {
        let focusedSelectizeObj = this;
        let focusedObj = $(focusedSelectizeObj.$input);

        let currentItemSelector = focusedObj.closest('li');
        let currentTargetOrder = currentItemSelector.find('input[id^="ddm_plugin_navigation_order_"]');
        let currentNavigationLabel = currentItemSelector.find('input[id^="ddm_plugin_navigation_label_"]');
        let currentNavigationLink = currentItemSelector.find('input[id^="ddm_plugin_navigation_link_"]');
        let currentNavigationCustom = currentItemSelector.find('input[id^="ddm_plugin_navigation_custom_"]');
        let currentNavigationPageId = currentItemSelector.find('input[id^="ddm_plugin_navigation_pageid_"]');
        let currentNavigationArea = currentItemSelector.find('input[id^="ddm_plugin_navigation_area_"]');
        let dettachAllSubMenusButtonSelector = currentItemSelector.find('button.btn.ddm-plugin-detach-all-sub-menus');
        let hideOnFrontEndIcon = currentItemSelector.find('i.ddm-plugin-hidden-on-frontend-icon');
        let selectParentLinkToContainerSelector = currentItemSelector.find('span[id^="ddm_plugin_navigation_linkto_"][id$="_container"]');
        let selectParentContainerSelector = currentItemSelector.find('span[id^="ddm_plugin_navigation_parent_"][id$="_container"]');
        let currentNavigationLabelValue = currentNavigationLabel.val();
        let currentNavigationPageIdValue = currentNavigationPageId.val();
        let currentTargetOrderValue = currentTargetOrder.val();
        let currentNavigationLinkValue = currentNavigationLink.val();
        let selectParentLinkToContainerDataUnavailableFrontendPageid = selectParentLinkToContainerSelector.data('unavailable-frontend-pageid');
        if (currentItemSelector.length === 0 ||selectParentLinkToContainerSelector.length===0|| selectParentContainerSelector.length===0 || dettachAllSubMenusButtonSelector.length === 0 || hideOnFrontEndIcon.length===0 || currentNavigationArea.length === 0 || currentTargetOrder.length === 0 || currentNavigationLabel.length === 0 || currentNavigationLink.length === 0 || currentNavigationCustom.length ===  0 || currentNavigationPageId === 0 || currentTargetOrderValue === undefined || currentNavigationLinkValue === undefined || currentNavigationPageIdValue === undefined || currentNavigationLabelValue === undefined || selectParentLinkToContainerDataUnavailableFrontendPageid === undefined) {
            registerAndNotifyInternalCriticalError();
            return;
        }
        let focusedLinktoSelectValue = focusedSelectizeObj.getValue();

        let ddmPluginDropDownLinkToBlurHandler = function () {
            let bluredSelectizeObj = this;
            bluredSelectizeObj.off('blur', ddmPluginDropDownLinkToBlurHandler);
            let bluredLinktoSelectValue = bluredSelectizeObj.getValue();
            if (bluredLinktoSelectValue!==focusedLinktoSelectValue) {
                if(focusedLinktoSelectValue==='dropdown'){
                    //Run through all dropdowns elements
                    let currentLiChildSiblings = currentItemSelector.siblings('li').find('select[id^="ddm_plugin_navigation_parent_"]');
                    currentLiChildSiblings.each(function (index, selectedOption) {
                        if (!hasFormStructureOrDataErrors) {
                            let selectParentSelector = $(selectedOption);
                            if (selectParentSelector.length === 0 || selectParentSelector[0].selectize === undefined) {
                                registerAndNotifyInternalCriticalError();
                                return;
                            }
                            let selectParentSelectizeObj = selectParentSelector[0].selectize;
                            let selectParentSelectizeValue = selectParentSelectizeObj.getValue();
                            if(selectParentSelectizeValue===currentTargetOrderValue){//This was not supposed to occur, but it is here for safety
                                let currentSubMenuItemSelector = $(selectedOption).closest('li');
                                let dropdownIconSelector = currentSubMenuItemSelector.find('i.ddm-plugin-dropdown-icon');
                                let dettachSubMenuButtonSelector = currentSubMenuItemSelector.find('button.ddm-plugin-detach-sub-menu');
                                let selectParentContainerSelector = currentSubMenuItemSelector.find('span[id^="ddm_plugin_navigation_parent_"][id$="_container"]');
                                let inputSelectedParentSelector = currentSubMenuItemSelector.find('input[id^="ddm_plugin_navigation_selected_parent_"]');
                                let currentItemInputAreaSelector = currentSubMenuItemSelector.find('input[id^="ddm_plugin_navigation_area_"]');
                                if (currentSubMenuItemSelector.length === 0 || dropdownIconSelector.length === 0 || dettachSubMenuButtonSelector.length === 0 || selectParentContainerSelector.length === 0 || inputSelectedParentSelector.length === 0 || currentItemInputAreaSelector.length === 0) {
                                    registerAndNotifyInternalCriticalError();
                                    return;
                                }
                                updateLinksToDropdownSelectionOptionAfterDetach(currentSubMenuItemSelector);

                                dropdownIconSelector.addClass('ddm-plugin-hide');
                                dettachSubMenuButtonSelector.addClass('ddm-plugin-hide');
                                selectParentContainerSelector.removeClass('ddm-plugin-hide');

                                selectParentSelectizeObj.setValue('NULL', true);//Selects Not associated
                                inputSelectedParentSelector.val('');
                                currentItemInputAreaSelector.val('header');
                            }
                            if(selectizeHasOption(selectParentSelectizeObj,currentTargetOrderValue)){
                                selectParentSelectizeObj.removeOption(currentTargetOrderValue);
                            }
                        }
                    });
                    if (auxLiTextSelectParentSelector.length===0) {
                        registerAndNotifyInternalCriticalError();
                        return;
                    }
                    let auxLiTextSelectParentTargetOptionSelector = auxLiTextSelectParentSelector.find('option[value="' + currentTargetOrderValue + '"]');
                    if(auxLiTextSelectParentTargetOptionSelector.length!==0){
                        auxLiTextSelectParentTargetOptionSelector.remove();
                    }
                    if(currentNavigationLinkValue==='dropdown'){
                        currentNavigationLink.val('');
                    }
                    currentNavigationArea.val('header');
                    if(!hideOnFrontEndIcon.hasClass('ddm-plugin-hide')){
                        hideOnFrontEndIcon.addClass('ddm-plugin-hide');
                    }
                    if(!dettachAllSubMenusButtonSelector.hasClass('ddm-plugin-hide')){
                        dettachAllSubMenusButtonSelector.addClass('ddm-plugin-hide');
                    }
                    if(selectParentContainerSelector.hasClass('ddm-plugin-hide')){
                        selectParentContainerSelector.removeClass('ddm-plugin-hide');
                    }
                } else if(focusedLinktoSelectValue==='custom'){
                    currentNavigationLink.addClass('ddm-plugin-disabled-custom-link');
                    currentNavigationLink.prop('disabled', true);
                    if (currentNavigationLinkValue.trim() !== '') {
                        currentNavigationLink.data('last-value-before-disable', currentNavigationLinkValue);
                    }
                    currentNavigationCustom.val('0');
                    currentNavigationLink.val('');
                } else {
                    currentNavigationPageId.val('');
                }
                if(bluredLinktoSelectValue==='dropdown'){
                    //Run through all dropdowns elements
                    let currentLiChildSiblings = currentItemSelector.siblings('li').find('select[id^="ddm_plugin_navigation_parent_"]');
                    currentLiChildSiblings.each(function (index, selectedOption) {
                        if (!hasFormStructureOrDataErrors) {
                            let selectParentSelector = $(selectedOption);
                            if (selectParentSelector.length === 0 || selectParentSelector[0].selectize === undefined) {
                                registerAndNotifyInternalCriticalError();
                                return;
                            }
                            let selectParentSelectizeObj = selectParentSelector[0].selectize;
                            if(!selectizeHasOption(selectParentSelectizeObj,currentTargetOrderValue)){
                                selectParentSelectizeObj.addOption({
                                    'value': currentTargetOrderValue,
                                    'text': currentNavigationLabelValue
                                });
                            } else {
                                selectParentSelectizeObj.updateOption(currentTargetOrderValue, {
                                    'value': currentTargetOrderValue,
                                    'text': currentNavigationLabelValue
                                });
                            }
                        }
                    });
                    if (auxLiTextSelectParentSelector.length===0) {
                        registerAndNotifyInternalCriticalError();
                        return;
                    }
                    let auxLiTextSelectParentTargetOptionSelector = auxLiTextSelectParentSelector.find('option[value="' + currentTargetOrderValue + '"]');
                    if(auxLiTextSelectParentTargetOptionSelector.length===0){
                        auxLiTextSelectParentSelector.append('<option value="' + currentTargetOrderValue + '">' + currentNavigationLabelValue + '</option>');
                    }
                    currentNavigationArea.val('header_dropdown');
                    if(hideOnFrontEndIcon.hasClass('ddm-plugin-hide')){
                        hideOnFrontEndIcon.removeClass('ddm-plugin-hide');
                    }
                    if(dettachAllSubMenusButtonSelector.hasClass('ddm-plugin-hide')){
                        dettachAllSubMenusButtonSelector.removeClass('ddm-plugin-hide');
                        dettachAllSubMenusButtonSelector.prop('disabled', true);
                    }
                    if(!selectParentContainerSelector.hasClass('ddm-plugin-hide')){
                        selectParentContainerSelector.addClass('ddm-plugin-hide');
                    }
                } else if(bluredLinktoSelectValue==='custom'){
                    currentNavigationLink.removeClass('ddm-plugin-disabled-custom-link');
                    currentNavigationLink.prop('disabled', false);
                    let lastValueBeforeDisable = currentNavigationLink.data('last-value-before-disable');
                    if (lastValueBeforeDisable !== undefined) {
                        currentNavigationLink.val(lastValueBeforeDisable);
                    }
                    currentNavigationCustom.val('1');
                    if(currentNavigationLink.val()!==undefined&&currentNavigationLink.val().trim()!==''){
                        if(!hideOnFrontEndIcon.hasClass('ddm-plugin-hide')){
                            hideOnFrontEndIcon.addClass('ddm-plugin-hide');
                        }
                    } else {
                        if(hideOnFrontEndIcon.hasClass('ddm-plugin-hide')){
                            hideOnFrontEndIcon.removeClass('ddm-plugin-hide');
                        }
                    }
                } else {
                    currentNavigationPageId.val(bluredLinktoSelectValue);
                    if(bluredLinktoSelectValue===selectParentLinkToContainerDataUnavailableFrontendPageid){
                        if(hideOnFrontEndIcon.hasClass('ddm-plugin-hide')){
                            hideOnFrontEndIcon.removeClass('ddm-plugin-hide');
                        }
                    } else {
                        if(!hideOnFrontEndIcon.hasClass('ddm-plugin-hide')){
                            hideOnFrontEndIcon.addClass('ddm-plugin-hide');
                        }
                    }
                }
            }
        };
        focusedSelectizeObj.on('blur', ddmPluginDropDownLinkToBlurHandler);
    };

    let ddmPluginSaveButtonClickHandler = function(event){
        if (hasFormStructureOrDataErrors) {
            if(window.notify!==undefined) {
                window.notify.error(somethinWrongUnableToSave, '', {fadeOut: 0});
            }
            event.preventDefault();
            event.stopPropagation();
            return;
        }
        let thisObj = $(this);
        let demoLiveMode = thisObj.data('demo-live-mode');
        if(demoLiveMode){
            if($.isFunction(window.livemodeMessage)){
                window.livemodeMessage(true, false);
            }
        } else {
            let formNavigation = $('#form_navigation');
            let menuItensContainerSelector = $('ul#ddmPluginSortableNavigation');
            let menuItensSelector = menuItensContainerSelector.find('li');
            let menuItensSelectLinkToSelector = menuItensSelector.find('select[id^="ddm_plugin_navigation_linkto_"]');
            let menuItensSelectParentSelector = menuItensSelector.find('select[id^="ddm_plugin_navigation_parent_"]');
            let menuItensInputLabelSelector =  menuItensSelector.find('input[id^="ddm_plugin_navigation_label_"]');
            let menuItensInputLinkSelector = menuItensSelector.find('input[id^="ddm_plugin_navigation_link_"]');
            let menuItensAnchorWithRemoveItemClassSelector = menuItensSelector.find('a.remove-item');
            let menuItensButtonDetachAllSubMenusSelector = menuItensSelector.find('.ddm-plugin-detach-all-sub-menus');
            let menuItensButtonDetachSubMenuSelector = menuItensSelector.find('.ddm-plugin-detach-sub-menu');
            let menuItensInputSelectedParentSelector = menuItensSelector.find('input[id^="ddm_plugin_navigation_selected_parent_"]');

            if(formNavigation.length===0||menuItensContainerSelector.length===0||menuItensSelector.length===0||menuItensSelectLinkToSelector.length===0||menuItensSelectParentSelector.length===0||menuItensInputLabelSelector.length===0||menuItensInputLinkSelector.length===0||menuItensAnchorWithRemoveItemClassSelector.length===0||menuItensButtonDetachAllSubMenusSelector.length===0||menuItensButtonDetachSubMenuSelector.length===0||menuItensInputSelectedParentSelector.length===0||menuItensSelectLinkToSelector[0].selectize===undefined||menuItensSelectParentSelector[0].selectize===undefined){
                if(window.notify!==undefined) {
                    window.notify.error(somethinWrongUnableToSave, '', {fadeOut: 0});
                }
                event.preventDefault();
                event.stopPropagation();
                return;
            }

            let menuItensSelectLinkToSelectizeObj = menuItensSelectLinkToSelector[0].selectize;
            let menuItensSelectParentSelectizeObj = menuItensSelectParentSelector[0].selectize;

            menuItensButtonDetachAllSubMenusSelector.off('click', ddmPluginDetachAllSubMenusClickHandler);
            menuItensButtonDetachSubMenuSelector.off('click', ddmPluginDetachSubMenuClickHandler);
            menuItensAnchorWithRemoveItemClassSelector.off('click', ddmPluginSortableRemoveItemClickHandler);
            menuItensInputLabelSelector.off('focus', ddmPluginInputNavigationLabelFocusHandler);
            menuItensInputLinkSelector.off('focus', ddmPluginInputNavigationLinkFocusHandler);
            menuItensSelectLinkToSelectizeObj.off('focus', ddmPluginSelectLinkToSelectizeFocusHandler);
            menuItensSelectParentSelectizeObj.off('focus', ddmPluginSelectParentSelectizeFocusHandler);

            let initialOrderIndex = 0;
            let initialOrderChildIndex = 1;
            let nextTargetMenuItemCouldBeChild = false;
            menuItensSelector.each(function (index, targetMenuItem) {
                if (!hasFormStructureOrDataErrors) {
                    let targetMenuItemSelector = $(targetMenuItem);
                    let targetMenuInputAreaSelector = targetMenuItemSelector.find('input[id^="ddm_plugin_navigation_area_"]');
                    let targetMenuInputOrderSelector = targetMenuItemSelector.find('input[id^="ddm_plugin_navigation_order_"]');
                    let targetMenuInputCustomSelector = targetMenuItemSelector.find('input[id^="ddm_plugin_navigation_custom_"]');
                    let targetMenuInputPageIdSelector = targetMenuItemSelector.find('input[id^="ddm_plugin_navigation_pageid_"]');
                    let targetMenuInputSelectedParentSelector = targetMenuItemSelector.find('input[id^="ddm_plugin_navigation_selected_parent_"]');

                    let targetMenuInputAreaValue = targetMenuInputAreaSelector.val();
                    let targetMenuInputOrderValue = targetMenuInputOrderSelector.val();
                    let targetMenuInputCustomValue = targetMenuInputCustomSelector.val();
                    let targetMenuInputPageIdValue = targetMenuInputPageIdSelector.val();
                    if (targetMenuItemSelector.length === 0||targetMenuInputAreaValue===0||targetMenuInputOrderSelector.length === 0||targetMenuInputCustomSelector.length===0||targetMenuInputPageIdSelector.length===0||targetMenuInputSelectedParentSelector.length===0||targetMenuInputOrderValue===undefined||targetMenuInputCustomValue===undefined||targetMenuInputPageIdValue===undefined||targetMenuInputAreaValue===undefined) {
                        registerAndNotifyInternalCriticalError();
                        return;
                    }
                    let targetMenuInputNewOrderValue = (initialOrderIndex+1) * 100;
                    let updateParentId = false;
                    if(targetMenuInputAreaValue==='header_dropdown'){
                        if(nextTargetMenuItemCouldBeChild){//Could be a child menu
                            if(targetMenuInputPageIdValue===''&&targetMenuInputCustomValue==='0'){
                                nextTargetMenuItemCouldBeChild = true;
                                initialOrderChildIndex = 1;
                            } else {
                                updateParentId = true;
                                initialOrderIndex--;
                                targetMenuInputNewOrderValue = ((initialOrderIndex+1) * 100) + initialOrderChildIndex;
                                initialOrderChildIndex++;
                            }
                        } else {
                            if(targetMenuInputPageIdValue!==''||targetMenuInputCustomValue!=='0'){
                                registerAndNotifyInternalCriticalError();
                                return;
                            } else {
                                nextTargetMenuItemCouldBeChild = true;
                                initialOrderChildIndex = 1;
                                let menuItensInputTargetSelectedParentSelector = menuItensInputSelectedParentSelector.filter('[value="' + targetMenuInputOrderValue + '"]');
                                if(menuItensInputTargetSelectedParentSelector.length>0){
                                    menuItensInputTargetSelectedParentSelector.val(targetMenuInputNewOrderValue);
                                }
                            }
                        }
                    } else {
                        nextTargetMenuItemCouldBeChild=false;
                    }
                    targetMenuInputOrderSelector.val(targetMenuInputNewOrderValue);
                    if(updateParentId){
                        targetMenuInputSelectedParentSelector.val((initialOrderIndex+1) * 100);
                    }
                }
                initialOrderIndex++;
            });
            if (hasFormStructureOrDataErrors) {
                if(window.notify!==undefined) {
                    window.notify.error(somethinWrongUnableToSave, '', {fadeOut: 0});
                }
                event.preventDefault();
                event.stopPropagation();
                return;
            }
            formNavigation.submit();
        }
    };

    let ddmPluginUpdateLastSubMenuIcon = function(currentItemSelector, previousItemSelector, previousItemBeforeMoveSelector){
        if(previousItemBeforeMoveSelector===undefined){
            previousItemBeforeMoveSelector = previousItemSelector;
        }
        let previousItemBeforeMoveSelectorInputAreaSelector = previousItemBeforeMoveSelector.find('input[id^="ddm_plugin_navigation_area_"]');
        let previousItemBeforeMoveSelectorSelectLinkToSelector  = previousItemBeforeMoveSelector.find('select[id^="ddm_plugin_navigation_linkto_"]');
        let previousVisibleItemInputAreaSelector = previousItemSelector.find('input[id^="ddm_plugin_navigation_area_"]');
        let previousVisibleItemSelectLinkToSelector = previousItemSelector.find('select[id^="ddm_plugin_navigation_linkto_"]');
        let dropdownLastChildIconSelector = currentItemSelector.find('i.ddm-plugin-dropdown-icon.ddm-plugin-dropdown-last-child');
        let isLastChild = dropdownLastChildIconSelector.length > 0;
        if (isLastChild) {
            let previousItemBeforeMoveAreaInputValue = previousItemBeforeMoveSelectorInputAreaSelector.val();
            let previousItemBeforeMoveLinktoSelectValue = previousItemBeforeMoveSelectorSelectLinkToSelector[0].selectize.getValue();
            if (previousItemBeforeMoveAreaInputValue === 'header_dropdown' && previousItemBeforeMoveLinktoSelectValue !== 'dropdown') {
                let previousItemBeforeMoveDropdownIconSelector = previousItemBeforeMoveSelector.find('i.ddm-plugin-dropdown-icon');
                if (previousItemBeforeMoveDropdownIconSelector.length > 0) {
                    previousItemBeforeMoveDropdownIconSelector.addClass('ddm-plugin-dropdown-last-child');
                    dropdownLastChildIconSelector.removeClass('ddm-plugin-dropdown-last-child');
                }
            }
        } else {
            let previousItemAreaInputValue = previousVisibleItemInputAreaSelector.val();
            let previousItemLinktoSelectValue = previousVisibleItemSelectLinkToSelector[0].selectize.getValue();
            if (previousItemAreaInputValue === 'header_dropdown' && previousItemLinktoSelectValue !== 'dropdown') {
                let dropdownIconSelector = currentItemSelector.find('i.ddm-plugin-dropdown-icon');
                let previousDropdownIconSelector = previousItemSelector.find('i.ddm-plugin-dropdown-icon.ddm-plugin-dropdown-last-child');
                if (previousDropdownIconSelector.length > 0) {
                    dropdownIconSelector.addClass('ddm-plugin-dropdown-last-child');
                    previousDropdownIconSelector.removeClass('ddm-plugin-dropdown-last-child');
                }
            }
        }
    };

    let ddmPluginDetachAllSubMenusClickHandler = function() {
        let thisObj = $(this);

        let currentItemSelector = thisObj.closest('li');

        let dettachAllSubMenusButtonSelector = currentItemSelector.find('button.btn.ddm-plugin-detach-all-sub-menus');
        let hideOnFrontEndHiddenIcon = currentItemSelector.find('i.ddm-plugin-hidden-on-frontend-icon');

        let currentTargetOrder = currentItemSelector.find('input[id^="ddm_plugin_navigation_order_"]');
        let currentTargetOrderValue = currentTargetOrder.val();

        let selectLinkToSelector = currentItemSelector.find('select[id^="ddm_plugin_navigation_linkto_"]');

        if (currentItemSelector.length === 0 || dettachAllSubMenusButtonSelector.length === 0 || hideOnFrontEndHiddenIcon.length === 0 || currentTargetOrder.length === 0 || selectLinkToSelector.length === 0 || currentTargetOrderValue === undefined || selectLinkToSelector[0].selectize === undefined) {
            registerAndNotifyInternalCriticalError();
            return;
        }
        let selectLinkToSelectizeObj = selectLinkToSelector[0].selectize;


        //Run through all dropdowns elements
        let currentLiChildSiblings = currentItemSelector.siblings('li').find('select[id^="ddm_plugin_navigation_parent_"]>option[value="' + currentTargetOrderValue + '"][selected="selected"]');
        currentLiChildSiblings.each(function (index, selectedOption) {
            if (!hasFormStructureOrDataErrors) {
                let selectedOptionOfSelectParentSelector = $(selectedOption);
                let selectParentSelector = selectedOptionOfSelectParentSelector.parent('select');
                let currentSubMenuItemSelector = $(selectedOption).closest('li');
                let dropdownIconSelector = currentSubMenuItemSelector.find('i.ddm-plugin-dropdown-icon');
                let dettachSubMenuButtonSelector = currentSubMenuItemSelector.find('button.ddm-plugin-detach-sub-menu');
                let selectParentContainerSelector = currentSubMenuItemSelector.find('span[id^="ddm_plugin_navigation_parent_"][id$="_container"]');
                let inputSelectedParentSelector = currentSubMenuItemSelector.find('input[id^="ddm_plugin_navigation_selected_parent_"]');
                let currentItemInputAreaSelector = currentSubMenuItemSelector.find('input[id^="ddm_plugin_navigation_area_"]');

                if (selectedOptionOfSelectParentSelector.length === 0 || selectParentSelector.length === 0 || selectParentSelector[0].selectize === undefined || currentSubMenuItemSelector.length === 0 || dropdownIconSelector.length === 0 || dettachSubMenuButtonSelector.length === 0 || selectParentContainerSelector.length === 0 || inputSelectedParentSelector.length === 0 || currentItemInputAreaSelector.length === 0) {
                    registerAndNotifyInternalCriticalError();
                    return;
                }
                updateLinksToDropdownSelectionOptionAfterDetach(currentSubMenuItemSelector);

                dropdownIconSelector.addClass('ddm-plugin-hide');
                dettachSubMenuButtonSelector.addClass('ddm-plugin-hide');
                selectParentContainerSelector.removeClass('ddm-plugin-hide');

                selectParentSelector[0].selectize.setValue('NULL', true);
                inputSelectedParentSelector.val('');
                currentItemInputAreaSelector.val('header');
            }
        });

        selectLinkToSelectizeObj.enable();

        dettachAllSubMenusButtonSelector.prop('disabled', true);
        if (hideOnFrontEndHiddenIcon.length > 0) {
            hideOnFrontEndHiddenIcon.removeClass('ddm-plugin-hide');
        }
    };

    let ddmPluginDetachSubMenuClickHandler = function(){
        let thisObj = $(this);

        let currentItemSelector = thisObj.closest('li');

        let dropdownIconSelector = currentItemSelector.find('i.ddm-plugin-dropdown-icon');
        let dettachSubMenuButtonSelector = currentItemSelector.find('button.ddm-plugin-detach-sub-menu');
        let selectParentContainerSelector = currentItemSelector.find('span[id^="ddm_plugin_navigation_parent_"][id$="_container"]');
        let currentItemInputAreaSelector = currentItemSelector.find('input[id^="ddm_plugin_navigation_area_"]');
        let currentItemInputSelectedParentSelector = currentItemSelector.find('input[id^="ddm_plugin_navigation_selected_parent_"]');
        let currentItemInputSelectedParentVal = currentItemInputSelectedParentSelector.val();
        let dettachSubMenuButtonAssociationLabelData = dettachSubMenuButtonSelector.data('associationlabel');
        if (currentItemSelector.length === 0 || dettachSubMenuButtonSelector.length === 0 || selectParentContainerSelector.length === 0 || currentItemInputAreaSelector.length === 0 || currentItemInputSelectedParentSelector.length === 0 || currentItemInputSelectedParentVal===undefined || dettachSubMenuButtonAssociationLabelData===undefined) {
            registerAndNotifyInternalCriticalError();
            return;
        }
        let previousVisibleItemSelector = currentItemSelector.prevAll('li:not(.ddm-plugin-hide):first');
        ddmPluginUpdateLastSubMenuIcon(currentItemSelector, previousVisibleItemSelector);
        dropdownIconSelector.addClass('ddm-plugin-hide');
        dettachSubMenuButtonSelector.addClass('ddm-plugin-hide');
        let notAssociatedString = (document.DDM_PLUGIN_TRANSLATED_NOT_ASSOCIATED!==undefined)?document.DDM_PLUGIN_TRANSLATED_NOT_ASSOCIATED:'Not associated';
        dettachSubMenuButtonSelector.data('associationlabel',notAssociatedString);
        selectParentContainerSelector.removeClass('ddm-plugin-hide');
        updateLinksToDropdownSelectionOptionAfterDetach(currentItemSelector);
        updateMenuWithSubMenusLinkToAndDetachAllAfterDetachOrDelete(currentItemSelector, previousVisibleItemSelector);
        currentItemInputAreaSelector.val('header');
        let lastInputSelectedParentOfVisibleItemWithSameParent = currentItemSelector.parent().find('li:not(.ddm-plugin-hide) input[id^="ddm_plugin_navigation_selected_parent_"][value="'+currentItemInputSelectedParentVal+'"]:last');
        if(lastInputSelectedParentOfVisibleItemWithSameParent.length>0){
            currentItemSelector.detach().insertAfter(lastInputSelectedParentOfVisibleItemWithSameParent.closest('li'));
        }
    };

    let registerAndNotifyInternalCriticalError = function(errorMessage) {
        hasFormStructureOrDataErrors = true;
        if(errorMessage) {
            console.error(errorMessage);
        }
        if(window.notify!==undefined) {
            window.notify.error(somethinWrongUnableToSave, '', {fadeOut: 0});
        }
    };

    let auxLiText = $('#aux_litext');
    let auxLiTextSelectParentSelector = auxLiText.find('select[id^="ddm_plugin_navigation_parent_"]');
    if (auxLiText.length === 0 || auxLiTextSelectParentSelector.length===0){
        registerAndNotifyInternalCriticalError();
    }

    let ddmPluginSortableCreateNewItemClickHandler = function(){
        let menuItensContainerSelector = $('ul#ddmPluginSortableNavigation');
        let menuItensContainerSelectorNextMenuOrderData = menuItensContainerSelector.data('next-menu-order');
        let allMenuItensSelector = menuItensContainerSelector.find('li');
        let addNewMenuItemItem = menuItensContainerSelector.find('span#addItem');
        let allNotDeletedMenuItensSelector = menuItensContainerSelector.find('li:not(.ddm-plugin-hide)');
        if (auxLiText.length === 0 || menuItensContainerSelector.length===0 || menuItensContainerSelectorNextMenuOrderData===undefined) {
            registerAndNotifyInternalCriticalError();
            return;
        }
        let newMenuItemIdentityIndex = allMenuItensSelector.length;
        let newMenuItemHtmlFromTemplate = auxLiText.html();
        if(newMenuItemHtmlFromTemplate.trim()===''){
            registerAndNotifyInternalCriticalError();
            return;
        }
        while (newMenuItemHtmlFromTemplate.indexOf('LI_ID') !== -1) {
            newMenuItemHtmlFromTemplate = newMenuItemHtmlFromTemplate.replace('LI_ID', newMenuItemIdentityIndex);
        }
        newMenuItemHtmlFromTemplate = newMenuItemHtmlFromTemplate.replace('LI_ORDER', menuItensContainerSelectorNextMenuOrderData);
        let newMenuItemLabelDefaultValue = (document.DDM_PLUGIN_TRANSLATED_NEW_NAVIGATION_ITEM_DEFAULT_LABEL_VALUE!==undefined)?document.DDM_PLUGIN_TRANSLATED_NEW_NAVIGATION_ITEM_DEFAULT_LABEL_VALUE:'New menu';
        let newMenuItemLabelValue = newMenuItemLabelDefaultValue;
        let newMenuItemHtmlFromTemplateSuffixNumber = 1;
        while (allNotDeletedMenuItensSelector.find('input[id^="ddm_plugin_navigation_label_"][value="'+newMenuItemLabelValue+'"]').length!==0){
            newMenuItemLabelValue = newMenuItemLabelDefaultValue + ' #' + newMenuItemHtmlFromTemplateSuffixNumber;
            newMenuItemHtmlFromTemplateSuffixNumber++;
        }
        newMenuItemHtmlFromTemplate = newMenuItemHtmlFromTemplate.replace('LI_LABEL', newMenuItemLabelValue);
        let newMenuItemSelector = $(newMenuItemHtmlFromTemplate);
        newMenuItemSelector.insertBefore(addNewMenuItemItem);//Adds menu item at last position of Navigation menu - before button add new

        menuItensContainerSelector.data('next-menu-order', parseInt(menuItensContainerSelectorNextMenuOrderData) + 100);
        let newMenuItemSelectLinkToSelector = newMenuItemSelector.find('select[id^="ddm_plugin_navigation_linkto_"]');
        let newMenuItemSelectParentSelector = newMenuItemSelector.find('select[id^="ddm_plugin_navigation_parent_"]');
        let newMenuItemInputLabelSelector =  newMenuItemSelector.find('input[id^="ddm_plugin_navigation_label_"]');
        let newMenuItemInputLinkSelector = newMenuItemSelector.find('input[id^="ddm_plugin_navigation_link_"]');
        let newMenuItemAnchorWithRemoveItemClassSelector = newMenuItemSelector.find('a.remove-item');
        let newMenuItemButtonDetachAllSubMenus = newMenuItemSelector.find('.ddm-plugin-detach-all-sub-menus');
        let newMenuItemButtonDetachSubMenu = newMenuItemSelector.find('.ddm-plugin-detach-sub-menu');
        if(newMenuItemSelector.length===0||newMenuItemSelectLinkToSelector.length===0||newMenuItemSelectParentSelector.length===0||newMenuItemInputLabelSelector.length===0||newMenuItemInputLinkSelector.length===0||newMenuItemAnchorWithRemoveItemClassSelector.length===0||newMenuItemButtonDetachAllSubMenus.length===0||newMenuItemButtonDetachSubMenu.length===0) {
            registerAndNotifyInternalCriticalError();
            return;
        }
        newMenuItemSelectLinkToSelector = newMenuItemSelectLinkToSelector.selectize();
        newMenuItemSelectParentSelector = newMenuItemSelectParentSelector.selectize();

        if(newMenuItemSelectLinkToSelector.length===0||newMenuItemSelectParentSelector.length===0||newMenuItemSelectLinkToSelector[0].selectize===undefined||newMenuItemSelectParentSelector[0].selectize===undefined) {
            registerAndNotifyInternalCriticalError();
            return;
        }
        let newMenuItemSelectLinkToSelectizeObj = newMenuItemSelectLinkToSelector[0].selectize;
        let newMenuItemSelectParentSelectizeObj = newMenuItemSelectParentSelector[0].selectize;

        newMenuItemButtonDetachAllSubMenus.on('click', ddmPluginDetachAllSubMenusClickHandler);
        newMenuItemButtonDetachSubMenu.on('click', ddmPluginDetachSubMenuClickHandler);
        newMenuItemAnchorWithRemoveItemClassSelector.on('click', ddmPluginSortableRemoveItemClickHandler);
        newMenuItemInputLabelSelector.on('focus', ddmPluginInputNavigationLabelFocusHandler);
        newMenuItemInputLinkSelector.on('focus', ddmPluginInputNavigationLinkFocusHandler);
        newMenuItemSelectLinkToSelectizeObj.on('focus', ddmPluginSelectLinkToSelectizeFocusHandler);
        newMenuItemSelectParentSelectizeObj.on('focus', ddmPluginSelectParentSelectizeFocusHandler);
    };

    let ddmPluginWindowLoadHandler = function () {
        let ddmPluginSortable = $('#ddmPluginSortableNavigation');

        let menuItemCouldBeDropped = function(currentLiId, prevLiId, nextLiId) {
            let allowToDrop = true;
            let currentItemAreaInputSelectorText = currentLiId!==undefined ? '#ddm_plugin_navigation_area_' + currentLiId : '';
            let currentItemParentSelectSelectorText = currentLiId!==undefined ? '#ddm_plugin_navigation_parent_' + currentLiId : '';
            let currentItemLinktoSelectSelectorText = currentLiId!==undefined ? '#ddm_plugin_navigation_linkto_' + currentLiId : '';
            let previousItemAreaInputSelectorText = prevLiId!==undefined ? '#ddm_plugin_navigation_area_' + prevLiId : '';
            let previousItemParentSelectSelectorText = prevLiId!==undefined ? '#ddm_plugin_navigation_parent_' + prevLiId : '';
            let previousItemLinktoSelectSelectorText = prevLiId!==undefined ? '#ddm_plugin_navigation_linkto_' + prevLiId : '';
            let previousItemOrderSelectorText = prevLiId!==undefined ? '#ddm_plugin_navigation_order_' + prevLiId : '';
            let nextItemAreaInputSelectorText = nextLiId!==undefined ?'#ddm_plugin_navigation_area_' + nextLiId : '';
            let nextItemParentSelectSelectorText = nextLiId!==undefined ? '#ddm_plugin_navigation_parent_' + nextLiId : '';
            let nextItemLinktoSelectSelectorText = nextLiId!==undefined ? '#ddm_plugin_navigation_linkto_' + nextLiId : '';
            if(currentItemAreaInputSelectorText && currentItemParentSelectSelectorText && currentItemLinktoSelectSelectorText) {
                let currentItemAreaInputSelector = $(currentItemAreaInputSelectorText);
                let currentItemLinktoSelectSelector = $(currentItemLinktoSelectSelectorText);
                let currentItemParentSelectSelector = $(currentItemParentSelectSelectorText);
                let currentItemAreaValue = currentItemAreaInputSelector.val();
                let currentItemLinktoValue = currentItemLinktoSelectSelector.length ? currentItemLinktoSelectSelector[0].selectize.getValue() : '';
                let currentItemParentValue = currentItemParentSelectSelector.length ? currentItemParentSelectSelector[0].selectize.getValue() : '';
                let previousItemAreaValue = '';
                let previousItemOrderValue = '';
                let previousItemLinktoValue = '';
                let previousItemParentValue = '';
                if (previousItemAreaInputSelectorText && previousItemParentSelectSelectorText && previousItemLinktoSelectSelectorText && previousItemOrderSelectorText) {
                    let previousItemAreaInputSelector = $(previousItemAreaInputSelectorText);
                    let previousItemOrderSelector = $(previousItemOrderSelectorText);
                    let previousItemLinktoSelectSelector = $(previousItemLinktoSelectSelectorText);
                    let previousItemParentSelectSelector = $(previousItemParentSelectSelectorText);
                    previousItemAreaValue = previousItemAreaInputSelector.val();
                    previousItemOrderValue = previousItemOrderSelector.val();
                    previousItemLinktoValue = previousItemLinktoSelectSelector.length ? previousItemLinktoSelectSelector[0].selectize.getValue() : '';
                    previousItemParentValue = previousItemParentSelectSelector.length ? previousItemParentSelectSelector[0].selectize.getValue() : '';
                }
                let nextItemAreaValue = '';
                let nextItemLinktoValue = '';
                if (nextItemAreaInputSelectorText && nextItemParentSelectSelectorText && nextItemLinktoSelectSelectorText) {
                    let nextItemAreaInputSelector = $(nextItemAreaInputSelectorText);
                    let nextItemLinktoSelectSelector = $(nextItemLinktoSelectSelectorText);
                    nextItemAreaValue = nextItemAreaInputSelector.val();
                    nextItemLinktoValue = nextItemLinktoSelectSelector.length ? nextItemLinktoSelectSelector[0].selectize.getValue() : '';
                }
                if (currentItemAreaValue === 'header_dropdown' && currentItemLinktoValue !== 'dropdown') {//If it's a child dropdown item
                    if (previousItemAreaValue !== 'header_dropdown') {
                        allowToDrop = false;
                    } else if (previousItemLinktoValue !== 'dropdown') {
                        if (currentItemParentValue !== previousItemParentValue) {
                            allowToDrop = false;
                        }
                    } else if (currentItemParentValue !== previousItemOrderValue) {
                        allowToDrop = false;
                    }
                } else {//If it's a dropdown or non dropdown child page/custom page or custom link
                    if (previousItemAreaValue === 'header_dropdown') {
                        if (previousItemLinktoValue === 'dropdown') {
                            allowToDrop = false;
                        } else if (nextItemAreaValue === 'header_dropdown') {
                            if (nextItemLinktoValue !== 'dropdown') {
                                allowToDrop = false;
                            }
                        }
                    }
                }
            } else {
                allowToDrop = false;
            }
            return allowToDrop && $('li#currentLiId').prevAll('span#addItem:first').length===0;
        };

        let registerAndNotifyDroppedMenuItemCriticalError = function(errorMessage) {
            if(errorMessage) {
                registerAndNotifyInternalCriticalError(errorMessage);
            } else {
                registerAndNotifyInternalCriticalError('Dropped navigation item has invalid structure/data.');
            }
        };

        let updateDropdownChildItemsPosition = function(currentItemSelector, previousItemAtStartSortingSelector) {
            let currentLiId = currentItemSelector.attr('id');

            if (currentLiId===undefined){
                registerAndNotifyDroppedMenuItemCriticalError();
                return;
            }

            let currentItemAreaInputSelector = $('#ddm_plugin_navigation_area_' + currentLiId);
            let currentItemParentSelectSelector = $('#ddm_plugin_navigation_parent_' + currentLiId);
            let currentItemLinktoSelectSelector = $('#ddm_plugin_navigation_linkto_' + currentLiId);
            let currentItemOrderInputSelector = $('#ddm_plugin_navigation_order_' + currentLiId);

            if (currentItemAreaInputSelector.length===0 || currentItemParentSelectSelector.length===0 || currentItemLinktoSelectSelector.length===0 || currentItemOrderInputSelector.length===0) {
                registerAndNotifyDroppedMenuItemCriticalError();
                return;
            }

            let currentItemOrderInputValue = currentItemOrderInputSelector.val();
            let currentItemAreaInputValue = currentItemAreaInputSelector.val();
            if(!currentItemOrderInputValue||!currentItemAreaInputValue||currentItemAreaInputValue===''){
                registerAndNotifyDroppedMenuItemCriticalError();
                return;
            }
            if(currentItemAreaInputValue==='header_dropdown') {
                let hasPreviousItemAtStartSorting = previousItemAtStartSortingSelector.length>0;

                let previousItemAtStartSortingAreaInputSelector;
                let previousItemAtStartSortingParentSelectSelector;
                let previousItemAtStartSortingLinktoSelectSelector;
                let previousItemAtStartSortingOrderSelector;

                let previousItemAreaInputSelector;
                let previousItemParentSelectSelector;
                let previousItemLinktoSelectSelector;
                let previousItemOrderSelector;

                if (hasPreviousItemAtStartSorting) {
                    let previousLiAtStartSortingId = previousItemAtStartSortingSelector.attr('id');

                    if (previousLiAtStartSortingId === undefined) {
                        registerAndNotifyDroppedMenuItemCriticalError('Navigation item previous of dragged navigation item has invalid structure/data.');
                        return;
                    }

                    previousItemAtStartSortingAreaInputSelector = $('#ddm_plugin_navigation_area_' + previousLiAtStartSortingId);
                    previousItemAtStartSortingParentSelectSelector = $('#ddm_plugin_navigation_parent_' + previousLiAtStartSortingId);
                    previousItemAtStartSortingLinktoSelectSelector = $('#ddm_plugin_navigation_linkto_' + previousLiAtStartSortingId);
                    previousItemAtStartSortingOrderSelector = $('#ddm_plugin_navigation_order_' + previousLiAtStartSortingId);

                    if (previousItemAtStartSortingAreaInputSelector.length === 0 || previousItemAtStartSortingParentSelectSelector.length === 0 || previousItemAtStartSortingLinktoSelectSelector.length === 0 || previousItemAtStartSortingOrderSelector.length === 0) {
                        registerAndNotifyDroppedMenuItemCriticalError('Navigation item previous of dragged navigation item has invalid structure/data.');
                        return;
                    }
                }

                let previousItemSelector = currentItemSelector.prevAll('li:not(.ddm-plugin-hide):first');
                let hasPreviousItem = previousItemSelector.length > 0;

                if (hasPreviousItem) {
                    let previousLiId = previousItemSelector.attr('id');

                    if (previousLiId === undefined) {
                        registerAndNotifyDroppedMenuItemCriticalError('Navigation item previous of dropped navigation item has invalid structure/data.');
                        return;
                    }

                    previousItemAreaInputSelector = $('#ddm_plugin_navigation_area_' + previousLiId);
                    previousItemParentSelectSelector = $('#ddm_plugin_navigation_parent_' + previousLiId);
                    previousItemLinktoSelectSelector = $('#ddm_plugin_navigation_linkto_' + previousLiId);
                    previousItemOrderSelector = $('#ddm_plugin_navigation_order_' + previousLiId);

                    if (previousItemAreaInputSelector.length === 0 || previousItemParentSelectSelector.length === 0 || previousItemLinktoSelectSelector.length === 0 || previousItemOrderSelector.length === 0) {
                        registerAndNotifyDroppedMenuItemCriticalError('Navigation item previous of dropped navigation item has invalid structure/data.');
                        return;
                    }
                }

                let currentItemParentSelectValue = currentItemParentSelectSelector[0].selectize.getValue();
                let currentItemLinktoSelectValue = currentItemLinktoSelectSelector[0].selectize.getValue();

                if (currentItemLinktoSelectValue === 'dropdown') {
                    let currentLiChildSiblings = currentItemSelector.siblings('li').find('select[id^="ddm_plugin_navigation_parent_"]>option[value="'+ currentItemOrderInputValue +'"][selected="selected"]');
                    let insertAfter = currentItemSelector;
                    currentLiChildSiblings.each(function(index, selectedOption){
                        insertAfter = $(selectedOption).closest('li').detach().insertAfter(insertAfter);
                    });
                } else {
                    if (!hasPreviousItemAtStartSorting){
                        registerAndNotifyDroppedMenuItemCriticalError();
                        return;
                    }

                    if (currentItemParentSelectValue !== 0 && !currentItemParentSelectValue) {
                        registerAndNotifyDroppedMenuItemCriticalError();
                        return;
                    }
                    if (!hasPreviousItemAtStartSorting || !hasPreviousItem) {
                        registerAndNotifyDroppedMenuItemCriticalError('Navigation items have invalid structure/data.');
                        return;
                    }

                    ddmPluginUpdateLastSubMenuIcon(currentItemSelector, previousItemSelector, previousItemAtStartSortingSelector);
                }
            }
        };

        let mainContentSelector = $('body>main>div.main-wrapper>div.main-content');
        let mainContentScrollTopValue = mainContentSelector.scrollTop();
        mainContentSelector.on('scroll', function(){
            let scrolledDiv = $(this);
            mainContentScrollTopValue = scrolledDiv.scrollTop();
        });
        let cancellingSort = false;
        let previousLiSelectorOnStartSort;
        let nextLiSelectorOnStartSort;
        let mainContentScrollTopValueOnStartSort;
        ddmPluginSortable.sortable({
            placeholder: 'row ddm-plugin-sortable-highlight',
            items: 'li:not(.ddm-plugin-not-sortable)',
            cancel: '.ddm-plugin-not-sortable,a,button,.selectize,.selectize *,input[type="text"]',
            helper: 'clone',
            axis:'y',
            opacity: 0.5,
            beforeStop: function(event, ui) {
                let currentLiId = ui.item.attr('id');
                let prevLiId = ui.item.prevAll('li:not(.ddm-plugin-hide):first').attr('id');
                let nextLiId = ui.placeholder.nextAll('li:not(.ddm-plugin-hide):first').attr('id');
                let allowToDrop = menuItemCouldBeDropped(currentLiId,prevLiId,nextLiId);
                if(!allowToDrop){
                    let thisSortable = $(this);
                    thisSortable.sortable('disable');
                    cancellingSort = true;
                    let mainContentScrollTopDifferenceSinceStartSort = mainContentScrollTopValue - mainContentScrollTopValueOnStartSort;
                    let adjustedOriginalPositionTop = ui.originalPosition.top - mainContentScrollTopDifferenceSinceStartSort;
                    let originalPosition = {
                        left: ui.originalPosition.left,
                        top: adjustedOriginalPositionTop,
                        opacity: 1
                    };
                    let uiItem = ui.item;
                    let realWidth = uiItem.innerWidth();
                    let uiItemClone = ui.item.clone(false);
                    uiItemClone.insertAfter(ui.placeholder);
                    uiItemClone.removeClass('originalSpace');
                    uiItemClone.removeAttr('style');
                    uiItemClone.css('top',ui.position.top);
                    uiItemClone.css('left',originalPosition.left);
                    uiItemClone.css('position', 'absolute');
                    uiItemClone.css('opacity', '0.5');
                    uiItemClone.css('height', '50px');
                    uiItemClone.css('max-height', '50px');
                    uiItemClone.css('width',realWidth+'px');
                    uiItemClone.css('max-width',realWidth+'px');
                    uiItemClone.animate(originalPosition, 'slow', function() {
                        let uiItemClone = $(this);
                        uiItem.removeClass('originalSpace');
                        uiItem.removeAttr('style');
                        // Cancel the sortable action to return it to it's origin
                        uiItemClone.remove();
                        thisSortable.sortable('enable');
                        previousLiSelectorOnStartSort = undefined;
                        nextLiSelectorOnStartSort = undefined;
                        cancellingSort = false;
                    });
                } else {
                    ui.item.removeClass('originalSpace');
                }
            },
            stop: function (e, ui) {
                let thisSortable = $(this);
                if(cancellingSort){
                    thisSortable.sortable('cancel');
                }
                ui.item.removeAttr('style');
            },
            start: function (e, ui) {
                mainContentScrollTopValueOnStartSort = mainContentScrollTopValue;
                if(!ui.item.is('li')){
                    e.preventDefault();
                    return false;
                }
                cancellingSort = false;
                previousLiSelectorOnStartSort = ui.item.prevAll('li:not(.ddm-plugin-hide):first');
                nextLiSelectorOnStartSort = ui.placeholder.nextAll('li:not(.ddm-plugin-hide):first');
                ui.item.addClass('originalSpace');
                ui.item.removeAttr('style');
            },
            sort: function (event, ui) {
                let currentLiId = ui.item.attr('id');
                let prevLiId = ui.placeholder.prevAll('li:not(.ddm-plugin-hide):first').attr('id');
                let nextLiId = ui.placeholder.nextAll('li:not(.ddm-plugin-hide):first').attr('id');
                let allowToDrop = menuItemCouldBeDropped(currentLiId,prevLiId,nextLiId);
                if(!allowToDrop){
                    ui.placeholder.addClass('forbidden');
                } else {
                    ui.placeholder.removeClass('forbidden');
                }
            },
            update: function (event, ui) {
                if(!cancellingSort) {
                    updateDropdownChildItemsPosition(ui.item, previousLiSelectorOnStartSort);
                    previousLiSelectorOnStartSort = undefined;
                    nextLiSelectorOnStartSort = undefined;
                }
            },
        });
        ddmPluginSortable.disableSelection();
    };

    let allSelectNavigationLinkToSelector = $('select[id^="ddm_plugin_navigation_linkto_"]').filter(':not([id$="LI_ID"])');
    allSelectNavigationLinkToSelector.selectize();

    let allSelectNavigationParentSelector = $('select[id^="ddm_plugin_navigation_parent_"]').filter(':not([id$="LI_ID"])');
    allSelectNavigationParentSelector.selectize();

    allSelectNavigationLinkToSelector.each(function (index, selectedOption) {
        if (!hasFormStructureOrDataErrors) {
            let currentLinkToSelectizeObj = $(selectedOption)[0].selectize;
            if (currentLinkToSelectizeObj === undefined) {
                registerAndNotifyInternalCriticalError();
                return;
            }
            currentLinkToSelectizeObj.on('focus', ddmPluginSelectLinkToSelectizeFocusHandler);
        }
    });

    allSelectNavigationParentSelector.each(function (index, selectedOption) {
        if (!hasFormStructureOrDataErrors) {
            let currentParentSelectizeObj = $(selectedOption)[0].selectize;
            if (currentParentSelectizeObj === undefined) {
                registerAndNotifyInternalCriticalError();
                return;
            }
            currentParentSelectizeObj.on('focus', ddmPluginSelectParentSelectizeFocusHandler);
        }
    });

    $('.ddm-plugin-detach-all-sub-menus').on('click', ddmPluginDetachAllSubMenusClickHandler);

    $('.ddm-plugin-detach-sub-menu').on('click', ddmPluginDetachSubMenuClickHandler);

    $('a.create-new-item').on('click', ddmPluginSortableCreateNewItemClickHandler);

    $('a.remove-item').on('click', ddmPluginSortableRemoveItemClickHandler);

    $('input[id^="ddm_plugin_navigation_label_"]').on('focus', ddmPluginInputNavigationLabelFocusHandler);

    $('input[id^="ddm_plugin_navigation_link_"]').on('focus', ddmPluginInputNavigationLinkFocusHandler);

    $('#ddmPluginSaveButton').on('click', ddmPluginSaveButtonClickHandler);

    $(window).on('load', ddmPluginWindowLoadHandler);
});
