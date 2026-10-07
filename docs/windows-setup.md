# Windows setup: PHP, extensions and Composer

Use this guide before Lesson 5 if you already have Git and an editor but do not have PHP or Composer. Run commands in **PowerShell** (Windows Terminal or your editor's PowerShell terminal), one block at a time. Stop and fix any error before continuing.

This project uses Laravel 13, **PHP 8.3 or newer**, Composer 2 and SQLite. For this lesson, install the latest **PHP 8.3 patch release** to match the PHP version in the practice workflow. You do not need XAMPP, Apache, MySQL, Node.js or a separate SQLite installer.

## 1. Download PHP for Windows

These steps are for a **64-bit Intel/AMD Windows 10 or 11 PC**. Check **Settings → System → About → System type**. If you have an ARM-based, 32-bit or older Windows PC, ask the instructor for help before choosing a download.

1. Install Microsoft's [latest Visual C++ Redistributable, x64](https://learn.microsoft.com/en-us/cpp/windows/latest-supported-vc-redist). Choose the **X64** download in the latest supported v14 section. If it is already installed, keep it. A school-managed PC may need the lab administrator's help.
2. Open the [official PHP 8.3 Windows downloads](https://www.php.net/downloads.php?version=8.3&os=windows). Under **PHP 8.3 → VS16 x64 Non Thread Safe**, choose **Zip**. Use the newest 8.3 patch offered, not the source code, Debug Pack or development package. NTS is suitable for the command-line tools used here.
3. Extract the **whole ZIP** to `C:\php`. You should have `C:\php\php.exe`, `C:\php\ext`, `C:\php\libsqlite3.dll` and `C:\php\php.ini-development`. Do not leave an extra version-named folder between `C:\php` and `php.exe`.

If `C:\php` already contains another installation, do not overwrite it. Ask the instructor to help check its version and configuration first.

## 2. Create php.ini and enable extensions

For this new installation, run:

```powershell
Copy-Item C:\php\php.ini-development C:\php\php.ini
notepad C:\php\php.ini
```

In `php.ini`, find the existing lines below. Remove the leading `;` to enable each setting, and change `extension_dir` to the full path shown. A line starting with `;` is a comment and is ignored. Edit the existing lines rather than adding duplicates.

```ini
extension_dir = "C:\php\ext"
extension=curl
extension=fileinfo
extension=mbstring
extension=openssl
extension=pdo_sqlite
extension=sqlite3
extension=zip
```

Save the file as **php.ini**, not `php.ini.txt`. File Explorer's **View → Show → File name extensions** can help you check.

`pdo_sqlite` lets Laravel use SQLite; `sqlite3` enables PHP's SQLite API. `zip` lets Composer unpack downloads. The other enabled extensions support Laravel and secure dependency downloads. Keep the DLLs from the same PHP ZIP together; do not download replacement DLLs from unrelated sites or copy them into Windows system folders.

The official PHP build already includes modules such as `ctype`, `dom`, `filter`, `hash`, `iconv`, `json`, `libxml`, `pcre`, `PDO`, `Phar`, `session`, `tokenizer`, `xml` and `xmlwriter`. Do not add `extension=dom`, `extension=json` or similar lines for built-in modules.

## 3. Add PHP to PATH and check it

PATH tells Windows where to find a command such as `php`.

1. Search the Start menu for **Edit environment variables for your account**.
2. Under **User variables**, select **Path → Edit → New** and add `C:\php` (the folder, not `php.exe`). Keep all existing entries and press **OK** to close the dialogs.
3. Close and reopen PowerShell. If using VS Code, close **all VS Code windows** and reopen it too so its terminal receives the new PATH.

Run:

```powershell
where.exe php
php -v
php --ini
php -m
php -r "print_r(PDO::getAvailableDrivers());"
```

Check that:

- The first `where.exe php` result is `C:\php\php.exe`.
- `php -v` shows **PHP 8.3.x**, with no startup warnings.
- `php --ini` shows **Loaded Configuration File: C:\php\php.ini**.
- `php -m` includes `curl`, `fileinfo`, `mbstring`, `openssl`, `pdo_sqlite`, `sqlite3`, `zip`, plus the built-in modules listed above.
- The final command lists **sqlite** as an available PDO driver.

If you edit `php.ini` later, the next PHP command reads the new settings. Stop and restart `php artisan serve` if it is already running.

## 4. Install Composer

1. Open the [official Composer Windows installation instructions](https://getcomposer.org/doc/00-intro.md#installation-windows) and download **Composer-Setup.exe** from that page.
2. Run the installer with the normal installation settings. When it asks for the command-line PHP executable, select **C:\php\php.exe**. Do not select PHP from an older XAMPP or other installation. Leave proxy settings empty unless your school requires a proxy.
3. Finish the installation. Composer's installer adds its command to PATH. Close and reopen PowerShell and, if used, all VS Code windows again.

```powershell
composer --version
where.exe composer
php -v
```

You should see **Composer version 2.x**, a Composer command path, and **PHP 8.3.x**. If Composer's version output includes a PHP path, it should point to `C:\php\php.exe`.

## 5. Set up your fork and run the tests

First complete [Lesson 5, step 1: Fork and clone](LESSON5_PRACTICE.md#1-fork-and-clone), then return here. Its Git commands also work in PowerShell. Use your **own fork** for the lesson.

In PowerShell, open your cloned `laravel-student-management-ci` folder. It must contain `artisan` and `composer.json`. Run the following for a fresh clone; if `.env` or `.env.testing` already exists, keep it and skip that file's copy command.

```powershell
composer install
composer check-platform-reqs
Copy-Item .env.example .env
Copy-Item .env.testing.example .env.testing
php artisan key:generate
php artisan key:generate --env=testing
composer test
```

Expected baseline: **17 tests pass** before the instructor publishes the deliberate regression. `composer test` clears cached configuration and runs the tests. Use `composer install` so everyone uses the committed lockfile, including test dependencies. Do not use `composer update`, `--no-dev` or `--ignore-platform-reqs` to work around setup errors.

The templates already configure SQLite. Keep `DB_CONNECTION=sqlite` in both files and `DB_DATABASE=:memory:` in `.env.testing`. Tests build their own temporary in-memory database; you do not need migrations, a database server or `php artisan serve` for the test run. Keep `phpunit.xml` and the supplied tests unchanged.

## 6. Optional: run the API in your browser

For the local API, `.env.example` leaves `DB_DATABASE` unset, so Laravel uses `database/database.sqlite`. Create that file only if it does not exist, then migrate and seed it:

```powershell
if (-not (Test-Path database/database.sqlite)) {
    New-Item -ItemType File -Path database/database.sqlite | Out-Null
}
php artisan migrate --seed
php artisan serve
```

Open <http://127.0.0.1:8000/api/students>. You should see JSON containing three fictional students on a fresh database. Keep this terminal open while using the API; press **Ctrl+C** to stop it. Use a second terminal in the project folder for tests or Git commands. To try requests from PowerShell, you can also use `Invoke-RestMethod http://127.0.0.1:8000/api/students`; the README's multi-line `curl` examples are for Bash.

## Quick fixes

| Problem | What to check |
|---|---|
| `php` or `composer` is not recognized | Reopen PowerShell and the editor after installation. Check PATH and use `where.exe php` / `where.exe composer`. |
| PHP is older than 8.3, or a different PHP runs | `where.exe php` lists competing installations. Ask the instructor to help make `C:\php\php.exe` the first match, then reopen the terminal. |
| `VCRUNTIME140.dll` or `MSVCP140.dll` is missing | Install/repair Microsoft's x64 Visual C++ Redistributable from step 1. |
| `Loaded Configuration File: (none)` | Check that `C:\php\php.ini` exists and is not named `php.ini.txt`; rerun `php --ini`. |
| `Unable to load dynamic library` | Check the loaded `php.ini`, the absolute `extension_dir`, and that the complete matching ZIP was extracted. `libsqlite3.dll` stays beside `php.exe`; `C:\php` must be on PATH. |
| `could not find driver` / missing `pdo_sqlite` | Enable both SQLite lines in the loaded `php.ini`; rerun `php -m` and the PDO-driver check. |
| Composer reports missing `ext-...`, or cannot unpack ZIPs | Enable the named extension (including `zip` for unpacking); run `composer install` again, then `composer check-platform-reqs`. |
| Composer reports a certificate/TLS error | Check the PC's date/time and ask the instructor or school IT about certificate/proxy setup. Do not disable TLS or certificate verification. |
| `Could not open input file: artisan` / no `composer.json` | Open PowerShell in the cloned project folder, not Downloads or `C:\php`. |
| `vendor/autoload.php` is missing | `composer install` did not finish successfully; fix its first error and retry. |
| Missing application encryption key | Check that both environment files were copied and run both `key:generate` commands from step 5. |
| SQLite file/table is missing when opening the API | Complete step 6 in the project folder. Tests use a separate in-memory database. |
| Port 8000 is already in use | Run `php artisan serve --port=8001` and open `http://127.0.0.1:8001/api/students`. |

## Ready for Lesson 5

- [ ] PHP 8.3.x and Composer 2.x are available in a newly opened terminal.
- [ ] The extension and PDO SQLite checks pass without startup warnings.
- [ ] `composer install` and `composer check-platform-reqs` finish successfully.
- [ ] `composer test` reports 17 passing tests on the clean starter.

Return to [Lesson 5, step 3: Write your own workflow](LESSON5_PRACTICE.md#3-write-your-own-workflow). The starter intentionally has no CI workflow; you will create it in your fork. Never commit `.env`, `.env.testing`, application keys, `vendor/` or SQLite database files.

Official references: [PHP Windows installation](https://www.php.net/manual/en/install.windows.manual.php), [enabling PHP extensions](https://www.php.net/manual/en/install.pecl.windows.php), [SQLite on Windows](https://www.php.net/manual/en/sqlite3.installation.php), [Laravel 13 requirements](https://laravel.com/docs/13.x/deployment#server-requirements), and [Composer for Windows](https://getcomposer.org/doc/00-intro.md#installation-windows).
