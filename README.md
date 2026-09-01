# Akeeba ContactUs

A simple contact form component for Joomla™ 5 and 6

## What does it do?

It lets you add a very simple contact form on your Joomla 5 or 6 site. Each contact category can have a different set of recipients. Also, each contact category can have an auto-responder. We built this for use on our site. 

## Download

Pre-built packages of Akeeba ContactUs are made available through [our site's Download page](https://www.akeeba.com/download/official/contactus.html).

## No support - For developers only

This software is provided **WITHOUT ANY KIND OF SUPPORT WHATSOEVER**. It's also too simple to even have any kind of documentation whatsoever.

If you are a developer you are free to submit a pull request with your code fix, as long as there is a clear description of what was not working for you, why and how you fixed it. 
 
## Prerequisites

In order to build the installation packages of this component you will need to have the following tools:

* A command line environment. Using Bash under Linux / Mac OS X works best. On Windows you will need to run most tools through an elevated privileges (administrator) command prompt on an NTFS filesystem due to the use of symlinks. Press WIN-X and click on "Command Prompt (Admin)" to launch an elevated command prompt.
* A PHP CLI binary in your path
* Command line Git executables
* Phing

You will also need the following path structure inside a folder on your system

* **contactus** This repository
* **buildfiles** [Akeeba Build Tools](https://github.com/akeeba/buildfiles)

You will need to use the exact folder names specified here.

### Useful Phing tasks

All of the following commands are to be run from the root of this repository.
Lines starting with $ indicate a Mac OS X / Linux / other *NIX system commands.
Lines starting with > indicate Windows commands. The starting character ($ or >)
MUST NOT be typed!

#### Creating a dev release installation package

This creates the installable ZIP packages of the component inside the
repository's `release` directory.

    $ phing git
    > phing git

## Regulatory status (EU Cyber Resilience Act)

Akeeba ContactUs is free and open-source software released under the GPLv3 license. It is developed and distributed on a purely non-commercial basis: there is no charge for the software or any version of it, no paid tier or edition, no bundled or gated services, and no plan to monetize it in the future. It is not tied to, bundled with, or a dependency of any commercial product or service offered by Akeeba Ltd or any other party. On this basis, it falls outside the scope of Regulation (EU) 2024/2847 (the Cyber Resilience Act), which exempts free and open-source software supplied outside the course of a commercial activity. This statement reflects our assessment as of 27 August 2026 and will be revisited if the project's distribution model changes.