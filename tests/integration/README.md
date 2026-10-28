# ContactUs end-to-end tests

These tests drive a **real, disposable Joomla site over real HTTP**, exactly as a browser or an attacker
would. The whole stack is stood up in Docker, provisioned from nothing, tested, and thrown away:

| Service | What it is |
|---|---|
| `db` | MySQL |
| `php` | PHP-FPM running Joomla, and the Joomla CLI. **No route to the internet** (see below) |
| `web` | Apache, proxying to `php` over FastCGI |
| `mailpit` | SMTP sink with a REST API: the notifications and auto-replies really go out over SMTP |

Nothing boots Joomla inside the PHPUnit process. You never configure a site by hand, and a run that fails or
crashes mid-way never leaves anything behind in an unknown state.

The unit tests are a separate suite; see [`UnitTest/README.md`](../../UnitTest/README.md).

## Requirements

* **Docker** with the Compose plugin.
* **PHP CLI** (with `curl`, `pdo_mysql`, `dom`) and **PHPUnit 11**, installed globally
  (`composer global require phpunit/phpunit ^11`) and on your `PATH`. The test runner is a host-side process
  that talks to the site over its published ports.
* **Phing** on your `PATH`, plus the `../buildfiles` checkout and `node`, to build the package (`phing git`; see
  the repository's `README.md`). Skip with `--skip-build` if `release/` already holds a package.
* `unzip`, plus `getcms` from the sibling `../buildfiles` checkout (run `composer install` there),
  used to resolve, download and extract Joomla.

> [!WARNING]
> Until known issue #5 is fixed (a stale `composer.lock`), `phing git` fails at `composer install`, so a plain
> `run.sh` stops at the build step. Build once with a refreshed lock (`composer update --lock && phing git &&
> git checkout composer.lock`) and run with `--skip-build`.

## Quick start

```
tests/integration/docker/run.sh
```

That one command scrubs, brings the stack up, installs Joomla, points its mailer at Mailpit, builds and installs
ContactUs, provisions the fixtures, runs the suite, and tears the stack down — containers, networks and the database
volume — however it ends: the suite finishing, a failed step, or Ctrl-C. Only `--keep-containers` and `--no-tests`
leave it up. After a passing run `docker/www/` is deleted too; after a failure it is left for inspection, and the next
run clears it.

The `php` and `web` images are not built here. They are the `akeeba-e2e/*` images every Akeeba E2E stack shares,
defined in the sibling `buildfiles` repository under `docker/e2e/` (see its `README.md`); `run.sh` calls
`../buildfiles/docker/e2e/images.sh ensure` for each before bringing the stack up. ContactUs's own PHP settings,
`docker/config/php.ini`, are bind-mounted into the `php` container; Apache uses the shared image's default vhost. A PHP
extension the suite needs goes into that shared image, never into a project-local Dockerfile.

To iterate, keep the stack up and re-run PHPUnit directly — provisioning is the slow part, the tests themselves
take seconds:

```
tests/integration/docker/run.sh --keep-containers
phpunit -c phpunit-integration.xml
phpunit -c phpunit-integration.xml --filter SubmissionRefusalTest
php tests/integration/provision.php      # put the fixtures back
tests/integration/docker/run.sh --down   # tear it all down
```

While the stack is up: the site is on <http://localhost:8170> (`admin` / `test`), and Mailpit's web UI is on
<http://localhost:8175>.

## `run.sh` options

| Option | Effect |
|---|---|
| `-j`, `--joomla=V` | Override `JOOMLA_VERSION` for this run (`6`, `6.1`, `6.1.3`) |
| `-p`, `--php=V` | Override `PHP_VERSION` for this run (`8.1`, `8.3`, `8.5`) |
| `--matrix` | Run every Joomla/PHP pair in `JOOMLA_MATRIX`, then exit |
| `-f`, `--filter=NAME` | Passed through to PHPUnit |
| `--skip-build` | Don't run `phing git`; install the newest package already in `release/` |
| `--no-tests` | Provision the site but don't run the suite (leaves it up) |
| `--keep-containers` | Leave the stack running afterwards |
| `--down` | Tear everything down and exit |
| `-- <args>` | Everything after `--` goes to PHPUnit |

## The version matrix

```
tests/integration/docker/run.sh --matrix
```

Runs the suite once per pair in `JOOMLA_MATRIX` — by default Joomla **5.4 on PHP 8.1 and 8.5, 6.0 on PHP 8.3
and 8.5, 6.1 on PHP 8.3 and 8.5**. Where each bound comes from:

* **Joomla ≥ 5.4.0, < 6.3** — `$minimumJoomla` / `$maximumJoomla` in `component/script.contactus.php` (the
  same values as `composer.json` `extra.akcompat.limit` and `Helper\VersionLimits`; the unit test
  `VersionLimitsTest::testBoundsMatchComposerAndInstaller` keeps the three in step). `run.sh` reads the bounds from
  the installer script and refuses anything outside them before touching Docker. 6.1 is the latest stable
  release today (6.2 is not out), so it is the day-to-day default and the ceiling.
* **PHP ≥ 8.1, < 8.7** — `$minimumPhp` / `$maximumPhp`, likewise. Joomla 6 itself needs PHP 8.3; `run.sh` reads
  Joomla's own floor out of the extracted package and refuses an impossible pair. The newest published
  `php:*-fpm` image is 8.5, so that is the ceiling in practice.
* The pairs are the **edges** of the range, not a cross product.

Why more than one Joomla version is not optional thoroughness — the code paths that differ between them:

* **Error reporting from models and tables.** `CMSObjectWorkaroundTrait` bridges Joomla 5's legacy
  `setError()`/`getError()` and Joomla 6's exceptions. Every refusal of the contact form (no consent, a disabled
  category, an empty field) and every back-end validation error travels through it, so `SubmissionRefusalTest`
  and `BackendAccessTest` prove it only for the Joomla versions they run on.
* **The back-end lists' multi-select script** is loaded one way below Joomla 6 and another from 6 on
  (`tmpl/items/default.php`, `tmpl/categories/default.php`, Joomla PR 45925).
* **Tables converted to arrays** (097e236): Joomla 6 includes non-scalar values, which is what
  `GetPropertiesAwareTrait` works around. Storing a message and a category (`ContactFormTest`,
  `BackendAccessTest::testSuperUserCreatesCategory`) goes through it.
* **The mailer** is created through `MailerFactoryInterface`; the notification and auto-reply tests are the only
  thing that checks it still delivers through each release's mail stack.

And why more than one PHP version: the floor, 8.1, is only proven by running on it — the unit tests run on the
newest PHP only.

The day-to-day run (no flags) covers only Joomla 6.1 on PHP 8.5. **A green single-version run proves less than
it looks** — in particular nothing about Joomla 5.4 or the PHP 8.1 floor. Run the matrix before a release.

## Configuration

Everything is in `docker/env.dist`, which **is committed**. `run.sh` creates `docker/.env` from it on first run;
edit that for local overrides. Ports (8170 / 33316 / 8175) are deliberately off the sibling harnesses' (Admin
Tools, ATS, Akeeba Backup, ARS, com_compatibility, Data Compliance, DocImport, Paddle, Onthos), so any of those
stacks can be up at the same time.

`tests/integration/config.php` is written by `run.sh` to match what it actually provisioned. It is git-ignored,
and `config.dist.php` carries the same defaults, so a fresh clone can run PHPUnit without configuring anything.

**The site has no internet access.** The `php` container sits only on an `internal` Docker network, reaching
`db`, `mailpit` and `web` and nothing else. That keeps runs hermetic, and it is the realistic "host blocks
outbound HTTP" condition under which the Akismet check fails (`SensitiveOutputTest`, known issue #3). The
baseline configuration has no Akismet key, so ordinary submissions never try to leave the stack.

## How it fits together

| Piece | What it does |
|---|---|
| `docker/run.sh` | The one-shot orchestrator described above |
| `docker/docker-compose.yml` | The services, on the shared `akeeba-e2e/*` images |
| `docker/config/php.ini` | The site's PHP settings, bind-mounted into the `php` container |
| `assets/e2e-provision.php` | Runs **inside** the container: the nested-set fixtures — the user groups and the component's permission rules |
| `assets/e2e-probe.php` | Runs inside the container: reports who a session belongs to and what it may do |
| `src/SiteProvisioner.php` | Host-side: runs the above, then seeds everything flat over PDO (users, contact categories, component options); hands fixtures to tests by name; creates messages |
| `src/Engine/Surfer.php` | cURL client with a cookie jar, token extraction, and redirects captured rather than followed |
| `src/Engine/Mailpit.php` | Reads the mail the site actually sent |
| `src/Engine/ContainerCli.php` | Commands inside the `php` container |
| `src/AbstractE2ETestCase.php` | Base class: actors, the contact-form request helpers, refusal and known-issue assertions |

**The provisioner's nested-set half runs inside the container on purpose.** User groups and assets are nested
sets; building them with hand-written SQL means reimplementing `lft`/`rgt` bookkeeping and then debugging ACL
results that are wrong for reasons unrelated to ContactUs. **The container-side scripts call
`$app->createExtensionNamespaceMap()` explicitly**: nothing else registers the extension namespaces when the
application is not executed, and the failure would be silent.

## The fixtures

**Actors** — `guest()`, `loggedIn($role)` (front-end), `superUser()` and `loggedInBackend($role)`:

| Role | What it is | Why it exists |
|---|---|---|
| guest | Not logged in | Sees Public categories only |
| `alice` | Registered | Sees the Registered-only category a guest must not; not the Special one |
| `viewer` | Back-end login + `core.manage` on the component, nothing else | May list; must not create, edit or delete |
| `nomanage` | An Administrator explicitly **denied** `core.manage` on the component | The back-end dispatcher gate |
| `admin` | The installer's Super User | The control for every refusal |

`HarnessTest::testAclMatrixIsWhatItClaims()` asserts, through the site's own authorisation code, that each role
holds exactly what the table says. A fixture that accidentally granted the wrong thing would turn the tests that
rely on it green while proving nothing.

**Contact categories** — every one has recipients no other shares, so Mailpit alone tells which category a
message was filed under:

| Key | Access | Enabled | Auto-reply |
|---|---|---|---|
| `general` | Public | yes | yes, with `[FROMNAME]`, `[SITENAME]`, `[CATEGORY]`, `[SUBJECT]` |
| `noreply` | Public | yes | no |
| `disabled` | Public | **no** | no |
| `registered` | Registered | yes | no |
| `special` | Special | yes | no |

Tests that need a stored message make their own with `static::$fixtures->createMessage()`; tests that change the
component's options do it through `$this->setComponentParams()`, which puts them back afterwards.

## Writing tests

Test classes go in `src/Tests/`, namespace `Akeeba\ContactUs\IntegrationTest\Tests`, extending
`AbstractE2ETestCase`.

```php
$jform    = $this->contactData('disabled');           // a valid submission, marked for finding it later
$response = $this->submitContact($this->guest(), $jform);

$this->assertRefused($this->guest(), $response);
$this->assertSame([], $this->messagesMatching($this->markerOf($jform)));   // …and nothing was stored
$this->assertSame(0, $this->mailpit()->count());                           // …or mailed
```

Conventions:

- **Assert the refusal AND the absence of its effect.** `assertRefused()` says the response was not a success;
  the database and Mailpit say nothing happened. A message can be reworded, a row cannot.
- **Pass the surfer that made the request to `assertRefused()`.** A bad anti-CSRF token does not throw — Joomla
  enqueues a message and redirects — and the message lives in that session.
- **Make sure the test could fail.** Every refusal has a legitimate twin that succeeds (`ContactFormTest` for the
  form, the Super User in `BackendAccessTest`), and tests guard against fixtures that would make them moot ("the
  form did not render; the test would prove nothing"). The regression tests for b3a24d8 (`LayoutTraversalTest`)
  and b69505f (`FrontendRoutingTest::testNonTaskMethodsAreNotExecuted`) were verified to fail against the
  pre-fix code by reverting the fix in the installed copy.
- **A crash is not a refusal.** Use `assertNotServerError()` on anything that should be refused: it also fails on
  a PHP fatal in `php-errors.log`.
- **Surface suspected bugs, don't encode them.** Where the product is wrong, the test asserts the correct
  behaviour through `assertOrKnownIssue($ok, N, 'diagnosis')`, which skips with a pointer to item N of the
  git-ignored `known-issues.md` while the bug stands, and passes once it is fixed. When you fix one, replace the
  call with a plain assertion.

## Practical notes

- **Prove a test fails on the old code (or passes on the fixed code) without rebuilding:** the installed site
  lives in `docker/www`. Patch the installed copy of a file, give it a new modification time so opcache picks it up
  (`touch`), run the test, then copy the repository file back over it. `VersionLimitsDisclosureTest` does exactly
  this to simulate an unsupported PHP version, and always restores the file.
- **PHP errors** go to `docker/www/php-errors.log` (display is off, so tests observe real error pages); `run.sh`
  prints its tail when the suite fails, and `newPhpErrors()` returns what a test caused.
- **Mail:** clear Mailpit at the start of a test that asserts on mail (`$this->mailpit()->clear()`); the
  contact-form test classes do it in `setUp()`.
- **Rejected submissions land on a 404 page** today (known issue #4). `assertRefused()` still recognises them — the
  refusal message is on that page — so only `SubmissionRefusalTest::testRefusalReturnsToTheForm` is about it.
