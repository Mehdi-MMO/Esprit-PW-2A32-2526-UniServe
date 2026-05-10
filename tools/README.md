# Tools

## [`sync-upstream-reference.ps1`](sync-upstream-reference.ps1)

Shallow-clones the canonical UniServe repo into `modules/_upstream/` (see [`modules/README.md`](../modules/README.md)).

## [`checkout-all-remote-branches.ps1`](checkout-all-remote-branches.ps1) / [`checkout-all-remote-branches.bat`](checkout-all-remote-branches.bat)

Materializes **each remote branch** under `origin/` as a separate folder: `worktrees/<slug>/`, after `git fetch --all --prune`.

**Run:** double-click the `.bat`, or from repo root:

```bat
tools\checkout-all-remote-branches.bat
```

**Important:**

- Git allows **only one checkout per branch** across all worktrees. If a branch is already checked out in this repo or another worktree, it is **skipped** (including the branch currently on `HEAD`).
- If `worktrees/<slug>/` already exists, that branch is **skipped** until you remove the folder (and `git worktree remove` if Git still tracks it).
- Each folder is a full working tree; disk usage grows with branch count — `worktrees/` is gitignored.
