# SMS2 Capstone — CRAD Modules

This repository contains the SMS2 Capstone system with the completed CRAD first- and second-semester workflow.

## Quick setup on XAMPP

### Option A — transferred SMS2 + CRAD data (recommended for group testing)

1. Download the repository through **Code → Download ZIP**, or clone it inside `C:\xampp\htdocs`.
2. Start **Apache** and **MySQL** in XAMPP.
3. Open phpMyAdmin and import these two files in this order:

   1. `database/sms2_db.sql`
   2. `modules/crad/database/crad_db.sql`

   The files create and select the correct databases automatically: `sms2_db` and `crad_db`.
4. Copy `config/local.example.php` to `config/local.php` only when the MySQL host, port, username, or password is different from the XAMPP defaults.
5. Open `http://localhost/<project-folder>/` in a browser.

Do not run the clean schema installers after importing these two databases. The transfer exports already contain the complete table structures, user accounts, roles, and sanitized CRAD records.

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

The clean installers use `database/sms2_schema.sql` and `modules/crad/database/crad_schema.sql`. The Option A exports include sanitized classroom accounts and CRAD workflow records. Login/security logs, reset tokens, authenticators, passkeys, raw signatures, local database passwords, and uploaded manuscript files are intentionally excluded.

## Main CRAD test accounts

| Portal | Username | Password |
| --- | --- | --- |
| Super Admin | `superadmin` | `@superadmin123` |
| System Admin | `admin` | `@admin123` |
| Student | `s230000001` | `@student123` |
| Adviser | `rsantos` | `@faculty123` |
| Research Coordinator | `researchcoordinator` | `@research123` |
| Research Director | `researchdirector` | `@faculty123` |
| CRAD Officer | `cradofficer` | `@cradofficer123` |
| Research Grant Officer | `researchgrant` | `@researchgrant123` |
| Review Committee | `reviewcommitee` | `@review123` |
| Grammarian | `grammarian` | `@grammarian123` |
| Panel 1 | `jobert.valentino` | `@panel123` |
| Panel 2 | `jonathan.estrada` | `@panel123` |
| Panel 3 | `michelle.guevarra` | `@panel123` |
| Finance | `finance` | `@finance123` |

The login accepts either the username above or the account email. Copy the password exactly, including the leading `@`.

### If imported accounts say “Incorrect password”

From the project folder, run this once while XAMPP MySQL is started:

```powershell
C:\xampp\php\php.exe database\seed_accounts.php
```

This restores the classroom account passwords, clears login lockouts, and repairs their role permissions without deleting CRAD workflow records. Then refresh the login page and use the account table above.

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
- The repository includes the sanitized `sms2_db.sql` and `crad_db.sql` transfer exports. Private security history, credentials, signature images, machine-specific paths, and uploaded document binaries remain excluded.
