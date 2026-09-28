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
 * Strings em pt_br para theme_categoriaboard.
 *
 * @package    theme_categoriaboard
 * @copyright  2026 Dhyego Barbosa
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['advancedsettings'] = 'Avançado';
$string['categorycolors'] = 'Cores por categoria';
$string['categorycolors_desc'] = 'Um par "id|#rrggbb" por linha, por exemplo "2|#1E4FD8". O id da categoria aparece na URL ao navegar pelas categorias de curso em Administração do site. Categorias sem entrada usam a cor padrão.';
$string['choosereadme'] = 'Painel por Categoria é um tema filho do Boost que agrupa os cursos matriculados do aluno por categoria, mostrando o progresso de cada curso. Identidade visual (cor primária, logo, cores por categoria) configurável pelo painel de administração, sem alterar o código do Moodle.';
$string['configtitle'] = 'Painel por Categoria';
$string['coursecount'] = '{$a} curso(s)';
$string['emptystate_nocourses'] = 'Você ainda não está matriculado em nenhum curso. Quando estiver, eles vão aparecer aqui agrupados por categoria.';
$string['emptystate_nocoursesincategory'] = 'Nenhum curso visível nesta categoria.';
$string['enablegrouping'] = 'Ativar agrupamento por categoria';
$string['enablegrouping_desc'] = 'Quando ativado, adiciona o link "Meus cursos por categoria" à navegação e mostra os cursos matriculados agrupados por categoria, com o progresso de cada um.';
$string['error_colormap_color'] = 'Linha {$a}: a cor deve estar no formato "#rrggbb".';
$string['error_colormap_format'] = 'Linha {$a}: use o formato "id|#rrggbb".';
$string['error_colormap_id'] = 'Linha {$a}: o id da categoria deve ser um número.';
$string['generalsettings'] = 'Geral';
$string['identityheading'] = 'Identidade visual';
$string['identityheading_desc'] = 'Cores e logo usados em todo o tema, sem precisar editar código.';
$string['logo'] = 'Logo';
$string['logo_desc'] = 'Logo exibida na página de login e no cabeçalho do site.';
$string['mycoursesbycategory'] = 'Meus cursos por categoria';
$string['noprogress'] = 'O acompanhamento de progresso não está ativado neste curso';
$string['panelheading'] = 'Painel do aluno';
$string['panelheading_desc'] = 'Configurações do painel "Meus cursos por categoria".';
$string['pluginname'] = 'Painel por Categoria';

$string['primarycolor'] = 'Cor primária';
$string['primarycolor_desc'] = 'Cor principal da marca, usada em botões, links e destaques.';


$string['privacy:metadata'] = 'O tema Painel por Categoria não armazena nenhum dado pessoal. Ele apenas lê dados de curso, categoria, matrícula e conclusão que já existem no Moodle para montar o painel do aluno.';
$string['progressof'] = 'Progresso em {$a}';
$string['progresspercent'] = '{$a}%';
$string['scsscode'] = 'SCSS bruto';
$string['scsscode_desc'] = 'Use este campo para adicionar regras SCSS extras, que serão injetadas depois da folha de estilos própria do tema.';

$string['themepreference'] = 'Tema';
$string['themepreference_dark'] = 'Escuro';
$string['themepreference_light'] = 'Claro';
$string['themepreference_system'] = 'Sistema';
$string['unknowncategory'] = 'Sem categoria';
