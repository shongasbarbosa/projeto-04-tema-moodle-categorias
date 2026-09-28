#Requires -Version 5.1
<#
.SYNOPSIS
    Roda o seed idempotente de dados de demonstração (categorias, cursos, matrículas, progresso).
#>
$ErrorActionPreference = "Stop"
$root = Split-Path -Parent $PSScriptRoot
& bash "$root/scripts/lib/seed.sh"
