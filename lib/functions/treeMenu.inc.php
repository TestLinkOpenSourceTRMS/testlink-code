<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: treeMenu.inc.php,v $
 *
 * @version $Revision: 1.4 $
 * @modified $Date: 2005/09/15 17:00:14 $
 *
 * 	This file generates tree menu for test specification.
 *
 * @author 20050810 - fm refactoring:  removed deprecated: $_SESSION['product']
 * @author 20050807 - fm refactoring:  removed deprecated: $_SESSION['project']
 *
**/
require_once '../../config.inc.php';

define('TL_TIME_LIMIT_EXTEND', 30); //seconds

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
* @param string $menustring 
* @param string $highLight optional
* @return string generated html code
*/
function invokeMenu($menustring, $highLight = "")
{
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
	$str = addslashes($str);
	$str = htmlspecialchars($str, ENT_QUOTES);	
	
	return $str;
}


/** 
* 	generate data for tree menu of Test Specification
*
* @param numeric prodID
* @param string  prodName
* @param string $linkto path for generated URL
*	@param integer $hidetc [0: show TCs, 1: disable TCs ]
*	@param string $getArguments additional $_GET arguments
* @return string input string for layersmenu
*
* @author Francisco Mancardi - fm - reduce global coupling
*
*/
function generateTestSpecTree($prodID, $prodName, $linkto, $hidetc, $getArguments = '')
{
	
	if (!$prodID)
		return null;
	$menustring = null; // storage for output
	
	// Queries to determine total test cases
	$sqlProdCount = "select count(mgttestcase.id) from mgtproduct,mgtcomponent," .
			"mgtcategory,mgttestcase where mgtproduct.id = mgtcomponent.prodid " .
			"and mgtcomponent.id=mgtcategory.compid and " .
			"mgtcategory.id=mgttestcase.catid and mgtproduct.id=" . $prodID;
	$resultProdCount = do_mysql_query($sqlProdCount);
	if ($resultProdCount)
		$prodCount = mysql_fetch_row($resultProdCount);
	else
		$prodCount = 0;
	
	$productName = filterString($prodName);
	if (TL_TREE_KIND == 'LAYERSMENU')
	{ 
		$menustring .= ".|" . $productName . " (" . $prodCount[0] . ")|" . $linkto . "?edit=product&data=" . $prodID . $getArguments . "|Product||workframe|\n";
	}
 	elseif (TL_TREE_KIND == 'JTREE')
	{		
		$menustring .=  "['" . $productName . " (" . $prodCount[0] . ")','EP({$prodID})',\n";
	}
	elseif (TL_TREE_KIND == 'DTREE')
	{
		$dtreeCounter = 0;
		$menustring .= "tlTree.add(" . $dtreeCounter++ . ",-1,'" . $productName . " (" . $prodCount[0] . ")','" . $linkto . "?edit=product&data=" . $prodID . $getArguments . "');\n";
	}
	
	//Parse components
	$sqlCOM = "select id, name from mgtcomponent where prodid='" . $prodID . "' order by name";
	$resultCOM = do_mysql_query($sqlCOM);
		
	while ($myrowCOM = mysql_fetch_row($resultCOM)) //loop through all Components
	{
		//Queries to determine how many total test cases there are by Component. The number is then displayed next to the component
		$sqlCOMCount = "select count(mgttestcase.id) from mgtcomponent,mgtcategory,mgttestcase where mgtcomponent.id=mgtcategory.compid and mgtcategory.id=mgttestcase.catid and mgtcomponent.id='" . $myrowCOM[0] . "'";
		$resultCOMCount = do_mysql_query($sqlCOMCount);
		$COMCount = mysql_fetch_row($resultCOMCount);

		$componentName = filterString($myrowCOM[1]);
		if (TL_TREE_KIND == 'LAYERSMENU')
		{ 
			$menustring .= "..|" . $componentName . " (" . $COMCount[0] . ")|" . $linkto . "?edit=component&data=" . $myrowCOM[0] . $getArguments . "|Component||workframe|\n";
		}
		elseif (TL_TREE_KIND == 'JTREE')
		{	
			$menustring .= "['" . $componentName . " (" . $COMCount[0] . ")','ECO({$myrowCOM[0]})',\n";
		}
		elseif (TL_TREE_KIND == 'DTREE')
		{
			$dtreeComponentId = $dtreeCounter;
			$menustring .= "tlTree.add(" . $dtreeCounter++. ",0,'" . $componentName . " (" . $COMCount[0] . ")','" . $linkto . "?edit=component&data=" . $myrowCOM[0] . $getArguments . "');\n";
		}

		//Parse categories
		$sqlCAT = "select id, name from mgtcategory where compid='" . $myrowCOM[0] . "' order by CATorder,id";		
		$resultCAT = do_mysql_query($sqlCAT);

		while ($myrowCAT = mysql_fetch_row($resultCAT)) //loop through all Categories
		{
			//Queries to determine how many total test cases there are by Category. The number is then displayed next to the Category
			$sqlCATCount = "select count(mgttestcase.id) from mgtcategory,mgttestcase where mgtcategory.id=mgttestcase.catid and mgtcategory.id='" . $myrowCAT[0] . "'";
			$resultCATCount = do_mysql_query($sqlCATCount);
			$CATCount = mysql_fetch_row($resultCATCount);
	
			$categoryName = filterString($myrowCAT[1]);
			if (TL_TREE_KIND == 'LAYERSMENU')
			{ 
				$menustring .= "...|" . $categoryName . " (" . $CATCount[0] . ")|" . $linkto . "?edit=category&data=" . $myrowCAT[0] . $getArguments . "|Category||workframe|\n";
			}
			elseif (TL_TREE_KIND == 'JTREE') 
			{								
				$menustring .=  " ['" . $categoryName . " (" . $CATCount[0] . ")','EC({$myrowCAT[0]})',\n";
			}
			elseif (TL_TREE_KIND == 'DTREE')
			{
				$dtreeCategoryId = $dtreeCounter;
				$menustring .= "tlTree.add(" . $dtreeCounter++. "," . $dtreeComponentId . ",'" . $categoryName . " (" . $CATCount[0] . ")','" . $linkto . "?edit=category&data=" . $myrowCAT[0] . $getArguments . "');\n";
			}

			if ($hidetc == 0) 
			{
				//Parse Test Cases
				$sqlTC = "select id, title from mgttestcase where catid='" . $myrowCAT[0] . "' order by TCorder,id";
				$resultTC = do_mysql_query($sqlTC);

				while ($myrowTC = mysql_fetch_row($resultTC)) //loop through all Test cases
				{
					$tcName = filterString($myrowTC[1]);
					if (TL_TREE_KIND == 'LAYERSMENU')
					{ 
						$menustring .= "....|<b>" . $myrowTC[0] . "</b>: " . $tcName . "|" . $linkto . "?edit=testcase&data=" . $myrowTC[0] . $getArguments . "|Test Case||workframe|\n";
					}
					elseif (TL_TREE_KIND == 'JTREE')
					{								
						$menustring .=  "['<b>" . $myrowTC[0] . "</b>: " . $tcName . "','ET({$myrowTC[0]})'],\n";
					}	
					elseif (TL_TREE_KIND == 'DTREE')
					{
						$menustring .= "tlTree.add(" . $dtreeCounter++. "," . $dtreeCategoryId . ",'<b>" . $myrowTC[0] . "</b>: " . $tcName . "','" . $linkto . "?edit=testcase&data=" . $myrowTC[0] . $getArguments . "');\n";
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
*	@param integer $hidetc [0: show TCs, 1: disable TCs ]
*	@param string $getArguments additional $_GET arguments
* 	@return string input string for layersmenu
*/
function generateTestSuiteTree($linkto, $hidetc, $getArguments = '')
{
	$menustring = null;
	// define root directory
	if (TL_TREE_KIND == 'LAYERSMENU') 
		$menustring .= ".|" . $_SESSION['testPlanName'] . "|" . $linkto . "?level=root" . $getArguments . "|Test Case Suite||workframe|\n";
	elseif (TL_TREE_KIND == 'JTREE')
		$menustring .= "['" . $_SESSION['testPlanName'] . "','PTP()',\n";
	elseif (TL_TREE_KIND == 'DTREE')
	{
		$dtreeCounter = 0;
		$menustring .= "tlTree.add(" . $dtreeCounter++ . ",-1,'" . $_SESSION['testPlanName'] . "','" . $linkto . "?level=root" . $getArguments . "');\n";
	}
	
	// grab every component depending on the test plan
	//
	// 20050807 - fm 
	$sql = "select component.id, component.name from component,project where " .
			"project.id = " . $_SESSION['testPlanId'] . 
			" and component.projid = project.id order by component.name";
	$comResult = do_mysql_query($sql);

	while ($myrowCOM = mysql_fetch_row($comResult)) { //display all the components until we run out

		$componentName = filterString($myrowCOM[1]);
		if (TL_TREE_KIND == 'LAYERSMENU') 
			$menustring .= "..|" . $componentName . "|" . $linkto . "?level=component&data=" . $myrowCOM[0] . $getArguments . "|Component||workframe|\n";
		elseif (TL_TREE_KIND == 'JTREE')
			$menustring .= "['" . $componentName . "','PCO({$myrowCOM[0]})',\n";
		elseif (TL_TREE_KIND == 'DTREE')
		{
			$dtreeComponentId = $dtreeCounter;
			$menustring .= "tlTree.add(" . $dtreeCounter++. ",0,'" . $componentName . "','" . linkto . "?level=component&data=" . $myrowCOM[0] . $getArguments . "');\n";
		}

		// grab every category depending on the component 
		$catResult = do_mysql_query("select category.id, category.name from component," .
				"category where component.id = " . $myrowCOM[0] . 
				" and component.id = category.compid order by CATorder,category.id");
			
		while ($myrowCAT = mysql_fetch_row($catResult)) {  //display all the categories until we run out

			$categoryName = filterString($myrowCAT[1]);
			if (TL_TREE_KIND == 'LAYERSMENU') 
				$menustring .= "...|" . $categoryName . "|" . $linkto . "?level=category&data=" . $myrowCAT[0] . $getArguments . "|Category||workframe|\n";
			elseif (TL_TREE_KIND == 'JTREE')				
				$menustring .= "['" . $categoryName . "','PC({$myrowCAT[0]})',\n";
			elseif (TL_TREE_KIND == 'DTREE')
			{
				$dtreeCategoryId = $dtreeCounter;
				$menustring .= "tlTree.add(" . $dtreeCounter++. "," . $dtreeComponentId . ",'" . $categoryName . "','" . $linkto . "?level=category&data=" . $myrowCAT[0] . $getArguments . "');\n";
			}

			// create TCs if required
			if ($hidetc == 0) {
				$TCResult = do_mysql_query("SELECT testcase.id, testcase.title,testcase." .
					                         "mgttcid from category,testcase where category.id = " . 
					$myrowCAT[0] . " and category.id = testcase.catid order by " .
					"TCorder,testcase.mgttcid");

				while ($myrowTC = mysql_fetch_row($TCResult)) 
				{  
					$tcName = filterString($myrowTC[1]);
					if (TL_TREE_KIND == 'LAYERSMENU')
					{ 
						$menustring .= "....|<b>" . $myrowTC[2] . "</b>: " . $tcName . "|" . $linkto . 
						               "?level=tc&data=" . $myrowTC[0] . $getArguments . "|Test Case||workframe|\n";
					}
					elseif (TL_TREE_KIND == 'JTREE')
					{						
						$menustring .= "['<b>" . $myrowTC[2] . "</b>:" . $tcName . "','PT({$myrowTC[0]})'],\n";
					}
					elseif (TL_TREE_KIND == 'DTREE')
					{
						$menustring .= "tlTree.add(" . $dtreeCounter++. "," . $dtreeCategoryId . ",'<b>" . 
						               $myrowTC[2] . "</b>:" . $tcName . "','" . $linkto . "?level=tc&data=" . 
						               $myrowTC[0] . $getArguments . "');\n";
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