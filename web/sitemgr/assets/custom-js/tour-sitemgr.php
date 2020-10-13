<script>
    $(document).ready(function(){
        let tour = new Tour({
            container: 'html',
            storage: false,
            onEnd: function() {
                MixpanelHelper.track('First steps Tutorial Ended');
                $('.user-account').removeClass('tour-enabled');
            },
            onShown: function() {
                var stepIndex = tour.getCurrentStep();
                var step = tour.getStep(stepIndex);
                MixpanelHelper.track('Step shown', {'Step': step.title});
                $('.user-account').addClass('tour-enabled');
            },
            steps: [{
                element: '#navBrand',
                title: '<?=system_showText(LANG_SITEMGR_DASHTOUR_1_TITLE);?>',
                content: '<?=system_showText(LANG_SITEMGR_DASHTOUR_1_TIP);?>',
                placement: 'bottom',
            },{
                element: '#notify-list',
                title: '<?=system_showText(LANG_SITEMGR_SUPPORT);?>',
                content: '<?=system_showText(LANG_SITEMGR_DASHTOUR_4_TIP);?>',
                placement: 'bottom'
            },{
                element: '#user-links-my-account',
                title: '<?=system_showText(LANG_MENU_ACCOUNT);?>',
                content: '<?=system_showText(LANG_SITEMGR_DASHTOUR_ACCOUNT);?>',
                placement: 'left',
                onShow: function(tour){
                    if(!$('.user-account').hasClass('is-open')){
                        $('.user-account').click();
                    }
                },
                onPrev: function(tour){
                    $('.user-account').click();
                },
                <?php if(!permission_hasSMPermSection(SITEMGR_PERMISSION_SITES)){ ?>
                    onNext: function(tour){
                        $('.user-account').click();
                    }
                <?php } ?>
            },

            <?php if (permission_hasSMPermSection(SITEMGR_PERMISSION_SITES)) { ?>
                {
                    element: '#user-links-sites',
                    title: '<?=system_showText(LANG_SITEMGR_NAVBAR_DOMAIN_PLURAL);?>',
                    content: '<?=system_showText(LANG_SITEMGR_DASHTOUR_2_TIP);?>',
                    placement: 'left',
                    onNext: function(tour){
                        $('.user-account').click();
                    }
                },
            <?php } ?>

            {
                element: '#tour-dashboard',
                title: '<?=system_showText(LANG_SITEMGR_DASHBOARD);?>',
                content: '<?=system_showText(LANG_SITEMGR_DASHTOUR_5_TIP);?>',
                placement: 'right',
            },

            <?php if (permission_hasSMPermSection(SITEMGR_PERMISSION_CONTENT)) { ?>
                {
                    element: '#tour-content',
                    title: '<?=system_showText(LANG_SITEMGR_CONTENT_MANAGER);?>',
                    content: '<?=system_showText(LANG_SITEMGR_DASHTOUR_6_TIP);?>',
                    placement: 'right'
                },
            <?php } ?>

            <?php if (permission_hasSMPermSection(SITEMGR_PERMISSION_DESIGN)) { ?>
                {
                    element: '#tour-design',
                    title: '<?=system_showText(LANG_SITEMGR_DESIGN_CUSTOM);?>',
                    content: '<?=system_showText(LANG_SITEMGR_DASHTOUR_9_TIP);?>',
                    placement: 'right'
                },
            <?php } ?>

            <?php if (permission_hasSMPermSection(SITEMGR_PERMISSION_ACTIVITY)) { ?>
                {
                    element: '#tour-activity',
                    title: '<?=system_showText(LANG_SITEMGR_ACTIVITY);?>',
                    content: '<?=system_showText(LANG_SITEMGR_DASHTOUR_7_TIP);?>',
                    placement: 'right'
                },
            <?php } ?>

            <?php if (permission_hasSMPermSection(SITEMGR_PERMISSION_PROMOTE)) { ?>
                {
                    element: '#tour-promote',
                    title: '<?=system_showText(LANG_SITEMGR_PROMOTE);?>',
                    content: '<?=system_showText(LANG_SITEMGR_DASHTOUR_8_TIP);?>',
                    placement: 'right'
                },
            <?php } ?>

            <?php if (permission_hasSMPermSection(SITEMGR_PERMISSION_CONFIG)) { ?>
                {
                    element: '#tour-settings',
                    title: '<?=system_showText(LANG_SITEMGR_CONFIG);?>',
                    content: '<?=system_showText(LANG_SITEMGR_DASHTOUR_10_TIP);?>',
                    placement: 'right'
                },
            <?php } ?>

            <?php if (permission_hasSMPermSection(SITEMGR_PERMISSION_ACCOUNTS)) { ?>
                {
                    element: '#tour-user-accounts',
                    title: '<?=system_showText(LANG_SITEMGR_NAVBAR_ACCOUNTS);?>',
                    content: '<?=system_showText(LANG_SITEMGR_DASHTOUR_3_TIP);?>',
                    placement: 'right',
                },
            <?php } ?>

            <?php if (permission_hasSMPermSection(SITEMGR_PERMISSION_MOBILE)) { ?>
                {
                    element: '#tour-mobile-apps',
                    title: '<?=system_showText(LANG_SITEMGR_MOBILE_APPS);?>',
                    content: '<?=system_showText(LANG_SITEMGR_DASHTOUR_11_TIP);?>',
                    placement: 'right',
                    next: -1
                }
            <?php } ?>
            ]
        });

        $('#start-tour').click( function() {
            tour.restart();
            tour.start();
            MixpanelHelper.track('First steps Tutorial Started');
        });
    });
</script>
