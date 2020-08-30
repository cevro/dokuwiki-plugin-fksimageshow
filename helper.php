<?php

use dokuwiki\Extension\Plugin;

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Michal Červeňák <miso@fykos.cz>
 */
class helper_plugin_fksimageshow extends Plugin {

    private static function findPosition(string $match): string {
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

    private static function printLabel(?string $label): string {
        return $label ? '<div class="title"><span class="icon"></span><span class="label">' . htmlspecialchars($label) . '</span></div>' : '';
    }

    private static function printImage(string $image, int $size): string {
        return '<div class="image" style="background-image: url(\'' . ml($image, ['w' => $size]) . '\')"></div>';
    }

    public static function parseIlData($match): array {
        global $conf;
        $position = '';
        $image = [];
        [$gallery, $href, $label] = preg_split('~(?<!\\\)' . preg_quote('|', '~') . '~', $match);
        if (!file_exists(mediaFN($gallery)) || is_dir(mediaFN($gallery))) {
            search($files, $conf['mediadir'], 'search_media', [], utf8_encodeFN(str_replace(':', '/', trim($gallery))));
            $position = self::findPosition($gallery);
            if (count($files)) {
                $image = $files[array_rand($files)];
                unset($files);
            }
        } else {
            $image = ['id' => pathID($gallery)];
        }
        return ['image' => $image, 'href' => $href, 'label' => $label, 'position' => $position];
    }

    public static function printIlImageDiv(string $imageId, ?string $label, string $href, int $imgSize = 240, array $param = []): string {
        $r = '<div ' . buildAttributes($param) . '>';
        $r .= '<div class="image-container">';
        $r .= '<a href="' . (preg_match('|^http[s]?://|', trim($href)) ? htmlspecialchars($href) : wl(cleanID($href))) . '">';
        $r .= self::printImage($imageId, $imgSize);
        $r .= self::printLabel($label);
        $r .= '</a>';
        $r .= '</div>';
        $r .= '</div>';
        return $r;
    }
}
