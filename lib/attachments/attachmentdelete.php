<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource	attachmentdelete.php
 *
 * Deletes an attachment by a given id
 */
require_once('../../config.inc.php');
require_once('../functions/common.php');
require_once('../functions/attachments.inc.php');
testlinkInitPage($db);

$args = init_args();	
checkRights($db,$_SESSION['currentUser'],$args);

$l18n = init_labels(array('deleting_was_ok' => null,'error_attachment_delete' => null));
$gui = new stdClass();
$gui->userFeedback = $l18n['error_attachment_delete'];

if ($args->id)
{
	$repo = tlAttachmentRepository::create($db);
	$attachmentInfo = $repo->getAttachmentInfo($args->id);
	if ($attachmentInfo && checkAttachmentID($db,$args->id,$attachmentInfo))
	{
		$opOK = $repo->deleteAttachment($args->id,$attachmentInfo);
		if ($opOK)
		{
      $gui->userFeedback = $l18n['deleting_was_ok'];
			logAuditEvent(TLS("audit_attachment_deleted",
			              $attachmentInfo['title']),"DELETE",$args->id,"attachments");
		}	
	}
}

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display('attachmentdelete.tpl');


/**
 * @return object returns the arguments for the page
 */
function init_args()
{
	//the id (attachments.id) of the attachment to be deleted
	$iParams = array("id" => array(tlInputParameter::INT_N));
	$args = new stdClass();
	G_PARAMS($iParams,$args);
	
	// take care of proper escaping when magic_quotes_gpc is enabled
	$_REQUEST=strings_stripSlashes($_REQUEST);

	return $args;
}


/**
 */
function checkRights(&$db,&$userObj,$argsObjs)
{
	if(!(config_get("attachments")->enabled))
	{
		redirect($_SESSION['basehref'],"top.location");
		exit();
	}
}
?>