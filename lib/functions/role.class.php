<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: role.class.php,v $
 *
 * @version $Revision: 1.4 $
 * @modified $Date: 2007/12/27 17:03:49 $ $Author: franciscom $
 */
class tlRole extends tlDBObject
{
	public $name;
	public $description;
	public $rights;
	
	protected $replacementRoleID;
	//options
	const ROLE_O_SEARCH_BYNAME = 2;
	
	//detail leveles
	const TLOBJ_O_GET_DETAIL_RIGHTS = 1;
		
	const ROLE_E_DBERROR = -2;	
	const ROLE_E_NAMELENGTH = -3;
	const ROLE_E_NAMEALREADYEXISTS = -4;
	const ROLE_E_EMPTYROLE = -5;
		
	function __construct($dbID = null)
	{
		parent::__construct($dbID);
		
		$this->replacementRoleID = config_get('role_replace_for_deleted_roles');
	}
	protected function _clean($options = self::TLOBJ_O_SEARCH_BY_ID)
	{
		if (!($options & self::ROLE_O_SEARCH_BYNAME))
			$this->name = null;
		$this->descriptions = null;
		$this->rights = null;
		if (!($options & self::TLOBJ_O_SEARCH_BY_ID))
			$this->dbID = null;
	}
	//BEGIN interface iDBSerialization
	public function readFromDB(&$db,$options = self::TLOBJ_O_SEARCH_BY_ID)
	{
		$this->_clean($options);
		$query = "SELECT id,description, notes FROM roles";
		
		$clauses = null;
		if ($options & self::ROLE_O_SEARCH_BYNAME)
			$clauses[] = "description = '".$db->prepare_string($this->name)."'";
		if ($options & self::TLOBJ_O_SEARCH_BY_ID)
			$clauses[] = "id = {$this->dbID}";		
		if ($clauses)
			$query .= " WHERE " . implode(" AND ",$clauses);
			
		$info = $db->fetchFirstRow($query);			 
		if ($info)
		{
			$this->dbID = $info['id'];
			$this->name = $info['description'];
			$this->description = $info['notes'];
			if ($this->detailLevel & self::TLOBJ_O_GET_DETAIL_RIGHTS)
				$this->readRights($db);
		}
		return $info ? tl::OK : tl::ERROR;
	}

  /*
  function: 

  args:
  
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
					$query = "UPDATE roles SET description = '".$db->prepare_string($this->name)."',".
							"notes ='".$db->prepare_string($this->description)."'".
							" WHERE id = {$this->dbID}";
					$result = $db->exec_query($query);	
				}
			}
			else
			{
				$query = "INSERT INTO roles (description,notes) VALUES ('".$db->prepare_string($this->name)."',".
						 "'".$db->prepare_string($this->description)."')";
				$result = $db->exec_query($query);	
				if($result)
					$this->dbID = $db->insert_id('users');
			}
			$result = $result ? tl::OK : self::ROLE_E_DBERROR;
			if ($result >= tl::OK)
				$result = $this->addRightsToDB($db);
		}
		
		return $result;
	}
	public function checkDetails(&$db)
	{
		$this->name = trim($this->name);
		$this->description = trim($this->description);
		
		$result = tl::OK;
		if (!sizeof($this->rights))
			$result = self::ROLE_E_EMPTYROLE;
		if ($result >= tl::OK)
			$result = self::checkRoleName($this->name);
		if ($result >= tl::OK )
			$result = self::doesRoleExist($db,$this->name,$this->dbID) ? self::ROLE_E_NAMEALREADYEXISTS : tl::OK;
		
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
		return is_blank($name) ? self::ROLE_E_NAMELENGTH : tl::OK;
	}
	public function deleteFromDB(&$db)
	{
		$result = $this->deleteRightsFromDB($db);
		if ($result >= tl::OK)
		{
			//SCHLUNDUS: needs refactoring
			//reset all affected users by replacing the deleted role with configured role
			resetUserRoles($db,$this->dbID,$this->replacementRoleID);

			$query = "DELETE FROM roles WHERE id = {$this->dbID}";
			$result = $db->exec_query($query);
			$result = $result ? tl::OK : tl::ERROR;
		}
		return $result;
	}
	protected function deleteRightsFromDB(&$db)
	{
		$query = "DELETE FROM role_rights WHERE role_id = {$this->dbID}";
		$result = $db->exec_query($query);
		
		return $result ? tl::OK : tl::ERROR;
	}                               
	
	
  /*
  function: 

  args:
  
  returns: 

  */
	protected function addRightsToDB(&$db)
	{
		$bSuccess = 1;
		if ($this->rights)
		{
			foreach($this->rights as $right)
			{
				$rightID = $right->dbID;
				$query = "INSERT INTO role_rights (role_id,right_id) VALUES ({$this->dbID},{$rightID})";
				$bSuccess = $bSuccess && ($db->exec_query($query) ? 1 : 0);
			}
		}
		return $bSuccess ? tl::OK : tl::ERROR;
	}


	protected function readRights(&$db)
	{
		$query = "SELECT right_id FROM role_rights WHERE role_id = {$this->dbID}";
		$this->rights = tlDBObject::createObjectsFromDBbySQL($db,$query,'right_id','tlRight');
	}	
	static public function getByID(&$db,$id,$detailLevel = self::TLOBJ_O_GET_DETAIL_FULL)
	{
		return tlDBObject::createObjectFromDB($db,$id,__CLASS__,self::TLOBJ_O_SEARCH_BY_ID,$detailLevel);
	}
	static public function getAll(&$db,$whereClause = null,$column = null,$orderBy = null,$detailLevel = self::TLOBJ_O_GET_DETAIL_FULL)
	{
		$query = " SELECT id FROM roles";
		if (!is_null($whereClause))
			$query .= ' '.$whereClause;
	
		$query .= is_null($orderBy) ? " ORDER BY id ASC " : $orderBy;
	
		return tlDBObject::createObjectsFromDBbySQL($db,$query,'id',__CLASS__,true,$detailLevel);
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
	public function writeToDB(&$db)
	{
		return self::handleNotImplementedMethod("writeToDB");
	}
	public function deleteFromDB(&$db)
	{
		return self::handleNotImplementedMethod("deleteFromDB");
	}
	
	static public function getByID(&$db,$id,$detailLevel = self::TLOBJ_O_GET_DETAIL_FULL)
	{
		return tlDBObject::createObjectFromDB($db,$id,__CLASS__,self::TLOBJ_O_SEARCH_BY_ID,$detailLevel);
	}
	static public function getAll(&$db,$whereClause = null,$column = null,$orderBy = null,$detailLevel = self::TLOBJ_O_GET_DETAIL_FULL)
	{
		$query = " SELECT id FROM rights";
		if (!is_null($whereClause))
			$query .= ' '.$whereClause;
	
		$query .= is_null($orderBy) ? " ORDER BY id ASC " : $orderBy;
		return tlDBObject::createObjectsFromDBbySQL($db,$query,'id',__CLASS__,true,$detailLevel);
	}
}

?>
