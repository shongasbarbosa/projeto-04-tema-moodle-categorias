#Requires -Version 5.1
<#
.SYNOPSIS
    Para os containers, mantendo os dados (banco, moodledata) para religar depois.
#>
$ErrorActionPreference = "Stop"
$root = Split-Path -Parent $PSScriptRoot
& bash "$root/scripts/lib/down.sh"
