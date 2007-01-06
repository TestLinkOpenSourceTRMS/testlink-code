<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: treeMenu.inc.php,v $
 *
 * @version $Revision: 1.34 $
 * @modified $Date: 2007/01/06 15:16:26 $ by $Author: franciscom $
 * @author Martin Havlat
 *
 * 	This file generates tree menu for test specification and test execution.
 * 	Three kinds of menu component are supported: LAYERSMENU (default), DTREE,
 * 	and JTREE. Used type is defined in config.inc.php.
 * 
 * Revisions:
 *
 * 20051011 - MHT - minor refactorization, header update
 * 20051118 - scs - testplanname was not filtered (JS-Error in certain cases)
 * 20060304 - franciscom - changes on invokeMenu()
 * 20060305 - franciscom - towards TL 1.7
 * 20060503 - franciscom - moved here generateExecTree()
 * 20060924 - franciscom - added get_nodes_testcount()
 *                         changes to prepareNode() in order to use it in get_nodes_testcount
 *
 * 20061105 - franciscom - interface changes to prepareNode()
 *
 **/
require_once(dirname(__FILE__)."/../../config.inc.php");


if (TL_TREE_KIND == 'LAYERSMENU') 
{
	define('TL_MENU_PATH', TL_ABS_PATH . 'third_party' . DS .'phplayersmenu' . DS);
	define('TL_MENU_LIB_PATH', TL_MENU_PATH . 'lib' . DS);
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
function invokeMenu($menustring, $highLight = "")
{
	tLog('invokeMenu started');
	
	if (TL_TREE_KIND == 'LAYERSMENU') 
	{
		$mid = new TreeMenu();

		$mid->setLibjsdir(TL_MENU_PATH . 'libjs' . DS);
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
		$data .= "tlTree.config.target = 'workframe';\n";
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
 * 20061105 - franciscom - added $ignore_inactive_testcases
 *                         
 * ignore_inactive_testcases: if all test case versions are inactive, 
 *                            the test case will ignored.
 *
*/
function generateTestSpecTree(&$db,$tproject_id, $tproject_name,$linkto, $bHideTCs, 
                              $tc_action_enabled = 1,$getArguments = '',$keyword_id = 0,
                              $ignore_inactive_testcases=0)
{
	$menustring = null;

	$tproject_mgr = new testproject($db);
	$tree_manager = &$tproject_mgr->tree_manager;

	$tcase_node_type = $tree_manager->node_descr_id['testcase'];
	$hash_descr_id = $tree_manager->get_available_node_types();
	$hash_id_descr = array_flip($hash_descr_id);
	
	$test_spec = $tree_manager->get_subtree($tproject_id,array('testplan'=>'exclude me'),
												                  array('testcase'=>'exclude my children'),null,null,true);
	$test_spec['name'] = $tproject_name;
	$test_spec['id'] = $tproject_id;
	$test_spec['node_type_id'] = 1;
	
	$map_node_tccount=array();
	$tplan_tcs=null;
	
	DEFINE('DONT_FILTER_BY_TESTER',0);
	DEFINE('DONT_FILTER_BY_EXEC_STATUS',null);
	
	
	
	if($test_spec)
	{
		$tck_map = null;  // means no filter
		if($keyword_id)
		{
			$tck_map = $tproject_mgr->get_keywords_tcases($tproject_id,$keyword_id);
			if( is_null($tck_map) )
			{
			  $tck_map=array();  // means filter everything
			}
		}
		$testcase_count = prepareNode($db,$test_spec,$hash_id_descr,$map_node_tccount,
		                              $tck_map,$tplan_tcs,$bHideTCs,
		                              DONT_FILTER_BY_TESTER,DONT_FILTER_BY_EXEC_STATUS,
		                              $ignore_inactive_testcases);

		
		$test_spec['testcase_count'] = $testcase_count;
		$menustring = renderTreeNode(1,$test_spec,$getArguments,$hash_id_descr,$tc_action_enabled,$linkto);
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
function prepareNode(&$db,&$node,&$hash_id_descr,&$map_node_tccount,
                     $tck_map = null,$tp_tcs = null,$bHideTCs = 0,
                     $assignedTo = 0,$status = null, $ignore_inactive_testcases=0)
{
	$nodeDesc = $hash_id_descr[$node['node_type_id']];
  	
	$nTestCases = 0;
	if ($nodeDesc == 'testcase')
	{
		if (!is_null($tck_map))
		{
			if (!isset($tck_map[$node['id']]))
				$node = null;
		}
	  	if ($node && !is_null($tp_tcs))
		{
		  // We are buildind a execution tree.
			if (!isset($tp_tcs[$node['id']]))
			{
				$node = null;
			}
			else if ($assignedTo && ($tp_tcs[$node['id']]['user_id'] != $assignedTo))
			{
				$node = null;
			}
			else if ($status && ($tp_tcs[$node['id']]['exec_status'] != $status))
			{
				$node = null;
			}
			else
			{
				$node['tcversion_id'] = $tp_tcs[$node['id']]['tcversion_id'];		
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
			//   While testing witrh postgres
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
		
		$nTestCases = $node ? 1 : 0;
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
      
			$nTestCases += prepareNode($db,$current,$hash_id_descr,$map_node_tccount,
			                           $tck_map,$tp_tcs,$bHideTCs,$assignedTo,$status,
 			                           $ignore_inactive_testcases);

		}
		$node['testcase_count'] = $nTestCases;
		
		if (isset($node['id']))
		{
			$map_node_tccount[$node['id']] = array(	'testcount' => $node['testcase_count'],
		                                     		'name'      => $node['name']
													);
		}
		if ((!is_null($tck_map) || !is_null($tp_tcs)) && !$nTestCases && ($nodeDesc != 'testproject'))
		{
			$node = null;
		}
	}
 	else if ($nodeDesc == 'testsuite')
	{
		$map_node_tccount[$node['id']] = array(	'testcount' => 0,
								                'name'      => $node['name']
											  );
		
		if (!is_null($tp_tcs))
			$node = null;
	}
	
	return $nTestCases;
}


//
// Create the string representation suitable to create a graphic visualization
// of a node, for the type of menu selected.
//
//
function renderTreeNode($level,&$node,$getArguments,$hash_id_descr,$tc_action_enabled,$linkto)
{
	$nodeDesc = $hash_id_descr[$node['node_type_id']];

	if (TL_TREE_KIND == 'JTREE')
		$menustring = jtree_renderTestSpecTreeNodeOnOpen($node,$nodeDesc,$tc_action_enabled);
	else if (TL_TREE_KIND == 'DTREE')
		$menustring = dtree_renderTestSpecTreeNodeOnOpen($node,$nodeDesc,$linkto,$getArguments,$tc_action_enabled);
	else 
		$menustring = layersmenu_renderTestSpecTreeNodeOnOpen($node,$nodeDesc,$linkto,$getArguments,$level,$tc_action_enabled);
		
	if (isset($node['childNodes']) && $node['childNodes'])
	{
		$childNodes = $node['childNodes'];
		$nChildren = sizeof($childNodes);
		for($i = 0;$i < $nChildren;$i++)
		{
			$current = $childNodes[$i];
			if(is_null($current))
				continue;
			
			$menustring .= renderTreeNode($level+1,$current,$getArguments,$hash_id_descr,$tc_action_enabled,$linkto);
		}
	}
	if (TL_TREE_KIND == 'JTREE')
		$menustring .= jtree_renderTestSpecTreeNodeOnClose($node,$nodeDesc);
	
	return $menustring;
}



//
// Create the string representation suitable to create a graphic visualization
// of a node, for layersmenu
//
//
//
function layersmenu_renderTestSpecTreeNodeOnOpen($node,$nodeDesc,$linkto,$getArguments,$level,$tc_action_enabled)
{
	$name = filterString($node['name']);
	$label = $name;
	$icon = "";
	$buildLinkTo = 1;
	$dots  = str_repeat('.',$level);
	
	$testcase_count = isset($node['testcase_count']) ? $node['testcase_count'] : 0;
	
	if ($nodeDesc == 'testproject')
	{
		$label = $name . " ({$testcase_count})";
		$dots = ".";
	}
	else
	{			
		if($nodeDesc == "testcase") 
		{
			$icon = "gnome-starthere-mini.png";
			$buildLinkTo = $tc_action_enabled;
			$label = "<b>{$node['id']}</b>: {$name}";
		}		   
		else if ($nodeDesc == "testsuite")
			$label = $name . " ({$testcase_count})";
	}	

	if ($buildLinkTo)
		$myLinkTo = "{$linkto}?edit={$nodeDesc}&id={$node['id']}{$getArguments}";
	else	
		$myLinkTo = ' ';
		
	$menustring = "{$dots}|{$label}|{$myLinkTo}|{$nodeDesc}". 
		           "|{$icon}|workframe|\n";
		
	return $menustring;				
}


//
// Create the string representation suitable to create a graphic visualization
// of a node, for dtree
//
//
//
function dtree_renderTestSpecTreeNodeOnOpen($current,$nodeDesc,$linkto,$getArguments,$tc_action_enabled)
{
	$dtreeCounter = $current['id'];

	$parentID = isset($current['parent_id']) ? $current['parent_id'] : -1;
	$name = filterString($current['name']);
	$buildLinkTo = 1;
	
	$edit = 'testcase';
	$label = $name;
	$testcase_count = isset($current['testcase_count']) ? $current['testcase_count'] : 0;
	if ($nodeDesc == 'testproject')
	{
		$label = $name ." (" . $testcase_count . ")";
	}
	else if ($nodeDesc == 'testcase')
	{
		$label = "<b>{$current['id']}</b>:".$name;
		$buildLinkTo = $tc_action_enabled;
	}
	else
	{
		$label = $name ." (" . $testcase_count . ")";
	}
	if ($buildLinkTo)
		$myLinkTo = $linkto . "?edit={$nodeDesc}&id=" . $current['id'] . $getArguments;
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
function jtree_renderTestSpecTreeNodeOnOpen($current,$nodeDesc,$tc_action_enabled)
{
	$menustring = "['";
	$name = filterString($current['name']);
	$buildLinkTo = 1;
	$pfn = "ET";
	$testcase_count = isset($current['testcase_count']) ? $current['testcase_count'] : 0;	
	
	if($nodeDesc == 'testproject')
	{
		$pfn = 'EP';
		$label =  $name . " (" . $testcase_count . ")";
	}
	else if ($nodeDesc == 'testsuite')
	{
		$pfn = 'ETS';
		$label =  $name . " (" . $testcase_count . ")";	
	}
	else if ($nodeDesc == 'testcase')
	{
		$buildLinkTo = $tc_action_enabled;
		if (!$buildLinkTo)
			$pfn = "void";
			
		$label = "<b>" . $current['id'] . "</b>: ".$name;
	}
	$menustring = "['{$label}','{$pfn}({$current['id']})',\n";
			
	return $menustring;
}

function jtree_renderTestSpecTreeNodeOnClose($current,$nodeDesc)
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
* 20060429 - franciscom - 
* removing coupling with _POST
* interface changes
*
* operation: string that can take the following values:
*            testcase_execution
*            remove_testcase_from_testplan
*             
*            and changes how the URL's are build.
* 
*/
function generateExecTree(&$db,&$menuUrl,$tproject_id,$tproject_name,$tplan_id,$tplan_name,$build_id,
                          $getArguments, $keyword_id = 0,$tc_id = 0,$bForPrinting = false,
			                    $assignedTo = 0, $status = null)
{
	$menustring = null;
	$any_exec_status=null;
  
	define('ANY_OWNER',null);
	define('RECURSIVE_MODE',true);
  
  
	$tplan_mgr = new testplan($db);
	$tproject_mgr = new testproject($db);
	
	$tree_manager = $tplan_mgr->tree_manager;
	$tcase_node_type = $tree_manager->node_descr_id['testcase'];
	$hash_descr_id = $tree_manager->get_available_node_types();
	$hash_id_descr = array_flip($hash_descr_id);

	$test_spec = $tree_manager->get_subtree($tproject_id,array('testplan'=>'exclude me'),
	                                                     array('testcase'=>'exclude my children'),
	                                                     null,null,RECURSIVE_MODE);
	                                                     
	$tp_tcs = $tplan_mgr->get_linked_tcversions($tplan_id,$tc_id,$keyword_id,null,ANY_OWNER,$status);

	if (is_null($tp_tcs))
		$tp_tcs = array();
	
	$test_spec['name'] = $tproject_name . " / " . $tplan_name;  // To be discussed
	$test_spec['id'] = $tproject_id;
	$test_spec['node_type_id'] = $hash_descr_id['testproject'];
	$map_node_tccount = array();

	
	if($test_spec)
	{
		$tck_map = null;
		if($keyword_id)
			$tck_map = $tproject_mgr->get_keywords_tcases($tproject_id,$keyword_id);
		
		
		// 20061112 - interface changes:
		// 1. added $db as first argument
		// 2. mandatory and optional arguments are grouped:
		//    i.e. mandatory arguments start with first argument, and all arguments
		//        till first optional, are mandatory.
		// This was not this ways before this change.
		//
		$testcase_count = prepareNode($db,$test_spec,$hash_id_descr,$map_node_tccount,
		                              $tck_map,$tp_tcs,$bForPrinting,$assignedTo,$status);


		$test_spec['testcase_count'] = $testcase_count;
	
		$menustring = renderExecTreeNode(1,$test_spec,$getArguments,$hash_id_descr,1,$menuUrl,$bForPrinting);
	}
	return $menustring;
}

function renderExecTreeNode($level,&$node,$getArguments,$hash_id_descr,$tc_action_enabled,$linkto,$bForPrinting)
{
	$nodeDesc = $hash_id_descr[$node['node_type_id']];

	if (TL_TREE_KIND == 'JTREE')
		$menustring = jtree_renderExecTreeNodeOnOpen($node,$nodeDesc,$tc_action_enabled,$bForPrinting);
	else if (TL_TREE_KIND == 'DTREE')
		$menustring = dtree_renderExecTreeNodeOnOpen($node,$nodeDesc,$linkto,$getArguments,$tc_action_enabled,$bForPrinting);
	else 
		$menustring = layersmenu_renderExecTreeNodeOnOpen($node,$nodeDesc,$linkto,$getArguments,$level,$tc_action_enabled,$bForPrinting);
	if (isset($node['childNodes']) && $node['childNodes'])
	{
		$childNodes = $node['childNodes'];
		for($i = 0;$i < sizeof($childNodes);$i++)
		{
			$current = $childNodes[$i];
			if(is_null($current))
				continue;
			
			$menustring .= renderExecTreeNode($level+1,$current,$getArguments,$hash_id_descr,$tc_action_enabled,$linkto,$bForPrinting);
		}
	}
	if (TL_TREE_KIND == 'JTREE')
		$menustring .= jtree_renderTestSpecTreeNodeOnClose($node,$nodeDesc);
	
	return $menustring;
}

function layersmenu_renderExecTreeNodeOnOpen($node,$nodeDesc,$linkto,$getArguments,$level,$tc_action_enabled,$bForPrinting)
{
	$name = filterString($node['name']);
	$label = $name;
	$icon = "";
	$buildLinkTo = 1;
	$dots  = str_repeat('.',$level);
	
	$testcase_count = isset($node['testcase_count']) ? $node['testcase_count'] : 0;
	
	$versionID = null;
	if ($nodeDesc == 'testproject')
	{
		$label = $name . " ({$testcase_count})";
		$dots = ".";
	}
	else
	{			
		if($nodeDesc == "testcase") 
		{
			$icon = "gnome-starthere-mini.png";
			$label = "<b>{$node['id']}</b>: {$name}";
			$versionID = "&version_id=" . $node['tcversion_id'];
		}		   
		else if ($nodeDesc == "testsuite")
			$label = $name . " ({$testcase_count})";
	}	

	
	$myLinkTo = $linkto."?level={$nodeDesc}&id={$node['id']}".$versionID.$getArguments;
	$menustring = "{$dots}|{$label}|{$myLinkTo}|{$nodeDesc}". 
		           "|{$icon}|workframe|\n";
	
	
	return $menustring;				
}

function dtree_renderExecTreeNodeOnOpen($current,$nodeDesc,$linkto,$getArguments,$tc_action_enabled,$bForPrinting)
{
	$dtreeCounter = $current['id'];

	$parentID = isset($current['parent_id']) ? $current['parent_id'] : -1;
	$name = filterString($current['name']);
	$buildLinkTo = 1;
	
	$edit = 'testcase';
	$label = $name;
	$testcase_count = isset($current['testcase_count']) ? $current['testcase_count'] : 0;
	$versionID = 0;
	if ($nodeDesc == 'testproject')
	{
		$label = $name ." (" . $testcase_count . ")";
	}
	else if ($nodeDesc == 'testcase')
	{
		$label = "<b>{$current['id']}</b>:".$name;
		$buildLinkTo = $tc_action_enabled;
		$versionID = $current['tcversion_id'];
	}
	else
	{
		$label = $name ." (" . $testcase_count . ")";
	}
	if ($buildLinkTo)
		$myLinkTo = $linkto . "?version_id={$versionID}&level={$nodeDesc}&id=" . $current['id'] . $getArguments;
	else
		$myLinkTo = "";
		
		
	$menustring = "tlTree.add(" . $dtreeCounter . ",{$parentID},'" ;
	$menustring .= $label. "','{$myLinkTo}');\n";
				   
	return $menustring;				   
}

function jtree_renderExecTreeNodeOnOpen($current,$nodeDesc,$tc_action_enabled,$bForPrinting)
{
	$menustring = "['";
	$name = filterString($current['name']);
	$buildLinkTo = 1;
	$pfn = "ST";
	$testcase_count = isset($current['testcase_count']) ? $current['testcase_count'] : 0;	
	$versionID = 0;
	
	if($nodeDesc == 'testproject')
	{
		$pfn = $bForPrinting ? 'PTP' : 'SP';
		$label =  $name . " (" . $testcase_count . ")";
	}
	else if ($nodeDesc == 'testsuite')
	{
		$pfn = $bForPrinting ? 'PTS' : 'STS';
		$label =  $name . " (" . $testcase_count . ")";	
	}
	else if ($nodeDesc == 'testcase')
	{
		$buildLinkTo = $tc_action_enabled;
		if (!$buildLinkTo)
			$pfn = "void";
			
		$label = "<b>" . $current['id'] . "</b>: ".$name;
		$versionID = $current['tcversion_id'];
	}
	$menustring = "['{$label}','{$pfn}({$current['id']},{$versionID})',\n";
			
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
function get_testproject_nodes_testcount(&$db,$tproject_id, $tproject_name,$keyword_id=0)
{
	$recurse_on=true;
	$show_tc_on=0;
	
	$tproject_mgr = new testproject($db);
	$tree_manager = &$tproject_mgr->tree_manager;

	$tcase_node_type = $tree_manager->node_descr_id['testcase'];
	$hash_descr_id = $tree_manager->get_available_node_types();
	$hash_id_descr = array_flip($hash_descr_id);
	
	
	$test_spec = $tree_manager->get_subtree($tproject_id,array('testplan'=>'exclude me'),
												                               array('testcase'=>'exclude my children'),
												                               null,null,$recurse_on);
	$test_spec['name'] = $tproject_name;
	$test_spec['id'] = $tproject_id;
	$test_spec['node_type_id'] = 1;
	
	$map_node_tccount=array(); 
	$tp_tcs=null;
	
	if($test_spec)
	{
		$tck_map = null;
		if($keyword_id)
		{
			$tck_map = $tproject_mgr->get_keywords_tcases($tproject_id,$keyword_id);
		}	
		$testcase_count = prepareNode($db,$test_spec,$hash_id_descr,$map_node_tccount,
		                              $tck_map,$tp_tcs,$show_tc_on);
	
		$test_spec['testcase_count'] = $testcase_count;
	}

	return($map_node_tccount);
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
                                      $tplan_id,$tplan_name,$keyword_id=0)
{
	$recurse_on=true;
	$show_tc_on=0;

	$tplan_mgr = new testplan($db);
	$tproject_mgr = new testproject($db);
	
	$tree_manager = $tplan_mgr->tree_manager;
	$tcase_node_type = $tree_manager->node_descr_id['testcase'];
	$hash_descr_id = $tree_manager->get_available_node_types();
	$hash_id_descr = array_flip($hash_descr_id);

	$test_spec = $tree_manager->get_subtree($tproject_id,array('testplan'=>'exclude me'),
	                                                     array('testcase'=>'exclude my children'),
	                                                     null,null,$recurse_on);
	$tp_tcs = $tplan_mgr->get_linked_tcversions($tplan_id,0,$keyword_id);
	if (is_null($tp_tcs))
		$tp_tcs = array();
	
	$test_spec['name'] = $tproject_name;
	$test_spec['id'] = $tproject_id;
	$test_spec['node_type_id'] = $hash_descr_id['testproject'];
	$map_node_tccount=array(); 
	
	if($test_spec)
	{
		$tck_map = null;
		if($keyword_id)
		{
			$tck_map = $tproject_mgr->get_keywords_tcases($tproject_id,$keyword_id);
		}	
  	$testcase_count = prepareNode($db,$test_spec,$hash_id_descr,$map_node_tccount,
		                              $tck_map,$tp_tcs,$show_tc_on);
		
		$test_spec['testcase_count'] = $testcase_count;
	}
	return($map_node_tccount);
}




?>