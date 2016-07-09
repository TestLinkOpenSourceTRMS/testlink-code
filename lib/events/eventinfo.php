<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: eventinfo.php,v $
 *
 * @version $Revision: 1.12 $
 * @modified $Date: 2010/05/18 05:07:52 $ by $Author: amkhullar $
**/
require_once("../../config.inc.php");
require_once("common.php");
testlinkInitPage($db,false,false,"checkRights");
$templateCfg = templateConfiguration();

$user = null;
$event = null;

$args = init_args();
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
  $iParams = array("id" => array(tlInputParameter::INT_N));
  $args = new stdClass();
  P_PARAMS($iParams,$args);

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
function checkRights(&$db,&$user)
{
  return ($user->hasRight($db,"mgt_view_events")) ? true : false;
}