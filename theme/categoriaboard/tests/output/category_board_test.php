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

/**
 * Tests for theme_categoriaboard\output\category_board.
 *
 * @package    theme_categoriaboard
 * @copyright  2026 Dhyego Barbosa
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \theme_categoriaboard\output\category_board
 */
final class category_board_test extends \advanced_testcase {
    public function test_empty_board_when_user_has_no_courses(): void {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();

        $board = new category_board($user->id);

        $this->assertSame([], $board->get_groups());
    }

    public function test_groups_enrolled_courses_by_category(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();

        $catprog = $generator->create_category(['name' => 'Programação']);
        $catdesign = $generator->create_category(['name' => 'Design']);

        $course1 = $generator->create_course(['category' => $catprog->id, 'fullname' => 'PHP para EaD']);
        $course2 = $generator->create_course(['category' => $catprog->id, 'fullname' => 'JavaScript Essencial']);
        $course3 = $generator->create_course(['category' => $catdesign->id, 'fullname' => 'UI para EaD']);

        $user = $generator->create_user();
        $generator->enrol_user($user->id, $course1->id, 'student');
        $generator->enrol_user($user->id, $course2->id, 'student');
        $generator->enrol_user($user->id, $course3->id, 'student');

        $board = new category_board($user->id);
        $groups = $board->get_groups();

        $this->assertCount(2, $groups);

        // Alphabetically: Design comes before Programação.
        $this->assertSame('Design', $groups[0]->categoryname);
        $this->assertCount(1, $groups[0]->courses);

        $this->assertSame('Programação', $groups[1]->categoryname);
        $this->assertCount(2, $groups[1]->courses);
    }

    public function test_ignores_courses_the_user_is_not_enrolled_in(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();

        $category = $generator->create_category();
        $generator->create_course(['category' => $category->id]);
        $user = $generator->create_user();

        $board = new category_board($user->id);

        $this->assertSame([], $board->get_groups());
    }

    public function test_ignores_hidden_courses(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();

        $category = $generator->create_category();
        $course = $generator->create_course(['category' => $category->id, 'visible' => 0]);
        $user = $generator->create_user();
        $generator->enrol_user($user->id, $course->id, 'student');

        $board = new category_board($user->id);

        $this->assertSame([], $board->get_groups());
    }

    public function test_uses_moodles_own_generated_image_when_course_has_none(): void {
        global $OUTPUT;

        $this->resetAfterTest();
        $generator = $this->getDataGenerator();

        $category = $generator->create_category();
        $course = $generator->create_course(['category' => $category->id]);
        $user = $generator->create_user();
        $generator->enrol_user($user->id, $course->id, 'student');

        $board = new category_board($user->id);
        $groups = $board->get_groups();

        $expected = $OUTPUT->get_generated_image_for_id((int) $course->id);
        $this->assertSame($expected, $groups[0]->courses[0]->imageurl);
    }

    public function test_applies_configured_category_color(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();

        $category = $generator->create_category();
        $course = $generator->create_course(['category' => $category->id]);
        $user = $generator->create_user();
        $generator->enrol_user($user->id, $course->id, 'student');

        set_config('categorycolors', $category->id . '|#00A676', 'theme_categoriaboard');

        $board = new category_board($user->id);
        $groups = $board->get_groups();

        $this->assertCount(1, $groups);
        $this->assertSame('#00A676', $groups[0]->color);
    }

    public function test_export_for_template_shape(): void {
        global $PAGE;

        $this->resetAfterTest();
        $generator = $this->getDataGenerator();

        $category = $generator->create_category();
        $generator->create_course(['category' => $category->id]);
        $user = $generator->create_user();

        $board = new category_board($user->id);
        $data = $board->export_for_template($PAGE->get_renderer('core'));

        $this->assertArrayHasKey('hasgroups', $data);
        $this->assertArrayHasKey('groups', $data);
        $this->assertFalse($data['hasgroups']);
        $this->assertSame([], $data['groups']);
    }
}
