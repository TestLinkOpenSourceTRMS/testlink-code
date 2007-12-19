<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: users.inc.php,v $
 *
 * @version $Revision: 1.58 $
 * @modified $Date: 2007/12/19 21:33:40 $ $Author: schlundus $
 *
 * Functions for usermanagement
 *
 * 20050821 - fm - BUGID 239
 * 20051231 - scs - changes due to ADBdb
 * 20060205 - JBA - Remember last product (BTS 221); added by MHT
 * 20060224 - franciscom - changes in session product -> testproject
 * 20070104 - franciscom - changes in getUserName()
 * 20070106 - franciscom - getAllUsers() - new argument order_by
**/
require_once("common.php");

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
	
	//error codes ...
	const USER_E_LOGINLENGTH = -1;
	const USER_E_EMAILLENGTH = -2;
	const USER_E_NOTALLOWED = -4;
	const USER_E_DBERROR = -8;
	const USER_E_FIRSTNAMELENGTH = -16;
	const USER_E_LASTNAMELENGTH = -32;
	const USER_E_PWDEMPTY = -64;
	const USER_E_PWDDONTMATCH = -128;
	const USER_E_LOGINALREADYEXISTS = -256;

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
	/* fills the members  */
	function create()
	{
	}
	//BEGIN interface iDBSerialization
	public function readFromDB(&$db,$options = self::TLOBJ_O_SEARCH_BY_ID)
	{
		$this->_clean($options);
		$query = "SELECT id,login,password,first,last,email,role_id,locale, login AS fullname, active,default_testproject_id FROM users";
		
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
		return $info ? OK : ERROR;
	}
	public function writeToDB(&$db)
	{
		$result = $this->checkDetails($db);
		if ($result == OK)
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
			$result = $result ? OK : self::USER_E_DBERROR;
		}
		return $result;
	}	
	public function deleteFromDB(&$db)
	{
		$query = "DELETE FROM users WHERE id=" . $this->dbID;
		$result = $db->exec_query($query);
			
		return $result ? OK : ERRROR;
	}
	
	public function getDisplayName()
	{
		if (!$this->showRealname)
			return $this->login;

		$keys = array('%first%','%last%','%login%','%email%');
		$values = array($this->firstName, $this->lastName,$this->login,$this->emailAddress);
		
		$displayName = str_replace($keys,$values,$this->usernameFormat);
	
		return $displayName;
	}
	protected function encryptPassword($pwd)
	{
		return md5($pwd);
	}
	public function setPassword($pwd)
	{
		if ($this->loginMethod == 'MD5' && !strlen($pwd))
			return self::USER_E_PWDEMPTY;
		$this->password = $this->encryptPassword($pwd);
		return OK;
	}
	public function getPassword()
	{
		return $this->password;
	}
	public function comparePassword($pwd)
	{
		if ($this->getPassword($pwd) == $this->encryptPassword($pwd))
			return OK;
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
	
		$result = tlUser::checkEmailAdress($this->emailAddress);
		if ($result == OK)
			$result = $this->checkLogin($this->login);
		if ($result == OK && !$this->dbID)
			$result = tlUser::doesUserExist($db,$this->login) ? self::USER_E_LOGINALREADYEXISTS : OK;
		if ($result == OK)
			$result = tlUser::checkFirstName($this->firstName);
		if ($result == OK)
			$result = tlUser::checkLastName($this->lastName);
			
		return $result;
	}
	public function checkLogin($login)
	{
		$result = OK;
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
		return is_blank($email) ? self::USER_E_EMAILLENGTH : OK;
	}
	static public function checkFirstName($first)
	{
		return is_blank($first) ? self::USER_E_FIRSTNAMELENGTH : OK;
	}
	static public function checkLastName($last)
	{
		return is_blank($last) ? self::USER_E_LASTNAMELENGTH : OK;
	}
	static public function doesUserExist(&$db,$login)
	{
		$user = new tlUser();
		$user->login = $login;
		if ($user->readFromDB($db,tlUser::USER_O_SEARCH_BYLOGIN) == OK)
			return $user->dbID;
		return null;
	}
	static public function getByID(&$db,$id)
	{
		return tlDBObject::createObjectFromDB($db,$id,__CLASS__,tlUser::TLOBJ_O_SEARCH_BY_ID);
	}
}

class tlRole extends tlDBObject
{
	public $description;
	public $notes;
	
	function __construct($dbID = null)
	{
		parent::__construct($dbID);
	}
	protected function _clean($options = self::TLOBJ_O_SEARCH_BY_ID)
	{
		$this->description = null;
		$this->notes = null;
		if (!($options & self::TLOBJ_O_SEARCH_BY_ID))
			$this->dbID = null;
	}
	//BEGIN interface iDBSerialization
	public function readFromDB(&$db,$options = self::TLOBJ_O_SEARCH_BY_ID)
	{
		$this->_clean($options);
		$query = "SELECT id,description, notes FROM roles";
		
		$clauses = null;
		if ($options & self::TLOBJ_O_SEARCH_BY_ID)
			$clauses[] = "id = {$this->dbID}";		
		if ($clauses)
			$query .= " WHERE " . implode(" AND ",$clauses);
			
		$info = $db->fetchFirstRow($query);			 
		if ($info)
		{
			$this->description = $info['description'];
			$this->notes = $info['notes'];
		}
		return $info ? OK : ERROR;
	}
	public function writeToDB(&$db)
	{
	
	}
	public function deleteFromDB(&$db)
	{
	
	}
	static public function getByID(&$db,$id)
	{
		return tlDBObject::createObjectFromDB($db,$id,__CLASS__,tlRole::TLOBJ_O_SEARCH_BY_ID);
	}
}

if( 'LDAP' == config_get('login_method') )
{
  require_once(dirname(__FILE__) . "/ldap_api.php");
}


/**
 * set session data after modification or authorization
 *
 * @param type $db [ref] documentation
 * @param type $user 
 * @param type $id 
 * @param type $roleID documentation
 * @param type $email documentation
 * @param type $locale [default = null] documentation
 * @param type $active [default = null] documentation
 * @return type documentation

 **/
function setUserSession(&$db,$user, $id, $roleID, $email, $locale = null, $active = null,
                        $usertestprojectRoles = null,$userTestPlanRoles = null)
{
	tLog('setUserSession: $user=' . $user . ' $id='.$id.' $roleID='.$roleID.' $email='.$email.' $locale='.$locale);
	if (!is_null($user))
		$_SESSION['user'] = $user; 

	$_SESSION['userID']	= $id;
	$_SESSION['userdisplayname'] = $user;
	$_SESSION['email'] = $email; 
	$_SESSION['testprojectRoles'] = $usertestprojectRoles; 
	$_SESSION['testPlanRoles'] = $userTestPlanRoles; 
	$_SESSION['testprojectID'] = null;
	$_SESSION['s_lastAttachmentList'] = null;

	$user = tlUser::getById($db,$id);
	if ($user)
		$_SESSION['userdisplayname'] = $user->getDisplayName();
	
	if (!is_null($roleID))
	{
		$roleID = intval($roleID);
		$_SESSION['roleID'] = $roleID; 
		$role = new tlRole($roleID);
		if ($role->readFromDb($db) == OK)
			$_SESSION['role'] = $role->description;
		tLog('setUserSession: $user='.$_SESSION['role']);
	}
	if (!is_null($locale))
	{
		$_SESSION['locale'] = $locale;
		set_dt_formats();
	} 
		
	$tproject_mgr = new testproject($db);

	$gui_cfg = config_get('gui');
	$order_by = $gui_cfg->tprojects_combo_order_by;
	$arrProducts = $tproject_mgr->get_accessible_for_user($id,'map',$order_by);
	
	 // 20051208 - JBA - added to set the lastProduct the user has selected before logging off.
    $cookedProduct = 'lastProductForUser'. $id;
    if (isset($_COOKIE[$cookedProduct]))
	{
		if (isset($arrProducts[$_COOKIE[$cookedProduct]]) && $arrProducts[$_COOKIE[$cookedProduct]])
    	{
			$_SESSION['testprojectID'] = $_COOKIE[$cookedProduct];
    		tLog('Cookie: lastProductForUser='.$_SESSION['testprojectID']);
    	}
	}
	if (!$_SESSION['testprojectID'])
	{
    	$tpID = null;
    	if (sizeof($arrProducts))
    		$tpID = key($arrProducts);
   		$_SESSION['testprojectID'] = $tpID;
	}
	
	return 1;
}

/**
 * Function-Documentation
 *
 * @param $db [ref]
 * @param $whereClause [default = null]
 * @param $column [default = null]
 *        $column=column name of users table that will be used as key 
 *                in the returned assoc. array
 *
 *        $column=null, the returned array will be a 'classic' array
 *                
 * @param $order_by [default=null, the following order is used " ORDER BY login "]
 * 
 * @return type documentation
 *
 * 20051112 - scs - where clause was added at the wrong place
 * 20060224 - franciscom - table name user -> users
 *                       - removed role_id AS
 * 20060911 - some documentation improvements
 *
 **/
function getAllUsers(&$db,$whereClause = null,$column = null, $order_by=null)
{
	$show_realname = config_get('show_realname');
	
	$sql = " SELECT id,login,password,first,last,email,role_id,locale,".
	       " login AS fullname, active FROM users";
		     
		     
		     
	if (!is_null($whereClause))
	{
		$sql .= ' '.$whereClause;
	}
	
	$sql .= is_null($order_by) ? " ORDER BY login " : $order_by;
	
	$users = null;
	$result = $db->exec_query($sql);
	if ($result)
	{
		while($user = $db->fetch_array($result))
		{
			$user['fullname'] = format_username($user);
			if (!is_null($column))
				$users[$user[$column]] = $user;
			else 
				$users[] = $user;
		}	
	}
	
	return $users;
}

/**
 * get User Name from ID
 * @param integer $id_user
 * @return string user name
 * 
 * 20051015 - scs - added check of userId of 0
 * 20060102 - scs - refactored 
 * 20060224 - franciscom - table name user -> users
 * 20070104 - franciscom - refactoring to return unknown user 
 *                         when id_user range invalid.
 *                         Needed to cope with the situation
 *                         of modifier_id, that before any
 *                         modification is null.
 **/
function getUserName(&$db,$userID)
{
	$user = tlUser::getById($db,$userID);
	if ($user)
		return $user->getDisplayName();
	return '';
}


/**
 * Function-Documentation
 *
 * @param type $hash documentation
 * @return type documentation
 *
 **/
function format_username($hash)
{
	$show_realname  = config_get('show_realname');
	if (!$show_realname)
		return $hash['login'];
	 
	$username_format = config_get('username_format');
	
	$keys = array_keys($hash);
	$values = array_values($hash);
	for($i = 0;$i < sizeof($keys);$i++)
	{
		$keys[$i] = "%".$keys[$i]."%";
	}
	$username_format = str_replace($keys,$values,$username_format);
	
	return $username_format;
}

function get_users_for_html_options(&$db,$whereClause = null,$add_blank_option=false)
{
	$users_map = null;
	$users = getAllUsers($db,$whereClause,'id');
  
	if(!is_null($users) && $add_blank_option)
		$users_map[0] = '';
	
	foreach($users as $key => $value)
	{
		$users_map[$key] = $value['fullname'];
	}
	return($users_map);
}

function resetPassword(&$db,$userID,&$errorMsg)
{
	$errorMsg = '';
	$user = new tlUser($userID);
	$result = $user->readFromDB($db);
	if ($result == OK)
	{
		if (strlen($user->emailAddress))
		{
			$newPassword = md5(uniqid(rand(),1));
			$result = $user->setPassword($newPassword);
			if ($result == OK)
			{
				$msgBody = lang_get('your_password_is') . $newPassword . lang_get('contact_admin');  
				$mail_op = @email_send(config_get('from_email'), $user->emailAddress,  
		                       lang_get('mail_passwd_subject'), $msgBody);
				if ($mail_op->status_ok)
					$result = $user->writeToDB($db);
				else
				{
					$result = ERROR;
					$errorMsg = $mail_op->msg;
				}
			}
		}
		else
			$result = tlUser::USER_E_EMAILLENGTH;
	}
	if (!strlen($errorMsg))
		$errorMsg = getUserErrorMessage($result);
	
	return $result;
} 

function getUserErrorMessage($code)
{
	$msg = 'ok';
	switch($code)
	{
		case OK:
			break;
		case tlUser::USER_E_LOGINLENGTH:
			$msg = lang_get('error_user_login_length_error');
			break;
		case tlUser::USER_E_EMAILLENGTH:
			$msg = lang_get('empty_email_address');
			break;
		case tlUser::USER_E_NOTALLOWED:
			$msg = lang_get('user_login_valid_regex');
			break;
		case tlUser::USER_E_FIRSTNAMELENGTH:
			$msg = lang_get('empty_first_name');
			break;
		case tlUser::USER_E_LOGINALREADYEXISTS:
			$msg = lang_get('user_name_exists');
			break;
		case tlUser::USER_E_LASTNAMELENGTH:
			$msg = lang_get('empty_last_name');
			break;
		case tlUser::USER_E_PWDEMPTY:
			$msg = lang_get('warning_empty_pwd');
			break;
		case tlUser::USER_E_PWDDONTMATCH:
			$msg = lang_get('passwd_dont_match');
			break;
		case ERROR:
		case tlUser::USER_E_DBERROR:
		default:
			$msg = lang_get('error_user_not_updated');
	}
	return $msg;
}

function getAllUsersRoles(&$db,$order_by = null)
{
	$query = "SELECT users.id FROM users LEFT OUTER JOIN roles ON users.role_id = roles.id ";
	$query .= is_null($order_by) ? " ORDER BY login " : $order_by;
	
	$users = tlDBObject::createObjectsFromDBbySQL($db,$query,"id","tlUser");
	return $users;
}
?>
