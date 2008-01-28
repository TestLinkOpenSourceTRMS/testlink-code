<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: attachmentdelete.php,v $
 *
 * @version $Revision: 1.6 $
 * @modified $Date: 2008/01/28 21:17:30 $ by $Author: schlundus $
 *
 * Deletes an attachment by a given id
 * Code check: 2007/11/16 schlundus
**/
require_once('../../config.inc.php');
require_once('../functions/common.php');
require_once('../functions/attachments.inc.php');
testlinkInitPage($db);

//the id (attachments.id) of the attachment to be deleted 
$id = isset($_GET['id'])? intval($_GET['id']) : 0;
$bDeleted = false;
if ($id)
{
	$attachmentRepository = tlAttachmentRepository::create($db);
	$attachmentInfo = $attachmentRepository->getAttachmentInfo($id);
	if ($attachmentInfo && checkAttachmentID($db,$id,$attachmentInfo))
	{
		$bDeleted = $attachmentRepository->deleteAttachment($id,$attachmentInfo);
		if ($bDeleted)
			logAuditEvent(TLS("audit_attachment_deleted",$attachmentInfo['title']),"DELETE",$id,"attachments");
	}
}

$smarty = new TLSmarty();
$smarty->assign('bDeleted',$bDeleted);
$smarty->display('attachmentdelete.tpl');
?>
