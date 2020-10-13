<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2020 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/assets/custom-js/emailconfig.php
	# ----------------------------------------------------------------------------------------------------
?>
<script>
    let emailConfig = function(){
        let _this = this;
        
        this.radioValues = {
            'plain': 1,
            'login': 2,
            'noauth': 3
        };
        this.errorMessages = [];
        this.successMessages = [];

        this.clearMessages = (type) => {
            if(type == 'error') _this.errorMessages.length = 0;
            if(type == 'success') _this.successMessages.length = 0;
        };

        this.validateForm = async () => {
            if ($("#host").val() == "") {
                _this.errorMessages.push('- <?=system_showText(LANG_SITEMGR_SETTINGS_EMAILCONF_MSGERROR_SERVERFIELD)?>');
            }
            
            if ($("#port").val() == "") {
                _this.errorMessages.push('- <?=system_showText(LANG_SITEMGR_SETTINGS_EMAILCONF_MSGERROR_PORTFIELD)?>');
            }

            if ($("#email").val() == "") {
                _this.errorMessages.push('- <?=system_showText(LANG_SITEMGR_SETTINGS_EMAILCONF_MSGERROR_EMAILFIELD)?>');
            }

            if (!$('#auth3').attr("checked")) {
                if ($("#username").val() == "") {
                    _this.errorMessages.push('- <?=system_showText(LANG_SITEMGR_SETTINGS_EMAILCONF_MSGERROR_USERNAMEFIELD)?>');
                }

                if ($("#password").val() == "") {
                    _this.errorMessages.push('- <?=system_showText(LANG_SITEMGR_SETTINGS_EMAILCONF_MSGERROR_PASSWORDFIELD)?>');
                }
            }

            if(_this.errorMessages.length > 0) return false;

            return await _this.verifyConnection();
        }

        this.verifyConnection = async () => {
            try {
                const res = await $.ajax({url: '<?=DEFAULT_URL."/".SITEMGR_ALIAS."/configuration/email/index.php"?>', data: $('#adminemail').serialize(), dataType: 'json'}).promise();
                
                if (res.status !== 'success') {
                    _this.errorMessages.push(res.msg_error);
                    return false;
                }

                return true;
            } catch(e) {
                _this.errorMessages.push(e.message);
                return false;
            }
        }

        this.isValidEmail = (email) => {
            var emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
            return emailPattern.test(email);
        }

        this.verifyEmail = (email) => {
            if(email != ''){
                if(_this.isValidEmail(email)){
                    $('#email_status').html('<i class="fa fa-check-circle-o"></i>');
                } else {
                    $('#email_status').html('<i class="fa fa-times-circle-o"></i>');
                }
            }
        }

        this.switchPorts = (protocol) => {
            if (protocol == "ssl") {
                $("#port").attr("value", "465");
            } else {
                $("#port").attr("value", "587");
            }
        }

        this.switchAuth = (type) => {
            $("#auth"+_this.radioValues[type]).attr('checked', true);

            if (type == 'login' || type == 'plain') {
                if (type == "login") {
                    $('#protocol')[0].selectize.enable();
                } else {
                    $('#protocol')[0].selectize.disable();
                }

                $('#username').prop('disabled', '');
                $('#password').prop('disabled', '');
            } else {
                $('#protocol')[0].selectize.disable();
                $('#username').prop('disabled', 'disabled');
                $('#password').prop('disabled', 'disabled');
            }

            if (type == 'login') {
                _this.switchPorts($('#protocol').val());
            } else {
                $('#port').attr('value', '25');
            }
        }

        this.submitForm = (e) => {
            const _event = e;
            
            e.preventDefault();
            notify.clear();

            $("#bt_submit").button('loading');

            _this.validateForm()
                .then(success => {
                    $("#bt_submit").button('reset');

                    if (success) {
                        $("#ajaxVerify").attr('value', 0);
                        $('#adminemail').removeAttr('onsubmit');
                        $('#adminemail').submit();
                    } else {
                        if(_this.errorMessages.length > 0){
                            notify.error(_this.errorMessages.join('<br/>'), '', { fadeOut: 0 });
                            _this.clearMessages('error');
                        }
                    }
                });
        }
    }

    let emailConfigFunction = new emailConfig();

    <?php if ($message_adminemail) { ?>
        <?php if($message_style == 'warning'){ ?>
            notify.error('<?=$message_adminemail;?>', '', { fadeOut: 0 });
        <?php } else if($message_style == 'success'){ ?>
            notify.success('<?=$message_adminemail;?>');
        <?php } ?>
    <?php } ?>

    <?php if ($message_confemail) { ?>
        <?php if($message_style == 'warning'){ ?>
            notify.error('<?=$message_confemail;?>', '', { fadeOut: 0 });
        <?php } else if($message_style == 'success'){ ?>
            notify.success('<?=$message_confemail;?>');
        <?php } ?>
    <?php } ?>
</script>
