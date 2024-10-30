jQuery(function($){
    
    $('.drawer').drawer();
    
    var ww = $(window).width();
    $('.modal-open').on('click',function(){
        var target = $(this).next();
        
        $("body").append('<div class="modal__overlay"></div>');
        $(".modal__overlay").fadeIn("slow");
        //$(".modal__content").fadeIn("slow");
        modalResize(target);
        $('html').css('overflow','hidden');
        target.fadeIn("slow");
        $(".modal__overlay,.modal-close").click(function(){
            target.fadeOut("fast");
            $(".modal__overlay").fadeOut("slow",function(){
                $('.modal__overlay').remove() ;
            });
            $('html').css('overflow','auto');
        });
        $(window).resize(modalResize(target));
    });
    
    
    function modalResize(element){
        //var element = $('.modal__content');
        var w = $(window).width();
        var h = $(window).height();
        var cw = element.outerWidth();
        var ch = element.outerHeight();
        //取得した値をcssに追加する
        element.css({
            "left": ((w - cw)/2) + "px",
            "top": ((h - ch)/2) + "px"
        });
     }
});

jQuery(function($){
    $('a[href^="#"]').on('click',function(){
        var adjust = -100;
        if($(window).width() < 735) {
            adjust = -63;
        }
        var speed = 400;
        var href= $(this).attr("href");
        var target = $(href == "#" || href == "" ? 'html' : href);
        var position = target.offset().top + adjust;
        $('body,html').animate({
            scrollTop:position
        }, speed, 'swing', function(){
            $('.drawer').drawer('close');
        });
        return false;
    });
});