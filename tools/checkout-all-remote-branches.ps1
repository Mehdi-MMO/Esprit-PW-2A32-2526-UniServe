#Requires -Version 5.1
<#
  Fetches all remotes, then adds one git worktree per origin/* branch under ./worktrees/<slug>/.
  Skips branches already checked out in any worktree (including this repo root).
  Skips if worktrees/<slug> already exists (delete manually or remove worktree first).
#>
$ErrorActionPreference = 'Stop'

$RepoRoot = Split-Path -Parent $PSScriptRoot
Set-Location $RepoRoot

function Get-LockedBranchRefs {
    $locked = @{}
    $lines = @(git worktree list --porcelain 2>&1)
    if ($LASTEXITCODE -ne 0) {
        throw "git worktree list failed: $($lines -join "`n")"
    }
    foreach ($line in $lines) {
        if ($line -match '^branch (.+)$') {
            $locked[$matches[1]] = $true
        }
    }
    return $locked
}

function ConvertTo-Slug([string]$branchTail) {
    return ($branchTail -replace '/', '-')
}

Write-Host "Repository: $RepoRoot"
Write-Host "Fetching remotes..."
git fetch --all --prune
if ($LASTEXITCODE -ne 0) {
    throw "git fetch failed"
}

$locked = Get-LockedBranchRefs
Write-Host "Branches currently locked (checked out somewhere): $($locked.Keys.Count)"

$remoteBranches = @(git for-each-ref 'refs/remotes/origin/*' --format='%(refname:short)' 2>&1)
if ($LASTEXITCODE -ne 0) {
    throw "git for-each-ref failed: $($remoteBranches -join "`n")"
}

$worktreesRoot = Join-Path $RepoRoot 'worktrees'
if (-not (Test-Path $worktreesRoot)) {
    New-Item -ItemType Directory -Path $worktreesRoot -Force | Out-Null
}

$added = 0
$skippedLocked = 0
$skippedExists = 0

foreach ($rb in $remoteBranches) {
    if ([string]::IsNullOrWhiteSpace($rb)) {
        continue
    }

    if ($rb -eq 'origin/HEAD' -or $rb -eq 'origin') {
        continue
    }

    if ($rb -notmatch '^origin/(.+)$') {
        Write-Host "SKIP (unexpected ref): $rb"
        continue
    }

    $tail = $matches[1]
    $headRef = "refs/heads/$tail"

    if ($locked.ContainsKey($headRef)) {
        Write-Host "SKIP (already checked out): $rb -> $headRef"
        $skippedLocked++
        continue
    }

    $slug = ConvertTo-Slug $tail
    $dest = Join-Path $worktreesRoot $slug

    if (Test-Path $dest) {
        Write-Host "SKIP (folder exists): $dest"
        $skippedExists++
        continue
    }

    Write-Host "ADD worktree: $rb -> $dest"
    git worktree add $dest $rb
    if ($LASTEXITCODE -ne 0) {
        throw "git worktree add failed for $rb"
    }
    $added++

    # New checkout creates refs/heads/<tail>; avoid attempting duplicate slug for same session if Git reused lock (normally unique branch names).
    $locked[$headRef] = $true
}

Write-Host ""
Write-Host "Done. Added: $added | Skipped (locked): $skippedLocked | Skipped (folder exists): $skippedExists"
