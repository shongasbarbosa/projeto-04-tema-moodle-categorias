#!/usr/bin/env bash
set -euo pipefail
DIR="$(dirname "${BASH_SOURCE[0]}")"
source "$DIR/common.sh"

"$DIR/core-volume.sh"

echo "==> Subindo apenas webserver, db e mailpit (sem selenium/exttests — ver README)..."
echo "    (banco: $MOODLE_DOCKER_DB, PHP: $MOODLE_DOCKER_PHP_VERSION, porta: $MOODLE_DOCKER_WEB_PORT)"
moodle_docker_compose up -d "${CORE_SERVICES[@]}"

echo "==> Aguardando o banco de dados ficar pronto..."
"$MOODLEDOCKER_DIR/bin/moodle-docker-wait-for-db"

echo "==> Containers no ar. Use scripts/install.ps1 na primeira vez (instala o Moodle e o idioma pt_br)."
