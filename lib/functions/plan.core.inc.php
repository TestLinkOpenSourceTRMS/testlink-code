<?php
/**
 * TestLink Open Source Project - @link http://testlink.sourceforge.net/
 *  
 * @filesource $RCSfile: plan.core.inc.php,v $
 * @version $Revision: 1.39 $
 * @modified $Date: 2007/10/29 21:00:26 $ $Author: asielb $
 *  
 * 
 * @author 	Martin Havlat
 *
 *
 * rev:
 *       20070911 - asielb
 *      getTestPlansWithoutProject(&$db)
 *
 *      20070906 - franciscom - getAccessibleTestPlans() 
 *                              interface changes
 *
 *      20070821 - franciscom - BUGID: 951
**/

/*
  function: 

  args :
  
  returns: 

*/
function getAccessibleTestPlans(&$db,$testproject_id,$user_id=0,$filter_by_product=0,$tpID = null)
{
	$show_tp_without_prodid = config_get('show_tp_without_prodid');
	
	$my_user_id=$user_id;
	if( $user_id==0 )
	{
	  $my_user_id = $_SESSION['userID'];
	}
	
	$query = "SELECT nodes_hierarchy.id, nodes_hierarchy.name, testplans.active 
	         FROM nodes_hierarchy 
	         JOIN testplans ON nodes_hierarchy.id=testplans.id  
	         LEFT OUTER JOIN user_testplan_roles ON testplans.id = user_testplan_roles.testplan_id 
	         AND user_testplan_roles.user_id = {$my_user_id} WHERE active=1 AND  ";

	if ($filter_by_product)
		$query .= "(testproject_id = {$testproject_id} OR testproject_id = 0) AND ";
	
	$bGlobalNo = ($_SESSION['roleId'] == TL_ROLES_NONE);
	$bProductNo = 0;
	// BUGID: 951 - wrong key to access session info
	$analyse_global_role=1;
	if (isset($_SESSION['testprojectRoles'][$testproject_id]['role_id']))
	{
		$bProductNo = ($_SESSION['testprojectRoles'][$testproject_id]['role_id'] == TL_ROLES_NONE); 
	  $analyse_global_role=0;	
	}
	
  if( $bProductNo || ($analyse_global_role && $bGlobalNo))
  {
    $query .= "(role_id IS NOT NULL AND role_id != ".TL_ROLES_NONE.")";
  }	
  else
  {
    $query .= "(role_id IS NULL OR role_id != ".TL_ROLES_NONE.")";
  }
   
	if (!is_null($tpID))
		$query .= " AND nodes_hierarchy.id = {$tpID}";
		
	$query .= " ORDER BY name";


	$testPlans = $db->get_recordset($query);

	$arrPlans = null;
	for($i = 0;$i < sizeof($testPlans);$i++)
	{
		$testPlan = $testPlans[$i];
	 	if ($i == 0 && (!isset($_SESSION['testPlanId']) || !$_SESSION['testPlanId']))
		{
        	$_SESSION['testPlanId'] = $testPlan['id'];
	        $_SESSION['testPlanName'] = $testPlan['name'];
		}	
		
		$selected = null;
		if ($testPlan['id'] == $_SESSION['testPlanId'])
			$selected = 'selected="selected"';
		$arrPlans[] =  array( 'id' => $testPlan['id'], 
							  'name' => $testPlan['name'],
							  'selected' => $selected
							 );
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
function getNumberOfAccessibleTestPlans(&$db,$testproject_id, $user_id=0, $filter_by_product=0,$tpID = null)
{
  
	$tpData = getAccessibleTestPlans($db,$testproject_id, $user_id, $filter_by_product,$tpID);
	return sizeof($tpData);	
}

/**
 * Get list of users
 *
 * 20051222 - fm  - contribution by
 *
 * 20051203 - scs - added param tpID for getting only those user
 * 					which belong to a certain tp
 */
function getTestPlanUsers(&$db,$tpID)
{
	//@todo schlundus: code is not correct
	/*
	$show_realname = config_get('show_realname');
	
	$sql = " SELECT users.id, login ";
	if ($show_realname)
	{
	  $sql .= " ,first,last ";
	}
	$sql .= " FROM users,user_testplan_rights 
	          WHERE users.id = user_testplan_rights.user_id 
	          AND user_testplan_rights.testplan_id = {$tpID}";
             
	$result = $db->exec_query($sql);
	if ($result)
	{
		$data = null;
		while($rowUser = $db->fetch_array($result))
		{
			$data[$rowUser['id']] = $rowUser['login'];
			if ($show_realname)
			{
			  $data[$rowUser['id']] = format_username($rowUser);
			}
		}
	}
	return $data;
	*/
	return null;
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
// honors the configuration parameter show_tp_without_prodid
//
// 20051120 - fm - Interface Changed, added filter on product
// 20051121 - scs - added missing global $g_show_tp_without_prodid
// 20060114 - scs - correct wrong SQL Statement
//
function getAllTestPlans(&$db,$testproject_id=ALL_PRODUCTS,$plan_status=null,$filter_by_product=0, $tpID = null)
{
	$sql = " SELECT nodes_hierarchy.id, nodes_hierarchy.name, 
	                notes,active, testproject_id 
	         FROM nodes_hierarchy,testplans";
	$where = " WHERE nodes_hierarchy.id=testplans.id ";
	
	// 20051120 - fm
	if($filter_by_product)
	{
		if ($testproject_id != ALL_PRODUCTS)
		{
			$where .= ' AND (testproject_id = ' . $testproject_id . " ";  	
			if (config_get('show_tp_without_tproject_id'))
			{
				$where .= " OR testproject_id = 0 ";
			}
			$where .= " ) ";
		}
	}
	
	if(!is_null($plan_status))
	{	
		$my_active = to_boolean($plan_status);
		$where .= " AND active = " . $my_active;
	}
	if (!is_null($tpID))
		$where .= " AND testplans.id = " . $tpID;
	
	$sql .= $where . " ORDER BY name";

	return $db->get_recordset($sql);
}

// 20051120 - fm
// interface changes
function getAllActiveTestPlans(&$db,$testproject_id = ALL_PRODUCTS,$filter_by_product = 0)
{
	return getAllTestPlans($db,$testproject_id,TP_STATUS_ACTIVE,$filter_by_product);
}

// ------------------------------------------------------------
// 20050810 - fm
// Checks if the testproject_id is tp's father
function check_tp_father(&$db,$testproject_id,$tpID)
{
  $ret = 0;
	$sql = " SELECT id, name, notes , active, testproject_id " .
	       " FROM testplans " . 
	       " WHERE testplans.id=" . $tpID .
	       " AND   testplans.testproject_id=" . $testproject_id;
	       
	$rs = selectData($db,$sql);
	
	if( sizeof($rs) == 1)
  {
  	$ret = 1;
	}       
	return($ret);
}
// ------------------------------------------------------------

// ------------------------------------------------------------
// 20050926 - fm
// 
function get_tp_father(&$db,$tpID)
{
  $ret = 0;
	$sql = " SELECT id, name, notes , active, testproject_id " .
	       " FROM testplans TP" . 
	       " WHERE TP.id=" . $tpID;
	       
	       
	$rs = selectData($db,$sql);
	return($rs[0]['testproject_id']);
}
// ------------------------------------------------------------





/*
20050914 - fm - interface changes

*/
function dispCategories(&$db,$idPlan, $keyword, $resultCat) 
{
	$arrData = array();
	
	while($rowCAT = $db->fetch_array($resultCat))
	{ 
		$arrTestCases = array();					
		$idCAT = $rowCAT[0];
		$nameCAT = $rowCAT[1];

		$sqlTC = "SELECT id, title FROM mgttestcase " .
		         "WHERE catid=" . $idCAT;
		         
	
		
		//Check the keyword that the user has submitted.
		if($keyword != 'NONE')
		{
			$keyword = $db->prepare_string($keyword);
			//keywordlist always have a trailing slash, so there are only two cases to consider 
			//the keyword is the first in the list
			//or its in the middle of list 		 
			$sqlTC .= " AND (keywords LIKE '%,{$keyword},%' OR keywords like '{$keyword},%') ";
		}
		$sqlTC .= " ORDER BY TCorder,id";

		$resultTC = $db->exec_query($sqlTC);
		
		while($rowTC = $db->fetch_array($resultTC))
		{ 
			//Display all test cases
			$idTC = $rowTC['id']; 
			$titleTC = $rowTC['title']; 
			
			//Displays the test case name and a checkbox next to it
			//
			// 20050807 - fm - $idPlan
			
			$sqlCheck = " SELECT mgttcid FROM testplans,component,category,testcase " .
			            " WHERE mgttcid=" . $idTC . 
			            " AND testplans.id=component.projid AND component.id=category.compid AND " .
			            " category.id=testcase.catid AND testplans.id=" . $idPlan;
			$checkResult = $db->exec_query($sqlCheck);
			$checkRow = $db->num_rows($checkResult);
			
			array_push($arrTestCases, array( 'id' => $idTC, 'name' => $titleTC,
											                 'added' => $checkRow));
		}
		
		array_push($arrData, array( 'id' => $idCAT, 'name' => $nameCAT,
									              'tc' => $arrTestCases));
	}
	
	return $arrData;
}

// 20070911 - azl
// 20071029 - azl - modified to only get active test plans bug # 1148
function getTestPlansWithoutProject(&$db)
{
	$query = "select id,name from nodes_hierarchy WHERE id IN(SELECT id FROM testplans
				WHERE testproject_id=0 and ACTIVE=1)";
	$testPlans = $db->get_recordset($query);
	return $testPlans;
}


?>