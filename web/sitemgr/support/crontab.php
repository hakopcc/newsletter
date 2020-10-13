<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
    # * FILE: /ed-admin/support/crontab.php
    # ----------------------------------------------------------------------------------------------------

    # ----------------------------------------------------------------------------------------------------
    # THIS PAGE IS ONLY USED BY THE SUPPORT
    # ----------------------------------------------------------------------------------------------------

    # ----------------------------------------------------------------------------------------------------
    # LOAD CONFIG
    # ----------------------------------------------------------------------------------------------------
    include("../../conf/loadconfig.inc.php");

    # ----------------------------------------------------------------------------------------------------
    # SESSION
    # ----------------------------------------------------------------------------------------------------
    sess_validateSMSession();

    if (!permission_hasSMPermSection(SITEMGR_PERMISSION_SUPERADMIN)) {
        header("Location: ".DEFAULT_URL."/".SITEMGR_ALIAS."/");
        exit;
    }

    # ----------------------------------------------------------------------------------------------------
    # FORMS DEFINES
    # ----------------------------------------------------------------------------------------------------

    $crons_manager = [];
    $crons_manager[] = "rollback_import.php";
    $crons_manager[] = "rollback_import_events.php";
    $crons_manager[] = "export_listings.php";
    $crons_manager[] = "export_events.php";
    $crons_manager[] = "export_mailapp.php";
    $crons_manager[] = "daily_maintenance.php";
    $crons_manager[] = "email_traffic.php";
    $crons_manager[] = "renewal_reminder.php";
    $crons_manager[] = "report_rollup.php";
    $crons_manager[] = "sitemap.php";
    $crons_manager[] = "statisticreport.php";

    $cronTabText = "0,20,40 * * * * php -f ".EDIRECTORY_ROOT."/cron/email_traffic.php 1>&2 >> ".EDIRECTORY_ROOT."/cron/cron.log
    0,20,40 * * * * php -f ".EDIRECTORY_ROOT."/cron/renewal_reminder.php 1>&2 >> ".EDIRECTORY_ROOT."/cron/cron.log
    0 */3 * * * php -f ".EDIRECTORY_ROOT."/cron/daily_maintenance.php 1>&2 >> ".EDIRECTORY_ROOT."/cron/cron.log
    5 0 * * * php -f ".EDIRECTORY_ROOT."/cron/report_rollup.php 1>&2 >> ".EDIRECTORY_ROOT."/cron/cron.log
    5 0 * * * php -f ".EDIRECTORY_ROOT."/cron/statisticreport.php 1>&2 >> ".EDIRECTORY_ROOT."/cron/cron.log
    0 20 * * * php -f ".EDIRECTORY_ROOT."/cron/sitemap.php 1>&2 >> ".EDIRECTORY_ROOT."/cron/cron.log
    */5 * * * * php -f ".EDIRECTORY_ROOT."/cron/export_listings.php 1>&2 >> ".EDIRECTORY_ROOT."/cron/cron.log
    */5 * * * * php -f ".EDIRECTORY_ROOT."/cron/export_events.php 1>&2 >> ".EDIRECTORY_ROOT."/cron/cron.log
    */5 * * * * php -f ".EDIRECTORY_ROOT."/cron/export_mailapp.php 1>&2 >> ".EDIRECTORY_ROOT."/cron/cron.log
    */10 * * * * php -f ".EDIRECTORY_ROOT."/cron/rollback_import.php 1>&2 >> ".EDIRECTORY_ROOT."/cron/cron.log
    */10 * * * * php -f ".EDIRECTORY_ROOT."/cron/rollback_import_events.php 1>&2 >> ".EDIRECTORY_ROOT."/cron/cron.log
    */5 * * * * php ".SymfonyCore::getKernel()->getRootDir()."/console edirectory:import --all-domains 1>&2 >> ".SymfonyCore::getKernel()->getLogDir()."/import.log
    */10 * * * * php ".SymfonyCore::getKernel()->getRootDir()."/console edirectory:sync -i --all-domains 1>&2 >> ".SymfonyCore::getKernel()->getLogDir()."/import-sync.log";

    $cronTabText2 = "*/5 * * * * php -f ".EDIRECTORY_ROOT."/cron/cron_manager.php 1>&2 >> ".EDIRECTORY_ROOT."/cron/cron.log
    */5 * * * * php ".SymfonyCore::getKernel()->getRootDir()."/console edirectory:import --all-domains 1>&2 >> ".SymfonyCore::getKernel()->getLogDir()."/import.log
    */10 * * * * php ".SymfonyCore::getKernel()->getRootDir()."/console edirectory:sync -i --all-domains 1>&2 >> ".SymfonyCore::getKernel()->getLogDir()."/import-sync.log";

    # ----------------------------------------------------------------------------------------------------
    # AUXILIARY FUNCTIONS
    # ----------------------------------------------------------------------------------------------------

    function resetFlags()
    {
        $dbMain = db_getDBObject(DEFAULT_DB, true);
        $result = true;

        $sql = [
            "UPDATE Control_Cron SET running = 'N', last_run_date = '0000-00-00 00:00:00'",
            "UPDATE Control_Export_Event   SET scheduled = 'N', running_cron = 'N', finished = 'Y'",
            "UPDATE Control_Export_Listing SET scheduled = 'N', running_cron = 'N', finished = 'Y'",
            "UPDATE Control_Export_MailApp SET scheduled = 'N', running = 'N'",
            "UPDATE Setting SET value = 'N' WHERE name = 'running_cron_manager'",
        ];

        foreach ($sql as $query) {
            $result = ($dbMain->query($query) && $result);
        }

        return $result ? "1" : "0";
    }

    # ----------------------------------------------------------------------------------------------------
    # ACTION
    # ----------------------------------------------------------------------------------------------------

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        switch ($_POST['action']) {
            case "resetflags":
                echo resetFlags();
                exit;
        }
    }

    # ----------------------------------------------------------------------------------------------------
    # HEADER
    # ----------------------------------------------------------------------------------------------------
    include(SM_EDIRECTORY_ROOT."/layout/header.php");
?>
    <main class="main-dashboard">
        <nav class="main-sidebar">
            <?php include(SM_EDIRECTORY_ROOT."/layout/sidebar-dashboard.php"); ?>
            <div class="sidebar-submenu">
                <?php include(SM_EDIRECTORY_ROOT . "/layout/sidebar.php"); ?>
            </div>
        </nav>
        <div class="main-wrapper">
            <?php include(SM_EDIRECTORY_ROOT."/layout/navbar.php"); ?>
            <div class="main-content" content-full="true">
                <?php
                    require SM_EDIRECTORY_ROOT.'/registration.php';
                    require EDIRECTORY_ROOT.'/includes/code/checkregistration.php';
                ?>

                <section class="section-heading">
                    <div class="section-heading-content">
                        <h1 class="section-heading-title">Crontab <span class="icon-help8" data-toggle="tooltip" data-placement="bottom" title="Please, be aware that cron_manager.php runs others crons indicated below. If you schedule cron_manager, DO NOT SCHEDULE any other cron that cron_manager runs. If you need to schedule any cron separately and still keep cron_manager scheduled, go to the file cron/cron_manager.php and comment the line that corresponds to the cron you want to run separately."></span></h1>
                    </div>
                </section>

                <div class="support-wrapper" style="padding: 24px">
                    <div class="panel panel-default">
                        <div class="panel-heading">Cron History</div>
                        <div class="panel-body"><?php include(INCLUDES_DIR."/code/cronjobreport.php"); ?></div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="panel panel-default">
                                <div class="panel-heading">Crontab <strong>without cron manager</strong></div>
                                <div class="panel-body"><textarea class="form-control" name="text" id="textarea" rows="8"><?= htmlspecialchars($cronTabText) ?></textarea></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="panel panel-default">
                                <div class="panel-heading">Crontab <strong>with cron manager</strong></div>
                                <div class="panel-body"><textarea class="form-control" name="text" id="textarea" rows="8"><?= htmlspecialchars($cronTabText2) ?></textarea></div>
                            </div>
                        </div>
                    </div>

                    <div class="panel panel-default">
                        <div class="panel-heading">Cron Manager includes</div>
                        <div class="panel-body">
                            <ul style="margin: 0;padding: 0;margin-left: 18px;">
                                <?php foreach ($crons_manager as $cron) { ?>
                                    <li><?= $cron ?></li>
                                <?php } ?>
                            </ul>
                        </div>
                    </div>

                    <div class="panel panel-default">
                        <div class="panel-heading">Reset Cron Flags</div>
                        <div class="panel-body">
                            <p>This will reset the current cron status flags to its default values. Use this when you notice a flag is out of sync with it's corresponding cron</p>
                            <div id="reset_message_box"></div>
                            <button class="btn btn-danger btn-large" type="button" id="reset_flags_button">Reset All Flags</button>
                            <?php /*<button class="btn btn-primary btn-large" type="button" id="launch_manager_button">Launch Cron Manager</button>*/ ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
<?php
    # ----------------------------------------------------------------------------------------------------
    # FOOTER
    # ----------------------------------------------------------------------------------------------------
    $customJS = SM_EDIRECTORY_ROOT."/assets/custom-js/support.php";
    include(SM_EDIRECTORY_ROOT."/layout/footer.php");
?>
