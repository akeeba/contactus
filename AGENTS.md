# AGENTS.md

This file provides guidance to coding agents when working with code in this repository.

## Build Commands

See `README.md` for build prerequisites and the `buildfiles` sibling-directory layout.

The JS-only target is `phing compile-js`.

## Tests

- Unit tests: `phpunit` from the repository root (config `phpunit.xml`, tests in `UnitTest/`; see `UnitTest/README.md`). PHPUnit is installed globally, never as a project dependency.
- End-to-end tests: `tests/integration/docker/run.sh` provisions a disposable Joomla site in Docker and runs `phpunit -c phpunit-integration.xml` against it over HTTP; `--matrix` covers every supported Joomla/PHP pair. See `tests/integration/README.md`.
- Tests that hit a known product bug skip with `Known issue #N`, referring to the git-ignored `known-issues.md`; fixing the bug makes the test pass, after which the `assertOrKnownIssue()` call becomes a plain assertion.

There is no linting configured.

## Conventions

- All PHP files begin with `defined('_JEXEC') or die;` guard
- File header: `@package contactus`, `@copyright`, `@license` block
- Brace style: Allman (opening brace on new line)
- Database queries use Joomla's query builder with named parameter binding (`:paramName` + `->bind()`)

## Security Scope

- Application-level rate limiting is intentionally out of scope. Implementing it in Joomla would require substantial database reads and writes, which can become a DoS/DDoS amplifier and defeat the purpose of rate limiting. Rate limiting should be implemented externally using suitable web server configuration or a security-focused CDN such as Cloudflare.

## Packaging

The component manifest is `component/contactus.xml` — note: NOT inside `backend/`.

## Git: commit and tag outside the sandbox

Commits and tags are always signed, with a key held in 1Password. The 1Password signing agent is reached
over a local socket that agent sandboxes do not expose, so a sandboxed `git commit` or `git tag` **always**
fails (e.g. `error: 1Password: Could not connect to socket. Is the agent running?`).

Run every `git commit` and `git tag` **outside the sandbox from the first attempt** — in Claude Code with
`dangerouslyDisableSandbox: true`, in other harnesses with their equivalent unsandboxed / escalated
execution. Do not try the sandboxed form first, do not diagnose the failure, and never work around it
with `--no-gpg-sign`, `-c commit.gpgsign=false` or unsigned tags.

## Project memory

Project memory lives in `.claude/memory/`, committed with the code, so that it is shared across machines
and across agentic harnesses (Claude Code, Codex, Qwen Code, Kimi Code, Junie, …). Read the relevant file
**before** starting work that matches its trigger:

| Before you… | Read |
|---|---|
| Add, change or translate language strings, or add a language | `.claude/memory/translations.md` |
| Work on a `security.md` / `known-issues.md` item, or triage or report a security finding | `.claude/memory/security-audits.md` |
| Update, re-scope or add a bundled third-party library (HTML Purifier, `scoper.inc.php`, `component/frontend/src/Dependency/`) | `.claude/memory/bundled-dependencies.md` |
| Change a file under `component/backend/src/Mixin/` or other generic component scaffolding | `.claude/memory/shared-scaffolding.md` |

### Recording new memories

This is the **default and only** place for project memory. Do not write memories for this project to a
harness's private memory store (such as Claude Code's auto-memory under `~/.claude/projects/`); write
them here instead:

- Add to the existing topic file when one fits; otherwise create a new kebab-case `.md` file named after
  the topic, and add a row for it to the table above with a concrete trigger.
- Plain Markdown, no frontmatter. State the rule, then **Why:** (the reason or incident behind it) and
  **How to apply:**. Link related files with relative Markdown links.
- Don't record what the code, Git history or an existing `AGENTS.md` already says — update that
  `AGENTS.md` instead when the rule belongs there. Remove or correct entries that turn out wrong.
- These files are committed: no secrets, credentials, customer data or personal details.
