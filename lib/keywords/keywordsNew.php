<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 *
 * Filename $RCSfile: keywordsNew.php,v $
 *
 * @version $Revision: 1.2 $
 * @modified $Date: 2005/08/16 18:00:55 $
 *
 * @author Martin Havlat
 * 
 * Page create new keywords for the actual product.
 *
 * @author: francisco mancardi - 20050810 - deprecated $_SESSION['product'] removed
 *
**/
require_once("../../config.inc.php");
require_once("../functions/common.php");
require_once('keywords.inc.php');
require_once("../../lib/functions/lang_api.php");
testlinkInitPage();

$sqlResult = null;
$keyword = isset($_POST['keyword']) ? strings_stripSlashes($_POST['keyword']) : null;
if(isset($_POST['newKey']))
{
	if (strlen($keyword))
	{
		//scs: we shouldnt allow " and , in keywords any longer
		if (!preg_match("/(\"|,)/",$keyword,$m))
		{
			$notes = isset($_POST['notes']) ? strings_stripSlashes($_POST['notes']) : null;
			$sqlResult = addNewKeyword($_SESSION['productID'],$keyword,$notes);
		}
		else
			$sqlResult = lang_get('keywords_char_not_allowed'); 
	}
	else
		$sqlResult = lang_get('empty_keyword_no');
}

$smarty = new TLSmarty();
$smarty->assign('sqlResult', $sqlResult);
$smarty->assign('name',$keyword);
$smarty->display('keywordsNew.tpl');
?>
