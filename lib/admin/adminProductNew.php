<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: adminProductNew.php,v $
 *
 * @version $Revision: 1.2 $
 * @modified $Date: 2005/08/16 18:00:53 $
 *
 * @author Martin Havlat
 *
 * This page create New products.
 * 
**/
require_once('../../config.inc.php');
require_once('../functions/common.php');
require_once('../functions/product.inc.php');
testlinkInitPage();

$sqlResult = null;
$name = null;
$createResult = null;
if (isset($_POST['newProduct']))
{
	$name = isset($_POST['name']) ? strings_stripSlashes($_POST['name']) : null;
	$color = isset($_POST['color']) ? strings_stripSlashes($_POST['color']) : TL_BACKGROUND_DEFAULT;
	$optReq = isset($_POST['optReq']) ? intval($_POST['optReq']) : 0;
	
	if (strlen($name))
	{
		if (createProduct($name,$color,$optReq))
			$createResult = 'ok';
		else
			$createResult = lang_get('refer_to_log');
	}
	else
		$createResult = lang_get('info_product_name_empty');
}

$smarty = new TLSmarty;
$smarty->assign('sqlResult', $createResult);
$smarty->assign('name', $name);
$smarty->assign('defaultColor', TL_BACKGROUND_DEFAULT);
$smarty->display('adminProductNew.tpl');
?>
