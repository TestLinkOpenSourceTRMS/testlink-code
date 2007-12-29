<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: user.class.php,v $
 *
 * @version $Revision: 1.4 $
 * @modified $Date: 2007/12/29 08:27:51 $ $Author: franciscom $
 *
 */

//SCHLUNDUS: I will cleanup this file step by step
//SCHLUNDUS: not completed
class tlUser extends tlDBObject
{
	public $firstName;
	public $lastName;
	public $emailAddress;
	public $locale;
	public $bActive;
	public $defaultTestprojectID;
	public $globalRole;
	public $globalRoleID;
	public $login;
	protected $password;
	
	//configuration options
	protected $showRealname;
	protected $usernameFormat;
	protected $loginMethod;
	protected $maxLoginLength;
	
	//error codes
	const USER_E_LOGINLENGTH = -1;
	const USER_E_EMAILLENGTH = -2;
	const USER_E_NOTALLOWED = -4;
	const USER_E_DBERROR = -8;
	const USER_E_FIRSTNAMELENGTH = -16;
	const USER_E_LASTNAMELENGTH = -32;
	const USER_E_PWDEMPTY = -64;
	const USER_E_PWDDONTMATCH = -128;
	const USER_E_LOGINALREADYEXISTS = -256;
	
	const USER_S_PWDMGTEXTERNAL = 2;
	
	//search options
	const USER_O_SEARCH_BYLOGIN = 2;
	
	function __construct($dbID = null)
	{
		parent::__construct($dbID);
		
		$this->showRealname = config_get('show_realname');
		$this->usernameFormat = config_get('username_format');
		$this->loginRegExp = config_get('user_login_valid_regex');
		$this->maxLoginLength = 30; 
		$this->loginMethod = config_get('login_method');
		
		$this->globalRoleID = TL_DEFAULT_ROLEID;
		$this->locale =  TL_DEFAULT_LOCALE;
		$this->bActive = 1;
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

	}
	
	static public function isPasswordMgtExternal()
	{
		$loginMethod = config_get('login_method');
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
		         " login AS fullname, active,default_testproject_id FROM users";
		
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
			
			if ($this->globalRoleID)
			{
				$this->globalRole = new tlRole($this->globalRoleID);
				$this->globalRole->readFromDB($db);
			}
			
			$this->locale = $info['locale'];
			$this->password = $info['password'];
			$this->bActive = $info['active'];
			$this->defaultTestprojectID = $info['default_testproject_id'];
		}
		return $info ? tl::OK : tl::ERROR;
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
				   ", role_id = ". "'" . $db->prepare_string($this->globalRoleID) . "'" .
				   ", active = ". "'" . $db->prepare_string($this->bActive) . "'" ;
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
			$result = $result ? tl::OK : self::USER_E_DBERROR;
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
		if (!$this->showRealname)
			return $this->login;

		$keys = array('%first%','%last%','%login%','%email%');
		$values = array($this->firstName, $this->lastName,$this->login,$this->emailAddress);
		
		$displayName = trim(str_replace($keys,$values,$this->usernameFormat));
	
		return $displayName;
	}
	
	protected function encryptPassword($pwd)
	{
		if (self::isPasswordMgtExternal())
			return self::USER_S_PWDMGTEXTERNAL;

		return md5($pwd);
	}
	
	public function setPassword($pwd)
	{
		if (self::isPasswordMgtExternal())
			return self::USER_S_PWDMGTEXTERNAL;
			
		if (!strlen($pwd))
			return self::USER_E_PWDEMPTY;
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
			return self::USER_S_PWDMGTEXTERNAL;

		if ($this->getPassword($pwd) == $this->encryptPassword($pwd))
			return tl::OK;
		return self::USER_E_PWDDONTMATCH;		
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
			$result = self::doesUserExist($db,$this->login) ? self::USER_E_LOGINALREADYEXISTS : tl::OK;
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
			$result = self::USER_E_LOGINLENGTH;
		else if (!preg_match($this->loginRegExp,$login)) //Only allow a basic set of characters
			$result = self::USER_E_NOTALLOWED;

		return $result;
	}
	
	static public function checkEmailAdress($email)
	{
		return is_blank($email) ? self::USER_E_EMAILLENGTH : tl::OK;
	}
	
	static public function checkFirstName($first)
	{
		return is_blank($first) ? self::USER_E_FIRSTNAMELENGTH : tl::OK;
	}
	
	static public function checkLastName($last)
	{
		return is_blank($last) ? self::USER_E_LASTNAMELENGTH : tl::OK;
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
		if ($id)
			return tlDBObject::createObjectFromDB($db,$id,__CLASS__,self::TLOBJ_O_SEARCH_BY_ID,$detailLevel);
		return null;
	}
	
  /*
    function: 

    args :
    
    returns: 

  */
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
