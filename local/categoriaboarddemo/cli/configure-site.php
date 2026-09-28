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
 * Idempotent CLI script that applies the demo environment's identity and
 * a handful of settings that install_database.php does not expose as
 * flags (site full/short name beyond what --fullname/--shortname already
 * set, the guest login button, and a core language string override).
 *
 * Usage (inside the webserver container): php local/categoriaboarddemo/cli/configure-site.php
 *
 * @package    local_categoriaboarddemo
 * @copyright  2026 Dhyego Barbosa
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');

cli_writeln('Painel por Categoria — configuração do site');
cli_writeln('=============================================');

// Site full/short name (shown in the header and in <title>).
$DB->set_field('course', 'fullname', 'Plataforma EaD Demonstração', ['id' => SITEID]);
$DB->set_field('course', 'shortname', 'Painel por Categoria', ['id' => SITEID]);
cli_writeln('  - nome do site: "Plataforma EaD Demonstração" / "Painel por Categoria"');

// Site default timezone: without this the install defaults to the PHP/OS
// timezone (Europe/London in the container image used here), which showed
// up as wrong activity/deadline times for every demo account since none of
// them override it individually either.
set_config('timezone', 'America/Sao_Paulo');
set_config('forcetimezone', 'America/Sao_Paulo');
cli_writeln('  - fuso horário do site: America/Sao_Paulo (forçado para todos os usuários)');

// This project has no use for anonymous access.
set_config('guestloginbutton', 0);
cli_writeln('  - botão "Acessar como visitante" desativado');

// Require login on every page: without this, the front page lists courses
// to anonymous visitors and, more importantly for this project, logging out
// lands back on that public front page instead of the login screen.
set_config('forcelogin', 1);
cli_writeln('  - "Forçar login em todo o site" ativado (forcelogin)');

// Overrides the login page title (core string "loginsite"). IMPORTANT: this
// must go in dataroot/lang/<lang>_local/, never dataroot/lang/<lang>/ — that
// second path *is* the installed language pack itself (where
// install-langpack.php writes the real moodle.php, with every core string).
// Writing our own moodle.php there replaces the whole pack instead of
// overriding one string, which is exactly what broke every other core
// string in pt_br in an earlier version of this script. The "_local"
// suffix is the officially supported override layer, merged on top of the
// real pack by the string manager without touching it — the same mechanism
// admin/tool/customlang writes to.
$overrides = [
    'pt_br_local' => 'Entrar no Painel por Categoria',
    'en_local' => 'Log in to Category board',
];
foreach ($overrides as $lang => $text) {
    $dir = $CFG->dataroot . '/lang/' . $lang;
    if (!is_dir($dir)) {
        mkdir($dir, $CFG->directorypermissions, true);
    }
    file_put_contents($dir . '/moodle.php', "<?php\n\$string['loginsite'] = " . var_export($text, true) . ";\n");
}
cli_writeln('  - título da página de login sobrescrito via lang/pt_br_local e lang/en_local');

// Keeps the custom menu link in sync with the theme's own setting, in case
// this runs before anyone has saved the theme settings form.
$enabled = (bool) get_config('theme_categoriaboard', 'enablegrouping');
\theme_categoriaboard\local\navigation::sync_custom_menu_item($enabled);
cli_writeln('  - link "Meus cursos por categoria" no menu: ' . ($enabled ? 'ativado' : 'desativado'));

cli_writeln('Concluído.');
exit(0);
