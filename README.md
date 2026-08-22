# SMS2 Capstone — CRAD Modules

This repository contains the SMS2 Capstone system with the completed CRAD first- and second-semester workflow.

## Quick setup on XAMPP

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

The installers use the safe schema-only files `database/sms2_schema.sql` and `modules/crad/database/crad_schema.sql`. Live account records, activity logs, uploaded manuscripts, signatures, and local database passwords are intentionally excluded from GitHub.

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

