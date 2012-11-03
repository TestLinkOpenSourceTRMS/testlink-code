<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Functions for usermanagement
 * 
 * @filesource	users.inc.php
 * @package 	  TestLink
 * @author 		  Martin Havlat
 * @copyright 	2006-2012, TestLink community 
 * @link 		    http://www.teamst.org/index.php
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
function XXXXgetTestersForHtmlOptions(&$db,$tplanID,$tproject,$users = null, 
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
    	// Assign test case to test project fails for PRIVATE TEST PROJECT (tested with admin user)
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
?>