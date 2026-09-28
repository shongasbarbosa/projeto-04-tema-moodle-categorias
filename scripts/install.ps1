#Requires -Version 5.1
<#
.SYNOPSIS
    Instala o Moodle (CLI, não interativo), o idioma pt_br e ativa o tema Painel por Categoria.
.DESCRIPTION
    Rode depois de scripts/up.ps1, apenas na primeira vez (ou depois de scripts/destroy.ps1).
#>
$ErrorActionPreference = "Stop"
$root = Split-Path -Parent $PSScriptRoot
& bash "$root/scripts/lib/install.sh"
