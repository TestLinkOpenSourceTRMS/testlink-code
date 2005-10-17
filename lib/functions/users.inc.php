<?
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: users.inc.php,v $
 *
 * @version $Revision: 1.9 $
 * @modified $Date: 2005/10/17 20:11:27 $
 *
 * @author Chad Rosen, Martin Havlat
 * @author Martin Havlat
 *
 * Functions for Users management
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
 * 20050528 - fm
 * 1. header docum improved
 * 2. changed function prototype (r_user_data argument)
 */
function existLogin($login, &$r_user_data)
{
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
	$sql = " SELECT password, login, user.id, rightsid, " .
	       "        email, first, last, " .  
	       "        role, role, rights, locale" .
	       " FROM user,rights " .
	       " WHERE user.rightsid = rights.id " .
	       " AND login='" . mysql_escape_string($login) . "'";
	
	$r_user_data = null;
	$userExists = 0;
	$result = do_mysql_query($sql);
	if ($result)
	{
		if ($row = mysql_fetch_array($result))
			$r_user_data = $row;
	}
	
	return $r_user_data ? 1 : 0;
}

/**
 * 20050829 - scs - added param for locale
 * 
 * Function inserts new user to db
 * @param string login
 * @param string password
 * @param string first name
 * @param string last name
 * @param string email
 * @param string rights (optional; default is guest)
 * @param string locale (optional; locale for the user)
 */
function userInsert($login, $password, $first, $last, $email, $rights = 5,$locale = TL_DEFAULT_LOCALE)
{
	$password = md5($password);
	$sqlInsert = "INSERT INTO user (login,password,first,last,email,rightsid,locale) VALUES ('" . 
				mysql_escape_string($login) . "','" . mysql_escape_string($password) . "','" . mysql_escape_string($first) . "','" . mysql_escape_string($last) .
				 "','" . mysql_escape_string($email) . "'," . $rights . ",'".mysql_escape_string($locale)."')";
	$insertResult = do_mysql_query($sqlInsert);
	
	return $insertResult ? 1 : 0;
}

/** Function pernamently delete user */ 
function userDelete($id)
{
	$sql = "DELETE FROM user WHERE id=" . $id;
	$result = do_mysql_query($sql);
			
	return $result ? 'ok' : mysql_error();
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
	$result = do_mysql_query($query);
	$arrOut = null;
	if ($result)
	{
		while ($myrow = mysql_fetch_row($result))
			$arrOut[$myrow[0]] = $myrow[1];
	}
	
	return $arrOut;
}

function setUserPassword($userID,$password)
{
	//if we have successfully (at least we have sent the email (-;) we reset the pwd 
	$password = md5($password);
	$sql = "UPDATE user SET password = '" . mysql_escape_string($password) . "' WHERE id = ".$userID;
	$result = do_mysql_query($sql); 
	
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
		$updateResult = setUserPassword($userID,$newPswd) ? 'ok' : mysql_error();
	else
		$updateResult = 'Wrong old password!';
	
	return $updateResult;
}

function getUserPassword($userID)
{
	//Find old password are correct
	$sql = "SELECT password FROM user WHERE id=" . $userID;
	$result = do_mysql_query($sql);
	
	$pwd = null;
	if ($result)
	{
		$pwd = mysql_fetch_assoc($result);
		$pwd = $pwd['password'];
	}
	
	return $pwd;
}

/** Function update personal data name and e-mail */
//      20050424 - fm added argument locale
function userUpdate($userID, $first, $last, $email ,
                    $login = null, $rightsID = null, $locale = null)
{
 	$sql = "UPDATE user " .
	       "SET first='" . mysql_escape_string($first) . "'" .
	       ", last='" .  mysql_escape_string($last)    . "'" .
	       ", email='" . mysql_escape_string($email)   . "'";
	
	if (!is_null($login))
		$sql .= ", login = '". mysql_escape_string($login) . "' ";
	if (!is_null($rightsID))
		$sql .= ", rightsid = ". $rightsID ;
	if (!is_null($locale))
		$sql .= ", locale = ". "'" . mysql_escape_string($locale) . "'" ;
		
	$sql .= " WHERE id=" . $userID;
	$result = do_mysql_query($sql);

	// MHT 200507 - update session data if admin modify yourself
	if (($userID == $_SESSION['userID']) && $result)
		setUserSession($login, $userID, $rightsID, $email, $locale);
	
	return $result ? 'ok' : mysql_error();
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
	    $result = do_mysql_query($sql);
	    if ($result)
		{
	    	$_SESSION['role'] = mysql_result($result, 0, 'role'); 
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

function deleteUsersProjectRights($id)
{
	$sql = "DELETE FROM projrights WHERE userid = " . $id;
	$result = do_mysql_query($sql);
	
	return $result ? 1 : 0;
}

function getUserById($id,&$users)
{
	return getAllUsers($users,"where id=" . $id);
}

function getAllUsers(&$users,$whereClause = null)
{
	$sql = "SELECT id,login,password,first,last,email,rightsid FROM user ORDER BY login";
	if (!is_null($whereClause))
		$sql .= $whereClause;

	$result = do_mysql_query($sql);
	$users = null;
	if ($result)
	{
		while($user = mysql_fetch_array($result))
		{
			$users[] = $user;
		}	
	}
	
	return $result ? 1 : 0;
}

function getAllUsers_assoc(&$users,$whereClause = null)
{
  
	$sql = "SELECT id,login,password,first,last,email,rightsid,locale " .
	       "FROM user ORDER BY login";
	
	if (!is_null($whereClause))
		$sql .= $whereClause;
  
	$result = do_mysql_query($sql);
	$users = null;
	if ($result)
	{
		while($user = mysql_fetch_array($result))
		{
			$users[] = $user;
		}	
	}
	
	return $result ? 1 : 0;
}

# Check if the username is a valid username (does not account for uniqueness) 
#  realname can match
# Return true if it is, false otherwise
function user_is_name_valid( $p_username ) {
  
	$user_ok = true;
  
	# The regular expression to use when validating new user login names
	# The default regular expression allows a-z, A-z, 0-9, as well as space and
	#  underscore.  If you change this, you may want to update the
	#  ERROR_USER_NAME_INVALID string in the language files to explain
	#  the rules you are using on your site
	$user_login_valid_regex = '/^[\w \-]+$/';

	# The DB field is only 32 characters
	if ( strlen( $p_username ) > 32 ) {
		$user_ok = false;
	}

	# Only allow a basic set of characters
	if ( 0 == preg_match( $user_login_valid_regex, $p_username ) )
		$user_ok = false;

	if (strlen($p_username) == 0)
	  $user_ok = false;

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
		$result = do_mysql_query($sql);
		
		if ($result && (mysql_num_rows($result) > 0))
		{
			$row = mysql_fetch_array($result);
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









// --- END ----------------------------------------------------

?>