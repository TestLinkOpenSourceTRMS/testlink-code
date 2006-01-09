<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: adminProductEdit.php,v $
 *
 * @version $Revision: 1.12 $
 * @modified $Date: 2006/01/09 18:52:46 $
 *
 * @author Martin Havlat
 *
 * This page allows users to edit/delete products.
 * 
 * @todo Verify dependency before delete testplan 
 *
 * 20051211 - fm - poor workaround for the delete loop - BUGID 180 Unable to delete Product
 * 20050908 - fm - BUGID 0000086
 * 20050831 - scs - moved POST to top, some small changes
 * 20060107 - scs - added new product functionality
**/
include('../../config.inc.php');
require_once('common.php');
require_once('product.inc.php');
require_once("../../third_party/fckeditor/fckeditor.php");
testlinkInitPage($db,true);

$sessionProdID = isset($_SESSION['productID']) ? $_SESSION['productID'] : 0;

$updateResult = null;
$action = 'no';
$show_prod_attributes = 'yes';

$tlog_msg = "Product [ID: Name]=";
$tlog_level = 'INFO';

$args = init_args($db,$_REQUEST,$sessionProdID);

// ----------------------------------------------------------------------
// 20060101 - fm
$of = new fckeditor('notes') ;
$of->BasePath = $_SESSION['basehref'] . 'third_party/fckeditor/';
$of->ToolbarSet = 'TL_Medium';
$of->Value = $args->notes;
// ----------------------------------------------------------------------

if ($sessionProdID)
	$tlog_msg .= $sessionProdID . ': ' . $_SESSION['productName'];
else
	$tlog_msg .= $args->id . ': ' . $args->name;

switch($args->do)
{
	case 'deleteProduct':
		$show_prod_attributes = 'no';
		$error = null;
		if (deleteProduct($args->id,$error))
		{
			$updateResult = lang_get('info_product_was_deleted');
			$tlog_msg .= " was deleted.";
		} 
		else 
		{
			$updateResult = lang_get('info_product_not_deleted_check_log') . ' ' . $error;
			$tlog_msg .=  " wasn't deleted.\t";
			$tlog_level = 'ERROR';
		}
		$action = 'delete';
		break;
	case 'createProduct':
		$args->id = -1;
		break;	 
		
	case 'editProduct':
		$name_ok = 1;
		if (!strlen($args->name))
		{
			$updateResult = lang_get('info_product_name_empty');
			$name_ok = 0;
		}
		// BUGID 0000086
		if ($name_ok && !check_string($args->name,$g_ereg_forbidden))
		{
			$updateResult = lang_get('string_contains_bad_chars');
			$name_ok = 0;
		}
		if ($name_ok && $args->id)
		{
			if ($args->id == -1)
			{
				$updateResult = 'ok';
				$args->id = createProduct($db,$args->name, $args->color, $args->optReq, $args->notes);
				if (!$args->id)
					$updateResult = lang_get('refer_to_log');
				else
					$args->id = -1;
			}
			else
				$updateResult = updateProduct($args->id, $args->name, $args->color,$args->optReq, $args->notes);
		}
		$action = 'updated';
		break;
	case 'inactivateProduct':
		if (activateProduct($args->id, 0))
		{
			$updateResult = lang_get('info_product_inactivated');
			$tlog_msg .= 'was inactivated.';
		}
		$action = 'inactivate';
	break;

	case 'activateProduct':
		if (activateProduct($args->id, 1))
		{
			$updateResult = lang_get('info_product_activated');
			$tlog_msg .= 'was activated.';
		}
		$action = 'activate';
	break;
  
}

$smarty = new TLSmarty();
// Common Processing
if ($args->do != 'deleteProduct')
{
	if ($args->id != -1)
	{
		if ($sessionProdID)
		{
			$productData = getProduct($db,$sessionProdID);
			if ($productData)
			{
				$args->name = $productData['name'];
				$smarty->assign('found', 'yes');
				$smarty->assign('id', $productData['id']);
				$smarty->assign('color', $productData['color']);
				$smarty->assign('active', $productData['active']);
				$smarty->assign('reqs_default', $productData['option_reqs']);
			}
			else
				$updateResult = lang_get('info_failed_loc_prod');
		}
		else
			$updateResult = lang_get('info_no_more_prods');
	}
	else
	{
		$smarty->assign('found', 'yes');
		$smarty->assign('id', -1);
		$args->name = '';
		$args->notes = '';
		$args->color = '';
	}
}

if($action != 'no')
	tLog($tlog_msg, $tlog_level);

$smarty->assign('action', $action);
$smarty->assign('sqlResult', $updateResult);
$smarty->assign('name', $args->name);
$smarty->assign('show_prod_attributes', $show_prod_attributes);
$smarty->assign('notes', $of->CreateHTML());
$smarty->display('adminProductEdit.tpl');

/*
 * INITialize page ARGuments, using the $_REQUEST and $_SESSION
 * super-global hashes.
 * Important: changes in HTML input elements on the Smarty template
 *            must be reflected here.
 *
 *  
 * @parameter hash request_hash the $_REQUEST
 * @parameter hash session_hash the $_SESSION
 * @return    object with html values tranformed and other
 *                   generated variables.
 *
 * 20060102 - fm 
*/
function init_args(&$db,$request_hash, $sessionProdID)
{
	$request_hash = strings_stripSlashes($request_hash);
	
	$do_keys = array('deleteProduct','editProduct','inactivateProduct','activateProduct','createProduct');
	$args->do = '';
	foreach ($do_keys as $value)
	{
		$args->do = isset($request_hash[$value]) ? $value : $args->do;
	}
	
	$nullable_keys = array('name','color','notes');
	foreach ($nullable_keys as $value)
	{
		$args->$value = isset($request_hash[$value]) ? $request_hash[$value] : null;
	}
	
	$intval_keys = array('optReq' => 0, 'id' => null);
	foreach ($intval_keys as $key => $value)
	{
		$args->$key = isset($request_hash[$key]) ? intval($request_hash[$key]) : $value;
	}
	
	// Special algorithm for notes
	$the_prodid = 0;
	if ($args->do == 'createProduct')
		$args->id = -1;
	else if ($args->id == -1)
		$the_prodid = -1;
	else if ($sessionProdID)
		$the_prodid = $sessionProdID;
	else if(!is_null($args->id))
		$the_prodid = $args->id;

	if ($the_prodid > 0)
	{
		$productData = getProduct($db,$the_prodid);
		$args->notes = 	$productData['notes'];
	}
	else 
		$args->notes = '';
	return $args;
}
?>
