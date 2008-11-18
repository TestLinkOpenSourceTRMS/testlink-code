<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: keywordsView.php,v $
 *
 * @version $Revision: 1.23 $
 * @modified $Date: 2008/11/18 20:54:42 $ by $Author: schlundus $
 *
 * allows users to manage keywords. 
 */
require_once("../../config.inc.php");
require_once("common.php");
require_once("keyword.class.php");
testlinkInitPage($db);

$templateCfg = templateConfiguration();
$args = init_args();

$tlKeyword = new tlKeyword();
$exportTypes = $tlKeyword->getSupportedSerializationInterfaces();

$tproject = new testproject($db);
$keywords = $tproject->getKeywords($args->testproject_id);

$smarty = new TLSmarty();
$smarty->assign('action',null);
$smarty->assign('sqlResult',null);
$smarty->assign('keywords', $keywords);
$smarty->assign('canManage',has_rights($db,"mgt_modify_key"));
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);

/*
  function: init_args

  args:
  
  returns: 

*/
function init_args()
{
	$args = new stdClass();
	$args->testproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;

	return $args;
}
?>