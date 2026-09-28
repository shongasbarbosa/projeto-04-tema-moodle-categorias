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
 * Parses and validates the admin-configured "category id -> color" map.
 *
 * The map is stored as plain text, one "id|#rrggbb" pair per line, so it can
 * live in a single admin_setting_configtextarea without a custom form widget.
 *
 * @package    theme_categoriaboard
 * @copyright  2026 Dhyego Barbosa
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class colormap {
    /** @var string Fallback color used when a category has no entry in the map. */
    const DEFAULT_COLOR = '#1E4FD8';

    /**
     * Parses raw settings text into a [categoryid => '#rrggbb'] array, silently
     * skipping malformed lines (validation happens separately, at save time).
     *
     * @param string $raw
     * @return array<int, string>
     */
    public static function parse(string $raw): array {
        $map = [];
        foreach (self::split_lines($raw) as $line) {
            $parts = explode('|', $line);
            if (count($parts) !== 2) {
                continue;
            }
            [$id, $color] = array_map('trim', $parts);
            if (!ctype_digit($id) || !self::is_valid_hex($color)) {
                continue;
            }
            $map[(int) $id] = strtoupper($color);
        }
        return $map;
    }

    /**
     * Validates raw settings text, returning a human-readable error for the
     * first malformed line, or null when the whole text is valid.
     *
     * @param string $raw
     * @return string|null
     */
    public static function validate(string $raw): ?string {
        foreach (self::split_lines($raw) as $index => $line) {
            $linenumber = $index + 1;
            $parts = explode('|', $line);
            if (count($parts) !== 2) {
                return get_string('error_colormap_format', 'theme_categoriaboard', $linenumber);
            }
            [$id, $color] = array_map('trim', $parts);
            if (!ctype_digit($id)) {
                return get_string('error_colormap_id', 'theme_categoriaboard', $linenumber);
            }
            if (!self::is_valid_hex($color)) {
                return get_string('error_colormap_color', 'theme_categoriaboard', $linenumber);
            }
        }
        return null;
    }

    /**
     * Returns the color configured for a category, or the default color.
     *
     * @param int $categoryid
     * @param array<int, string> $map
     * @return string
     */
    public static function color_for_category(int $categoryid, array $map): string {
        return $map[$categoryid] ?? self::DEFAULT_COLOR;
    }

    /**
     * Checks whether a string is a well-formed "#rrggbb" color.
     *
     * @param string $color
     * @return bool
     */
    public static function is_valid_hex(string $color): bool {
        return (bool) preg_match('/^#[0-9A-Fa-f]{6}$/', $color);
    }

    /**
     * Splits raw settings text into non-empty, trimmed lines.
     *
     * @param string $raw
     * @return string[] Non-empty, trimmed lines.
     */
    private static function split_lines(string $raw): array {
        $lines = preg_split('/\r\n|\r|\n/', trim($raw));
        $lines = array_map('trim', $lines);
        return array_values(array_filter($lines, fn($line) => $line !== ''));
    }
}
