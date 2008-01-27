<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: eventviewer.php,v $
 *
 * @version $Revision: 1.2 $
 * @modified $Date: 2008/01/27 21:13:20 $ by $Author: schlundus $
**/
require_once("../../config.inc.php");
require_once("common.php");
testlinkInitPage($db);

$template_dir = 'events/';
$default_template = str_replace('.php','.tpl',basename($_SERVER['SCRIPT_NAME']));

$errorLevels = array(
			tlLogger::AUDIT => lang_get("error_level_AUDIT"),
			tlLogger::ERROR => lang_get("error_level_ERROR"),
			tlLogger::WARNING => lang_get("error_level_WARNING"),
			tlLogger::INFO => lang_get("error_level_INFO"),
			tlLogger::DEBUG => lang_get("error_level_DEBUG"),
			);

$errorLevel = isset($_POST['errorLevel']) ? $_POST['errorLevel'] : null;
$startDate = isset($_POST['date1']) ? $_POST['date1'] : null;
$endDate = isset($_POST['date2']) ? $_POST['date2'] : null;

$startTime = null;
$endTime = null;
if (strlen($startDate))
{
	$startTime = strToTime($startDate);
	if (!$startTime)
		$startTime = null;
}		
if (strlen($endDate))
{
	$endTime = strToTime($endDate) + (24*60*60-1);
	if (!$endTime)
		$endTime = null;
}
$events = $g_tlLogger->getEventsFor($errorLevel,null,null,null,500,$startTime,$endTime);


$smarty = new TLSmarty();
$smarty->assign('events',$events);
$smarty->assign('errorLevels',$errorLevels);
$smarty->assign('selectedErrorLevels',array_values($errorLevel ? $errorLevel : array()));
$smarty->assign('startDate',$startDate);
$smarty->assign('endDate',$endDate);
$smarty->display($template_dir . $default_template);
?>