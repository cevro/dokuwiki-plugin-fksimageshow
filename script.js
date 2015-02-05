var files = new Array();



function imageshow(rand) {
    var images = files[rand]["images"];
    var show = 0;
    shownext(show, rand, images);
}
;
function shownext(show, rand, images) {
    if (show === (images - 1)) {
        var show = 0;
    } else {
        show++;
    }
    ;
    document.getElementById("fks_image_url" + rand).href = files[rand][show]["href"];
    document.getElementById("fks_image" + rand).src = files[rand][show]["src"];
    var el = document.getElementById("fks_images" + rand);
    //el.innerHTML=rand;
    el.style.opacity = 0;
    el.style.display = "block";
    //var step=0.00;
    document.getElementById("fks_image" + rand).onload = function() {
        upopacity(el, show, rand, images, 0);
    };
    document.getElementById("fks_image" + rand).onerror = function() {
        document.getElementById("fks_image" + rand).src = "http://img.ffffound.com/static-data/assets/6/77443320c6509d6b500e288695ee953502ecbd6d_m.gif";
        window.setTimeout(function() {
            shownext(show, rand, images);
        }, 5000);
    };

}
;
function upopacity(ele, show, rand, images, step) {
    ele.style.opacity = step / 20.0;
    window.setTimeout(function() {
        if (step > 19) {
            ele.style.opacity = 1;
            stayshow(ele, show, rand, images);
        } else {
            step++;
            upopacity(ele, show, rand, images, step);
        }
        ;
    }, 100);
}
;
function stayshow(elem, show, rand, images) {
    window.setTimeout(function() {
        downopacity(elem, show, rand, images);
    }, 5000);
}
;
function downopacity(elm, show, rand, images) {
    window.setTimeout(function() {
        elm.style.opacity = elm.style.opacity - 0.05;
        if (elm.style.opacity < 0.06) {
            elm.style.opacity = 0;
            elm.style.display = "none";
            shownext(show, rand, images);
        } else {
            downopacity(elm, show, rand, images);
        }
        ;
    }, 100);

}
;
jQuery(function() {
    jQuery('div.fks_image_show[data-animate=slide]').each(function() {

        imageshow(this.id);
    });

});