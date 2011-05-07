<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Display list of test projects
 *
 * @filesource	projectView.php
 * @package 	TestLink
 * @author 		TestLink community
 * @copyright 	2007-2011, TestLink community 
 * @link 		http://www.teamst.org/index.php
 *
 */

require_once('../../config.inc.php');
require_once("common.php");
testlinkInitPage($db);

$templateCfg = templateConfiguration();
$args = init_args();
checkRights($db,$_SESSION['currentUser'],$args);

$gui = new stdClass();
$gui->canManage = true;
// $_SESSION['currentUser']->hasRight($db,"mgt_modify_product",$args->tproject_id);
$gui->doAction = $args->doAction;
$gui->tproject_id = $args->contextTprojectID;
$gui->contextTprojectID = $args->contextTprojectID;
$gui->reloadType = 'none';

$tproject_mgr = new testproject($db);
$gui->tprojects = $tproject_mgr->get_accessible_for_user($args->userID,'array_of_map');

$template2launch = $templateCfg->default_template;
if(count($gui->tprojects) == 0)
{
    $template2launch = "projectEdit.tpl"; 
    $gui->doAction = "create";
}

// new dBug($gui);
$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $template2launch);


/**
 * 
 *
 */
function init_args()
{
	$_REQUEST = strings_stripSlashes($_REQUEST);
	$args = new stdClass();
	$args->doAction = isset($_REQUEST['doAction']) ? $_REQUEST['doAction'] : 'list' ;
	$args->userID =isset($_SESSION['userID']) ? $_SESSION['userID'] : 0;

	// Important Notice
	// this is value set by user on NavBar.php, that set the context.
	// needed on refresh GUI logic, example: if user deleted this test project we need
	// to reload NavBar to refresh test project COMOBO and set a new context test project id.
	//
	$args->contextTprojectID = isset($_REQUEST['tproject_id']) ? intval($_REQUEST['tproject_id']) : 0 ;
	return $args;  
}


/**
 * checkRights
 *
 */
function checkRights(&$db,&$userObj,$argsObj)
{
	// For this feature check must be done on Global Rights => those that belong to
	// role assigned to user when user was created (Global/Default Role)
	// => enviroment is ignored.
	// To instruct method to ignore enviromente, we need to set enviroment but with INEXISTENT ID 
	// (best option is negative value)
	$env['tproject_id'] = -1;
	$env['tplan_id'] = -1;
	checkSecurityClearance($db,$userObj,$env,array('mgt_modify_product'),'and');
}
?>