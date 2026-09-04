# Akeeba ContactUs

A simple contact form component for Joomla™ 5 and 6

## What does it do?

It lets you add a very simple contact form on your Joomla 5 or 6 site. Each contact category can have a different set of recipients. Also, each contact category can have an auto-responder. We built this for use on our site. 

## Build instructions

Check out this repository and Akeeba Build Tools — Public Packager using the following directory names:

- `contactus` This repository.
- `buildfiles` [Akeeba Build Tools — Public Packager](https://github.com/akeeba/buildfiles-public)
- `build.properties` A file created as per the instructions in `buildfiles/README.md`

Then:

```bash
cd contactus
composer install
phing git
```

The generated package is under `contactus/release`.

## Supported PHP and Joomla versions

Each release of Akeeba ContactUs only supports a specific range of PHP and Joomla versions — the ones we have
actually tested it against and can vouch for from a security standpoint. This range is documented in this
`README.md`, in the `CHANGELOG`, and on [our compatibility page](https://www.akeeba.com/compatibility.html).

Starting with version 4.3.0, this is no longer just documentation: the component actively checks the PHP and Joomla
version it is running under **every time it runs**, not only when you install or update it. If your host changes
your site's PHP version, or you upgrade (or downgrade) Joomla itself to a version outside the range this release
supports, the component will refuse to operate.

If this happens:

- **Loading the component's backend (Components → Contact Us)** will show a clear error message telling you exactly
  what is wrong — e.g. that your PHP version is too old or too new, or that your Joomla version is too old or too
  new — and what you need to do about it.
- **Loading a contact form on the frontend** will show a generic error page rather than expose PHP details to your
  site's visitors. Check the backend (as above) to see the specific reason.

To fix it, either:

1. Change your server's PHP version, or your site's Joomla version, so that it falls within the range this release
   of Akeeba ContactUs supports, or
2. Install a version of Akeeba ContactUs whose supported range matches the PHP and Joomla versions you are actually
   running.

## Regulatory status (EU Cyber Resilience Act)

Akeeba ContactUs is free and open-source software released under the GPLv3 license. It is developed and distributed on a purely non-commercial basis: there is no charge for the software or any version of it, no paid tier or edition, no bundled or gated services, and no plan to monetize it in the future. It is not tied to, bundled with, or a dependency of any commercial product or service offered by Akeeba Ltd or any other party. On this basis, it falls outside the scope of Regulation (EU) 2024/2847 (the Cyber Resilience Act), which exempts free and open-source software supplied outside the course of a commercial activity. This statement reflects our assessment as of 27 August 2026 and will be revisited if the project's distribution model changes.