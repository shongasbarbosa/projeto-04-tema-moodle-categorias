#!/usr/bin/env bash
set -euo pipefail
source "$(dirname "${BASH_SOURCE[0]}")/common.sh"

echo "==> Instalando ferramentas de qualidade (phpcs + padrão moodle) dentro do container..."
moodle_docker_exec composer require --dev --no-interaction \
    squizlabs/php_codesniffer:"^3.9" \
    moodlehq/moodle-cs:"^4"

echo "==> Inicializando o ambiente PHPUnit do Moodle (uma vez por instalação)..."
moodle_docker_exec php admin/tool/phpunit/cli/init.php

echo "==> Pronto. Rode scripts/check.ps1 para as verificações rápidas."
