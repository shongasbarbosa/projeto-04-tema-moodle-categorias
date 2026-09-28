<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace theme_categoriaboard\local;

/**
 * Adds/removes the "My courses by category" link from the site-wide custom
 * menu (`$CFG->custommenuitems`).
 *
 * A theme's `<theme>_extend_navigation()` is never called by Moodle — that
 * callback only exists for `local` plugins (see `lib/navigationlib.php`,
 * `get_plugin_list_with_function('local', 'extend_navigation')`). Themes
 * have no supported hook to add a node to the primary/drawer navigation, so
 * this uses the one navigation surface a theme *can* legitimately drive:
 * the core "Custom menu items" setting (Site administration > Appearance >
 * Advanced theme settings), which every Boost-based theme already renders
 * in its header. See README, section "Menu Meus cursos por categoria".
 *
 * @package    theme_categoriaboard
 * @copyright  2026 Dhyego Barbosa
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class navigation {
    /** @var string Marks our own line so it can be found and replaced without touching others. */
    const MARKER = '/theme/categoriaboard/pages/painel.php';

    /** @var string[] Languages the link is translated to, one custom menu line per language. */
    const LANGS = ['pt_br', 'en'];

    /**
     * Adds our link to $CFG->custommenuitems, or removes it, without
     * touching any other line an admin may have added by hand.
     *
     * One line per language in {@see self::LANGS} is written, each with the
     * fourth "langs" field of the custommenuitems format (see
     * {@see \custom_menu_item} / {@see parse_custom_menu()}), so the link
     * text follows the viewer's language instead of always showing in
     * whichever language built the string first.
     *
     * @param bool $enabled
     */
    public static function sync_custom_menu_item(bool $enabled): void {
        $lines = self::other_lines(get_config(null, 'custommenuitems') ?: '');

        if ($enabled) {
            foreach (self::LANGS as $lang) {
                $lines[] = self::translated_text($lang) . '|' . self::MARKER . '||' . $lang;
            }
        }

        set_config('custommenuitems', implode("\n", $lines));
    }

    /**
     * Returns the "My courses by category" string in a specific language.
     *
     * Deliberately does not use {@see get_string()}'s `$lang` override
     * (`get_string_manager()->get_string(..., $lang)`): that only resolves
     * strings for a language whose *full* pack is installed site-wide
     * (`$CFG->dataroot/lang/<lang>/langconfig.php`), otherwise Moodle's
     * parent-language resolution silently falls back to English even for a
     * string our own plugin ships. Reading `lang/<lang>/theme_categoriaboard.php`
     * directly works on any install, with or without that langpack.
     *
     * @param string $lang
     * @return string
     */
    private static function translated_text(string $lang): string {
        $dir = \core_component::get_plugin_directory('theme', 'categoriaboard');
        $file = "$dir/lang/$lang/theme_categoriaboard.php";
        $string = [];
        if (is_readable($file)) {
            include($file);
        }
        return $string['mycoursesbycategory'] ?? get_string('mycoursesbycategory', 'theme_categoriaboard');
    }

    /**
     * Returns every line of $CFG->custommenuitems except our own.
     *
     * @param string $raw
     * @return string[]
     */
    private static function other_lines(string $raw): array {
        $lines = preg_split('/\r\n|\r|\n/', $raw);
        $lines = array_map('rtrim', $lines);
        return array_values(array_filter($lines, function ($line) {
            return $line !== '' && strpos($line, self::MARKER) === false;
        }));
    }
}
