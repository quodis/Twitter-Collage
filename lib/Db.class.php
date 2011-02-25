<?php
/**
 * @pacjage    Firefox 4 Twitter Party
 * @subpackage server
 * @version    v.0.1
 * @author     Andre Torgal <andre@quodis.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

 /**
 * very simple connection handler
 * manages seversl connections
 * and returns results as DataResult object
 */
class Db
{

	/**
	 * @const string used inf config files
	 */
	const MODE_READ  = '_modeRead';
	const MODE_WRITE = '_modeWrite';

	/**
	 * @var array stores connection details
	 */
	private static $_connections = array();
	/**
	 * @var string id of default read connection
	 */
	private static $_defaultReadId = null;
	/**
	 * @var string id of default write connection
	 */
	private static $_defaultWriteId = null;

	/**
	 * static class, nothing to see here, move along
	 */
	private static function _constructor() { }


	/**
	 * @param array $config (by reference)
	 */
	public static function configure(array & $config)
	{
		foreach ($config['Db']['connections'] as $id => $connectionDetails)
		{
			self::addConnection($id, $connectionDetails);
		}
	}


	// ---- connections


	/**
	 * Adds a single connection to the DB, does not connect
	 *
	 * @param string $id
	 * @param array $connectionDetails (by reference)
	 *   mode: _modeRead|_modeWrite
	 *   host:
	 *   user:
	 *   pass:
	 *
	 * @throws Exception if unknown mode suplied
	 */
	public static function addConnection($id, array & $connectionDetails)
	{
		// validate mode
		$mode = & $connectionDetails['mode'];
		switch ($mode)
		{
			// store default
			case self::MODE_READ:
				if (!isset(self::$_defaultReadId)) self::$_defaultReadId = $id;
				break;

				// store default
			case self::MODE_WRITE:
				if (!isset(self::$_defaultWriteId)) self::$_defaultWriteId = $id;
				break;

				// !?
			default:
				throw new Exception('Invalid mode Db::addConnection() mode:' . $mode);
		}

		// init link
		$connectionDetails['linkResource'] = null;

		// add id for debug purposes
		$connectionDetails['id'] = $id;

		// store connection details
		self::$_connections[$id] = $connectionDetails;
	}


	/**
	 * cleanup all connections (usefull for tests)
	 */
	public static function cleanupAll()
	{
		// loop connections
		foreach (self::$_connections as $connection)
		{
			if (isset($connection['linkResource']))
			{
				$connection['linkResource']->close();
			}
		}
		// cleanup
		unset(self::$_connections);
		unset(self::$_defaultId);
	}


	/**
	 * close all connections (usefull for tests)
	 */
	public static function resetAll()
	{
		// loop connections
		foreach (self::$_connections as $key => $connection)
		{
			if (isset($connection['linkResource']))
			{
				$connection['linkResource']->close();
				self::$_connections[$key]['linkResource'] = null;
			}
		}
	}


	/**
	 * returns connection by id, or default connection
	 *
	 * @param string $mode
	 * @param string $connectionId (optional)
	 *
	 * @return array (by reference)
	 */
	public static function & _getConnection($mode, $connectionId = null)
	{
		// default connection id
		if (!$connectionId) if ($mode) switch ($mode)
		{
			case self::MODE_READ:
				// fetch default
				$connectionId = self::$_defaultReadId;
				break;

			case self::MODE_WRITE:
				// fetch default
				$connectionId = self::$_defaultWriteId;
				break;

				// !?
			default:
				throw new Exception('Invalid mode Db::_getConnection() mode:' . $mode);
		}

		// validate id
		if (!$connectionId || !isset(self::$_connections[$connectionId])) throw new Exception('Invalid connectionId:' . $connectionId);

		// fetch connection
		$connectionDetails = & self::$_connections[$connectionId];

		// validate mode (WhyTF?, lame)
		if ($mode && $connectionDetails['mode'] != $mode) throw new Exception('Inconsistent mode in Db::_getConnection() mode:' . $mode . ' connectionId:' . $connectionId . ' connectionMode:' . $connectionDetails['mode']);

		// return
		return $connectionDetails;
	}


	/**
	 * connects to sql server, stores link
	 *
	 * @param array & $connection (by reference)
	 *
	 * @throws Exception on fail
	 */
	private static function _connect(array & $connection)
	{
		$handler = mysqli_init();

		$handler->options(MYSQLI_INIT_COMMAND, "SET AUTOCOMMIT=1");
		$handler->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);

		// connect and select db
		if ($connection['name'])
		{
			$connected = $handler->real_connect($connection['host'], $connection['user'], $connection['pass'], $connection['name']);
		}
		// just connect
		else $connected = $handler->real_connect($connection['host'], $connection['user'], $connection['pass']);


		if ($connected)
		{
			$handler->set_charset('utf8');

			// store link
			$connection['linkResource'] = $handler;
		}
		// fail connect
		else throw new Exception('Failed connect in Db::_connect() :' . json_encode($connection));
	}


	// ---- commands


	/**
	 * executes a query and returns encapsulated result
	 * uses said connection or defaults to _modeRead default connection
	 *
	 * @param string $sql
	 * @param array $values (optional, by reference)
	 * @param string $connectionId (optional)
	 *
	 * @return DataResult
	 */
	public static function query($sql, $connectionId = null)
	{
		// connection details
		$connection = & self::_getConnection(self::MODE_READ, $connectionId);

		// connect?
		if (!$connection['linkResource']) self::_connect($connection);

		// execute
		$result = $connection['linkResource']->query($sql);

		// yeld
		return new DataResult(!!$result, $connection['id'], $result);
	}


	/**
	 * executes query and returns first row
	 * uses said connection or defaults to _modeRead default connection
	 *
	 * @param string $sql
	 * @param array $values (optional, by reference)
	 * @param string $connectionId (optional)
	 *
	 * @return array or null
	 */
	public static function queryRow($sql, $connectionId = null)
	{
		$result = self::query($sql, $connectionId);

		if ($result->success()) return $result->row();
	}


	/**
	 * executes query and returns the requested column's value of the first row
	 * uses said connection or defaults to _modeRead default connection
	 *
	 * @param string $sql
	 * @param array $values (optional, by reference)
	 * @param string $columnName
	 * @param string $connectionId (optional)
	 *
	 * @return array or FALSE
	 */
	public static function queryValue($sql, $columnName, $connectionId = null)
	{
		if ($row = self::queryRow($sql, $connectionId))
		{
			return $row[$columnName];
		}
	}


	/**
	 * returns the next row as an array or false if no more rows available
	 *
	 * @param mysqli_result $resource
	 *
	 * @return array or FALSE
	 */
	public static function row(mysqli_result $resource)
	{
		return mysqli_fetch_assoc($resource);
	}


	/**
	 * Retrieves the number of rows from a result set
	 *
	 * @param mysqli_result $resource
	 *
	 * @return array or FALSE
	 */
	public static function count(mysqli_result $resource)
	{
		return mysqli_num_rows($resource);
	}


	/**
	 * Moves the resource current selected row to the start of the resource
	 *
	 * @param mysqli_result $resource
	 */
	public static function reset(mysqli_result $resource)
	{
		mysqli_data_seek($resource, 0);
	}


	/**
	 * returns the last id generated by the a auto_increment primary key (if available)
	 *
	 * @param string $connectionId
	 *
	 * @return integer
	 */
	public static function lastInsertId($connectionId = null)
	{
		// connection details
		$connection = & self::_getConnection(self::MODE_WRITE, $connectionId);

		// connect?
		if (!$connection['linkResource']) self::_connect($connection);
		//
		return $connection['linkResource']->insert_id;
	}


	/**
	 * executes a query and return result
	 * uses said connection or defaults to _modeWrite default connection
	 *
	 * @param string $sql
	 * @param string $connectionId (optional)
	 *
	 * @return DataResult
	 */
	public static function execute($sql, $connectionId = null)
	{
		// connection details
		$connection = & self::_getConnection(self::MODE_WRITE, $connectionId);

		// connect?
		if (!$connection['linkResource']) self::_connect($connection);

		// execute it
		$ok = !!$connection['linkResource']->query($sql);

		// yeld
		return new DataResult($ok, $connection['id']);
	}


	/**
	 * returns the last error within connection
	 *
	 * @param string $connectionId
	 *
	 * @return string
	 */
	public static function error($connectionId)
	{
		// connection details
		$connection = & self::_getConnection(null, $connectionId);

		// connect?
		if (!$connection['linkResource']) self::_connect($connection);
		//
		return $connection['linkResource']->error;
	}


	/**
	 * executes the given sql file using given connection
	 *
	 * @param string $fileName
	 * @param string $connectionId
	 *
	 * @throws Exception if output generated by cmd
	 */
	public static function executeFile($fileName, $connectionId = null)
	{
		// @todo EXECUTE WRITE

		// connection details
		$connection = & self::_getConnection(self::MODE_WRITE, $connectionId);

		$host	 = $connection['host'];
		$userName = $connection['user'];
		$userPass = $connection['pass'];
		$dbName   = $connection['name'];

		// execute
		$shell = "mysql -h $host --user=$userName --password=$userPass --database=$dbName < $fileName";
		$output = shell_exec($shell . ' 2>&1 1> /dev/null');
		if (strlen($output)) throw new Exception('Mysql output:' . $output);
	}


	// ---- helper methods


	/**
	 * escapes special caracters in the string so it is safe to insert in mysql
	 *
	 * @param string $unescapedString
	 * @param string $connectionId (optional)
	 *
	 * @return string
	 */
	public static function escape($unescapedString, $connectionId = null)
	{
		// connection details
		$connection = & self::_getConnection(self::MODE_WRITE, $connectionId);

		// connect?
		if (!$connection['linkResource']) self::_connect($connection);
		//
		return $connection['linkResource']->real_escape_string($unescapedString);
	}


	/**
	 * starts a transaction
	 * uses said connection or defaults to _modeWrite default connection
	 *
	 * @param string $connectionId (optional, defaults to _modeWrite default connection)
	 *
	 * @return boolean
	 */
	public static function startTransaction($connectionId = null)
	{
		return self::execute('START TRANSACTION', $connectionId);
	}


	/**
	 * rollback transaction
	 * uses said connection or defaults to _modeWrite default connection
	 *
	 * @param string $connectionId (optional, defaults to _modeWrite default connection)
	 *
	 * @return booleans
	 */
	public static function rollback($connectionId = null)
	{
		return self::execute('ROLLBACK', $connectionId);
	}


	/**
	 * commits transaction
	 * uses said connection or defaults to _modeWrite default connection
	 *
	 * @param string $connectionId (optional, defaults to _modeWrite default connection)
	 *
	 * @return boolean
	 */
	public static function commit($connectionId = null)
	{
		return self::execute('COMMIT', $connectionId);
	}

}

?>