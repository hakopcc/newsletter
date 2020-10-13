<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2018 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/assets/custom-js/modules.php
	# ----------------------------------------------------------------------------------------------------

    //Section
    $maxCategoryAllowed = MAX_CATEGORY_ALLOWED;
    if (string_strpos($_SERVER['PHP_SELF'], '/' .LISTING_FEATURE_FOLDER. '/') !== false || string_strpos($_SERVER['PHP_SELF'], '/claim/') !== false) {
        if (string_strpos($_SERVER['PHP_SELF'], '/listing.php') !== false) {
            $feedName = 'listing';
        } elseif (string_strpos($_SERVER['PHP_SELF'], '/type.php') !== false) {
            $feedName = 'listingtemplate';
        }
        $maxCategoryAllowed = LISTING_MAX_CATEGORY_ALLOWED;
    } elseif (string_strpos($_SERVER['PHP_SELF'], '/' .EVENT_FEATURE_FOLDER. '/event.php') !== false) {
        $feedName = 'event';
        $eventScript = true;
    } elseif (string_strpos($_SERVER['PHP_SELF'], '/' .CLASSIFIED_FEATURE_FOLDER. '/classified.php') !== false) {
        $feedName = 'classified';
    } elseif (string_strpos($_SERVER['PHP_SELF'], '/' .ARTICLE_FEATURE_FOLDER. '/article.php') !== false) {
        $feedName = 'article';
    } elseif (string_strpos($_SERVER['PHP_SELF'], '/' .BANNER_FEATURE_FOLDER. '/') !== false) {
        $bannerScript = true;
    } elseif (string_strpos($_SERVER['PHP_SELF'], '/' .PROMOTION_FEATURE_FOLDER. '/deal.php') !== false) {
        $promotionScript = true;
    } elseif (string_strpos($_SERVER['PHP_SELF'], '/' .BLOG_FEATURE_FOLDER. '/blog.php') !== false) {
        $feedName = 'blog';
    }

    if ($loadMap) {
        include EDIRECTORY_ROOT. '/includes/code/maptuning_forms.php';
    }

    if (string_strpos($_SERVER['PHP_SELF'], '/' .SITEMGR_ALIAS. '/') !== false) {
        $tutSteps = '';
        if (is_array($arrayTutorial)) {
            foreach ($arrayTutorial as $key => $fieldTut) {
                $tutSteps .= '{
                            placement: "' . $fieldTut['placement'] . '",
                            element: "#' . $fieldTut['id'] . '",
                            title: "' . addslashes($fieldTut['field']) . '",
                            content: "' . addslashes($fieldTut['content']) . '"
                          },';
            }
        }
        $tutSteps = string_substr($tutSteps, 0, -1);
    }

    /* ModStores Hooks */
    HookFire('modulesfoorter_before_render_js', [
        'feedName' => &$feedName,
        'members' => &$members,
    ]);

?>

	<script>
        <? if (string_strpos($_SERVER['PHP_SELF'], '/' .SITEMGR_ALIAS. '/') !== false) { ?>
            //show modal
            $(document).on('click', '.create-new-category-button', function(){
                var origin = '<?= $_SERVER['PHP_SELF'] ?>'.split("/");
                if($('.input-categories-list').length>0 && !$('.checkDisableCategory').hasClass('hide')){
                    $('.checkDisableCategory').addClass('hide');
                }
                MixpanelHelper.track('Clicked on Create a new category', {'Section': origin[3] +' form'});
                $('#modal-create-categories').modal('show');
            });
        <?php }?>
        <? if (string_strpos($_SERVER['PHP_SELF'], '/' .MEMBERS_ALIAS. '/') !== false) { ?>
            $(document).on('click', '#upgradeButton', function(){
                $.post("<?=DEFAULT_URL."/".MEMBERS_ALIAS."/ajax.php"?>", {
                    ajax_type: 'sendEmailUpgrade',
                    id: $('#id').val()
                }, function () {}).done(function () {
                    $('.first-step').fadeOut(function(){
                        $('.icon-item').addClass('is-flipped');
                        $('.second-step').fadeIn();
                        btn = $('#upgradeButton');
                        btn.button('reset');
                    })
                });

            });
        <?php }?>

        $(document).ready(function () {
            $('#loading_ajax').hide();

            //Load Map
            <?php if ($hasValidCoord && $feedName && setting_get('locations_enable')!=="off") { ?>
                loadMap(document.<?=$feedName?>, true);
            <?php } ?>

            //Load video automatically
            if ($("#video").val()) {
                autoEmbed('video', '<?=system_showText(LANG_VIDEO_NOTFOUND);?>', '<?=system_showText(LANG_SITEMGR_SUCCESSFULLY_UPLOADED_VIDEO);?>');
            }

            <?php if ($promotionScript) { ?>
                <?php if (!$members) { ?>
                    // Initialize the selectize control for the account field
                    window.selectAcc = $('#account_id').selectize({});

                    // fetch the instance
                    var selectize1 = window.selectAcc[0].selectize;
                <?php } ?>

                showAmountType('<?=$aux_deal_type?>', 'show');
                calculateDiscount();
            <?php } ?>

            <?php if($feedName == 'listing'){ ?>
                function rangeWidget(el, value) {
                    $(`#${el} .range-icon`).css('opacity', '0');

                    for (let index = 1; index <= value; index++) {
                        $(`#${el} .range-icon[data-item="range-${index}"]`).css('opacity', '1');
                    }
                }

                $(".range-slider").on("input", function(e){
                    let rangeRef = $(this).data('ref');
                    rangeWidget(rangeRef, e.target.value);
                });
            <?php } ?>
        });

        function sendAuthorImage(path, action) {

            var returnMessageAuthor = $("#returnMessageAuthor");

            if ($('#article input[type="file"][name="author_image-image"]').length &&
                $('#article input[type="file"][name="author_image-image"]').prop('files')[0].size > 5000000) {
                $('#article input[type="file"][name="author_image-image"]').val('');

                notify.error(LANG_JS_MAXIMUM_FILE_SIZE, '', { fadeOut: 0 });
                return;
            }

            $("#article").vPB({
                url: DEFAULT_URL + "/" + path + "?action=ajax&type=" + action + "&domain_id=" + <?=SELECTED_DOMAIN_ID?>,
                success: function(response)
                {
                    strReturn = response.split("||");

                    if (strReturn[0] == "ok") {
                        $("#authorimage").hide().fadeIn('slow').html(strReturn[1]);
                        if (action == "deleteAuthor") {
                            $("#buttonResetAuthor").addClass("hidden");
                        } else {
                            $("#buttonResetAuthor").removeClass("hidden");
                        }
                    } else {
                        notify.error(strReturn[1], '', { fadeOut: 0 });
                    }

                    btn = $('.action-save');
                    btn.button('reset');
                }
            }).submit();

        }

        function sendCoverImage(form_id, path, acc_id, action) {

            var returnMessage = $("#returnMessage");

            if($("#"+form_id+' input[type="file"][name="cover-image"]').length &&
                $("#"+form_id+' input[type="file"][name="cover-image"]').prop('files')[0] &&
                $("#"+form_id+' input[type="file"][name="cover-image"]').prop('files')[0].size > 5000000) {
                $("#"+form_id+' input[type="file"][name="cover-image"]').val('');

                notify.error(LANG_JS_MAXIMUM_FILE_SIZE, '', { fadeOut: 0 });
                return;
            }

            $("#"+form_id).vPB({
                url: DEFAULT_URL + "/" + path + "?action=ajax&type=" + action + "&domain_id=" + <?=SELECTED_DOMAIN_ID?> + "&account_id=" + acc_id + "&module=" + form_id,
                success: function(response)
                {
                    strReturn = response.split("||");

                    if (strReturn[0] == "ok") {
                        $("#coverimage").hide().fadeIn('slow').html(strReturn[1]);
                        if (action == "deleteCover") {
                            $("#buttonReset").addClass("hidden");
                        } else {
                            $("#buttonReset").removeClass("hidden");
                        }
                    } else {
                        notify.error(strReturn[1], '', { fadeOut: 0 });
                    }

                    btn = $('.action-save');
                    btn.button('reset');
                }
            }).submit();

        }

        function sendLogoImage(form_id, path, acc_id, action) {

            var returnMessage = $("#logoReturnMessage");

            if($("#"+form_id+' input[type="file"][name="logo-image"]').length &&
                $("#"+form_id+' input[type="file"][name="logo-image"]').prop('files')[0] &&
                $("#"+form_id+' input[type="file"][name="logo-image"]').prop('files')[0].size > 5000000) {
                $("#"+form_id+' input[type="file"][name="logo-image"]').val('');

                notify.error(LANG_JS_MAXIMUM_FILE_SIZE, '', { fadeOut: 0 });
                return;
            }

            $("#"+form_id).vPB({
                url: DEFAULT_URL + "/" + path + "?action=ajax&type=" + action + "&domain_id=" + <?=SELECTED_DOMAIN_ID?> + "&account_id=" + acc_id + "&module=" + form_id,
                success: function(response)
                {
                    strReturn = response.split("||");

                    if (strReturn[0] == "ok") {
                        $("#logoimage").hide().fadeIn('slow').html(strReturn[1]);
                        if (action == "deleteLogo") {
                            $("#buttonResetLogo").addClass("hidden");
                        } else {
                            $("#buttonResetLogo").removeClass("hidden");
                        }
                    } else {
                        notify.error(strReturn[1], '', { fadeOut: 0 });
                    }

                    btn = $('.action-save');
                    btn.button('reset');
                }
            }).submit();

        }

        <?php if ($promotionScript) { ?>

        function calculateDiscount() {

            var percentage = false;
            var realvalue = Number($('#real_price_int').val() + "." + $('#real_price_cent').val());
            var dealvalue = Number($('#deal_price_int').val() + "." + $('#deal_price_cent').val());

            if (document.getElementById("type_percentage").checked) {
                percentage = true;
            }

            if (realvalue != 'NaN' && dealvalue != 'NaN' ) {
                if (realvalue < 0) {
                    realvalue = realvalue * (-1);
                }

                if (dealvalue < 0) {
                    dealvalue = dealvalue * (-1);
                }

                if ((dealvalue > realvalue) && (percentage == false)) {
                    notify.error('<?=system_showText(str_replace('NAME_FIELD', LANG_LABEL_DISC_AMOUNT, LANG_MSG_VALID_MINOR))?>', '', { fadeOut: 0 });
                    $('#discountAmount').html('');
                } else {
                    if (percentage) {
                        discount = realvalue - ((dealvalue*realvalue)/100);
                    } else {
                        discount = 100 - (dealvalue/realvalue)*100;
                    }

                    if (!isNaN(discount) && discount >= 0) {
                        if (discount > 100 && !percentage) {
                            discount = 100;
                        }

                        if (percentage) {
                            $('#discountAmount').html('<?=PAYMENT_CURRENCY_SYMBOL?>'+discount.toFixed(2));
                        } else {
                            $('#discountAmount').html(Math.round(discount)+'%');
                        }
                    }
                }
            }
        }

        function showAmountType(type, show) {
            if (type == '%') {
                $("#dealPriceValueLabel").html("<?=ucfirst(LANG_LABEL_PERCENTAGE)?>");
                document.getElementById('amount_monetary').innerHTML = ':';
                document.getElementById('amount_monetary').style.display = 'none';
                document.getElementById('amount_percentage').innerHTML = type;
                document.getElementById('amount_percentage').style.display = '';
                document.getElementById('label_deal_cent').style.display = 'none';
                document.getElementById('deal_price_cent').style.display = 'none';

                $('#discountAmount').html('');

                if (show == "not") {
                    document.getElementById('deal_price_int').value = '';
                    document.getElementById('deal_price_cent').value = '';
                }

                document.getElementById('deal_price_int').setAttribute('maxlength', 2);
            } else {
                $("#dealPriceValueLabel").html("<?=ucfirst(LANG_LABEL_DISC_AMOUNT)?>");

                document.getElementById('amount_monetary').innerHTML = type;
                document.getElementById('amount_monetary').style.display = '';
                document.getElementById('amount_percentage').style.display = 'none';
                document.getElementById('label_deal_cent').style.display = '';
                document.getElementById('deal_price_cent').style.display = '';

                $('#discountAmount').html('');

                if (show == "not") {
                    document.getElementById('deal_price_int').value = '';
                }

                document.getElementById('deal_price_int').setAttribute('maxlength', 5);
            }
        }

        <? } ?>

        <? if ($eventScript) { ?>

            function recurringcheck() {
                if (document.getElementById("recurring").checked==true){
                    document.getElementById("reccuring_events").style.display='';
                    document.getElementById("reccuring_ends").style.display='';
                    document.getElementById("end_date").disabled=true;
                    document.getElementById("labelEndDate").style.display='none';
                    chooseperiod(document.getElementById("period").value);
                    if (document.getElementById("eventEver").checked==true){
                        document.getElementById("until_date").disabled=true;
                    } else {
                        document.getElementById("until_date").disabled=false;
                    }

                } else {
                    document.getElementById("reccuring_events").style.display='none';
                    document.getElementById("reccuring_ends").style.display='none';
                    document.getElementById("end_date").disabled=false;
                    document.getElementById("labelEndDate").style.display='';
                }
            }

            function chooseperiod(value){
                if (value=="daily" || value=="" ){
                    document.getElementById("select_day").style.display='none';
                    document.getElementById("select_week").style.display='none';
                    document.getElementById("day").disabled=true;
                    document.getElementById("week").disabled=true;
                    document.getElementById("dayofweek").disabled=true;
                    for (i=0;i<7;i++){
                        document.getElementById("dayofweek_"+i).disabled=true;
                    }
                    for (i=0;i<5;i++){
                        document.getElementById("numberofweek_"+i).disabled=true;
                    }
                    document.getElementById("month").disabled=true;
                }else if(value=='weekly'){
                    document.getElementById("select_day").style.display='none';
                    document.getElementById("of").style.display='none';
                    document.getElementById("week_of").style.display='none';
                    document.getElementById("of2").style.display='';
                    document.getElementById("of3").style.display='none';
                    document.getElementById("of4").style.display='none';
                    document.getElementById("month_of").style.display='none';
                    document.getElementById("week").style.display='none';
                    document.getElementById("weeklabel").style.display='none';
                    document.getElementById("month").style.display='none';
                    document.getElementById("month2").style.display='none';
                    document.getElementById("select_week").style.display='';

                    document.getElementById("day").disabled=true;
                    document.getElementById("dayofweek").disabled=false;
                    for (i=0;i<7;i++){
                        document.getElementById("dayofweek_"+i).disabled=false;
                    }
                    for (i=0;i<5;i++){
                        document.getElementById("numberofweek_"+i).disabled=false;
                    }
                    document.getElementById("week").disabled=true;
                    document.getElementById("month").disabled=true;
                    document.getElementById("month2").disabled=true;
                    document.getElementById("precision1").style.display='none';
                    document.getElementById("precision2").style.display='none';

                }else if(value=='monthly'){
                    document.getElementById("precision1").style.display='';
                    document.getElementById("precision2").style.display='';
                    document.getElementById("precision2").checked=true;
                    document.getElementById("select_day").style.display='';
                    document.getElementById("of").style.display='';
                    document.getElementById("week_of").style.display='';
                    document.getElementById("of2").style.display='none';
                    document.getElementById("of3").style.display='none';
                    document.getElementById("of4").style.display='';
                    document.getElementById("month_of").style.display='none';
                    document.getElementById("week").style.display='';
                    document.getElementById("weeklabel").style.display='';
                    document.getElementById("month").style.display='none';
                    document.getElementById("month2").style.display='none';
                    document.getElementById("select_week").style.display='';

                    document.getElementById("day").disabled=true;
                    document.getElementById("dayofweek").disabled=false;
                    for (i=0;i<7;i++){
                        document.getElementById("dayofweek_"+i).disabled=false;
                    }
                    for (i=0;i<5;i++){
                        document.getElementById("numberofweek_"+i).disabled=false;
                    }
                    document.getElementById("week").disabled=false;
                    document.getElementById("month").disabled=false;
                    document.getElementById("month2").disabled=true;

                }else if(value=='yearly'){
                    document.getElementById("select_day").style.display='';
                    document.getElementById("of").style.display='';
                    document.getElementById("week_of").style.display='';
                    document.getElementById("of2").style.display='';
                    document.getElementById("of3").style.display='';
                    document.getElementById("of4").style.display='none';
                    document.getElementById("month_of").style.display='';
                    document.getElementById("week").style.display='';
                    document.getElementById("weeklabel").style.display='';
                    document.getElementById("month").style.display='';
                    document.getElementById("month2").style.display='';
                    document.getElementById("select_week").style.display='';
                    document.getElementById("precision1").style.display='';
                    document.getElementById("precision2").style.display='';
                    document.getElementById("precision2").checked=true;
                    document.getElementById("day").disabled=true;
                    document.getElementById("dayofweek").disabled=false;
                    for (i=0;i<7;i++){
                        document.getElementById("dayofweek_"+i).disabled=false;
                    }
                    for (i=0;i<5;i++){
                        document.getElementById("numberofweek_"+i).disabled=false;
                    }
                    document.getElementById("week").disabled=false;
                    document.getElementById("month").disabled=true;
                    document.getElementById("month2").disabled=false;
                }
            }

            function chooseprecision(value){

                if (value=='day'){

                    var start_date = $("#start_date").val();
                    var date_format = '<?=DEFAULT_DATE_FORMAT;?>';
                    var arrStDate = start_date.split("/");
                    if (date_format == 'd/m/Y') {
                        var defDay = arrStDate[0];
                        var defMonth = arrStDate[1];
                    } else if (date_format == 'm/d/Y') {
                        var defDay = arrStDate[1];
                        var defMonth = arrStDate[0];
                    }

                    if ($("#day").val() == "") {
                        $("#day").val(defDay);
                    }

                    if ($("#month option:selected").val() == "") {
                        if (defMonth) {
                            var nMonth = document.getElementById("month");
                            nMonth[(defMonth - 1) + 1].selected = true;
                        }
                    }

                    document.getElementById("day").disabled=false;
                    document.getElementById("dayofweek").disabled=true;
                    for (i=0;i<7;i++){
                        document.getElementById("dayofweek_"+i).disabled=true;
                    }
                    for (i=0;i<5;i++){
                        document.getElementById("numberofweek_"+i).disabled=true;
                    }
                    document.getElementById("week").disabled=true;
                    document.getElementById("month").disabled=false;
                    document.getElementById("month2").disabled=true;
                    document.getElementById("precision1").checked=true;
                    document.getElementById("precision2").checked=false;
                } else if (value=='weekday') {
                    document.getElementById("day").disabled=true;
                    document.getElementById("dayofweek").disabled=false;
                    for (i=0;i<7;i++){
                    document.getElementById("dayofweek_"+i).disabled=false;
                    }
                    for (i=0;i<5;i++){
                    document.getElementById("numberofweek_"+i).disabled=false;
                    }
                    document.getElementById("week").disabled=false;
                    document.getElementById("month").disabled=true;
                    document.getElementById("month2").disabled=false;
                } else {
                    document.getElementById("day").disabled=true;
                    document.getElementById("dayofweek").disabled=false;
                    for (i=0;i<7;i++){
                        document.getElementById("dayofweek_"+i).disabled=false;
                    }
                    for (i=0;i<5;i++){
                        document.getElementById("numberofweek_"+i).disabled=false;
                    }
                    document.getElementById("week").disabled=false;
                    document.getElementById("month").disabled=true;
                    document.getElementById("week2").disabled=false;
                }
            }

            function enableUntil(op){
                if (op==1){
                    document.getElementById("until_date").disabled=true;
                } else if (op==2){
                    document.getElementById("until_date").disabled=false;
                }
            }

            <? if ($recurring == 'Y') { ?>
                recurringcheck();
                chooseperiod('<?=$period?>');
                <? if($period == 'monthly' || $period == 'yearly' ){ ?>
                    chooseprecision('<?=$precision?>');
                <? } ?>
            <? } ?>

        <? } ?>

        <? if ($feedName) { ?>

        function JS_submit() {
            document.<?=$feedName?>.submit();
        }

        <? } ?>

        <? if (!$members) { ?>

        function changeModuleLevel() {

            var auxLevel = $('#level').val();
            <? if (is_object($listing)) { ?>

            var auxTemplate = $('#listingtemplate_id').val();
            var url = "<?=DEFAULT_URL.str_replace(EDIRECTORY_FOLDER, '', $_SERVER['PHP_SELF']);?>?level="+auxLevel+"&listingtemplate_id="+auxTemplate+"<?=($id ? '&id=' .$id : '')?>";
            var currTemplateId = '<?=$listingtemplate_id ? $listingtemplate_id : ''?>';

            <? } else { ?>

            var url = "<?=DEFAULT_URL.str_replace(EDIRECTORY_FOLDER, '', $_SERVER['PHP_SELF']);?>?level="+auxLevel+"<?=($id ? '&id=' .$id : '')?>";

            <? } ?>
            var currLevel = '<?=is_numeric($level) ? $level : ''?>';

            bootbox.confirm('<?=system_showText(LANG_CONFIRM_CHANGE_LEVEL)?>', function(result) {
                if (result) {
                    document.location.href = url;
                } else {
                    <? if (is_object($listing)) { ?>
                    $("#listingtemplate_id").val(currTemplateId);
                    <? } ?>
                    $("#level").val(currLevel);
                }
            });
        }

        <? } ?>

        //Remove attachment file
        function removeAttachment() {
            $("#div_attachment").addClass("hidden");
            $("#remove_attachment").attr("value", "1");
        }

        var auxStepsTutorial = [<?=$tutSteps?>]

    </script>

    <?php include SM_EDIRECTORY_ROOT.'/assets/custom-js/categories.php' ?>

    <? if (string_strpos($_SERVER['PHP_SELF'], '/' .SITEMGR_ALIAS. '/') !== false) { ?>

    <script src="<?=DEFAULT_URL?>/<?=SITEMGR_ALIAS?>/assets/js/module-tutorial.js"></script>

    <? }

    if ($facebookScript) { ?>
        <div id="fb-root"></div>
        <script async defer crossorigin="anonymous" src="https://connect.facebook.net/<?=EDIR_LANGUAGEFACEBOOK?>/sdk.js#xfbml=1&version=v3.3&appId=<?= FACEBOOK_API_ID ?>&autoLogAppEvents=1"></script>
    <? } ?>

    <script type="text/javascript" src="<?=DEFAULT_URL?>/scripts/jquery/jcrop/js/jquery.Jcrop.min.js"></script>

    <script>
        // Banner
        <? if ($bannerScript) { ?>
            var banner_tmp_form_images_content = document.getElementById("banner_with_images").innerHTML;
            var banner_tmp_form_text_content = document.getElementById("banner_with_text").innerHTML;

            function fillCaption (capt) {
                $("#mainCaption").attr("value", capt);
            }

            <?
            if ($type < 50)       echo 'bannerDisableTextForm();';
            else if ($type >= 50) echo 'bannerDisableImagesForm();';
            if ($forceTextForm) echo 'bannerDisableImagesForm();';
            }
        ?>

    </script>

    <?php if (string_strpos($_SERVER['PHP_SELF'], '/' .LISTING_FEATURE_FOLDER. '/') !== false || string_strpos($_SERVER['PHP_SELF'], '/claim/') !== false) { ?>
        <?php // Add JS codes when Listing are created or edited  ?>
        <script src="<?=DEFAULT_URL?>/bundles/fosjsrouting/js/router.js"></script>
        <script src="<?=DEFAULT_URL?>/js/routing?callback=fos.Router.setData"></script>

        <script type="text/template" id="templateHoursWork">
            <div class="form-group row custom-content-row group-hours-work" data-hours-work-id="%item_id%">
                <input type="hidden" name="hours_work[%item_id%][weekday]" value="%weekday_value%">
                <input type="hidden" name="hours_work[%item_id%][hours_start]" value="%hours_start_value%">
                <input type="hidden" name="hours_work[%item_id%][hours_end]" value="%hours_end_value%">

                <div class="col-md-3">
                    <input type="text" value="%weekday%" class="form-control" disabled>
                </div>
                <div class="col-md-3">
                    <input type="text" value="%hours_start%" class="form-control" disabled>
                </div>
                <div class="col-md-3">
                    <input type="text" value="%hours_end%" class="form-control" disabled>
                </div>
                <div class="col-md-3">
                    <a href="javascript:;" class="btn btn-block btn-link remove-hours-work"><?= system_showText(LANG_LABEL_REMOVE) ?></a>
                </div>
            </div>
        </script>

        <script type="text/template" id="templateFeature">
            <div class="group-feature" data-feature-id="%item_id%">
                <input type="hidden" name="features[%item_id%][icon]" class="input-feature-icon" value="%item_icon%">
                <input type="hidden" name="features[%item_id%][title]" class="input-feature-title" value="%item_title_value%">
                <div class="group-feature-icon">
                    <i class="fa %item_icon%" aria-hidden="true"></i>
                </div>
                <a href="javascript:;" class="group-feature-link">%item_title%</a>
            </div>
        </script>

        <script type="text/javascript">
            $('.formatLink').on('change', function () {
                let input = $(this);
                let val = $(this).val();

                if (val && !val.match(/^http([s]?):\/\/.*/)) {
                    val = 'http://' + val;
                }
                input.val(val.trim());
            });

            $(document).on('click', '.btn-add-hours', function (e) {
                e.preventDefault();

                var weekday = $(".weekday option:selected").text();
                var hours_start = $(".hours-start").val();
                var hours_end = $(".hours-end").val();
                var end_nextday = $("#end-nextday").val();

                var weekday_value = $(".weekday option:selected").val();
                var hours_start_value;
                var hours_end_value;

                <?php if(CLOCK_TYPE === '12') { ?>
                    var time_start = hours_start.split(':');
                    var time_end = hours_end.split(':');
                    var new_time_start;
                    var new_time_end;

                    if(time_start[1]) {
                        if (time_start[1].match('pm')) {
                            new_time_start = (time_start[0] !== '12' ? 12 : 0) + parseInt(time_start[0]);
                            if (new_time_start.toString().length === 1) {
                                new_time_start = '0' + new_time_start;
                            }
                            hours_start_value = new_time_start + ':' + time_start[1].replace(' pm', '');
                        } else {
                            new_time_start = (time_start[0] === '12' ? -12 : 0) + parseInt(time_start[0]);
                            if (new_time_start.toString().length === 1) {
                                new_time_start = '0' + new_time_start;
                            }
                            hours_start_value = new_time_start + ':' + time_start[1].replace(' am', '');
                        }
                    }

                    if(time_end[1]) {
                        if (time_end[1].match('pm')) {
                            new_time_end = (time_end[0] !== '12' ? 12 : 0) + parseInt(time_end[0]);
                            if (new_time_end.toString().length === 1) {
                                new_time_end = '0' + new_time_end;
                            }
                            hours_end_value = new_time_end + ':' + time_end[1].replace(' pm', '');
                        } else {
                            new_time_end = (time_end[0] === '12' ? -12 : 0) + parseInt(time_end[0]);
                            if (new_time_end.toString().length === 1) {
                                new_time_end = '0' + new_time_end;
                            }
                            hours_end_value = new_time_end + ':' + time_end[1].replace(' am', '');
                        }
                    }
                <?php } else { ?>
                    hours_start_value = hours_start;
                    hours_end_value = hours_end;
                <?php } ?>

                if(!weekday_value) {
                    notify.error('<?=system_showText(LANG_HOURS_WORK_NEED_WEEK_DAY);?>', '', { fadeOut: 0 });
                    return;
                }

                var nextday_value;
                if(weekday_value === '6') {
                    nextday_value = '0';
                } else {
                    nextday_value = (parseInt(weekday_value) + 1);
                }

                var nextWeekDay = $('.weekday [data-selectable][data-value="' + nextday_value + '"]').text();

                if(hours_start_value && hours_end_value) {
                    if((hours_start_value < hours_end_value || hours_end_value === '00:00') && end_nextday !== 'true') {
                        var item_id = parseInt($('.group-hours-work').length);
                        while ($('.group-hours-work[data-hours-work-id="'+item_id+'"]').length > 0) {
                            item_id++;
                        }

                        var item = $('#templateHoursWork').html();
                        item = item.replace(/\%item_id\%/g, item_id);
                        item = item.replace(/\%weekday\%/g, weekday);
                        item = item.replace(/\%weekday_value\%/g, weekday_value);
                        item = item.replace(/\%hours_start\%/g, hours_start);
                        item = item.replace(/\%hours_start_value\%/g, hours_start_value);
                        item = item.replace(/\%hours_end\%/g, hours_end);
                        item = item.replace(/\%hours_end_value\%/g, hours_end_value);

                        $('.list-hours-work').append(item);

                        if ($('.weekday')[0].selectize){
                            $('.weekday')[0].selectize.clear();
                        }
                        if ($('.hours-start').data("DateTimePicker")){
                            $('.hours-start').data("DateTimePicker").clear();
                        }
                        if ($('.hours-end').data("DateTimePicker")){
                            $('.hours-end').data("DateTimePicker").clear();
                        }
                    } else if (end_nextday === 'true') {
                        var item_id = parseInt($('.group-hours-work').length);
                        while ($('.group-hours-work[data-hours-work-id="'+item_id+'"]').length > 0) {
                            item_id++;
                        }

                        var midnight = '<?=(CLOCK_TYPE === '12' ? '12:00 am' : '00:00')?>';

                        var item = $('#templateHoursWork').html();
                        item = item.replace(/\%item_id\%/g, item_id);
                        item = item.replace(/\%weekday\%/g, weekday);
                        item = item.replace(/\%weekday_value\%/g, weekday_value);
                        item = item.replace(/\%hours_start\%/g, hours_start);
                        item = item.replace(/\%hours_start_value\%/g, hours_start_value);
                        item = item.replace(/\%hours_end\%/g, midnight);
                        item = item.replace(/\%hours_end_value\%/g, '00:00');

                        $('.list-hours-work').append(item);

                        if(hours_end_value !== '00:00') {
                            item_id++;

                            var item = $('#templateHoursWork').html();
                            item = item.replace(/\%item_id\%/g, item_id);
                            item = item.replace(/\%weekday\%/g, nextWeekDay);
                            item = item.replace(/\%weekday_value\%/g, nextday_value);
                            item = item.replace(/\%hours_start\%/g, midnight);
                            item = item.replace(/\%hours_start_value\%/g, '00:00');
                            item = item.replace(/\%hours_end\%/g, hours_end);
                            item = item.replace(/\%hours_end_value\%/g, hours_end_value);

                            $('.list-hours-work').append(item);
                        }

                        if ($('.weekday')[0].selectize){
                            $('.weekday')[0].selectize.clear();
                        }
                        if ($('.hours-start').data("DateTimePicker")){
                            $('.hours-start').data("DateTimePicker").clear();
                        }
                        if ($('.hours-end').data("DateTimePicker")){
                            $('.hours-end').data("DateTimePicker").clear();
                        }
                    } else {
                        notify.error('<?=system_showText(LANG_HOURS_WORK_START_LESS_END);?>', '', { fadeOut: 0 });
                    }
                } else {
                    notify.error('<?=system_showText(LANG_HOURS_WORK_NEED_HOURS);?>', '', { fadeOut: 0 });
                }

                $('#end-nextday').val('');
            });

            $(document).on('click', '.remove-hours-work', function (e) {
                $(this).parent().parent().remove();
            });

            $(document).on('click', '.group-feature-link', function (e, callback) {
                e.preventDefault();

                var id = $(this).parents('.group-feature').attr('data-feature-id');
                var icon = $(this).siblings('.input-feature-icon').val();
                var title = $(this).siblings('.input-feature-title').val();
                $('.feature-content #pageiconimage').html("<?="<img src='" . DEFAULT_URL . '/assets/icons/api/'?>" + icon + "' alt='Icon'>");
                $('.feature-content #feature_icon')[0].selectize.setValue(icon);
                $('.feature-content #feature_title').val(title);
                $('.feature-content').attr('data-feature-edit-id', id);
                $('.btn-save-feature').text(LANG_JS_SAVE);
                $(".btn-delete-feature").show();
            });

            $(document).on('click', '.btn-delete-feature', function (e, callback) {
                e.preventDefault();

                var id = $('.feature-content').attr('data-feature-edit-id');

                $('.list-features').find("[data-feature-id='" + id + "']").remove();
                $('.feature-content #feature_icon')[0].selectize.setValue('');
                $('.feature-content #feature_title').val('');
                $('.feature-content').attr('data-feature-edit-id', '');
                $(".btn-delete-feature").hide();
            });

            $(document).on('click', '.btn-save-feature', function (e) {
                e.preventDefault();

                var item_id = $('.feature-content').attr('data-feature-edit-id');
                var item_icon = $('.feature-content #feature_icon').val();
                var item_title = $('.feature-content #feature_title').val();
                var item_title_value = escapeHtml(item_title);

                if(item_icon !== '' && item_title !== '') {
                    if (item_id === '') {
                        item_id = parseInt($('.group-feature').length);
                        while ($('.group-feature[data-feature-id="'+item_id+'"]').length > 0) {
                            item_id++;
                        }

                        var item = $('#templateFeature').html();
                        item = item.replace(/\%item_id\%/g, item_id);
                        item = item.replace(/\%item_icon\%/g, item_icon);
                        item = item.replace(/\%item_title\%/g, item_title);
                        item = item.replace(/\%item_title_value\%/g, item_title_value);

                        $('.list-features').append(item);
                    } else {
                        var $group_feature = $('.list-features').find("[data-feature-id='" + item_id + "']");

                        $group_feature.find('.input-feature-icon').val(item_icon);
                        $group_feature.find('.group-feature-icon').html('<i class="fa ' + item_icon + '" aria-hidden="true"></i>');
                        $group_feature.find('.input-feature-title').val(item_title_value);
                        $group_feature.find('.group-feature-link').html(item_title);
                    }

                    $('.feature-content #feature_icon')[0].selectize.setValue('');
                    $('.feature-content #feature_title').val('');
                    $('.feature-content').attr('data-feature-edit-id', '');
                    $('.btn-save-feature').text(LANG_JS_ADD);
                    $(".btn-delete-feature").hide();
                } else {
                    notify.error(LANG_FEATURE_NAME_EMPTY, '', { fadeOut: 0 });
                }
            });

            function escapeHtml(text) {
                var map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                };

                return text.replace(/[&<>"']/g, function(m) { return map[m]; });
            }

            $('#listingTemplate, #listingLevel').on('change', function () {
                let listingTemplateValue = $('#listingTemplate').val();
                let listingLevelValue = $('#listingLevel').val();

                if(listingLevelValue && listingTemplateValue) {
                    $('#blockedSection').css('display', 'none');
                } else {
                    $('#blockedSection').css('display', 'flex');
                }
            });

            $('#listingLevel').on('change', function () {
                $('#loading_ajax').show();

                let level = $(this).val();
                let template = $('#listingTemplate').val();
                let listingId = $(this).data('id');

                let data = {
                    level: level,
                    templateId: template,
                    domain_id: <?=SELECTED_DOMAIN_ID?>
                };

                $.get(DEFAULT_URL + '/includes/code/loadLevelFieldsActionAjax.php', data).done(function (result) {
                    let data = JSON.parse(result);
                    data.displayFields.forEach(function(value, key) {
                        let dataBlock = $('[data-block="' + value + '"]');

                        if(dataBlock.hasClass('panel-blocked')) {
                            dataBlock.hide();
                        } else {
                            dataBlock.removeClass('form-control-blocked');
                            dataBlock.removeAttr('disabled');
                            $("label[for='" + dataBlock.attr('id') + "']").tooltip('disable');
                        }
                    });

                    data.blockFields.forEach(function(value, key) {
                        let dataBlock = $('[data-block="' + value + '"]');

                        if(dataBlock.hasClass('panel-blocked')) {
                            dataBlock.show();
                        } else {
                            dataBlock.addClass('form-control-blocked');
                            dataBlock.attr('disabled', 'disabled');
                            let tooltipLabel = $("label[for='" + dataBlock.attr('id') + "']");
                            tooltipLabel.attr('title', tooltipLabel.data('title'));
                            tooltipLabel.attr('data-placement', 'right');
                            tooltipLabel.tooltip('enable');
                        }
                    });

                    $('[data-customtab]').remove();

                    $('#dealTab').hide();

                    $('#classifiedTab').hide();

                    $('#loading_ajax').hide();
                });
            });

            $('#listingTemplate').on('change', function () {
                $('#loading_ajax').show();

                let template = $(this).val();
                let listingId = $(this).data('id');
                let level = $('#listingLevel').val();

                let data = {
                    templateId: template,
                    listingId: listingId,
                    level: level,
                    domain_id: <?=SELECTED_DOMAIN_ID?>
                };

                $.get(DEFAULT_URL + '/includes/code/loadCustomFieldsActionAjax.php', data).done(function (result) {
                    let data = JSON.parse(result);
                    $('#listing-extra-fields').empty().html(data.block);
                    $('#listing-extra-fields').find('.selectize > select').selectize();
                    $('[data-customtab]').remove();

                    $('#loading_ajax').hide();
                });
            });

        </script>
    <?php } ?>

    <script>
        <?php if (is_numeric($message) && isset(${'msg_' .$feedName}[$message])) { ?>
            notify.success('<?=${'msg_' .$feedName}[$message];?>');
        <?php } ?>

        <?php if((is_numeric($message)) && (isset($msg_post[$message])) && (string_strpos($_SERVER["PHP_SELF"], "/".BLOG_FEATURE_FOLDER."/blog.php") !== false)) { ?>
            notify.success('<?=$msg_post[$message];?>');
        <?php } ?>

        <?php if ((is_numeric($message)) && (isset($msg_promotion[$message])) && (string_strpos($_SERVER["PHP_SELF"], "/".PROMOTION_FEATURE_FOLDER."/deal.php") !== false)) { ?>
            notify.success('<?=$msg_promotion[$message];?>');
        <?php } ?>

        <?php if ((is_numeric($message)) && (isset($msg_banner[$message])) && (string_strpos($_SERVER["PHP_SELF"], "/".BANNER_FEATURE_FOLDER."/banner.php") !== false)) { ?>
            notify.success('<?=$msg_banner[$message];?>');
        <?php } ?>

        <?php if (isset(${'message_' .$feedName})) { ?>
            notify.error('<?=${'message_' .$feedName};?>', '<?=system_showText(LANG_MSG_FIELDS_CONTAIN_ERRORS);?>', { fadeOut: 0 });
        <?php } ?>

        <?php if ((isset($message_post)) && (string_strpos($_SERVER["PHP_SELF"], "/".BLOG_FEATURE_FOLDER."/blog.php") !== false)) { ?>
            notify.error('<?=$message_post;?>', '<?=system_showText(LANG_MSG_FIELDS_CONTAIN_ERRORS);?>', { fadeOut: 0 });
        <?php } ?>

        <?php if ((isset($message_promotion)) && (string_strpos($_SERVER["PHP_SELF"], "/".PROMOTION_FEATURE_FOLDER."/deal.php") !== false)) { ?>
            notify.error('<?=$message_promotion;?>', '<?=system_showText(LANG_MSG_FIELDS_CONTAIN_ERRORS);?>', { fadeOut: 0 });
        <?php } ?>

        <?php if ((isset($message_banner)) && (string_strpos($_SERVER["PHP_SELF"], "/".BANNER_FEATURE_FOLDER."/banner.php") !== false)) { ?>
            notify.error('<?=$message_banner;?>', '<?=system_showText(LANG_MSG_FIELDS_CONTAIN_ERRORS);?>', { fadeOut: 0 });
        <?php } ?>

        <?php if ($message_listingclassified) { ?>
            notify.error('<?=$message_listingclassified;?>', '', { fadeOut: 0 });
        <?php } ?>

        <?php if ($message_listingpromotion) { ?>
            notify.error('<?=$message_listingpromotion?>', '', { fadeOut: 0 });
        <?php } ?>
    </script>

    <?php
        /* ModStores Hooks */
        HookFire("modulesfooter_after_render_js", [
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
        ]);
