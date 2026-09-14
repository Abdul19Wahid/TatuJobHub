# Running TatuJobHub locally — what was fixed and how to run it

## What was broken

You were starting the app with:

```
php -S localhost:8000
```

PHP's built-in server **ignores `.htaccess`** entirely, so the clean-URL
rewriting (`/login`, `/jobs/{slug}`, dashboards, etc.) that the app depends
on never happened — only the homepage worked. On top of that,
`config/app.php` had a bug where it would silently force `BASE_URL` to
`/config/public` any time it couldn't detect a script path — which is
exactly the situation `php -S` creates, so even fixed routing would have
produced broken links.

## What changed

- `core/bootstrap.php` — new shared boot logic used by both entry points,
  so they can't drift out of sync again.
- `public/index.php` and `config/public/index.php` — now thin wrappers
  around `core/bootstrap.php`.
- `config/app.php` — `BASE_URL` detection no longer guesses
  `/config/public` by default; it trusts an explicit `APP_BASE_PATH` set
  by whichever entry point is running, and otherwise uses the real
  (possibly empty) script directory.
- `public/router.php` — new, optional. Lets you use `php -S` correctly if
  you ever want to, by manually replicating the `.htaccess` rewrite.
- Removed stray empty folders left over from a shell brace-expansion typo
  (e.g. `app/{controllers,models,...}`), and stray debug/log files
  (`tmp_baseurl_test.php`, `public/server.log`).

## Option A (recommended) — XAMPP / Apache

This is what the project is built for, and what `SetupController.php`'s
own error messages assume.

1. Make sure this folder lives at `C:\xampp\htdocs\job-portal`.
2. Start **Apache** and **MySQL** from the XAMPP Control Panel.
3. Create a database named `job_portal` in phpMyAdmin and import
   `database/schema.sql` (plus the migration files if you need those
   features).
4. Visit **http://localhost/job-portal/public/**.

## Option B — PHP's built-in server, done correctly

If you don't want to use XAMPP:

```
cd job-portal/public
php -S localhost:8000 router.php
```

Then visit **http://localhost:8000/**. `router.php` manually replicates
the `.htaccess` rewrite that the built-in server otherwise skips, and
tells the app it's being served from the domain root.

Don't run `php -S localhost:8000` without `router.php` — that's the
original problem.

## Database setup — import order matters

In phpMyAdmin, on a fresh `job_portal` database, import these **in this
exact order** (SQL tab → paste each file's contents → Go, one at a time):

1. `database/schema.sql`
2. `database/job-alerts-migration.sql`
3. `database/google-oauth-migration.sql`
4. `database/messaging-migration.sql`
5. `database/application-status-tracking-migration.sql`
6. `database/employer-reviews-migration.sql`

All six now import cleanly with zero errors, verified by actually running
them start to finish (not just reading the SQL).

### Two more bugs fixed in this pass

- **`schema.sql`'s seeded demo accounts had a broken password hash.** The
  same bcrypt hash was copy-pasted for all three demo users
  (`admin@jobportal.com`, `kwame@example.com`, `ama@techcorp.com`), but it
  didn't actually match *any* of the three documented passwords below —
  nobody could log into any seeded account. Regenerated a correct,
  distinct hash per account.
- **`job-alerts-migration.sql` failed on fresh installs** with "Duplicate
  column name 'is_remote'" — `schema.sql` already had those columns
  baked in from an earlier session, so this migration was trying to
  re-add them. Trimmed it down to just the one thing it was still
  missing: the `idx_ja_token` unique index.
- **`messaging-migration.sql`'s last line failed** with a SQL syntax
  error — `rows` is a reserved word in MariaDB, used unquoted as a
  column alias. Table creation above it succeeded fine either way; this
  was cosmetic but still worth fixing. Renamed to `row_count`.
- **`schema.sql` couldn't be safely re-run.** Its own DROP list at the
  top was missing `seeker_education` and `seeker_work_experience`, and
  its three stored procedures had no `DROP PROCEDURE IF EXISTS` at all
  — so resetting the database by re-running `schema.sql` on top of an
  existing one (a normal thing to do while testing) would fail partway
  through with "already exists" errors. Added the missing drops for
  both. Verified by actually running `schema.sql` twice in a row.

### Demo login credentials (now working)

| Role     | Email                  | Password        |
|----------|-------------------------|------------------|
| Admin    | admin@jobportal.com     | Admin@1234       |
| Seeker   | kwame@example.com       | Seeker@1234      |
| Employer | ama@techcorp.com        | Employer@1234    |

## What was verified by actually running the app (not just reading code)

- PHP syntax-checked every `.php` file in the project — zero errors.
- Confirmed every route in `routes.php` resolves to a real controller
  class and method (109 checked).
- Confirmed every view and layout a controller renders exists on disk
  (41 views, 4 layouts).
- Ran the app on a real PHP + MySQL server, logged in as all three
  roles, and loaded every page in every dashboard (seeker, employer,
  admin) — all returned 200 OK, no fatal errors, no missing tables.
- Exercised the application-status-update flow end-to-end (the earlier
  `status_note` bug): submitted a real status change as the employer and
  confirmed it landed correctly in both `applications` and the
  `application_status_logs` audit table.
