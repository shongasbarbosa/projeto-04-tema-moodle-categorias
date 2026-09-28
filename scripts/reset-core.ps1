#Requires -Version 5.1
<#
.SYNOPSIS
    Remove e reclona do zero o volume com o código do Moodle. Raramente necessário.
#>
$ErrorActionPreference = "Stop"
$root = Split-Path -Parent $PSScriptRoot
& bash "$root/scripts/lib/reset-core.sh"
