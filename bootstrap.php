<?php
/**
 * @package    Firefox 4 Twitter Party
 * @subpackage server
 * @version    v.0.4
 * @author     Andre Torgal <andre@quodis.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */


DEFINE('LIB_PATH', dirname(__FILE__) . '/lib');
DEFINE('REQ_ID', isset($_REQUEST['reqId']) ? $_REQUEST['reqId'] : 'NOID');

DEFINE('NL', "\n");

DEFINE('UPLOAD_FILE_FIELD', 'file');

DEFINE ('AJAX',   'ajax');
DEFINE ('HJSON',  'hjson');
DEFINE ('IMAGE',  'image');
DEFINE ('HTML',   'html');
DEFINE ('SCRIPT', 'script');

// TODO AUTOLOAD
require LIB_PATH . '/tiny.lib.php';
require LIB_PATH . '/twitterparty.model.php';
require LIB_PATH . '/Curl.class.php';
require LIB_PATH . '/Image.class.php';
require LIB_PATH . '/Mosaic.class.php';
require LIB_PATH . '/Twitter.class.php';

Debug::setCtx(basename(CONTEXT));
Debug::setLogMsgFile('/var/log/twitterparty/msg.log');
Debug::setLogErrorFile('/var/log/twitterparty/error.log');


// TODO SESSION
session_start();

// DEBUG
set_error_handler(array('Debug', 'handleError'));


/**
 * boot db
 * @param array $config (by reference)
 */
function initDb(array & $config)
{
	require LIB_PATH . '/Db.class.php';
	require LIB_PATH . '/DataResult.class.php';
	foreach ($config['Db']['databases'][0]['connections'] as $id => $connectionDetails)
	{
		Db::addConnection($id, $connectionDetails);
	}
}

// CONFIG
if (!defined('NO_CONFIG'))
{
	global $config;
	// load
	include dirname(__FILE__) . '/config/config.php';
	// configure facebook, cache and game
	Cache::configure($config);
	Image::configure($config);
	Mosaic::configure($config);
	Twitter::configure($config);

	// connect cache + db
	Cache::connect();
	if (!defined('NO_DB')) initDb($config);

}


?>