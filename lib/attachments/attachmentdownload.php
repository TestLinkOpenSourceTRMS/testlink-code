<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: attachmentdownload.php,v $
 *
 * @version $Revision: 1.15 $
 * @modified $Date: 2009/04/07 18:55:29 $ by $Author: schlundus $
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
	
$args = init_args();

if ($args->id)
{
	$attachmentRepository = tlAttachmentRepository::create($db);
	$attachmentInfo = $attachmentRepository->getAttachmentInfo($args->id);
	if ($attachmentInfo && checkAttachmentID($db,$args->id,$attachmentInfo))
	{
		$content = $attachmentRepository->getAttachmentContent($args->id,$attachmentInfo);
		if ($content != "")
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

function init_args()
{
	//the id (attachments.id) of the attachment to be downloaded
	$iParams = array(
		"id" => array(tlInputParameter::INT_N),
	);
	$pParams = G_PARAMS($iParams);
	
	$args = new stdClass();
	$args->id = $pParams["id"];
	
	return $args;
}
?>