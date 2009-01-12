<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource $RCSfile: requirements.inc.php,v $
 * @version $Revision: 1.74 $
 * @modified $Date: 2009/01/12 07:55:27 $ by $Author: franciscom $
 *
 * @author Martin Havlat <havlat@users.sourceforge.net>
 *
 * Functions for support requirement based testing
 *
 * Revisions:
 *
 * 20081103 - sisajr     - DocBook XML import
 * 20070710 - franciscom - BUGID 939
 * 20070705 - franciscom - improved management of arrReqStatus
 * 20070617 - franciscom - removed include of deprecated file
 * 20070310 - franciscom - changed return type createRequirement()
 */
////////////////////////////////////////////////////////////////////////////////


require_once("print.inc.php");
require_once("requirement_spec_mgr.class.php");
require_once("requirement_mgr.class.php");

// 20080414 - franciscom
// $arrReqStatus = init_labels(config_get('req_status'));

$g_reqFormatStrings = array (
							"csv" => lang_get('req_import_format_description1'),
							"csv_doors" => lang_get('req_import_format_description2'),
							"XML" => lang_get('the_format_req_xml_import'),
							"DocBook" => lang_get('req_import_format_docbook')
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
	$output =  buildHTMLHeader($title,$base_href);
	$tprojectInfo = $tproject->get_by_id($testproject_id);
	$output .= printFirstPage($db,"req_spec_short","requirement",$title." - ".$prodName,$tprojectInfo,$user_id);
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
	$req_spec_mgr = new requirement_spec_mgr($db);
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
  	$req_spec_mgr = new requirement_spec_mgr($db);

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
			
		// 20081103 - sisajr
		case 'DocBook':
			$pfn = "importReqDataFromDocBook";
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


// 20081103 - sisajr
/** Constants used for parsing DocBook XML files */
define("DOCBOOK_REQUIREMENT", "sect3");
define("DOCBOOK_TITLE", "title");
define("DOCBOOK_PARAGRAPH", "para");
define("DOCBOOK_ORDERED_LIST", "orderedlist");
define("DOCBOOK_LIST_ITEM", "listitem");
define("DOCBOOK_TABLE", "informaltable");
define("DOCBOOK_TABLE_GROUP", "tgroup");
define("DOCBOOK_TABLE_HEAD", "thead");
define("DOCBOOK_TABLE_BODY", "tbody");
define("DOCBOOK_TABLE_ROW", "row");
define("DOCBOOK_TABLE_ENTRY", "entry");

/**
 * Parses one 'informaltable' XML entry and produces HTML table as string.
 *
 * XML relationship:
 * informaltable -> tgroup -> thead -> row -> entry
 *                         -> tbody -> row -> entry
 *
 * 20081103 - sisajr
 */
function getDocBookTableAsHtmlString($docTable)
{
	$resultTable = "";
	foreach ($docTable->child_nodes() as $tgroup)
	{
		if ($tgroup->node_name() != DOCBOOK_TABLE_GROUP)
			continue;

		$table = "";
		foreach ($tgroup->child_nodes() as $tbody)
		{
			// get table head
			if ($tbody->node_name() == DOCBOOK_TABLE_HEAD)
			{
				foreach ($tbody->child_nodes() as $row)
				{
					if ($row->node_name() != DOCBOOK_TABLE_ROW)
						continue;

					$table_row = "<tr>";

					foreach ($row->child_nodes() as $entry)
						if ($entry->node_name() == DOCBOOK_TABLE_ENTRY)
							$table_row .= "<th>" . $entry->get_content() . "</th>";

					$table_row .= "</tr>";

					$table .= $table_row;
				}
			}

			// get table body - rows
			if ($tbody->node_name() == DOCBOOK_TABLE_BODY)
			{
				foreach ($tbody->child_nodes() as $row)
				{
					if ($row->node_name() != DOCBOOK_TABLE_ROW)
						continue;

					$table_row = "<tr>";

					foreach ($row->child_nodes() as $entry)
						if ($entry->node_name() == DOCBOOK_TABLE_ENTRY)
							$table_row .= "<td>" . $entry->get_content() . "</td>";

					$table_row .= "</tr>";

					$table .= $table_row;
				}
			}
		}

		$resultTable .= "<table>" . $table . "</table>";
	}

	return $resultTable;
}

/**
 * Imports data from DocBook XML
 *
 * 20081103 - sisajr
 */
function importReqDataFromDocBook($fileName)
{
	$dom = domxml_open_file($fileName);
	$xmlReqs = null;
  $field_size=config_get('field_size');  

	// get all Requirement elements in the document
	if ($dom)
		$xmlReqs = $dom->get_elements_by_tagname(DOCBOOK_REQUIREMENT);
	
	$xmlData = null;
	$num_elem=sizeof($xmlReqs);

	$counter = array();
	
	// for each Requirement we need this: Req_doc_id, Title, Description
	for($i = 0;$i < $num_elem ;$i++)
	{
		$xmlReq = $xmlReqs[$i];
		if ($xmlReq->node_type() != XML_ELEMENT_NODE)
			continue;

		// get all child elements of this requirement
		$children = $xmlReq->child_nodes();

		$description = "";
		foreach ($children as $child)
		{
			// requirement title
			if ($child->node_name() == DOCBOOK_TITLE )
			{
				foreach ($child->child_nodes() as $a)
					if ($a->node_name() == "remark" || $a->node_name() == "note" )
						$child->remove_child($a);

				$title = $child->get_content();
				continue;
			}

			// part of description as ordered list
			if ($child->node_name() == DOCBOOK_ORDERED_LIST)
			{
				$list = "";
				foreach ($child->child_nodes() as $item)
					if ($item->node_name() == DOCBOOK_LIST_ITEM)
						$list .= "<li>" . $item->get_content() . "</li>";
					else
						$list .= "<p>" . $item->get_content() . "</p>";

				$description .= "<ul>" . $list . "</ul>";
				continue;
			}

			// part of description as table
			if ($child->node_name() == DOCBOOK_TABLE)
			{
				$description .= getDocBookTableAsHtmlString($child);
				continue;
			}

			// part of description as paragraph
			if ($child->node_name() == DOCBOOK_PARAGRAPH)
			{
				$description .= "<p>" . $child->get_content() . "</p>";
				continue;
			}

			// default behaviour: use unknown node content as part of description
			$description .= "<p>" . $child->get_content() . "</p>";
		}

		$xmlData[$i]['description'] = $description; 
		$xmlData[$i]['title'] = trim_and_limit($title,$field_size->req_title);

		// parse Doc ID from requirement title

		// first remove any weird characters before the title. This could be probably omitted
		$xmlData[$i]['title'] = preg_replace("/^[^a-zA-Z_0-9]*/","",$xmlData[$i]['title']);

		// get Doc ID

		// this will create Doc ID as words ended with number
		// Example: Req BL 20 Business Logic
		// Doc ID: Req BL 20
		//if (preg_match("/[ a-zA-Z_]*[0-9]*/", $xmlData[$i]['title'], $matches))
		//{
		//	$xmlData[$i]['req_doc_id'] = $matches[0];
		//}

		// this matches first two words in Title and adds counter started from 1
		// Doc ID is grouped (case insensitive), so different groups have their own counter running
		// Example: Req BL Business Logic
		// Doc ID: Req BL 1
		if (preg_match("/[ ]*[a-zA-Z_0-9]*[ ][a-zA-Z_0-9]*/", $xmlData[$i]['title'], $matches))
		{
			$index = strtolower($matches[0]);
			if( !isset($counter[$index]) )
				$counter[$index] = 0;
			$counter[$index]++;
			$xmlData[$i]['req_doc_id'] = $matches[0] . " " . $counter[$index];
		}
		// Note: Doc ID doesn't need trim_and_limit since it is parsed from Title
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

/*
  function:

  args :

  returns:

*/
function exportReqDataToCSV($reqData)
{
	$sKeys = array(
					"req_doc_id",
					"title",
					"scope",
				   );
	return exportDataToCSV($reqData,$sKeys,$sKeys,0,',');
}


/*
  function: 

  args :
  
  returns: 

*/
// function getReqCoverage($reqs,$execMap,&$coveredReqs)
// {
// 
//   $resultsCfg = config_get('results');
//   
// 	$arrCoverage = array(
// 						"passed" => array(),
// 						"failed" => array(),
// 						"blocked" => array(),
// 						"not_run" => array(),
// 					);
// 	$coveredReqs = null;
// 	if (sizeof($reqs))
// 	{
// 		foreach($reqs as $id => $tc)
// 		{
// 			$n = sizeof($tc);
// 			$nPassed = 0;
// 			$nBlocked = 0;
// 			$nFailed = 0;
// 			$req = array("id" => $id, "title" => "");
// 			
// 			if (sizeof($tc))
// 				$coveredReqs[$id] = 1;
// 			for($i = 0;$i < sizeof($tc);$i++)
// 			{
// 				$tcInfo = $tc[$i];
// 				if (!$i)
// 					$req['title'] = $tcInfo['req_title'];
// 				$execTc = $tcInfo['testcase_id'];
// 
// 				// BUGID 1063
// 				if ($execTc)
// 					$req['tcList'][] = array(
// 												"tcID" => $execTc,
// 												"title" => $tcInfo['testcase_name'],
// 												"tcaseExternalID" => $tcInfo['tc_external_id'],
// 												"status" => $resultsCfg['status_code']['not_run'],
// 												"status_label" => $resultsCfg['status_label']['not_run']
// 											);
//         $current_index = count($req['tcList']);
//         $current_index = $current_index > 0 ? $current_index-1 : 0;
// 
// 				$exec = 'n';
// 				if (isset($execMap[$execTc]) && sizeof($execMap[$execTc]))
// 				{
// 					$execInfo = end($execMap[$execTc]);
// 					$exec = isset($execInfo['status']) ? $execInfo['status'] : 'n';
// 				}
// 				$req['tcList'][$current_index]['status']=$exec;
// 				$req['tcList'][$current_index]['status_label']=$resultsCfg['status_label'][$resultsCfg['code_status'][$exec]];
// 				
// 				
// 				if ($exec == 'p')
// 					$nPassed++;
// 				else if ($exec == 'b')
// 					$nBlocked++;
// 				else if ($exec == 'f')
// 					$nFailed++;
// 			}
// 			if ($nFailed)
// 				$arrCoverage['failed'][] = $req;
// 			else if ($nBlocked)
// 				$arrCoverage['blocked'][] = $req;
// 			else if (!$nPassed)
// 				$arrCoverage['not_run'][] = $req;
// 			else if ($nPassed == $n)
// 				$arrCoverage['passed'][] = $req;
// 			else
// 				$arrCoverage['failed'][] = $req;
// 		}
// 	}
// 	return $arrCoverage;
// }
//
// 20090111 - franciscom
function getReqCoverage($reqs,&$execMap)
{
  $coverageAlgorithm=config_get('req_cfg')->coverageStatusAlgorithm;
  $resultsCfg=config_get('results');
  $status2check=array_keys($resultsCfg['status_label_for_exec_ui']);
 
  $coverage['byStatus']=null;
  $coverage['withTestCase']=null;
  $coverage['withoutTestCase']=null;
  
  
  $coverage['byStatus']=$resultsCfg['status_label_for_exec_ui'];
  $status_counters=array();
  foreach($coverage['byStatus'] as $status_code => $value)
  {
      $coverage['byStatus'][$status_code]=array();
      $status_counters[$resultsCfg['status_code'][$status_code]]=0;
  }

	$reqs_qty=count($reqs);
	if($reqs_qty > 0)
	{
		foreach($reqs as $requirement_id => $req_tcase_set)
		{
        $first_key=key($req_tcase_set);
			  $item_qty = count($req_tcase_set);

			  $req = array("id" => $requirement_id, 
			                "title" => $req_tcase_set[$first_key]['req_title']);
			  foreach($status_counters as $key => $value)
			  {
			      $status_counters[$key]=0;
			  }
        
			  if( $req_tcase_set[$first_key]['testcase_id'] > 0 )
			  {
			  	$coverage['withTestCase'][$requirement_id] = 1;
			  }
			  else
			  {
			    $coverage['withoutTestCase'][$requirement_id] = $req;  
			  }
			  	
			  for($idx = 0; $idx < $item_qty; $idx++)
			  {
			       $item_info=$req_tcase_set[$idx];
			       if( $idx==0 ) // just to avoid useless assignments
			       {
			           $req['title']=$item_info['req_title'];  
			       } 
             
			  	   // BUGID 1063
			  	   if( $item_info['testcase_id'] > 0 )
			  	   {
                  $exec_status = $resultsCfg['status_code']['not_run'];
			  	        if (isset($execMap[$item_info['testcase_id']]) && sizeof($execMap[$item_info['testcase_id']]))
			  	        {
			  	            $execInfo = end($execMap[$item_info['testcase_id']]);
			  	            if( isset($execInfo['status']) && trim($execInfo['status']) !='')
			  	            {
			  	        	       $exec_status = $execInfo['status'];
			  	        	   }    
			  	        }
			  	        $status_counters[$exec_status]++;
			            $req['tcList'][] = array("tcID" => $item_info['testcase_id'],
			                                     "title" => $item_info['testcase_name'],
			             						            "tcaseExternalID" => $item_info['tc_external_id'],
			  	   		  						            "status" => $exec_status,
			  	   		  						            "status_label" => $resultsCfg['status_label']
			  	   		  						                                         [$resultsCfg['code_status'][$exec_status]]);
             }
			   } // for($idx = 0; $idx < $item_qty; $idx++)
		    
			   // We analyse counters
			   $go_away=0;
         foreach( $coverageAlgorithm['checkOrder'] as $checkKey )
         {
             foreach( $coverageAlgorithm['checkType'][$checkKey] as $tcase_status )
             {
                 if($checkKey == 'atLeastOne')
                 {
                     if($status_counters[$resultsCfg['status_code'][$tcase_status]] > 0 )
                     {
                         $coverage['byStatus'][$tcase_status][] = $req;
                         $go_away=1;
                         break;
                     }
                 }
                 if($checkKey == 'all')
                 {
                     if($status_counters[$resultsCfg['status_code'][$tcase_status]] == $item_qty )
                     {
                         $coverage['byStatus'][$tcase_status][] = $req;
                         $go_away=1;
                         break;
                     }
                 }
             }  
             if($go_away)
             {
                break;
             }
         }
		} // foreach($reqs as $requirement_id => $req_tcase_set)
	}
	return $coverage;
}


/*
  function: 

  args :
  
  returns: 

*/
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
		// 20081103 - sisajr
		case 'DocBook':
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

/*
  function: 

  args:
  
  returns: 

*/
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

