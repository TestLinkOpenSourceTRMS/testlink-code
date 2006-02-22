<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: attachments.inc.php,v $
 *
 * @version $Revision: 1.1 $
 * @modified $Date: 2006/02/22 20:26:38 $ by $Author: schlundus $
 *
 * functions related to attachments
**/

function getFileContentsForDBRepository($fTmpName,$destFName)
{
	global $g_repositoryCompressionType;
	global $g_repositoryPath;
	
	switch($g_repositoryCompressionType)
	{
		case TL_REPOSITORY_COMPRESSIONTYPE_NONE:
			break;
		case TL_REPOSITORY_COMPRESSIONTYPE_GZ:
			$tmpGZName = $g_repositoryPath.DS.$destFName.".gz";
			gzip_compress_file($fTmpName, $tmpGZName);
			$fTmpName = $tmpGZName;
			unlink($tmpGZName);	
			break;
	}
	$fContents = getFileContents($fTmpName);
	
	return $fContents;
}

function storeFileInFSRepository($fTmpName,$destFPath)
{
	global $g_repositoryCompressionType;
	
	switch($g_repositoryCompressionType)
	{
		case TL_REPOSITORY_COMPRESSIONTYPE_NONE:
			$bUploaded = move_uploaded_file($fTmpName,$destFPath);
			break;
		case TL_REPOSITORY_COMPRESSIONTYPE_GZIP:
			$bUploaded = gzip_compress_file($fTmpName, $destFPath.".gz");
			break;
	}
	return $bUploaded;
}
function insertAttachment(&$db,$id,$tableName,$fName,$destFPath,$fContent,$fType,$fSize,$title)
{
	global $g_repositoryCompressionType;
	
	$tableName = $db->prepare_string($tableName);
	$fName = $db->prepare_string($fName);
	$destFPath = is_null($destFPath) ? 'NULL' : "'".$db->prepare_string($destFPath)."'";
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
		$zp = gzopen($dstName, "w9");
		if ($zp)
		{
			gzwrite($zp, $data);
			gzclose($zp);
			return true;
		}
	}
	return false;
}
?>