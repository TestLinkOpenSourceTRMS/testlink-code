<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: tcImport.php,v $
 * Filename $RCSfile: tcImport.php,v $
 * @version $Revision: 1.29 $
 *
 * @modified $Date: 2007/12/16 12:20:58 $ by $Author: schlundus $
*/
require('../../config.inc.php');
require_once('common.php');
require_once('import.inc.php');
require_once('csv.inc.php');
require_once('xml.inc.php');
require_once('keyword.class.php');
require_once('../../third_party/phpexcel/reader.php');

testlinkInitPage($db);

$template_dir='testcases/';

$importType = isset($_POST['importType']) ? $_POST['importType'] : null;
$bRecursive = isset($_REQUEST['bRecursive']) ? $_REQUEST['bRecursive'] : 0;
$location = isset($_POST['location']) ? strings_stripSlashes($_POST['location']) : null; 
$container_id = isset($_REQUEST['containerID']) ? intval($_REQUEST['containerID']) : 0;
$bIntoProject = isset($_REQUEST['bIntoProject']) ? intval($_REQUEST['bIntoProject']) : 0;
$resultMap = null;
$containerType = isset($_REQUEST['containerType']) ? intval($_REQUEST['containerType']) : 0;
$do_upload = isset($_REQUEST['UploadFile']) ? 1 : 0;

$userID = $_SESSION['userID'];
$tproject_id = $_SESSION['testprojectID'];
$testprojectName = $_SESSION['testprojectName'];

$dest_common = TL_TEMP_PATH . session_id(). "-importtcs";
$dest_files=array('XML' => $dest_common . ".csv",
                  'XLS' => $dest_common . ".xls");

$dest=$dest_files['XML'];
if( !is_null($importType) )
{                  
  $dest=$dest_files[$importType];
}

$file_check = array('status_ok' => 1, 'msg' => 'ok');

$import_title = lang_get('title_tc_import_to');
$container_description=lang_get('test_case');
if($bRecursive)
{
	$import_title = lang_get('title_tsuite_import_to');  
	$container_description=lang_get('test_suite');
}

$container_name = '';
if($container_id)
{
	$tree_mgr = new tree($db);
	$node_info = $tree_mgr->get_node_hierachy_info($container_id);    
	$container_name = $node_info['name'];
	if($container_id == $tproject_id)
		$container_description=lang_get('testproject');
}

if ($do_upload)
{
	// check the uploaded file
	$source = isset($_FILES['uploadedFile']['tmp_name']) ? $_FILES['uploadedFile']['tmp_name'] : null;
	if (($source != 'none') && ($source != ''))
	{ 
		$file_check['status_ok'] = 1;
		if (move_uploaded_file($source, $dest))
		{
			switch($importType)
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
				$file_check = $pcheck_fn($dest,$bRecursive);
		}
		if($file_check['status_ok'] && $pimport_fn)
		{
			$resultMap = $pimport_fn($db,$dest,$container_id,$tproject_id,
										                 $userID,$bRecursive,$bIntoProject);
		}
	}
	else
	{
		$file_check = array('status_ok' => 0, 'msg' => lang_get('please_choose_file_to_import'));
		$importType = null;
	}
}

if($bRecursive)
{
  $obj_mgr = new testsuite($db);
}
else
{
  $obj_mgr = new testcase($db);
}
$import_file_types = $obj_mgr->get_import_file_types();


$smarty = new TLSmarty();
$smarty->assign('import_title',$import_title);  
$smarty->assign('file_check',$file_check);  
$smarty->assign('bRecursive',$bRecursive); 
$smarty->assign('resultMap',$resultMap); 
$smarty->assign('tcFormatStrings',$g_tcFormatStrings);
$smarty->assign('importTypes',$import_file_types);
$smarty->assign('testprojectName', $testprojectName);
$smarty->assign('containerID', $container_id);
$smarty->assign('container_name', $container_name);
$smarty->assign('container_description', $container_description);
$smarty->assign('bIntoProject',$bIntoProject);
$smarty->assign('importLimitKB',TL_IMPORT_LIMIT / 1024);
$smarty->assign('bImport',strlen($importType));
$smarty->display($template_dir . 'tcImport.tpl');

/*
  function: importTestCaseDataFromXML

  args :
  
  returns: 

*/
function importTestCaseDataFromXML(&$db,$fileName,$parentID,$tproject_id,
                                   $userID,$bRecursive,$importIntoProject = 0)
{
	$xmlTCs = null;
	$resultMap  = null;
	$dom = domxml_open_file($fileName);
	
	if ($dom)
	{
		$root = $dom->document_element();
		
		$xmlKeywords = $root->get_elements_by_tagname("keywords");
		$kwMap = null;
		if ($xmlKeywords)
		{
			$tproject = new testproject($db);
			if ($tproject->importKeywordsFromXML($tproject_id,$xmlKeywords[0]->dump_node()) == OK)
			{
				$kwMap = $tproject->get_keywords_map($tproject_id);
				$kwMap = array_flip($kwMap);
			}
		}
		if ($bRecursive && $root->tagname == 'testsuite')
			$resultMap = importTestSuite($db,$root,$parentID,$tproject_id,$userID,$kwMap,$importIntoProject);
		else if (!$bRecursive && $root->tagname == 'testcases')
			$resultMap = importTestCases($db,$root,$parentID,$tproject_id,$userID,$kwMap);
	}
	return $resultMap;
}


/*
  function: importTestCases

  args :
  
  returns: 

*/
function importTestCases(&$db,&$node,$parentID,$tproject_id,$userID,$kwMap)
{
	$resultMap = null;
	if ($node->tagname == 'testcases')
	{
		$xmlTCs = $node->get_elements_by_tagname("testcase");
		$tcData = importTCsFromXML($xmlTCs);
		if ($tcData)
			$resultMap = saveImportedTCData($db,$tcData,$tproject_id,$parentID,$userID,$kwMap);
	}
	return $resultMap;
}

/*
  function: importTestSuite

  args :
  
  returns: 

*/
function importTestSuite(&$db,&$node,$parentID,$tproject_id,$userID,$kwMap,$importIntoProject = 0)
{
	$resultMap = null;
	if ($node->tagname() == 'testsuite')
	{
		$name = $node->get_attribute("name");
		$details = getNodeContent($node,'details');
		
		$ts = null;
		if (strlen($name))
		{
			$ts = new testsuite($db);
			$ret = $ts->create($parentID,$name,$details);
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

/*
  function: saveImportedTCData

  args :
  
  returns: 

*/
function saveImportedTCData(&$db,$tcData,$tproject_id,$container_id,$userID,$kwMap)
{
	if (!$tcData)
		return;
		
	$resultMap = array();
	
	$tc_qty = sizeof($tcData);
	if($tc_qty)
	{
		$tcase_mgr = new testcase($db);
		$tproject = new testproject($db);
	}
	for($idx = 0; $idx <$tc_qty ; $idx++)
	{
		$tc = $tcData[$idx];
		
		$summary = $tc['summary'];
		$expected_results = $tc['expectedresults'];
		$steps = $tc['steps'];
		$name = $tc['name'];
		
		$kwIDs = null;
		if (isset($tc['keywords']) && $tc['keywords'])
			$kwIDs = buildKeywordList($kwMap,$tc['keywords'],true);
			
		if ($ret = $tcase_mgr->create($container_id,$name,$summary,$steps,$expected_results,$userID,$kwIDs))
			$resultMap[] = array($name,$ret['msg']);
	}

	return $resultMap;
}


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
		$kwIDs = implode(",",$kwIDs);
	return $kwIDs;
}


/*
  function: importTCsFromXML

  args :
  
  returns: 

*/
function importTCsFromXML($xmlTCs)
{
	$tcs = null;
	if (!$xmlTCs)
		return $tcs;
		
	$jdx = 0;
	for($idx = 0; $idx < sizeof($xmlTCs); $idx++)
	{
		$xmlTC = $xmlTCs[$idx];
		if ($xmlTC->node_type() != XML_ELEMENT_NODE)
			continue;
		$tc = importTCFromXML($xmlTC);
		if ($tc)
		{
			$keywords = importKeywordsFromXML($xmlTC->get_elements_by_tagname("keyword"));
			if ($keywords)
				$tc['keywords'] = $keywords;
			$tcs[$jdx++] = $tc;
		}
	}
	return $tcs;
}


/*
  function: importTCFromXML()

  args :
  
  returns: 

*/
function importTCFromXML(&$xmlTC)
{
	if (!$xmlTC)
		return null;
		
	$tc = null;
	$tc['name'] = $xmlTC->get_attribute("name");
	$tc['summary'] = trim(getNodeContent($xmlTC,"summary"));
	$tc['steps'] = trim(getNodeContent($xmlTC,"steps"));
	$tc['expectedresults'] = trim(getNodeContent($xmlTC,"expectedresults"));
	
	return $tc; 		
}
/*
  function: 

           Check if at least the file starts seems OK

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
  define('FIRTS_DATA_ROW',2);
  define('IDX_COL_NAME',1);
  define('IDX_COL_SUMMARY',2);
  define('IDX_COL_STEPS',3);
  define('IDX_COL_EXPRESULTS',4);
  
  
  
  $xls_handle = new Spreadsheet_Excel_Reader(); 
  
  $xls_handle->setOutputEncoding('CP1251'); 
  $xls_handle->read($xls_filename);
  $xls_rows = $xls_handle->sheets[0]['cells'];
  $xls_row_qty=sizeof($xls_rows);
  
  if( $xls_row_qty <= FIRTS_DATA_ROW )
  {
     return;  // >>>----> bye!
  }
  
  // OK, go ahead
  $xmlFileHandle = fopen($xml_filename, 'w') or die("can't open file");
  fwrite($xmlFileHandle,"<testcases>\n");

  for($idx=FIRTS_DATA_ROW; $idx <= $xls_row_qty; $idx++ )
  {                       
    $name=htmlspecialchars($xls_rows[$idx][IDX_COL_NAME]);
    fwrite($xmlFileHandle,"<testcase name=" . '"' . $name. '"'.">\n");
    
    $summary=htmlspecialchars($xls_rows[$idx][IDX_COL_SUMMARY]);
    fwrite($xmlFileHandle,"<summary>" . $xls_rows[$idx][IDX_COL_SUMMARY] . "</summary>\n");
    
    $steps=str_replace('…',"...",$xls_rows[$idx][IDX_COL_STEPS]);
    $steps=htmlspecialchars($xls_rows[$idx][IDX_COL_STEPS]);
    fwrite($xmlFileHandle,"<steps>".$steps."</steps>\n");
    
    $expresults=str_replace('…',"...",$xls_rows[$idx][IDX_COL_EXPRESULTS]);
    $expresults=htmlspecialchars($xls_rows[$idx][IDX_COL_EXPRESULTS]);
    fwrite($xmlFileHandle,"<expectedresults>".$expresults."</expectedresults>\n");
    
    fwrite($xmlFileHandle,"</testcase>\n");
  }
  fwrite($xmlFileHandle,"</testcases>\n");
  fclose($xmlFileHandle);
}

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
?>
