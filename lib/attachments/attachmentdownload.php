<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: attachmentdownload.php,v $
 *
 * @version $Revision: 1.12 $
 * @modified $Date: 2009/01/13 20:21:23 $ by $Author: schlundus $
 *
 * Downloads the attachment by a given id
 */
@ob_end_clean();
require_once('../../config.inc.php');
require_once('../functions/common.php');
require_once('../functions/attachments.inc.php');
testlinkInitPage($db);

if (!config_get("attachments")->enabled)
	exit();

//the id (attachments.id) of the attachment to be downloaded
$id = isset($_GET['id'])? intval($_GET['id']) : 0;
if ($id)
{
	$attachmentRepository = tlAttachmentRepository::create($db);
	$attachmentInfo = $attachmentRepository->getAttachmentInfo($id);
	if ($attachmentInfo && checkAttachmentID($db,$id,$attachmentInfo))
	{
		$content = $attachmentRepository->getAttachmentContent($id,$attachmentInfo);
		if (strlen($content))
		{
			@ob_end_clean();
			header('Pragma: public');
			header("Cache-Control: ");
			if (!(isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on" && preg_match("/MSIE/",$_SERVER["HTTP_USER_AGENT"])))
				header('Pragma: no-cache');
			header('Content-Type: '.$attachmentInfo['file_type']);
			header('Content-Length: '.$attachmentInfo['file_size']);
			header("Content-Disposition: attachment; filename=\"{$attachmentInfo['file_name']}\"");
			header("Content-Description: Download Data");
			echo $content;
			exit();
		}
	}
}
$smarty = new TLSmarty();
$smarty->display('attachment404.tpl');	
