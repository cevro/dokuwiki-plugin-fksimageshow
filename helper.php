<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Michal Červeňák <miso@fykos.cz>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')){
    die();
}
require 'fks_image.php';

class helper_plugin_fksimageshow extends DokuWiki_Plugin {

    public $FKS_helper;
    public $size_names;

    /**
     *
     * @var array $sizes define sizey of pictures
     */
    private static $sizes = array(
        'tera' => array(600,100),
        'giga' => array(480,80),
        'mega' => array(400,66,666666),
        'kilo' => array(360,60),
        'normal' => array(300,50),
        'mili' => array(240,40),
        'mikro' => array(200,33,333333),
        'nano' => array(150,25),
        'piko' => array(120,20)
    );

    public function __construct() {
        $this->FKS_helper = $this->loadHelper('fkshelper');
        $this->size_names = array_keys(self::$sizes);
    }

    /**
     * 
     * @param type $images
     * @param type $format
     * @param type $label
     * @return type
     */
    public static function FindImage($images,$format,$label) {
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
                $img = self::FindImage($images,$format,$label);
            }
        }elseif($format == 'portrait'){
            if($w > $h){
                $img = self::FindImage($images,$format,$label);
            }
        }
        return $img;
    }

    /**
     * 
     * @param type $img
     * @param type $label
     * @return string
     */
    private static function CreateLabel($img,$label = "") {

        if(preg_match('/@headline@/',$label)){
            $JpegMeta = new JpegMeta($img);
            $t = $JpegMeta->getRawInfo();

            $label = str_replace('@headline@',$t['iptc']['Headline'],$label);
        }
        if(preg_match('/@caption@/',$label)){
            $JpegMeta = new JpegMeta($img);
            $t = $JpegMeta->getRawInfo();
            $label = str_replace('@caption@',$t['iptc']['Caption'],$label);
        }

        return $label;
    }

    /**
     * 
     * @param type $gallerys
     * @return type
     */
    public static function GetAllImages($gallerys) {
        $files = Array();

        foreach ($gallerys as $value) {

            $dir = $value;
            $filess = self::AllImage($dir);
            $files = array_merge($files,$filess);
        }

        return $files;
    }

    /**
     * 
     * @param type $dir
     * @return type
     */
    private static function AllImage($dir) {
        $files = helper_plugin_fkshelper::filefromdir($dir,false);

        if($files == null){
            return array();
        }
        $filtred_files = array_filter($files,function($v) {
            return is_array(@getimagesize($v));
        });

        return $filtred_files;
    }

    /**
     * 
     * @param type $images
     * @param type $foto
     * @param type $format
     * @param type $label
     * @return type
     */
    public static function ChooseImages($images,$foto = 1,$format = null,$label = "",$href = null) {
        if($images == null){
            //msg('No images to dislay',-1,'','',MSG_USERS_ONLY);
            return;
        }
        $choose = array();
        for ($i = 0; $i < $foto; $i++) {
            $choose[$i]['src'] = self::FindImage($images,$format,$label);
            $choose[$i]['label'] = self::CreateLabel($choose[$i]['src'],$label);
            if($href != null){
                $choose[$i]['href'] = $href;
            }
        }

        return (array) $choose;
    }

    /**
     * 
     * @global type $ID
     * @param type $mode
     * @param Doku_Renderer $renderer
     * @param type $data
     * @return boolean
     */
    public function render($mode,Doku_Renderer &$renderer,$data) {
        global $ID;
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


            if(!is_array($data['size'])){

                $size = $data['size'];
                $data['size'] = array();
                $data['size']['w'] = $size;
                $data['size']['h'] = $size;
            }


            $param['class'].=' w_'.$data['size']['w'].' h_'.$data['size']['h'];
            list($img_size) = self::$sizes[$data['size']['w']];


            /* specific scale for webpage */
            $img_size*=2;
            /*
             * set floating
             */
            switch ($data['position']) {
                case "left":
                    $param['class'].=' left';

                    break;
                case "right":
                    $param['class'].=' right';

                    break;
                default :
                    $param['class'].=' center';

                    break;
            }


            $renderer->doc .= html_open_tag('div',$param);
            if($data['images'] == null){

                if(auth_quickaclcheck($ID) >= AUTH_EDIT){
                    $renderer->doc.='<div class="info">FKS_imageshow: No images find</div>';
                }
                if($data['href']){
                    $renderer->doc.='<a href="'.$this->GalleryLink($data['href']).'">'.$data['label'].'</a>';
                }
            }else{
                $t = $this;
                $data['images'] = array_map(function($a)use ($mode,$t) {
                    $info = array();
                    if(mb_strlen($a['label']) > 25){
                        $l = mb_strcut($a['label'],0,22).'...';
                    }else{
                        $l = $a['label'];
                    }
                    return array('label' => p_render($mode,p_get_instructions($l),$info),
                        'href' => $t->GalleryLink($a['href'],$a['src']),
                        'src' => $a['src']);
                },$data['images']);


                if($data['type'] == 'slide'){
                    self::PrintScript($renderer,$data['images'],$data,$data['foto'],$data['rand'],$data['href'],$img_size);
                }
                foreach ($data['images']as $value) {


                    $renderer->doc .= html_open_tag('div',array('class' => 'image_show'));
                    $renderer->doc .= html_open_tag('div',array('class' => 'images'));

                    $renderer->doc .= html_open_tag('a',array('href' => $value['href']));
                    self::PrintImage($renderer,$value['src'],$img_size);
                    self::PrintLabel($renderer,$value['label']);
                    $renderer->doc .= html_close_tag('a');
                    $renderer->doc .= html_close_tag('div');
                    $renderer->doc .= html_close_tag('div');
                }
            }
        }
        $renderer->doc .=html_close_tag('div');
        return false;
    }

    /**
     * 
     * @param type $image
     * @param type $img_size
     * @return type
     */
    private static function PrintImage(Doku_Renderer &$renderer,$image,$img_size = 200) {
        $renderer->doc .= html_open_tag('div',array('class' => 'image','style' => 'background-image: url(\''.self::MediaLink($image,$img_size).'\')'));
        $renderer->doc .= html_close_tag('div');
    }

    /**
     * 
     * @param type $label
     * @return string
     */
    private static function PrintLabel(Doku_Renderer &$renderer,$label = "") {
        if($label == null){
            //    return '';
        }
        $renderer->doc .= html_open_tag('div',array('class' => 'title'));
        $renderer->doc .= html_open_tag('h2',array());
        $renderer->doc .= $label;

        $renderer->doc .= html_close_tag('h2');
        $renderer->doc .= html_close_tag('div');
    }

    /**
     * 
     * @param Doku_Renderer $renderer
     * @param array $images
     * @param array $data
     * @param type $foto
     * @param type $rand
     * @param type $href
     * @param type $size
     * @param type $label
     * @return boolean
     */
    private static function PrintScript(Doku_Renderer &$renderer,$images,&$data,$foto = 1,$rand = "",$href = false,$size = 300) {
        $no = 0;
        $j['images'] = $foto;
        foreach ($images as $value) {
            if(array_key_exists('href',$value)){
                $href = $value['href'];
            }
            $j[] = array("label" => $value['label'],
                "href" => $href,
                "src" => self::MediaLink($value['src'],$size));
            $no++;
        }
        $json = new JSON();
        $renderer->doc.= '<script type="text/javascript">files["'.$rand.'"]='.$json->encode($j).';'.html_close_tag('script');
        $first = $data['images'][0];
        unset($data['images']);
        $data['images'][0] = $first;
        return true;
    }

    /**
     * 
     * @param type $link
     * @param type $size
     * @return type
     */
    private static function MediaLink($link,$size = 300) {
        return ml(str_replace(array(DOKU_INC,'data/media'),'',$link),array('w' => $size),true,'&');
    }

    private function GalleryLink($href = null,$link = '') {
        global $conf;

        if(preg_match('|http://|',$href) || preg_match('|https://|',$href)){

            return $href;
        }
        if($href){
            return wl(cleanID($href));
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
        return wl(cleanID($wiki_from_media));
    }

    /**
     * 
     * @param type $match
     * @return string
     */
    public static function FindPosition($match) {
        if(preg_match('/\s+(.+)\s+/',$match)){
            return 'center';
        }elseif(preg_match('/(.+)\s+/',$match)){
            return 'left';
        }elseif(preg_match('/\s+(.+)/',$match)){
            return 'right';
        }else{
            return 'center';
        }
    }

    public function FindSize($match = "") {
        $matches = array();
        if(preg_match('/([a-z]*)X([a-z]*)/',$match,$matches)){

            list(,$w,$h) = $matches;
        }else{
            $w = $h = $match;
        }
        $r = array();
        if(in_array($w,$this->size_names)){
            $r['w'] = $w;
        }else{
            $r['w'] = 'normal';
        }
        if(in_array($h,$this->size_names)){
            $r['h'] = $h;
        }else{
            $r['h'] = 'normal';
        }

        return $r;
    }

}
