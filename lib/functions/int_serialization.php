<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 * 
 * Classes for serialized interfaces
 *
 * @package 	TestLink
 * @copyright 	2005-2009, TestLink community 
 * @version    	CVS: $Id: int_serialization.php,v 1.9 2009/08/24 19:18:45 schlundus Exp $
 * @link 		http://www.teamst.org/index.php
 * 
 * @TODO example of using
 *
 **/

/** 
 * basic serialization interface 
 * @package 	TestLink
 **/
interface iSerialization
{
	/**
	 * Returns all supported Import/Export Serialization Interfaces in a human readable way
	 * 
	 * @return array key = Interfacename, value = Format name (humand readable)	
	 */
	public function getSupportedSerializationInterfaces();

	/**
	 * Returns all supported Import/Export Serialization Interfaces Format Descriptor
	 * 
	 * @return array, key = Format name (humand readable)	
	 * 		     value = Format descriptor, e.G. sample snippet ...
	 */
	public function getSupportedSerializationFormatDescriptions();
}

/** 
 * Bulk db read serialization interface 
 * @package 	TestLink
 **/
interface iDBBulkReadSerialization
{
	/**
	 * Initializes the object from a single result row of a query, obtained by getReadFromDBQuery
	 * 
	 */
	public function readFromDBRow($row);

	/**
	 * Returns a query which can be used to read an arbitrary number of objects
	 * 
	 * @return string the query
	 */
	public function getReadFromDBQuery($ids,$options = self::TLOBJ_O_SEARCH_BY_ID);
}

/**
 * Any objects which support serialization from or to CSV should implement this interface
 * All Import/Export Interfaces must be named  like SerializationTo <NAME> to be automatically detected
 * @package 	TestLink
 */
interface iSerializationToCSV 
{
	/**
	 * Serializes the objects to csv (string)
	 * 
	 * @return integer tl::OK on success, other error code else 
	 */
	public function writeToCSV(&$csv,$delimiter = ';');

	/**
	 * Serializes the objects from csv (string)
	 * 
	 * @return integer tl::OK on success, other error code else 
	 */
	public function readFromCSV($csv,$delimiter = ';');
	
	/**
	 * Returns the format description, like sample snippet ....
	 * 
	 * @return string the format descriptor 
	 */
	public function getFormatDescriptionForCSV();
}


/**
 * Any objects which support serialization from or to XML should implement this interface
 * @package 	TestLink
 */
interface iSerializationToXML
{
	/**
	 * Serializes the objects to XML code (string)
	 * 
	 * @return integer tl::OK on success, other error code else 
	 */
	public function writeToXML(&$xml,$bNoHeader = false);

	/**
	 * Serializes the objects from XML code (string)
	 * 
	 * @return integer tl::OK on success, other error code else 
	 */
	public function readFromXML($xml);
	
	/**
	 * Serializes the objects from SimpleXML node (string)
	 * 
	 * @return integer tl::OK on success, other error code else 
	 */
	public function readFromSimpleXML($xmlNode);
	
	/**
	 * Returns the format description, like sample snippet ....
	 * 
	 * @return string the format descriptor 
	 */
	public function getFormatDescriptionForXML();
}


/**
 * Any objects which support serialization from or to Database should implement this interface
 * @package 	TestLink
 */
interface iDBSerialization
{
	/**
	 * Serializes the object to the database connection given by [ref] $db
	 * 
	 * @param resource &$db reference to database handler
	 * @return integer tl::OK on success, other error code else 
	 */
	public function readFromDB(&$db,$options = self::TLOBJ_O_SEARCH_BY_ID);

	/**
	 * Serializes the object from the database connection given by [ref] $db
	 * 
	 * @param resource &$db reference to database handler
	 * @return integer tl::OK on success, other error code else 
	 */
	public function writeToDB(&$db);
	
	/**
	 * Deletes the object from the database connection given by [ref] $db
	 * 
	 * @param resource &$db reference to database handler
	 * @return int, tl::OK on success, other error code else 
	 */
	public function deleteFromDB(&$db);
	
	/**
	 * factory function to create an object from [ref] $db and $id 
	 * 
	 * @param resource &$db reference to database handler
	 */
	static public function getByID(&$db,$id,$detailLevel = self::TLOBJ_O_GET_DETAIL_FULL);
	
	static public function getByIDs(&$db,$ids,$detailLevel = self::TLOBJ_O_GET_DETAIL_FULL);

	static public function getAll(&$db,$whereClause = null,$column = null,$orderBy = null,
	                              $detailLevel = self::TLOBJ_O_GET_DETAIL_FULL);
	
}
?>
