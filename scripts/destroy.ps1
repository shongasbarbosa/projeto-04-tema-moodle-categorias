#Requires -Version 5.1
<#
.SYNOPSIS
    Remove containers e volumes (apaga o banco de dados). Peça confirmação antes de usar em automações.
#>
$ErrorActionPreference = "Stop"
$root = Split-Path -Parent $PSScriptRoot
& bash "$root/scripts/lib/destroy.sh"
