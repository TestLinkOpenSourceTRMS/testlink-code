<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: attachmentdelete.php,v $
 *
 * @version $Revision: 1.3 $
 * @modified $Date: 2007/10/16 19:42:37 $ by $Author: schlundus $
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
	$attachmentInfo = getAttachmentInfo($db,$id);
	if (checkAttachmentID($db,$id,$attachmentInfo))
		$bDeleted = deleteAttachment($db,$id,$attachmentInfo);
}

$smarty = new TLSmarty();
$smarty->assign('bDeleted',$bDeleted);
$smarty->display('attachmentdelete.tpl');
?>