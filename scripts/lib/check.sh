#!/usr/bin/env bash
set -uo pipefail
source "$(dirname "${BASH_SOURCE[0]}")/common.sh"

status=0

echo "==> php -l (theme_categoriaboard + local_categoriaboarddemo)"
if ! moodle_docker_exec bash -lc "find theme/categoriaboard local/categoriaboarddemo -name '*.php' -print0 | xargs -0 -n1 php -l"; then
    status=1
fi

echo ""
echo "==> phpcs (padrão moodle)"
if moodle_docker_exec bash -lc "test -x vendor/bin/phpcs"; then
    if ! moodle_docker_exec vendor/bin/phpcs --standard=moodle theme/categoriaboard local/categoriaboarddemo; then
        status=1
    fi
else
    echo "    vendor/bin/phpcs não encontrado (composer install do Moodle não inclui phpcs por padrão)."
    echo "    Rode: scripts/lib/install-ci-tools.sh, ou instale moodle-plugin-ci (ver README, seção Qualidade)."
    status=1
fi

echo ""
echo "==> PHPUnit (theme_categoriaboard + local_categoriaboarddemo)"
if moodle_docker_exec bash -lc "test -f vendor/bin/phpunit"; then
    if ! moodle_docker_exec vendor/bin/phpunit --testsuite theme_categoriaboard_testsuite 2>/dev/null; then
        echo "    Suite nomeada não encontrada; rodando por diretório."
        if ! moodle_docker_exec vendor/bin/phpunit theme/categoriaboard/tests local/categoriaboarddemo/tests; then
            status=1
        fi
    fi
else
    echo "    vendor/bin/phpunit não encontrado. Rode primeiro:"
    echo "    bin/moodle-docker-compose exec webserver php admin/tool/phpunit/cli/init.php"
    status=1
fi

echo ""
echo "==> Purge de caches"
if ! moodle_docker_exec php admin/cli/purge_caches.php; then
    status=1
fi

exit $status
