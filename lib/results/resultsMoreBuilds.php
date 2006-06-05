<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsMoreBuilds.php,v 1.21 2006/06/05 01:55:43 kevinlevy Exp $ 
*
* @author	Kevin Levy <kevinlevy@users.sourceforge.net>
* 
* This page will forward the user to a form where they can select
* the builds they would like to query results against.
*
* @author Francisco Mancardi - 20050912 - remove unused code
* @author Kevin Levy - 20060603 - starting 1.7 changes
*/
require('../../config.inc.php');
require_once('common.php');
require_once('builds.inc.php');
// allow us to retreive array of users 
require_once('plan.core.inc.php');
require_once('resultsMoreBuilds.inc.php');
require_once('../keywords/keywords.inc.php');
testlinkInitPage($db);

$prodID = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
$tpID = $_SESSION['testPlanId'];

$DEBUG = 1;

if ($DEBUG) {
  print "Work in progress - KL - 6/3/2006 <BR>";

  print "prodID = $prodID <BR>";
  print "tpID = $tpID <BR>";

  print "getExecutionsMap =";
  $mapOfResults = getExecutionsMap($db, $tpID);

  print_r($mapOfResults);
  
  print "<BR>";
 }

$arrBuilds = getBuilds($db,$tpID, " ORDER BY builds.name "); 
print "arrBuilds = ";
print_r($arrBuilds);
print "<BR>"; 

$arrOwners = getTestPlanUsers($db,$tpID);
print "arrOwners = ";
print_r($arrOwners);
print "<BR>";

$arrKeywords = selectKeywords($db,$prodID);
print "arrKeywords = ";
print_r($arrKeywords);
print "<BR>";

/** 
 * this function call is currently causing an error
 *$arrComponents = getArrayOfComponentNames($db,$tpID);
 *print "arrComponents = ";
 *print_r($arrComponents);
 *print "<BR>";
 **/


/**
$smarty = new TLSmarty();
$smarty->assign('testPlanName',$_SESSION['testPlanName']);
$smarty->assign('testplanid', $tpID);
$smarty->assign('arrBuilds', $arrBuilds); 
$smarty->assign('arrOwners', $arrOwners);
$smarty->assign('arrKeywords', $arrKeywords);
$smarty->assign('arrComponents', $arrComponents);
$smarty->display('resultsMoreBuilds_query_form.tpl');
**/

?>