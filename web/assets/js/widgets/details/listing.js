$(".tab-navbar.is-selected").each(function(){
    $ref = $(this).attr("href").split("#")[1];
    $(".tab-content#" + $ref).addClass("is-active");

    if($ref == "deals-classifieds"){
        $(".detail-body").addClass("no-padding");
    }
});

$(".tab-navbar").on("click", function(e){
    e.preventDefault();

    $(".tab-navbar").removeClass("is-selected");
    $(".tab-content").removeClass("is-active");
    $(".detail-body").removeClass("no-padding");
    $ref = $(this).attr("href").split("#")[1];
    $(this).addClass("is-selected");
    $(".tab-content#" + $ref).addClass("is-active");

    if($ref == "deals-classifieds"){
        $(".detail-body").addClass("no-padding");
    }
});

$(".all-reviews").on("click", function(e) {
    e.preventDefault();
    $(".tab-navbar").removeClass("is-selected");
    $(".tab-content").removeClass("is-active");
    $(".detail-body").removeClass("no-padding");
    $ref = $(this).attr("href").split("#")[1];
    $(".tab-navbar[href$='#reviews']").addClass("is-selected");
    $(".tab-content#" + $ref).addClass("is-active");
    $(window).scrollTop($('.details-header-navbar').offset().top);
});

$("#fb-comments").on("click", function() {
    $("html, body").animate({scrollTop: $('.article-categories').offset().top}, 500);
});

let scrollAmount = 0;
$('.sidebar-cards-nav').on('click', function(){
    let element = $(this).data('ref');
    let delay = 400;
    let cardMargin = 8;
    let cardWidth = $('.card-sidebar').width() + cardMargin;
    let direction = $(this).attr('nav-direction');

    if(direction == 'right'){
        scrollAmount += cardWidth;
        $(`#${element} .cards-list`).animate({scrollLeft: scrollAmount}, delay);
    } else {
        scrollAmount -= cardWidth;
        $(`#${element} .cards-list`).animate({scrollLeft: scrollAmount}, delay);
    }
});

$(document).on("click", "reviews-pagination > a.item-pagination", function(e){
    e.preventDefault();
    $.ajax({
        url: $(this).attr('href'),
        success: function(response) {
            $('#review-content').html(response.reviewBlock);
        }
    });
    return false; // for good measure
});

$(document).ready(function () {
    $('#modalLogin').on('hidden.bs.modal', function () {
        $(this).removeData('bs.modal');
    });

    $(".fancybox").fancybox();

    $(".first-hours").on("click", function(){
        $(".hours-more").slideToggle(400);
        $(this).find(".fa").toggleClass("is-open");
    });
});

$( ".form-control" ).change(function() {
     location.href = (Routing.generate('listing_sample', {friendlyUrl: "sample", level: $('.form-control').attr('id'), template: $( ".form-control option:selected" ).val(), _format:"html"}))
});
function detailChoice(type,level,idFirst){
     if(type == 'listing'){
         if( $( ".form-control option:selected" ).val() != undefined){
             Cookies.set('listingTemplateChoice', $( ".form-control option:selected" ).val());
         }else{
             Cookies.set('listingTemplateChoice', idFirst);
         }
     }
    location.href = '/advertise/'+type+'/'+level;
}
