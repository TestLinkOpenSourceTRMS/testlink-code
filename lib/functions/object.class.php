<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* This script is distributed under the GNU General Public License 2 or later. 
*
* Filename $RCSfile: object.class.php,v $
* 
* @version $Id: object.class.php,v 1.11 2007/12/22 09:58:59 schlundus Exp $
* @modified $Date: 2007/12/22 09:58:59 $ by $Author: schlundus $
*
**/
/* Namespace for TestLink, here we can safely define constants and other stuff, without risk of collision with other stuff */
abstract class tl
{
	//error and status codes
	//all SUCCESS error codes and SUCCESS status codes should be greater than tl::OK
	//so we can check for SUCCESS with >= tl::OK, and for ERROR with < tl::OK
	const OK = 1;
	//all ERROR error codes and ERROR status codes should be lesser than tl::ERROR
	//so we can check for ERRORS with <= tl::ERROR, and for SUCCESS with > tl::ERROR
	const ERROR = 0;
	
	//return code for not implemented interface functions
	const E_NOT_IMPLEMENTED = -0xFFFFFFFF;
};


require_once(dirname(__FILE__) . '/int_serialization.php');
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
		
		/*
			Any supported import/Export Serialization Interfaces must be prefixed with iSerializationTo 
			so we can automatically detected the interfaces
		*/
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
	protected function _clean()
	{
	}
	/* returns all supported Import/Export Interfaces  */
	function getSupportedSerializationInterfaces()
	{
		return $this->serializationInterfaces;
	}
	/* returns all supported Import/Export Interfaces  Format Descriptors */
	function getSupportedSerializationFormatDescriptions()
	{
		return $this->serializationFormatDescriptors;
	}
	
	/* should be called whenever a not implemented method is called  */	
	protected function handleNotImplementedMethod($fName = "<unknown>")
	{
		trigger_error("Method ".$fName." called which is not implemented",E_USER_WARNING);
		return tl::E_NOT_IMPLEMENTED;
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
	*	//SCHLUNDUS: legacy function to keep existing code, should be replaced by a function which returns objects 
	*	@param int $id this is the fkid of the attachments table
	*
	*	@return , returns map with the infos of the attachment, keys are the column names of the attachments table 
	*/
	function getAttachmentInfos($id)
	{
		return $this->attachmentRepository->getAttachmentInfosFor($id,$this->attachmentTableName);
	}
	/*
	*	deletes all attachments of the object specified by $id 	
	*
	*	@param int $id this is the fkid of the attachments table
	*
	*	@return returns tl::OK on success, else error code
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
abstract class tlDBObject extends tlObject implements iDBSerialization
{
	public $dbID;
	protected $detailLevel;
	
	//standard get options, all other get options must be greater than this
	const TLOBJ_O_SEARCH_BY_ID = 1;
	
	//standard detail levels, can be used to get only some specific details when reading an object
	//to avoid unneccessary DB queries (if the info is actual not used and not needed)
	const TLOBJ_O_GET_DETAIL_MINIMUM = 0;
	const TLOBJ_O_GET_DETAIL_FULL = 0xFFFFFFFF;
	
	/* standard constructor */
	function __construct($dbID = null)
	{
		parent::__construct();
		
		$this->dbID = $dbID;
		$this->detailLevel = self::TLOBJ_O_GET_DETAIL_FULL;
	}
	public function getDbID()
	{
		return $dbID;
	}
	public function setDbID($id)
	{
		$this->dbID = $id;
	}
	
	/* 
		if we fetch an object, we can set here different details levels for the objects, because we 
		don't always all nested data 
	*/		
	public function setDetailLevel($level = self::TLOBJ_O_GET_DETAIL_FULL)
	{
		$this->detailLevel = $level;
	}
	
	static public function createObjectFromDB(&$db,$id,$className,$options = self::TLOBJ_O_SEARCH_BY_ID,$detailLevel = self::TLOBJ_O_GET_DETAIL_FULL)
	{
		if ($id)
		{
			$item = new $className($id);
			$item->setDetailLevel($detailLevel);
			if ($item->readFromDB($db,$options) == tl::OK)
				return $item;
		}
		return null;
	}
	static public function createObjectsFromDBbySQL(&$db,$query,$column,$className,$bAssoc = false,$detailLevel = self::TLOBJ_O_GET_DETAIL_FULL)
	{
		$ids = $db->fetchColumnsIntoArray($query,$column);
		return self::createObjectsFromDB($db,$ids,$className,$bAssoc,$detailLevel);
	}
	static public function createObjectsFromDB(&$db,$ids,$className,$bAssoc = false,$detailLevel = self::TLOBJ_O_GET_DETAIL_FULL)
	{
		$items = null;
		for($i = 0;$i < sizeof($ids);$i++)
		{
			$id = $ids[$i];
			$item = self::createObjectFromDB($db,$id,$className,self::TLOBJ_O_SEARCH_BY_ID,$detailLevel);
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
		if ($id)
		{
			$item = new $className($id);
			return $item->deleteFromDB($db);
		}
		return tl::ERROR;
	}
}
