<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource $RCSfile: requirementsImport.inc.php,v $
 * @version $Revision: 1.6 $
 * @modified $Date: 2006/01/05 07:30:33 $ by $Author: franciscom $
 * @author Martin Havlat
 * 
 * Functions for Import requirements to a specification. 
 *
 * @author Francisco Mancardi - 20050906 - reduce global coupling
 *
 * @author Francisco Mancardi - 20050905 - trim_title() refactoring 
 */

require_once('requirements.inc.php');


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
		if (($emptyScope == 'on') && empty($data[1]))
		{
			// skip rows with empty scope
			$status = lang_get('req_import_result_skipped');
		}
		else
		{
			// 
			$title = trim_title($data[0]);
			$scope = nl2br($data[1]);
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
				$status = createRequirement ($title, $scope, $idSRS, $userID); // default status and type
			}
			$arrImport[] = array($title, $status);
		}
	}
	
	return $arrImport;
}

/** compare titles of importing and existing requirements */
function compareImportedReqs($arrImportSource, $arrReqTitles)
{
	foreach ($arrImportSource as $data)
	{
		if (array_search($data[0], $arrReqTitles))
		{
			$status = lang_get('conflict');
			tLog('REQ: '.$data[0]. "\n CONTENT: ".$data[1]);
		} else {
			$status = lang_get('ok');
		}
		$arrImport[] = array($data[0], $data[1], $status);
	}
	
	return $arrImport;
}

/** get Titles of existing requirements */
function getReqTitles(&$db,$idSRS)
{
	// collect existing req titles in the SRS
	$arrCurrentReq = getRequirements($db,$idSRS);
	$arrReqTitles = array();
	if (count($arrCurrentReq)) { // only if some reqs exist
		foreach ($arrCurrentReq as $data) {
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
	$fp = fopen($CSVfile,"r");
	if ($importType == 'csv_doors')
	{
		// read header and find order of important fields
		$data = fgetcsv($fp, TL_IMPORT_ROW_MAX, ",");
		$titleNumber = array_search("Object Identifier", $data);
		$scopeNumber = array_search("Object Text", $data);
		$createdByNumber = array_search("Created By", $data);
		$createdOnNumber = array_search("Created On", $data);
		$modifiedByNumber = array_search("Last Modified By", $data);
		$modifiedOnNumber = array_search("Last Modified On", $data);
				
		tLog("titleNumber=$titleNumber scopeNumber=$scopeNumber");
	}
			
	while ($data = fgetcsv($fp, TL_IMPORT_ROW_MAX, ","))
	{
		// process only rows with two and more fields
		if (count ($data) > 1){
			if ($importType == 'csv_doors') {
				//$arrImportSource[] = array($data[$titleNumber],$data[$scopeNumber]);
				$arrImportSource[] = array($data[$titleNumber],$data[$scopeNumber],$data[$createdByNumber],
						$data[$createdOnNumber],$data[$modifiedByNumber],$data[$modifiedOnNumber]);
			} elseif ($importType == 'csv') {
				$arrImportSource[] = $data; // simple CSV
			} else {
				tLog("Wrong import type.", "ERROR");
			}
		}
	}
	fclose ($fp);
	
	return $arrImportSource;
}

?>