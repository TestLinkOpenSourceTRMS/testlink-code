<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: attachmentdelete.php,v $
 *
 * @version $Revision: 1.2 $
 * @modified $Date: 2006/06/30 18:41:25 $ by $Author: schlundus $
 *
 * Deletes an attachment
**/
require_once('../../config.inc.php');
require_once('../functions/common.php');
require_once('../functions/attachments.inc.php');
testlinkInitPage($db);

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