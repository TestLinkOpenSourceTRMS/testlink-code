<?php
/** TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * 
 * @filesource $RCSfile: fix_tplans.php,v $
 * @version $Revision: 1.6 $
 * @modified $Date: 2009/08/03 08:15:43 $  $Author: franciscom $
 * @author asielb
 *
 * fixes bug 1021
**/
 
require_once('../../config.inc.php');
require_once('users.inc.php');
require_once('testproject.class.php');
require_once('configCheck.php');

testlinkInitPage($db);
$template_dir='project/';

$can_manage_tprojects=has_rights($db,'mgt_modify_product');

// make sure the user has rights to manage test projects
if ($can_manage_tprojects)
{
	
	if($_POST)
	{
		foreach ($_POST as $testPlan => $testProject)
		{
			if ($testProject != "none")
			{
				echo "<br />changing test plan $testPlan to go with test project $testProject";			
				changeTestProjectForTestPlan($db, $testPlan, $testProject);
			}
		}
		echo "<hr>";
	}	
	
	$testPlans = getTestPlansWithoutProject($db);
	$testPlansCount = count($testPlans);
	
	$tpObj = new testproject($db);
	$testProjects = $tpObj->get_all();
	 
	$smarty = new TLSmarty();
	$smarty->assign('testPlans', $testPlans);
	$smarty->assign('testProjects', $testProjects);
	$smarty->assign('count', $testPlansCount);
	$smarty->display($template_dir . 'fix_tplans.tpl');
}
else
{
	echo "<p>".lang_get('fix_tplans_no_rights')."</p>";
}


function changeTestProjectForTestPlan(&$db, $testPlan, $testProject)
{
	$query = "UPDATE testplans SET testproject_id={$testProject} WHERE id={$testPlan}";
	$db->exec_query($query);
	echo "<br />Done changing test project";
}

?>