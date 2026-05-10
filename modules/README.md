# Module imports (multi-repo integration)

This folder holds **provenance and upstream snapshots** for code merged into the main UniServe MVC tree (`Controller/`, `Model/`, `View/`). The application router in `Controller/App.php` only loads controllers from `Controller/<Name>Controller.php`; it does not auto-discover subfolders here.

## Integrated app on `main`

The runnable application is the **repository root** (`index.php`, `Controller/`, `Model/`, `View/`) with schema [`uniserve.sql`](../uniserve.sql). See [`manifest.json`](manifest.json) for the current `main` commit pin.

## Canonical upstream from Git

There is **one** published Git remote for this course project: `https://github.com/Mehdi-MMO/Esprit-PW-2A32-2526-UniServe.git`. Separate “module-only” repositories were not found under that maintainer account; all features ship in the monolith.

- **Reference clone:** run [`tools/sync-upstream-reference.ps1`](../tools/sync-upstream-reference.ps1). It shallow-clones into `modules/_upstream/Esprit-PW-2A32-2526-UniServe/` (gitignored; refresh anytime).
- **Pinned SHA:** see [`manifest.json`](manifest.json).

To materialize **every remote branch** into separate folders under `worktrees/`, run [`tools/checkout-all-remote-branches.bat`](../tools/checkout-all-remote-branches.bat) (see [`tools/README.md`](../tools/README.md)).

**Chosen wiring:** strategy **A** (pragmatic merge into flat `Controller/`, `Model/`, `View/`). Legacy English URLs `/events/*` and `/clubs/*` remain supported via thin proxy controllers that delegate to `EvenementsController`.

## Import procedure

1. On branch `main`, add a subfolder: `modules/<module_key>/` (e.g. `demandes`, `rendezvous`). Use a short, stable key aligned with the feature URL where possible.
2. Copy or `git subtree add` the external repository into that folder. Record the **remote URL** and **commit SHA** in `modules/<module_key>/INTEGRATION.md`.
3. Merge behavior into the flat runtime MVC (strategy **A** in the integration plan): move or reconcile logic into `Controller/`, `Model/`, and `View/`. Resolve PHP class name clashes (this project does not use namespaces).
4. Remove dead duplicates once routes and tests match; keep `INTEGRATION.md` updated if you trim the snapshot.

## Template

See `_template/INTEGRATION.md` for the fields to fill when importing a new repo.
