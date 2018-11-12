<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later.
 * 
 * @filesource  tlUser.class.php
 * @package     TestLink
 * @copyright   2007-2018, TestLink community 
 * @link        http://www.testlink.org
 *
 */
 
/**
 * Class for handling users in TestLink
 * 
 * @package TestLink
 * @author   Andreas Morsing
 * @uses   config.inc.php
 * @since   1.7
 */ 
class tlUser extends tlDBObject {
  /**
   * @var the name of the table the object is stored into
   * @access private
   */
  private $object_table = "users";
    
  /**
   * @var string the first name of the user
   */
  public $firstName;

  /**
   * @var string the last name of the user
   */
  public $lastName;

  /**
   * @var string the email address of the user
   */
  public $emailAddress;

  /**
   * @var string the locale of the user (eG de_DE, en_GB)
   */
  public $locale;

  /**
   * @var boolean true if the user is active, false else
   */
  public $isActive;

  /**
   * @var integer the default testprojectID of the user
   */
  public $defaultTestprojectID;

  /**
   * @var tlRole the global role of the user
   */
  public $globalRole;

  /**
   * @var integer the id of global role of the user
   */
  public $globalRoleID;

  /**
   * @var array of tlRole, holds the roles of the user for the different testprojects 
   */
  public $tprojectRoles; 

  /**
   * @var array of tlRole, holds the roles of the user for the different testplans 
   */
  public $tplanRoles;

  /**
   * @var string the login of the user
   */
  public $login;

  /**
   * @var string the API Key for the user
   */
  public $userApiKey;


  public $authentication;
  public $creation_ts;
  public $expiration_date;


  /**
   * @var string the password of the user
   * @access protected
   */
  protected $password;
  
  /**
   * @var string security cookie (security) of the user
   * @access protected
   */
  protected $securityCookie;

  
  /** configuration options */
  protected $usernameFormat;
  protected $loginMethod;
  protected $maxLoginLength;
  
  /** error codes */
  const E_LOGINLENGTH = -1;
  const E_EMAILLENGTH = -2;
  const E_NOTALLOWED = -4;
  const E_DBERROR = -8;
  const E_FIRSTNAMELENGTH = -16;
  const E_LASTNAMELENGTH = -32;
  const E_PWDEMPTY = -64;
  const E_PWDDONTMATCH = -128;
  const E_LOGINALREADYEXISTS = -256;
  const E_EMAILFORMAT = -512;
  const S_PWDMGTEXTERNAL = 2;
  
  // search options
  // 1 already in use by TLOBJ_O_SEARCH_BY_ID
  const USER_O_SEARCH_BYLOGIN = 2;
  const USER_O_SEARCH_BYEMAIL = 4;

  
  
  // detail levels
  const TLOBJ_O_GET_DETAIL_ROLES = 1;

  const SKIP_CHECK_AT_TESTPROJECT_LEVEL = -1;
  const SKIP_CHECK_AT_TESTPLAN_LEVEL = -1;

  const CHECK_PUBLIC_PRIVATE_ATTR = true;

  /**
   * Constructor, creates the user object
   * 
   * @param resource $db database handler
   */
  function __construct($dbID = null) {

    parent::__construct($dbID);

    $this->object_table = $this->tables['users']; 
    
    $authCfg = config_get('authentication');
    $this->usernameFormat = config_get('username_format');
    $this->loginRegExp = config_get('validation_cfg')->user_login_valid_regex;
    $this->maxLoginLength = 100; 
    $this->loginMethod = $authCfg['method'];

    $this->globalRoleID = config_get('default_roleid');
    $this->locale = config_get('default_language');
    $this->isActive = 1;
    $this->tprojectRoles = null;
    $this->tplanRoles = null;
  }
  
  /** 
   * Cleans the object by resetting the members to default values
   * 
   * @param mixed $options tlUser/tlObject options
   */
  protected function _clean($options = self::TLOBJ_O_SEARCH_BY_ID) {
    $this->firstName = null;
    $this->lastName = null;
    $this->locale = null;
    $this->password = null;
    $this->isActive = null;
    $this->defaultTestprojectID = null;
    $this->globalRoleID = null;
    $this->tprojectRoles = null;
    $this->tplanRoles = null;
    $this->userApiKey = null;
    $this->securityCookie = null;
    $this->authentication = null;
    $this->expiration_date = null;

    if (!($options & self::TLOBJ_O_SEARCH_BY_ID)) {
      $this->dbID = null;
    }

    if (!($options & self::USER_O_SEARCH_BYLOGIN)) {
      $this->login = null;
    }

    if (!($options & self::USER_O_SEARCH_BYEMAIL)) {
      $this->emailAddress = null;
    }


  }
  
  /** 
   * Checks if password management is external (like LDAP)...
   *
   * @param  string $method2check must be one of the keys of configuration $tlCfg->authentication['domain']
   *           
   * @return boolean return true if password management is external, else false
   */
  static public function isPasswordMgtExternal($method2check=null)
  {
    $target = $method2check;

    // Contains Domain and Default Method  
    $authCfg = config_get('authentication');
 
    if( is_null($target) || $target=='')
    {
      $target = $authCfg['method'];
    }

    $ret = true;
    if( isset($authCfg['domain'][$target]) )
    {
      $ret = !$authCfg['domain'][$target]['allowPasswordManagement'];
    }
    return $ret;
  }
  
  /**
   *  Obtain a secure password. 
   *  You can choose the number of alphanumeric characters to add and 
   *  the number of non-alphanumeric characters. 
   *  You can add another characters to the non-alphanumeric list if you need.
   *           
   *   @param integer $numAlpha number alphanumeric characters in generated password
   *  @param integer $numNonAlpha number special characters in generated password
   * 
   *   @return string the generated password
  */
  static public function generatePassword($numAlpha = 6,$numNonAlpha = 2)
  {
    $listAlpha = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $listNonAlpha = ',;:!?.$/*-+&@_+;./*&?$-!,';
    
    return str_shuffle(substr(str_shuffle($listAlpha),0,$numAlpha) .
                        substr(str_shuffle($listNonAlpha),0,$numNonAlpha));
  }
  
  /** 
   * not used at the moment, only placeholder
   * 
   * @return void
   * @TODO implement  
   **/
  function create()
  {
  }
  
  //----- BEGIN interface iDBSerialization -----
  /** 
   * Reads an user object identified by its database id from the given database
   * 
   * @param resource &$db reference to database handler
   * @param mixed $options (optional) tlUser/tlObject options
   * 
   * @return integer tl::OK if the object could be read from the db, else tl::ERROR
   */
  public function readFromDB(&$db,$options = self::TLOBJ_O_SEARCH_BY_ID) {
    $this->_clean($options);
    $sql = " SELECT id,login,password,cookie_string,first,last,email," .
           " role_id,locale, " .
           " login AS fullname, active,default_testproject_id, script_key,auth_method,creation_ts,expiration_date " .
           " FROM {$this->object_table}";
    $clauses = null;

    if ($options & self::TLOBJ_O_SEARCH_BY_ID) {
      $clauses[] = "id = " . intval($this->dbID);    
    }

    if ($options & self::USER_O_SEARCH_BYLOGIN) {
      $clauses[] = "login = '".$db->prepare_string($this->login)."'";    
    }

    if ($options & self::USER_O_SEARCH_BYEMAIL) {
      $clauses[] = "email = '".$db->prepare_string($this->emailAddress)."'";    
    }

    if ($clauses) {
      $sql .= " WHERE " . implode(" AND ",$clauses);
    }
    $info = $db->fetchFirstRow($sql);
    if ($info) {
      $this->dbID = $info['id'];
      $this->firstName = $info['first'];
      $this->lastName = $info['last'];
      $this->login = $info['login'];
      $this->emailAddress = $info['email'];
      $this->globalRoleID = $info['role_id'];
      $this->userApiKey = $info['script_key'];
      $this->securityCookie = $info['cookie_string'];
      $this->authentication = $info['auth_method'];
      $this->expiration_date = $info['expiration_date'];
      $this->creation_ts = $info['creation_ts'];
      
      if ($this->globalRoleID) {
        $this->globalRole = new tlRole($this->globalRoleID);
        $this->globalRole->readFromDB($db);
      }

      if ($this->detailLevel & self::TLOBJ_O_GET_DETAIL_ROLES) {
        $this->readTestProjectRoles($db);
        $this->readTestPlanRoles($db);
      }
      
      $this->locale = $info['locale'];
      $this->password = $info['password'];
      $this->isActive = $info['active'];
      $this->defaultTestprojectID = $info['default_testproject_id'];
    }
    return $info ? tl::OK : tl::ERROR;
  }
  
  /**
   * Fetches all the testproject roles of of the user, and store them into the object. 
   * Result could be limited to a certain testproject
   * 
   * @param resource &$db reference to database handler
   * @param integer $testProjectID Identifier of the testproject to read the roles for, 
   *     if null all roles are read
   * 
   * @return integer returns tl::OK 
   */
  public function readTestProjectRoles(&$db,$testProjectID = null) {
    $sql = "SELECT testproject_id,role_id " .
           " FROM {$this->tables['user_testproject_roles']} user_testproject_roles " .
           " WHERE user_id = " . intval($this->dbID);

    if ($testProjectID) {
      $sql .= " AND testproject_id = " . intval($testProjectID);
    }
    $allRoles = $db->fetchColumnsIntoMap($sql,'testproject_id','role_id');
    $this->tprojectRoles = null;
    if (null != $allRoles && sizeof($allRoles)) {
      $roleCache = null;
      foreach($allRoles as $tprojectID => $roleID) {
        if (!isset($roleCache[$roleID])) {
          $tprojectRole = tlRole::createObjectFromDB($db,$roleID,"tlRole",true);
          $roleCache[$roleID] = $tprojectRole;
        } else {
          $tprojectRole = clone($roleCache[$roleID]);
        }

        if ($tprojectRole) {
          $this->tprojectRoles[$tprojectID] = $tprojectRole;
        }  
      }
    }
    return tl::OK;
  }
  
  /**
   * Fetches all the testplan roles of of the user, and store them into the object. 
   * Result could be limited to a certain testplan
   * 
   * @param resource &$db reference to database handler
   * @param integer $testPlanID Identifier of the testplan to read the roles for, if null all roles are read
   * 
   * @return integer returns tl::OK 
   */
  public function readTestPlanRoles(&$db,$testPlanID = null) {
    $sql = "SELECT testplan_id,role_id " . 
           " FROM {$this->tables['user_testplan_roles']} user_testplan_roles " .
           " WHERE user_id = " . intval($this->dbID);
    if ($testPlanID) {
      $sql .= " AND testplan_id = " . intval($testPlanID);
    }
        
    $allRoles = $db->fetchColumnsIntoMap($sql,'testplan_id','role_id');
    $this->tplanRoles = null;
    if (null != $allRoles  && sizeof($allRoles)) {
      $roleCache = null;
      foreach($allRoles as $tplanID => $roleID) {
        if (!isset($roleCache[$roleID])) {
          $tplanRole = tlRole::createObjectFromDB($db,$roleID,"tlRole",true);
          $roleCache[$roleID] = $tplanRole;
        } else {
          $tplanRole = clone($roleCache[$roleID]);
        }

        if ($tplanRole) {
          $this->tplanRoles[$tplanID] = $tplanRole;
        }  
      }
    }
    return tl::OK;
  }
  
  /** 
   * Writes the object into the database
   * 
   * @param resource &$db reference to database handler
   * @return integer tl::OK if the object could be written to the db, else error code
   */
  public function writeToDB(&$db)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $result = $this->checkDetails($db);
    if ($result >= tl::OK)
    {    
      $t_cookie_string = $this->auth_generate_unique_cookie_string($db);   

      // After addition of cookie_string, and following Mantisbt pattern,
      // seems we need to check if password has changed.
      //
      // IMPORTANT NOTICE: 
      // this implementation works ONLY when password is under TestLink control
      // i.e. is present on TestLink Database.
      //
      // if answer is yes => change also cookie_string.
      if($this->dbID)
      {
        $gsql = " /* debugMsg */ SELECT password FROM {$this->object_table} WHERE id = " . $this->dbID;
        $rs = $db->get_recordset($gsql);
        if(strcmp($rs[0]['password'],$this->password) == 0) 
        {
          // NO password change
          $t_cookie_string = null;
        }    

        $sql = "/* debugMsg */ UPDATE {$this->tables['users']} " .
               " SET first = '" . $db->prepare_string($this->firstName) . "'" .
               ", last = '" .  $db->prepare_string($this->lastName)    . "'" .
               ", email = '" . $db->prepare_string($this->emailAddress)   . "'" .
               ", locale = ". "'" . $db->prepare_string($this->locale) . "'" . 
               ", password = " . "'" . $db->prepare_string($this->password) . "'" .
               ", role_id = ". $db->prepare_int($this->globalRoleID) . 
               ", active = ". $db->prepare_string($this->isActive) . 
               ", auth_method = ". "'" . $db->prepare_string($this->authentication) . "'";

        if(!is_null($t_cookie_string) )
        {        
          $sql .= ", cookie_string = " .  "'" . $db->prepare_string($t_cookie_string) . "'";
        }        
        $sql .= " WHERE id = " . intval($this->dbID);
        $result = $db->exec_query($sql);
      }
      else
      {
        $sql = "/* debugMsg */ INSERT INTO {$this->tables['users']} " .
               " (login,password,cookie_string,first,last,email,role_id,locale,active,auth_method) " .
               " VALUES ('" . 
               $db->prepare_string($this->login) . "','" . $db->prepare_string($this->password) . "','" . 
               $db->prepare_string($t_cookie_string) . "','" .
               $db->prepare_string($this->firstName) . "','" . $db->prepare_string($this->lastName) . "','" . 
               $db->prepare_string($this->emailAddress) . "'," . $db->prepare_int($this->globalRoleID) . ",'". 
               $db->prepare_string($this->locale). "'," . $this->isActive . "," . 
               "'" . $db->prepare_string($this->authentication). "'" . ")";

        $result = $db->exec_query($sql);
        if($result)
        {
          $this->dbID = $db->insert_id($this->tables['users']);
        }  
      }
      $result = $result ? tl::OK : self::E_DBERROR;
    }
    return $result;
  }  

  /** 
   * WARNING: DO NOT USE THE FUNCTION - CAUSES DB INCONSISTENCE! 
   *  
   * @deprecated 1.8.3 
   * @see #2407 
   **/  
  public function deleteFromDB(&$db)
  {
    $safeUserID = intval($this->dbID);
    $sqlSet = array();
    $sqlSet[] = "DELETE FROM {$this->table['user_assignments']} WHERE user_id = {$safeUserID}";
    $sqlSet[] = "DELETE FROM {$this->table['users']}  WHERE id = {$safeUserID}";

    foreach($sqlSet as $sql)
    {
      $result = $db->exec_query($sql) ? tl::OK : tl::ERROR;
      if($result == tl::ERROR) 
      {
        break;  
      }
    }
  
    if ($result == tl::OK)
    {
      $result = $this->deleteTestProjectRoles($db);
    }
    return $result;
  }

  /**
   * Deletes all testproject related role assignments for a given user
   *
   * @param resource &$db reference to database handler
   * @param integer $userID the user ID
   * 
   * @return integer tl::OK on success, tl:ERROR else
   **/
  protected function deleteTestProjectRoles(&$db)
  {
    $sql = "DELETE FROM {$this->tables['user_testproject_roles']} WHERE user_id = " . intval($this->dbID);
    return $db->exec_query($sql) ? tl::OK : tl::ERROR;
  }

  /** 
   * Returns a user friendly representation of the user name
   * 
   * @return string the display nmae
   */
  public function getDisplayName($format=null)
  {
    $keys = array('%first%','%last%','%login%','%email%');
    $values = array($this->firstName, $this->lastName,$this->login,$this->emailAddress);
    
    $fmt = is_null($format) ? $this->usernameFormat : $format;
    $displayName = trim(str_replace($keys,$values,$fmt));

    return $displayName;
  }
  
  /**
   * Encrypts a given password with MD5
   * 
   * @param $pwd the password to encrypt
   * @return string the encrypted password
   */
  protected function encryptPassword($pwd,$authentication=null)
  {
    if (self::isPasswordMgtExternal($authentication))
    {  
      return self::S_PWDMGTEXTERNAL;
    }  
    return md5($pwd);
  }
  
  /**
   * Set encrypted password
   * 
   * @param string $pwd the new password
   * @return integer return tl::OK is the password is stored, else errorcode
   */
  public function setPassword($pwd,$authentication=null)
  {
    if (self::isPasswordMgtExternal($authentication))
    {
      return self::S_PWDMGTEXTERNAL;
    }
    $pwd = trim($pwd);  
    if ($pwd == "")
    {
      return self::E_PWDEMPTY;
    }
    $this->password = $this->encryptPassword($pwd,$authentication);
    return tl::OK;
  }
  
  /**
   * Getter for the password of the user
   * 
   * @return string the password of the user
   */
  public function getPassword()
  {
    return $this->password;
  }
  
  /**
   * compares a given password with the current password of the user
   * 
   * @param string $pwd the password to compate with the password actually set 
   * @return integer returns tl::OK if the password's match, else errorcode
   */
  public function comparePassword($pwd)
  {
    if (self::isPasswordMgtExternal($this->authentication))
    {  
      return self::S_PWDMGTEXTERNAL;
    }

    if ($this->getPassword($pwd) == $this->encryptPassword($pwd,$this->authentication))
    {  
      return tl::OK;
    }
    return self::E_PWDDONTMATCH;    
  }

  
  /**
   * 
   */
  public function checkDetails(&$db) {
    $this->firstName = trim($this->firstName);
    $this->lastName = trim($this->lastName);
    $this->emailAddress = trim($this->emailAddress);
    $this->locale = trim($this->locale);
    $this->isActive = intval($this->isActive);
    $this->login = trim($this->login);
  
    $result = self::checkEmailAddress($this->emailAddress);
    if ($result >= tl::OK)
    {
      $result = $this->checkLogin($this->login);
    }
    if ($result >= tl::OK && !$this->dbID)
    {
      $result = self::doesUserExist($db,$this->login) ? self::E_LOGINALREADYEXISTS : tl::OK;
    }
    if ($result >= tl::OK)
    {
      $result = self::checkFirstName($this->firstName);
    }
    if ($result >= tl::OK)
    {
      $result = self::checkLastName($this->lastName);
    }
    return $result;
  }

  
  public function checkLogin($login)
  {
    $result = tl::OK;
    $login = trim($login);
    
    if ($login == "" || (tlStringLen($login) > $this->maxLoginLength))
    {  
      $result = self::E_LOGINLENGTH;
    }
    else if (!preg_match($this->loginRegExp,$login)) 
    {
      //Only allow a basic set of characters
      $result = self::E_NOTALLOWED;
    }  
    return $result;
  }
  
  /**
   * Returns the id of the effective role in the context of ($tproject_id,$tplan_id)
   * 
   * @param resource &$db reference to database handler
   * @param integer $tproject_id the testproject id
   * @param integer $tplan_id the plan id
   * 
   * @return integer tlRole the effective role
   */
  function getEffectiveRole(&$db,$tproject_id,$tplan_id)
  {
    $tprojects_role = $this->tprojectRoles;
    $tplans_role = $this->tplanRoles;
    $effective_role = $this->globalRole;
    if(!is_null($tplans_role) && isset($tplans_role[$tplan_id]))
    {
      $effective_role = $tplans_role[$tplan_id];  
    }
    else if(!is_null($tprojects_role) && isset($tprojects_role[$tproject_id]))
    {
      $effective_role = $tprojects_role[$tproject_id];  
    }
    return $effective_role;
  }

  /**
   * Gets all userids of users with a certain testplan role @TODO WRITE RIGHT COMMENTS FROM START
   *
   * @param resource &$db reference to database handler
   * @return array returns array of userids
   **/
  protected function getUserNamesWithTestPlanRole(&$db)
  {
    $sql = "SELECT DISTINCT id FROM {$this->tables['users']} users," . 
           " {$this->tables['user_testplan_roles']} user_testplan_roles " .
           " WHERE  users.id = user_testplan_roles.user_id";
    $sql .= " AND user_testplan_roles.role_id = " . intval($this->dbID);
    $idSet = $db->fetchColumnsIntoArray($sql,"id");
    
    return $idSet; 
  }


  /**
     * Get a list of names with a defined project right (for example for combo-box)
     * used by ajax script getUsersWithRight.php
     * 
     * @param integer $db DB Identifier
     * @param string $rightNick key corresponding with description in rights table
     * @param integer $testprojectID Identifier of project
     *
     * @return array list of user IDs and names
     * 
     * @todo fix the case that user has default role with a right but project role without
     *     i.e. he should be listed
     */
  public function getNamesForProjectRight(&$db,$rightNick,$testprojectID = null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    if (is_null($testprojectID))
    {
      tLog( $debugMsg . ' requires Test Project ID defined','ERROR');
      return null;
    }
    
    $output = array();
    
    //get users for default roles
    $sql = "/* $debugMsg */ SELECT DISTINCT u.id,u.login,u.first,u.last FROM {$this->tables['users']} u" .
         " JOIN {$this->tables['role_rights']} a ON a.role_id=u.role_id" .
         " JOIN {$this->tables['rights']} b ON a.right_id = b.id " .
         " WHERE b.description='" . $db->prepare_string($rightNick) . "'";
    $defaultRoles = $db->fetchRowsIntoMap($sql,'id');

    // get users for project roles
    $sql = "/* $debugMsg */ SELECT DISTINCT u.id,u.login,u.first,u.last FROM {$this->tables['users']} u" .
         " JOIN {$this->tables['user_testproject_roles']} p ON p.user_id=u.id" .
         " AND p.testproject_id=" . intval($testprojectID) .
         " JOIN {$this->tables['role_rights']} a ON a.role_id=p.role_id" .
         " JOIN {$this->tables['rights']} b ON a.right_id = b.id " .
         " WHERE b.description='" . $db->prepare_string($rightNick) . "'";
    $projectRoles = $db->fetchRowsIntoMap($sql,'id');
    
    // merge arrays    
    // the next function is available from php53 but we support php52
    // $output = array_replace($output1, $output2);
    if( !is_null($projectRoles) )
    {
      foreach($projectRoles as $k => $v) 
      {
        if( !isset($defaultRoles[$k]) ) 
        {
          $defaultRoles[$k] = $v;
        }
      }
    }

    // format for ext-js combo-box (remove associated array)
    // foreach($defaultRoles as $k => $v) 
    // {
    //     $output[] = $v;
    // }
    $output = array_values($defaultRoles);   
       
    return $output;
  }

  
  /**
     * Get a list of all names 
     * used for replacement user ID by user login
     * 
     * @param integer $db DB Identifier
     * @return array list of user IDs and names
     */
  public function getNames(&$db,$idSet=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $sql = " SELECT id,login,first,last FROM {$this->tables['users']}";

    $inClause = '';
    if( !is_null($idSet) )
    {
      $inClause = " WHERE id IN (" . implode(',',(array)$idSet) . ") ";    
    }

    $output = $db->fetchRowsIntoMap($sql . $inClause,'id');
    return $output;
  }


  /**
   * check right on effective role for user, using test project and test plan,
   * means that check right on effective role.
   *
   * @return string|null 'yes' or null
   *
   * @internal revisions
   */
  function hasRight(&$db,$roleQuestion,$tprojectID = null,$tplanID = null,$getAccess=false)
  {
    global $g_propRights_global;
    global $g_propRights_product;

    if (!is_null($tplanID))
    {
      $testPlanID = $tplanID;
    }
    else
    {
      $testPlanID = isset($_SESSION['testplanID']) ? $_SESSION['testplanID'] : 0;
    }
    
    
    if (!is_null($tprojectID))
    {
      $testprojectID = $tprojectID;
    }
    else
    {
      $testprojectID = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
    }
    
    $accessPublic = null;
    if($getAccess)
    {
      if($testprojectID > 0)
      {
        $mgr = new testproject($db);
        $accessPublic['tproject'] = $mgr->getPublicAttr($testprojectID);
        unset($mgr);
      }  

      if($testPlanID > 0)
      {
        $mgr = new testplan($db);
        $accessPublic['tplan'] = $mgr->getPublicAttr($testPlanID);
        unset($mgr);
      }
    }
    
    $userGlobalRights = (array)$this->globalRole->rights;
    $globalRights = array();
    foreach($userGlobalRights as $right)
    {
      $globalRights[] = $right->name;
    }
    $allRights = $globalRights;
    $userTestProjectRoles = $this->tprojectRoles;
    $userTestPlanRoles = $this->tplanRoles;
    
    if (isset($userTestProjectRoles[$testprojectID]))
    {
      $userTestProjectRights = (array)$userTestProjectRoles[$testprojectID]->rights;

      // Special situation => just one right
      $doMoreAnalysis = true;
      if( count($userTestProjectRights) == 1)
      {
        $doMoreAnalysis = !is_null($userTestProjectRights[0]->dbID);
      }  

      $allRights = null;
      if( $doMoreAnalysis )
      {
        //echo 'do more';
        $testProjectRights = array();
        foreach($userTestProjectRights as $right)
        {
          $testProjectRights[] = $right->name;
        }

        // subtract global rights    
        $testProjectRights = array_diff($testProjectRights,array_keys($g_propRights_global));
        propagateRights($globalRights,$g_propRights_global,$testProjectRights);
        $allRights = $testProjectRights;
      }  
      else
      {
        return false;
      }  

    }
    else
    {
      if(!is_null($accessPublic) && $accessPublic['tproject'] == 0)
      {
        return false;      
      }  
    }

    if( $testPlanID > 0)
    {
      if (isset($userTestPlanRoles[$testPlanID]))
      {
        $userTestPlanRights = (array) $userTestPlanRoles[$testPlanID]->rights;
        $testPlanRights = array();
        foreach($userTestPlanRights as $right)
        {
          $testPlanRights[] = $right->name;
        }
        
        //subtract test projects rights    
        $testPlanRights = array_diff($testPlanRights,array_keys($g_propRights_product));
        
        propagateRights($allRights,$g_propRights_product,$testPlanRights);
        $allRights = $testPlanRights;
      }
      else
      {
        if(!is_null($accessPublic) && $accessPublic['tplan'] == 0)
        {
          return false;      
        }  
      }
    }
    return checkForRights($allRights,$roleQuestion);
  }

  /**
     * get array with accessible test plans for user on a test project, 
     * analising user roles.
     *
     * @param resource $db database handler  
     * @param int testprojectID 
     * @param int testplanID: default null. 
     *            Used as filter when you want to check if this test plan
     *            is accessible.
     *
     * @param map options keys :
     *               'output' => null -> get numeric array
     *                   => map => map indexed by testplan id
     *                   => combo => map indexed by testplan id and only returns name
     *              'active' => ACTIVE (get active test plans)
     *                   => INACTIVE (get inactive test plans)
     *                   => TP_ALL_STATUS (get all test plans)
     *
     * @return array if 0 accessible test plans => null
     *
     * @internal revisions
     * 
     */
  function getAccessibleTestPlans(&$db,$testprojectID,$testplanID=null, $options=null) {
    $debugTag = 'Class:' .  __CLASS__ . '- Method:' . __FUNCTION__ . '-';
    
    $my['options'] = array( 'output' => null, 'active' => ACTIVE);
    $my['options'] = array_merge($my['options'], (array)$options);
    
    $fields2get = ' NH.id, NH.name, TPLAN.is_public, ' .
                  ' COALESCE(USER_TPLAN_ROLES.testplan_id,0) AS has_role, ' .
                  ' USER_TPLAN_ROLES.role_id AS user_testplan_role, TPLAN.active, 0 AS selected ';

    if( $my['options']['output'] == 'mapfull' ) {
      $fields2get .= ' ,TPLAN.notes, TPLAN.testproject_id ';
    }    
    
    $sql = " /* $debugTag */  SELECT {$fields2get} " .
           " FROM {$this->tables['nodes_hierarchy']} NH" .
           " JOIN {$this->tables['testplans']} TPLAN ON NH.id=TPLAN.id  " .
           " LEFT OUTER JOIN {$this->tables['user_testplan_roles']} USER_TPLAN_ROLES" .
           " ON TPLAN.id = USER_TPLAN_ROLES.testplan_id " .
           " AND USER_TPLAN_ROLES.user_id = " . intval($this->dbID);
    

    // Construct where sentence
    $where = " WHERE testproject_id = " . intval($testprojectID) . " AND ";
    if (!is_null($my['options']['active'])) {
      $where .= " active = {$my['options']['active']} AND ";
    }
  
    if (!is_null($testplanID)) {
      $where .= " NH.id = " . intval($testplanID) . " AND ";
    }
    
    $analyseGlobalRole = 1;
    $userGlobalRoleIsNoRights = ($this->globalRoleID == TL_ROLES_NO_RIGHTS);

    // Role at Test Project level is defined?
    $userProjectRoleIsNoRights = 0;
    if( isset($this->tprojectRoles[$testprojectID]->dbID) ) {
      $userProjectRoleIsNoRights = 
        ($this->tprojectRoles[$testprojectID]->dbID == TL_ROLES_NO_RIGHTS); 
    }

    // according to new configuration option
    //
    // testplan_role_inheritance_mode
    //
    // this logic will be different
    $joins = '';
    switch ( config_get('testplan_role_inheritance_mode') ) {

      case 'testproject':
        // If user has a role for $testprojectID, then we DO NOT HAVE 
        // to check for globalRole
        if( isset($this->tprojectRoles[$testprojectID]->dbID) ) {
          $analyseGlobalRole = 0;
        }

        // User can have NO RIGHT on test project under analisys ($testprojectID), 
        // in this situation he/she 
        // has to have a role at Test Plan level in order to access one or more test plans 
        // that belong to $testprojectID.
        //
        // Other situation: he/she has been created with role without rights ($globalNoRights)
        //
        if( $userProjectRoleIsNoRights || 
            ($analyseGlobalRole && $userGlobalRoleIsNoRights) ) {
          // In order to access he/she needs specific configuration.
          $where .= "(USER_TPLAN_ROLES.role_id IS NOT NULL AND ";
        }  
        else {
          // in this situation:
          // We can use what we have inherited from test project 
          // OR 
          // We can use specific test plan role if defined            
          $where .= "(USER_TPLAN_ROLES.role_id IS NULL OR ";
        }
        $where .= " USER_TPLAN_ROLES.role_id != " . TL_ROLES_NO_RIGHTS .")"; 

      break;


      case 'global':

        // Because inheritance is from GLOBAL Role, do not need to care
        // about existence of specific role defined AT TEST PROJECT LEVEL

        // If User has NO RIGHTS at GLOBAL Level he/she need specific
        // on test plan
        if( $userGlobalRoleIsNoRights ) {
          // In order to access he/she needs specific configuration.
          $where .= "(USER_TPLAN_ROLES.role_id IS NOT NULL AND ";
        }  
        else {
          // in this situation:
          // We can use what we have inherited from GLOBAL
          // 
          // OR 
          // We can use specific test plan role if defined            
          $where .= "(USER_TPLAN_ROLES.role_id IS NULL OR ";
        }
        $where .= " USER_TPLAN_ROLES.role_id != " . TL_ROLES_NO_RIGHTS .")"; 
      break;
    }
    
    $sql .= $joins . $where;
    
    
    // new dBug($sql);  
    $sql .= " ORDER BY name";
    $numericIndex = false;
    switch($my['options']['output']) {
      case 'map':
      case 'mapfull':
        $testPlanSet = $db->fetchRowsIntoMap($sql,'id');
      break;
      
      case 'combo':
        $testPlanSet = $db->fetchRowsIntoMap($sql,'id');
      break;
      
      default:
        $testPlanSet = $db->get_recordset($sql);
        $numericIndex = true;
      break;
    }
                                           
    // Admin exception
    $doReindex = false;
    if( $this->globalRoleID != TL_ROLES_ADMIN && count($testPlanSet) > 0 )
    {
      foreach($testPlanSet as $idx => $item)
      {
        if( $item['is_public'] == 0 && $item['has_role'] == 0 )
        {
          unset($testPlanSet[$idx]);
          $doReindex = true;
        }         
      }
    } 
    
    if($my['options']['output'] == 'combo')
    {
      foreach($testPlanSet as $idx => $item)
      {
        $dummy[$idx] = $item['name'];
      }
      $testPlanSet = $dummy;
    }
    if( $doReindex && $numericIndex)
    {
      $testPlanSet = array_values($testPlanSet);
    }
    return $testPlanSet;
  }


  /**
   * Checks the correctness of an email address
   * 
   * @param string $email
   * @return integer returns tl::OK on success, errorcode else
   */
  static public function checkEmailAddress($email)
  {
    $result = is_blank($email) ? self::E_EMAILLENGTH : tl::OK;
    if ($result == tl::OK)
    {
      $matches = array();
      $email_regex = config_get('validation_cfg')->user_email_valid_regex_php;
      if (!preg_match($email_regex,$email,$matches))
      {
        $result = self::E_EMAILFORMAT;
      }  
    }
    return $result;
  }
  
  static public function checkFirstName($first)
  {
    return is_blank($first) ? self::E_FIRSTNAMELENGTH : tl::OK;
  }
  
  static public function checkLastName($last)
  {
    return is_blank($last) ? self::E_LASTNAMELENGTH : tl::OK;
  }
  
  /**
   *
   */
  static public function doesUserExist(&$db,$login)
  {
    $user = new tlUser();
    $user->login = $login;
    if ($user->readFromDB($db,self::USER_O_SEARCH_BYLOGIN) >= tl::OK) {
      return $user->dbID;
    }
    return null;
  }

  /**
   *
   */
  static public function doesUserExistByEmail(&$db,$email) {
    $user = new tlUser();
    $user->emailAddress = $email;
    if ($user->readFromDB($db,self::USER_O_SEARCH_BYEMAIL) >= tl::OK) {
      return $user->dbID;
    }
    return null;
  }

  
  /**
   *
   */
  static public function getByID(&$db,$id,$detailLevel = self::TLOBJ_O_GET_DETAIL_FULL) {
    return tlDBObject::createObjectFromDB($db,$id,__CLASS__,self::TLOBJ_O_SEARCH_BY_ID,$detailLevel);
  }
  

  /**
   *
   */
  static public function getByIDs(&$db,$ids,$detailLevel = self::TLOBJ_O_GET_DETAIL_FULL) {
    $users = null;
 
    if( null == $ids ) {
      return null;
    }

    for($idx = 0;$idx < sizeof($ids);$idx++) {
      $id = $ids[$idx];
      $user = tlDBObject::createObjectFromDB($db,$id,__CLASS__,self::TLOBJ_O_SEARCH_BY_ID,$detailLevel);
      if ($user) {  
        $users[$id] = $user;
      }  
    }
    return $users ? $users : null;
  }

  static public function getAll(&$db,$whereClause = null,$column = null,$orderBy = null,
                                $detailLevel = self::TLOBJ_O_GET_DETAIL_FULL)
  {
    $tables = tlObject::getDBTables('users');
    $sql = " SELECT id FROM {$tables['users']} ";
    if (!is_null($whereClause))
    {
      $sql .= ' '.$whereClause;
    }
    $sql .= is_null($orderBy) ? " ORDER BY login " : $orderBy;
    
    return tlDBObject::createObjectsFromDBbySQL($db,$sql,'id',__CLASS__,true,$detailLevel);
  }

  /** 
   */
  public function setActive(&$db,$value)
  {
    $booleanVal = intval($value) > 0 ? 1 : 0;
    $sql = " UPDATE {$this->tables['users']} SET active = {$booleanVal} " .
           " WHERE id = " . intval($this->dbID);
    $result = $db->exec_query($sql);
    return tl::OK;
  }


  /** 
   * Writes user password into the database
   * 
   * @param resource &$db reference to database handler
   * @return integer tl::OK if no problem written to the db, else error code
   *
   * (ideas regarding cookie_string -> from Mantisbt).
   *
   * @internal revisions
   */
  public function writePasswordToDB(&$db)
  {
    if($this->dbID)
    {
      // After addition of cookie_string, and following Mantisbt pattern,
      // seems we need to check if password has changed.
      //
      // IMPORTANT NOTICE: 
      // this implementation works ONLY when password is under TestLink control
      // i.e. is present on TestLink Database.
      //
      // if answer is yes => change also cookie_string.
      $t_cookie_string = null;

      $gsql = " SELECT password FROM {$this->object_table} WHERE id = " . intval($this->dbID);
      $rs = $db->get_recordset($gsql);
      if(strcmp($rs[0]['password'],$this->password) != 0) 
      {
        // Password HAS CHANGED
        $t_cookie_string = $this->auth_generate_unique_cookie_string($db);   
      }    
      
      $sql = "UPDATE {$this->tables['users']} " .
             " SET password = ". "'" . $db->prepare_string($this->password) . "'";
      
      if(!is_null($t_cookie_string) )
      {        
        $sql .= ", cookie_string = " .  "'" . $db->prepare_string($t_cookie_string) . "'";
      }        
      $sql .= " WHERE id = " . intval($this->dbID);
      $result = $db->exec_query($sql);
    }
    $result = $result ? tl::OK : self::E_DBERROR;
    return $result;
  }  


  /**
   * (from Mantisbt)
   *
   * Generate a string to use as the identifier for the login cookie
   * It is not guaranteed to be unique and should be checked
   * The string returned should be 64 characters in length
   * @return string 64 character cookie string
   * @access public
   */
  function auth_generate_cookie_string() 
  {
    $t_val = mt_rand( 0, mt_getrandmax() ) + mt_rand( 0, mt_getrandmax() );
    $t_val = md5( $t_val ) . md5( time() );
    return $t_val;
  }

  /**
   * (from Mantisbt)
   *
   * Return true if the cookie login identifier is unique, false otherwise
   * @param string $p_cookie_string
   * @return bool indicating whether cookie string is unique
   * @access public
   */
  function auth_is_cookie_string_unique(&$db,$p_cookie_string) 
  {
    $sql = "SELECT COUNT(0) AS hits FROM $this->object_table " .
           "WHERE cookie_string = '" . $db->prepare_string($p_cookie_string) . "'" ;
    $rs = $db->fetchFirstRow($sql);
  
    if( !is_array($rs) )
    {
      // better die because this method is used in a do/while
      // that can create infinite loop
      die(__METHOD__);  
    }
    $status = ($rs['hits'] == 0);
    return $status;
  }

  /**
   * (from Mantisbt)
   *
   * Generate a UNIQUE string to use as the identifier for the login cookie
   * The string returned should be 64 characters in length
   *
   * @return string 64 character cookie string
   * @access public
   *
   * @since 1.9.4
   */
  function auth_generate_unique_cookie_string(&$db) 
  {
    do {
      $t_cookie_string = $this->auth_generate_cookie_string();
    }
    while( !$this->auth_is_cookie_string_unique($db,$t_cookie_string ) );
  
    return $t_cookie_string;
  }


  /**
   * (from Mantisbt)
   *
   * @since 1.9.4
   */
  static function auth_get_current_user_cookie() 
  {
    $t_cookie_name = config_get('auth_cookie');
    $t_cookie = isset($_COOKIE[$t_cookie_name]) ? $_COOKIE[$t_cookie_name] : null;  
    return $t_cookie;
  }

  /**
   * (from Mantisbt)
   *
   * is cookie valid?
   * @param string $p_cookie_string
   * @return bool
   * @access public
   *
   * @since 1.9.4
   */
  function auth_is_cookie_valid(&$db,$p_cookie_string) 
  {
    # fail if cookie is blank
    $status = ('' === $p_cookie_string) ? false : true;
    
    if( $status )
    {
      # look up cookie in the database to see if it is valid
      $sql =  "SELECT COUNT(0) AS hits FROM $this->object_table " .
              "WHERE cookie_string = '" . $db->prepare_string($p_cookie_string) . "'" ;
      $rs = $db->fetchFirstRow($sql);
        
      if( !is_array($rs) )
      {
        // better die because this method is used in a do/while
        // that can create infinite loop
        die(__METHOD__);  
      }
      $status = ($rs['hits'] == 1);
    }
    return $status;
  }

  /**
   * (from Mantisbt)
   * 
   * Getter 
   * 
   * @return string 
   *
   * @since 1.9.4
   */
  public function getSecurityCookie()
  {
    return $this->securityCookie;
  }

  /**
   *
   */
  static function hasRoleOnTestProject(&$dbHandler,$id,$tprojectID)
  {
    $tables = tlObject::getDBTables('user_testproject_roles');
    $sql = " SELECT user_id FROM {$tables['user_testproject_roles']} " .
           ' WHERE testproject_id=' . intval($tprojectID) . ' AND user_id=' . intval($id);
    $rs = $dbHandler->fetchRowsIntoMap($sql, "user_id");
    return !is_null($rs);
  }

  /**
   *
   */
  static function hasRoleOnTestPlan(&$dbHandler,$id,$tplanID)
  {
    $tables = tlObject::getDBTables('user_testplan_roles');
    $sql = " SELECT user_id FROM {$tables['user_testplan_roles']} " .
           ' WHERE testplan_id=' . intval($tplanID) . ' AND user_id=' . intval($id);
    $rs = $dbHandler->fetchRowsIntoMap($sql, "user_id");
    return !is_null($rs);
  }


  /**
   *
   */
  static public function getByAPIKey(&$dbHandler,$value)
  {
    $tables = tlObject::getDBTables('users');
    $target = $dbHandler->prepare_string($value);
    $sql = "SELECT * FROM {$tables['users']} WHERE script_key='" . $dbHandler->prepare_string($target) . "'";

    $rs = $dbHandler->fetchRowsIntoMap($sql, "id");
    return $rs;
  }

  /**
   * @use _SESSION
   * 
   */
  function checkGUISecurityClearance(&$dbHandler,$context,$rightsToCheck,$checkMode)
  {
    $doExit = false;
    $action = 'any';
    $myContext = array('tproject_id' => 0, 'tplan_id' => 0);
    $myContext = array_merge($myContext, $context);

    if( $doExit = (is_null($myContext) || $myContext['tproject_id'] == 0) )
    {
      logAuditEvent(TLS("audit_security_no_environment",$myContext['script']), $action,$this->dbID,"users");
    }
     
    if( !$doExit )
    {
      foreach($rightsToCheck as $verboseRight)
      {
        $status = $this->hasRight($dbHandler,$verboseRight,$myContext['tproject_id'],$myContext['tplan_id']);
    
        if( ($doExit = !$status) && ($checkMode == 'and'))
        { 
          $action = 'any';
          logAuditEvent(TLS("audit_security_user_right_missing",$this->login,$myContext['script'],$action),
                        $action,$this->dbID,"users");
          break;
        }
      }
    }
    if ($doExit)
    {   
      redirect($_SESSION['basehref'],"top.location");
      exit();
    }
  }


  /**
   *
   */
  static function checkPasswordQuality($password)
  {
    $ret = array('status_ok' => tl::OK, 'msg' => 'ok');
    $cfg = config_get('passwordChecks');
    if( is_null($cfg) )
    {
      return $ret;  // >>---> Bye!
    }  

    $regexp['number'] = "#[0-9]+#";
    $regexp['letter'] = "#[a-z]+#";
    $regexp['capital'] = "#[A-Z]+#";
    $regexp['symbol'] = "#\W+#";

    $pl = strlen($password);

    foreach($cfg as $attr => $val)
    {
      $base_msg = lang_get('bad_password_' . $attr);
      switch($attr)
      {
        case 'minlen':
          if( $pl < intval($val) )
          {
            $ret['status_ok'] = tl::ERROR;
            $ret['msg'] = sprintf($base_msg,intval($val), $pl);
          }  
        break;

        case 'maxlen':
          if( $pl > intval($val) )
          {
            $ret['status_ok'] = tl::ERROR;
            $ret['msg'] = sprintf($base_msg, intval($val), $pl);
          }  
        break;

        case 'number':
        case 'letter':
        case 'capital':
        case 'symbol':
          if( !preg_match($regexp[$attr], $password) )
          {
            $ret['status_ok'] = tl::ERROR;
            $ret['msg'] = $base_msg;
          }  
        break;
      }

      if($ret['status_ok'] == tl::ERROR)
      {
        break;
      }  
    }  
    return $ret;

  }

  /** 
   */
  static public function setExpirationDate(&$dbHandler,$userID,$ISODate)
  {
    $sch = tlObject::getDBTables(array('users'));

    $setClause = " SET expiration_date = ";
    if( is_null($ISODate) || trim($ISODate) == '' )
    {
      $setClause .= " NULL "; 
    }  
    else
    {
      // it's really a date?
      // if not => do nothing
      try {
        $xx = new DateTime($ISODate);
        $setClause .= "'" . $dbHandler->prepare_string($ISODate) . "'"; 
      } 
      catch (Exception $e) {
        return;
      }
    }  

    $sql = " UPDATE {$sch['users']} {$setClause} " .
           " WHERE id = " . intval($userID);

    $rx = $dbHandler->exec_query($sql);
    return tl::OK;
  }

}