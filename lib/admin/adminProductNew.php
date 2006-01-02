<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: adminProductNew.php,v $
 *
 * @version $Revision: 1.7 $
 * @modified $Date: 2006/01/02 14:05:17 $
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
require_once("../../third_party/fckeditor/fckeditor.php");

testlinkInitPage();

$_REQUEST = strings_stripSlashes($_REQUEST);
$bNewProduct = isset($_REQUEST['newProduct']) ? 1 : 0;
$name = isset($_REQUEST['name']) ? $_REQUEST['name'] : null;
$color = isset($_REQUEST['color']) ? $_REQUEST['color'] : TL_BACKGROUND_DEFAULT;
$optReq = isset($_REQUEST['optReq']) ? intval($_REQUEST['optReq']) : 0;

// ----------------------------------------------------------------------
// 20060101 - fm
$notes = isset($_REQUEST['notes']) ? $_REQUEST['notes'] : null;
$of = new fckeditor('notes') ;
$of->BasePath = $_SESSION['basehref'] . 'third_party/fckeditor/';
$of->ToolbarSet = 'TL_Medium';
// ----------------------------------------------------------------------

$msg = null;
$createResult = null;
$of->Value = null;
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
		if (!createProduct($name,$color,$optReq,$notes))
		{
			$msg = lang_get('refer_to_log');
		}	
	}
}

$smarty = new TLSmarty();
$smarty->assign('sqlResult', $msg);
$smarty->assign('name', $name);
$smarty->assign('defaultColor', TL_BACKGROUND_DEFAULT);
$smarty->assign('notes', $of->CreateHTML());

$smarty->display('adminProductNew.tpl');
?>
