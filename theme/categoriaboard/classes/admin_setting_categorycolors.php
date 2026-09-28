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

namespace theme_categoriaboard;

/**
 * Admin setting for the "category id -> color" map, validated with
 * {@see \theme_categoriaboard\local\colormap::validate()}.
 *
 * A plain admin_setting_configtextarea does not expose a validation hook in
 * this Moodle version (only admin_setting_configselect and
 * admin_setting_configduration do), so this small subclass overrides
 * validate() directly instead.
 *
 * @package    theme_categoriaboard
 * @copyright  2026 Dhyego Barbosa
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class admin_setting_categorycolors extends \admin_setting_configtextarea {
    /**
     * Validates the setting value.
     *
     * @param string $data
     * @return true|string
     */
    public function validate($data) {
        $error = \theme_categoriaboard\local\colormap::validate($data);
        return $error === null ? true : $error;
    }
}
