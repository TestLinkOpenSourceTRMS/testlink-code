<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: attachmentdelete.php,v $
 *
 * @version $Revision: 1.5 $
 * @modified $Date: 2007/12/03 20:42:26 $ by $Author: schlundus $
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
		$bDeleted = $attachmentRepository->deleteAttachment($id,$attachmentInfo);
}

$smarty = new TLSmarty();
$smarty->assign('bDeleted',$bDeleted);
$smarty->display('attachmentdelete.tpl');
?>
