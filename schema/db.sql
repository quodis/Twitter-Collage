/**
 * 
 * @pacjage    Firefox 4 Twitter Party
 * @subpackage sql
 * @version    v.0.1
 * @author     Andre Torgal <andre@quodis.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */
CREATE DATABASE twitterparty;
CREATE USER 'twitterparty'@'localhost' IDENTIFIED BY 'twitterparty';
GRANT ALL PRIVILEGES ON `twitterparty`.* TO 'twitterparty'@'localhost';
