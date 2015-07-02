<?php
/**
 * class which represents a right in TestLink
 * @package 	TestLink
 */
class tlRight extends tlDBObject implements iDBBulkReadSerialization
{
	/**
	 * @var string the name of the right
	 */
	public $name;
	
	/**
	 * constructor
	 * 
	 * @param resource $db database handler
	 */
	function __construct($dbID = null)
	{
		parent::__construct($dbID);
		$this->activateCaching = true;
	}
	
	/** 
	 * brings the object to a clean state
	 * 
	 * @param integer $options any combination of TLOBJ_O_ Flags
	 */
	protected function _clean($options = self::TLOBJ_O_SEARCH_BY_ID)
	{
		$this->name = null;
		if (!($options & self::TLOBJ_O_SEARCH_BY_ID))
		{	
			$this->dbID = null;
    }  
	}
	
	/** 
	 * Magic function, called by PHP whenever a tlRight object should be printed
	 * 
	 * @return string returns the name of the right
	 */
	public function __toString()
	{
		return $this->name;
	}
	
	/**
	 * Initializes the right object
	 * 
	 * @param integer $dbID the database id of the right
	 * @param string $name the name of the right
	 **/
	function initialize($dbID, $name)
	{
		$this->dbID = $dbID;
		$this->name = $name;
	}
	
	/* Copies a tlRole object from another
	 * 
	 * @param $role tlRole the role which should be used to initialize this role 
	 * 
	 * @return integer always returns tl::OK
	 * @see lib/functions/tlDBObject#copyFromCache($object)
	 */
	public function copyFromCache($right)
	{
		$this->name = $right->name;
		
		return tl::OK;
	}
	/** 
	 * Read a right object from the database
	 *
	 * @param resource &$db reference to database handler
	 * @param interger $option any combination of TLOBJ_O_ flags
	 * 
	 * @return integer returns tl::OK on success, tl::ERROR else
	 */
	public function readFromDB(&$db,$options = self::TLOBJ_O_SEARCH_BY_ID)
	{
		if ($this->readFromCache() >= tl::OK)
    {  
			return tl::OK;
    }

		$readSucceeded = tl::ERROR;	
		$this->_clean($options);
		$query = $this->getReadFromDBQuery($this->dbID,$options);
		
    $info = $db->fetchFirstRow($query);
		if ($info)
    {  
			$readSucceeded = $this->readFromDBRow($info);
		}

		if ($readSucceeded >= tl::OK)
    {  
			$this->addToCache();	
		}

		return $info ? tl::OK : tl::ERROR;
	}

	/* Initializes a right object, from a single row read by a query obtained by getReadFromDBQuery 
	 * @see lib/functions/iDBBulkReadSerialization#readFromDBRow($row)
	 * @param $row array map with keys 'id',description'
	 */
	public function readFromDBRow($row)
	{
		$this->initialize($row['id'],$row['description']);
		
		return tl::OK;
	}
	
	/* Returns a query which can be used to read one or multiple rights from a db
	 * @param $ids array integer array of db ids (from rights)
	 * @param integer $options any combination of TLOBJ_O_ Flags
	 * @see lib/functions/iDBBulkReadSerialization#getReadFromDBQuery($ids, $options)
	 */
	public function getReadFromDBQuery($ids,$options = self::TLOBJ_O_SEARCH_BY_ID)
	{
		$tables = tlObject::getDBTables('rights');
		$query = "SELECT id,description FROM {$tables['rights']} ";
		
		$clauses = null;
		if ($options & self::TLOBJ_O_SEARCH_BY_ID)
		{
			if (!is_array($ids))
      {  
				$clauses[] = "id = {$ids}";
			}
      else
      {		
				$clauses[] = "id IN (".implode(",",$ids).")";
      }  
		}

		if ($clauses)
    {  
			$query .= " WHERE " . implode(" AND ",$clauses);
		}	
		return $query;	
	}
	
	/**
	 * Get a right by its database id
	 * 
	 * @param resource &$db reference to database handler
	 * @param integer $id the database identifier
	 * @param integer $detailLevel the detail level, any combination TLOBJ_O_GET_DETAIL_ flags
	 *
	 * @return tlRight returns the create right or null
	 */
	static public function getByID(&$db,$id,$detailLevel = self::TLOBJ_O_GET_DETAIL_FULL)
	{
		return tlDBObject::createObjectFromDB($db,$id,__CLASS__,self::TLOBJ_O_SEARCH_BY_ID,$detailLevel);
	}
	
	/**
	 * Get multiple rights by their database ids
	 * 
	 * @param resource &$db reference to database handler
	 * @param array $ids the database identifier
	 * @param integer $detailLevel the detail level, any combination TLOBJ_O_GET_DETAIL_ flags
	 *
	 * @return tlRight returns the create right or null
	 */  
	static public function getByIDs(&$db,$ids,$detailLevel = self::TLOBJ_O_GET_DETAIL_FULL)
	{
		return tlDBObject::createObjectsFromDB($db,$ids,__CLASS__,false,$detailLevel);
	}

	/** 
	 * @param resource &$db reference to database handler
	 **/    
	static public function getAll(&$db,$whereClause = null,$column = null,
	                              $orderBy = null,$detailLevel = self::TLOBJ_O_GET_DETAIL_FULL)
	{
		$tables = tlObject::getDBTables('rights');
		$sql = " SELECT id FROM {$tables['rights']} ";
		if (!is_null($whereClause))
    {  
			$sql .= ' ' . $whereClause;
	  } 
		$sql .= is_null($orderBy) ? " ORDER BY id ASC " : $orderBy;
		return tlDBObject::createObjectsFromDBbySQL($db,$sql,'id',__CLASS__,true,$detailLevel);
	}

	/** 
	 * @param resource &$db reference to database handler
	 **/    
	public function writeToDB(&$db)
	{
		//@TODO schlundus, now i removed the potentially modified object from the cache
		//another optimization could be read the new contents if storing was successfully into the
		//cache
		$this->removeFromCache();
		return self::handleNotImplementedMethod(__FUNCTION__);
	}
	
	/** 
	 * @param resource &$db reference to database handler
	 **/    
	public function deleteFromDB(&$db)
	{
		$this->removeFromCache();
		return self::handleNotImplementedMethod(__FUNCTION__);
	}
}
?>