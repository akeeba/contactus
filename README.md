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

## Regulatory status (EU Cyber Resilience Act)

Akeeba ContactUs is free and open-source software released under the GPLv3 license. It is developed and distributed on a purely non-commercial basis: there is no charge for the software or any version of it, no paid tier or edition, no bundled or gated services, and no plan to monetize it in the future. It is not tied to, bundled with, or a dependency of any commercial product or service offered by Akeeba Ltd or any other party. On this basis, it falls outside the scope of Regulation (EU) 2024/2847 (the Cyber Resilience Act), which exempts free and open-source software supplied outside the course of a commercial activity. This statement reflects our assessment as of 27 August 2026 and will be revisited if the project's distribution model changes.