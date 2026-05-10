#Requires -Version 5.1
<#
  Shallow-clone the canonical UniServe monolith into modules/_upstream/ for diff review.
  Output folder is gitignored — safe to replace entirely each run.
#>
$ErrorActionPreference = "Stop"
$root = Split-Path -Parent $PSScriptRoot
$dest = Join-Path $root "modules\_upstream\Esprit-PW-2A32-2526-UniServe"
$gitUrl = "https://github.com/Mehdi-MMO/Esprit-PW-2A32-2526-UniServe.git"

$upstreamDir = Join-Path $root "modules\_upstream"
if (-not (Test-Path $upstreamDir)) {
    New-Item -ItemType Directory -Path $upstreamDir -Force | Out-Null
}

if (Test-Path $dest) {
    Write-Host "Removing previous snapshot: $dest"
    Remove-Item -Recurse -Force $dest
}

Write-Host "Shallow cloning into $dest"
git clone --depth 1 $gitUrl $dest

Push-Location $dest
try {
    $sha = git rev-parse HEAD
    Write-Host "HEAD = $sha"
}
finally {
    Pop-Location
}

Write-Host "Update modules/manifest.json canonicalUpstream.pinnedCommitAtLastSync if you track the pin."
