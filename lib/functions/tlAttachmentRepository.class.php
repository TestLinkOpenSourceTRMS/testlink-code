<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @package 	  TestLink
 * @author 		  Andreas Morsing
 * @copyright 	2007-2012, TestLink community 
 * @filesource  tlAttachmentRepository.class.php
 * @link 		    http://www.teamst.org/index.php
 *
 * @internal revisions
 * @since 2.0
 * 20120909 - franciscom - checkRepositoryStatus()
 *
 *
 */

/**
 * class store and load attachments
 * @package 	TestLink
 */
require_once('files.inc.php');
class tlAttachmentRepository extends tlObjectWithDB
{
  
  const STOREINSESSION = true;
  
	//the one and only attachment repository object
	private static $s_instance;

	/**
	 * @var int the type of the repository
	 */
	private $type;
	
	/**
	 * @var int the compression type for the attachments
	 */
	private $compressionType;

	/**
	 * @var string the path to the repository if filesystem 
	 */
	protected $path;
	
	/**
	 * @var array additional attachment configuration
	 */
	protected $attachmentCfg;


	/**
	 * class constructor
	 * 
	 * @param $db [ref] resource the database connection
	 */
	function __construct(&$db)
	{
		tlObjectWithDB::__construct($db);
		$this->attachmentCfg = config_get('attachments');
		
		$prop2init = array('type','compressionType','path');
		foreach($prop2init as $prop)
		{
		  $this->$prop = $this->attachmentCfg->repository->$prop;
	  }
	}

    /**
     * Creates the one and only repository object
     * 
     * @param $db [ref] resource the database connection
     * @return tlAttachmenRepository
     */
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
     * Returns the type of the repository, like filesystem, database,...
     * 
     * @return integer the type of the repository 
     */
    public function getType()
    {
    	return $this->type;
    }
    
	/**
	 * returns the compression type of the repository
	 * 
	 * @return integer the compression type
	 */
	  public static function getCompression()
    {
      $cfg = config_get('attachments');
    	return $cfg->repository->compressionType;
    }
    
    /**
     * returns the path to the repository
     * 
     * @return string path to the repository
     */
    public static function getPathToRepository()
    {
      $cfg = config_get('attachments');
    	return $cfg->repository->path;
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

		if ($this->type == TL_REPOSITORY_TYPE_FS)
		{
		 	$destFPath = $this->buildRepositoryFilePath($destFName,$fkTableName,$fkid);
			$fileUploaded = $this->storeFileInFSRepository($fTmpName,$destFPath);
		}
		else
		{
			$fContents = $this->getFileContentsForDBRepository($fTmpName,$destFName);
			$fileUploaded = sizeof($fContents);
			if($fileUploaded)
			{
		    	@unlink($fTmpName);	
		  }	
		}

		if ($fileUploaded)
		{
			$attachment = new tlAttachment();
			$fileUploaded = ($attachment->create($fkid,$fkTableName,$fName,$destFPath,
			                                     $fContents,$fType,$fSize,$title) >= tl::OK);
			if ($fileUploaded)
			{
				$fileUploaded = $attachment->writeToDb($this->db);
			}
			else
			{ 
				@unlink($destFPath);
			}
		}

		return $fileUploaded;
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
		$destFPath = $this->buildRepositoryFolderFor($tableName,$id,true) . DIRECTORY_SEPARATOR . $destFName;
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
		switch($this->compressionType)
		{
			case TL_REPOSITORY_COMPRESSIONTYPE_NONE:
				break;                        
				
			case TL_REPOSITORY_COMPRESSIONTYPE_GZIP:
				//copy the file into a dummy file in the repository and gz it and
				//read the file contents from this new file
				$tmpGZName = $this->path . DIRECTORY_SEPARATOR . $destFName . ".gz";
				gzip_compress_file($fTmpName, $tmpGZName);
				$fTmpName = $tmpGZName;
				break;
		}
		$fContents = getFileContents($fTmpName);
		//delete the dummy file if present
		if (!is_null($tmpGZName))
		{
			unlink($tmpGZName);
		}
		return $fContents;
	}

	/**
	 * Stores a file into the FS-repository. 
	 * It checks if the given  tmp name is of an uploaded file. 
	 * If so, it moves the file from the temp dir to the upload destination using move_uploaded_file(). 
	 * Else it simply rename the file through rename function.
	 * This process is needed to allow use of this method when uploading attachments via XML-RPC API
	 * 
	 * @param string $fTmpName the filename
	 * @param string $destFPath [ref] the destination file name
	 *
	 * @return bool returns true if the file was uploaded, false else
	 *
	 * @internal revision
	 **/
	protected function storeFileInFSRepository($fTmpName,&$destFPath)
	{
		switch($this->compressionType)
		{
			case TL_REPOSITORY_COMPRESSIONTYPE_NONE:
				if ( is_uploaded_file($fTmpName))
				{
					$fileUploaded = move_uploaded_file($fTmpName,$destFPath);
				} 
				else 
				{
					$fileUploaded = rename($fTmpName,$destFPath);
				}
				break;
				
			case TL_REPOSITORY_COMPRESSIONTYPE_GZIP:
				//add the gz extension and compress the file
				$destFPath .= ".gz";
				$fileUploaded = gzip_compress_file($fTmpName,$destFPath);
				break;
		}
		return $fileUploaded;
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
 	protected function buildRepositoryFolderFor($tableName,$id,$mkDir = false)
	{
		$path = $this->path . DIRECTORY_SEPARATOR . $tableName;
		if ($mkDir && !file_exists($path))          
		{
			mkdir($path);
		}

		$path .= DIRECTORY_SEPARATOR . $id;
		if ($mkDir && !file_exists($path))
		{
			mkdir($path);
    }
		return $path;
	}

	/**
	 * Deletes an attachment from the filesystem
	 * 
	 * @param $dummy not used, only there to keep the interface equal to deleteAttachmentFromDB
	 * @param $attachmentInfo array with information about the attachments
	 * @return interger returns tl::OK on success, tl::ERROR else
	 */
	protected function deleteAttachmentFromFS($dummy,$attachmentInfo = null)
	{
  	$destFPath = $this->path . DIRECTORY_SEPARATOR . $attachmentInfo['file_path'];
		return @unlink($destFPath) ? tl::OK : tl::ERROR;
	}

	/**
	 * Deletes an attachment from the database
	 * 
	 * @param $id integer the database identifier of the attachment
	 * @param $dummy not used, only there to keep the interface equal to deleteAttachmentFromDB
	 * @return integer returns tl::OK on success, tl::ERROR else
	 */
	protected function deleteAttachmentFromDB($id,$dummy = null)
	{
		$attachment = new tlAttachment($id);
		return $attachment->deleteFromDB($this->db);
	}

	/**
	 * Deletes the attachment with the given database id
	 * 
	 * @param $id integer the database identifier of the attachment
	 * @param $attachmentInfo array, optional information about the attachment
	 * @return integer returns tl::OK on success, tl::ERROR else
	 */
	public function deleteAttachment($id,$attachmentInfo = null,$opt=null)
	{
	  
	  $options = array_merge(array('audit' => true),(array)$opt);
		$opStatus = tl::ERROR;

		if (is_null($attachmentInfo))
		{
			$attachmentInfo = $this->getAttachmentInfo($id);
		}

		if ($attachmentInfo)
		{
			$opStatus = tl::OK;
			if (trim($attachmentInfo['file_path']) != "")
			{
				$opStatus = $this->deleteAttachmentFromFS($id,$attachmentInfo);
			}
			$opStatus = $this->deleteAttachmentFromDB($id,null) && $opStatus;
		}
		
		
		if($opStatus && $options['audit'])
		{
      $msg = $this->attachmentIdentity($attachmentInfo);   
  	  logAuditEvent(TLS("audit_attachment_deleted",$msg),"DELETE",$id,"attachments");
		} 
		
		return $opStatus ? tl::OK : tl::ERROR;
	}
	
	/**
	 * Gets the contents of the attachments from the repository
	 * 
	 * @param $id integer the database identifier of the attachment
	 * @param $attachmentInfo array, optional information about the attachment
	 * @return string the contents of the attachment or null on error
	 *
	 * @internal revision
	 */
	public function getAttachmentContent($id,$attachmentInfo = null)
	{
		$content = null;
		if (!$attachmentInfo)
		{
			$attachmentInfo = $this->getAttachmentInfo($id);
		}
		
		if ($attachmentInfo)
		{
			$pfn = 'getAttachmentContentFrom';
			$pfn .= ($this->type == TL_REPOSITORY_TYPE_FS) ? 'FS' : 'DB';
			$content = $this->$pfn($id);
		}
		return $content;
	}
	
	/**
	 * Gets the contents of the attachment given by it's database identifier from the filesystem 
	 * 
	 * @param $id integer the database identifier of the attachment
	 * @return string the contents of the attachment or null on error
	 */
	protected function getAttachmentContentFromFS($id)
	{
		$query = "SELECT file_size,compression_type,file_path " .
		         " FROM {$this->tables['attachments']} WHERE id = {$id}";
		$row = $this->db->fetchFirstRow($query);
		$content = null;
		if ($row)
		{
			$destFPath = $this->path . DIRECTORY_SEPARATOR . $row['file_path'];
			switch($row['compression_type'])
			{
				case TL_REPOSITORY_COMPRESSIONTYPE_NONE:
					$content = getFileContents($destFPath);
				break;
					
				case TL_REPOSITORY_COMPRESSIONTYPE_GZIP:
					$content = gzip_readFileContent($destFPath,$row['file_size']);
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
	//@TODO schlundus, should be protected, but blocker is testcase::copy_attachments
	public function getAttachmentContentFromDB($id)
	{
		$query = "SELECT content,file_size,compression_type " .
		         " FROM {$this->tables['attachments']} WHERE id = {$id}";
		$row = $this->db->fetchFirstRow($query);

		$content = null;
		if ($row)
		{
			$content = $row['content'];
			switch($row['compression_type'])
			{
				case TL_REPOSITORY_COMPRESSIONTYPE_NONE:
					break;
					
				case TL_REPOSITORY_COMPRESSIONTYPE_GZIP:
					$content = gzip_uncompress_content($content,$row['file_size']);
					break;
			}
		}

		return $content;
	}
	
	/**
	 * Deletes all attachments of a certain object of a given type
	 * 
	 * @param $itemID      integer the id of the object whose attachments should be deleted
	 * @param $itemDBTable string  some sort of "type" of the object, or the table the object is stored in 
	 * 
	 * @return boolean returns true if all attachments are deleted, false else
	 */
	public function deleteAttachmentsFor($itemID,$itemDBTable)
	{
		$opOK = true;
		$itemSet = $this->getAttachmentIDSetFor($itemID,$itemDBTable);
		
		$loop2do = sizeof($itemSet);
		for($idx = 0; $idx < $loop2do; $idx++)
		{
			$opOK = ($this->deleteAttachment($itemSet[$idx]) && $opOK);
		}
		
		if ($opOK)
		{
			$folder = $this->buildRepositoryFolderFor($itemDBTable,$itemID);
			if (is_dir($folder))
			{
				rmdir($folder);
			}
		}
		return $opOK;
	}

	/**
	 * Reads the information about the attachment with the given database id
	 * 
	 * @param $id integer the database identifier of the attachment
	 * @return array the information about the attachment
	 */
	public function getAttachmentInfo($id)
	{
		$info = null;
		$attachment = new tlAttachment($id);
		if ($attachment->readFromDB($this->db))
		{
			$info = $attachment->getInfo();
    }
		return $info;
	}
	
	/**
	 * Reads all attachments for a certain object of a given type
	 * 
	 * @param $fkid integer the id of the object whose attachments should be read
	 * @param $fkTableName the "type" of the object, or the table the object is stored in 
	 * 
	 * @return arrays returns an array with the attachments of the objects, or null on error
	 */
	public function getAttachmentInfosFor($fkid,$fkTableName,$storeListInSession = true,$counter = 0)
	{
		$infoSet = null;
		$idSet = $this->getAttachmentIDSetFor($fkid,$fkTableName);
		$loop2do = sizeof($idSet);
		for($idx = 0;$idx < $loop2do; $idx++)
		{
			$info = $this->getAttachmentInfo($idSet[$idx]);
			if ($info)
			{
				// needed because on inc_attachments.tpl this test:
				// {if $info.title eq ""}
				// is used to undertand if icon or other handle is needed to access
				// file content
				$info['title'] = trim($info['title']);
				$infoSet[] = $info;
			}
		}
		
		// Now manage cache in session if requested
	  if (!is_null($infoSet) && $storeListInSession)
	  {
		  $this->storeAttachmentsInSession($infoSet,$counter);
	  }
		return $infoSet;
	}
	
	/**
	 * Yields all attachment ids for a certain object of a given type
	 * 
	 * @param $fkid integer the id of the object whose attachments should be read
	 * @param $fkTableName the "type" of the object, or the table the object is stored in 
	 * 
	 * @return arrays returns an array with the attachments of the objects, or null on error
	 */
	public function getAttachmentIDSetFor($itemID,$itemDBTable,$opt=null)
	{
	  $my = array('opt' => array('action' => 'display'));
	  $my['opt'] = array_merge($my['opt'], (array)$opt);

    // avoid order by on SQL when useless
	  $order_by = ($my['opt']['action'] == 'delete') ? '' : $this->attachmentCfg->orderBy;
    
		$query = " SELECT id FROM {$this->tables['attachments']} WHERE fk_id = {$itemID} " .
		         " AND fk_table = '" . $this->db->prepare_string($itemDBTable). "' " . $order_by;
		$idSet = $this->db->fetchColumnsIntoArray($query,'id');

		return $idSet;
	}

    /*
  	 * @param $fkTableName the "type" of the object, or the table the object is stored in 
     */
	function copyAttachments($source_id,$target_id,$fkTableName)
	{
		$f_parts = null;
		$destFPath = null;
		$mangled_fname = '';
		$status_ok = false;
		// $repo_type = config_get('repositoryType');
		// $repo_path = config_get('repositoryPath') .  DIRECTORY_SEPARATOR;
		
		$attachments = $this->getAttachmentInfosFor($source_id,$fkTableName);
		if(count($attachments) > 0)
		{
			foreach($attachments as $key => $value)
			{
				$file_contents = null;
				$f_parts = explode(DIRECTORY_SEPARATOR,$value['file_path']);
				$mangled_fname = $f_parts[count($f_parts)-1];
				
				if ($this->type == TL_REPOSITORY_TYPE_FS)
				{
					$destFPath = $this->buildRepositoryFilePath($mangled_fname,$table_name,$target_id);
					$status_ok = copy($this->path . DIRECTORY_SEPARATOR . $value['file_path'],$destFPath);
				}
				else
				{
					$file_contents = $this->getAttachmentContentFromDB($value['id']);
					$status_ok = sizeof($file_contents);
				}
				if($status_ok)
				{
          $attachmentMgr = new tlAttachment();
					$attachmentMgr->create($target_id,$fkTableName,$value['file_name'],
						                     $destFPath,$file_contents,$value['file_type'],
						                     $value['file_size'],$value['title']);
					$attachmentMgr->writeToDB($this->db);
				}
			}
		}
	}
	
  /**
   * Stores the attachment infos into the session for referencing it later
   * 
   * @param array $attach infos about attachment
   * @param $counter counter for the attachments in the session
   *
   * @used-by tlAttachment.class.php
   * 
   */
  function storeAttachmentsInSession($attach,$counter = 0)
  {
    if(!$attach)
    {
    	$attach = array();
    }
    
    $tkey = 's_lastAttachmentInfos';
    if (!isset($_SESSION[$tkey]) || !$_SESSION[$tkey])
    {
    	$_SESSION[$tkey] = array();
    }
    
    if ($counter == 0)
    { 
    	$_SESSION[$tkey] = $attach;
    }
    else
    {
    	$_SESSION[$tkey] = array_merge($_SESSION[$tkey],$attach);
    }	
  }
	
	
	function checkRepositoryStatus($forceDirCheck = false)
	{
    $ret = array('enabled' => TRUE, 'disabledMsg' => '');
  	if($this->type == TL_REPOSITORY_TYPE_FS || $forceDirCheck)
  	{
  	  $l18n = init_labels(array('attachments_dir' => null,'exists' => null,
  	                            'directory_is_writable' => null, 'does_not_exist' => null,
  	                            'but_directory_is_not_writable' => null));
	    clearstatcache();
	    $ret['msg'] = $l18n['attachments_dir'] . " " . $this->path . " ";
	    $ret['status_ok'] = false;
      if(is_dir($this->path)) 
	    {
    		$ret['msg'] .= $l18n['exists'] . ' ';
		    $ret['status_ok'] = is_writable($this->path) ? true : false; 

        $ret['msg'] .= $ret['status_ok'] ? $l18n['directory_is_writable'] : 
                                           $l18n['but_directory_is_not_writable'];
      }
      else
      {
        $ret['msg'] .= $l18n['does_not_exist']; 
      }      
      
  	  if(!$ret['status_ok'])
  	  {
  		  $ret['enabled'] = FALSE;
  		  $ret['disabledMsg'] = $ret['msg'];
  	  }
  	}
    return $ret;
	}


  /**
   * Get Metadata information about all attachments of a given object
   * 
   * @param object $attachmentRepository [ref] the attachment Repository
   * @param int $fkid the id of the object (attachments.fk_id);
   * @param string $fkTableName the name of the table $fkid refers to (attachments.fk_table)
   * @param bool $storeInSession if true, the attachment list will be stored within the session
   * @param int $counter if $counter > 0 the attachments are appended to existing attachments within the session
   *
   * @return array infos about the attachment on success, NULL else
  */
  function getAllAttachmentsMetadata($fkid,$fkTableName,$storeInSession = self::STOREINSESSION, $counter = 0)
  {
  	$metadata = $this->getAttachmentInfosFor($fkid,$fkTableName);
  	if($storeListInSession == self::STOREINSESSION)
  	{
  		$this->storeAttachmentsInSession($metadata,$counter);
  	}
  	return $metadata;
  }
  
  
  function attachmentIdentity($attachInfo)
  {
    $key2loop = array('title','description', 'file_name');
    foreach($key2loop as $target)
    {
      if( ($identity = trim($attachInfo[$target]) ) != '' )
      {
        break;
      } 
    }
    
    $family = $this->getAttachmentFamily($attachInfo);
    if(!is_null($family))
    {
      if($identity != '')
      {
        $identity .= lang_get('attach_linked_to') . ' ' . $family['owner'] . ' - ' . $family['ancestor'];
      }  
    }
    return ($identity != '' ? $identity : 'Warning! - Not able to create identity');
  }

  function getAttachmentFamily($attachInfo)
  {
    $ret = null;
    switch($attachInfo['fk_table'])
    {
      case 'nodes_hierarchy':
        $tree_manager = new tree($this->db);
        $dummy = $tree_manager->get_node_hierarchy_info($attachInfo['fk_id']);
        $class2use = $tree_manager->class_name[$dummy['node_type_id']];
        switch($class2use)
        {
          case 'testcase':
 		        $the_path = $tree_manager->get_path($attachInfo['fk_id']);
		        $tproject = $tree_manager->get_node_hierarchy_info($the_path[0]['parent_id']);
            $ret = array('ownerType' => 'testcase', 'owner' => lang_get('testcase') . ':' . $dummy['name'], 
                         'ancestor' => lang_get('testproject') . ':' . $tproject['name']);
          break;              
        }
      break;
    }
    return $ret;
    
  }
  
}
?>