<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: treeMenu.inc.php,v $
 *
 * @version $Revision: 1.55 $
 * @modified $Date: 2008/02/04 22:32:52 $ by $Author: franciscom $
 * @author Martin Havlat
 *
 * 	This file generates tree menu for test specification and test execution.
 * 	Three kinds of menu component are supported: 
 *                                              LAYERSMENU, DTREE,	and JTREE. 
 *  Used type is defined in config.inc.php.
 * 
 * Rev:
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
function invokeMenu($menustring, $highLight = "",$target = "workframe")
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
                              $getArguments = '',$keyword_id = 0,
                              $ignore_inactive_testcases=0,$exclude_branches=null)
{
	
	$showTestCaseID=config_get('tree_show_testcase_id');
	$menustring = null;

	$tproject_mgr = new testproject($db);
	$tree_manager = &$tproject_mgr->tree_manager;


	$tcase_node_type = $tree_manager->node_descr_id['testcase'];
	$hash_descr_id = $tree_manager->get_available_node_types();
	$hash_id_descr = array_flip($hash_descr_id);
  $status_descr_code=config_get('tc_status');
  $status_code_descr=array_flip($status_descr_code);

  $decoding_hash=array('node_id_descr' => $hash_id_descr,
                       'status_descr_code' =>  $status_descr_code,
                       'status_code_descr' =>  $status_code_descr);
	
	$tcase_prefix=$tproject_mgr->getTestCasePrefix($tproject_id);
	$test_spec = $tproject_mgr->get_subtree($tproject_id,RECURSIVE_MODE,
												                  $exclude_branches,NO_NODE_TYPE_TO_FILTER);
												                  
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
		if($keyword_id)
		{
			$tck_map = $tproject_mgr->get_keywords_tcases($tproject_id,$keyword_id);
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
		
		// 20080113 - franciscom - added $tcase_prefix
    // 20080110 - franciscom - added $showTestCaseID
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
                     $tck_map = null,$tp_tcs = null,$bHideTCs = 0,
                     $assignedTo = 0,$status = null, 
                     $ignore_inactive_testcases=0,$show_tc_id=1)
{
  
  // ------------------------------------------------------------------------------  
  $hash_id_descr=$decoding_info['node_id_descr'];
  $status_descr_code=$decoding_info['status_descr_code'];
  $status_code_descr=$decoding_info['status_code_descr'];
  
  $tcase_counters=array('testcase_count' => 0);
  foreach($status_descr_code as $status_descr => $status_code)
  {
    $tcase_counters[$status_descr]=0;
  }
  // ------------------------------------------------------------------------------

	$node_type = $hash_id_descr[$node['node_type_id']];
  $tcase_counters['testcase_count']=0;
  
	if ($node_type == 'testcase')
	{
    $viewType=is_null($tp_tcs) ? 'testSpecTree' : 'executionTree';
    
		if (!is_null($tck_map))
		{
			if (!isset($tck_map[$node['id']]))
				$node = null;
		}
	
	  if ($node && $viewType=='executionTree')
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
				$node['version'] = $tp_tcs[$node['id']]['version'];		

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
		
		// 20080113 - franciscom
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
		if( isset($tp_tcs[$node['id']]['exec_status']) )
		{
		   $tc_status_code = $tp_tcs[$node['id']]['exec_status'];
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
			                            $tck_map,$tp_tcs,$bHideTCs,
			                            $assignedTo,$status,
 			                            $ignore_inactive_testcases,$show_tc_id);
      
      
      // -------------------------------------------------
      // 20071111 - franciscom
      foreach($counters_map as $key => $value)
      {
        $tcase_counters[$key] += $counters_map[$key];   
      }  
      // -------------------------------------------------


		}
		// $node['testcase_count'] = $nTestCases;
    foreach($tcase_counters as $key => $value)
    {
        $node[$key] = $tcase_counters[$key];
    }  
		
		
		if (isset($node['id']))
		{
			$map_node_tccount[$node['id']] = array(	'testcount' => $node['testcase_count'],
		                                     		  'name'      => $node['name']);
		}
		if ((!is_null($tck_map) || !is_null($tp_tcs)) && 
		     !$tcase_counters['testcase_count'] && ($node_type != 'testproject'))
		{
			$node = null;
		}
	}
 	else if ($node_type == 'testsuite')
	{
		$map_node_tccount[$node['id']] = array(	'testcount' => 0,
								                            'name' => $node['name']	  );
		
		if (!is_null($tp_tcs))
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
                        $tc_action_enabled,$linkto,
                        $testCasePrefix,
                        $bForPrinting=0,$showTestCaseID)
{
	$node_type = $hash_id_descr[$node['node_type_id']];

	if (TL_TREE_KIND == 'JTREE')
		$menustring = jtree_renderTestSpecTreeNodeOnOpen($node,$node_type,$tc_action_enabled,
		                                                 $bForPrinting,$showTestCaseID,$testCasePrefix);
	else if (TL_TREE_KIND == 'DTREE')
		$menustring = dtree_renderTestSpecTreeNodeOnOpen($node,$node_type,$linkto,
		                                                 $getArguments,$tc_action_enabled,
		                                                 $bForPrinting,$showTestCaseID,$testCasePrefix);
	else 
		$menustring = layersmenu_renderTestSpecTreeNodeOnOpen($node,$node_type,$linkto,$getArguments,
		                                                      $level,$tc_action_enabled,
		                                                      $bForPrinting,$showTestCaseID,$testCasePrefix);
		
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
*      added $useCounters and $useColors
* 
*/
function generateExecTree(&$db,&$menuUrl,$tproject_id,$tproject_name,$tplan_id,
                          $tplan_name,$build_id,
                          $getArguments, $keyword_id = 0,$tc_id = 0, 
                          $bHideTCs = false,
			                    $assignedTo = 0, $status = null, $cf_hash = null,
			                    $useCounters=1,$useColors=1)
{
  $showTestCaseID=config_get('tree_show_testcase_id');
	$menustring = null;
	$any_exec_status=null;

	$tplan_mgr = new testplan($db);
	$tproject_mgr = new testproject($db);
	
	$tree_manager = $tplan_mgr->tree_manager;
	$tcase_node_type = $tree_manager->node_descr_id['testcase'];
	$hash_descr_id = $tree_manager->get_available_node_types();
	$hash_id_descr = array_flip($hash_descr_id);
  $status_descr_code=config_get('tc_status');
  $status_code_descr=array_flip($status_descr_code);

  $decoding_hash=array('node_id_descr' => $hash_id_descr,
                       'status_descr_code' =>  $status_descr_code,
                       'status_code_descr' =>  $status_code_descr);



  // 20080114 - franciscom
 	$tcase_prefix = $tproject_mgr->getTestCasePrefix($tproject_id);
	$test_spec = $tproject_mgr->get_subtree($tproject_id,RECURSIVE_MODE);


  // 20071002 - jbarchibald - BUGID 1051
  // 20070306 - franciscom - BUGID 705   
	$tp_tcs = $tplan_mgr->get_linked_tcversions($tplan_id,$tc_id,$keyword_id,
	                                            null,$assignedTo,$status,$build_id,
                                                $cf_hash);

     
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
		// 20071014 - franciscom
		$bForPrinting=$bHideTCs;
		$testcase_counters = prepareNode($db,$test_spec,$decoding_hash,$map_node_tccount,
		                                 $tck_map,$tp_tcs,$bHideTCs,$assignedTo,$status);

		foreach($testcase_counters as $key => $value)
		{
		  $test_spec[$key]=$testcase_counters[$key];
		}
		
	  // 20071111 - franciscom
	  // added map $tp_tcs.
	  // key -> testcase id.
	  // value -> map with info about execution status
	  //
		$menustring = renderExecTreeNode(1,$test_spec,$tp_tcs,$getArguments,$hash_id_descr,1,
		                                 $menuUrl,$bHideTCs,$useCounters,$useColors,
		                                 $showTestCaseID,$tcase_prefix);
	}
	return $menustring;
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
                            $tc_action_enabled,$linkto,$bHideTCs,
                            $useCounters,$useColors,$showTestCaseID,$testCasePrefix)
{
	$node_type = $hash_id_descr[$node['node_type_id']];

	if (TL_TREE_KIND == 'JTREE')
		$menustring = jtree_renderExecTreeNodeOnOpen($node,$node_type,$tcase_node,
		                                             $tc_action_enabled,$bHideTCs,
		                                             $useCounters,$useColors,
		                                             $showTestCaseID,$testCasePrefix);
	else if (TL_TREE_KIND == 'DTREE')
		$menustring = dtree_renderExecTreeNodeOnOpen($node,$node_type,$tcase_node,
		                                             $linkto,$getArguments,
		                                             $tc_action_enabled,$bHideTCs,
		                                             $useCounters,$useColors,
		                                             $showTestCaseID,$testCasePrefix);
	else 
		$menustring = layersmenu_renderExecTreeNodeOnOpen($node,$node_type,$tcase_node,
		                                                  $linkto,$getArguments,$level,
		                                                  $tc_action_enabled,$bHideTCs,
		                                                  $useCounters,$useColors,
		                                                  $showTestCaseID,$testCasePrefix);
		                                                  
	if (isset($node['childNodes']) && $node['childNodes'])
	{
		$childNodes = $node['childNodes'];
		$nodes_qty = sizeof($childNodes);
		for($idx = 0;$idx <$nodes_qty ;$idx++)
		{
			$current = $childNodes[$idx];
			if(is_null($current))
				continue;
			
			$menustring .= renderExecTreeNode($level+1,$current,$tcase_node,
			                                  $getArguments,$hash_id_descr,
			                                  $tc_action_enabled,$linkto,$bHideTCs,
			                                  $useCounters,$useColors,$showTestCaseID,
			                                  $testCasePrefix);
		}
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
                                             $useCounters=1,$useColors=1,
                                             $showTestCaseID=1,$testCasePrefix)
{
	$cfg=config_get('testcase_cfg');
	$status_descr_code=config_get('tc_status');
	$status_code_descr=array_flip($status_descr_code);
	$status_verbose=config_get('tc_status_verbose_labels');


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
		   // $label .= "<b>{$node['id']}</b>:";
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
		$pfn = $bForPrinting ? 'TPLAN_PTS' : 'STS';
		$create_counters=1;
	  break;

	}	
	
  if($create_counters)
  {
		$label = $name ." (" . $testcase_count . ")";

    if($useCounters)
    {
		    // Created counters info
		    $keys2display=array('not_run' => 'not_run' ,'passed' => 'passed',
		                        'failed' =>'failed' ,'blocked' =>'blocked');
		    $add_html='';
		    foreach($keys2display as $key => $value)
		    {
          if( isset($node[$key]) )
          {
		        $add_html .='<span class="' . $key . '">' . $node[$key] . "</span>,";
		      }
		    }
	      $add_html = "(" . rtrim($add_html,",") . ")"; 
		    $label .= $add_html; 
   }
  }
	

	$myLinkTo = $linkto."?level={$node_type}&id={$node['id']}".$versionID.$getArguments;
	if ($buildLinkTo)
		$myLinkTo = "javascript:{$pfn}({$node['id']},{$versionID})";
	else	
		$myLinkTo = ' ';

	
	$menustring = "{$dots}|{$label}|{$myLinkTo}|{$node_type}". 
		           "|{$icon}||\n";
	
	
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
                                        $useCounters=1,$useColors=1,
                                        $showTestCaseID=1,$testCasePrefix)
{
 	$cfg=config_get('testcase_cfg');
	$status_descr_code=config_get('tc_status');
	$status_code_descr=array_flip($status_descr_code);
	$status_verbose=config_get('tc_status_verbose_labels');
	
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
  		
  	  // 20071229 - franciscom - added title and $useColors	
      $css_class= $useColors ? (" class=\"{$status_descr}\" ") : '';   
		  $label = "<span {$css_class} " . '  title="' . lang_get($status_verbose[$status_descr]) . '">';
		  
		  if($showTestCaseID)
		  {
		     // $label .= "<b>{$node['id']}</b>:";
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
		  $create_counters=1;
		  $pfn = $bForPrinting ? 'TPLAN_PTS' : 'STS';
		  break;
	
	} // switch

  if($create_counters)
  {
		$label = $name ." (" . $testcase_count . ")";

    if($useCounters)
    {
  	    // ----------------------------------------------------------------------------
		    // Created counters info
		    $keys2display=array('not_run' => 'not_run' ,'passed' => 'passed',
		                        'failed' =>'failed' ,'blocked' =>'blocked');
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
		    // ----------------------------------------------------------------------------
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
                                        $showTestCaseID=1,$testCasePrefix)
{
 	$cfg=config_get('testcase_cfg');
	$status_descr_code=config_get('tc_status');
	$status_code_descr=array_flip($status_descr_code);
	$status_verbose=config_get('tc_status_verbose_labels');
	
	$menustring = "['";
	$name = filterString($node['name']);
	$buildLinkTo = 1;
	$pfn = "ST";
	$testcase_count = isset($node['testcase_count']) ? $node['testcase_count'] : 0;	
	$versionID = 0;
	
  switch($node_type)
  {
	  case 'testproject':
		$pfn = $bForPrinting ? 'TPLAN_PTP' : 'SP';
		$label =  $name . " (" . $testcase_count . ")";
	  break;

	  case 'testsuite':
		$pfn = $bForPrinting ? 'TPLAN_PTS' : 'STS';
		$label =  $name . " (" . $testcase_count . ")";	
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
		   //$label .= "<b>{$node['id']}</b>:";
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
function get_testproject_nodes_testcount(&$db,$tproject_id, $tproject_name,$keyword_id=0)
{
	$tproject_mgr = new testproject($db);
	$tree_manager = &$tproject_mgr->tree_manager;

	$tcase_node_type = $tree_manager->node_descr_id['testcase'];
	$hash_descr_id = $tree_manager->get_available_node_types();
	$hash_id_descr = array_flip($hash_descr_id);

  // 20071111 - franciscom
  $status_descr_code=config_get('tc_status');
  $status_code_descr=array_flip($status_descr_code);
  $decoding_hash=array('node_id_descr' => $hash_id_descr,
                       'status_descr_code' =>  $status_descr_code,
                       'status_code_descr' =>  $status_code_descr);
	

  // 20071111 - franciscom	
	$test_spec = $tproject_mgr->get_subtree($tproject_id,RECURSIVE_MODE);


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
		$testcase_counters = prepareNode($db,$test_spec,$decoding_hash,$map_node_tccount,
		                                 $tck_map,$tp_tcs,SHOW_TESTCASES);
	
		$test_spec['testcase_count'] = $testcase_counters['testcase_count'];
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
	$tplan_mgr = new testplan($db);
	$tproject_mgr = new testproject($db);
	
	$tree_manager = $tplan_mgr->tree_manager;
	$tcase_node_type = $tree_manager->node_descr_id['testcase'];
	$hash_descr_id = $tree_manager->get_available_node_types();
	$hash_id_descr = array_flip($hash_descr_id);

  $status_descr_code=config_get('tc_status');
  $status_code_descr=array_flip($status_descr_code);
  $decoding_hash=array('node_id_descr' => $hash_id_descr,
                       'status_descr_code' =>  $status_descr_code,
                       'status_code_descr' =>  $status_code_descr);

  // 20071111 - franciscom
	$test_spec = $tproject_mgr->get_subtree($tproject_id,RECURSIVE_MODE);
	
	

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
  	$testcase_counters = prepareNode($db,$test_spec,$decoding_hash,$map_node_tccount,
		                                 $tck_map,$tp_tcs,SHOW_TESTCASES);
		
		// $test_spec['testcase_count'] = $testcase_count;
		$test_spec['testcase_count'] = $testcase_counters['testcase_count'];
	}
	return($map_node_tccount);
}




?>
