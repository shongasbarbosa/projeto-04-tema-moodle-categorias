#Requires -Version 5.1
<#
.SYNOPSIS
    Roda as verificações rápidas: php -l, phpcs (padrão moodle), PHPUnit e purge de caches.
#>
$ErrorActionPreference = "Stop"
$root = Split-Path -Parent $PSScriptRoot
& bash "$root/scripts/lib/check.sh"
exit $LASTEXITCODE
