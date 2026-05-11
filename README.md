# UniServe

**UniServe** est le portail des services universitaires : un seul site pour les **étudiants et enseignants** (espace principal) et le **personnel** (gestion). L’idée est de centraliser demandes, rendez-vous, documents, vie associative (clubs / événements) et outils d’aide, sans jongler entre une demi-douzaine d’outils.

## Ce que tu y fais

- **Compte** : connexion par email, profil, et récupération de mot de passe par code (email) quand c’est configuré côté serveur.
- **Demandes** : ouvrir et suivre des demandes de service (scolarité, logement, autre), avec statuts visibles ; le staff les traite et les assigne.
- **Rendez-vous** : réserver des créneaux auprès des bureaux, sans chevauchement sur le même poste.
- **Documents** : demander des documents académiques (attestations, relevés, etc.) et suivre le traitement.
- **Clubs & événements** : parcourir les clubs, proposer un club ou un événement, s’inscrire aux activités, avec validation côté administration quand c’est nécessaire.
- **Agenda** : calendrier personnel sur le tableau de bord (rendez-vous + événements) et **brief de semaine** (résumé des priorités / actions) via **Groq** lorsque `GROQ_API_KEY` est renseignée et `CALENDAR_BRIEF_AI_ENABLED` reste actif.
- **Assistant** : petit panneau d’aide (questions / orientation) via **Groq** lorsque `GROQ_API_KEY` est configurée dans `.env`.

En bref : **tout ce qui concerne les démarches, le calendrier et la vie asso** au même endroit, avec des vues adaptées au rôle (membre de la promo vs équipe admin).
