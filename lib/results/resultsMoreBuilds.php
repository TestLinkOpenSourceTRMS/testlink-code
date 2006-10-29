<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsMoreBuilds.php,v 1.43 2006/10/29 06:44:16 kevinlevy Exp $ 
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
// used to retrieve users 
require_once('../functions/users.inc.php');
testlinkInitPage($db);

$prodID = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$tpID = isset($_SESSION['testPlanId']) ? $_SESSION['testPlanId'] : 0 ;
$tplanName = isset($_SESSION['testPlanName']) ? $_SESSION['testPlanName'] : null;

define('ALL_USERS_FILTER', null);
define('ADD_BLANK_OPTION', false);
$arrOwners = get_users_for_html_options($db, ALL_USERS_FILTER, ADD_BLANK_OPTION);
/**
print "arrOwners = <BR>";
print_r($arrOwners);
print "<BR>";
*/

$tp = new testplan($db);
//$tree = new tree($db);
$builds_to_query = -1;
$suitesSelected = 'all';
$re = new results($db, $tp, $suitesSelected, $builds_to_query);

$arrKeywords = $tp->get_keywords_map($tpID); 
$arrBuilds = $tp->get_builds($tpID); 
/**
print "arrBuilds = <BR>";
print_r($arrBuilds);
print "<BR>";
*/

$arrComponents = $re->getTopLevelSuites();
/**
print "arrComponents = <BR>";
print_r($arrComponents);
print "<BR>";
*/
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
$smarty->assign('arrOwners', $arrOwners);
$smarty->display('resultsMoreBuilds_query_form.tpl');
?>