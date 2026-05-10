# Esprit-PW-2A32-2526-UniServe

Plateforme PHP MVC unique : authentification, **demandes de service**, **rendez-vous**, **documents académiques**, **événements et clubs**, gestion **utilisateurs** (back-office).

## Prérequis

- PHP 8+ avec extensions PDO MySQL
- MySQL / MariaDB
- Serveur web (Apache avec `mod_rewrite` recommandé pour les URLs propres)

## Installation rapide

1. Cloner le dépôt et placer le dossier dans la racine web (ex. `htdocs/INTEG`).
2. Créer la base et importer le schéma :
   - Créer une base `uniserve` (ou adapter le nom).
   - Importer [`uniserve.sql`](uniserve.sql).
3. Variables d’environnement (optionnel, sinon valeurs par défaut dans [`config.php`](config.php)) :
   - `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`
4. Ouvrir l’application via `index.php` (ex. `http://localhost/INTEG/`).

Comptes de démo : voir les `INSERT` dans `uniserve.sql` (emails `@uniserve.net`).

## Structure intégrée

| Zone | Rôle |
|------|------|
| [`Controller/`](Controller/) | Contrôleurs par segment d’URL (`App.php` route le premier segment). |
| [`Model/`](Model/) | Accès données (`User`, `Event`, `Club`, `Model` générique). |
| [`View/`](View/) | Vues front-office / back-office / landing + [`View/shared/`](View/shared/) (CSS, JS). |
| [`modules/`](modules/) | Documentation d’intégration et manifeste ; clones de référence optionnels (`modules/_upstream/`, gitignored). |
| [`tools/`](tools/) | Scripts utilitaires (sync upstream, worktrees par branche). |

Les dossiers `worktrees/` et `THEMODULES/` sont ignorés par Git : copies locales / autres branches, pas le runtime.

## Branche `main`

Le développement intégré est sur **`main`** : une seule arborescence MVC à la racine, schéma unique `uniserve.sql`.
