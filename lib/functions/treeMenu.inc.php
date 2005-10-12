<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: treeMenu.inc.php,v $
 *
 * @version $Revision: 1.6 $
 * @modified $Date: 2005/10/12 04:19:10 $ by $Author: havlat $
 * @author Martin Havlat
 *
 * 	This file generates tree menu for test specification and test execution.
 * 	Three kinds of menu component are supported: LAYERSMENU (default), DTREE,
 * 	and JTREE. Used type is defined in config.inc.php.
 * 
 * Revisions:
 *
 * @author 20050810 - fm refactoring:  removed deprecated: $_SESSION['product']
 * @author 20050807 - fm refactoring:  removed deprecated: $_SESSION['project']
 * 20051011 - MHT - minor refactorization, header update
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
 **/
function invokeMenu($menustring, $highLight = "")
{
	tLog('invokeMenu started');
	
	if (TL_TREE_KIND == 'LAYERSMENU') 
	{
		$mid = new TreeMenu();

		$mid->setLibjsdir(TL_MENU_PATH . 'libjs' . DS);
		$mid->setImgwww(TL_MENU_WWW . 'menuimages/');
		$mid->setIconsize(16, 16);

		$mid->setMenuStructureString($menustring);
		$mid->parseStructureForMenu('treemenu1');
		
		//I had to figure this one out on my own.
		//The method I'm using will color an item in the tree if you pass it a value
		if($highLight != "")
		{
			$mid->setSelectedItemByUrl('treemenu1', $highLight);
    }
    
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
	$str = addslashes($str);
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
 * Revisions:
 * @author Francisco Mancardi - fm - reduce global coupling
 *
 */
function generateTestSpecTree($prodID, $prodName, $linkto, $hidetc, $getArguments = '')
{
	
	if (!$prodID)
	{
		return null;
	}	
	
	$menustring = null; // storage variable for output
	
	// Queries to determine total test cases
	$sqlProdCount = " SELECT count(mgttestcase.id) AS qty" .
	                " FROM mgtproduct,mgtcomponent, mgtcategory,mgttestcase " .
	                " WHERE mgtproduct.id = mgtcomponent.prodid " .
			            " AND mgtcomponent.id=mgtcategory.compid " .
			            " AND mgtcategory.id=mgttestcase.catid " .
			            " AND mgtproduct.id=" . $prodID;
			
			
	$resultProdCount = do_mysql_query($sqlProdCount);
	$prodCount = 0;
	if ($resultProdCount)
	{
		$prodCount = mysql_fetch_assoc($resultProdCount);
	}
		
	
	$productName = filterString($prodName);
	if (TL_TREE_KIND == 'LAYERSMENU')
	{ 
		$menustring .= ".|" . $productName . " (" . $prodCount['qty'] . ")|" . $linkto . 
		               "?edit=product&data=" . $prodID . $getArguments . "|Product||workframe|\n";
	}
 	elseif (TL_TREE_KIND == 'JTREE')
	{		
		$menustring .=  "['" . $productName . " (" . $prodCount['qty'] . ")','EP({$prodID})',\n";
	}
	elseif (TL_TREE_KIND == 'DTREE')
	{
		$dtreeCounter = 0;
		$menustring .= "tlTree.add(" . $dtreeCounter++ . ",-1,'" . $productName . 
		               " (" . $prodCount['qty'] . ")','" . $linkto . "?edit=product&data=" . 
		               $prodID . $getArguments . "');\n";
	}
	
	//Parse components
	$sqlCOM = " SELECT id, name from mgtcomponent " .
	          " WHERE prodid=" . $prodID . 
	          " ORDER BY name";
	$resultCOM = do_mysql_query($sqlCOM);
		
	while ($myrowCOM = mysql_fetch_row($resultCOM)) //loop through all Components
	{
		// Queries to determine how many total test cases there are by Component. 
		// The number is then displayed next to the component
		$sqlCOMCount = " SELECT count(mgttestcase.id) "  . 
		               " FROM mgtcomponent,mgtcategory,mgttestcase " .
		               " WHERE mgtcomponent.id=mgtcategory.compid " .
		               " AND mgtcategory.id=mgttestcase.catid " .
		               " AND mgtcomponent.id=" . $myrowCOM[0];
		$resultCOMCount = do_mysql_query($sqlCOMCount);
		$COMCount = mysql_fetch_row($resultCOMCount);

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
		$resultCAT = do_mysql_query($sqlCAT);

		while ($myrowCAT = mysql_fetch_row($resultCAT)) //loop through all Categories
		{
			//Queries to determine how many total test cases there are by Category. 
			//The number is then displayed next to the Category
			$sqlCATCount = " SELECT count(mgttestcase.id) " . 
			               " FROM mgtcategory,mgttestcase " .
			               " WHERE mgtcategory.id=mgttestcase.catid " .
			               " AND mgtcategory.id=" . $myrowCAT[0];
			               
			$resultCATCount = do_mysql_query($sqlCATCount);
			$CATCount = mysql_fetch_row($resultCATCount);
	
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
				$resultTC = do_mysql_query($sqlTC);

				while ($myrowTC = mysql_fetch_row($resultTC)) //loop through all Test cases
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
function generateTestSuiteTree($linkto, $hidetc, $getArguments = '')
{
	$menustring = null;
	// define root directory
	if (TL_TREE_KIND == 'LAYERSMENU') 
	{
		$menustring .= ".|" . $_SESSION['testPlanName'] . "|" . $linkto . "?level=root" . 
		               $getArguments . "|Test Case Suite||workframe|\n";
	}	
	elseif (TL_TREE_KIND == 'JTREE')
	{
		$menustring .= "['" . $_SESSION['testPlanName'] . "','PTP()',\n";
	}	
	elseif (TL_TREE_KIND == 'DTREE')
	{
		$dtreeCounter = 0;
		$menustring .= "tlTree.add(" . $dtreeCounter++ . ",-1,'" . $_SESSION['testPlanName'] . "','" . 
		               $linkto . "?level=root" . $getArguments . "');\n";
	}
	
	// 20050915 - fm - grab every component depending on the test plan
	$sql = " SELECT component.id, mgtcomponent.name " .
	       " FROM component,mgtcomponent, project " .
	       " WHERE mgtcomponent.id = component.mgtcompid " .
			   " AND component.projid = project.id " .
			   " AND project.id = " . $_SESSION['testPlanId'] . 
			   " ORDER BY mgtcomponent.name";
	   
	$comResult = do_mysql_query($sql);

	while ($myrowCOM = mysql_fetch_assoc($comResult)) { 

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
		
		$catResult = do_mysql_query($sql);
			
		while ($myrowCAT = mysql_fetch_assoc($catResult)) {  //display all the categories until we run out

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

				$TCResult = do_mysql_query($sql);

				while ($myrowTC = mysql_fetch_assoc($TCResult)) 
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