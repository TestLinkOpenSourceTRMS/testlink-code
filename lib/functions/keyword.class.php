<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* This script is distributed under the GNU General Public License 2 or later. 
*
* Filename $RCSfile: keyword.class.php,v $
* 
* @version $Id: keyword.class.php,v 1.1 2007/12/05 21:25:14 schlundus Exp $
* @modified $Date: 2007/12/05 21:25:14 $ by $Author: schlundus $
*
* Functions for support keywords management. 
**/
require_once( dirname(__FILE__) . '/object.class.php');
require_once( dirname(__FILE__) . '/csv.inc.php');
require_once( dirname(__FILE__) . '/xml.inc.php');

//this class will be later moved to an extra file
class tlKeyword extends tlDBObject implements iSerialization,iSerializationToXML, iSerializationToCSV
{
	//the name of the keyword
	protected $m_name;
	//the notes for the keyword
	protected $m_notes;
	// the testprojectID the keyword belongs to
	protected $m_testprojectID;
	// config valuze
	protected $m_allow_duplicate_keywords; 
	
	//Some error codes
	const KW_E_NOTALLOWED = -1;
	const KW_E_EMPTY = -2;
	const KW_E_DUPLICATE = -4;
	const KW_E_DBERROR = -8;
	const KW_E_WRONGFORMAT = -16;
	
	protected function _clean()
	{
		$this->m_name = NULL;
		$this->m_notes = NULL;
		$this->m_testprojectID = NULL;
	}
	
	function __construct($dbID = null)
	{
		parent::__construct($dbID);
	
		global $g_allow_duplicate_keywords;
		$this->m_allow_duplicate_keywords = $g_allow_duplicate_keywords;
	}
	function __destruct()
	{
		parent::__destruct();
		$this->_clean();
	}
	/* fills the members  */
	function create($testprojectID,$name,$notes)
	{
		$this->m_name = $name;
		$this->m_notes = $notes;
		$this->m_testprojectID = $testprojectID;
	}
	//BEGIN interface iDBSerialization
	public function readFromDB(&$db)
	{
		$query = " SELECT id,keyword,notes,testproject_id FROM keywords " .
			   " WHERE id = {$this->m_dbID}" ;
		$info = $db->fetchFirstRow($query);			 
		$this->_clean();
		if ($info)
		{
			$this->m_name = $info['keyword'];
			$this->m_notes = $info['notes'];
			$this->m_testprojectID = $info['testproject_id'];
		}
		return $info ? true : false;
	}
	public function writeToDB(&$db)
	{
		$result = $this->checkKeywordName();
		if ($result == OK && !$this->m_allow_duplicate_keywords)
			$result = $this->doesKeywordExist($db);
		if ($result == OK)
		{
			$name = $db->prepare_string($this->m_name);
			$notes = $db->prepare_string($this->m_notes);

			if ($this->m_dbID)
			{
				$query = "UPDATE keywords SET keyword = '{$name}',notes='{$notes}',testproject_id={$this->m_testprojectID}" .
						" WHERE id = {$this->m_dbID}";
				$result = $db->exec_query($query);
			}
			else
			{
				$query = " INSERT INTO keywords (keyword,testproject_id,notes) " .
						 " VALUES ('" . $name .	"'," . $this->m_testprojectID . ",'" . $notes . "')";
				
				$result = $db->exec_query($query);
				if ($result)
					$this->m_dbID = $db->insert_id();
			}
			$result = $result ? OK : self::KW_E_DBERROR;
		}
		return $result;
	}
	//END interface iDBSerialization
	/* for legacy purposes */
	public function getInfo()
	{
		return array(
			"id" => $this->m_dbID,
			"keyword" => $this->m_name,
			"notes" => $this->m_notes,
			"testproject_id" => $this->m_testprojectID,
		);
	}
	
	/**
	 * Checks a keyword against syntactic rules
	 *
	 **/
	protected function checkKeywordName()
	{
		$result = OK;
		if (strlen($this->m_name))
		{
			//we shouldnt allow " and , in keywords any longer
			$dummy = null;
			if (preg_match("/(\"|,)/",$this->m_name,$dummy))
				$result = self::KW_E_NOTALLOWED;
		}
		else
			$result = self::KW_E_EMPTY;

		return $result;
	}
	/**
	 * checks if a keyword already exists in the database
	 **/
	protected function doesKeywordExist(&$db)
	{
		$name = $db->prepare_string(strtoupper($this->m_name));
		$query = " SELECT id FROM keywords " .
				 " WHERE UPPER(keyword) ='" . $name.
			     "' AND testproject_id = " . $this->m_testprojectID ;
		
		if ($this->m_dbID)
			$query .= " AND id <> " . $this->m_dbID;
		
		$result = OK;
		if ($db->fetchFirstRow($query))
			$result = self::KW_E_DUPLICATE;
		
		return $result;
	}
	//BEGIN interface iSerializationToXML
	
	/**
	 * gets the format descriptor for XML
	 **/
	public function getFormatDescriptionForXML()
	{
		return "<keywords><keyword name=\"name\">Notes</keyword></keywords>";
	}

	public function writeToXML(&$xml,$bNoHeader = false)
	{
		//SCHLUNDUS: maybe written with SimpleXML ?
		$keywords = array($this->getInfo());
		$keywordElemTpl = '<keyword name="{{NAME}}"><notes><![CDATA['."\n||NOTES||\n]]>".'</notes></keyword>'."\n";
		$keywordInfo = array (
							"{{NAME}}" => "keyword",
							"||NOTES||" => "notes",
						);
		$xml .= exportDataToXML($keywords,"{{XMLCODE}}",$keywordElemTpl,$keywordInfo,$bNoHeader);
	}
	public function readFromXML($xml)
	{
		$this->m_name = NULL;
		$this->m_notes = NULL;
		
		$keyword = simplexml_load_string($xml);
		if (!$keyword || $keyword->getName() != 'keyword')
			return self::KW_E_WRONGFORMAT;
			
		$attributes = $keyword->attributes();
		if (!isset($attributes['name']))
			return self::KW_E_WRONGFORMAT;
			
		$name = (string)$attributes['name'];
		$this->m_name = $name;
		if ($keyword->notes)
			$this->m_notes = (string)$keyword->notes[0];
			
		return OK;
	}
	//END interface iSerializationToXML
	
	//BEGIN interface iSerializationToCSV
	public function getFormatDescriptionForCSV()
	{
		return "keyword;notes";
	}
	public function writeToCSV(&$csv,$delimiter = ';')
	{
		$keyword = array($this->getInfo());
		$sKeys = array(
					"keyword",
					"notes",
				   );
		$csv .= exportDataToCSV($keyword,$sKeys,$sKeys);
	}
	public function readFromCSV($csv,$delimiter = ';')
	{
		$delimiter = ';';
		$data = explode($delimiter,$csv);
	 					
		$this->m_name = isset($data[0]) ? $data[0] : null;
		$this->m_notes = isset($data[1]) ? $data[1] : null;
		
		return sizeof($data) ? true : false;
	}
	//END interface iSerializationToCSV
	
}
?>
