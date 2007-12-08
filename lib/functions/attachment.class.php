<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: attachment.class.php,v $
 *
 * @version $Revision: 1.4 $
 * @modified $Date: 2007/12/08 19:07:40 $ by $Author: schlundus $
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
	protected $m_fkID;
	protected $m_fkTableName;
	protected $m_fName;
	protected $m_title;
	protected $m_fType;
	protected $m_fSize;
	protected $m_destFPath; 
	protected $m_fContents;
	protected $m_compressionType;
	protected $m_description;
	protected $m_dateAdded;
	protected $m_repositoryPath;
	protected $m_attachmentCfg;
	
	protected function _clean()
	{
		$this->m_fkID = NULL;
		$this->m_fkTableName = NULL;
		$this->m_fName = NULL;
		$this->m_title = NULL;
		$this->m_fType = NULL;
		$this->m_fSize = NULL;
		$this->m_destFPath = NULL; 
		$this->m_fContents = NULL;
		$this->m_description = NULL;
		$this->m_dateAdded = NULL;
		$this->m_dbID = NULL;
	} 

	function __construct($dbID = null)
	{
		parent::__construct();

		global $g_repositoryCompressionType;
		global $g_repositoryPath;
		$this->m_compressionType  = $g_repositoryCompressionType;
		$this->m_repositoryPath = $g_repositoryPath;
		$this->m_attachmentCfg = config_get('attachments');

		$this->_clean();
		$this->setDbID($dbID);
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
		
		$this->m_fkID = $fkid;
		$this->m_fkTableName = trim($fkTableName);
		$this->m_fType = trim($fType);
		$this->m_fSize = $fSize;
		$this->m_fName = $fName; 
		$this->m_destFPath = trim($destFPath); 
		$this->m_fContents = $fContents;
		
		if(!strlen(trim($title)))
		{
			switch($this->m_attachmentCfg->action_on_save_empty_title)
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
		$this->m_destFPath = str_replace($this->m_repositoryPath.DS,"",$destFPath);
		$this->m_title = trim($title);
		
		return OK;
	}
	public function readFromDB(&$db)
	{
		$query = "SELECT id,title,description,file_name,file_type,file_size,date_added,".
				"compression_type,file_path,fk_id,fk_table " .
				"FROM attachments WHERE id = {$this->m_dbID} ";

		$info = $db->fetchFirstRow($query);			 
		$this->_clean();
		if ($info)
		{
			$this->m_fkID = $info['fk_id'];
			$this->m_fkTableName = $info['fk_table'];
			$this->m_fName = $info['file_name'];
			$this->m_destFPath = $info['file_path'];
			$this->m_fType  = $info['file_type'];
			$this->m_fSize = $info['file_size'];
			$this->m_dbID =  $info['id'];
			$this->m_description = $info['description'];
			$this->m_dateAdded = $info['date_added'];
			$this->m_title = $info['title'];
			$this->m_compressionType = $info['compression_type'];
		}
				
		return $info ? OK : ERROR;
	}
	
	public function getInfo()
	{
		return array(
			"id" => $this->m_dbID,
			"title" => $this->m_title,
			"description" => $this->m_description,
			"file_name" => $this->m_fName,
			"file_type" => $this->m_fType,
			"file_size" => $this->m_fSize,
			"date_added" => $this->m_dateAdded,
			"compression_type" => $this->m_compressionType,
			"file_path" => $this->m_destFPath,
			"fk_id" => $this->m_fkID,
			"fk_table" => $this->m_fkTableName,
		);
	}
	
	public function writeToDB(&$db)
	{
		$tableName = $db->prepare_string($this->m_fkTableName);
		$fName = $db->prepare_string($this->m_fName);
		$title = $db->prepare_string($this->m_title);
		$fType = $db->prepare_string($this->m_fType);
		
		$destFPath = is_null($this->m_destFPath) ? 'NULL' : "'".$db->prepare_string($this->m_destFPath)."'";
		//for FS-repository the contents are null
		$fContents = is_null($this->m_fContents) ? 'NULL' : "'".$db->prepare_string($this->m_fContents)."'";
		
		$query = "INSERT INTO attachments 
	       (fk_id,fk_table,file_name,file_path,file_size,file_type, date_added,content,compression_type,title) 
    	    VALUES ({$this->m_fkID},'{$tableName}','{$fName}',{$destFPath},{$this->m_fSize},'{$this->m_fType}'," . $db->db_now() . 
       		",$fContents,{$this->m_compressionType},'{$title}')";
	  
		$result = $db->exec_query($query);
		if ($result)
			$this->m_dbID = $db->insert_id();
		
		return $result ? OK : ERROR;
	}
	
	public function deleteFromDB(&$db)
	{
		$query = "DELETE FROM attachments WHERE id = {$this->m_dbID}";
		$result = $db->exec_query($query);
		
		return $result ? OK : ERROR;
	}
};

?>
