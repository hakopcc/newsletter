<?php

    /*==================================================================*\
    ######################################################################
    #                                                                    #
    # Copyright 2018 Arca Solutions, Inc. All Rights Reserved.           #
    #                                                                    #
    # This file may not be redistributed in whole or part.               #
    # eDirectory is licensed on a per-domain basis.                      #
    #                                                                    #
    # ---------------- eDirectory IS NOT FREE SOFTWARE ----------------- #
    #                                                                    #
    # http://www.edirectory.com | http://www.edirectory.com/license.html #
    ######################################################################
    \*==================================================================*/

    # ----------------------------------------------------------------------------------------------------
    # * FILE: /frontend/widgets/footer-with-logo.php
    # ----------------------------------------------------------------------------------------------------

    setting_get('footer_copyright', $footer_copyright);

?>

    <!-- Footer Begin -->
    <footer class="footer" data-type="5" is-inverse="false">
        <div class="footer-content" data-trans="Footer with Newsletter">
            <div class="container">
                <div class="footer-logo">
                    <a href="<?=DEFAULT_URL?>" class="logo-link">
                        <img src="<?=image_getLogoImagePath() . '?' . date('U');?>" class="img-responsive" alt="<?=EDIRECTORY_TITLE?>">
                    </a>
                </div>

                <?php if ($setting_twitter_link || $setting_facebook_link || $setting_linkedin_link || $setting_instagram_link || $setting_pinterest_link ) { ?>
                    <div class="footer-social">
                        <?php if (!empty($setting_facebook_link)) { ?>
                            <a href="<?=$setting_facebook_link?>" target="_blank" class="social-link">
                                <i class="fa fa-facebook"></i>
                            </a>
                        <?php } ?>
                        <?php if (!empty($setting_linkedin_link)) { ?>
                            <a href="<?=$setting_linkedin_link?>" target="_blank" class="social-link">
                                <i class="fa fa-linkedin"></i>
                            </a>
                        <?php } ?>
                        <?php if (!empty($setting_twitter_link)) { ?>
                            <a href="<?=$setting_twitter_link?>" target="_blank" class="social-link">
                                <i class="fa fa-twitter"></i>
                            </a>
                        <?php } ?>
                        <?php if (!empty($setting_instagram_link)) { ?>
                            <a href="<?=$setting_instagram_link?>" target="_blank" class="social-link">
                                <i class="fa fa-instagram"></i>
                            </a>
                        <?php } ?>
                        <?php if (!empty($setting_pinterest_link)) { ?>
                            <a href="<?=$setting_pinterest_link?>" target="_blank" class="social-link">
                                <i class="fa fa-pinterest"></i>
                            </a>
                        <?php } ?>
                    </div>
                <?php } ?>

                <div class="footer-actions">
                    <div class="footer-item" data-content="site-content">
                        <div class="heading footer-item-title" data-trans="Site Content">
                        </div>
                        <div class="footer-item-content">
                            <?php include EDIRECTORY_ROOT.'/frontend/footer_menu.php'; ?>
                        </div>
                    </div>

                </div>
                
                <?php if (BRANDED_PRINT === 'on' || $footer_copyright) { ?>
                    <div class="footer-bar">
                        <div class="container">
                            <div class="wrapper">
                                <div class="footer-copyright"><?=$footer_copyright?></div>

                                <?php  if (BRANDED_PRINT === 'on') { ?>
                                <div class="footer-powered">
                                    <?=LANG_POWEREDBY?>
                                    <a href="http://www.edirectory.com<?=(string_strpos($_SERVER['HTTP_HOST'], '.com.br') !== false ? '.br' : '')?>" class="edirectory-link">
                                        <img src="/assets/images/<?php echo($widgetContent['backgroundColor'] === 'base' ? 'edirectory-logo-inverse' : 'edirectory-logo'); ?>.svg" alt="eDirectory Cloud Service &trade;">
                                    </a>
                                </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>

            </div>
        </div>
    </footer>
    <!-- Footer End -->
