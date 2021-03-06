<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/assets/custom-js/export.php
	# ----------------------------------------------------------------------------------------------------

?>
	<script>

        <?php if (LISTING_SCALABILITY_OPTIMIZATION === 'on' && $aux_export_running['finished'] === 'N') { ?>
            $(".exporting").addClass("hidden");
            $("#exporting-form-listing").removeClass("hidden");
            setTimeout("checkExportProgressListing()", 500);
        <?php } ?>

        <?php if (EVENT_SCALABILITY_OPTIMIZATION === 'on' && $aux_export_runningEvent['finished'] === 'N') { ?>
            $(".exporting").addClass("hidden");
            $("#exporting-form-event").removeClass("hidden");
            setTimeout("checkExportProgressEvent()", 500);
        <?php } ?>

        var check_progress_time = 5*1000;
        var lastprogress = 0;

        function showForm(module) {

            if (module) {
                if (module === "listing") {
                    startExport();
                } else if (module === "event") {
                    startExportEvent();
                }

                MixpanelHelper.track('Exported content on eDirectory format', {'Module': module});

                $(".exporting").addClass("hidden");
                $("#exporting-form-" + module).removeClass("hidden");
            }
        }

        function showLoading () {
            showMessages('clear');
            if ($("#export_loading").hasClass("hidden")) {
                $("#export_loading").removeClass("hidden");
            } else {
                $("#export_loading").addClass("hidden");
            }
        }

        function showMessages (type, message) {
            if (type === 'success') {
                notify.success(message);
            } else if (type === 'error') {
                if (!message) message = "<?=LANG_SITEMGR_EXPORT_ERROR;?>";
                notify.error(message, '', { fadeOut: 0 });
            }

            if (type === 'clear') {
                notify.clear();
            }
        }

        function exportFile () {

            type = $('#select_export_backup option:selected').val();

            showLoading();

            MixpanelHelper.track('Exported a backup file', {'Type': type});

            var ajaxURL = '<?=system_getFormAction($_SERVER['PHP_SELF']);?>';

            var data = {
                ajax_action: 'generate_data',
                domain_id: '<?=SELECTED_DOMAIN_ID;?>',
                file_extension: 'csv',
                filter_categoryId: $("#category_id").val() || 0,
                item_filter: $('input[name="item_filter"]:checked').val() || 0,
                item_type: type
            };

            var location = $("#location").val();

            if(location) {
                var parts = location.split(':');
                data.filter_locationId = parts[1];
                data.filter_locationLevel = parts[0];
            }

            if (type !== "Email") $("#emailDataFields").hide();

            $.post(ajaxURL, data, function (res) {
                /**
                * options[0] = message type (Success / Error)
                * options[1] = message (Status message from process)
                * options[2] = zip filename ([TYPE].zip)
                */
                var options = res.split(' - ');
                showLoading();

                if (options[0] === 'success' && options[2] !== '') {
                    showMessages(options[0], options[1]);
                    window.location = ajaxURL + '?download=' + options[2];
                } else {
                    if (options[2] === '') showMessages('error', "<?=system_showText(LANG_SITEMGR_EXPORT_NO_DATAFOUND);?>");
                    else showMessages('error');
                }
            });
        }

        function scheduleExport () {
            showMessages('clear');
            var ajaxURL = '<?=$_SERVER['PHP_SELF'];?>';
            var filename = $('#nextFileName').val();
            var domain = '<?=SELECTED_DOMAIN_ID;?>';

            $("#export_cron_loading").show();
            $("#export_progress").show();
            $("#export_link_start").hide();
            $("#file_link").hide();
            $("#export_progress").html('<span>0%</span>');

            $.post(ajaxURL, {
                ajax_action: 'schedule_export',
                file_name: filename,
                domain_id: domain
            }, function (res) {
                if (res != 0 && res == 1) {
                    showMessages('error', "<?=system_showText(LANG_SITEMGR_EXPORT_ALREADY_SCHEDULED);?>");
                    $("#export_progress").html('');
                } else if (res != 0) {
                    showMessages('error', "<?=system_showText(LANG_SITEMGR_EXPORT_ERROR_SCHEDULE);?>");
                    $("#export_progress").html('');
                }

                if (res != 0) {
                    $("#export_link_start").show();
                    $("#export_cron_loading").hide();
                } else {
                    setTimeout("checkExportProgress()", check_progress_time);
                }
            });
        }

        function checkExportProgress () {
            var ajaxURL = '<?=$_SERVER['PHP_SELF'];?>';
            var domain = '<?=SELECTED_DOMAIN_ID;?>';
            var filename = $('#nextFileName').val();
            var nextFileName = '<?=md5(uniqid(rand(), true)).'.zip';?>';

            $.post(ajaxURL, {
                ajax_action: 'check_progress',
                file_name: filename,
                domain_id: domain
            }, function (res) {
                var options = res.split(" - ");
                if (options[0] === "waiting") {
                    $("#export_cron_loading").show();
                    $("#export_progress_backup").show();
                    $("#export_link_start").hide();
                    $("#export_progress_backup").html('<span>0%</span>');
                    setTimeout("checkExportProgress()", check_progress_time);
                } else if (options[0] === "progress") {
                    if (options[1] >= 0 && options[1] < 100) {
                        $("#export_progress_backup").html('<span>' + options[1] + '%</span>');
                        setTimeout("checkExportProgress()", check_progress_time);
                    } else if (options[1] == 100) {
                        showMessages('success', "<?=system_showText(LANG_SITEMGR_EXPORT_SUCCESSFULLY);?>");
                        $("#export_progress_backup").html('');
                        $("#export_link_start").show();
                        $("#export_cron_loading").hide();
                        $('#nextFileName').val(nextFileName);
                        $('#showFileName').text(nextFileName);
                        $("#file_link").html('<a href="' + ajaxURL + '?action=cron&download=' + filename + '"><?=system_showText(LANG_SITEMGR_EXPORT_LASTFILE_MESSAGE)?></a>');
                        $("#file_link").show();
                    }
                } else if (options[0] === "error") {
                    showMessages('error', "<?=system_showText(LANG_SITEMGR_EXPORT_ERROR_SCHEDULE);?>");
                    $("#export_progress_backup").html('');
                    $("#export_link_start").show();
                    $("#export_cron_loading").hide();
                }
            });
        }

        function startExportProcess() {
            $.get("./itemexportfile.php", {
                export_type: 'listing',
                file: '<?=$exportFileListing?>',
                domain_id: <?=SELECTED_DOMAIN_ID?>
            }, function (res) {});
		}
		function removeExportControl() {
            $.get("./itemexportfile.php", {
                export_type: 'listing',
                file: '<?=$exportFileListing?>',
                removecontrol: 'true',
                domain_id: <?=SELECTED_DOMAIN_ID?>
            }, function (res) {});

		}
		function checkExportProgressListing() {

            $.get("./itemexportcheck.php", {
                export_type: 'listing',
                file: '<?=$exportFileListing?>',
                lastprogress: lastprogress,
                domain_id: <?=SELECTED_DOMAIN_ID?>
            }, function (res) {
                string_status = res;
                current_progress = parseInt(string_status);

                <?php if (LISTING_SCALABILITY_OPTIMIZATION !== 'on') { ?>
                lastprogress = current_progress;
                <?php } ?>

                if (isNaN(current_progress)) {
                    <?php if (LISTING_SCALABILITY_OPTIMIZATION === 'on') { ?>
                    aux_status = string_status.split("||");
                    if (aux_status[1] === "error") {
                        document.getElementById("export_message").style.color = "#FF0000";
                        document.getElementById("export_message").innerHTML = aux_status[0];
                        document.getElementById("export_progress").innerHTML = "&nbsp;";
                        document.getElementById("export_progress_percentage").innerHTML = "&nbsp;";
                        removeExportControl();
                    } else {
                        document.getElementById("export_message").innerHTML = string_status+"<br /><img src='<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/img/preloader-32.gif' />";
                        document.getElementById("export_progress").innerHTML = "0";
                        document.getElementById("export_progress_percentage").innerHTML = "%";
                        setTimeout("checkExportProgressListing()", check_progress_time);
                    }
                    <?php } else { ?>
                    document.getElementById("export_message").style.color = "#FF0000";
                    document.getElementById("export_message").innerHTML = string_status;
                    document.getElementById("export_progress").innerHTML = "&nbsp;";
                    document.getElementById("export_progress_percentage").innerHTML = "&nbsp;";
                    removeExportControl();
                    <?php } ?>
                } else {
                    <?php if ($aux_export_running['finished'] === 'N' && LISTING_SCALABILITY_OPTIMIZATION === 'on') { ?>
                    document.getElementById("export_message").innerHTML = "<?=system_showText(LANG_SITEMGR_EXPORT_WAITING_CRON)?><br /><img src='<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/img/preloader-32.gif' />";
                    document.getElementById("export_progress").innerHTML = current_progress;
                    document.getElementById("export_progress_percentage").innerHTML = "%";
                    <?php } else { ?>
                    document.getElementById("export_progress").innerHTML = current_progress;
                    <?php } ?>

                    if (parseInt(document.getElementById("export_progress").innerHTML) >= 100) {
                        document.getElementById("export_message").style.fontSize = "15px";
                        document.getElementById("export_message").style.color = "#466E1E";
                        document.getElementById("export_message").style.fontWeight = "bold";
                        document.getElementById("export_message").innerHTML = "<?=system_showText(LANG_SITEMGR_EXPORT_EXPORTDONE);?>";
                        document.getElementById("export_progress").innerHTML = "&nbsp;";
                        document.getElementById("export_progress_percentage").innerHTML = "&nbsp;";
                        $("#download_file").removeClass('hidden');
                        removeExportControl();
                    } else {
                        setTimeout("checkExportProgressListing()", check_progress_time);
                    }
                }
            });

		}
		function startExport() {
			document.getElementById("export_message").innerHTML = "<?=system_showText(LANG_SITEMGR_EXPORT_EXPORTINGPLEASEWAIT)?><br /><img src='<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/img/preloader-32.gif' />";
			document.getElementById("export_progress").innerHTML = "0";
			document.getElementById("export_progress_percentage").innerHTML = "%";
			startExportProcess();
			setTimeout("checkExportProgressListing()", check_progress_time);
		}

        function startExportProcessEvent() {
            $.get("./itemexportfile.php", {
                export_type: 'event',
                file: '<?=$exportFileEvent?>',
                domain_id: <?=SELECTED_DOMAIN_ID?>
            }, function (res) {});
		}

		function removeExportControlEvent() {
            $.get("./itemexportfile.php", {
                export_type: 'event',
                file: '<?=$exportFileEvent?>',
                removecontrol: 'true',
                domain_id: <?=SELECTED_DOMAIN_ID?>
            }, function (res) {});
		}

		function checkExportProgressEvent() {

            $.get("./itemexportcheck.php", {
                export_type: 'event',
                file: '<?=$exportFileEvent?>',
                lastprogress: lastprogress,
                domain_id: <?=SELECTED_DOMAIN_ID?>
            }, function (res) {

                string_status = res;
                current_progress = parseInt(string_status);
                <?php if (EVENT_SCALABILITY_OPTIMIZATION !== 'on') { ?>
                lastprogress = current_progress;
                <?php } ?>

                if (isNaN(current_progress)) {
                    <?php if (EVENT_SCALABILITY_OPTIMIZATION === 'on') { ?>
                    aux_status = string_status.split("||");
                    if (aux_status[1] === "error") {
                        document.getElementById("export_messageEvent").style.color = "#FF0000";
                        document.getElementById("export_messageEvent").innerHTML = aux_status[0];
                        document.getElementById("export_progressEvent").innerHTML = "&nbsp;";
                        document.getElementById("export_progress_percentageEvent").innerHTML = "&nbsp;";
                        removeExportControlEvent();
                    } else {
                        document.getElementById("export_messageEvent").innerHTML = string_status+"<br /><img src='<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/img/preloader-32.gif' />";
                        document.getElementById("export_progressEvent").innerHTML = "0";
                        document.getElementById("export_progress_percentageEvent").innerHTML = "%";
                        setTimeout("checkExportProgressEvent()", check_progress_time);
                    }
                    <?php } else { ?>
                    document.getElementById("export_messageEvent").style.color = "#FF0000";
                    document.getElementById("export_messageEvent").innerHTML = string_status;
                    document.getElementById("export_progressEvent").innerHTML = "&nbsp;";
                    document.getElementById("export_progress_percentageEvent").innerHTML = "&nbsp;";
                    removeExportControlEvent();
                    <?php } ?>
                } else {
                    <?php if (EVENT_SCALABILITY_OPTIMIZATION === 'on' && $aux_export_runningEvent['finished'] === 'N') { ?>
                    document.getElementById("export_messageEvent").innerHTML = "<?=system_showText(LANG_SITEMGR_EXPORT_WAITING_CRON)?><br /><img src='<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/img/preloader-32.gif' />";
                    document.getElementById("export_progressEvent").innerHTML = current_progress;
                    document.getElementById("export_progress_percentageEvent").innerHTML = "%";
                    <?php } else { ?>
                    document.getElementById("export_progressEvent").innerHTML = current_progress;
                    <?php } ?>

                    if (parseInt(document.getElementById("export_progressEvent").innerHTML) >= 100) {
                        document.getElementById("export_messageEvent").style.fontSize = "15px";
                        document.getElementById("export_messageEvent").style.color = "#466E1E";
                        document.getElementById("export_messageEvent").style.fontWeight = "bold";
                        document.getElementById("export_messageEvent").innerHTML = "<?=system_showText(LANG_SITEMGR_EXPORT_EXPORTDONE);?>";
                        document.getElementById("export_progressEvent").innerHTML = "&nbsp;";
                        document.getElementById("export_progress_percentageEvent").innerHTML = "&nbsp;";
                        $("#download_fileEvent").removeClass('hidden');
                        removeExportControlEvent();
                    } else {
                        setTimeout("checkExportProgressEvent()", check_progress_time);
                    }
                }
            });
		}

		function startExportEvent() {
			document.getElementById("export_messageEvent").innerHTML = "<?=system_showText(LANG_SITEMGR_EXPORT_EXPORTINGPLEASEWAIT)?><br /><img src='<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/img/preloader-32.gif' />";
			document.getElementById("export_progressEvent").innerHTML = "0";
			document.getElementById("export_progress_percentageEvent").innerHTML = "%";
			startExportProcessEvent();
			setTimeout("checkExportProgressEvent()", check_progress_time);
		}

        function deleteFile(file) {
            document.getElementById("deleteFile").value = file;
            bootbox.confirm('<?=system_showText(LANG_SITEMGR_EXPORT_DELETEQUESTION);?>', function(result) {
                if (result) {
                    $('#export_delete').submit();
                }
            });
        }

        $(document).ready(function () {
            <?php if (LISTING_SCALABILITY_OPTIMIZATION === 'on' && $export['finished'] === 'N') { ?>
                checkExportProgress();
            <?php } ?>
            $('#select_export_backup').on('change', function() {
                var scheduledExport = $('#exportlisting');
                if(this.value === 'Listing') {
                    <?php if(LISTING_SCALABILITY_OPTIMIZATION === 'on') { ?>
                        scheduledExport.css('display', '');
                    <?php } ?>
                } else {
                    scheduledExport.css('display', 'none');
                }
            });
        });

        <?php if ($message) { ?>
            notify.success('<?=$message;?>');
        <?php } ?>

        <?php if($messageStyle){ ?>
            <?php if($messageStyle == 'success'){ ?>
                notify.success('<?=$message;?>');
            <?php } else { ?>
                notify.error('<?=$message;?>', '', { fadeOut: 0 });
            <?php } ?>
        <?php } ?>
    </script>
