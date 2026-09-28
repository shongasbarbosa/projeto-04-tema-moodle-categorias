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
 * Admin settings for theme_categoriaboard.
 *
 * @package    theme_categoriaboard
 * @copyright  2026 Dhyego Barbosa
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings = new theme_boost_admin_settingspage_tabs(
        'themesettingcategoriaboard',
        get_string('configtitle', 'theme_categoriaboard')
    );

    // Tab: general / identity.
    $page = new admin_settingpage('theme_categoriaboard_general', get_string('generalsettings', 'theme_categoriaboard'));

    $page->add(new admin_setting_heading(
        'theme_categoriaboard_identity_heading',
        get_string('identityheading', 'theme_categoriaboard'),
        get_string('identityheading_desc', 'theme_categoriaboard')
    ));

    $name = 'theme_categoriaboard/primarycolor';
    $title = get_string('primarycolor', 'theme_categoriaboard');
    $description = get_string('primarycolor_desc', 'theme_categoriaboard');
    $setting = new admin_setting_configcolourpicker($name, $title, $description, '#1E4FD8');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $name = 'theme_categoriaboard/logo';
    $title = get_string('logo', 'theme_categoriaboard');
    $description = get_string('logo_desc', 'theme_categoriaboard');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'logo');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $page->add(new admin_setting_heading(
        'theme_categoriaboard_panel_heading',
        get_string('panelheading', 'theme_categoriaboard'),
        get_string('panelheading_desc', 'theme_categoriaboard')
    ));

    $name = 'theme_categoriaboard/enablegrouping';
    $title = get_string('enablegrouping', 'theme_categoriaboard');
    $description = get_string('enablegrouping_desc', 'theme_categoriaboard');
    $setting = new admin_setting_configcheckbox($name, $title, $description, 1);
    $setting->set_updatedcallback('theme_categoriaboard_enablegrouping_updated');
    $page->add($setting);

    $name = 'theme_categoriaboard/categorycolors';
    $title = get_string('categorycolors', 'theme_categoriaboard');
    $description = get_string('categorycolors_desc', 'theme_categoriaboard');
    $default = '';
    $setting = new \theme_categoriaboard\admin_setting_categorycolors($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $settings->add($page);

    // Tab: advanced (raw SCSS), same pattern used by Boost itself.
    $page = new admin_settingpage('theme_categoriaboard_advanced', get_string('advancedsettings', 'theme_categoriaboard'));

    $name = 'theme_categoriaboard/scsscode';
    $title = get_string('scsscode', 'theme_categoriaboard');
    $description = get_string('scsscode_desc', 'theme_categoriaboard');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $settings->add($page);
}
