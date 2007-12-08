<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: keywordsView.php,v $
 *
 * @version $Revision: 1.19 $
 * @modified $Date: 2007/12/08 15:41:21 $ by $Author: franciscom $
 *
 * allows users to manage keywords. 
 */
require_once("../../config.inc.php");
require_once("common.php");
require_once("keyword.class.php");

$templateDir='keywords/';

testlinkInitPage($db);

$args=init_args();
$canManage = has_rights($db,"mgt_modify_key");
$tproject = new testproject($db);

$msg = null;
$action = null;

$allKeywords = $tproject->getKeywords($args->testproject_id);

$smarty = new TLSmarty();
$smarty->assign('action',$action);
$smarty->assign('sqlResult',$msg);
$smarty->assign('arrKeywords', $allKeywords);
$smarty->assign('canManage', $canManage);

$smarty->display($templateDir . 'keywordsView.tpl');
?>



<?php
function init_args()
{
  $args->testproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
  return $args;
}
?>