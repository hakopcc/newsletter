var cardsHeight = function () {
    $('.card-centralized').each(function(){
        let titleHeight = $(this).find('.title').height();
        let titleMargin = parseInt($(this).find('.title').css('margin-bottom'));

        let contentHeight = $(this).find('.content').outerHeight();
        let contentPadding = parseInt($(this).find('.content').css('padding-top'));

        let bottomValue = (contentHeight - titleHeight - titleMargin - contentPadding) * -1;

        $(this).find('.content').css('bottom', bottomValue);
    });
};

$(document).ready(function() {
    cardsHeight();
});

$(window).on("resize", function() {
    cardsHeight();
});
