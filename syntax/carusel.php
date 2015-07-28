<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Michal Červeňák <miso@fykos.cz>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')){
    die();
}

class syntax_plugin_fksimageshow_carusel extends DokuWiki_Syntax_Plugin {

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
        $this->Lexer->addSpecialPattern('\{\{[a-z-]*carusel\>.+?\}\}',$mode,'plugin_fksimageshow_carusel');
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

        $data['position'] = 'center';
        $data['format'] = null;
        $data['type'] = 'slide';

        $data['images'] = array();
        $params = array();

        $matches = array();
        preg_match('/\{\{(([a-z]*)-)?carusel\>/',$match,$matches);
        list(,,$p1) = $matches;
        if(in_array($p1,self::$size_names)){
            $data['size'] = $p1;
        }

        // ;


        $es = explode("\n",$match);
        foreach ($es as $e) {
            if(preg_match('/.*?\|.*?\|.*?/',$e)){
                list($g,$href,$l) = preg_split('~(?<!\\\)'.preg_quote('|','~').'~',$e);
                $gallerys[] = DOKU_INC.'data/media/'.trim($g);

                $images = helper_plugin_fksimageshow::GetAllImages($gallerys);
                $params['images'][] = helper_plugin_fksimageshow::ChooseImages($images,1,null,$l,$href);
            }
        }
        
        foreach ($params['images']as $image){
            
            $data['images'][]=$image[0];
        }
        $data['rand'] = helper_plugin_fkshelper::_generate_rand(5);
        $data['foto'] = count($data['images']);

       


        return array($state,array($data));
    }

    public function render($mode,Doku_Renderer &$renderer,$data) {

        $this->helper->render($mode,$renderer,$data);

        return false;
    }

}
