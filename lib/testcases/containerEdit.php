<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/ */
/* $Id: containerEdit.php,v 1.2 2005/08/16 18:00:59 franciscom Exp $ */
/* Purpose:  This page manages all the editing of test specification containers. */
/*
 * @ author: francisco mancardi - 20050810
 * deprecated $_SESSION['product'] removed
*/
require_once("../../config.inc.php");
require_once("../functions/common.php");
require('archive.inc.php');
require_once("../../lib/functions/lang_api.php");
testlinkInitPage();

$data = isset($_GET['data']) ? intval($_GET['data']) : null;
$smarty = new TLSmarty;

//If the user has chosen to edit a component then show this code
if(isset($_POST['editCOM']))
{
	$smarty->assign('path_htmlarea', $_SESSION['basehref'] . 'third_party/htmlarea/');
	$smarty->assign('level', 'component');
	$smarty->assign('data',getComponent($data));
	$smarty->display('containerEdit.tpl');
//update a component
}
else if(isset($_POST['updateCOM']))
{
	$name = isset($_POST['name']) ? strings_stripSlashes($_POST['name']) : null;
	$intro = isset($_POST['intro']) ? strings_stripSlashes($_POST['intro']) : null;
	$scope = isset($_POST['scope']) ? strings_stripSlashes($_POST['scope']) : null;
	$ref = isset($_POST['ref']) ? strings_stripSlashes($_POST['ref']) : null;
	$method = isset($_POST['method']) ? strings_stripSlashes($_POST['method']) : null;
	$lim = isset($_POST['lim']) ? strings_stripSlashes($_POST['lim']) : null;
	
	// display updated component
	if (updateComponent($data,$name,$intro,$scope,$ref,$method,$lim))
		$SQLResult = 'ok';
   	else
		$SQLResult = mysql_error();

	showComponent($data, $SQLResult);
}
else if(isset($_POST['newCOM'])) //Creating a new component
{
	// display smarty
	$smarty->assign('path_htmlarea', $_SESSION['basehref'] . 'third_party/htmlarea/');
	$smarty->assign('level', 'component');
	$smarty->assign('sqlResult', null);
	$smarty->display('containerNew.tpl');
}
else if(isset($_POST['addCOM']))
{
	$name = isset($_POST['name']) ? strings_stripSlashes($_POST['name']) : null;
	$intro = isset($_POST['intro']) ? strings_stripSlashes($_POST['intro']) : null;
	$scope = isset($_POST['scope']) ? strings_stripSlashes($_POST['scope']) : null;
	$ref = isset($_POST['ref']) ? strings_stripSlashes($_POST['ref']) : null;
	$method = isset($_POST['method']) ? strings_stripSlashes($_POST['method']) : null;
	$lim = isset($_POST['lim']) ? strings_stripSlashes($_POST['lim']) : null;

	// display smarty
	if (strlen($name))
	{
		if (insertProductComponent($_SESSION['productID'],$name,$intro,$scope,$ref,$method,$lim))
			$result = 'ok';
	   	else
			$result = mysql_error();
	}
	else
		$result = lang_get('warning_empty_com_name');;
		
	$smarty->assign('sqlResult',$result);
	$smarty->assign('path_htmlarea', $_SESSION['basehref'] . 'third_party/htmlarea/');
	$smarty->assign('level', 'component');
	$smarty->assign('name', $name);
	$smarty->display('containerNew.tpl');

// delete component and inner data (cat + tc) 
}
else if (isset($_POST['deleteCOM']))
{
	//check to see if the user said he was sure he wanted to delete
	if(isset($_GET['sure']) && ($_GET['sure'] == 'yes'))
	{
		$compID = $data;

		$cats = null;
		getComponentCategoryIDs($compID,$cats);
		if (sizeof($cats))
		{
			$catIDs = "'".implode(",",$cats)."'";
			deleteCategoriesTestCases($catIDs);
			deleteComponentCategories($compID);
		}
		if (deleteComponent($compID))
			$smarty->assign('sqlResult', 'ok');
	   	else
			$smarty->assign('sqlResult', mysql_error());
	}
	else //if the user has clicked the delete button on the archive page show the delete confirmation page
		$smarty->assign('data', $data);
	
	$smarty->assign('level', 'component');
	$smarty->display('containerDelete.tpl');
//user has chosen to move/copy a component to a different product
}
else if(isset($_POST['moveCom'])) 
{
	$products = null;
	getAllProductsBut($_SESSION['productID'],$products);

	$smarty->assign('old', $_SESSION['productID']); // original container
	$smarty->assign('arraySelect', $products);
	$smarty->assign('data', $data);
	$smarty->assign('level', 'component');
	$smarty->display('containerMove.tpl');
}
else if(isset($_POST['reorderCAT'])) //user has chosen the reorder CAT page
{
	$cats = null;
	getOrderedComponentCategories($data,$cats);

	$smarty->assign('arraySelect', $cats);
	$smarty->assign('data', $data);
	$smarty->display('containerOrder.tpl');
}
else if(isset($_POST['updateCategoryOrder'])) //Execute update categories order
{
	$newArray = extractInput($_POST);
	$generalResult = 'ok';
	
	//skip the first one, this is the submit button
	for($i = 1;$i < sizeof($newArray);$i++)
	{
		$catID = intval($newArray[$i++]);
		$order = intval($newArray[$i]);
		
		if (!updateCategoryOrder($catID,$order))
			$generalResult .= lang_get('error_update_catorder')." {$catID}";
	}

	showComponent($data, $generalResult);
}
elseif(isset($_POST['newCAT'])) //Creating a new category
{
	// display smarty
	$smarty->assign('path_htmlarea', $_SESSION['basehref'] . 'third_party/htmlarea/');
	$smarty->assign('sqlResult', null);
	$smarty->assign('level', 'category');
	$smarty->assign('data', $data);
	$smarty->display('containerNew.tpl');
}
else if(isset($_POST['addCAT']))
{
	//Execute query 'Add Category'
	$name = isset($_POST['name']) ? strings_stripSlashes($_POST['name']) : null;
	$objective = isset($_POST['objective']) ? strings_stripSlashes($_POST['objective']) : null;
	$config = isset($_POST['config']) ? strings_stripSlashes($_POST['config']) : null;
	$testdata = isset($_POST['testdata']) ? strings_stripSlashes($_POST['testdata']) : null;
	$tools = isset($_POST['tools']) ? strings_stripSlashes($_POST['tools']) : null;

	if (insertComponentCategory($data,$name,$objective,$config,$testdata,$tools))
		$sqlResult = 'ok';
	else
		$sqlResult = lang_get('error_cat_add');
		
	$smarty->assign('path_htmlarea', $_SESSION['basehref'] . 'third_party/htmlarea/');
	$smarty->assign('sqlResult', $sqlResult);
	$smarty->assign('data', $data);
	$smarty->assign('name', $name);
	$smarty->assign('level', 'category');
	// show again a new container form
	$smarty->display('containerNew.tpl');
}
else if (isset($_POST['deleteCat']))
{
	/** @todo delete also tests in test plan(?) */
	if(isset($_GET['sure']) && ($_GET['sure'] == 'yes'))
	{
		deleteCategoriesTestCases($data);
		$smarty->assign('sqlResult',  deleteCategory($data) ? 'ok' : mysql_error());
	}
	else
		$smarty->assign('data', $data);
	
	$smarty->assign('level', 'category');
	$smarty->display('containerDelete.tpl');
}
else if(isset($_POST['editCat']))
{
	$smarty->assign('path_htmlarea', $_SESSION['basehref'] . 'third_party/htmlarea/');
	$smarty->assign('level', 'category');
	$smarty->assign('data', getCategory($data));
	$smarty->display('containerEdit.tpl');
}
elseif(isset($_POST['updateCat'])) //Update a category (from edit window)
{
	$name = isset($_POST['name']) ? strings_stripSlashes($_POST['name']) : null;
	$objective = isset($_POST['objective']) ? strings_stripSlashes($_POST['objective']) : null;
	$config = isset($_POST['config']) ? strings_stripSlashes($_POST['config']) : null;
	$pdata = isset($_POST['data']) ? strings_stripSlashes($_POST['data']) : null;
	$tools = isset($_POST['tools']) ? strings_stripSlashes($_POST['tools']) : null;
	
	$sqlResult = updateCategory($data,$name,$objective,$config,$pdata,$tools) ? 'ok' : mysql_error();
	// display updated component
	showCategory($data, $sqlResult);
}
elseif(isset($_POST['moveCat']))
{
	$compID = 0;
	$prodID = 0;
	getCategoryComponentAndProduct($data,$compID,$prodID);
	$comps = null;
	getAllProductComponentsBut($compID,$prodID,$comps);

	$smarty->assign('old', $compID); // original container
	$smarty->assign('arraySelect', $comps);
	$smarty->assign('data', $data);
	$smarty->assign('level', 'category');
	$smarty->display('containerMove.tpl');
	// reorder test cases form	
}
else if(isset($_POST['reorderTC'])) //user has chosen to reorder the test cases of this category
{
	$tcs = null;
	getOrderedCategoryTestcases($data,$tcs);

	$smarty->assign('arrTC', $tcs);
	$smarty->assign('data', $data);
	$smarty->display('tcReorder.tpl');
	
} //Update db according to a category's reordered test cases
else if(isset($_POST['updateTCorder'])) 
{
	$newArray = extractInput($_POST); //Reorder the POST array to numeric
	$generalResult = 'ok';
	
	//skip the first one, this is the submit button
	for($i = 1;$i < sizeof($newArray);$i++)
	{
		$id = intval($newArray[$i++]);
		$order = intval($newArray[$i]);
		
		if (!updateTestCaseOrder($id,$order))
			$generalResult .= mysql_error() . '<br />';
	}

	$smarty->assign('sqlResult', $generalResult);
	$smarty->assign('level', 'category');
	$smarty->assign('data', getCategory($data));
	$smarty->display('containerView.tpl');
}
else if(isset($_POST['categoryCopy']))
{
	$compID = isset($_POST['moveCopy']) ? intval($_POST['moveCopy']) : 0;
	$nested = isset($_POST['nested']) ? $_POST['nested'] : "no";
	$old = isset($_POST['old']) ? intval($_POST['old']): 0;
	
	if ($compID)
		$result = copyCategoryToComponent($compID, $data, $nested);
	showComponent($old, $result,'update',$compID);
}
else if(isset($_POST['categoryMove']))
{
	$compID = isset($_POST['moveCopy']) ? intval($_POST['moveCopy']) : 0;
	$old = isset($_POST['old']) ? intval($_POST['old']): 0;
	
	if ($compID)
		$result = moveCategoryToComponent($compID, $data);
	showComponent($old, $result);
}
else if(isset($_POST['componentCopy']))
{
	$prodID = isset($_POST['moveCopy']) ? intval($_POST['moveCopy']) : 0;
	$nested = isset($_POST['nested']) ? $_POST['nested'] : "no";
	$result = 0;
	if ($prodID)
		$result = copyComponentToProduct($prodID, $data, $nested);
	
	showProduct($_SESSION['productID'], $result,'update',$prodID);
}
else if(isset($_POST['componentMove']))
{
	$prodID = isset($_POST['moveCopy']) ? intval($_POST['moveCopy']) : 0;
	$result = 0;
	if ($prodID)
		$result = moveComponentToProduct($prodID, $data);
	showProduct($_SESSION['productID'], $result);
}
else 
{
	trigger_error("containerEdit.php - No correct GET/POST data", E_USER_ERROR);
} // end of main if
?>

