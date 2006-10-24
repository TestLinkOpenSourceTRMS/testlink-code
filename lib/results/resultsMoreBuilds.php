<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsMoreBuilds.php,v 1.40 2006/10/24 21:51:00 kevinlevy Exp $ 
*
* @author	Kevin Levy <kevinlevy@users.sourceforge.net>
* 
* This page will forward the user to a form where they can select
* the builds they would like to query results against.
*
* @author Francisco Mancardi - 20050912 - remove unused code
**/
require('../../config.inc.php');
require_once('common.php');
require_once('builds.inc.php');
require_once('../functions/results.class.php');
testlinkInitPage($db);

$prodID = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$tpID = isset($_SESSION['testPlanId']) ? $_SESSION['testPlanId'] : 0 ;
$tplanName = isset($_SESSION['testPlanName']) ? $_SESSION['testPlanName'] : null;

$tp = new testplan($db);
//$tree = new tree($db);
$builds_to_query = -1;
$suitesSelected = 'all';
$re = new results($db, $tp, $suitesSelected, $builds_to_query, $prodID, $tpID);

$arrKeywords = $tp->get_keywords_map($tpID); 
$arrBuilds = $tp->get_builds($tpID); 
$arrComponents = $re->getTopLevelSuites();
$mapOfSuiteSummary = $re->getAggregateMap();

// $count = count($arrComponents);
// print "count = $count <BR>";
// print_r($arrComponents);

/**
$revisedArrComponents;
while ($key = key($arrComponents)){
  $currentId = $arrComponents[$key][id];
  //  print "currentId = $currentId ";
  if ($mapOfSuiteSummary[$currentId]){
    //print "in the map of results <BR>";
    $revisedArrComponents[$key] = $arrComponents[$key];
  }
  else {
    //print "not in the results <BR>";
    
  }

  next($arrComponents);
}
*/

while ($key2 = key($mapOfSuiteSummary)){
  // print "key2 = $key2 <BR>";
  next ($mapOfSuiteSummary);
 }

$smarty = new TLSmarty();
$smarty->assign('testPlanName',$tplanName);
$smarty->assign('arrBuilds', $arrBuilds); 
$smarty->assign('arrKeywords', $arrKeywords);
$smarty->assign('arrComponents', $arrComponents);
$smarty->display('resultsMoreBuilds_query_form.tpl');
?>