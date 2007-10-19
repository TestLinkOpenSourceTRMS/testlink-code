<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: attachments.inc.php,v $
 *
 * @version $Revision: 1.10 $
 * @modified $Date: 2007/10/19 18:21:19 $ by $Author: schlundus $
 *
 * functions related to attachments
 *
 * 20060602 - franciscom - changed title management in insertAttachment
**/

// 20060602 - franciscom - to enable use of config_get()
require_once('common.php');

/**
 * Fetches the contents of a file for storing it into the DB-repository
 *
 * @param string $fTmpName the filename of the attachment
 * @param string $destFName a unique file name for temporary usage
 * 
 * @return string the contents of the attachment to be stored into the db
 **/
function getFileContentsForDBRepository($fTmpName,$destFName)
{
	global $g_repositoryCompressionType;
	global $g_repositoryPath;
	
	$tmpGZName = null;
	switch($g_repositoryCompressionType)
	{
		case TL_REPOSITORY_COMPRESSIONTYPE_NONE:
			break;
		case TL_REPOSITORY_COMPRESSIONTYPE_GZIP:
			//copy the file into a dummy file in the repository and gz it and 
			//read the file contents from this new file
			$tmpGZName = $g_repositoryPath.DS.$destFName.".gz";
			gzip_compress_file($fTmpName, $tmpGZName);
			$fTmpName = $tmpGZName;
			break;
	}
	$fContents = getFileContents($fTmpName);
	//delete the dummy file if present
	if (!is_null($tmpGZName))
		unlink($tmpGZName);			
		
	return $fContents;
}

/**
 * Stores a file into the FS-repository
 *
 * @param string $fTmpName the filename
 * @param string $destFPath [ref] the destination file name
 *
 * @return bool returns true if the file was uploaded, false else
 **/
function storeFileInFSRepository($fTmpName,&$destFPath)
{
	global $g_repositoryCompressionType;
	
	switch($g_repositoryCompressionType)
	{
		case TL_REPOSITORY_COMPRESSIONTYPE_NONE:
			$bUploaded = move_uploaded_file($fTmpName,$destFPath);
			break;
		case TL_REPOSITORY_COMPRESSIONTYPE_GZIP:
			//add the gz extension and compress the file
			$destFPath .= ".gz";
			$bUploaded = gzip_compress_file($fTmpName,$destFPath);
			break;
	}
	return $bUploaded;
}

/**
 * Inserts the information about an attachment into the db
 *
 * @param object $db [ref] the db-object
 * @param int $id the foreign key id (attachments.fk_id)
 * @param string $tableName the tablename to which the $id refers to (attachments.fk_table)
 * @param string $fName the filename
 * @param string $destFPath the file path 
 * @param string $fContents the contents of the file
 * @param string $fType the mime-type of the file
 * @param int $fSize the filesize (uncompressed)
 * @param string $title the title used for the attachment
 *
 * @return int returns 1 if the information was successfully stored, 0 else
 *
 **/
function insertAttachment(&$db,$id,$tableName,$fName,$destFPath,$fContents,$fType,$fSize,$title)
{
	global $g_repositoryCompressionType;
	global $g_repositoryPath;
	
	$tableName = $db->prepare_string($tableName);
	$fName = $db->prepare_string($fName);
	//for DB-repository the filename is null
	//for FS-repository, the path to the repository itself is cut off, so the path is
	//					relative to the repository itself
	$destFPath = is_null($destFPath) ? 'NULL' : "'".$db->prepare_string(str_replace($g_repositoryPath.DS,"",$destFPath))."'";
	//for FS-repository the contents are null
	$fContents = is_null($fContents) ? 'NULL' : "'".$db->prepare_string($fContents)."'";
	
	if(strlen(trim($title)) == 0)
	{
		$cfg = config_get('attachments');
		switch ($cfg->action_on_save_empty_title)
		{
			case 'use_filename':
				$title = $fName;
				break;
			default:
				break;  
		}
	}
	$title = $db->prepare_string($title);
	$fType = $db->prepare_string($fType);

	$query = "INSERT INTO attachments 
       (fk_id,fk_table,file_name,file_path,file_size,file_type, date_added,content,compression_type,title) 
        VALUES ({$id},'{$tableName}','{$fName}',{$destFPath},{$fSize},'{$fType}'," . $db->db_now() . 
       ",$fContents,$g_repositoryCompressionType,'{$title}')";
  

	$result = $db->exec_query($query);					

	return $result ? 1 : 0;
}

/**
 * Builds the path for a given filename according to the tablename and id
 *
 * @param string $destFName the fileName
 * @param string $tableName the tablename to which $id referes to (attachments.fk_table)
 * @param int $id the foreign key id attachments.fk_id)
 *
 * @return string returns the full path for the file 
 **/
function buildRepositoryFilePath($destFName,$tableName,$id)
{
	$destFPath = buildRepositoryFolderFor($tableName,$id,true);
	$destFPath .= DS.$destFName;
	
	return $destFPath;
}

/**
 * Builds the repository folder for the attachment
 *
 * @param string $tableName the tablename to which $id referes to (attachments.fk_table)
 * @param int $id the foreign key id attachments.fk_id)
 * @param bool $mkDir if true then the the directory will be created, else not
 *
 * @return string returns the full path for the folder 
 **/
function buildRepositoryFolderFor($tableName,$id,$mkDir = false)
{
	global $g_repositoryPath;

	$path = $g_repositoryPath.DS.$tableName;
	if ($mkDir && !file_exists($path))
		mkdir($path);
	$path .= DS.$id;
	if ($mkDir && !file_exists($path))
		mkdir($path);
	
	return $path;
}
/**
 * Gets an unique file name to be used for the attachment
 *
 * @param string $fExt the file extension
 *
 * @return string the filename
 **/
function getUniqueFileName($fExt)
{
	$destFName = md5(uniqid(rand(), true)).".".$fExt; 
	
	return $destFName;
}

/**
 * gets the extension from a file name
 *
 * @param string $fName the filename
 * @param string $default a default extension 
 *
 * @return string returns the extension
 **/
function getFileExtension($fName,$default)
{
	$fExt = pathinfo($fName);
	if (isset($fExt['extension']))
		$fExt = $fExt['extension'];
	else
		$fExt = $default;
		
	return $fExt;
}

/**
 * get the contents of a file 
 *
 * @param string $fName the name of the file to read
 *
 * @return string the file contents
 **/
function getFileContents($fName)
{
	$fContents = null;
	$fd = fopen($fName,"rb");
	if ($fd)
	{
		$fContents = fread($fd,filesize($fName));
		fclose($fd);
	}
	return $fContents;
}

/**
 * Compresses a file (creates a gzipped file)
 *
 * @param string $srcName the source file
 * @param string $dstName the destination file name (the compressed one)
 *
 * @return bool returns true on success, false else
 **/
function gzip_compress_file($srcName, $dstName)
{
	$bSuccess = false;

	$data = getFileContents($srcName);
	if (strlen($data))
		$bSuccess = gzip_writeToFile($dstName,$data);
		
	return $bSuccess;
}

/**
 * Writes contents to a gzip-file
 *
 * @param string $dstName the filename
 * @param string $data the contents to be written
 *
 * @return bool returns true on success, false else
 **/
function gzip_writeToFile($dstName,$data)
{
	$zp = gzopen($dstName, "wb9");
	if ($zp)
	{
		gzwrite($zp, $data);
		gzclose($zp);
		return true;
	}
	return false;
}

/*
 * Get infos about the attachments of a given object
 * 
 * @param object $db [ref] the db-object
 * @param int $fkid the id of the object (attachments.fk_id);
 * @param string $tablename the name of the table $fkid refers to (attachments.fk_table)
 * @param bool $bStoreListInSession if true, the attachment list will be stored within the session
 * @param int $counter if $counter > 0 the attachments are appended to existing attachments within the session
 *
 * @return bool returns true on success, false else
 
  rev :
       20070930 - franciscom - using attachment config 
*/
function getAttachmentInfos(&$db,$fkid,$tableName,$bStoreListInSession = true,$counter = 0)
{
	$attachment_cfg = config_get('attachments');
	$order_by = $attachment_cfg->order_by;
  
	$query = "SELECT id,title,description,file_name,".
	         " file_type,file_size,date_added,compression_type," .
	         " file_path, fk_id,fk_table " .
	         " FROM attachments " .
	         " WHERE fk_id = {$fkid} AND fk_table = '" . $db->prepare_string($tableName). "' " . $order_by;
	         
	$attachmentInfos = $db->get_recordset($query);
	if ($bStoreListInSession)
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

	return $attachmentInfos;
}


/**
 * Gets some common infos about attachments 
 *
 * @param object $db [ref] the db-object
 * @param int $id the id of the attachment (attachments.id)
 * 
 * @return array info about the attachment, if one exists, null if not 

  rev :
       20070930 - franciscom - using attachment config 
*/
function getAttachmentInfo(&$db,$id)
{
	$attachment_cfg = config_get('attachments');
	$order_by = $attachment_cfg->order_by;
  
	$query = "SELECT id,title,description,file_name,file_type,file_size,date_added,".
			 "compression_type,file_path,fk_id,fk_table " .
			 "FROM attachments WHERE id = {$id} " .  $order_by;

	return $db->fetchFirstRow($query);			 
}

/**
 * Gets some common infos about attachments 
 *
 * @param object $db [ref] the db-object
 * @param int $id the id of the attachment (attachments.id)
 * 
 * @return string returns the contents of the attachment 

*/
function getAttachmentContentFromDB(&$db,$id)
{
	global $g_repositoryCompressionType;
	
	$query = "SELECT content,file_size,compression_type FROM attachments WHERE id = {$id}";
	$row = $db->fetchFirstRow($query);
	
	$content = null;
	if ($row)
	{
		$content = $row['content'];
		$fileSize = $row['file_size'];
		switch($row['compression_type'])
		{
			case TL_REPOSITORY_COMPRESSIONTYPE_NONE:
				break;
			case TL_REPOSITORY_COMPRESSIONTYPE_GZIP:
				$content = gzip_uncompress_content($content,$fileSize);
				break;
		}
	}
	
	return $content;
}

function gzip_uncompress_content($content,$fileSize)
{
	global $g_repositoryPath;

	$dest = $g_repositoryPath.DS.session_id().".dummy.gz";
	$fp = fopen($dest,"wb");
	if ($fp)
	{
		fwrite($fp,$content,strlen($content));
		fclose($fp);
		return gzip_readFileContent($dest,$fileSize);
	}
	return null;
}

function gzip_readFileContent($fName,$fileSize)
{
	$content = null;
	$zp = gzopen($fName, "rb9");
	if ($zp)
	{
		$content = gzread($zp,$fileSize);
		gzclose($zp);
		
	}
	return $content;
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

function deleteAttachment($db,$id,$attachmentInfo = null)
{
	if (is_null($attachmentInfo))
		$attachmentInfo = getAttachmentInfo($db,$id);
	$bResult = false;
	if ($attachmentInfo)
	{	
		$bResult = true;
		if (strlen($attachmentInfo['file_path']))
			$bResult = deleteAttachmentFromFS($db,$id,$attachmentInfo);
		$bResult = $bResult && deleteAttachmentFromDB($db,$id,$attachmentInfo);
	}
	return $bResult ? 1 : 0;
}

function deleteAttachmentFromDB(&$db,$id,$attachmentInfo = null)
{
	$query = "DELETE FROM attachments WHERE id = {$id}";
	return $db->exec_query($query);
}

function deleteAttachmentFromFS(&$db,$id,$attachmentInfo = null)
{
	$filePath = $attachmentInfo['file_path'];
	global $g_repositoryPath;
	
	$destFPath = $g_repositoryPath.DS.$filePath;
	return @unlink($destFPath) ? 1 : 0;
}

function getAttachmentContentFromFS(&$db,$id)
{
	global $g_repositoryPath;
	$query = "SELECT file_size,compression_type,file_path FROM attachments WHERE id = {$id}";
	$row = $db->fetchFirstRow($query);
	
	$content = null;
	if ($row)
	{
		$filePath = $row['file_path'];
		$fileSize = $row['file_size'];
		$destFPath = $g_repositoryPath.DS.$filePath;
		switch($row['compression_type'])
		{
			case TL_REPOSITORY_COMPRESSIONTYPE_NONE:
				$content = getFileContents($destFPath);
				break;
			case TL_REPOSITORY_COMPRESSIONTYPE_GZIP:
				$content = gzip_readFileContent($destFPath,$fileSize);
				break;
		}
	}
	
	return $content;
}

function deleteAttachmentsFor(&$db,$id,$tableName)
{
	$attachmentInfos = getAttachmentInfos($db,$id,$tableName,false);
	$bSuccess = true;
	if (sizeof($attachmentInfos))
	{
		for($i = 0;$i < sizeof($attachmentInfos);$i++)
		{
			$attachmentInfo = $attachmentInfos[$i];
			$id = $attachmentInfo['id'];
			$bSuccess = (deleteAttachment($db,$id,$attachmentInfo) && $bSuccess);
		}
	}
	return $bSuccess;
}
?>