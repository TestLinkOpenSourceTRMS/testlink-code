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



// WITH PLATFORMS
/*
$tplan_id = 33111;
$platform_id = 0;
echo '<h1>Test group conditions</h1>';
echo 'Test Plan ID:' . $tplan_id . '<br>';
echo 'Platform ID:' . $platform_id . '<br>';
*/


?>
