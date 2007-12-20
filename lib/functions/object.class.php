<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* This script is distributed under the GNU General Public License 2 or later. 
*
* Filename $RCSfile: object.class.php,v $
* 
* @version $Id: object.class.php,v 1.9 2007/12/20 20:36:35 schlundus Exp $
* @modified $Date: 2007/12/20 20:36:35 $ by $Author: schlundus $
*
**/
require_once( dirname(__FILE__) . '/int_serialization.php' );
/*
	The base class for all managed TestLink objects, all tl-managed objects should extend this base class
*/
abstract class tlObject implements iSerialization
{	
	/* the unique object id */
	protected $objectID;
		
	/* supported serialization Interfaces */	
	protected $serializationInterfaces;
	protected $serializationFormatDescriptors;
	
	/* standard constructor, set's the object id */
	public function __construct()
	{
		$this->objectID = uniqid("tl", true);
		
		/* Any supported import/Export Serialization Interfaces must be prefixed with iSerializationTo */
		$prefix = "iSerializationTo";
		$prefixLen = strlen($prefix);
		$o = new ReflectionObject($this);
		$interfaces = $o->getInterfaces();
		$this->serializationInterfaces = null;
		$this->serializationFormatDescriptors = null;
		if ($interfaces)
		{
			foreach($interfaces as $name => $info)
			{
				$iPos = strpos($name,$prefix);
				if ($iPos === 0)
				{
					$format = substr($name,$prefixLen);
					$this->serializationInterfaces[$name] = $format;
					$pfn = "getFormatDescriptionFor".$format;
					$this->serializationFormatDescriptors[$format] = $this->$pfn();
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
		return $this->serializationInterfaces;
	}
	/* returns all supported Import/Export Interfaces  Format Descriptors */
	function getSupportedSerializationFormatDescriptions()
	{
		return $this->serializationFormatDescriptors;
	}
};
/*
	The base class for all managed TestLink objects which need a db connection
*/
abstract class tlObjectWithDB extends tlObject
{	
	/* the db connection to the testlink database */
	protected $db;
	
	/* 
	*	@param object [ref] $db the database connection
	*/
	function __construct(&$db)
	{
		tlObject::__construct();
		$this->db = &$db;
	}
}

/*
	The base class for all managed TestLink objects which support attachments
*/
abstract class tlObjectWithAttachments extends tlObjectWithDB
{
	/* the attachment repository object */
	protected $attachmentRepository;
	/* the foreign key table name to store the attachements */
	protected $attachmentTableName;
	
	/* 
	*	@param object [ref] $db the database connection
	*	@param string $attachmentTableName the foreign key table name to store the attachements
	*/
	function __construct(&$db,$attachmentTableName)
	{
		tlObjectWithDB::__construct($db);
		$this->attachmentRepository = tlAttachmentRepository::create($this->db);
		$this->attachmentTableName = $attachmentTableName;
	}
	/*
	*	gets all infos about the attachments of the object specified by $id 	
	*
	*	@param int $id this is the fkid of the attachments table
	*/
	function getAttachmentInfos($id)
	{
		return $this->attachmentRepository->getAttachmentInfosFor($id,$this->attachmentTableName);
	}
	/*
	*	deletes all attachments of the object specified by $id 	
	*
	*	@param int $id this is the fkid of the attachments table
	*/
	function deleteAttachments($id)
	{
		return $this->attachmentRepository->deleteAttachmentsFor($id,$this->attachmentTableName);
	}
	
	/* function used for resetting the object's internal data */
	protected function _clean()
	{
		$this->attachmentRepository = null;
		$this->attachmentTableName = null;
	}
}
//SCHLUNDUS: not sure about this Object... need to think about it
abstract class tlDBObject extends tlObject implements iDBSerialization
{
	public $dbID;
	
	const TLOBJ_O_SEARCH_BY_ID = 1;
	
	/* standard constructor */
	function __construct($dbID = null)
	{
		parent::__construct();
		
		$this->dbID = $dbID;
	}
	public function getDbID()
	{
		return $dbID;
	}
	public function setDbID($id)
	{
		$this->dbID = $id;
	}
	static public function createObjectFromDB(&$db,$id,$className,$options = self::TLOBJ_O_SEARCH_BY_ID)
	{
		$item = new $className($id);
		if ($item->readFromDB($db,$options) == OK)
			return $item;
		return null;
	}
	static public function createObjectsFromDBbySQL(&$db,$query,$column,$className,$bAssoc = false)
	{
		$ids = $db->fetchColumnsIntoArray($query,$column);
		return self::createObjectsFromDB($db,$ids,$className,$bAssoc);
	}
	static public function createObjectsFromDB(&$db,$ids,$className,$bAssoc = false)
	{
		$items = null;
		for($i = 0;$i < sizeof($ids);$i++)
		{
			$id = $ids[$i];
			$item = self::createObjectFromDB($db,$id,$className);
			if ($item)
			{
				if ($bAssoc)
					$items[$id] = $item;
				else
					$items[] = $item;
			}
		}
		return $items;
	}
	static public function deleteObjectFromDB(&$db,$id,$className)
	{
		$item = new $className($id);
		return $item->deleteFromDB($db);
	}
}
