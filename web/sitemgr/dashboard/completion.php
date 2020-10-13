<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
    # * FILE: /ed-admin/dashboard/completion.php
    # ----------------------------------------------------------------------------------------------------

    if ($FirstStartDashboard) {
?>
    <section id="welcome">
        <div class="jumbotron">
            <h1><?= system_showText(LANG_SITEMGR_DASH_WELCOME) ?></h1>
            <p><?= system_showText(LANG_SITEMGR_DASH_WELCOME_TIP) ?></p>
            <button type="button" id="start-tour" class="btn btn-primary btn-lg hidden-xs">
                <small class="icon-small31"></small> <?= system_showText(LANG_SITEMGR_DASH_START_TOUR); ?>
            </button>
            <hr>

            <div class="row">
                <div class="col-md-4 col-sm-4 col-xs-12">
                    <h2><?= system_showText(LANG_SITEMGR_DASH_START1) ?></h2>
                    <p><?= system_showText(LANG_SITEMGR_DASH_START1_TIP) ?></p>
                    <a href="<?= DEFAULT_URL."/".SITEMGR_ALIAS."/design/page-editor/" ?>" class="btn btn-info" data-mixpanel-event='Clicked on "Customize my Directory"'>
                        <?= system_showText(LANG_SITEMGR_DASH_START1_TIP2) ?>
                    </a>
                </div>
                <div class="col-md-4 col-sm-4 col-xs-12">
                    <h2><?= system_showText(LANG_SITEMGR_DASH_START2) ?></h2>
                    <p><?= system_showText(LANG_SITEMGR_DASH_START2_TIP) ?></p>
                    <a href="<?= DEFAULT_URL."/".SITEMGR_ALIAS."/promote/helpme/" ?>" class="btn btn-info" data-mixpanel-event='Clicked on "How to Promote"'>
                        <?= system_showText(LANG_SITEMGR_DASH_START2_TIP2) ?>
                    </a>
                </div>
                <div class="col-md-4 col-sm-4 col-xs-12">
                    <h2><?= system_showText(LANG_SITEMGR_DASH_START3) ?></h2>
                    <p><?= system_showText(LANG_SITEMGR_DASH_START3_TIP) ?></p>
                    <a href="http://support.edirectory.com/" target="_blank" class="btn btn-info" data-mixpanel-event='Clicked on "Frequently Asked Questions"'>
                        <?= system_showText(LANG_SITEMGR_FREQUENTLYASKEDQUESTIONS); ?>
                    </a>
                </div>
            </div>

            <?php if (is_array($arrayCompletion) && count($arrayCompletion)) { ?>
                <hr>

                <div class="row">
                    <div class="dashincomplete col-xs-12">
                        <h2><?= system_showText(LANG_SITEMGR_DASH_COMPLETION); ?></h2>
                        <p><?= system_showText(LANG_SITEMGR_TODO_WELCOME_TIP); ?></p>
                        <p><?= system_showText(LANG_SITEMGR_TODO_WELCOME_TIP2); ?></p>
                    </div>
                    <input type="hidden" name="dashcomplete" id="dashcomplete" value="<?=system_showText(LANG_SITEMGR_DASH_DONE);?> <?=system_showText(LANG_SITEMGR_DASH_DONE2);?>">
                </div>

                <div class="row completion-panel dashincomplete">
                    <?php foreach ($arrayCompletion as $completion) { ?>
                        <div class="col-sm-6 col-lg-<?=PAYMENTSYSTEM_FEATURE === 'on' ? '3' : '4'?> clearfix">
                            <div id="step_<?= $completion["option"] ?>" class="completion-tip <?= $completion["div_class"] ?>">
                                <span id="span_<?= $completion["option"] ?>" class="tipcomplete" title="<?= $completion["check_tip"] ?>" data-auxtip="<?= system_showText(LANG_SITEMGR_DASH_STEPSDONE) ?>" data-placement="bottom" onclick="updateDashboard('<?= $completion["option"] ?>')">
                                    <i id="icon_<?= $completion["option"] ?>" class="<?= $completion["icon_class"] ?>" data-mixpanel-event='Completed step "<?= $completion["title"] ?>"'></i>
                                </span>
                                <a href="<?= $completion["link"] ?>">
                                    <div class="pull-right" data-mixpanel-event='Clicked on "<?= $completion["title"] ?>"'>
                                        <strong><?= $completion["title"] ?></strong>
                                        <p><?= $completion["tip"] ?></p>
                                    </div>
                                </a>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    </section>
<?php } elseif (is_array($arrayCompletion) && count($arrayCompletion)) { ?>
    <section id="completion">
        <div class="panel panel-dashboard dashincomplete">
            <div class="panel-heading">
                <h1><?= system_showText(LANG_SITEMGR_DASH_COMPLETION); ?></h1>
                <p><?= system_showText(LANG_SITEMGR_TODO_WELCOME_TIP); ?></p>
                <p><?= system_showText(LANG_SITEMGR_TODO_WELCOME_TIP2); ?></p>
            </div>
            <div class="panel-body completion-panel">
                <?php foreach ($arrayCompletion as $completion) { ?>
                    <div class="col-sm-6 col-lg-<?=PAYMENTSYSTEM_FEATURE === 'on' ? '3' : '4'?> clearfix">
                        <div id="step_<?= $completion["option"] ?>" class="completion-tip <?= $completion["div_class"] ?>">
                            <span id="span_<?= $completion["option"] ?>" class="tipcomplete" title="<?= $completion["check_tip"] ?>" data-auxtip="<?= system_showText(LANG_SITEMGR_DASH_STEPSDONE) ?>" data-placement="bottom" onclick="updateDashboard('<?= $completion["option"] ?>')">
                                <i id="icon_<?= $completion["option"] ?>" class="<?= $completion["icon_class"] ?>"></i>
                            </span>
                            <a href="<?= $completion["link"] ?>">
                                <div class="pull-right">
                                    <strong><?= $completion["title"] ?></strong>
                                    <p><?= $completion["tip"] ?></p>
                                </div>
                            </a>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>

        <input type="hidden" name="dashcomplete" id="dashcomplete" value="<?=system_showText(LANG_SITEMGR_DASH_DONE);?>">
    </section>
<?php } ?>
