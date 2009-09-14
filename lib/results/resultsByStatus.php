<?php
/**
* TestLink Open Source Project - http://testlink.sourceforge.net/
* $Id: resultsByStatus.php,v 1.69 2009/09/14 13:23:32 franciscom Exp $
*
* @author	Martin Havlat <havlat@users.sourceforge.net>
* @author Chad Rosen
* @author KL
*
*
* rev : 
*   20090517 - franciscom - fixed management of deleted testers
* 	20090414 - amikhullar - BUGID: 2374 - Show Assigned User in the Not Run Test Cases Report 
* 	20090325 - amkhullar  - BUGID 2249
* 	20090325 - amkhullar  - BUGID 2267
*   20080602 - franciscom - changes due to BUGID 1504
*   20070623 - franciscom - BUGID 911
*/
require('../../config.inc.php');
require_once('common.php');
require_once("results.class.php");
require_once('displayMgr.php');
require_once('users.inc.php');
testlinkInitPage($db,true,false,"checkRights");

$templateCfg = templateConfiguration();

$resultsCfg = config_get('results');
$statusCode = $resultsCfg['status_code'];

$args = init_args($statusCode);
$gui = initializeGui($statusCode,$args);

$tplan_mgr = new testplan($db);
$tproject_mgr = new testproject($db);
$tcase_mgr = new testcase($db);
$tplan_info = $tplan_mgr->get_by_id($args->tplan_id);
$tproject_info = $tproject_mgr->get_by_id($args->tproject_id);

$gui->platformSet = $tplan_mgr->getPlatforms($args->tplan_id,'map');
if( is_null($gui->platformSet) )
{
	$gui->platformSet = array('');
}
$gui->bugInterfaceOn = config_get('bugInterfaceOn');
$deleted_user_label = lang_get('deleted_user');

$gui->tplan_name = $tplan_info['name'];
$gui->tproject_name = $tproject_info['name'];

$testCaseCfg = config_get('testcase_cfg');
$testCasePrefix = $tproject_info['prefix'] . $testCaseCfg->glue_character;;

$buildSet = $tplan_mgr->get_builds($args->tplan_id);
$lastBuildID = $tplan_mgr->get_max_build_id($args->tplan_id,1,1);
$results = new results($db, $tplan_mgr, $tproject_info, $tplan_info, ALL_TEST_SUITES, ALL_BUILDS);

$mapOfLastResult = $results->getMapOfLastResult();
$arrOwners = getUsersForHtmlOptions($db);

$canExecute = has_rights($db,"tp_execute");
if (is_array($mapOfLastResult)) 
{
	foreach($mapOfLastResult as $suiteId => $suiteContents)
    {
    	foreach($suiteContents as $tcId => $platformData)
      	{
			foreach($platformData as $platform_id => $tcaseContent)
			{
  	        	$lastBuildIdExecuted = $tcaseContent['buildIdLastExecuted'];
	  	    	if ($tcaseContent['result'] == $args->type)
	  	    	{
	  	    		$bugString = null; 
	  	    		$currentBuildInfo = null;
	  	    		if ($lastBuildIdExecuted) 
	  	    		{
	  	    			$currentBuildInfo = $buildSet[$lastBuildIdExecuted];
	  	    		}
	  	    		else if ($args->type == $statusCode['not_run'])
	  	    		{
	  	    			$lastBuildIdExecuted = $lastBuildID;
	  	    		}
            	
	  	    		$buildName = $currentBuildInfo['name'];
	  	    		$notes = $tcaseContent['notes'];
	  	    		$suiteName = $tcaseContent['suiteName'];
	  	    		$name = $tcaseContent['name'];
	  	    		$tester_id = $tcaseContent['tester_id'];
	  	    		$executions_id = $tcaseContent['executions_id'];
	  	    		$tcversion_id = $tcaseContent['tcversion_id'];
	        	    $testVersion = $tcaseContent['version'];
                    $platformName = $gui->platformSet[$platform_id];
	        	       
	  	    		// ------------------------------------------------------------------------------------
	  	    		// 20070623 - BUGID 911 - no need to localize, is already localized
	  	    		$execution_ts = $tcaseContent['execution_ts'];
	  	    		$localizedTS = '';
	  	    		if ($execution_ts != null) 
	  	    		{
	  	    		   $localizedTS = $execution_ts;
	  	    		}
	  	    		// ------------------------------------------------------------------------------------
            	
            	    if($gui->bugInterfaceOn)
            	    {
	  	    			$bugString = $results->buildBugString($db, $executions_id);
	  	    			//20090325 - amkhullar  - BUGID 2249 - find missing bug links with TC
	  	    			if (is_null($bugString))
	  	    			{
	  	    				$gui->without_bugs_counter += 1;
	  	    			}
	  	    		} 
	  	    		$testTitle = buildTCLink($tcId,$tcversion_id,$name,$lastBuildIdExecuted,
	  	    		                         $testCasePrefix . $tcaseContent['external_id'],$args->tplan_id);
            	
            	    
            	    $testerName = '';
            	    if(!is_null($tester_id) && $tester_id > 0 )
            	    {
	  	    		    if (array_key_exists($tester_id, $arrOwners))
	  	    		    {
	  	    		       $testerName = $arrOwners[$tester_id];
	  	    		    }
	  	    		    else
	  	    		    {
	  	    		        // user id has been deleted
	  	    		        $testerName = sprintf($deleted_user_label,$tester_id);
	  	    		    }
	  	    		}
	  	    		
	  	    		// we escape here , because on smarty template we use a simple loop algorithm
	  	    		$suiteName = htmlspecialchars($suiteName);
	  	    		$platformName = htmlspecialchars($platformName);
	  	    		if($args->type == $statusCode['not_run'])
	  	    		{
	  	    			//amitkhullar - BUGID: 2374-Show Assigned User in the Not Run Test Cases Report 
	  	    			$gui->dataSet[] = array('suiteName' => $suiteName,'testTitle' => $testTitle,
	  	    			                        'testVersion' => $testVersion, 'platformName' => $platformName,
	  	    			                        'testerName' => htmlspecialchars($testerName));
	  	    		}
	  	    		else
					{
	  	    			$gui->dataSet[] = array('suiteName' => $suiteName, 'testTitle' => $testTitle,
	  	    			                        'testVersion' => $testVersion, 'platformName' => $platformName,
	  	    			                        'buildName' => htmlspecialchars($buildName),
            	                                'testerName' => htmlspecialchars($testerName),
            	                                'localizedTS' => htmlspecialchars($localizedTS),
        			                            'notes' => strip_tags($notes),'bugString' => $bugString);
      				}
	  	    	}
	  	    }
	    } //foreach $suiteContents
    } //foreach $mapOfLastResult
} // end if

$smarty = new TLSmarty();
$smarty->assign('gui', $gui );

displayReport($templateCfg->template_dir . $templateCfg->default_template, $smarty, $args->format);

/**
* Function returns number of Test Cases in the Test Plan
* @return string Link of Test ID + Title
*/
function buildTCLink($tcID,$tcversionID, $title, $buildID,$testCaseExternalId, $tplanId)
{
	$title = htmlspecialchars($title);
	$suffix = htmlspecialchars($testCaseExternalId) . ":&nbsp;<b>" . $title. "</b></a>";
	//Added tplan_id as a parameter - amitkhullar -BUGID 2267
	$testTitle = '<a href="lib/execute/execSetResults.php?level=testcase&build_id='
				 . $buildID . '&id=' . $tcID . '&version_id='. $tcversionID . '&tplan_id=' . $tplanId .'">';
	$testTitle .= $suffix;

	return $testTitle;
}


function init_args($statusCode)
{
    $iParams = array(
		"format" => array(tlInputParameter::INT_N),
		"tplan_id" => array(tlInputParameter::INT_N),
    	"type" => array(tlInputParameter::STRING_N,0,1),
	);
	$args = new stdClass();
	$pParams = R_PARAMS($iParams,$args);
	
	$args->tproject_id = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0;

	return $args;
}

/**
 * initializeGui
 *
 */
function initializeGui($statusCode,&$argsObj)
{
    $guiObj = new stdClass();
    
    // Count for the Failed Issues whose bugs have to be raised/not linked. 
    $guiObj->without_bugs_counter = 0; 
    $guiObj->dataSet = null;
    $guiObj->title = null;
    $guiObj->type = $argsObj->type;

    // Humm this may be can be configured ???
    foreach(array('failed','blocked','not_run') as $verbose_status)
    {
        if($argsObj->type == $statusCode[$verbose_status])
        {
            $guiObj->title = lang_get('list_of_' . $verbose_status);
            break;
        }  
    }
    if(is_null($guiObj->title))
    {
    	tlog('wrong value of GET type');
    	exit();
    }

    
    return $guiObj;    
}

function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'testplan_metrics');
}
?>
