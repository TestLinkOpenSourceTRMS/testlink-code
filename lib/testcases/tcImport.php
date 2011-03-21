<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Scope: control test specification import
 *
 * @filesource	tcImport.php
 * @package 	TestLink
 * @copyright 	2007-2011, TestLink community 
 * @link 		http://www.teamst.org/index.php
 * 
 * @internal revisions
 *
 *	20101106 - franciscom - fixed warning on event viewer when there are no keywords defined on test project
 *	20101002 - franciscom - BUGID 3801
 *  20100911 - amitkhullar - BUGID 3764 - Req Mapping Error while import of Test cases.
 *	20100905 - franciscom - BUGID 3431 - Custom Field values at Test Case VERSION Level
 *							processCustomFields()
 *	20100904 - franciscom - BUGID 3571 - Add 'create new version' choice when Import Test Suite
 *	20100821 - franciscom - changes to getStepsFromSimpleXMLObj() due to:
 *							BUGID 3695: Test Case Steps - Export/Import - missing attribute execution type
 *								
 *  20100719 - amitkhullar - BUGID 3609 - fix for keyword import error
 *	20100620 - franciscom - Trying to reduce memory problems using statics on 
 *							saveImportedTCData() after issue 3521
 *	20100619 - franciscom - added file size control 
 *	20100409 - franciscom - added import importance and execution_type
 *	20100317 - franciscom - BUGID 3236 - work in progress
 *	20100214 - franciscom - refactoring to use only simpleXML functions
 *	20100106 - franciscom - Multiple Test Case Steps Feature
 *	20090831 - franciscom - preconditions
 *  20090506 - Requirements refactoring
 *  20090221 - BUGID - Improvement on messages to user when XML file contains
 *                     Custom Field Information.
 *  20090206 - BUGID - Import TC-REQ relationship - franciscom
 *  20090117 - BUGID 1991 - franciscom
 *             BUGID 1992 - contribution for XLS import - franciscom
 *  20090106 - BUGID - franciscom - added logic to import Test Cases custom field values
 *  20081001 - franciscom - added logic to manage too long testcase name
 * 	20080813 - havlatm - added a few logging
 * 
 * *********************************************************************************** */
require('../../config.inc.php');
require_once('common.php');
require_once('csv.inc.php');
require_once('xml.inc.php');

testlinkInitPage($db);


$templateCfg = templateConfiguration();
$pcheck_fn=null;
$args = init_args();

$gui = new stdClass();
$gui->importLimitBytes = config_get('import_file_max_size_bytes');
$gui->importLimitKB = ($gui->importLimitBytes / 1024);
$gui->hitCriteria = $args->hit_criteria;
$gui->useRecursion = $args->useRecursion;
$gui->containerID = $args->container_id;
$gui->bImport = tlStringLen($args->importType);
$gui->bIntoProject = $args->bIntoProject;
$gui->resultMap = null;


$dest_common = TL_TEMP_PATH . session_id(). "-importtcs";
// $dest_files = array('XML' => $dest_common . ".xml",'XLS' => $dest_common . ".xls");
$dest_files = array('XML' => $dest_common . ".xml");
$dest=$dest_files['XML'];
if(!is_null($args->importType))
{
	$dest = $dest_files[$args->importType];
}

$gui->file_check = array('status_ok' => 1, 'msg' => 'ok');

if($args->useRecursion)
{
	$gui->import_title = lang_get('title_tsuite_import_to');  
	$gui->container_description = lang_get('test_suite');
}
else
{
	$gui->import_title = lang_get('title_tc_import_to');
	$gui->container_description = lang_get('test_case');
}

$gui->container_name = '';
if($args->container_id)
{
	$tree_mgr = new tree($db);
	$node_info = $tree_mgr->get_node_hierarchy_info($args->container_id);
	unset($tree_mgr);    
	$gui->container_name = $node_info['name'];
	if($args->container_id == $args->tproject_id)
	{
		$gui->container_description=lang_get('testproject');
	}	
}

if ($args->do_upload)
{
  
	// check the uploaded file
	$source = isset($_FILES['uploadedFile']['tmp_name']) ? $_FILES['uploadedFile']['tmp_name'] : null;
	
	tLog('Uploaded file: '.$source);
	$doIt = false;
	$gui->file_check = null;
	if (($source != 'none') && ($source != ''))
	{ 
		// ATTENTION:
		// MAX_FILE_SIZE hidden input is defined on form, but anyway we do not get error at least using
		// Firefox and Chrome.
		if( !($doIt = $_FILES['uploadedFile']['size'] <= $gui->importLimitBytes) )
		{
			$gui->file_check['status_ok'] = 0;
			$gui->file_check['msg'] = sprintf(lang_get('file_size_exceeded'),$_FILES['uploadedFile']['size'],$gui->importLimitBytes);
		}
	}
	if($doIt)
	{ 
		$gui->file_check['status_ok'] = 1;
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
				$gui->file_check = $pcheck_fn($dest,$args->useRecursion);
			}
		}
		if($gui->file_check['status_ok'] && $pimport_fn)
		{
			tLog('Check is Ok.');
			$opt = array();
			$opt['useRecursion'] = $args->useRecursion;
			$opt['importIntoProject'] = $args->bIntoProject;
			$opt['duplicateLogic'] = array('hitCriteria' => $args->hit_criteria,
			                               'actionOnHit' => $args->action_on_duplicated_name);
			$gui->resultMap = $pimport_fn($db,$dest,$args->container_id,$args->tproject_id,$args->userID,$opt);
		}
	}
	else if(is_null($gui->file_check))
	{
		
		tLog('Missing upload file','WARNING');
		$gui->file_check = array('status_ok' => 0, 'msg' => lang_get('please_choose_file_to_import'));
		$args->importType = null;
	}
}

if($args->useRecursion)
{
	// BUGID 3240 - Contribution 
	$obj_mgr = new testsuite($db);
	// $gui->actionOptions=null;
	// $gui->hitOptions=null;
	// $gui->actionOptions=array('update_last_version' => lang_get('update_last_testcase_version'),
	// 						  'generate_new' => lang_get('new_testcase_under_new_testsuite'),
    //                           'create_new_version' => lang_get('new_version_under_testsuite'));

  	$gui->actionOptions=array('update_last_version' => lang_get('update_last_testcase_version'),
                              'generate_new' => lang_get('generate_new_testcase'),
                              'create_new_version' => lang_get('create_new_testcase_version'));
	
	$gui->hitOptions=array('name' => lang_get('same_name'),
                           'internalID' => lang_get('same_internalID'),
                           'externalID' => lang_get('same_externalID'));
}
else
{
  $obj_mgr = new testcase($db);
  $gui->actionOptions=array('update_last_version' => lang_get('update_last_testcase_version'),
                            'generate_new' => lang_get('generate_new_testcase'),
                            'create_new_version' => lang_get('create_new_testcase_version'));

  $gui->hitOptions=array('name' => lang_get('same_name'),
                         'internalID' => lang_get('same_internalID'),
                         'externalID' => lang_get('same_externalID'));

}

$gui->testprojectName = $_SESSION['testprojectName'];
$gui->importTypes = $obj_mgr->get_import_file_types();
                          
$gui->action_on_duplicated_name=$args->action_on_duplicated_name;


$smarty = new TLSmarty();
$smarty->assign('gui',$gui);  
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


// --------------------------------------------------------------------------------------
/*
  function: importTestCaseDataFromXML
  args :
  returns: 
*/
function importTestCaseDataFromXML(&$db,$fileName,$parentID,$tproject_id,$userID,$options=null)
{
	tLog('importTestCaseDataFromXML called for file: '. $fileName);
	$xmlTCs = null;
	$resultMap  = null;
	$my = array();
	$my['options'] = array('useRecursion' => false, 'importIntoProject' => 0,
	                       'duplicateLogic' => array('hitCriteria' => 'name', 'actionOnHit' => null)); 
    $my['options'] = array_merge($my['options'], (array)$options);
    foreach($my['options'] as $varname => $value)
    {
    	$$varname = $value;
    }
	
	if (file_exists($fileName))
	{
		$xml = @simplexml_load_file($fileName);
		if($xml !== FALSE)
        {
			$xmlKeywords = $xml->xpath('//keywords');
			$kwMap = null;
			if ($xmlKeywords)
			{
				$tproject = new testproject($db);
				$loop2do = sizeof($xmlKeywords);
				for($idx = 0; $idx < $loop2do ;$idx++)
				{
					$tproject->importKeywordsFromSimpleXML($tproject_id,$xmlKeywords[$idx]);
				}
				$kwMap = $tproject->get_keywords_map($tproject_id);
				$kwMap = is_null($kwMap) ? null : array_flip($kwMap);
			}

			if (!$useRecursion &&  ($xml->getName() == 'testcases') )
			{
				$resultMap = importTestCasesFromSimpleXML($db,$xml,$parentID,$tproject_id,$userID,$kwMap,$duplicateLogic);
			}
			
			if ($useRecursion && ($xml->getName() == 'testsuite'))
			{
				$resultMap = importTestSuitesFromSimpleXML($db,$xml,$parentID,$tproject_id,$userID,
														   $kwMap,$importIntoProject,$duplicateLogic);
			}

		}
	}
	return $resultMap;
}


// --------------------------------------------------------------------------------------
/*
  function: saveImportedTCData
  args :
  returns: 
  
  rev:
	  20101002 - franciscom - BUGID 3801	
  	  20100905 - franciscom - BUGID 3431 - Custom Field values at Test Case VERSION Level	
 	  20100317 - franciscom - manage different criteria to decide that test case
 	  	                      is present on system
 	  	                                 		
      20090204 - franciscom - use value of node_order readed from file
      
      configure create to rename test case if exists 
*/
function saveImportedTCData(&$db,$tcData,$tproject_id,$container_id,
                            $userID,$kwMap,$duplicatedLogic = array('hitCriteria' => 'name', 'actionOnHit' => null))
{
	static $messages;
	static $fieldSizeCfg;
	static $feedbackMsg;
	static $tcase_mgr;
	static $tproject_mgr;
	static $req_spec_mgr;
	static $req_mgr;
	static $safeSizeCfg;
	static $linkedCustomFields;
	static $tprojectHas;
	static $reqSpecSet;
	static $getVersionOpt;
	
	if (!$tcData)
	{
		return;
	}
	
	// $tprojectHas = array('customFields' => false, 'reqSpec' => false);
  	$hasCustomFieldsInfo=false;
  	$hasRequirements=false;
  	
	if(is_null($messages))
	{
		$feedbackMsg = array();
  		$messages = array();
		$fieldSizeCfg = config_get('field_size');

		$tcase_mgr = new testcase($db);
		$tproject_mgr = new testproject($db);
		$req_spec_mgr = new requirement_spec_mgr($db);
		$req_mgr = new requirement_mgr($db);

  		
  		$messages['cf_warning'] = lang_get('no_cf_defined_can_not_import');
  		$messages['reqspec_warning'] = lang_get('no_reqspec_defined_can_not_import');
		$messages['already_exists_updated'] = lang_get('already_exists_updated');
  		$messages['original_name'] = lang_get('original_name');
  		$messages['testcase_name_too_long'] = lang_get('testcase_name_too_long');
	    $messages['start_warning'] = lang_get('start_warning');
	    $messages['end_warning'] = lang_get('end_warning');
	    $messages['testlink_warning'] = lang_get('testlink_warning');
	    $messages['start_feedback'] = $messages['start_warning'] . "\n" . $messages['testlink_warning'] . "\n";
  		
  		
  		
  		$feedbackMsg['cfield']=lang_get('cf_value_not_imported_missing_cf_on_testproject');
  		$feedbackMsg['tcase'] = lang_get('testcase');
  		$feedbackMsg['req'] = lang_get('req_not_in_req_spec_on_tcimport');
  		$feedbackMsg['req_spec'] = lang_get('req_spec_ko_on_tcimport');


		// because name can be changed automatically during item creation
		// to avoid name conflict adding a suffix automatically generated,
		// is better to use a max size < max allowed size 
		$safeSizeCfg = new stdClass();
		$safeSizeCfg->testcase_name=($fieldSizeCfg->testcase_name) * 0.8;


	    // Get CF with scope design time and allowed for test cases linked to this test project
	    // $customFields=$tproject_mgr->get_linked_custom_fields($tproject_id,'testcase','name');
	    // function get_linked_cfields_at_design($tproject_id,$enabled,$filters=null,
        //                                       $node_type=null,$node_id=null,$access_key='id')
        // 
        $linkedCustomFields = $tcase_mgr->cfield_mgr->get_linked_cfields_at_design($tproject_id,1,null,'testcase',null,'name');
        $tprojectHas['customFields']=!is_null($linkedCustomFields);                   

        // BUGID - 20090205 - franciscom
		$reqSpecSet = $tproject_mgr->getReqSpec($tproject_id,null,array('RSPEC.id','NH.name AS title','RSPEC.doc_id as rspec_doc_id', 'REQ.req_doc_id'),'req_doc_id');
		$tprojectHas['reqSpec'] = (!is_null($reqSpecSet) && count($reqSpecSet) > 0);

		$getVersionOpt = array('output' => 'minimun');
    }
  
	$resultMap = array();
	$tc_qty = sizeof($tcData);
	for($idx = 0; $idx <$tc_qty ; $idx++)
	{
		$tc = $tcData[$idx];
		$name = $tc['name'];
		$summary = $tc['summary'];
		$steps = $tc['steps'];
		$node_order = isset($tc['node_order']) ? intval($tc['node_order']) : testcase::DEFAULT_ORDER;
		$externalid = $tc['externalid'];
		$internalid = $tc['internalid'];
		$preconditions = $tc['preconditions'];
		$exec_type = isset($tc['execution_type']) ? $tc['execution_type'] : TESTCASE_EXECUTION_TYPE_MANUAL;
		$importance = isset($tc['importance']) ? $tc['importance'] : MEDIUM;		
    
		$name_len = tlStringLen($name);  
		if($name_len > $fieldSizeCfg->testcase_name)
		{
		    // Will put original name inside summary
		    $xx = $messages['start_feedback'];
		    $xx .= sprintf($messages['testcase_name_too_long'],$name_len, $fieldSizeCfg->testcase_name) . "\n";
		    $xx .= $messages['original_name'] . "\n" . $name. "\n" . $messages['end_warning'] . "\n";
		    $summary = nl2br($xx) . $summary;
		    $name = tlSubStr($name, 0, $safeSizeCfg->testcase_name);      
		}
    		
		
		$kwIDs = null;
		if (isset($tc['keywords']) && $tc['keywords'])
		{
			$kwIDs = implode(",",buildKeywordList($kwMap,$tc['keywords']));
		}	
		
		$doCreate=true;
		if( $duplicatedLogic['actionOnHit'] == 'update_last_version' )
		{
			switch($duplicatedLogic['hitCriteria'])
			{
				case 'name':
					$info = $tcase_mgr->getDuplicatesByName($name,$container_id);
				break;
				
				case 'internalID':
					$dummy = $tcase_mgr->tree_manager->get_node_hierarchy_info($internalid,$container_id);
					if( !is_null($dummy) )
					{
						$info[$internalid] = $dummy;
					}
				break;
		
				case 'externalID':
					$info = $tcase_mgr->get_by_external($externalid,$container_id);
				break;
		
				
   		 	}

   		 	if( !is_null($info) )
   		 	{
   		 		$tcase_qty = count($info);
		 	    switch($tcase_qty)
		 	    {
		 	        case 1:
		 	        	$doCreate=false;
		 	        	$tcase_id = key($info); 
         	        	$last_version = $tcase_mgr->get_last_version_info($tcase_id,$getVersionOpt);
         	        	$tcversion_id = $last_version['id'];
         	        	$ret = $tcase_mgr->update($tcase_id,$tcversion_id,$name,$summary,
         	        	                          $preconditions,$steps,$userID,$kwIDs,
         	        	                          $node_order,$exec_type,$importance);


						// BUGID 3801
						$ret['id'] = $tcase_id;
						$ret['tcversion_id'] = $tcversion_id;
         	        	$resultMap[] = array($name,$messages['already_exists_updated']);

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
		    $createOptions = array( 'check_duplicate_name' => testcase::CHECK_DUPLICATE_NAME, 
	                                'action_on_duplicate_name' => $duplicatedLogic['actionOnHit']);

		    if ($ret = $tcase_mgr->create($container_id,$name,$summary,$preconditions,$steps,
		                                  $userID,$kwIDs,$node_order,testcase::AUTOMATIC_ID,
		                                  $exec_type,$importance,$createOptions))
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

		$hasCustomFieldsInfo = (isset($tc['customfields']) && !is_null($tc['customfields']));
		if($hasCustomFieldsInfo)
		{
			//new dBug($ret);
			
		    if($tprojectHas['customFields'])
		    {                         
				// BUGID 3431 - Custom Field values at Test Case VERSION Level
		        $msg = processCustomFields(	$tcase_mgr,$name,$ret['id'],$ret['tcversion_id'],$tc['customfields'],
		        							$linkedCustomFields,$feedbackMsg);
		        if( !is_null($msg) )
		        {
		            $resultMap = array_merge($resultMap,$msg);
		        }
		    }
		    else
		    {
            	// Can not import Custom Fields Values, give feedback
            	$msg[]=array($name,$messages['cf_warning']);
            	$resultMap = array_merge($resultMap,$msg);		      
		    }
		}
		
		// BUGID - 20090205 - franciscom
		// Requirements Management
		// Check if Requirement ...
		// If Check fails => give message to user.
		// Else Import 
		// 	
		$hasRequirements=(isset($tc['requirements']) && !is_null($tc['requirements']));
		if($hasRequirements)
		{
  	        if( $tprojectHas['reqSpec'] )
            {
		        $msg = processRequirements($db,$req_mgr,$name,$ret['id'],$tc['requirements'],$reqSpecSet,$feedbackMsg);
		        if( !is_null($msg) )
		        {
		            $resultMap = array_merge($resultMap,$msg);
		        }
		    }
		    else
		    {
            	$msg[]=array($name,$messages['reqspec_warning']);
            	$resultMap = array_merge($resultMap,$msg);		      
		    }
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
function buildKeywordList($kwMap,$keywords)
{
	$items = array();
	$loop2do = sizeof($keywords);
	for($jdx = 0; $jdx <$loop2do ; $jdx++)
	{
		$items[] = $kwMap[trim($keywords[$jdx]['name'])]; 
	}
	return $items;
}


// --------------------------------------------------------------------------------------

// --------------------------------------------------------------------------------------

/*
  function: Check if at least the file starts seems OK
*/
function check_xml_tc_tsuite($fileName,$recursiveMode)
{
	$xml = @simplexml_load_file($fileName);
	$file_check = array('status_ok' => 0, 'msg' => 'xml_load_ko');    		  
	if($xml !== FALSE)
	{
		$file_check = array('status_ok' => 1, 'msg' => 'ok');    		  
		$elementName = $xml->getName();
		if($recursiveMode)
		{
			if($elementName != 'testsuite')
			{
				$file_check=array('status_ok' => 0, 'msg' => lang_get('wrong_xml_tsuite_file'));
			}	
		}
		else
		{
			if($elementName != 'testcases' && $elementName != 'testcase')
		    {
				$file_check=array('status_ok' => 0, 'msg' => lang_get('wrong_xml_tcase_file'));
			}	
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
        useRecursion: 1 -> recursive, used when importing testsuites
        [importIntoProject]: default 0
        
  
  returns: map 

  rev:
      Original code by lightbulb.
      Refactoring by franciscom
*/
function importTestCaseDataFromSpreadsheet(&$db,$fileName,$parentID,$tproject_id,
                                           $userID,$useRecursion,$importIntoProject = 0)
{
	$xmlTCs = null;
	$resultMap  = null;
	$xml_filename=$fileName . '.xml';
	create_xml_tcspec_from_xls($fileName,$xml_filename);
	$resultMap=importTestCaseDataFromXML($db,$xml_filename,$parentID,$tproject_id,$userID,
	                                     $useRecursion,$importIntoProject);
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
  
	$xls_handle->setOutputEncoding(config_get('charset')); 
	$xls_handle->read($xls_filename);
	$xls_rows = $xls_handle->sheets[0]['cells'];
	$xls_row_qty = sizeof($xls_rows);
  
	if($xls_row_qty < FIRST_DATA_ROW)
	{
    	return;  // >>>----> bye!
  	}
  
	$xmlFileHandle = fopen($xml_filename, 'w') or die("can't open file");
	fwrite($xmlFileHandle,"<testcases>\n");

	for($idx = FIRST_DATA_ROW; $idx <= $xls_row_qty; $idx++ )
	{                       
		$name = htmlspecialchars($xls_rows[$idx][IDX_COL_NAME]);
		fwrite($xmlFileHandle,"<testcase name=" . '"' . $name. '"'.">\n");
	    
	    $summary = convert_special_char($xls_rows[$idx][IDX_COL_SUMMARY]);  
		$summary = nl2p(htmlspecialchars($summary));
		fwrite($xmlFileHandle,"<summary><![CDATA[" . $summary . "]]></summary>\n");
	    
	    $steps = convert_special_char($xls_rows[$idx][IDX_COL_STEPS]);
	    $steps = nl2p(htmlspecialchars($steps));
	    fwrite($xmlFileHandle,"<steps><![CDATA[".$steps."]]></steps>\n");
	    
		$expresults = convert_special_char($xls_rows[$idx][IDX_COL_EXPRESULTS]);
		$expresults = nl2p(htmlspecialchars($expresults));
	    fwrite($xmlFileHandle,"<expectedresults><![CDATA[".$expresults."]]></expectedresults>\n");
	    
	    fwrite($xmlFileHandle,"</testcase>\n");
	}
	fwrite($xmlFileHandle,"</testcases>\n");
	fclose($xmlFileHandle);
	
}

// 20090402 - BUGID 1519: Extract this function from create_xml_tcspec_from_xls()
function convert_special_char($target_string)
{
	$from_char = iconv("CP1252", config_get('charset'), '\205');
	$to_char = "...";

	if ($from_char)
	{
		return str_replace($from_char, $to_char, $target_string);
	}
	else
	{
		return $string;
	}
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

    $key='hit_criteria';
    $args->$key = isset($_REQUEST[$key]) ? $_REQUEST[$key] : 'name';
       
        
    $args->importType = isset($_REQUEST['importType']) ? $_REQUEST['importType'] : null;
    $args->useRecursion = isset($_REQUEST['useRecursion']) ? $_REQUEST['useRecursion'] : 0;
    $args->location = isset($_REQUEST['location']) ? $_REQUEST['location'] : null; 
    $args->container_id = isset($_REQUEST['containerID']) ? intval($_REQUEST['containerID']) : 0;
    $args->bIntoProject = isset($_REQUEST['bIntoProject']) ? intval($_REQUEST['bIntoProject']) : 0;
    
    $args->containerType = isset($_REQUEST['containerType']) ? intval($_REQUEST['containerType']) : 0;
    $args->do_upload = isset($_REQUEST['UploadFile']) ? 1 : 0;
    
    $args->userID = $_SESSION['userID'];
    $args->tproject_id = $_SESSION['testprojectID'];
    
    return $args;
}


/**
 * processCustomFields
 *
 * Analise custom field info related to test case being imported.
 * If everything OK, assign to test case.
 * Else return an array of messages.
 *
 *
 * @internal revisions
 * 20100905 - franciscom - BUGID 3431 - Custom Field values at Test Case VERSION Level
 */
function processCustomFields(&$tcaseMgr,$tcaseName,$tcaseId,$tcversionId,$cfValues,$cfDefinition,$messages)
{
    static $missingCfMsg;
    $cf2insert=null;
    $resultMsg=null;
      
    foreach($cfValues as $value)
    {
       if( isset($cfDefinition[$value['name']]) )
       {
           $cf2insert[$cfDefinition[$value['name']]['id']]=array('type_id' => $cfDefinition[$value['name']]['type'],
                                                                 'cf_value' => $value['value']);         
       }
       else
       {
           if( !isset($missingCfMsg[$value['name']]) )
           {
               $missingCfMsg[$value['name']] = sprintf($messages['cfield'],$value['name'],$messages['tcase']);
           }
           $resultMsg[] = array($tcaseName,$missingCfMsg[$value['name']]); 
       }
    }  
    //
    // BUGID 3431 - Custom Field values at Test Case VERSION Level
    $tcaseMgr->cfield_mgr->design_values_to_db($cf2insert,$tcversionId,null,'simple');
    return $resultMsg;
}

/**
 * processRequirements
 *
 * Analise requirements info related to test case being imported.
 * If everything OK, assign to test case.
 * Else return an array of messages.
 *
 * 20100911 - amitkhullar - BUGID 3764
 */
function processRequirements(&$dbHandler,&$reqMgr,$tcaseName,$tcaseId,$tcReq,$reqSpecSet,$messages)
{
    static $missingReqMsg;
    static $missingReqSpecMsg;
    static $cachedReqSpec;
    $resultMsg=null;
	$tables = tlObjectWithDB::getDBTables(array('requirements'));


    foreach($tcReq as $ydx => $value)
    {
      $cachedReqSpec=array();
      $doit=false;
      if( ($doit=isset($reqSpecSet[$value['doc_id']])) )
      {
          if( !(isset($cachedReqSpec[$value['req_spec_title']])) )
          {
              // $cachedReqSpec
              // key: Requirement Specification Title
              // value: map with follogin keys
              //        id => requirement specification id
              //        req => map with key: requirement document id
              $cachedReqSpec[$value['req_spec_title']]['id']=$reqSpecSet[$value['doc_id']]['id'];
              $cachedReqSpec[$value['req_spec_title']]['req']=null;
          }
      }
    
      if($doit)
      {
          $useit=false;
          $req_spec_id=$cachedReqSpec[$value['req_spec_title']]['id'];
    
          // Check if requirement with desired document id exists on requirement specification.
          // If not => create message for user feedback.
          if( !($useit=isset($cachedReqSpec[$value['req_spec_title']]['req'][$value['doc_id']])) )
          {
              $sql = " SELECT REQ.id from {$tables['requirements']} REQ " .
                     " WHERE REQ.req_doc_id='{$dbHandler->prepare_string($value['doc_id'])}' " .
                     " AND REQ.srs_id={$req_spec_id} ";     
                   
              $rsx=$dbHandler->get_recordset($sql);
              if( $useit=((!is_null($rsx) && count($rsx) > 0) ? true : false) )
              {
                $cachedReqSpec[$value['req_spec_title']]['req'][$value['doc_id']]=$rsx[0]['id'];
              }  
          }
          
          
          if($useit)
          {
              $reqMgr->assign_to_tcase($cachedReqSpec[$value['req_spec_title']]['req'][$value['doc_id']],$tcaseId);
          }
          else
          {
              if( !isset($missingReqMsg[$value['doc_id']]) )
              {
                  $missingReqMsg[$value['doc_id']]=sprintf($messages['req'],
                                                       $value['doc_id'],$value['req_spec_title']);  
              }
              $resultMsg[] = array($tcaseName,$missingReqMsg[$value['doc_id']]); 
          }
      } 
      else
      {
          // Requirement Specification not found
          if( !isset($missingReqSpecMsg[$value['req_spec_title']]) )
          {
              $missingReqSpecMsg[$value['req_spec_title']]=sprintf($messages['req_spec'],$value['req_spec_title']);  
          }
          $resultMsg[] = array($tcaseName,$missingReqSpecMsg[$value['req_spec_title']]); 
      }
      
    } //foreach
     
    return $resultMsg;
}



/**
 * 
 *
 */
function importTestCasesFromSimpleXML(&$db,&$simpleXMLObj,$parentID,$tproject_id,$userID,$kwMap,$duplicateLogic)
{
	$resultMap = null;
	$xmlTCs = $simpleXMLObj->xpath('//testcase');
	$tcData = getTestCaseSetFromSimpleXMLObj($xmlTCs);
	if ($tcData)
	{
		$resultMap = saveImportedTCData($db,$tcData,$tproject_id,$parentID,$userID,$kwMap,$duplicateLogic);
	}	
	return $resultMap;
}

/**
 * 
 *
 * @internal revisions
 * 20100317 - added internalid - BUGID 3236
 */
function getTestCaseSetFromSimpleXMLObj($xmlTCs)
{
	$tcSet = null;
	if (!$xmlTCs)
	{
		return $tcSet;
	}
		
	$jdx = 0;
	$loops2do=sizeof($xmlTCs);
	$tcaseSet = array();
	
	// $tcXML['elements'] = array('string' => array("summary","preconditions"),
    //             'integer' => array("node_order","externalid","execution_type","importance"));
	// $tcXML['attributes'] = array('string' => array("name"), 'integer' =>array('internalid'));

	$tcXML['elements'] = array('string' => array("summary" => null,"preconditions" => null),
                               'integer' => array("node_order" => null,"externalid" => null,
                               					  "execution_type" => null ,"importance" => null));
	$tcXML['attributes'] = array('string' => array("name" => 'trim'), 
	                             'integer' =>array('internalid' => null));

	for($idx = 0; $idx < $loops2do; $idx++)
	{
        $dummy = getItemsFromSimpleXMLObj(array($xmlTCs[$idx]),$tcXML);
        $tc = $dummy[0]; 
        
		if ($tc)
		{
			// Test Case Steps
			$steps = getStepsFromSimpleXMLObj($xmlTCs[$idx]->steps->step);
			$tc['steps'] = $steps;

			$keywords = getKeywordsFromSimpleXMLObj($xmlTCs[$idx]->keywords->keyword);
			if ($keywords)
			{
				$tc['keywords'] = $keywords;
			}

			$cf = getCustomFieldsFromSimpleXMLObj($xmlTCs[$idx]->custom_fields->custom_field);
			if($cf)
			{
			    $tc['customfields'] = $cf;  
			} 

			$requirements = getRequirementsFromSimpleXMLObj($xmlTCs[$idx]->requirements->requirement);
			if($requirements)
			{
			    $tc['requirements'] = $requirements;  
			} 
   		}	
    	$tcaseSet[$jdx++] = $tc;    
    }
	return $tcaseSet;
}


/**
 * 
 *
 * @internal revisions
 * 20100821 - franciscom - BUGID 3695 - added "execution_type"
 */
function getStepsFromSimpleXMLObj($simpleXMLItems)
{
  	$itemStructure['elements'] = array('string' => array("actions"=>null,"expectedresults" => null),
				                       'integer' => array("step_number" => null,"execution_type" => null));
				                       
    // 20110205 - franciscom - seems key 'transformations' is not managed on
    // getItemsFromSimpleXMLObj(), then ??? is useless???				                       
  	$itemStructure['transformations'] = array("expectedresults" => "expected_results");
				                       
	$items = getItemsFromSimpleXMLObj($simpleXMLItems,$itemStructure);

    // need to do this due to (maybe) a wrong name choice for XML element
	if( !is_null($items) )
	{
		$loop2do = count($items);
		for($idx=0; $idx < $loop2do; $idx++)
		{
			$items[$idx]['expected_results'] = '';
			if( isset($items[$idx]['expectedresults']) )
			{
				$items[$idx]['expected_results'] = $items[$idx]['expectedresults'];
				unset($items[$idx]['expectedresults']);
			}
		}
	}
	// new dBug($items);
	return $items;
}

function getCustomFieldsFromSimpleXMLObj($simpleXMLItems)
{
  	$itemStructure['elements'] = array('string' => array("name" => 'trim',"value" => 'trim'));
	$items = getItemsFromSimpleXMLObj($simpleXMLItems,$itemStructure);
	return $items;

}

function getRequirementsFromSimpleXMLObj($simpleXMLItems)
{
  	$itemStructure['elements'] = array('string' => array("req_spec_title" => 'trim',
  														 "doc_id" => 'trim' ,"title" => 'trim' ));
	$items = getItemsFromSimpleXMLObj($simpleXMLItems,$itemStructure);
	return $items;
}

function getKeywordsFromSimpleXMLObj($simpleXMLItems)
{
  	$itemStructure['elements'] = array('string' => array("notes" => null));
  	$itemStructure['attributes'] = array('string' => array("name" => 'trim'));
	$items = getItemsFromSimpleXMLObj($simpleXMLItems,$itemStructure);
	return $items;
}


/*
  function: importTestSuite
  args :
  returns: 
  
  @internal revisions
  added duplicate logic
  
*/
function importTestSuitesFromSimpleXML(&$dbHandler,&$xml,$parentID,$tproject_id,
									   $userID,$kwMap,$importIntoProject = 0,$duplicateLogic)
{
	static $tsuiteXML;
	static $tsuiteMgr;
	static $myself;
	static $callCounter = 0;
	$resultMap = array();
    
	// $callCounter++;
	if(is_null($tsuiteXML) )
	{
		$tsuiteXML = array();
		$tsuiteXML['elements'] = array('string' => array("details" => null),
			                           'integer' => array("node_order" => null));
		$tsuiteXML['attributes'] = array('string' => array("name" => 'trim'));
		
		$tsuiteMgr = new testsuite($dbHandler);
		
		$myself = __FUNCTION__;
	}
	
	if($xml->getName() == 'testsuite')
	{
		
		// getItemsFromSimpleXMLObj() first argument must be an array
        $dummy = getItemsFromSimpleXMLObj(array($xml),$tsuiteXML);
        $tsuite = current($dummy); 
		$tsuiteID = $parentID;  // hmmm, not clear

		if ($tsuite['name'] != "")
		{
			// Check if Test Suite with this name exists on this container
			// if yes -> update instead of create
			$info = $tsuiteMgr->get_by_name($tsuite['name'],$parentID);
			if( is_null($info) )
			{
				$ret = $tsuiteMgr->create($parentID,$tsuite['name'],$tsuite['details'],$tsuite['node_order']);
				$tsuiteID = $ret['id'];
			}
			else
			{
				$tsuiteID = $info[0]['id'];
				$ret = $tsuiteMgr->update($tsuiteID,$tsuite['name'],$tsuite['details'],null,$tsuite['node_order']);
			}
			
			unset($tsuite);
			unset($dummy);
			
			if (!$tsuiteID)
			{
				return null;
			}	
		}
		else if($importIntoProject)
		{
			$tsuiteID = $tproject_id;
		}

		$childrenNodes = $xml->children();	
		$loop2do = sizeof($childrenNodes);
		
		for($idx = 0; $idx < $loop2do; $idx++)
		{
			$target = $childrenNodes[$idx];
			switch($target->getName())
			{
				case 'testcase':
				    // getTestCaseSetFromSimpleXMLObj() first argument must be an array
					$tcData = getTestCaseSetFromSimpleXMLObj(array($target));

					// 20100904 - francisco.mancardi@gruppotesi.com
					// echo 'Going to work on Test Case INSIDE Test Suite:' . $tsuite['name'] . '<br>';
					$resultMap = array_merge($resultMap,
											 saveImportedTCData($dbHandler,$tcData,$tproject_id,
											                    $tsuiteID,$userID,$kwMap,$duplicateLogic));
					unset($tcData);
				break;

				case 'testsuite':
					$resultMap = array_merge($resultMap,
											 $myself($dbHandler,$target,$tsuiteID,
											         $tproject_id,$userID,$kwMap,$importIntoProject,$duplicateLogic));
				break;

				// do not understand why we need to do this particular logic.
				// Need to understand				
				case 'details':
					if (!$importIntoProject)
					{
						$keywords = getKeywordsFromSimpleXMLObj($target->xpath("//keyword"));
						if($keywords)
						{
							$kwIDs = buildKeywordList($kwMap,$keywords);
							$tsuiteMgr->addKeywords($tsuiteID,$kwIDs);
						}
					}
				break;
				
			}			
		}
	}
	return $resultMap;
}
?>