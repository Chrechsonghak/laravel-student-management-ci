# Lesson 5 — instructor regression exercise

This starter intentionally has **no active GitHub Actions workflow on `main`**. Each student forks it and creates `.github/workflows/laravel-ci.yml` using the [Lesson 5 Practice Session](https://docs.google.com/presentation/d/1h1yP-l7Ai2SxtNX0Fq4tzq1g4h5qcoGm8vMUNMZhB6I/edit). Do not add that workflow to the upstream starter or accept student workflow PRs into it.

The instructions below are for a **future, supervised classroom exercise**. The published starter is currently left working; the deliberate regression has not been applied.

## Before class

1. Confirm `main` has no files under `.github/workflows/` and the create-student response returns `201`.
2. Have every student use their own fork and enable Actions if prompted.
3. Wait until every student has committed their workflow to their fork's `main` and recorded a passing baseline run (currently 17 tests).
4. Ask students to leave the application code and existing tests unchanged until the sync exercise.
5. Coordinate the break window: this API is also used by the Flutter lesson. Restore the starter before another class or demo uses it.

## Introduce a real application regression

In your clone of **maohieng/laravel-student-management-ci**, check that `origin` points to the upstream repository, that your working tree is clean, and update `main`:

```bash
git remote -v
git status
git switch main
git pull --ff-only origin main
```

Edit `app/Http/Controllers/StudentController.php`. In `store()`, change only:

```php
->response()->setStatusCode(201);
```

to:

```php
->response()->setStatusCode(200);
```

Keep `assertCreated()` and all other test expectations unchanged. The create-student endpoint should return HTTP 201; returning 200 is the regression. If your local PHP/Composer setup is ready, run `php artisan test --filter=test_creates_student_and_persists_it` and observe the expected failure.

```bash
git diff -- app/Http/Controllers/StudentController.php
git add app/Http/Controllers/StudentController.php
git commit -m "Demo: return wrong student creation status"
git push origin main
git rev-parse HEAD
```

Record and share the resulting commit SHA as `BREAK_COMMIT_SHA`. Do not add `[skip ci]` to the commit message. **Leave the break present until all students have synced and captured their failed run.** The upstream repository will not run CI because it has no workflow; students' forks will run their own workflows.

## Students receive the regression

Each student runs the following inside their own clone, starting with a clean working tree:

```bash
git switch main
git pull --ff-only origin main
git fetch upstream
git log --oneline main..upstream/main
git merge --no-edit upstream/main
git push origin main
```

They must inspect **their own fork → Actions → Laravel CI → latest run → laravel-tests → Run Laravel tests**. The existing `test_creates_student_and_persists_it` expects 201 and receives 200. Other tests that create students may also fail. This is the intended red result, not a broken CI setup.

An ordinary merge retains each student's independently added workflow. `git fetch` alone does not merge anything, and a local merge alone does not trigger remote CI. The explicit student-authenticated `git push origin main` triggers the workflow's `push` event. Do not use `git reset --hard upstream/main`, force-sync, or force-push: these can discard the student's workflow commit.

If a student prefers GitHub's **Sync fork → Update branch**, confirm that their workflow remains present and inspect Actions afterward. If no new run appears, use **Laravel CI → Run workflow → main**. The command-line path above is the primary classroom procedure.

## Student repair and evidence

Students restore `201` in their own copy of `StudentController.php`, keep the tests unchanged, run the tests, commit the repair, and push to their own `main`. They show the first green run, the red run after sync, and the new green run. They explain the status mismatch aloud; no long writing assignment is needed.

Do not “fix” the failure by accepting 200 in the test, removing tests, or adding `continue-on-error`.

## Restore upstream after the exercise

After everyone has captured the failure, revert **only the recorded deliberate break commit**:

```bash
git switch main
git pull --ff-only origin main
git revert BREAK_COMMIT_SHA
git push origin main
```

Replace `BREAK_COMMIT_SHA` with the actual recorded SHA; do not blindly revert `HEAD` if other commits have arrived. If a conflict occurs, stop and review it. Check that `store()` returns 201 again and run `composer test` locally when available. Announce that the starter is restored.

Students repeat the merge-and-push sync sequence to receive the upstream repair. If they already made the same repair, Git will normally combine the identical change; inspect any conflict rather than overwriting their work. Keep upstream free of active workflows for the next cohort.

## Troubleshooting

- **No Actions run:** enable Actions in the fork; check `.github/workflows/laravel-ci.yml` is committed to `main`; use manual execution if necessary.
- **Sync is still green:** inspect the SHA and controller. The student may have synced before the break or after the restore, or already repaired their copy. Coordinate a new exercise window instead of changing random tests.
- **Composer/environment failure:** fix setup first. The intended exercise failure is the 201/200 test mismatch after setup succeeds.
- **Old fork:** follow the existing-fork guidance in the student guide before creating the new workflow.

References: [GitHub fork sync](https://docs.github.com/en/pull-requests/how-tos/work-with-forks/syncing-a-fork), [GitHub Actions events and fork behavior](https://docs.github.com/en/actions/reference/workflows-and-actions/events-that-trigger-workflows).
