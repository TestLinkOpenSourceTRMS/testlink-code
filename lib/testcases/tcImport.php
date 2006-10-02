<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: tcImport.php,v $
 *
 * @version $Revision: 1.12 $
 * @modified $Date: 2006/10/02 17:36:56 $
*/
require('../../config.inc.php');
require_once('common.php');
require_once('import.inc.php');
require_once('csv.inc.php');
require_once('xml.inc.php');
testlinkInitPage($db);

$source = isset($HTTP_POST_FILES['uploadedFile']['tmp_name']) ? $HTTP_POST_FILES['uploadedFile']['tmp_name'] : null;
$bImport = isset($_POST['import']) ? 1 : 0;
$importType = isset($_POST['importType']) ? $_POST['importType'] : null;
$bRecursive = isset($_REQUEST['bRecursive']) ? $_REQUEST['bRecursive'] : 0;
$location = isset($_POST['location']) ? strings_stripSlashes($_POST['location']) : null; 
$container_id = isset($_REQUEST['containerID']) ? intval($_REQUEST['containerID']) : 0;
$resultMap = null;

$userID = $_SESSION['userID'];
$tproject_id = $_SESSION['testprojectID'];
$productName = $_SESSION['testprojectName'];
$dest = TL_TEMP_PATH . session_id()."-importtcs.csv";

// check the uploaded file
if (($source != 'none') && ($source != ''))
{ 
	if (move_uploaded_file($source, $dest))
	{
		switch($importType)
		{
			case 'XML':
				$pfn = "importTestCaseDataFromXML";
				break;
		}
		if ($pfn)
		{
			$pfn($db,$dest,$container_id,$tproject_id,$userID,$bRecursive);
		}
	}
} 
function importTestCaseDataFromXML(&$db,$fileName,$parentID,$tproject_id,$userID,$bRecursive)
{
	$xmlTCs = null;
	$dom = domxml_open_file($fileName);
	if ($dom)
	{
		$root = $dom->document_element();
		
		$keywords = importKeywordsFromXML($root->get_elements_by_tagname("keyword"));
		if ($keywords)
		{
			$tproject = new testproject($db);
			$tproject->addKeywords($tproject_id,$keywords);
			$kwMap = $tproject->get_keywords_map($tproject_id);
			$kwMap = array_flip($kwMap);
		}
		if ($bRecursive && $root->tagname == 'testsuite')
			$resultMap = importTestSuite($db,$root,$parentID,$tproject_id,$userID,$kwMap);
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

function importTestSuite(&$db,&$node,$parentID,$tproject_id,$userID,$kwMap)
{
	$resultMap = null;
	if ($node->tagname() == 'testsuite')
	{
		$name = $node->get_attribute("name");
		$details = getNodeContent($node,'details');
		
		$ts = new testsuite($db);
		$ret = $ts->create($parentID,$name,$details);
		$tsID = $ret['id'];
		if (!$tsID)
			return null;
		
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
					$keywords = importKeywordsFromXML($cNode->get_elements_by_tagname("keyword"));
					if ($keywords)
					{
						$kwIDs = buildKeywordList($kwMap,$keywords);
						$ts->addKeywords($tsID,$kwIDs);
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
	$tproject = new testproject($db);
	
	$resultMap = array();
	
	for($i = 0;$i < sizeof($tcData);$i++)
	{
		$tc = $tcData[$i];
		$tcase_mgr = new testcase($db);
		
		$summary = $tc['summary'];
		$expected_results = $tc['expectedresults'];
		$steps = $tc['steps'];
		$name = $tc['name'];
		
		$kwIDs = null;
		if ($tc['keywords'])
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
	if (!$xmlTCs)
		return $tcs;
		
	$tcs = null;
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

					
$smarty = new TLSmarty();
$smarty->assign('bRecursive',$bRecursive); 
$smarty->assign('resultMap',$resultMap); 
$smarty->assign('tcFormatStrings',$g_tcFormatStrings);
$smarty->assign('importTypes',$g_tcImportTypes);
$smarty->assign('productName', $productName);
$smarty->assign('containerID', $container_id);
$smarty->assign('productID', $testproject_id);
$smarty->assign('importLimitKB',TL_IMPORT_LIMIT / 1024);
$smarty->display('tcimport.tpl');
?>