<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: users.inc.php,v $
 *
 * @version $Revision: 1.38 $
 * @modified $Date: 2007/01/10 10:41:47 $ $Author: franciscom $
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
**/
require_once("common.php");

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
                    $role_id=TL_DEFAULT_ROLEID, $locale = TL_DEFAULT_LOCALE, $active=1)
{
	$password = md5($password);
	$sql= "INSERT INTO users (login,password,first,last,email,role_id,locale,active) 
	       VALUES ('" . 
			   $db->prepare_string($login) . "','" . $db->prepare_string($password) . "','" . 
			   $db->prepare_string($first) . "','" . $db->prepare_string($last) . "','" . 
			   $db->prepare_string($email) . "'," . $role_id . ",'". 
			   $db->prepare_string($locale). "'," . $active . ")";
	$result = $db->exec_query($sql);
	
	// 20060511 - franciscom
	$new_user_id=0;
	if( $result )
	{

	  $new_user_id=$db->insert_id('users');
	}
	
	return($new_user_id);
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
 * @param type $user documentation
 * @param type $id documentation
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
	$_SESSION['email'] = $email; 
	
	if (!is_null($roleID))
	{
		$_SESSION['roleId'] = intval($roleID); 
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
	
	// 20051208 - JBA - added to set the lastProduct the user has selected before logging off.
    $cookedProduct = 'lastProductForUser'. $id;
    if (isset($_COOKIE[$cookedProduct])) {
    	$_SESSION['testprojectID'] = $_COOKIE[$cookedProduct];
    	tLog('Cookie: lastProductForUser='.$_SESSION['testprojectID']);
    }
	$_SESSION['s_lastAttachmentList'] = null;
	
	return 1;
}


/**
 * Function-Documentation
 *
 * @param type $db [ref] documentation
 * @param type $userID documentation
 * @param type $prodID documentation
 * @return type documentation
 *
 * BUGID 239 - TestPlan are filtered by Product ID
 * 20060102 - scs - ADOdb changes
 **/
function DEPRECATED_deleteUsersTestPlanRights(&$db,$userID,$prodID)
{
	$sql = " DELETE FROM testplans_rights
	         WHERE userid = " . $userID .
	       " AND projid IN (SELECT id FROM testplans WHERE prodid = " . $prodID . ")";
	      
	$result = $db->exec_query($sql);
	return $result ? 1 : 0;
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
			if($show_realname)
			{
				$user['fullname'] = format_username($user);
			}	
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
 * 				   maxlength check
 **/
function user_is_name_valid($p_username)
{
 	$user_ok = true;
	
	$p_username = trim($p_username);
	//simple check for empty login, or login consisting only of whitespaces
	//The DB field is only 30 characters
	if (!strlen($p_username) || (strlen($p_username) > 30))
	{
		$user_ok = false;
	}
    # The regular expression to use when validating new user login names
	# The default regular expression allows a-z, A-z, 0-9, as well as space and
	#  underscore.  If you change this, you may want to update the
	#  ERROR_USER_NAME_INVALID string in the language files to explain
	#  the rules you are using on your site
	$user_login_valid_regex = '/^[\w \-]+$/';
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
  }
  
	if ($id_user)
	{
		$sql = "SELECT login, first, last FROM users WHERE id=" . $id_user;
		$row = $db->fetchFirstRow($sql); 
		$username = format_username($row);
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
	$username_format = config_get('username_format');
	$username = $hash['first'] . " " . $hash['last'];
	
	switch($username_format)
	{
		case "name_surname_login":
			$username .= " [" . $hash['login'] . "]";
			break;	
		case "name_surname":
			default:
			break;	
	}
	
	return $username;
}

function checkLogin(&$db,$login)
{
	$sqlResult = lang_get("login_must_not_be_empty");
	if (strlen($login))
	{
		if (user_is_name_valid($login))
		{
			$userInfo = null;
			if (existLogin($db,$login,$userInfo))
				$sqlResult = lang_get('duplicate_login');
			else
				$sqlResult = 'ok';
		}
		else
			$sqlResult = $message = lang_get('invalid_user_name') . "\n" . lang_get('valid_user_name_format');
	}		
	return $sqlResult;
}


function get_users_for_html_options(&$db,$whereClause = null,$add_blank_option=false)
{
	global $g_show_realname;
	
	$users_map = null;
	$users = getAllUsers($db,$whereClause,'id');
  
	if(!is_null($users) && $add_blank_option)
		$users_map[0] = '';
	
	foreach($users as $key => $value)
	{
		if ($g_show_realname)
			$label = $value['fullname'];
		else
			$label = $value['login'];
		$users_map[$key] = $label;
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
	$show_realname = config_get('show_realname');
	
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
			if($show_realname)
			{
				$user['fullname'] = format_username($user);
			}	
  		$users[] = $user;
		}	
	}
	
	return $users;
}



?>