<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* This script is distributed under the GNU General Public License 2 or later. 
*
* Filename $RCSfile: keywords.inc.php,v $
* 
* @version $Id: keywords.inc.php,v 1.31 2007/02/04 20:18:32 schlundus Exp $
* @modified $Date: 2007/02/04 20:18:32 $ by $Author: schlundus $
*
* Functions for support keywords management. 
*
* 20051216 - MHT - fixed update keywords also in testplan
* 20051229 - scs - added support for ADODB, added some functions related to import/export
* 20060224 - franciscom - changes due to new schema
**/
if (version_compare(PHP_VERSION,'5','>=') && extension_loaded('xsl'))
{
	require_once(dirname(__FILE__) . '/../../third_party/domxml-php4-to-php5.php');
}

$g_keywordImportTypes = array( "CSV" => "CSV",
							                 "XML" => "XML",
							          );

$g_keywordExportTypes = $g_keywordImportTypes;

$g_keywordFormatStrings = array (
							"CSV" => lang_get('the_format_keyword_csv_import'),
							"XML" => lang_get('the_format_keyword_xml_import')
							); 		

/**
 * Deletes the keyword with the given id 
 *
 * @param object $db [ref] the database object
 * @param int $id the keywordID
 *
 * @return int returns 1 on success, 0 else
 * 
 * 20060311 - scs - fix for 0000064 
 * 				   When deleting a keyword, the keyword is not deleted from test cases 
 * @todo: should we now increment the tcversion also?
 **/
function deleteKeyword(&$db,$id)
{
	$sql = "DELETE FROM testcase_keywords WHERE keyword_id = " . $id;
	$result = $db->exec_query($sql);
	
	if ($result)
	{
		$sql = "DELETE FROM object_keywords WHERE keyword_id = " . $id;
		$result = $db->exec_query($sql);
	}
	if ($result)
	{
		$sql = "DELETE FROM keywords WHERE id = " . $id;
		$result = $db->exec_query($sql);
	}
		
	return $result ? 1 : 0;
}

/**
 * Exports the given keywords to a XML file
 *
 * @param type $keywords the keywords to export in the form
 * 				 keywordData[$i]['keyword'] => the keyword itself
 * 				 keywordData[$i]['notes'] => the notes of keyword
 * @return string the generated CSV code
 **/
function exportKeywordDataToCSV($keywords)
{
	$sKeys = array(
					"keyword",
					"notes",
				   );
	return exportDataToCSV($keywords,$sKeys,$sKeys);
}

/**
 * Exports the given keywords to a XML file
 *
 * @param type $keywords the keywords to export in the form
 * 				 keywordData[$i]['keyword'] => the keyword itself
 * 				 keywordData[$i]['notes'] => the notes of keyword
 *
 * @return strings the generated XML Code
 **/
function exportKeywordDataToXML($keywords,$bNoHeader = false)
{
	$keywordRootElem = "<keywords>{{XMLCODE}}</keywords>";
	$keywordElemTpl = "\t".'<keyword name="{{NAME}}"><notes><![CDATA['."\n||NOTES||\n]]>".'</notes></keyword>'."\n";
	$keywordInfo = array (
							"{{NAME}}" => "keyword",
							"||NOTES||" => "notes",
						);
	return exportDataToXML($keywords,$keywordRootElem,$keywordElemTpl,$keywordInfo,$bNoHeader);
}

/**
 * Imports the keywords from a XML file
 *
 * @param string $fileName the name of the XML file
 * @return array returns the keywordData in the form
 * 				 keywordData[$i]['keyword'] => the keyword itself
 * 				 keywordData[$i]['notes'] => the notes of keyword
 *
 **/
function importKeywordDataFromXML($fileName)
{
	$dom = domxml_open_file($fileName);
	$xmlKeywords = null;
	if ($dom)
		$xmlKeywords = $dom->get_elements_by_tagname("keyword");
	$keywordData = importKeywordsFromXML($xmlKeywords);
	
	return $keywordData;
}

function importKeywordsFromXML($xmlKeywords)
{
	if (!$xmlKeywords)
		return null;
		
	$keywords = null;	
	$j = 0;
	for($i = 0;$i < sizeof($xmlKeywords);$i++)
	{
		$xmlKeyword = $xmlKeywords[$i];		
		$keywordData = importKeywordFromXML($xmlKeyword);
		if ($keywordData)
			$keywords[$j++] = $keywordData;
	}
	return $keywords;
}
/**
 * Imports a single keywords from a XML Element
 *
 * @param object $fileName [ref] the XML Element to import from
 * @return array return null on error or an array of
 * 				 keyword['keyword'] => the keyword itself
 * 				 keyword['notes'] => the notes of keyword
 *
 **/
function importKeywordFromXML(&$xmlKeyword)
{
	if (!$xmlKeyword)
		return null;
	$keyword['keyword'] = $xmlKeyword->get_attribute("name");
	$keyword['notes'] = trim(getNodeContent($xmlKeyword,'notes'));

	return $keyword;
}
/**
 * Import keywords from a CSV file to keyword data which can be further processed
 *
 * @param string $fileName the input CSV filename
 * @return array return null on error or an array of
 * 				 keywordData[$i]['keyword'] => the keyword itself
 * 				 keywordData[$i]['notes'] => the notes of keyword
 *
 **/
function importKeywordDataFromCSV($fileName)
{
  	$delimiter = ';';
	$destKeys = array("keyword","notes");
	 					
	$keywordData = importCSVData($fileName,$destKeys,$delimiter,count($destKeys));
	return $keywordData;
}

/**
 * Checks a keyword against syntactic rules
 *
 * @param string $keyword the keyword to check
 * @return string returns null, when the keyword is correct, an errormsg else
 *
 **/
function checkKeywordName($keyword)
{
	$msg = null;
	if (strlen($keyword))
	{
		//we shouldnt allow " and , in keywords any longer
		if (preg_match("/(\"|,)/",$keyword,$m))
			$msg = lang_get('keywords_char_not_allowed'); 
	}
	else
		$msg = lang_get('empty_keyword_no');

	return $msg;
}
?>
