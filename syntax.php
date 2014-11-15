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
        global $userdata;
        global $files;
        global $rand;
        global $images;
        $this->helper->getData($match);
        $files = $this->helper->getAllFiles();
        $rand = $this->helper->getRand();
        $images = $this->helper->getPrezImg();
        $script = $this->helper->getImageScript();



        $to_page.='<div class="fks_image_show" id="' . $rand . '">'
                . '<div id="fks_images' . $rand . '" class="fks_images" style="display: none;opacity:1;">'
                . '<a id="fks_image_url' . $rand . '" href=" ">'
                . '<img '
                . 'id="fks_image' . $rand . '" '
                . 'src=" " '
                . 'width="95%" '
                . 'style="left: 0%; top: -30%;">'
                . '</a></div>';


        $to_page.='</div>';
        $to_page = $script . "\n" . $to_page;
        //echo $script;
        return array($state, array($to_page));
    }

    public function render($mode, Doku_Renderer &$renderer, $data) {
// $data is what the function handle return'ed.
        if ($mode == 'xhtml') {
            /** @var Do ku_Renderer_xhtml $renderer */
            list($state, $match) = $data;
            list($to_page) = $match;
            $renderer->doc .= $to_page;
        }
        return false;
    }

}
