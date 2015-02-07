var files = new Array();


jQuery(function() {
    var $ = jQuery;
    $(window).load(function() {
        $('div.FKS_image_show[data-animate=slide]').each(function() {
            _start_slide(this);
        });
    });

    function _start_slide($div) {
        var rand = $($div).data('rand');
        var $bg_div = $($div).children().children().children('div');
        _slide_next($bg_div, rand, 0);
        ;
    }
    ;
    function _slide_next($bg_div, rand, next) {
        if (next == files[rand]['images']) {
            next = 0;
        }
        $bg_div.css({"background-image": "url('" + files[rand][next]['src'] + "')"});
        $bg_div.animate({opacity: 1}, 1000, function() {
            next++;
            window.setTimeout(function() {
                $bg_div.animate({opacity: 0}, 1000, function() {
                    _slide_next($bg_div, rand, next);
                });
            }, 3000);

        });
    }
    ;
});