$(document).ready(function(){

    $('.leadgen-form').submit(function (e) {
        e.preventDefault();

        var $form = $(this);
        var data = $form.serialize();
        var $btn = $form.find('.button[data-loading]');

        $form.find('[type=submit]').attr('disabled', 'disabled');
        $form.find('.alert').addClass('hide');

        var request = $.post(this.action, data);

        request
            .then(function () {
                $form[0].reset();
                $form.find('.alert-success').removeClass('hide');
            })
            .fail(function (res) {
                $form.find('.alert-danger').addClass('hide');
                var json = JSON.parse(res.responseText);
                for (var obj in json.errors){
                    $form.find('#'+obj).removeClass('hide');
                    $('#'+obj+ ' p').text(json.errors[obj]);
                }
            })
            .always(function () {
                var captcha = $form.find('img[title="captcha"]');
                if(captcha.length !== 0) {
                    var captchaId = captcha.first().attr('id');
                    eval('reload_' + captchaId + '()');
                }else if($form.find('.g-recaptcha').length !== 0){
                    if($('.g-recaptcha').length<=2){
                        grecaptcha.reset();
                    }else{
                        $('.g-recaptcha').each(function(captcha) {
                            grecaptcha.reset(captcha)
                        });
                    }
                }
                btnReset($btn);
            });
    });

    $('#leadgenslider-submit').on('click', function(e) {
        var form = $(this).find('.leadgen-form');
        var checkboxDivs = form.find('[data-type=checkbox]');
        checkboxDivs.each(function (checkboxDiv) {
            if ($(checkboxDivs[checkboxDiv]).data('required') === true) {
                var checkBoxes = $(checkboxDivs[checkboxDiv]).find('input[type=checkbox]:checked');
                if (checkBoxes.length === 0) {
                    var lastCheckBox = $(checkboxDivs[checkboxDiv]).find('input[type=checkbox]').last();
                    lastCheckBox.attr('required', 'required');
                    lastCheckBox[0].setCustomValidity('Please make sure you have selected at least one of the boxes');
                } else {
                    var requiredCheckBox = $(checkboxDivs[checkboxDiv]).find('input[type=checkbox][required]');
                    requiredCheckBox.removeAttr('required');
                    requiredCheckBox[0].setCustomValidity('');
                }
            }
        });

        if ($(form).valid) {
            form.submit();
        }
    });

    if($(".hero-lead-form").attr("active-slider") == "true"){
        var heroSlider = $('.hero-lead-form');

        heroSlider.on('ready.flickity', function(){
            if($(window).width() >= 992){
                var leadGenFormHeight = ($(".leadgen-form").height() + 40) / 2;
            } else {
                var leadGenFormHeight = ($(".leadgen-form").height() + 160) / 2;
            }

            $(".hero-leadgen .carousel-cell").css("padding-top", leadGenFormHeight);
            $(".hero-leadgen .carousel-cell").css("padding-bottom", leadGenFormHeight + 40);
        });

        heroSlider.flickity({
            autoPlay: true,
            pauseAutoPlayOnHover: true,
            wrapAround: true,
            imagesLoaded: true,
            arrowShape: {
                x0: 25,
                x1: 55, y1: 30,
                x2: 65, y2: 20,
                x3: 45
            }
        });

        heroSlider.flickity('resize');
    } else {
        if($(window).width() >= 992){
            if($(".hero-default .slider-content").length != 0){
                var leadGenFormHeight = ($(".leadgen-form").height() + 40) / 2;
            } else {
                var leadGenFormHeight = ($(".leadgen-form").height() + 160) / 2;
            }
        } else {
            var leadGenFormHeight = ($(".leadgen-form").height() + 160) / 2;
        }

        $(".hero-leadgen .carousel-cell").css("padding-top", leadGenFormHeight);
        $(".hero-leadgen .carousel-cell").css("padding-bottom", leadGenFormHeight);
    }
});
