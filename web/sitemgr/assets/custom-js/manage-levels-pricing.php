<script>
    $('.toggle-panel').on('click', function(){
        let panelRef = $(this).data('ref');
        
        $(this).toggleClass('is-closed');
        $('#'+panelRef).find('.slide-container').slideToggle();
    });

    $('.action-button').on('click', function(){
        let refModule = $(this).attr('href').replace('#', '');

        $('.action-button').removeClass('is-active');
        $(this).addClass('is-active');
        
        localStorage.setItem('active-tab', refModule);
    });
    
    $('.module-toggle').on('click', function(){
        let refModule = $(this).attr('href').replace('#', '');
        
        localStorage.setItem('active-module', refModule);
    });

    $('.toggle-item').on('click', function(){
        let state = $(this).parent(".switch-button").hasClass("is-enable");
        let inputRef = $(this).data('ref');

        if(state){
            $(this).parent(".switch-button").removeClass("is-enable");
            $(this).parent(".switch-button").addClass("is-disable");
            // $('#managerStatusState').val('2');
            $('#'+inputRef).prop("checked", false);
        } else {
            $(this).parent(".switch-button").removeClass("is-disable");
            $(this).parent(".switch-button").addClass("is-enable");
            // $('#managerStatusState').val('1');
            $('#'+inputRef).prop("checked", true);
        }
    });

    $(document).ready(function(){
        if(localStorage.getItem('active-tab') !== null){
            let activeTab = localStorage.getItem('active-tab');

            $('section.tab-pane').removeClass('active');
            $('.action-button').removeClass('is-active');

            $('#'+activeTab).addClass('active');
            $('.action-button[href="#'+activeTab+'"]').addClass('is-active');
        }
        
        if(localStorage.getItem('active-module') !== null){
            let activeModule = localStorage.getItem('active-module');

            $('.module-toggle').parent('li').removeClass('active');
            $('.modules-pane').removeClass('active');

            $('.module-toggle[href="#'+activeModule+'"]').parent('li').addClass('active');
            $('#'+activeModule).addClass('active');
        }
    });
</script>