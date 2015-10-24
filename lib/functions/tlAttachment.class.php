<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @package   TestLink
 * @author    Francisco Mancardi
 * @copyright   2007-2009, TestLink community 
 * @version     CVS: $Id: tlAttachment.class.php,v 1.2 2009/12/28 08:53:37 franciscom Exp $
 * @link    http://www.teamst.org/index.php
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

  protected $isImage;
  protected $inlineString;


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
    $this->isImage = NULL;
    $this->inlineString = NULL;
    
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
   *
   */
  function setID($id)
  {
    $this->dbID = $id;
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
  public function create($fkid,$fkTableName,$fName,$destFPath,$fContents,$fType,
                         $fSize,$title,$opt=null)
  {
    $this->_clean();
 
    $title = trim($title);
    $config = $this->attachmentCfg;
    if($title == "")
    {
      switch($config->action_on_save_empty_title)
      {
        case 'use_filename':
          $title = $fName;
          break;
  
        default:
          break;  
      }

    }

    $allowEmptyTitle = $config->allow_empty_title;
    if( isset($opt['allow_empty_title']) )
    {
      $allowEmptyTitle = $opt['allow_empty_title'];
    }  

    if( !$allowEmptyTitle && $title == "")
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

    $this->isImage = !(strpos($this->fType,'image/') === FALSE);
    $this->inlineString = NULL;
    
    //for FS-repository, the path to the repository itself is cut off, so the path is
    //          relative to the repository itself
    $this->destFPath = str_replace($this->repositoryPath.DIRECTORY_SEPARATOR,"",$destFPath);
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
    $query = "SELECT id,title,description,file_name,file_type,file_size,date_added,".
        "compression_type,file_path,fk_id,fk_table FROM {$this->tables['attachments']} ";
        
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
      $this->fType = trim($info['file_type']);
      $this->fSize = $info['file_size'];
      $this->dbID =  $info['id'];
      $this->description = $info['description'];
      $this->dateAdded = $info['date_added'];
      $this->title = $info['title'];
      $this->compressionType = $info['compression_type'];

      $this->isImage = !(strpos($this->fType,'image/') === FALSE);
      $this->inlineString = NULL;
      if($this->isImage)
      {
        $this->inlineString = "[tlInlineImage]{$this->dbID}[/tlInlineImage]";  
      } 

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
               "file_size" => $this->fSize, "is_image" => $this->isImage,
               "date_added" => $this->dateAdded, "inlineString" => $this->inlineString,
               "compression_type" => $this->compressionType,
               "file_path" => $this->destFPath,
               "fk_id" => $this->fkID,"fk_table" => $this->fkTableName,
    );
  }
  
  /* 
   * Writes the attachment into the database, for database repositories also the contents
   * of the attachments are written
   * 
   * @return integer returns tl::OK on success, tl::ERROR else
   */
  public function writeToDB(&$db,&$itemID=null)
  {
    $tableName = $db->prepare_string($this->fkTableName);
    $fName = $db->prepare_string($this->fName);
    $title = $db->prepare_string($this->title);
    $fType = $db->prepare_string($this->fType);
    
    $destFPath = is_null($this->destFPath) ? 'NULL' : "'".$db->prepare_string($this->destFPath)."'";

    // for FS-repository the contents are null
    $fContents = is_null($this->fContents) ? 'NULL' : "'".$db->prepare_string($this->fContents)."'";
    
    $query = "INSERT INTO {$this->tables['attachments']} 
             (fk_id,fk_table,file_name,file_path,file_size,file_type, date_added,content,compression_type,title) 
             VALUES ({$this->fkID},'{$tableName}','{$fName}',{$destFPath},{$this->fSize},'{$this->fType}'," . $db->db_now() . 
             ",$fContents,{$this->compressionType},'{$title}')";
    
    $result = $db->exec_query($query);
    if ($result)
    {
      $this->dbID = $db->insert_id();
      $itemID = $this->dbID;
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
};
?>