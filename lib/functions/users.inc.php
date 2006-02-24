<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: users.inc.php,v $
 *
 * @version $Revision: 1.27 $
 * @modified $Date: 2006/02/24 18:13:23 $ $Author: franciscom $
 *
 * Functions for usermanagement
 *
 * 20051228 - fm - added active attribute
 * 20050821 - fm - BUGID 239
 * 20051231 - scs - changes due to ADBdb
 * 20060205 - JBA - Remember last product (BTS 221); added by MHT
 * 20060224 - franciscom - changes in session product -> testproject
**/
require_once("common.php");

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
 */
function existLogin(&$db,$login, &$r_user_data)
{
	// twice role to mantain array indexes
	// 20051228 - fm - added active field
	$sql = " SELECT password, login, user.id, role_id AS rightsid, " .
	       "        email, first, last, " .  
	       "        roles.description AS role, locale, active" .
	       " FROM users,roles " .
	       " WHERE user.role_id = roles.id " .
	       " AND login='" . $db->prepare_string($login) . "'";
	
	$r_user_data = $db->fetchFirstRow($sql);
	return $r_user_data ? 1 : 0;
}

/**
 * 20051228 - fm - active field
 *
 * 20050829 - scs - added param for locale
 * 
 * Function inserts new user to db
 * @param string login
 * @param string password
 * @param string first name
 * @param string last name
 * @param string email
 * @param string rights  (optional; default is TL_DEFAULT_ROLEID)
 * @param string locale  (optional; locale for the user)
 * @param numeric active (optional; default ACTIVE_USER)
 */
function userInsert(&$db,$login, $password, $first, $last, $email, 
                    $rights=TL_DEFAULT_ROLEID, $locale = TL_DEFAULT_LOCALE, $active=1)
{
	$password = md5($password);
	$sqlInsert = "INSERT INTO user (login,password,first,last,email,role_id,locale,active) 
	              VALUES ('" . 
				        $db->prepare_string($login) . "','" . $db->prepare_string($password) . "','" . 
				        $db->prepare_string($first) . "','" . $db->prepare_string($last) . "','" . 
				        $db->prepare_string($email) . "'," . $rights . ",'". 
				        $db->prepare_string($locale). "'," . $active . ")";
	$insertResult = $db->exec_query($sqlInsert);
	
	return $insertResult ? 1 : 0;
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
 * @param type $query documentation
 * @return type documentation
**/
function getTwoColumnsMap(&$db,$query)
{
	$result = $db->exec_query($query);
	$arrOut = null;
	if ($result)
	{
		while ($myrow = $db->fetch_array($result))
		{
			$arrOut[$myrow[0]] = $myrow[1];
		}	
	}
	
	return $arrOut;
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
	$sql = "UPDATE user SET password = '" . $db->prepare_string($password) . "' WHERE id = ".$userID;
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
 * @param type $rightsID [default = null] documentation
 * @param type $locale [default = null] documentation
 * @param type $active [default = null] documentation
 * @return type documentation
 *
 * 20051228 - fm - added active  
 * 20050424 - fm added argument locale
 **/
function userUpdate(&$db,$userID, $first, $last, $email ,
                    $login = null, $rightsID = null, $locale = null, $active = null)
{
 	$sql = "UPDATE user " .
	       "SET first='" . $db->prepare_string($first) . "'" .
	       ", last='" .  $db->prepare_string($last)    . "'" .
	       ", email='" . $db->prepare_string($email)   . "'";
	
	if (!is_null($login))
	{
		$sql .= ", login = '". $db->prepare_string($login) . "' ";
	}	
	if (!is_null($rightsID))
	{
		$sql .= ", role_id = ". $rightsID ;
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
		setUserSession($db,$login, $userID, $rightsID, $email, $locale);
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
function setUserSession(&$db,$user, $id, $roleID, $email, $locale = null,$active = null,$usertestprojectRoles = null,$userTestPlanRoles = null)
{
	tLog('setUserSession: $user='.$user.' $id='.$id.' $roleID='.$roleID.' $email='.$email.' $locale='.$locale);
	
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
function deleteUsersTestPlanRights(&$db,$userID,$prodID)
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
	if(!is_null($id) and intval($id) > 0)
	$ret = getAllUsers($db,"where id=" . $id);

	return $ret;
}

/**
 * Function-Documentation
 *
 * @param type $db [ref] documentation
 * @param type $whereClause [default = null] documentation
 * @return type documentation
 *
 * 20051112 - scs - where clause was added at the wrong place
 * 20060224 - franciscom - table name user -> users
 **/
function getAllUsers(&$db,$whereClause = null,$column = null)
{
	$show_realname = config_get('show_realname');
	
	$sql = " SELECT id,login,password,first,last,email,role_id AS rightsid,locale,".
		   " login AS fullname, active FROM users";
	if (!is_null($whereClause))
	{
		$sql .= ' '.$whereClause;
	}
	$sql .= " ORDER BY login";
	
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
 **/
function getUserName(&$db,$id_user)
{
	$username = lang_get('Unknown');
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
	
	$username_format = "name_surname_login";
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

// 20051228 - fm
//NOT USED AT THE MOMENT
/*
function user_is_active($login)
{
	$is_active=0;
	
	$sql = " SELECT active
	         FROM user
	         WHERE login='" . $db->prepare_string($login) . "'";
	
	$result = do_sql_query($sql);
  
	if ($result)
	{
		if ($row = db->fetch_array($result))
		{
			$is_active = $row['active'];
		}	
	}

  return ($is_active);

}
*/
?>