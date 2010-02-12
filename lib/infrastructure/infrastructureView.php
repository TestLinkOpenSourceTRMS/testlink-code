<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * View project infrastructure 
 * 
 * @package 	TestLink
 * @author 		Martin Havlat
 * @copyright 	2009, TestLink community 
 * @version    	CVS: $Id: infrastructureView.php,v 1.1 2010/02/12 00:20:12 havlat Exp $
 *
 *	@todo redirect if no right
 *
 * @internal Revisions:
 * None
 *
 **/

require_once('../../config.inc.php');
require_once("common.php");
require_once("tlInfrastructure.class.php");
testlinkInitPage($db);

$templateCfg = templateConfiguration();
	$args = new stdClass();
    $args->testprojectId = $_SESSION['testprojectID'];
    $args->userId = $_SESSION['userID'];

$gui = new stdClass();
$gui->rightEdit = has_rights($db,"project_infrastructure_edit");
$gui->rightView = has_rights($db,"project_infrastructure_view");
//   $args->tproject_name = isset($_SESSION['testprojectName']) ? trim($_SESSION['testprojectName']) : '' ;


if($args->testprojectId)
{
//	$tlIs = new tlInfrastructure($args->testprojectId, $db);

	//new dBug($args);
	if ($gui->rightEdit)
	{
//		$tlIs->createInfrastructure($args);
//		$tlUser = new tlUser($_SESSION['userID']);
//		$args->dip = $tlUser->getNamesForProjectRight($db,'project_infrastructure_edit',$args->testprojectId);
	}

//	$gui->infrastructureList = $tlIs->getAll();
}

$smarty = new TLSmarty();
//$smarty->assign('aadebug',$args/*$gui->infrastructureList*/);
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);

?>