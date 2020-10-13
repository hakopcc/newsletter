function loadLocationsChild(url, level, id, childLevel) {

    if (!isNaN(id)) {
        $.get(url + "/location.php", {
            id: id,
            level: level,
            childLevel: childLevel,
            type: 'byId'
        }, function (location) {
            var text = $("#l_location_" + childLevel).text();
            if (location != "empty") {
                $("#default_L" + childLevel + "_id").html(location);
                $("#l_location_" + childLevel).html(text);
            } else
                $("#default_L" + childLevel + "_id").html('<option id=\"l_location_' + childLevel + '\" value=\"\">' + text + '</option>');
        });
    }
}

function loadAllLocations(url, level) {
    $.get(url + "/location.php", {level: level, type: 'All'}, function (location) {
        if (location != "empty") {
            var text = $("#l_location_" + level).text();
            $("#default_L" + level + "_id").html(location);
            $("#l_location_" + level).html(text);
        }
    });
}

function formLocations_submit(level, form) {
    if (level <= 3) {
        for (i = (level + 1); i <= 4; i++)
            if ($('#select_location' + i).val())
                $('#select_location' + i).remove();
    }
    form.submit();
}

function loadLocationSitemgrMembers(url, edir_locations, level, childLevel, id, extra_loc = null) {
    if (!isNaN(id)) {
        let getParamsObj = {
            id: id,
            level: level,
            childLevel: childLevel,
            type: 'byId'
        };
        let locationIdSuffix = "";
        if (extra_loc) {
            getParamsObj.extraLocation = extra_loc;
            locationIdSuffix = "_extra_loc_" + extra_loc;
        }

        let locationIdFieldSuffix = "_field" + locationIdSuffix;
        let locationIdLinkSuffix = "_link" + locationIdSuffix;
        let locationChildPostPreffixType1 = "location" + childLevel;
        let locationChildPostPreffixType2 = "location_" + childLevel;
        let locationChildSelector = "#" + locationChildPostPreffixType2 + locationIdSuffix;
        let lLocationChildSelector = "#l_" + locationChildPostPreffixType2 + locationIdSuffix;
        let divLocationChildSelector = "#div_" + locationChildPostPreffixType2 + locationIdSuffix;
        let divImgLoadingChildSelector = "#div_img_loading_" + childLevel + locationIdSuffix;
        let boxNoLocationFoundChildSelector = "#box_no_location_found_" + childLevel + locationIdSuffix;
        let divSelectChildSelector = "#div_select_" + childLevel + locationIdSuffix;
        let divNewLocationChildLinkSelector = "#div_new_" + locationChildPostPreffixType1 + locationIdLinkSuffix;

        let getFunction = function (location) {
            if (location !== "empty") {
                let text = $(lLocationChildSelector).text();
                $(locationChildSelector).html(location);
                $(lLocationChildSelector).html(text);
                $(locationChildSelector).css('display', '');
                try {
                    $(divSelectChildSelector).css('display', '');
                } catch (e) {
                }
                display_level_limit = childLevel;
            } else {
                if (!id) {
                    $(divLocationChildSelector).css("display", 'none');
                } else {
                    try {
                        $(divSelectChildSelector).css('display', '');
                    } catch (e) {
                    }
                    $(boxNoLocationFoundChildSelector).css('display', '');
                }
            }

            if (childLevel && id) {
                $(divNewLocationChildLinkSelector).css('display', '');
            } else {
                $(divNewLocationChildLinkSelector).css('display', 'none');
            }
            $(divImgLoadingChildSelector).css('display', 'none');
        };

        let edir_locations_splitted = edir_locations.split(',');

        for (i = 0; i < edir_locations_splitted.length; i++) {
            if (edir_locations_splitted[i] > level) {
                let locationIdPostPreffixType1 = "location" + edir_locations_splitted[i];
                let locationIdPostPreffixType2 = "location_" + edir_locations_splitted[i];
                let locationId = locationIdPostPreffixType2 + locationIdSuffix;
                let lLocationId = "l_" + locationId;

                let locationIdSelector = "#" + locationId;
                let lLocationIdSelector = "#" + lLocationId;
                let divLocationSelector = "#div_" + locationIdPostPreffixType2 + locationIdSuffix;
                let newLocationSelector = "#new_" + locationIdPostPreffixType1 + locationIdFieldSuffix;
                let divNewLocationSelector = "#div_new_" + locationIdPostPreffixType1 + locationIdFieldSuffix;

                let text = $(lLocationIdSelector).text();
                $(locationIdSelector).html("<option id=\"" + lLocationId + "\" value=\"\">" + text + "</option>");
                $(divLocationSelector).css('display', 'none');
                $(newLocationSelector).attr('value', '');
                $(divNewLocationSelector).css('display', 'none');
            }
        }

        $(locationChildSelector).css('display', 'none');
        $(divLocationChildSelector).css("display", "");
        $(divImgLoadingChildSelector).css('display', '');
        $(boxNoLocationFoundChildSelector).css('display', 'none');
        try {
            $(divSelectChildSelector).css('display', 'none');
        } catch (e) {
        }

        $.get(url + "/location.php", getParamsObj, getFunction);
    }
}

function loadLocation(url, edir_locations, level, childLevel, id, showClear, newStyle, extra_loc = null) {
    if (!isNaN(id)) {
        let getParamsObj = {
            id: id,
            level: level,
            childLevel: childLevel,
            type: 'byId'
        };
        let locationIdSuffix = "";
        if (extra_loc) {
            getParamsObj.extraLocation = extra_loc;
            locationIdSuffix = "_extra_loc_" + extra_loc;
        }

        let locationIdFieldSuffix = "_field" + locationIdSuffix;
        let locationIdLinkSuffix = "_link" + locationIdSuffix;
        let locationChildPostPreffixType1 = "location" + childLevel;
        let locationChildPostPreffixType2 = "location_" + childLevel;
        let locationLevelPostPreffixType2 = "location_" + level;
        let locationsClearPostPreffix = "locations_clear";
        let locationChildSelector = "#" + locationChildPostPreffixType2 + locationIdSuffix;

        let locationLevelSelector = "#" + locationLevelPostPreffixType2 + locationIdSuffix;

        let locationsClearSelector = "#" + locationsClearPostPreffix + locationIdSuffix;
        let lLocationChildSelector = "#l_" + locationChildPostPreffixType2 + locationIdSuffix;
        let divLocationChildSelector = "#div_" + locationChildPostPreffixType2 + locationIdSuffix;
        let divImgLoadingChildSelector = "#div_img_loading_" + childLevel + locationIdSuffix;
        let boxNoLocationFoundChildSelector = "#box_no_location_found_" + childLevel + locationIdSuffix;
        let divSelectChildSelector = "#div_select_" + childLevel + locationIdSuffix;
        let divNewLocationChildLinkSelector = "#div_new_" + locationChildPostPreffixType1 + locationIdLinkSuffix;

        let edir_locations_splitted = edir_locations.split(',');

        let getFunction = function (location) {
            if (location !== "empty") {
                let text = $(lLocationChildSelector).text();
                $(locationChildSelector).html(location);
                $(lLocationChildSelector).html(text);
                if (newStyle !== "1") {
                    $(locationChildSelector).css('display', '');
                }
                try {
                    $(divSelectChildSelector).css('display', '');

                } catch (e) {
                }
                display_level_limit = childLevel;
            } else {
                if (!id) {
                    if (newStyle !== "1") {
                        $(locationChildSelector).css("display", 'none');
                    }
                } else {
                    try {
                        $(divSelectChildSelector).css('display', '');
                    } catch (e) {
                    }
                    $(boxNoLocationFoundChildSelector).css('display', '');
                }
            }

            if (childLevel && id) {
                $(divNewLocationChildLinkSelector).css('display', '');
            } else {
                if (newStyle !== "1") {
                    $(divNewLocationChildLinkSelector).css('display', 'none');
                }
            }

            $(locationLevelSelector).prop('disabled', '');
            $(divImgLoadingChildSelector).css('display', 'none');
            if ($(locationsClearSelector)) {
                $(locationsClearSelector).css('display', '');
            }

            if (location !== "empty") {
                for (i = 0; i < edir_locations_splitted.length; i++) {
                    if (edir_locations_splitted[i] !== childLevel) {
                        let locationIdPostPreffixType2 = "location_" + edir_locations_splitted[i];
                        let divLocationSelector = "#div_" + locationIdPostPreffixType2 + locationIdSuffix;

                        if (newStyle !== "1") {
                            $(divLocationSelector).css('display', 'none');
                        }
                    }
                }
            } else {
                if (newStyle !== "1") {
                    $(divLocationChildSelector).css('display', 'none');
                }
            }

            if (newStyle !== "1") {
                if (edir_locations.length > 0) {
                    let whereElementSelector = '#where';
                    let whereResponsiveElementSelector = '#where_resp';
                    let whereElement = $(whereElementSelector);
                    let whereResponsiveElement = $(whereResponsiveElementSelector);
                    if (whereElement.length) {
                        whereElement.attr("value", "");
                    }
                    if(whereResponsiveElement.length) {
                        whereResponsiveElement.attr("value", "");
                    }

                    let locationsDefaultWhereSelector = "#locations_default_where";
                    let locationsDefaultWhere = $(locationsDefaultWhereSelector);
                    if (locationsDefaultWhere.length) {
                        if (locationsDefaultWhere.hasAttribute("value")) {
                            if (whereElement.length) {
                                whereElement.attr("value", locationsDefaultWhere.attr('value'));
                            }
                            if(whereResponsiveElement.length) {
                                whereResponsiveElement.attr("value", locationsDefaultWhere.attr('value'));
                            }
                        }
                    }

                    for (let i = 0; i < edir_locations.length; i++) {
                        let locationIdPostPreffixType2 = "location_" + edir_locations_splitted[i];
                        let locationSelector = "#" + locationIdPostPreffixType2 + locationIdSuffix;
                        let locationOptionSelectedSelector = locationSelector + " option:selected";
                        if ($(locationOptionSelectedSelector).val() > 0) {
                            let location_title = $(locationOptionSelectedSelector).text();
                            if (whereElement.length) {
                                if (whereElement.hasAttribute("value")){
                                    let whereValue = whereElement.attr("value");
                                    if(whereValue !== '') {
                                        whereElement.attr("value", whereValue + ', ' + location_title);
                                    } else {
                                        whereElement.attr("value", location_title);
                                    }
                                } else {
                                    whereElement.attr("value", location_title);
                                }
                            }
                            //Responsive layout
                            if (whereResponsiveElement.length) {
                                if (whereResponsiveElement.hasAttribute("value")){
                                    let whereResponsiveValue = whereResponsiveElement.attr("value");
                                    if(whereResponsiveValue !== '') {
                                        whereResponsiveElement.attr("value", whereResponsiveValue + ', ' + location_title);
                                    } else {
                                        whereResponsiveElement.attr("value", location_title);
                                    }
                                } else {
                                    whereResponsiveElement.attr("value", location_title);
                                }
                            }
                        }
                    }
                }
            } else {
                if ($().selectpicker) {
                    $('.selectpicker .select').selectpicker('refresh');
                }
            }

            if (showClear) {
                $(locationsClearSelector).css('display', '');
            }
        };

        for (i = 0; i < edir_locations.length; i++) {
            if (edir_locations[i] > level) {
                let locationIdPostPreffixType1 = "location" + edir_locations_splitted[i];
                let locationIdPostPreffixType2 = "location_" + edir_locations_splitted[i];
                let locationId = locationIdPostPreffixType2 + locationIdSuffix;
                let lLocationId = "l_" + locationId;

                let locationIdSelector = "#" + locationId;
                let lLocationIdSelector = "#" + lLocationId;
                let divLocationSelector = "#div_" + locationIdPostPreffixType2 + locationIdSuffix;
                let newLocationSelector = "#new_" + locationIdPostPreffixType1 + locationIdFieldSuffix;
                let divNewLocationSelector = "#div_new_" + locationIdPostPreffixType1 + locationIdFieldSuffix;

                let text = $(lLocationIdSelector).text();
                $(locationIdSelector).html("<option id=\"" + lLocationId + "\" value=\"\">" + text + "</option>");
                $(divLocationSelector).css('display', 'none');
                $(newLocationSelector).attr('value', '');
                $(divNewLocationSelector).css('display', 'none');
            }
        }

        $(locationChildSelector).css('display', 'none');
        $(divLocationChildSelector).css("display", "");
        $(divImgLoadingChildSelector).css('display', '');
        $(boxNoLocationFoundChildSelector).css('display', 'none');
        try {
            $(divSelectChildSelector).css('display', 'none');
        } catch (e) {
        }

        $(locationLevelSelector).prop('disabled', 'true');
        if ($(locationsClearSelector)) {
            $(locationsClearSelector).css('display', 'none');
        }

        $.get(url + "/location.php", getParamsObj, getFunction);
    }
}

function showNewLocationField(level, edir_locations, back, text, extra_loc = null) {

    let locationIdSuffix = "";
    if (extra_loc) {
        locationIdSuffix = "_extra_loc_" + extra_loc;
    }

    let locationIdFieldSuffix = "_field" + locationIdSuffix;
    let locationIdLinkSuffix = "_link" + locationIdSuffix;
    let locationIdBackSuffix = "_back" + locationIdSuffix;
    let locationLevelPostPreffixType1 = "location" + level;

    let newLocationLevelFieldSelector = "#new_" + locationLevelPostPreffixType1 + locationIdFieldSuffix;
    let divNewLocationLevelLinkSelector = "#div_new_" + locationLevelPostPreffixType1 + locationIdLinkSuffix;
    let divNewLocationLevelFieldSelector = "#div_new_" + locationLevelPostPreffixType1 + locationIdFieldSuffix;
    let divNewLocationLevelBackSelector = "#div_new_" + locationLevelPostPreffixType1 + locationIdBackSuffix;

    let splitted_edir_locations = edir_locations.split(',');

    for (i = 0; i < splitted_edir_locations.length; i++) {
        if (splitted_edir_locations[i] >= level) {
            let locationIdPostPreffixType1 = "location" + splitted_edir_locations[i];
            let locationIdPostPreffixType2 = "location_" + splitted_edir_locations[i];
            let locationId = locationIdPostPreffixType2 + locationIdSuffix;

            let locationIdSelector = "#" + locationId;
            let divLocationSelector = "#div_" + locationIdPostPreffixType2 + locationIdSuffix;
            let newLocationSelector = "#new_" + locationIdPostPreffixType1 + locationIdFieldSuffix;
            let divNewLocationSelector = "#div_new_" + locationIdPostPreffixType1 + locationIdFieldSuffix;

            $(locationIdSelector).val('0');
            $(divLocationSelector).css('display', 'none');
            $(newLocationSelector).attr('value', '');
            $(divNewLocationSelector).css('display', 'none');
        }
    }

    $(divNewLocationLevelFieldSelector).css('display', '');
    $(divNewLocationLevelLinkSelector).css('display', 'none');

    if (!back) {
        $(divNewLocationLevelBackSelector).css('display', 'none');
    } else {
        $(divNewLocationLevelBackSelector).css('display', '');
    }

    if (text) {
        $(newLocationLevelFieldSelector).val(text);
    }
}

function hideNewLocationField(level, edir_locations, extra_loc = null) {

    let locationIdSuffix = "";
    if (extra_loc) {
        locationIdSuffix = "_extra_loc_" + extra_loc;
    }

    let locationIdFieldSuffix = "_field" + locationIdSuffix;
    let locationIdLinkSuffix = "_link" + locationIdSuffix;
    let locationLevelPostPreffixType1 = "location" + level;
    let locationLevelPostPreffixType2 = "location_" + level;
    let boxNoLocationFoundLevelSelector = "#box_no_location_found_" + level + locationIdSuffix;

    let locationLevelSelector = "#" + locationLevelPostPreffixType2 + locationIdSuffix;
    let divLocationLevelSelector = "#div_" + locationLevelPostPreffixType2 + locationIdSuffix;
    let divNewLocationLevelLinkSelector = "#div_new_" + locationLevelPostPreffixType1 + locationIdLinkSuffix;

    let splitted_edir_locations = edir_locations.split(',');

    for (let i = 0; i < splitted_edir_locations.length; i++) {
        if (splitted_edir_locations[i] >= level) {
            let locationIdPostPreffixType1 = "location" + splitted_edir_locations[i];
            let locationIdPostPreffixType2 = "location_" + splitted_edir_locations[i];
            let locationId = locationIdPostPreffixType2 + locationIdSuffix;

            let locationIdSelector = "#" + locationId;
            let newLocationSelector = "#new_" + locationIdPostPreffixType1 + locationIdFieldSuffix;
            let divNewLocationSelector = "#div_new_" + locationIdPostPreffixType1 + locationIdFieldSuffix;
            $(locationIdSelector).val('0');
            $(newLocationSelector).attr('value', '');
            $(divNewLocationSelector).css('display', 'none');
        }
    }

    $(divLocationLevelSelector).css('display', '');
    $(divNewLocationLevelLinkSelector).css('display', '');
    if (!$(locationLevelSelector).is(":visible")) {
        $(boxNoLocationFoundLevelSelector).css('display', '');
    }
}
