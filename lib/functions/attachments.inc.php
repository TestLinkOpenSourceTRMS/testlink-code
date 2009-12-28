<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * functions related to attachments
 *
 * @package 	TestLink
 * @copyright 	2007-2009, TestLink community 
 * @version    	CVS: $Id: attachments.inc.php,v 1.20 2009/12/28 08:52:59 franciscom Exp $
 * @link 		http://www.teamst.org/index.php
 *
 **/

/** core functions */
require_once('common.php');
require_once( dirname(__FILE__) . '/files.inc.php' );

/**
 * Get infos about the attachments of a given object
 * 
 * @param object $attachmentRepository [ref] the attachment Repository
 * @param int $fkid the id of the object (attachments.fk_id);
 * @param string $fkTableName the name of the table $fkid refers to (attachments.fk_table)
 * @param bool $storeListInSession if true, the attachment list will be stored within the session
 * @param int $counter if $counter > 0 the attachments are appended to existing attachments within the session
 *
 * @return array infos about the attachment on success, NULL else
*/
function getAttachmentInfos(&$attachmentRepository,$fkid,$fkTableName,$storeListInSession = true,$counter = 0)
{
	$attachmentInfos = $attachmentRepository->getAttachmentInfosFor($fkid,$fkTableName);
	if ($storeListInSession)
	{
		storeAttachmentsInSession($attachmentInfos,$counter);
	}
	return $attachmentInfos;
}

/**
 * Get infos about the attachments of a given object
 * 
 * @param tlObjectWithAttachments $object The object whose attachment should be fetched
 * @param int $fkid the id of the object (attachments.fk_id);
 * @param bool $storeListInSession if true, the attachment list will be stored within the session
 * @param int $counter if $counter > 0 the attachments are appended to existing attachments within the session
 *
 * @return array returns infos about the attachment on success, NULL else
 */
function getAttachmentInfosFrom(&$object,$fkid,$storeListInSession = true,$counter = 0)
{
	$attachmentInfos = $object->getAttachmentInfos($fkid);
	if ($storeListInSession)
	{
		storeAttachmentsInSession($attachmentInfos,$counter);
	}
	return $attachmentInfos;
}

/**
 * Stores the attachment infos into the session for referencing it later
 * 
 * @param array $attachmentInfos infos about attachment
 * @param $counter counter for the attachments in the session
 */
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

/**
 * Checks the id of an attachment and the corresponding attachment info for validity
 * 
 * @param resource $db [ref] the database connection
 * @param integer $id the database identifier of the attachment
 * @param $attachmentInfo 
 * @return boolean return true if the id is valid, false else
 */
function checkAttachmentID(&$db,$id,$attachmentInfo)
{
	$isValid = false;
	if ($attachmentInfo)
	{
		$sLastAttachmentInfos = isset($_SESSION['s_lastAttachmentInfos']) ? $_SESSION['s_lastAttachmentInfos'] : null;
		for($i = 0;$i < sizeof($sLastAttachmentInfos);$i++)
		{
			$info = $sLastAttachmentInfos[$i];
			if ($info['id'] == $id)
			{
				$isValid = true;
				break;
			}
		}
	}
	return $isValid;	
}
?>