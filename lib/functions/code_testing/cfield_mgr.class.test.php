<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: cfield_mgr.class.test.php,v $
 *
 * @version $Revision: 1.1 $
 * @modified $Date: 2008/08/14 15:15:28 $ by $Author: franciscom $
 * @author Francisco Mancardi
 *
 * With this page you can launch a set of available methods, to understand
 * how object works and have inside view about return type .
 *
 * rev : 20080811 - franciscom
 *
*/
require_once('../../../config.inc.php');
require_once('common.php');
require_once('tree.class.php');

testlinkInitPage($db);
$object_item="Custom Field Manager";
$object_class="cfield_mgr";

echo "<pre>Poor's Man - $object_item - code inspection tool<br>";
echo "<pre>Scope of this page is allow you to understand with live<br>";
echo "examples how to use object: $object_item (implemented in file $object_class.class.php)<br>";
echo "Important:";
echo "You are using your testlink DB to do all operations";
echo "</pre>";
echo "<hr>";
echo "<pre> $object_item - constructor - $object_class(&\$db)";echo "</pre>";
$obj_mgr=new $object_class($db);
new dBug($obj_mgr);

$tproject_id=2714;
$enabled=1;
echo "
function get_linked_cfields_at_testplan_design(\$tproject_id,\$enabled,
                                               \$node_type=null,\$node_id=null,
                                               \$link_id=null,\$testplan_id=null)
";
echo "
function get_linked_cfields_at_testplan_design($tproject_id,$enabled);
";
$cf=$obj_mgr->get_linked_cfields_at_testplan_design($tproject_id,$enabled);
new dBug($cf);


?>