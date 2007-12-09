<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: users.inc.php,v $
 *
 * @version $Revision: 1.49 $
 * @modified $Date: 2007/12/09 12:12:03 $ $Author: schlundus $
 *
 * Functions for usermanagement
 *
 * 20050821 - fm - BUGID 239
 * 20051231 - scs - changes due to ADBdb
 * 20060205 - JBA - Remember last product (BTS 221); added by MHT
 * 20060224 - franciscom - changes in session product -> testproject
 * 20060511 - franciscom - changes in userInsert()
 * 20070104 - franciscom - changes in getUserName()
 * 20070106 - franciscom - getAllUsers() - new argument order_by
 * 20070617 - franciscom - using new config param in user_is_name_valid()
**/
require_once("common.php");

//SCHLUNDUS: not completed
class tlUser extends tlDBObject
{
	public $m_firstName;
	public $m_lastName;
	public $m_emailAddress;
	public $m_locale;
	public $m_bActive;
	public $m_defaultTestprojectID;
	public $m_globalRole;
	public $m_login;
	protected $m_password;
	
	protected $m_showRealname;
	protected $m_usernameFormat;
	
	const USER_E_LOGINLENGTH = -1;
	const USER_E_EMAILLENGTH = -2;
	const USER_E_NOTALLOWED = -4;
	const USER_E_DBERROR = -8;
	const USER_E_FIRSTNAMELENGTH = -16;
	const USER_E_LASTNAMELENGTH = -32;
	const USER_E_PWDEMPTY = -64;
	const USER_E_PWDDONTMATCH = -128;
	
	function __construct($dbID = null)
	{
		parent::__construct($dbID);
		$this->m_showRealname = config_get('show_realname');
		$this->m_usernameFormat = config_get('username_format');
		$this->m_loginRegExp = config_get('user_login_valid_regex');
		$this->m_maxLoginLength = 30; 
	}
	
	protected function _clean()
	{
		$m_firstName = null;
		$m_lastName = null;
		$m_emailAddress = null;
		$m_locale = null;
		$m_password = null;
		$m_bActive = null;
		$m_defaultTestprojectID = null;
		$m_globalRole = null;
		$m_login = null;
	}
	/* fills the members  */
	function create()
	{
	}
	//BEGIN interface iDBSerialization
	public function readFromDB(&$db)
	{
		$this->_clean();
		$query = "SELECT id,login,password,first,last,email,role_id,locale, login AS fullname, active,default_testproject_id FROM users";
		$query .= " WHERE id = {$this->m_dbID}";

		$info = $db->fetchFirstRow($query);			 
		if ($info)
		{
			$this->m_firstName = $info['first'];
			$this->m_lastName = $info['last'];
			$this->m_login = $info['login'];
			$this->m_emailAddress = $info['email'];
			$this->m_globalRole = $info['role_id'];
			$this->m_locale = $info['locale'];
			$this->m_password = $info['password'];
			$this->m_bActive = $info['active'];
			$this->m_defaultTestprojectID = $info['default_testproject_id'];
		}
		return $info ? OK : ERROR;
	}
	public function writeToDB(&$db)
	{
		$result = $this->checkUserDetails();
		if ($result == OK)
		{		
			if($this->m_dbID)
			{
				$query = "UPDATE users " .
			       "SET first='" . $db->prepare_string($this->m_firstName) . "'" .
			       ", last='" .  $db->prepare_string($this->m_lastName)    . "'" .
			       ", email='" . $db->prepare_string($this->m_emailAddress)   . "'" .
				   ", locale = ". "'" . $db->prepare_string($this->m_locale) . "'" . 
				   ", password = ". "'" . $db->prepare_string($this->m_password) . "'" ;
				$query .= " WHERE id=" . $this->m_dbID;
				$result = $db->exec_query($query);
			}
			 $result = $result ? OK : self::USER_E_DBERROR;
		}
		return $result;
	}	
	public function deleteFromDB(&$db)
	{
		$query = "DELETE FROM users WHERE id=" . $this->m_dbID;
		$result = $db->exec_query($query);
			
		return $result ? OK : ERRROR;
	}
	
	public function getDisplayName()
	{
		if (!$this->m_showRealname)
			return $this->m_login;

		$info = null;
		$keys = array('%first%','%last%','%login%','%email%');
		$values = array($this->m_firstName, $this->m_lastName,$this->m_login,$this->m_email);
		
		$displayName = str_replace($keys,$values,$this->m_usernameFormat);
	
		return $displayName;
	}
	protected function encryptPassword($pwd)
	{
		return md5($pwd);
	}
	public function setPassword($pwd)
	{
		if (!strlen($pwd))
			return self::USER_E_PWDEMPTY;
		$this->m_password = $this->encryptPassword($pwd);
		return OK;
	}
	public function getPassword()
	{
		return $this->m_password;
	}
	
	public function comparePassword($pwd)
	{
		if ($this->getPassword($pwd) == $this->encryptPassword($pwd))
			return OK;
		return self::USER_E_PWDDONTMATCH;		
	}
	
	protected function checkUserDetails()
	{
		$result = $this->checkEmailAdress($this->m_emailAddress);
		if ($result == OK)
			$result = $this->checkLogin($this->m_login);
		if ($result == OK)
			$result = $this->checkFirstName($this->m_firstName);
		if ($result == OK)
			$result = $this->checkLastName($this->m_lastName);
		return $result;
	}
	public function checkLogin($login)
	{
		$result = OK;
		$login = trim($login);
		//simple check for empty login, or login consisting only of whitespaces
		//The DB field is only 30 characters
		if (!strlen($login) || (strlen($login) > $this->m_maxLoginLength))
			$result = self::USER_E_LOGINLENGTH;

		//Only allow a basic set of characters
		if (!preg_match($this->m_loginRegExp,$login))
			$result = self::USER_E_NOTALLOWED;

		return $result;
	}
	
	public function checkEmailAdress($email)
	{
		$result = OK;
		$email = trim($email);
		if (!strlen($email))
			$result = self::USER_E_EMAILLENGTH;
			
		return $result;
	}
	public function checkFirstName($first)
	{
		$result = OK;
		$first = trim($first);
		if (!strlen($first))
			$result = self::USER_E_FIRSTNAMELENGTH;
			
		return $result;
	}
	public function checkLastName($last)
	{
		$result = OK;
		$last = trim($last);
		if (!strlen($last))
			$result = self::USER_E_LASTNAMELENGTH;
			
		return $result;
	}
}

if( 'LDAP' == config_get('login_method') )
{
  require_once(dirname(__FILE__) . "/ldap_api.php");
}

/**
 * Function verifies if login exists
 * @param string login name
 * @param array (passed by reference), to return the user record data
 *        
 *    
 * @return 0: account doesn't exist
 *         1: account exists
 *
 * 20060224 - franciscom - table name user -> users
 * 20051228 - fm - active field
 *
 * 20050528 - fm
 * 1. header docum improved
 * 2. changed function prototype (r_user_data argument)
 * 20060102 - scs - refactored
 * 20060224 - franciscom - removed role_id AS
 */
function existLogin(&$db,$login, &$r_user_data)
{
  
	$sql = " SELECT password, login, users.id, role_id, " .
	       "        email, first, last, " .  
	       "        roles.description AS role, locale, active" .
	       " FROM users,roles " .
	       " WHERE users.role_id = roles.id " .
	       " AND login='" . $db->prepare_string($login) . "'";
	
	$r_user_data = $db->fetchFirstRow($sql);
	return $r_user_data ? 1 : 0;
}

/**
 * 
 * Function inserts new user to db
 * @param string login
 * @param string password
 * @param string first name
 * @param string last name
 * @param string email
 * @param string role_id  (optional; default is TL_DEFAULT_ROLEID)
 * @param string locale  (optional; locale for the user)
 * @param numeric active (optional; default ACTIVE_USER)
 *
 *
 * return: 
 *        if insert OK -> user id
 *                  KO -> 0
 *
 * 20060511 - franciscom - changed the returns value
 *                         
 *
 * 20051228 - franciscom - active field
 * 20050829 - scs - added param for locale
 *  
 */
function userInsert(&$db,$login, $password, $first, $last, $email, 
                    $role_id = TL_DEFAULT_ROLEID, $locale = TL_DEFAULT_LOCALE, $active = 1)
{
	$password = md5($password);
	$sql= "INSERT INTO users (login,password,first,last,email,role_id,locale,active) 
	       VALUES ('" . 
			   $db->prepare_string($login) . "','" . $db->prepare_string($password) . "','" . 
			   $db->prepare_string($first) . "','" . $db->prepare_string($last) . "','" . 
			   $db->prepare_string($email) . "'," . $role_id . ",'". 
			   $db->prepare_string($locale). "'," . $active . ")";
	$result = $db->exec_query($sql);
	
	$new_user_id = 0;
	if($result)
	{
		$new_user_id = $db->insert_id('users');
	}
	
	return $new_user_id;
}

/**
 * Deletes a user
 *
 * @param type $db [ref] documentation
 * @param type $id documentation
 * @return type documentation
 * 20060224 - franciscom - table name user -> users
 **/
function userDelete(&$db,$id)
{
	$sql = "DELETE FROM users WHERE id=" . $id;
	$result = $db->exec_query($sql);
			
	return $result ? 'ok' : $db->error_msg();
}




/**
 * Function-Documentation
 *
 * @param type $db [ref] documentation
 * @param type $userID documentation
 * @param type $password documentation
 * @return type documentation
 **/
function setUserPassword(&$db,$userID,$password)
{
	$password = md5($password);
	$sql = "UPDATE users SET password = '" . $db->prepare_string($password) . "' WHERE id = ".$userID;
	$result = $db->exec_query($sql); 
	
	return $result ? 1 : 0;
}

/** 
* Function update user password
* @param string user ID
* @param string old password
* @param string new password
* @return integer result 
*/
function updateUserPassword(&$db,$userID, $oldPswd, $newPswd)
{
	// use md5 to encrypt the password string
	if (getUserPassword($db,$userID) == md5($oldPswd))
		$updateResult = setUserPassword($db,$userID,$newPswd) ? 'ok' : $db->error_msg();
	else
		$updateResult = lang_get('wrong_old_password');
	
	return $updateResult;
}

/**
 * Function-Documentation
 *
 * @param type $db [ref] documentation
 * @param type $userID documentation
 * @return type documentation
 *
 * 20060224 - franciscom - table name user -> users
 **/
function getUserPassword(&$db,$userID)
{
	$sql = "SELECT password FROM users WHERE id=" . $userID;
	$pwd = $db->fetchFirstRowSingleColumn($sql,"password");
	
	return $pwd;
}

/**
 * Function update personal data
 *
 * @param type $db [ref] documentation
 * @param type $userID documentation
 * @param type $first documentation
 * @param type $last documentation
 * @param type $email documentation
 * @param type $login [default = null] documentation
 * @param type $role_id [default = null] documentation
 * @param type $locale [default = null] documentation
 * @param type $active [default = null] documentation
 * @return type documentation
 *
 * 20051228 - fm - added active  
 * 20050424 - fm added argument locale
 **/
function userUpdate(&$db,$userID, $first, $last, $email ,
                    $login = null, $role_id = null, $locale = null, $active = null)
{
 	$sql = "UPDATE users " .
	       "SET first='" . $db->prepare_string($first) . "'" .
	       ", last='" .  $db->prepare_string($last)    . "'" .
	       ", email='" . $db->prepare_string($email)   . "'";
	
	if (!is_null($login))
	{
		$sql .= ", login = '". $db->prepare_string($login) . "' ";
	}	
	if (!is_null($role_id))
	{
		$sql .= ", role_id = ". $role_id ;
	}	
	if (!is_null($locale))
	{
		$sql .= ", locale = ". "'" . $db->prepare_string($locale) . "'" ;
	}
	if (!is_null($active))
	{
		$sql .= ", active = ". $active;
	}	
	
	$sql .= " WHERE id=" . $userID;
	$result = $db->exec_query($sql);

	// MHT 200507 - update session data if admin modify yourself
	if (($userID == $_SESSION['userID']) && $result)
	{
		setUserSession($db,$login, $userID, $role_id, $email, $locale);
	}
	return $result ? 'ok' : $db->error_msg();
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
 *
 * 20051005 - fm - set_dt_formats()
 * 20050701 - create function: update session data if admin modify yourself
 * 20060102 - scs - ADOdb changes
 **/
function setUserSession(&$db,$user, $id, $roleID, $email, $locale = null, $active = null,
                        $usertestprojectRoles = null,$userTestPlanRoles = null)
{
	tLog('setUserSession: $user=' . $user . ' $id='.$id.' $roleID='.$roleID.' $email='.$email.' $locale='.$locale);
	if (!is_null($user))
		$_SESSION['user'] = $user; 

	$_SESSION['userID']	= $id;
	
	$uInfo = getUserById($db,$id);
	$_SESSION['userdisplayname'] = $user;
	if ($uInfo)
		$_SESSION['userdisplayname'] = $uInfo[0]['fullname'];
	
	
	$_SESSION['email'] = $email; 
	
	if (!is_null($roleID))
	{
		$_SESSION['roleID'] = intval($roleID); 
		$sql = "SELECT description FROM roles WHERE id = " . $roleID;
		$result = $db->exec_query($sql);
		if ($result)
		{
			$row = $db->fetch_array($result);
			$_SESSION['role'] = $row['description']; 
			tLog('setUserSession: $user='.$_SESSION['role']);
		}
	}
	if (!is_null($locale))
	{
		$_SESSION['locale'] = $locale;
		set_dt_formats();
	} 
	
	$_SESSION['testprojectRoles'] = $usertestprojectRoles; 
	$_SESSION['testPlanRoles'] = $userTestPlanRoles; 
	$_SESSION['testprojectID'] = null;
	
	// 20071103 - franciscom
  $tproject_mgr = new testproject($db);

  $gui_cfg=config_get('gui');
  $order_by=$gui_cfg->tprojects_combo_order_by;
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
	$_SESSION['s_lastAttachmentList'] = null;
	
	return 1;
}

/**
 * Function-Documentation
 *
 * @param  type $db [ref] ADODB
 * @param  type $id user_id
 * @return null or assoc array with user data
 **/
function getUserById(&$db,$id)
{
	$ret = null;
	if(!is_null($id) && intval($id) > 0)
	$ret = getAllUsers($db,"WHERE id=" . $id);

	return $ret;
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
* Check if the username is a valid username (does not account for uniqueness) 
* realname can match
* Return true if it is, false otherwise
 *
 * @param type $p_username documentation
 * @return type documentation
 *
 * 20051112 - scs - small cosmetic changes, added trimming, corrected wrong login 
 * 				          maxlength check
 **/
function user_is_name_valid($p_username)
{
  $user_login_valid_regex=config_get('user_login_valid_regex');
 	$user_ok = true;
	
	$p_username = trim($p_username);
	//simple check for empty login, or login consisting only of whitespaces
	//The DB field is only 30 characters
	if (!strlen($p_username) || (strlen($p_username) > 30))
	{
		$user_ok = false;
	}

	# Only allow a basic set of characters
	if (!preg_match($user_login_valid_regex, $p_username))
	{
		$user_ok = false;
	}

	return $user_ok;
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
function getUserName(&$db,$id_user)
{
	$username = '';
	if(intval($id_user) > 0 )
	{
		$username = lang_get('Unknown');
		$uInfo = getUserById($db,$id_user);
		if ($uInfo) 
			$username = $uInfo[0]['fullname'];
	}
	return $username;
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

function checkLogin(&$db,$login)
{
	$message = lang_get("login_must_not_be_empty");
	if (strlen($login))
	{
		if (user_is_name_valid($login))
		{
			$userInfo = null;
			if (existLogin($db,$login,$userInfo))
				$message = lang_get('duplicate_login');
			else
				$message = 'ok';
		}
		else
			$message = lang_get('invalid_user_name') . "\n" . lang_get('valid_user_name_format');
	}		
	return $message;
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


/*
  function: 

  args :
  
  returns: 

*/
function get_all_users_roles(&$db,$order_by=null)
{
	$sql = " SELECT users.id,login,password,first,last,email, " .
	       "        roles.description AS role_description,locale,".
	       " login AS fullname, active " .
	       " FROM users LEFT OUTER JOIN roles ON users.role_id=roles.id ";
	    
	$sql .= is_null($order_by) ? " ORDER BY login " : $order_by;
	
	$users = null;
	$result = $db->exec_query($sql);
	if ($result)
	{
		while($user = $db->fetch_array($result))
		{
			$user['fullname'] = format_username($user);
	  		$users[] = $user;
		}	
	}
	
	return $users;
}


/*
  function: 

  args :
  
  returns: 

*/
function reset_password(&$db,$user_id)
{

$newPassword = md5(uniqid(rand(),1));

$user = getUserById($db,$user_id);
$email=$user[0]['email'];

$msgBody = lang_get('your_password_is') . $newPassword .  lang_get('contact_admin');  
$mail_op = @email_send(config_get('from_email'), $email,  
                       lang_get('mail_passwd_subject'), $msgBody);

if ($mail_op->status_ok)
{
	setUserPassword($db,$user_id,$newPassword);
}
return($mail_op);
} 

function getUserErrorMessage($code)
{
	$msg = 'ok';
	switch($code)
	{
		case tlUser::USER_E_LOGINLENGTH:
			$msg = lang_get('error_user_login_length_error');
			break;
		case tlUser::USER_E_EMAILLENGTH:
			$msg = lang_get('empty_email_address');
			break;
		case tlUser::USER_E_NOTALLOWED:
			$msg = lang_get('user_login_valid_regex');
			break;
		case ERROR:
		case tlUser::USER_E_DBERROR:
			$msg = lang_get('error_user_not_updated');
			break;
		case tlUser::USER_E_FIRSTNAMELENGTH:
			$msg = lang_get('empty_first_name');
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
		default:
			
	}
}
?>