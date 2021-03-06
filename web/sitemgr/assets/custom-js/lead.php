<?php
    /*
    * # Admin Panel for eDirectory
    * @copyright Copyright 2020 Arca Solutions, Inc.
    * @author Basecode - Arca Solutions, Inc.
    */

    # ----------------------------------------------------------------------------------------------------
	# * FILE: /ed-admin/assets/custom-js/lead.php
	# ----------------------------------------------------------------------------------------------------
?>
<script>
    var thisForm = "";
    var thisId = "";

    function showLead(idIn, type) {
        thisForm = type;
        thisId = idIn;

        if (type == "view") {
            hideAllLeads();

            $(".errorMessage").css("display", "none");
            $("#"+type+"TR"+idIn).css("display", "");
            $("#"+type+"TR"+idIn).addClass("active");
            $("#leadTR"+idIn).addClass("active");
            $("#reply_lead_"+idIn).css("display", "none");
            $("#forward_lead_"+idIn).css("display", "none");

            $("#linkreply"+idIn).removeClass("active");
            $("#linkforward"+idIn).removeClass("active");

            if (document.getElementById("dropdownDomain")) {
                document.getElementById("dropdownDomain").disabled = true;
            }
        } else {
            $("#reply_lead_"+idIn).css("display", "none");
            $("#forward_lead_"+idIn).css("display", "none");
            $("#"+type+"_lead_"+idIn).css("display", "");

            $("#linkreply"+idIn).removeClass("active");
            $("#linkforward"+idIn).removeClass("active");
            $("#link"+type+idIn).addClass("active");
        }
    }

    function hideLead(idIn, type) {
        if (type == "view") {
            $("#"+type+"TR"+idIn).css("display", "none");
            $("#leadTR"+idIn).removeClass("active");
            $(".errorMessage").css("display", "none");

            if (document.getElementById("dropdownDomain")) {
                document.getElementById("dropdownDomain").disabled = false;
            }
        } else {
            $("#"+type+"_lead_"+idIn).css("display", "none");
            $("#link"+type+idIn).removeClass("active");
        }
    }

    function hideAllLeads() {
        <?php if ($leadsArr) foreach($leadsArr as $each_lead) { ?>
            $("#viewTR"+<?=$each_lead->getNumber('id');?>).css("display", "none");
            $("#viewTR"+<?=$each_lead->getNumber('id');?>).removeClass("active");
            $("#leadTR"+<?=$each_lead->getNumber('id');?>).removeClass("active");
        <?php } ?>
    }


    <?php if (is_numeric($message) && isset($msg_lead[$message])) { ?>
        notify.success('<?=$msg_lead[$message]?>');
    <?php } elseif ($errorMessage) { ?>
        notify.error('<?=$errorMessage;?>', '', { fadeOut: 0 });
    <?php } ?>
</script>
