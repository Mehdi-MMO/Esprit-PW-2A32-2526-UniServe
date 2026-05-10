# Esprit-PW-2A32-2526-UniServe

Plateforme PHP MVC unique : authentification, **demandes de service**, **rendez-vous**, **documents académiques**, **événements et clubs**, gestion **utilisateurs** (back-office).

## Arborescence du dépôt

À la racine du projet, seuls trois dossiers applicatifs :

| Dossier | Rôle |
|---------|------|
| [`Controller/`](Controller/) | Routage (`App.php`), contrôleurs par segment d’URL. |
| [`Model/`](Model/) | PDO ([`Database.php`](Model/Database.php)), modèles, [`ValidationService.php`](Model/ValidationService.php) ; scripts SQL additifs optionnels sous [`Model/schema/`](Model/schema/) (migrations). |
| [`View/`](View/) | Vues et assets partagés [`View/shared/`](View/shared/). |

Fichiers à la racine (hors des trois dossiers) : **`index.php`**, **`.htaccess`**, **`README.md`**, et le dump **[`uniserve_full.sql`](uniserve_full.sql)** (base complète + données de démo).

La connexion MySQL est configurée dans [`Model/Database.php`](Model/Database.php) (variables d’environnement `DB_*`).

## Prérequis

- PHP 8+ avec extensions PDO MySQL
- MySQL / MariaDB
- Serveur web (Apache avec `mod_rewrite` recommandé pour les URLs propres)

## Installation rapide

1. Cloner le dépôt et placer le dossier dans la racine web (ex. `htdocs/INTEG`).
2. Créer / réinitialiser la base et importer le dump complet :
   - Importer [`uniserve_full.sql`](uniserve_full.sql) dans MySQL / MariaDB (le script crée la base `uniserve` si besoin, supprime les tables existantes dans l’ordre, puis recrée schéma + jeux de données).
   - Ou créer une base vide nommée comme `DB_NAME` puis n’y importer que le fichier (adapter `DB_NAME` dans l’environnement si le nom diffère de `uniserve`).
3. Variables d’environnement (optionnel ; défauts dans [`Model/Database.php`](Model/Database.php)) :
   - `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`
4. Ouvrir l’application via `index.php` (ex. `http://localhost/INTEG/`).

Comptes et données de démo : voir les `INSERT` dans [`uniserve_full.sql`](uniserve_full.sql) (ex. admin `admin.uniserve@gmail.com`, étudiant de test, etc.).

## Schéma et migrations

- Dump canonique à la racine : **[`uniserve_full.sql`](uniserve_full.sql)** (schéma aligné avec les modèles PHP, clubs avec `cree_par` / `statut_validation`, tables auth optionnelles issues du dump Heidi, données de démo).
- Le fichier [`Model/schema/uniserve.sql`](Model/schema/uniserve.sql) renvoie vers ce dump ; ne plus dupliquer le schéma complet à cet emplacement.
- Évolutions ultérieures : scripts SQL **additifs** sous `Model/schema/migrations/`, par exemple `001_description.sql`, appliqués dans l’ordre sur une base déjà importée.

### Vérification rapide après import

1. Importer `uniserve_full.sql` sur une base de test (ou recréer `uniserve`).
2. Vérifier [`Model/Database.php`](Model/Database.php) (`DB_*`) puis ouvrir l’app (`index.php`).
3. Connexion : utiliser un compte présent dans les `INSERT` du dump ; parcourir accueil, liste clubs / événements si les routes sont activées.

## Développement Git (optionnel)

- Pour une copie de référence du dépôt distant : `git clone --depth 1 https://github.com/Mehdi-MMO/Esprit-PW-2A32-2526-UniServe.git` dans un dossier local hors dépôt si besoin.
- Pour matérialiser chaque branche distante dans des dossiers séparés : utiliser des **git worktrees** manuellement (`git worktree add …`), pas requis pour faire tourner l’application.

Les dossiers locaux **`worktrees/`**, **`THEMODULES/`** ou autres copies d’équipe peuvent être présents sur votre machine mais ne font pas partie du runtime MVC ; ajoutez-les au `.gitignore` si nécessaire.

## Branche `main`

Le développement intégré suit **`main`** : une seule arborescence **Controller / Model / View** et un dump unique **`uniserve_full.sql`** à la racine.
