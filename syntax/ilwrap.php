<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Michal Červeňák <miso@fykos.cz>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')){
    die();
}
require_once(DOKU_INC.'inc/search.php');
require_once(DOKU_INC.'inc/JpegMeta.php');

class syntax_plugin_fksimageshow_ilwrap extends DokuWiki_Syntax_Plugin {

    private static $size_names;
    private $helper;

    public function __construct() {
        $this->helper = $this->loadHelper('fksimageshow');
        self::$size_names = $this->helper->size_names;
    }

    public function getType() {
        return 'substition';
    }

    public function getPType() {
        return 'block';
    }

    public function getAllowedTypes() {
        return array();
    }

    public function getSort() {
        return 226;
    }

    public function connectTo($mode) {

        $this->Lexer->addSpecialPattern('\{\{il-wrap\>.+?\}\}',$mode,'plugin_fksimageshow_ilwrap');
    }

    /**
     * Handle the match
     */
    public function handle($match,$state) {
        $matches = array();
        preg_match('/{{il-wrap>([\S\s]+)*}}/',$match,$matches);
        preg_match_all('/(.*)\s?/',$matches[1],$ms);
        $datas = array();
        foreach ($ms[1] as $m) {
            if($m == ''){
                continue;
            }
            $datas[] = $this->helper->parseIlData($m);
        }
        return array($state,array($datas));
    }

    public function render($mode,Doku_Renderer &$renderer,$data) {


        global $ID;

        if($mode == 'xhtml'){

            /** @var Do ku_Renderer_xhtml $renderer */
            list($state,$matches) = $data;
            list($datas) = $matches;
            $renderer->doc.='<div class="FKS_image_show wrap">';
            foreach ($datas as $data) {
                $param = array('class'=>'FKS_image_show imagelink il default','data-animate' => 'static');
                

                /* specific scale for webpage */
                $img_size = $data['size']['w'] ? ($data['size']['w'] * 2) : 240;
                if($data['image'] == null){
                    $renderer->nocache();
                    if(auth_quickaclcheck($ID) >= AUTH_EDIT){
                        $renderer->nocache();
                        $renderer->doc.='<div class="info">FKS_imageshow: No images find</div>';
                    }
                    if($data['href']){
                        $rend = new syntax_plugin_fksimageshow_fl();
                        $rend->render($mode,$renderer,array($state,$data['href'],$data['label']));
                    }
                }else{
                    $renderer->doc .=$this->helper->printIlImageDiv($data['image']['id'],$data['label'],$data['href'],$img_size,$param);
                }
            }
            $renderer->doc.='</div>';
        }



        return false;
    }

}
