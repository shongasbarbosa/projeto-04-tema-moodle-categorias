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

namespace theme_categoriaboard\output;

use theme_categoriaboard\local\colormap;
use theme_categoriaboard\local\progress;

/**
 * Builds the "my courses grouped by category" board for a user.
 *
 * Deliberately assembled server-side (rather than as a Mustache override of
 * block_myoverview): that block loads its courses through a Vue app calling
 * external functions, so a template override alone cannot group them by
 * category or read completion progress. See README, section
 * "Painel do aluno" for the full rationale.
 *
 * @package    theme_categoriaboard
 * @copyright  2026 Dhyego Barbosa
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class category_board implements \renderable, \templatable {
    /** @var category_group[] */
    private $groups = [];

    /**
     * Builds the board for a given user.
     *
     * @param int $userid
     */
    public function __construct(int $userid) {
        $this->build($userid);
    }

    /**
     * Groups the user's enrolled courses by category.
     *
     * @param int $userid
     */
    private function build(int $userid): void {
        $courses = enrol_get_all_users_courses($userid, true, ['enablecompletion', 'summary']);
        if (empty($courses)) {
            return;
        }

        $bycategory = [];
        foreach ($courses as $course) {
            if (!$course->visible) {
                continue;
            }
            $bycategory[(int) $course->category][] = $course;
        }

        if (empty($bycategory)) {
            return;
        }

        $categories = \core_course_category::get_many(array_keys($bycategory));
        $theme = \theme_config::load('categoriaboard');
        $map = colormap::parse($theme->settings->categorycolors ?? '');

        $names = [];
        foreach ($bycategory as $categoryid => $categorycourses) {
            $category = $categories[$categoryid] ?? null;
            $categoryname = $category
                ? $category->get_formatted_name()
                : get_string('unknowncategory', 'theme_categoriaboard');
            $names[$categoryid] = $categoryname;

            $cards = [];
            foreach ($categorycourses as $course) {
                $courseurl = new \moodle_url('/course/view.php', ['id' => $course->id]);
                $cards[] = new course_card(
                    (int) $course->id,
                    format_string($course->fullname),
                    $courseurl->out(false),
                    self::get_course_image_url($course),
                    progress::for_course($course, $userid)
                );
            }

            $this->groups[$categoryid] = new category_group(
                $categoryid,
                $categoryname,
                colormap::color_for_category($categoryid, $map),
                $cards
            );
        }

        uasort($this->groups, function (category_group $a, category_group $b) {
            return strnatcasecmp($a->categoryname, $b->categoryname);
        });
        $this->groups = array_values($this->groups);
    }

    /**
     * Exports the board for the Mustache template.
     *
     * @param \renderer_base $output
     * @return array
     */
    public function export_for_template(\renderer_base $output): array {
        return [
            'hasgroups' => !empty($this->groups),
            'groups' => array_map(fn (category_group $g) => $g->export_for_template($output), $this->groups),
        ];
    }

    /**
     * Returns the category groups built for this board.
     *
     * @return category_group[]
     */
    public function get_groups(): array {
        return $this->groups;
    }

    /**
     * Returns the course's overview image, or the same per-course generated
     * pattern Moodle itself uses in "My courses" when there is none.
     *
     * @param \stdClass $course
     * @return string
     */
    private static function get_course_image_url(\stdClass $course): string {
        $listelement = new \core_course_list_element($course);
        foreach ($listelement->get_course_overviewfiles() as $file) {
            if (!$file->is_valid_image()) {
                continue;
            }
            $path = '/' . implode('/', [
                $file->get_contextid(),
                $file->get_component(),
                $file->get_filearea() . ($file->get_itemid() !== null ? $file->get_itemid() : ''),
            ]) . $file->get_filepath() . $file->get_filename();
            return \moodle_url::make_file_url('/pluginfile.php', $path, false)->out();
        }

        global $OUTPUT;
        return $OUTPUT->get_generated_image_for_id((int) $course->id);
    }
}
