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
 * Tests for theme_categoriaboard\local\navigation.
 *
 * @package    theme_categoriaboard
 * @copyright  2026 Dhyego Barbosa
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \theme_categoriaboard\local\navigation
 */
final class navigation_test extends \advanced_testcase {
    public function test_enabling_adds_the_link(): void {
        $this->resetAfterTest();
        set_config('custommenuitems', '');

        navigation::sync_custom_menu_item(true);

        $this->assertStringContainsString(navigation::MARKER, get_config(null, 'custommenuitems'));
    }

    public function test_disabling_removes_the_link(): void {
        $this->resetAfterTest();
        set_config('custommenuitems', '');
        navigation::sync_custom_menu_item(true);

        navigation::sync_custom_menu_item(false);

        $this->assertStringNotContainsString(navigation::MARKER, get_config(null, 'custommenuitems'));
    }

    public function test_preserves_other_custom_menu_lines(): void {
        $this->resetAfterTest();
        set_config('custommenuitems', "Ajuda|https://example.com/ajuda");

        navigation::sync_custom_menu_item(true);
        $withlink = get_config(null, 'custommenuitems');
        $this->assertStringContainsString('Ajuda|https://example.com/ajuda', $withlink);
        $this->assertStringContainsString(navigation::MARKER, $withlink);

        navigation::sync_custom_menu_item(false);
        $withoutlink = get_config(null, 'custommenuitems');
        $this->assertStringContainsString('Ajuda|https://example.com/ajuda', $withoutlink);
        $this->assertStringNotContainsString(navigation::MARKER, $withoutlink);
    }

    public function test_re_enabling_does_not_duplicate_the_link(): void {
        $this->resetAfterTest();
        set_config('custommenuitems', '');

        navigation::sync_custom_menu_item(true);
        navigation::sync_custom_menu_item(true);

        $count = substr_count(get_config(null, 'custommenuitems'), navigation::MARKER);
        $this->assertSame(count(navigation::LANGS), $count);
    }

    public function test_writes_one_line_per_language_with_translated_text(): void {
        $this->resetAfterTest();
        set_config('custommenuitems', '');

        navigation::sync_custom_menu_item(true);
        $menu = get_config(null, 'custommenuitems');

        $this->assertStringContainsString('Meus cursos por categoria|' . navigation::MARKER . '||pt_br', $menu);
        $this->assertStringContainsString('My courses by category|' . navigation::MARKER . '||en', $menu);
    }
}
