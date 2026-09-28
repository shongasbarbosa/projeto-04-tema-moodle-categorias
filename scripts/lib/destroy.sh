#!/usr/bin/env bash
set -euo pipefail
source "$(dirname "${BASH_SOURCE[0]}")/common.sh"

echo "==> Removendo containers e volumes (o banco de dados será perdido)."
echo "    O volume '$CATEGORIAB_CORE_VOLUME' (código do Moodle) é externo e"
echo "    NÃO é removido por este comando — use scripts/reset-core.ps1 para isso."
moodle_docker_compose down -v
