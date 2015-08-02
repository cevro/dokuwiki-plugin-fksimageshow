var files = new Array();
jQuery(function () {
    var TIME_TRANSLATION = 2000;
    var TIME_BETWEEN = 6000;
    var $ = jQuery;
    $(window).ready(function () {
        $('div.FKS_image_show[data-animate=slide]').each(function () {
            console.log(this);
            _start_slide($(this));
        });
    });
    function _start_slide($div) {
        var rand = $div.data('rand');
        var $bg_img = $('.image', $div);
        window.setTimeout(function () {
            _slide_next($bg_img, rand, 0);
        }, Math.random() * TIME_BETWEEN);
    }
    function _slide_next($bg_img, rand, next) {
        try {
            if (next === files[rand]['images']) {
                next = 0;
            }
            $bg_img.css({"background-image": "url('" + files[rand][next]['src'] + "')"});
            $bg_img.parent().attr("href", files[rand][next]['href']);
            $('<img/>').attr('src', files[rand][next]['src']).load(function () {
                $bg_img.parents().find('.title').find('h2').html(files[rand][next]['label']);
                $(this).remove();
                $bg_img.parents().find('.title').find('h2').animate({opacity: 1}, TIME_TRANSLATION, function () {
                });
                $bg_img.animate({opacity: 1}, TIME_TRANSLATION, function () {
                    next++;
                    window.setTimeout(function () {
                        $bg_img.parents().find('.title').find('h2').animate({opacity: 0}, TIME_TRANSLATION, function () {
                        });
                        $bg_img.animate({opacity: 0}, TIME_TRANSLATION, function () {
                            _slide_next($bg_img, rand, next);
                        });
                    }, TIME_BETWEEN);

                });
            });
        } catch (err) {
        }
    }
});