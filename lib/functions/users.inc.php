<?
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: users.inc.php,v $
 *
 * @version $Revision: 1.15 $
 * @modified $Date: 2005/12/30 16:02:26 $ $Author: franciscom $
 *
 * @author Chad Rosen, Martin Havlat
 * @author Martin Havlat
 *
 * Functions for Users management
 *
 * @author Francisco Mancardi - 20051228 - added active attribute
 * @author Francisco Mancardi - 20050821 - BUGID 239
 *
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
 * 20051228 - fm - active field
 *
 * 20050528 - fm
 * 1. header docum improved
 * 2. changed function prototype (r_user_data argument)
 */
function existLogin($login, &$r_user_data)
{
global $db;

/*
to maintain compatibility  
Array
(
    [0] => [password]
    [1] => [login] 
    [2] => [id]   userid
    [3] => [rightsid] 
    [4] => [email] 
    [5] => [first] 
    [6] => [last] 
    [7] => [id]   rightsid
    [8] => [role] 
    [9] => [rights] 
    [10] => [locale] 
)  
*/
 
	// twice role to mantain array indexes
  // 20051228 - fm - added active field
	$sql = " SELECT password, login, user.id, rightsid, " .
	       "        email, first, last, " .  
	       "        role, role, rights, locale, active" .
	       " FROM user,rights " .
	       " WHERE user.rightsid = rights.id " .
	       " AND login='" . $GLOBALS['db']->prepare_string($login) . "'";
	
	$r_user_data = null;
	$userExists = 0;
	$result = do_sql_query($sql);
	if ($result)
	{
		if ($row = $GLOBALS['db']->fetch_array($result))
			$r_user_data = $row;
	}
	
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
 * @param string rights  (optional; default is GUEST)
 * @param string locale  (optional; locale for the user)
 * @param numeric active (optional; default ACTIVE_USER)
 */
function userInsert($login, $password, $first, $last, $email, 
                    $rights=GUEST, $locale = TL_DEFAULT_LOCALE, $active=ACTIVE_USER)
{
	$password = md5($password);
	$sqlInsert = "INSERT INTO user (login,password,first,last,email,rightsid,locale,active) 
	              VALUES ('" . 
				        $GLOBALS['db']->prepare_string($login) . "','" . $GLOBALS['db']->prepare_string($password) . "','" . 
				        $GLOBALS['db']->prepare_string($first) . "','" . $GLOBALS['db']->prepare_string($last) . "','" . 
				        $GLOBALS['db']->prepare_string($email) . "'," . $rights . ",'". 
				        $GLOBALS['db']->prepare_string($locale). "'," . $active . ")";
	$insertResult = do_sql_query($sqlInsert);
	
	return $insertResult ? 1 : 0;
}

/** Function pernamently delete user */ 
function userDelete($id)
{
	$sql = "DELETE FROM user WHERE id=" . $id;
	$result = do_sql_query($sql);
			
	return $result ? 'ok' : $GLOBALS['db']->error_msg();
}

/** Function get associated array of IDs with logins (used for listbox)*/
function getListOfUsers()
{
	return getTwoColumnsMap("SELECT id,login FROM user");
}

/** Function get associated array of IDs with rights (used for listbox)*/
function getListOfRights()
{
	return getTwoColumnsMap("SELECT id,role FROM rights");
}

function getTwoColumnsMap($query)
{
	$result = do_sql_query($query);
	$arrOut = null;
	if ($result)
	{
		while ($myrow = $GLOBALS['db']->fetch_array($result))
		{
			$arrOut[$myrow[0]] = $myrow[1];
		}	
	}
	
	return $arrOut;
}


function setUserPassword($userID,$password)
{
	//if we have successfully (at least we have sent the email (-;) we reset the pwd 
	$password = md5($password);
	$sql = "UPDATE user SET password = '" . $GLOBALS['db']->prepare_string($password) . "' WHERE id = ".$userID;
	$result = do_sql_query($sql); 
	
	return $result ? 1 : 0;
}

/** 
* Function update user password
* @param string user ID
* @param string old password
* @param string new password
* @return integer result 
*/
function updateUserPassword($userID, $oldPswd, $newPswd)
{
	// use md5 to encrypt the password string
	if (getUserPassword($userID) == md5($oldPswd))
		$updateResult = setUserPassword($userID,$newPswd) ? 'ok' : $GLOBALS['db']->error_msg();
	else
		$updateResult = lang_get('wrong_old_password');
	
	return $updateResult;
}

function getUserPassword($userID)
{
	//Find old password are correct
	$sql = "SELECT password FROM user WHERE id=" . $userID;
	$result = do_sql_query($sql);
	
	$pwd = null;
	if ($result)
	{
		$pwd = $GLOBALS['db']->fetch_array($result);
		$pwd = $pwd['password'];
	}
	
	return $pwd;
}

/** Function update personal data name and e-mail */
// 20051228 - fm - added active  
// 20050424 - fm added argument locale
function userUpdate($userID, $first, $last, $email ,
                    $login = null, $rightsID = null, $locale = null, $active = null)
{
 	$sql = "UPDATE user " .
	       "SET first='" . $GLOBALS['db']->prepare_string($first) . "'" .
	       ", last='" .  $GLOBALS['db']->prepare_string($last)    . "'" .
	       ", email='" . $GLOBALS['db']->prepare_string($email)   . "'";
	
	if (!is_null($login))
	{
		$sql .= ", login = '". $GLOBALS['db']->prepare_string($login) . "' ";
	}	
	if (!is_null($rightsID))
	{
		$sql .= ", rightsid = ". $rightsID ;
	}	
	if (!is_null($locale))
	{
		$sql .= ", locale = ". "'" . $GLOBALS['db']->prepare_string($locale) . "'" ;
	}
	// 20051228 - fm
	if (!is_null($active))
	{
		$sql .= ", active = ". $active;
	}	
	
	$sql .= " WHERE id=" . $userID;
	$result = do_sql_query($sql);

	// MHT 200507 - update session data if admin modify yourself
	if (($userID == $_SESSION['userID']) && $result)
	{
		setUserSession($login, $userID, $rightsID, $email, $locale);
	}
	return $result ? 'ok' : $GLOBALS['db']->error_msg();
}

/** set session data after modification or authorization */
// fm - 20051005  - set_dt_formats()
// MHT 200507 - create function: update session data if admin modify yourself
function setUserSession($user, $id, $roleID, $email, $locale = null)
{
	
	tLog('setUserSession: $user='.$user.' $id='.$id.' $roleID='.$roleID.' $email='.$email.' $locale='.$locale);

	if (!is_null($user))
	{
	  $_SESSION['user'] = $user; 
	}    
	$_SESSION['userID']	= $id;
  $_SESSION['email'] = $email; 
  
	if (!is_null($roleID))
	{
	    $_SESSION['roleId'] = intval($roleID); 
    	$sql = "SELECT role FROM rights WHERE id = " . $roleID;
	    $result = do_sql_query($sql);
	    if ($result)
		  {
	      $row = $GLOBALS['db']->fetch_array($result);
	    	$_SESSION['role'] = $row['role']; 
	    	tLog('setUserSession: $user='.$_SESSION['role']);
	    }
  }
  if (!is_null($locale))
  {
		$_SESSION['locale'] = $locale;
    set_dt_formats();
  }  
	return 1;
}


// 20051130 - fm
// BUGID 239 - TestPlan are filtered by Product ID
function deleteUsersProjectRights($userID, $prodID)
{
	$sql = " DELETE FROM projrights
	         WHERE userid = " . $userID .
	       " AND projid IN (SELECT id FROM project WHERE prodid = " . $prodID . ")";
	      
	$result = do_sql_query($sql);
	return $result ? 1 : 0;
}

function getUserById($id)
{
	return getAllUsers("where id=" . $id);
}

// 20051227 - fm
// 20051112 - scs - where clause was added at the wrong place
function getAllUsers($whereClause = null)
{
	$show_realname=config_get('show_realname');
	
	$sql = " SELECT id,login,password,first,last,email,rightsid,'' AS fullname, active 
	         FROM user";
	if (!is_null($whereClause))
	{
		$sql .= ' '.$whereClause;
	}
	
	$sql .= " ORDER BY login";
	$result = do_sql_query($sql);
	$users = null;
	if ($result)
	{
		while($user = $GLOBALS['db']->fetch_array($result))
		{
			$user['fullname'] = $user['login'];
			if($show_realname)
			{
			  $user['fullname'] = format_username($user);
			}
			$users[] = $user;
		}	
	}
	
	return($users);
}

// 20051230 - fm - active
// 20051227 - fm
function getAllUsers_assoc($whereClause = null)
{
  
	$sql = " SELECT id,login,password,first,last,email,rightsid,locale,active " .
	       " FROM user ORDER BY login ";
	
	if (!is_null($whereClause))
	{
		$sql .= $whereClause;
  }
  
	$result = do_sql_query($sql);
	$users = null;
	if ($result)
	{
		while($user = $GLOBALS['db']->fetch_array($result))
		{
			$users[] = $user;
		}	
	}
	
	return $users;
}

/*
* Check if the username is a valid username (does not account for uniqueness) 
* realname can match
* Return true if it is, false otherwise
* 
* 20051112 - scs - small cosmetic changes, added trimming, corrected wrong login 
* 				   maxlength check
*/
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
 * @author havlatm
 * 20051015 - am - added check of userId of 0
 **/
function getUserName($id_user)
{
	$username = lang_get('Unknown');
	if ($id_user)
	{
		$sql = "SELECT login, first, last FROM user WHERE id=" . $id_user;
		$result = do_sql_query($sql);
		
		if ($result && ($GLOBALS['db']->num_rows($result) > 0))
		{
			$row = $GLOBALS['db']->fetch_array($result);
			if (empty($row['first']) && empty($row['last']))
			{
				// return login (name was not defined)
				$username = $row['login'];
			}
			else
			{
				// return first + last name
				$username = $row['first'] . ' ' . $row['last'];
			}	
		}
	}
	
	tLog('username = ' . $username);
	return $username;
}


// 20051227 - fm
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



// 20051228 - fm
function user_is_active($login)
{
	$is_active=0;
	
	$sql = " SELECT active
	         FROM user
	         WHERE login='" . $GLOBALS['db']->prepare_string($login) . "'";

	$result = do_sql_query($sql);
  
	if ($result)
	{
		if ($row = $GLOBALS['db']->fetch_array($result))
		{
			$is_active = $row['active'];
		}	
	}

  return ($is_active);

}





// --- END ----------------------------------------------------

?>