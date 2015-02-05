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
        return 'normal';
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
            $gallerys = $this->getAllGallery($params);

            /**
             * select randomly gallery
             */
            $rand = rand(0, count($gallerys) - 1);
            $params['url'] = $gallerys[$rand];
        } else {

            $params['rand'] = $this->helper->FKS_helper->_generate_rand(5);
        }

        /*
         * and find all files
         */
        $params['files'] = $this->getAllFiles($params);
        $images['rand'] = $this->choose_images($params);

        foreach ($images['rand'] as $key => $value) {
            $images['file'][$key] = $params['files'][$value];
        }

        $images['script'] = $this->getImageScript($images, $params);     //echo $script;
        return array($state, array($images, $params));
    }

    public function render($mode, Doku_Renderer &$renderer, $data) {
// $data is what the function handle return'ed.
        if ($mode == 'xhtml') {
            /** @var Do ku_Renderer_xhtml $renderer */
            list($state, $match) = $data;
            list($images, $params) = $match;
            if (array_key_exists('static', $params)) {
                
                $renderer->doc .='<div class="fks_image_show" data-animate="static" >';
                foreach ($images['file']as $value) {
                    $renderer->doc .='<div class="FKS_image">'
                            . '<a href="'.$this->get_gallery_link($value).'">'
                            . '<img src="' . $this->get_media_link($value) . '">'
                            . '</a></div>';
                }
                $renderer->doc .='</div>';
            } else {
                $to_page.= $images['script'];
                $to_page.='<div class="fks_image_show" data-animate="slide" id="' . $params['rand'] . '">'
                        . '<div id="fks_images' . $params['rand'] . '" class="fks_images" style="display: none;opacity:1;">'
                        . '<a id="fks_image_url' . $params['rand'] . '" href=" ">'
                        . '<img '
                        . 'id="fks_image' . $params['rand'] . '" '
                        . 'src=" " '
                        . 'width="95%" '
                        . 'style="left: 0%; top: -30%;">'
                        . '</a></div>';


                $to_page.='</div>';
            }

            $renderer->doc .= $to_page;
        }
        return false;
    }

    private function getAllFiles($param = array()) {

        if (!isset($param["url"])) {
            $dirs = $this->getConf('dirs');
            $dir = preg_split('/;/', $dirs);
            $files = Array();
            foreach ($dir as $key) {
                $filesnew = glob('data/media/' . $key . '/*.jpg');
                $files = array_merge($files, $filesnew);
            }
        } else {
            $files = glob('data/media/' . $param["url"] . '/*.jpg');
        }
        return $files;
    }

    private function choose_images($params = array()) {

        for ($i = 0; $i < $params['foto']; $i++) {
            $images[$i] = $this->get_image($params);
        }
        return $images;
    }

    private function get_image($params) {

        if (!$params['files']) {
            return null;
        }
        $rand = rand(0, count($params['files']) - 1);
        $imegesize = getimagesize($params['files'][$rand]);
        if ($params['format'] == 'landscape') {
            if ($imegesize[0] < $imegesize[1]) {
                $rand = $this->get_image($params);
            }
        } elseif ($params['format'] == 'portrait') {
            if ($imegesize[0] > $imegesize[1]) {
                $rand = $this->get_image($params);
            }
        }
        return $rand;
    }

    public function getImageScript($images, $params) {
        if (array_key_exists('static', $params)) {
            return;
        }

        $no = 0;
        $script = '<script> files["' . $params['rand'] . '"]={"images":' . $params['foto'];
        foreach ($images['file'] as $value) {
            

            $script.='
                    ,' . $no . ':{
                    "href":"' . $this->get_gallery_link($value).'",
                    "src":"' . $this->get_media_link($value) . '"}';
            $no++;
        }
        $script.='}</script>';
        return $script;
    }

    /**
     * 
     * @return array
     */
    private function getAllGallery() {
        $dirs = $this->getConf('dirs');
        return (array) preg_split('/;/', $dirs);
    }

    private function get_media_link($link) {
        return DOKU_BASE . '_media/' . substr($link, 10);
    }

    private function get_gallery_link($link) {
        $path = pathinfo($link);
        
        return str_replace('/data/media', '', DOKU_BASE . $path['dirname'] . '/page');
    }

}
