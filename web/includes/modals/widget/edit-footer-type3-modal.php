<!-- edit footer modal -->
<?php
$setting_facebook_link = $container->get('settings')->getDomainSetting('setting_facebook_link');
$twitter_account = $container->get('settings')->getDomainSetting('twitter_account');
$setting_linkedin_link = $container->get('settings')->getDomainSetting('setting_linkedin_link');
$setting_instagram_link = $container->get('settings')->getDomainSetting('setting_instagram_link');
$setting_pinterest_link = $container->get('settings')->getDomainSetting('setting_pinterest_link');
$content = json_decode($content, true);
$trans = json_decode($trans, true);
?>
<div class="modal-dialog modal-lg widget-footer" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"
                id="myModalLabel"><?= system_showText(LANG_SITEMGR_EDIT_WIDGET); ?> - <span class="widgetTitle"><?= $widgetTitle ?></span></h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <h4 class="subtitle"><?= system_showText(LANG_SITEMGR_LABEL_SOCIAL_NETWORKS); ?></h4>
                <form class="form" id="form_social" name="form_social">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="setting_facebook_link" class="control-label">
                                    <?= system_showText(LANG_SITEMGR_WIDGET_FACEBOOK_LINK);?>
                                </label>
                                <input type="text" name="setting_facebook_link" id="setting_facebook_link"
                                       value="<?= $setting_facebook_link ?>" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="setting_linkedin_link" class="control-label">
                                    <?= system_showText(LANG_SITEMGR_WIDGET_LINKEDIN_LINK);?>
                                </label>
                                <input type="text" name="setting_linkedin_link" id="setting_linkedin_link"
                                       value="<?= $setting_linkedin_link ?>" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="twitter_account" class="control-label">
                                    <?= system_showText(LANG_SITEMGR_WIDGET_TWITTER_LINK);?>
                                </label>
                                <input type="text" name="twitter_account" id="twitter_account"
                                       value="<?= $twitter_account ?>" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="setting_instagram_link" class="control-label">
                                    <?= system_showText(LANG_SITEMGR_WIDGET_INSTAGRAM_LINK);?>
                                </label>
                                <input type="text" name="setting_instagram_link" id="setting_instagram_link"
                                       value="<?= $setting_instagram_link ?>" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="setting_pinterest_link" class="control-label">
                                    <?= system_showText(LANG_SITEMGR_WIDGET_PINTEREST_LINK);?>
                                </label>
                                <input type="text" name="setting_pinterest_link" id="setting_pinterest_link"
                                       value="<?= $setting_pinterest_link ?>" class="form-control">
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <form class="form" name="form_footer" id="form_footer">

                <?php
                $hasNeutralColor = false;

                include INCLUDES_DIR . '/forms/form-design-settings.php';
                ?>

                <div class="showLabels">
                    <div class="row">
                        <div class="col-sm-12">
                            <p>
                                <a role="button" class="arrow-toggle collapsed" data-toggle="collapse" href="#collapseShowLabelsFooter" aria-expanded="false" aria-controls="collapseShowLabelsFooter" tabindex="25">
                                    <?= system_showText(LANG_SITEMGR_WIDGET_SHOW_FOOTER_LABELS) ?>
                                    <i class="fa fa-chevron-down" aria-hidden="true"></i>
                                    <i class="fa fa-chevron-up" aria-hidden="true"></i>
                                </a>
                            </p>

                            <div class="collapse" id="collapseShowLabelsFooter" style="height: auto;">
                                <div id="labelInputs">
                                    <input type="hidden" name="pageWidgetId" value="<?= $pageWidgetId ?>" />
                                    <? echo $widgetService->getGenericLabelInputs($content, $trans); ?>
                                </div>
                                <input type="hidden" name="saveWidgetForAllPages" value="1">
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="modal-footer">
            <div class="row">
                <div class="col-xs-12 text-right">
                    <button type="button" class="btn btn-lg"
                            data-dismiss="modal"><?= system_showText(LANG_SITEMGR_CANCEL); ?></button>
                    <button type="button" class="btn btn-primary btn-lg action-save" data-loading-text="<?=system_showText(LANG_LABEL_FORM_WAIT);?>"
                            onclick="<?= DEMO_LIVE_MODE ? 'livemodeMessage(true, false);' : "saveWidget('footer-with-social-media');" ?>"><?= system_showText(LANG_SITEMGR_SAVE_CHANGES); ?></button>

                </div>
            </div>
        </div>

        <div class="alert alert-warning text-center">
            <i class="fa fa-exclamation-circle" aria-hidden="true"></i>
            <?=system_showText(LANG_SITEMGR_CHANGES_WIDGET)?>
        </div>
    </div>
</div>
