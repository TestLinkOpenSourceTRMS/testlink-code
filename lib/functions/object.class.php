<?php
require_once( dirname(__FILE__) . '/int_serialization.php' );
/*
	The base class for all managed TestLink objects, all tl-managed objects should extend this base class
*/
abstract class tlObject implements iSerialization
{	
	/* the unique object id */
	protected $m_objectID;
		
	/* supported serialization Interfaces */	
	protected $m_serializationInterfaces;
	protected $m_serializationFormatDescriptors;
	
	/* standard constructor, set's the object id */
	public function __construct()
	{
		$this->m_objectID = uniqid("tl", true);
		
		/* Any supported import/Export Serialization Interfaces must be prefixed with iSerializationTo */
		$prefix = "iSerializationTo";
		$prefixLen = strlen($prefix);
		$o = new ReflectionObject($this);
		$interfaces = $o->getInterfaces();
		$this->m_serializationInterfaces = null;
		$this->m_serializationFormatDescriptors = null;
		if ($interfaces)
		{
			foreach($interfaces as $name => $info)
			{
				$iPos = strpos($name,$prefix);
				if ($iPos === 0)
				{
					$format = substr($name,$prefixLen);
					$this->m_serializationInterfaces[$name] = $format;
					$pfn = "getFormatDescriptionFor".$format;
					$this->m_serializationFormatDescriptors[$format] = $this->$pfn();
				}
			}
		}
		$this->getSupportedSerializationFormatDescriptions();
	}
	/* standard destructor */
	public function __destruct()
	{
		$this->_clean();
	}
	/* magic method for usage with print() or echo() , dumps out the object */
	public function __toString()
	{
		return __CLASS__.", ".print_r($this,true);
	}
	
	/* function used for resetting the object's internal data */
	//nothing special at the moment
	protected function _clean()
	{
	}
	/* returns all supported Import/Export Interfaces */
	function getSupportedSerializationInterfaces()
	{
		return $this->m_serializationInterfaces;
	}
	/* returns all supported Import/Export Interfaces  Format Descriptors */
	function getSupportedSerializationFormatDescriptions()
	{
		return $this->m_serializationFormatDescriptors;
	}
};
/*
	The base class for all managed TestLink objects which need a db connection
*/
abstract class tlObjectWithDB extends tlObject
{	
	/* the db connection to the testlink database */
	protected $m_db;
	
	/* 
	*	@param object [ref] $db the database connection
	*/
	function __construct(&$db)
	{
		tlObject::__construct();
		$this->m_db = &$db;
	}
}

/*
	The base class for all managed TestLink objects which support attachments
*/
abstract class tlObjectWithAttachments extends tlObjectWithDB
{
	/* the attachment repository object */
	protected $m_attachmentRepository;
	/* the foreign key table name to store the attachements */
	protected $m_attachmentTableName;
	
	/* 
	*	@param object [ref] $db the database connection
	*	@param string $attachmentTableName the foreign key table name to store the attachements
	*/
	function __construct(&$db,$attachmentTableName)
	{
		tlObjectWithDB::__construct($db);
		$this->m_attachmentRepository = tlAttachmentRepository::create($this->m_db);
		$this->m_attachmentTableName = $attachmentTableName;
	}
	/*
	*	gets all infos about the attachments of the object specified by $id 	
	*
	*	@param int $id this is the fkid of the attachments table
	*/
	function getAttachmentInfos($id)
	{
		return $this->m_attachmentRepository->getAttachmentInfosFor($id,$this->m_attachmentTableName);
	}
	/*
	*	deletes all attachments of the object specified by $id 	
	*
	*	@param int $id this is the fkid of the attachments table
	*/
	function deleteAttachments($id)
	{
		return $this->m_attachmentRepository->deleteAttachmentsFor($id,$this->m_attachmentTableName);
	}
	
	/* function used for resetting the object's internal data */
	protected function _clean()
	{
		$this->m_attachmentRepository = null;
		$this->m_attachmentTableName = null;
	}
}
//SCHLUNDUS: not sure about this Object... need to think about it
abstract class tlDBObject extends tlObject implements iDBSerialization
{
	public $m_dbID;
	
	/* standard constructor */
	function __construct($dbID = null)
	{
		parent::__construct();
		
		$this->m_dbID = $dbID;
	}
	public function getDbID()
	{
		return $m_dbID;
	}
	public function setDbID($id)
	{
		$this->m_dbID = $id;
	}
	static public function createObjectFromDB(&$db,$id,$className)
	{
		$item = new $className($id);
		if ($item->readFromDB($db) == OK)
			return $item;
		return null;
	}
	static public function createObjectsFromDBbySQL(&$db,$query,$column,$className)
	{
		$ids = $db->fetchColumnsIntoArray($query,$column);
		return self::createObjectsFromDB($db,$ids,$className);
	}
	static public function createObjectsFromDB(&$db,$ids,$className)
	{
		$items = null;
		for($i = 0;$i < sizeof($ids);$i++)
		{
			$item = self::createObjectFromDB($db,$ids[$i],$className);
			if ($item)
				$items[] = $item;
		}
		return $items;
	}
	static public function deleteObjectFromDB(&$db,$id,$className)
	{
		$item = new $className($id);
		return $item->deleteFromDB($db);
	}
}