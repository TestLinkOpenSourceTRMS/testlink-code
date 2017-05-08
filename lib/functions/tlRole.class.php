<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @package     TestLink
 * @copyright   2004-2016, TestLink community 
 * @filesource  tlRole.class.php
 * @link        http://www.teamst.org/index.php
 *
 * @internal revisions
 * 
 */

/**
 * 
 * @package   TestLink
 */
class tlRole extends tlDBObject
{
  /**
   * @var string role name
   */
  public $name;
  
  /**
   * @var string role description 
   */
  public $description;

  /**
   * @var array the tlRights of the role
   */
  public $rights;

  
  /**
   * @var string the name of the database table a role is store to
   */
  protected $object_table;
  
  /**
   * @var integer the replacement roleID, if assigned roles get deleted
   */
  protected $replacementRoleID;
  
  const ROLE_O_SEARCH_BYNAME = 2;
  const TLOBJ_O_GET_DETAIL_RIGHTS = 1;

  // some error code
  const E_DBERROR = -2; 
  const E_NAMELENGTH = -3;
  const E_NAMEALREADYEXISTS = -4;
  const E_EMPTYROLE = -5;
    
  /** 
   * class constructor 
   * @param integer $dbID the database id of the role 
   **/    
  function __construct($dbID = null)
  {
    parent::__construct($dbID);
    
    $this->object_table = $this->tables['roles']; 
    $this->replacementRoleID = config_get('role_replace_for_deleted_roles');
    $this->activateCaching = true;
  }

  /* Used to clean up the tlRole object
   * 
   * @param $options array any combination of TLOBJ_O_SEARCH_BY_ID or ROLE_O_SEARCH_BYNAME
   * 
   * @return integer always returns tl::OK
   * 
   * @see lib/functions/tlObject#_clean()
   */
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

    return tl::OK;
  }
  
  /* Copies a tlRole object from another
   * 
   * @param $role tlRole the role which should be used to initialize this role 
   * 
   * @return integer always returns tl::OK
   * @see lib/functions/tlDBObject#copyFromCache($object)
   */
  public function copyFromCache($role)
  {
    $this->description = $role->description;
    $this->rights = $role->rights;
    $this->name = $role->name;
    
    return tl::OK;
  }
  
  /* Read a role from the database 
   * @param $db resource [ref] the database connection
   * @param $options integer any combination of TLOBJ_O_ or ROLE_O - Flags
   * 
   * @return integer returns tl::OK on success, tl::ERROR else
   * 
   * @see lib/functions/iDBSerialization#readFromDB($db, $options)
   */
  public function readFromDB(&$db,$options = self::TLOBJ_O_SEARCH_BY_ID)
  {
    if ($this->readFromCache() >= tl::OK)
    {  
      return tl::OK;
    }
      
    $this->_clean($options);
    $getFullDetails = ($this->detailLevel & self::TLOBJ_O_GET_DETAIL_RIGHTS);
    $sql = "SELECT a.id AS role_id,a.description AS role_desc, a.notes ";
    if ($getFullDetails)
    {
      $sql .= " ,c.id AS right_id,c.description ";
    }  
    
    $sql .= " FROM {$this->object_table} a ";
    
    if ($getFullDetails)
    {
      $sql .= " LEFT OUTER JOIN {$this->tables['role_rights']} b ON a.id = b.role_id " . 
              " LEFT OUTER JOIN {$this->tables['rights']}  c ON b.right_id = c.id ";
    }
    
    $clauses = null;
    if ($options & self::ROLE_O_SEARCH_BYNAME)
    {  
      $clauses[] = "a.description = '".$db->prepare_string($this->name)."'";
    }
      
    if ($options & self::TLOBJ_O_SEARCH_BY_ID)
    {  
      $clauses[] = "a.id = {$this->dbID}";    
    }
    
    if ($clauses)
    {  
      $sql .= " WHERE " . implode(" AND ",$clauses);
    }

    $rightInfo = $db->get_recordset($sql);       
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

    $readSucceeded = $rightInfo ? tl::OK : tl::ERROR;
    if ($readSucceeded >= tl::OK)
    {  
      $this->addToCache();
    }

    return $readSucceeded;
  }

  /** 
   * @param resource &$db reference to database handler
   **/    
  public function writeToDB(&$db)
  {
    //@TODO schlundus, now i removed the potentially modified object from the cache
    //another optimization could be: read the new contents if storing was successfully into the
    //cache
    $this->removeFromCache();
    
    $result = $this->checkDetails($db);
    if ($result >= tl::OK)
    {   
      if ($this->dbID)
      {
        $result = $this->deleteRightsFromDB($db);
        if ($result >= tl::OK)
        {
          $sql = "UPDATE {$this->object_table} " .
                 " SET description = '".$db->prepare_string($this->name)."',".
               " notes ='".$db->prepare_string($this->description)."'".
               " WHERE id = {$this->dbID}";
          $result = $db->exec_query($sql);  
        }
      }
      else
      {
        $sql = "INSERT INTO {$this->object_table} (description,notes) " .
               " VALUES ('".$db->prepare_string($this->name)."',".
               "'" . $db->prepare_string($this->description)."')";
        $result = $db->exec_query($sql);  
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
  
  /** 
   * @param resource &$db reference to database handler
   **/    
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
  
  /** 
   * @param resource &$db reference to database handler
   **/    
  static public function doesRoleExist(&$db,$name,$id)
  {
    $role = new tlRole();
    $role->name = $name;
    if ($role->readFromDB($db,self::ROLE_O_SEARCH_BYNAME) >= tl::OK && $role->dbID != $id)
    {  
      return $role->dbID;
    }  
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
      $roleName = str_replace(" ","_",substr($displayName,1,-1));
      $displayName = "<".lang_get($roleName).">";
    }
    return $displayName;
  }
  
  /** 
   * @param resource &$db reference to database handler
   **/    
  public function deleteFromDB(&$db)
  {
    $this->removeFromCache();
 
    $result = $this->deleteRightsFromDB($db);
    if ($result >= tl::OK)
    {
      //reset all affected users by replacing the deleted role with configured role
      $this->replaceUserRolesWith($db,$this->replacementRoleID);

      $sql = "DELETE FROM {$this->object_table} WHERE id = {$this->dbID}";
      $result = $db->exec_query($sql) ? tl::OK : tl::ERROR;
    }
    return $result;
  }
  
  /** 
   * @param resource &$db reference to database handler
   **/    
  protected function replaceUserRolesWith(&$db,$newRole)
  {
    $result = true;
    $tables = array('users','user_testproject_roles','user_testplan_roles');
    foreach($tables as $table)
    {
      $sql = "UPDATE {$this->tables[$table]} SET role_id = {$newRole} WHERE role_id = {$this->dbID}";
      $result = $result && ($db->exec_query($sql) ? true : false);
    }
    return $result ? tl::OK : tl::ERROR;
  }
  
  /**
   * Gets all users with a certain global role
   *
   * @param resource &$db reference to database handler
   * @return array assoc map with the user ids as the keys
   **/
  public function getUsersWithGlobalRole(&$db,$opt=null)
  {
    $idSet = $this->getUserIDsWithGlobalRole($db,$opt);
    return self::createObjectsFromDB($db,$idSet,"tlUser",true,self::TLOBJ_O_GET_DETAIL_MINIMUM);
  }
  
  /**
   * Gets all userids of users with a certain global role  @TODO WRITE RIGHT COMMENTS FROM START
   *
   * @param resource &$db reference to database handler
   * @return array of userids
   **/
  protected function getUserIDsWithGlobalRole(&$db,$opt=null)
  {
    $my['opt'] = array('active' => -1);
    $my['opt'] = array_merge($my['opt'],(array)$opt);
    
    $sql = "SELECT id FROM {$this->tables['users']} " .
           " WHERE role_id = {$this->dbID}";

    if($my['opt']['active'] != -1)
    {
      $sql .= ' and active = ' . (intval($my['opt']['active']) > 0 ? 1 : 0);
    } 

    $idSet = $db->fetchColumnsIntoArray($sql,"id");
    return $idSet; 
  }

  /**
   * Gets all userids of users with a certain testproject role @TODO WRITE RIGHT COMMENTS FROM START
   *
   * @param resource &$db reference to database handler
   * @return array returns array of userids
   **/
  protected function getUserIDsWithTestProjectRole(&$db)
  {
    $sql = "SELECT DISTINCT id FROM {$this->tables['users']} users," .
           " {$this->tables['user_testproject_roles']} user_testproject_roles " .
           " WHERE users.id = user_testproject_roles.user_id";
    $sql .= " AND user_testproject_roles.role_id = {$this->dbID} ";
    $idSet = $db->fetchColumnsIntoArray($sql,"id");
    
    return $idSet; 
  }

  /**
   * Gets all userids of users with a certain testplan role @TODO WRITE RIGHT COMMENTS FROM START
   *
   * @param resource &$db reference to database handler
   * @return array returns array of userids
   **/
  protected function getUserIDsWithTestPlanRole(&$db)
  {
    $sql = "SELECT DISTINCT id FROM {$this->tables['users']} users," . 
           " {$this->tables['user_testplan_roles']} user_testplan_roles " .
           " WHERE  users.id = user_testplan_roles.user_id";
    $sql .= " AND user_testplan_roles.role_id = {$this->dbID}";
    $idSet = $db->fetchColumnsIntoArray($sql,"id");
    
    return $idSet; 
  }
  
  
  /**
   * Gets all users with a certain testproject role
   *
   * @param resource &$db reference to database handler
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
   * @param resource &$db reference to database handler
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
   * @param resource &$db reference to database handler
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
  
  /** 
   * Delete the rights of a role from the db
   * 
   * @param resource &$db reference to database handler
   * @return returns tl::OK on success, tl::ERROR else
   */
  protected function deleteRightsFromDB(&$db)
  {
    $tablename = $this->tables['role_rights'];
    $sql = "DELETE FROM {$tablename} WHERE role_id = {$this->dbID}";
    $result = $db->exec_query($sql);
    
    return $result ? tl::OK : tl::ERROR;
  }

  protected function addRightsToDB(&$db)
  {
    $status_ok = 1;
    if ($this->rights)
    {
      foreach($this->rights as $right)
      {
        $rightID = $right->dbID;
        $sql = "INSERT INTO {$this->tables['role_rights']} (role_id,right_id) " .
               "VALUES ({$this->dbID},{$rightID})";
        $status_ok = $status_ok && ($db->exec_query($sql) ? 1 : 0);
      }
    }
    return $status_ok ? tl::OK : tl::ERROR;
  }
  
  protected function readRights(&$db)
  {
    $sql = "SELECT right_id,description FROM {$this->tables['role_rights']} a " .
           "JOIN {$this->tables['rights']} b ON a.right_id = b.id " .
             "WHERE role_id = {$this->dbID}";
    $rightInfo = $db->get_recordset($sql);
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
    $tables  = tlObject::getDBTables("roles");
    $sql = "SELECT id FROM {$tables['roles']} ";
    if (!is_null($whereClause))
      $sql .= ' '.$whereClause;
    $sql .= is_null($orderBy) ? " ORDER BY id ASC " : $orderBy;
  
    $roles = tlDBObject::createObjectsFromDBbySQL($db,$sql,'id',__CLASS__,true,$detailLevel);
    
    $inheritedRole = new tlRole(TL_ROLES_INHERITED);
    $inheritedRole->name = "<inherited>";
    $roles[TL_ROLES_INHERITED] = $inheritedRole;
    
    return $roles;
  }
  
  static public function getByIDs(&$db,$ids,$detailLevel = self::TLOBJ_O_GET_DETAIL_FULL)
  {
    return self::handleNotImplementedMethod(__FUNCTION__);
  }


    /**
   * get roles present on system and return map with colour associations
   * if there is no colour configured for role '' is returned as colour.
   *
   */
  static public function getRoleColourCfg(&$db)
  {
      $role_colour = config_get('role_colour');
    $tables  = tlObject::getDBTables("roles");
    $sql = "SELECT description FROM {$tables['roles']} ";
        $roles = $db->fetchColumnsIntoArray($sql,"description");
      foreach($roles as $description)
      {
          if(!isset($role_colour[$description]))
          {
              $role_colour[$description] = '';
          }
      }
      return $role_colour;
  }
}
?>
