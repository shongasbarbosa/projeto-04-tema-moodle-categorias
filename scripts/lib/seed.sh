#!/usr/bin/env bash
set -euo pipefail
source "$(dirname "${BASH_SOURCE[0]}")/common.sh"

echo "==> Rodando o seed de dados de demonstração..."
moodle_docker_exec php local/categoriaboarddemo/cli/seed.php

echo "==> Limpando os caches..."
moodle_docker_exec php admin/cli/purge_caches.php
