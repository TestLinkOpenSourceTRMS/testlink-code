<?
/**
 * TestLink Open Source Project - @link http://testlink.sourceforge.net/
 *  
 * @filesource $RCSfile: plan.core.inc.php,v $
 * @version $Revision: 1.15 $
 * @modified $Date: 2005/11/24 21:27:13 $ $Author: schlundus $
 *  
 * 
 * @author 	Martin Havlat
 *
 * Collect Test Plan information
 * @todo common.php includes related function getUserTestPlan (move it here)
 *
 *
 * @author 20050928 - fm - getTestPlans() interface changes 
 * @author 20050926 - fm - get_tp_father() 
 * @author 20050904 - fm 
 * TL 1.5.1 compatibility, get also Test Plans without product id.
 *
 * @author 20050813 - fm product filter, added getCountTestPlans4UserProd()
 * @author 20050809 - fm added getCountTestPlans4UserProd()
 * @author 20050809 - fm getTestPlans(), added filter on prodid
 * @author 20050807 - fm refactoring:  removed deprecated: $_SESSION['project']
 * @author 20051012 - azl optimize getTestPlans() function sql queries.
**/

/**
 * Take data of all the available Test Plans
 * @return array select list 
 * @todo refactorize this function via selectOptionData($sql); use one sql instead of two 
 *
 *
 * rev :
 *      20050928 - fm
 *      added argument $filter_by_product
 *
 *      20050904 - fm 
 *      TL 1.5.1 compatibility, get also Test Plans without product id.
 *
 *      20050810 - fm
 *      Removed Global Coupling:
 *      ($_SESSION['productID'], $_SESSION['userID'])
 *
 *      20050809 - fm
 *      changes in active field type now is boolean
 *      added filter by product id
 *
 *      MHT 20050707 order by name
 */
function getTestPlans($productID, $userID, $filter_by_product=0)
{
	global $g_show_tp_without_prodid;
 	$arrPlans = array();
	
	// 20050809 - fmm
	// added filter by product id
	// 20051012 - azl
	// removed join with projrights table because it was slowing down query signifigantly and 
	// it wasn't being used. Also removed selecting notes field because it isn't needed. 
	// 
	$sql = " SELECT DISTINCT id,name,active,prodid FROM project " .
			           " WHERE active=1 ";
			           
	// 20050928 - fm
	if ( $filter_by_product )
	{
	   $sql .= " AND prodid=" . $productID;

		// 20050904 - fm - TL 1.5.1 compatibility, get also Test Plans without product id.		           
  	if ($g_show_tp_without_prodid)
  	{
  		$sql .= " OR prodid=0 ";
		}
	}
	
	$sql .= " ORDER BY name";
			           
	$result = do_mysql_query($sql);

	if ($result) {
    	$testplanCount = mysql_num_rows($result);
	} else {
		  $testplanCount = 0;
	}
	if($testplanCount > 0) {

      $cAvailablePlans = 0;  // count the available plans
      while ($myrow = mysql_fetch_row($result))
      {
        //Block of code will determines if the user has the appropriate rights to view available projects
        $sqlProjRights = "select projid from projrights where userid=" . $userID . 
                         " and projid=" . $myrow[0];
        $projRightsResult = do_mysql_query($sqlProjRights);
        $myrowProjRights = mysql_fetch_row($projRightsResult);

        //If the user has the rights to the project/test plan show it
        if($myrowProjRights[0] == $myrow[0])
        {
            //This code block checks to see if the user has already selected 
            //a project once before and sets the default to that.. Bug 11453
            // If this is the first plan we're displaying,
            // and no session project has been set yet, then set it.
			//20050810 - am - added check if a testPlanID is set
            if ($cAvailablePlans == 0 && (!isset($_SESSION['testPlanId']) || !$_SESSION['testPlanId'])) {
				        $_SESSION['testPlanId'] = $myrow[0];
				        $_SESSION['testPlanName'] = $myrow[1];
            }

            $cAvailablePlans++;

            if($myrow[0] == $_SESSION['testPlanId']) { //did I choose this selection last
				array_push($arrPlans, array( 'id' => $myrow[0], 'name' => $myrow[1],
						'notes' => $myrow[2], 'active' => $myrow[3], 
						'selected' => 'selected="selected"'));
            } else { //Else just display the value
				array_push($arrPlans, array( 'id' => $myrow[0], 'name' => $myrow[1],
						'notes' => $myrow[2], 'active' => $myrow[3], 
						'selected' => ''));
            }
        }
   	  }//END WHILE
	}//end testplan count

	return $arrPlans;
}


/**
 * get count Test Plans available for user
 *
 * 20050810 - fm
 * changes need due to ACTIVE FIELD type change
 * interface changes
 *
 */
function getCountTestPlans4User($userID)
{
	$sql = "SELECT count(project.id) FROM project,projrights WHERE active=1" .  
			" AND projid=project.id AND userid=" . $userID;
	$result = do_mysql_query($sql);
	
	if ($result){
		return mysql_result($result, 0);
	} else {
		return null;
	}
}





/**
 * get count Test Plans available for user and Product
 *
 * 20050904 - fm - TL 1.5.1 compatibility, show also Test Plans without product id.
 *
 * 20050813 - fm - product filter
 * 
 * 20050810 - fm
 * changes need due to ACTIVE FIELD type change interface changes
 */
function getCountTestPlans4UserProd($userID,$prodID=null)
{
	global $g_show_tp_without_prodid;
	
	$sql = "SELECT count(project.id) FROM project,projrights WHERE active=1" .  
		   " AND projid=project.id AND userid=" . $userID;
	
	//20051015 - am - removed negation of $prodID		   
	if ($prodID)
	{		   
		$sql .= " AND project.prodid=" . $prodID;
		
		// 20050904 - fm - TL 1.5.1 compatibility, get also Test Plans without product id.
		if ($g_show_tp_without_prodid)
		{
			$sql .= " OR project.prodid=0";
		}  	
	}		   
	$result = do_mysql_query($sql);
	
	if ($result){
		return mysql_result($result, 0);
	} else {
		return null;
	}
}

/**
 * Get list of users
 * @todo only users valid for the project should be collected
 * @todo ? DELETE - should be used user.inc.php
 */
function getTestPlanUsers()
{
	$sqlUser = "select login from user";
	$resultUser = @do_mysql_query($sqlUser);
	$i = 0;
	while($rowUser = mysql_fetch_array($resultUser)){
		$data[$i++] = $rowUser[0];
	}
	
	return $data;
}


// Get All Test Plans for a product
// 
//
// [prodID]: numeric
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
//
function getAllTestPlans($prodID=ALL_PRODUCTS,$plan_status=null,$filter_by_product=0)
{
	global $g_show_tp_without_prodid;
		  
	$show_tp_without_prodid = config_get('show_tp_without_prodid');
	
	$sql = "SELECT id, name, notes,active, prodid FROM project";
	$where = ' WHERE 1=1';
	
	// 20051120 - fm
	if( $filter_by_product )
	{
		if ($prodID != ALL_PRODUCTS)
		{
			$where .= ' AND prodid=' . $prodID . " ";  	
			if ($g_show_tp_without_prodid)
			{
				$where .= " OR prodid=0 ";
			}
		}
	}
	
	if( !is_null($plan_status) )
	{	
		$my_active = to_boolean($plan_status);
		$where .= " AND active=" . $my_active;
	}
	$sql .= $where . " ORDER BY name";
	
	return selectData($sql);
}



// 20051120 - fm
// interface changes
function getAllActiveTestPlans($prodID=ALL_PRODUCTS,$filter_by_product=0)
{
	return getAllTestPlans($prodID,TP_STATUS_ACTIVE,$filter_by_product);
}



// ------------------------------------------------------------
// 20050810 - fm
// Checks if the prodID is tp's father
function check_tp_father($prodID,$tpID)
{
  $ret = 0;
	$sql = " SELECT id, name, notes , active, prodid " .
	       " FROM project " . 
	       " WHERE project.id=" . $tpID .
	       " AND   project.prodid=" . $prodID;
	       
	$rs = selectData($sql);
	
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
function get_tp_father($tpID)
{
  $ret = 0;
	$sql = " SELECT id, name, notes , active, prodid " .
	       " FROM project TP" . 
	       " WHERE TP.id=" . $tpID;
	       
	       
	$rs = selectData($sql);
	return($rs[0]['prodid']);
}
// ------------------------------------------------------------





/*
20050914 - fm - interface changes

*/
function dispCategories($idPlan, $keyword, $resultCat) 
{
	$arrData = array();
	
	while($rowCAT = mysql_fetch_array($resultCat))
	{ 
		$arrTestCases = array();					
		$idCAT = $rowCAT[0];
		$nameCAT = $rowCAT[1];

		$sqlTC = "SELECT id, title FROM mgttestcase " .
		         "WHERE catid=" . $idCAT;
		         
	
		
		//Check the keyword that the user has submitted.
		if($keyword != 'NONE')
		{
			$keyword = mysql_escape_string($keyword);
			//keywordlist always have a trailing slash, so there are only two cases to consider 
			//the keyword is the first in the list
			//or its in the middle of list 		 
			$sqlTC .= " AND (keywords LIKE '%,{$keyword},%' OR keywords like '{$keyword},%') ";
		}
		$sqlTC .= " ORDER BY TCorder,id";

		$resultTC = do_mysql_query($sqlTC);
		
		while($rowTC = mysql_fetch_array($resultTC))
		{ 
			//Display all test cases
			$idTC = $rowTC['id']; 
			$titleTC = $rowTC['title']; 
			
			//Displays the test case name and a checkbox next to it
			//
			// 20050807 - fm - $idPlan
			
			$sqlCheck = " SELECT mgttcid FROM project,component,category,testcase " .
			            " WHERE mgttcid=" . $idTC . 
			            " AND project.id=component.projid AND component.id=category.compid AND " .
			            " category.id=testcase.catid AND project.id=" . $idPlan;
			$checkResult = do_mysql_query($sqlCheck);
			$checkRow = mysql_num_rows($checkResult);
			
			array_push($arrTestCases, array( 'id' => $idTC, 'name' => $titleTC,
											                 'added' => $checkRow));
		}
		
		array_push($arrData, array( 'id' => $idCAT, 'name' => $nameCAT,
									              'tc' => $arrTestCases));
	}
	
	return $arrData;
}

?>