<?php
/** 
* 	TestLink Open Source Project - http://testlink.sourceforge.net/
* 
* 	@version 	$Id: gettprojectnodes.php,v 1.2 2008/05/28 20:57:12 franciscom Exp $
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
*   rev: 
*        
*/
require_once('../../config.inc.php');
require_once('common.php');
testlinkInitPage($db);


$root_node=isset($_REQUEST['root_node']) ? $_REQUEST['root_node']: null;
$node=isset($_REQUEST['node']) ? $_REQUEST['node'] : $root_node;
$filter_node=isset($_REQUEST['filter_node']) ? $_REQUEST['filter_node'] : null;

$nodes=display_children($db,$root_node,$node,$filter_node);
echo json_encode($nodes);
?>

<?php
function display_children($dbHandler,$root_node,$parent,$filter_node) 
{             
    $nodes=null;
                                       
    $sql = "SELECT NH.*, NT.description AS node_type FROM nodes_hierarchy NH, node_types NT " .
           " WHERE NH.node_type_id=NT.id " .
           " AND parent_id = {$parent} " .
           " AND NT.description NOT IN ('testcase_version','testplan','requirement_spec','requirement') ";

    if( !is_null($filter_node) && $filter_node > 0 && $parent==$root_node)
    {
       $sql .="AND NH.id = {$filter_node}";  
    }
    // for debug file_put_contents('d:\sql_display_node.txt', $sql); 
    $nodeSet = $dbHandler->get_recordset($sql);
				
		foreach($nodeSet as $key => $row)
		{
		    // Response parameters.                                                                  
		    $path['text']		= html_entity_decode($row['name']);                                  
		    $path['id']			= $row['id'];                                                           
		    $path['position']	= $row['node_order'];                                                   
        
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