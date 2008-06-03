<?php
/** 
* 	TestLink Open Source Project - http://testlink.sourceforge.net/
* 
* 	@version 	$Id: gettprojectnodes.php,v 1.3 2008/06/03 20:28:32 franciscom Exp $
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
*   rev: 20080603 - franciscom - added external id on test case nodes
*        
*/
require_once('../../config.inc.php');
require_once('common.php');
testlinkInitPage($db);


$root_node=isset($_REQUEST['root_node']) ? $_REQUEST['root_node']: null;
$node=isset($_REQUEST['node']) ? $_REQUEST['node'] : $root_node;
$filter_node=isset($_REQUEST['filter_node']) ? $_REQUEST['filter_node'] : null;
$tcprefix=$_REQUEST['tcprefix'];

// for debug - file_put_contents('d:\request.txt', serialize($_REQUEST));                            

$nodes=display_children($db,$root_node,$node,$filter_node,$tcprefix);
echo json_encode($nodes);
?>

<?php
function display_children($dbHandler,$root_node,$parent,$filter_node,$tcprefix) 
{             
    $nodes=null;
                                       
    $sql = " SELECT NHA.*, NT.description AS node_type " . 
           " FROM nodes_hierarchy NHA, node_types NT " .
           " WHERE NHA.node_type_id=NT.id " .
           " AND parent_id = {$parent} " .
           " AND NT.description NOT IN ('testcase_version','testplan','requirement_spec','requirement') ";

    if( !is_null($filter_node) && $filter_node > 0 && $parent==$root_node)
    {
       $sql .=" AND NHA.id = {$filter_node} ";  
    }
    // $sql .=" ORDER BY NHA.name ";  
    $sql .= " ORDER BY NHA.node_order ";    
    
    
    // for debug 
    // file_put_contents('d:\sql_display_node.txt', $sql); 
    $nodeSet = $dbHandler->get_recordset($sql);
    
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
    
    // print_r(array_values($nodeSet));
    // file_put_contents('d:\sql_display_node.txt', serialize(array_values($nodeSet))); 
		
		foreach($nodeSet as $key => $row)
		{
		    // Response parameters.                                                                  
		    $path['text']		= html_entity_decode($row['name']);                                  
		    $path['id']			= $row['id'];                                                           
		   
		    // 20080602 - franciscom
		    // seems this attribute is not used.
		    // Node order is detemined by writing order => sql select ORDER BY
		    //
		    // $path['position']	= $row['node_order'];                                                   
        // $path['position']	= $idx++;                                                   
        
        switch($row['node_type'])
        {
            case 'testproject':
            $path['leaf']	= false;
            $path['href'] = "javascript:EP({$path['id']})";
            break;
            
            case 'testsuite':
            $path['leaf']	= false;
            $path['href'] = "javascript:ETS({$path['id']})";
            break;
            
            case 'testcase':
            $path['text'] = $tcprefix . $external[$row['id']]['tc_external_id'] . ":" . $path['text'];
            $path['leaf']	= true;
            $path['href'] = "javascript:ET({$path['id']})";
            break;
        }
 		    $path['cls']	= 'folder';
                                                                                                  
		    // call this function again to display this                                              
		    // child's children                                                                      
		    $nodes[] = $path;                                                                        
		}		

		return $nodes;                                                                             
}                                                                                               