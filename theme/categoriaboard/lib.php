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
 * Library of callbacks for theme_categoriaboard.
 *
 * @package    theme_categoriaboard
 * @copyright  2026 Dhyego Barbosa
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Returns the main SCSS content, built from the Boost preset plus our own overrides.
 *
 * @param theme_config $theme
 * @return string
 */
function theme_categoriaboard_get_main_scss_content($theme) {
    global $CFG;

    // Reuse Boost's own default preset as our base, exactly like other Boost
    // child themes do, so we inherit its layout/grid without duplicating it.
    return file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss');
}

/**
 * Pre-SCSS: color and design-token variables, computed from the admin settings,
 * injected before the Bootstrap/Boost SCSS so they can be used by it.
 *
 * @param theme_config $theme
 * @return string
 */
function theme_categoriaboard_get_pre_scss($theme) {
    $scss = '';

    $primary = $theme->settings->primarycolor ?? '#1E4FD8';
    $scss .= '$primary: ' . $primary . ";\n";

    // Surface, radius and shadow tokens, matching the visual language used in
    // the earlier projects of this portfolio series.
    $scss .= '$categoriaboard-radius-sm: 8px;' . "\n";
    $scss .= '$categoriaboard-radius-md: 12px;' . "\n";
    $scss .= '$categoriaboard-radius-lg: 16px;' . "\n";
    $scss .= '$categoriaboard-shadow-sm: 0 1px 2px rgba(15, 23, 42, 0.08);' . "\n";
    $scss .= '$categoriaboard-shadow-md: 0 4px 12px rgba(15, 23, 42, 0.10);' . "\n";
    $scss .= '$categoriaboard-surface: #FFFFFF;' . "\n";
    $scss .= '$categoriaboard-surface-dark: #12161C;' . "\n";

    if (!empty($theme->settings->preset)) {
        $scss .= '// Preset: ' . $theme->settings->preset . "\n";
    }

    return $scss;
}

/**
 * Extra SCSS appended after the main Boost/Bootstrap SCSS: our own component
 * styles for the category board, login page, header and theme switcher.
 *
 * @param theme_config $theme
 * @return string
 */
function theme_categoriaboard_get_extra_scss($theme) {
    global $CFG;

    $content = '';
    $filename = $CFG->dirroot . '/theme/categoriaboard/scss/categoriaboard.scss';
    if (file_exists($filename)) {
        $content .= file_get_contents($filename);
    }

    if (!empty($theme->settings->scsscode)) {
        $content .= "\n" . $theme->settings->scsscode;
    }

    return $content;
}

/**
 * Serves files from the theme, including the configurable logo and preset files.
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return bool
 */
function theme_categoriaboard_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    if ($context->contextlevel != CONTEXT_SYSTEM) {
        return false;
    }

    $theme = theme_config::load('categoriaboard');

    if ($filearea === 'logo') {
        return $theme->setting_file_serve('logo', $args, $forcedownload, $options);
    }

    send_file_not_found();
}

/**
 * Called when the "enablegrouping" setting is saved: purges caches and
 * keeps the "My courses by category" custom menu link in sync with it. See
 * {@see \theme_categoriaboard\local\navigation} for why a custom menu item
 * is used instead of a `extend_navigation()` callback (never called for
 * themes).
 */
function theme_categoriaboard_enablegrouping_updated() {
    theme_reset_all_caches();
    \theme_categoriaboard\local\navigation::sync_custom_menu_item(
        (bool) get_config('theme_categoriaboard', 'enablegrouping')
    );
}
