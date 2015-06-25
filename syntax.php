<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Michal Červeňák <miso@fykos.cz>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')){
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
        return array('formatting','substition','disabled');
    }

    public function getSort() {
        return 226;
    }

    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('\{\{fksimageshow>.+?\}\}',$mode,'plugin_fksimageshow');
    }

    /**
     * Handle the match
     */
    public function handle($match,$state) {


        $params = helper_plugin_fkshelper::extractParamtext(substr($match,15,-2));
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
        $data = array();

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

        if($params['mini']){
            $data['size'] = 'mini';
        }else{
            $data['size'] = 'normal';
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
            $gallerys[] = $params['gallery'];
        }else{
            /**
             * is not set
             * find all gallery
             */
            $all_gallerys = $this->get_all_gallery();
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


        $images = $this->get_all_images($gallerys);



        $data['images'] = self::choose_images($images,$data['foto'],$data['format']);


        if($params['label'] != ""){
            $data['label'] = $params['label'];
        }




        return array($state,array($data));
    }

    public function render($mode,Doku_Renderer &$renderer,$data) {

        if($mode == 'xhtml'){
            /** @var Do ku_Renderer_xhtml $renderer */
            list(,$matches) = $data;
            list($data) = $matches;
          

            /**
             * @TODO dorobiť pridavanie style a dalšíc atr;
             */
            $param = array('class' => 'FKS_image_show');


            /**
             * iné pre statické a iné pre slide
             */
            switch ($data['type']) {
                case "static":
                    $param = array_merge($param,array('data-animate' => 'static'));
                    break;
                case "slide":
                    $param = array_merge($param,array('data-animate' => 'slide','data-rand' => $data['rand']));
                    break;
            }
            /**
             * for mini scale is smaller
             */
            switch ($data['size']) {
                case "mini":
                    $param['class'].=' FKS_image_show_mini';
                    $img_size = 300;
                    break;
                default :
                    $img_size = 600;
                    break;
            }
            $renderer->doc .= html_open_tag('div',$param);
            if($data['images'] == null){
                $renderer->doc.='<div class="info">FKS_imageshow: No images find</div>';
            }else{
                if($data['type'] == 'slide'){
                    $renderer->doc.= $this->get_script($data['images'],$data,$data['foto'],$data['rand'],$data['href'],$img_size);
                }
                foreach ($data['images']as $value) {
                    $renderer->doc .= html_open_tag('div',array('class' => 'images'));
                    $renderer->doc .= html_open_tag('a',array('href' => ($data['href']) ? wl($data['href']) : $this->get_gallery_link($value)));
                    $renderer->doc .= self::make_image($value,$img_size);
                    $renderer->doc .= self::make_label($data['label']);
                    $renderer->doc .= html_close_tag('a');
                    $renderer->doc .= html_close_tag('div');
                }
            }
        }
        $renderer->doc .=html_close_tag('div');


        return false;
    }

    private static function make_image($image,$img_size) {
        $r .= html_open_tag('div',array('class' => 'image','style' => 'background-image: url(\''.self::get_media_link($image,$img_size).'\')'));
        $r .= html_close_tag('div');
        return $r;
    }

    private static function make_label($label) {
        if($label == null){
            return '';
        }
        if(preg_match('/@exif/',$label)){
            /**
             * TODO!!!
             */
        }
        $r.= html_open_tag('div',array('class' => 'title'));
        $r .= html_open_tag('h2',array());
        $r.= $label;
        $r.= html_close_tag('h2');
        $r.= html_close_tag('div');
        return $r;
    }

    private static function get_all_images($gallerys) {
        $files = Array();
        foreach ($gallerys as $value) {

            $dir = $value;
            $filess = self::all_Image($dir);
            $files = array_merge($files,$filess);
        }

        return $files;
    }

    private static function choose_images($images,$foto = 1,$format = null) {
        if($images == null){
            msg('No images to dislay',-1);
            return;
        }
        for ($i = 0; $i < $foto; $i++) {

            $choose[$i] = self::get_image($images,$format);
        }

        return $choose;
    }

    private static function get_image($images,$format) {
        /*
         * when is images empty 
         */
        if(empty($images)){
            return null;
        }
        /*
         * random key of array
         */
        $rand = array_rand($images);
        $img = $images[$rand];
        list($w,$h) = getimagesize($img);
        /*
         * is format ok ?
         */
        if($format == 'landscape'){
            if($w < $h){
                $img = self::get_image($images,$format);
            }
        }elseif($format == 'portrait'){
            if($w > $h){
                $img = self::get_image($images,$format);
            }
        }


        return $img;
    }

    private function get_script($images,&$data,$foto = 1,$rand = "",$href = false,$size = 300) {

        $no = 0;
        $script = '<script type="text/javascript"> files["'.$rand.'"]={"images":'.$foto;
        foreach ($images as $value) {
            $script.='
                    ,'.$no.':{
                    "href":"'.(($href) ? wl($href) : $this->get_gallery_link($value)).'",
                    "src":"'.self::get_media_link($value,$size).'"}';
            $no++;
        }
        $script.='}'.html_close_tag('script');
        unset($data['images']);
        $data['images'][0] = "";

        return $script;
    }

    /**
     * 
     * @return array
     */
    private function get_all_gallery() {
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

    private static function get_media_link($link,$size = 300) {
        return ml(str_replace(array(DOKU_INC,'data/media'),'',$link),array('w' => $size),true,'&');
    }

    private function get_gallery_link($link) {
        global $conf;
        if(!$this->getConf('allow_url')){
            return '#';
        }

        $path = pathinfo($link);
        $matches = array();
        preg_match('|'.$conf['mediadir'].'[/](.*)|',$path['dirname'],$matches);
        list(,$path_from_media) = $matches;
        unset($matches);

        $wiki_from_media = str_replace('/',':',$path_from_media);

        if($this->getConf('pref_delete')){
            preg_match('|[:]?'.$this->getConf('pref_delete').'[:](.*)|',$wiki_from_media,$matches);
            list(,$wiki_from_media) = $matches;
            unset($matches);
        }
        if($this->getConf('sulf_delete')){
            preg_match('|\A(.*)[:]'.$this->getConf('sulf_delete').'[:]*\z|',$wiki_from_media,$matches);
            list(,$wiki_from_media) = $matches;

            unset($matches);
        }
        if($this->getConf('pref_add')){
            $wiki_from_media = $this->getConf('pref_add').':'.$wiki_from_media;
        }
        if($this->getConf('sulf_add')){
            $wiki_from_media = $wiki_from_media.':'.$this->getConf('sulf_add');
        }
        return wl($wiki_from_media);
    }

    private static function all_Image($dir) {
        $files = helper_plugin_fkshelper::filefromdir($dir,false);

        if($files == null){
            return array();
        }
        $filtred_files = array_filter($files,function($v) {
            return is_array(@getimagesize($v));
        });

        return $filtred_files;
    }

}
