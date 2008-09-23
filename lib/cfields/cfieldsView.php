<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: cfieldsView.php,v $
 *
 * @version $Revision: 1.5 $
 * @modified $Date: 2008/09/23 06:59:59 $ by $Author: franciscom $
 *
 * rev: 20080921 - franciscom - minor refactoring
 *
**/

require_once(dirname(__FILE__) . "/../../config.inc.php");
require_once("common.php");

testlinkInitPage($db);
$gui=new stdClass();
$templateCfg = templateConfiguration();

$cfield_mgr = new cfield_mgr($db);
$gui->cf_map = $cfield_mgr->get_all();
$gui->cf_types=$cfield_mgr->get_available_types();

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);
?>
