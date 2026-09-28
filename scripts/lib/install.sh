#!/usr/bin/env bash
set -euo pipefail
source "$(dirname "${BASH_SOURCE[0]}")/common.sh"

echo "==> Instalando o Moodle (não interativo)..."
moodle_docker_exec php admin/cli/install_database.php \
    --agree-license \
    --fullname="Plataforma EaD Demonstração" \
    --shortname="Painel por Categoria" \
    --summary="Ambiente de demonstração do tema Painel por Categoria" \
    --adminuser="$ADMIN_USER" \
    --adminpass="$ADMIN_PASS" \
    --adminemail="$ADMIN_EMAIL"

echo "==> Instalando o pacote de idioma pt_br..."
moodle_docker_exec php local/categoriaboarddemo/cli/install-langpack.php --lang=pt_br

echo "==> Definindo pt_br como idioma padrão do site..."
moodle_docker_exec php admin/cli/cfg.php --name=lang --set=pt_br

echo "==> Ativando o tema Painel por Categoria como tema padrão..."
moodle_docker_exec php admin/cli/cfg.php --name=theme --set=categoriaboard

echo "==> Aplicando identidade e configurações do site (nome, login, menu, visitante)..."
moodle_docker_exec php local/categoriaboarddemo/cli/configure-site.php

echo "==> Limpando os caches..."
moodle_docker_exec php admin/cli/purge_caches.php

echo "==> Instalação concluída."
echo "    Admin: $ADMIN_USER / $ADMIN_PASS"
