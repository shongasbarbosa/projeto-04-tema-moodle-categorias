#Requires -Version 5.1
<#
.SYNOPSIS
    Sobe o ambiente Moodle + tema Painel por Categoria via Docker.
#>
$ErrorActionPreference = "Stop"
$root = Split-Path -Parent $PSScriptRoot
& bash "$root/scripts/lib/up.sh"
