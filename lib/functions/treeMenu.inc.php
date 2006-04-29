<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: treeMenu.inc.php,v $
 *
 * @version $Revision: 1.16 $
 * @modified $Date: 2006/04/29 19:32:54 $ by $Author: schlundus $
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
		$data .=  "]\n];\n"; //end the product block and whole array
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
	if (TL_TREE_KIND != LAYERSMENU)
	{
		$str = addslashes($str);
	}
	$str = htmlspecialchars($str, ENT_QUOTES);	
	
	return $str;
}


/** 
 * generate data for tree menu of Test Specification
 *
 * @param numeric prodID
 * @param string  prodName
 * @param string $linkto path for generated URL
 * @param integer $hidetc [0: show TCs, 1: disable TCs ]
 * @param string $getArguments additional $_GET arguments
 * @return string input string for layersmenu
 *
 */
function generateTestSpecTree(&$db,$tproject_id, $tproject_name, 
                              $linkto, $hidetc, $getArguments = '')
{
	$menustring = null; // storage variable for output

	$tree_manager = new tree($db);
	$tproject_mgr = new testproject($db);
	
	$test_spec = $tree_manager->get_subtree($tproject_id,array('testplan'=>'exclude me'),
	                                                     array('testcase'=>'exclude my children'));

	$hash_descr_id = $tree_manager->get_available_node_types();
	$hash_id_descr = array_flip($hash_descr_id);
	$testcase_count = $tproject_mgr->count_testcases($tproject_id);
	
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
		               filterString($tproject_name) . " (" . $testcase_count . ")" . "|" . 
		               $linkto . "?edit=testproject&data=" . $tproject_id . $getArguments . "|" .
		               "testproject". "|" . "|" . "workframe" ."|\n";
	}
 	
	if(count($test_spec))
	{
	   	$pivot = $test_spec[0];
	   	$the_level = 1;
	    $level = array();
  
		foreach ($test_spec as $elem)
		{
			$current = $elem;
			
			/*	
			if($pivot['parent_id'] == $current['parent_id'])
			{
				$the_level = $the_level;
			}
			*/
			if ($pivot['id'] == $current['parent_id'])
			{
				$the_level++;
				$level[$current['parent_id']] = $the_level;
			}
			else 
			{
				$the_level = $level[$current['parent_id']];
			}
		
			$icon = "";
			if($hash_id_descr[$current['node_type_id']] == "testcase") 
			{
				$icon = "gnome-starthere-mini.png";	
			}
		
			$menustring .= str_repeat('.',$the_level) . ".|" . 
							" " . filterString($current['name']) . "|" . 
						$linkto . "?edit=" . $hash_id_descr[$current['node_type_id']] . 
						"&data=" . $current['id'] . $getArguments . "|" . 
						$hash_id_descr[$current['node_type_id']] . "|" . $icon . "|" . "workframe" ."|\n"; 
		
			$level[$current['parent_id']] = $the_level;
			$pivot = $elem;
		}
	}
	
	return $menustring;
 	//The remaining code stays until all works as expected
	//but will not executed	
	
	if (!$tproject_id)
	{
		return null;
	}	
	
	
	// Queries to determine total test cases
	$sqlProdCount = " SELECT count(mgttestcase.id) AS qty" .
	                " FROM mgtproduct,mgtcomponent, mgtcategory,mgttestcase " .
	                " WHERE mgtproduct.id = mgtcomponent.prodid " .
			            " AND mgtcomponent.id=mgtcategory.compid " .
			            " AND mgtcategory.id=mgttestcase.catid " .
			            " AND mgtproduct.id=" . $tproject_id;
			
			
	$resultProdCount = $db->exec_query($sqlProdCount);
	$prodCount = 0;
	if ($resultProdCount)
	{
		$prodCount = $db->fetch_array($resultProdCount);
	}
		
	
	$tproject_name = filterString($tproject_name);
	if (TL_TREE_KIND == 'LAYERSMENU')
	{ 
		$menustring .= ".|" . $productName . " (" . $prodCount['qty'] . ")|" . $linkto . 
		               "?edit=product&data=" . $tproject_id . $getArguments . "|Product||workframe|\n";
	}
 	elseif (TL_TREE_KIND == 'JTREE')
	{		
		$menustring .=  "['" . $productName . " (" . $prodCount['qty'] . ")','EP({$tproject_id})',\n";
	}
	elseif (TL_TREE_KIND == 'DTREE')
	{
		$dtreeCounter = 0;
		$menustring .= "tlTree.add(" . $dtreeCounter++ . ",-1,'" . $productName . 
		               " (" . $prodCount['qty'] . ")','" . $linkto . "?edit=product&data=" . 
		               $tproject_id . $getArguments . "');\n";
	}
	
	//Parse components
	$sqlCOM = " SELECT id, name from mgtcomponent " .
	          " WHERE prodid=" . $tproject_id . 
	          " ORDER BY name";
	$resultCOM = $db->exec_query($sqlCOM);
		
	while ($myrowCOM = $db->fetch_array($resultCOM)) //loop through all Components
	{
		// Queries to determine how many total test cases there are by Component. 
		// The number is then displayed next to the component
		$sqlCOMCount = " SELECT count(mgttestcase.id) "  . 
		               " FROM mgtcomponent,mgtcategory,mgttestcase " .
		               " WHERE mgtcomponent.id=mgtcategory.compid " .
		               " AND mgtcategory.id=mgttestcase.catid " .
		               " AND mgtcomponent.id=" . $myrowCOM[0];
		$resultCOMCount = $db->exec_query($sqlCOMCount);
		$COMCount = $db->fetch_array($resultCOMCount);

		$componentName = filterString($myrowCOM[1]);
		$sItemName = $componentName . " (" . $COMCount[0] . ")";
		$sItemLink = $linkto . "?edit=component&data=" . $myrowCOM[0] . $getArguments;
		
		if (TL_TREE_KIND == 'LAYERSMENU')
		{ 
			$menustring .= "..|" . $sItemName . "|" . 
			               $sItemLink . "|Component||workframe|\n";
		}
		elseif (TL_TREE_KIND == 'JTREE')
		{	
			$menustring .= "['" . $sItemName . "','ECO({$myrowCOM[0]})',\n";
		}
		elseif (TL_TREE_KIND == 'DTREE')
		{
			$dtreeComponentId = $dtreeCounter;
			$menustring .= "tlTree.add(" . $dtreeCounter++. ",0,'" . $sItemName . 
					"', '" . $sItemLink . "');\n";
		}

		//Parse categories
		$sqlCAT = " SELECT id, name from mgtcategory " .  
		          " WHERE compid=" . $myrowCOM[0] . 
		          " ORDER BY CATorder,id";		
		$resultCAT = $db->exec_query($sqlCAT);

		while ($myrowCAT = $db->fetch_array($resultCAT)) //loop through all Categories
		{
			//Queries to determine how many total test cases there are by Category. 
			//The number is then displayed next to the Category
			$sqlCATCount = " SELECT count(mgttestcase.id) " . 
			               " FROM mgtcategory,mgttestcase " .
			               " WHERE mgtcategory.id=mgttestcase.catid " .
			               " AND mgtcategory.id=" . $myrowCAT[0];
			               
			$resultCATCount = $db->exec_query($sqlCATCount);
			$CATCount = $db->fetch_array($resultCATCount);
	
			$categoryName = filterString($myrowCAT[1]) . " (" . $CATCount[0] . ")";
			
			if (TL_TREE_KIND == 'LAYERSMENU')
			{ 
				$menustring .= "...|" . $categoryName . "|" . 
				               $linkto . "?edit=category&data=" . $myrowCAT[0] . $getArguments . "|Category||workframe|\n";
			}
			elseif (TL_TREE_KIND == 'JTREE') 
			{								
				$menustring .=  " ['" . $categoryName . "','EC({$myrowCAT[0]})',\n";
			}
			elseif (TL_TREE_KIND == 'DTREE')
			{
				$dtreeCategoryId = $dtreeCounter;
				$menustring .= "tlTree.add(" . $dtreeCounter++. "," . $dtreeComponentId . ",'" . 
				               $categoryName . "','" . $linkto . "?edit=category&data=" . 
				               $myrowCAT[0] . $getArguments . "');\n";
			}

			if ($hidetc == 0) 
			{
				//Parse Test Cases
				$sqlTC = "SELECT id, title FROM mgttestcase WHERE catid=" . $myrowCAT[0] . 
				         " ORDER BY TCorder,id";
				$resultTC = $db->exec_query($sqlTC);

				while ($myrowTC = $db->fetch_array($resultTC)) //loop through all Test cases
				{
					$tcName = filterString($myrowTC[1]);
					
					if (TL_TREE_KIND == 'LAYERSMENU')
					{ 
						$menustring .= "....|<b>" . $myrowTC[0] . "</b>: " . $tcName . "|" . 
						               $linkto . "?edit=testcase&data=" . $myrowTC[0] . $getArguments . "|Test Case||workframe|\n";
					}
					elseif (TL_TREE_KIND == 'JTREE')
					{								
						$menustring .=  "['<b>" . $myrowTC[0] . "</b>: " . $tcName . "','ET({$myrowTC[0]})'],\n";
					}	
					elseif (TL_TREE_KIND == 'DTREE')
					{
						$menustring .= "tlTree.add(" . $dtreeCounter++. "," . $dtreeCategoryId . ",'<b>" . 
						               $myrowTC[0] . "</b>: " . $tcName . "','" . $linkto . "?edit=testcase&data=" . 
						               $myrowTC[0] . $getArguments . "');\n";
					}
				}
			}  // end hidetc

			if (TL_TREE_KIND == 'JTREE')
			{
				$menustring .=  "]\n,"; //end the category block
			}
		}

		if (TL_TREE_KIND == 'JTREE')
		{
			$menustring .=  "]\n,"; //end the component block
		}

		// MHT 20050630 - extend script deadline
		// [ 1119896 ] Cannot see test cases in Test Case Execution page
		set_time_limit(TL_TIME_LIMIT_EXTEND);
	}

	// return input string for layersmenu
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
?>