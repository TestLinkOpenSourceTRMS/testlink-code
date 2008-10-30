<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: eventviewer.php,v $
 *
 * @version $Revision: 1.16 $
 * @modified $Date: 2008/10/30 20:00:37 $ by $Author: franciscom $
 *
 * rev: 20081029 - franciscom - added 'clear' action to delete all events and transactions
 *                              present on database.
 *      20080207 - franciscom - refactored
**/
require_once("../../config.inc.php");
require_once("common.php");
require_once("users.inc.php");

testlinkInitPage($db);

$templateCfg = templateConfiguration();

$logLevels = array(
			tlLogger::AUDIT => lang_get("log_level_AUDIT"),
			tlLogger::ERROR => lang_get("log_level_ERROR"),
			tlLogger::WARNING => lang_get("log_level_WARNING"),
			tlLogger::INFO => lang_get("log_level_INFO"),
			tlLogger::DEBUG => lang_get("log_level_DEBUG"),
			);

$args = init_args();
$startTime = null;
$endTime = null;

switch($args->doAction)
{
  
    case 'clear':
    $g_tlLogger->deleteEventsFor();
    break;
    
    case 'filter':
    default:
    if (strlen($args->startDate))
    {
    	$startTime = strToTime($args->startDate);
    	if (!$startTime)
    		$startTime = null;
    }
    if (strlen($args->endDate))
    {
    	$endTime = strToTime($args->endDate) + (24*60*60-1);
    	if (!$endTime)
    		$endTime = null;
    }
    break;
}

$events = $g_tlLogger->getEventsFor($args->logLevel,$args->object_id ? $args->object_id : null,
									                  $args->object_type ? $args->object_type : null,null,500,$startTime,$endTime);
$users = getUsersForHtmlOptions($db,null,false,null);
$users[0] = false;

$gui = new stdClass();
$gui->canDelete=has_rights($db,"events_mgt") ? 1: 0;

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->assign('events',$events);
$smarty->assign('users',$users);
$smarty->assign('logLevels',$logLevels);
$smarty->assign('object_id',$args->object_id);
$smarty->assign('object_type',$args->object_type);
$smarty->assign('selectedLogLevels',$args->logLevel ? array_values($args->logLevel) : array());
$smarty->assign('startDate',$args->startDate);
$smarty->assign('endDate',$args->endDate);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);

function init_args()
{
	  $args = new stdClass();
    $_REQUEST = strings_stripSlashes($_REQUEST);
    $nullable_keys = array('logLevel','startDate','endDate','object_id',"object_type",'doAction');
	  foreach($nullable_keys as $value)
	  {
	  	$args->$value = isset($_REQUEST[$value]) ? $_REQUEST[$value] : null;
	  }
    return $args;
}
?>