$(document).ready(function(){
    $(document).on({
        mouseover: function(){
            $(this).parent().parent('.card-item').addClass('edit-hovered');
        },
        mouseleave: function(){
            $(this).parent().parent('.card-item').removeClass('edit-hovered');
        }
    }, '.card-button-edit');

    $(document).on({
        mouseover: function(){
            $(this).parent('.card-item').addClass('remove-hovered');
        },
        mouseleave: function(){
            $(this).parent('.card-item').removeClass('remove-hovered');
        }
    }, '.card-button-remove');
});
