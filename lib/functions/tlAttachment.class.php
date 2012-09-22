<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @package     TestLink
 * @author      Francisco Mancardi
 * @copyright   2007-2012, TestLink community 
 * @filesource  tlAttachment.class.php
 * @link        http://www.teamst.org/index.php
 *
 */
/** parenthal class */
require_once( 'object.class.php' );

/**
 * An attachment helper class used to manage the storage of the attachment's meta information
 * Attachments contents are handled by the repository
 * @package   TestLink
 */
class tlAttachment extends tlDBObject 
{
  /**
   * @var integer error code for invalid title length
   */
  const E_TITLELENGTH = -1;
  

  /**
   * @var int $fkid the foreign key id (attachments.fk_id)
   */
  protected $fkID;

  /**
   * @var string $fktableName the tablename to which the $id refers to (attachments.fk_table)
   */
  protected $fkTableName;

  /**
   * @var string the filename the attachment is stored to
   */
  protected $fName;

  /**
   * @var string the title used for the attachment
   */
  protected $title;

  /**
   * @var string $fType the mime-type of the file
   */
  protected $fType;

  /**
   * @var int $fSize the filesize (uncompressed)
   */
  protected $fSize;

  /**
   * @var string $destFPath the path to file within the repository
   */
  protected $destFPath; 

  /**
   * @var string $fContents the contents of the file
   */
  protected $fContents;

  /**
   * 
   * @var int the compression type used for the attachment
   * @see TL_REPOSITORY_COMPRESSIONTYPE_NONE 
   * @see TL_REPOSITORY_COMPRESSIONTYPE_GZIP
   * */
  protected $compressionType;

  /**
   * @var string a description for the attachment
   */
  protected $description;

  /**
   * @var timestamp the timestampe when the attachment was added
   */
  protected $dateAdded;

  /**
   * @var string the path to the repository
   */
  protected $repositoryPath;

  /**
   * @var unknown_type
   */
  protected $attachmentCfg;
  
  /* Cleanup function to set the object to an initial state
   * @param $options options to control the initialization
   */
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
    {
      $this->dbID = null;
    } 
  } 

  /**
   * Class constructor
   * 
   * @param $dbID integer the database identifier of the attachment
   */
  function __construct($dbID = null)
  {
    parent::__construct();

    $this->compressionType  = tlAttachmentRepository::getCompression();
    $this->repositoryPath = tlAttachmentRepository::getPathToRepository();
    $this->attachmentCfg = config_get('attachments');

    $this->_clean();
    $this->dbID = $dbID;
  }
  
  /* 
   * Class destructor, cleans the object 
   */
  function __destruct()
  {
    parent::__destruct();
    $this->_clean();
  }
  
  /*
   * Initializes the attachment object 
   * 
   * @param object $db [ref] the db-object
   * @param int $fkid the foreign key id (attachments.fk_id)
   * @param string $fktableName the tablename to which the $id refers to (attachments.fk_table)
   * @param string $fName the filename
   * @param string $destFPath the file path
   * @param string $fContents the contents of the file
   * @param string $fType the mime-type of the file
   * @param int $fSize the filesize (uncompressed)
   * @param string $title the title used for the attachment
   * 
   * @return integer returns tl::OK
   */
  public function create($fkid,$fkTableName,$fName,$destFPath,$fContents,$fType,$fSize,$title)
  {
    $this->_clean();
    
    $title = trim($title);
    $config = $this->attachmentCfg;
    if($title == "")
    {
      switch($config->emptyTitleActionOnSave)
      {
        case 'use_filename':
          $title = $fName;
          break;
  
        default:
          break;  
      }

    }
    if(!$config->emptyTitleAllowed && $title == "")
    {
      return self::E_TITLELENGTH; 
    }
    
    $this->fkID = $fkid;
    $this->fkTableName = trim($fkTableName);
    $this->fType = trim($fType);
    $this->fSize = $fSize;
    $this->fName = $fName; 
    $this->destFPath = trim($destFPath); 
    $this->fContents = $fContents;

    // for FS-repository, the path to the repository itself is cut off, so the path is
    // relative to the repository itself
    $this->destFPath = str_replace($this->repositoryPath . DIRECTORY_SEPARATOR,"",$destFPath);
    $this->title = trim($title);
    
    return tl::OK;
  }
  
  /* Read the attachment information from the database, for filesystem repository this doesn't read
   * the contents of the attachments
   * 
   * @param $db [ref] the database connection
   * @param $options integer null or TLOBJ_O_SEARCH_BY_ID
   * 
   * @return integer returns tl::OK on success, tl::ERROR else
   */
  public function readFromDB(&$db,$options = self::TLOBJ_O_SEARCH_BY_ID)
  {
    $this->_clean($options);

    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $query = "/* $debugMsg */  SELECT id,title,description,file_name,file_type,file_size,date_added,".
             "compression_type,file_path,fk_id,fk_table FROM {$this->tables['attachments']} ";
        
    $clauses = null;
    if ($options & self::TLOBJ_O_SEARCH_BY_ID)
    {
      $clauses[] = "id = {$this->dbID}";    
    }
    if ($clauses)
    {
      $query .= " WHERE " . implode(" AND ",$clauses);
    }
    $info = $db->fetchFirstRow($query);      
    if ($info)
    {
      $this->fkID = $info['fk_id'];
      $this->fkTableName = $info['fk_table'];
      $this->fName = $info['file_name'];
      $this->destFPath = $info['file_path'];
      $this->fType = $info['file_type'];
      $this->fSize = $info['file_size'];
      $this->dbID =  $info['id'];
      $this->description = $info['description'];
      $this->dateAdded = $info['date_added'];
      $this->title = $info['title'];
      $this->compressionType = $info['compression_type'];
    }
        
    return $info ? tl::OK : tl::ERROR;
  }
  
  /**
   * Returns the attachment meta information in a legacy way
   * 
   * @return array array with the attachment information
   */
  public function getInfo()
  {
    return array("id" => $this->dbID,"title" => $this->title,
                 "description" => $this->description,
                 "file_name" => $this->fName, "file_type" => $this->fType,
                 "file_size" => $this->fSize,
                 "date_added" => $this->dateAdded,
                 "compression_type" => $this->compressionType,
                 "file_path" => $this->destFPath,
                 "fk_id" => $this->fkID,"fk_table" => $this->fkTableName);
  }
  
  /* 
   * Writes the attachment into the database, for database repositories also the contents
   * of the attachments are written
   * 
   * @return integer returns tl::OK on success, tl::ERROR else
   */
  public function writeToDB(&$db)
  {
    $tableName = $db->prepare_string($this->fkTableName);
    $fName = $db->prepare_string($this->fName);
    $title = $db->prepare_string($this->title);
    $fType = $db->prepare_string($this->fType);
    
    $destFPath = is_null($this->destFPath) ? 'NULL' : "'".$db->prepare_string($this->destFPath)."'";
    //for FS-repository the contents are null
    $fContents = is_null($this->fContents) ? 'NULL' : "'".$db->prepare_string($this->fContents)."'";
    
    $query = "INSERT INTO {$this->tables['attachments']} 
         (fk_id,fk_table,file_name,file_path,file_size,file_type, date_added,content,compression_type,title) 
          VALUES ({$this->fkID},'{$tableName}','{$fName}',{$destFPath},{$this->fSize},'{$this->fType}'," . $db->db_now() . 
          ",$fContents,{$this->compressionType},'{$title}')";
    
    $result = $db->exec_query($query);
    if ($result)
    {
      $this->dbID = $db->insert_id();
    }
    
    return $result ? tl::OK : tl::ERROR;
  }
  
  /* 
   * Deletes an attachment from the db, for databse repositories also the contents are deleted
   * 
   * @return integer return tl::OK on success, tl::ERROR else
   */
  public function deleteFromDB(&$db)
  {
    $query = "DELETE FROM {$this->tables['attachments']} WHERE id = {$this->dbID}";
    $result = $db->exec_query($query);
    
    return $result ? tl::OK : tl::ERROR;
  }
  
  /**
   * Creates an attachment by a given database identifier
   * 
   * @param $db [ref] the database connection
   * @param $id the database identifier of the attachment
   * @param $detailLevel the detailLevel
   * @return tlAttachment the created attachment or null on failure
   */
  static public function getByID(&$db,$id,$detailLevel = self::TLOBJ_O_GET_DETAIL_FULL)
  {
    return tlDBObject::createObjectFromDB($db,$id,__CLASS__,tlAttachment::TLOBJ_O_SEARCH_BY_ID,$detailLevel);
  }
  
  /**
   * Creates some attachments by given database identifiers
   * 
   * @param $db [ref] the database connection
   * @param $id the database identifier of the attachment
   * @param $detailLevel the detailLevel
   * @return array returns an array of tlAttachment (the created attachments) or null on failure
   */
  static public function getByIDs(&$db,$ids,$detailLevel = self::TLOBJ_O_GET_DETAIL_FULL)
  {
    return self::handleNotImplementedMethod(__FUNCTION__);
  }
  
  /**
   * Currently not implemented 
   * 
   * @param $db [ref] the database connection
   * @param $whereClause string and addtional where clause
   * @param $column string the name of column which holds the id
   * @param $orderBy string an additional ORDER BY clause
   * @param $detailLevel the detailLevel
   * @return unknown_type
   */
  static public function getAll(&$db,$whereClause = null,$column = null,$orderBy = null,$detailLevel = self::TLOBJ_O_GET_DETAIL_FULL)
  {
    return self::handleNotImplementedMethod(__FUNCTION__);
  }



  static public function getGuiCfg()
  {
        $cfg = new stdClass();
        
        // this can be overwritten for logic present on object/item we are using
        $cfg->display = true;
        $cfg->uploadEnabled = true;

        $cfg->tableStyles = "font-size:12px";
        $cfg->tableClassName = "simple";
        $cfg->inheritStyle = 0;
        $cfg->downloadOnly = false;
        $cfg->loadOnCancelURL = ''; // will be setted by each feature
        $cfg->disabledMsg = '';  // will be setted after repo checks

        // theses are specific to allow different behaviours ONLY on execution feature
        // @see const.inc.php
        // @see $tlCfg->exec_cfg->att_model
        $cfg->showUploadBtn = true;  
        $cfg->showTitle =  true;
        $cfg->showUploadColumn = false;
        $cfg->numCols = 4; 

        $dummy = array('title_upload_attachment' => null, 'enter_attachment_title' => null,
                       'btn_upload_file' => null, 'warning' => null, 'enter_attachment_title' => null,
                       'local_file' => null, 'attachment_upload_ok' => null,
                       'title_choose_local_file' => null ,'btn_cancel' => null,
                       'warning_delete_attachment' => 'warning', 'delete' => null,
                       'attachment_feature_disabled' => null,'attached_files' => null,
                       'max_size_file_upload' => null,'upload_file_new_file' => null);
        $cfg->labels = init_labels($dummy);
        return $cfg;  
  }

  /**
   * Use repository manager, to get information about the attachments of a given object,
   * 
   * @param object $repository [ref] the attachment Repository
   * @param int $objectID id of the object that owns the attachment (attachments.fk_id);
   * @param string $fkTableName the name of the table $fkid refers to (attachments.fk_table)
   * @param bool $storeListInSession if true, the attachment list will be stored within the session
   * @param int $counter if $counter > 0 the attachments are appended to existing attachments within the session
   *
   * @return array infos about the attachment on success, NULL else
  */
  function getAttachMetaDataViaRepository(&$repository,$itemID,$fkTableName,$storeListInSession = true,$counter = 0)
  {
    $attach = $repository->getAttachmentInfosFor($itemID,$fkTableName);
    if ($storeListInSession)
    {
      $this->storeInSession();
    }
    return $attach;
  }

  /**
   * Use specific object manager, to get infos about it's attachments.
   * 
   * @param tlObjectWithAttachments $object The object whose attachment should be fetched
   * @param int $itemID the id of the object that owns the attachment (attachments.fk_id);
   * @param bool $storeListInSession if true, the attachment list will be stored within the session
   * @param int $counter if $counter > 0 the attachments are appended to existing attachments within the session
   *
   * @return array returns infos about the attachment on success, NULL else
   */
  function getAttachMetaDataViaObject(&$object,$itemID,$storeListInSession = true,$counter = 0)
  {
  	$attach = $object->getAttachmentInfos($itemID);
  	if ($storeListInSession)
  	{
  		storeAttachmentsInSession($attach,$counter);
  	}
  	return $attach;
  }
}
?>