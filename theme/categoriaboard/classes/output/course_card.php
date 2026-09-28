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
 * A single course card in the category board.
 *
 * @package    theme_categoriaboard
 * @copyright  2026 Dhyego Barbosa
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_card implements \renderable, \templatable {
    /** @var int */
    public $id;
    /** @var string */
    public $fullname;
    /** @var string */
    public $url;
    /** @var string */
    public $imageurl;
    /** @var int|null */
    public $progress;

    /**
     * Builds a course card.
     *
     * @param int $id
     * @param string $fullname Already formatted (format_string).
     * @param string $url
     * @param string $imageurl
     * @param int|null $progress 0-100, or null when there is nothing to track.
     */
    public function __construct(int $id, string $fullname, string $url, string $imageurl, ?int $progress) {
        $this->id = $id;
        $this->fullname = $fullname;
        $this->url = $url;
        $this->imageurl = $imageurl;
        $this->progress = $progress;
    }

    /**
     * Exports the card for the Mustache template.
     *
     * @param \renderer_base $output
     * @return array
     */
    public function export_for_template(\renderer_base $output): array {
        return [
            'id' => $this->id,
            'fullname' => $this->fullname,
            'url' => $this->url,
            'imageurl' => $this->imageurl,
            'hasprogress' => $this->progress !== null,
            'progress' => $this->progress,
            'progresslabel' => $this->progress !== null
                ? \theme_categoriaboard\local\progress::format_label($this->progress)
                : get_string('noprogress', 'theme_categoriaboard'),
        ];
    }
}
