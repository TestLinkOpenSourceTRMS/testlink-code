<?php
/** 
*   TestLink Open Source Project - http://testlink.sourceforge.net/
* 
*   @version  $Id: gettprojectnodes.php,v 1.22 2010/10/10 14:47:57 franciscom Exp $
*   @author   Francisco Mancardi
* 
*   **** IMPORTANT *****   
*   Created using Ext JS example code
*
*   Is the tree loader, will be called via AJAX.
*   Ext JS automatically will pass $_REQUEST['node']   
*   Other arguments will be added by TL php code that needs the tree.
*   
*   This tree is used to navigate Test Project, and is used in following feature:
*
*   - Create test suites, test cases on test project
*   - Assign keywords to test cases
*   - Assign requirements to test cases
*
*   EXT-JS - Important:
*   Custom keys can be added, and will be access on EXT-JS code using
*   public property 'attributes' of object of Class Ext.tree.TreeNode 
*   
*
* @internal revisions
* @since 1.9.10
*
*/
require_once('../../config.inc.php');
require_once('common.php');
testlinkInitPage($db);

$root_node = isset($_REQUEST['root_node']) ? intval($_REQUEST['root_node']): null;
$node = isset($_REQUEST['node']) ? intval($_REQUEST['node']) : $root_node;
$filter_node = isset($_REQUEST['filter_node']) ? intval($_REQUEST['filter_node']) : null;
$show_tcases = isset($_REQUEST['show_tcases']) ? intval($_REQUEST['show_tcases']) : 1;

$tcprefix = isset($_REQUEST['tcprefix']) ? $_REQUEST['tcprefix'] : '';
$operation = isset($_REQUEST['operation']) ? $_REQUEST['operation']: 'manage';

$helpText = array();
$helpText['testproject'] = isset($_REQUEST['tprojectHelp']) ? $_REQUEST['tprojectHelp'] : '';
$helpText['testsuite'] = isset($_REQUEST['tsuiteHelp']) ? $_REQUEST['tsuiteHelp'] : '';
$nodes = display_children($db,$root_node,$node,$filter_node,$tcprefix,$show_tcases,$operation,$helpText);
echo json_encode($nodes);

/**
 *
 *
 */
function display_children($dbHandler,$root_node,$parent,$filter_node,
                          $tcprefix,$show_tcases = 1,$operation = 'manage',$helpText=array()) 
{             
  static $showTestCaseID;
    
  $tables = tlObjectWithDB::getDBTables(array('tcversions','nodes_hierarchy','node_types'));
  
  $forbidden_parent = array('testproject' => 'none','testcase' => 'testproject', 'testsuite' => 'none');
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
         " AND parent_id = " . intval($parent) .
         " AND NT.description NOT IN " .
         " ('testcase_version','testplan','requirement_spec','requirement'{$filter_node_type}) ";

  if(!is_null($filter_node) && $filter_node > 0 && $parent == $root_node)
  {
    $sql .=" AND NHA.id = " . intval($filter_node);  
  }
  $sql .= " ORDER BY NHA.node_order ";    
    
    
  $nodeSet = $dbHandler->get_recordset($sql);
       
  if($show_tcases)
  {  
    // Get external id, used on test case nodes   
    $sql =  " SELECT DISTINCT tc_external_id,NHA.parent_id " .
            " FROM {$tables['tcversions']} TCV " .
            " JOIN {$tables['nodes_hierarchy']} NHA  ON NHA.id = TCV.id  " .
            " JOIN {$tables['nodes_hierarchy']} NHB ON NHA.parent_id = NHB.id " . 
            " WHERE NHB.parent_id = " . intval($parent) . " AND NHA.node_type_id = 4"; 
    $external = $dbHandler->fetchRowsIntoMap($sql,'parent_id');
  }
    
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
      $path['forbidden_parent'] = 'none';

      $tcase_qty = null;
      switch($row['node_type'])
      {
        case 'testproject':
          // at least on Test Specification seems that we do not execute this piece of code.
          $path['href'] = "javascript:EP({$path['id']})";
          $path['forbidden_parent'] = $forbidden_parent[$row['node_type']];
        break;
              
        case 'testsuite':
          $tcase_qty = $tproject_mgr->count_testcases($row['id']);
          $path['href'] = "javascript:" . $js_function[$row['node_type']]. "({$path['id']})";
          $path['forbidden_parent'] = $forbidden_parent[$row['node_type']];
        break;
                
        case 'testcase':
          $path['href'] = "javascript:" . $js_function[$row['node_type']]. "({$path['id']})";
          $path['forbidden_parent'] = $forbidden_parent[$row['node_type']];
          if(is_null($showTestCaseID))
          {
            $showTestCaseID = config_get('treemenu_show_testcase_id');
          }
          if($showTestCaseID)
          {
            $path['text'] = htmlspecialchars($tcprefix . $external[$row['id']]['tc_external_id'] . ":") . $path['text'];
          }
          $path['leaf'] = true;
        break;
      }
         
      if(!is_null($tcase_qty))
      {
        $path['text'] .= " ({$tcase_qty})";   
      }

      switch($row['node_type'])
      {
        case 'testproject':
        case 'testsuite':
          if( isset($helpText[$row['node_type']]) )
          {
            $path['text'] = '<span title="' . $helpText[$row['node_type']] . '">' . $path['text'] . '</span>';
          }  
        break;
      }

      $nodes[] = $path;                                                                        
    }
  }
  return $nodes;                                                                             
}