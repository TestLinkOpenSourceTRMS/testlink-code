<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: eventinfo.php,v $
 *
 * @version $Revision: 1.9 $
 * @modified $Date: 2009/05/11 20:39:26 $ by $Author: franciscom $
**/
require_once("../../config.inc.php");
require_once("common.php");
testlinkInitPage($db,false,false,"checkRights");
$templateCfg = templateConfiguration();

$args = init_args();

$user = null;
$event = null;

if ($args->id)
{
	$event = new tlEvent($args->id);
	if ($event->readFromDB($db,tlEvent::TLOBJ_O_GET_DETAIL_TRANSACTION) >= tl::OK)
	{
		$user = new tlUser($event->userID);
		if ($user->readFromDB($db) < tl::OK)
		{
			$user = null;
		}	
	}
	else
	{
		$event = null;
	}	
}

$smarty = new TLSmarty();
$smarty->assign("event",$event);
$smarty->assign("user",$user);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);

function checkRights(&$db,&$user,&$action)
{
	return (!$user->hasRight($db,"mgt_view_events")) ? false : true;
}

function init_args()
{
	$iParams = array("id" => array(tlInputParameter::STRING_N,0,50));
	$args = new stdClass();
	$pParams = P_PARAMS($iParams,$args);
	return $args;
}
?>