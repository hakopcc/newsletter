<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
    # * FILE: /ed-admin/configuration/payment/index.php
    # ----------------------------------------------------------------------------------------------------

	# ----------------------------------------------------------------------------------------------------
	# LOAD CONFIG
	# ----------------------------------------------------------------------------------------------------
	include '../../../conf/loadconfig.inc.php';

	# ----------------------------------------------------------------------------------------------------
	# SESSION
	# ----------------------------------------------------------------------------------------------------
	sess_validateSMSession();
    permission_hasSMPerm();

    mixpanel_track('Accessed section Manage Levels & Pricing');

    $container = SymfonyCore::getContainer();

    # ----------------------------------------------------------------------------------------------------
	# CODE
	# ----------------------------------------------------------------------------------------------------
    include INCLUDES_DIR.'/code/paymentgateway.php';

    # ----------------------------------------------------------------------------------------------------
	# HEADER
	# ----------------------------------------------------------------------------------------------------
	include SM_EDIRECTORY_ROOT.'/layout/header.php';

    # ----------------------------------------------------------------------------------------------------
	# FUNCTIONS
	# ----------------------------------------------------------------------------------------------------

    /**
     * Checks if a tab is "active" judging from what comes from $_SESSION['PaymentOptions']['type'].
     *
     * @param string $name the name of the post action
     * @param boolean $default is this the default option?
     * @return string "active" or nothing.
     */
    function checkActiveTab( $name, $default = false, $class = 'active' )
    {
        $return = null;

        if( !empty( $_SESSION['PaymentOptions']['type'] ) )
        {
            $postResponseType = $_SESSION['PaymentOptions']['type'];

            if( is_array( $name ) )
            {
                $isSelected = in_array( $postResponseType, $name );
            }
            else
            {
                $isSelected = $name == $postResponseType;
            }

            $isSelected and $return = $class;
        }

        return $default && empty( $_SESSION['PaymentOptions']['type'] ) ? $class : $return;
    }

/**
 * Adds HTML to create a checkbox for each of a module's levels.
 * Used in the level options page.
 *
 * @param string $name The input name value
 * @param string $title The option title
 * @param string $tip The option explanation
 * @param array $levelvalues an array containing the levels
 * @param mixed $levelObj an isntance of the module listinglevel class
 * @param array $array_fields additional fields not contained in class properties
 * @param string $type module type
 * @param $class
 * @param bool $customField
 * @param bool $customFieldGroup
 */
    function createCheckboxField( $name, $title, $tip, $levelvalues, $levelObj, $array_fields, $type, $class, $customField = false, $customFieldGroup = false)
    {
        echo "<td> $title <small class=\"help-block\"> $tip </small></td>";

        foreach ($levelvalues as $key => $levelvalue)
        {
            $checked = null;

            $disabled = PAYMENTSYSTEM_FEATURE === 'off' && $levelvalue > 10 ? 'disabled' : '';

            if($customFieldGroup) {
                if ((is_array($array_fields[$levelvalue]['listingtfieldgroup_id']) && in_array($name, $array_fields[$levelvalue]['listingtfieldgroup_id']))) {
                    $checked = 'checked="checked"';
                }
            } elseif($customField) {
                if ((is_array($array_fields[$levelvalue]['listingtfield_id']) && in_array($name, $array_fields[$levelvalue]['listingtfield_id']))) {
                    $checked = 'checked="checked"';
                }
            } elseif ((isset($levelObj->{$name}[$key]) && $levelObj->{$name}[$key] == 'y') || (is_array($array_fields[$levelvalue]) && in_array($name, $array_fields[$levelvalue]))) {
                $checked = 'checked="checked"';
            }

            if($customFieldGroup) {
                echo '<td class="checkbox-table">'
                    . "    <input name=\"customFieldGroup[$type][$name][$levelvalue]\" data-module=\"$type\" data-level=\"$levelvalue\" class=\"$class\" type=\"checkbox\" $checked $disabled>"
                    . '</td>';
            } elseif (!$customField) {
                echo '<td class="checkbox-table">'
                    . "    <input name=\"levelOption[$type][$name][$levelvalue]\" data-module=\"$type\" data-level=\"$levelvalue\" class=\"$class\" type=\"checkbox\" $checked $disabled>"
                    . '</td>';
            } else {
                echo '<td class="checkbox-table">'
                    . "    <input name=\"customField[$type][$name][$levelvalue]\" data-module=\"$type\" data-level=\"$levelvalue\" class=\"$class\" type=\"checkbox\" $checked $disabled>"
                    . '</td>';
            }
        }
    }

/**
 * Adds HTML to create a numeric text field for each of a module's levels.
 * Used in the level options page.
 *
 * @param string $name The input name value
 * @param string $title The option title
 * @param string $tip The option explanation
 * @param int $max the maximum value allowed. HTML5
 * @param int $min the minimum value allowed. HTML5
 * @param array $levelvalues an array containing the levels
 * @param mixed $levelObj an isntance of the module listinglevel class
 * @param array $array_fields additional fields not contained in class properties
 * @param string $type module type
 * @param bool $customField
 */
    function createNumericField( $name, $title, $tip, $max, $min, $levelvalues, $levelObj, $array_fields, $type, $customField = false)
    {
        echo "<td> $title <small class=\"help-block\"> $tip </small></td>";

        foreach ($levelvalues as $key => $levelvalue)
        {
            if($customField) {
                $value = $array_fields[$levelvalue]['listingtfield_id'][$name];
            } else {
                $value = !empty($levelObj->{$name}[$key]) ? $levelObj->{$name}[$key] : $array_fields[$levelvalue][$name];
            }

            $default = 'value="'.sprintf('%d', $value).'"';

            $disabled = PAYMENTSYSTEM_FEATURE === 'off' && $levelvalue > 10 ? 'disabled' : '';

            if(!$customField) {
                echo '<td class="form-group-table">'
                    . "    <input name=\"levelOption[$type][$name][$levelvalue]\" type=\"number\" min=\"$min\" max=\"$max\" class=\"form-control input-sm\" $default $disabled>"
                    . '</td>';
            } else {
                echo '<td class="form-group-table">'
                    . "    <input name=\"customField[$type][$name][$levelvalue]\" type=\"number\" min=\"$min\" max=\"$max\" class=\"form-control input-sm\" $default $disabled>"
                    . '</td>';
            }
        }
    }

    /* Loads messages from session, if any.*/
    MessageHandler::unserialize();
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
                        <h1 class="section-heading-title"><?=system_showText(LANG_SITEMGR_PAYMENT_OPTIONS);?> <span class="icon-help8" data-toggle="tooltip" data-placement="bottom" title="<?=system_showText(LANG_SITEMGR_SETTINGS_MANAGE_LEVELS_TIP1);?>"></span></h1>
                    </div>
                    <div role="tablist" class="header-bar-action">
                        <a href="#payment-pricing" id="pricing-tab" class="action-button <?=checkActiveTab('levels', true, 'is-active')?>" data-toggle="tab" role="tab"><?=system_showText(LANG_SITEMGR_SETTINGS_PAYMENTS_LEVELS_TAB);?></a>
                        <a href="#payment-options" id="options-tab" class="action-button <?=checkActiveTab('currencyOptions', false, 'is-active')?>" data-toggle="tab" role="tab"><?=system_showText(LANG_SITEMGR_SETTINGS_PAYMENTS_CURRENCY_TAB);?></a>
                        <?php if (PAYMENTSYSTEM_FEATURE === 'on') { ?>
                            <a href="#payment-gateways" id="gateways-tab" class="action-button <?=checkActiveTab('gateways', false, 'is-active')?>" data-toggle="tab" role="tab"><?=system_showText(LANG_SITEMGR_SETTINGS_PAYMENTS_GATEWAY_TAB);?></a>
                        <?php } ?>
                    </div>
                </section>

                <form name="header" id="header" method="post" action="<?=system_getFormAction($_SERVER['PHP_SELF'])?>">
                    <input type="hidden" name="save-pricing" id="save-pricing" value="">

                    <div class="tab-options custom-tabs">
                        <div class="tab-content">
                            <section id="payment-pricing" class="tab-pane <?=checkActiveTab( 'levels', true )?>">
                                <ul class="nav nav-pills" role="tablist">
                                    <li class="<?=(!$_GET['option']) || $_GET['option'] === 'listing' ? 'active' : ''?>"><a href="#pricing-listing" class="module-toggle" data-toggle="tab" role="tab"><?=system_showText(LANG_SITEMGR_NAVBAR_LISTING);?></a></li>

                                    <?php
                                        foreach ( $availableModules as $type => $value ){
                                            if( $value['active'] ){
                                                $active = $_GET['option'] === $type ? 'active' : '';
                                                echo "<li class=\"{$active}\"><a href=\"#pricing-{$type}\" class=\"module-toggle\" data-toggle=\"tab\" role=\"tab\">{$value['name']}</a></li>";
                                            }
                                        }
                                    ?>
                                </ul>

                                <div class="tab-content content-pills">
                                    <?php
                                        if (PAYMENTSYSTEM_FEATURE === 'off' && system_getListingCount()) {
                                            include INCLUDES_DIR.'/views/upgrade_plan_banner.php';
                                        }

                                        if (STRIPEPAYMENT_FEATURE === 'on' && PAYMENTSYSTEM_FEATURE === 'on'){
                                            echo "<div class='alert alert-warning'>".system_showText(LANG_SITEMGR_SETTINGS_PAYMENTS_GATEWAY_STRIPE_TIP2)."</div>";
                                        }
                                    ?>
                                    <div class="tab-pane modules-pane <?=(!$_GET['option']) || $_GET['option'] === 'listing' ? 'active' : ''?>" id="pricing-listing">
                                        <?php
                                            $type = 'listing';
                                            include INCLUDES_DIR.'/forms/form-payment-pricing.php';
                                        ?>
                                    </div>
                                    <?php
                                        foreach ( $availableModules as $type => $value ){
                                            if( $value['active'] ){
                                                $active = $_GET['option'] === $type ? 'active' : '';
                                                echo "<div class=\"tab-pane modules-pane {$active}\" id=\"pricing-{$type}\">";
                                                include INCLUDES_DIR.'/forms/form-payment-pricing.php';
                                                echo '</div>';
                                            }
                                        }
                                    ?>
                                </div>
                            </section>

                            <section id="payment-options" class="tab-pane <?=checkActiveTab( [
                                'currencyOptions',
                                'taxOptions',
                                'invoiceOptions'
                            ] )?>">
                                <?php include INCLUDES_DIR.'/forms/form-payment-options.php'; ?>
                            </section>

                            <?php if (PAYMENTSYSTEM_FEATURE === 'on') { ?>
                                <section id="payment-gateways" class="tab-pane <?=checkActiveTab('gateways')?>">
                                    <?include INCLUDES_DIR.'/forms/form-payment-gateways.php';?>
                                </section>
                            <?php } ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>
<?php
	# ----------------------------------------------------------------------------------------------------
	# FOOTER
    # ----------------------------------------------------------------------------------------------------
    $customJS = SM_EDIRECTORY_ROOT.'/assets/custom-js/manage-levels-pricing.php';
	include SM_EDIRECTORY_ROOT.'/layout/footer.php';
