<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: attachments.inc.php,v $
 *
 * @version $Revision: 1.2 $
 * @modified $Date: 2006/03/23 20:46:28 $ by $Author: schlundus $
 *
 * functions related to attachments
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
			$tmpGZName = $g_repositoryPath.DS.$destFName.".gz";
			gzip_compress_file($fTmpName, $tmpGZName);
			$fTmpName = $tmpGZName;
			break;
	}
	$fContents = getFileContents($fTmpName);
	if (!is_null($tmpGZName))
		unlink($tmpGZName);			
		
	return $fContents;
}

function storeFileInFSRepository($fTmpName,&$destFPath)
{
	global $g_repositoryCompressionType;
	
	switch($g_repositoryCompressionType)
	{
		case TL_REPOSITORY_COMPRESSIONTYPE_NONE:
			$bUploaded = move_uploaded_file($fTmpName,$destFPath);
			break;
		case TL_REPOSITORY_COMPRESSIONTYPE_GZIP:
			$destFPath .= ".gz";
			$bUploaded = gzip_compress_file($fTmpName,$destFPath);
			break;
	}
	return $bUploaded;
}

function insertAttachment(&$db,$id,$tableName,$fName,$destFPath,$fContents,$fType,$fSize,$title)
{
	global $g_repositoryCompressionType;
	global $g_repositoryPath;
	
	$tableName = $db->prepare_string($tableName);
	$fName = $db->prepare_string($fName);
	$destFPath = is_null($destFPath) ? 'NULL' : "'".$db->prepare_string(str_replace($g_repositoryPath.DS,"",$destFPath))."'";
	
	$fContents = is_null($fContents) ? 'NULL' : "'".$db->prepare_string($fContents)."'";
	$title = $db->prepare_string($title);
	$fType = $db->prepare_string($fType);
	$date = date("Y-m-d H:i:s");
	$query = "INSERT INTO attachments (fk_id,fk_table,file_name,file_path,file_size,file_type,date_added,content,compression_type,title) VALUES " 
				. "({$id},'{$tableName}','{$fName}',{$destFPath},{$fSize},'{$fType}','{$date}',$fContents,$g_repositoryCompressionType,'{$title}')";
			
	$result = $db->exec_query($query);					

	return $result ? 1 : 0;
}

function buildRepositoryFolder($destFName,$tableName,$id)
{
	global $g_repositoryPath;
	
	$destFPath = $g_repositoryPath.DS.$tableName.$id;
	if (!file_exists($destFPath))
			mkdir($destFPath);
	$destFPath .= DS.$destFName;
	
	return $destFPath;
}

function getUniqueFileName($fExt)
{
	$destFName = md5(uniqid(rand(), true)).".".$fExt; 
	
	return $destFName;
}

function getFileExtension($fName,$default)
{
	$fExt = pathinfo($fName);
	if (isset($fExt['extension']))
		$fExt = $fExt['extension'];
	else
		$fExt  = $default;
		
	return $fExt;
}

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

function gzip_compress_file($srcName, $dstName)
{
	$fp = fopen($srcName, "r");
	if ($fp)
	{
		$data = fread($fp,filesize($srcName));
		fclose($fp);
		$zp = gzopen($dstName, "wb9");
		if ($zp)
		{
			gzwrite($zp, $data);
			gzclose($zp);
			return true;
		}
	}
	return false;
}

function getAttachmentInfos(&$db,$fkid,$tableName,$bStoreListInSession = true)
{
	$query = "SELECT id,title,description,file_name,file_type,file_size,date_added,compression_type,file_path, fk_id,fk_table FROM attachments WHERE fk_id = {$fkid} AND fk_table = '" .
			 $db->prepare_string($tableName)."' ORDER BY date_added DESC";
	$attachmentInfos = $db->get_recordset($query);			 
	$_SESSION['s_lastAttachmentInfos'] = $attachmentInfos;

	return $attachmentInfos;
}

function getAttachmentInfo(&$db,$id)
{
	$query = "SELECT id,title,description,file_name,file_type,file_size,date_added,".
			 "compression_type,file_path,fk_id,fk_table FROM attachments WHERE id = {$id} ".
			 "ORDER BY date_added DESC";
	return $db->fetchFirstRow($query);			 
}

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
		$sLastAttachmentInfos = $_SESSION['s_lastAttachmentInfos'];
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
			$bResult = deleteAttachmentFromFS(&$db,$id,$attachmentInfo);
		$bResult = $bResult && deleteAttachmentFromDB(&$db,$id,$attachmentInfo);
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
?>