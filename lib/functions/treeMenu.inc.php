<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: treeMenu.inc.php,v $
 *
 * @version $Revision: 1.19 $
 * @modified $Date: 2006/05/05 20:07:23 $ by $Author: schlundus $
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
 *
 **/
require_once '../../config.inc.php';

define('TL_TIME_LIMIT_EXTEND', 30); // limit of possible connection delay in seconds

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
		
		// 20060304 - franciscom
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
 * 20060501 - franciscom - interface changes
 */
function fman_generateTestSpecTree(&$db,$tproject_id, $tproject_name, 
                              $linkto, $hidetc, $tc_action_enabled=1,
                              $getArguments = '',$keyword_id=0)
{
	$menustring = null; // storage variable for output

	$tree_manager = New tree($db);
	$tproject_mgr = New testproject($db);

	$tcase_node_type = $tree_manager->node_descr_id['testcase'];
	 $hash_descr_id = $tree_manager->get_available_node_types();
	 $hash_id_descr = array_flip($hash_descr_id);
	
	$test_spec = $tree_manager->get_subtree($tproject_id,array('testplan'=>'exclude me'),
	                                                     array('testcase'=>'exclude my children'));

  // 20060501 - franciscom
  // ------------------------------------------------------------------------------------------- 
  if( $keyword_id > 0 )
  {
     // Get the Test Cases that has the Keyword_id
     $tck_map=$tproject_mgr->get_keywords_tcases($tproject_id,$keyword_id);
    
     if( !is_null($tck_map) )
     {
        // filter the test_spec
        foreach($test_spec as $key => $node)
        {
           if( $node['node_type_id'] == $tcase_node_type &&  !isset($tck_map[$node['id']]) )
           {
              $test_spec[$key]=null;            
           }      
        }
        $test_spec = array_merge($test_spec);
        
     } 
  }
  // -------------------------------------------------------------------------------------------
  
  
	$testcase_count=$tproject_mgr->count_testcases($tproject_id);
	
	if (TL_TREE_KIND == 'LAYERSMENU')
	{ 
	
	  // [dots] | [text] | [link] | [title] | [icon] | [target] | [expand]
    // 
	  // dots= ".|"
	  // text= $tproject_name . " (" . $testcase_count . ")
	  // link= $linkto . "?edit=product&data=" . $tproject_id . $getArguments
	  // title="Product"
	  // icon= -
	  // target="workframe"
	  // expand=-
	  //
		$menustring .= "." . "|" . 
		               $tproject_name . " (" . $testcase_count . ")" . "|" . 
		               $linkto . "?edit=testproject&data=" . $tproject_id . $getArguments . "|" .
		               "testproject". "|" . "|" . "workframe" ."|\n";
	}
 	
  // 20060223 - franciscom
  if( count($test_spec) > 0 )
  {
   	$pivot=$test_spec[0];
   	$the_level=1;
    $level=array();
  
   	foreach ($test_spec as $elem)
   	{
     // 20060501 - franciscom
     if( is_null($elem) )
     {
        // I use set an element to null to filter out leaf menu items
        continue;
     } 
   	 
   	 $current = $elem;
  
     // 20060503 - franciscom - seems stupid but without this (I need to find a better
     //                         solution) the tree is drawn in a wrong way
     if( $pivot['parent_id'] == $current['parent_id'])
     {
       $the_level=$the_level;
     }
     else if ($pivot['id'] == $current['parent_id'])
     {
     	  $the_level++;
     	  $level[$current['parent_id']]=$the_level;
     }
     else 
     {
     	  $the_level=$level[$current['parent_id']];
     }
     
     // 20060303 - franciscom - added icon
     $icon="";
     $build_linkto=1;
     if( $hash_id_descr[$current['node_type_id']] == "testcase") 
     {
       $icon="gnome-starthere-mini.png";
       $build_linkto=$tc_action_enabled;
     }
     
     if($build_linkto)
     {
       $my_linkto=    $linkto . "?edit=" . $hash_id_descr[$current['node_type_id']] . 
                              "&data=" . $current['id'] . $getArguments ;
     }
     else
     {
       $my_linkto=' |'; 
     }
     /*
     $menustring .= str_repeat('.',$the_level) . ".|" . 
                    " " . $current['name'] . "|" . 
                    $linkto . "?edit=" . $hash_id_descr[$current['node_type_id']] . 
                              "&data=" . $current['id'] . $getArguments . "|" . 
                    $hash_id_descr[$current['node_type_id']] . "|" . $icon . "|" . "workframe" ."|\n"; 
     
     */
     $menustring .= str_repeat('.',$the_level) . ".|" . 
                    " " . filterString($current['name']) .      "|" . 
                    $my_linkto .                  "|" .
                    $hash_id_descr[$current['node_type_id']] . "|" . $icon . "|" . "workframe" ."|\n"; 
     
     
     
     // update pivot
     $level[$current['parent_id']]= $the_level;
     $pivot=$elem;
   	}
	}
	
	//echo $menustring;
	return $menustring;
}


/** 
 * generate data for tree menu of Test Specification
*/
function generateTestSpecTree(&$db,$tproject_id, $tproject_name, 
                              $linkto, $hidetc, $tc_action_enabled=1,
                              $getArguments = '',$keyword_id=0)
{
	$menustring = null; // storage variable for output

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
	if($test_spec)
	{
		$tck_map = null;
		if($keyword_id)
			$tck_map = $tproject_mgr->get_keywords_tcases($tproject_id,$keyword_id);
		$testcase_count = prepareNode(&$test_spec,$hash_id_descr,$tck_map);
		$test_spec['testcase_count'] = $testcase_count;
	
		$menustring = renderTreeNode(1,$test_spec,$getArguments,$hash_id_descr,$tc_action_enabled,$linkto);
	}

	return $menustring;
}

function prepareNode(&$node,$hash_id_descr,$tck_map = null)
{
	$nodeDesc = $hash_id_descr[$node['node_type_id']];
	
	$nTestCases = 0;
	if ($nodeDesc == 'testcase')
	{
		$nTestCases = 1;
		if ($tck_map)
		{
			if (!isset($tck_map[$node['id']]))
			{
				$node = null;
				$nTestCases = 0;
			}
		}
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
			$nTestCases += prepareNode($current,$hash_id_descr,$tck_map);
		}
		$node['testcase_count'] = $nTestCases;
		if ($tck_map && !$nTestCases)
			$node = null;
		
	}
	
	return $nTestCases;
}

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
		for($i = 0;$i < sizeof($childNodes);$i++)
		{
			$current = $childNodes[$i];
			// I use set an element to null to filter out leaf menu items
			if(is_null($current))
				continue;
			
			$menustring .= renderTreeNode($level+1,$current,$getArguments,$hash_id_descr,$tc_action_enabled,$linkto);
		}
	}
	if (TL_TREE_KIND == 'JTREE')
		$menustring .= jtree_renderTestSpecTreeNodeOnClose($node,$nodeDesc);
	
	return $menustring;
}


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
		$myLinkTo = "{$linkto}?edit={$nodeDesc}&data={$node['id']}{$getArguments}";
	else	
		$myLinkTo = ' ';
		
	$menustring = "{$dots}|{$label}|{$myLinkTo}|{$nodeDesc}". 
		           "|{$icon}|workframe|\n";
		
	return $menustring;				
}

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
		$myLinkTo = $linkto . "?edit={$nodeDesc}&data=" . $current['id'] . $getArguments;
	else
		$myLinkTo = "";
		
		
	$menustring = "tlTree.add(" . $dtreeCounter . ",{$parentID},'" ;
	$menustring .= $label. "','{$myLinkTo}');\n";
				   
	return $menustring;				   
}

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
* 	generate data for tree menu of Test Case Suite (in Test Plan)
*
* 	@param string $linkto path for generated URL
*	  @param integer $hidetc [0: show TCs, 1: hide TCs ]
*	  @param string $getArguments additional $_GET arguments
* 	@return string input string for layersmenu
*/
function generateTestSuiteTree(&$db,$linkto, $hidetc, $getArguments = '')
{
	$menustring = null;
	$tpName = filterString($_SESSION['testPlanName']);
	// define root directory
	if (TL_TREE_KIND == 'LAYERSMENU') 
	{
		$menustring .= ".|" . $tpName . "|" . $linkto . "?level=root" . 
		               $getArguments . "|Test Case Suite||workframe|\n";
	}	
	elseif (TL_TREE_KIND == 'JTREE')
	{
		$menustring .= "['" . $tpName . "','PTP()',\n";
	}	
	elseif (TL_TREE_KIND == 'DTREE')
	{
		$dtreeCounter = 0;
		$menustring .= "tlTree.add(" . $dtreeCounter++ . ",-1,'" . $tpName . "','" . 
		               $linkto . "?level=root" . $getArguments . "');\n";
	}
	
	// 20050915 - fm - grab every component depending on the test plan
	$sql = " SELECT component.id, mgtcomponent.name " .
	       " FROM component,mgtcomponent, testplans " .
	       " WHERE mgtcomponent.id = component.mgtcompid " .
			   " AND component.projid = testplans.id " .
			   " AND testplans.id = " . $_SESSION['testPlanId'] . 
			   " ORDER BY mgtcomponent.name";
	   
	$comResult = $db->exec_query($sql);

	while ($myrowCOM = $db->fetch_array($comResult)) { 

		$componentName = filterString($myrowCOM['name']);
		if (TL_TREE_KIND == 'LAYERSMENU') 
			$menustring .= "..|" . $componentName . "|" . $linkto . 
			               "?level=component&data=" . $myrowCOM['id'] . $getArguments . "|Component||workframe|\n";
		elseif (TL_TREE_KIND == 'JTREE')
			$menustring .= "['" . $componentName . "','PCO({$myrowCOM['id']})',\n";
		elseif (TL_TREE_KIND == 'DTREE')
		{
			$dtreeComponentId = $dtreeCounter;
			$menustring .= "tlTree.add(" . $dtreeCounter++. ",0,'" . $componentName . "','" . 
			               $linkto . "?level=component&data=" . $myrowCOM['id'] . $getArguments . "');\n";
		}

		// grab every category depending on the component 
		$sql =" SELECT category.id, mgtcategory.name " .
		      " FROM component,category,mgtcategory " .
				  " WHERE component.id = category.compid " .
				  " AND   mgtcategory.id = category.mgtcatid " .
				  " AND component.id = " . $myrowCOM['id'] . 
				  " ORDER BY mgtcategory.CATorder,category.id";
		
		$catResult = $db->exec_query($sql);
			
		while ($myrowCAT = $db->fetch_array($catResult)) {  //display all the categories until we run out

			$categoryName = filterString($myrowCAT['name']);
			if (TL_TREE_KIND == 'LAYERSMENU') 
				$menustring .= "...|" . $categoryName . "|" . $linkto . 
				               "?level=category&data=" . $myrowCAT['id'] . $getArguments . "|Category||workframe|\n";
			elseif (TL_TREE_KIND == 'JTREE')				
				$menustring .= "['" . $categoryName . "','PC({$myrowCAT['id']})',\n";
			elseif (TL_TREE_KIND == 'DTREE')
			{
				$dtreeCategoryId = $dtreeCounter;
				$menustring .= "tlTree.add(" . $dtreeCounter++. "," . $dtreeComponentId . ",'" . 
				               $categoryName . "','" . $linkto . "?level=category&data=" . $myrowCAT['id'] . 
				               $getArguments . "');\n";
			}

			// create TCs if required
			if ($hidetc == 0) {
				$sql = " SELECT testcase.id, testcase.title,testcase.mgttcid " .
				       " FROM category,testcase " .
				       " WHERE category.id = testcase.catid " .
				       " AND category.id = " . 	$myrowCAT['id'] . 
				       " ORDER BY TCorder,testcase.mgttcid";

				$TCResult = $db->exec_query($sql);

				while ($myrowTC = $db->fetch_array($TCResult)) 
				{  
					$tcName = filterString($myrowTC['title']);
					if (TL_TREE_KIND == 'LAYERSMENU')
					{ 
						$menustring .= "....|<b>" . $myrowTC['mgttcid'] . "</b>: " . $tcName . "|" . $linkto . 
						               "?level=tc&data=" . $myrowTC['id'] . $getArguments . "|Test Case||workframe|\n";
					}
					elseif (TL_TREE_KIND == 'JTREE')
					{						
						$menustring .= "['<b>" . $myrowTC['mgttcid'] . "</b>:" . $tcName . "','PT({$myrowTC['id']})'],\n";
					}
					elseif (TL_TREE_KIND == 'DTREE')
					{
						$menustring .= "tlTree.add(" . $dtreeCounter++. "," . $dtreeCategoryId . ",'<b>" . 
						               $myrowTC['mgttcid'] . "</b>:" . $tcName . "','" . $linkto . "?level=tc&data=" . 
						               $myrowTC['id'] . $getArguments . "');\n";
					}
				}
			}

			if (TL_TREE_KIND == 'JTREE')
				$menustring .=  "]\n,"; //end the component block
		}

		if (TL_TREE_KIND == 'JTREE')
			$menustring .=  "]\n,"; //end the component block
		
		// MHT 20050630 - extend script deadline
		// [ 1119896 ] Cannot see test cases in Test Case Execution page
		set_time_limit(TL_TIME_LIMIT_EXTEND);
	}

	tLog("function generateTestSuiteTree output:\n" . $menustring);
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
function generateExecTree(&$db,&$menuUrl,$tplan_id,$tplan_name,$build_id,$url_to_help,
                          $operation,
                          $testcase_id = null,
                          $keyword_id = 0,
                          $owner = null,
                          $tc_status = null,      
                          $do_coloring_by='result')
{
	global $dtreeCounter;

	$dtreeCategoryId = null;
	$menustring = null;
	
	$tree_mgr = New tree($db);
	$tplan_mgr = New testplan($db);

  $hash_descr_id = $tree_mgr->get_available_node_types();
  $hash_id_descr = array_flip($hash_descr_id);

  $testcase_count=$tplan_mgr->count_testcases($tplan_id);


	$keyword = 'All';
	$testcase_status = 'all';

  switch ($operation)
  {
     case 'testcase_execution':
     $menuUrl .= '?keyword_id=' . $keyword_id . '&build_id=' . $build_id . '&owner=' . $owner;
	   break;
	   
	   case 'remove_testcase_from_testplan':
	   $menuUrl .= '?keyword_id=' . $keyword_id;
	   break;
	   
  } 

	
	if (TL_TREE_KIND == 'LAYERSMENU') 
	{
		$menustring = ".|" . $tplan_name . " (" . $testcase_count . ")" . "|" . 
		              $purl_to_help . "|Test Case Suite||workframe|\n";
	}
	elseif (TL_TREE_KIND == 'DTREE')
	{
		$menustring .= "tlTree.add(" . $dtreeCounter++ . ",-1,'" . $tplan_name . 
		               "','" . $purl_to_help . "');\n";
	}
	elseif (TL_TREE_KIND == 'JTREE')	
	{
		$help_html = $purl_to_help . "/testExecute.html";
		$menustring .= "['" . $tplan_name . "','SP()',\n";
	}
		
  $mtime_start = array_sum(explode(" ",microtime()));
  
  // 20060430 - franciscom
  $xx=$tplan_mgr->get_linked_tcversions($tplan_id,$testcase_id,$keyword_id);
  
  
  $test_spec=array();
  $zz=array();
  $added=array();
  $first_level=array();
  $debug_counter=array();
  $idx=0;
  $jdx=0;
 
// ------------------------------------------------------------------------ 
// 20060401 - franciscom
if( !is_null($xx) )
{ 
  // Get the path for every test case, grouping test cases that
  // have same parent.
  foreach($xx as $item)
  {
 	  $path=$tree_mgr->get_path($item['tc_id']);
    
 	  if( !isset($first_level[$path[0]['id']]) )
  	{
      $first_level[$path[0]['id']]=$jdx++; 
    }
 	  
 	  if( isset($added[$item['testsuite_id']]) )
  	{
  		$pos = $added[$item['testsuite_id']];
  	  $zz[$pos][]=end($path);
      $debug_counter[$item['testsuite_id']]++;
  	}
    else
    {
    	$added[$item['testsuite_id']]=$idx++;
  		$debug_counter[$item['testsuite_id']]=1;
  		$zz[]=$path;
  	}
    
  }
   
   
  // we can have branchs with common path, but still not joined
  // that's what we want to solve with the following process.  
  // Now group test suites under it's parent 
  $added=array();
  $gdx=0;
  foreach($zz as $item)
  {
  	if( isset($added[$item[0]['id']]) )
  	{
  		// look for the point where to join
  		$pos=$first_level[$item[0]['id']];
      foreach( $zz[$pos] as $the_k => $the_e)
      {
      	  if( $the_e['id'] != $item[$the_k]['id'] )
      	  {
 	          $qty=count($item)-1;
            for( $jdx=$the_k; $jdx <= $qty ; $jdx++)
            {
      	  			$zz[$pos][]=$item[$jdx];
        	  }
        	  break;
        	}  
      }
      $zz[$gdx]=null;
  	}
  	else
  	{
  		$added[$item[0]['id']]=$item[0]['id'];
  	}
  	$gdx++;
 	}  
    
 	// Now create the data structure that like the tree drawing algorithm
 	foreach($zz as $item)
  {
    $test_spec=array_merge($test_spec,$item);
  }

  // 20060223 - franciscom
  if( count($test_spec) > 0 )
  {
   	$pivot=$test_spec[0];
   	$the_level=1;
    $level=array();
  
   	foreach ($test_spec as $elem)
   	{
   	 $current = $elem;
  
     if( $pivot['parent_id'] == $current['parent_id'])
     {
       $the_level=$the_level;
     }
     else if ($pivot['id'] == $current['parent_id'])
     {
     	  $the_level++;
     	  $level[$current['parent_id']]=$the_level;
     }
     else 
     {
     	  $the_level=$level[$current['parent_id']];
     }
     
     // 20060303 - franciscom - added icon
     $icon="";
     $version_id = "";
     if( $hash_id_descr[$current['node_type_id']] == "testcase") 
     {
       $version_id = "&version_id=" . $xx[$current['id']]['tcversion_id'];
       $icon="gnome-starthere-mini.png";	
     }
     
     
     /*
     $menustring .= str_repeat('.',$the_level) . ".|" . 
                    " " . $current['name'] . "|" . 
                    $linkto . "?edit=" . $hash_id_descr[$current['node_type_id']] . 
                              "&data=" . $current['id'] . $getArguments . "|" . 
                    $hash_id_descr[$current['node_type_id']] . "|" . $icon . "|" . "workframe" ."|\n"; 
     */               
     
     $menustring .= str_repeat('.',$the_level) . ".|" . 
                         " " . $current['name'] . "|" . 
                    $menuUrl . "&level=" . $hash_id_descr[$current['node_type_id']] . 
                               "&id=" . $current['id'] . 
                               $version_id . "|" . 
                               $hash_id_descr[$current['node_type_id']] . "|" .
                               $icon . "|" . "workframe" ."|\n";
     // update pivot
     $level[$current['parent_id']]= $the_level;
     $pivot=$elem;
   	}
	}
}

	
	$mtime_stop = array_sum(explode(" ",microtime()));
	$ttime=$mtime_stop - $mtime_start;
	//echo "Total Time = $ttime (millisec) <br>";
	
	//echo $menustring;
	return $menustring;
}
?>