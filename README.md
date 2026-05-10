# Esprit-PW-2A32-2526-UniServe

Plateforme PHP MVC unique : authentification, **demandes de service**, **rendez-vous**, **documents académiques**, **événements et clubs**, gestion **utilisateurs** (back-office).

## Arborescence du dépôt

À la racine du projet, seuls trois dossiers applicatifs :

| Dossier | Rôle |
|---------|------|
| [`Controller/`](Controller/) | Routage (`App.php`), contrôleurs par segment d’URL. |
| [`Model/`](Model/) | PDO (`Database.php`), modèles, [`ValidationService.php`](Model/ValidationService.php), schéma SQL sous [`Model/schema/`](Model/schema/). |
| [`View/`](View/) | Vues et assets partagés [`View/shared/`](View/shared/). |

Fichiers de bootstrap à la racine : **`index.php`**, **`.htaccess`**, **`README.md`** (ce fichier).

La connexion MySQL est configurée dans [`Model/Database.php`](Model/Database.php) (variables d’environnement `DB_*`).

## Prérequis

- PHP 8+ avec extensions PDO MySQL
- MySQL / MariaDB
- Serveur web (Apache avec `mod_rewrite` recommandé pour les URLs propres)

## Installation rapide

1. Cloner le dépôt et placer le dossier dans la racine web (ex. `htdocs/INTEG`).
2. Créer la base et importer le schéma :
   - Créer une base `uniserve` (ou adapter le nom selon `DB_NAME`).
   - Importer [`Model/schema/uniserve.sql`](Model/schema/uniserve.sql).
3. Variables d’environnement (optionnel ; défauts dans [`Model/Database.php`](Model/Database.php)) :
   - `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`
4. Ouvrir l’application via `index.php` (ex. `http://localhost/INTEG/`).

Comptes de démo : voir les `INSERT` dans `Model/schema/uniserve.sql` (emails `@uniserve.net`).

## Schéma et migrations

- Script canonique : **`Model/schema/uniserve.sql`** (tous les modules : utilisateurs, demandes, rendez-vous, documents, événements/clubs).
- Pour des évolutions après coup, ajouter des scripts SQL **additifs** sous `Model/schema/migrations/`, par exemple `001_description.sql`, et les appliquer dans l’ordre sur une base existante.

## Développement Git (optionnel)

- Pour une copie de référence du dépôt distant : `git clone --depth 1 https://github.com/Mehdi-MMO/Esprit-PW-2A32-2526-UniServe.git` dans un dossier local hors dépôt si besoin.
- Pour matérialiser chaque branche distante dans des dossiers séparés : utiliser des **git worktrees** manuellement (`git worktree add …`), pas requis pour faire tourner l’application.

Les dossiers locaux **`worktrees/`**, **`THEMODULES/`** ou autres copies d’équipe peuvent être présents sur votre machine mais ne font pas partie du runtime MVC ; ajoutez-les au `.gitignore` si nécessaire.

## Branche `main`

Le développement intégré suit **`main`** : une seule arborescence **Controller / Model / View** et un schéma dans **`Model/schema/uniserve.sql`**.
