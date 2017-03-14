<?php require("class.xml.php"); ?>
<?php
/**
* Class of Manipulation of files XML and SGBD
*
* @author    			Olavo Alexandrino <oalexandrino@yahoo.com.br> - 2004
* @originalAuthor		Ricardo Costa <ricardo.community@globo.com>   - 2002
* @based				MySQL to XML - XML to MySQL - <http://www.phpclasses.org/browse/package/782.html>
* @require				Class XMLFile <Olavo Alexandrino version> - <orignal version: http://www.phpclasses.org/browse/package/79.html>
*		- function commentary to strtoupper of the methods:
*			1. add_attribute
*			2. set_name
* @require				ADOdb Database Library for PHP <http://php.weblogs.com/adodb#downloads>
*
*
* rev: 20080914 - Francisco Mancardi <francisco.mancardi@gmail.com>
*      added new properties rootTagName,rowTagName with getter and setter methods
*
*/

class ADODB_XML 
{
	/**
	*	Object representation:  File XML
	*
	*	@type		objcet
	*	@access		public
	*/
	var $xml = null; 
    
    private $rootTagName='ROOT';
    private $rowTagName='ROW';
  
    var $tmpDir = null;
  
	/**
	*	Creating the members
	*
	*	@param		string		Version of file XML
	*	@param		string		Codification to be used	
	*	@access		public
	*/
	function ADODB_XML($version = "", $encoding = "", $tmpDir = null) 
	{
	  $this->xml = new XMLFile($version, $encoding);
	  $this->tmpDir = is_null($tmpDir) ? sys_get_temp_dir() : $tmpDir; 
	}
	
	/**
	*	It converts Table of the SGBD into file XML
	*
	*	@param		object 			Connection of ADOdb Database Library	
	*	@param		string			Query SQL			
	*	@access		public
	*	@return 	string      XML with table contents 	
	*/	
	function ConvertToXMLString(&$dbConnection,$strSQL,$write_header=1)  
	{
	  	// $temp_dir = sys_get_temp_dir();
	    $temp_dir = $this->tmpDir;
	    $tmpfname = tempnam($temp_dir,'AGS');
	
	    $this->ConvertToXML($dbConnection, $strSQL, $tmpfname, $write_header);
	    $handle = fopen($tmpfname, "r");
	    if ($handle)
	    {
	    	$contents = fread($handle, filesize($tmpfname));
	    	fclose($handle);
	    }
	    unlink($tmpfname);

	  	return $contents;
	}
	
	
	
	/**
	*	It converts Table of the SGBD into file XML
	*
	*	@param		object 			Connection of ADOdb Database Library	
	*	@param		string			Query SQL			
	*	@param		string			Name of existing file XML
	*	@param		boolean			write xml header default:1
	*	@access		public
	*	@return 	void	
	*/	
	function ConvertToXML($dbConnection, $strSQL, $filename, $write_header=1) 
	{
	  $dbConnection->SetFetchMode(ADODB_FETCH_ASSOC);
	  $rs = $dbConnection->Execute($strSQL);
	  
	  $this->xml->create_root();
	  $this->xml->roottag->name = $this->rootTagName;
	  
	  while(!$rs->EOF)
	  {
		 $this->xml->roottag->add_subtag($this->rowTagName, array());
		 $tag = &$this->xml->roottag->curtag;
		 
		 for ($i = 0; $i < $rs->_numOfFields ; $i++)
		 {
			list($field, $value) = each($rs->fields);		 
			$tag->add_subtag($field);
			// 20080921 - francisco.mancardi@gmail.com
			// added CDATA to avoid problem with special characters
			$tag->curtag->cdata = "<![CDATA[{$value}]]>";
		 }	  
	  
		 $rs->moveNext();
	  }
	
	  $xml_file = fopen($filename, "w" );
	  $this->xml->write_file_handle( $xml_file, $write_header);
	}
	
	
	/**
	*	It inserts XML in table of the SGBD
	*
	*	@param		object 			Connection of ADOdb Database Library	
	*	@param		string			Name of to be created file XML	
	*	@param		string			Table of BD		
	*	@access		public
	*	@return 	void	
	*/	
	function InsertIntoDB($dbConnection, $filename, $tablename) 
	{
	
	  $xml_file = fopen($filename, "r"); 
	  $this->xml->read_file_handle($xml_file);
		  
	  $numRows = $this->xml->roottag->num_subtags();
	  
	  for ($i = 0; $i < $numRows; $i++) 
	  {
		   $arrFields = null;
		   $arrValues = null; 
	
		   $row = $this->xml->roottag->tags[$i];
		   $numFields = $row->num_subtags();
	
		   for ($ii = 0; $ii < $numFields; $ii++) 
		   {
			  $field = $row->tags[$ii];
			  $arrFields[] = $field->name;
			  $arrValues[] = "\"".$field->cdata."\"";
		   }
	
		   $fields = join($arrFields, ", ");
		   $values = join($arrValues, ", ");
	
		   $strSQL = "INSERT INTO $tablename ($fields) VALUES ($values)";
		   $dbConnection->Execute($strSQL);	  
	  } 
		  
	}
  
  function setRootTagName($value) 
	{
	    $this->rootTagName=$value;
	}
   
   
  function setRowTagName($value) 
	{
	    $this->rowTagName=$value;
	}
   

  function getRootTagName() 
	{
	    return $this->rootTagName;
	}

  function getRowTagName() 
	{
	    return $this->rowTagName;
	}
   
   
   
}// end class
?>