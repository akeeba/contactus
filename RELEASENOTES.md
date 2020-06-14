## Joomla and PHP Compatibility

We are developing, testing and using Akeeba Contact Us using the latest version of Joomla! and a popular and actively maintained branch of PHP 7. At the time of this writing this is:

* Joomla! 3.9
* PHP 7.3

Akeeba Contact Us should be compatible with:
* Joomla! 3.9, 4.0
* PHP 7.1, 7.2, 7.3, 7.4

## Changelog

**New features**

* Optional GPG encryption of outgoing emails
* gh-15 Akismet support  
* Set default backend Items ordering to ID descending (new messages first)
* Common PHP version warning scripts
* Dark Mode
* Joomla 4 compatible

**Bug fixes**

* Unpublished contact categories were displayed in the frontend
* Joomla 4 throws an exception when mail is disabled and you try to send an email
