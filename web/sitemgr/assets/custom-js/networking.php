<script>
    <?php if ($success) { ?>
    notify.success('<?=system_showText(LANG_SITEMGR_SETTINGS_YOURSETTINGSWERECHANGED);?>');
    <?php } ?>
    <?php if (!empty(FACEBOOK_API_ID)) { ?>
    window.fbAsyncInit = function() {
        FB.init({
            'appId': '<?=FACEBOOK_API_ID?>',
            'status': 'true',
            'xfbml': 'true',
            'version': 'v5.0'
        });
    };
    <?php } ?>
    $(document).ready(function () {
        let notifyErrorToast = null;
        let configurationNetworkingErrors = [];
        let fireConfigurationNetworkingError = function(errorMsg){
            if(errorMsg!==null && errorMsg.trim()!=='') {
                if (configurationNetworkingErrors.findIndex(existingMsg => existingMsg.trim() === errorMsg.trim())===-1) {
                    configurationNetworkingErrors.push(errorMsg.trim());
                }
                if (notifyErrorToast !== null) {
                    notifyErrorToast.remove();
                }
                notifyErrorToast = notify.error('<ul><li>'+configurationNetworkingErrors.join('</li><li>')+'</li></ul>', '', {fadeOut: 0});
            }
        };
        let removeConfigurationNetworkingError = function(errorMsg){
            if(errorMsg!==null && errorMsg.trim()!=='') {
                let removeIndex = configurationNetworkingErrors.findIndex(existingMsg => existingMsg.trim() === errorMsg.trim());
                if (removeIndex >= 0) {
                    configurationNetworkingErrors.splice(removeIndex,1);

                    if (notifyErrorToast !== null) {
                        notifyErrorToast.remove();
                    }
                    if(configurationNetworkingErrors.length>0) {
                        notifyErrorToast = notify.error('<ul><li>'+configurationNetworkingErrors.join('</li><li>')+'</li></ul>', '', {fadeOut: 0});
                    } else {
                        notifyErrorToast = null;
                    }
                }
            }
        };
        let initialFbAppId = null;
        <?php if (!empty(FACEBOOK_API_ID)) { ?>
        initialFbAppId = '<?=FACEBOOK_API_ID?>';
        <?php } ?>
        let fbAppIdField = $('#fb_appID');
        let neededFbPermissions = ['email'];
        let checkLoginStatusAndLogin = function(reRequest){
            if(reRequest===true){
                FB.login(function (loginResponse) {
                    loggedInAction(loginResponse);
                }, {auth_type: 'rerequest', scope: neededFbPermissions.join(',')});
            } else {
                FB.getLoginStatus(function (response) {
                    if (response.status === 'connected') {
                        loggedInAction(response);
                    } else if (response.status === 'not_authorized') {
                        // the user is logged in to Facebook,
                        // but has not authenticated your app
                        // re-request authorizations
                        FB.login(function (loginResponse) {
                            loggedInAction(loginResponse);
                        }, {auth_type: 'rerequest', scope: neededFbPermissions.join(',')});
                    } else {
                        FB.login(function (loginResponse) {
                            loggedInAction(loginResponse);
                        }, {scope: neededFbPermissions.join(',')});
                    }
                });
            }
        };

        $('#confirmFbUserIdModal').modal({'show':false});
        let loggedInFbUserId = null;
        let loggedInFbUserAccessToken = null;
        let loggedInAction = function (response) {
            let fbData = [];
            if (response.authResponse) {
                loggedInFbUserAccessToken = response.authResponse.accessToken;
                FB.api('/me/permissions', 'get', {
                    access_token: loggedInFbUserAccessToken
                }, function (permissionsResponse) {
                    let permissionsResponseData = permissionsResponse.data;
                    if (Array.isArray(permissionsResponseData)) {
                        let hasAllPermissionsNeeded = true;
                        permissionsResponseData.forEach(function (item, index) {
                            let permissionItem = item;
                            neededFbPermissions.forEach(function (perm, pindex){
                                if(hasAllPermissionsNeeded && permissionItem.permission===perm && permissionItem.status!=='granted'){
                                    hasAllPermissionsNeeded = false;
                                }
                            })
                        });
                        if(hasAllPermissionsNeeded){
                            FB.api('/me', 'get', {
                                access_token: loggedInFbUserAccessToken,
                                fields: 'id,name,email'
                            }, function (userResponse) {
                                loggedInFbUserId = userResponse.id;

                                fbData.push({
                                    id: userResponse.id,
                                    name: userResponse.name,
                                    email: userResponse.email
                                });

                                if (fbData.length > 0) {
                                    let confirmFbIdTemplate = $.templates("#fb-id-container-template");
                                    let confirmFbIdOptHtml = confirmFbIdTemplate.render(fbData);
                                    $("#fb-id-container").html(confirmFbIdOptHtml);
                                    $('#confirmFbUserIdModal').modal('show');
                                }
                            });
                        } else {
                            $('#confirmFbUserIdModal').modal('hide');
                            checkLoginStatusAndLogin(true);
                        }
                    } else {
                        $('#confirmFbUserIdModal').modal('hide');
                    }
                });
            } else {
                $('#confirmFbUserIdModal').modal('hide');
            }
        };

        $('#logOffFacebook').on('click', function () {
            FB.getLoginStatus(function (response) {
                if (response.status === 'connected' || response.status === 'not_authorized') {
                    $('#confirmFbUserIdModal').modal('hide');
                    FB.api('/' + loggedInFbUserId + '/permissions', 'delete', {
                        access_token: loggedInFbUserAccessToken,
                    }, function (deletePermissionsResponse) {
                        FB.login(function (reLoginResponse) {
                            loggedInAction(reLoginResponse);
                        }, { auth_type: 'rerequest',scope:neededFbPermissions.join(',') });
                    });
                }
            });
        });

        $('#obtain_Fb_userID').on('click', function () {
            let couldOpenModal = false;
            let initFBSdk = function(appId){
                FB.init({
                    'appId': appId,
                    'status': 'true',
                    'xfbml': 'true',
                    'version': 'v5.0'
                });
            };
            let actualFbAppId = fbAppIdField[0].value;
            if(initialFbAppId!==null) {
                if(actualFbAppId===initialFbAppId) {
                    couldOpenModal = true;
                } else if(actualFbAppId.trim()!=='') {
                    initFBSdk(actualFbAppId);
                    couldOpenModal = true;
                }
            } else {
                if(actualFbAppId.trim()!=='') {
                    FB.init({
                        'appId': actualFbAppId,
                        'status': 'true',
                        'xfbml': 'true',
                        'version': 'v5.0'
                    });
                    couldOpenModal = true;
                }
            }
            if(couldOpenModal) {
                checkLoginStatusAndLogin(false);
            } else {
                fireConfigurationNetworkingError('<?=system_showText(LANG_SITEMGR_SETTINGS_FACEBOOK_APP_ID_REQUIRED_TO_CONFIRMFBID_MODAL)?>');
            }
        });

        $("#submit-select-fb-id-form").on('click', function(event){
            $("#fb_userId").val($("#confirmed-fb-id")[0].value);
            $('#confirmFbUserIdModal').modal('hide');
        });

        let enableFacebookField = $("#fb_op");
        let allowSignInWithFacebookField = $("#foreignaccount_facebook");
        let fbAppIdBeforeFieldFocus = fbAppIdField[0].value;
        let appIdFieldWasChangedAction = function(actualFbAppIdValue){
            if(fbAppIdBeforeFieldFocus!==actualFbAppIdValue){
                $("#fb_userId").val('');
                if(actualFbAppIdValue.trim()===''){
                    enableFacebookField.attr("checked", false);
                    allowSignInWithFacebookField.attr("checked", false);
                } else {
                    removeConfigurationNetworkingError('<?=system_showText(LANG_SITEMGR_SETTINGS_FACEBOOK_APP_ID_REQUIRED_TO_CONFIRMFBID_MODAL)?>');
                    removeConfigurationNetworkingError("<?=system_showText(LANG_SITEMGR_SETTINGS_FACEBOOK_APP_ID_REQUIRED_TO_ENABLE_FB_COMMENTS_SYSTEM)?>");
                    removeConfigurationNetworkingError("<?=system_showText(LANG_SITEMGR_SETTINGS_FACEBOOK_APP_ID_REQUIRED_TO_ALLOW_FB_SIGNIN)?>");
                }
                fbAppIdBeforeFieldFocus = actualFbAppIdValue;
            }
        };

        fbAppIdField.on('focus', function(event){
            fbAppIdBeforeFieldFocus = $(this)[0].value;
        });

        fbAppIdField.on('blur', function(event){
            appIdFieldWasChangedAction($(this)[0].value);
        });

        fbAppIdField.on('change', function(event){
            appIdFieldWasChangedAction($(this)[0].value);
        });

        enableFacebookField.on('change', function(event){
            if($(this).is(":checked") && fbAppIdBeforeFieldFocus.trim()===''){
                fireConfigurationNetworkingError("<?=system_showText(LANG_SITEMGR_SETTINGS_FACEBOOK_APP_ID_REQUIRED_TO_ENABLE_FB_COMMENTS_SYSTEM)?>");
                enableFacebookField.attr("checked", false);
            }
        });

        allowSignInWithFacebookField.on('change', function(event){
            if($(this).is(":checked") && fbAppIdBeforeFieldFocus.trim()===''){
                fireConfigurationNetworkingError("<?=system_showText(LANG_SITEMGR_SETTINGS_FACEBOOK_APP_ID_REQUIRED_TO_ALLOW_FB_SIGNIN)?>");
                allowSignInWithFacebookField.attr("checked", false);
            }
        });

    });
</script>
