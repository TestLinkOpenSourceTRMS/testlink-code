<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * functions related to attachments
 *
 * @package     TestLink
 * @filesource  attachments.inc.php
 * @copyright   2007-2014, TestLink community 
 * @link        http://www.testlink.org
 *
 *
 * @internal revisions
 * @since 1.9.10
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
  {
    $attachmentInfos = array();
  }  
    
  if (!isset($_SESSION['s_lastAttachmentInfos']) || !$_SESSION['s_lastAttachmentInfos'])
  {
    $_SESSION['s_lastAttachmentInfos'] = array();
  } 
    
  if ($counter == 0) 
  {
    $_SESSION['s_lastAttachmentInfos'] = $attachmentInfos;
  }  
  else
  {
    $_SESSION['s_lastAttachmentInfos'] = array_merge($_SESSION['s_lastAttachmentInfos'],$attachmentInfos);
  }  
    
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


/**
 *
 */
function fileUploadManagement(&$dbHandler,$id,$title,$table)
{
  $retVal = new stdClass();
  $retVal->uploaded = null;
  $retVal->msg = null;
  
  $fInfo  = isset($_FILES['uploadedFile']) ? $_FILES['uploadedFile'] : null;
  if ($fInfo && $id)
  {
    $fSize = isset($fInfo['size']) ? $fInfo['size'] : 0;
    $fTmpName = isset($fInfo['tmp_name']) ? $fInfo['tmp_name'] : '';

    if ($fSize && $fTmpName != "")
    {
      $repo = tlAttachmentRepository::create($dbHandler);
      $retVal->uploaded = $repo->insertAttachment($id,$table,$title,$fInfo);
      if ($retVal->uploaded)
      {
        logAuditEvent(TLS("audit_attachment_created",$title,$fInfo['name']),"CREATE",$id,"attachments");
      } 
    }
    else
    {
      $retVal->msg  = getFileUploadErrorMessage($fInfo);
    } 
  }
  return $retVal;
}

/**
 *
 */
function deleteAttachment(&$dbHandler,$fileID,$checkOnSession=true)
{
  $repo = tlAttachmentRepository::create($dbHandler);
  $info = $repo->getAttachmentInfo($fileID);
  if( $info )
  {
    $doIt = true;
    if( $checkOnSession )
    {
      $doIt = checkAttachmentID($dbHandler,$fileID,$info);
    }

    if( $doIt )
    {  
      if($repo->deleteAttachment($fileID,$info))
      {
        logAuditEvent(TLS("audit_attachment_deleted",$info['title']),"DELETE",$fileID,"attachments");
      } 
    }
  }
}

