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

    public $FKS_helper;

    public function __construct() {


        $this->FKS_helper = $this->loadHelper('fkshelper');
    }

   

   

    function resizeImage($image) {
        list($w, $h) = getimagesize($image);
        $k = $w / 320.0;
        $hnew = floor($h / (double) $k);

        $size = '?w=320' . '&h=' . $hnew . '&tok=' . $this->getRand();
        return $size;
    }

    

    

}

class fksimage extends helper_plugin_fksimageshow {

    /**
     *
     * @var string path to style.ini
     */
    private $ini_file;

    /**
     *
     * @var int filetime style.ini
     */
    private $ini_time;

    /**
     *
     * @var array params of style.ini
     */
    private $ini_atr = array();

    /**
     * @var string name od season
     */
    public $season_name;

    /**
     *
     * @var string folder to save images
     */
    private $season_dir;

    /**
     *
     * @var string folder to default files
     */
    public $default_dir;

    /**
     *
     * @var string path of defaul file
     */
    private $default_file;

    /**
     *
     * @var int filetime of new file
     */
    private $file_time;

    /**
     * @var string path of new file
     */
    private $file_patch;

    /**
     *
     * @var string name of file
     */
    private $file_name;

    /**
     *
     * @var string type of file
     */
    private $file_ext;

    public function __construct() {
        global $conf;
        $this->ini_file = DOKU_INC . 'lib/tpl/' . $conf['template'] . '/style.ini';
        $this->ini_time = filemtime($this->ini_file);
        $this->ini_atr = parse_ini_file($this->ini_file);



        $this->season_name = $this->ini_atr['__season__'];
        $this->season_dir = 'lib/tpl/' . $conf['template'] . '/images/season/' . $this->season_name . '/';
        $this->default_dir = 'lib/tpl/' . $conf['template'] . '/images/season/default/';
    }

    public function _create($file, $type) {

        $this->file_ext = $type;
        $this->file_name = $file;
        $this->file_patch = DOKU_INC . $this->season_dir . $file . '.' . $type;
        $this->file_time = @filemtime($this->file_patch);
    }

    public function _colorize() {

        if (!file_exists(DOKU_INC . $this->season_dir)) {
            mkdir(DOKU_INC . $this->season_dir);
        }

        if ((!file_exists($this->file_patch) || ($this->file_time < $this->ini_time))) {
            $this->_fks_colorize_img();
        }
    }

    private function _fks_colorize_img() {

        $this->default_file = DOKU_INC . $this->default_dir . $this->file_name . '.' . $this->file_ext;
        if ($this->file_ext == "png") {
            $im = imagecreatefrompng($this->default_file);
        } elseif ($this->file_ext == 'jpg' || $this->file_ext == 'jpeg') {
            $im = imagecreatefromjpeg($this->default_file);
        } else {
            return;
        }
        list($w, $h) = getimagesize($this->default_file);
        if (preg_match('/radioactive/i', $this->file_name)) {
            $style_rgb = hexdec($this->ini_atr['__vyfuk_back__']);
        } else {
            $style_rgb = hexdec($this->ini_atr['__vyfuk_head__']);
        }
        $style_color = imagecolorsforindex($im, $style_rgb);
        for ($i = 0; $i < $w; $i++) {
            for ($j = 0; $j < $h; $j++) {
                $rgb = imagecolorat($im, $i, $j);
                $colors = imagecolorsforindex($im, $rgb);
                if ($colors['alpha'] != 127) {
                    if ($colors["red"] != 255 || $colors["green"] != 255 || $colors["blue"] != 255) {
                        if ($colors["red"] != $style_color["red"] || $colors["green"] != $style_color["green"] || $colors["blue"] != $style_color["blue"]) {
                            $color = imagecolorallocate($im, $style_color["red"], $style_color["green"], $style_color["blue"]);
                            imagesetpixel($im, $i, $j, $color);
                        }
                    }
                }
            }
        }
        ob_start();
        if ($this->file_ext == "png") {
            imagesavealpha($im, true);
            imagepng($im);
        } elseif ($this->file_ext == 'jpg' || $this->file_ext == 'jpeg') {
            imagejpeg($im);
        }
        $contents = ob_get_contents();
        imagedestroy($im);
        ob_end_clean();
        io_saveFile(DOKU_INC . $this->season_dir . $this->file_name . '.' . $this->file_ext, $contents);

        return true;
    }

    public static function _fks_season_image($file, $type, $dis_scan = false) {
        global $conf;

        $image = new fksimage;

        $image->_create($file, $type);

        $image->_colorize();

        if (!$dis_scan) {
            foreach (scandir(DOKU_INC . $image->default_dir) as $value) {

                $more_file = pathinfo(DOKU_INC . $image->default_dir . $value);

                fksimage::_fks_season_image($more_file['filename'], $more_file['extension'], true);
            }
        }
        return DOKU_BASE . 'lib/tpl/' . $conf['template'] . '/images/season/' . $image->season_name . '/' . $file . '.' . $type;
    }

}
