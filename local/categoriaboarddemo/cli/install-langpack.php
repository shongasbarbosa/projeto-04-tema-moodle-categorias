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
 * Installs a language pack from the CLI.
 *
 * MOODLE_405_STABLE ships no admin/tool/langimport/cli/install.php (that
 * script was removed from core); this calls the same controller class the
 * "Language packs" admin page itself uses.
 *
 * Usage: php local/categoriaboarddemo/cli/install-langpack.php --lang=pt_br
 *
 * @package    local_categoriaboarddemo
 * @copyright  2026 Dhyego Barbosa
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');

[$options, ] = cli_get_params(['lang' => '']);

if (empty($options['lang'])) {
    cli_error('Uso: php local/categoriaboarddemo/cli/install-langpack.php --lang=<código>');
}

$controller = new \tool_langimport\controller();
$controller->install_languagepacks([$options['lang']]);

foreach ($controller->info as $line) {
    cli_writeln(strip_tags($line));
}
foreach ($controller->errors as $line) {
    cli_writeln('ERRO: ' . strip_tags($line));
}

exit(empty($controller->errors) ? 0 : 1);
