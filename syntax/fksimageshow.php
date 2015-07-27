<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Michal Červeňák <miso@fykos.cz>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')){
    die();
}

class syntax_plugin_fksimageshow_fksimageshow extends DokuWiki_Syntax_Plugin {

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
        return array('formatting','substition','disabled');
    }

    public function getSort() {
        return 226;
    }

    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('\{\{fksimageshow\>.+?\}\}',$mode,'plugin_fksimageshow_fksimageshow');
    }

    /**
     * Handle the match
     */
    public function handle($match,$state) {
        $data = array();
        /**
         * @params[type] static / slide / ?,
         * @params[size] normal/mini;
         * @params[href] link to galery
         * @params[gallery]  path to galery relative from data/media
         * @params[path]
         * 
         * @params[foto] 
         * @params[label] text 
         * @params[random] /all/one/??
         */
        $data['size'] = 'normal';
        $data['foto'] = 1;
        $data['position'] = 'center';
        $data['format'] = null;
        $data['type'] = 'static';
        $data['images'] = array();

        $params = helper_plugin_fkshelper::extractParamtext(substr($match,15,-2));

        if($params['landscape']){
            $data['format'] = 'landscape';
        }elseif($params['portrait']){
            $data['format'] = 'portrait';
        }elseif(isset($params['format'])){
            $data['format'] = $params['format'];
        }
        if($params['href']){
            $data['href'] = $params['href'];
        }else{
            $data['href'] = false;
        }
        /**
         * switch by type
         */
        if($params['static']){
            $data['type'] = 'static';
        }elseif($params['slide']){
            $data['type'] = 'slide';
        }else{
            $data['type'] = 'slide';
        }


        foreach (array('tera','giga','mega','kilo','normal','mili','mikro','nano','piko') as $size) {
            if($params[$size]){
                $data['size'] = $size;
            }
        }
        foreach (array('left','right','auto','center') as $position) {
            if($params[$position]){
                $data['position'] = $position;
            }
        }

        /**
         * for slide muss generate rand
         */
        if($data['type'] == 'slide'){
            $data['rand'] = helper_plugin_fkshelper::_generate_rand(5);
        }

        if(isset($params['gallery'])){
            /**
             * is set galleryy
             */
            $gallerys[] = DOKU_INC.'data/media/'.$params['gallery'];
        }else{
            /**
             * is not set
             * find all gallery
             */
            $all_gallerys = $this->AllGallery();
            if($params['random'] == 'all'){
                /**
                 * all
                 */
                $gallerys = $all_gallerys;
            }else{
                /**
                 * one
                 */
                $rand = array_rand($all_gallerys);
                $gallerys[] = $all_gallerys[$rand];
            }
        }

        if(isset($params['foto'])){
            $data['foto'] = $params['foto'];
        }else{
            $data['foto'] = 1;
        }


        $images = helper_plugin_fksimageshow::GetAllImages($gallerys);

        $data['images'] = helper_plugin_fksimageshow::ChooseImages($images,$data['foto'],$data['format'],$params['label']);

       
        return array($state,array($data));
    }

    public function render($mode,Doku_Renderer &$renderer,$data) {
        
        $this->helper->render($mode,$renderer,$data);
        return;
    }

  

  
    

    

    /**
     * 
     * @return array
     */
    private function AllGallery() {
        $dirs = $this->getConf('dirs');
        $_dirs = array_map(function($value) {
            return DOKU_INC.'data/media/'.trim($value);
        },explode(';',$dirs));
        $sub_dirs = array();
        $all_dirs = array_filter($_dirs,function($value)use(&$sub_dirs) {
            if(preg_match('/(.*)\*\z/',$value)){
                $_sub_dirs = array_filter(glob($value),"is_dir");
                $sub_dirs = array_merge($sub_dirs,$_sub_dirs);
                return FALSE;
            }
            return true;
        });
        return (array) array_merge($all_dirs,$sub_dirs);
    }



}
