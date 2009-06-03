<?php
/**
 * TestLink Open Source Project - @link http://testlink.sourceforge.net/
 *  
 * @filesource $RCSfile: plan.core.inc.php,v $
 * @version $Revision: 1.48 $
 * @modified $Date: 2009/06/03 21:18:24 $ $Author: franciscom $
 *  
 * 
 * @author 	Martin Havlat
 *
 *
 * rev: 20081218 - franciscom - TL_ROLES_NO_RIGHTS
 *      20070821 - franciscom - BUGID: 951
**/



/** @TODO havlatm: (obsolete file) move to class testplan; check/extend for example: $testplan->getTestPlanNames() */





/*
  function: 

  args :
  
  returns: 

*/
function getAccessibleTestPlans(&$db,$testproject_id,$user_id=0,$tpID = null)
{
	$currentUser = $_SESSION['currentUser'];
	
	$my_user_id = $user_id ? $user_id : $currentUser->dbID;
	
	$sql = "SELECT nodes_hierarchy.id, nodes_hierarchy.name, testplans.active 
	         FROM nodes_hierarchy 
	         JOIN testplans ON nodes_hierarchy.id=testplans.id  
	         LEFT OUTER JOIN user_testplan_roles ON testplans.id = user_testplan_roles.testplan_id 
	         AND user_testplan_roles.user_id = {$my_user_id} WHERE active=1 AND  ";

	$sql .= " testproject_id = {$testproject_id} AND ";
	
	$bGlobalNo = ($currentUser->globalRoleID == TL_ROLES_NO_RIGHTS);
	$bProductNo = 0;
	$analyse_global_role = 1;
	if (isset($currentUser->tprojectRoles[$testproject_id]->dbID))
	{
		$bProductNo = ($currentUser->tprojectRoles[$testproject_id]->dbID == TL_ROLES_NO_RIGHTS); 
		$analyse_global_role = 0;	
	}
	
  if( $bProductNo || ($analyse_global_role && $bGlobalNo))
  {
    $sql .= "(role_id IS NOT NULL AND role_id != ".TL_ROLES_NO_RIGHTS.")";
  }	
  else
  {
    $sql .= "(role_id IS NULL OR role_id != ".TL_ROLES_NO_RIGHTS.")";
  }
   
	if (!is_null($tpID))
	{
		$sql .= " AND nodes_hierarchy.id = {$tpID}";
	}
		
	$sql .= " ORDER BY name";

	$testPlans = $db->get_recordset($sql);
	$arrPlans = null;
  $tplanQty=sizeof($testPlans);
	for($idx = 0; $idx < $tplanQty ;$idx++)
	{
		$testPlan = $testPlans[$idx];
	 	if ($idx == 0 && (!isset($_SESSION['testPlanId']) || !$_SESSION['testPlanId']))
		{
        	$_SESSION['testPlanId'] = $testPlan['id'];
	        $_SESSION['testPlanName'] = $testPlan['name'];
		}	
	
		$selected = ($testPlan['id'] == $_SESSION['testPlanId']) ? 'selected="selected"' : null ;
		$arrPlans[] =  array( 'id' => $testPlan['id'], 'name' => $testPlan['name'],
							            'selected' => $selected);
	}
	
	if (!sizeof($testPlans))
	{
		  unset($_SESSION['testPlanId']);
	    unset($_SESSION['testPlanName']);
	}
	
	return $arrPlans;
}

/**
 * get count Test Plans available for user and Product
 */
function getNumberOfAccessibleTestPlans(&$db,$testproject_id, $user_id=0,$tpID = null)
{
  
	$tpData = getAccessibleTestPlans($db,$testproject_id, $user_id,$tpID);
	return sizeof($tpData);	
}


// Get All Test Plans for a product
// 
//
// [testproject_id]: numeric
//           default: 0 => don't filter by product ID
//
// [plan_status]: boolean
//                default: null => get active and inactive TP
//                        
// [filter_by_product]: boolean
//                      default: 0 => don't filter by product ID
//
//
function getAllTestPlans(&$db,$testproject_id=ALL_PRODUCTS,$plan_status=null,$tpID = null)
{
	$sql = " SELECT nodes_hierarchy.id, nodes_hierarchy.name, 
	                notes,active, testproject_id 
	         FROM nodes_hierarchy,testplans";
	$where = " WHERE nodes_hierarchy.id=testplans.id ";
	
	if ($testproject_id != ALL_PRODUCTS)
  {
			$where .= " AND testproject_id = {$testproject_id} ";  	
	}
	
	if(!is_null($plan_status))
	{	
		$my_active = to_boolean($plan_status);
		$where .= " AND active = " . $my_active;
	}
	
	if (!is_null($tpID))
	{
		$where .= " AND testplans.id = " . $tpID;
	}
	
	$sql .= $where . " ORDER BY name";

	return $db->get_recordset($sql);
}

// 20051120 - fm
// interface changes
function getAllActiveTestPlans(&$db,$testproject_id = ALL_PRODUCTS)
{
	return getAllTestPlans($db,$testproject_id,TP_STATUS_ACTIVE);
}

// 20070911 - azl
// 20071029 - azl - modified to only get active test plans bug # 1148
function getTestPlansWithoutProject(&$db)
{
    $tables['nodes_hierarchy'] = DB_TABLE_PREFIX . 'nodes_hierarchy';
    $tables['testplans'] = DB_TABLE_PREFIX . 'testplans';
    
	$sql = "select id,name from {$tables['nodes_hierarchy']} WHERE id " . 
	       " IN( SELECT id FROM {$tables['testplans']}  " .
		   " WHERE testproject_id=0 and active=1)";
	$testPlans = $db->get_recordset($sql);
	return $testPlans;
}


?>