<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @package 	TestLink
 * @author 		franciscom
 * @copyright 	2005-2009, TestLink community 
 * @version    	CVS: $Id: files.inc.php,v 1.7 2009/07/09 19:02:55 schlundus Exp $
 * @link 		http://www.teamst.org/index.php
 *
 */

/**
 * Gets an unique file name to be used for the attachment
 *
 * @param string $fExt the file extension
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
	$success = false;
	$data = getFileContents($srcName);
	if ($data != "")
		$success = gzip_writeToFile($dstName,$data);
	return $success;
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
	$success = false;
	if ($zp)
	{
		gzwrite($zp, $data);
		gzclose($zp);
		$success = true;
	}
	return $success;
}


/**
 * uncompresses arbitrary gzipped content
 *
 * @param string content the compressed content
 * @param int $fileSize the original size of the uncompressed content
 *
 * @return string returns the uncompressed contents on success or null on error
 */
function gzip_uncompress_content($content,$fileSize)
{
	global $g_repositoryPath;

	$dest = $g_repositoryPath.DIRECTORY_SEPARATOR.session_id().".dummy.gz";
	$fp = fopen($dest,"wb");
	if ($fp)
	{
		fwrite($fp,$content,strlen($content));
		fclose($fp);
		return gzip_readFileContent($dest,$fileSize);
	}
	return null;
}


/**
 * Read contents from a gzip-file
 *
 * @param string $fName the filename
 * @param int $fileSize the original size of the uncompressed content
 *
 * @return string returns the uncompressed contents on success or null on error
 **/
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
?>