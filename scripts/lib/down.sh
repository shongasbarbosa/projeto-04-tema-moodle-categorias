#!/usr/bin/env bash
set -euo pipefail
source "$(dirname "${BASH_SOURCE[0]}")/common.sh"

echo "==> Parando e removendo os containers (mantém os volumes/dados)."
moodle_docker_compose stop
