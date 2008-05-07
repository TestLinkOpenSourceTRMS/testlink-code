<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: attachment.class.php,v $
 *
 * @version $Revision: 1.13 $
 * @modified $Date: 2008/05/07 21:01:23 $ by $Author: schlundus $
 * @author Francisco Mancardi
 *
*/
require_once( dirname(__FILE__) . '/object.class.php' );
/*
	An attachment helper class used to manage the storage of the attachment's meta information
	Attachments contents are handled by the repository
*/
class tlAttachment extends tlDBObject 
{
	/*
	 * @param object $db [ref] the db-object
	 * @param int $fkid the foreign key id (attachments.fk_id)
	 * @param string $fktableName the tablename to which the $id refers to (attachments.fk_table)
	 * @param string $fName the filename
	 * @param string $destFPath the file path 
	 * @param string $fContents the contents of the file
	 * @param string $fType the mime-type of the file
	 * @param int $fSize the filesize (uncompressed)
	 * @param string $title the title used for the attachment
	 */
	protected $fkID;
	protected $fkTableName;
	protected $fName;
	protected $title;
	protected $fType;
	protected $fSize;
	protected $destFPath; 
	protected $fContents;
	protected $compressionType;
	protected $description;
	protected $dateAdded;
	protected $repositoryPath;
	protected $attachmentCfg;
	
	protected function _clean($options = self::TLOBJ_O_SEARCH_BY_ID)
	{
		$this->fkID = NULL;
		$this->fkTableName = NULL;
		$this->fName = NULL;
		$this->title = NULL;
		$this->fType = NULL;
		$this->fSize = NULL;
		$this->destFPath = NULL; 
		$this->fContents = NULL;
		$this->description = NULL;
		$this->dateAdded = NULL;
		
		if (!($options & self::TLOBJ_O_SEARCH_BY_ID))
			$this->dbID = null;
	} 

	function __construct($dbID = null)
	{
		parent::__construct();

		global $g_repositoryCompressionType;
		global $g_repositoryPath;
		$this->compressionType  = $g_repositoryCompressionType;
		$this->repositoryPath = $g_repositoryPath;
		$this->attachmentCfg = config_get('attachments');

		$this->_clean();
		$this->dbID = $dbID;
	}
	function __destruct()
	{
		parent::__destruct();
		$this->_clean();
	}
	/*
	* @param object $db [ref] the db-object
	* @param int $fkid the foreign key id (attachments.fk_id)
	* @param string $fktableName the tablename to which the $id refers to (attachments.fk_table)
	* @param string $fName the filename
	* @param string $destFPath the file path
	* @param string $fContents the contents of the file
	* @param string $fType the mime-type of the file
	* @param int $fSize the filesize (uncompressed)
	* @param string $title the title used for the attachment
	*/
	public function create($fkid,$fkTableName,$fName,$destFPath,$fContents,$fType,$fSize,$title)
	{
		$this->_clean();
		
		$this->fkID = $fkid;
		$this->fkTableName = trim($fkTableName);
		$this->fType = trim($fType);
		$this->fSize = $fSize;
		$this->fName = $fName; 
		$this->destFPath = trim($destFPath); 
		$this->fContents = $fContents;
		
		if(!strlen(trim($title)))
		{
			switch($this->attachmentCfg->action_on_save_empty_title)
			{
				case 'use_filename':
					$title = $fName;
					break;
				default:
					break;  
			}

		}
		//for FS-repository, the path to the repository itself is cut off, so the path is
		//					relative to the repository itself
		$this->destFPath = str_replace($this->repositoryPath.DIRECTORY_SEPARATOR,"",$destFPath);
		$this->title = trim($title);
		
		return tl::OK;
	}
	
	public function readFromDB(&$db,$options = self::TLOBJ_O_SEARCH_BY_ID)
	{
		$this->_clean($options);
		$query = "SELECT id,title,description,file_name,file_type,file_size,date_added,".
				"compression_type,file_path,fk_id,fk_table FROM attachments ";
				
		$clauses = null;
		if ($options & self::TLOBJ_O_SEARCH_BY_ID)
			$clauses[] = "id = {$this->dbID}";		
		if ($clauses)
			$query .= " WHERE " . implode(" AND ",$clauses);
		
		$info = $db->fetchFirstRow($query);			 
		if ($info)
		{
			$this->fkID = $info['fk_id'];
			$this->fkTableName = $info['fk_table'];
			$this->fName = $info['file_name'];
			$this->destFPath = $info['file_path'];
			$this->fType  = $info['file_type'];
			$this->fSize = $info['file_size'];
			$this->dbID =  $info['id'];
			$this->description = $info['description'];
			$this->dateAdded = $info['date_added'];
			$this->title = $info['title'];
			$this->compressionType = $info['compression_type'];
		}
				
		return $info ? tl::OK : tl::ERROR;
	}
	
	public function getInfo()
	{
		return array(
			"id" => $this->dbID,
			"title" => $this->title,
			"description" => $this->description,
			"file_name" => $this->fName,
			"file_type" => $this->fType,
			"file_size" => $this->fSize,
			"date_added" => $this->dateAdded,
			"compression_type" => $this->compressionType,
			"file_path" => $this->destFPath,
			"fk_id" => $this->fkID,
			"fk_table" => $this->fkTableName,
		);
	}
	
	public function writeToDB(&$db)
	{
		$tableName = $db->prepare_string($this->fkTableName);
		$fName = $db->prepare_string($this->fName);
		$title = $db->prepare_string($this->title);
		$fType = $db->prepare_string($this->fType);
		
		$destFPath = is_null($this->destFPath) ? 'NULL' : "'".$db->prepare_string($this->destFPath)."'";
		//for FS-repository the contents are null
		$fContents = is_null($this->fContents) ? 'NULL' : "'".$db->prepare_string($this->fContents)."'";
		
		$query = "INSERT INTO attachments 
	       (fk_id,fk_table,file_name,file_path,file_size,file_type, date_added,content,compression_type,title) 
    	    VALUES ({$this->fkID},'{$tableName}','{$fName}',{$destFPath},{$this->fSize},'{$this->fType}'," . $db->db_now() . 
       		",$fContents,{$this->compressionType},'{$title}')";
	  
		$result = $db->exec_query($query);
		if ($result)
			$this->dbID = $db->insert_id();
		
		return $result ? tl::OK : tl::ERROR;
	}
	
	public function deleteFromDB(&$db)
	{
		$query = "DELETE FROM attachments WHERE id = {$this->dbID}";
		$result = $db->exec_query($query);
		
		return $result ? tl::OK : tl::ERROR;
	}
	
	static public function getByID(&$db,$id,$detailLevel = self::TLOBJ_O_GET_DETAIL_FULL)
	{
		return tlDBObject::createObjectFromDB($db,$id,__CLASS__,tlAttachment::TLOBJ_O_SEARCH_BY_ID,$detailLevel);
	}
	
	static public function getByIDs(&$db,$ids,$detailLevel = self::TLOBJ_O_GET_DETAIL_FULL)
	{
		return self::handleNotImplementedMethod(__FUNCTION__);
	}
	
	static public function getAll(&$db,$whereClause = null,$column = null,$orderBy = null,$detailLevel = self::TLOBJ_O_GET_DETAIL_FULL)
	{
		return self::handleNotImplementedMethod(__FUNCTION__);
	}
};

?>
