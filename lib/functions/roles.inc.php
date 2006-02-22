<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * 
 * @filesource $RCSfile: roles.inc.php,v $
 * @version $Revision: 1.2 $
 * @modified $Date: 2006/02/22 20:26:38 $ by $Author: schlundus $
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
 * mgt_modify_req - edit Product Requirements
 * testplan_planning, testplan_create_build, testplan_assign_rights - Test Leader plans/prepares a testing
 * mgt_modify_product, mgt_users - just Admin edits Products and Users
 *
 *
 */
require_once( dirname(__FILE__). '/lang_api.php' );

$g_rights_tp = array (	"testplan_execute" => lang_get('desc_testplan_execute'),
						"testplan_create_build" => lang_get('desc_testplan_create_build'),
						"testplan_metrics" => lang_get('desc_testplan_metrics'),
						"testplan_planning" => lang_get('desc_testplan_planning'),
						"testplan_assign_rights" => lang_get('desc_testplan_assign_rights'),
					);

					
$g_rights_mgttc = array (	"mgt_view_tc" => lang_get('desc_mgt_view_tc'),
							"mgt_modify_tc" => lang_get('desc_mgt_modify_tc'),
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
$g_rights_users = array (	
							"mgt_users" => lang_get('desc_mgt_modify_users'),
						);	

						
$g_propRights_global = array_merge($g_rights_users,$g_rights_product);
$g_propRights_product = array_merge($g_propRights_global,$g_rights_mgttc,$g_rights_kw,$g_rights_req);
						
/**
 * Fetches all rights
 *
 * @param object $db [ref] db-object
 * @param string $column [default = 'id'] column used as the key for the map
 * @return array assoc. array with keys from the column
 **/
function getAllRights(&$db,$column = 'id')
{
	$query = "SELECT id,description FROM rights ORDER BY id ASC";
	$rights = $db->fetchRowsIntoMap($query,$column);
	
	return $rights;
}
						
/**
 * Creates a role with a given name and rights
 *
 * @param object $db [ref] the db-object
 * @param type $roleName the name for the role
 * @param array $rights the rights for the role (string array) 
 * @return int the new roleID on success, 0 else
 **/
function createRole(&$db,$roleName,$rights)
{
	$roleID = 0;

	$query = "INSERT INTO roles (description) VALUES ('".$db->prepare_string($roleName)."')";
	$result = $db->exec_query($query);		 
	if ($result)
	{
		$roleID = $db->insert_id();
		if ($roleID)
			insertRoleRights($db,$roleID,$rights);
	}
			 
	return $roleID;
}									

/**
 * Inserts the rights for the role 
 *
 * @param object $db [ref] the db-object
 * @param int $roleID the id of the role
 * @param array $rights string array of rights for the roles
 * @return int 1 on success, 0 else
 **/
function insertRoleRights(&$db,$roleID,$rights)
{
	$bSuccess = 1;
	$allRights = getAllRights($db,'description');
	for($i = 0;$i < sizeof($rights);$i++)
	{
		$rightID = $allRights[$rights[$i]]['id'];
		$query = "INSERT INTO role_rights (role_id,right_id) VALUES ({$roleID},{$rightID})";
		$bSuccess = $bSuccess && ($db->exec_query($query) ? 1 : 0);
	}
	return $bSuccess;
}

/**
 * Gets all testplan related user assignments
 *
 * @param object $db [ref] the db-object
 * @param int $testPlanID the testplan id
 * @return array assoc map with keys taken from the user_id column
 **/
function getTestPlanUserRoles(&$db,$testPlanID)
{
	$query = "SELECT user_id,role_id FROM user_testplan_roles WHERE testplan_id = {$testPlanID}";
	$roles = $db->fetchRowsIntoMap($query,'user_id');
	
	return $roles;

}

/**
 * Gets all testproject related role assignments for a give user
 *
 * @param object $db [ref] the db-object
 * @param int $userID the user id
 * @return array assoc map with keys taken from the testproject_id column
 **/
function getUserProductRoles(&$db,$userID)
{
	$query = "SELECT testproject_id,role_id FROM user_testproject_roles WHERE user_id = {$userID}";
	$roles = $db->fetchRowsIntoMap($query,'testproject_id');
	
	return $roles;
}


/**
 * Gets all testplan related role assignments for a given user
 *
 * @param object $db [ref] the db-object
 * @param int $userID the user id
 * @return array documentation assoc array with keys take from the testplan_id
 * 				column
 **/
function getUserTestPlanRoles(&$db,$userID)
{
	$query = "SELECT testplan_id,role_id FROM user_testplan_roles WHERE user_id = {$userID}";
	$roles = $db->fetchRowsIntoMap($query,'testplan_id');
	
	return $roles;
}

/**
 * Gets all testproject related role assignments
 *
 * @param object $db [ref] the db-object
 * @param int $productID documentation
 * @return array assoc array with keys take from the user_id column
 **/
function getProductUserRoles(&$db,$productID)
{
	$query = "SELECT user_id,role_id FROM user_testproject_roles WHERE testproject_id = {$productID}";
	$roles = $db->fetchRowsIntoMap($query,'user_id');
	
	return $roles;
}

/**
 * Deletes all testproject related role assignments for a given user
 *
 * @param object $db [ref] the db-object
 * @param int $userID the user id
 * @return int 1 on success, false else
 **/
function deleteUserProductRoles(&$db,$userID)
{
	$query = "DELETE FROM user_testproject_roles WHERE user_id = {$userID}";
	return ($db->exec_query($query) ? 1 : 0);
}

/**
 * Deletes all testproject related role assignments for a given testproject
 *
 * @param object $db [ref] the db-object
 * @param int $productID the product id
 * @return int 1 on success, false else
 **/
function deleteProductUserRoles(&$db,$productID)
{
	$query = "DELETE FROM user_testproject_roles WHERE testproject_id = {$productID}";
	return ($db->exec_query($query) ? 1 : 0);
}

/**
 * Deletes all testplan related role assignments for a given testplan
 *
 * @param object $db [ref] the db-object
 * @param int $testPlanID the testplan id
 * @return int 1 on success, false else
 **/
function deleteTestPlanUserRoles(&$db,$testPlanID)
{
	$query = "DELETE FROM user_testplan_roles WHERE testplan_id = {$testPlanID}";
	return ($db->exec_query($query) ? 1 : 0);
}

function updateUserProductRoles(&$db,$userID,$userProductRoles)
{
	foreach($userProductRoles as $pID => $roleID)
	{
		if ($roleID)
			insertUserProductRole($db,$userID,$pID,$roleID);
	}
}

function insertUserTestPlanRole(&$db,$userID,$testPlanID,$roleID)
{
	$query = "INSERT INTO user_testplan_roles (user_id,testplan_id,role_id) VALUES ({$userID},{$testPlanID},{$roleID})";
	return ($db->exec_query($query) ? 1 : 0);
}

function insertUserProductRole(&$db,$userID,$productID,$roleID)
{
	$query = "INSERT INTO user_testproject_roles (user_id,testproject_id,role_id) VALUES ({$userID},{$productID},{$roleID})";
	return ($db->exec_query($query) ? 1 : 0);
}

function getUsersWithGlobalRole(&$db,$roleID)
{
	$query = "SELECT id FROM user WHERE role_id={$roleID}";
	$users = $db->fetchColumnsIntoArray($query,'id');
	
	return $users;
}

function getUsersWithProductRole(&$db,$roleID)
{
	$query = "SELECT id FROM user,user_testproject_roles WHERE user.id = user_testproject_roles.user_id";
	$query .= " AND user_testproject_roles.role_id = {$roleID}";
	$users = $db->fetchColumnsIntoArray($query,'id');
	
	return $users;
}

function getUsersWithTestPlanRole(&$db,$roleID)
{
	$query = "SELECT id FROM `user`,user_testplan_roles WHERE  user.id = user_testplan_roles.user_id";
	$query .= " AND user_testplan_roles.role_id = {$roleID}";
	$users = $db->fetchColumnsIntoArray($query,'id');
	
	return $users;
}

function getAllUsersWithRole(&$db,$roleID)
{
	$affectedGlobalUsers = getUsersWithGlobalRole($db,$roleID);
	$affectedTestPlanUsers = getUsersWithTestPlanRole($db,$roleID);
	$affectedProductUsers = getUsersWithProductRole($db,$roleID);
	$affectedUsers = array_unique(array_merge($affectedGlobalUsers,$affectedTestPlanUsers,$affectedProductUsers));

	return $affectedUsers;
}

function deleteRoleRights(&$db,$roleID)
{
	$query = "DELETE FROM role_rights WHERE role_id = {$roleID}";
	$result = $db->exec_query($query);
	
	return $result ? 1 : 0;
}

function deleteRole(&$db,$roleID)
{
	if (deleteRoleRights($db,$roleID))
	{
		$query = "DELETE FROM roles WHERE id = {$roleID}";
		$result = $db->exec_query($query);
	}
	
	return $result ? 1 : 0;
}

function updateRole(&$db,$roleID,$roleName,$rights)
{
	deleteRoleRights($db,$roleID);
	
	$query = "UPDATE roles SET description = '".$db->prepare_string($roleName)."'".
				" WHERE id = {$roleID}";
	
	insertRoleRights($db,$roleID,$rights);
	
	return $db->exec_query($query) ? 1 : 0;
}

function resetUserRoles(&$db,$id)
{
	$query = "UPDATE user SET role_id = ".TL_ROLES_NONE." WHERE role_id = {$id}";
	$db->exec_query($query);

	$query = "UPDATE user_testproject_roles SET role_id = ".TL_ROLES_NONE." WHERE role_id = {$id}";
	$db->exec_query($query);
	
	$query = "UPDATE user_testplan_roles SET role_id = ".TL_ROLES_NONE." WHERE role_id = {$id}";
	$db->exec_query($query);
}
						
function getRoles(&$db)
{
	$roles = null;
	$query = "SELECT roles.id, roles.description, rights.description AS rights_description FROM role_rights r, roles, rights WHERE role_id=roles.id and right_id=rights.id";
	$result = $db->exec_query($query);
	if ($result)
	{
		$tmp = 0;
		$role = null;
		$roles[TL_ROLES_NONE] = array('id' => TL_ROLES_NONE,
									  'role' => '<no rights>',
									  'rights' => '',
									  );
		while($row = $db->fetch_array($result))
		{
			$roleID = $row['id'];
			$roleDesc = $row['description'];
			$rightDescription = $row['rights_description'];
			if (!isset($roles[$roleID]))
				$roles[$roleID] = array('id' => $roleID,
										'role' => $roleDesc,
										'rights' => $rightDescription,
										);
			else
			{
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

function getListOfRoles(&$db)
{
	$roles = getTwoColumnsMap($db,"SELECT id,description FROM roles");
	$roles[0] = "<undefined>";
	
	return $roles;
}

function existRole(&$db,$roleName,$id = null)
{
	$roleName = $db->prepare_string($roleName);
	$query = "SELECT id FROM roles WHERE description = '{$roleName}'";
	if ($id)
		$query .= " AND id != {$id}";
		
	return ($db->fetchFirstRowSingleColumn($query,"id") ? 1 : 0);
}

function checkRole(&$db,$roleName,$rights,$id = null)
{
	$sqlResult = 'ok';
	if (sizeof($rights))
	{
		if (strlen($roleName))
		{
			if (existRole($db,$roleName,$id))
				$sqlResult = lang_get('error_duplicate_rolename');
		}
		else
			$sqlResult = lang_get('error_role_no_rolename');
	}
	else
		$sqlResult = lang_get('error_role_no_rights');
		
	return $sqlResult;		
}
/** 
* function takes a roleQuestion from a specified link and returns whether 
* the user has rights to view it
* 20051231 - scs - added reloading the rights if the users role has changed
*
*/
function has_rights(&$db,$roleQuestion)
{
	global $g_rights_product;
	global $g_rights_users;
	global $g_propRights_global;
	global $g_propRights_product;
	
	
	// 20050819 - scs - we dont need to query the db for the rights every call
	//				 - so the rights are fetched only once per script 
	static $s_allRoles = null;
	static $s_userProductRoles = null;
	static $s_userTestPlanRoles = null;
	
	//load the rights
	if (is_null($s_allRoles))
		$s_allRoles = getRoles($db);
	if (is_null($s_userProductRoles))
	{
		$s_userProductRoles = getUserProductRoles($db,$_SESSION['userID']);
		$_SESSION['productRoles'] = $s_userProductRoles;
	}
	if (is_null($s_userTestPlanRoles))
	{
		$s_userTestPlanRoles = getUserTestPlanRoles($db,$_SESSION['userID']);
		$_SESSION['testPlanRoles'] = $s_userTestPlanRoles;
	}
	$globalRoleID = $_SESSION['roleId'];
	$globalRights = $s_allRoles[$globalRoleID]['rights'];
	$globalRights = explode(",",$globalRights);
	
	//check for testplan rights first
	$testPlanID = $_SESSION['testPlanId'];
	$userTestPlanRoles = $_SESSION['testPlanRoles'];

	if (isset($userTestPlanRoles[$testPlanID]))
	{
		$testPlanRoleID = $userTestPlanRoles[$testPlanID]['role_id'];
		$testPlanRights = $s_allRoles[$testPlanRoleID]['rights'];
		$testPlanRights = explode(",",$testPlanRights);
		
		propagateRights($globalRights,$g_propRights_global,$testPlanRights);
		propagateRights($globalRights,$g_propRights_product,$testPlanRights);
		return checkForRights($testPlanRights,$roleQuestion);
	}
	
	//check for product rights first
	$productID = $_SESSION['testprojectID'];
	$userProductRoles = $_SESSION['productRoles'];
	if (isset($userProductRoles[$productID]))
	{
		$productRoleID = $userProductRoles[$productID]['role_id'];
		$productRights = $s_allRoles[$productRoleID]['rights'];
		$productRights = explode(",",$productRights);
		
		propagateRights($globalRights,$g_propRights_global,$productRights);
		return checkForRights($productRights,$roleQuestion);
	}

	//check global rights last;
	return checkForRights($globalRights,$roleQuestion);
}

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
	// 20050819 - scs - extended to so we can check for the presence of multiple rights
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
?>