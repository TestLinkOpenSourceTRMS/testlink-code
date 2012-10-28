<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Functions for usermanagement
 * 
 * @filesource	users.inc.php
 * @package 	TestLink
 * @author 		Martin Havlat
 * @copyright 	2006-2011, TestLink community 
 * @link 		http://www.teamst.org/index.php
 *
 * @internal revisions
 */

/** core functions */
require_once("common.php");

$authCfg = config_get('authentication');
if( 'LDAP' == $authCfg['method'] )
{
	/** support for LDAP authentication */
	require_once("ldap_api.php");
}

/**
 * set session data after modification or authorization
 *
 * @param resource &$db reference to DB identifier
 * @param string $user
 * @param integer $id
 * @param integer $roleID 
 * @param string $email 
 * @param string $locale [default = null]
 * @param boolean $active [default = null] documentation
 * 
 * @return integer status code
 * 
 **/
function XXXsetUserSession(&$db,$user, $id, $roleID, $email, $locale = null, $active = null)
{
	tLog('setUserSession: $user=' . $user . ' $id='.$id.' $roleID='.$roleID.' $email='.$email.' $locale='.$locale);

	$_SESSION['userID']	= $id;
	$_SESSION['testprojectID'] = null;
	$_SESSION['s_lastAttachmentList'] = null;

	if (!is_null($locale))
	{
		$_SESSION['locale'] = $locale;
		set_dt_formats();
	}

	$tproject_mgr = new testproject($db);
	$arrProducts = $tproject_mgr->get_accessible_for_user($id,'map');
	$tproject_cookie = 'TL_lastTestProjectForUserID_'. $id;
	if (isset($_COOKIE[$tproject_cookie]))
	{
		if (isset($arrProducts[$_COOKIE[$tproject_cookie]]) && $arrProducts[$_COOKIE[$tproject_cookie]])
      {
		  	  $_SESSION['testprojectID'] = $_COOKIE[$tproject_cookie];
      		tLog('Cookie: {$tproject_cookie}='.$_SESSION['testprojectID']);
      }
	}
	if (!$_SESSION['testprojectID'])
	{
    	$tpID = null;
    	if (sizeof($arrProducts))
    	{
    		$tpID = key($arrProducts);
    	}	
   		$_SESSION['testprojectID'] = $tpID;
	}
	// Validation is done in navBar.php
	$tplan_cookie = 'TL_lastTestPlanForUserID_' . $id;
	if (isset($_COOKIE[$tplan_cookie]))
	{
		$_SESSION['testplanID'] = $_COOKIE[$tplan_cookie];
		tLog("Cookie: {$tplan_cookie}=".$_SESSION['testplanID']);
	}

	return 1;
}

/*
  function: getUsersForHtmlOptions

  args:  db: reference to db object
        [whereClause]:
        [add_blank_option]:
        [active_filter]:

  returns: map

  rev :
       20071228 - franciscom - added active_filter
*/
function getUsersForHtmlOptions(&$db,$whereClause = null,$additional_users = null, $active_filter = null,$users = null)
{
	$users_map = null;
	if (!$users)
	{
		$sqlWhere = $whereClause;
		if(!is_null($active_filter))
		{
			$whereClause .= ' AND active =' . ($active_filter > 0 ? 1 : 0) . ' ';
		}
		$users = tlUser::getAll($db,$sqlWhere,"id",null,tlUser::TLOBJ_O_GET_DETAIL_MINIMUM);
	}
  
	//$the_users = $users;
	
	// if ($users)
	// {
	// 	if(!is_null($active_filter))
	// 	{
	// 		$the_users = array();
	// 		foreach($users as $id => $user)
	// 		{
	// 			if($user->isActive == $active_filter)
	// 			{
	// 				$the_users[$id] = $users[$id];
	// 			}	
	// 		}
	// 	}
	// }
	return buildUserMap($users,!is_null($additional_users),$additional_users);
}

/*
  function: buildUserMap

  args:
       $users: map of user objects
       [add_options]: default false.
                      true, elements present on additional_options arguments
                      will be will added to result map.
       
       [additional_options]: default null
                             map with key=user id, value=verbose description 
  
  returns: map ready to be used on a HTML select input.

*/
function buildUserMap($users,$add_options = false, $additional_options=null)
{
	$usersMap = null;
	$inactivePrefix = lang_get('tag_for_inactive_users');
	if ($users)
	{
		if($add_options)
		{
		  $my_options = is_null($additional_options) ? array( 0 => '') : $additional_options;
		  foreach($my_options as $code => $verbose_code)
		  {
			    $usersMap[$code] = $verbose_code;
			}
		}
		$userSet = array_keys($users);
		$loops2do = count($userSet);
		
		// foreach($usersr as $id => $user)
		// {
		// 	$usersMap[$id] = $user->getDisplayName();
		// 	if($user->isActive == 0)
		// 	{
		// 	    $usersMap[$id] = $inactivePrefix . ' ' . $usersMap[$id];
		// 	} 
		// }
        for( $idx=0; $idx < $loops2do ; $idx++)
        {
        	$userID = $userSet[$idx];
			$usersMap[$userID] = $users[$userID]->getDisplayName();
			if($users[$userID]->isActive == 0)
			{
			    $usersMap[$userID] = $inactivePrefix . ' ' . $usersMap[$userID];
			} 
        }
	}
	return $usersMap;
}



/*
  function: getAllUsersRoles

  args:

  returns:
  

*/
function getAllUsersRoles(&$db,$order_by = null)
{
    $tables = tlObject::getDBTables(array('users','roles'));
    
	$sql = "SELECT users.id FROM {$tables['users']} users " .
	         " LEFT OUTER JOIN {$tables['roles']} roles ON users.role_id = roles.id ";
	$sql .= is_null($order_by) ? " ORDER BY login " : $order_by;

	$users = tlDBObject::createObjectsFromDBbySQL($db,$sql,"id","tlUser",false,tlUser::TLOBJ_O_GET_DETAIL_MINIMUM);

	$loop2do = count($users);
	$specialK = array_flip((array)config_get('demoSpecialUsers'));
	for($idx=0; $idx < $loop2do; $idx++)
	{
		$users[$idx]->isDemoSpecial = isset($specialK[$users[$idx]->login]);
	}
	return $users;
}

/*
  function: getTestersForHtmlOptions

  args: 

  returns:

*/
/**
 * getTestersForHtmlOptions
 * returns users that have role on ($tplanID,$tprojectID) with right
 * to execute a test case.
 *
 * @param resource &$db reference to database handler
 * @param integer $tplanID test plan id
 * @param integer $tprojectID test project id
 * @param $users UNUSED - remove
 * @param $additional_testers TBD
 * @param string $activeStatus. values: 'active','inactive','any'
 * 
 * @return array TBD  
 * @internal revisions
 * 20101023 - franciscom - BUGID 3931: Assign test case to test project fails for 
 *						   PRIVATE TEST PROJECT (tested with admin user)
 */
function getTestersForHtmlOptions(&$db,$tplanID,$tproject,$users = null, 
                                  $additional_testers = null,$activeStatus = 'active')
{
	$orOperand = false;
    $activeTarget = 1;
    switch ($activeStatus)
    {
        case 'any':
            $orOperand = true;
        break;
        
        case 'inactive':
            $activeTarget = 0;
    	break;
        
        case 'active':
        default:
	    break;
    }

    $users_roles = get_tplan_effective_role($db,$tplanID,$tproject,null,$users);

    $userFilter = array();
    foreach($users_roles as $keyUserID => $roleInfo)
    {
    	// BUGID 3931: Assign test case to test project fails for PRIVATE TEST PROJECT (tested with admin user)
    	if( is_object($roleInfo['effective_role']) )
    	{
        	if( $roleInfo['effective_role']->hasRight('testplan_execute') && 
        	    ($orOperand || $roleInfo['user']->isActive == $activeTarget) )
        	{
        	    
        	     $userFilter[$keyUserID] = $roleInfo['user'];
        	}
        }   
    }
	return buildUserMap($userFilter,true,$additional_testers);
}

function initialize_tabsmenu()
{
	$hl = new stdClass();
	$hl->view_roles = 0;
	$hl->create_role = 0;
	$hl->edit_role = 0;

	$hl->view_users = 0;
	$hl->create_user = 0;
	$hl->edit_user = 0;

	$hl->assign_users_tproject = 0;
	$hl->assign_users_tplan = 0;
	return $hl;
}


/*
  function: getGrantsForUserMgmt 
            utility function used on all user and role pages
            to pass grants to smarty templates.
            Logic is:
            if user has Global user management right => no control
               on specific test project or test plan is done
           

  args:
  
  returns: 

*/
function getGrantsForUserMgmt(&$dbHandler,&$userObj,$tprojectID=null,$tplanID=null)
{
    $answers = new stdClass();
    $grants = new stdClass();
    $grants->user_mgmt = $userObj->hasRight($dbHandler,"mgt_users",$tprojectID);
    $grants->role_mgmt = $userObj->hasRight($dbHandler,"role_management",$tprojectID);
    $grants->tproject_user_role_assignment = "no";
    $grants->tplan_user_role_assignment = "no";
    
    if($grants->user_mgmt == 'yes')
    {
        $grants->tplan_user_role_assignment = 'yes';
        $grants->tproject_user_role_assignment = 'yes';  
    }
    else
    {
        
        $grants->tplan_user_role_assignment = $userObj->hasRight($dbHandler,"testplan_user_role_assignment",
                                                                 $tprojectID,$tplanID);
        
        
        $answers->user_role_assignment = $userObj->hasRight($dbHandler,"user_role_assignment",null,-1);
        $answers->testproject_user_role_assignment=$userObj->hasRight($dbHandler,"testproject_user_role_assignment",$tprojectID,-1);
        if($answers->user_role_assignment == "yes" || $answers->testproject_user_role_assignment == "yes")
        {    
            $grants->tproject_user_role_assignment = "yes";
        }
    }    
    foreach($grants as $key => $value)
    {
        $grants->$key = $value == "yes" ? "yes" : "no";       
    }
    
    return $grants;
}
?>
