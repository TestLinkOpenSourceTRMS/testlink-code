<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: requirement_mgr.class.test.php,v $
 *
 * @version $Revision: 1.1 $
 * @modified $Date: 2010/01/24 15:57:18 $ by $Author: franciscom $
 * @author Francisco Mancardi
 *
 * 
 *
 * rev :
*/

require_once('../../../config.inc.php');
require_once('common.php');
testlinkInitPage($db);

$classUnderTest = 'requirement_mgr';

echo "<h1> Class Under Test : {$classUnderTest} </h1>";
echo "<pre> {$classUnderTest}.class - constructor - $classUnderTest(&\$db)";echo "</pre>";
$obj_mgr=new $classUnderTest($db);
new dBug($obj_mgr);

// $method2test = "create";
// echo "<pre> {$method2test} - $$method2test(&\$db)";echo "</pre>";
// $obj_mgr=new $classUnderTest($db);
// new dBug($obj_mgr);
   
$method2test = "updateOpen";
$reqID = 18;
$reqVersionID = 19;
$value = 0;
echo "<pre> {$method2test} - $$method2test(&\$reqVersionID,\$value)";echo "</pre>";
echo "<pre> {$method2test} - $$method2test($reqVersionID,$value)";echo "</pre>";

$obj_mgr->$method2test($reqVersionID,$value);
$req_version = $obj_mgr->get_by_id($reqID);
new dBug($req_version);
   

$method2test = "updateOpen";
$reqID = 18;
$reqVersionID = 19;
$value = 1;
echo "<pre> {$method2test} - $$method2test(&\$reqVersionID,\$value)";echo "</pre>";
echo "<pre> {$method2test} - $$method2test($reqVersionID,$value)";echo "</pre>";

$obj_mgr->$method2test($reqVersionID,$value);
$req_version = $obj_mgr->get_by_id($reqID);
new dBug($req_version);

$method2test = "updateActive";
$reqID = 18;
$reqVersionID = 19;
$value = 0;
echo "<pre> {$method2test} - $$method2test(&\$reqVersionID,\$value)";echo "</pre>";
echo "<pre> {$method2test} - $$method2test($reqVersionID,$value)";echo "</pre>";

$obj_mgr->$method2test($reqVersionID,$value);
$req_version = $obj_mgr->get_by_id($reqID);
new dBug($req_version);

$method2test = "updateActive";
$reqID = 18;
$reqVersionID = 19;
$value = 1000;
echo "<pre> {$method2test} - $$method2test(&\$reqVersionID,\$value)";echo "</pre>";
echo "<pre> {$method2test} - $$method2test($reqVersionID,$value)";echo "</pre>";

$obj_mgr->$method2test($reqVersionID,$value);
$req_version = $obj_mgr->get_by_id($reqID);
new dBug($req_version);

$method2test = "updateActive";
$reqID = 18;
$reqVersionID = 19;
$value = null;
echo "<pre> {$method2test} - $$method2test(&\$reqVersionID,\$value)";echo "</pre>";
echo "<pre> {$method2test} - $$method2test($reqVersionID,$value)";echo "</pre>";

$obj_mgr->$method2test($reqVersionID,$value);
$req_version = $obj_mgr->get_by_id($reqID);
new dBug($req_version);

$method2test = "updateActive";
$reqID = 18;
$reqVersionID = 19;
$value = "one";
echo "<pre> {$method2test} - $$method2test(&\$reqVersionID,\$value)";echo "</pre>";
echo "<pre> {$method2test} - $$method2test($reqVersionID,$value)";echo "</pre>";

$obj_mgr->$method2test($reqVersionID,$value);
$req_version = $obj_mgr->get_by_id($reqID);
new dBug($req_version);

$method2test = "updateActive";
$reqID = 18;
$reqVersionID = 19;
$value = array();
echo "<pre> {$method2test} - $$method2test(&\$reqVersionID,\$value)";echo "</pre>";
echo "<pre> {$method2test} - $$method2test($reqVersionID,$value)";echo "</pre>";

$obj_mgr->$method2test($reqVersionID,$value);
$req_version = $obj_mgr->get_by_id($reqID);
new dBug($req_version);

$method2test = "updateActive";
$reqID = 18;
$reqVersionID = 19;
$value = -18;
echo "<pre> {$method2test} - $$method2test(&\$reqVersionID,\$value)";echo "</pre>";
echo "<pre> {$method2test} - $$method2test($reqVersionID,$value)";echo "</pre>";

$obj_mgr->$method2test($reqVersionID,$value);
$req_version = $obj_mgr->get_by_id($reqID);
new dBug($req_version);

$method2test = "updateActive";
$reqID = 18;
$reqVersionID = 19;
$value = false;
echo "<pre> {$method2test} - $$method2test(&\$reqVersionID,\$value)";echo "</pre>";
echo "<pre> {$method2test} - $$method2test($reqVersionID,$value)";echo "</pre>";

$obj_mgr->$method2test($reqVersionID,$value);
$req_version = $obj_mgr->get_by_id($reqID);
new dBug($req_version);


?>