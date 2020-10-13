$(document).ready(function() {
    if(window.location.pathname.toLowerCase().indexOf('sponsor')>=0){
        $('#myselect').selectize();
    }
    if($('.sample-page-modal').length > 1){
        $('.sample-page-modal:not(:last)').each(function () {
            $(this).remove();
        });
    }

    $('.pricing-nav .button').on("click", function() {
        var period = $(this).data('period');
        var widgetId = $(this).data('widget-id');

        $(this).siblings('button').removeClass('is-active');
        $(this).addClass('is-active');

        if(period === 'monthly') {
            $('#yearly-'+widgetId).removeClass('is-active');
            $('#monthly-'+widgetId).addClass('is-active');
        } else if(period === 'yearly') {
            $('#monthly-'+widgetId).removeClass('is-active');
            $('#yearly-'+widgetId).addClass('is-active');
        } else {
            $('.plans-container .pricing-wrapper.is-active').removeClass('is-active');
            $('#'+period).addClass('is-active');
        }
    });

    // Princing Plans scroll buttons
    $endNext = true;
    $endPrev = true;

    $('.pricing-buttons .next').on('click', function(){
        if($endNext){
            $('.pricing-list').animate({scrollLeft:'+=500'}, 500);
            $('.pricing-buttons .next').hide();
            $('.pricing-buttons .previous').show();
            $endPrev = true;
        }

        $endNext = false;
    });

    $('.pricing-buttons .previous').on('click', function(){
        if($endPrev){
            $('.pricing-list').animate({scrollLeft:'-=500'}, 500);
            $('.pricing-buttons .previous').hide();
            $('.pricing-buttons .next').show();
            $endNext = true;
        }

        $endPrev = false;
    });

    $('.sample-page-modal-button').on('click', function(e){
        e.preventDefault();
        $('.sample-page-modal').find('.advanceListing').data('ref',$(this).data('ref'));
        $('.sample-page-modal').css("display", "flex").hide().fadeIn();
    });

    $('.close-sample-page-modal').on('click', function(e){
        $('.sample-page-modal').fadeOut();
    });

    $('.advanceListing').on('click', function(e){
        window.open(window.href = (Routing.generate('listing_sample', {friendlyUrl: "sample", level: $(this).data('ref'), template: $( "#myselect option:selected" ).val(), _format:"html"})));
        $('.sample-page-modal').fadeOut();
    });
});

function advertiseChoice(frequency){
    Cookies.set('edirectory_advertiseChoice', frequency);
    Cookies.remove('listingTemplateChoice');
}
