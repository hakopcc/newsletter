<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/layout/footer.php
	# ----------------------------------------------------------------------------------------------------

        if (DEMO_LIVE_MODE) {
            include(INCLUDES_DIR."/modals/modal-livemode.php");
        }

        include(INCLUDES_DIR."/modals/modal-about.php");

        // GOOGLE ANALYTICS FEATURE
        if (GOOGLE_ANALYTICS_ENABLED == "on") {
            $google_analytics_page = "sitemgr";
            include(INCLUDES_DIR."/code/google_analytics.php");
        } ?>

        <!-- Auxiliary vars -->
        <script>
            DEFAULT_URL = "<?=DEFAULT_URL?>";
            SITEMGR_ALIAS = "<?=SITEMGR_ALIAS?>";
            DATEPICKER_FORMAT = '<?=(DEFAULT_DATE_FORMAT == "m/d/Y" ? "mm/dd/yyyy" : "dd/mm/yyyy")?>';
            DATEPICKER_LANGUAGE = '<?=$sitemgr_language?>';
            PATH = "<?= $_SERVER['PHP_SELF'] ?>";
            DOMAIN_ID = "<?=SELECTED_DOMAIN_ID?>";
            DATEPICKER_TIME_FORMAT = '<?=(CLOCK_TYPE === '12' ? 'hh:mm a' : 'HH:mm')?>';
        </script>

        <!-- Core Scripts -->

        <!-- Modernizr -->
        <script src="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/js/modernizr.custom.13060.js"></script>

        <!-- jQuery -->
        <script src="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/js/jquery-1.11.1.min.js"></script>

        <!-- jQuery - Sortable package only -->
        <script src="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/js/jquery-ui-1.11.1.min.js"></script>

        <!-- jQuery - Text Area Counter -->
        <script src="<?=DEFAULT_URL?>/scripts/jquery/jquery.textareaCounter.plugin.js"></script>

        <!-- Bootstrap -->
        <script src="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/js/bootstrap.min.js"></script>

        <!-- Admin Scripts -->
        <script src="<?=language_getFilePath($sitemgr_language, true);?>"></script>
        <script src="<?=DEFAULT_URL?>/scripts/specialChars.js"></script>
        <script src="<?=DEFAULT_URL?>/scripts/bulkupdate.js"></script>
        <script src="<?=DEFAULT_URL?>/scripts/banner.js"></script>
        <script src="<?=DEFAULT_URL?>/scripts/common.js"></script>

        <?php
        /* ModStores Hooks */
        if (!HookFire("sitemgr_custom_js_locationjs")) { ?>
            <script src="<?=DEFAULT_URL?>/scripts/location.js"></script>
        <?php } ?>

        <script src="<?=DEFAULT_URL?>/scripts/domain.js"></script>

        <!-- External Plugins -->

        <!--Bootstrap Date Picker-->
        <script src="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/js/bootstrap-datepicker-master/bootstrap-datepicker.js"></script>
        <?php if ($sitemgr_language != "en_us") { ?>
        <script src="<?=language_getDatePickPath($sitemgr_language, SELECTED_DOMAIN_ID, false, true);?>"></script>
        <?php } ?>

        <!--Moment-->
        <script src="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/js/moment/moment-with-locales.min.js"></script>

        <!-- Jquery Time Picker-->
        <script src="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/js/bootstrap-timepicker/bootstrap-timepicker.js"></script>

        <!-- Bootstrap file style-->
        <script src="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/js/bootstrap-filestyle/bootstrap-filestyle.js"></script>

        <!--Selectize-->
        <script src="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/js/selectize.js-master/selectize.min.js"></script>

        <!--Bootstrap Tour-->
        <script src="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/js/bootstrap-tour-0.9.3/bootstrap-tour.min.js"></script>

        <!--Colpick -->
        <script src="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/js/colpick/colpick.js"></script>

        <!--List.js -->
        <script src="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/js/list.js/list.min.js"></script>

        <!-- nano Scroller-->
        <script src="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/js/jquery.nanoscroller/jquery.nanoscroller.min.js"></script>

        <!-- Bootstrap bootbox-->
        <script src="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/js/bootstrap-bootbox/bootbox.min.js"></script>

        <!-- jQuery - jScroll -->
        <script src="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/js/jquery.jscroll.min.js"></script>

        <!-- jQuery - Visible -->
        <script src="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/js/jquery-visible/jquery.visible.min.js"></script>

        <!-- Notify -->
        <script src="<?=DEFAULT_URL?>/assets/js/lib/notify.js"></script>

        <!-- Mixpanel -->
        <script src="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/js/mixpanel.js"></script>

        <script>
            MIXPANEL_DISTINCTID = '<?=mixpanel_getDistinctId()?>';
        </script>

        <!-- Check Registration notify -->
        <script>
            $(document).ready(function(){
                <?php if(isset($checkRegistrationNotify) && $checkRegistrationNotify){ ?>
                    notify.error('eDirectory IS NOT FREE SOFTWARE! <br/> eDirectory activation required! <br/> Registration Process was corrupted!', '', {
                        fadeOut: 0,
                        closeButton: false,
                        blockScroll: true,
                        alignment: {
                            x: 'center',
                            y: 'center'
                        }
                    });
                <?php } ?>
            });
        </script>

        <!-- Bootstrap bootbox Locales-->
        <script>
            bootbox.setDefaults({
                /**
                * @optional String
                * @default: en
                * which locale settings to use to translate the three
                * standard button labels: OK, CONFIRM, CANCEL
                */
                locale: "<?=$sitemgr_languageArr[0]?>"
            });
        </script>

        <!--Custom javascripts for admin section -->
        <?php if ($customJS && file_exists($customJS)) {
            include($customJS);
        }

        if (system_getCountAccountsItems() <= MAXIMUM_NUMBER_OF_ITEMS_IN_SELECTIZE) {
        //Auxiliary code to build accounts dropdown
            if (!empty($auxAccountSelectize) and is_array($auxAccountSelectize) && count($auxAccountSelectize)) { ?>
            <script>
                $('.mail-select').selectize({
                    sortField: null,
                    persist: false,
                    maxItems: 1,
                    openOnFocus: false,
                    valueField: 'id',
                    labelField: 'name',
                    searchField: ['name', 'email'],
                    options: [
                        <? foreach ($auxAccountSelectize as $accSelectize) { ?>
                            {
                                email: '<?=addslashes($accSelectize["email"])?>',
                                name: '<?=addslashes($accSelectize["name"])?>',
                                id: <?=db_formatNumber($accSelectize["id"])?>},
                        <? } ?>
                    ],
                    render: {
                        item: function(item, escape) {
                            return '<div class="selectize-dropdown-content">' +
                                (item.name ? '<span class="name">' + escape(item.name) + ' </span> ' : ' <span class="email">' + escape(item.email) + ' </span> ')
                            '</div>';
                        },
                        option: function(item, escape) {
                            var label = item.name || item.email;
                            var caption = item.name ? item.email : null;
                            return '<div>' +
                                '<span class="label-name">' + escape(label) + '</strong>' +
                                (caption ? '<i>' + escape(caption) + '</i>' : '') +
                            '</div>';
                        }
                        },
                        onInitialize: function(){
                            var $this = this;
                            var account_id = $this.$input.data('value');
                            $this.setValue(account_id, true);
                        },
                    });
                </script>
            <?php } ?>
        <?php } else { ?>
            <script type="text/javascript">
                $('.mail-select').selectize({
                    sortField: null,
                    persist: false,
                    maxItems: 1,
                    openOnFocus: true,
                    valueField: 'id',
                    labelField: 'name',
                    searchField: ['name', 'email'],
                    loadThrottle: 250,
                    loadingClass: 'is-loading',
                    create: false,
                    options: [],
                    onInitialize: function(){
                        var $this = this;
                        var account_id = $this.$input.data('value');
                        $.ajax({
                            url: DEFAULT_URL + '/' + SITEMGR_ALIAS + '/account_ajax.php',
                            type: 'GET',
                            dataType: 'json',
                            data: {
                                account_id: account_id,
                            },
                            error: function () {
                                return false;
                            },
                            success: function (data) {
                                if (Object.keys(data).length > 0) {
                                    $this.clear(true);
                                    $this.removeOption(account_id);
                                    $.each(data, function (key, value) {
                                        $this.addOption(value);
                                    });
                                    $this.setValue(account_id, true);
                                }
                    }
                        });
                    },
                    load: function (query, callback) {
                        if (!query.length) return callback();
                        $.ajax({
                            url: DEFAULT_URL + '/' + SITEMGR_ALIAS + '/account_ajax.php',
                            type: 'GET',
                            dataType: 'json',
                            data: {
                                query: query,
                            },
                            error: function () {
                                callback();
                            },
                            success: function (data) {
                                callback(data);
                            }
                        });
                    },
                    render: {
                        item: function(item, escape) {
                            return '<div class="selectize-dropdown-content">' +
                                (item.name ? '<span class="name">' + escape(item.name) + ' </span> ' : ' <span class="email">' + escape(item.email) + ' </span> ')
                            '</div>';
                        },
                        option: function(item, escape) {
                            var label = item.name || item.email;
                            var caption = item.name ? item.email : null;
                            return '<div>' +
                                '<span class="label-name">' + escape(label) + '</strong>' +
                                (caption ? '<i>' + escape(caption) + '</i>' : '') +
                                '</div>';
                        }
                    },
                });
            </script>
        <?php } ?>

        <!-- Main Script -->
        <script src="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/js/adminpanel.js"></script>

        <script>

            $('#about-modal').on('show.bs.modal', function(){
                $.ajax({
                    url: '<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS;?>/about.php',
                    dataType: "html",
                    success: function(data) {
                        $('#about-modal .modal-body').html(data);
                        MixpanelHelper.track('Clicked on the menu About');
                    },
                });
            });

        </script>

        <?php
            if(isset($FirstStartDashboard)){
                include(EDIRECTORY_ROOT.'/'.SITEMGR_ALIAS.'/assets/custom-js/tour-sitemgr.php');
            }
        ?>

        <?php
            if (DEMO_LIVE_MODE && file_exists(EDIRECTORY_ROOT."/frontend/livebar.php")) {
                include(EDIRECTORY_ROOT."/frontend/livebar.php");
            }

            if (class_exists("JavaScriptHandler")) {
                JavaScriptHandler::render();
            }
            $container = SymfonyCore::getContainer();
            if(!empty($container)) {
                $twig = $container->get('twig');
                if (!empty($twig)) {
                    $notLoggedCriticalException = null;
                    $logger = $container->get('logger');
                    try {
                        echo $twig->render('CoreBundle::legacy-footer-renderjs.html.twig', []);
                    } catch (Exception $e) {
                        if(!empty($logger)){
                            $logger->error("Error on rendering template 'legacy-footer-renderjs.html.twig'.", ['exception' => $e]);
                        } else {
                            $notLoggedCriticalException = $e;
                        }
                    }
                    finally {
                        unset($logger);
                        if (!empty($notLoggedCriticalException)) {
                            throw $notLoggedCriticalException;
                        }
                    }
                }
                unset($twig);
            }
            unset($container);
        ?>

        <?php
        /* ModStores Hooks */

        HookFire("sitemgrfooter_after_render_js", [
            "feedName"          => &$feedName,
            "id"                => &$id,
            "account_id"        => &$account_id,
            "customJS"          => &$customJS,
            "members"           => &$members,
            "message"           => &$message,
            "screen"            => &$screen,
            "url_redirect"      => &$url_redirect,
            "url_base"          => &$url_base,
            "search_page"       => &$search_page,
            "sitemgr"           => &$sitemgr,
            "url_search_params" => &$url_search_params,
            "errorPage"         => &$errorPage,
        ]); ?>

    </body>

</html>
