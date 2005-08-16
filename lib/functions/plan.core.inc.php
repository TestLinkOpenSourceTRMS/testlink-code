<?
/**
 * TestLink Open Source Project - @link http://testlink.sourceforge.net/
 *  
 * @filesource $RCSfile: plan.core.inc.php,v $
 * @version $Revision: 1.2 $
 * @modified $Date: 2005/08/16 18:00:55 $ $Author: franciscom $
 *  
 * 
 * @author 	Martin Havlat
 *
 * Collect Test Plan information
 * @todo common.php includes related function getUserTestPlan (move it here)
 *
 *
 * @author 20050813 - fm product filter, added getCountTestPlans4UserProd()
 *
 *
 * @author 20050809 - fm added getCountTestPlans4UserProd()
 *
 * @author 20050809 - fm getTestPlans(), added filter on prodid
 *
 *
 * @author 20050807 - fm refactoring:  removed deprecated: $_SESSION['project']
**/
/**
 * Take data of all the available Test Plans
 * @return array select list 
 * @todo refactorize this function via selectOptionData($sql); use one sql instead of two 
 *
 *
 * rev :
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
function getTestPlans($productID, $userID)
{
	
 	$arrPlans = array();
	
	// 20050809 - fmm
	// added filter by product id
	//
	$queryString = " SELECT DISTINCT id,name,notes,active,prodid FROM project,projrights " .
			           " WHERE active=1 AND prodid=" . $productID .
			           " ORDER BY name";
			           
	$result = do_mysql_query($queryString);

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
            if ($cAvailablePlans == 0 && !$_SESSION['testPlanId']) {
            	  // 20050807 - fm
                // $_SESSION['project'] = $myrow[0];
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
 *
 * 20050813 - fm
 * product filter
 * 
 * 20050810 - fm
 * changes need due to ACTIVE FIELD type change
 * interface changes
 *
 */
function getCountTestPlans4UserProd($userID,$prodID=null)
{
	$sql = "SELECT count(project.id) FROM project,projrights WHERE active=1" .  
			   " AND projid=project.id AND userid=" . $userID;
			   
	if (!$prodID)
	{		   
		$sql .= " AND project.prodid=" . $prodID;
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
function getProjectUsers()
{
	$sqlUser = "select login from user";
	$resultUser = @do_mysql_query($sqlUser);
	$i = 0;
	while($rowUser = mysql_fetch_array($resultUser)){
		$data[$i++] = $rowUser[0];
	}
	
	return $data;
}


// 20050810 - fm
// added optional parameter
//
// args:
//      [plan_status]: boolean
//                     default: null => get active and inactive TP
//                                      
function getAllTestPlans($plan_status=null)
{

	$sql = "SELECT id, name, notes,active, prodid FROM project";
  $where ='';

	if( !is_null($plan_status) )
  {	
    $my_active = to_boolean($plan_status);
    $where = " WHERE active=" . $my_active;
	}
  $sql .= $where;
	
	return selectData($sql);
}


// 20050810 - fm
function getAllActiveTestPlans()
{
	// 20050810 - fm
	//$sql = "SELECT id, name, notes,active, prodid FROM project WHERE active='y'";
	$active_tp=1;
	return getAllTestPlans($active_tp);
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
	
	/*
	echo "<pre> check_tp";
	echo $sql;
	print_r($rs);
	echo "</pre>";
	exit;
	*/
	if( sizeof($rs) == 1)
  {
  	$ret = 1;
	}       
	return($ret);
}
// ------------------------------------------------------------



?>