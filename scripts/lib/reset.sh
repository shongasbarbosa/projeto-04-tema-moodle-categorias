#!/usr/bin/env bash
set -euo pipefail
DIR="$(dirname "${BASH_SOURCE[0]}")"
source "$DIR/common.sh"

echo "==> Resetando o ambiente (containers, volumes e banco de dados)."
"$DIR/destroy.sh"
"$DIR/up.sh"
"$DIR/install.sh"
"$DIR/seed.sh"
