<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: keywordsView.php,v $
 *
 * @version $Revision: 1.25 $
 * @modified $Date: 2008/12/13 23:47:01 $ by $Author: schlundus $
 *
 * allows users to manage keywords. 
 */
require_once("../../config.inc.php");
require_once("common.php");
require_once("keyword.class.php");
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();
$args = init_args();

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

function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'mgt_view_key');
}
?>