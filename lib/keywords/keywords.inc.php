<?
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* This script is distributed under the GNU General Public License 2 or later. 
*
* Filename $RCSfile: keywords.inc.php,v $
* 
* @version $Id: keywords.inc.php,v 1.14 2005/12/29 20:59:00 schlundus Exp $
* @modified $Date: 2005/12/29 20:59:00 $ by $Author: schlundus $
*
* Functions for support keywords management. 
*
* 20051011 - fm - new dunction check_for_keyword_existence()
* 20051004 - fm - addNewKeyword() refactoring and improvements
* 20050810 - fm - deprecated $_SESSION['product'] removed
* 20051216 - MHT - fixed update keywords also in testplan
* 20051229 - scs - added support for ADODB, added some functions related to import/export
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
 * @param int $prodID the productID
 * @param string $selectedKey [default = ''] a possible selected keyword
 * @param int $keywordID [default = null] a possible keywordID to search for
 * 
 * @return type documentation
 **/
function selectKeywords(&$db,$prodID, $selectedKey = '',$keywordID = null)
{
	$arrKeywords = null;
	if ($prodID)
	{	
	  	//20050827 - scs - added sorting of keyword
	  	$sql = "SELECT id,keyword,notes FROM keywords WHERE prodid = " . $prodID ;
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
 * Function-Documentation
 *
 * @param object $db [ref] documentation
 * @param type $id documentation
 * @param type $arrKeywords documentation
 *
 * @return type documentation
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
 **/
function addTCKeyword(&$db,$tcID, $newKey)
{
	$sqlTC = "SELECT keywords FROM mgttestcase where id=" . $tcID;
	//$resultUpdate = do_sql_query($sqlTC);
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
 * Function-Documentation
 *
 * @param object $db [ref] documentation
 * @param type $prodID documentation
 * @param type $id documentation
 * @param type $keyword documentation
 * @param type $notes documentation
 * 
 * @return type documentation
 **/
function updateKeyword(&$db,$prodID,$id,$keyword,$notes)
{
	global $g_allow_duplicate_keywords;

	$ret = array("msg" => "ok", 
				 "status_ok" => 0);
	$do_action = 1;
	$my_kw = trim($keyword);

	if (!$g_allow_duplicate_keywords)
	{
		$check = check_for_keyword_existence($db,$prodID, $my_kw,$id);
		$do_action = !$check['keyword_exists'];

		$ret['msg'] = $check['msg'];
		$ret['status_ok'] = $do_action;
	}

	if ($do_action)
	{
		$sql = "UPDATE keywords SET notes='" . $db->prepare_string($notes) . "', keyword='" 
				. $db->prepare_string($my_kw) . "' where id=" . $id;
		$result = $db->exec_query($sql);
		
		if (!$result)
		{
			$ret['msg'] = $db->error_msg();
			$ret['status_ok'] = 0;
		}
	}

	return $ret;
}


/**
 * Deletes the keyword with the given id 
 *
 * @param object $db [ref] the database object
 * @param int $id the keywordID
 *
 * @return int returns 1 on success, 0 else
 **/
function deleteKeyword(&$db,$id)
{
	$sql = "DELETE FROM keywords WHERE id=" . $id;
	$result = $db->exec_query($sql);
	
	return $result ? 1 : 0;
}

/**
 * Adds a new keyword to the given product
 *
 * @param object $db [ref] the database object
 * @param int  $prodID
 * @param string $keyword
 * @param string $notes
 *
 * @return string 'ok' on success, a db error msg else
 *
 * 20051011 - fm - use of check_for_keyword_existence()
 * 20051004 - fm - refactoring
 **/
function addNewKeyword(&$db,$prodID,$keyword,$notes)
{
	global $g_allow_duplicate_keywords;
	
	$ret = 'ok';
	$do_action = 1;
	$my_kw = trim($keyword);
	if (!$g_allow_duplicate_keywords)
	{
		$check = check_for_keyword_existence($db,$prodID, $my_kw);
		$ret = $check['msg'];
		$do_action = !$check['keyword_exists'];
	}
	
	if ($do_action)
	{
		$sql =  " INSERT INTO keywords (keyword,prodid,notes) " .
				" VALUES ('" . $db->prepare_string($my_kw) .	"'," . 
				$prodID . ",'" . $db->prepare_string($notes) . "')";
		
		$result = do_sql_query($sql);
		if (!$result)
			$ret = $db->error_msg();
	}
  
	return $ret;
}
/**
 * Function-Documentation
 *
 * @param object $db [ref] documentation
 * @param type $tcID documentation
 * @return type documentation
 *
 * 20051004 - fm - return type changed 
 **/
function getTCKeywords(&$db,$tcID)
{
	$sql = "SELECT keywords FROM mgttestcase WHERE id=" . $tcID;
	$keywords = $db->fetchFirstRowSingleColumn($sql,'keywords');
	if (!is_null($keywords))
		$keywords = explode(",",$keywords);	
	
	return $keywords;
}

/**
 * Function-Documentation
 *
 * @param object $db [ref] documentation
 * @param type $prodID documentation
 * @param type $searchKW [default = null] documentation
 * @param type $kwID [default = null] documentation
 * 
 * @return type documentation
 *
 * 20051004 - fm - return type changed
 * 20051126 - scs - added parameter kwID for getting the keyword name by id
 **/
function getProductKeywords(&$db,$prodID,$searchKW = null,$kwID = null)
{
	$sql = "SELECT keyword FROM keywords WHERE prodid=" . $prodID;
	
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
 * Function-Documentation
 *
 * @param object $db [ref] documentation
 * @param inr $prodID product ID
 * @param string $kw keyword to search for
 * @param int $kwID[default = 0] ignore  keyword with this id
 *
 * @return type
 *				 				
 **/
function check_for_keyword_existence($db,$prodID, $kw, $kwID = 0)
{
	$ret = array(
				 'msg' => 'ok', 
				 'keyword_exists' => 0
				 );
	  
	$sql = 	" SELECT * FROM keywords " .
			" WHERE UPPER(keyword) ='" . strtoupper($db->prepare_string($kw)).
		    "' AND prodid=" . $prodID ;
	
	if ($kwID)
		$sql .= " AND id <> " . $kwID;
	
	if ($db->fetchFirstRow($sql))
	{
		$ret['keyword_exists'] = 1;
		$ret['msg'] = lang_get('keyword_already_exists');
	}
	
	return $ret;
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
	$keywordElemTpl = "\t".'<keyword name="{{NAME}}"><notes><![CDATA['."\n{{NOTES}}\n]]>".'</notes></keyword>'."\n";
	$keywordInfo = array (
							"{{NAME}}" => "keyword",
							"{{NOTES}}" => "notes",
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
 * Imports the keywords contained in keywordData to the given product
 *
 * @param type $db [ref] documentation
 * @param int $prodID the product to which the keywords should be imported
 * @param array $keywordData an array with keyword information like
 * 				 keywordData[$i]['keyword'] => the keyword itself
 * 				 keywordData[$i]['notes'] => the notes of keyword
 *
 * @return array returns an array of result msgs
 *
 * @author Andreas Morsing <schlundus@web.de>
 **/
function importKeywords(&$db,$prodID,$keywordData)
{
	$sqlResults = null;
	for($i = 0;$i < sizeof($keywordData);$i++)
	{
		$keyword = $keywordData[$i]['keyword'];
		$notes = $keywordData[$i]['notes'];
		$msg = checkKeyword($keyword);
		if (!is_null($msg))
			$sqlResults[] = $msg;
		else
			$sqlResults[] = addNewKeyword($db,$prodID,$keyword,$notes);
	}
	
	return $sqlResults;
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
function checkKeyword($keyword)
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