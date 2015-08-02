<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Michal Červeňák <miso@fykos.cz>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')){
    die();
}

class syntax_plugin_fksimageshow_al extends DokuWiki_Syntax_Plugin {

    private static $size_names;
    private $helper;

    public function __construct() {
        $this->helper = $this->loadHelper('fksimageshow');
        /* muss be load after inicialize helper fuck static; */
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
        return 200;
    }

    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('\{\{[a-z0-9-]*al\>.+?\}\}',$mode,'plugin_fksimageshow_al');
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
        $data['foto'] = 3;
        $data['position'] = 'center';
        $data['format'] = null;
        $data['type'] = 'slide';
        $data['images'] = array();
        $matches = array();
        preg_match('/\{\{(([a-z0-9]*)-)?(([a-z0-9]*)-)?al\>(.+?)\}\}/',$match,$matches);
        list(,,$p2,,$p1,$p) = $matches;
        list($g,$data['href'],$label) = preg_split('~(?<!\\\)'.preg_quote('|','~').'~',$p);
        $params['gallery'] = explode(';',$g);
        $data['position'] = helper_plugin_fksimageshow::FindPosition($g);
        if(preg_match('/[0-9]+/',$p1)){
            $data['foto'] = $p1;
            $data['size'] = $this->helper->FindSize($p2);
        }else{
            $data['foto'] = $p2;
            $data['size'] = $this->helper->FindSize($p1);
        }

        /**
         * for slide muss generate rand
         */
        $data['rand'] = helper_plugin_fkshelper::_generate_rand(5);



        /**
         * is set galleryy
         */
        foreach ($params['gallery'] as $g) {
            $gallerys[] = DOKU_INC.'data/media/'.trim($g);
        }

        $images = helper_plugin_fksimageshow::GetAllImages($gallerys);
        $data['images'] = helper_plugin_fksimageshow::ChooseImages($images,$data['foto'],null,$label,$data['href']);
        return array($state,array($data));
    }

    public function render($mode,Doku_Renderer &$renderer,$data) {
        $this->helper->render($mode,$renderer,$data);

        return false;
    }

}
