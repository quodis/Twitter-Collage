/**
 * 
 * @package    Firefox 4 Twitter Party
 * @subpackage sql
 * @version    v.0.1
 * @author     Andre Torgal <andre@quodis.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */
USE twitterparty;
DROP TABLE IF EXISTS `tweet`;
CREATE TABLE `tweet` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `page` INT(10) UNSIGNED NOT NULL,
  `position` SMALLINT(5) UNSIGNED NOT NULL,
  `twitterId` BIGINT(20) UNSIGNED NOT NULL,    /* id_str: "32073166788493312" */
  `userId` INT(10) UNSIGNED NOT NULL,          /* from_user_id_str: "70669597" */
  `userName` VARCHAR(36) NOT NULL,    /* from_user: "onion_soup" */
  `imageUrl` VARCHAR(200) NOT NULL,   /* profile_image_url: http://a1.twimg.com/sticky/default_profile_images/default_profile_0_normal.png */
  `createdDate` VARCHAR(32) NOT NULL, /* created_at: "Mon, 31 Jan 2011 13:56:12 +0000" */
  `createdTs` INT(10) UNSIGNED NOT NULL,       /* created_at: "Mon, 31 Jan 2011 13:56:12 +0000" */
  `contents` VARCHAR(200) NOT NULL,   /* text: "http://tinyurl.com/4m9efb5 Quodis - About" */
  `isoLanguage` VARCHAR(3) NOT NULL,  /* iso_language_code: "en" */
  `payload` TEXT,
  `imageData` TEXT,
  `processedTs` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (page, position),
  UNIQUE KEY (twitterId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
