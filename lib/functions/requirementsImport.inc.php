<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource $RCSfile: requirementsImport.inc.php,v $
 * @version $Revision: 1.8 $
 * @modified $Date: 2006/04/07 20:15:26 $ by $Author: schlundus $
 * @author Martin Havlat
 * 
 * Functions for Import requirements to a specification. 
 *
 * @author Francisco Mancardi - 20050906 - reduce global coupling
 *
 * @author Francisco Mancardi - 20050905 - trim_title() refactoring 
 */

require_once('requirements.inc.php');
require_once( dirname(__FILE__). '/csv.inc.php' );


$g_reqImportTypes = array( "csv" => "CSV",
							"csv_doors" => "CSV (Doors)",
							 "XML" => "XML",
							 );

$g_reqFormatStrings = array (
							"csv" => lang_get('req_import_format_description1'),
							"csv_doors" => lang_get('req_import_format_description2'),
							"XML" => lang_get('the_format_req_xml_import')
							); 		

/** 
 * trim title to N chars
 * @param string title
 * @param int [len]: how many chars return
 *
 * @return string trimmed title
 *
 * @author Francisco Mancardi - 20050905 - refactoring
 *
 */
function trim_title($title, $len=100)
{
	if (strlen($title) > $len ) {
		$title = substr($title, 0, $len);
	}
	return $title;
}

/** collect information about one Requirement from REQ Title
 * @param string $title of req.
 * @return assoc_array list of requirements
 */
function getReqDataByTitle(&$db,$title)
{
	$output = array();
	
	$sql = "SELECT * FROM requirements WHERE title='" . $title . "'";
	$result = $db->exec_query($sql);
	if (!empty($result)) {
		$output = $db->fetch_array($result);
	}
	
	return $output;
}

/** function process CVS file with requirements into TL and creates an array with reports 
 * @return array_of_strings list of particular REQ titles with resolution 
 *
 *
 * @author Francisco Mancardi - 20050906 - added $userID
 **/
function executeImportedReqs(&$db,$arrImportSource, $arrReqTitles, 
                             $conflictSolution, $emptyScope, $idSRS, $userID)
{
	foreach ($arrImportSource as $data)
	{
		$title = $data['title'];
		$description = $data['description'];
		if (($emptyScope == 'on') && empty($description))
		{
			// skip rows with empty scope
			$status = lang_get('req_import_result_skipped');
		}
		else
		{
			$title = trim_title($title);
			$scope = nl2br(htmlspecialchars($description));
			tLog('REQ: '.$title. "\n scope: ".$scope);
		
			if (array_search($title, $arrReqTitles))
			{
				// process conflick according to choosen solution
				tLog('Conflict found. solution: ' . $conflictSolution);

				if ($conflictSolution == 'overwrite') {
					$arrOldReq = getReqDataByTitle($db,$title);
					$status = updateRequirement($db,$arrOldReq[0]['id'],$title,$scope,$userID,
							                        $arrOldReq[0]['status'],$arrOldReq[0]['type']);
					if ($status == 'ok') {
						$status = lang_get('req_import_result_overwritten');
					}
				} 

				elseif ($conflictSolution == 'double') 
				{
					$status = createRequirement($db,$title, $scope, $idSRS, $userID); // default status and type
					if ($status == 'ok') {
						$status = lang_get('req_import_result_added');
					}
				} 

				elseif ($conflictSolution == 'skip') {
					// no work
					$status = lang_get('req_import_result_skipped');
				}

				else
				{
					$status = 'Error';
				}

			} else {
				// no conflict - just add requirement
				$status = createRequirement ($db, $title, $scope, $idSRS, $userID); // default status and type
			}
			$arrImport[] = array($title, $status);
		}
	}
	
	return $arrImport;
}

/** compare titles of importing and existing requirements */
function compareImportedReqs($arrImportSource, $arrReqTitles)
{
	$arrImport = null;
	if (sizeof($arrImportSource))
	{
		foreach ($arrImportSource as $data)
		{
			$status = lang_get('ok');
			$title = $data['title'];
			if (!strlen(trim($title)))
				continue;
			if ($arrReqTitles &&  in_array($title, $arrReqTitles,true))
			{
				$status = lang_get('conflict');
				tLog('REQ: '.$title. "\n CONTENT: ".$data['description']);
			}
			$arrImport[] = array($title, $data['description'], $status);
		}
	}
	
	return $arrImport;
}

/** get Titles of existing requirements */
function getReqTitles(&$db,$idSRS)
{
	// collect existing req titles in the SRS
	$arrCurrentReq = getRequirements($db,$idSRS);
	$arrReqTitles = null;
	if (count($arrCurrentReq))
	{ 
		// only if some reqs exist
		foreach ($arrCurrentReq as $data)
		{
			$arrReqTitles[$data['id']] = $data['title'];
		}
	}
	
	return $arrReqTitles;
}

/**
 * load imported data from file and parse it to array
 * @return array_of_array each inner array include fields title and scope (and more)
 */
function loadImportedReq($CSVfile, $importType)
{
	$fileName = $CSVfile;
	switch($importType)
	{
		case 'csv':
			$pfn = "importReqDataFromCSV";
			break;
		case 'csv_doors':
			$pfn = "importReqDataFromCSVDoors";
			break;
		case 'XML':
			$pfn = "importReqDataFromXML";
			break;
	}
	if ($pfn)
	{
		$data = $pfn($fileName);
		return $data;
	}
	return;
	
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
function importReqDataFromCSV($fileName)
{
	$destKeys = array(
					"title",
					"description",
	 					);
	$reqData = importCSVData($fileName,$destKeys,$delimiter = ',');
	
	return $reqData;
}

function importReqDataFromCSVDoors($fileName)
{
	$destKeys = array(
					"Object Identifier" => "title",
					"Object Text" => "description",
					"Created By",
					"Created On",
					"Last Modified By",
					"Last Modified On",
				);
				
	$reqData = importCSVData($fileName,$destKeys,$delimiter = ',',true,false);
	
	return $reqData;
}


function importReqDataFromXML($fileName)
{
	$dom = domxml_open_file($fileName);
	$xmlReqs = null;
	if ($dom)
		$xmlReqs = $dom->get_elements_by_tagname("requirement");
	
	$xmlData = null;
	for($i = 0;$i < sizeof($xmlReqs);$i++)
	{
		$xmlReq = $xmlReqs[$i];
		if ($xmlReq->node_type() != XML_ELEMENT_NODE)
			continue;
		$xmlData[$i]['title'] = getNodeContent($xmlReq,"title");
		$xmlData[$i]['description'] = getNodeContent($xmlReq,"description");
	}
	return $xmlData;
}

function doImport(&$db,$userID,$idSRS,$fileName,$importType,$emptyScope,$conflictSolution,$bImport)
{
	$arrImportSource = loadImportedReq($fileName, $importType);
	
	$arrImport = null;
	if (count($arrImportSource))
	{
		$arrReqTitles = getReqTitles($db,$idSRS);
		
		if ($bImport)
		{
			$arrImport = executeImportedReqs($db,$arrImportSource, $arrReqTitles, 
		                        $conflictSolution, $emptyScope, $idSRS, $userID);
		}
		else
			$arrImport = compareImportedReqs($arrImportSource, $arrReqTitles);
	}
	return $arrImport;
}
?>