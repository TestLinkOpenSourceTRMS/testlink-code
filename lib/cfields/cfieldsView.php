<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource  cfieldsView.php
 *
**/
require_once(dirname(__FILE__) . "/../../config.inc.php");
require_once("common.php");

testlinkInitPage($db,false,false,"checkRights");
$gui = new stdClass();
$templateCfg = templateConfiguration();

$cfield_mgr = new cfield_mgr($db);
$gui->cf_map = $cfield_mgr->get_all(null,'transform');
$gui->cf_types = $cfield_mgr->get_available_types();

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);

function checkRights(&$db,&$user)
{
  return $user->hasRight($db,"cfield_view");
}