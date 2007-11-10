<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *  
 * @filesource $RCSfile: requirements.inc.php,v $
 * @version $Revision: 1.62 $
 * @modified $Date: 2007/11/10 08:10:35 $ by $Author: franciscom $
 *
 * @author Martin Havlat <havlat@users.sourceforge.net>
 * 
 * Functions for support requirement based testing 
 *
 * Revisions:
 *
 * 20070710 - franciscom - BUGID 939
 * 20070705 - franciscom - improved management of arrReqStatus
 * 20070617 - franciscom - removed include of deprecated file
 * 20070310 - franciscom - changed return type createRequirement()
 */
////////////////////////////////////////////////////////////////////////////////


require_once("print.inc.php");
require_once("requirement_spec_mgr.class.php");
require_once("requirement_mgr.class.php");

// 20070705 - franciscom
$arrReqStatus = init_labels(config_get('req_status'));

$g_reqFormatStrings = array (
							"csv" => lang_get('req_import_format_description1'),
							"csv_doors" => lang_get('req_import_format_description2'),
							"XML" => lang_get('the_format_req_xml_import')
							); 		

/** 
 * print Requirement Specification 
 *
 * @param integer $srs_id
 * @param string $prodName
 * @param string $user_id
 * @param string $base_href
 *
 * @author Martin Havlat
 *  
 * @version 1.2 - 20050905
 * @author Francisco Mancardi
 *
 * @version 1.1 - 20050830
 * @author Francisco Mancardi
 *
 **/
function printSRS(&$db,&$tproject,$srs_id, $prodName, $testproject_id, $user_id, $base_href)
{
	$arrSpec = $tproject->getReqSpec($testproject_id,$srs_id);
	
	$title = $arrSpec[0]['title'];
	$output =  printHeader($title,$base_href);
	$output .= printFirstPage($db,$title,$prodName,'',$user_id);
	$output .= "<h2>" . lang_get('scope') . "</h2>\n<div>" . $arrSpec[0]['scope'] . "</div>\n";
	$output .= printRequirements($db,$srs_id);
	$output .= "\n</body>\n</html>";

	return $output;
}

/** 
 * print Requirement for SRS 
 * 
 * @param integer $srs_id
 * 
 * @author Martin Havlat 
 * 20051125 - scs - added escaping of req names
 * 20051202 - scs - fixed 241
 **/
function printRequirements(&$db,$srs_id)
{
	$arrReq = $req_spec_mgr->get_requirements($srs_id);
	
	$output = "<h2>" . lang_get('reqs') . "</h2>\n<div>\n";
	if (count($arrReq))
	{
		foreach ($arrReq as $REQ)
		{
			$output .= '<h3>' .htmlspecialchars($REQ["req_doc_id"]). " - " . 
						htmlspecialchars($REQ['title']) . "</h3>\n<div>" . 
						$REQ['scope'] . "</div>\n";
		}
	}
	else
		$output .= '<p>' . lang_get('none') . '</p>';

	$output .= "\n</div>";

	return $output;
}



function exportReqDataToXML($reqData)
{
	$rootElem = "<requirements>{{XMLCODE}}</requirements>";
	$elemTpl = "\t".'<requirement><docid><![CDATA['."\n||DOCID||\n]]>".'</docid><title><![CDATA['."\n||TITLE||\n]]>".'</title>'.
					'<description><![CDATA['."\n||DESCRIPTION||\n]]>".'</description>'.
					'</requirement>'."\n";
	$info = array (
							"||DOCID||" => "req_doc_id",
							"||TITLE||" => "title",
							"||DESCRIPTION||" => "scope",
						);
	return exportDataToXML($reqData,$rootElem,$elemTpl,$info);
}



/** Process CVS file contents with requirements into TL 
 *  and creates an array with reports 
 *  @return array_of_strings list of particular REQ data with resolution comment
 *
 *
 **/
function executeImportedReqs(&$db,$arrImportSource, $map_cur_reqdoc_id, 
                             $conflictSolution, $emptyScope, $idSRS, $userID)
{
	$req_mgr = new requirement_mgr($db);
	define('SKIP_CONTROLS',1);
	$field_size = config_get('field_size');

	foreach ($arrImportSource as $data)
	{
		$docID = trim_and_limit($data['req_doc_id'],$field_size->req_docid);
		$title = trim_and_limit($data['title'],$field_size->req_title);
		$scope = $data['description'];
		
		if (($emptyScope == 'on') && empty($scope))
		{
			// skip rows with empty scope
			$status = lang_get('req_import_result_skipped');
		}
		else
		{
			if ($map_cur_reqdoc_id && array_search($docID, $map_cur_reqdoc_id))
			{
				// process conflict according to choosen solution
				tLog('Conflict found. solution: ' . $conflictSolution);
				if ($conflictSolution == 'overwrite')
				{
					$row_curr_data = getReqByReqdocId($db,$docID);
					$req_id = key($row_curr_data);
					$status = $req_mgr->update($req_id,$docID,$title,$scope,$userID,
							                       $row_curr_data[$req_id]['status'],
							                       $row_curr_data[$req_id]['type'],SKIP_CONTROLS);
							                            
					if ($status == 'ok') {
						$status = lang_get('req_import_result_overwritten');
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
				$status = $req_mgr->create($idSRS,$docID, $title, $scope,  $userID,
				                           TL_REQ_STATUS_VALID, TL_REQ_STATUS_NOT_TESTABLE);
			}
			$arrImport[] = array($docID,$title, $status['msg']);
		}
	}
	
	return $arrImport;
}

/*
20061014 - franciscom -
algorithm changes, now is the docid the attribute that must be unique
*/
function compareImportedReqs($arrImportSource, $map_cur_reqdoc_id)
{
	$arrImport = null;
	if (sizeof($arrImportSource))
	{
		foreach ($arrImportSource as $data)
		{
			$status = lang_get('ok');
			$req_doc_id = $data['req_doc_id'];
			
			if ($map_cur_reqdoc_id &&  in_array($req_doc_id, $map_cur_reqdoc_id,true))
			{
				$status = lang_get('conflict');
				tLog('REQ: '. $data['req_doc_id'] . "\n CONTENT: ".$data['description']);
			}
			$arrImport[] = array($data['req_doc_id'],
								           trim($data['title']), 
								           $data['description'], $status);
		}
	}
	
	return $arrImport;
}

// 20061014 - franciscom
function getReqDocIDs(&$db,$srs_id)
{
  $req_spec_mgr= new requirement_spec_mgr($db);

	$arrCurrentReq = $req_spec_mgr->get_requirements($srs_id);
	$result = null;
	if (count($arrCurrentReq))
	{ 
		// only if some reqs exist
		foreach ($arrCurrentReq as $data)
		{
			$result[$data['id']] = $data['req_doc_id'];
		}
	}
	
	return($result);
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


function importReqDataFromCSV($fileName)
{
  $field_size=config_get('field_size');  
  $delimiter=',';
  
  // CSV line format
	$destKeys = array("req_doc_id",
					          "title",       
					          "description");

  // lenght will be adjusted to these values
  $field_length = array("req_doc_id" => $field_size->req_docid,
					              "title" => $field_size->req_title);
					          
	$reqData = importCSVData($fileName,$destKeys,$delimiter,count($destKeys));
	
	// 20061015 - franciscom
	// adjust value length to field length to avoid problems during inset
	if ($reqData)
	{
		foreach($reqData as $key => $value)
		{
	     foreach($field_length as $fkey => $len)
		   {
	       $reqData[$key][$fkey]=trim_and_limit($reqData[$key][$fkey],$len); 	      
		   }
		}
	}
	return $reqData;
}


function importReqDataFromCSVDoors($fileName)
{
  $delimiter=',';
  $bWithHeader = true;
  $bDontSkipHeader = false;
  
	$destKeys = array("Object Identifier" => "title",
					          "Object Text" => "description",
					          "Created By",
					          "Created On",
					          "Last Modified By",
					          "Last Modified On");
				
	$reqData = importCSVData($fileName,$destKeys,$delimiter,0,$bWithHeader,$bDontSkipHeader);
	
	return $reqData;
}

/*
20061015 - franciscom - added trim_and_limit

*/
function importReqDataFromXML($fileName)
{
	$dom = domxml_open_file($fileName);
	$xmlReqs = null;
  $field_size=config_get('field_size');  


	if ($dom)
		$xmlReqs = $dom->get_elements_by_tagname("requirement");
	
	$xmlData = null;
	$num_elem=sizeof($xmlReqs);
	
	for($i = 0;$i < $num_elem ;$i++)
	{
		$xmlReq = $xmlReqs[$i];
		if ($xmlReq->node_type() != XML_ELEMENT_NODE)
			continue;
		$xmlData[$i]['req_doc_id'] = trim_and_limit(getNodeContent($xmlReq,"docid"),$field_size->req_docid);
		$xmlData[$i]['title'] = trim_and_limit(getNodeContent($xmlReq,"title"),$field_size->req_title);
		$xmlData[$i]['description'] = getNodeContent($xmlReq,"description");
	}
	
	return $xmlData;
}


/*
  function: 

  args :
  
  returns: 

*/
function doImport(&$db,$userID,$idSRS,$fileName,$importType,$emptyScope,$conflictSolution,$bImport)
{
	$arrImportSource = loadImportedReq($fileName, $importType);
	
	$arrImport = null;
	if (count($arrImportSource))
	{
		// $arrReqTitles = getReqTitles($db,$idSRS);
		$map_cur_reqdoc_id = getReqDocIDs($db,$idSRS);
		
		if ($bImport)
		{
			$arrImport = executeImportedReqs($db,$arrImportSource, $map_cur_reqdoc_id, 
		                                   $conflictSolution, $emptyScope, $idSRS, $userID);
		}
		else
		{
			$arrImport = compareImportedReqs($arrImportSource, $map_cur_reqdoc_id);
		}	
	}
	return $arrImport;
}

function exportReqDataToCSV($reqData)
{
	$sKeys = array(
					"req_doc_id",
					"title",
					"scope",
				   );
	return exportDataToCSV($reqData,$sKeys,$sKeys,0,',');
}


function getReqCoverage($reqs,$execMap,&$coveredReqs)
{
	$arrCoverage = array(
						"passed" => array(),
						"failed" => array(),
						"blocked" => array(),
						"not_run" => array(),
					);
	$coveredReqs = null;
	if (sizeof($reqs))
	{
		foreach($reqs as $id => $tc)
		{
			$n = sizeof($tc);
			$nPassed = 0;
			$nBlocked = 0;
			$nFailed = 0;
			$req = array("id" => $id,
						 "title" => "",
						 );
			if (sizeof($tc))
				$coveredReqs[$id] = 1;
			for($i = 0;$i < sizeof($tc);$i++)
			{
				$tcInfo = $tc[$i];	
				if (!$i)
					$req['title'] = $tcInfo['title'];
				$execTc = $tcInfo['testcase_id'];
				if ($execTc)
					$req['tcList'][] = array(
												"tcID" => $execTc,
												"title" => $tcInfo['title']
											); 
				
				
				$exec = 'n';
				if (isset($execMap[$execTc]) && sizeof($execMap[$execTc]))
				{
					$execInfo = end($execMap[$execTc]);
					$exec = isset($execInfo['status']) ? $execInfo['status'] : 'n';
				}
				if ($exec == 'p')
					$nPassed++;		
				else if ($exec == 'b')
					$nBlocked++;		
				else if ($exec == 'f')
					$nFailed++;					
			}
			if ($nFailed)
				$arrCoverage['failed'][] = $req;			
			else if ($nBlocked)
				$arrCoverage['blocked'][] = $req;			
			else if (!$nPassed)
				$arrCoverage['not_run'][] = $req;
			else if ($nPassed == $n)
				$arrCoverage['passed'][] = $req;
			else 
				$arrCoverage['failed'][] = $req;
		}
	}
	return $arrCoverage;
}

function getLastExecutions(&$db,$tcs,$tpID)
{	
	$execMap = array();
	if (sizeof($tcs))
	{
		$tcase_mgr = new testcase($db);
		foreach($tcs as $tcID => $tcInfo)
		{
			$tcversion_id = $tcInfo['tcversion_id'];
		    $execMap[$tcID] = $tcase_mgr->get_last_execution($tcID,$tcversion_id,$tpID,ANY_BUILD,GET_NO_EXEC);
		}
	}
	return $execMap;
}

// 20061009 - franciscom
function getReqByReqdocId(&$db,$reqdoc_id)
{
	$sql = "SELECT * FROM requirements " .
	       " WHERE req_doc_id='" . $db->prepare_string($reqdoc_id) . "'";

	return($db->fetchRowsIntoMap($sql,'id'));
}


// 20061014 - franciscom
function check_syntax($fileName,$importType)
{
	switch($importType)
	{
		case 'csv':
			$pfn = "check_syntax_csv";
			break;

		case 'csv_doors':
			$pfn = "check_syntax_csv_doors";
			break;

		case 'XML':
			$pfn = "check_syntax_xml";
			break;
	}
	if ($pfn)
	{
		$data = $pfn($fileName);
		return $data;
	}
	return;
}

function check_syntax_xml($fileName)
{
  $ret=array();
  $ret['status_ok']=1;
  $ret['msg']='ok';
  
  //@ -> shhhh!!!! silence please
  if (!$dom = @domxml_open_file($fileName)) 
  {
    $ret['status_ok']=0;
    $ret['msg']=lang_get('file_is_not_xml');
  }  
  return($ret);
}


function check_syntax_csv($fileName)
{
  $ret=array();
  $ret['status_ok']=1;
  $ret['msg']='ok';
  return($ret);
}



// Must be implemented !!!
function check_syntax_csv_doors($fileName)
{
  $ret=array();
  $ret['status_ok']=1;
  $ret['msg']='ok';
  
  return($ret);
}


// 20061224 - francisco.mancardi@gruppotesi.com
function get_srs_by_id(&$db,$srs_id)
{
	$output=null;
	
	$sql = "SELECT * FROM req_specs WHERE id={$srs_id}";
	$output = $db->fetchRowsIntoMap($sql,'id');
	return($output);
}
?>
