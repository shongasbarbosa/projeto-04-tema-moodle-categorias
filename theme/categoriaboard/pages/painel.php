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
 * "My courses by category" page.
 *
 * @package    theme_categoriaboard
 * @copyright  2026 Dhyego Barbosa
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../config.php');

require_login(null, false);

if (isguestuser()) {
    throw new \moodle_exception('noguest');
}

$theme = \theme_config::load('categoriaboard');
if (empty($theme->settings->enablegrouping)) {
    redirect(new \moodle_url('/my/'));
}

$PAGE->set_context(\context_system::instance());
$PAGE->set_url(new \moodle_url('/theme/categoriaboard/pages/painel.php'));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('mycoursesbycategory', 'theme_categoriaboard'));
$PAGE->set_heading(get_string('mycoursesbycategory', 'theme_categoriaboard'));

$board = new \theme_categoriaboard\output\category_board($USER->id);
$renderer = $PAGE->get_renderer('theme_categoriaboard');

echo $OUTPUT->header();
echo $renderer->render_category_board($board);
echo $OUTPUT->footer();
