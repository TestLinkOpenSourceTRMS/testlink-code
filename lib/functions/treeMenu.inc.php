<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: treeMenu.inc.php,v $
 *
 * @version $Revision: 1.79 $
 * @modified $Date: 2008/10/16 18:50:53 $ by $Author: schlundus $
 * @author Martin Havlat
 *
 * 	This file generates tree menu for test specification and test execution.
 * 	Three kinds of menu component are supported: 
 *                                              LAYERSMENU, DTREE,	and JTREE. 
 *  Used type is defined in config.inc.php.
 * 
 * Rev:
 *      20080705 - franciscom - found another null to replace in order to 
 *                              make menustring good for extjs.
 *                              Fixed json string whe array is empty [null] KO -> [] OK for extjs.
 *
 *      20080629 - franciscom - fixed bug in extjs_renderExecTreeNodeOnOpen()
 *      20080621 - franciscom - changes in exec functions to support ext js tree.
 *      20080510 - franciscom - interface changes get_testplan_nodes_testcount() 
 *      20080508 - franciscom - interface changes get_testproject_nodes_testcount()
 *      20080304 - franciscom - added management of exec_cfg->show_testsuite_contents
 *      20080223 - franciscom - fixed call to get_subtree() on generateTestSpecTree()
 *      20080114 - franciscom - changes to *_renderExecTreeNode*
 *
 *      20080113 - franciscom - changes to *_renderTestSpec* functions
 *                              to manage new external_id and testcase prefix.
 *
 *      20080111 - franciscom - added logic to manage show/hide testcase id
 *      20071229 - franciscom - added new arguments in generateExecTree()
 *                              renderExecTreeNode()
 *      20071111 - franciscom - added contribution to show number of
 *                              testcases with different exec status on DTREE
 *      
 *      20071024 - franciscom - DTREE bug
 *
 *      20071014 - franciscom - generateTestSpecTree() interface changes
 *                              minor change in prepareNode.
 *
 *      20071002 - jbarchibald - BUGID 1051
 *      20070306 - franciscom - BUGID 705 
 *
 **/
require_once(dirname(__FILE__)."/../../config.inc.php");
require_once(dirname(__FILE__)."/../../third_party/dBug/dBug.php");


if (TL_TREE_KIND == 'LAYERSMENU') 
{
	define('TL_MENU_PATH', TL_ABS_PATH . 'third_party' . DIRECTORY_SEPARATOR .'phplayersmenu' . DIRECTORY_SEPARATOR);
	define('TL_MENU_LIB_PATH', TL_MENU_PATH . 'lib' . DIRECTORY_SEPARATOR);
	define('TL_MENU_WWW', 'third_party/phplayersmenu/');

	require_once TL_MENU_LIB_PATH . 'PHPLIB.php';
	require_once TL_MENU_LIB_PATH . 'layersmenu-common.inc.php';
	require_once TL_MENU_LIB_PATH . 'treemenu.inc.php';
}


/** 
 * generate html of tree menu
 *
 * @param string $menustring own menu data
 * @param string $highLight optional
 * @return string generated html/javascript code
 *
 * 20060304 - franciscom - setting config params for icons
 *
 **/
function invokeMenu($menustring, $highLight = "",$target = "workframe")
{
	tLog('invokeMenu started');
	
	if (TL_TREE_KIND == 'LAYERSMENU') 
	{
		$mid = new TreeMenu();

		$mid->setLibjsdir(TL_MENU_PATH . 'libjs' . DIRECTORY_SEPARATOR);
		$mid->setImgwww(TL_MENU_WWW . 'menuimages/');
		
		// needed to be able to set the icon file for a menu item (works only for LEAF nodes)
		$mid->setIcondir(TL_MENU_PATH . 'menuicons/');
		$mid->setIconwww(TL_MENU_WWW . 'menuicons/');
		
		$mid->setIconsize(16, 16);

		$mid->setMenuStructureString($menustring);
		$mid->parseStructureForMenu('treemenu1');
		
		//The method I'm using will color an item in the tree if you pass it a value
		if($highLight != "")
			$mid->setSelectedItemByUrl('treemenu1', $highLight);
    
		//print the client side menu
		$data = $mid->newTreeMenu('treemenu1');
	} 
	else if (TL_TREE_KIND == 'DTREE')
	{
		$data = "<script type='text/javascript'>\n<!--\n";
		$data .= "tlTree = new dTree('tlTree');\n";
		$data .= "tlTree.config.inOrder = true;\n";
		
		// 20071024 - franciscom
		// if ($target)
		//	$data .= "tlTree.config.target = 'workframe';\n";
			
		$data .= $menustring;
		$data .= "document.write(tlTree);\n";
		$data .= "//-->\n</script>\n";
	}
	else if (TL_TREE_KIND == 'JTREE')
	{
		$data = "<script type='text/javascript'>\n<!--\n var TREE_ITEMS = [\n"; 
		$data .= $menustring;
		$data .=  "\n];\n"; //end the product block and whole array
		$data .=  "new tree (TREE_ITEMS, TREE_TPL);";
		$data .= "//-->\n</script>\n";
	}

	return $data;
}


/**
*	strip potential newlines and other unwanted chars from strings
*	Mainly for stripping out newlines, carriage returns, and quotes that were 
*	causing problems in javascript espicially using jtree
*
*	@param string $str
*	@return string string with the newlines removed
*/
function filterString($str)
{
	$str = str_replace(array("\n","\r"), array("",""), $str);
	if (TL_TREE_KIND != 'LAYERSMENU')
		$str = addslashes($str);

	$str = htmlspecialchars($str, ENT_QUOTES);	
	
	return $str;
}

/** 
 * generate data for tree menu of Test Specification
 *
 * 20080501 - franciscom - keyword_id can be an array
 *
 * 20071014 - franciscom - $bForPrinting
 *                         used to choose Javascript function 
 *                         to call when clicking on a tree node
 *                         
 *
 * 20070922 - franciscom - interface changes added $tplan_id,
 * 20070217 - franciscom - added $exclude_branches
 *
 * 20061105 - franciscom - added $ignore_inactive_testcases
 *                         
 * ignore_inactive_testcases: if all test case versions are inactive, 
 *                            the test case will ignored.
 *
 * exclude_branches: map key=node_id
 *
*/
function generateTestSpecTree(&$db,$tproject_id, $tproject_name,
                              $linkto,$bForPrinting=0,$bHideTCs = 0,
                              $tc_action_enabled = 1,
                              $getArguments = '',
                              $keywordsFilter=null,
                              $ignore_inactive_testcases=0,$exclude_branches=null)
{
	$resultsCfg=config_get('results');
	$showTestCaseID=config_get('tree_show_testcase_id');
	$menustring = null;

	$tproject_mgr = new testproject($db);
	$tree_manager = &$tproject_mgr->tree_manager;


	$tcase_node_type = $tree_manager->node_descr_id['testcase'];
	$hash_descr_id = $tree_manager->get_available_node_types();
	$hash_id_descr = array_flip($hash_descr_id);
  $status_descr_code=$resultsCfg['status_code'];
  $status_code_descr=$resultsCfg['code_status'];

  $decoding_hash=array('node_id_descr' => $hash_id_descr,
                       'status_descr_code' =>  $status_descr_code,
                       'status_code_descr' =>  $status_code_descr);
	
	$tcase_prefix=$tproject_mgr->getTestCasePrefix($tproject_id);
	$test_spec = $tproject_mgr->get_subtree($tproject_id,
	                                        testproject::RECURSIVE_MODE,
	                                        testproject::INCLUDE_TESTCASES,
												                  $exclude_branches);
												                  
	// Added root node for test specification -> testproject
	$test_spec['name'] = $tproject_name;
	$test_spec['id'] = $tproject_id;
	$test_spec['node_type_id'] = $hash_descr_id['testproject'];;
		
	$map_node_tccount=array();
	$tplan_tcs=null;
	
	DEFINE('DONT_FILTER_BY_TESTER',0);
	DEFINE('DONT_FILTER_BY_EXEC_STATUS',null);
	
	
	if($test_spec)
	{
		$tck_map = null;  // means no filter

		if(!is_null($keywordsFilter))
		{
			$tck_map = $tproject_mgr->get_keywords_tcases($tproject_id,$keywordsFilter->items,$keywordsFilter->type);
			if( is_null($tck_map) )
			{
			  $tck_map=array();  // means filter everything
			}
		}
    
		$testcase_counters = prepareNode($db,$test_spec,$decoding_hash,$map_node_tccount,
		                                 $tck_map,$tplan_tcs,$bHideTCs,
		                                 DONT_FILTER_BY_TESTER,DONT_FILTER_BY_EXEC_STATUS,
		                                 $ignore_inactive_testcases);

		foreach($testcase_counters as $key => $value)
		{
		  $test_spec[$key]=$testcase_counters[$key];
		}
		$menustring = renderTreeNode(1,$test_spec,$getArguments,$hash_id_descr,
		                             $tc_action_enabled,$linkto,$tcase_prefix,
		                             $bForPrinting,$showTestCaseID);
	}
	return $menustring;
}

//
// Prepares a Node to be displayed in a navigation tree.
// This function is used in the construction of:
//
// - Test project specification -> we want ALL test cases defined in test project.
// - Test execution             -> we only want the test cases linked to a test plan.
//
//
// status: one of the possible execution status of a test case.
//
//
// tp_tcs: map with testcase versions linked to test plan. (TestPlan TestCaseS -> tp_tcs)
//         due to the multiples uses of this function, null has to meanings
//
//         When we want to build a Test Project specification tree,
//         WE SET tp_tcs to NULL, because we are not interested in a test plan.
// 
//         When we want to build a Test execution tree, we dont set tp_tcs deliverately
//         to null, but null can be the result of no tcversion linked.
//
//
// 20071014 - franciscom - added version info fro test cases in return data structure.
//
// 20061105 - franciscom
// ignore_inactive_testcases: useful when building a Test Project Specification tree 
//                            to be used in the add/link test case to Test Plan.
//
//
// 20061030 - franciscom
// tck_map: Test Case Keyword map:
//          null        => no filter
//          empty map   => filter out test case ALWAYS
//          initialized map => filter out test case ONLY if present in map.
//
//
// 20060924 - franciscom
// added argument:
//                $map_node_tccount
//                key => node_id
//                values => node test case count
//                          node name (useful only for debug purpouses
//
//                IMPORTANT: this new argument is not useful for tree rendering
//                           but to avoid duplicating logic to get test case count
//
//
// return: map with keys:
//         'total_count'
//         'passed'  
//         'failed'
//         'blocked'
//         'not run'
//
// 
function prepareNode(&$db,&$node,&$decoding_info,&$map_node_tccount,
                     $tck_map = null,$tplan_tcases = null,$bHideTCs = 0,
                     $assignedTo = 0,$status = null, 
                     $ignore_inactive_testcases=0,$show_tc_id=1)
{
  
	static $hash_id_descr;
	if (!$hash_id_descr)
		$hash_id_descr = $decoding_info['node_id_descr'];
	static $status_descr_code;
  	if (!$status_descr_code)
		$status_descr_code = $decoding_info['status_descr_code'];
  	static $status_code_descr;
  	if (!$status_code_descr)
  		$status_code_descr = $decoding_info['status_code_descr'];
  
	$tcase_counters=array('testcase_count' => 0);
	foreach($status_descr_code as $status_descr => $status_code)
	{
		$tcase_counters[$status_descr]=0;
	}

	$node_type = $hash_id_descr[$node['node_type_id']];
	$tcase_counters['testcase_count']=0;
  
  if ($node_type == 'testcase')
	{
    $viewType=is_null($tplan_tcases) ? 'testSpecTree' : 'executionTree';
		if (!is_null($tck_map))
		{
			if (!isset($tck_map[$node['id']]))
				$node = null;
		}
	
	  if ($node && $viewType=='executionTree')
		{
		  // We are buildind a execution tree.
			if ( !isset($tplan_tcases[$node['id']]) ||
			     ($assignedTo && ($tplan_tcases[$node['id']]['user_id'] != $assignedTo)) ||
			     ($status && ($tplan_tcases[$node['id']]['exec_status'] != $status)) )
			{
				$node = null;
			}
			else
			{
				$node['tcversion_id'] = $tplan_tcases[$node['id']]['tcversion_id'];		
				$node['version'] = $tplan_tcases[$node['id']]['version'];		

			  $sql=" SELECT TCV.tc_external_id AS external_id " .
			       " FROM tcversions TCV " .
			       " WHERE TCV.id=" . $node['tcversion_id'];
			     
			  $result = $db->exec_query($sql);
			  $myrow = $db->fetch_array($result);
				$node['external_id'] = $myrow['external_id'];		

			}
		}
	
		if ($node && $ignore_inactive_testcases)
		{
			// there are active tcversions for this node ???
			// I'm doing this instead of creating a test case manager object, because
			// I think is better for performance.
			//
			// =======================================================================================
			// 20070106 - franciscom
			// Postgres Problems
			// =======================================================================================
			// Problem 1 - SQL Sintax
			//   While testing with postgres
			//   SELECT count(TCV.id) NUM_ACTIVE_VERSIONS   -> Error
			//
			//   At least for what I remember using AS to create COLUMN ALIAS IS REQUIRED and Standard
			//   while AS is NOT REQUIRED (and with some DBMS causes errors) when you want to give a 
			//   TABLE ALIAS
			//
			// Problem 2 - alias cas
			//   At least in my installation the aliases column name is returned lower case, then
			//   PHP fails when:
			//                  if($myrow['NUM_ACTIVE_VERSIONS'] == 0)
			//
			//
			$sql=" SELECT count(TCV.id) AS num_active_versions " .
			     " FROM tcversions TCV, nodes_hierarchy NH " .
			     " WHERE NH.parent_id=" . $node['id'] .
			     " AND NH.id = TCV.id AND TCV.active=1";
			
			$result = $db->exec_query($sql);
			$myrow = $db->fetch_array($result);
			if($myrow['num_active_versions'] == 0)
			{
				$node = null;
			}
		}
		
		if ($node && $viewType=='testSpecTree')
		{
			  $sql=" SELECT DISTINCT(TCV.tc_external_id) AS external_id " .
			       " FROM tcversions TCV, nodes_hierarchy NH " .
			       " WHERE  NH.id = TCV.id " .
			       " AND NH.parent_id=" . $node['id'];
	
		    $result = $db->exec_query($sql);
			  $myrow = $db->fetch_array($result);
				$node['external_id'] = $myrow['external_id'];		
		}
		
		foreach($tcase_counters as $key => $value)
		{
		  $tcase_counters[$key]=0;
		}
		
		$tc_status_descr="not_run";
		if( isset($tplan_tcases[$node['id']]['exec_status']) )
		{
		   $tc_status_code = $tplan_tcases[$node['id']]['exec_status'];
		   $tc_status_descr = $status_code_descr[$tc_status_code];   
		}
  
    $init_value=$node ? 1 : 0;
		$tcase_counters[$tc_status_descr]=$init_value;
		$tcase_counters['testcase_count']=$init_value;
		
		if ($bHideTCs)
			$node = null;
	}
	
	if (isset($node['childNodes']) && $node['childNodes'])
	{
		$childNodes = &$node['childNodes'];
		for($i = 0;$i < sizeof($childNodes);$i++)
		{
			$current = &$childNodes[$i];
			// I use set an element to null to filter out leaf menu items
			if(is_null($current))
				continue;
  		$counters_map = prepareNode($db,$current,$decoding_info,$map_node_tccount,
			                            $tck_map,$tplan_tcases,$bHideTCs,
			                            $assignedTo,$status,
 			                            $ignore_inactive_testcases,$show_tc_id);
      foreach($counters_map as $key => $value)
      {
        $tcase_counters[$key] += $counters_map[$key];   
      }  

		}
    foreach($tcase_counters as $key => $value)
    {
        $node[$key] = $tcase_counters[$key];
    }  
		
		if (isset($node['id']))
		{
			$map_node_tccount[$node['id']] = array(	'testcount' => $node['testcase_count'],
		                                     		  'name'      => $node['name']);
		}
		if ((!is_null($tck_map) || !is_null($tplan_tcases)) && 
		     !$tcase_counters['testcase_count'] && ($node_type != 'testproject'))
		{
			$node = null;
		}
	}
 	else if ($node_type == 'testsuite')
	{
		$map_node_tccount[$node['id']] = array(	'testcount' => 0,
								                            'name' => $node['name']	  );
		
		if (!is_null($tplan_tcases))
			$node = null;
	}
	
	return $tcase_counters;
}


//
// Create the string representation suitable to create a graphic visualization
// of a node, for the type of menu selected.
//
//
function renderTreeNode($level,&$node,$getArguments,$hash_id_descr,
                        $tc_action_enabled,$linkto,$testCasePrefix,
                        $bForPrinting=0,$showTestCaseID)
{
	$node_type = $hash_id_descr[$node['node_type_id']];

	if (TL_TREE_KIND == 'JTREE')
		$menustring = jtree_renderTestSpecTreeNodeOnOpen($node,$node_type,$tc_action_enabled,
		                                                 $bForPrinting,$showTestCaseID,
		                                                 $testCasePrefix);
	else if (TL_TREE_KIND == 'DTREE')
		$menustring = dtree_renderTestSpecTreeNodeOnOpen($node,$node_type,$linkto,
		                                                 $getArguments,$tc_action_enabled,
		                                                 $bForPrinting,$showTestCaseID,
		                                                 $testCasePrefix);
	else 
		$menustring = layersmenu_renderTestSpecTreeNodeOnOpen($node,$node_type,$linkto,$getArguments,
		                                                      $level,$tc_action_enabled,
		                                                      $bForPrinting,$showTestCaseID,
		                                                      $testCasePrefix);
		
	if (isset($node['childNodes']) && $node['childNodes'])
	{
		$childNodes = $node['childNodes'];
		$nChildren = sizeof($childNodes);
		for($i = 0;$i < $nChildren;$i++)
		{
			$current = $childNodes[$i];
			if(is_null($current))
				continue;
			
			$menustring .= renderTreeNode($level+1,$current,$getArguments,$hash_id_descr,
			                              $tc_action_enabled,$linkto,$testCasePrefix,
			                              $bForPrinting,$showTestCaseID);
		}
	}
	if (TL_TREE_KIND == 'JTREE')
		$menustring .= jtree_renderTestSpecTreeNodeOnClose($node,$node_type);
	
	return $menustring;
}



//
// Create the string representation suitable to create a graphic visualization
// of a node, for layersmenu
//
//
//
// rev :
//      20071014 - franciscom - added $bForPrinting
//
function layersmenu_renderTestSpecTreeNodeOnOpen($node,$node_type,$linkto,
                                                 $getArguments,$level,$tc_action_enabled,
                                                 $bForPrinting,$showTestCaseID,$testCasePrefix)
{
	$cfg=config_get('testcase_cfg');

	$pfn = $bForPrinting ? 'TPROJECT_PTS' : 'ETS';
	$name = filterString($node['name']);
	$icon = "";
	$buildLinkTo = 1;
	$dots  = str_repeat('.',$level);
	
	$testcase_count = isset($node['testcase_count']) ? $node['testcase_count'] : 0;
	
  switch($node_type)
  {
	  case 'testproject':
		$label = $name . " ({$testcase_count})";
		$dots = ".";
		$pfn = $bForPrinting ? 'TPROJECT_PTP' : 'EP';
    break;
  
    case 'testcase':
		$icon = "gnome-starthere-mini.png";
		$buildLinkTo = $tc_action_enabled;
		$pfn = 'ET';

    $label='';  
		if($showTestCaseID)
		{
		   if( strlen(trim($testCasePrefix)) > 0 )
       {
            $testCasePrefix .= $cfg->glue_character;
       }
  	   $label .= "<b>{$testCasePrefix}{$node['external_id']}</b>:";
		} 
		$label .= $name;
    break;

    case 'testsuite':
		$label = $name . " ({$testcase_count})";
    break;

	}	
	
	if ($buildLinkTo)
		$myLinkTo = "javascript:{$pfn}({$node['id']})";
	else	
		$myLinkTo = ' ';
		
	$menustring = "{$dots}|{$label}|{$myLinkTo}|{$node_type}". 
		           "|{$icon}||\n";
		
	return $menustring;				
}


//
// Create the string representation suitable to create a graphic visualization
// of a node, for dtree
//
//
//
function dtree_renderTestSpecTreeNodeOnOpen($node,$node_type,$linkto,$getArguments,
                                            $tc_action_enabled,$bForPrinting,
                                            $showTestCaseID,$testCasePrefix)
{
	$cfg=config_get('testcase_cfg');
	$dtreeCounter = $node['id'];

	$parentID = isset($node['parent_id']) ? $node['parent_id'] : -1;
	$name = filterString($node['name']);
	$buildLinkTo = 1;
	
	$pfn = $bForPrinting ? 'TPROJECT_PTS' : 'ETS';
	
	$edit = 'testcase';
	$label = $name;
	$testcase_count = isset($node['testcase_count']) ? $node['testcase_count'] : 0;

  switch($node_type)
  {
	  case 'testproject':
		$pfn = $bForPrinting ? 'TPROJECT_PTP' : 'EP';
		$label = $name ." (" . $testcase_count . ")";
    break;
	
	  case 'testcase':
		$label = "";
		if($showTestCaseID)
		{
 		   if( strlen(trim($testCasePrefix)) > 0 )
       {
            $testCasePrefix .= $cfg->glue_character;
       }
  	   $label .= "<b>{$testCasePrefix}{$node['external_id']}</b>:";
		} 
		$label .= $name;
		
		$pfn = 'ET';
		$buildLinkTo = $tc_action_enabled;
    break;

    default:
		$label = $name ." (" . $testcase_count . ")";
    break;

	}

	if ($buildLinkTo)
		$myLinkTo = "javascript:{$pfn}({$node['id']})";// . $getArguments;
	else
		$myLinkTo = "";
		
		
	$menustring = "tlTree.add(" . $dtreeCounter . ",{$parentID},'" ;
	$menustring .= $label. "','{$myLinkTo}');\n";
				   
	return $menustring;				   
}

//
// Create the string representation suitable to create a graphic visualization
// of a node, for jtree
//
//
//
function jtree_renderTestSpecTreeNodeOnOpen($node,$node_type,$tc_action_enabled,
                                            $bForPrinting,$showTestCaseID,$testCasePrefix)
{
	$cfg=config_get('testcase_cfg');
	$menustring = "['";
	$name = filterString($node['name']);
	$buildLinkTo = 1;
	$pfn = "ET";
	$testcase_count = isset($node['testcase_count']) ? $node['testcase_count'] : 0;	

  switch($node_type)
  {
	  case 'testproject':
		$pfn = $bForPrinting ? 'TPROJECT_PTP' : 'EP';
		$label =  $name . " (" . $testcase_count . ")";
	  break;

	  case 'testsuite':
		$pfn = $bForPrinting ? 'TPROJECT_PTS' : 'ETS';
		$label =  $name . " (" . $testcase_count . ")";	
	  break;
    
	  case 'testcase':
		$buildLinkTo = $tc_action_enabled;
		if (!$buildLinkTo)
			$pfn = "void";

		$label = "";
		if($showTestCaseID)
		{
		   // $label .= "<b>{$node['id']}</b>:";
 		   if( strlen(trim($testCasePrefix)) > 0 )
       {
            $testCasePrefix .= $cfg->glue_character;
       }
  	   $label .= "<b>{$testCasePrefix}{$node['external_id']}</b>:";
		} 
		$label .= $name;
	  break;

  } // switch	
	$menustring = "['{$label}','{$pfn}({$node['id']})',\n";
			
	return $menustring;
}


/*
  function: 

  args :
  
  returns: 

*/
function jtree_renderTestSpecTreeNodeOnClose($node,$node_type)
{
	$menustring =  "],";
	
	return $menustring;
}

/** 
* Creates data for tree menu used on :
*
* Execution of Test Cases
* Remove Test cases from test plan
* 
* 20071002 - jbarchibald - BUGID 1051 - added cf element to parameter list
* 20070204 - franciscom - changed $bForPrinting -> $bHideTCs
*
* operation: string that can take the following values:
*            testcase_execution
*            remove_testcase_from_testplan
*             
*            and changes how the URL's are build.
*
* rev :
*      20080617 - franciscom - return type changed to use extjs tree component
*
*      20080305 - franciscom - interface refactoring
*      20080224 - franciscom - added include_unassigned
*/
function generateExecTree(&$db,&$menuUrl,$tproject_id,$tproject_name,$tplan_id,
                          $tplan_name,$getArguments,$filters,$additionalInfo) 
{
    $treeMenu=new stdClass(); 
    $treeMenu->rootnode=null;
    $treeMenu->menustring='';
    
    
    $treemenu_type=config_get('treemenu_type');
    $resultsCfg=config_get('results');
    $showTestCaseID=config_get('tree_show_testcase_id');
	  $menustring = null;
	  $any_exec_status=null;
    $tplan_tcases=null;
    $tck_map = null;

    $keyword_id = $filters->keyword_id;
    $keywordsFilterType = $filters->keywordsFilterType;
    
    $tc_id = $filters->tc_id; 
    $build_id = $filters->build_id; 
    $bHideTCs = $filters->hide_testcases;
    $assignedTo = $filters->assignedTo; 
    $status = $filters->status;
    $cf_hash = $filters->cf_hash;
    $include_unassigned = $filters->include_unassigned;
    $show_testsuite_contents = $filters->show_testsuite_contents;
    $urgencyImportance = isset($filters->urgencyImportance) ? $filters->urgencyImportance : null;
    $useCounters=$additionalInfo->useCounters;
    $useColors=$additionalInfo->useColours;

    $tplan_mgr = new testplan($db);
	  $tproject_mgr = new testproject($db);
    $tcase_mgr = new testcase($db);
	  
	  $tree_manager = $tplan_mgr->tree_manager;
	  $tcase_node_type = $tree_manager->node_descr_id['testcase'];
	  $hash_descr_id = $tree_manager->get_available_node_types();

	  $hash_id_descr = array_flip($hash_descr_id);
	  
	  $status_descr_code=$resultsCfg['status_code'];
    $status_code_descr=$resultsCfg['code_status'];

    $decoding_hash=array('node_id_descr' => $hash_id_descr,
                         'status_descr_code' =>  $status_descr_code,
                         'status_code_descr' =>  $status_code_descr);

 	  $tcase_prefix = $tproject_mgr->getTestCasePrefix($tproject_id);

    $nt2exclude=array('testplan' => 'exclude_me',
	                    'requirement_spec'=> 'exclude_me',
	                    'requirement'=> 'exclude_me');
    
    $nt2exclude_children=array('testcase' => 'exclude_my_children',
												       'requirement_spec'=> 'exclude_my_children');
   
    $order_cfg=array("type" =>'exec_order',"tplan_id" => $tplan_id);
	  $test_spec = $tree_manager->get_subtree($tproject_id,$nt2exclude,$nt2exclude_children,
	                                          null,'',RECURSIVE_MODE,$order_cfg);

	  $test_spec['name'] = $tproject_name . " / " . $tplan_name;  // To be discussed
	  $test_spec['id'] = $tproject_id;
	  $test_spec['node_type_id'] = $hash_descr_id['testproject'];
	  $map_node_tccount = array();
    
	  if($test_spec)
	  {
	  	  if(is_null($tc_id) || $tc_id >= 0)
	  	  {
            $keywordSet=$keyword_id;
            $testCaseSet=$tc_id;
            $doFilterByKeyword=(!is_null($keywordSet) && $keywordSet > 0) ? true : false;
    	      
	  	      if($doFilterByKeyword)
	  	      {
	  	      	$tck_map = $tproject_mgr->get_keywords_tcases($tproject_id,$keywordSet,$keywordsFilterType);
	          }
            
            // Multiple step algoritm to apply keyword filter on type=AND
            // get_linked_tcversions filters by keyword ALWAYS in OR mode.
	          $tplan_tcases = $tplan_mgr->get_linked_tcversions($tplan_id,$testCaseSet,$keywordSet,
	                                                            null,$assignedTo,$status,$build_id,
                                                              $cf_hash,$include_unassigned,$urgencyImportance);
            
	          if($doFilterByKeyword && $keywordsFilterType == 'AND')
	          {
              $filteredSet=$tcase_mgr->filterByKeyword(array_keys($tplan_tcases),$keyword_id,$keywordsFilterType);
              $testCaseSet=array_keys($filteredSet);   
              
  	          $tplan_tcases = $tplan_mgr->get_linked_tcversions($tplan_id,$testCaseSet);
	          }
	      }   
        // --------------------------------------------------------------------------------------

	      if (is_null($tplan_tcases))
	      {
	      	$tplan_tcases = array();
	  	  }
	  	  
	  	  
	  	  // 20080224 - franciscom - 
	  	  // After reviewing code, seems that assignedTo has no sense because tp_tcs
	  	  // has been filtered.
	  	  // Then to avoid changes to prepareNode() due to include_unassigned,
	  	  // seems enough to set assignedTo to 0, if include_unassigned==true
	  	  $assignedTo= $include_unassigned ? 0 :$assignedTo;
	  	  
	  	  $bForPrinting=$bHideTCs;
	  	  $testcase_counters = prepareNode($db,$test_spec,$decoding_hash,$map_node_tccount,
	  	                                   $tck_map,$tplan_tcases,$bHideTCs,$assignedTo,$status);
        
	  	  foreach($testcase_counters as $key => $value)
	  	  {
	  	    $test_spec[$key]=$testcase_counters[$key];
	  	  }
	      
	      // 20071111 - franciscom
	      // added map $tplan_tcases.
	      // key -> testcase id.
	      // value -> map with info about execution status
	      //
	  	  $menustring = renderExecTreeNode(1,$test_spec,$tplan_tcases,$getArguments,$hash_id_descr,1,
	  	                                   $menuUrl,$bHideTCs,$useCounters,$useColors,
	  	                                   $showTestCaseID,$tcase_prefix,$show_testsuite_contents);
	  }
	  
 	  if($treemenu_type=='EXTJS')
 	  {
	    $treeMenu->rootnode=new stdClass();
	    $treeMenu->rootnode->name=$test_spec['text'];
	    $treeMenu->rootnode->id=$test_spec['id'];
	    $treeMenu->rootnode->leaf=$test_spec['leaf'];
	    $treeMenu->rootnode->text=$test_spec['text'];
      $treeMenu->rootnode->position=$test_spec['position'];	    
	    $treeMenu->rootnode->href=$test_spec['href'];

 	    // Change key ('childNodes')  to the one required by Ext JS tree.
	    $dummy_stringA=str_ireplace('childNodes', 'children', json_encode($test_spec['childNodes'])); 
	    
	    // Remove null elements (Ext JS tree do not like it ).
	    $dummy_stringB=str_ireplace('null,', '', $dummy_stringA); 
      $dummy_string=str_ireplace(',null', '', $dummy_stringB); 
      $menustring=str_ireplace('null', '', $dummy_string); 
      
	    $treeMenu->menustring=$menustring;
	    	    
	  }
	  else
	  {
	      $treeMenu->menustring=$menustring;  
	  }
	  
	  return $treeMenu;
}


/*
  function: renderExecTreeNode 

  args : level:
         node: reference to recursive map
         tcases_map: reference to map that contains info about testcase exec status
                     when node is of testcase type.
            
         getArguments:
         hash_id_descr:
         tc_action_enabled:
         linkto:
         bHideTCs: 1 -> hide testcase
  
  returns: 

  rev : 20071229 - franciscom
        added $useCounters,$useColors
*/
function renderExecTreeNode($level,&$node,&$tcase_node,$getArguments,$hash_id_descr,
                            $tc_action_enabled,$linkto,$bHideTCs,$useCounters,$useColors,
                            $showTestCaseID,$testCasePrefix,$showTestSuiteContents)
{
	$node_type = $hash_id_descr[$node['node_type_id']];

  

  switch(TL_TREE_KIND)
  {
      case 'JTREE':
		      $menustring = jtree_renderExecTreeNodeOnOpen($node,$node_type,$tcase_node,
		                                                   $tc_action_enabled,$bHideTCs,
		                                                   $useCounters,$useColors,$showTestCaseID,
		                                                   $testCasePrefix,$showTestSuiteContents);
      break;
      
      case 'DTREE':
	        $menustring = dtree_renderExecTreeNodeOnOpen($node,$node_type,$tcase_node,
	                                                     $linkto,$getArguments,
	                                                     $tc_action_enabled,$bHideTCs,
	                                                     $useCounters,$useColors,$showTestCaseID,
	                                                     $testCasePrefix,$showTestSuiteContents);
      break;
           
      case 'LAYERSMENU':
          $menustring = layersmenu_renderExecTreeNodeOnOpen($node,$node_type,$tcase_node,
	       	                                                  $linkto,$getArguments,$level,
	       	                                                  $tc_action_enabled,$bHideTCs,
	       	                                                  $useCounters,$useColors,$showTestCaseID,
	       	                                                  $testCasePrefix,$showTestSuiteContents);
	       	                                                  
      break;
      
      case 'EXTJS':
         $menustring='';  // just to silence PHP warnings
         extjs_renderExecTreeNodeOnOpen($node,$node_type,$tcase_node,
	                                      $tc_action_enabled,$bHideTCs,
	                                      $useCounters,$useColors,$showTestCaseID,
	                                      $testCasePrefix,$showTestSuiteContents);
      break;
      
            
    
  }
  
	if (isset($node['childNodes']) && $node['childNodes'])
	{
	  // 20080615 - franciscom - need to work always original object
	  //                         in order to change it's values using reference .
	  // Can not assign anymore to intermediate variables.
	  //
    $nodes_qty = sizeof($node['childNodes']);
		$skipped=0;
		for($idx = 0;$idx <$nodes_qty ;$idx++)
		{
			if(is_null($node['childNodes'][$idx]))
			{
			  $skipped++;
				continue;
			}
			$menustring .= renderExecTreeNode($level+1,$node['childNodes'][$idx],$tcase_node,
			                                  $getArguments,$hash_id_descr,
			                                  $tc_action_enabled,$linkto,$bHideTCs,
			                                  $useCounters,$useColors,$showTestCaseID,
			                                  $testCasePrefix,$showTestSuiteContents);
		}
		// if( $skipped == $nodes_qty )
		// {
		//     // EXTJS tree component do not like empty array
		//     unset($node['childNodes']);  
		// }
	}
  else if (TL_TREE_KIND == 'EXTJS')
  {
    unset($node['childNodes']);
    $node['leaf']=true;;
  }
  
	if (TL_TREE_KIND == 'JTREE')
		$menustring .= jtree_renderTestSpecTreeNodeOnClose($node,$node_type);
	
	return $menustring;
}


/*
  function: 

  args :
  
  returns: 
  
  rev: 20071112 - interface changes - added $tcase_node
      

*/
function layersmenu_renderExecTreeNodeOnOpen($node,$node_type,$tcase_node,$linkto,$getArguments,$level,
                                             $tc_action_enabled,$bForPrinting,
                                             $useCounters=1,$useColors=1,$showTestCaseID=1,
                                             $testCasePrefix,$showTestSuiteContents=1)
{
	$cfg=config_get('testcase_cfg');
	$resultsCfg=config_get('results');
	
  $status_descr_code=$resultsCfg['status_code'];
  $status_code_descr=$resultsCfg['code_status'];
	$status_verbose=$resultsCfg['status_label'];


	$pfn = "ST";
	$name = filterString($node['name']);
	$label = $name;
	$icon = "";
	$buildLinkTo = 1;
	$dots  = str_repeat('.',$level);
	
	$testcase_count = isset($node['testcase_count']) ? $node['testcase_count'] : 0;
	$create_counters=0;
	$versionID = 0;
	
  switch($node_type)
  {
	  case 'testproject':
		$pfn = $bForPrinting ? 'TPLAN_PTP' : 'SP';
    $create_counters=1;
		$dots = ".";
	  break;
	  
	  case "testcase":
		$status_code = $tcase_node[$node['id']]['exec_status'];
  	$status_descr=$status_code_descr[$status_code];

		if (!$tc_action_enabled)
			$pfn = "void";

		$icon = "gnome-starthere-mini.png";

   	$status_code = $tcase_node[$node['id']]['exec_status'];
 	  $status_descr=$status_code_descr[$status_code];
    $css_class= $useColors ? (" class=\"{$status_descr}\" ") : '';   
		$label = "<span {$css_class} " . '  title="' . lang_get($status_verbose[$status_descr]) . '">';

		if($showTestCaseID)
		{
		   if( strlen(trim($testCasePrefix)) > 0 )
       {
            $testCasePrefix .= $cfg->glue_character;
       }
  	   $label .= "<b>{$testCasePrefix}{$node['external_id']}</b>:";
		} 
		$label .= $name . "</span>";
		$versionID = $node['tcversion_id'];
	  break;

	  case "testsuite":
	  if( $bForPrinting )
	  {
	      $pfn = 'TPLAN_PTS';
	  }
	  else
	  {
	     $pfn = $showTestSuiteContents ? 'STS' : null; 
	  }
		$create_counters=1;
	  break;

	}	
	
  if($create_counters)
  {
		$label = $name ." (" . $testcase_count . ")";
    if($useCounters)
    {
        $add_html=create_counters_info($node,$useColors);
		    $label .= $add_html; 
    }
  }

	$myLinkTo = $linkto."?level={$node_type}&id={$node['id']}".$versionID.$getArguments;
	if ($buildLinkTo && !is_null($pfn))
		$myLinkTo = "javascript:{$pfn}({$node['id']},{$versionID})";
	else	
		$myLinkTo = ' ';

	$menustring = "{$dots}|{$label}|{$myLinkTo}|{$node_type}|{$icon}||\n";
	return $menustring;				
}

/*
  function: dtree_renderExecTreeNodeOnOpen

  args :
  
  returns: 

  rev : 20071229 - franciscom
        added useCounters and useColors
  
*/
function dtree_renderExecTreeNodeOnOpen($node,$node_type,$tcase_node,$linkto,$getArguments,
                                        $tc_action_enabled,$bForPrinting,
                                        $useCounters=1,$useColors=1,$showTestCaseID=1,
                                        $testCasePrefix,$showTestSuiteContents=1)
{
 	$cfg=config_get('testcase_cfg');
	$resultsCfg=config_get('results');
	
  $status_descr_code=$resultsCfg['status_code'];
  $status_code_descr=$resultsCfg['code_status'];
	$status_verbose=$resultsCfg['status_label'];
	
	$dtreeCounter = $node['id'];

	$parentID = isset($node['parent_id']) ? $node['parent_id'] : -1;
	$name = filterString($node['name']);
	$buildLinkTo = 1;
	
	$pfn = 'ST';
	$edit = 'testcase';
	$label = $name;
	$testcase_count = isset($node['testcase_count']) ? $node['testcase_count'] : 0;
	$versionID = 0;
	
	$create_counters=0;
	switch($node_type)
	{
	    case 'testproject':
	    $create_counters = 1;
		  $pfn = $bForPrinting ? 'TPLAN_PTP' : 'SP';
		  break;
  
	    case 'testcase':
	  	$status_code = $tcase_node[$node['id']]['exec_status'];
  	  $status_descr=$status_code_descr[$status_code];
  		
      $css_class= $useColors ? (" class=\"{$status_descr}\" ") : '';   
		  $label = "<span {$css_class} " . '  title="' . lang_get($status_verbose[$status_descr]) . '">';
		  
		  if($showTestCaseID)
		  {
		     if( strlen(trim($testCasePrefix)) > 0 )
         {
            $testCasePrefix .= $cfg->glue_character;
         }
  	     $label .= "<b>{$testCasePrefix}{$node['external_id']}</b>:";
		  } 
		  $label .= $name . "</span>";
		         
		  $versionID = $node['tcversion_id'];
		  $buildLinkTo = $tc_action_enabled;
		  if (!$buildLinkTo)
			  $pfn = "void";
			break;
	
	    default:		  
	    if( $bForPrinting )
	    {
	        $pfn = 'TPLAN_PTS';
	    }
	    else
	    {
	       $pfn = $showTestSuiteContents ? 'STS' : "void"; 
	    }
		  $create_counters=1;
		  break;
	
	} // switch

  if($create_counters)
  {
		$label = $name ." (" . $testcase_count . ")";
    if($useCounters)
    {
        $add_html=create_counters_info($node,$useColors);
	      $label .= $add_html; 
	  }
  }


	if ($buildLinkTo)
		$myLinkTo = "javascript:{$pfn}({$node['id']},{$versionID})";
	else
		$myLinkTo = "";
		
		
	$menustring = "tlTree.add(" . $dtreeCounter . ",{$parentID},'" ;
	$menustring .= $label. "','{$myLinkTo}');\n";
				   
	return $menustring;				   
}

/*
  function: 

  args :
  
  returns: 

  rev:
      20080110 - franciscom - added $showTestCaseID
*/
function jtree_renderExecTreeNodeOnOpen($node,$node_type,$tcase_node,$tc_action_enabled,
                                        $bForPrinting,$useCounters=1,$useColors=1,
                                        $showTestCaseID=1,$testCasePrefix,$showTestSuiteContents=1)
{
 	$cfg=config_get('testcase_cfg');
	$resultsCfg=config_get('results');
	
  $status_descr_code=$resultsCfg['status_code'];
  $status_code_descr=$resultsCfg['code_status'];
	$status_verbose=$resultsCfg['status_label'];
	
	$menustring = "['";
	$name = filterString($node['name']);
	$buildLinkTo = 1;
	$pfn = "ST";
	$testcase_count = isset($node['testcase_count']) ? $node['testcase_count'] : 0;	
	$create_counters=0;
	$versionID = 0;
	
  switch($node_type)
  {
	  case 'testproject':
 		$create_counters=1;
		$pfn = $bForPrinting ? 'TPLAN_PTP' : 'SP';
		$label =  $name . " (" . $testcase_count . ")";
	  break;

	  case 'testsuite':
		$create_counters=1;
		$label =  $name . " (" . $testcase_count . ")";	
	  if( $bForPrinting )
	  {
	      $pfn = 'TPLAN_PTS';
	  }
	  else
	  {
	     $pfn = $showTestSuiteContents ? 'STS' : "void"; 
	  }
	  break;

	  case 'testcase':
		$buildLinkTo = $tc_action_enabled;
		if (!$buildLinkTo)
			$pfn = "void";

	  $status_code = $tcase_node[$node['id']]['exec_status'];
	  $status_descr = $status_code_descr[$status_code];

    $css_class= $useColors ? (" class=\"{$status_descr}\" ") : '';   
		$label = "<span {$css_class} " . '  title="' . lang_get($status_verbose[$status_descr]) . '">';
		
		if($showTestCaseID)
		{
 		   if( strlen(trim($testCasePrefix)) > 0 )
       {
            $testCasePrefix .= $cfg->glue_character;
       }
  	   $label .= "<b>{$testCasePrefix}{$node['external_id']}</b>:";
		} 
		$label .= $name . "</span>";
		$versionID = $node['tcversion_id'];
    break;
	}

  // -------------------------------------------------------------------------------
  // 20080305 - franciscom
  if($create_counters)
  {
		$label = $name ." (" . $testcase_count . ")";
    if($useCounters)
    {
        $add_html=create_counters_info($node,$useColors);
	      $label .= $add_html; 
	  }
  }
  // -------------------------------------------------------------------------------
	$menustring = "['{$label}','{$pfn}({$node['id']},{$versionID})',\n";
			
	return $menustring;
}


//
// Returns a map:
//         key    => node_id
//         values => node test case count considering test cases presents
//                   in the nodes of the subtree that starts on node_id
//                   Means test case can not be sons/daughters of node_id.
// 
//                   node name (useful only for debug purpouses).
//
function get_testproject_nodes_testcount(&$db,$tproject_id, $tproject_name,
                                         $keywordsFilter=null)
{
	$tproject_mgr = new testproject($db);
	$tree_manager = &$tproject_mgr->tree_manager;

	$tcase_node_type = $tree_manager->node_descr_id['testcase'];
	$hash_descr_id = $tree_manager->get_available_node_types();
	$hash_id_descr = array_flip($hash_descr_id);

	$resultsCfg = config_get('results');
	$status_descr_code = $resultsCfg['status_code'];
	$status_code_descr = $resultsCfg['code_status'];
  
	$decoding_hash = array('node_id_descr' => $hash_id_descr,
                       'status_descr_code' =>  $status_descr_code,
                       'status_code_descr' =>  $status_code_descr);
	$test_spec = $tproject_mgr->get_subtree($tproject_id,RECURSIVE_MODE);
	
	$test_spec['name'] = $tproject_name;
	$test_spec['id'] = $tproject_id;
	$test_spec['node_type_id'] = 1;
	
	$map_node_tccount = array(); 
	$tplan_tcases = null;
	
	if($test_spec)
	{
		$tck_map = null;
		if( !is_null($keywordsFilter) )
		{
			$tck_map = $tproject_mgr->get_keywords_tcases($tproject_id,
			                                              $keywordsFilter->items,$keywordsFilter->type);
		}	
		$testcase_counters = prepareNode($db,$test_spec,$decoding_hash,$map_node_tccount,
		                                 $tck_map,$tplan_tcases,SHOW_TESTCASES);
	
		$test_spec['testcase_count'] = $testcase_counters['testcase_count'];
	}

	return $map_node_tccount;
}

// Returns a map:
//         key    => node_id
//         values => node test case count considering test cases presents
//                   in the nodes of the subtree that starts on node_id
//                   Means test case can not be sons/daughters of node_id.
// 
//                   node name (useful only for debug purpouses).
//
function get_testplan_nodes_testcount(&$db,$tproject_id, $tproject_name,
                                      $tplan_id,$tplan_name,$keywordsFilter=null)
{
	$tplan_mgr = new testplan($db);
	$tproject_mgr = new testproject($db);
	
	$tree_manager = $tplan_mgr->tree_manager;
	$tcase_node_type = $tree_manager->node_descr_id['testcase'];
	$hash_descr_id = $tree_manager->get_available_node_types();
	$hash_id_descr = array_flip($hash_descr_id);

	$resultsCfg=config_get('results');
  $status_descr_code=$resultsCfg['status_code'];
  $status_code_descr=$resultsCfg['code_status'];
  
  $decoding_hash=array('node_id_descr' => $hash_id_descr,
                       'status_descr_code' =>  $status_descr_code,
                       'status_code_descr' =>  $status_code_descr);

	$test_spec = $tproject_mgr->get_subtree($tproject_id,RECURSIVE_MODE);
	
  // 20080510 - franciscom
	$tplan_tcases = $tplan_mgr->get_linked_tcversions($tplan_id,0,$keywordsFilter->items);
	if (is_null($tplan_tcases))
		$tplan_tcases = array();
	
	$test_spec['name'] = $tproject_name;
	$test_spec['id'] = $tproject_id;
	$test_spec['node_type_id'] = $hash_descr_id['testproject'];
	$map_node_tccount=array(); 
	
	if($test_spec)
	{
		$tck_map = null;

	  // 20080510 - franciscom 
		if(!is_null($keywordsFilter))
		{
			$tck_map = $tproject_mgr->get_keywords_tcases($tproject_id,
			                                               $keywordsFilter->items,$keywordsFilter->type);
		}	
  	$testcase_counters = prepareNode($db,$test_spec,$decoding_hash,$map_node_tccount,
		                                 $tck_map,$tplan_tcases,SHOW_TESTCASES);
		
		$test_spec['testcase_count'] = $testcase_counters['testcase_count'];
	}
	return($map_node_tccount);
}


/*
  function: 

  args:
  
  returns: 

*/
function create_counters_info(&$node,$useColors)
{
    $resultsCfg=config_get('results');
    
    // I will add not_run if not exists
		$keys2display=array('not_run' => 'not_run');
		
		foreach($resultsCfg['status_label_for_exec_ui'] as $key => $value)
		{
		    if( $key != 'not_run')
		    {
		        $keys2display[$key]=$key;  
		    }  
		}
	  $status_verbose=$resultsCfg['status_label']; //config_get('tc_status_verbose_labels');

		$add_html='';
		foreach($keys2display as $key => $value)
		{
      if( isset($node[$key]) )
      {
        $css_class= $useColors ? (" class=\"{$key}\" ") : '';   
        $add_html .= "<span {$css_class} " . ' title="' . lang_get($status_verbose[$key]) . '">' . 
                     $node[$key] . "</span>,";
		  }
		}
	  $add_html = "(" . rtrim($add_html,",") . ")"; 
    return $add_html;
}




// 20080615 - francisco.mancardi@gruppotesi.com
// VERY IMPORTANT:
// node must be passed BY REFERENCE
//
// rev: 20080629 - franciscom - fixed bug missing argument for call to ST
//
function extjs_renderExecTreeNodeOnOpen(&$node,$node_type,$tcase_node,$tc_action_enabled,
                                        $bForPrinting,$useCounters=1,$useColors=1,
                                        $showTestCaseID=1,$testCasePrefix,$showTestSuiteContents=1)
{
	$cfg=config_get('testcase_cfg');
	$resultsCfg=config_get('results');
	  
	$status_descr_code=$resultsCfg['status_code'];
	$status_code_descr=$resultsCfg['code_status'];
	$status_verbose=$resultsCfg['status_label'];

	$name = filterString($node['name']);
	$buildLinkTo = 1;
	$pfn = "ST";
	$testcase_count = isset($node['testcase_count']) ? $node['testcase_count'] : 0;	
	$create_counters=0;
	$versionID = 0;

  $node['leaf']=false;

  switch($node_type)
  {
	  case 'testproject':
 		$create_counters=1;
		$pfn = $bForPrinting ? 'TPLAN_PTP' : 'SP';
		$label =  $name . " (" . $testcase_count . ")";
	  break;

	  case 'testsuite':
		$create_counters=1;
		$label =  $name . " (" . $testcase_count . ")";	
	  if( $bForPrinting )
	  {
	      $pfn = 'TPLAN_PTS';
	  }
	  else
	  {
	     $pfn = $showTestSuiteContents ? 'STS' : null; 
	  }
	  break;

	  case 'testcase':
    $node['leaf']=true;
		$buildLinkTo = $tc_action_enabled;
		if (!$buildLinkTo)
			$pfn = null;

	  $status_code = $tcase_node[$node['id']]['exec_status'];
	  $status_descr = $status_code_descr[$status_code];

    $css_class= $useColors ? (" class=\"{$status_descr}\" ") : '';   
		
		// @TODO - francisco - 20080621
		// Need to ask Martin to help to fix CSS conflicts with ext js
		//
		$css_class='';
		$label = "<span {$css_class} " . '  title="' . lang_get($status_verbose[$status_descr]) . '">';
		
		if($showTestCaseID)
		{
			if(strlen(trim($testCasePrefix)))
				$testCasePrefix .= $cfg->glue_character;
			$label .= "<b>".htmlspecialchars($testCasePrefix.$node['external_id'])."</b>:";
		} 
		$label .= $name . "</span>";
		$versionID = $node['tcversion_id'];
    break;
	}

  // -------------------------------------------------------------------------------
  // 20080305 - franciscom
  if($create_counters)
  {
		$label = $name ." (" . $testcase_count . ")";
    if($useCounters)
    {
		    // @TODO - francisco - 20080621
		    // Need to ask Martin to help to fix CSS conflicts with ext js
		    //
        // $add_html=create_counters_info($node,$useColors);
        $add_html=create_counters_info($node,false);        
	      $label .= $add_html; 
	  }
  }
  // -------------------------------------------------------------------------------
  $node['text']=$label;
  $node['position']=isset($node['node_order']) ? $node['node_order'] : 0;
  $node['href']=is_null($pfn)? '' : "javascript:{$pfn}({$node['id']},{$versionID})";
  
  // Remove useless keys
  foreach($status_descr_code as $key => $code)
  {
     if(isset($node[$key]))
     {
        unset($node[$key]); 
     }  
  }

  $key2del=array('node_type_id','parent_id','node_order','node_table',
                 'tcversion_id','external_id','version','testcase_count');  
  foreach($key2del as $key)
  {
      if(isset($node[$key]))
      {
          unset($node[$key]); 
      }  
  }
}
?>