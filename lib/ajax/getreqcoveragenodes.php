<?php
/** 
* 	TestLink Open Source Project - http://testlink.sourceforge.net/
* 
* 	@author Tanguy Oger
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
*	@internal revision
*        
*/
require_once('../../config.inc.php');
require_once('common.php');
testlinkInitPage($db);


$root_node=isset($_REQUEST['root_node']) ? intval($_REQUEST['root_node']): null;
$node=isset($_REQUEST['node']) ? intval($_REQUEST['node']) : $root_node;
$filter_node=isset($_REQUEST['filter_node']) ? intval($_REQUEST['filter_node']) : null;

$show_children=isset($_REQUEST['show_children']) ? intval($_REQUEST['show_children']) : 1;
$operation=isset($_REQUEST['operation']) ? $_REQUEST['operation']: 'manage';

// for debug - file_put_contents('d:\request.txt', serialize($_REQUEST));
$nodes = display_children($db,$root_node,$node,$filter_node,$show_children,$operation);
echo json_encode($nodes);

/*

*/
function display_children($dbHandler,$root_node,$parent,$filter_node,
                          $show_children=ON,$operation='manage') 
{             
  $tables = tlObjectWithDB::getDBTables(array('requirements','nodes_hierarchy','node_types','req_specs'));
	$cfg = config_get('req_cfg');
	$forbidden_parent['testproject'] = 'none';
	$forbidden_parent['requirement'] = 'testproject';
	$forbidden_parent['requirement_spec'] = 'requirement_spec';
	if($cfg->child_requirements_mgmt)
	{
		$forbidden_parent['requirement_spec'] = 'none';
	} 
    
  switch($operation)
  {

  	case 'print':
  		$js_function = array('testproject' => 'TPROJECT_PTP',
  		'requirement_spec' =>'TPROJECT_PRS', 'requirement' => 'TPROJECT_PRS');
  		break;
  	
  	case 'manage':
  	default:
  		$js_function = array('testproject' => 'EP','requirement_spec' =>'ERS', 'requirement' => 'ER');
  		break; 
  }
    
  $nodes = null;
  $filter_node_type = $show_children ? '' : ",'requirement'";
  $sql = " SELECT NHA.*, NT.description AS node_type, RSPEC.doc_id " . 
         " FROM {$tables['nodes_hierarchy']} NHA JOIN {$tables['node_types']}  NT " .
         " ON NHA.node_type_id=NT.id " .
         " AND NT.description NOT IN " . 
         " ('testcase','testsuite','testcase_version','testplan','requirement_spec_revision' {$filter_node_type}) " .
         " LEFT OUTER JOIN {$tables['req_specs']} RSPEC " .
         " ON RSPEC.id = NHA.id " . 
         " WHERE NHA.parent_id = " . intval($parent);
    
  // file_put_contents('/tmp/getrequirementnodes.php.txt', $sql);                            
  if(!is_null($filter_node) && $filter_node > 0 && $parent == $root_node)
  {
    $sql .= " AND NHA.id = " . intval($filter_node);  
  }
  $sql .= " ORDER BY NHA.node_order ";    

  $nodeSet = $dbHandler->get_recordset($sql);
	if(!is_null($nodeSet)) 
	{
    $sql =  " SELECT DISTINCT req_doc_id AS doc_id,NHA.id" .
            " FROM {$tables['requirements']} REQ JOIN {$tables['nodes_hierarchy']} NHA ON NHA.id = REQ.id  " .
            " JOIN {$tables['nodes_hierarchy']}  NHB ON NHA.parent_id = NHB.id " . 
            " JOIN {$tables['node_types']} NT ON NT.id = NHA.node_type_id " .
            " WHERE NHB.id = " . intval($parent) . " AND NT.description = 'requirement'";
    $requirements = $dbHandler->fetchRowsIntoMap($sql,'id');

	  $treeMgr = new tree($dbHandler);
	  $ntypes = $treeMgr->get_available_node_types();
		$peerTypes = array('target' => $ntypes['requirement'], 'container' => $ntypes['requirement_spec']); 
	  foreach($nodeSet as $key => $row)
	  {
	    $path['text'] = htmlspecialchars($row['name']);                                  
	    $path['id'] = $row['id'];                                                           
        
      // this attribute/property is used on custom code on drag and drop
	    $path['position'] = $row['node_order'];                                                   
      $path['leaf'] = false;
 	    $path['cls'] = 'folder';
 	        
 	    $path['testlink_node_type']	= $row['node_type'];		                                 
	    $path['testlink_node_name'] = $path['text']; // already htmlspecialchars() done

      $path['forbidden_parent'] = 'none';
      switch($row['node_type'])
      {
        case 'testproject':
          $path['href'] = "javascript:EP({$path['id']})";
          $path['forbidden_parent'] = $forbidden_parent[$row['node_type']];
	      break;

        case 'requirement_spec':
          $req_list = array();
	        $treeMgr->getAllItemsID($row['id'],$req_list,$peerTypes);

	        $path['href'] = "javascript:" . $js_function[$row['node_type']]. "({$path['id']})";
	        $path['text'] = htmlspecialchars($row['doc_id'] . ":") . $path['text'];
          $path['forbidden_parent'] = $forbidden_parent[$row['node_type']];
   	      if(!is_null($req_list))
	        {
            $item_qty = count($req_list);
	        	$path['text'] .= " ({$item_qty})";   
	        }
	      break;

        case 'requirement':
          $path['href'] = "javascript:" . $js_function[$row['node_type']]. "({$path['id']})";
	        $path['text'] = htmlspecialchars($requirements[$row['id']]['doc_id'] . ":") . $path['text'];
	        $path['leaf']	= true;
          $path['forbidden_parent'] = $forbidden_parent[$row['node_type']];
	      break;
      }

      $nodes[] = $path;                                                                        
    }	// foreach	
  }
	return $nodes;                                                                             
}                                                                                           