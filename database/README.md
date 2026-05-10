# Database schema

The canonical bootstrap script for a fresh environment is [`../uniserve.sql`](../uniserve.sql) at the repository root. It defines all integrated modules (utilisateurs, demandes de service, rendez-vous, documents, événements/clubs).

## Migrations

When you add columns or tables after the initial import, create an additive script under `database/migrations/` using a zero-padded prefix, for example:

`database/migrations/001_add_example_column.sql`

Apply migrations in order on top of an existing database. There is no duplicate delta versus external modules in-tree: all runtime code targets the `uniserve` schema in `uniserve.sql`.
