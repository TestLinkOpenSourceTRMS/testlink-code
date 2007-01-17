<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: tcImport.php,v $
 * Filename $RCSfile: tcImport.php,v $
 * @version $Revision: 1.18 $
 *
 * @modified $Date: 2007/01/17 20:47:56 $ by $Author: schlundus $
*/
require('../../config.inc.php');
require_once('common.php');
require_once('import.inc.php');
require_once('csv.inc.php');
require_once('xml.inc.php');
testlinkInitPage($db);

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

$dest = TL_TEMP_PATH . session_id()."-importtcs.csv";
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
  if( $container_id == $tproject_id )
  {
    $container_description=lang_get('testproject');
  }
}

if ($do_upload)
{
	// check the uploaded file
	$source = isset($_FILES['uploadedFile']['tmp_name']) ? $_FILES['uploadedFile']['tmp_name'] : null;
	if (($source != 'none') && ($source != ''))
	{ 
		$file_check = check_valid_ftype($_FILES['uploadedFile'],$importType);
		if($file_check['status_ok'])
		{
			if (move_uploaded_file($source, $dest))
			{
				switch($importType)
				{
					case 'XML':
					$pcheck_fn  = "check_xml_tc_tsuite";
					$pimport_fn = "importTestCaseDataFromXML";
					break;
				}
				if ($pcheck_fn)
				{
					$file_check = $pcheck_fn($dest,$bRecursive);
					if($file_check['status_ok'])
					{
						if ($pimport_fn)
						{
							$resultMap = $pimport_fn($db,$dest,$container_id,$tproject_id,
											$userID,$bRecursive,$bIntoProject);
						}
					}
				}
			}
		}
	}
	else
	{
		$file_check = array('status_ok' => 0, 'msg' => lang_get('please_choose_file_to_import'));
		$importType = null;
	}
}

$smarty = new TLSmarty();
$smarty->assign('import_title',$import_title);  
$smarty->assign('file_check',$file_check);  
$smarty->assign('bRecursive',$bRecursive); 
$smarty->assign('resultMap',$resultMap); 
$smarty->assign('tcFormatStrings',$g_tcFormatStrings);
$smarty->assign('importTypes',$g_tcImportTypes);
$smarty->assign('testprojectName', $testprojectName);
$smarty->assign('containerID', $container_id);
$smarty->assign('container_name', $container_name);
$smarty->assign('container_description', $container_description);
$smarty->assign('bIntoProject',$bIntoProject);
$smarty->assign('importLimitKB',TL_IMPORT_LIMIT / 1024);
$smarty->assign('bImport',strlen($importType));
$smarty->display('tcImport.tpl');

function importTestCaseDataFromXML(&$db,$fileName,$parentID,$tproject_id,$userID,$bRecursive,$importIntoProject = 0)
{
	$xmlTCs = null;
	$resultMap  = null;
	$dom = domxml_open_file($fileName);
	
	if ($dom)
	{
		$root = $dom->document_element();
		
		$keywords = importKeywordsFromXML($root->get_elements_by_tagname("keyword"));
		$kwMap = null;
		if ($keywords)
		{
			$tproject = new testproject($db);
			$tproject->addKeywords($tproject_id,$keywords);
			$kwMap = $tproject->get_keywords_map($tproject_id);
			$kwMap = array_flip($kwMap);
		}
		if ($bRecursive && $root->tagname == 'testsuite')
			$resultMap = importTestSuite($db,$root,$parentID,$tproject_id,$userID,$kwMap,$importIntoProject);
		else if (!$bRecursive && $root->tagname == 'testcases')
			$resultMap = importTestCases($db,$root,$parentID,$tproject_id,$userID,$kwMap);
	}
	return $resultMap;
}

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
		for($i = 0;$i < sizeof($cNodes);$i++)
		{
			$cNode = $cNodes[$i];
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
	for($i = 0; $i <$tc_qty ;$i++)
	{
		$tc = $tcData[$i];
		
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

function buildKeywordList($kwMap,$keywords,$bList = false)
{
	$kwIDs = array();
	for($j = 0;$j < sizeof($keywords);$j++)
	{
		$kwIDs[] = $kwMap[$keywords[$j]['keyword']];
	}
	if ($bList)
		$kwIDs = implode(",",$kwIDs);
	return $kwIDs;
}

function importTCsFromXML($xmlTCs)
{
	$tcs = null;
	if (!$xmlTCs)
		return $tcs;
		
	$j = 0;
	for($i = 0;$i < sizeof($xmlTCs);$i++)
	{
		$xmlTC = $xmlTCs[$i];
		if ($xmlTC->node_type() != XML_ELEMENT_NODE)
			continue;
		$tc = importTCFromXML($xmlTC);
		if ($tc)
		{
			$keywords = importKeywordsFromXML($xmlTC->get_elements_by_tagname("keyword"));
			if ($keywords)
				$tc['keywords'] = $keywords;
			$tcs[$j++] = $tc;
		}
	}
	return $tcs;
}


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

function check_valid_ftype($upload_info,$import_type)
{
	$ret = array();
	$ret['status_ok'] = 0;
	$ret['msg']='ok';
	
	$mime_types = array();
	$import_type = strtoupper($import_type);
	
	$mime_import_types['text/xml'] = array('XML' => 'XML');
	
	if(isset($mime_import_types[$upload_info['type']])) 
	{
		if(isset($mime_import_types[$upload_info['type']][$import_type]))
		{
			$ret['status_ok'] = 1;
			if( isset($mime_types['check_ext'][$upload_info['type']]))
			{
				$path_parts = pathinfo($upload_info['name']);
				if($path_parts['extension'] != $mime_types['check_ext'][$upload_info['type']])
				{
					$status_ok = 0;    
					$ret['msg'] = lang_get('file_is_not_text');
				}
			}
		}
		else
			$ret['msg'] = lang_get('file_is_not_ok_for_import_type');
	}
	else
		$ret['msg'] = lang_get('file_is_not_xml');
	
	return $ret;
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
			if($root->tagname != 'testcases')
				$file_check=array('status_ok' => 0, 'msg' => lang_get('wrong_xml_tcase_file'));
		}
	}
	return $file_check;
}
?>
