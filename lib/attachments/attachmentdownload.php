<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: attachmentdownload.php,v $
 *
 * @version $Revision: 1.3 $
 * @modified $Date: 2006/06/30 18:41:25 $ by $Author: schlundus $
 *
 * Download dialog
**/
ob_end_clean();
require_once('../../config.inc.php');
require_once('../functions/common.php');
require_once('../functions/attachments.inc.php');
testlinkInitPage($db);

$id = isset($_GET['id'])? $_GET['id'] : 0;

$attachmentInfo = getAttachmentInfo($db,$id);
if ($attachmentInfo && checkAttachmentID($db,$id,$attachmentInfo))
{
	if (is_null($attachmentInfo['file_path']))
		$content = getAttachmentContentFromDB($db,$id);
	else
		$content = getAttachmentContentFromFS($db,$id);
	if (strlen($content))
	{
		ob_end_clean();
		header('Pragma: no-cache');
		header('Content-Type: '.$attachmentInfo['file_type']);
		header('Content-Length: '.$attachmentInfo['file_size']);
		header("Content-Disposition: attachment; filename=\"{$attachmentInfo['file_name']}\"");
		header("Content-Description: Download Data");
		echo $content;
		exit();
	}
}
$smarty = new TLSmarty();
$smarty->display('attachment404.tpl');	
?>