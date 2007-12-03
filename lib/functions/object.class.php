<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: object.class.php,v $
 *
 * @version $Revision: 1.2 $
 * @modified $Date: 2007/12/03 20:42:27 $ by $Author: schlundus $
 * @author Francisco Mancardi
 *
*/

/*
Any objects which support serialization from or to XML should implement this interface
*/
interface iXMLSerialization
{
	/*
		Serializes the objects to XML code (string)
	*/
	public function writeToXML(&$xml);
	/*
		Serializes the objects from XML code (string)
	*/
	public function readFromXML($xml);
}

/*
Any objects which support serialization from or to Database should implement this interface
*/
interface iDBSerialization
{
	/*
		Serializes the objects to the database connection given by [ref] $db
	*/
	public function readFromDB(&$db);
	/*
		Serializes the objects from the database connection given by [ref] $db
	*/
	public function writeToDB(&$db);
}
/*
	The base class for all managed TestLink objects
*/
class tlObject
{	
	/* the unique object id */
	protected $m_objectID;
	
	/* standard constructor, set's the object id */
	public function __construct()
	{
		$this->m_objectID = uniqid("tl", true);
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
};
/*
	The base class for all managed TestLink objects which need a db connection
*/
class tlObjectWithDB extends tlObject
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
class tlObjectWithAttachments extends tlObjectWithDB
{
	/* the attachemt repository object */
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
	
	protected function _clean()
	{
		$this->m_attachmentRepository = null;
		$this->m_attachmentTableName = null;
	}
}
//SCHLUNDUS: not sure about this Object... need to think about it
abstract class tlDBObject extends tlObject implements iDBSerialization
{
	protected $m_dbID;
	
	//all function should return a bool success flag
	function __construct()
	{
		parent::__construct();
		
		$this->m_dbID = NULL;
	}
	public function getDbID()
	{
		return $m_dbID;
	}
	public function setDbID($id)
	{
		$this->m_dbID = $id;
	}
}