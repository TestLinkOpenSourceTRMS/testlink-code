<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: attachments.inc.php,v $
 *
 * @version $Revision: 1.12 $
 * @modified $Date: 2007/12/02 15:44:18 $ by $Author: schlundus $
 *
 * functions related to attachments
 *
**/
require_once('common.php');
require_once( dirname(__FILE__) . '/attachment.class.php' );
require_once( dirname(__FILE__) . '/attachmentrepository.class.php' );
require_once( dirname(__FILE__) . '/files.inc.php' );

/*
 * Get infos about the attachments of a given object
 * 
 * @param object $attachmentRepository [ref] the attachment Repository
 * @param int $fkid the id of the object (attachments.fk_id);
 * @param string $fkTableName the name of the table $fkid refers to (attachments.fk_table)
 * @param bool $bStoreListInSession if true, the attachment list will be stored within the session
 * @param int $counter if $counter > 0 the attachments are appended to existing attachments within the session
 *
 * @return bool returns infos about the attachment on success, NULL else
*/
function getAttachmentInfos(&$attachmentRepository,$fkid,$fkTableName,$bStoreListInSession = true,$counter = 0)
{
	$attachmentInfos = $attachmentRepository->getAttachmentInfosFor($fkid,$fkTableName);
	if ($bStoreListInSession)
		storeAttachmentsInSession($attachmentInfos,$counter);
	
	return $attachmentInfos;
}

function storeAttachmentsInSession($attachmentInfos,$counter = 0)
{
	if (!$attachmentInfos)
		$attachmentInfos = array();
	if (!isset($_SESSION['s_lastAttachmentInfos']) || !$_SESSION['s_lastAttachmentInfos'])
		$_SESSION['s_lastAttachmentInfos'] = array();
	if ($counter == 0) 
		$_SESSION['s_lastAttachmentInfos'] = $attachmentInfos;
	else
		$_SESSION['s_lastAttachmentInfos'] = array_merge($_SESSION['s_lastAttachmentInfos'],$attachmentInfos);
}

function checkAttachmentID(&$db,$id,$attachmentInfo)
{
	$bValid = false;
	if ($attachmentInfo)
	{
		$sLastAttachmentInfos = isset($_SESSION['s_lastAttachmentInfos']) ? $_SESSION['s_lastAttachmentInfos'] : null;
		for($i = 0;$i < sizeof($sLastAttachmentInfos);$i++)
		{
			$info = $sLastAttachmentInfos[$i];
			if ($info['id'] == $id)
			{
				$bValid = true;
				break;
			}
		}
	}
	return $bValid;	
}
?>