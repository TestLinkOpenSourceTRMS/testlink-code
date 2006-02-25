<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 * 
 * @filesource $RCSfile: doAuthorize.php,v $
 * @version $Revision: 1.10 $
 * @modified $Date: 2006/02/25 07:02:25 $ by $Author: franciscom $
 * @author Chad Rosen, Martin Havlat
 *
 * This file handles the initial login and creates all user session variables.
 *
 * @todo Setting up cookies so that the user can automatically login next time
 * 
 * Revision:
 * 
 * 20060224 - franciscom - role_id instead of deprecated rights id
 * 20051007 MHT Solved  0000024 Session confusion 
 *
 *///////////////////////////////////////////////////////////////////////////


require_once("users.inc.php");
require_once("roles.inc.php");


/** authorization function verifies login & password and set user session data */
//20051118 - scs - login and pwd are stripped two times, replaced POST by 
//					function params
//20060102 - scs - ADOdb changes
function doAuthorize(&$db,$login,$pwd)
{
	$bSuccess = false;
	$sProblem = 'wrong'; // default problem attribute value
	
	$_SESSION['locale'] = TL_DEFAULT_LOCALE; 

	if (!is_null($pwd) && !is_null($login))
	{
		$login_exists = existLogin($db,$login,$userInfo);
		tLog("Account exist = " . $login_exists);
		//encrypt the password so it isn't stored plain text in the db
		if ($login_exists && $userInfo['password'] == md5($pwd) && $userInfo['active'])
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
				$userProductRoles = getUserProductRoles($db,$userInfo['id']);
				$userTestPlanRoles = getUserTestPlanRoles($db,$userInfo['id']);
			    //Setting user's session information
			    // MHT 200507 move session update to function
			    setUserSession($db,$userInfo['login'], $userInfo['id'], 
			    		$userInfo['role_id'], $userInfo['email'], 
			    		$userInfo['locale'],null,$userProductRoles,$userTestPlanRoles);
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
