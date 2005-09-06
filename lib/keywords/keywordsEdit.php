<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: keywordsEdit.php,v $
 *
 * @version $Revision: 1.3 $
 * @modified $Date: 2005/09/06 06:45:02 $
 *
 * @author	Martin Havlat
 * 
 * Multi editing/deleting of keywords.
 *
**/
require_once("../../config.inc.php");
require_once("../functions/common.php");
require_once("keywords.inc.php");
require_once("../../lib/functions/lang_api.php");
testlinkInitPage();

// 20050905 - fm
$prodID = isset($_SESSION['productID']) ? $_SESSION['productID'] : 0;

$updated = null;
$arrUpdate = null;
if (isset($_POST['editKey']))
{
	$arrUpdate = multiUpdateKeywords();
	$updated = 'yes';
}

$smarty = new TLSmarty();
$smarty->assign('updated', $updated);
$smarty->assign('arrUpdate', $arrUpdate);
$smarty->assign('arrKeywords', selectKeywords($prodID));
$smarty->display('keywordsEdit.tpl');
?>
