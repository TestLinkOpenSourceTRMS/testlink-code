<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: keywordsNew.php,v $
 *
 * @version $Revision: 1.3 $
 * @modified $Date: 2005/09/07 20:19:25 $
 *
 * @author Martin Havlat
 * 
 * Page create new keywords for the actual product.
 *
 * @author: francisco mancardi - 20050810 - deprecated $_SESSION['product'] removed
 * 20050907 - scs - moved POST parms to the top
 *
**/
require_once("../../config.inc.php");
require_once("../functions/common.php");
require_once('keywords.inc.php');
testlinkInitPage();

$_POST = strings_stripSlashes($_POST);
$keyword = isset($_POST['keyword']) ? $_POST['keyword'] : null;
$bNewKey = isset($_POST['newKey']) ? 1 : 0;
$notes = isset($_POST['notes']) ? $_POST['notes'] : null;
$prodID = isset($_SESSION['productID']) ? $_SESSION['productID'] : 0;

$sqlResult = null;
if($bNewKey)
{
	if (strlen($keyword))
	{
		//we shouldnt allow " and , in keywords any longer
		if (!preg_match("/(\"|,)/",$keyword,$m))
		{
			$sqlResult = addNewKeyword($prodID,$keyword,$notes);
		}
		else
		{
			$sqlResult = lang_get('keywords_char_not_allowed'); 
		}
	}
	else
		$sqlResult = lang_get('empty_keyword_no');
}

$smarty = new TLSmarty();
$smarty->assign('sqlResult', $sqlResult);
$smarty->assign('name',$keyword);
$smarty->display('keywordsNew.tpl');
?>
