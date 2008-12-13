<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: user.class.php,v $
 *
 * @version $Revision: 1.22 $
 * @modified $Date: 2008/12/13 19:25:41 $ $Author: franciscom $
 *
 * rev: 20081213 - franciscom - removed global coupling to access config parameters
 */
class tlUser extends tlDBObject
{
	private $object_table = "users";

	public $firstName;
	public $lastName;
	public $emailAddress;
	public $locale;
	public $bActive;
	public $defaultTestprojectID;
	public $globalRole;
	public $globalRoleID;
	public $tprojectRoles; 
	public $tplanRoles;
	public $login;
	public $userApiKey;
	protected $password;
	
	//configuration options
	protected $usernameFormat;
	protected $loginMethod;
	protected $maxLoginLength;
	
	//error codes
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
	
	//search options
	const USER_O_SEARCH_BYLOGIN = 2;
	
	
	//detail leveles
	const TLOBJ_O_GET_DETAIL_ROLES = 1;
	
	function __construct($dbID = null)
	{
		parent::__construct($dbID);
		
		$authCfg = config_get('authentication');
		$this->usernameFormat = config_get('username_format');
		$this->loginRegExp = config_get('user_login_valid_regex');
		$this->maxLoginLength = 30; 
		$this->loginMethod = $authCfg['method'];
		
		$this->globalRoleID = config_get('default_roleid');
		$this->locale = config_get('default_language');
		$this->bActive = 1;
		$this->tprojectRoles = null;
		$this->tplanRoles = null;
	}
	
	protected function _clean($options = self::TLOBJ_O_SEARCH_BY_ID)
	{
		$this->firstName = null;
		$this->lastName = null;
		$this->emailAddress = null;
		$this->locale = null;
		$this->password = null;
		$this->bActive = null;
		$this->defaultTestprojectID = null;
		$this->globalRoleID = null;
		if (!($options & self::TLOBJ_O_SEARCH_BY_ID))
			$this->dbID = null;
		if (!($options & self::USER_O_SEARCH_BYLOGIN))
			$this->login = null;
		$this->tprojectRoles = null;
		$this->tplanRoles = null;
		$this->userApiKey = null;
	}
	
	static public function isPasswordMgtExternal()
	{
		$authCfg = config_get('authentication');
		$loginMethod = $authCfg['method'];
		if ($loginMethod != '' &&  $loginMethod != 'MD5')
			return true;
		return false;
	}
	
	/* fills the members  */
	function create()
	{
	}
	//BEGIN interface iDBSerialization
	public function readFromDB(&$db,$options = self::TLOBJ_O_SEARCH_BY_ID)
	{
		$this->_clean($options);
		$query = "SELECT id,login,password,first,last,email,role_id,locale, " .
		         " login AS fullname, active,default_testproject_id, script_key " .
		         " FROM {$this->object_table}";
		
		$clauses = null;
		if ($options & self::TLOBJ_O_SEARCH_BY_ID)
			$clauses[] = "id = {$this->dbID}";		
		if ($options & self::USER_O_SEARCH_BYLOGIN)
			$clauses[] = "login = '".$db->prepare_string($this->login)."'";		
		if ($clauses)
			$query .= " WHERE " . implode(" AND ",$clauses);
		$info = $db->fetchFirstRow($query);	
		if ($info)
		{
			$this->dbID = $info['id'];
			$this->firstName = $info['first'];
			$this->lastName = $info['last'];
			$this->login = $info['login'];
			$this->emailAddress = $info['email'];
			$this->globalRoleID = $info['role_id'];
			$this->userApiKey = $info['script_key'];
			
			if ($this->globalRoleID)
			{
				$this->globalRole = new tlRole($this->globalRoleID);
				$this->globalRole->readFromDB($db);
			}
			if ($this->detailLevel & self::TLOBJ_O_GET_DETAIL_ROLES)
			{
				$this->readTestProjectRoles($db);
				$this->readTestPlanRoles($db);
			}
			
			$this->locale = $info['locale'];
			$this->password = $info['password'];
			$this->bActive = $info['active'];
			$this->defaultTestprojectID = $info['default_testproject_id'];
		}
		return $info ? tl::OK : tl::ERROR;
	}
	
	protected function readTestProjectRoles(&$db)
	{
		$query = "SELECT testproject_id,role_id FROM user_testproject_roles WHERE user_id = {$this->dbID}";
		$allRoles = $db->fetchColumnsIntoMap($query,'testproject_id','role_id');
		$this->tprojectRoles = null;
		if (sizeof($allRoles))
		{
			foreach($allRoles as $tprojectID => $roleID)
			{
				$tpRole = tlRole::createObjectFromDB($db,$roleID,"tlRole",true);
				if ($tpRole)
					$this->tprojectRoles[$tprojectID] = $tpRole;
			}
		}
		return tl::OK;
	}
	
	protected function readTestPlanRoles(&$db)
	{
		$query = "SELECT testplan_id,role_id FROM user_testplan_roles WHERE user_id = {$this->dbID}";
		$allRoles = $db->fetchColumnsIntoMap($query,'testplan_id','role_id');
		$this->tplanRoles = null;
		if (sizeof($allRoles))
		{
			foreach($allRoles as $tplanID => $roleID)
			{
				$tpRole = tlRole::createObjectFromDB($db,$roleID,"tlRole",true);
				if ($tpRole)
					$this->tplanRoles[$tplanID] = $tpRole;
			}
		}
		return tl::OK;
	}
	
	public function writeToDB(&$db)
	{
		$result = $this->checkDetails($db);
		if ($result >= tl::OK)
		{		
			if($this->dbID)
			{
				$query = "UPDATE users " .
			       "SET first='" . $db->prepare_string($this->firstName) . "'" .
			       ", last='" .  $db->prepare_string($this->lastName)    . "'" .
			       ", email='" . $db->prepare_string($this->emailAddress)   . "'" .
				   ", locale = ". "'" . $db->prepare_string($this->locale) . "'" . 
				   ", password = ". "'" . $db->prepare_string($this->password) . "'" .
				   ", role_id = ". $db->prepare_string($this->globalRoleID) . 
				   ", active = ". $db->prepare_string($this->bActive);
				$query .= " WHERE id=" . $this->dbID;
				$result = $db->exec_query($query);
			}
			else
			{
				$query = "INSERT INTO users (login,password,first,last,email,role_id,locale,active) 
							VALUES ('" . 
							$db->prepare_string($this->login) . "','" . $db->prepare_string($this->password) . "','" . 
							$db->prepare_string($this->firstName) . "','" . $db->prepare_string($this->lastName) . "','" . 
							$db->prepare_string($this->emailAddress) . "'," . $this->globalRoleID. ",'". 
							$db->prepare_string($this->locale). "'," . $this->bActive . ")";
				$result = $db->exec_query($query);
				if($result)
					$this->dbID = $db->insert_id('users');
			}
			$result = $result ? tl::OK : self::E_DBERROR;
		}
		return $result;
	}	
	
	public function deleteFromDB(&$db)
	{
		$query = "DELETE FROM users WHERE id=" . $this->dbID;
		$result = $db->exec_query($query) ? tl::OK : tl::ERROR;
		if ($result == tl::OK)
			$result = $this->deleteTestProjectRoles($db);
		
		return $result;
	}

	/**
	 * Deletes all testproject related role assignments for a given user
	 *
	 * @param object $db [ref] the db-object
	 * @param int $userID the user id
	 * @return tl::OK on success, tl:ERROR else
	 **/
	 protected function deleteTestProjectRoles(&$db)
	{
		$query = "DELETE FROM user_testproject_roles WHERE user_id = {$this->dbID}";
		return $db->exec_query($query) ? tl::OK : tl::ERROR;
	}

	public function getDisplayName()
	{
		$keys = array('%first%','%last%','%login%','%email%');
		$values = array($this->firstName, $this->lastName,$this->login,$this->emailAddress);
		
		$displayName = trim(str_replace($keys,$values,$this->usernameFormat));

		return $displayName;
	}
	
	protected function encryptPassword($pwd)
	{
		if (self::isPasswordMgtExternal())
			return self::S_PWDMGTEXTERNAL;

		return md5($pwd);
	}
	
	public function setPassword($pwd)
	{
		if (self::isPasswordMgtExternal())
			return self::S_PWDMGTEXTERNAL;
			
		if (!strlen($pwd))
			return self::E_PWDEMPTY;
		$this->password = $this->encryptPassword($pwd);
		return tl::OK;
	}
	
	public function getPassword()
	{
		return $this->password;
	}
	
	public function comparePassword($pwd)
	{
		if (self::isPasswordMgtExternal())
			return self::S_PWDMGTEXTERNAL;

		if ($this->getPassword($pwd) == $this->encryptPassword($pwd))
			return tl::OK;
		return self::E_PWDDONTMATCH;		
	}
	
	public function checkDetails(&$db)
	{
		$this->firstName = trim($this->firstName);
		$this->lastName = trim($this->lastName);
		$this->emailAddress = trim($this->emailAddress);
		$this->locale = trim($this->locale);
		$this->bActive = intval($this->bActive);
		$this->login = trim($this->login);
	
		$result = self::checkEmailAdress($this->emailAddress);
		if ($result >= tl::OK)
			$result = $this->checkLogin($this->login);
		if ($result >= tl::OK && !$this->dbID)
			$result = self::doesUserExist($db,$this->login) ? self::E_LOGINALREADYEXISTS : tl::OK;
		if ($result >= tl::OK)
			$result = self::checkFirstName($this->firstName);
		if ($result >= tl::OK)
			$result = self::checkLastName($this->lastName);
			
		return $result;
	}
	
	public function checkLogin($login)
	{
		$result = tl::OK;
		$login = trim($login);
		//simple check for empty login, or login consisting only of whitespaces
		//The DB field is only 30 characters
		if (!strlen($login) || (strlen($login) > $this->maxLoginLength))
			$result = self::E_LOGINLENGTH;
		else if (!preg_match($this->loginRegExp,$login)) //Only allow a basic set of characters
			$result = self::E_NOTALLOWED;

		return $result;
	}
	
	/*
		Returns the id of the effective role in the context of ($tproject_id,$tplan_id)
	  	@param object $db [ref] the db-object
		@param int $tproject_id the testproject id
		@param int $tplan_id the plan id
  
		@return tlRole the effective role
	*/
	function getEffectiveRole(&$db,$tproject_id,$tplan_id)
	{
		$default_role = $this->globalRole;
		$tprojects_role = $this->tprojectRoles;
		$tplans_role = $this->tplanRoles;

		$effective_role = $default_role;
		if(!is_null($tplans_role) && isset($tplans_role[$tplan_id]))
			$effective_role = $tplans_role[$tplan_id];  
		else if(!is_null($tprojects_role) && isset($tprojects_role[$tproject_id]))
			$effective_role = $tprojects_role[$tproject_id];  
		
		return $effective_role;
	}
	
	function hasRight(&$db,$roleQuestion,$tprojectID = null,$tplanID = null)
	{
		global $g_propRights_global;
		global $g_propRights_product;
		
		$userGlobalRights = (array)$this->globalRole->rights;
		$globalRights = array();
		foreach($userGlobalRights as $right)
		{
			$globalRights[] = $right->name;
		}
		
		if (!is_null($tplanID))
			$testPlanID = $tplanID;
		else
			$testPlanID = isset($_SESSION['testPlanId']) ? $_SESSION['testPlanId'] : 0;
		$userTestPlanRoles = $this->tplanRoles;
		
		if (!is_null($tprojectID))
			$productID = $tprojectID;
		else
			$productID = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
		
		$allRights = $globalRights;
			
		$userTestProjectRoles = $this->tprojectRoles;
		/* if $productID == -1 we dont check rights at product level! */
		if (isset($userTestProjectRoles[$productID]))
		{
			$userProductRights = (array)$userTestProjectRoles[$productID]->rights;
			$productRights = array();
			foreach($userProductRights as $right)
			{
				$productRights[] = $right->name;
			}
			//subtract global rights		
			$productRights = array_diff($productRights,array_keys($g_propRights_global));
			propagateRights($globalRights,$g_propRights_global,$productRights);
			$allRights = $productRights;
		}
		/* if $tplanID == -1 we dont check rights at tp level! */
		if (isset($userTestPlanRoles[$testPlanID]))
		{
			$userTestPlanRights = (array) $userTestPlanRoles[$testPlanID]->rights;
			$testPlanRights = array();
			foreach($userTestPlanRights as $right)
			{
				$testPlanRights[] = $right->name;
			}
			//subtract product rights		
			$testPlanRights = array_diff($testPlanRights,array_keys($g_propRights_product));
			
			propagateRights($allRights,$g_propRights_product,$testPlanRights);
			$allRights = $testPlanRights;
		}
		return checkForRights($allRights,$roleQuestion);
	}
	
	
	static public function checkEmailAdress($email)
	{
		$result = is_blank($email) ? self::E_EMAILLENGTH : tl::OK;
		if ($result == tl::OK)
		{
			if (!preg_match(config_get('user_email_valid_regex'),$email))
				$result = self::E_EMAILFORMAT;
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
	
	static public function doesUserExist(&$db,$login)
	{
		$user = new tlUser();
		$user->login = $login;
		if ($user->readFromDB($db,self::USER_O_SEARCH_BYLOGIN) >= tl::OK)
			return $user->dbID;
		return null;
	}
	
	static public function getByID(&$db,$id,$detailLevel = self::TLOBJ_O_GET_DETAIL_FULL)
	{
		return tlDBObject::createObjectFromDB($db,$id,__CLASS__,self::TLOBJ_O_SEARCH_BY_ID,$detailLevel);
	}
	

	static public function getByIDs(&$db,$ids,$detailLevel = self::TLOBJ_O_GET_DETAIL_FULL)
	{
		$users = null;
		for($i = 0;$i < sizeof($ids);$i++)
		{
			$id = $ids[$i];
			$user = tlDBObject::createObjectFromDB($db,$id,__CLASS__,self::TLOBJ_O_SEARCH_BY_ID,$detailLevel);
			if ($user)
				$users[$id] = $user;
		}
		return $users ? $users : null;
	}

	static public function getAll(&$db,$whereClause = null,$column = null,$orderBy = null,
	                              $detailLevel = self::TLOBJ_O_GET_DETAIL_FULL)
	{
		$query = " SELECT id FROM users";
		if (!is_null($whereClause))
			$query .= ' '.$whereClause;
	
		$query .= is_null($orderBy) ? " ORDER BY login " : $orderBy;
		return tlDBObject::createObjectsFromDBbySQL($db,$query,'id',__CLASS__,true,$detailLevel);
	}
}
?>
