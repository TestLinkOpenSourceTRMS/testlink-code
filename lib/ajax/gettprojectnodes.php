<?php
/** 
* 	TestLink Open Source Project - http://testlink.sourceforge.net/
* 
* 	@version 	$Id: gettprojectnodes.php,v 1.23 2010/12/01 14:37:08 asimon83 Exp $
* 	@author 	Francisco Mancardi
* 
*   **** IMPORTANT *****   
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
* 	EXT-JS - Important:
* 	Custom keys can be added, and will be access on EXT-JS code using
* 	public property 'attributes' of object of Class Ext.tree.TreeNode 
* 	
*
* @internal revisions 
*
* 20110419 - franciscom - BUGID 4429: Code refactoring to remove global coupling as much as possible
* 20101010 - franciscom - added custom node attribute: testlink_node_name
* 20100908 - franciscom - added custom node attribute: testlink_node_type
*        
*/
require_once('../../config.inc.php');
require_once('common.php');
testlinkInitPage($db);

// BUGID 4066 - take care of proper escaping when magic_quotes_gpc is enabled
$_REQUEST=strings_stripSlashes($_REQUEST);

$root_node = isset($_REQUEST['root_node']) ? $_REQUEST['root_node']: null;
$env['tproject_id'] = isset($_REQUEST['tproject_id']) ? intval($_REQUEST['tproject_id']) : 0;
$env['tplan_id'] = isset($_REQUEST['tplan_id']) ? intval($_REQUEST['tplan_id']) : 0;

$node = isset($_REQUEST['node']) ? $_REQUEST['node'] : $root_node;
$filter_node = isset($_REQUEST['filter_node']) ? $_REQUEST['filter_node'] : null;
$tcprefix = isset($_REQUEST['tcprefix']) ? $_REQUEST['tcprefix'] : '';

// 20080622 - franciscom - useful only for feature: test plan add test case
$show_tcases = isset($_REQUEST['show_tcases']) ? $_REQUEST['show_tcases'] : 1;

$operation = isset($_REQUEST['operation']) ? $_REQUEST['operation']: 'manage';

// for debug - file_put_contents('d:\request.txt', serialize($_REQUEST));                            
$nodes = display_children($db,$env,$root_node,$node,$filter_node,$tcprefix,$show_tcases,$operation);
echo json_encode($nodes);

function display_children($dbHandler,$env,$root_node,$parent,$filter_node,
                          $tcprefix,$show_tcases = 1,$operation = 'manage') 
{             
    static $showTestCaseID;
    
    $tables = tlObjectWithDB::getDBTables(array('tcversions','nodes_hierarchy','node_types'));

    $external = '';
    $nodes = null;
    $filter_node_type = $show_tcases ? '' : ",'testcase'";
        
    switch($operation)
    {
        case 'print':
            $js_function = array('testproject' => 'TPROJECT_PTP',
                                 'testsuite' =>'TPROJECT_PTS', 'testcase' => 'TPROJECT_PTS');
       	 	break;
        
        case 'manage':
        default:
            $js_function = array('testproject' => 'EP','testsuite' =>'ETS', 'testcase' => 'ET');
        	break;  
    }
    
    $sql = " SELECT NHA.*, NT.description AS node_type " . 
           " FROM {$tables['nodes_hierarchy']} NHA, {$tables['node_types']} NT " .
           " WHERE NHA.node_type_id = NT.id " .
           " AND parent_id = {$parent} " .
           " AND NT.description NOT IN " .
           " ('testcase_version','testplan','requirement_spec','requirement'{$filter_node_type}) ";

    if(!is_null($filter_node) && $filter_node > 0 && $parent == $root_node)
    {
       $sql .=" AND NHA.id = {$filter_node} ";  
    }
    $sql .= " ORDER BY NHA.node_order ";    
    
    
    // for debug 
    //file_put_contents('c:\austausch\sql_display_node.txt', $sql); 
    $nodeSet = $dbHandler->get_recordset($sql);
       
    if($show_tcases)
    {  
        // Get external id, used on test case nodes   
        $sql =  " SELECT DISTINCT tc_external_id,NHA.parent_id " .
                " FROM {$tables['tcversions']} TCV " .
                " JOIN {$tables['nodes_hierarchy']} NHA  ON NHA.id = TCV.id  " .
                " JOIN {$tables['nodes_hierarchy']} NHB ON NHA.parent_id = NHB.id " . 
                " WHERE NHB.parent_id = {$parent} AND NHA.node_type_id = 4"; 
        //file_put_contents('c:\austausch\sql_display_node1.txt', $sql); 
        $external = $dbHandler->fetchRowsIntoMap($sql,'parent_id');
    }
    
    // print_r(array_values($nodeSet));
    // file_put_contents('/tmp/sql_display_node.txt', serialize(array_values($nodeSet))); 
	if(!is_null($nodeSet)) 
	{
	    $tproject_mgr = new testproject($dbHandler);
	    foreach($nodeSet as $key => $row)
	    {
	        $path['text'] = htmlspecialchars($row['name']);
	        $path['id'] = $row['id'];                                                           
        
          	// this attribute/property is used on custom code on drag and drop
	        $path['position'] = $row['node_order'];                                                   
          	$path['leaf'] = false;
 	        $path['cls'] = 'folder';

			// customs key will be accessed using node.attributes.[key name]
	        $path['testlink_node_type'] = $row['node_type'];
	        $path['testlink_node_name'] = $path['text']; // already htmlspecialchars() done
	       
	        $tcase_qty = null;
	        switch($row['node_type'])
	        {
	        	case 'testproject':
	                // 20080817 - franciscom - 
	                // at least on Test Specification seems that we do not execute this piece of code.
	                $path['href'] = "javascript:EP({$env['tproject_id']},{$env['tplan_id']},{$path['id']})";
	                break;
	              
	           case 'testsuite':
	                $tcase_qty = $tproject_mgr->count_testcases($row['id']);
	                $path['href'] = "javascript:" . $js_function[$row['node_type']] . 
	                				"({$env['tproject_id']},{$env['tplan_id']},{$path['id']})";
	                break;
	              
	           case 'testcase':
		       		// $path['href'] = "javascript:" . $js_function[$row['node_type']] . 
	                // 				"({$env['tproject_id']},{$env['tplan_id']},{$path['id']})";

		       		$path['href'] = "javascript:" . $js_function[$row['node_type']] . 
	                 				"({$env['tproject_id']},{$path['id']})";

                  	// BUGID 1928
                  	if(is_null($showTestCaseID))
                  	{
                  		$showTestCaseID = config_get('treemenu_show_testcase_id');
                  	}
                  	if($showTestCaseID)
	                {
	                	$path['text'] = htmlspecialchars($tcprefix . $external[$row['id']]['tc_external_id'] . ":") .
	                                   $path['text'];
	                }
	                $path['leaf']	= true;
	              	break;
	        }
	        if(!is_null($tcase_qty))
	        {
	        	$path['text'] .= " ({$tcase_qty})";   
	        }
          	$nodes[] = $path;                                                                        
	    }
    }
	return $nodes;                                                                             
}                                                                                               
?>