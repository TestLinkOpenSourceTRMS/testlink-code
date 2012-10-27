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
			$sql .= ' '.$whereClause;
	  }
		$sql .= is_null($orderBy) ? " ORDER BY id ASC " : $orderBy;
		return tlDBObject::createObjectsFromDBbySQL($db,$sql,'id',__CLASS__,true,$detailLevel);
	}

	/** 
	 * @param resource &$db reference to database handler
	 **/    
	public function writeToDB(&$db)
	{
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
	
	
	static function getRightsCfg()
	{
    $cfg = new stdClass();
    $l18nCfg = array('desc_testplan_execute' => null,'desc_testplan_create_build' => null,
							       'desc_testplan_metrics' => null,'desc_testplan_planning' => null,
							       'desc_user_role_assignment' => null,'desc_mgt_view_tc' => null,
								     'desc_mgt_modify_tc'  => null,'mgt_testplan_create' => null,
                     'desc_mgt_view_key' => null,'desc_mgt_modify_key' => null,
								     'desc_keyword_assignment' => null,'desc_mgt_view_req' => null,
                     'desc_mgt_modify_req' => null,'desc_req_tcase_link_management' => null,
                     'desc_mgt_modify_product' => null,'desc_project_inventory_management' => null,
                     'desc_project_inventory_view' => null,
                     'desc_cfield_view' => null,'desc_cfield_management' => null,
                     'desc_platforms_view' => null,'desc_platforms_management' => null,
                     'desc_issuetrackers_view' => null,'desc_issuetrackers_management' => null,
                     'desc_mgt_modify_users' => null,'desc_role_management' => null,
                     'desc_user_role_assignment' => null,
                     'desc_mgt_view_events' => null, 'desc_events_mgt' => null,
                     'desc_mgt_unfreeze_req' => null,
                     'right_exec_edit_notes' => null, 'right_exec_delete' =>null);

    $l18n = init_labels($l18nCfg);
    
	  $cfg->testplans = array("testplan_execute" => $l18n['desc_testplan_execute'],
                            "testplan_create_build" => $l18n['desc_testplan_create_build'],
							              "testplan_metrics" => $l18n['desc_testplan_metrics'],
							              "testplan_planning" => $l18n['desc_testplan_planning'],
							              "testplan_user_role_assignment" => $l18n['desc_user_role_assignment']);
	
	
		$cfg->testcases = array("mgt_view_tc" => $l18n['desc_mgt_view_tc'],
								            "mgt_modify_tc" => $l18n['desc_mgt_modify_tc'],
								            "mgt_testplan_create" => $l18n['mgt_testplan_create']);


    $cfg->keywords = array("mgt_view_key" => $l18n['desc_mgt_view_key'],
								           "mgt_modify_key" => $l18n['desc_mgt_modify_key'],
								           "keyword_assignment" => $l18n['desc_keyword_assignment']);

    $cfg->requirements = array("mgt_view_req" => $l18n['desc_mgt_view_req'],
								               "mgt_modify_req" => $l18n['desc_mgt_modify_req'],
								               "req_tcase_link_management" => $l18n['desc_req_tcase_link_management'],
								               "mgt_unfreeze_req" => $l18n['desc_mgt_unfreeze_req']);


	  $cfg->testprojects = array("mgt_modify_product" => $l18n['desc_mgt_modify_product'],
                               "project_inventory_management" => $l18n['desc_project_inventory_management'],
                               "project_inventory_view" => $l18n['desc_project_inventory_view'] );						


	  $cfg->customfields = array("cfield_view" => $l18n['desc_cfield_view'],
						                   "cfield_management" => $l18n['desc_cfield_management']);
	
	  $cfg->platforms = array("platform_view" => $l18n['desc_platforms_view'],
						                "platform_management" => $l18n['desc_platforms_management']);

	  $cfg->issuetrackers = array("issuetracker_view" => $l18n['desc_issuetrackers_view'],
                                "issuetracker_management" => $l18n['desc_issuetrackers_management']);


	  $cfg->users = array("mgt_users" => $l18n['desc_mgt_modify_users'],
								        "role_management" => $l18n['desc_role_management'],
								        "user_role_assignment" => $l18n['desc_user_role_assignment']); 

	  $cfg->system = array("mgt_view_events" => $l18n['desc_mgt_view_events'],
	                       "events_mgt" => $l18n['desc_events_mgt']);

    // TICKET 
	  $cfg->execution = array("exec_edit_notes" => $l18n['right_exec_edit_notes'],
	                          "exec_delete" => $l18n['right_exec_delete']);


    // Do some grouping, needed by other methods
    // some rights are system wide => test project has no effect
    $cfg->systemWideRange = array_merge($cfg->users,$cfg->system,$cfg->testprojects);
    unset($cfg->systemWideRange["testproject_user_role_assignment"]);
     
    $cfg->testprojectWideRange = array_merge($cfg->systemWideRange,$cfg->testcases,$cfg->keywords,
                                             $cfg->requirements);    

    // 20121013 - not clear with platforms,customfields,issuetrackers,testplans are not 
    // present on one of WideRange config, need to try to understand 
    
	  return $cfg;
	}


	
}
?>