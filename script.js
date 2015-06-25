var files = new Array();


jQuery(function () {
    var $ = jQuery;
    $(window).ready(function () {
        $('div.FKS_image_show[data-animate=slide]').each(function () {
            console.log(this);
            _start_slide($(this));
        });
    });



    function _start_slide($div) {
        var rand = $div.data('rand');
        var $bg_img = $div.children().children().children('.image');
        _slide_next($bg_img, rand, 0);

    }
    function _slide_next($bg_img, rand, next) {
        try {
            if (next === files[rand]['images']) {
                next = 0;
            }
            ;
            $bg_img.css({"background-image": "url('" + files[rand][next]['src'] + "')"});
            $bg_img.parent().attr("href", files[rand][next]['href']);
            //$bg_img.one("load", function() {
            $('<img/>').attr('src', files[rand][next]['src']).load(function () {
                $(this).remove(); // prevent memory leaks as @benweet suggested
                $bg_img.animate({opacity: 1}, 1000, function () {
                    next++;
                    //var Dtime= 4000+(Math.random()-0.5)*3000;
                    window.setTimeout(function () {
                        $bg_img.animate({opacity: 0}, 1000, function () {
                            _slide_next($bg_img, rand, next);

                        });
                    }, 3000);

                });
            });

        } catch (err) {

        }


        //});
    }
});