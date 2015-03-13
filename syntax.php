<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Michal Červeňák <miso@fykos.cz>
 */
// must be run within Dokuwiki
if (!defined('DOKU_INC')) {
    die();
}

class syntax_plugin_fksimageshow extends DokuWiki_Syntax_Plugin {

    public function __construct() {
        $this->helper = $this->loadHelper('fksimageshow');
    }

    public function getType() {
        return 'substition';
    }

    public function getPType() {
        return 'block';
    }

    public function getAllowedTypes() {
        return array('formatting', 'substition', 'disabled');
    }

    public function getSort() {
        return 226;
    }

    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('\{\{fksimageshow>.+?\}\}', $mode, 'plugin_fksimageshow');
    }

    /**
     * Handle the match
     */
    public function handle($match, $state, $pos, Doku_Handler &$handler) {


        $params = helper_plugin_fkshelper::extractParamtext(substr($match, 15, -2));

        if (array_key_exists('static', $params)) {
            /**
             * find all allow gallery
             */
            if (!isset($params["url"])) {
                $gallerys = $this->getAllGallery($params);
                $rand = rand(0, count($gallerys) - 1);
                $params['url'] = $gallerys[$rand];
            }
            /**
             * select randomly gallery
             */
        } else {

            $params['rand'] = $this->helper->FKS_helper->_generate_rand(5);
        }

        /**
         * and find all files
         */
        $params['files'] = $this->getAllFiles($params);
        $images['rand'] = self::choose_images($params);

        foreach ($images['rand'] as $key => $value) {
            $images['file'][$key] = $params['files'][$value];
        }
        $images['script'] = $this->getImageScript($images, $params);     //echo $script;
        return array($state, array(array($images, $params)));
    }

    public function render($mode, Doku_Renderer &$renderer, $data) {

        if ($mode == 'xhtml') {
            /** @var Do ku_Renderer_xhtml $renderer */
            list($state, $matches) = $data;
            list($match) = $matches;
            list($images, $params) = $match;

            /**
             * @TODO dorobiť pridavanie style a dalšíc atr;
             */
            $param = array('class' => 'FKS_image_show');
            if (array_key_exists('static', $params)) {
                $param = array_merge($param, array('data-animate' => 'static'));
            } else {
                $param = array_merge($param, array('data-animate' => 'slide', 'data-rand' => $params['rand']));
                $renderer->doc.=$images['script'];
            }
            if (array_key_exists('mini', $params)) {
                $param['class'].=' FKS_image_show_mini';
            }
            $renderer->doc .= html_open_tag('div', $param);
            /**
             * iné pre statické a iné pre slide
             */
            if (array_key_exists('static', $params)) {

                foreach ($images['file']as $value) {
                    $renderer->doc .= html_open_tag('div', array('class' => 'FKS_images'));
                    $renderer->doc .= html_open_tag('a', array('href' => $this->get_gallery_link($value)));
                    if (!empty($params['label'])) {
                        $renderer->doc .= html_open_tag('div', array('class' => 'FKS_image', 'style' => 'background-image: url(\'' . self::get_media_link($value) . '\')'));
                        $renderer->doc .= html_make_tag('div', array('class' => 'FKS_image_title'));
                        $renderer->doc .= html_make_tag('h2', array());
                        $renderer->doc .= $params['label'];
                        $renderer->doc .= html_close_tag('h2');
                        $renderer->doc .= html_close_tag('div');
                        $renderer->doc .= html_close_tag('div');
                    } else {
                        $renderer->doc .= html_make_tag('img', array('class' => 'FKS_image', 'alt' => 'foto', 'src' => self::get_media_link($value)));
                    }
                    $renderer->doc .= html_close_tag('a');
                    $renderer->doc .= html_close_tag('div');
                }
            } else {
                $renderer->doc .= html_open_tag('div', array('class' => 'FKS_images'));
                $renderer->doc .= html_open_tag('a', array());
                $renderer->doc .= html_make_tag('img', array('class' => 'FKS_image', 'style' => 'opacity:0', 'src'=>' ','alt' => 'foto'));
                $renderer->doc .= html_close_tag('a');
                $renderer->doc .= html_close_tag('div');
            }
        }
        $renderer->doc .=html_close_tag('div');

        return false;
    }

    private function getAllFiles($param = array()) {

        if (!isset($param["url"])) {
            $dir = $this->getAllGallery();

            $files = Array();
            foreach ($dir as $key) {
                $dir = DOKU_INC . 'data/media/' . $key;
                $filess = self::allImage($dir);
                $files = array_merge($files, $filess);
            }
        } else {
            $dir = DOKU_INC . 'data/media/' . $param['url'];
            $files = self::allImage($dir);
        }
        return $files;
    }

    private static function choose_images($params = array()) {

        for ($i = 0; $i < $params['foto']; $i++) {
            $images[$i] = self::get_image($params);
        }
        return $images;
    }

    private static function get_image($params) {

        if (!$params['files']) {
            return null;
        }
        $rand = rand(0, count($params['files']) - 1);
        list($w, $h) = getimagesize($params['files'][$rand]);
        if ($params['format'] == 'landscape') {
            if ($w < $h) {
                $rand = self::get_image($params);
            }
        } elseif ($params['format'] == 'portrait') {
            if ($w > $h) {
                $rand = self::get_image($params);
            }
        }
        return $rand;
    }

    private function getImageScript($images, $params) {
        if (array_key_exists('static', $params)) {
            return;
        }
        $no = 0;
        $script = '<script type="text/javascript"> files["' . $params['rand'] . '"]={"images":' . $params['foto'];
        foreach ($images['file'] as $value) {
            $script.='
                    ,' . $no . ':{
                    "href":"' . $this->get_gallery_link($value) . '",
                    "src":"' . self::get_media_link($value) . '"}';
            $no++;
        }
        $script.='}'.html_close_tag('script');
        return $script;
    }

    /**
     * 
     * @return array
     */
    private function getAllGallery() {
        $dirs = $this->getConf('dirs');

        return (array) array_map(function($value) {
                    return str_replace(array("\n", " "), '', $value);
                }, explode(';', $dirs));
    }

    private static function get_media_link($link) {
        return ml(str_replace(array(DOKU_INC, 'data/media'), '', $link));
    }

    private function get_gallery_link($link) {
        if (!$this->getConf('allow_url')) {
            return ' ';
        }
        $path = pathinfo($link);
       
        return  wl(str_replace(array(DOKU_INC, 'data/media'), '', $path['dirname'] . $this->getConf('gallery_page')));
    }

    private static function allImage($dir) {
        $files = helper_plugin_fkshelper::filefromdir($dir, false);
        $filtred_files = array_filter($files, function($v) {
            return is_array(@getimagesize($v));
        });
        return $filtred_files;
    }

}
