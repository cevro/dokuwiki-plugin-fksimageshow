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
    public static $sizes = array(
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
            list($state,$matches) = $data;
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



            if($data['images'] == null){
                $renderer->nocache();
                if(auth_quickaclcheck($ID) >= AUTH_EDIT){
                    $renderer->nocache();
                    $renderer->doc.='<div class="info">FKS_imageshow: No images find</div>';
                }
                if($data['href']){
                    $rend = new syntax_plugin_fksimageshow_fl();
                    $rend->render($mode,$renderer,array($state,$data['href'],$data['label']));
                    //$renderer->doc.='<a href="'.$this->GalleryLink($data['href']).'"><button class="fast_link">'.$data['label'].'</button></a>';
                }
            }else{
                $renderer->doc .= html_open_tag('div',$param);
                $t = $this;
                $data['images'] = array_map(function($a)use ($mode,$t) {
                    $info = array();
                    if(mb_strlen($a['label']) > 25){
                        $l = mb_strcut($a['label'],0,22).'...';
                    }else{
                        $l = $a['label'];
                    }
                    return array('label' => $l,
                        'href' => $t->GalleryLink($a['href'],$a['src']),
                        'src' => $a['src']);
                },$data['images']);


                if($data['type'] == 'slide'){
                    self::PrintScript($renderer,$data['images'],$data,$data['foto'],$data['rand'],$data['href'],$img_size);
                }
                foreach ($data['images']as $value) {


                    //    $renderer->doc .= html_open_tag('div',array('class' => 'image_show'));
                    $renderer->doc .= html_open_tag('div',array('class' => 'images'));

                    $renderer->doc .= html_open_tag('a',array('href' => $value['href']));
                    // self::PrintImage($renderer,$value['src'],$img_size);
                    //self::PrintLabel($renderer,$value['label']);
                    $renderer->doc .= html_close_tag('a');
                    $renderer->doc .= html_close_tag('div');
                    //  $renderer->doc .= html_close_tag('div');
                }
                $renderer->doc .=html_close_tag('div');
            }
        }

        return false;
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

    private function GalleryLink($href = null,$link = '') {
        global $conf;

        if(preg_match('|http://|',$href) || preg_match('|https://|',$href)){

            return $href;
        }
        var_dump($href);
        var_dump(page_exists(cleanID($href)));


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
    public function FindPosition($match) {
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

    public function FindSize($match = null) {
        $matches = array();
        if(preg_match('/([a-z]*)X([a-z]*)/',$match,$matches)){

            list(,$w,$h) = $matches;
        }elseif($match){
            $w = $h = $match;
        }else{
            return array('default');
        }
        $r = array();
        if(in_array($w,$this->size_names)){
            $r['w'] = 'w_'.$w;
        }else{
            $r['w'] = 'w_normal';
        }
        if(in_array($h,$this->size_names)){
            $r['h'] = 'h_'.$h;
        }else{
            $r['h'] = 'h_normal';
        }

        return $r;
    }

    public function printLabel($label) {
        return '<div class="title"><span class="icon"></span><span class="label">'.htmlspecialchars($label).'</span></div>';
    }

    public function printImage($image,$size) {
        return '<div class="image" style="background-image: url(\''.ml($image,array('w' => $size)).'\')"></div>';
    }

    public function parseIlData($m) {
        global $conf;

        list($gallery,$href,$label) = preg_split('~(?<!\\\)'.preg_quote('|','~').'~',$m);

        search($files,$conf['mediadir'],'search_media',array(),utf8_encodeFN(str_replace(':','/',trim($gallery))));
        $position = $this->FindPosition($gallery);
        $image = $files[array_rand($files)];
        unset($files);
        return array('image' => $image,'href' => $href,'label' => $label,'position' => $position);
    }

    public function printIlImageDiv($image_id,$label,$href,$img_size = 240,$param = array()) {
        $r = "";
        $r .= '<div '.buildAttributes($param).'>';
        $r .= '<div class="images">';
        $r .= '<a href="'.wl(cleanID($href)).'">';
        $r .= $this->printImage($image_id,$img_size);
        $r .= $this->printLabel($label);
        $r .= '</a>';
        $r .= '</div>';
        $r .='</div>';
        return $r;
    }

}
