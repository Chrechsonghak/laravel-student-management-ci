# Lesson 5 — individual Laravel CI practice

Follow the [Lesson 5 Practice Session slides](https://docs.google.com/presentation/d/1h1yP-l7Ai2SxtNX0Fq4tzq1g4h5qcoGm8vMUNMZhB6I/edit). Suggested time: 90 minutes. Each student uses **one personal fork**, writes their own workflow, and reviews their own runs.

**Windows students without PHP or Composer:** complete steps 1–4 of the [Windows setup guide](windows-setup.md) before the session. It shows the downloads, `php.ini` extension settings, PATH setup and version checks. Git and your existing editor are enough for the other tools.

## 1. Fork and clone

On [the starter repository](https://github.com/maohieng/laravel-student-management-ci), choose **Fork**, select your personal account, and create your fork. Keep the repository name and the `main` branch. Open your fork's **Actions** tab and enable workflows if prompted. The starter includes application code and tests, but intentionally has no active CI workflow.

Replace `YOUR_USERNAME` below before running these commands in PowerShell on Windows, or Terminal/Git Bash. The Git commands in this guide work in all three:

```bash
git clone https://github.com/YOUR_USERNAME/laravel-student-management-ci.git
cd laravel-student-management-ci
git remote add upstream https://github.com/maohieng/laravel-student-management-ci.git
git remote -v
git switch main
```

`origin` is your fork; `upstream` is the instructor's source. Always push this exercise to `origin`.

**Already have an older fork?** Before writing the new workflow, commit or otherwise preserve your own work, merge the latest upstream changes, and push normally. The upstream removal of the old inherited workflow should arrive through this merge. If you changed that workflow, a modify/delete conflict may need the instructor's help. Once your fork has received the removal commit, create your own workflow following the slides. Do not overwrite your fork with a hard reset or force-sync.

## 2. Local baseline

You need PHP 8.3+, Composer 2, and the PHP extensions listed in the root README.

**Windows PowerShell:** run these commands inside your cloned project folder. For installation help, use the [Windows setup guide](windows-setup.md#5-set-up-your-fork-and-run-the-tests).

```powershell
composer install
composer check-platform-reqs
Copy-Item .env.example .env
Copy-Item .env.testing.example .env.testing
php artisan key:generate
php artisan key:generate --env=testing
composer test
```

**macOS/Linux or Git Bash:**

```bash
composer install
cp .env.example .env
cp .env.testing.example .env.testing
php artisan key:generate
php artisan key:generate --env=testing
composer test
```

If either environment file already exists, keep it and skip that copy command. The supplied baseline currently contains 17 passing tests. Tests use disposable SQLite data and do not require a running web server. Keep the supplied tests and `phpunit.xml` unchanged.

## 3. Write your own workflow

In your editor, create a `.github` folder in the project root, a `workflows` folder inside it, then a file named `laravel-ci.yml`. The complete path is `.github/workflows/laravel-ci.yml`. Combine **Parts 1, 2, and 3 in the slides** into this one file. Use spaces for YAML indentation.

Your workflow must include:

1. `push`, `pull_request`, and `workflow_dispatch` triggers.
2. A `laravel-tests` job on Ubuntu with read-only repository contents permission.
3. Checkout, PHP 8.3 with SQLite extensions, and Composer installation.
4. `composer install`, including development dependencies and the lockfile.
5. Copies of `.env.example` and `.env.testing.example`, a testing application key, and cleared configuration.
6. An absolute SQLite path in the runner, an empty database file, and migrations.
7. `php artisan test` as the **Run Laravel tests** step.

The supplied PHPUnit defaults do not force a database override, so the CI process environment can select the SQLite file. Do not delete the defaults from `phpunit.xml`. Never commit generated environment files, keys, `vendor/`, or database files.

```bash
git status
git add .github/workflows/laravel-ci.yml
git commit -m "Add Laravel CI with SQLite tests"
git push origin main
```

## 4. Review your first run

Open **your fork → Actions → Laravel CI → latest run → laravel-tests**. Expand each step and read the test summary. Fix setup problems until the run is green. Save its link and tell the instructor you are ready. If no run appears after enabling Actions, choose **Laravel CI → Run workflow → main**.

## 5. Sync the instructor's regression

Wait for the instructor to announce the deliberate break and share its commit SHA. Start with a clean working tree:

```bash
git switch main
git pull --ff-only origin main
git fetch upstream
git log --oneline main..upstream/main
git merge --no-edit upstream/main
git push origin main
```

This merge keeps your workflow and brings in the instructor's application change. Do not reset your fork to upstream or force-push. If a conflict occurs, stop and ask the instructor. Fetch/merge locally is not enough: push the merged result to run remote CI.

Open the **new** run in your fork. In **Run Laravel tests**, locate `test_creates_student_and_persists_it`. Expected status: **201**. Actual status: **200**. Other create-student tests may fail too. The CI has detected a regression in application behavior.

If the run is green, confirm you received the announced break commit and inspect `StudentController.php`. You may have synced after the instructor restored the starter. Do not change unrelated tests to manufacture a failure.

## 6. Repair and review again

In `app/Http/Controllers/StudentController.php`, inside `store()`, restore `setStatusCode(201)`. Keep `assertCreated()` in the existing tests.

```bash
php artisan test --filter=test_creates_student_and_persists_it
composer test
git add app/Http/Controllers/StudentController.php
git commit -m "Fix student creation response to 201"
git push origin main
```

Inspect the new green run, rather than rerunning the old broken commit. After the instructor announces the upstream repair, repeat the sync sequence to receive it too.

## Completion

Show your fork, your workflow file, the first green run, the red run after sync, and the green run after your repair. Submit links and explain the 201/200 mismatch aloud in about 30 seconds. No long README assignment or teammate review is required.
