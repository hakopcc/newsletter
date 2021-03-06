var activeNextStep = true;
function nextStep(item_type, feed, item_title, gotoPackage, finalStep) {

    if (!activeNextStep) {
        return;
    }
    activeNextStep = false;

    var paymentRadio = $('input[name=payment_method]');
    var checkedValue = paymentRadio.filter(':checked').val();

    if (finalStep) {
        $("#screenPackage").hide();
        $("#screen2").fadeIn();
        activeNextStep = true;
    } else {

        disableButtons();

        //validate first step

        var payment_selected = 0;

        if (checkedValue || $("#free_item").val() == "1") {
            payment_selected = 1;
        }

        $.post(DEFAULT_URL + "/validateAdvertise.php", {
            item_type:              item_type,
            signup:                 1,
            title:                  $("#"+item_title).val(),
            friendly_url:           $("#friendly_url").val(),
            return_categories:      $('input[name="return_categories"]').val(),
            discount_id:            $("#promocode").val(),
            start_date:             $("#start_date").val(),
            end_date:               $("#end_date").val(),
            type:                   $("#type").val(),
            caption:                $("#"+item_title).val(),
            has_payment:            payment_selected,
            listingtemplate_id:     $( "[name='select_listingtemplate_id']" ).val()
        }, function (response) {
            if ($.trim(response) == "ok") {
                $("#errorMessage").addClass('hidden');
                $("#screen1").hide();
                $("#screen2").hide();
                if (gotoPackage) {
                    $("#screenPackage").fadeIn();
                } else {
                    $("#screen2").fadeIn();
                }
            } else {
                $("#errorMessage").html(response);
                $("#errorMessage").removeClass('hidden');
                $('html, body').animate({
                    scrollTop: $('#errorMessage').offset().top
                }, 500);
            }
            activeNextStep = true;
            enableButtons();
        });

    }

}

function backStep(is_package, go_package) {
    if (is_package) {
        $("#screenPackage").hide();
    }
    $("#screen1").hide();
    $("#screen2").hide();

    if (go_package) {
         $("#screenPackage").fadeIn();
    } else {
        $("#screen1").fadeIn();
    }
}

function submitForm() {
    if (typeof updateFormAction == "function") {
        updateFormAction();
    }
    $("#form_username").attr("value", $("#dir_username").val());
    $("#form_password").attr("value", $("#dir_password").val());
    document.formDirectory.submit();
}

function disableButtons() {
    var i;
    for (i = 1; i <= 2; i++) {
        if (document.getElementById('button' + i)) {
            document.getElementById('button' + i).innerHTML = LANG_JS_WAIT;
        }
    }
}

function enableButtons() {
    var i;
    for (i = 1; i <= 2; i++) {
        if (document.getElementById('button' + i)) {
            document.getElementById('button' + i).innerHTML = LANG_JS_CONTINUE;
        }
    }
}

function acceptPackage(pvalue) {
    $("#using_package").attr("value", pvalue);
    updateFormAction();
}

$('.renewal_radio').change(function(){
    orderCalculate();
});
