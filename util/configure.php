<?php
/**
 * @package    Firefox 4 Twitter Party
 * @subpackage server
 * @version    v.0.4
 * @author     Andre Torgal <andre@quodis.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */


/**
 * escape from global scope
 */
function main()
{
	DEFINE('CLIENT', 'script');
	DEFINE('CONTEXT', __FILE__);
	DEFINE('NO_CONFIG', 'TRUE');
	DEFINE('NO_SESSION', 'TRUE');
	include dirname(__FILE__) . '/../bootstrap.php';

	require_once LIB_PATH . '/spyc-0.4.5/spyc.php';

	$configDir = realpath(dirname(__FILE__) . '/../config');

	if (file_exists($configDir . '/config.yaml'))
	{
		$data = Spyc::YAMLLoad($configDir . '/config.yaml');

		$contents = "<?php \$config = unserialize('" . serialize($data) . "'); ?>";

		$fileName = $configDir . '/config.php';

		file_put_contents($fileName, $contents);
		chmod($fileName, octdec($data['Config']['filePermissions']));
		chgrp($fileName, $data['Config']['group']);
	}
	else
	{
		Dispatch::now(0, 'configuration file config/config.yaml not found');
	}
	Dispatch::now(1, 'OK');
}

try
{
	main();
}
catch(Exception $e) {
	Debug::logError($e, 'EXCEPTION ' . $e->getMessage());
	Dispatch::now(0, 'EXCEPTION ' . $e->getMessage());
}

?>