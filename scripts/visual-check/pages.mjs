// Pages checked by scripts/visual-check/run.mjs, grouped by which login the
// page needs (or "none" for pages reachable while logged out).
export const PAGES = [
    {name: '01-login', path: '/login/index.php', as: 'none'},
    {name: '02-home', path: '/', as: 'aluno'},
    {name: '03-my', path: '/my/', as: 'aluno'},
    {name: '04-my-courses', path: '/my/courses.php', as: 'aluno'},
    {name: '05-painel-categoria', path: '/theme/categoriaboard/pages/painel.php', as: 'aluno'},
    {name: '06-curso', path: '/course/view.php?id=2', as: 'aluno'},
    {name: '07-participantes', path: '/user/index.php?id=2', as: 'aluno'},
    {name: '08-notas', path: '/grade/report/user/index.php?id=2', as: 'aluno'},
    {name: '09-atividade', path: '/mod/page/view.php?id=2', as: 'aluno'},
    {name: '12-admin-search', path: '/admin/search.php', as: 'admin'},
    {name: '13-config-tema', path: '/admin/settings.php?section=themesettingcategoriaboard', as: 'admin'},
    {name: '14-notificacoes', path: '/message/output/popup/notifications.php', as: 'aluno'},
    {name: '15-perfil', path: '/user/profile.php', as: 'aluno'},
    {name: '16-arquivos', path: '/user/files.php', as: 'aluno'},
    {name: '17-editar-atividade', path: '/course/modedit.php?update=2&return=1', as: 'admin'},
];

// Pages above are static navigations. These need an extra interaction (open
// a menu/popover, toggle edit mode, open a modal) after the page loads,
// handled specially in run.mjs's INTERACTION_HANDLERS.
export const INTERACTIVE_PAGES = [
    {name: '10-menu-usuario', path: '/my/', as: 'aluno', open: 'usermenu'},
    {name: '11-notificacoes-popover', path: '/my/', as: 'aluno', open: 'notifications'},
    {name: '18-my-modo-edicao', path: '/my/', as: 'aluno', open: 'editmode'},
    {name: '19-curso-abas', path: '/course/view.php?id=2', as: 'aluno', open: 'coursetabs'},
    {name: '20-gaveta-mensagens', path: '/my/', as: 'aluno', open: 'messagedrawer'},
    {name: '21-seletor-arquivos', path: '/user/files.php', as: 'aluno', open: 'filepicker'},
    {name: '22-tinymce', path: '/course/modedit.php?update=2&return=1', as: 'admin', open: 'tinymce'},
    {name: '23-seletor-data', path: '/course/modedit.php?update=2&return=1', as: 'admin', open: 'datefield'},
    {name: '24-modal-confirmacao', path: '/course/view.php?id=2', as: 'admin', open: 'confirmmodal'},
];

export const VIEWPORTS = [
    {name: 'desktop', width: 1280, height: 800},
    {name: 'mobile', width: 360, height: 720},
];

export const MODES = ['light', 'dark'];
