<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* This script is distributed under the GNU General Public License 2 or later. 
*
* Filename $RCSfile: int_serialization.php,v $
* 
* @version $Id: int_serialization.php,v 1.4 2007/12/19 21:33:40 schlundus Exp $
* @modified $Date: 2007/12/19 21:33:40 $ by $Author: schlundus $
*
**/

/* basic serialization interface */
interface iSerialization
{
	/*
		Returns all supported Import/Export Serialization Interfaces
	*/
	public function getSupportedSerializationInterfaces();
	/*
		Returns all supported Import/Export Serialization Interfaces Format Descriptors
	*/
	public function getSupportedSerializationFormatDescriptions();
}

/* All Import/Export Interfaces must be named  like SerializationTo <NAME>*/
interface iSerializationToCSV 
{
	/*
		Serializes the objects to csv (string)
	*/
	public function writeToCSV(&$csv,$delim = ';');
	/*
		Serializes the objects from csv (string)
	*/
	public function readFromCSV($csv);
	
	/*
		Returns a format description
	*/
	public function getFormatDescriptionForCSV();
}
/*
Any objects which support serialization from or to XML should implement this interface
*/
interface iSerializationToXML
{
	/*
		Serializes the objects to XML code (string)
	*/
	public function writeToXML(&$xml,$bNoHeader = false);
	/*
		Serializes the objects from XML code (string)
	*/
	public function readFromXML($xml);
	
	/*
		Serializes the objects from SimpleXML node (string)
	*/
	public function readFromSimpleXML($xmlNode);
	
	/*
		Returns a format description
	*/
	public function getFormatDescriptionForXML();
}

/*
Any objects which support serialization from or to Database should implement this interface
*/
interface iDBSerialization
{
	/*
		Serializes the object to the database connection given by [ref] $db
	*/
	public function readFromDB(&$db);
	/*
		Serializes the object from the database connection given by [ref] $db
	*/
	public function writeToDB(&$db);
	
	/*
		Deletes the object from the database connection given by [ref] $db
	*/
	public function deleteFromDB(&$db);
	
	/* factory function to create an object from [ref] $db */
	static public function getByID(&$db,$id);
}
?>