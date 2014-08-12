<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 * 
 * Functions for support requirement based testing
 *
 * @filesource  requirements.inc.php
 * @package     TestLink
 * @author      Martin Havlat
 * @copyright   2007-2014, TestLink community 
 * @link        http://www.testlink.org
 *
 * @internal revisions
 * @since 1.9.9
 */

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
    $docID = trim_and_limit($data['docid'],$field_size->req_docid);
    $title = trim_and_limit($data['title'],$field_size->req_title);
    $scope = $data['description'];
    $type = $data['type'];
    $status = $data['status'];
    $expected_coverage = $data['expected_coverage'];
    $node_order = $data['node_order'];
  
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
          
          // BUGID 0003745: CSV Requirements Import Updates Frozen Requirement
          if( $last_version['is_open'] == 1 )
          {
            $op = $req_mgr->update($item['id'],$last_version['id'],$docID,$title,$scope,$userID,
                                 $status,$type,$expected_coverage,$node_order,SKIP_CONTROLS);
            if( $op['status_ok']) 
            {
              $import_status['msg'] = lang_get('req_import_result_overwritten');
            }
          } 
          else
          {
            $import_status['msg'] = lang_get('req_import_result_skipped_is_frozen');
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
      //     We will follow user option: update,create new version
      //
      // If do not exist check must be repeated, but on WHOLE test project
      //  If there is a hit -> we can not create
      //    else => create
      // 
            // 
            // 20100321 - we do not manage yet user option
      $check_in_reqspec = $reqMgr->getByDocID($req['docid'],$tprojectID,$reqSpecID,$getOptions);
        if(is_null($check_in_reqspec))
      {
        $check_in_tproject = $reqMgr->getByDocID($req['docid'],$tprojectID,null,$getOptions);
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
      
      $arrImport[] = array('req_doc_id' => $req['docid'], 'title' => trim($req['title']),
                           'scope' => $req['description'], 'type' => $verbose['type'], 
                           'status' => $verbose['status'], 'expected_coverage' => $req['expected_coverage'],
                           'node_order' => $req['order'], 'check_status' => $messages[$msgID]);
    }
  }
  return $arrImport;
}

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
  $retVal = null;
  switch($importType)
  {
    case 'csv':
      $pfn = "importReqDataFromCSV";
    break;
      
    case 'csv_doors':
      $pfn = "importReqDataFromCSVDoors";
    break;
      
    case 'DocBook':
      $pfn = "importReqDataFromDocBook";
    break;
  }
  
  if ($pfn)
  {
    $retVal = $pfn($fileName);
    if($importType == 'DocBook')
    {
      // this structure if useful when importing from CSV
      // $retVal = array('userFeedback' => arra(),'info' => null);
      //
      // But we need to return same data structure ALWAYS
      // for DocBook we do not use 'parsedCounter' and 'syntaxError'
      //
      $dummy = array('userFeedback' => null, 'info' => $retVal);
      $retVal = $dummy;        
    }
  }
   
  return $retVal;
}

/**
 * importReqDataFromCSV
 *
 */
function importReqDataFromCSV($fileName)
{
  // CSV line format
  $fieldMappings = array("docid","title","description","type","status","expected_coverage","node_order");
    

  $options = array('delimiter' => ',' , 'fieldQty' => count($fieldMappings));
  $impData = importCSVData($fileName,$fieldMappings,$options);
  
  $reqData = &$impData['info'];
  if($reqData)
  {
    // lenght will be adjusted to these values
    $field_size=config_get('field_size');
    $fieldLength = array("docid" => $field_size->req_docid, "title" => $field_size->req_title);

    $reqCfg = config_get('req_cfg');
    $fieldDefault = array("type" => array('check' => 'type_labels', 'value' => TL_REQ_TYPE_FEATURE), 
                          "status" => array('check' => 'status_labels' , 'value' => TL_REQ_STATUS_VALID));
  
    $loop2do = count($reqData);
    for($ddx=0; $ddx < $loop2do; $ddx++)
    {
      foreach($reqData[$ddx] as $fieldKey => &$fieldValue)
      {
        // Adjust Lenght 
        if( isset($fieldLength[$fieldKey]) )
        {
          $fieldValue = trim_and_limit($fieldValue,$fieldLength[$fieldKey]);
        }
        else if(isset($fieldDefault[$fieldKey]))
        {
          // Assign default value
          $checkKey = $fieldDefault[$fieldKey]['check'];
          $checkObj = &$reqCfg->$checkKey;
          if( !isset($checkObj[$fieldValue]) )
          {
            $fieldValue = $fieldDefault[$fieldKey]['value'];
          }
        }
      }
    }
  }
  return $impData;
}

/**
 * importReqDataFromCSVDoors
 *
 * @internal revision
 *
 */
function importReqDataFromCSVDoors($fileName)
{
  // Some keys are strings, other numeric
  $fieldMappings = array("Object Identifier" => "title","Object Text" => "description",
                 "Created By","Created On","Last Modified By","Last Modified On");
  
  $options = array('delimiter' => ',', 'fieldQty' => count($fieldMappings), 'processHeader' => true);
  $impData = importCSVData($fileName,$fieldMappings,$options);

  return $impData;
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
 * @return array of map
 *
 */
function importReqDataFromDocBook($fileName)
{
  $req_cfg = config_get('req_cfg');
  $docbookCfg = $req_cfg->importDocBook;
  $xmlReqs = null;
  $xmlData = null;
  $field_size=config_get('field_size');  
  
  $simpleXMLObj = simplexml_load_file($fileName);
  $num_elem = count($simpleXMLObj->{$docbookCfg->requirement});

  $idx=0; 
  foreach($simpleXMLObj->{$docbookCfg->requirement} as $xmlReq)
  {
    // get all child elements of this requirement
    $title = "";
    $description = "";
    $children = $xmlReq->children();
    foreach ($children as $child)
    {                        
      $nodeName = $child->getName();
      if ($nodeName == $docbookCfg->title )
      {
        $title = (string)$child;
      } 
      else if ($nodeName == $docbookCfg->ordered_list)
      {
        $list = "";
        foreach( $child->children() as $item )
        {
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
    //  $xmlData[$i]['req_doc_id'] = $matches[0];
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
      $xmlData[$idx]['docid'] = $matches[0] . " " . $counter[$index];
    }
    else
    {
      $xmlData[$idx]['docid'] = uniqid('REQ-');
    }  

    $xmlData[$idx]['node_order'] = $idx;
    $xmlData[$idx]['expected_coverage'] = 0;
    $xmlData[$idx]['type'] = TL_REQ_TYPE_FEATURE;
    $xmlData[$idx]['status'] = TL_REQ_STATUS_VALID;

    $idx++;
  }
  
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
 * 20110525 - Julian - BUGID 4487 - allow to specify requirement version for internal links
 * 20100301 - asimon - added anchor and tproj parameters to tags
 * 
 * @param resource $dbHandler database handle
 * @param string $scope text in which to replace tags with links
 * @param integer $tprojectID ID of testproject to which req/reqspec belongs
 * @return string $scope text with generated links
 */
function req_link_replace($dbHandler, $scope, $tprojectID) 
{

  // Use this to improve performance when is called in loops
  static $tree_mgr;
  static $tproject_mgr;
  static $req_mgr;
  static $cfg;
  static $l18n;
  static $title;
  static $tables;
  
  if(!$tproject_mgr)
  {
    $tproject_mgr = new testproject($dbHandler);
    $tree_mgr = new tree($dbHandler);
    $req_mgr = new requirement_mgr($dbHandler);

    $tables = tlObjectWithDB::getDBTables(array('requirements', 'req_specs'));

    $cfg = config_get('internal_links');
    $l18n['version'] = lang_get('tcversion_indicator');

    $prop2loop = array('req' => array('prop' => 'req_link_title', 'default_lbl' => 'requirement'), 
               'req_spec' => array('prop' => 'req_spec_link_title','default_lbl' => 'req_spec_short'));
    

    // configure link title (first part of the generated link)
    $title = array();
    foreach($prop2loop as $key => $elem)
    {
      $prop = $elem['prop'];
      if ($cfg->$prop->type == 'string' && $cfg->$prop->value != '') 
      {
        $title[$key] = lang_get($cfg->$prop->value);
      }   
      else if ($cfg->$prop->type == 'none') 
      {
        $title[$key] = '';
      } 
      else
      {
        $title[$key] = lang_get($elem['default_lbl']) . ": ";
      }
    } 

  }

  $prefix = $tproject_mgr->getTestCasePrefix($tprojectID);
  $string2replace = array();

  // configure target in which link shall open
  // use a reasonable default value if nothing is set in config
  $cfg->target = isset($cfg->target) ? $cfg->target :'popup';

  switch($cfg->target)
  {
    case 'popup':
      // use javascript to open popup window
      $string2replace['req'] = '<a href="javascript:openLinkedReqVersionWindow(%s,%s,\'%s\')">%s%s%s</a>';
      $string2replace['req_spec'] = '<a href="javascript:openLinkedReqSpecWindow(%s,\'%s\')">%s%s</a>';
    break;
    
    case 'window':
      case 'frame':// open in same frame
      $target = ($cfg->target == 'window') ? 'target="_blank"' : 'target="_self"';
      $string2replace['req'] = '<a ' . $target . ' href="lib/requirements/reqView.php?' .
                         'item=requirement&requirement_id=%s&req_version_id=%s#%s">%s%s%s</a>';
      $string2replace['req_spec'] = '<a ' . $target . ' href="lib/requirements/reqSpecView.php?' .
                              'item=req_spec&req_spec_id=%s#%s">%s%s</a>';
    break;
    }

  // now the actual replacing
  $patterns2search = array();
  $patterns2search['req'] = "#\[req(.*)\](.*)\[/req\]#iU";
  $patterns2search['req_spec'] = "#\[req_spec(.*)\](.*)\[/req_spec\]#iU";
  $patternPositions = array('complete_string' => 0,'attributes' => 1,'doc_id' => 2);

  $items2search['req'] = array('tproj','anchor','version');
  $items2search['req_spec'] = array('tproj','anchor');
  $itemPositions = array ('item' => 0,'item_value' => 1);
  
  $sql2exec = array();
  $sql2exec['req'] = " SELECT id, req_doc_id AS doc_id " .
                     " FROM {$tables['requirements']} WHERE req_doc_id=";
   
  $sql2exec['req_spec'] = " SELECT id, doc_id FROM {$tables['req_specs']} " .
                          " WHERE doc_id=" ;

  foreach($patterns2search as $accessKey => $pattern )
  {
  
    $matches = array();
    preg_match_all($pattern, $scope, $matches);
    
    // if no req_doc_id is set skip loop
    if( count($matches[$patternPositions['doc_id']]) == 0 )
    {
      continue;
    }
    
    foreach ($matches[$patternPositions['complete_string']] as $key => $matched_string) 
    {
      
      $matched = array ();
      $matched['tproj'] = '';
      $matched['anchor'] = '';
      $matched['version'] = '';
      
      // only look for attributes if any found
      if ($matches[$patternPositions['attributes']][$key] != '') {
        foreach ($items2search[$accessKey] as $item) {
          $matched_item = array();
          preg_match('/'.$item.'=([\w]+)/',$matched_string,$matched_item);
          $matched[$item] = (isset($matched_item[$itemPositions['item_value']])) ? 
                            $matched_item[$itemPositions['item_value']] : '';
        }
      }
      // set tproj to current project if tproj is not specified in attributes
      if (!isset($matched['tproj']) || $matched['tproj'] == '') 
      {
        $matched['tproj'] = $prefix;
      }
      
      // get all reqs / req specs with the specified doc_id
      $sql = $sql2exec[$accessKey] . "'{$matches[$patternPositions['doc_id']][$key]}'";
      $rs = $dbHandler->get_recordset($sql);
      
      if (count($rs) > 0) 
      {
  
        foreach($rs as $key => $value) 
        {
          // get root of linked node and check
          $real_root = $tree_mgr->getTreeRoot($value['id']);
          $matched_root_info = $tproject_mgr->get_by_prefix($matched['tproj']);
          
          // do only continue if project with the specified project exists and
          // if the requirement really belongs to the specified project (requirements
          // with the same doc_id may exist within different projects)
          if ($real_root == $matched_root_info['id']) 
          {
            if($accessKey == 'req') 
            {
              // add version to link title if set
              $version = '';
              $req_version_id = 'null';
              if ($matched['version'] != '') 
              {
                // get requirement version_id of the specified version
                $req_version = $req_mgr->get_by_id($value['id'],null,$matched['version']);
            
                // if version is not set or wrong version was set 
                // -> show latest version by setting version_id to null
                $req_version_id = isset($req_version[0]['version_id']) ? $req_version[0]['version_id'] :'null';
            
                // if req_version_id exists set the version to show on hyperlink text
                if ($req_version_id != 'null') 
                {
                  $version = sprintf($l18n['version'],$matched['version']);
                }
              }
              $urlString = sprintf($string2replace[$accessKey], $value['id'], $req_version_id,
                                   $matched['anchor'], $title[$accessKey], $value['doc_id'], $version);
            } 
            else 
            {
              // build urlString for req specs which do not have a version
              $urlString = sprintf($string2replace[$accessKey], $value['id'],
                                   $matched['anchor'], $title[$accessKey], $value['doc_id']);
            }
            $scope = str_replace($matched_string,$urlString,$scope);
          }
        }
      }
    }
  }
  
  return $scope;
}

?>