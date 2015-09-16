<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Michal Červeňák <miso@fykos.cz>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')){
    die();
}

class syntax_plugin_fksimageshow_il extends DokuWiki_Syntax_Plugin {

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
        return array('formatting','substition','disabled');
    }

    public function getSort() {
        return 226;
    }

    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('\{\{[a-zX-]*il\>.+?\}\}',$mode,'plugin_fksimageshow_il');
    }

    /**
     * Handle the match
     */
    public function handle($match,$state) {
        $data = array();
        /**
         * @data[type] static / slide / ?,
         * @data[size] normal/mini;
         * @data[href] link to galery
         * @params[gallery]  path to galery relative from data/media
         * @var $data ['images']
         * @data[path]
         * 
         * @data[foto] 
         * @data[label] text 
         * @data[random] /all/one/??
         */
        $data['size'] = 'normal';
        $data['foto'] = 1;
        $data['position'] = 'center';
        $data['format'] = null;
        $data['type'] = 'static';
        $data['images'] = array();
        $matches = array();
        preg_match('/\{\{(([Xa-z]*)-)?il\>(.+?)\}\}/',$match,$matches);
        list(,,$p1,$p) = $matches;
        list($params['gallery'],$href,$label) = preg_split('~(?<!\\\)'.preg_quote('|','~').'~',$p);
        $data['position'] = helper_plugin_fksimageshow::FindPosition($params['gallery']);
        $data['href'] = $href;
        $data['label'] = $label;
        $data['size'] = $this->helper->FindSize($p1);


        $data['type'] = 'static';
        $gallerys[] = DOKU_INC.'data/media/'.trim($params['gallery']);
        $images = helper_plugin_fksimageshow::GetAllImages($gallerys);
        $data['images'] = helper_plugin_fksimageshow::ChooseImages($images,1,null,$label,$href);
        if($data['images']){
            unset($data['href']);
            unset($data['label']);
        }

        return array($state,array($data));
    }

    public function render($mode,Doku_Renderer &$renderer,$data) {

        $this->helper->render($mode,$renderer,$data);

        return false;
    }

}
