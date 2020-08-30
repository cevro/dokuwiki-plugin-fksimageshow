<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Michal Červeňák <miso@fykos.cz>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')){
    die();
}

class syntax_plugin_fksimageshow_fl extends DokuWiki_Syntax_Plugin {

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

        $this->Lexer->addSpecialPattern('{{fl>.+?\|.+?}}',$mode,'plugin_fksimageshow_fl');
    }

    public function handle($match,$state) {
        preg_match('/{{\s*fl\s*>(.*)\|(.*)}}/',$match,$matches);
        list(,$link,$text) = $matches;
        return array($state,$link,$text);
    }

    public function render($mode,Doku_Renderer $renderer,$data) {

        if($mode == 'xhtml'){
            list(,$link,$text) = $data;
            $renderer->doc.='<a href="'.wl(cleanID($link)).'">';
            $renderer->doc.='<span class="button fast_link">';
            $renderer->doc.=htmlspecialchars(trim($text));
            $renderer->doc.='</span>';
            $renderer->doc.='</a>';
            return true  ;
        }
        return false;
    }
}
