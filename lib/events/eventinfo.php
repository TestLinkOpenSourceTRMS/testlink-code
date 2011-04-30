<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: eventinfo.php,v $
 *
 * @version $Revision: 1.14 $
 * @modified $Date: 2011/01/10 15:38:55 $ by $Author: asimon83 $
**/
require_once("../../config.inc.php");
require_once("common.php");

testlinkInitPage($db);
$templateCfg = templateConfiguration();
$args = init_args();
checkRights($db,$_SESSION['currentUser'],$args);

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

/**
 * 
 * @return object returns the arguments of the page
 */
function init_args()
{
	$iParams = array("id" => array(tlInputParameter::STRING_N,0,50));
	$args = new stdClass();
	P_PARAMS($iParams,$args);

	// BUGID 4066 - take care of proper escaping when magic_quotes_gpc is enabled
	$_REQUEST=strings_stripSlashes($_REQUEST);

	return $args;
}


/**
 * Checks the user rights for using the page
 *
 * checkRights
 *
 */
function checkRights(&$db,&$userObj,$argsObj)
{
	$checkStatus = $userObj->hasRight($db,"mgt_view_events");
	if(!$checkStatus)
	{
	  	redirect($_SESSION['basehref'],"top.location");
		exit();
	}
}

?>