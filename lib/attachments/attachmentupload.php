<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: attachmentupload.php,v $
 *
 * @version $Revision: 1.10 $
 * @modified $Date: 2007/11/09 20:04:10 $ by $Author: schlundus $
 *
 * Upload dialog for attachments
 *
 *  Code check: 2007/11/16 schlundus
**/
require_once('../../config.inc.php');
require_once('../functions/common.php');
require_once('../functions/attachments.inc.php');
testlinkInitPage($db);

//the id (attachments.fk_id) of the object, to which the attachment belongs to 
$id = isset($_GET['id'])? intval($_GET['id']) : 0;
//the table to which the fk_id refers to (attachments.fk_table) of the attachment 
$tableName = isset($_GET['tableName'])? $_GET['tableName'] : null;

$bPostBack = sizeof($_POST);
$bUploaded = false;
$msg = null;
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
		$fName = isset($fInfo['name']) ? $fInfo['name'] : null;
		$fSize = isset($fInfo['size']) ? $fInfo['size'] : 0;
		$fType = isset($fInfo['type']) ? $fInfo['type'] : '';
		$fTmpName = isset($fInfo['tmp_name']) ? $fInfo['tmp_name'] : '';
		$fContents = null;
		if ($fSize && strlen($fTmpName))
		{
			$fExt = getFileExtension(isset($fInfo['name']) ? ($fInfo['name']) : '',"bin");
			$destFPath = null;
			$destFName = getUniqueFileName($fExt);
			
			if ($g_repositoryType == TL_REPOSITORY_TYPE_FS)
			{
				$destFPath = buildRepositoryFilePath($destFName,$tableName,$id);
				$bUploaded = storeFileInFSRepository($fTmpName,$destFPath);
			}
			else
			{
				$fContents = getFileContentsForDBRepository($fTmpName,$destFName);
				$bUploaded = sizeof($fContents);
			}
			@unlink($fTmpName);			
		}
		else
			$msg  = getFileUploadErrorMessage($fInfo);
		
		if ($bUploaded)
			$bUploaded = insertAttachment($db,$id,$tableName,$fName,$destFPath,$fContents,$fType,$fSize,$title);
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