/**
 * @package   contactus
 * @copyright Copyright (c)2013-2022 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

DROP TABLE IF EXISTS `#__contactus_keys`;

ALTER TABLE `#__contactus_categories` MODIFY `created_on` DATETIME NULL DEFAULT NULL;

UPDATE `#__contactus_categories` SET `created_on` = NULL WHERE `created_on` = '0000-00-00 00:00:00';

ALTER TABLE `#__contactus_categories` MODIFY `modified_on` DATETIME NULL DEFAULT NULL;

UPDATE `#__contactus_categories` SET `modified_on` = NULL WHERE `modified_on` = '0000-00-00 00:00:00';

ALTER TABLE `#__contactus_categories` MODIFY `locked_on` DATETIME NULL DEFAULT NULL;

UPDATE `#__contactus_categories` SET `locked_on` = NULL WHERE `locked_on` = '0000-00-00 00:00:00';

ALTER TABLE `#__contactus_items` MODIFY `created_on` DATETIME NULL DEFAULT NULL;

UPDATE `#__contactus_items` SET `created_on` = NULL WHERE `created_on` = '0000-00-00 00:00:00';

ALTER TABLE `#__contactus_items` MODIFY `modified_on` DATETIME NULL DEFAULT NULL;

UPDATE `#__contactus_items` SET `modified_on` = NULL WHERE `modified_on` = '0000-00-00 00:00:00';

ALTER TABLE `#__contactus_items` MODIFY `locked_on` DATETIME NULL DEFAULT NULL;

UPDATE `#__contactus_items` SET `locked_on` = NULL WHERE `locked_on` = '0000-00-00 00:00:00';

ALTER TABLE `#__contactus_categories` ENGINE InnoDB;

ALTER TABLE `#__contactus_items` ENGINE InnoDB;
