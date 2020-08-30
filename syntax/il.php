<?php

require_once(DOKU_INC . 'inc/search.php');
require_once(DOKU_INC . 'inc/JpegMeta.php');

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Michal Červeňák <miso@fykos.cz>
 */
class syntax_plugin_fksimageshow_il extends DokuWiki_Syntax_Plugin {

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
        $this->Lexer->addSpecialPattern('\{\{il\>.+?\}\}', $mode, 'plugin_fksimageshow_il');
    }

    public function handle($match, $state, $pos, Doku_Handler $handler): array {
        $matches = [];
        preg_match('/\{\{il\>(.+?)\}\}/', $match, $matches);
        [, $p] = $matches;
        $data = helper_plugin_fksimageshow::parseIlData($p);
        return [$state, [$data]];
    }

    public function render($mode, Doku_Renderer $renderer, $data): bool {
        global $ID;
        if ($mode == 'xhtml') {

            [, $matches] = $data;
            [$data] = $matches;

            $param = ['class' => 'imageShow imagelink'];

            if (!page_exists($data['href'])) {
                if (auth_quickaclcheck($ID) >= AUTH_EDIT) {
                    $renderer->nocache();
                    msg('page not exist: ' . $data['href'], -1, null, null, MSG_EDIT);
                    $param['class'] .= ' naught';
                }
            }
            $img_size = 360;
            switch ($data['position']) {
                case "left":
                    $param['class'] .= ' left';
                    break;
                case "right":
                    $param['class'] .= ' right';
                    break;
                default :
                    $param['class'] .= ' center';
                    break;
            }
            if ($data['image'] == null) {
                $renderer->nocache();
                $renderer->doc .= '<a href="' . (preg_match('|^http[s]?://|', trim($data['href'])) ? htmlspecialchars($data['href']) : wl(cleanID($data['href']))) . '">' . htmlspecialchars($data['label']) . '</a>';
            } else {
                $renderer->doc .= helper_plugin_fksimageshow::printIlImageDiv($data['image']['id'], $data['label'], $data['href'], $img_size, $param);
            }
        }
        return false;
    }
}
