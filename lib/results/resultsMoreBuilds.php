<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* $Id: resultsMoreBuilds.php,v 1.23 2006/06/05 05:33:34 kevinlevy Exp $ 
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

$DEBUG = 0;

$mapOfResults = getExecutionsMap($db, $tpID);

print "Work in progress - upgrading to 1.7 - KL - 6/3/2006 <BR>";

if ($DEBUG) {
  print "prodID = $prodID <BR>";
  print "tpID = $tpID <BR>";

  //  $keys = array_keys($mapOfResults);

  print "<row><th>id</th>
                <th>build_id</th>
                <th>tester_id</th>
                <th>execution_ts</th>
                <th>status</th>
                <th>testplan_id</th>
                <th>tcversion_id</th>
                <th>notes</th></row>";
    print "<BR/>";
  while ($key = key($mapOfResults)){
    //    print "execution # " . $key . "<br />";
    $resultArray2 = $mapOfResults[$key];

    //    $key2 = key($resultArray);

    //$resultArray2 = $resultArray[$key2];
    $id = $resultArray2[id];
    $build_id = $resultArray2[build_id];
    $tester_id = $resultArray2[tester_id];
    $execution_ts = $resultArray2[execution_ts];
    $status = $resultArray2[status];
    $testplan_id = $resultArray2[testplan_id];
    $tcversion_id = $resultArray2[tcversion_id];
    $notes = $resultArray2[notes];
    
    
    print "<row><td>$id</td>
                <td>$build_id</td>
                <td>$tester_id</td> 
                <td>$execution_ts</td>
                <td>$status</td>
                <td>$testplan_id</td>
                <td>$tcversion_id</td>
                <td>$notes</td></row>";
    print "<BR/>";
    //    print_r($resultArray2);
    
    next($mapOfResults);
  } // end while loop

  //  print_r($mapOfResults);

 } // end if DEBUG


$arrBuilds = getBuilds($db,$tpID, " ORDER BY builds.name "); 
//print "arrBuilds = ";
//print_r($arrBuilds);
//print "<BR>"; 

/** not working  - comment out for now
until we figure out exactly what is needed
$arrOwners = getTestPlanUsers($db,$tpID);
print "arrOwners = ";
print_r($arrOwners);
print "<BR>";

$arrKeywords = selectKeywords($db,$prodID);
print "arrKeywords = ";
print_r($arrKeywords);
print "<BR>";
*/

/** 
 * this function call is currently causing an error
 *$arrComponents = getArrayOfComponentNames($db,$tpID);
 *print "arrComponents = ";
 *print_r($arrComponents);
 *print "<BR>";
 **/

$smarty = new TLSmarty();
$smarty->assign('testPlanName',$_SESSION['testPlanName']);
$smarty->assign('testplanid', $tpID);
$smarty->assign('arrBuilds', $arrBuilds); 
$smarty->assign('mapOfResults', $mapOfResults); 
//$smarty->assign('arrOwners', $arrOwners);
//$smarty->assign('arrKeywords', $arrKeywords);
//$smarty->assign('arrComponents', $arrComponents);
$smarty->display('resultsMoreBuilds_query_form.tpl');

?>