<?php
/**
 * @package    Firefox 4 Twitter Party
 * @subpackage server
 * @version    v.0.1
 * @author     Andre Torgal <andre@quodis.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License


 /**
 * wrapper tha can hold a database result source or offline data
 */
final class DataResult
{

	/**
	 * @const string result source types
	 */
	const TYPE_LIVE = '_resultTypeLive';
	const TYPE_DATA = '_resultTypeData';

	/**
	 * @var array stores data
	 */
	private $_data = null;
	/**
	 * stores connection id (used in Db::...)
	 */
	private $_connectionId = null;
	/**
	 * stores result source
	 *
	 * @var mixed
	 */
	private $_resultSource = null;
	/**
	 * @var boolean
	 */
	private $_success = null;
	/**
	 * @var string result source type
	 */
	private $_type = null;
	/**
	 * @var array stores fields
	 */
	private $_fields = null;
	/**
	 * @var integer
	 */
	private $_total = null;
	/**
	 * @var integer
	 */
	private $_page = null;
	/**
	 * @var integer
	 */
	private $_pageSize = null;


	/**
	 * constructor
	 *
	 * @param boolean $success
	 * @param string $connectionId
	 * @param mixed $resultSource
	 * @param string $type
	 */
	public function __construct($success = TRUE, $connectionId = null, $resultsource = null, $type = null)
	{
		// store
		$this->_success = $success;
		$this->_connectionId = $connectionId;
		$this->_resultSource = $resultsource;
		$this->_type = $type ? $type : self::TYPE_LIVE;
	}


	/**
	 * store data
	 *
	 * connection uses this method to populate fields from statement (if they are available)
	 *
	 * @param array $data (by reference)
	 */
	public function setData(array & $data)
	{
		// just store
		$this->_data = $data;
	}


	/**
	 * store fields
	 *
	 * connection uses this method to populate fields from statement (if they are available)
	 *
	 * @param array $fields
	 */
	public function setFields(array $fields)
	{
		//
		$this->_fields = $fields;
	}


	/**
	 * store total
	 *
	 * @param integer $total
	 */
	public function setTotal($total)
	{
		//
		$this->_total = $total;
	}


	/**
	 * store page and pageSize
	 *
	 * @param integer $page
	 * @param integer $pageSize
	 */
	public function setPageAndPageSize($page, $pageSize)
	{
		//
		$this->_page = $page;
		$this->_pageSize = $pageSize;
	}


	// ---- get


	/**
	 * fetch connection id
	 *
	 * @return string
	 */
	public function connectionId()
	{
		return $this->_connectionId;
	}


	/**
	 * fetch result source
	 *
	 * @return mixed
	 */
	public function resultsource()
	{
		return $this->_resultSource;
	}


	/**
	 * fetch next row
	 *
	 * @return array|boolean false if there are no more rows
	 */
	public function row()
	{
		switch ($this->_type)
		{
			case self::TYPE_LIVE:
				// use resource
				if ($this->_resultSource) return Db::row($this->_resultSource);
				break;

			case self::TYPE_DATA:
				// fetch from data
				if (isset($this->_data))
				{
					$arrayElement = each($this->_data);
					return $arrayElement[1];
				}
				break;
		}
		// no rows / no resource
		return null;
	}


	/**
	 * reset resultSource / data
	 */
	public function reset()
	{
		switch ($this->_type)
		{
			case self::TYPE_LIVE:
				// use resource
				if ($this->_resultSource) return Db::reset($this->_resultSource);
				break;

			case self::TYPE_DATA:
				// reset internal array
				reset($this->_data);
				break;
		}
	}


	/**
	 * fetch row count
	 *
	 * @return integer
	 */
	public function count()
	{
		switch ($this->_type)
		{
			case self::TYPE_LIVE:
				if ($this->_resultSource) return Db::count($this->_resultSource);
				break;

			case self::TYPE_DATA:
				if (isset($this->_data)) return count($this->_data);
				break;
		}
	}


	/**
	 * fetch last connection error
	 *
	 * @return string
	 */
	public function error()
	{
		if ($this->_type == self::TYPE_LIVE)
		{
			return Db::error($this->_connectionId);
		}
	}


	/**
	 * fetch total count
	 *
	 * @return integer
	 */
	public function total()
	{
		return isset($this->_total) ? $this->_total : $this->count();
	}


	/**
	 * fetch page
	 *
	 * @return integer
	 */
	public function page()
	{
		return $this->_page;
	}


	/**
	 * fetch integer
	 *
	 * @return integer
	 */
	public function pageSize()
	{
		return $this->_pageSize;
	}


	/**
	 * fetch success
	 *
	 * @return bool
	 */
	public function success()
	{
		return $this->_success;
	}


	/**
	 * fetch type
	 *
	 * @return string
	 */
	public function type()
	{
		return $this->_type;
	}


	/**
	 * return result fields
	 *
	 * if field info is stored return it
	 * otherwise use connection to fetch metadata
	 *
	 * @return array
	 */
	public function fields()
	{
		// fields were set by query?
		if (!isset($this->_fields))
		{
			// no,
			switch ($this->_type)
			{
				case self::TYPE_LIVE:
					// use connection to fetch fields associated with result
					if ($this->_resultSource) $this->_fields = Db::fields($this->_resultSource);
					break;

				case self::TYPE_DATA:
					if (isset($this->_data)) $this->_fields = array_keys($this->_data);
					break;
			}
		}
		// yes, return them
		return $this->_fields;
	}

}

?>