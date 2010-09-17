<?php
/** TestLink Open Source Project - http://testlink.sourceforge.net/
 *
 * @filesource $RCSfile: reassign_tc_id.php,v $
 * @version $Revision: 1.4 $
 * @modified $Date: 2010/09/17 18:26:09 $  $Author: franciscom $
 * @author Francisco Mancardi - francisco.mancardi@gmail.com
 *
 * utility to align Test Case External ID to Test Case INTERNAL ID
 * Use at your risk
*/

require_once("../../config.inc.php");
require_once("common.php");

testlinkInitPage($db);
// Check if user has right role to execute
if( !($_SESSION['currentUser']->globalRole->name=='admin') )
{
	echo 'You need to have admin role in order to use this page <b> ';
	die();
}


$tcase_mgr = new testcase($db);
$tproject_mgr = new testproject($db);

$testProjects = $tproject_mgr->get_all();

// $exclude_node_types=array('testplan' => 1,'requirement_spec' => 1 );
// $exclude_children=array('testcase' =>1);

$my['filters']=array('exclude_node_types' => array('testplan' => 1,'requirement_spec' => 1),
                     'exclude_children' => array('testcase' =>1) );
                          
foreach( $testProjects as $item )
{
    $tproject_id=$item['id'];
  	// $elements = $tproject_mgr->tree_manager->get_subtree($tproject_id,$exclude_node_types,
	//                                                        $exclude_children,null,null,null);
  	$elements = $tproject_mgr->tree_manager->get_subtree($tproject_id,$my['filters']);
    $tcaseSet=null;
    foreach($elements as $elem)
    {
       //new dBug($elem);
       if( $elem['node_table']=='testcases' )
       {
           $tcaseSet[]=$elem['id']; 
       }  
    }
    
    if( !is_null($tcaseSet) )
    { 
        asort($tcaseSet);
        $maxTestCaseNumber = end($tcaseSet)+1 ;
        reset($tcaseSet);

        foreach($tcaseSet as $elem)
        {
           $sql = "UPDATE tcversions " .
                  "SET tc_external_id = {$elem} " .
                  "WHERE id IN (SELECT id FROM nodes_hierarchy WHERE parent_id={$elem}) ";
           $db->exec_query($sql);
        }
        
        $sql = " UPDATE testprojects " .
               " SET tc_counter = {$maxTestCaseNumber} " .
               " WHERE id = {$tproject_id} ";
        $db->exec_query($sql);
    }
}
?>