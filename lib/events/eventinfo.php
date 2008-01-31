<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: eventinfo.php,v $
 *
 * @version $Revision: 1.3 $
 * @modified $Date: 2008/01/31 22:15:47 $ by $Author: schlundus $
**/
require_once("../../config.inc.php");
require_once("common.php");
testlinkInitPage($db);

$template_dir = 'events/';
$default_template = str_replace('.php','.tpl',basename($_SERVER['SCRIPT_NAME']));

$eventID = isset($_POST['id']) ? $_POST['id'] : null;
$user = null;
if ($eventID)
{
	$event = new tlEvent($eventID);
	if ($event->readFromDB($db,tlEvent::TLOBJ_O_GET_DETAIL_TRANSACTION) >= tl::OK)
	{
		$user = new tlUser($event->userID);
		if ($user->readFromDB($db) < tl::OK)
			$user = null;
	}
	else
		$event = null;
}

$smarty = new TLSmarty();
$smarty->assign("event",$event);
$smarty->assign("user",$user);
$smarty->display($template_dir . $default_template);
?>