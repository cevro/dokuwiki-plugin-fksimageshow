<?php

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Michal Červeňák <miso@fykos.cz>
 */
// must be run within Dokuwiki
if (!defined('DOKU_INC')) {
    die();
}
require_once(DOKU_INC . 'inc/search.php');
require_once(DOKU_INC . 'inc/JpegMeta.php');

class syntax_plugin_fksimageshow_ilwrap extends DokuWiki_Syntax_Plugin {

    public function getType(): string {
        return 'substition';
    }

    public function getPType(): string {
        return 'block';
    }

    public function getAllowedTypes(): array {
        return [];
    }

    public function getSort(): int {
        return 226;
    }

    public function connectTo($mode): void {
        $this->Lexer->addSpecialPattern('\{\{il-wrap\>.+?\}\}', $mode, 'plugin_fksimageshow_ilwrap');
    }

    public function handle($match, $state, $pos, Doku_Handler $handler): array {
        $matches = [];
        preg_match('/{{il-wrap>([\S\s]+)*}}/', $match, $matches);
        $ms = [];
        preg_match_all('/(.*)\s?/', $matches[1], $ms);
        $datas = [];
        foreach ($ms[1] as $m) {
            if ($m == '') {
                continue;
            }
            $datas[] = helper_plugin_fksimageshow::parseIlData($m);
        }
        return [$state, [$datas]];
    }

    public function render($mode, Doku_Renderer $renderer, $data): bool {
        if ($mode == 'xhtml') {
            [, $matches] = $data;
            [$datas] = $matches;
            $renderer->doc .= '<div class="imageShowWrap">';
            foreach ($datas as $data) {
                $param = ['class' => 'imageShow imagelink'];
                $img_size = 360;
                $renderer->doc .= helper_plugin_fksimageshow::printIlImageDiv($data['image']['id'], $data['label'], $data['href'], $img_size, $param);
            }
            $renderer->doc .= '</div>';
        }
        return false;
    }
}
