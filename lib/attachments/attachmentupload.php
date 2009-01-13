<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: attachmentupload.php,v $
 *
 * @version $Revision: 1.18 $
 * @modified $Date: 2009/01/13 20:21:23 $ by $Author: schlundus $
 *
 * Upload dialog for attachments
 *
**/
require_once('../../config.inc.php');
require_once('../functions/common.php');
require_once('../functions/attachments.inc.php');
testlinkInitPage($db);

if (!config_get("attachments")->enabled)
	exit();
	
//the id (attachments.fk_id) of the object, to which the attachment belongs to 
$id = isset($_GET['id'])? intval($_GET['id']) : 0;

//the table to which the fk_id refers to (attachments.fk_table) of the attachment 
$tableName = isset($_GET['tableName'])? $_GET['tableName'] : null;

$bUploaded = false;
$msg = null;
$bPostBack = sizeof($_POST);
if ($bPostBack > 2)
{
	$fInfo  = isset($_FILES['uploadedFile']) ? $_FILES['uploadedFile'] : null;
	//the title of the attachment (attachments.title) 
	$title = isset($_POST['title']) ? $_POST['title'] : "";
	//the id (attachments.fk_id) of the object, to which the attachment belongs to 
	$id = isset($_POST['id'])? intval($_POST['id']) : 0;
	//the table to which the fk_id refers to (attachments.fk_table) of the attachment 
	$tableName = isset($_POST['tableName'])? $_POST['tableName'] : null;
	if ($fInfo)
	{
		$fSize = isset($fInfo['size']) ? $fInfo['size'] : 0;
		$fTmpName = isset($fInfo['tmp_name']) ? $fInfo['tmp_name'] : '';
		if ($fSize && strlen($fTmpName))
		{
			$attachmentRepository = tlAttachmentRepository::create($db);
			$bUploaded = $attachmentRepository->insertAttachment($id,$tableName,$title,$fInfo);
			if ($bUploaded)
				logAuditEvent(TLS("audit_attachment_created",$title,$fInfo['name']),"CREATE",$id,"attachments");
		}
		else
			$msg  = getFileUploadErrorMessage($fInfo);
	}
}

$smarty = new TLSmarty();
$smarty->assign('import_limit',TL_REPOSITORY_MAXFILESIZE);
$smarty->assign('id',$id);
$smarty->assign('tableName',$tableName);
$smarty->assign('bUploaded',$bUploaded);
$smarty->assign('bPostBack',$bPostBack);
$smarty->assign('msg',$msg);
$smarty->display('attachmentupload.tpl');
?>
