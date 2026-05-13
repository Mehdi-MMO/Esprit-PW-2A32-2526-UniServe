# UniServe

**UniServe** est le portail web des services universitaires : un point d’entrée unique pour les **étudiants et enseignants** (espace principal) et le **personnel** (back-office). Les démarches administratives, le calendrier, les documents, la vie associative (clubs et événements) et l’aide en ligne sont regroupés au même endroit.

## Prérequis

- PHP 8.1+ avec extensions PDO MySQL, JSON, mbstring, fileinfo (recommandé).
- MySQL / MariaDB.
- Serveur web avec réécriture d’URL (fichier `.htaccess` fourni à la racine du projet).

## Installation rapide

1. **Base de données** — Créer une base vide puis importer le fichier unique **`db/uniserve_full.sql`** (schéma + données de démonstration).
2. **Configuration** — Copier `.env.example` vers **`.env`** et renseigner au minimum `DB_*` ; pour l’assistant et le brief hebdo, renseigner **`GROQ_API_KEY`** (voir `.env.example`).
3. **Fichiers runtime** — Le serveur web doit pouvoir écrire dans **`Model/storage/`** (cache du brief agenda) et **`Model/uploads/`** (pièces jointes des demandes, fichiers DOCAC, photos de profil). Ces dossiers sont ignorés par Git et créés à la volée si les droits le permettent.

## Routage

Le point d’entrée est **`index.php`** : les URLs du type `/demandes` sont mappées vers `Controller/DemandesController.php` et la méthode homonyme (voir `Controller/App.php`).

## Fonctionnalités principales

- **Compte** — Connexion par e-mail, profil, réinitialisation du mot de passe (OTP par e-mail si SMTP est configuré).
- **Demandes de service** — Dépôt, pièces jointes, suivi des statuts ; traitement côté staff (assignation, modération optionnelle via Groq).
- **Rendez-vous** — Réservation de créneaux par bureau, sans chevauchement côté serveur.
- **Documents** — Demandes de documents académiques et suivi des statuts.
- **Certifications (DOCAC)** — Parcours étudiant / gestion staff, quiz optionnel (Ollama ou Groq selon configuration).
- **Clubs et événements** — Clubs, propositions, inscriptions, validation administrative si nécessaire.
- **Agenda** — Calendrier sur le tableau de bord (rendez-vous et événements) et **brief de semaine** via Groq lorsque la clé API et `CALENDAR_BRIEF_AI_ENABLED` sont actifs.
- **Assistant** — Panneau d’aide (Groq) lorsque `GROQ_API_KEY` est défini.
- **Notifications** — Notifications in-app liées aux modules (demandes, certifications, etc.).

Les vues et parcours s’adaptent au **rôle** (étudiant, enseignant, staff, administrateur).
