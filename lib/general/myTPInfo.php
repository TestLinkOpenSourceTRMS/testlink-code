<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: myTPInfo.php,v $
 *
 * @version $Revision: 1.6 $
 * @modified $Date: 2005/12/31 14:38:10 $ $Author: schlundus $
 *
 * @author Martin Havlat
 *
 * Defines functions used to get info about a users testPlans
 * 
 * 20051001 - scs - changes for build_id column
 * 20051231 - scs - changes for active state of users
 *
**/
function printMyTPData()
{
    $myData = '';
 	$metrics = getMetrics();
 	if (sizeof($metrics))
 	{
 		foreach($metrics as $project=>$metric)
 		{
			$myData .= "<tr><td class=\"mainMenu\">" . htmlspecialchars($metric[3]) . "</td><td class=\"mainMenu\">" . 
                         $metric[1] . "</td><td class=\"mainMenu\">" . $metric[2] . "</td></tr>";
 			
 		}
	}
    else 
    {
        $myData .= "<tr><td class=\"mainMenu\"><font color=\"#FF0000\">".lang_get("no_testplans_available")."</font>
                    </td><td class=\"mainMenu\"><font color=\"#FF0000\">---</font></td><td class=\"mainMenu\">
                    <font color=\"#FF0000\">---</font></td></tr>";
    }
    return $myData;
}

/**
* This function does the bulk of the work in determining the calculations for testcases.
* 20051231 - scs - fixed ambigious column name 
*/
function getMetrics()
{
    $sql = " SELECT project.name,project.id FROM project,projrights,user where ".
           " project.id=projrights.projid AND user.id=projrights.userid AND project.active=1 AND ".
           " user.id=" . $_SESSION['userID'];
    $result = do_sql_query($sql);
	$metrics = null;
	$projects = null;
	while($row = $GLOBALS['db']->fetch_array($result))
	{
		$metrics[$row[1]] = array(0,0,0,$row[0]);
		$projects[] = $row[1];
	}
	
	if (!sizeof($projects))
		return null;
	$projectList = implode(",",$projects);
	
    $sql = " SELECT COUNT(testcase.mgttcid),projID " .
           " FROM project,component,category,testcase " .
           " WHERE project.id = component.projid " .
           " AND component.id = category.compid " .
           " AND category.id = testcase.catid " .
           " AND projID IN ({$projectList}) GROUP BY projId ";
		   
	$result = do_sql_query($sql);
	while($row = $GLOBALS['db']->fetch_array($result))
		$metrics[$row[1]][0] = $row[0];
	$tcInfo = null;
	$sql = " SELECT projID,tcid,status " .
	       " FROM results,project,component,category,testcase " .
	       " WHERE project.id = component.projid " .
	       " AND component.id = category.compid " .
	       " AND category.id = testcase.catid " .
	       " AND testcase.id = results.tcid " .
	       " AND projID IN ({$projectList}) ORDER BY projID,tcID,build_id";
	getTCInfo($sql,$tcInfo);
	calculateMetrics($metrics,$tcInfo,1);
	
	$sql = "SELECT projID,tcid,status FROM results,project,component,category,testcase where ".
         "project.id = component.projid AND component.id = category.compid AND category.id = testcase.catid and testcase.id = ".
         "results.tcid AND owner = '".$GLOBALS['db']->prepare_string($_SESSION['user'])."' AND projID IN ({$projectList}) ORDER BY projID,tcID,build_id";

	$myTcInfo = null;
	getTCInfo($sql,$myTcInfo);
	calculateMetrics($metrics,$myTcInfo,2);

	return $metrics;
}

function calculateMetrics(&$metrics,&$myTcInfo,$index)
{
	if (sizeof($myTcInfo))
	{
		foreach($myTcInfo as $projID => $tcs)
		{
			$tcs = array_count_values($tcs);	
			$stat = @$tcs["p"] + @$tcs["f"] + @$tcs["b"];
			$total = $metrics[$projID][0];
			$stat = round(($stat /$total)*100,2);
			$metrics[$projID][$index] = $stat;
		}
	}
}

function getTCInfo($sql,&$tcInfo)
{
	$result = do_sql_query($sql);
	$tcInfo = null;
	if ($result)
	{   
		while($row = $GLOBALS['db']->fetch_array($result))
			$tcInfo[$row[0]][$row[1]] = $row[2];
	}
}
?>
