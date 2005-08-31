<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/ */
/* $Id: containerEdit.php,v 1.11 2005/08/31 08:46:28 franciscom Exp $ */
/* Purpose:  This page manages all the editing of test specification containers. */
/*
 *
 * @author: francisco mancardi - 20050830
 * bug in deleteCOM e deleteCAT 
 *
 * @author: francisco mancardi - 20050820/20050826
 * fckeditor
 * refactoring
 *
 * @author: francisco mancardi - 20050820
 * added missing control con category name length
 *
 * @author: francisco mancardi - 20050810
 * deprecated $_SESSION['product'] removed
*/
require_once("../../config.inc.php");
require_once("../functions/common.php");
require('archive.inc.php');
require_once("../../lib/functions/lang_api.php");
require_once("../../third_party/fckeditor/fckeditor.php");
require('containerComp.inc.php');
require('containerCat.inc.php');

testlinkInitPage();


// 20050826 - fm - $data has been replaced with the corresponding container ID
$my_componentID = isset($_GET['componentID']) ? intval($_GET['componentID']) : null;
$my_categoryID  = isset($_GET['categoryID']) ? intval($_GET['categoryID']) : null;
// ----------------------------------------------------------------------------
$my_productID   = isset($_GET['productID']) ? intval($_GET['productID']) : null;
if( !$my_productID )
{
  $my_productID = $_SESSION['productID'];	
}
// ----------------------------------------------------------------------------

// 20050830 - fm 
$compName = isset($_REQUEST['componentName']) ? stripslashes($_REQUEST['componentName']) : null;
$catName = isset($_REQUEST['categoryName']) ? stripslashes($_REQUEST['categoryName']) : null;


$objectID = isset($_GET['objectID']) ? intval($_GET['objectID']) : null;
$smarty = new TLSmarty;

// 20050822 - fm
// name/key of fck objects to create and table column name
$a_keys['component'] = array('intro','scope','ref','method','lim');
$a_keys['category']  = array('objective','config','data','tools');

$a_tpl = array( 'moveCom' => 'containerMove.tpl',
                'addCOM' => 'containerNew.tpl',
                'deleteCOM' => 'containerDelete.tpl',
                'moveCat' => 'containerMove.tpl',
                'addCAT'  => 'containerNew.tpl',
                'deleteCat' => 'containerDelete.tpl',
                'reorderCAT' => 'containerOrder.tpl',
                'updateTCorder' => 'containerView.tpl',
                'reorderTC' => 'tcReorder.tpl'); 


$a_com_actions = array ('editCOM', 'newCOM', 'addCOM', 'deleteCOM',
                        'moveCom','updateCOM', 'componentCopy', 'componentMove' );

$a_cat_actions = array ('reorderCAT','updateCategoryOrder','newCAT','addCAT',
                        'deleteCat','editCat','updateCat','moveCat','categoryCopy',
                        'categoryMove', 'updateTCorder', 'reorderTC');

// ----------------------------------------------------------------------------
$the_tpl=null;

$do_search=1;                    
foreach ($a_com_actions as $the_key)
{
  if (isset($_POST[$the_key]) )
  {
    $the_tpl = $a_tpl[$the_key];
    $action = $the_key;
    $level = 'component';
    $do_search = 0;
    break;
  }
}                    

if ($do_search)
{
  foreach ($a_cat_actions as $the_key)
  {
    if (isset($_POST[$the_key]) )
    {
      $the_tpl = $a_tpl[$the_key];
      $level = 'category';
      $action = $the_key;
      $do_search = 0;
      break;
    }
  }                    
}
// ----------------------------------------------------------------------------
$smarty->assign('level', $level);
 

// --------------------------------------------------------------------
// create  fckedit objects
//
$amy_keys = $a_keys[$level];
$oFCK = array();
foreach ($amy_keys as $key)
{
 $oFCK[$key] = new FCKeditor($key) ;
 $of = &$oFCK[$key];
 $of->BasePath = $_SESSION['basehref'] . 'third_party/fckeditor/';
 $of->ToolbarSet=$g_fckeditor_toolbar;;
}
// --------------------------------------------------------------------


//If the user has chosen to edit a component then show this code
if( $action == 'editCOM' || $action == 'newCOM')
{
	viewer_edit_new_com($amy_keys, $oFCK, $action,$my_productID, $my_componentID);
}
else if($action == 'updateCOM')
{
	$c_data=get_comp_values_from_post($amy_keys);
	$SQLResult = 'ok';
	if (!updateComponent($my_componentID,
	                     $c_data['name'],$c_data['intro'],$c_data['scope'],
		                   $c_data['ref'],$c_data['method'],$c_data['lim']))
	{
		$SQLResult = mysql_error();
	}	
	showComponent($my_componentID, $SQLResult);
}
else if($action == 'addCOM')
{
  // we will arrive here after submit in containerNew.tpl (newCOM)
	$c_data=get_comp_values_from_post($amy_keys);

	$result = lang_get('warning_empty_com_name');
	if (strlen($c_data['name']))
	{
		$result = 'ok';
		if (!insertProductComponent($my_productID,
		                            $c_data['name'],$c_data['intro'],$c_data['scope'],
		                            $c_data['ref'],$c_data['method'],$c_data['lim']))
		{	
	   $result = mysql_error();
		}	
	}

  // setup for displaying an empty form
	foreach ($amy_keys as $key)
  {
  	// Warning:
  	// the data assignment will work while the keys in $the_data are identical
  	// to the keys used on $oFCK.
  	$of = &$oFCK[$key];
  	$smarty->assign($key, $of->CreateHTML());
	}
			
	$smarty->assign('sqlResult',$result);
	//$smarty->assign('name', $c_data['name']);

}
else if ($action == 'deleteCOM')
{
  // delete component and inner data (cat + tc) 
	//check to see if the user said he was sure he wanted to delete
	if(isset($_GET['sure']) && ($_GET['sure'] == 'yes'))
	{

		$cats = null;
		$smarty->assign('sqlResult', 'ok');

		getComponentCategoryIDs($objectID,$cats);
		if (sizeof($cats))
		{
			$catIDs = "'".implode(",",$cats)."'";
			deleteCategoriesTestCases($catIDs);
			deleteComponentCategories($objectID);
		}
		
		if (!deleteComponent($objectID))
		{
		  $smarty->assign('sqlResult', mysql_error());
		}
			
	}
	else
	{
	  //if the user has clicked the delete button on the archive page show the delete confirmation page
	  $smarty->assign('objectName', $compName);
		$smarty->assign('objectID', $my_componentID);
	}
}
else if( $action == 'moveCom') 
{
	$products = null;
	getAllProductsBut($my_productID,$products);

	$smarty->assign('old_containerID', $my_productID); // original container
	$smarty->assign('arraySelect', $products);
	$smarty->assign('objectID', $my_componentID);
}
else if($action == 'reorderCAT') //user has chosen the reorder CAT page
{
	$cats = null;
	getOrderedComponentCategories($my_componentID,$cats);

	$smarty->assign('arraySelect', $cats);
	$smarty->assign('data', $my_componentID);
}
else if($action == 'updateCategoryOrder') //Execute update categories order
{
	$newArray = extractInput($_POST);
	$generalResult = 'ok';
	
	//skip the first one, this is the submit button
	$qta_loops=sizeof($newArray);
	for($i = 1;$i < $qta_loops ;$i++)
	{
		$catID = intval($newArray[$i++]);
		$order = intval($newArray[$i]);
		
		if (!updateCategoryOrder($catID,$order))
			$generalResult .= lang_get('error_update_catorder')." {$catID}";
	}

	showComponent($my_componentID, $generalResult);
}
else if($action == 'editCat' || $action == 'newCAT')
{
	viewer_edit_new_cat($amy_keys, $oFCK, $action, $my_componentID, $my_categoryID);
}
else if($action == 'addCAT')
{
  // we will arrive here after submit in containerNew.tpl (newCAT)
  $c_data = get_comp_values_from_post($amy_keys);
 	$result = lang_get('warning_empty_cat_name');	

  if (strlen($c_data['name']))
	{
		$result = lang_get('error_cat_add');
	 	if (insertComponentCategory($my_componentID,
	 	                            $c_data['name'], $c_data['objective'],
	 	                            $c_data['config'],$c_data['data'],$c_data['tools']))
	 	{
  		$result = 'ok';
  	}	
  }

	// show again a new empty container form
	foreach ($amy_keys as $key)
  {
  	// Warning:
  	// the data assignment will work while the keys in $the_data are identical
  	// to the keys used on $oFCK.
  	$of = &$oFCK[$key];
  	$smarty->assign($key, $of->CreateHTML());
	}
	$smarty->assign('sqlResult',$result);

}
else if ($action == 'deleteCat')
{
	/** @todo delete also tests in test plan(?) */
	if(isset($_GET['sure']) && ($_GET['sure'] == 'yes'))
	{
		deleteCategoriesTestCases($objectID);
		$smarty->assign('sqlResult',  deleteCategory($objectID) ? 'ok' : mysql_error());
	}
	else
	{
		// 20050830 - fm 
	  $smarty->assign('objectName', $catName);
		$smarty->assign('objectID', $my_categoryID);
	}	
	
}
elseif($action == 'updateCat') //Update a category (from edit window)
{

  $c_data = get_comp_values_from_post($amy_keys);
	$sqlResult = updateCategory($my_categoryID,
	                            $c_data['name'], $c_data['objective'],$c_data['config'],
	                            $c_data['data'],$c_data['tools']) ? 'ok' : mysql_error();
	
	// display updated component
	showCategory($my_categoryID, $sqlResult);
}
elseif($action == 'moveCat')
{
	$compID = 0;
	$prodID = 0;
	$comps = null;

	//20050821 - scs - fix for Mantis 37, unable to copy a category into the same component it is in
	getCategoryComponentAndProduct($my_categoryID,$compID,$prodID);
	$compID = 0;
	getAllProductComponentsBut($compID,$prodID,$comps);

	$smarty->assign('old_containerID', $compID); // original container
	$smarty->assign('arraySelect', $comps);
	$smarty->assign('objectID', $my_categoryID);
}
else if($action == 'reorderTC') 
{
	//user has chosen to reorder the test cases of this category
	$tcs = null;
	getOrderedCategoryTestcases($my_categoryID,$tcs);

	$smarty->assign('arrTC', $tcs);
	$smarty->assign('data', $my_categoryID);
	
} //Update db according to a category's reordered test cases
else if($action == 'updateTCorder') 
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
	$smarty->assign('data', getCategory($my_categoryID));
}
else if($action == 'categoryCopy' || $action == 'categoryMove')
{
	copy_or_move_cat( $action, $objectID, $_POST);
}
else if( $action == 'componentCopy' || $action == 'componentMove')
{
	$prodID=$_SESSION['productID'];
	copy_or_move_comp( $action, $objectID, $prodID ,$_POST);
}	
else 
{
	trigger_error("containerEdit.php - No correct GET/POST data", E_USER_ERROR);
} // end of main if


if ($the_tpl)
{
	$smarty->display($the_tpl);
} 
?>



<?php
function get_comp_values_from_post($akeys2get)
{
  $amy_post = $akeys2get;
	$amy_post[] = 'name';
	$c_data=array();
  foreach ($amy_post as $key)
  {
  	$c_data[$key] = isset($_POST[$key]) ? strings_stripSlashes($_POST[$key]) : null;
  }
  return ($c_data);
}	
?>

