<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* This script is distributed under the GNU General Public License 2 or later. 
*
* Filename $RCSfile: keywords.inc.php,v $
* 
* @version $Id: keywords.inc.php,v 1.20 2006/03/11 23:09:28 schlundus Exp $
* @modified $Date: 2006/03/11 23:09:28 $ by $Author: schlundus $
*
* Functions for support keywords management. 
*
* 20051216 - MHT - fixed update keywords also in testplan
* 20051229 - scs - added support for ADODB, added some functions related to import/export
* 20060224 - franciscom - changes due to new schema
**/
$g_keywordImportTypes = array( "CSV" => "CSV",
							 "XML" => "XML",
							 );

$g_keywordFormatStrings = array (
							"CSV" => lang_get('the_format_keyword_csv_import'),
							"XML" => lang_get('the_format_keyword_xml_import')
							); 		

/**
 * collect all keywords for the product and return as associative array 
 *
 * @param object $db [ref] the database object
 * @param int $testprojectID the productID
 * @param string $selectedKey [default = ''] a possible selected keyword
 * @param int $keywordID [default = null] a possible keywordID to search for
 * 
 * @return type documentation
 * DEPRECATED
 **/
function selectKeywords(&$db,$testprojectID, $selectedKey = '',$keywordID = null)
{
	$arrKeywords = null;
	if ($testprojectID)
	{	
	  	//20050827 - scs - added sorting of keyword
	  	$sql = "SELECT id,keyword,notes FROM keywords WHERE testproject_id = " . $testprojectID ;
		if (!is_null($keywordID))
			$sql .= " AND id = {$keywordID} ";
		$sql .= " ORDER BY keyword ASC";
	  	
		$result = $db->exec_query($sql);
	  	if ($result)
	  	{
	  		while ($myrow = $db->fetch_array($result)) 
	  		{
	  			// add selected string for an appropriate row
	  			$selData = '';
	  			if (!is_null($selectedKey) && ($selectedKey == $myrow['id']))
	  				$selData = 'selected="selected"';
	  			$arrKeywords[] = array( 'id' => $myrow['id'],
	  									'keyword' => $myrow['keyword'], 
	  									'notes' => $myrow['notes'], 
	  				   					'selected' => $selData,
	  								   );
	  		}
	  	}
	}
	return $arrKeywords;
}

/**
 * Gets the keyword with the given keywordID
 *
 * @param int $tprojectID the test project id
 * @param int $keywordID the keyword id
 * @return array returns the keyword information
 **/
function getKeyword(&$db,$keywordID)
{
  	$sql = "SELECT id,keyword,notes FROM keywords WHERE id = {$keywordID} " .
		   " ORDER BY keyword ASC";
	  	
	$aKeywords = $db->get_recordset($sql);
	if (sizeof($aKeywords))
		$aKeywords = $aKeywords[0];
	
	return $aKeywords;
}
/**
 * Function-Documentation
 *
 * @param object $db [ref] documentation
 * @param type $id documentation
 * @param type $arrKeywords documentation
 *
 * @return type documentation
 * DEPRECATED
 **/
function updateTCKeywords(&$db,$id,$arrKeywords)
{
	$keywords = null;
	if ($arrKeywords)
		$keywords = $db->prepare_string(implode(",",$arrKeywords).",");
	
	$sqlUpdate = "UPDATE mgttestcase SET keywords='" . $keywords ."' where id=".$id;
	$resultUpdate = $db->exec_query($sqlUpdate);

 	// 200507 - MHT - SF1243285: TC version in TP is not incremented when keyword is added
 	if ($resultUpdate)
 	{
 		$sqlUpdate = "UPDATE testcase, mgttestcase SET testcase.keywords='" . $keywords .
 			"' WHERE testcase.version=mgttestcase.version AND testcase.mgttcid=" . $id .
 			" AND mgttestcase.id=" . $id;
 		$resultUpdate = $db->exec_query($sqlUpdate);
 	}
	
	return $resultUpdate ? 'ok' : $db->error_msg();
}

/**
 * Function-Documentation
 *
 * @param object $db [ref] documentation
 * @param type $id documentation
 * @param type $newKey documentation
 * @return type documentation
 *
 *
**/
function updateCategoryKeywords(&$db,$id, $newKey)
{
	$sqlTC = "SELECT id,title FROM mgttestcase WHERE catid=" . $id;
	$resultTC = $db->exec_query($sqlTC);
	
	$resultUpdate = null;
	if ($resultTC)
	{
		// execute for all test cases of the category
		while($rowTC = $db->fetch_array($resultTC))
		{ 
			$resultAdd = addTCKeyword($db,$rowTC['id'], $newKey);
			if ($resultAdd != 'ok')
				$resultUpdate .= lang_get('tc_kw_update_fails1'). htmlspecialchars($rowTC['title']) . 
				                 lang_get('tc_kw_update_fails2').': ' . $resultAdd . '<br />';
		}
	}
	else
	{
		$resultUpdate = $db->error_msg();
	}
	return $resultUpdate ? $resultUpdate : 'ok';
}


/**
 * Function-Documentation
 *
 * @param object $db [ref] documentation
 * @param type $id documentation
 * @param type $newKey documentation
 * @return type documentation
 **/
function updateComponentKeywords(&$db,$id, $newKey)
{
	$sqlCat = "SELECT id AS cat_id FROM mgtcategory WHERE compid=" . $id;
	$resultCat = $db->exec_query($sqlCat);
	
	$resultUpdate = null;
	if ($resultCat)
	{
		// execute for all test cases of the category
		while($rowCat = $db->fetch_array($resultCat))
		{ 
			$resultAdd = updateCategoryKeywords($db,$rowCat['cat_id'], $newKey);
			if ($resultAdd != 'ok')
			{
				$resultUpdate .= $resultAdd . '<br />';
			}	
		}
	}
	else
	{
		$resultUpdate = $db->error_msg();
	}
  
	return $resultUpdate ? $resultUpdate : 'ok';
}

/**
 * Function-Documentation
 *
 * @param object $db [ref] documentation
 * @param type $tcID documentation
 * @param type $newKey documentation
 *
 * @return type documentation
 * DEPRECATED
 **/
function addTCKeyword(&$db,$tcID, $newKey)
{
	$sqlTC = "SELECT keywords FROM mgttestcase where id=" . $tcID;
	//$resultUpdate = $db->exec_query($sqlTC);
	$TCKeys = $db->fetchFirstRowSingleColumn($sqlTC,'keywords');
	// add newKey if is not included
	$keys = explode(",",$TCKeys);
	$resultUpdate = 1;
	if (!in_array($newKey,$keys))
	{
		$TCKeys .= $newKey.",";
		$TCKeys = $db->prepare_string($TCKeys);
		$sqlUpdate = "UPDATE mgttestcase SET keywords='".$TCKeys."' WHERE id=". $tcID;
		$resultUpdate = $db->exec_query($sqlUpdate);

 		// 200507 - MHT - SF1243285: TC version in TP is not incremented when keyword is added
 		if ($resultUpdate)
			{
				$sqlUpdate = "UPDATE testcase, mgttestcase SET testcase.keywords='" . $TCKeys .
					"' WHERE testcase.version=mgttestcase.version AND testcase.mgttcid=" . $tcID .
					" AND mgttestcase.id=" . $tcID;
				$resultUpdate = $db->exec_query($sqlUpdate);
			}
	}
	
	return $resultUpdate ? 'ok' : $db->error_msg();
}


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
	$sql = "DELETE FROM testcase_keywords WHERE keyword_id=" . $id;
	$result = $db->exec_query($sql);
	
	if ($result)
	{
		$sql = "DELETE FROM keywords WHERE id=" . $id;
		$result = $db->exec_query($sql);
	}
		
	return $result ? 1 : 0;
}

/**
 * Function-Documentation
 *
 * @param object $db [ref] documentation
 * @param type $testprojectID documentation
 * @param type $searchKW [default = null] documentation
 * @param type $kwID [default = null] documentation
 * 
 * @return type documentation
 *
 * 20051004 - fm - return type changed
 * 20051126 - scs - added parameter kwID for getting the keyword name by id
 * DEPRECATED
 **/
function getProductKeywords(&$db,$testprojectID,$searchKW = null,$kwID = null)
{
	$sql = "SELECT keyword FROM keywords WHERE testproject_id=" . $testprojectID;
	
	if (!is_null($searchKW))
	{
		$sql .= " AND keyword = '".$db->prepare_string($searchKW)."'";
	}
	if (!is_null($kwID))
	{
		$sql .= " AND id = {$kwID}";
	}
	$sql .= " ORDER BY keyword ASC";
	
	$keywords = $db->fetchColumnsIntoArray($sql,'keyword');
	
	return $keywords;
}

/**
 * Exports the given keywords to a XML file
 *
 * @param type $keywords the keywords to export in the form
 * 				 keywordData[$i]['keyword'] => the keyword itself
 * 				 keywordData[$i]['notes'] => the notes of keyword
 * @return string the generated CSV code
 *
 * @author Andreas Morsing <schlundus@web.de>
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
 *
 * @author Andreas Morsing <schlundus@web.de>
 **/
function exportKeywordDataToXML($keywords)
{
	$keywordRootElem = "<keywords>{{XMLCODE}}</keywords>";
	$keywordElemTpl = "\t".'<keyword name="{{NAME}}"><notes><![CDATA['."\n||NOTES||\n]]>".'</notes></keyword>'."\n";
	$keywordInfo = array (
							"{{NAME}}" => "keyword",
							"||NOTES||" => "notes",
						);
	return exportDataToXML($keywords,$keywordRootElem,$keywordElemTpl,$keywordInfo);
}

/**
 * Imports the keywords from a XML file
 *
 * @param string $fileName the name of the XML file
 * @return array returns the keywordData in the form
 * 				 keywordData[$i]['keyword'] => the keyword itself
 * 				 keywordData[$i]['notes'] => the notes of keyword
 *
 * @author Andreas Morsing <schlundus@web.de>
 **/
function importKeywordDataFromXML($fileName)
{
	$dom = domxml_open_file($fileName);
	$xmlKeywords = null;
	if ($dom)
		$xmlKeywords = $dom->get_elements_by_tagname("keyword");
	
	$keywordData = null;
	for($i = 0;$i < sizeof($xmlKeywords);$i++)
	{
		$xmlKeyword = $xmlKeywords[$i];
		$keywordData[$i]['keyword'] = $xmlKeyword->get_attribute("name");
		$xmlKeywordNotes = $xmlKeyword->get_elements_by_tagname("notes");
		if ($xmlKeywordNotes)
		{
			$xmlKeywordNote = $xmlKeywordNotes[0]->first_child();
			if ($xmlKeywordNote)
				$keywordData[$i]['notes'] = $xmlKeywordNote->node_value();
		}
	}
	return $keywordData;
}

/**
 * Import keywords from a CSV file to keyword data which can be further processed
 *
 * @param string $fileName the input CSV filename
 * @return array return null on error or an array of
 * 				 keywordData[$i]['keyword'] => the keyword itself
 * 				 keywordData[$i]['notes'] => the notes of keyword
 *
 * @author Andreas Morsing <schlundus@web.de>
 **/
function importKeywordDataFromCSV($fileName)
{
	$destKeys = array(
					"keyword",
					"notes",
	 					);
	$keywordData = importCSVData($fileName,$destKeys,$delimiter = ';');
	
	return $keywordData;
}

/**
 * Checks a keyword against syntactic rules
 *
 * @param string $keyword the keyword to check
 * @return string returns null, when the keyword is correct, an errormsg else
 *
 * @author Andreas Morsing <schlundus@web.de>
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