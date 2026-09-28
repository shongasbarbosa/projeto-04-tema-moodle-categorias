#Requires -Version 5.1
<#
.SYNOPSIS
    Reseta o ambiente por completo: destrói containers/volumes, sobe de novo, instala e semeia os dados.
#>
$ErrorActionPreference = "Stop"
$root = Split-Path -Parent $PSScriptRoot
& bash "$root/scripts/lib/reset.sh"
