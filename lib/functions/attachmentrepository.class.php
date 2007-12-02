<?php
require_once( dirname(__FILE__) . '/attachment.class.php' );

class tlAttachmentRepository extends tlObjectWithDB
{
	//the one and only attachment repository object
	private static $s_instance;
	
    private $m_repositoryType;
	private $m_repositoryCompressionType;
   	protected $m_repositoryPath;
	protected $m_attachmentCfg;

	
	function __construct(&$db)
	{
		global $g_repositoryType;
		global $g_repositoryCompressionType;
		global $g_repositoryPath;

		tlObjectWithDB::__construct($db);
    	$this->m_repositoryType = $g_repositoryType;
    	$this->m_repositoryCompressionType = $g_repositoryCompressionType;
		$this->m_repositoryPath = $g_repositoryPath;
		$this->m_attachmentCfg = config_get('attachments');
	}
	
    public static function create(&$db) 
    {
        if (!isset(self::$s_instance))
		{
            $c = __CLASS__;
            self::$s_instance = new $c($db);
        }

        return self::$s_instance;
    }

	/**
	* Inserts the information about an attachment into the db
	*
	* @param int $fkid the foreign key id (attachments.fk_id)
	* @param string $fktableName the tablename to which the $id refers to (attachments.fk_table)
	* @param string $title the title used for the attachment
	* @param array $fInfo should be $_FILES in most cases
	*
	* @return int returns true if the information was successfully stored, false else
	*
	**/
	public function insertAttachment($fkid,$fkTableName,$title,$fInfo)
	{
		$fName = isset($fInfo['name']) ? $fInfo['name'] : null;
		$fType = isset($fInfo['type']) ? $fInfo['type'] : '';
		$fSize = isset($fInfo['size']) ? $fInfo['size'] : 0;
		$fTmpName = isset($fInfo['tmp_name']) ? $fInfo['tmp_name'] : '';
		
		$fContents = null;
		
		$fExt = getFileExtension(isset($fInfo['name']) ? ($fInfo['name']) : '',"bin");
		$destFPath = null;
		$destFName = getUniqueFileName($fExt);
		
		if ($this->m_repositoryType == TL_REPOSITORY_TYPE_FS)
		{
			$destFPath = $this->buildRepositoryFilePath($destFName,$fkTableName,$fkid);
			$bUploaded = $this->storeFileInFSRepository($fTmpName,$destFPath);
		}
		else
		{
			$fContents = $this->getFileContentsForDBRepository($fTmpName,$destFName);
			$bUploaded = sizeof($fContents);
		}
		
		if ($bUploaded)
		{
			$attachment = new tlAttachment();
			$attachment->create($fkid,$fkTableName,$fName,$destFPath,$fContents,$fType,$fSize,$title);
			$bUploaded = $attachment->writeToDb($this->m_db);
		}
		
		@unlink($fTmpName);
		
		return $bUploaded;
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
	public function buildRepositoryFilePath($destFName,$tableName,$id)
	{
		$destFPath = $this->buildRepositoryFolderFor($tableName,$id,true);
		$destFPath .= DS.$destFName;
		
		return $destFPath;
	}

	/**
	 * Fetches the contents of a file for storing it into the DB-repository
	 *
	 * @param string $fTmpName the filename of the attachment
	 * @param string $destFName a unique file name for temporary usage
	 * 
	 * @return string the contents of the attachment to be stored into the db
	 **/
	protected function getFileContentsForDBRepository($fTmpName,$destFName)
	{
		$tmpGZName = null;
		switch($this->m_repositoryCompressionType)
		{
			case TL_REPOSITORY_COMPRESSIONTYPE_NONE:
				break;
			case TL_REPOSITORY_COMPRESSIONTYPE_GZIP:
				//copy the file into a dummy file in the repository and gz it and 
				//read the file contents from this new file
				$tmpGZName = $this->m_repositoryPath.DS.$destFName.".gz";
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
	protected function storeFileInFSRepository($fTmpName,&$destFPath)
	{
		switch($this->m_repositoryCompressionType)
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
	 * Builds the repository folder for the attachment
	 *
	 * @param string $tableName the tablename to which $id referes to (attachments.fk_table)
	 * @param int $id the foreign key id attachments.fk_id)
	 * @param bool $mkDir if true then the the directory will be created, else not
	 *
	 * @return string returns the full path for the folder 
	 **/
 	//SCHLUNDUS: should be protected
	public function buildRepositoryFolderFor($tableName,$id,$mkDir = false)
	{
		$path = $this->m_repositoryPath.DS.$tableName;
		if ($mkDir && !file_exists($path))
			mkdir($path);
		$path .= DS.$id;
		if ($mkDir && !file_exists($path))
			mkdir($path);
		
		return $path;
	}
	
	protected function deleteAttachmentFromFS($dummy,$attachmentInfo = null)
	{
		$filePath = $attachmentInfo['file_path'];
		
		$destFPath = $this->m_repositoryPath.DS.$filePath;
		return @unlink($destFPath) ? 1 : 0;
	}

	protected function deleteAttachmentFromDB($id,$dummy = null)
	{
		$query = "DELETE FROM attachments WHERE id = {$id}";
		return $this->m_db->exec_query($query);
	}
	
	
	public function deleteAttachment($id,$attachmentInfo = null)
	{
		if (is_null($attachmentInfo))
			$attachmentInfo = $this->getAttachmentInfo($id);
		$bResult = false;
		if ($attachmentInfo)
		{	
			$bResult = true;
			if (strlen($attachmentInfo['file_path']))
				$bResult = $this->deleteAttachmentFromFS($id,$attachmentInfo);
			$bResult = $this->deleteAttachmentFromDB($id,$attachmentInfo) && $bResult;
		}
		return $bResult ? 1 : 0;
	}
	public function getAttachmentContent($id,$attachmentInfo = null)
	{
		$content = null;
		if (!$attachmentInfo)
			$attachmentInfo = $this->getAttachmentInfo($id);
		if ($attachmentInfo)
		{
			//for DB-repository the filename is null
			if (is_null($attachmentInfo['file_path']))
				$content = $this->getAttachmentContentFromDB($id);
			else
				$content = $this->getAttachmentContentFromFS($id);
		}
		return $content;
	}
	protected function getAttachmentContentFromFS($id)
	{
		$query = "SELECT file_size,compression_type,file_path FROM attachments WHERE id = {$id}";
		$row = $this->m_db->fetchFirstRow($query);
		
		$content = null;
		if ($row)
		{
			$filePath = $row['file_path'];
			$fileSize = $row['file_size'];
			$destFPath = $this->m_repositoryPath.DS.$filePath;
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
	/**
	 * Gets some common infos about attachments 
	 *
	 * @param int $id the id of the attachment (attachments.id)
	 * 
	 * @return string returns the contents of the attachment 
	*/
	//SCHLUNDUS: should be protected
	public function getAttachmentContentFromDB($id)
	{
		$query = "SELECT content,file_size,compression_type FROM attachments WHERE id = {$id}";
		$row = $this->m_db->fetchFirstRow($query);
		
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
	public function deleteAttachmentsFor($fkid,$fkTableName)
	{
		$bSuccess = true;
		$attachmentIDs = $this->getAttachmentIDsFor($fkid,$fkTableName);
		for($i = 0;$i < sizeof($attachmentIDs);$i++)
		{
			$id = $attachmentIDs[$i];
			$bSuccess = ($this->deleteAttachment($id) && $bSuccess);
		}
		if ($bSuccess)
		{
			$folder = $this->buildRepositoryFolderFor($fkTableName,$fkid);
			rmdir($folder);
		}
		return $bSuccess;
	}
	
	public function getAttachmentInfo($id)
	{
		$attachment = new tlAttachment($id);
		$info = null;
		if ($attachment->readFromDB($this->m_db))
			$info = $attachment->getAttachmentInfo();
		
		return $info;
	}
	public function getAttachmentInfosFor($fkid,$fkTableName)
	{
		$attachmentInfos = null;
		$attachmentIDs = $this->getAttachmentIDsFor($fkid,$fkTableName);
		for($i = 0;$i < sizeof($attachmentIDs);$i++)
		{
			$attachmentInfo = $this->getAttachmentInfo($attachmentIDs[$i]);
			if ($attachmentInfo)
				$attachmentInfos[] = $attachmentInfo;
		}
		return $attachmentInfos;
	}
	public function getAttachmentIDsFor($fkid,$fkTableName)
	{
		$order_by = $this->m_attachmentCfg->order_by;
	  
		$query = "SELECT id FROM attachments WHERE fk_id = {$fkid} AND fk_table = '" . $this->m_db->prepare_string($fkTableName). "' " . $order_by;
		$attachmentIDs = $this->m_db->fetchColumnsIntoArray($query,'id');
		
		return $attachmentIDs;
	}
}
?>