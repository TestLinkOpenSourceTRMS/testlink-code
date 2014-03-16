<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: attachmentdownload.php,v $
 *
 * @version $Revision: 1.17 $
 * @modified $Date: 2009/08/14 20:58:03 $ by $Author: schlundus $
 *
 * Downloads the attachment by a given id
 */
@ob_end_clean();
require_once('../../config.inc.php');
require_once('../functions/common.php');
require_once('../functions/attachments.inc.php');
testlinkInitPage($db,false,false,"checkRights");

$args = init_args();

if ($args->id)
{
  $attachmentRepository = tlAttachmentRepository::create($db);
  $attachmentInfo = $attachmentRepository->getAttachmentInfo($args->id);
  // if ($attachmentInfo)
  if ($attachmentInfo && ($args->skipCheck || checkAttachmentID($db,$args->id,$attachmentInfo)) )
  {
    $content = $attachmentRepository->getAttachmentContent($args->id,$attachmentInfo);
    if ($content != "")
    {
      @ob_end_clean();
      header('Pragma: public');
      header("Cache-Control: ");
      if (!(isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on" && preg_match("/MSIE/",$_SERVER["HTTP_USER_AGENT"])))
      { 
        header('Pragma: no-cache');
      }
      header('Content-Type: '.$attachmentInfo['file_type']);
      header('Content-Length: '.$attachmentInfo['file_size']);
      // header("Content-Disposition: attachment; filename=\"{$attachmentInfo['file_name']}\"");
      header("Content-Disposition: inline; filename=\"{$attachmentInfo['file_name']}\"");
      header("Content-Description: Download Data");
      echo $content;
      exit();
    }
  }
}
$smarty = new TLSmarty();
$smarty->assign('gui',$args);
$smarty->display('attachment404.tpl');

/**
 * @return object returns the arguments for the page
 */
function init_args()
{
  //the id (attachments.id) of the attachment to be downloaded
  $iParams = array("id" => array(tlInputParameter::INT_N),'skipCheck' => array(tlInputParameter::INT_N));
  $args = new stdClass();
  G_PARAMS($iParams,$args);
  return $args;
}

/**
 * @param $db resource the database connection handle
 * @param $user the current active user
 * @return boolean returns true if the page can be accessed
 */
function checkRights(&$db,&$user)
{
  return (config_get("attachments")->enabled);
}