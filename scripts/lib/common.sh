#!/usr/bin/env bash
# Shared setup for every script in scripts/lib. Sourced, not executed
# directly. Resolves paths and exports the moodle-docker environment
# variables. Não clona mais o Moodle para uma pasta do host — ver
# scripts/lib/core-volume.sh e o README, seção "Desempenho: código do
# Moodle fora do bind mount do Windows".
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT_DIR"

if [ -f "$ROOT_DIR/.env" ]; then
    set -a
    # shellcheck disable=SC1091
    source "$ROOT_DIR/.env"
    set +a
fi

MOODLE_BRANCH="${MOODLE_BRANCH:-MOODLE_405_STABLE}"
export MOODLE_DOCKER_DB="${MOODLE_DOCKER_DB:-pgsql}"
export MOODLE_DOCKER_DB_VERSION="${MOODLE_DOCKER_DB_VERSION:-16}"
export MOODLE_DOCKER_PHP_VERSION="${MOODLE_DOCKER_PHP_VERSION:-8.3}"
export MOODLE_DOCKER_WEB_PORT="${MOODLE_DOCKER_WEB_PORT:-8000}"
export COMPOSE_PROJECT_NAME="${COMPOSE_PROJECT_NAME:-categoriaboard}"

# bin/moodle-docker-compose exige que $MOODLE_DOCKER_WWWROOT exista como
# diretório no host, mas o conteúdo dele não é mais usado: docker/local.yml
# substitui o mount de /var/www/html por um volume nomeado (ver
# core-volume.sh). Este diretório fica sempre vazio.
MOODLEDOCKER_DIR="$ROOT_DIR/.moodle-docker"
export MOODLE_DOCKER_WWWROOT="$ROOT_DIR/.moodle/wwwroot-placeholder"
mkdir -p "$MOODLE_DOCKER_WWWROOT"

export CATEGORIAB_THEME_DIR="$ROOT_DIR/theme/categoriaboard"
export CATEGORIAB_LOCAL_DIR="$ROOT_DIR/local/categoriaboarddemo"
export CATEGORIAB_CORE_VOLUME="${COMPOSE_PROJECT_NAME}_moodlecore"

ADMIN_USER="${MOODLE_ADMIN_USER:-admin}"
ADMIN_PASS="${MOODLE_ADMIN_PASS:-Categoriaboard@2026}"
ADMIN_EMAIL="${MOODLE_ADMIN_EMAIL:-admin@categoriaboard.local}"

# moodle-docker itself (the tool), pinned to a known-good ref.
if [ ! -d "$MOODLEDOCKER_DIR/.git" ]; then
    echo "==> Baixando moodle-docker (ferramenta oficial) em .moodle-docker/ ..."
    git clone https://github.com/moodlehq/moodle-docker.git "$MOODLEDOCKER_DIR"
fi

# Regenerate local.yml from the versioned template on every run.
cp "$ROOT_DIR/docker/local.yml" "$MOODLEDOCKER_DIR/local.yml"

MDC="$MOODLEDOCKER_DIR/bin/moodle-docker-compose"

moodle_docker_compose() {
    "$MDC" "$@"
}

# Only these three services are used by this project (see README, section
# "Serviços removidos"): webserver, db, mailpit. Selenium/exttests (Behat)
# are defined in moodle-docker's base.yml but never started.
CORE_SERVICES=(webserver db mailpit)

moodle_docker_exec() {
    moodle_docker_compose exec -T webserver "$@"
}
