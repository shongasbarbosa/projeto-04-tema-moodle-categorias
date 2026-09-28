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
 * Tests for theme_categoriaboard\local\colormap.
 *
 * @package    theme_categoriaboard
 * @copyright  2026 Dhyego Barbosa
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \theme_categoriaboard\local\colormap
 */
final class colormap_test extends \advanced_testcase {
    public function test_parse_returns_empty_array_for_empty_input(): void {
        $this->assertSame([], colormap::parse(''));
        $this->assertSame([], colormap::parse("   \n  \n"));
    }

    public function test_parse_reads_valid_pairs(): void {
        $raw = "2|#1E4FD8\n5|#00A676\n";
        $this->assertSame([2 => '#1E4FD8', 5 => '#00A676'], colormap::parse($raw));
    }

    public function test_parse_uppercases_hex_codes(): void {
        $this->assertSame([2 => '#1E4FD8'], colormap::parse('2|#1e4fd8'));
    }

    public function test_parse_ignores_blank_lines_and_surrounding_whitespace(): void {
        $raw = "\n  2 | #1E4FD8  \n\n   \n5|#00A676\n";
        $this->assertSame([2 => '#1E4FD8', 5 => '#00A676'], colormap::parse($raw));
    }

    public function test_parse_skips_malformed_lines(): void {
        $raw = "2|#1E4FD8\nnotanumber|#00A676\n5|notacolor\njustoneToken\n8|#123\n";
        $this->assertSame([2 => '#1E4FD8'], colormap::parse($raw));
    }

    public function test_parse_last_entry_wins_for_duplicate_ids(): void {
        $raw = "2|#1E4FD8\n2|#00A676\n";
        $this->assertSame([2 => '#00A676'], colormap::parse($raw));
    }

    public function test_validate_accepts_well_formed_input(): void {
        $this->assertNull(colormap::validate("2|#1E4FD8\n5|#00A676"));
        $this->assertNull(colormap::validate(''));
    }

    public function test_validate_rejects_missing_separator(): void {
        $this->assertNotNull(colormap::validate("2 #1E4FD8"));
    }

    public function test_validate_rejects_non_numeric_id(): void {
        $this->assertNotNull(colormap::validate('abc|#1E4FD8'));
    }

    public function test_validate_rejects_malformed_color(): void {
        $this->assertNotNull(colormap::validate('2|blue'));
        $this->assertNotNull(colormap::validate('2|#FFF'));
        $this->assertNotNull(colormap::validate('2|#GGGGGG'));
    }

    public function test_validate_error_message_references_correct_line_number(): void {
        $error = colormap::validate("2|#1E4FD8\nbad|#00A676");
        $this->assertStringContainsString('2', $error);
    }

    public function test_color_for_category_returns_configured_color(): void {
        $map = [2 => '#1E4FD8'];
        $this->assertSame('#1E4FD8', colormap::color_for_category(2, $map));
    }

    public function test_color_for_category_falls_back_to_default(): void {
        $this->assertSame(colormap::DEFAULT_COLOR, colormap::color_for_category(999, []));
    }

    public function test_is_valid_hex(): void {
        $this->assertTrue(colormap::is_valid_hex('#1E4FD8'));
        $this->assertTrue(colormap::is_valid_hex('#abcdef'));
        $this->assertFalse(colormap::is_valid_hex('1E4FD8'));
        $this->assertFalse(colormap::is_valid_hex('#1E4FD'));
        $this->assertFalse(colormap::is_valid_hex('#1E4FD8A'));
        $this->assertFalse(colormap::is_valid_hex('#GGGGGG'));
    }
}
