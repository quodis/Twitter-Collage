/**
 * 
 * @package    TwitterCollage
 * @subpackage sql
 * @version    v.0.1
 * @author     Andre Torgal <andre@quodis.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */
CREATE DATABASE twitter_collage;
CREATE USER 'twitter_collage'@'localhost' IDENTIFIED BY 'twitter_collage';
GRANT ALL PRIVILEGES ON `twitter_collage`.* TO 'twitter_collage'@'localhost'; 
