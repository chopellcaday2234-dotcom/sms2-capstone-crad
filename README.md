# SMS2 Capstone — CRAD Modules

This repository contains the SMS2 Capstone system with the completed CRAD first- and second-semester workflow.

## Quick setup on XAMPP

### Option A — transferred CRAD demo data (recommended for group testing)

1. Download the repository through **Code → Download ZIP**, or clone it inside `C:\xampp\htdocs`.
2. Start **Apache** and **MySQL** in XAMPP.
3. Open phpMyAdmin and import these two files in this order:

   1. `database/sms2_demo_database.sql`
   2. `modules/crad/database/crad_demo_database.sql`

   The files create and select the correct databases automatically: `sms2_db` and `crad_db`.
4. Copy `config/local.example.php` to `config/local.php` only when the MySQL host, port, username, or password is different from the XAMPP defaults.
5. Open `http://localhost/<project-folder>/` in a browser.

Do not run the clean schema installers after importing the demo databases. The demo exports already contain the complete table structures and transferred records.

### Option B — clean database without transferred records

1. Download the repository through **Code → Download ZIP**, or clone it inside `C:\xampp\htdocs`.
2. Start **Apache** and **MySQL** in XAMPP.
3. Copy `config/local.example.php` to `config/local.php` and update the database settings only when your XAMPP setup is different from the defaults.
4. Open a terminal in the project folder and run:

   ```powershell
   C:\xampp\php\php.exe database\install.php
   C:\xampp\php\php.exe modules\crad\database\install.php
   C:\xampp\php\php.exe database\seed_accounts.php
   C:\xampp\php\php.exe database\seed_panel_members.php
   ```

5. Open `http://localhost/<project-folder>/` in a browser.

The clean installers use `database/sms2_schema.sql` and `modules/crad/database/crad_schema.sql`. The Option A exports include sanitized classroom/demo accounts and CRAD workflow records. Login/security logs, reset tokens, authenticators, passkeys, raw signatures, local database passwords, and uploaded manuscript files are intentionally excluded.

## Main CRAD demo accounts

| Portal | Username | Password |
| --- | --- | --- |
| Student | `s230000001` | `@student123` |
| Adviser | `rsantos` | `@faculty123` |
| Research Coordinator | `researchcoordinator` | `@research123` |
| Research Director | `researchdirector` | `@faculty123` |
| CRAD Officer | `cradofficer` | `@cradofficer123` |
| Grammarian | `grammarian` | `@grammarian123` |
| Panel 1 | `jobert.valentino` | `@panel123` |
| Panel 2 | `jonathan.estrada` | `@panel123` |
| Panel 3 | `michelle.guevarra` | `@panel123` |
| System Admin | `admin` | `@admin123` |

## CRAD second-semester flow

1. Semester carry-over and start final documentation
2. Student submits the consolidated Chapters 1–5 draft
3. Adviser reviews and endorses the final draft
4. CRAD completes the Final Defense readiness check
5. Adviser submits the Final Defense recommendation
6. Student submits the official Chapters 1–5 manuscript
7. CRAD evaluates and approves the official manuscript
8. Research Director proposes and finalizes the Final Defense schedule
9. Three panel members submit Final Defense evaluations
10. CRAD completes Final Manuscript Approval

The completed state is **CAPSTONE ACADEMIC PROCESS COMPLETED**.

## Important

- The seed scripts are for local development and classroom demonstration only. Change all seeded passwords before deploying the system anywhere public.
- Keep `config/local.php` private.
- Do not commit files inside `storage/uploads`, `storage/keys`, or `storage/backups`.
- The repository contains source code and clean database schemas, not the current laptop's live database records.
