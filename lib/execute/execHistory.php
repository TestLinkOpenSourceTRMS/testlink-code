<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource	execHistory.php
 *
**/
require_once('../../config.inc.php');
require_once('common.php');
require_once('exec.inc.php');
require_once("attachments.inc.php");
require_once("web_editor.php");

testlinkInitPage($db);
$templateCfg = templateConfiguration();

$tcase_mgr = new testcase($db);
$args = init_args();
$gui = new stdClass();
$gui->exec_cfg = config_get('exec_cfg');


$node['basic'] = $tcase_mgr->tree_manager->get_node_hierarchy_info($args->tcase_id); 
$node['specific'] = $tcase_mgr->getExternalID($args->tcase_id); 
$idCard = $node['specific'][0] . ' : ' . $node['basic']['name'];


// IMPORTANT NOTICE:
// getExecutionSet() consider only executions written to DB.
$gui->execSet = $tcase_mgr->getExecutionSet($args->tcase_id);
$gui->warning_msg = (!is_null($gui->execSet)) ? '' : lang_get('tcase_never_executed');
$gui->user_is_admin = ($_SESSION['currentUser']->globalRole->name=='admin') ? true : false;
$gui->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;

$gui->execPlatformSet = null;
$gui->cfexec = null;
$gui->attachments = null;

if(!is_null($gui->execSet) )
{
	$gui->execPlatformSet = $tcase_mgr->getExecutedPlatforms($args->tcase_id);

	// get issue tracker config and object to manage TestLink - BTS integration 
	$its = null;
	$tproject_mgr = new testproject($db);
	$info = $tproject_mgr->get_by_id($gui->tproject_id);
	if($info['issue_tracker_enabled'])
	{
		$gui->bugs = getIssues($db,$gui->execSet,$gui->tproject_id);
	}	




	
	// get custom fields brute force => do not check if this call is needed
	$gui->cfexec = getCustomFields($tcase_mgr,$gui->execSet);
	$gui->attachments = getAttachments($db,$gui->execSet);
	
}

$gui->displayPlatformCol = !is_null($gui->execPlatformSet) ? 1 : 0;
$gui->main_descr = lang_get('execution_history');
$gui->detailed_descr = lang_get('test_case') . ' ' . $idCard;
$smarty = new TLSmarty();
$smarty->assign('gui',$gui);  
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);



function init_args()
{
	$args = new stdClass();
	$_REQUEST = strings_stripSlashes($_REQUEST);
	$iParams = array("tcase_id" => array(tlInputParameter::INT_N));
	$pParams = R_PARAMS($iParams);

	$args = new stdClass();
	$args->tcase_id = intval($pParams["tcase_id"]);
	
	return $args;
}


function getIssues(&$dbHandler,&$execSet,$tprojectID)
{

	$it_mgr = new tlIssueTracker($dbHandler);
	$its = $it_mgr->getInterfaceObject($tprojectID);
	unset($it_mgr);
	
	// we will see in future if we can use a better algorithm
	$issues = array();
	$tcv2loop = array_keys($execSet);
	foreach($tcv2loop as $tcvid)
	{
		$execQty = count($execSet[$tcvid]);
		for($idx=0; $idx < $execQty; $idx++)
		{
			$exec_id = $execSet[$tcvid][$idx]['execution_id'];
			$dummy = get_bugs_for_exec($dbHandler,$its,$exec_id);
			if(count($dummy) > 0)
			{
				$issues[$exec_id] = $dummy;
			}	
		} 
	}
	return $issues;
}


function getCustomFields(&$tcaseMgr,&$execSet)
{
	$cf = array();
	$tcv2loop = array_keys($execSet);
	foreach($tcv2loop as $tcvid)
	{
		$execQty = count($execSet[$tcvid]);
		for($idx=0; $idx < $execQty; $idx++)
		{
			$exec_id = $execSet[$tcvid][$idx]['execution_id'];
			$tplan_id = $execSet[$tcvid][$idx]['testplan_id'];
			$dummy = $tcaseMgr->html_table_of_custom_field_values($tcvid,'execution',null,$exec_id,$tplan_id);
			$cf[$exec_id] = (count($dummy) > 0) ? $dummy : '';
		} 
	}
	return $cf;
}

function getAttachments(&$dbHandler,&$execSet)
{
	$attachmentMgr = tlAttachmentRepository::create($dbHandler);

	$att = null;
	$tcv2loop = array_keys($execSet);
	foreach($tcv2loop as $tcvid)
	{
		$execQty = count($execSet[$tcvid]);
		for($idx=0; $idx < $execQty; $idx++)
		{
			$exec_id = $execSet[$tcvid][$idx]['execution_id'];
			$items = getAttachmentInfos($attachmentMgr,$exec_id,'executions',true,1);
			if($items)
			{
				$att[$exec_id] = $items;
			}
		} 
	}
	return $att;
}

?>