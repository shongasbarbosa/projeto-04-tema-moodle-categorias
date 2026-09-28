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
 * Idempotent CLI seed script for theme_categoriaboard demonstration data.
 *
 * Creates categories (each with a distinct color, wired into
 * theme_categoriaboard's own "category colors" setting), courses distributed
 * across them, a teacher, and a demo student enrolled across every category
 * with varied completion progress (0%, partial, 100%).
 *
 * Usage (inside the webserver container):
 *   php local/categoriaboarddemo/cli/seed.php
 *
 * @package    local_categoriaboarddemo
 * @copyright  2026 Dhyego Barbosa
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/course/modlib.php');

// With forcelogin enabled the CLI's anonymous user can't list categories
// ("Você não tem permissão para ver a lista de cursos"), so run as admin.
\core\session\manager::set_user(get_admin());

/**
 * Definition of the demo categories: name => hex color.
 */
const CATEGORIES = [
    'Programação'        => '#1E4FD8',
    'Design'              => '#F2545B',
    'Marketing Digital'   => '#00A676',
    'Dados e IA'          => '#8A4FFF',
    'Gestão de Projetos'  => '#FF9F1C',
];

/**
 * Courses per category, plus how many trackable activities each has.
 * "activities" controls how many manual-completion pages are created.
 */
const COURSES = [
    'Programação' => [
        ['PHP para EaD', 'CAT-PROG-C1'],
        ['JavaScript Essencial', 'CAT-PROG-C2'],
        ['Fundamentos de Bancos de Dados', 'CAT-PROG-C3'],
    ],
    'Design' => [
        ['UI para Plataformas EaD', 'CAT-DESIGN-C1'],
        ['Acessibilidade na Prática', 'CAT-DESIGN-C2'],
    ],
    'Marketing Digital' => [
        ['Marketing de Conteúdo', 'CAT-MKT-C1'],
        ['SEO para Cursos Online', 'CAT-MKT-C2'],
    ],
    'Dados e IA' => [
        ['Introdução a Dados', 'CAT-DADOS-C1'],
        ['IA Aplicada à Educação', 'CAT-DADOS-C2'],
        ['Estatística para EaD', 'CAT-DADOS-C3'],
    ],
    'Gestão de Projetos' => [
        ['Fundamentos de Gestão Ágil', 'CAT-GESTAO-C1'],
        ['Planejamento de Projetos EaD', 'CAT-GESTAO-C2'],
    ],
];

/** @var int Number of manually-completable "page" activities created per course. */
const ACTIVITIES_PER_COURSE = 4;

/** @var array Demo teacher account. */
const TEACHER = ['username' => 'professor.demo', 'firstname' => 'Paulo', 'lastname' => 'Professor'];
/** @var array Demo student account. */
const STUDENT = ['username' => 'aluno.demo', 'firstname' => 'Ana', 'lastname' => 'Aluna'];
/** @var string Password shared by the demo teacher and student accounts. */
const DEMO_PASSWORD = 'Categoriaboard@2026';

/**
 * Returns an existing category by name, or creates it.
 *
 * @param string $name
 * @return \core_course_category
 */
function categoriaboarddemo_get_or_create_category(string $name): \core_course_category {
    global $DB;

    $existing = $DB->get_record('course_categories', ['name' => $name]);
    if ($existing) {
        cli_writeln("  - categoria já existe: {$name} (id {$existing->id})");
        return \core_course_category::get($existing->id);
    }

    $category = \core_course_category::create(['name' => $name]);
    cli_writeln("  - categoria criada: {$name} (id {$category->id})");
    return $category;
}

/**
 * Returns an existing course by shortname, or creates it.
 *
 * @param string $fullname
 * @param string $shortname
 * @param int $categoryid
 * @return stdClass Course record.
 */
function categoriaboarddemo_get_or_create_course(string $fullname, string $shortname, int $categoryid): \stdClass {
    global $DB;

    $existing = $DB->get_record('course', ['shortname' => $shortname]);
    if ($existing) {
        cli_writeln("    - curso já existe: {$fullname} ({$shortname})");
        return $existing;
    }

    $course = create_course((object) [
        'fullname' => $fullname,
        'shortname' => $shortname,
        'category' => $categoryid,
        'enablecompletion' => 1,
        'visible' => 1,
        'summary' => "Curso de demonstração para o Painel por Categoria: {$fullname}.",
        'summaryformat' => FORMAT_HTML,
        // Demo enrolments are scripted, not real signups — a "welcome to
        // the course" email/notification in English (the site default
        // before configure-site.php's language override applies to it)
        // added noise with nothing behind it.
        'sendcoursewelcomemessage' => 0,
    ]);
    cli_writeln("    - curso criado: {$fullname} ({$shortname})");
    return $course;
}

/**
 * Creates ACTIVITIES_PER_COURSE manually-completable "page" activities in a
 * course, unless they already exist.
 *
 * @param stdClass $course
 * @return array<int, stdClass> Course modules, in creation order.
 */
function categoriaboarddemo_get_or_create_activities(\stdClass $course): array {
    global $DB, $CFG;
    require_once($CFG->dirroot . '/course/modlib.php');

    $existing = $DB->get_records('page', ['course' => $course->id], 'id ASC');
    if (count($existing) >= ACTIVITIES_PER_COURSE) {
        $cms = [];
        foreach ($existing as $page) {
            $cms[] = get_coursemodule_from_instance('page', $page->id, $course->id);
        }
        return array_slice($cms, 0, ACTIVITIES_PER_COURSE);
    }

    $cms = [];
    foreach ($existing as $page) {
        $cms[] = get_coursemodule_from_instance('page', $page->id, $course->id);
    }

    for ($i = count($existing) + 1; $i <= ACTIVITIES_PER_COURSE; $i++) {
        $moduleinfo = new \stdClass();
        $moduleinfo->modulename = 'page';
        $moduleinfo->module = $DB->get_field('modules', 'id', ['name' => 'page'], MUST_EXIST);
        $moduleinfo->course = $course->id;
        $moduleinfo->section = 0;
        $moduleinfo->visible = 1;
        $moduleinfo->name = "Atividade {$i}";
        $moduleinfo->intro = '';
        $moduleinfo->introformat = FORMAT_HTML;
        $moduleinfo->content = '<p>Conteúdo de demonstração gerado pelo seed do Painel por Categoria.</p>';
        $moduleinfo->contentformat = FORMAT_HTML;
        $moduleinfo->display = 5; // RESOURCELIB_DISPLAY_OPEN.
        $moduleinfo->printintro = 0;
        $moduleinfo->printlastmodified = 0;
        $moduleinfo->completion = COMPLETION_TRACKING_MANUAL;
        $moduleinfo->completionview = 0;
        $moduleinfo->completionexpected = 0;
        $moduleinfo->cmidnumber = '';

        $moduleinfo = add_moduleinfo($moduleinfo, $course);
        $cms[] = get_coursemodule_from_id('page', $moduleinfo->coursemodule);
    }

    return $cms;
}

/**
 * Returns an existing user by username, or creates it.
 *
 * @param array $user ['username' => ..., 'firstname' => ..., 'lastname' => ...]
 * @return stdClass
 */
function categoriaboarddemo_get_or_create_user(array $user): \stdClass {
    global $DB, $CFG;
    require_once($CFG->dirroot . '/user/lib.php');

    $existing = $DB->get_record('user', ['username' => $user['username'], 'deleted' => 0]);
    if ($existing) {
        // Re-apply the demo password every run: guarantees the documented
        // credentials always work, even if they were changed by hand or
        // (as happened once during development) stored incorrectly by an
        // earlier version of this script.
        update_internal_user_password($existing, DEMO_PASSWORD);
        // Re-apply on every run too: this is how an already-created account
        // from before this timezone fix was picked up without needing a
        // fresh install.
        $DB->set_field('user', 'timezone', 'America/Sao_Paulo', ['id' => $existing->id]);
        cli_writeln("  - usuário já existe: {$user['username']} (senha reaplicada)");
        return $existing;
    }

    $id = user_create_user((object) [
        'username' => $user['username'],
        'password' => DEMO_PASSWORD,
        'firstname' => $user['firstname'],
        'lastname' => $user['lastname'],
        'email' => $user['username'] . '@categoriaboard.local',
        'auth' => 'manual',
        'confirmed' => 1,
        'policyagreed' => 1,
        'mnethostid' => $CFG->mnet_localhost_id,
        'lang' => 'pt_br',
        'timezone' => 'America/Sao_Paulo',
    ]);

    cli_writeln("  - usuário criado: {$user['username']}");
    return $DB->get_record('user', ['id' => $id]);
}

/**
 * Enrols a user in a course with a given role, unless already enrolled.
 *
 * @param int $userid
 * @param int $courseid
 * @param string $rolename 'student' or 'editingteacher'
 */
function categoriaboarddemo_enrol(int $userid, int $courseid, string $rolename): void {
    global $DB;

    $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
    $context = \context_course::instance($courseid);

    if (is_enrolled($context, $userid)) {
        return;
    }

    $role = $DB->get_record('role', ['shortname' => $rolename], '*', MUST_EXIST);

    $instance = $DB->get_record('enrol', ['courseid' => $courseid, 'enrol' => 'manual'], '*', MUST_EXIST);
    $plugin = enrol_get_plugin('manual');
    $plugin->enrol_user($instance, $userid, $role->id);
}

/**
 * Marks $completedcount of the course's trackable activities as complete for
 * a user (0 => 0%, all => 100%, partial => in-between).
 *
 * @param stdClass $course
 * @param array $cms
 * @param int $userid
 * @param int $completedcount
 */
function categoriaboarddemo_set_progress(\stdClass $course, array $cms, int $userid, int $completedcount): void {
    $completion = new \completion_info($course);
    foreach (array_slice($cms, 0, $completedcount) as $cm) {
        $completion->update_state($cm, COMPLETION_COMPLETE, $userid);
    }
}

cli_writeln('Painel por Categoria — seed de dados de demonstração');
cli_writeln('====================================================');

cli_writeln('');
cli_writeln('1) Categorias e cores');
$categoriesbyname = [];
$colormaplines = [];
foreach (CATEGORIES as $name => $color) {
    $category = categoriaboarddemo_get_or_create_category($name);
    $categoriesbyname[$name] = $category;
    $colormaplines[] = $category->id . '|' . $color;
}
set_config('categorycolors', implode("\n", $colormaplines), 'theme_categoriaboard');
set_config('enablegrouping', 1, 'theme_categoriaboard');
\theme_categoriaboard\local\navigation::sync_custom_menu_item(true);
cli_writeln('  - configuração "categorycolors" do tema atualizada.');

cli_writeln('');
cli_writeln('2) Usuários de demonstração');
$teacher = categoriaboarddemo_get_or_create_user(TEACHER);
$student = categoriaboarddemo_get_or_create_user(STUDENT);

cli_writeln('');
cli_writeln('3) Cursos, atividades, matrículas e progresso');
$studentcoursesincategory = [];
foreach (COURSES as $categoryname => $courses) {
    cli_writeln("  Categoria: {$categoryname}");
    $category = $categoriesbyname[$categoryname];
    $index = 0;
    foreach ($courses as [$fullname, $shortname]) {
        $course = categoriaboarddemo_get_or_create_course($fullname, $shortname, (int) $category->id);
        $cms = categoriaboarddemo_get_or_create_activities($course);

        categoriaboarddemo_enrol((int) $teacher->id, (int) $course->id, 'editingteacher');
        categoriaboarddemo_enrol((int) $student->id, (int) $course->id, 'student');

        // Vary progress across the first three courses of every category:
        // none completed, half completed, all completed. Extra courses in a
        // category repeat the pattern.
        $pattern = [0, (int) floor(ACTIVITIES_PER_COURSE / 2), ACTIVITIES_PER_COURSE];
        $completedcount = $pattern[$index % count($pattern)];
        categoriaboarddemo_set_progress($course, $cms, (int) $student->id, $completedcount);

        $percent = (int) round(($completedcount / ACTIVITIES_PER_COURSE) * 100);
        cli_writeln("    - {$fullname}: progresso do aluno demo definido para {$percent}%");
        $index++;
    }
}

cli_writeln('');
cli_writeln('4) Limpeza de notificações de demonstração');
// Earlier runs of this script (before sendcoursewelcomemessage was turned
// off above) generated real "welcome to the course" notifications in
// English for the demo accounts — clearing those out so a fresh visual
// check of the notifications page doesn't show leftover noise.
$demouserids = [(int) $teacher->id, (int) $student->id];
[$insql, $inparams] = $DB->get_in_or_equal($demouserids, SQL_PARAMS_NAMED);
$DB->delete_records_select('notifications', "useridto {$insql}", $inparams);
cli_writeln('  - notificações antigas removidas para as contas de demonstração.');

cli_writeln('');
cli_writeln('Concluído. Credenciais de demonstração:');
cli_writeln('  Admin:      admin / (definida na instalação, ver scripts/install.ps1)');
cli_writeln('  Professor:  ' . TEACHER['username'] . ' / ' . DEMO_PASSWORD);
cli_writeln('  Aluno:      ' . STUDENT['username'] . ' / ' . DEMO_PASSWORD);
exit(0);
