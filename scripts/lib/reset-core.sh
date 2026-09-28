#!/usr/bin/env bash
# Remove e recria do zero o volume com o código do Moodle (ex.: para trocar
# de branch, ou se o volume corromper). NÃO é chamado por reset.ps1/destroy.ps1
# — só quando você precisar mesmo de um Moodle novo, já que reclonar demora.
set -euo pipefail
DIR="$(dirname "${BASH_SOURCE[0]}")"
source "$DIR/common.sh"

"$DIR/down.sh" || true
echo "==> Removendo o volume '$CATEGORIAB_CORE_VOLUME'..."
docker volume rm "$CATEGORIAB_CORE_VOLUME" 2>/dev/null || true

"$DIR/core-volume.sh"
echo "==> Volume recriado. Rode scripts/up.ps1, install.ps1 e seed.ps1 de novo."
