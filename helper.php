<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Michal Červeňák <miso@fykos.cz>
 */
// must be run within Dokuwiki
if (!defined('DOKU_INC')) {
    die();
}
require 'fks_image.php';

class helper_plugin_fksimageshow extends DokuWiki_Plugin {

    public function FindPosition($match) {
        if (preg_match('/\s+(.+)\s+/', $match)) {
            return 'center';
        } elseif (preg_match('/(.+)\s+/', $match)) {
            return 'left';
        } elseif (preg_match('/\s+(.+)/', $match)) {
            return 'right';
        } else {
            return 'center';
        }
    }

    public function printLabel($label) {
        return $label?'<div class="title"><span class="icon"></span><span class="label">' . htmlspecialchars($label) . '</span></div>':'';
    }

    public function printImage($image, $size) {
        return '<div class="image" style="background-image: url(\'' . ml($image, array('w' => $size)) . '\')"></div>';
    }

    public function parseIlData($m) {
        global $conf;
        $position = '';
        $image = [];
        list($gallery, $href, $label) = preg_split('~(?<!\\\)' . preg_quote('|', '~') . '~', $m);
        if (!file_exists(mediaFN($gallery)) || is_dir(mediaFN($gallery))) {
            search($files, $conf['mediadir'], 'search_media', [], utf8_encodeFN(str_replace(':', '/', trim($gallery))));
            $position = $this->FindPosition($gallery);
            if (count($files)) {
                $image = $files[array_rand($files)];
                unset($files);
            }
        } else {
            $image = ['id' => pathID($gallery)];
        }
        return ['image' => $image, 'href' => $href, 'label' => $label, 'position' => $position];
    }

    public function printIlImageDiv($image_id, $label, $href, $img_size = 240, $param = array()) {
        $r = "";
        $r .= '<div ' . buildAttributes($param) . '>';
        $r .= '<div class="image-container">';

        $r .= '<a href="' . (preg_match('|^http[s]?://|', trim($href)) ? htmlspecialchars($href) : wl(cleanID($href))) . '">';
        $r .= $this->printImage($image_id, $img_size);
        $r .= $this->printLabel($label);
        $r .= '</a>';
        $r .= '</div>';
        $r .= '</div>';
        return $r;
    }
}
