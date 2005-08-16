<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: adminProductEdit.php,v $
 *
 * @version $Revision: 1.2 $
 * @modified $Date: 2005/08/16 18:00:53 $
 *
 * @author Martin Havlat
 *
 * This page allows users to edit/delete products.
 * 
 * @todo Verify dependency before delete project 
 * @todo Enable inactivate a product instead of delete
 *
**/
include('../../config.inc.php');
require_once('common.php');
require_once('product.inc.php');

testlinkInitPage(true);

$updateResult = null;
$action = 'no';
$error = null;
$smarty = new TLSmarty;

if (isset($_SESSION['productID']))
	tLog('Edit product: ' . $_SESSION['productID'] . ': ' . $_SESSION['productName']);

if (isset($_GET['deleteProduct']))
{
	$name = isset($_GET['name']) ? strings_stripSlashes($_GET['name']) : null;
	$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
		
	if (deleteProduct($id, &$error))
	{
		$updateResult = lang_get('info_product_was_deleted');
		tLog('Product: [' . $id . '] ' . $name . ' was deleted.', 'INFO');
	} 
	else 
	{
		$updateResult = lang_get('info_product_not_deleted_check_log') . ' ' . $error;
		tLog('Product: [' . $id . '] ' . $name . " wasn't deleted.\t", 'ERROR');
	}
	$action = 'delete';
}
else
{
	$name = isset($_POST['name']) ? strings_stripSlashes($_POST['name']) : null;
	$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

	if (isset($_POST['editProduct']))
	{
		$action = 'updated';

		if (strlen($name) && $id)
		{
			$color = isset($_POST['color']) ? strings_stripSlashes($_POST['color']) : null;
			$optReq = isset($_POST['optReq']) ? intval($_POST['optReq']) : 0;
		
			$updateResult = updateProduct($id, $name, $color, $optReq);
		}
		else {
			$updateResult = lang_get('info_product_name_empty');
		}
	}
	else if (isset($_POST['inactivateProduct']))
	{
		if (activateProduct($id, 0))
		{
			$updateResult = lang_get('info_product_inactivated');
			tLog('Product: ' . $id . ': ' . $name . 'was inactivated.', 'INFO');
		}

		$action = 'inactivate';
	}
	else if (isset($_POST['activateProduct']))
	{
		if (activateProduct($id, 1))
		{
			$updateResult = lang_get('info_product_activated');
			tLog('Product: ' . $id . ': ' . $name . 'was activated.', 'INFO');
		}
		$action = 'activate';
	}
	if (isset($_SESSION['productID']))
	{
		$productData = getProduct($_SESSION['productID']);
	
		if ($productData)
		{
			$smarty->assign('founded', 'yes');
			$smarty->assign('id', $productData['id']);
			$name = $productData['name'];
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

$smarty->assign('action', $action);
$smarty->assign('sqlResult', $updateResult);
$smarty->assign('name', $name);
$smarty->assign('productName', isset($_SESSION['productName']) ? $_SESSION['productName'] : '');
$smarty->display('adminProductEdit.tpl');
?>
