# UniServe

UniServe est une plateforme web de services universitaires qui centralise les principaux parcours du portail dans une seule application : compte utilisateur, démarches administratives, rendez-vous, documents, certifications, clubs, événements, calendrier, notifications et assistance intelligente.

L’application s’adresse aux étudiants, enseignants et membres du personnel via des interfaces adaptées aux rôles.

## Aperçu

- Application PHP classique, sans framework externe.
- Entrée unique via `index.php`.
- Routage basé sur les contrôleurs du dossier `Controller/`.
- Base de données MySQL/MariaDB fournie dans `db/uniserve_full.sql`.
- Configuration par variables d’environnement chargées depuis `.env`.

## Prérequis

- PHP 8.1 ou supérieur.
- MySQL ou MariaDB.
- Serveur web Apache recommandé, avec réécriture d’URL activée.
- Extensions PHP utiles : PDO MySQL, JSON, mbstring et fileinfo.

## Installation

1. Créez une base de données vide.
2. Importez `db/uniserve_full.sql` pour installer le schéma et les données de démonstration.
3. Copiez `.env.example` vers `.env`.
4. Renseignez au minimum les paramètres `DB_*` dans `.env`.
5. Vérifiez que le serveur web peut écrire dans les dossiers runtime, notamment `Model/storage/` et `Model/uploads/`.

Si vous utilisez XAMPP ou un environnement similaire, vérifiez aussi le port MySQL défini dans `.env` (`DB_PORT=3307` par défaut).

## Configuration

Le fichier `.env.example` contient les paramètres principaux du projet.

- Base de données : `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`.
- E-mail : `SMTP_HOST`, `SMTP_PORT`, `SMTP_SECURE`, `SMTP_USER`, `SMTP_PASS`, `SMTP_FROM`, `SMTP_FROM_NAME`.
- IA / assistant : `GROQ_API_KEY`, `GROQ_MODEL`, `CALENDAR_BRIEF_AI_ENABLED`.
- Sécurité de connexion : `LOGIN_RISK_AI_ENABLED`, `LOGIN_RISK_STEP_UP_ENABLED`, `LOGIN_RISK_MEDIUM_THRESHOLD`, `LOGIN_RISK_HIGH_THRESHOLD`.
- Démarches de service : `DEMANDE_AI_DESCRIPTION_ENABLED`, `DEMANDE_GROQ_MODERATION_ENABLED`, `DEMANDE_STAFF_AI_CHECK_ENABLED`.
- Certifications DOCAC : `DOCAC_QUIZ_AI`, `OLLAMA_URL`, `OLLAMA_MODEL`.
- Inscriptions : `INSTITUTIONAL_EMAIL_DOMAINS`.

La clé `GROQ_API_KEY` est requise uniquement pour les fonctionnalités qui utilisent Groq, comme l’assistant, certaines aides à la rédaction, la modération et le brief de calendrier.

## Démarrage

Le point d’entrée de l’application est `index.php`. Les URL propres sont redirigées vers ce fichier via `.htaccess`, puis résolues par le routeur interne.

Exemple : une URL comme `/demandes` est associée au contrôleur correspondant dans `Controller/DemandesController.php`.

## Fonctionnalités

- Authentification par e-mail, profil utilisateur et réinitialisation de mot de passe.
- Gestion des demandes de service avec pièces jointes, suivi des statuts et prise en charge côté personnel.
- Réservation de rendez-vous par bureau, avec contrôle des chevauchements.
- Gestion des demandes de documents académiques.
- Parcours DOCAC et quiz associés, avec option IA selon la configuration.
- Gestion des clubs et des événements.
- Tableau de bord avec calendrier et synthèse hebdomadaire.
- Notifications in-app.
- Panneau d’assistance intelligent lorsque l’API Groq est activée.

## Structure du projet

- `Controller/` : contrôleurs de l’application.
- `Model/` : logique métier, accès aux données et services.
- `View/` : templates et vues frontoffice/backoffice.
- `db/` : export SQL principal du projet.

## Notes d’exploitation

- L’application démarre depuis `index.php` après chargement de la configuration base de données.
- Les dossiers de cache et d’uploads doivent rester inscriptibles par le serveur web.
- Certaines fonctionnalités peuvent fonctionner en mode dégradé si SMTP, Groq ou Ollama ne sont pas configurés.

## Licence

Aucune licence explicite n’est fournie dans ce dépôt. Ajoutez-en une si le projet doit être distribué ou partagé en dehors de l’équipe actuelle.
