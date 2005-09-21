<?
/** TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: builds.inc.php,v 1.4 2005/09/21 10:32:00 franciscom Exp $
* 
* @author Martin Havlat
*
* Functions for Test Plan management - build related
*/
require_once('../../config.inc.php');
require_once("../functions/common.php");

/**
 * Collect all builds for the Test Plan
 *
 * 20050921 - fm - refactoring
 */
function getBuilds($idPlan)
{
 	$sql = "SELECT build.id, name FROM build WHERE projid = " . $idPlan . " ORDER BY build.id DESC";
	return getBuildInfo($sql);
}

function getBuildsAndNotes($idPlan)
{
  	$sql = "SELECT build.id,note FROM build WHERE projid = " . $idPlan . " ORDER BY build.id DESC";
	return getBuildInfo($sql);
}

function getBuildInfo($sql)
{
	$arrBuilds = array();
 	$result = do_mysql_query($sql) or die(mysql_error());

	while ($myrow = mysql_fetch_array($result))
	{
		$arrBuilds[$myrow[0]] = $myrow[1];
  }

 	return $arrBuilds;
}

function deleteTestPlanBuild($testPlanID,$buildID)
{
	$result = 1;
	if ($testPlanID)
	{ 
		$catIDs = null;
		getTestPlanCategories($testPlanID,$catIDs);
	
		// 20050914 - fm
		$tcIDs = getCategories_TC_ids($catIDs);	
		
		if (sizeof($tcIDs))
		{
			$tcIDList = implode(",",$tcIDs);
			
			$query = "DELETE FROM bugs WHERE tcid IN ({$tcIDList}) AND build = '{$buildID}'";
			$result = $result && do_mysql_query($query);
			
			
			$query = "DELETE FROM results WHERE tcid IN ({$tcIDList}) AND build = '{$buildID}'";
			$result = $result && do_mysql_query($query);
		}
	
		$query = "DELETE FROM build WHERE build.id={$buildID} AND projid=" . $testPlanID;
		$result = $result && do_mysql_query($query);
	}
	return $result ? 1 : 0;
}

?>