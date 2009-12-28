<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: attachmentupload.php,v $
 *
 * @version $Revision: 1.23 $
 * @modified $Date: 2009/12/28 08:52:06 $ by $Author: franciscom $
 *
 * Upload dialog for attachments
 *
**/
require_once('../../config.inc.php');
require_once('../functions/common.php');
require_once('../functions/attachments.inc.php');
testlinkInitPage($db,false,false,"checkRights");
	
$args = init_args();
$gui = new stdClass();
$gui->uploaded = false;
$gui->msg = null;
$gui->tableName = $args->tableName;
$gui->import_limit = TL_REPOSITORY_MAXFILESIZE;
$gui->id = $args->id;

if ($args->bPostBack)
{
	$fInfo  = isset($_FILES['uploadedFile']) ? $_FILES['uploadedFile'] : null;
	$id = $_SESSION['s_upload_id'];
	$gui->tableName = $_SESSION['s_upload_tableName'];
	
	if ($fInfo && $id && $gui->tableName != "")
	{
		$fSize = isset($fInfo['size']) ? $fInfo['size'] : 0;
		$fTmpName = isset($fInfo['tmp_name']) ? $fInfo['tmp_name'] : '';
		if ($fSize && $fTmpName != "")
		{
			$attachmentRepository = tlAttachmentRepository::create($db);
			$gui->uploaded = $attachmentRepository->insertAttachment($id,$gui->tableName,$args->title,$fInfo);
			if ($gui->uploaded)
			{
				logAuditEvent(TLS("audit_attachment_created",$args->title,$fInfo['name']),"CREATE",$id,"attachments");
			}	
		}
		else
		{
			$gui->msg  = getFileUploadErrorMessage($fInfo);
		}	
	}
}
else
{
	$_SESSION['s_upload_tableName'] = $args->tableName;
	$_SESSION['s_upload_id'] = $args->id;
}

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display('attachmentupload.tpl');

/**
 * @return object returns the arguments for the page
 */
function init_args()
{
	$iParams = array(
		//the id (attachments.fk_id) of the object, to which the attachment belongs to 
		"id" => array("GET",tlInputParameter::INT_N),
		//the table to which the fk_id refers to (attachments.fk_table) of the attachment 
		"tableName" => array("GET",tlInputParameter::STRING_N,0,250),
		//the title of the attachment (attachments.title) 
		"title" => array("POST",tlInputParameter::STRING_N,0,250),
	);
	$args = new stdClass();
	I_PARAMS($iParams,$args);
	
	$args->bPostBack = sizeof($_POST);
	
	return $args;
}

/**
 * @param $db resource the database connection handle
 * @param $user the current active user
 * @return boolean returns true if the page can be accessed
 */
function checkRights(&$db,&$user)
{
	return (config_get("attachments")->enabled);
}
?>