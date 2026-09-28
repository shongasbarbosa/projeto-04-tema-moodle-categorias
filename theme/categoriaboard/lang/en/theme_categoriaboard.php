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

/**
 * English strings for theme_categoriaboard.
 *
 * @package    theme_categoriaboard
 * @copyright  2026 Dhyego Barbosa
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['advancedsettings'] = 'Advanced';
$string['categorycolors'] = 'Category colors';
$string['categorycolors_desc'] = 'One "id|#rrggbb" pair per line, e.g. "2|#1E4FD8". The category id can be found in the URL when browsing course categories in Site administration. Categories without an entry use the default color.';
$string['choosereadme'] = 'Category board is a child theme of Boost that groups a student\'s enrolled courses by category, showing per-course progress. Configurable identity (primary color, logo, category colors) from the admin settings, without touching Moodle core.';
$string['configtitle'] = 'Category board';
$string['coursecount'] = '{$a} course(s)';
$string['emptystate_nocourses'] = 'You are not enrolled in any course yet. When you are, they will show up here grouped by category.';
$string['emptystate_nocoursesincategory'] = 'No visible courses in this category.';
$string['enablegrouping'] = 'Enable grouping by category';
$string['enablegrouping_desc'] = 'When enabled, adds a "My courses by category" link to the navigation and shows enrolled courses grouped by category, with per-course progress.';
$string['error_colormap_color'] = 'Line {$a}: the color must be in the "#rrggbb" format.';
$string['error_colormap_format'] = 'Line {$a}: use the "id|#rrggbb" format.';
$string['error_colormap_id'] = 'Line {$a}: the category id must be a number.';
$string['generalsettings'] = 'General';
$string['identityheading'] = 'Visual identity';
$string['identityheading_desc'] = 'Colors and logo used across the theme, without editing any code.';
$string['logo'] = 'Logo';
$string['logo_desc'] = 'Logo shown on the login page and the site header.';
$string['mycoursesbycategory'] = 'My courses by category';
$string['noprogress'] = 'Progress tracking is not enabled for this course';
$string['panelheading'] = 'Student panel';
$string['panelheading_desc'] = 'Settings for the "My courses by category" panel.';
$string['pluginname'] = 'Category board';

$string['primarycolor'] = 'Primary color';
$string['primarycolor_desc'] = 'Main brand color, used for buttons, links and highlights.';


$string['privacy:metadata'] = 'The Category board theme does not store any personal data. It only reads course, category, enrolment and completion data that already exists in Moodle to build the student panel.';
$string['progressof'] = 'Progress in {$a}';
$string['progresspercent'] = '{$a}%';
$string['scsscode'] = 'Raw SCSS';
$string['scsscode_desc'] = 'Use this field to add extra SCSS rules, which will be injected after the theme\'s own stylesheet.';

$string['themepreference'] = 'Theme';
$string['themepreference_dark'] = 'Dark';
$string['themepreference_light'] = 'Light';
$string['themepreference_system'] = 'System';
$string['unknowncategory'] = 'Uncategorized';
