<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 * 
 * Functions for support requirement based testing
 *
 * @package 	TestLink
 * @author 		Martin Havlat
 * @copyright 	2007-2009, TestLink community 
 * @version    	CVS: $Id: requirements.inc.php,v 1.108 2010/06/24 17:25:53 asimon83 Exp $
 * @link 		http://www.teamst.org/index.php
 *
 * @internal Revisions:
 *
 * 20100508 - franciscom - BUGID 3447: CVS Import - add new column type 
 * 20100301 - asimon - modified req_link_replace()
 * 20091202 - franciscom - added contribution req_link_replace()
 * 20090815 - franciscom - get_last_execution() call changes
 * 20090402 - amitkhullar - added TC version while displaying the Req -> TC Mapping 
 * 20090331 - amitkhullar - BUGFIX 2292
 * 20090304 - franciscom - BUGID 2171
 * 20081103 - sisajr - DocBook XML import
 * 20070710 - franciscom - BUGID 939
 * 20070705 - franciscom - improved management of arrReqStatus
 * 20070617 - franciscom - removed include of deprecated file
 * 20070310 - franciscom - changed return type createRequirement()
 */

/** inlude basic functions for printing Test Specification document */
require_once("print.inc.php"); //TODO is this needed here anymore after removal of render functions?


// printing code commented out and moved to other printing functions
// can be deleted later
///**
// * render Requirement Specification
// * @author Martin Havlat
// * 
// * @param resource &$db reference to database handler
// * @param integer $srs_id requirements specification identifier
// * @param string $tproject_id
// * @param string $user_id
// * @param string $base_href
// * 
// * @return string complete HTML source
// *
// * @uses print.inc.php 
// * @todo havlatm: refactore and move to other printing functions
// *  
// **/
//function renderSRS(&$db,$srs_id, $tproject_id, $user_id, $base_href)
//{
//	$tproject_mgr = new testproject($db);
//	$tprojectInfo = $tproject_mgr->get_by_id($tproject_id);
//	
//	$doc_info = new stdClass(); 
//	$doc_info->tproject_name = htmlspecialchars($tprojectInfo["name"]);
//	$doc_info->tproject_scope = $tprojectInfo['notes'];
//	$doc_info->title = '';
//	$doc_info->type_name = '';
//	
//	$arrSpec = $tproject_mgr->getReqSpec($tproject_id,$srs_id);
//	$doc_info->author =  gendocGetUserName($db, $arrSpec[0]['author_id']);
//	
//	$output =  renderHTMLHeader($arrSpec[0]['title'],$base_href);
//	$output .= renderFirstPage($doc_info);
//	
//	$output .= "<h2>" . lang_get('scope') . "</h2>\n<div>" . $arrSpec[0]['scope'] . "</div>\n";
//	$output .= renderRequirements($db,$srs_id);
//	$output .= "\n</body>\n</html>";
//	
//	return $output;
//}

///**
// * render Requirement for SRS
// *
// * @param resource &$db reference to database handler
// * @param integer $srs_id
// *
// * @author Martin Havlat
// **/
//function renderRequirements(&$db,$srs_id)
//{
//	$req_spec_mgr = new requirement_spec_mgr($db);
//	$arrReq = $req_spec_mgr->get_requirements($srs_id);
//
//	$output = "<h2>" . lang_get('reqs') . "</h2>\n<div>\n";
//	if (count($arrReq))
//	{
//		foreach ($arrReq as $REQ)
//		{
//			$output .= '<h3>' . htmlspecialchars($REQ["req_doc_id"]). " - " .
//					   htmlspecialchars($REQ['title']) . "</h3>\n<div>" .
//					   $REQ['scope'] . "</div>\n";
//		}
//	}
//	else
//	{
//		$output .= '<p>' . lang_get('none') . '</p>';
//	}
//	$output .= "\n</div>";
//
//	return $output;
//}


/**
 * exportReqDataToXML
 *
 */
function exportReqDataToXML($reqData)
{            
  
	$rootElem = "<requirements>{{XMLCODE}}</requirements>";
	$elemTpl = "\t".'<requirement><docid><![CDATA['."\n||DOCID||\n]]>".
	           '</docid><title><![CDATA['."\n||TITLE||\n]]>".'</title>'.
	   	       '<description><![CDATA['."\n||DESCRIPTION||\n]]>".'</description>'.
					   '</requirement>'."\n";
	$info = array("||DOCID||" => "req_doc_id","||TITLE||" => "title",
				  "||DESCRIPTION||" => "scope");
	return exportDataToXML($reqData,$rootElem,$elemTpl,$info);
}


/** Process CVS file contents with requirements into TL
 *  and creates an array with reports
 *  @return array_of_strings list of particular REQ data with resolution comment
 *
 *
 **/
function executeImportedReqs(&$db,$arrImportSource, $map_cur_reqdoc_id,$conflictSolution, 
							 $emptyScope, $idSRS, $tprojectID, $userID)
{
	define('SKIP_CONTROLS',1);

	$req_mgr = new requirement_mgr($db);
	$import_status = null;
	$field_size = config_get('field_size');
	
	foreach ($arrImportSource as $data)
	{
		$docID = trim_and_limit($data['req_doc_id'],$field_size->req_docid);
		$title = trim_and_limit($data['title'],$field_size->req_title);
		$scope = $data['description'];
		$type = $data['type'];
		$status = $data['status'];
		$expected_coverage = $data['expected_coverage'];
		$node_order = $data['order'];
	
		if (($emptyScope == 'on') && empty($scope))
		{
			// skip rows with empty scope
			$import_status = lang_get('req_import_result_skipped');
		}
		else
		{
			$crash = $map_cur_reqdoc_id && array_search($docID, $map_cur_reqdoc_id);
			if($crash)
			{
				// process conflict according to choosen solution
				tLog('Conflict found. solution: ' . $conflictSolution);
				$import_status['msg'] = 'Error';
				if ($conflictSolution == 'overwrite')
				{
					$item = current($req_mgr->getByDocID($docID,$tprojectID));
					$last_version = $req_mgr->get_last_version_info($item['id']);
					$op = $req_mgr->update($item['id'],$last_version['id'],$docID,$title,$scope,$userID,
						                   $status,$type,$expected_coverage,$node_order,SKIP_CONTROLS);
					if( $op['status_ok']) 
					{
						$import_status['msg'] = lang_get('req_import_result_overwritten');
					}
				}
				elseif ($conflictSolution == 'skip') 
				{
					// no work
					$import_status['msg'] = lang_get('req_import_result_skipped');
				}
			} 
			else 
			{
				// no conflict - just add requirement
				$import_status = $req_mgr->create($idSRS,$docID,$title,$scope,$userID,$status,$type,
				                                  $expected_coverage,$node_order);
			}
			$arrImport[] = array('doc_id' => $docID, 'title' => $title, 'import_status' => $import_status['msg']);
		}
	}
	return $arrImport;
}

/*
 On version 1.9 is NOT USED when importing from XML format
*/
function compareImportedReqs(&$dbHandler,$arrImportSource,$tprojectID,$reqSpecID)
{
	$reqCfg = config_get('req_cfg');
	$labels = array('type' => $reqCfg->type_labels, 'status' => $reqCfg->status_labels);
	$verbose = array('type' => null, 'status' => null);
	$cache = array('type' => null, 'status' => null);
	$cacheKeys = array_keys($cache);
	
	$unknown_code = lang_get('unknown_code');
	$reqMgr = new requirement_mgr($dbHandler);
	$arrImport = null;
	if( ($loop2do=count($arrImportSource)) )
	{
		$getOptions = array('output' => 'minimun');
		$messages = array('ok' => '', 'import_req_conflicts_other_branch' => '','import_req_exists_here' => '');
		foreach($messages as $key => $dummy)
		{
			$messages[$key] = lang_get($key);
		}
	    				
		for($idx=0 ; $idx < $loop2do; $idx++)
		{
			$msgID = 'ok';
			$req = $arrImportSource[$idx];

    		// Check:
            // If item with SAME DOCID exists inside container
			// If there is a hit
			//	   We will follow user option: update,create new version
			//
			// If do not exist check must be repeated, but on WHOLE test project
			// 	If there is a hit -> we can not create
			//		else => create
			// 
            // 
            // 20100321 - we do not manage yet user option
			$check_in_reqspec = $reqMgr->getByDocID($req['req_doc_id'],$tprojectID,$reqSpecID,$getOptions);
     		if(is_null($check_in_reqspec))
			{
				$check_in_tproject = $reqMgr->getByDocID($req['req_doc_id'],$tprojectID,null,$getOptions);
				if(!is_null($check_in_tproject))
				{
					$msgID = 'import_req_conflicts_other_branch';	
               	}             		 
            }
            else
            {
            	$msgID = 'import_req_exists_here';
            }
            
			foreach($cacheKeys as $attr)
			{
				if( isset($labels[$attr][$req[$attr]]) )
				{
					if( !isset($cache[$attr][$req[$attr]]) )
					{
						$cache[$attr][$req[$attr]] = lang_get($labels[$attr][$req[$attr]]);
					}
					$verbose[$attr] = $cache[$attr][$req[$attr]];
				}
				else
				{
					$verbose[$attr] = sprintf($unknown_code,$req[$attr]);
				}
			}
			
			$arrImport[] = array('req_doc_id' => $req['req_doc_id'], 'title' => trim($req['title']),
			                     'scope' => $req['description'], 'type' => $verbose['type'], 
			                     'status' => $verbose['status'], 'expected_coverage' => $req['expected_coverage'],
			                     'node_order' => $req['order'], 'check_status' => $messages[$msgID]);
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
function loadImportedReq($fileName, $importType)
{
	$data = null;
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

		case 'DocBook':
			$pfn = "importReqDataFromDocBook";
			break;
	}
	if ($pfn)
	{
		$data = $pfn($fileName);
	}
	return $data;

}

/**
 * importReqDataFromCSV
 *
 */
function importReqDataFromCSV($fileName)
{
  	$field_size=config_get('field_size');
  	
  	// CSV line format
	$fieldMappings = array("req_doc_id","title","description","type","status","expected_coverage","order");
	$options = array('delimiter' => ',' , 'fieldQty' => count($fieldMappings));
	$reqData = importCSVData($fileName,$fieldMappings,$options);

	if($reqData)
	{
  		// lenght will be adjusted to these values
  		$field_length = array("req_doc_id" => $field_size->req_docid, "title" => $field_size->req_title);
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

/**
 * importReqDataFromCSVDoors
 *
 */
function importReqDataFromCSVDoors($fileName)
{
	// Some keys are strings, other numeric
	$fieldMappings = array("Object Identifier" => "title","Object Text" => "description",
					       "Created By","Created On","Last Modified By","Last Modified On");
	
	$options = array('delimiter' => ',', 'fieldQty' => count($fieldMappings), 'processHeader' => true);
	$reqData = importCSVData($fileName,$fieldMappings,$options);

	return $reqData;
}

/**
 * Parses one 'informaltable' XML entry and produces HTML table as string.
 *
 * XML relationship:
 * informaltable -> tgroup -> thead -> row -> entry
 *                         -> tbody -> row -> entry
 *
 * 20081103 - sisajr
 */
function getDocBookTableAsHtmlString($docTable,$parseCfg)
{
	$resultTable = "";
	foreach ($docTable->children() as $tgroup)
	{
		if ($tgroup->getName() != $parseCfg->table_group)
		{
			continue;
    	}
    
		$table = "";
		foreach ($tgroup->children() as $tbody)
		{
			// get table head
			$tbodyName = $tbody->getName() ;
			$doIt = false;
			if( $tbodyName == $parseCfg->table_head)
			{
				$cellTag = array('open' => '<th>', 'close' => '</th>');
				$doIt = true;
			}
			else if( $tbodyName == $parseCfg->table_body)
			{                                           
				$cellTag = array('open' => '<td>', 'close' => '</td>');
				$doIt = true;
			}

			if( $doIt )
			{
				foreach ($tbody->children() as $row)
				{
					if( $row->getName() == $parseCfg->table_row )
					{
						$table_row = "<tr>";
						foreach ($row->children() as $entry)
						{
							if ( ($ename = $entry->getName()) == $parseCfg->table_entry)
							{
								if( $entry->count() == 0 )
								{
									$table_row .= $cellTag['open'] . (string)$entry . $cellTag['close'];
								}
								else
								{
									$table_row .= $cellTag['open'];
									foreach($parseCfg->table_entry_children as $ck)
									{
										if( property_exists($entry,$ck) )
										{
											$table_row .= (string)$entry->$ck;
										}
									}	
									$table_row .= $cellTag['close'];
								}
							}	
          				}
            	    	
						$table_row .= "</tr>";
						$table .= $table_row;
          			}
				}
			}
		}

		$resultTable .= "<table border=\"1\">" . $table . "</table>";
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
	$req_cfg = config_get('req_cfg');
	$docbookCfg = $req_cfg->importDocBook;
	// $docbookCfg->requirement= "sect3";
	// $docbookCfg->title= "title";
	// $docbookCfg->paragraph= "para";
	// $docbookCfg->ordered_list="orderedlist";
	// $docbookCfg->list_item="listitem";
	// $docbookCfg->table="informaltable";
	// $docbookCfg->table_group="tgroup";
	// $docbookCfg->table_head="thead";
	// $docbookCfg->table_body="tbody";
	// $docbookCfg->table_row="row";
	// $docbookCfg->table_entry="entry";
	// $docbookCfg->list_item_children = array('para','title');
	// $docbookCfg->table_entry_children = array('para');

	$xmlReqs = null;
	$xmlData = null;
  	$field_size=config_get('field_size');  
	
	$simpleXMLObj = simplexml_load_file($fileName);
	$num_elem = count($simpleXMLObj->sect1);

	$idx=0;	
	foreach($simpleXMLObj->sect1 as $xmlReq)
	{
		// get all child elements of this requirement
		// $title = (string)$xmlReq->title;
		// echo $title . '<br>'; 
		$title = "";
		$description = "";
		$children = $xmlReq->children();
		foreach ($children as $child)
		{                        
			$nodeName = $child->getName();
			// echo 'node name:' . $nodeName .'<br>';

			if ($nodeName == $docbookCfg->title )
			{
				// echo 'INSIDE::' . $nodeName . '<br>';
				$title = (string)$child;
				// echo '$title:' . $title .'<br>';
				
			}	
			else if ($nodeName == $docbookCfg->ordered_list)
			{
				// echo 'INSIDE' . $nodeName . '<br>';
				$list = "";
				foreach( $child->children() as $item )
				{
					// echo 'xxx' . $item->getName() . '<br>';
					if( $item->getName() == $docbookCfg->list_item )
					{
						if( $item->count() == 0 )
						{
							$list .= "<li>" . (string)$item . "</li>";
						}
						else
						{
							foreach($docbookCfg->list_item_children as $ck)
							{
								if( property_exists($item,$ck) )
								{
									$list .= "<li>" . (string)$item->$ck . "</li>";
								}
							}	
						}
					}
				}
				$description .= "<ul>" . $list . "</ul>";
			}
			else if ($nodeName == $docbookCfg->table)
			{
				// echo 'INSIDE: ' . $nodeName . '<br>';
				$description .= getDocBookTableAsHtmlString($child,$docbookCfg);
			}
			else if ($nodeName == $docbookCfg->paragraph)
			{
				$description .= "<p>" . (string)$child . "</p>";
			}
			else
			{
				$description .= "<p>" . (string)$child . "</p>";
			}


		}

		// echo '$description:' . '<xmp>' . $description . '</xmp>' . '<br>';

		$xmlData[$idx]['description'] = $description; 
		$xmlData[$idx]['title'] = trim_and_limit($title,$field_size->req_title);
    	
		// parse Doc ID from requirement title
    	
		// first remove any weird characters before the title. This could be probably omitted
		$xmlData[$idx]['title'] = preg_replace("/^[^a-zA-Z_0-9]*/","",$xmlData[$idx]['title']);
    	
		// get Doc ID
    	//
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
		// Note: Doc ID doesn't need trim_and_limit since it is parsed from Title
		// new dBug($xmlData[$idx]['title']);

		if (preg_match("/[ ]*[a-zA-Z_0-9]*[ ][a-zA-Z_0-9]*/", $xmlData[$idx]['title'], $matches))
		{
			$index = strtolower($matches[0]);
			if( !isset($counter[$index]) )
			{
				$counter[$index] = 0;
			}
			$counter[$index]++;
			$xmlData[$idx]['req_doc_id'] = $matches[0] . " " . $counter[$index];
		}
		$idx++;
	}
	
	new dBug($xmlData);	
	return $xmlData;
}


/*
  function:

  args :

  returns:

*/
function doReqImport(&$dbHandler,$tprojectID,$userID,$reqSpecID,$fileName,$importType,$emptyScope,
					 $conflictSolution,$doImport)
{
	$arrImportSource = loadImportedReq($fileName, $importType);
	$arrImport = null;

	if (count($arrImportSource))
	{
		$map_cur_reqdoc_id = getReqDocIDs($dbHandler,$reqSpecID);
		if ($doImport)
		{
			$arrImport = executeImportedReqs($dbHandler,$arrImportSource, $map_cur_reqdoc_id,
		                                     $conflictSolution, $emptyScope, $reqSpecID, $tprojectID, $userID);
		}
		else
		{
			$arrImport = compareImportedReqs($dbHandler,$arrImportSource,$tprojectID,$reqSpecID);
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
	$sKeys = array("req_doc_id","title","scope");
	return exportDataToCSV($reqData,$sKeys,$sKeys,0,',');
}


/**
 * getReqCoverage
 *
 */
function getReqCoverage(&$dbHandler,$reqs,&$execMap)
{
	$tree_mgr = new tree($dbHandler);
	
	$coverageAlgorithm=config_get('req_cfg')->coverageStatusAlgorithm;
	$resultsCfg=config_get('results');
	$status2check=array_keys($resultsCfg['status_label_for_exec_ui']);
	
	// $coverage['byStatus']=null;
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
			$req = array("id" => $requirement_id, "title" => $req_tcase_set[$first_key]['req_title'],
			             "req_doc_id" => $req_tcase_set[$first_key]["req_doc_id"]);
			
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
					$tcase_path='';
					if (isset($execMap[$item_info['testcase_id']]) && sizeof($execMap[$item_info['testcase_id']]))
					{
					    $execInfo = end($execMap[$item_info['testcase_id']]);
					    $tcase_path=$execInfo['tcase_path'];
					    if( isset($execInfo['status']) && trim($execInfo['status']) !='')
					    {
						       $exec_status = $execInfo['status'];
						}    
					}
					else
					{
						$path_info=$tree_mgr->get_full_path_verbose($item_info['testcase_id']);
						unset($path_info[$item_info['testcase_id']][0]); // remove test project name
						$path_info[$item_info['testcase_id']][]='';
						$tcase_path=implode(' / ',$path_info[$item_info['testcase_id']]);
					}
				    $status_counters[$exec_status]++;
					$req['tcList'][] = array("tcID" => $item_info['testcase_id'],
			                                 "title" => $item_info['testcase_name'],
			        		                 "tcaseExternalID" => $item_info['testcase_external_id'],
					                         "version" => $item_info['testcase_version'],
			        		                 "tcase_path" => $tcase_path,
				   	  	                     "status" => $exec_status,
				   	  	                     "status_label" => $resultsCfg['status_label'][$resultsCfg['code_status'][$exec_status]]);
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
	                    //-amitkhullar - 20090331 - BUGFIX 2292
	                    elseif ($status_counters[$resultsCfg['status_code'][$tcase_status]] > 0 )
	                    {   
	                        $coverage['byStatus'][$tcase_status][] = $req;
	                        $go_away=1;
	                        break;
	                    }
                        elseif ( isset($coverageAlgorithm['checkFail']) && 
	                             isset($coverageAlgorithm['checkFail'][$checkKey]) &&
	                             isset($req['tcList']) )
	                    { 	 
	                    	 
	                        // BUGID 2171
	                        // ($coverageAlgorithm['checkFail'][$checkKey]==$tcase_status)
	                        // If particular requirement has assigned more than one test cases, and:
	                        // - at least one of assigned test cases was not yet executed
	                        // - the rest of assigned test cases was executed and passed
	                        // then on the "Requirements based report" this particular requirement 
	                        // is not shown at all (in any section). 
	                        $coverage['byStatus'][$coverageAlgorithm['checkFail'][$checkKey]][] = $req;
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

  rev: 20090716 - franciscom - get_last_execution() interface changes
*/
function getLastExecutions(&$db,$tcaseSet,$tplanId)
{
	$execMap = array();
	if (sizeof($tcaseSet))
	{
		$tcase_mgr = new testcase($db);
    	$items=array_keys($tcaseSet);
    	$path_info=$tcase_mgr->tree_manager->get_full_path_verbose($items);
		$options=array('getNoExecutions' => 1, 'groupByBuild' => 0);
		foreach($tcaseSet as $tcaseId => $tcInfo)
		{
		    $execMap[$tcaseId] = $tcase_mgr->get_last_execution($tcaseId,$tcInfo['tcversion_id'],
		                                                         $tplanId,testcase::ANY_BUILD,
		                                                         testcase::ANY_PLATFORM,$options);
		                                                         
        	unset($path_info[$tcaseId][0]); // remove test project name
        	$path_info[$tcaseId][]='';
		    $execMap[$tcaseId][$tcInfo['tcversion_id']]['tcase_path']=implode(' / ',$path_info[$tcaseId]);
		}

    	unset($tcase_mgr); 
	}
	return $execMap;
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
  // if (!$dom = @domxml_open_file($fileName))
  // {
  //   $ret['status_ok']=0;
  //   $ret['msg']=lang_get('file_is_not_xml');
  // }
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

/**
 * replace BBCode-link tagged links in req/reqspec scope with actual links
 *
 * @internal revisions:
 * 20100301 - asimon - added anchor and tproj parameters to tags
 * 
 * @param resource $dbHandler database handle
 * @param string $scope text in which to replace tags with links
 * @param integer $tprojectID ID of testproject to which req/reqspec belongs
 * @return string $scope text with generated links
 */
function req_link_replace($dbHandler, $scope, $tprojectID) 
{
	$tree_mgr = new tree($dbHandler);
	$tproject_mgr = new testproject($dbHandler);
	$prefix = $tproject_mgr->getTestCasePrefix($tprojectID);
	$tables = tlObjectWithDB::getDBTables(array('requirements', 'req_specs'));
	$cfg = config_get('internal_links');
	$string2replace = array();
	$title = array();

	// configure target in which link shall open
	// use a reasonable default value if nothing is set in config
	$cfg->target = isset($cfg->target) ? $cfg->target :'popup';

	switch($cfg->target)
	{
		case 'popup':
			// use javascript to open popup window
			$string2replace['req'] = '<a href="javascript:openLinkedReqWindow(%s,\'%s\')">%s%s</a>';
			$string2replace['req_spec'] = '<a href="javascript:openLinkedReqSpecWindow(%s,\'%s\')">%s%s</a>';
		break;
		
		case 'window':
	    case 'frame':// open in same frame
			$target = ($cfg->target == 'window') ? 'target="_blank"' : 'target="_self"';
			$string2replace['req'] = '<a ' . $target . ' href="lib/requirements/reqView.php?' .
						             'item=requirement&requirement_id=%s#%s">%s%s</a>';
			$string2replace['req_spec'] = '<a ' . $target . ' href="lib/requirements/reqSpecView.php?' .
						                  'item=req_spec&req_spec_id=%s#%s">%s%s</a>';
		break;
    }

    
	// configure link title (first part of the generated link)
	// default: use item type as name (localized name for req)
	$title['req'] = lang_get('requirement') . ": "; 
	// default: use short item type as name (localized name for req spec)
	$title['req_spec'] = lang_get('req_spec_short') . ": ";

	if ($cfg->req_link_title->type == 'string' && $cfg->req_link_title->value != '') {
		$title['req'] = lang_get($cfg->req_link_title->value);
	} else if ($cfg->req_link_title->type == 'none') {
		$title['req'] = '';
	} 
	
	// now for the req specs
	if ($cfg->req_spec_link_title->type == 'string' && $cfg->req_spec_link_title->value != '') {
		// use user-configured string as link title
		$title['req_spec'] = lang_get($cfg->req_spec_link_title->value);
	} else if ($cfg->req_spec_link_title->type == 'none') {
		$title['req_spec'] = '';
	} 

	// now the actual replacing
	$patterns2search = array();
	$patterns2search['req'] =
		"#\[req[\s]*(tproj=([\w]+))*[\s]*(anchor=([\w]+))*[\s]*(tproj=([\w]+))*\](.*)\[/req\]#iU";
	$patterns2search['req_spec'] =
		"#\[req_spec[\s]*(tproj=([\w]+))*[\s]*(anchor=([\w]+))*[\s]*(tproj=([\w]+))*\](.*)\[/req_spec\]#iU";

	$sql2exec = array();
	$sql2exec['req'] = " SELECT id, req_doc_id AS doc_id " .
	                   " FROM {$tables['requirements']} WHERE req_doc_id=";
	 
	$sql2exec['req_spec'] = " SELECT id, doc_id FROM {$tables['req_specs']} " .
	                        " WHERE doc_id=" ;

	foreach($patterns2search as $accessKey => $pattern )
	{
		$matches = array();
		preg_match_all($pattern, $scope, $matches);

		if( count($matches[7]) == 0 )
		{
			continue;
		}

		foreach ($matches[0] as $key => $matched_string) {

			// get testproject prefix, if that was found with regex
			// if not, get prefix of current project
			if ($matches[2][$key] != '') {
				$matched_prefix = $matches[2][$key];
			} else if ($matches[6][$key] != '') {
				$matched_prefix = $matches[6][$key];
			} else {
				$matched_prefix = $prefix;
			}
			
			$matched_anchor = $matches[4][$key];
			$matched_doc_id = $matches[7][$key];
			
			$sql = $sql2exec[$accessKey] . "'{$matched_doc_id}'";
			$rs = $dbHandler->get_recordset($sql);
			
			if (count($rs)) {
				// get root of linked node and check
				$real_root = $tree_mgr->getTreeRoot($rs[0]['id']);
				$matched_root_info = $tproject_mgr->get_by_prefix($matched_prefix);
				if ($real_root != $matched_root_info['id']) {
					continue;
				}
				
				$urlString = sprintf($string2replace[$accessKey], $rs[0]['id'],
									$matched_anchor, $title[$accessKey], $rs[0]['doc_id']);
				$scope = str_replace($matched_string,$urlString,$scope);
			}
		}
	}
	
	return $scope;
}

?>