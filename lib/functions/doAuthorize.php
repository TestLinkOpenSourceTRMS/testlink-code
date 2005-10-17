<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 * 
 * @filesource $RCSfile: doAuthorize.php,v $
 * @version $Revision: 1.5 $
 * @modified $Date: 2005/10/17 20:11:27 $ by $Author: schlundus $
 * @author Chad Rosen, Martin Havlat
 *
 * This file handles the initial login and creates all user session variables.
 *
 * @todo Setting up cookies so that the user can automatically login next time
 * 
 * Revision:
 * 
 * 20051007 MHT Solved  0000024 Session confusion 
 *
 *///////////////////////////////////////////////////////////////////////////


require_once("users.inc.php");


/** authorization function verifies login & password and set user session data */
function doAuthorize()
{
	$bSuccess = false;
	$sProblem = 'wrong'; // default problem attribute value
	
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
			// 20051007 MHT Solved  0000024 Session confusion 
			// Disallow two session with one browser
			if (isset($_SESSION['user']) && strlen($_SESSION['user']))
			{
				$sProblem = 'sessionExists';
				tLog("Session exists. No second login is allowed", 'INFO');
			}
			else
			{
			    //Setting user's session information
			    // MHT 200507 move session update to function
			    setUserSession($userInfo['login'], $userInfo['id'], 
			    		$userInfo['rightsid'], $userInfo['email'], 
			    		$userInfo['locale']);
		    	$bSuccess = true;
			}
		}
		else
		{
			 tLog("Account ".$login." doesn't exist or used wrong password.",'INFO');
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
		redirect($_SESSION['basehref'] . "login.php?note=" . $sProblem);
	}
}
?>
