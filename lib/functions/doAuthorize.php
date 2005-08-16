<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * 
 * @filesource $RCSfile: doAuthorize.php,v $
 * @version $Revision: 1.2 $
 * @modified $Date: 2005/08/16 18:00:55 $ by $Author: franciscom $
 * @author Chad Rosen, Martin Havlat
 *
 * This file handles the initial login and creates all user session variables.
 *
 * @todo Setting up cookies so that the user can automatically login next time
 *
 *///////////////////////////////////////////////////////////////////////////
 
require_once("users.inc.php");


/** authorization function verifies login & password and set user session data */
function doAuthorize()
{
	tlTimingStart();
	$bSuccess = false;
	
	$pwd = isset($_POST['password']) ? strings_stripSlashes($_POST['password']) : null;
	$login = isset($_POST['login']) ? strings_stripSlashes($_POST['login']) : null;

  // 20050416 - fm
	$_SESSION['locale'] = TL_DEFAULT_LOCALE; 

	if (!is_null($pwd) && !is_null($login))
	{
		 $login_exists = existLogin($login,$userInfo);
		 tLog("Account exist = " . $login_exists);

		//encrypt the password so it isn't stored plain text in the db
		if ($login_exists && $userInfo['password'] == md5($pwd))
		{
		    //Setting user's session information
		    // MHT 200507 move session update to function
		    setUserSession($userInfo['login'], $userInfo['id'], $userInfo['rightsid'], 
		    		$userInfo['email'], $userInfo['locale']);
		    $bSuccess = true;
		}
	}
	if ($bSuccess)
	{
	    tLog("Login ok. (Timing: " . tlTimingCurrent() . ')', 'INFO');
	    //forwarding user to the mainpage
	    redirect($_SESSION['basehref'] ."index.php");
	}
	else
	{
		// not authorized
	    tLog("Login '$login' fails. (Timing: " . tlTimingCurrent() . ')', 'INFO');
		redirect($_SESSION['basehref'] ."login.php?note=wrong");
	}
}
?>
