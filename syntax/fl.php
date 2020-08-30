<?php

use dokuwiki\Extension\SyntaxPlugin;

/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Michal Červeňák <miso@fykos.cz>
 */
class syntax_plugin_fksimageshow_fl extends SyntaxPlugin {

    public function getType(): string {
        return 'substition';
    }

    public function getPType(): string {
        return 'block';
    }

    public function getAllowedTypes(): array {
        return [];
    }

    public function getSort(): string {
        return 226;
    }

    public function connectTo($mode): void {
        $this->Lexer->addSpecialPattern('{{fl>.+?\|.+?}}', $mode, 'plugin_fksimageshow_fl');
    }

    public function handle($match, $state, $pos, Doku_Handler $handler): array {
        preg_match('/{{\s*fl\s*>(.*)\|(.*)}}/', $match, $matches);
        [, $link, $text] = $matches;
        return [$state, $link, $text];
    }

    public function render($mode, Doku_Renderer $renderer, $data): bool {

        if ($mode == 'xhtml') {
            [, $link, $text] = $data;
            $renderer->doc .= '<a href="' . wl(cleanID($link)) . '">';
            $renderer->doc .= '<span class="button fast_link">';
            $renderer->doc .= htmlspecialchars(trim($text));
            $renderer->doc .= '</span>';
            $renderer->doc .= '</a>';
            return true;
        }
        return false;
    }
}
