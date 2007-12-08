<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: keywordsView.php,v $
 *
 * @version $Revision: 1.20 $
 * @modified $Date: 2007/12/08 19:10:19 $ by $Author: schlundus $
 *
 * allows users to manage keywords. 
 */
require_once("../../config.inc.php");
require_once("common.php");
require_once("keyword.class.php");
testlinkInitPage($db);

$template_dir = 'keywords/';

$args = init_args();
$canManage = has_rights($db,"mgt_modify_key");
$tproject = new testproject($db);

$keywordID = isset($_REQUEST['id']) ? $_REQUEST['id'] : null;
$keyword = isset($_REQUEST['keyword']) ? $_REQUEST['keyword'] : null;
$notes = isset($_REQUEST['notes']) ? $_REQUEST['notes'] : null;

$tlKeyword = new tlKeyword();
$exportTypes = $tlKeyword->getSupportedSerializationInterfaces();
$keywords = $tproject->getKeywords($args->testproject_id);

$smarty = new TLSmarty();
$smarty->assign('action',null);
$smarty->assign('sqlResult',null);
$smarty->assign('keywords', $keywords);
$smarty->assign('canManage',$canManage);
$smarty->assign('name',$keyword);
$smarty->assign('keyword',$keyword);
$smarty->assign('notes',$notes);
$smarty->assign('keywordID',$keywordID);
$smarty->assign('exportTypes',$exportTypes);

$smarty->display($template_dir . 'keywordsView.tpl');

function init_args()
{
  $args->testproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
  return $args;
}
?>