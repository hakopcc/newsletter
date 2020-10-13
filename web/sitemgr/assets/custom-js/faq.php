<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2020 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/assets/custom-js/faq.php
	# ----------------------------------------------------------------------------------------------------
?>
	<script>
        let thisId = '';

        function hideForm() {
            $('#FAQ_add').fadeOut(500);
        }

        function hideFormFaq(form_id) {
            $('#'+form_id).fadeOut(500);
        }

        function faq_edit(faq_id) {
            thisId = faq_id;
            $('.hideForm').css('display', 'none');
            $('#FAQ_edit'+faq_id).css('display', '');
        }

        function faq_delete(faq_id) {
            bootbox.confirm('<?=system_showText(LANG_SITEMGR_MSGAREYOUSURE);?>', function(result) {
                if (result) {
                    $("#faq_id").attr('value', faq_id);
                    document.getElementById('FAQ_post').submit();
                }
            });
        }

        function faq_add() {
            $('.hideForm').css('display', 'none');
            $('#FAQ_add').css('display', '');
        }

        $('document').ready(function() {
            $('button[name=FAQ_post_submit]').bind('click', function() {
                if (($('#faq_question').val() == '') || ($('#faq_answer').val() == '')) {
                    let errorsCreate = [];

                    if (!$('#faq_question').val()) {
                        errorsCreate.push('&#149;&nbsp;' + '<?=system_showText(LANG_SITEMGR_SETTINGS_MSGERROR_QUESTION)?>');
                    }

                    if (!$('#faq_answer').val()) {
                        errorsCreate.push('&#149;&nbsp;' + '<?=system_showText(LANG_SITEMGR_SETTINGS_MSGERROR_ANSWER)?>');
                    }

                    $('button[name=FAQ_post_submit]').button('reset');

                    notify.error(errorsCreate.join('<br/>'), '<?=system_showText(LANG_MSG_FIELDS_CONTAIN_ERRORS);?>', { fadeOut: 0 });
                    return false;
                }
            });

            $('button[name=FAQ_edit_submit]').bind('click', function() {
                if(($('#faq_question_edit'+thisId).val() == '') || ($('#faq_answer_edit'+thisId).val() == '')){
                    let errorsEdit = [];

                    if ($('#faq_question_edit'+thisId).val() == '') {
                        errorsEdit.push('&#149;&nbsp;' + '<?=system_showText(LANG_SITEMGR_SETTINGS_MSGERROR_QUESTION)?>');
                    }

                    if ($('#faq_answer_edit'+thisId).val() == '') {
                        errorsEdit.push('&#149;&nbsp;' + '<?=system_showText(LANG_SITEMGR_SETTINGS_MSGERROR_ANSWER)?>');
                    }

                    $('button[name=FAQ_edit_submit]').button('reset');

                    notify.error(errorsEdit.join('<br/>'), '<?=system_showText(LANG_MSG_FIELDS_CONTAIN_ERRORS);?>', { fadeOut: 0 });
                    return false;
                }
            });
        });

        <?php if (is_numeric($message) && isset($msg_faq[$message])) { ?>
            notify.success('<?=$msg_faq[$message];?>');
        <?php } ?>
    </script>
