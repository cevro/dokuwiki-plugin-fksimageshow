<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Michal Červeňák <miso@fykos.cz>
 */
// must be run within Dokuwiki
if (!defined('DOKU_INC')) {
    die();
}

class helper_plugin_fksimageshow extends DokuWiki_Plugin {

    function getimages($files, $format) {
        if(!$files) {
            return null;
        }
        $rand = rand(0, count($files) - 1);
        $imegesize = getimagesize($files[$rand]);
        if ($format == 'landscape') {
            if ($imegesize[0] < $imegesize[1]) {
                $rand = $this->getimages($files, $format);
            }
        } elseif ($format == 'portrait') {
            if ($imegesize[0] > $imegesize[1]) {
                $rand = $this->getimages($files, $format);
            }
        }
        return $rand;
    }

    function getRand() {

        $seed = str_split('abcdefghijklmnopqrstuvwxyz'
                . 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'); // and any other characters
        shuffle($seed); // probably optional since array_is randomized; this may be redundant
        $rand = '';
        foreach (array_rand($seed, 5) as $k) {
            $rand .= $seed[$k];
        }
        return $rand;
    }

    function resizeImage($image) {
        list($w, $h) = getimagesize($image);
        $k = $w / 320.0;
        $hnew = floor($h / (double) $k);

        $size = '?w=320' . '&h=' . $hnew . '&tok=' . $this->getRand();
        return $size;
    }

    function getData($match) {
        global $userdata;
        $parsedata = preg_split('/\|/', str_replace(' ', '', substr($match, 15, -2)));
        foreach ($parsedata as $key) {
            $simpledata = preg_split("/=/", $key);
            $userdata[$simpledata[0]] = $simpledata[1];
        }
    }

    function getAllFiles() {
        global $userdata;
        if (!isset($userdata["url"])) {
            $dirs = $this->getConf('dirs');
            $dir = preg_split('/;/', $dirs);
            $files = Array();
            foreach ($dir as $key) {
                $filesnew = glob('data/media/' . $key . '/*.jpg');
                $files = array_merge($files, $filesnew);
            }
        } else {
            $files = glob('data/media/galerie/' . $userdata["url"] . '/*.jpg');
        }
        return $files;
    }

    function getPrezImg() {
        global $userdata;
        global $files;
        for ($i = 0; $i < $userdata['foto']; $i++) {
            $images[$i] = $this->getimages($files, $userdata['format']);
        }
        return $images;
    }

    function getImageScript() {
        global $files;
        global $userdata;
        global $rand;
        global $images;

        $no = 0;
        $script = '<script> files["' . $rand . '"]={"images":' . $userdata['foto'];
        foreach ($images as $key) {
            $hrefs = preg_split('/\//', $files[$key]);
            $script.='
                    ,' . $no . ':{
                    "href":"' . DOKU_BASE . $hrefs[2] . '/' . $hrefs[3] . '/page",
                    "src":"' . DOKU_BASE . '_media' . substr($files[$key], 10) . '"}';
            $no++;
        }
        $script.='}</script>';
        return $script;
    }

}
