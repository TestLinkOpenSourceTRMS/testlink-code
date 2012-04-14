<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource	testplan.getHits.test.php,v $
 * @author Francisco Mancardi
 *
 * 
 *
 */

require_once('../../../config.inc.php');
require_once('common.php');
testlinkInitPage($db);

define('DBUG_ON',1);

echo '<b>Database:' . DB_NAME . '</b><br>';
$object_item="Testplan Manager";
$object_class="testplan";

echo "<pre>Poor's Man - $object_item - code inspection tool<br>";
echo "<pre>Scope of this page is allow you to understand with live<br>";
echo "examples how to use object: $object_item (implemented in file $object_class_file.class.php)<br>";
echo "Important:";
echo "You are using your testlink DB to do all operations";
echo "</pre>";
echo "<hr>";
echo "<pre> $object_item - constructor - $object_class(&\$db)";echo "</pre>";

echo "<hr>";
echo <<<STUFF
You need a setup similar to this  (ALL THIS HAS TO BE TESTED ALSO WITH PLATFORMS)
Test Plan: PLAN B 
Builds: B1,B2,B3
Test Cases: TC-100, TC-200,TC-300

Test Case - Build - run # / execution status history
TC-100      B1      3 / Passed
TC-100      B1      2 / BLOCKED
TC-100      B1      1 / FAILED
TC-100       B2      1 / FAILED
TC-100        B3      0 / Not Run

TC-200      B1      1/FAILED
TC-200       B2      1/FAILED
TC-200        B3      1/BLOCKED
	 
TC-300      B1      3/Passed
TC-300      B1      2/Passed
TC-300      B1      1/Passed
TC-300       B2      3/Passed
TC-300       B2      2/FAILED
TC-300       B2      1/Passed
TC-300        B3      4/BLOCKED
TC-300        B3      3/BLOCKED
TC-300        B3      2/Passed
TC-300        B3      1/FAILED
	 
TC-400      B1      2/FAILED =
TC-400      B1      1/BLOCKED
TC-400       B2      1/FAILED =
TC-400        B3      3/FAILED =
TC-400        B3      2/Passed
TC-400        B3      1/BLOCKED
STUFF;



$obj_mgr=new $object_class($db);
new dBug($obj_mgr);

$tplan_id = 33121;
$platform_id = 0;

echo '<h1>Test group conditions</h1>';
echo 'Test Plan ID:' . $tplan_id . '<br>';
echo 'Platform ID:' . $platform_id . '<br>';

echo '<hr>';
$method2call = 'getHitsNotRunFull';
$$method2call = $obj_mgr->$method2call($tplan_id,$platform_id); 
echo '<br>' . $method2call . '()' . '<br>';
new dBug($$method2call);
echo '<hr>';

$status = 'p';
$method2call = 'getHitsSingleStatusFull';
$$method2call = $obj_mgr->$method2call($tplan_id,$platform_id,$status); 
echo '<br>' . $method2call . '()' . '<br>';
new dBug($$method2call);
echo '<hr>';


$statusSet = array('b','p');
$method2call = 'getHitsStatusSetFull';
$$method2call = $obj_mgr->$method2call($tplan_id,$platform_id,$statusSet); 
echo '<br>' . $method2call . '()' . '<br>';
new dBug($$method2call);
echo '<hr>';

$statusSet = array('b','f');
$method2call = 'getHitsStatusSetFull';
$$method2call = $obj_mgr->$method2call($tplan_id,$platform_id,$statusSet); 
echo '<br>' . $method2call . '()' . '<br>';
new dBug($$method2call);
echo '<hr>';

$method2call = 'getHitsNotRunPartial';
$$method2call = $obj_mgr->$method2call($tplan_id,$platform_id); 
echo '<br>' . $method2call . '()' . '<br>';
new dBug($$method2call);
echo '<hr>';

$statusSet = array('b','p');
$method2call = 'getHitsStatusSetPartial';
$$method2call = $obj_mgr->$method2call($tplan_id,$platform_id); 
echo '<br>' . $method2call . '()' . '<br>';
new dBug($$method2call);
echo '<hr>';



// WITH PLATFORMS
/*
$tplan_id = 33111;
$platform_id = 0;
echo '<h1>Test group conditions</h1>';
echo 'Test Plan ID:' . $tplan_id . '<br>';
echo 'Platform ID:' . $platform_id . '<br>';
*/


/*
new view
last_executions
SELECT tcversion_id,testplan_id,platform_id,build_id,MAX(status) AS status, id AS MAX(E.id) AS id
FROM executions 
GROUP BY tcversion_id,testsplan_id,platform_id,build_id

CREATE VIEW tk_last_executions AS
SELECT tcversion_id,testplan_id,platform_id,build_id,max(status) AS status,max(id) AS id 
from tk_executions 
group by tcversion_id,testplan_id,platform_id,build_id
*/
?>
