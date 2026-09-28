#!/usr/bin/env bash
# Cria e popula o volume nomeado do Docker que guarda o código do Moodle
# (ver README, seção "Desempenho: código do Moodle fora do bind mount do
# Windows"). Idempotente: não faz nada se o volume já existir e já tiver o
# Moodle clonado dentro.
set -euo pipefail
source "$(dirname "${BASH_SOURCE[0]}")/common.sh"

# On Windows, Git Bash's MSYS layer rewrites arguments that look like POSIX
# paths before they reach docker.exe — including container-side paths like
# /var/www/html, which are not host paths at all. MSYS_NO_PATHCONV disables
# that rewriting for these ad-hoc `docker run` calls (bin/moodle-docker-compose
# never hits this, since its volume paths live inside a YAML file, not a
# literal shell argument).
export MSYS_NO_PATHCONV=1

WEBSERVER_IMAGE="moodlehq/moodle-php-apache:${MOODLE_DOCKER_PHP_VERSION}"

docker volume create "$CATEGORIAB_CORE_VOLUME" >/dev/null

already_populated() {
    docker run --rm -v "$CATEGORIAB_CORE_VOLUME:/var/www/html" "$WEBSERVER_IMAGE" \
        test -f /var/www/html/version.php
}

if already_populated; then
    echo "==> Volume '$CATEGORIAB_CORE_VOLUME' já tem o Moodle clonado. Nada a fazer."
    exit 0
fi

echo "==> Clonando o Moodle ($MOODLE_BRANCH) direto dentro do volume '$CATEGORIAB_CORE_VOLUME'..."
echo "    (roda dentro do filesystem nativo do Docker, sem passar pelo bind mount do Windows)"
docker run --rm -v "$CATEGORIAB_CORE_VOLUME:/var/www/html" "$WEBSERVER_IMAGE" \
    git clone --branch "$MOODLE_BRANCH" --depth 1 https://github.com/moodle/moodle.git /var/www/html

echo "==> Copiando config.php (ver docker/config.php.template)..."
docker run --rm \
    -v "$CATEGORIAB_CORE_VOLUME:/var/www/html" \
    -v "$ROOT_DIR/docker/config.php.template:/tmp/config.php.template:ro" \
    "$WEBSERVER_IMAGE" \
    cp /tmp/config.php.template /var/www/html/config.php

echo "==> Volume '$CATEGORIAB_CORE_VOLUME' pronto."
