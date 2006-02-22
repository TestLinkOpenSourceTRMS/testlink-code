<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: attachmentupload.php,v $
 *
 * @version $Revision: 1.1 $
 * @modified $Date: 2006/02/22 20:26:38 $ by $Author: schlundus $
 *
 * Upload dialog
**/
require_once('../../config.inc.php');
require_once('../functions/common.php');
require_once('../functions/attachments.inc.php');
testlinkInitPage($db);

$id = isset($_GET['id'])? $_GET['id'] : 0;
$tableName = isset($_GET['tableName'])? $_GET['tableName'] : null;
$bPostBack = isset($_POST);
$bUploaded = false;

if ($bPostBack)
{
	$fInfo  = isset($HTTP_POST_FILES['uploadedFile']) ? $HTTP_POST_FILES['uploadedFile'] : null;
	$title = isset($_POST['title']) ? $_POST['title'] : "";
	
	if ($fInfo)
	{
		$error = isset($fInfo['error']) ? $fInfo['error'] : 0;
		$fName = isset($fInfo['name']) ? $fInfo['name'] : null;
		$fSize = isset($fInfo['size']) ? $fInfo['size'] : 0;
		$fType = isset($fInfo['type']) ? $fInfo['type'] : '';
		$fTmpName = isset($fInfo['tmp_name']) ? $fInfo['tmp_name'] : '';
		if ($fSize && strlen($fTmpName) && !$bError)
		{
			$fExt = getFileExtension(isset($fInfo['name']) ? ($fInfo['name']) : '',"bin");
			$fContents = null;
			$destFPath = null;
			$destFName = getUniqueFileName($fExt);
			
			if ($g_repositoryType == TL_REPOSITORY_TYPE_FS)
			{
				$destFPath = buildRepositoryFolder($destFName,$tableName,$id);
				$bUploaded = storeFileInFSRepository($fTmpName,$destFPath);
			}
			else
			{
				$fContents = getFileContentsForDBRepository($fTmpName,$destFName);
				$bUploaded = sizeof($fContents);
			}
			unlink($fTmpName);			
		}
		if ($bUploaded)
			$bUploaded = insertAttachment(&$db,$id,$tableName,$fName,$destFPath,$fContent,$fType,$fSize,$title);
	}
}

$smarty = new TLSmarty();
$smarty->assign('import_limit',TL_REPOSITORY_MAXFILESIZE);
$smarty->assign('id',$id);
$smarty->assign('tableName',$tableName);
$smarty->assign('bUploaded',$bUploaded);
$smarty->assign('bPostBack',$bPostBack);
$smarty->display('attachmentupload.tpl');
?>