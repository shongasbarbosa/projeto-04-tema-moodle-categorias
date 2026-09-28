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
 * Course progress helpers for the category board.
 *
 * @package    theme_categoriaboard
 * @copyright  2026 Dhyego Barbosa
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class progress {
    /**
     * Returns the rounded completion percentage (0-100) for a user in a
     * course, or null when completion tracking is disabled for that course
     * or there is nothing to track yet.
     *
     * @param \stdClass $course
     * @param int $userid
     * @return int|null
     */
    public static function for_course(\stdClass $course, int $userid): ?int {
        if (empty($course->enablecompletion)) {
            return null;
        }

        $percentage = \core_completion\progress::get_course_progress_percentage($course, $userid);
        if ($percentage === null) {
            return null;
        }

        return (int) round($percentage);
    }

    /**
     * Formats a percentage (0-100) as a rounded label, e.g. "42%".
     *
     * @param int $percent
     * @return string
     */
    public static function format_label(int $percent): string {
        return get_string('progresspercent', 'theme_categoriaboard', $percent);
    }
}
