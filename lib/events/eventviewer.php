<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: eventviewer.php,v $
 *
 * @version $Revision: 1.30 $
 * @modified $Date: 2009/11/30 21:19:03 $ by $Author: havlat $
 *
 * rev: 20091005 - amitkhullar - improved function getEventsFor() - BUG 2862
 *      20081029 - franciscom - added 'clear' action to delete all events and transactions
 *                              present on database.
**/
require_once("../../config.inc.php");
require_once("common.php");
require_once("users.inc.php");
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();
$args = init_args();
$gui = initializeGui($db,$args);
$filters = getFilters();

switch($args->doAction)
{
    case 'clear':
	    $g_tlLogger->deleteEventsFor();
	    logAuditEvent(TLS("audit_events_deleted",$args->currentUser->login),"DELETE",null,"events");
	    break;
    
    case 'filter':
	default:
	    $filters = getFilters($args);
    	break;
}

$gui->events = $g_tlLogger->getEventsFor($args->logLevel,$args->object_id ? $args->object_id : null,
									$args->object_type ? $args->object_type : null,null,500,$filters->startTime,
									$filters->endTime,$filters->users);


$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
// $smarty->assign('events',$events);
// $smarty->assign('users',$users);
// $smarty->assign('logLevels',$logLevels);
// $smarty->assign('object_id',$args->object_id);
// $smarty->assign('object_type',$args->object_type);
// $smarty->assign('selectedLogLevels',$args->logLevel ? array_values($args->logLevel) : array());
//$smarty->assign('startDate',$args->startDate);
//$smarty->assign('endDate',$args->endDate);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);

/**
 * 
 * @return object returns the arguments of the page
 */
function init_args()
{
	$iParams = array("startDate" => array("POST",tlInputParameter::STRING_N,0,10),
					 "endDate" => array("POST",tlInputParameter::STRING_N,0,10),
					 "doAction" => array("POST",tlInputParameter::STRING_N,0,100),
					 "object_id" => array("REQUEST",tlInputParameter::INT_N),
					 "object_type" => array("REQUEST",tlInputParameter::STRING_N,0,15),
					 "logLevel" => array("POST",tlInputParameter::ARRAY_INT),
					 "testers" => array("REQUEST",tlInputParameter::ARRAY_INT));

	$args = new stdClass();
	I_PARAMS($iParams,$args);
	$args->currentUser = $_SESSION['currentUser'];
	return $args;
}

/**
 * Checks the user rights for viewing the page
 * 
 * @param $db resource the database connection handle
 * @param $user tlUser the object of the current user
 *
 * @return boolean return true if the page can be viewed, false if not
 */
function checkRights(&$db,&$user,$action)
{
	$checkStatus = $user->hasRight($db,"mgt_view_events");
	if( !$checkStatus )
	{
		$iParams = array("doAction" => array(tlInputParameter::STRING_N,0,100));
		$rParams = R_PARAMS($iParams);
		if ($rParams["doAction"] == 'clear')
		{
			$checkStatus = $user->hasRight($db,'events_mgt');
		}
	}
	return $checkStatus;
}


/**
 * 
 *
 */
function initializeGui(&$dbHandler,&$argsObj)
{
	$gui = new stdClass();
	$gui->logLevels = array(tlLogger::AUDIT => lang_get("log_level_AUDIT"),
	  			            tlLogger::ERROR => lang_get("log_level_ERROR"),
				            tlLogger::WARNING => lang_get("log_level_WARNING"),
				            tlLogger::INFO => lang_get("log_level_INFO"),
				            tlLogger::DEBUG => lang_get("log_level_DEBUG"));
	
	$gui->allusers = tlUser::getAll($dbHandler);   // THIS IS AN OVERKILL because get ALL USER OBJECTS
	$gui->testers = getUsersForHtmlOptions($dbHandler,null,null,true,$gui->allusers);
	$gui->users = getUsersForHtmlOptions($dbHandler);
    $gui->users[0] = false;

    $gui->startDate=$argsObj->startDate;
    $gui->endDate=$argsObj->endDate;
	$gui->object_id=$argsObj->object_id;
    $gui->object_type=$argsObj->object_type;

    $gui->selectedLogLevels = ($argsObj->logLevel ? array_values($argsObj->logLevel) : array());
    $gui->selectedTesters = ($argsObj->testers ? array_values($argsObj->testers) : array());

    // $gui->canDelete = has_rights($db,"events_mgt") ? 1 : 0;
    $gui->canDelete = $argsObj->currentUser->hasRight($dbHandler,"events_mgt");
	return $gui;
}


/**
 * 
 *
 */
function getFilters(&$argsObj=null)
{
	$filters = new stdClass();
	$filters->startTime = null;
    $filters->endTime = null;
    $filters->users = null;

    if( !is_null($argsObj) )
    {
		if ($argsObj->startDate != "")
		{
			$filters->startTime = strToTime($argsObj->startDate);
			if (!$filters->startTime)
			{
				$filters->startTime = null;
			}
		}
		
		if ($argsObj->endDate != "")
		{
			$filters->endTime = strToTime($argsObj->endDate) + (24*60*60-1);
			if (!$filters->endTime)
			{
				$filters->endTime = null;
			}
		}
			
		if (!is_null($argsObj->testers))
		{
		    $filters->users = implode(",",$argsObj->testers);
		    if (!$filters->users)
		    {
				$filters->users = null;
			}	
		}
	}	
	return $filters;
}
?>