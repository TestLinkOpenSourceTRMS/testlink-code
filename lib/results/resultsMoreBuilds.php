<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsMoreBuilds.php,v 1.32 2006/08/06 05:57:03 kevinlevy Exp $ 
*
* @author	Kevin Levy <kevinlevy@users.sourceforge.net>
* 
* This page will forward the user to a form where they can select
* the builds they would like to query results against.
*
* @author Francisco Mancardi - 20050912 - remove unused code
*
*/
require('../../config.inc.php');
require_once('common.php');
require_once('builds.inc.php');
// allow us to retreive array of users 
require_once('plan.core.inc.php');
require_once('resultsMoreBuilds.inc.php');
require_once('../keywords/keywords.inc.php');
require_once('../functions/results.class.php');
require_once('../functions/testplan.class.php');
require_once('../functions/tree.class.php');


testlinkInitPage($db);

$prodID = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$tpID = isset($_SESSION['testPlanId']) ? $_SESSION['testPlanId'] : 0 ;

$tp = new testplan($db);
$tree = new tree($db);
$re = new results($db, $tp, $tree, $prodID);

$arrKeywords = $tp->get_keywords_map($tpID); 
//print "print out keywords : <BR>";
//print_r($arrKeywords);

$arrBuilds = $tp->get_builds($tpID); 

//$arrBuilds = getBuilds($tpID, " ORDER BY build.name "); 
//$arrOwners = getTestPlanUsers($tpID);

$arrComponents = $re->getTopLevelSuites();


$smarty = new TLSmarty();
$smarty->assign('testPlanName',$_SESSION['testPlanName']);
$smarty->assign('projectid', $_SESSION['testPlanId']);
$smarty->assign('arrBuilds', $arrBuilds); 

//$smarty->assign('arrOwners', $arrOwners);
$smarty->assign('arrKeywords', $arrKeywords);
$smarty->assign('arrComponents', $arrComponents);

// kl - 10182005 debug
//print "resultMoreBuilds.php array of components = ";
//print_r($arrComponents);

$smarty->display('resultsMoreBuilds_query_form.tpl');

?>