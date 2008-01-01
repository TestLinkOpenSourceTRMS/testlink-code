<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * 
 * @filesource $RCSfile: roles.inc.php,v $
 * @version $Revision: 1.37 $
 * @modified $Date: 2008/01/01 16:38:17 $ by $Author: schlundus $
 * @author Martin Havlat, Chad Rosen
 * 
 * This script provides the get_rights and has_rights functions for
 *           verifying user level permissions.
 *
 *
 * Default USER RIGHTS:
 *
 * 'guest' 	- testplan_metrics, mgt_view_tc, mgt_view_key
 * 'tester' - testplan_execute, testplan_metrics
 * 'senior tester' 	- testplan_execute, testplan_metrics, mgt_view_tc, mgt_modify_tc, mgt_view_key
 * 'lead' 	- testplan_execute, testplan_create_build, testplan_metrics, testplan_planning, testplan_assign_rights,
 *				mgt_view_tc, mgt_modify_tc, mgt_view_key, mgt_modify_key
 * 'admin' 	- testplan_execute, testplan_create_build, testplan_metrics, testplan_planning, testplan_assign_rights,
 *				mgt_view_tc, mgt_modify_tc, mgt_view_key, mgt_modify_key,
 *				mgt_modify_product, mgt_users
 *
 *
 * OPTIONS: FUNCTIONALITY ALLOWED FOR USER:
 * 
 * mgt_view_tc, testplan_metrics, mgt_view_key - allow browse basic data
 * testplan_execute - edit Test Results
 * mgt_modify_tc - edit Test Cases
 * mgt_modify_key - edit Keywords
 * mgt_modify_req - edit Requirements
 * testplan_planning, testplan_create_build, testplan_assign_rights - Test Leader plans/prepares a testing
 * mgt_modify_product, mgt_users - just Admin edits Products and Users
 *
 *
 * rev : 20071228 - franciscom - added roleHasRight()
 *       20070901 - franciscom - BUGID 1016
 *       20070819 - franciscom - added get_tplan_effective_role(), get_tproject_effective_role()
 *       20070818 - franciscom - changes in getRoles()
 *       20070702 - franciscom - new get_effective_role()
 */
 
require_once( dirname(__FILE__). '/lang_api.php' );

// 
// This can seems weird but we have this problem:
//
// lang_get() is used to translate user rights description and needs $_SESSION info.
// If no _SESSION info is found, then default locale is used.
// We need to be sure _SESSION info exists before using lang_get(); in any module.
//  
// Then we need to explicitily init this globals to get right localization.
// With previous implementation we always get localization on TL DEFAULT LOCALE
//
init_global_rights_maps();


/*
  function: init_global_rights_maps
            init global map with user rights and user rights description localized.
            

  args :
  
  returns: 

*/

function init_global_rights_maps()
{
// Important:
// Every array, defines a section in the define role page
//
global $g_rights_tp;
global $g_rights_mgttc;
global $g_rights_kw;
global $g_rights_req;
global $g_rights_product;
global $g_rights_cf;
global $g_rights_users_global;
global $g_rights_users;
global $g_propRights_global;
global $g_propRights_product;


$g_rights_tp = array (	"testplan_execute" => lang_get('desc_testplan_execute'),
						"testplan_create_build" => lang_get('desc_testplan_create_build'),
						"testplan_metrics" => lang_get('desc_testplan_metrics'),
						"testplan_planning" => lang_get('desc_testplan_planning'),
						"testplan_user_role_assignment" => lang_get('desc_user_role_assignment'),
					);

					
$g_rights_mgttc = array (	"mgt_view_tc" => lang_get('desc_mgt_view_tc'),
							"mgt_modify_tc" => lang_get('desc_mgt_modify_tc'),
							"mgt_testplan_create" => lang_get('mgt_testplan_create'),
						);

$g_rights_kw = array (	
							"mgt_view_key" => lang_get('desc_mgt_view_key'),
							"mgt_modify_key" => lang_get('desc_mgt_modify_key'),
						);
$g_rights_req = array (	
							"mgt_view_req" => lang_get('desc_mgt_view_req'),
							"mgt_modify_req" => lang_get('desc_mgt_modify_req'),
						);

						
$g_rights_product = array (	
							"mgt_modify_product" => lang_get('desc_mgt_modify_product'),
						);						

// 20061231 - franciscom
$g_rights_cf = array (	
							"cfield_view" => lang_get('desc_cfield_view'),
							"cfield_management" => lang_get('desc_cfield_management'));


$g_rights_users_global = array (	
							"mgt_users" => lang_get('desc_mgt_modify_users'),
							"role_management" => lang_get('desc_role_management'),
							"user_role_assignment" => lang_get('desc_user_role_assignment')
							); 
						
						
$g_rights_users = $g_rights_users_global;
						
						
$g_propRights_global = array_merge($g_rights_users_global,$g_rights_product);
$g_propRights_product = array_merge($g_propRights_global,$g_rights_mgttc,$g_rights_kw,$g_rights_req);
}
						

/**
 * Gets all testplan related user assignments
 *
 * @param object $db [ref] the db-object
 * @param int $testPlanID the testplan id
 * @return array assoc map with keys taken from the user_id column
 **/
  //SCHLUNDUS: should be moved inside testplan class
function getTestPlanUserRoles(&$db,$testPlanID)
{
	$query = "SELECT user_id,role_id FROM user_testplan_roles WHERE testplan_id = {$testPlanID}";
	$roles = $db->fetchRowsIntoMap($query,'user_id');
	return $roles;
}

/**
 * Gets all testproject related role assignments
 *
 * @param object $db [ref] the db-object
 * @param int $tproject_id 
 * @return array assoc array with keys take from the user_id column
 **/
 //SCHLUNDUS: should be moved inside test_project class
function getTestProjectUserRoles(&$db,$tproject_id)
{
	$query = "SELECT user_id,role_id FROM user_testproject_roles " .
	         "WHERE testproject_id = {$tproject_id}";
	$roles = $db->fetchRowsIntoMap($query,'user_id');
	
	return $roles;
}

/**
 * Deletes all testproject related role assignments for a given testproject
 *
 * @param object $db [ref] the db-object
 * @param int $tproject_id
 * @return int 1 on success, false else
 **/
//SCHLUNDUS: should be moved inside test_project class
function deleteTestProjectUserRoles(&$db,$tproject_id)
{
	$query = "DELETE FROM user_testproject_roles WHERE testproject_id = {$tproject_id}";
	return ($db->exec_query($query) ? 1 : 0);
}

/**
 * Deletes all testplan related role assignments for a given testplan
 *
 * @param object $db [ref] the db-object
 * @param int $testPlanID the testplan id
 * @return int 1 on success, false else
 **/
//SCHLUNDUS: should be moved inside test_plan class 
function deleteTestPlanUserRoles(&$db,$testPlanID)
{
	$query = "DELETE FROM user_testplan_roles WHERE testplan_id = {$testPlanID}";
	return ($db->exec_query($query) ? 1 : 0);
}

/**
 * Inserts a testplan related role for a given user
 *
 * @param object $db [ref] the db-object
 * @param int $userID the id of the user
 * @param int $testPlanID the testplan id 
 * @param int $roleID the role id
 * @return int returns 1 on success, 0 else
 **/
//SCHLUNDUS: should be moved inside test_plan class 
function insertUserTestPlanRole(&$db,$userID,$testPlanID,$roleID)
{
	$query = "INSERT INTO user_testplan_roles (user_id,testplan_id,role_id) VALUES ({$userID},{$testPlanID},{$roleID})";
	return ($db->exec_query($query) ? 1 : 0);
}

/**
 * Inserts a testproject related role for a given user
 *
 * @param object $db [ref] the db-object
 * @param int $userID the id of the user
 * @param int $tproject_id 
 * @param int $roleID the role id
 * @return int returns 1 on success, 0 else
 **/
//SCHLUNDUS: should be moved inside testproject class 
function insertUserTestProjectRole(&$db,$userID,$tproject_id,$roleID)
{
	$query = " INSERT INTO user_testproject_roles " .
	         " (user_id,testproject_id,role_id) VALUES ({$userID},{$tproject_id},{$roleID})";
	return ($db->exec_query($query) ? 1 : 0);
}

						
/**
 * returns all roles from the db with the assigned rights. 
 *
 * @param object $db [ref] the db-object
 * @return array assoc-array of the following form
 * 				 roles[role_id] => array ('id' => role_id,
 * 										  'role' => role_description,
 * 										  'rights' => comma-separated list of rights
 *
 * rev:
 *      20070818 - franciscom - get description from db for TL_ROLES_NONE
 *                              instead of using a php constant
 *
 **/
function getRoles(&$db)
{
	$roles = null;
	$sql = "SELECT roles.id, roles.description, rights.description AS rights_description, notes
	        FROM role_rights r, roles, rights
	        WHERE role_id=roles.id and right_id=rights.id";
	          
	$result = $db->exec_query($sql);
	if ($result)
	{
	  // ---------------------------------------------------
	  // get description from db for TL_ROLES_NONE
	  $sql = " SELECT id,description" .
	         " FROM roles" .
	         " WHERE id=" . TL_ROLES_NONE;

    $mm=$db->fetchRowsIntoMap($sql,'id');	
    $descr=$mm[TL_ROLES_NONE]['description'];
    // ---------------------------------------------------
    
		$roles[TL_ROLES_NONE] = array('id' => TL_ROLES_NONE,
									                'role' => $descr,
									                'rights' => '',
									  );
		while($row = $db->fetch_array($result))
		{
			$roleID = $row['id'];
			$roleDesc = $row['description'];
			$rightDescription = $row['rights_description'];
			//add the new role if not present
			if (!isset($roles[$roleID]))
				$roles[$roleID] = array('id' => $roleID,
										'role' => $roleDesc,
										'rights' => $rightDescription,
										'notes' => $row['notes'],
										);
			else
			{
				//update the right list for existing roles
				$roleString = $roles[$roleID]['rights'];
				if (strlen($roleString))
					$roleString .= ",";
				$roleString .= $rightDescription;
				$roles[$roleID]['rights'] = $roleString;
			}
		}
	}
	
	return $roles;
}

/** 
* function takes a roleQuestion from a specified link and returns whether 
* the user has rights to view it
* 20051231 - scs - added reloading the rights if the users role has changed
*
*/
function has_rights(&$db,$roleQuestion,$tprojectID = null,$tplanID = null)
{
	global $g_propRights_global;
	global $g_propRights_product;
	
	// we dont need to query the db for the rights every call
	// so the rights are fetched only once per script 
	static $s_allRoles = null;
	static $s_currentUser = null;
	static $s_userGlobalRole = null;
	
	//load the current user
	if (is_null($s_currentUser))
	{
		checkSessionValid($db);
		$s_currentUser = $_SESSION['currentUser'];
	}
	//load the rights
	if (is_null($s_allRoles))
		$s_allRoles = tlRole::getAll($db);
		
	$globalRoleID = $s_currentUser->globalRoleID;
	$globalRights = is_null($s_allRoles[$globalRoleID]->rights) ? '' : $s_allRoles[$globalRoleID]->rights;
	//SCHLUNDUS: hack, will be removed later
	$globalRights = explode(",",implode(",",$globalRights));
	
	if (!is_null($tplanID))
		$testPlanID = $tplanID;
	else
		$testPlanID = isset($_SESSION['testPlanId']) ? $_SESSION['testPlanId'] : 0;
	$userTestPlanRoles = $s_currentUser->tplanRoles;
	
	if (!is_null($tprojectID))
		$productID = $tprojectID;
	else
		$productID = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
	
	$allRights = $globalRights;
		
	$userTestProjectRoles = $s_currentUser->tprojectRoles;
	/* if $productID == -1 we dont check rights at product level! */
	if (isset($userTestProjectRoles[$productID]))
	{
		$productRights = $userTestProjectRoles[$productID]->rights;
		//SCHLUNDUS: hack, will be removed later
		$productRights = explode(",",implode(",",$productRights));
		//subtract global rights		
		$productRights = array_diff($productRights,array_keys($g_propRights_global));

		propagateRights($globalRights,$g_propRights_global,$productRights);
		$allRights = $productRights;
	}
	/* if $tplanID == -1 we dont check rights at tp level! */
	if (isset($userTestPlanRoles[$testPlanID]))
	{
		$testPlanRights = $userTestPlanRoles[$testPlanID]->rights;
		//SCHLUNDUS: hack, will be removed later
		$testPlanRights = explode(",",implode(",",$testPlanRights));
		
		//subtract product rights		
		$testPlanRights = array_diff($testPlanRights,array_keys($g_propRights_product));
		
		propagateRights($allRights,$g_propRights_product,$testPlanRights);
		$allRights = $testPlanRights;
	}
	return checkForRights($allRights,$roleQuestion);
}

/*
  function: 

  args :
   
  returns: 
*/
function propagateRights($fromRights,$propRights,&$toRights)
{
	//the mgt_users right isn't product-related so this right is inherited from
	//the global role (if set)
	foreach($propRights as $right => $desc)
	{
		if (in_array($right,$fromRights) && !in_array($right,$toRights))
			$toRights[] = $right;
	}
}

/**
 * Function-Documentation
 *
 * @param type $rights documentation
 * @param type $roleQuestion documentation
 * @param type $bAND [default = 1] documentation
 * @return type documentation
 *
 * @author Andreas Morsing <schlundus@web.de>
 * @since 20.02.2006, 20:30:07
 *
 **/
function checkForRights($rights,$roleQuestion,$bAND = 1)
{
	$ret = null;
	//check to see if the $roleQuestion variable appears in the $roles variable
	if (is_array($roleQuestion))
	{
		$r = array_intersect($roleQuestion,$rights);
		if ($bAND)
		{
			//for AND all rights must be present
			if (sizeof($r) == sizeof($roleQuestion))
				$ret = 'yes';
		}	
		else 
		{
			//for OR one of all must be present
			if (sizeof($r))
				$ret = 'yes';
		}	
	}
	else
		$ret = (in_array($roleQuestion,$rights) ? 'yes' : null);
	
	return $ret;
}

/*
  function: get_effective_role()

  args : $user_id
         $tproject_id
         $tplan_id
         
  
  returns: effetive_role in context ($tproject_id,$tplan_id)

*/
function get_effective_role(&$db,$user_id,$tproject_id,$tplan_id)
{
	$user = tlUser::getById($db,$user_id);
	$default_role = $user->globalRoleID;
	$tprojects_role = $user->tprojectRoles;
	$tplans_role = $user->tplanRoles;

	$effective_role = $default_role;
	if(!is_null($tplans_role) && isset($tplans_role[$tplan_id]))
		$effective_role = $tplans_role[$tplan_id]->dbID;  
	else if(!is_null($tprojects_role) && isset($tprojects_role[$tproject_id]))
		$effective_role = $tprojects_role[$tproject_id]->dbID;  
	
	return $effective_role;
}

/*
  function: get_tproject_effective_role()
            Get info about user(s) role at test project level,
            with indication about the nature of role: inherited or assigned.
             
            To get a user role we consider a 3 layer model:
            
            layer 1 - user           <--- uplayer
            layer 2 - test project   <--- in this fuction we are interested in this level.
            layer 3 - test plan
            
            
  
  args : $tproject_id
         [$user_id]
  
  returns: map with effetive_role in context ($tproject_id)
           key: user_id 
           value: map with keys:
                  login                (from users table - useful for debug)
                  user_role_id         (from users table - useful for debug)
                  uplayer_role_id      (always = user_role_id)
                  uplayer_is_inherited
                  effective_role_id  user role for test project
                  is_inherited
  

*/
function get_tproject_effective_role(&$db,$tproject_id,$user_id = null)
{
	$filter = null;
	if(!is_null($user_id))
	{
		$filter = " WHERE id";
		if(is_array($user_id))
			$filter .= " IN (" . implode(',',$user_id) . ") ";
		else
			$filter .= " = {$user_id} ";    
	}
 
	$effective_role = array();
	$users = tlUser::getAll($db,$filter,"id");
	if ($users)
	{
		foreach($users as $id => $user)
		{
			$effective_role[$id] = array('login' => $user->login,
										 'user_role_id' => $user->globalRoleID,
										 'uplayer_role_id' => $user->globalRoleID,
										 'uplayer_is_inherited' => 0,
										 'effective_role_id' => $user->globalRoleID,
										 'is_inherited' => 1);
		}  
	}
	$tproject_users_role = getTestProjectUserRoles($db,$tproject_id);
  
	if(!is_null($tproject_users_role))
	{
		foreach($effective_role as $user_id => $row)
		{
			if(isset($tproject_users_role[$user_id]))
			{
				$effective_role[$user_id]['is_inherited'] = 0;
				$effective_role[$user_id]['effective_role_id'] = $tproject_users_role[$user_id]['role_id'];
			}
		}  
	}

	return $effective_role;
}


/*
  function: get_tplan_effective_role()
            Get info about user(s) role at test plan level,
            with indication about the nature of role: inherited or assigned.

            To get a user role we consider a 3 layer model:
            
            layer 1 - user
            layer 2 - test project   <--- uplayer
            layer 3 - test plan      <--- in this fuction we are interested in this level.
             
  
  args : $tplan_id
         $tproject_id
         [$user_id]
  
  returns: map with effetive_role in context ($tplan_id)
           key: user_id 
           value: map with keys:
                  login                (from users table - useful for debug)
                  user_role_id         (from users table - useful for debug)
                  uplayer_role_id      user role for test project
                  uplayer_is_inherited 1 -> uplayer role is inherited 
                                       0 -> uplayer role is written in table
                                       
                  effective_role_id    user role for test plan
                  is_inherited       
  

*/
function get_tplan_effective_role(&$db,$tplan_id,$tproject_id,$user_id=null)
{
  $effective_role=array();
  $effective_role=get_tproject_effective_role($db,$tproject_id,$user_id);   

  foreach($effective_role as $user_id => $row)
  {
    $effective_role[$user_id]['uplayer_role_id']=$effective_role[$user_id]['effective_role_id'];
    $effective_role[$user_id]['uplayer_is_inherited']=$effective_role[$user_id]['is_inherited'];
    $effective_role[$user_id]['is_inherited']=1;
  }
  
  $tplan_users_role = getTestPlanUserRoles($db,$tplan_id);
  if( !is_null($tplan_users_role) )
  {
    foreach($effective_role as $user_id => $row)
    {
      if( isset($tplan_users_role[$user_id]) )
      {
        $effective_role[$user_id]['is_inherited']=0;
        $effective_role[$user_id]['effective_role_id']=$tplan_users_role[$user_id]['role_id'];  
      }
    }  
  }

  return $effective_role;
}

function getRoleErrorMessage($code)
{
	$msg = 'ok';
	switch($code)
	{
		case tlRole::E_NAMEALREADYEXISTS:
			$msg = lang_get('error_duplicate_rolename');
			break;
		case tlRole::E_NAMELENGTH:
			$msg = lang_get('error_role_no_rolename');
			break;
		case tlRole::E_EMPTYROLE:
			$msg = lang_get('error_role_no_rights');
			break;
		case tl::OK:
			break;
		case ERROR:
		case tlRole::E_DBERROR:
		default:
			$msg = lang_get('error_role_not_updated');
	}
	return $msg;
}

/*
  function: roleHasRight
            check if a role has requested right

  args: 
  
  returns: true / false 

*/
function roleHasRight(&$db,$roleID,$verboseRight)
{
  $sql=" SELECT RR.*, RI.description " .
       " FROM role_rights RR, rights RI " .
       " WHERE RR.right_id = RI.id " .
       " AND RI.description = '" . $verboseRight . "' " .
       " AND RR.role_id = {$roleID} ";
       
  $rs=$db->fetchRowsIntoMap($sql,'role_id');
  return (!is_null($rs) && count($rs) > 0) ? true : false;
}
?>
