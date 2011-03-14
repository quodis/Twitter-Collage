<?php
/**
 * @package    Firefox 4 Twitter Party
 * @subpackage server
 * @version    v.0.1
 * @author     Andre Torgal <andre@quodis.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 *
 * WARNING this file defines serveral classes:
 *  - Req
 *  - Dispatch
 *  - Cache
 *  - Audit
 *  - Debug
 */


/**
 * Request
 */
class Req
{
	/**
	 * @var integer
	 */
	private static $_time = null;

	/**
	 * @return integer
	 */
	public static function time()
	{
		if (!self::$_time) self::$_time = time();

		return self::$_time;
	}


	// ---- cli arguments


	/**
	 * @param string $arg (number, number range (1-23) or number list (23,34,..)
	 *
	 * @return array
	 */
	public static function getIntegerListFromArg($arg)
	{
		$retList = array();

		$arg = trim($arg);

		$range = explode('-', $arg);

		$list = explode(',', $arg);

		if (count($range) == 2)
		{
			if ($range[0] && $range[0] <= $range[1])
			{
				for ($i = $range[0]; $i <= $range[1]; $i++) $retList[] = $i;
			}
		}
		elseif (count($list) > 1)
		{
			foreach($list as $v) if ($v && (int)$v == $v) $retList[] = $v;
		}
		else if ($arg && (int)$arg == $arg) $retList = array($arg);

		return $retList;
	}

}

/**
 * dispatcher class
 *
 * depends on CLIENT constant
 */
class Dispatch
{
	/**
	 * @const string used to forward messages through redirects
	 */
	const MSG_REQUEST_VAR = 'msg';
	/**
	 * @const string used to store message in session between requests
	 */
	const MSG_SESSION_KEY = 'Dispatch::message::';

	/**
	 * dispatch and exit
	 *
	 * @param string $code
	 * @param string $contents
	 * @param array $data
	 * @param array $log
	 *
	 * @return unknown_type
	 */
	public static function now($code, $contents, & $data = null, array & $log = null)
	{
		switch (CLIENT)
		{

			// ---- ---- ---- IMAGE dispatcher
			case IMAGE:
				header('Content-type: image/jpeg');
				// TODO SERVER ERROR IMAGE!?
				if ($code == 1) echo $contents;
				break;

			// ---- ---- ---- HTML dispatcher
			case HTML:
				if (!headers_sent()) header('Content-type: text/html');
				echo $contents;
				break;

			// ---- ---- ---- AJAX dispatcher
			case AJAX:
				header('Content-type: application/text-json');

				$var = array(
					'code' => $code,
					'msg' => $contents,
					'payload' => $data
				);

				echo json_encode($var);

				break;

			// ---- ---- ---- HJSON dispatcher (used for uploads)
			case HJSON:

				header('Content-type: text/html');

				$var = array(
					'code' => $code,
					'msg' => $contents,
					'payload' => $data,
				);

				Debug::logMsg(json_encode($var), 'TERMINATE');

				echo '<html><body>' . NL;
				echo json_encode($var) . ';' . NL;
				echo '</body></html>'. NL;

				break;

			// ---- ---- ---- SCRIPT dispatcher
			case SCRIPT:
				if ($data) Debug::logMsg($data);
				if ($code == 1)
				{
					Debug::logMsg($contents, 'TERMINATE ');
				}
				else Debug::logError($contents, 'TERMINATE (ERROR) ');
				break;
		}

		// log
		if (CLIENT != SCRIPT && $log) Debug::logMsg($log, $contents);

		exit();
	}


	/**
	 * @param string $url (optional, defaults to current url, either framed or not)
	 * @param string $append (optional, add GET vars, either starting from ? or a path/to/something)
	 */
	public static function redirect($url = null, $append = null)
	{
		// default url?
		if (!$url) $url = FB::getUrl($append);

		switch (CLIENT)
		{
			case HTML:
				// send header
				header('Location: ' . $url);
				exit();

			case ENCODED:
			case AJAX:
				// delegate to client
				$data = array(
					'redirect' => $url
				);
				Dispatch::now(511, null, $data);

			default:
				header('HTTP/1.0 511 Coder made a boo');
				exit();

		}
		exit();
	}


	/**
	 * stores message
	 *
	 * @param mixed $message
	 *
	 * @return string with url expression msg=123ab
	 */
	public static function sendMessage($message)
	{
		$value = json_encode($message);

		// reuse existing message token
		$msgId = isset($_REQUEST[self::MSG_REQUEST_VAR]) ? $_REQUEST[self::MSG_REQUEST_VAR] : null;
		// but validate it first
		if (!preg_match('/^[0-9A-F]{5}$/', $msgId)) $msgId = null;
		// or generate one from message
		if (!$msgId) $msgId = substr(md5($value), -5);

		// store in session
		$key = self::MSG_SESSION_KEY . $msgId;
		$_SESSION[$key] = $value;

		return self::MSG_REQUEST_VAR . '=' . $msgId;
	}


	/**
	 * get message stored in session, using requested msgId key
	 *
	 * @return array or null
	 */
	public static function getMessage()
	{
		// not requested
		if (!isset($_REQUEST[self::MSG_REQUEST_VAR])) return;

		// session key
		$key = self::MSG_SESSION_KEY . $_REQUEST[self::MSG_REQUEST_VAR];

		// if exists
		return (isset($_SESSION[$key])) ? json_decode($_SESSION[$key], TRUE) : null;
	}

}


/**
 * memcache
 */
class Cache
{

	/**
	 * configuration
	 *
	 * Cache
	 *   enabled: 1
	 *   servers:
	 *	 - address: 127.0.0.1
	 *	   port: 11211
	 *
	 * @var array
	 */
	private static $_config;

	/**
	 * @var memcached handler
	 */
	private static $_handler;


	/**
	 * @param array $config (by reference)
	 */
	public static function configure(array & $config)
	{
		self::$_config = $config;
	}



	/**
	 * connect
	 */
	public static function connect()
	{
		// drop out if not enabled
		if (!self::$_config['Cache']['enabled']) return FALSE;

		// loop servers
		$servers = & self::$_config['Cache']['servers'];
		foreach ($servers as $key => $server)
		{
			// connect
			self::$_handler = memcache_connect($server['host'], $server['port']);
		}
	}


	/**
	 * store a key/value pair in cache
	 *
	 * @param string $cacheKey
	 * @param mixed $cacheValue
	 * @param integer $TTL
	 */
	public static function set($cacheKey, & $cacheValue, $TTL)
	{
		// drop out if not enabled
		if (!self::$_config['Cache']['enabled']) return FALSE;

		//dd($cacheValue, 'CACHE SET ' . $cacheKey . ' » ' . gettype($cacheValue));

		//
		$ok = self::$_handler->set($cacheKey, $cacheValue, MEMCACHE_COMPRESSED, $TTL);

		$message = $cacheKey . ' (ttl:' . $TTL . ')';
		audit::call('Cache', $ok ? 'set/OK' : 'set/FAIL', $message);
	}


	/**
	 * push a value into a cache key
	 *
	 * @param string $cacheKey
	 * @param mixed $cacheValue
	 */
	public static function push($cacheKey, & $cacheValue)
	{
		// drop out if not enabled
		if (!self::$_config['Cache']['enabled']) return FALSE;
		//
		return self::$_handler->push($cacheKey, $cacheValue);
	}


	/**
	 * fetch a value from cache
	 *
	 * @param string $cacheKey
	 *
	 * @return mixed
	 */
	public static function get($cacheKey)
	{
		// drop out if not enabled
		if (!self::$_config['Cache']['enabled']) return FALSE;
		//
		$ret = self::$_handler->get($cacheKey);

		//dd($ret, 'CACHE GET ' . $cacheKey . ' » ' . gettype($ret));

		audit::call('Cache', $ret ? 'get/OK' : 'get/MISS', $cacheKey);

		return $ret;
	}

	/**
	 * pop a value from a list
	 *
	 * @param string $cacheKey
	 *
	 * @return mixed
	 */
	public static function pop($cacheKey)
	{
		// drop out if not enabled
		if (!self::$_config['Cache']['enabled']) return FALSE;
		//
		return self::$_handler->pop($cacheKey);
	}


	/**
	 * delete a key/value pair from cache
	 *
	 * @param string $cacheKey
	 */
	public static function delete($cacheKey)
	{
		// drop out if not enabled
		if (!self::$_config['Cache']['enabled']) return FALSE;
		//
		$ok = self::$_handler->delete($cacheKey);

		audit::call('Cache', $ok ? 'delete/OK' : 'delete/FAIL', $cacheKey);

		return $ok;
	}


	/**
	 * getch a value and delete it
	 *
	 * @param string $cacheKey
	 *
	 * @return mixed
	 */
	public static function flush($cacheKey)
	{
		// drop out if not enabled
		if (!self::$_config['Cache']['enabled']) return FALSE;
		//
		$value = self::$get($cacheKey);
		self::$delete($cacheKey);
		return $value;
	}

}


/**
 * tiny debug/logger class
 *
 * will debug to php error log until you call setLogMsgFile() and setLogErrorFile()
 */
class Debug
{

	/**
	 * @var string
	 */
	private static $_logMsgFile;
	/**
	 * @var string
	 */
	private static $_logErrorFile;

	/**
	 * @var boolean
	 */
	private static $_logEnabled = FALSE;
	/**
	 * @var boolean
	 */
	private static $_forceLogToFile = FALSE;

	/**
	 * @var string message prefix
	 */
	private static $_context = '';

	/**
	 * set log message file
	 * @param string $logMsgFile
	 */
	public static function setLogMsgFile($logMsgFile)
	{
		self::$_logMsgFile = $logMsgFile;
	}

	public static function setLogErrorFile($logErrorFile)
	{
		self::$_logErrorFile = $logErrorFile;
	}

	public static function logEnabled($enable)
	{
		self::$_logEnabled = $enable;
	}
	public static function setForceLogToFile($force)
	{
		self::$_forceLogToFile = $force;
	}

	/**
	 *  set prefix
	 *
	 * @param string $ctx
	 */
	public static function setCtx($ctx)
	{
		self::$_context = $ctx;
	}


	/**
	 * log a message
	 *
	 * @param mixed $var
	 * @param string $msg
	 */
	public static function logMsg($var, $msg = null)
	{
		return self::_log(self::$_logMsgFile, $var, $msg, 0);
	}


	/**
	 * log an error
	 *
	 * @param mixed $var
	 * @param string $msg
	 */
	public static function logError($var, $msg = null)
	{
		return self::_log(self::$_logErrorFile, $var, $msg, 1);
	}


	/**
	 * use this as error handler
	 *
	 * @param(s) mixed, mixed, mixed,...
	 */
	public static function handleError()
	{
		$args = func_get_args();
		$message  = 'ERROR '  . $args[1] . ' (' . $args[2] . ':' . $args[3] . ')';

		if (is_array($args[4])) foreach ($args[4] as $name => $value) {
			$args[4][$name] = substr(serialize($value), 0, 100);
		}

		self::logError($args, $message);
		Dispatch::now(0, 'Sorry, something went terribly wrong.');
	}


	// ---- internal


	/**
	 * internal log
	 * @param string $file
	 * @param mixed $var
	 * @param string $msg
	 * @param boolean $isError
	 */
	private static function _log($file, $var, $msg, $isError)
	{
		// dump vars
		if (!is_string($var))
		{
			// dump (remove warnings)
			$var = @print_r($var, TRUE);
			$var = "\n------ " . $var;
		}

		$text = $msg ? $msg . ' ' . $var . ' ' . $msg : $var;

		$text = date('Y-m-d H:i:s') . ' | ' . Req::time() . ' > ' . self::$_context . ' > ' . $text . NL;

		if (self::$_forceLogToFile || (CLIENT != SCRIPT && self::$_logEnabled))
		{
			if ($file)
			{
				file_put_contents($file, $text, FILE_APPEND);
			}
			else error_log($text);
		}
		if (CLIENT == SCRIPT) echo ($isError) ? '>>>' . $text : $text;

		return $text;
	}
}


/**
 * tiny audit/profiler class
 *
 * please note that auditing has a cost (specially timers)
 */
class Audit {

	/**
	 * configuration
	 *
	 * Audit
	 *   tags:
	 *	 - cache
	 *	 - db
	 *
	 * @var array
	 */
	private static $_config;

	/**
	 * stores timers
	 *
	 * @var array
	 */
	private static $_timers = array();
	/**
	 * stores counters
	 *
	 * @var array
	 */
	private static $_counters = array();
	/**
	 * collects audit data
	 *
	 * @var array
	 */
	private static $_auditData = null;



	/**
	 * @param array $config (by reference)
	 */
	public static function configure(array & $config)
	{
		self::$_config = $config;
	}


	// ---- timer & counters


	/**
	 * init/increment a counter
	 *
	 * @param string
	 */
	public static function counterInc($key)
	{
		if (!isset(self::$_counters[$key])) self::$_counters[$key] = 0;
		self::$_counters[$key]++;
	}


	/**
	 * return a counter's elapsed time
	 *
	 * @param string
	 *
	 * @return float
	 */
	public static function counterGet($key)
	{
		if (!isset(self::$_counters[$key]))
		{
			return 0;
		}
		else return self::$_counters[$key];
	}


	/**
	 * init a timer
	 *
	 * @param string
	 */
	public static function timerInit($key)
	{
		self::$_timers[$key] = microtime(TRUE);
		return self::$_timers[$key];
	}


	/**
	 * return a counter's elapsed time
	 *
	 * @param string
	 *
	 * @return float
	 */
	public static function timerElapsed($key)
	{
		if (isset(self::$_timers[$key]))
		{
			return sprintf('%.4f', microtime(TRUE) - self::$_timers[$key]);
		}
		else return -1;
	}


	// ---- audit ----


	/**
	 * check if audit is globally active, or enabled for specific tag
	 *
	 * also inits audit data
	 *
	 * @param string $tag (optional)
	 *
	 * @return boolean
	 */
	public static function enabled($tag = null)
	{
		if (!isset(self::$_config['Audit']['tags'])) return FALSE;

		// init audit data
		if (!isset(self::$_auditData))
		{
			self::$_auditData = array();

			// for each enabled tag
			foreach (self::$_config['Audit']['tags'] as $auditKey)
			{
				self::$_auditData[$auditKey] = array(
					'count' => 0,
					'elapsed' => 0,
					'methods' => array(),
					'flags' => array(),
				);
			}
		}

		// is overall enabled? or specific tag enabled?
		return ($tag) ? TRUE : array_key_exists($key, self::$_auditData);
	}


	/**
	 * use at beggining of proc/function/block to init the timer
	 *
	 * signature is used to distinguish between different calls, make sure it's unique
	 * usually you can simply pass the func arguments, or local scope..
	 * but you can also generate your signature for each call (make sure you keep it to use in call)
	 *
	 * @param string $tag
	 * @param string $procName
	 * @param mixed $timerSignature (optional)
	 */
	public static function callInit($tag, $procName, & $timerSignature = null)
	{
		if (!self::enabled($tag)) return;

		$timer = $tag . $procName . serialize($timerSignature);

		self::timerInit($timer);
	}

	/**
	 * use at beggining of proc/function/block to init the timer
	 *
	 * @param string $tag
	 * @param string $procName
	 * @param $timerSignature
	 * @param $ignoreTimer
	 * @return unknown_type
	 */
	public static function call($tag, $procName, & $timerSignature = null, $ignoreTimer = FALSE)
	{
		if (!self::enabled($tag)) return;

		self::$_auditData[$tag]['count']++;

		if (!isset(self::$_auditData[$tag]['methods'][$procName]))
		{
			self::$_auditData[$tag]['methods'][$procName] = array('count' => null, 'elapsed' => null, 'calls' => array());
		}

		self::$_auditData[$tag]['methods'][$procName]['count']++;

		if ($timerSignature)
		{
			$elapsed = null;

			if (!$ignoreTimer)
			{
				$timer = $tag . $procName . serialize($timerSignature);
				$elapsed = self::timerElapsed($timer);

				if ($elapsed < 0) $elapsed = 0;

				self::$_auditData[$tag]['methods'][$methodName]['elapsed'] += $elapsed;
				self::$_auditData[$tag]['elapsed'] += $elapsed;
			}

			self::$_auditData[$tag]['methods'][$methodName]['calls'][] = array($elapsed, $timerSignature);
		}
	}


	/**
	 *
	 *
	 * @param string $tag
	 * @param string $flagName
	 * @param $value
	 * @return unknown_type
	 */	public static function auditFlag($tag, $flagName, $value = null)
	{
		if (!self::enabled($tag)) return;

		self::$_auditData[$tag]['flags'][$flagName] = $value;
	}


	public static function auditDump()
	{
		$globalData = array(
			'memory' => memory_get_usage(true),
			'mpeak' => memory_get_peak_usage(true),
			'count' => 0,
			'elapsed' => 0,
			'methods' => 0,
			'flags' => 0,
		_DATA => self::$_auditData
		);

		foreach (self::$_auditData as $data)
		{
			$globalData['count'] += $data['count'];
			$globalData['elapsed'] += $data['elapsed'];
			$globalData['methods'] += count($data['methods']);
			$globalData['flags'] += count($data['flags']);
		}

		return $globalData;
	}
}



/**
 * recursive makedir() with optional chmod() and chgrp()
 *
 * @param string $dir
 * @param string $dirPermissions (optional)
 * @param string $group (optional)
 *
 * @return bollean
 */
function rmkdir($dir, $dirPermissions = null, $group = null)
{
	while (!file_exists(dirname($dir))) if(!rmkdir(dirname($dir), $dirPermissions, $group)) return;

	if (!mkdir($dir)) return;

	if ($dirPermissions) if (!chmod($dir, octdec($dirPermissions))) return;

	if ($group) if (!chgrp($dir, $group)) return;

	return TRUE;
}

/**
 * call_user_func_array changes PHP 5.3+
 *
 * @param array $arr
 *
 * @return array
 */
function refValues($arr)
{
	if (strnatcmp(phpversion(),'5.3') >= 0)
	{
		$refs = array();
		foreach($arr as $key => $value)
		{
			$refs[$key] = &$arr[$key];
		}
		return $refs;
	}
	return $arr;
}


/**
 * logs message
 *
 * @param mixed $var
 * @param string $msg (optional)
 */
function lg($var, $msg = null) {
	Debug::logMsg($var, $msg);
}


/**
 * dumps message/var
 *
 * @param mixed $var
 * @param string $msg (optional)
 *
 * @return mixed
 */
function dd($var, $msg = null) {
	$msg = Debug::logMsg($var, $msg);
	if (CLIENT == HTML)
	{
		echo '<pre>' . NL;
		echo $msg . NL;
		echo '</pre>' . NL;
	}
	return $var;
}


/**
 * dumps message/var and exits
 *
 * @param mixed $var
 * @param string $msg (optional)
 */
function dk($var, $msg = null) {
	$msg = Debug::logMsg($var, $msg);
	if (CLIENT == HTML)
	{
		echo '<pre>' . NL;
		echo $msg . NL;
		echo '</pre>' . NL;
	}
	exit();
}

?>