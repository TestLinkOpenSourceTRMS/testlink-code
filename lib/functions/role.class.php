<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: role.class.php,v $
 *
 * @version $Revision: 1.25 $
 * @modified $Date: 2009/06/04 19:22:01 $ $Author: schlundus $
 *
 * rev:
 *     20090221 - franciscom - hasRight() - BUG - function parameter name crashes with local variable
 *     20090101 - franciscom - writeToDB() problems with Postgres
 *                             due to wrong table name in insert_id() call.
 *     
 */
class tlRole extends tlDBObject
{
	public $name;
	public $description;
	public $rights;

	private $object_table = "";
	private $tables = "";
	protected $replacementRoleID;
	
	//options
	const ROLE_O_SEARCH_BYNAME = 2;
	
	//detail levels
	const TLOBJ_O_GET_DETAIL_RIGHTS = 1;
		
	const E_DBERROR = -2;	
	const E_NAMELENGTH = -3;
	const E_NAMEALREADYEXISTS = -4;
	const E_EMPTYROLE = -5;
		
	function __construct($dbID = null)
	{
		parent::__construct($dbID);
	    $this->tables = array('roles' => DB_TABLE_PREFIX . 'roles', 
	                          'users' => DB_TABLE_PREFIX . 'users',
                              'user_testproject_roles' => DB_TABLE_PREFIX . 'user_testproject_roles',
                              'user_testplan_roles' => DB_TABLE_PREFIX . 'user_testplan_roles',
	                          'role_rights' => DB_TABLE_PREFIX . 'role_rights',
	                          'rights' => DB_TABLE_PREFIX . 'rights'); 
	
		$this->object_table = $this->tables['roles']; 
		$this->replacementRoleID = config_get('role_replace_for_deleted_roles');
	}

	protected function _clean($options = self::TLOBJ_O_SEARCH_BY_ID)
	{
		$this->description = null;
		$this->rights = null;
		if (!($options & self::ROLE_O_SEARCH_BYNAME))
		{
			$this->name = null;
		}
		if (!($options & self::TLOBJ_O_SEARCH_BY_ID))
		{
			$this->dbID = null;
		}	
	}
	
	//BEGIN interface iDBSerialization
	public function readFromDB(&$db,$options = self::TLOBJ_O_SEARCH_BY_ID)
	{
		$this->_clean($options);
		$getFullDetails = ($this->detailLevel & self::TLOBJ_O_GET_DETAIL_RIGHTS);
		
		$query = "SELECT a.id AS role_id,a.description AS role_desc, a.notes ";
		if ($getFullDetails)
		{
			$query .= " ,c.id AS right_id,c.description ";
		}
		
		$query .= " FROM {$this->object_table} a ";
		
		if ($getFullDetails)
		{
			$query .= " LEFT OUTER JOIN {$this->tables['role_rights']} b ON a.id = b.role_id " . 
			          " LEFT OUTER JOIN {$this->tables['rights']}  c ON b.right_id = c.id ";
		}
		
		$clauses = null;
		if ($options & self::ROLE_O_SEARCH_BYNAME)
			$clauses[] = "a.description = '".$db->prepare_string($this->name)."'";
		if ($options & self::TLOBJ_O_SEARCH_BY_ID)
			$clauses[] = "a.id = {$this->dbID}";		
		if ($clauses)
			$query .= " WHERE " . implode(" AND ",$clauses);
			
		$rightInfo = $db->get_recordset($query);			 
		if ($rightInfo)
		{
			$this->dbID = $rightInfo[0]['role_id'];
			$this->name = $rightInfo[0]['role_desc'];
			$this->description = $rightInfo[0]['notes'];

			if ($getFullDetails)
			{
				$this->rights = $this->buildRightsArray($rightInfo);
			}	
		}
		return $rightInfo ? tl::OK : tl::ERROR;
	}

	/*
    function: writeToDB

    args: db: dbHandler
    
    returns: 

  */
	public function writeToDB(&$db)
	{
		$result = $this->checkDetails($db);
		if ($result >= tl::OK)
		{		
			if ($this->dbID)
			{
				$result = $this->deleteRightsFromDB($db);
				if ($result >= tl::OK)
				{
					$query = "UPDATE {$this->object_table} SET description = '".$db->prepare_string($this->name)."',".
							     "notes ='".$db->prepare_string($this->description)."'".
							     " WHERE id = {$this->dbID}";
					$result = $db->exec_query($query);	
				}
			}
			else
			{
				$query = "INSERT INTO {$this->object_table} (description,notes) VALUES ('".$db->prepare_string($this->name)."',".
						 "'".$db->prepare_string($this->description)."')";
				$result = $db->exec_query($query);	
				if($result)
				{
					$this->dbID = $db->insert_id($this->object_table);
				}	
			}
			$result = $result ? tl::OK : self::E_DBERROR;
			if ($result >= tl::OK)
			{
				$result = $this->addRightsToDB($db);
			}	
		}
		
		return $result;
	}
	
	public function checkDetails(&$db)
	{
		$this->name = trim($this->name);
		$this->description = trim($this->description);
		
		$result = tl::OK;
		if (!sizeof($this->rights))
			$result = self::E_EMPTYROLE;
		if ($result >= tl::OK)
			$result = self::checkRoleName($this->name);
		if ($result >= tl::OK)
			$result = self::doesRoleExist($db,$this->name,$this->dbID) ? self::E_NAMEALREADYEXISTS : tl::OK;
		
		return $result;
	}
	
	static public function doesRoleExist(&$db,$name,$id)
	{
		$role = new tlRole();
		$role->name = $name;
		if ($role->readFromDB($db,self::ROLE_O_SEARCH_BYNAME) >= tl::OK && $role->dbID != $id)
			return $role->dbID;
		return null;
	}
	
	static public function checkRoleName($name)
	{
		return is_blank($name) ? self::E_NAMELENGTH : tl::OK;
	}
	
	public function getDisplayName()
	{
		$displayName = $this->name;
		if ($displayName{0} == "<")
		{
			$displayName = lang_get($displayName);
		}
		return $displayName;
	}
	
	public function deleteFromDB(&$db)
	{
		$result = $this->deleteRightsFromDB($db);
		if ($result >= tl::OK)
		{
			//reset all affected users by replacing the deleted role with configured role
			$this->replaceUserRolesWith($db,$this->replacementRoleID);

			$query = "DELETE FROM {$this->object_table} WHERE id = {$this->dbID}";
			$result = $db->exec_query($query) ? tl::OK : tl::ERROR;
		}
		return $result;
	}
	
	protected function replaceUserRolesWith(&$db,$newRole)
	{
		$result = true;
		$tables = array('users','user_testproject_roles','user_testplan_roles');
		foreach($tables as $table)
		{
		    $table_name = DB_TABLE_PREFIX . $table;
			$query = "UPDATE {$table_name} SET role_id = {$newRole} WHERE role_id = {$this->dbID}";
			$result = $result && ($db->exec_query($query) ? true : false);
		}
		return $result ? tl::OK : tl::ERROR;
	}
	
	/**
	 * Gets all users with a certain global role
	 *
	 * @param object $db [ref] the db-object
	 * @return array assoc map with the user ids as the keys
	 **/
	protected function getUsersWithGlobalRole(&$db)
	{
		$idSet = $this->getUserIDsWithGlobalRole($db);
		return self::createObjectsFromDB($db,$idSet,"tlUser",true,self::TLOBJ_O_GET_DETAIL_MINIMUM);
	}
	
	/**
	 * Gets all userids of users with a certain global role  @TODO WRITE RIGHT COMMENTS FROM START
	 *
	 * @param object $db [ref] the db-object
	 * @return array of userids
	 **/
	protected function getUserIDsWithGlobalRole(&$db)
	{
		$query = "SELECT id FROM {$this->tables['users']} WHERE role_id = {$this->dbID}";
		$idSet = $db->fetchColumnsIntoArray($query,"id");
		
		return $idSet; 
	}

	/**
	 * Gets all userids of users with a certain testproject role @TODO WRITE RIGHT COMMENTS FROM START
	 *
	 * @param object $db [ref] the db-object
	 * @return array returns array of userids
	 **/
	protected function getUserIDsWithTestProjectRole(&$db)
	{
		$query = "SELECT DISTINCT id FROM {$this->tables['users']} users," .
		         " {$this->tables['user_testproject_roles']} user_testproject_roles " .
		         " WHERE users.id = user_testproject_roles.user_id";
		$query .= " AND user_testproject_roles.role_id = {$this->dbID} AND users.id < 10";
		$idSet = $db->fetchColumnsIntoArray($query,"id");
		
		return $idSet; 
	}

	/**
	 * Gets all userids of users with a certain testplan role @TODO WRITE RIGHT COMMENTS FROM START
	 *
	 * @param object $db [ref] the db-object
	 * @return array returns array of userids
	 **/
	protected function getUserIDsWithTestPlanRole(&$db)
	{
		$query = "SELECT DISTINCT id FROM {$this->tables['users']} users," . 
		         " {$this->tables['user_testplan_roles']} user_testplan_roles " .
		         " WHERE  users.id = user_testplan_roles.user_id";
		$query .= " AND user_testplan_roles.role_id = {$this->dbID}";
		$idSet = $db->fetchColumnsIntoArray($query,"id");
		
		return $idSet; 
	}
	
	
	/**
	 * Gets all users with a certain testproject role
	 *
	 * @param object $db [ref] the db-object
	 * @return array returns assoc map with the userids as the keys
	 **/
	protected function getUsersWithTestProjectRole(&$db)
	{
	    $idSet = $this->getUserIDsWithTestProjectRole($db);
		return self::createObjectsFromDB($db,$idSet,"tlUser",true,self::TLOBJ_O_GET_DETAIL_MINIMUM);
	}
	
	
	/**
	 * Gets all users with a certain testplan role
	 *
	 * @param object $db [ref] the db-object
	 * @return array returns assoc map with the userids as the keys
	 **/
	protected function getUsersWithTestPlanRole(&$db)
	{
		$idSet = $this->getUserIDsWithTestPlanRole($db);
		return self::createObjectsFromDB($db,$idSet,"tlUser",true,self::TLOBJ_O_GET_DETAIL_MINIMUM);
	}
	
	
	/**
	 * Gets all users which have a certain global,testplan or testproject role
	 *
	 * @param object $db [ref] the db-object
	 * @return array returns assoc map with the userids as the keys
	 **/
	public function getAllUsersWithRole(&$db)
	{
		$global_users = $this->getUserIDsWithGlobalRole($db);
		$tplan_users = $this->getUserIDsWithTestPlanRole($db);
		$tproject_users = $this->getUserIDsWithTestProjectRole($db);

		$affectedUsers = (array)$global_users + (array)$tplan_users + (array)$tproject_users;
	    $affectedUsers = array_unique($affectedUsers);
		return self::createObjectsFromDB($db,$affectedUsers,"tlUser",true,self::TLOBJ_O_GET_DETAIL_MINIMUM);
	}

	/*
		check if a role has requested right
		
		@param string $rightName the name of the right to check
		
		@return bool returns true if present, false else
	*/
	public function hasRight($rightName)
	{
		$roleRights = (array)$this->rights;
		$rights = array();
		foreach($roleRights as $right)
		{
			$rights[] = $right->name;
		}
	 	$status = in_array($rightName,$rights);
	 	
		return $status;
	}
	
	/** Delete the rights of a role from the db
	 * @param $db [ref] the database connection
	 * @return returns tl::OK on success, tl::ERROR else
	 */
	protected function deleteRightsFromDB(&$db)
	{
		$tablename = $this->tables['role_rights'];
		$query = "DELETE FROM {$tablename} WHERE role_id = {$this->dbID}";
		$result = $db->exec_query($query);
		
		return $result ? tl::OK : tl::ERROR;
	}

	/*
    function: 

    args :
    
    returns: 

  */
	protected function addRightsToDB(&$db)
	{
		$bSuccess = 1;
		if ($this->rights)
		{
			$tablename = $this->tables['role_rights'];
			foreach($this->rights as $right)
			{
				$rightID = $right->dbID;
				$query = "INSERT INTO {$tablename} (role_id,right_id) VALUES ({$this->dbID},{$rightID})";
				$bSuccess = $bSuccess && ($db->exec_query($query) ? 1 : 0);
			}
		}
		return $bSuccess ? tl::OK : tl::ERROR;
	}
	
	protected function readRights(&$db)
	{
		$roleRightsTableName = $this->tables['role_rights'];
		$rightsTableName = $this->tables['rights'];
		$query = "SELECT right_id,description FROM {$roleRightsTableName} a JOIN {$rightsTableName} b ON a.right_id = b.id " .
		         "WHERE role_id = {$this->dbID}";
		$rightInfo = $db->get_recordset($query);
		$this->rights = buildRightsArray($rightInfo);
		return tl::OK;
	}	
	
	protected function buildRightsArray($rightInfo)
	{
		$rights = null;
		for($i = 0;$i < sizeof($rightInfo);$i++)
		{
			$id = $rightInfo[$i];
			$right = new tlRight($id['right_id']);
			$right->name = $id['description'];
			$rights[] = $right;
		}
		return $rights;
	}
	static public function getByID(&$db,$id,$detailLevel = self::TLOBJ_O_GET_DETAIL_FULL)
	{
		return tlDBObject::createObjectFromDB($db,$id,__CLASS__,self::TLOBJ_O_SEARCH_BY_ID,$detailLevel);
	}
	
	static public function getAll(&$db,$whereClause = null,$column = null,
	                              $orderBy = null,$detailLevel = self::TLOBJ_O_GET_DETAIL_FULL)
	{
		$query = " SELECT id FROM roles";
		if (!is_null($whereClause))
			$query .= ' '.$whereClause;
	
		$query .= is_null($orderBy) ? " ORDER BY id ASC " : $orderBy;
	
		$roles = tlDBObject::createObjectsFromDBbySQL($db,$query,'id',__CLASS__,true,$detailLevel);
		
		$inheritedRole = new tlRole(TL_ROLES_INHERITED);
		$inheritedRole->name = lang_get('inherited_role');
		$roles[TL_ROLES_INHERITED] = $inheritedRole;
		
		return $roles;
	}
	static public function getByIDs(&$db,$ids,$detailLevel = self::TLOBJ_O_GET_DETAIL_FULL)
	{
		return self::handleNotImplementedMethod(__FUNCTION__);
	}
}

class tlRight extends tlDBObject
{
	public $name;
	
	function __construct($dbID = null)
	{
		parent::__construct($dbID);
	}
	
	protected function _clean($options = self::TLOBJ_O_SEARCH_BY_ID)
	{
		$this->name = null;
		if (!($options & self::TLOBJ_O_SEARCH_BY_ID))
			$this->dbID = null;
	}
	
	public function __toString()
	{
		return $this->name;
	}
	
	//BEGIN interface iDBSerialization
	public function readFromDB(&$db,$options = self::TLOBJ_O_SEARCH_BY_ID)
	{
		$this->_clean($options);
		$query = "SELECT id,description FROM rights";
		
		$clauses = null;
		if ($options & self::TLOBJ_O_SEARCH_BY_ID)
			$clauses[] = "id = {$this->dbID}";		
		if ($clauses)
			$query .= " WHERE " . implode(" AND ",$clauses);
			
		$info = $db->fetchFirstRow($query);			 
		if ($info)
			$this->name = $info['description'];

		return $info ? tl::OK : tl::ERROR;
	}

	static public function getByID(&$db,$id,$detailLevel = self::TLOBJ_O_GET_DETAIL_FULL)
	{
		return tlDBObject::createObjectFromDB($db,$id,__CLASS__,self::TLOBJ_O_SEARCH_BY_ID,$detailLevel);
	}
	
	static public function getByIDs(&$db,$ids,$detailLevel = self::TLOBJ_O_GET_DETAIL_FULL)
	{
		if (!sizeof($ids))
			return null;
		$query = "SELECT id,description FROM rights WHERE id IN (".implode(",",$ids).")";
		$rows = $db->fetchArrayRowsIntoMap($query,"id");
		$rights = null;
		foreach($rows as $id => $row)
		{
			$right = new tlRight($id);
			$right->name = $row[0]["description"];
			$rights[$id] = $right;
		}
		return $rights;
	}

	static public function getAll(&$db,$whereClause = null,$column = null,
	                              $orderBy = null,$detailLevel = self::TLOBJ_O_GET_DETAIL_FULL)
	{
		$query = " SELECT id FROM rights";
		if (!is_null($whereClause))
			$query .= ' '.$whereClause;
	
		$query .= is_null($orderBy) ? " ORDER BY id ASC " : $orderBy;
		return tlDBObject::createObjectsFromDBbySQL($db,$query,'id',__CLASS__,true,$detailLevel);
	}

	public function writeToDB(&$db)
	{
		return self::handleNotImplementedMethod(__FUNCTION__);
	}
	
	public function deleteFromDB(&$db)
	{
		return self::handleNotImplementedMethod(__FUNCTION__);
	}
}
?>
