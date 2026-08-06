# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Build Commands

See `README.md` for build prerequisites and the `buildfiles` sibling-directory layout.

This project's JS-only target is `phing compile-javascript` (not `compile-js` as in most Akeeba projects).

There are no automated tests or linting configured.

## Conventions

- All PHP files begin with `defined('_JEXEC') or die;` guard
- File header: `@package contactus`, `@copyright`, `@license` block
- Brace style: Allman (opening brace on new line)
- Database queries use Joomla's query builder with named parameter binding (`:paramName` + `->bind()`)

## Security Scope

- Application-level rate limiting is intentionally out of scope. Implementing it in Joomla would require substantial database reads and writes, which can become a DoS/DDoS amplifier and defeat the purpose of rate limiting. Rate limiting should be implemented externally using suitable web server configuration or a security-focused CDN such as Cloudflare.

## Packaging

The component manifest is `component/contactus.xml` — note: NOT inside `backend/`.
