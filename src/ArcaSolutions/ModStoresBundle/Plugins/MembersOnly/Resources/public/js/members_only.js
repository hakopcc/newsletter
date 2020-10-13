$(window).on('load', function () {
    $(".members-only-modal").addClass("is-loaded");
    $("body").addClass("modal-open");
    $("a").attr("href", "");
    var membersOnlyModalContent = $(".members-only-modal:visible>.members-content");
    if(membersOnlyModalContent.length) {
        membersOnlyModalContent.each(function() {
            var curModalContent = $(this);
            var bodyImgsToRemoveWidthHeight = curModalContent.find(".members-body>p>img").filter(function () {
                var widthAttrValue = $(this).attr('width');
                var widthAttrIntValue = parseInt(widthAttrValue);
                return (isNaN(widthAttrIntValue) || widthAttrIntValue > (curModalContent.width()-32));//Subtract 32 to consider the members-body padding
            });
            if(bodyImgsToRemoveWidthHeight.length){
                bodyImgsToRemoveWidthHeight.removeAttr('width').removeAttr('height');
            }
        });
    }
});
$(document).ready(function () {
    $(document).bind('contextmenu', function(e) {
        e.preventDefault();
    });
    $("#becomeMember").on('click', function () {
        if (typeof BECOME_MEMBER_CLICK_LOCATION_HREF !== 'undefined' && (typeof BECOME_MEMBER_CLICK_LOCATION_HREF === 'string' || BECOME_MEMBER_CLICK_LOCATION_HREF instanceof String) && BECOME_MEMBER_CLICK_LOCATION_HREF!=='') {
            window.location.href = BECOME_MEMBER_CLICK_LOCATION_HREF;
        }
    });
    $("#loginMember").on('click', function () {
        if (typeof LOGIN_MEMBER_CLICK_LOCATION_HREF !== 'undefined' &&(typeof LOGIN_MEMBER_CLICK_LOCATION_HREF === 'string' || LOGIN_MEMBER_CLICK_LOCATION_HREF instanceof String) && LOGIN_MEMBER_CLICK_LOCATION_HREF!=='') {
            window.location.href = LOGIN_MEMBER_CLICK_LOCATION_HREF;
        }
    });
});
