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
 * Tests for theme_categoriaboard\local\progress.
 *
 * @package    theme_categoriaboard
 * @copyright  2026 Dhyego Barbosa
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \theme_categoriaboard\local\progress
 */
final class progress_test extends \advanced_testcase {
    public function test_returns_null_when_completion_disabled(): void {
        $this->resetAfterTest();
        $course = (object) ['id' => 1, 'enablecompletion' => 0];
        $this->assertNull(progress::for_course($course, 2));
    }

    public function test_returns_zero_percent_before_any_activity_is_completed(): void {
        $this->resetAfterTest();
        [$course, $user, $page] = $this->create_course_with_one_trackable_activity();

        $this->assertSame(0, progress::for_course($course, $user->id));
    }

    public function test_returns_hundred_percent_once_all_activities_are_completed(): void {
        $this->resetAfterTest();
        [$course, $user, $page] = $this->create_course_with_one_trackable_activity();

        $completion = new \completion_info($course);
        $cm = get_coursemodule_from_instance('page', $page->id, $course->id);
        $completion->update_state($cm, COMPLETION_COMPLETE, $user->id);

        $this->assertSame(100, progress::for_course($course, $user->id));
    }

    /**
     * Creates a course, a student, and one manually-completable page in it.
     *
     * @return array{0: \stdClass, 1: \stdClass, 2: \stdClass} [course, user, page module]
     */
    private function create_course_with_one_trackable_activity(): array {
        $generator = $this->getDataGenerator();
        $course = $generator->create_course([
            'enablecompletion' => 1,
        ]);
        $user = $generator->create_user();
        $generator->enrol_user($user->id, $course->id, 'student');

        $page = $generator->create_module('page', [
            'course' => $course->id,
            'completion' => COMPLETION_TRACKING_MANUAL,
        ]);

        return [$course, $user, $page];
    }

    public function test_format_label(): void {
        $this->resetAfterTest();
        $this->assertSame('42%', progress::format_label(42));
        $this->assertSame('0%', progress::format_label(0));
        $this->assertSame('100%', progress::format_label(100));
    }
}
