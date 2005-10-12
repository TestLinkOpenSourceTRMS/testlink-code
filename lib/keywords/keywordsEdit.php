<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: keywordsEdit.php,v $
 *
 * @version $Revision: 1.5 $
 * @modified $Date: 2005/10/12 06:25:06 $ $Author: franciscom $
 *
 * @author	Martin Havlat
 * 
 * Multi editing/deleting of keywords.
 *
 * 20050907 - scs - moved POST parms to the top
**/
require_once("../../config.inc.php");
require_once("../functions/common.php");
require_once("keywords.inc.php");
testlinkInitPage();

// 20050905 - fm
$prodID = isset($_SESSION['productID']) ? $_SESSION['productID'] : 0;
$bEditKey = isset($_POST['editKey']) ? 1 : 0;

$updated = null;
$arrUpdate = null;
if ($bEditKey)
{
  // 20051011 - fm 
	$arrUpdate = multiUpdateKeywords($prodID);
	$updated = 'yes';
}

$smarty = new TLSmarty();
$smarty->assign('updated', $updated);
$smarty->assign('arrUpdate', $arrUpdate);
$smarty->assign('arrKeywords', selectKeywords($prodID));
$smarty->display('keywordsEdit.tpl');
?>
