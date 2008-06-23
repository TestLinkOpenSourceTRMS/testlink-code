<?php
/** 
* 	TestLink Open Source Project - http://testlink.sourceforge.net/
* 
* 	@version 	$Id: gettprojectnodes.php,v 1.5 2008/06/23 06:23:53 franciscom Exp $
* 	@author 	Francisco Mancardi
* 
*   Created using Ext JS example code
*
* 	Is the tree loader, will be called via AJAX.
*   Ext JS automatically will pass $_REQUEST['node']   
*   Other arguments will be added by TL php code that needs the tree.
*   
*   This tree is used to navigate Test Project, and is used in following feature:
*
*   - Create test suites, test cases on test project
*   - Assign keywords to test cases
*   - Assign requirements to test cases
*
*   rev: 20080622 - franciscom - added new argument (show_tcases), 
*                                to use this page on test plan add test case feature.
*
*        20080603 - franciscom - added external id on test case nodes
*        
*/
require_once('../../config.inc.php');
require_once('common.php');
testlinkInitPage($db);


$root_node=isset($_REQUEST['root_node']) ? $_REQUEST['root_node']: null;
$node=isset($_REQUEST['node']) ? $_REQUEST['node'] : $root_node;
$filter_node=isset($_REQUEST['filter_node']) ? $_REQUEST['filter_node'] : null;
$tcprefix=isset($_REQUEST['tcprefix']) ? $_REQUEST['tcprefix'] : '';

// 20080622 - franciscom - useful only for feature: test plan add test case
$show_tcases=isset($_REQUEST['show_tcases']) ? $_REQUEST['show_tcases'] : 1;

// for debug - file_put_contents('d:\request.txt', serialize($_REQUEST));                            
$nodes=display_children($db,$root_node,$node,$filter_node,$tcprefix,$show_tcases);
echo json_encode($nodes);
?>

<?php
function display_children($dbHandler,$root_node,$parent,$filter_node,$tcprefix,$show_tcases=1) 
{             
    $nodes=null;
                                       
    // 20080622 - franciscom
    $filter_node_type=$show_tcases ? '' : ",'testcase'";

    $sql = " SELECT NHA.*, NT.description AS node_type " . 
           " FROM nodes_hierarchy NHA, node_types NT " .
           " WHERE NHA.node_type_id=NT.id " .
           " AND parent_id = {$parent} " .
           " AND NT.description NOT IN " .
           " ('testcase_version','testplan','requirement_spec','requirement'{$filter_node_type}) ";

    if( !is_null($filter_node) && $filter_node > 0 && $parent==$root_node)
    {
       $sql .=" AND NHA.id = {$filter_node} ";  
    }
    // $sql .=" ORDER BY NHA.name ";  
    $sql .= " ORDER BY NHA.node_order ";    
    
    
    // for debug 
    file_put_contents('d:\sql_display_node.txt', $sql); 
    $nodeSet = $dbHandler->get_recordset($sql);
    
    // Remove before create release
    // $sql = " SELECT MAX(TCV.id),tc_external_id,NHA.parent_id " .
    //        " FROM tcversions TCV,nodes_hierarchy NHA " .  
    //        " WHERE NHA.id = TCV.id " .
    //        " AND NHA.parent_id IN " .
    //        " (SELECT NHA.id  " .
    //        "  FROM nodes_hierarchy NHA, node_types NT " . 
    //        "  WHERE NHA.node_type_id=NT.id " .
    //        "  AND parent_id = {$parent} ".
    //        "  AND NT.description = 'testcase') ". 
    //        "  GROUP BY NHA.parent_id ";
       
    $external='';
    if( $show_tcases )
    {  
        // Get external id, used on test case nodes   
        $sql = " SELECT DISTINCT tc_external_id,NHA.parent_id " .
               " FROM tcversions TCV,nodes_hierarchy NHA " .  
               " WHERE NHA.id = TCV.id " .
               " AND NHA.parent_id IN " .
               " (SELECT NHA.id  " .
               "  FROM nodes_hierarchy NHA, node_types NT " . 
               "  WHERE NHA.node_type_id=NT.id " .
               "  AND parent_id = {$parent} ".
               "  AND NT.description = 'testcase') ";
           
           
        // file_put_contents('d:\sql_2.txt', $sql); 
        $external=$dbHandler->fetchRowsIntoMap($sql,'parent_id');
    }
    
    // print_r(array_values($nodeSet));
    // file_put_contents('d:\sql_display_node.txt', serialize(array_values($nodeSet))); 
		if( !is_null($nodeSet) ) 
		{
		    foreach($nodeSet as $key => $row)
		    {
		        $path['text']		= html_entity_decode($row['name']);                                  
		        $path['id']			= $row['id'];                                                           
        
            // this attribute/property is used on custom code on drag and drop
		        $path['position']	= $row['node_order'];                                                   
            $path['leaf']	= false;
 		        $path['cls']	= 'folder';
		       
            switch($row['node_type'])
            {
                case 'testproject':
                $path['href'] = "javascript:EP({$path['id']})";
                break;
                
                case 'testsuite':
                $path['href'] = "javascript:ETS({$path['id']})";
                break;
                
                case 'testcase':
                $path['href'] = "javascript:ET({$path['id']})";
                $path['text'] = $tcprefix . $external[$row['id']]['tc_external_id'] . ":" . $path['text'];
                $path['leaf']	= true;
                break;
            }
		        $nodes[] = $path;                                                                        
		    }	// foreach	
    }
		return $nodes;                                                                             
}                                                                                               