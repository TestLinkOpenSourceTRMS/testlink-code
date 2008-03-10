<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: eventviewer.php,v $
 *
 * @version $Revision: 1.9 $
 * @modified $Date: 2008/03/10 21:52:00 $ by $Author: schlundus $
 *
 * rev: 20080207 - franciscom - refactored
**/
require_once("../../config.inc.php");
require_once("common.php");
testlinkInitPage($db);

$template_dir = 'events/';
$default_template = str_replace('.php','.tpl',basename($_SERVER['SCRIPT_NAME']));

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
$events = $g_tlLogger->getEventsFor($args->logLevel,null,null,null,500,$startTime,$endTime);
$users = getUsersForHtmlOptions($db,null,false,null);
$users[0] = false;

$smarty = new TLSmarty();
$smarty->assign('events',$events);
$smarty->assign('users',$users);
$smarty->assign('logLevels',$logLevels);
$smarty->assign('selectedLogLevels',$args->logLevel ? array_values($args->logLevel) : array());
$smarty->assign('startDate',$args->startDate);
$smarty->assign('endDate',$args->endDate);
$smarty->display($template_dir . $default_template);

function init_args()
{
	$args = new stdClass();
    $_REQUEST = strings_stripSlashes($_REQUEST);
    $nullable_keys = array('logLevel','startDate','endDate');
	foreach($nullable_keys as $value)
	{
		$args->$value = isset($_REQUEST[$value]) ? $_REQUEST[$value] : null;
	}
    return $args;
}
?>