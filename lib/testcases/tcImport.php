<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: tcImport.php,v $
 * @version $Revision: 1.42 $
 * @modified $Date: 2009/02/04 22:03:50 $ by $Author: franciscom $
 * 
 * Scope: control test specification import
 * Troubleshooting: check if DOM module is enabled
 * 
 * Revision:
 *  20090117 - BUGID 1991 - franciscom
 *             BUGID 1992 - contribution for XLS import - franciscom
 *  20090106 - BUGID - franciscom - added logic to import Test Cases custom field values
 *  20081001 - franciscom - added logic to manage too long testcase name
 * 	20080813 - havlatm - added a few logging
 * 
 * *********************************************************************************** */
require('../../config.inc.php');
require_once('common.php');
require_once('import.inc.php');
require_once('csv.inc.php');
require_once('xml.inc.php');
require_once('keyword.class.php');
require_once('../../third_party/phpexcel/reader.php');

testlinkInitPage($db);

$gui = new stdClass();

$templateCfg = templateConfiguration();
$pcheck_fn=null;
$args = init_args();
$resultMap = null;

$dest_common = TL_TEMP_PATH . session_id(). "-importtcs";
$dest_files = array('XML' => $dest_common . ".csv",
                    'XLS' => $dest_common . ".xls");

$dest=$dest_files['XML'];
if(!is_null($args->importType))
{
	$dest = $dest_files[$args->importType];
}

$file_check = array('status_ok' => 1, 'msg' => 'ok');

if($args->bRecursive)
{
	$import_title = lang_get('title_tsuite_import_to');  
	$container_description = lang_get('test_suite');
}
else
{
	$import_title = lang_get('title_tc_import_to');
	$container_description = lang_get('test_case');
}

$container_name = '';
if($args->container_id)
{
	$tree_mgr = new tree($db);
	$node_info = $tree_mgr->get_node_hierachy_info($args->container_id);    
	$container_name = $node_info['name'];
	if($args->container_id == $args->tproject_id)
	{
		$container_description=lang_get('testproject');
	}	
}

if ($args->do_upload)
{
  
	// check the uploaded file
	$source = isset($_FILES['uploadedFile']['tmp_name']) ? $_FILES['uploadedFile']['tmp_name'] : null;
	tLog('Uploaded file: '.$source);
	if (($source != 'none') && ($source != ''))
	{ 
		$file_check['status_ok'] = 1;
		if (move_uploaded_file($source, $dest))
		{
			  tLog('Renamed uploaded file: '.$source);
			  switch($args->importType)
			  {
			  	case 'XML':
			  		$pcheck_fn = "check_xml_tc_tsuite";
			  		$pimport_fn = "importTestCaseDataFromXML";
			  		break;
        
			  	case 'XLS':
			  		$pcheck_fn = null;
			  		$pimport_fn = "importTestCaseDataFromSpreadsheet";
			  		break;
			  }
	      if(!is_null($pcheck_fn))
	      {
				    $file_check = $pcheck_fn($dest,$args->bRecursive);
				}
		}
		if($file_check['status_ok'] && $pimport_fn)
		{
			tLog('Check is Ok.');
			$resultMap = $pimport_fn($db,$dest,$args->container_id,$args->tproject_id,
										           $args->userID,$args->bRecursive,
										           $args->bIntoProject,$args->action_on_duplicated_name);
		}
	}
	else
	{
		tLog('Missing upload file','WARNING');
		$file_check = array('status_ok' => 0, 'msg' => lang_get('please_choose_file_to_import'));
		$args->importType = null;
	}
}

if($args->bRecursive)
{
  $obj_mgr = new testsuite($db);
  $gui->actionOptions=null;
}
else
{
  $obj_mgr = new testcase($db);
  $gui->actionOptions=array('update_last_version' => lang_get('update_last_testcase_version'),
                            'generate_new' => lang_get('generate_new_testcase'),
                            'create_new_version' => lang_get('create_new_testcase_version'));

}

$gui->testprojectName = $_SESSION['testprojectName'];
$gui->importTypes = $obj_mgr->get_import_file_types();
$gui->importLimitKB=(TL_IMPORT_LIMIT / 1024);
                          
$gui->action_on_duplicated_name=$args->action_on_duplicated_name;


$smarty = new TLSmarty();
$smarty->assign('gui',$gui);  
$smarty->assign('import_title',$import_title);  
$smarty->assign('file_check',$file_check);  
$smarty->assign('bRecursive',$args->bRecursive); 
$smarty->assign('resultMap',$resultMap); 
$smarty->assign('containerID', $args->container_id);
$smarty->assign('container_name', $container_name);
$smarty->assign('container_description', $container_description);
$smarty->assign('bIntoProject',$args->bIntoProject);
$smarty->assign('bImport',strlen($args->importType));
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


// --------------------------------------------------------------------------------------
/*
  function: importTestCaseDataFromXML
  args :
  returns: 
*/
function importTestCaseDataFromXML(&$db,$fileName,$parentID,$tproject_id,
                                   $userID,$bRecursive,$importIntoProject = 0,
                                   $duplicateLogic=null)
{
	tLog('importTestCaseDataFromXML called for file: '. $fileName);
	$xmlTCs = null;
	$resultMap  = null;
	if (file_exists($fileName))
	{
		$dom = domxml_open_file($fileName);
		if ($dom)
		{
			$root = $dom->document_element();
			
			$xmlKeywords = $root->get_elements_by_tagname("keywords");
			$kwMap = null;
			if ($xmlKeywords)
			{
				$tproject = new testproject($db);
				for($i = 0;$i < sizeof($xmlKeywords);$i++)
				{
					$tproject->importKeywordsFromXML($tproject_id,$xmlKeywords[$i]->dump_node());
				}
				$kwMap = $tproject->get_keywords_map($tproject_id);
				$kwMap = array_flip($kwMap);
				
			}
			
			if ($bRecursive && $root->tagname == 'testsuite')
			{
				$resultMap = importTestSuite($db,$root,$parentID,$tproject_id,$userID,$kwMap,$importIntoProject);
			}
			else if (!$bRecursive && $root->tagname == 'testcases')
			{
				$resultMap = importTestCases($db,$root,$parentID,$tproject_id,$userID,$kwMap,$duplicateLogic);
			}	
		}
	}
	return $resultMap;
}


// --------------------------------------------------------------------------------------
/*
  function: importTestCases
  args :
  returns: 
*/
function importTestCases(&$db,&$node,$parentID,$tproject_id,$userID,$kwMap,$duplicateLogic)
{
	$resultMap = null;
	if ($node->tagname == 'testcases')
	{
		$xmlTCs = $node->get_elements_by_tagname("testcase");
		$tcData = importTCsFromXML($xmlTCs);
		
		new dBug($tcData);
		
		if ($tcData)
		{
			$resultMap = saveImportedTCData($db,$tcData,$tproject_id,$parentID,$userID,$kwMap,$duplicateLogic);
		}	
	}
	return $resultMap;
}


// --------------------------------------------------------------------------------------
/*
  function: importTestSuite
  args :
  returns: 
  
  rev: 20090204 - franciscom - added node_order
*/
function importTestSuite(&$db,&$node,$parentID,$tproject_id,$userID,$kwMap,$importIntoProject = 0)
{
	$resultMap = null;
	if ($node->tagname() == 'testsuite')
	{
		$name = $node->get_attribute("name");
		$details = trim(getNodeContent($node,'details'));
		$node_order = intval(trim(getNodeContent($node,'node_order')));
		
		$ts = null;
		if (strlen($name))
		{
			$ts = new testsuite($db);
			$ret = $ts->create($parentID,$name,$details,$node_order);
			$tsID = $ret['id'];
			if (!$tsID)
				return null;
		}
		else if ($importIntoProject)
			$tsID = $tproject_id;
		else
			$tsID = $parentID;

		$cNodes = $node->child_nodes();	
		for($idx = 0; $idx < sizeof($cNodes); $idx++)
		{
			$cNode = $cNodes[$idx];
			if ($cNode->node_type() != XML_ELEMENT_NODE)
				continue;
			$tagName = $cNode->tagname();
			switch($tagName)
			{
				case 'testcase':
					$tcData = importTCsFromXML(array($cNode));
					saveImportedTCData($db,$tcData,$tproject_id,$tsID,$userID,$kwMap);
					break;
					
				case 'testsuite':
					importTestSuite($db,$cNode,$tsID,$tproject_id,$userID,$kwMap);
					break;
					
				case 'details':
					if (!$importIntoProject)
					{
						$keywords = importKeywordsFromXML($cNode->get_elements_by_tagname("keyword"));
						if ($keywords)
						{
							$kwIDs = buildKeywordList($kwMap,$keywords);
							$ts->addKeywords($tsID,$kwIDs);
						}
					}
					break;
			}
		}
	}
}


// --------------------------------------------------------------------------------------
/*
  function: saveImportedTCData
  args :
  returns: 
  
  rev:
      20090204 - franciscom - use value of node_order readed from file
      
      configure create to rename test case if exists 
*/
function saveImportedTCData(&$db,$tcData,$tproject_id,$container_id,
                            $userID,$kwMap,$actionOnDuplicatedName='generate_new')
{
	if (!$tcData)
	{
		return;
	}
		
	$resultMap = array();
	$fieldSizeCfg=config_get('field_size');
  $cf_msg = lang_get('cf_value_not_imported_missing_cf_on_testproject');
  $tc_msg = lang_get('testcase');
  $missingCfMsg = null;
	
	// because name can be changed automatically during item creation
	// to avoid name conflict adding a suffix automatically generated,
	// is better to use a max size < max allowed size 
	$safeSizeCfg = new stdClass();
	$safeSizeCfg->testcase_name=($fieldSizeCfg->testcase_name) * 0.8;
	
	$tc_qty = sizeof($tcData);
	if($tc_qty)
	{
		$tcase_mgr = new testcase($db);
		$tproject = new testproject($db);
	
	  // Get CF with scope design time and allowed for test cases linked to this test project
	  $customFields=$tproject->get_linked_custom_fields($tproject_id,'testcase','name');
	}
	
	for($idx = 0; $idx <$tc_qty ; $idx++)
	{
		$tc = $tcData[$idx];

		$name = $tc['name'];
		$summary = $tc['summary'];
		$expected_results = $tc['expectedresults'];
		$steps = $tc['steps'];
		$node_order = isset($tc['node_order']) ? intval($tc['node_order']) : testcase::DEFAULT_ORDER;
		$externalid = $tc['externalid'];
				
    
    $name_len=strlen($name);  
    if( $name_len > $fieldSizeCfg->testcase_name)
    {
        // Will put original name inside summary
        $xx=lang_get('start_warning'). "\n" . lang_get('testlink_warning') . "\n";
        $xx .=sprintf(lang_get('testcase_name_too_long'),$name_len, $fieldSizeCfg->testcase_name) . "\n";
        $xx .= lang_get('original_name'). "\n" . $name. "\n" . lang_get('end_warning'). "\n";
        $summary = nl2br($xx) . $summary;
        $name=substr($name, 0, $safeSizeCfg->testcase_name);      
    }
    		
		
		$kwIDs = null;
		if (isset($tc['keywords']) && $tc['keywords'])
		{
			$kwIDs = buildKeywordList($kwMap,$tc['keywords'],true);
		}	
		
		$doCreate=true;
		if( $actionOnDuplicatedName == 'update_last_version' )
		{
       $info=$tcase_mgr->getDuplicatesByName($name,$container_id);
   		 if( !is_null($info) )
   		 {
   		     $tcase_qty = count($info);
		       switch($tcase_qty)
		       {
		           case 1:
		               $doCreate=false;
		               $tcase_id = key($info); 
                   $last_version=$tcase_mgr->get_last_version_info($tcase_id);
                   $tcversion_id=$last_version['id'];
                   $ret = $tcase_mgr->update($tcase_id,$tcversion_id,$name,$summary,$steps,
                                             $expected_results,$userID,$kwIDs,$node_order);
                                             
                   $resultMap[] = array($name,lang_get('already_exists_updated'));
	             break;
		           
		           case 0:
		               $doCreate=true; 
		           break;
		           
		           default:
		               $doCreate=false; 
		           break;
		       }
		   }

		}
		
		if( $doCreate )
		{
		    if ($ret = $tcase_mgr->create($container_id,$name,$summary,$steps,
		                                  $expected_results,$userID,$kwIDs,
		                                  $node_order,testcase::AUTOMATIC_ID,
                                      testcase::CHECK_DUPLICATE_NAME,$actionOnDuplicatedName))
        {
            $resultMap[] = array($name,$ret['msg']);
        }                              
		}
			
		// 20090106 - franciscom
		// Custom Fields Management
		// Check if CF with this name and that can be used on Test Cases is defined in current Test Project.
		// If Check fails => give message to user.
		// Else Import CF data
		// 	
		if( !is_null($customFields) )
		{                         
		    $cfValues=null;
		    foreach($tc['customfields'] as $value)
		    {
		       if( isset($customFields[$value['name']]) )
		       {
		           $cfValues[$customFields[$value['name']]['id']]=array('type_id' => $customFields[$value['name']]['type'],
		                                                                'cf_value' => $value['value']);         
		       }
		       else
		       {
		           if( !isset($missingCfMsg[$value['name']]) )
		           {
		               $missingCfMsg[$value['name']] = sprintf($cf_msg,$value['name'],$tc_msg);
		           }
		           $resultMap[] = array($name,$missingCfMsg[$value['name']]); 
		       }
		    }  
		    $tcase_mgr->cfield_mgr->design_values_to_db($cfValues,$ret['id'],null,'simple');
		}	
			
	}

	return $resultMap;
}


// --------------------------------------------------------------------------------------
/*
  function: buildKeywordList
  args :
  returns: 
*/
function buildKeywordList($kwMap,$keywords,$bList = false)
{
	$kwIDs = array();
	for($jdx = 0;$jdx < sizeof($keywords); $jdx++)
	{
		$kwIDs[] = $kwMap[$keywords[$jdx]['keyword']];
	}
	
	if ($bList)
	{
		$kwIDs = implode(",",$kwIDs);
	}	
	return $kwIDs;
}


// --------------------------------------------------------------------------------------
/*
  function: importTCsFromXML
  args :
  returns: 
*/
function importTCsFromXML($xmlTCs)
{
	$tcSet = null;
	if (!$xmlTCs)
		return $tcSet;
		
	$jdx = 0;
	$loops2do=sizeof($xmlTCs);
	for($idx = 0; $idx < $loops2do; $idx++)
	{
		$xmlTC = $xmlTCs[$idx];
		if ($xmlTC->node_type() != XML_ELEMENT_NODE)
		{
			continue;
		}
		
		$tc = importTCFromXML($xmlTC);
		if ($tc)
		{
			$keywords = importKeywordsFromXML($xmlTC->get_elements_by_tagname("keyword"));
			if ($keywords)
			{
				$tc['keywords'] = $keywords;
			}
			$cf = importCustomFieldsFromXML($xmlTC->get_elements_by_tagname("custom_field"));
			
			if($cf)
			{
			    $tc['customfields'] = $cf;  
			} 
			$tcSet[$jdx++] = $tc;
		}
	}
	return $tcSet;
}


// --------------------------------------------------------------------------------------
/*
  function: importTCFromXML()
  args :
  returns: 
  
  rev: 20090204 - franciscom - added node_order,externalid
*/
function importTCFromXML(&$xmlTC)
{
	if (!$xmlTC)
	{
		return null;
	}
	
	$keyContent=array("summary","steps","expectedresults");
	$tc = null;
	$tc['name'] = $xmlTC->get_attribute("name");
  foreach($keyContent as $key)
  {
      $tc[$key] = trim(getNodeContent($xmlTC,$key));
  }
  
  $keyContent=array("node_order","externalid");
	foreach($keyContent as $key)
  {
      $tc[$key] = intval(trim(getNodeContent($xmlTC,$key)));
  }
	
	return $tc; 		
}


// --------------------------------------------------------------------------------------
/*
  function: Check if at least the file starts seems OK
*/
function check_xml_tc_tsuite($fileName,$bRecursive)
{
	$dom = domxml_open_file($fileName);
	$file_check = array('status_ok' => 0, 'msg' => 'dom_ko');    		  
	
	if ($dom)
	{
		$file_check = array('status_ok' => 1, 'msg' => 'ok');    		  
		$root = $dom->document_element();
		if($bRecursive)
		{
			if($root->tagname != 'testsuite')
				$file_check=array('status_ok' => 0, 'msg' => lang_get('wrong_xml_tsuite_file'));
		}
		else
		{
		  // 20070127 - franciscom 
			if($root->tagname != 'testcases' && $root->tagname != 'testcase')
				$file_check=array('status_ok' => 0, 'msg' => lang_get('wrong_xml_tcase_file'));
		}
	}
	return $file_check;
}


// *****************************************************************************************
// Contributed code - lightbulb
// *****************************************************************************************
/*
  function: importTestCaseDataFromSpreadsheet
            convert a XLS file to XML, and call importTestCaseDataFromXML() to do import.

  args: db [reference]: db object
        fileName: XLS file name
        parentID: testcases parent node (container)
        tproject_id: testproject where to import testcases 
        userID: who is doing import.
        bRecursive: 1 -> recursive, used when importing testsuites
        [importIntoProject]: default 0
        
  
  returns: map 

  rev:
      Original code by lightbulb.
      Refactoring by franciscom
*/
function importTestCaseDataFromSpreadsheet(&$db,$fileName,$parentID,$tproject_id,
                                           $userID,$bRecursive,$importIntoProject = 0)
{
	$xmlTCs = null;
	$resultMap  = null;
	$xml_filename=$fileName . '.xml';
	create_xml_tcspec_from_xls($fileName,$xml_filename);
	$resultMap=importTestCaseDataFromXML($db,$xml_filename,$parentID,$tproject_id,$userID,
	                                     $bRecursive,$importIntoProject);
	unlink($fileName);
	unlink($xml_filename);
	
	return $resultMap;
}


// --------------------------------------------------------------------------------------
/*
  function: create_xml_tcspec_from_xls
            Using an XSL file, that contains testcase specifications
            creates an XML testlink test specification file.
            
            XLS format:
            Column       Description
              1          test case name
              2          summary
              3          steps
              4          expectedresults
              
            First row contains header:  name,summary,steps,expectedresults
            and must be skipped.
            
  args: xls_filename
        xml_filename
  
  returns: 
*/
function create_xml_tcspec_from_xls($xls_filename,$xml_filename) 
{
	define('FIRST_DATA_ROW',2);
	define('IDX_COL_NAME',1);
	define('IDX_COL_SUMMARY',2);
	define('IDX_COL_STEPS',3);
	define('IDX_COL_EXPRESULTS',4);
  
	$xls_handle = new Spreadsheet_Excel_Reader(); 
  
	$xls_handle->setOutputEncoding('CP1252'); 
	$xls_handle->read($xls_filename);
	$xls_rows = $xls_handle->sheets[0]['cells'];
	$xls_row_qty = sizeof($xls_rows);
  
	if($xls_row_qty < FIRST_DATA_ROW)
	{
    	return;  // >>>----> bye!
  }
  
	$xmlFileHandle = fopen($xml_filename, 'w') or die("can't open file");
	fwrite($xmlFileHandle,"<testcases>\n");

	for($idx = FIRST_DATA_ROW;$idx <= $xls_row_qty; $idx++ )
	{                       
		  $name = htmlspecialchars(iconv("CP1252","UTF-8",$xls_rows[$idx][IDX_COL_NAME]));
		  fwrite($xmlFileHandle,"<testcase name=" . '"' . $name. '"'.">\n");
	    
		  // $summary = htmlspecialchars(iconv("CP1252","UTF-8",$xls_rows[$idx][IDX_COL_SUMMARY]));
	    // 20090117 - contribution - BUGID 1992
	    $summary = str_replace('…',"...",$xls_rows[$idx][IDX_COL_SUMMARY]);  
		  $summary = nl2p(htmlspecialchars(iconv("CP1252","UTF-8", $summary)));
	    fwrite($xmlFileHandle,"<summary><![CDATA[" . $summary . "]]></summary>\n");
	    
	    // 20090117 - BUGID 1991,1992
	    $steps = str_replace('…',"...",$xls_rows[$idx][IDX_COL_STEPS]);
	    $steps = nl2p(htmlspecialchars(iconv("CP1252","UTF-8",$steps)));
	    fwrite($xmlFileHandle,"<steps><![CDATA[".$steps."]]></steps>\n");
	    
	    // 20090117 - BUGID 1991,1992
	    $expresults = str_replace('…',"...",$xls_rows[$idx][IDX_COL_EXPRESULTS]);
	    $expresults = nl2p(htmlspecialchars(iconv("CP1252","UTF-8",$expresults)));
	    fwrite($xmlFileHandle,"<expectedresults><![CDATA[".$expresults."]]></expectedresults>\n");
	    
	    fwrite($xmlFileHandle,"</testcase>\n");
	}
	fwrite($xmlFileHandle,"</testcases>\n");
	fclose($xmlFileHandle);
}

// --------------------------------------------------------------------------------------
//SCHLUNDUS will be removed after refactoring
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


// --------------------------------------------------------------------------------------
/**
 * Imports a single keywords from a XML Element
 *
 * @param object $fileName [ref] the XML Element to import from
 * @return array return null on error or an array of
 * 				 keyword['keyword'] => the keyword itself
 * 				 keyword['notes'] => the notes of keyword
 *
 **/
 //SCHLUNDUS will be removed after refactoring
function importKeywordFromXML(&$xmlKeyword)
{
	if (!$xmlKeyword)
		return null;
	$keyword['keyword'] = $xmlKeyword->get_attribute("name");
	$keyword['notes'] = trim(getNodeContent($xmlKeyword,'notes'));

	return $keyword;
}


/*
  function: importCustomFieldsFromXML

  args:
  
  returns: 

*/
function importCustomFieldsFromXML($xmlItems)
{
	if (!$xmlItems)
	{
		return null;
	}

  $items = null;
  $items_counter=0;
  
  $loop_qty = count($xmlItems);
  for($idx=0; $idx < $loop_qty; $idx++)
  {
	    foreach( array('name','value') as $key )
	    {
	        $dummy[$key] = trim(getNodeContent($xmlItems[$idx],$key));
	    }
			$items[$items_counter++] = $dummy;
  }

	return $items;
}

/* 20090117 - 
 contribution by mirosvad - 
 Convert new line characters from XLS to HTML 
*/
function nl2p($str)  
{
  return str_replace('<p></p>', '', '<p>' . preg_replace('#\n|\r#', '</p>$0<p>', $str) . '</p>'); //MS
}


/*
  function: 
  
  args :
  
  returns: 
  
*/
function init_args()
{
    $args = new stdClass();
    $_REQUEST = strings_stripSlashes($_REQUEST);

    $key='action_on_duplicated_name';
    $args->$key = isset($_REQUEST[$key]) ? $_REQUEST[$key] : 'generate_new';
       
        
    $args->importType = isset($_REQUEST['importType']) ? $_REQUEST['importType'] : null;
    $args->bRecursive = isset($_REQUEST['bRecursive']) ? $_REQUEST['bRecursive'] : 0;
    $args->location = isset($_REQUEST['location']) ? $_REQUEST['location'] : null; 
    $args->container_id = isset($_REQUEST['containerID']) ? intval($_REQUEST['containerID']) : 0;
    $args->bIntoProject = isset($_REQUEST['bIntoProject']) ? intval($_REQUEST['bIntoProject']) : 0;
    
    $args->containerType = isset($_REQUEST['containerType']) ? intval($_REQUEST['containerType']) : 0;
    $args->do_upload = isset($_REQUEST['UploadFile']) ? 1 : 0;
    
    $args->userID = $_SESSION['userID'];
    $args->tproject_id = $_SESSION['testprojectID'];
    
    return $args;
}
?>