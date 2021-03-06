<?
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/content/faq/index.php
	# ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# LOAD CONFIG
	# ----------------------------------------------------------------------------------------------------
	include("../../../conf/loadconfig.inc.php");

	# ----------------------------------------------------------------------------------------------------
	# SESSION
	# ----------------------------------------------------------------------------------------------------
	sess_validateSMSession();
	permission_hasSMPerm();

    # ----------------------------------------------------------------------------------------------------
    # AUX
    # ----------------------------------------------------------------------------------------------------
    extract($_POST);
    extract($_GET);

    # ----------------------------------------------------------------------------------------------------
    # TRACK MIXPANEL EVENT
    # ----------------------------------------------------------------------------------------------------
    if (!isset($_GET["message"])) mixpanel_track("Accessed Manage FAQ section");

    # ----------------------------------------------------------------------------------------------------
    # SUBMIT - FAQ
    # ----------------------------------------------------------------------------------------------------
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if ($del_faq_id) {
            mixpanel_track("Deleted a FAQ entry");
            $id = intval($del_faq_id);
            $faqObj = new FAQ($id);
            $faqObj->Delete();
            header("Location: ".DEFAULT_URL."/".SITEMGR_ALIAS."/content/faq/index.php?message=3");
            exit;
        } else {
            if ($FAQ_post_submit) {

                if ( validate_form("faq", $_POST, $error) ) {

                    $faqObj = new FAQ();

                    if ($faq_section_front == "on") {
                        $faqObj->setString("frontend", "y");
                    }
                    if ($faq_section_members == "on") {
                        $faqObj->setString("member", "y");
                    }

                    mixpanel_track("Created a new FAQ entry");

                    $faqObj->setString("editable", "y");
                    $faqObj->setString("question", $faq_question);
                    $faqObj->setString("answer", $faq_answer);
                    $faqObj->Save();
                    header("Location: ".DEFAULT_URL."/".SITEMGR_ALIAS."/content/faq/index.php?message=0");
                    exit;
                } else {
                    header("Location: ".DEFAULT_URL."/".SITEMGR_ALIAS."/content/faq/index.php?message=1");
                    exit;
                }

            } else if ($FAQ_edit_submit) {

                $faqObj = new FAQ($faq_id);

                ($faq_section_front_edit) ? $faqObj->setString("frontend", "y") : $faqObj->setString("frontend", "n");
                ($faq_section_members_edit) ? $faqObj->setString("member", "y") : $faqObj->setString("member", "n");

                $faqObj->setString("question", $faq_question_edit);
                $faqObj->setString("answer", $faq_answer_edit);
                $faqObj->Save();
                header("Location: ".DEFAULT_URL."/".SITEMGR_ALIAS."/content/faq/index.php?message=0");
                exit;

            }
        }
    }

    # PAGE BROWSING
    $pageObjFaq  = new pageBrowsing("FAQ", $screen, false, "id", "question", $letter, "editable='y'");
    $contentsFaq = $pageObjFaq->retrievePage();

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
                    require(SM_EDIRECTORY_ROOT."/registration.php");
                    require(EDIRECTORY_ROOT."/includes/code/checkregistration.php");
                ?>

                <section class="heading">
                    <h1><?=string_ucwords(system_showText(LANG_SITEMGR_FREQUENTLYASKEDQUESTIONS))?></h1>
                </section>

                <div class="tab-options">
                    <div class="tab-content">
                        <section class="tab-pane active">
                            <form name="FAQ_post" id="FAQ_post" action="<?=system_getFormAction($_SERVER["PHP_SELF"])?>" method="post">
                                <input type="hidden" name="del_faq_id" id="faq_id">
                            </form>

                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <a href="javascript:void(0);" onclick="faq_add();"class="btn btn-primary"><?=system_showText(LANG_SITEMGR_NEW_FAQ)?></a>
                                </div>

                                <form id="FAQ_add" role="form" class="hideForm" name="FAQ_add" action="<?=system_getFormAction($_SERVER["PHP_SELF"]);?>" method="post" style="display:none;">
                                    <div class="panel-body panel-border-bot">
                                        <div class="col-xs-12">
                                            <div class="form-group form-horizontal row">
                                                <label for="faq_question" class="col-xs-2 control-label"><?=system_showText(LANG_SITEMGR_SETTINGS_FAQ_QUESTION)?></label>
                                                <div class="col-xs-10">
                                                    <textarea class="form-control" name="faq_question" id="faq_question" rows="2"></textarea>
                                                </div>
                                            </div>
                                            <div class="form-group form-horizontal row">
                                                <label for="faq_answer" class="col-xs-2 control-label"><?=system_showText(LANG_SITEMGR_SETTINGS_FAQ_ANSWER)?></label>
                                                <div class="col-xs-10">
                                                    <textarea class="form-control" name="faq_answer" id="faq_answer" rows="4"></textarea>
                                                </div>
                                            </div>
                                            <div class="form-group form-horizontal row">
                                                <label class="col-xs-2 control-label"><?=system_showText(LANG_SITEMGR_LABEL_SECTION)?></label>
                                                <div class="col-xs-10">
                                                    <div class="checkbox-inline">
                                                        <label>
                                                            <input type="checkbox" name="faq_section_front">
                                                            <?=system_showText(LANG_SITEMGR_FRONT)?>
                                                        </label>
                                                    </div>
                                                    <div class="checkbox-inline">
                                                        <label>
                                                            <input type="checkbox" name="faq_section_members">
                                                            <?=system_showText(LANG_SITEMGR_MEMBERS)?>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-xs-10 col-xs-offset-2">
                                                    <button class="btn btn-sm btn-primary action-save" type="submit" name="FAQ_post_submit" value="Submit" data-loading-text="<?=system_showText(LANG_LABEL_FORM_WAIT);?>"><?=system_showText(LANG_SITEMGR_SAVE);?></button>
                                                    <button class="btn btn-sm btn-default" type="button" onclick="hideForm();"><?=system_showText(LANG_BUTTON_CANCEL);?></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>

                                <?php if ($contentsFaq) { ?>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                            <tr>
                                                <th>
                                                    <span><?=system_showText(LANG_SITEMGR_SETTINGS_FAQ_QUESTION)?></span>
                                                </th>
                                                <th class="text-center" width="140px">
                                                    <span><?=system_showText(LANG_LABEL_OPTIONS)?></span>
                                                </th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php
                                                foreach ($contentsFaq as $content) {
                                                    $id = $content->getNumber("id");
                                            ?>

                                                <tr>
                                                    <td class="faq-item">
                                                        <b><?=$content->getString("question")?></b>
                                                        <blockquote class="small"><?=$content->getString("answer")?></blockquote>

                                                        <form id="FAQ_edit<?=$id?>" role="form" class="hideForm" name="FAQ_edit" action="<?=system_getFormAction($_SERVER["PHP_SELF"]);?>" method="post" style="display:none;">
                                                            <div class="col-xs-12">
                                                                <input type="hidden" name="faq_id" value="<?=$id?>">
                                                                <div class="form-group form-horizontal row">
                                                                    <label for="faq_question_edit<?=$id?>" class="col-xs-2 control-label"><?=system_showText(LANG_SITEMGR_SETTINGS_FAQ_QUESTION)?>: </label>
                                                                    <div class="col-xs-10">
                                                                        <textarea class="form-control" name="faq_question_edit" id="faq_question_edit<?=$id?>" rows="2"><?=$content->getString("question")?></textarea>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group form-horizontal row">
                                                                    <label for="faq_answer_edit<?=$id?>" class="col-xs-2 control-label"><?=system_showText(LANG_SITEMGR_SETTINGS_FAQ_ANSWER)?>: </label>
                                                                    <div class="col-xs-10">
                                                                        <textarea class="form-control" name="faq_answer_edit" id="faq_answer_edit<?=$id?>" rows="4"><?=$content->getString("answer")?></textarea>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group form-horizontal row">
                                                                    <label class="col-xs-2 control-label"><?=system_showText(LANG_SITEMGR_LABEL_SECTION)?>: </label>
                                                                    <div class="col-xs-10">
                                                                        <div class="checkbox-inline">
                                                                            <label>
                                                                                <input type="checkbox" name="faq_section_front_edit" <?=($content->getString("frontend") == 'y') ? "checked=\"checked\"" : ""?>>
                                                                                <?=system_showText(LANG_SITEMGR_FRONT)?>
                                                                            </label>
                                                                        </div>
                                                                        <div class="checkbox-inline">
                                                                            <label>
                                                                                <input type="checkbox" name="faq_section_members_edit" <?=($content->getString("member") == 'y') ? "checked=\"checked\"" : ""?>>
                                                                                <?=system_showText(LANG_SITEMGR_MEMBERS)?>
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-xs-10 col-xs-offset-2">
                                                                        <button class="btn btn-sm btn-primary action-save" type="submit" name="FAQ_edit_submit" value="Submit" data-loading-text="<?=system_showText(LANG_LABEL_FORM_WAIT);?>"><?=system_showText(LANG_SITEMGR_SAVE);?></button>
                                                                        <button class="btn btn-sm btn-default" type="button" onclick="hideFormFaq('FAQ_edit<?=$id?>');"><?=system_showText(LANG_BUTTON_CANCEL);?></button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </td>

                                                    <td nowrap class="main-options text-center">
                                                        <a class="btn btn-primary btn-xs" href="javascript: void(0);" onclick="faq_edit(<?=$id?>)">
                                                            <?=string_strtolower(system_showText(LANG_SITEMGR_EDIT))?>
                                                        </a>
                                                        <a class="btn btn-warning btn-xs" href="javascript: void(0);" onclick="faq_delete(<?=$id?>)">
                                                            <?=string_strtolower(system_showText(LANG_SITEMGR_DELETE))?>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php } else { ?>
                                    <div class="col-sm-12">
                                        <br>
                                        <p class="alert alert-info"><?=system_showText(LANG_SITEMGR_SETTINGS_FAQ_NOFAQ);?></p>
                                    </div>
                                <?php } ?>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </main>
<?php
	# ----------------------------------------------------------------------------------------------------
	# FOOTER
	# ----------------------------------------------------------------------------------------------------
    $customJS = SM_EDIRECTORY_ROOT."/assets/custom-js/faq.php";
    include(SM_EDIRECTORY_ROOT."/layout/footer.php");
