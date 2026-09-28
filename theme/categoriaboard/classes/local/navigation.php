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

    /**
     * Adds our link to $CFG->custommenuitems, or removes it, without
     * touching any other line an admin may have added by hand.
     *
     * @param bool $enabled
     */
    public static function sync_custom_menu_item(bool $enabled): void {
        $lines = self::other_lines(get_config(null, 'custommenuitems') ?: '');

        if ($enabled) {
            $lines[] = get_string('mycoursesbycategory', 'theme_categoriaboard') . '|' . self::MARKER;
        }

        set_config('custommenuitems', implode("\n", $lines));
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
