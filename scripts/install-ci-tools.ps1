#Requires -Version 5.1
<#
.SYNOPSIS
    Instala phpcs (padrão moodle) e inicializa o ambiente PHPUnit dentro do container.
.DESCRIPTION
    Rode uma vez, depois de scripts/install.ps1, antes da primeira vez que rodar scripts/check.ps1.
#>
$ErrorActionPreference = "Stop"
$root = Split-Path -Parent $PSScriptRoot
& bash "$root/scripts/lib/install-ci-tools.sh"
