<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2020 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/assets/custom-js/reports.php
	# ----------------------------------------------------------------------------------------------------
?>
	<script>
        var moduleActive = <?=($_GET['module'] ? "'".$_GET['module']."'" : "'module_h'")?>;

        function displayReport(module) {

            if (!module) return false;

            <?php foreach($modules as $module ) { ?>
                document.getElementById("module_<?=$module;?>").style.display = "none";
                document.getElementById("style_module_<?=$module;?>").className = "";
            <?php } ?>

            document.getElementById(module).style.display = "block";
            document.getElementById('style_' + module).className = "active";

            moduleActive = module;
        }

        function doRefreshStatus() {
            MixpanelHelper.track('Clicked on Refresh Statistics Now');
            dataFormat = '<?=format_date(date("Y-m-d"), DEFAULT_DATE_FORMAT, "datetime")." - ".format_getTimeString(date("Y-m-d H:i:s"))?>';
            url = "<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/activity/reports/statisticreport_refresh.php";
            $.get(url, {'refresh':1}, function (data) {
                secondsFormat = data.substr(data.lastIndexOf(" - "));
                let message = "<?=system_showText(LANG_SITEMGR_REPORT_STATSHASBEENUPDATED)?> "+dataFormat+secondsFormat+" <?=system_showText(LANG_SITEMGR_REPORT_SECONDS)?>";

                notify.info(message, '', { fadeOut: 4000, onHidden: () => {
                    url = "<?=$url?>"+moduleActive;
                    window.location.href = url;
                }});
            });
        }

        <?php if ($openActiveModule) { ?>
            displayReport(moduleActive);
        <?php } ?>

    </script>
