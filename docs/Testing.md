# Backend regression tests

These characterization tests establish a baseline for the original PHP 7
application before upgrading PHP or its runtime dependencies. They use PHPUnit
9.6 and real Twig/Cron implementations from `backend/composer.lock`.

## Run in Docker (recommended)

From the repository root:

```sh
docker build -f docker/tests/Dockerfile -t supla-scripts-backend-tests .
docker run --rm --network none supla-scripts-backend-tests --testdox
```

The image supplies PHP 7.4, the required extensions, Composer, the locked
dependencies, and the tests. Nothing needs to be installed on your host except
Docker. Building needs internet access; running the tests does not.

Rebuild after editing sources or tests. Docker caches the dependency-install
layer when the Composer files have not changed. You can run a subset by passing
PHPUnit arguments:

```sh
docker run --rm --network none supla-scripts-backend-tests --filter FeedbackInterpolatorTest
docker run --rm --network none supla-scripts-backend-tests --filter FeedbackTwig
docker run --rm --network none supla-scripts-backend-tests --filter SceneExecutorTest
docker run --rm --network none supla-scripts-backend-tests --filter ThermostatProfileTimeSpanTest
```

The runner copies the backend and coding standard, not your application configuration or
credentials. It does not run application initialization, migrations, a database
server, or an HTTP server. History tests use a disposable SQLite in-memory
database. SUPLA calls use test doubles, and `--network none` additionally
prevents the container from reaching devices or external services.

The current baseline passes 91 tests (135 assertions). The locked Twig 2.5 package emits a known
PHP 7.4 deprecation about an unparenthesized nested ternary. It is left visible;
vendor files are not patched and deprecations are not suppressed globally.

Travis uses the same Docker image to run tests and the existing coding-standard
check. PHP_CodeSniffer was updated within its existing `^3.1` constraint so it
can run on PHP 7.4. To run that check separately:

```sh
docker run --rm --network none --entrypoint composer supla-scripts-backend-tests phpcs
```

The baseline has two pre-existing coding-standard findings, deliberately left
unchanged: missing visibility on `$states` in
`backend/models/supla/SuplaApiCached.php` (error), and an overlong line in
`backend/app/commands/ClearInvalidUserTokensCommand.php` (warning). Thus the full
lint check currently fails even though the regression tests pass. New test files
pass the project's coding standard.

**PHP 7.4 and the legacy dependencies are end-of-life. This image is for isolated
baseline tests only, not production deployment.** Do not use
`--ignore-platform-reqs` for the baseline: dependency installation must succeed
under the actual PHP 7.4 runtime.
The Dockerfile uses Debian's archived Bullseye repositories because the original
PHP 7.4 base image no longer has a supported distribution behind it.

## Run locally with PHP 7.4

With the required PHP extensions installed:

```sh
cd backend
composer install
composer test
```

## Scope and limitations

The default suite below remains independent of a MySQL server. The separate
database suite is described under [Host MySQL integration tests](#host-mysql-integration-tests).

- `FeedbackInterpolator::interpolate()` / `isConditionMet()`: actual Twig
  rendering, registered filters, condition interpretation, legacy channel
  placeholders, user timezones, and error reporting.
- `SceneExecutor::executeCommandsFromString()`: command parsing, ordered SUPLA
  actions and arguments, returned results, and recovery from SUPLA failures.
- `ThermostatProfileTimeSpan::getClosestStart()` / `getClosestEnd()`: scheduling
  boundaries, weekday selection, fallback times, and timezone handling.
- Real templates invoke every registered `FeedbackTwigExtension` function and
  filter. `state()` tests use a fake SUPLA API with real property access and
  cache-control context. `history()` tests use real Eloquent queries against
  SQLite to verify user/channel isolation, inclusive cutoff, descending order,
  the 20-entry limit, empty results, and decoded JSON state.
- `timestamp()`, `sunriseTime()`, and `sunsetTime()` render fixed dates, including
  winter/summer and timezone-derived coordinates. Solar expected values record
  the legacy calculation exactly; changing algorithms requires reviewing them.
- `getUrl()` runs real cURL against temporary `file://` fixtures, exercising
  function registration, regex arguments/capture groups, configuration arrays,
  cache reuse/isolation, and failure feedback without opening a network port.

Test-only subclasses override the existing protected `getApi()` / `getApp()`
methods to supply fake device access and a current-user context, without
bootstrapping the configured application. The Twig extension methods themselves
are not mocked. Production implementations and method signatures are unchanged.
Tests use fixed inputs and literal expected values, not real devices or the
current clock. Temporary files, cache directories, database connections, and
global timezone/resolver settings are cleaned up or restored between tests.

Characterization includes existing quirks: for example, condition feedback
accepts any truthy text shorter than six characters (even the literal `false`),
and a schedule queried exactly at a boundary returns the next occurrence, not
the current one. These are recorded behaviors, not newly introduced rules.

The default suite alone does not cover HTTP/JWT authorization,
nested scenes, notifications, the complete thermostat
control loop, live SUPLA access, and HTTP-specific cURL behavior (headers, TLS,
redirects, timeouts) still need separate tests. These are inline representative
templates, not templates exported from user configurations.

After the migration, run the same tests on PHP 8.3 against the upgraded
dependencies. Passing this PHP 7.4 baseline alone does not establish PHP 8.3
compatibility.

## Host MySQL integration tests

The separate PHP 7.4 integration suite uses **only `supla_scripts_test`** on an
explicitly configured MySQL-compatible server. It has been verified against the
local MariaDB 11.7 server. Other MySQL/MariaDB versions still need separate runs.
The database suite currently passes 16 tests / 123 assertions; the independent
default suite continues to pass 91 tests / 135 assertions.

**Every invocation drops all tables in `supla_scripts_test` and runs all 36 real
application migrations from scratch, even when `--filter` is supplied. Never
store useful data in this database.** The database itself must already exist.
It was created locally for these tests with UTF-8 encoding.

On Linux, from the repository root:

```bash
read -rsp 'Test database password: ' TEST_DB_PASSWORD; echo
export TEST_DB_PASSWORD
bash docker/tests/run-database-tests.sh --testdox
unset TEST_DB_PASSWORD
```

Defaults are `127.0.0.1:3306`, username `root`. Override `TEST_DB_HOST`,
`TEST_DB_PORT`, or `TEST_DB_USERNAME` as needed. Passwords are passed through
environment variables, never committed, copied into the image, or written to
host application configuration. A restricted account with schema privileges on
`supla_scripts_test.*` is preferable to root for ongoing use.

The script builds the explicit `database-tests` Docker target and uses Linux
host networking to reach MySQL on loopback; it does not require changing MySQL's
listen address. Do not pass `--network none` for this suite. The container runs
read-only with temporary `/tmp` and `/app/var` filesystems. It generates its own
disposable encryption key, and does not mount host configuration or secrets.

Safety checks require the dedicated image marker, the exact database name, and
`TEST_DB_RESET=supla_scripts_test` before connecting or changing the schema.
A database advisory lock prevents two copies of the suite from rebuilding it
concurrently. Model tests use transactions which are rolled back. HTTP tests
need committed fixtures visible to the separate server process; these use a
separate connection and explicitly delete their users (with foreign-key cascade)
in teardown. Successful runs leave the migrated schema but no test users or
dependent records.
Do not run other clients against this disposable database during the suite.

Coverage includes:

- All application migrations from an empty schema, migration version tracking,
  expected tables/columns, and a real foreign-key rejection of an orphan scene.
- Scene save/reload/update, UUIDs, fixed timestamp serialization, voice-trigger
  normalization, and JSON actions/notifications/action triggers.
- User timezone and scene-limit settings, encrypted API/Pushover/webhook
  credentials, updates, and nullable encrypted values. Raw SQL verifies that
  stored secrets are encrypted rather than plaintext.
- Thermostat profile JSON configurations and nested schedules across reloads
  and updates.
- User-scoped scene, thermostat/profile, and state-log relationships, including
  cross-owner lookups and inverse ownership.
- Reloaded scene command execution with a mocked SUPLA boundary, and scheduling
  calculations from a reloaded thermostat profile.
- Automatic `createdAt`/`updatedAt` assignment and update behavior, nullable date
  fields, and offset-bearing dates persisted and serialized as the same UTC
  instant. Carbon's clock is controlled without sleeps and restored afterward.
- Real HTTP requests through `backend/index.php`, application boot, routes,
  controllers, and authentication middleware. Missing credentials, malformed
  JWTs, invalid signatures, and expired tokens return 401; a valid token resolves
  the correct account; reading another account returns 403. Basic authentication
  fallback is checked with correct and incorrect passwords. Account JSON is
  checked for identity and absence of stored credentials for a token-backed user.

HTTP smoke tests start a temporary PHP server bound only to `127.0.0.1` on an
ephemeral port, using disposable JWT settings and fixture credentials. No ports
are published to other machines and no SUPLA/notification services are called.
The server is stopped and temporary configuration removed in teardown. The
current-user endpoint intentionally exposes webhook refresh data for other
account types; these checks do not redefine that existing behavior.

Migration initialization and HTTP requests run the normal configured application
in child processes; persistence tests use real Eloquent without application
observers or device access. This is only a small HTTP authorization smoke suite,
not comprehensive authorization coverage, observer side-effect testing, live
device execution, or an upgrade of a populated production database.
The default Travis job still runs only the network-free suite; it does not
connect to a developer's host database.
