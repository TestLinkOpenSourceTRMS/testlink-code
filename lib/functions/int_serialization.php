<?php
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
		Serializes the objects to the database connection given by [ref] $db
	*/
	public function readFromDB(&$db);
	/*
		Serializes the objects from the database connection given by [ref] $db
	*/
	public function writeToDB(&$db);
}
?>