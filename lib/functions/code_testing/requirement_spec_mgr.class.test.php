<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource 
 *
 *
 * @internal revisions
*/

require_once('../../../config.inc.php');
require_once('common.php');
testlinkInitPage($db);

$classUnderTest = 'requirement_spec_mgr';

echo "<h1> Class Under Test : {$classUnderTest} </h1>";
echo "<pre> {$classUnderTest}.class - constructor - $classUnderTest(&\$db)";echo "</pre>";
$obj_mgr=new $classUnderTest($db);
new dBug($obj_mgr);

// $method2test = "create";
// echo "<pre> {$method2test} - $$method2test(&\$db)";echo "</pre>";
// $obj_mgr=new $classUnderTest($db);
// new dBug($obj_mgr);
   
$method2test = "create_revision";
$rspecID='12';
$item = array();
$item['revision'] = 2;
$item['doc_id'] = 'DOCO';
$item['name']='NIKO';
$item['scope'] = ' Scoppppp';
$item['status']=3;
$item['type'] ='K';
$item['log_message']='This is a log message';
$item['author_id']=1;
// $item['creation_ts']='This is a log message';
// $item['modifier_id']='This is a log message';
// $item['modification_ts']='This is a log message';

echo "<pre> {$method2test} - $$method2test(&\$rspecID,&\$item,)";echo "</pre>";


new dBug($obj_mgr->$method2test($rspecID,$item));

?>