<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: users.inc.php,v $
 *
 * @version $Revision: 1.54 $
 * @modified $Date: 2007/12/18 20:47:19 $ $Author: schlundus $
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
	public $m_firstName;
	public $m_lastName;
	public $m_emailAddress;
	public $m_locale;
	public $m_bActive;
	public $m_defaultTestprojectID;
	public $m_globalRole;
	public $m_globalRoleID;
	public $m_login;
	protected $m_password;
	
	protected $m_showRealname;
	protected $m_usernameFormat;
	protected $m_loginMethod;
	protected $m_maxLoginLength;
	
	const USER_E_LOGINLENGTH = -1;
	const USER_E_EMAILLENGTH = -2;
	const USER_E_NOTALLOWED = -4;
	const USER_E_DBERROR = -8;
	const USER_E_FIRSTNAMELENGTH = -16;
	const USER_E_LASTNAMELENGTH = -32;
	const USER_E_PWDEMPTY = -64;
	const USER_E_PWDDONTMATCH = -128;
	const USER_E_LOGINALREADYEXISTS = -256;
	
	function __construct($dbID = null)
	{
		parent::__construct($dbID);
		
		$this->m_showRealname = config_get('show_realname');
		$this->m_usernameFormat = config_get('username_format');
		$this->m_loginRegExp = config_get('user_login_valid_regex');
		$this->m_maxLoginLength = 30; 
		$this->m_loginMethod = config_get('login_method');
		
		$this->m_globalRoleID = TL_DEFAULT_ROLEID;
		$this->m_locale =  TL_DEFAULT_LOCALE;
		$this->m_bActive = 1;
	}
	
	protected function _clean()
	{
		$this->m_firstName = null;
		$this->m_lastName = null;
		$this->m_emailAddress = null;
		$this->m_locale = null;
		$this->m_password = null;
		$this->m_bActive = null;
		$this->m_defaultTestprojectID = null;
		$this->m_globalRoleID = null;
		$this->m_login = null;
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
			$this->m_globalRoleID = $info['role_id'];
			
			if ($this->m_globalRoleID)
			{
				$this->m_globalRole = new tlRole($this->m_globalRoleID);
				$this->m_globalRole->readFromDB($db);
			}
			
			$this->m_locale = $info['locale'];
			$this->m_password = $info['password'];
			$this->m_bActive = $info['active'];
			$this->m_defaultTestprojectID = $info['default_testproject_id'];
		}
		return $info ? OK : ERROR;
	}
	public function writeToDB(&$db)
	{
		$result = $this->checkDetails($db);
		if ($result == OK)
		{		
			if($this->m_dbID)
			{
				$query = "UPDATE users " .
			       "SET first='" . $db->prepare_string($this->m_firstName) . "'" .
			       ", last='" .  $db->prepare_string($this->m_lastName)    . "'" .
			       ", email='" . $db->prepare_string($this->m_emailAddress)   . "'" .
				   ", locale = ". "'" . $db->prepare_string($this->m_locale) . "'" . 
				   ", password = ". "'" . $db->prepare_string($this->m_password) . "'" .
				   ", role_id = ". "'" . $db->prepare_string($this->m_globalRoleID) . "'" .
				   ", active = ". "'" . $db->prepare_string($this->m_bActive) . "'" ;
				$query .= " WHERE id=" . $this->m_dbID;
				$result = $db->exec_query($query);
			}
			else
			{
				$query = "INSERT INTO users (login,password,first,last,email,role_id,locale,active) 
							VALUES ('" . 
							$db->prepare_string($this->m_login) . "','" . $db->prepare_string($this->m_password) . "','" . 
							$db->prepare_string($this->m_firstName) . "','" . $db->prepare_string($this->m_lastName) . "','" . 
							$db->prepare_string($this->m_emailAddress) . "'," . $this->m_globalRoleID. ",'". 
							$db->prepare_string($this->m_locale). "'," . $this->m_bActive . ")";
				$result = $db->exec_query($query);
				if($result)
					$this->m_dbID = $db->insert_id('users');
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
		if ($this->m_loginMethod == 'MD5' && !strlen($pwd))
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
	
	public function checkDetails(&$db)
	{
		$this->m_firstName = trim($this->m_firstName);
		$this->m_lastName = trim($this->m_lastName);
		$this->m_emailAddress = trim($this->m_emailAddress);
		$this->m_locale = trim($this->m_locale);
		$this->m_bActive = intval($this->m_bActive);
		$this->m_login = trim($this->m_login);
	
		$result = tlUser::checkEmailAdress($this->m_emailAddress);
		if ($result == OK)
			$result = $this->checkLogin($this->m_login);
		if ($result == OK && !$this->m_dbID)
			$result = tlUser::doesUserExist($db,$this->m_login) ? self::USER_E_LOGINALREADYEXISTS : OK;
		if ($result == OK)
			$result = tlUser::checkFirstName($this->m_firstName);
		if ($result == OK)
			$result = tlUser::checkLastName($this->m_lastName);
			
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
		else if (!preg_match($this->m_loginRegExp,$login)) //Only allow a basic set of characters
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
		$query = " SELECT id FROM users WHERE login='" . $db->prepare_string($login) . "'";
	
		$id = $db->fetchFirstRowSingleColumn($query,'id');
		return $id;
	}
}

class tlRole extends tlDBObject
{
	public $m_description;
	public $m_notes;
	
	function __construct($dbID = null)
	{
		parent::__construct($dbID);
	}
	protected function _clean()
	{
		$this->m_description = null;
		$this->m_notes = null;
	}
	//BEGIN interface iDBSerialization
	public function readFromDB(&$db)
	{
		$this->_clean();
		$query = "SELECT id,description, notes FROM roles";
		$query .= " WHERE id = {$this->m_dbID}";
		
		$info = $db->fetchFirstRow($query);			 
		if ($info)
		{
			$this->m_description = $info['description'];
			$this->m_notes = $info['notes'];
		}
		return $info ? OK : ERROR;
	}
	public function writeToDB(&$db)
	{
	
	}
	public function deleteFromDB(&$db)
	{
	
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
		if (strlen($user->m_emailAddress))
		{
			$newPassword = md5(uniqid(rand(),1));
			$result = $user->setPassword($newPassword);
			if ($result == OK)
			{
				$msgBody = lang_get('your_password_is') . $newPassword . lang_get('contact_admin');  
				$mail_op = @email_send(config_get('from_email'), $user->m_emailAddress,  
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