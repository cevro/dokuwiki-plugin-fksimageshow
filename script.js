
/* global files */
var files = Array();
jQuery(function () {
    var TIME_TRANSLATION = 2000;
    var TIME_BETWEEN = 6000;
    var $ = jQuery;


    $('div.FKS_image_show[data-animate=slide]').each(function () {
        StartSlide($(this));
    });



    /*
     $(window).ready(function () {
     $('div.FKS_image_show[data-animate=slide]').each(function () {
     
     //$(this).one("load", function () {
     console.log($(this));
     
     StartSlide($(this));
     //});
     });
     });*/
    function StartSlide($div) {
        var rand = $div.data('rand');
        var $bg_img = $('.image', $div);
        var $a = $bg_img.parent();
        var $title = $bg_img.parent().find('.title').find('span');

        window.setTimeout(function () {
            SlideNext($bg_img, $a, $title, rand, 0);
        }, Math.random() * TIME_BETWEEN);
    }
    function SlideNext($bg_img, $a, $title, rand, current) {
        try {
            if (current === Number(files[rand]['images'])) {
                current = 0;
            }
            var next = current + 1;
            if (next === Number(files[rand]['images'])) {
                next = 0;
            }
            
            $bg_img.css({"background-image": "url('" + files[rand][current]['src'] + "')"});
            $a.attr("href", files[rand][current]['href']);
            $('<img/>').attr('src', files[rand][current]['src']).load(function () {
                $title.html(files[rand][current]['label']);
                $(this).remove();
                $title.animate({opacity: 1}, TIME_TRANSLATION, function () {
                });
                $bg_img.animate({opacity: 1}, TIME_TRANSLATION, function () {

                    window.setTimeout(function () {
                        if (files[rand][current]['label'] !== files[rand][next]['label']) {
                            $title.animate({opacity: 0}, TIME_TRANSLATION, function () {
                            });
                        }
                        $bg_img.animate({opacity: 0}, TIME_TRANSLATION, function () {
                            SlideNext($bg_img, $a, $title, rand, next);
                        });
                    }, TIME_BETWEEN);

                });
            });
        } catch (err) {
        }
    }
});