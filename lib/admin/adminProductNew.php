<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: adminProductNew.php,v $
 *
 * @version $Revision: 1.5 $
 * @modified $Date: 2005/09/08 12:25:26 $
 *
 * @author Martin Havlat
 *
 * Create New products.
 *
 * 20050908 - fm - BUGID 0000086
 * 20050829 - scs - moved POST params to the top of the script
 *
**/
require_once('../../config.inc.php');
require_once('../functions/common.php');
require_once('../functions/product.inc.php');
testlinkInitPage();

$_POST = strings_stripSlashes($_POST);
$bNewProduct = isset($_POST['newProduct']) ? 1 : 0;
$name = isset($_POST['name']) ? $_POST['name'] : null;
$color = isset($_POST['color']) ? $_POST['color'] : TL_BACKGROUND_DEFAULT;
$optReq = isset($_POST['optReq']) ? intval($_POST['optReq']) : 0;

$createResult = null;
if ($bNewProduct)
{
	$name_ok = 1;
	if( $name_ok && !strlen($name) )
	{
		$msg = lang_get('info_product_name_empty');
		$name_ok = 0;
	}
	
	// BUGID 0000086
	if( $name_ok && !check_string($name,$g_ereg_forbidden) )
	{
		$msg = lang_get('string_contains_bad_chars');
		$name_ok = 0;
	}
	
	if ($name_ok)
	{
		$msg = 'ok';
		if (!createProduct($name,$color,$optReq))
		{
			$msg = lang_get('refer_to_log');
		}	
	}
}

$smarty = new TLSmarty();
$smarty->assign('sqlResult', $msg);
$smarty->assign('name', $name);
$smarty->assign('defaultColor', TL_BACKGROUND_DEFAULT);
$smarty->display('adminProductNew.tpl');
?>
