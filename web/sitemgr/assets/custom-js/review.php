<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2020 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/assets/custom-js/review.php
	# ----------------------------------------------------------------------------------------------------
?>
<script>
    function setDisplayRatingLevel(level, star_id) {
            $('img:lt('+level+')', '#'+star_id).attr('src', '<?=DEFAULT_URL?>/assets/images/structure/review-star.png');
            $('img:gt('+(level-1)+')', '#'+star_id).attr('src', '<?=DEFAULT_URL?>/assets/images/structure/review-star-o.png');
    }

    function resetRatingLevel(level, star_id) {
        setDisplayRatingLevel(level, star_id);
    }

    function setRatingLevel(level, rating_id, star_id) {
        $('#'+rating_id).val(level);
        $('img:lt('+level+')', '#'+star_id).attr('src', '<?=DEFAULT_URL?>/assets/images/structure/review-star.png');
        $('img:gt('+(level-1)+')', '#'+star_id).attr('src', '<?=DEFAULT_URL?>/assets/images/structure/review-star-o.png');

        $('#'+star_id).one('click', function() {
            $("#"+star_id).addClass("clicked");
        });

    }

    <?php if ($reviewsArr) foreach($reviewsArr as $each_rate) { ?>
        setDisplayRatingLevel(<?=db_formatNumber($each_rate->getString("rating"))?>, 'star_<?=$each_rate->getNumber('id');?>');

        $('#star_<?=$each_rate->getNumber('id');?>').bind("mouseleave",function() { 
            if(!$(this).hasClass('clicked')) {
                setDisplayRatingLevel(<?=db_formatNumber($each_rate->getString("rating"))?>, 'star_<?=$each_rate->getNumber('id');?>');
            }

            if($(this).hasClass('clicked')) {
                $(this).removeClass("clicked");
            }
        });
    <?php } ?>
    
    var thisForm = "";
    var thisId = "";

    $('img[alt=star]').bind('click', function(){
        $(this).fadeOut(50);
        $(this).fadeIn(50);
    });

    function showReviewField(idIn) {
        thisForm = "review";
        thisId = idIn;
        hideAllReviews();
        hideAllReplies();
        hideAllStatus();

        $("form").each(function() {
            this.reset();
        });
        $('#ReviewTR'+idIn).css('display', '');
    }

    function showReplyField(idIn) {
        thisForm = "reply";
        thisId = idIn;

        hideAllReplies();
        hideAllReviews();
        hideAllStatus();

        $("form").each(function() {
            this.reset();
        });
        $('#replyReviewTR'+idIn).css('display', '');
    }

    function showStatusField(idIn) {     
        thisForm = "status";
        thisId = idIn;

        hideAllComments();
        hideAllReviews();
        hideAllReplies();
        hideAllStatus();

        bootbox.confirm('<?=system_showText(LANG_SITEMGR_APPROVE_REVIEW_CONFIRM)?>', function(result) {
            if (result) {
                $('#formStatus_'+idIn).submit();
            }
        });
    }
    
    function hideReplyField(idIn) {
        $('#replyReviewTR'+idIn).css('display', 'none');
    }

    function hideReviewField(idIn) {
        <?php if ($reviewsArr) foreach($reviewsArr as $each_rate) { ?>
            $('#star_<?=$each_rate->getNumber('id');?>').removeClass("clicked");
            setDisplayRatingLevel(<?=db_formatNumber($each_rate->getString("rating"))?>, 'star_<?=$each_rate->getNumber('id');?>');
        <?php } ?>
        $('#ReviewTR'+idIn).css('display', 'none');
    }

    function hideReplyField(idIn) {
        $('#replyReviewTR'+idIn).css('display', 'none');
    }

    function hideStatusField(idIn) {
        $('#statusTR'+idIn).css('display', 'none');
    }
    
    function hideAllComments() {
        <?php if ($reviewsArr) foreach($reviewsArr as $each_rate) { ?>
            $('#CommentTR'+<?=$each_rate->getNumber('id');?>).css('display','none');
        <?php } ?>
    }

    function hideAllReviews() {
        <?php if ($reviewsArr) foreach($reviewsArr as $each_rate) { ?>
            $('#ReviewTR'+<?=$each_rate->getNumber('id');?>).css('display','none');
        <?php } ?>
    }

    function hideAllReplies() {
        <?php if ($reviewsArr) foreach($reviewsArr as $each_rate) { ?>
            $('#replyReviewTR'+<?=$each_rate->getNumber('id');?>).css('display','none');
        <?php } ?>
    }

    function hideAllStatus() {
        <?php if ($reviewsArr) foreach($reviewsArr as $each_rate) { ?>
            $('#statusTR'+<?=$each_rate->getNumber('id');?>).css('display','none');
        <?php } ?>
    }

    //Success and Error Messages
    <?php if (is_numeric($message) && isset(${'msg_' .$manageModule}[$message])) { ?>
        notify.success('<?=${'msg_' .$manageModule}[$message]?>');
    <?php } ?>

    <?php if (is_numeric($error_message)) { ?>
        notify.error('<?=$msg_bulkupdate[$error_message];?>', '', { fadeOut: 0, notifyUpdate: true });
    <?php } elseif ($error_msg) { ?>
        notify.error('<?=$error_msg;?>', '', { fadeOut: 0, notifyUpdate: true });
    <?php } elseif ($msg == 'success') { ?>
        notify.success('<?=$msgSucessUpdate?>');
    <?php } elseif ($msg == 'successdel') { ?>
        notify.success('<?=$msgSuccessDelete?>');
    <?php } ?>
    
    <?php unset($msg); ?>
</script>