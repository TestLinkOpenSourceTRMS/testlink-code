<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * SCOPE:
 * Generate documentation Test report based on Test plan data.
 *
 * @filesource 	printDocument.php
 * @author 		  Martin Havlat
 * @copyright 	2007-2012, TestLink community 
 * @link 		    http://www.teamst.org/index.php
 *
 *
 * @internal revisions
 * @since 1.9.5
 * 20121017 - asimon - TICKET 5288 - print importance/priority when printing test specification/plan
 *
 */
require_once('../../config.inc.php');
require('../../cfg/reports.cfg.php');
require_once('common.php');
require_once('print.inc.php');
require_once('displayMgr.php');

// displayMemUsage('START SCRIPT - LINE:' .__LINE__);

$treeForPlatform = null;
$docText = '';					
$topText = '';
$doc_data = new stdClass(); // gather content and tests related data

testlinkInitPage($db);
$tproject = new testproject($db);
$tree_manager = &$tproject->tree_manager;

$args = init_args();
$decode = getDecode($tree_manager);
list($doc_info,$my) = initEnv($db,$args,$tproject,$_SESSION['userID']);
$printingOptions = initPrintOpt($_REQUEST,$doc_info);

$subtree = $tree_manager->get_subtree($args->itemID,$my['filters'],$my['options']);
$treeForPlatform[0] = &$subtree;
$doc_info->title = $doc_info->tproject_name;

switch ($doc_info->type)
{
	case DOC_REQ_SPEC:
		switch($doc_info->content_range)
		{
			case 'reqspec':
    	      	$spec_mgr = new requirement_spec_mgr($db);
    	  	    $spec = $spec_mgr->get_by_id($args->itemID);
    	  	    unset($spec_mgr);
    	  	    
    	  	    $spec['childNodes'] = isset($subtree['childNodes']) ? $subtree['childNodes'] : null;
    	  	    $spec['node_type_id'] = $decode['node_descr_id']['requirement_spec'];
    	  	    
				unset($treeForPlatform[0]['childNodes']);
				$treeForPlatform[0]['childNodes'][0] = &$spec;

				$doc_info->title = htmlspecialchars($args->tproject_name . 
    	  	                                        $tlCfg->gui_title_separator_2 . $spec['title']);  	               

			break;    
    	} // $doc_info->content_range
	break;
		
    case DOC_TEST_SPEC:
        // TICKET 5288 - print importance when printing test specification
        $printingOptions['importance'] = $doc_info->test_priority_enabled;

		switch($doc_info->content_range)
		{
			case 'testsuite':
    	      	$tsuite = new testsuite($db);
    	  	    $tInfo = $tsuite->get_by_id($args->itemID);
    	  	    $tInfo['childNodes'] = isset($subtree['childNodes']) ? $subtree['childNodes'] : null;
    
    	  	    $treeForPlatform[0]['childNodes'] = array($tInfo);

				$doc_info->title = htmlspecialchars(isset($tInfo['name']) ? $args->tproject_name .
    	  	      	               $tlCfg->gui_title_separator_2.$tInfo['name'] : $args->tproject_name);
    	  	  	break;    
    	}
    break;
    
    case DOC_TEST_PLAN:
    case DOC_TEST_REPORT:
			$tplan_mgr = new testplan($db);
		    $tplan_info = $tplan_mgr->get_by_id($args->tplan_id);
		    $doc_info->testplan_name = htmlspecialchars($tplan_info['name']);
		    $doc_info->testplan_scope = $tplan_info['notes'];
		    $doc_info->title = $doc_info->testplan_name;

            $getOpt = array('outputFormat' => 'map', 'addIfNull' => true);
            $platforms = $tplan_mgr->getPlatforms($args->tplan_id,$getOpt);   
			$items2use = null;
			$execid_filter = null;
			$executed_qty = 0;
			$treeForPlatform = array();

            // TICKET 5288 - print priority when printing test plan
            $printingOptions['priority'] = $doc_info->test_priority_enabled;

			switch($doc_info->content_range)
			{
				case 'testproject':
			
					// displayMemUsage('BEFORE LOOP ON PLATFORMS');
					$linkedBy = array();
    	   	    	
    	   	    	// Prepare Node -> pn
                    $pnFilters = null;
                      
                    // due to Platforms we need to use 'viewType' => 'executionTree',
                    // if not we get ALWAYS the same set of test cases linked to test plan
                    // for each platform -> WRONG 
                    $pnOptions =  array('hideTestCases' => 0, 'showTestCaseID' => 1,
                    					'viewType' => 'executionTree',
		            					'getExternalTestCaseID' => 0, 'ignoreInactiveTestCases' => 0);

					foreach ($platforms as $platform_id => $platform_name)
					{
						$filters = array('platform_id' => $platform_id);	
    	   	    	  	$linkedBy[$platform_id] = $tplan_mgr->getLinkedStaticView($args->tplan_id,$filters);
   	   	    			
    	   	    	  	// IMPORTANTE NOTE:
    	   	    	  	// We are in a loop and we use tree on prepareNode, that changes it,
    	   	    	  	// then we can not use anymore a reference BUT WE NEED A COPY.
    	   	    	  	$tree2work = $subtree;
    	   	    	  	if (!$linkedBy[$platform_id])
    	   	    	  	{
    	   	    			$tree2work['childNodes'] = null;
    	   	    	  	}

						$dummy4reference = null;
    	   	    	  	prepareNode($db,$tree2work,$decode,$dummy4reference,$dummy4reference,
    	   	    	  				$linkedBy[$platform_id],$pnFilters,$pnOptions);
    	   	    	  	$treeForPlatform[$platform_id] = $tree2work; 
    	   	    	}              
            	break;
    	       
				case 'testsuite':
					$linkedBy = array();
					$branch_tsuites = null;

					$tsuite = new testsuite($db);
					$tInfo = $tsuite->get_by_id($args->itemID);
					$tInfo['node_type_id'] = $decode['node_descr_id']['testsuite'];
					$children_tsuites = $tree_manager->get_subtree_list($args->itemID,$decode['node_descr_id']['testsuite']);
					if( !is_null($children_tsuites) and trim($children_tsuites) != "")
					{
						$branch_tsuites = explode(',',$children_tsuites);
					}
					$branch_tsuites[]=$args->itemID;
					
					$doc_info->title = htmlspecialchars(isset($tInfo['name']) ? $tInfo['name'] : $doc_info->testplan_name);
					
    	   	        $filters = array( 'tsuites_id' => $branch_tsuites);
					foreach ($platforms as $platform_id => $platform_name)
					{
						// IMPORTANTE NOTICE:
						// This need to be initialized on each iteration because prepareNode() make changes on it.
						$tInfo['childNodes'] = isset($subtree['childNodes']) ? $subtree['childNodes'] : null;
						
    	            	$filters['platform_id'] = $platform_id;
						$items2use[$platform_id] = null;
    	   	        	$linkedBy[$platform_id] = $tplan_mgr->getLinkedStaticView($args->tplan_id, $filters); 
						
					
						// After architecture changes on how CF design values for Test Cases are
						// managed, we need the test case version ID and not test case ID
						// In addition if we loop over Platforms we need to save this set each time!!!
    	            	$items2loop = !is_null($linkedBy[$platform_id]) ? array_keys($linkedBy[$platform_id]) : null;
    	            	if( !is_null($items2loop) )
    	            	{ 
							foreach($items2loop as $rdx)
							{	
    	            			$items2use[$platform_id][] = $linkedBy[$platform_id][$rdx]['tcversion_id'];
    	            		}		
    	            	}
    	   	        	
						// Prepare Node -> pn
						$pnFilters = null;
                        $pnOptions =  array('hideTestCases' => 0);
                        $pnOptions = array_merge($pnOptions, $my['options']['prepareNode']);
						$dummy4reference = null;
						prepareNode($db,$tInfo,$decode,$dummy4reference,$dummy4reference,
									$linkedBy[$platform_id],$pnFilters,$pnOptions);
						
    	   	    	    $treeForPlatform[$platform_id]['childNodes'] = array($tInfo);   
                    }
				break;
			}  // switch($doc_info->content_range)
         
			// Create list of execution id, that will be used to compute execution time if
			// CF_EXEC_TIME custom field exists and is linked to current testproject
			$doc_data->statistics = null;                                            
			if ($printingOptions['metrics'])
			{
				$doc_data->statistics['estimated_execution'] = getStatsEstimatedExecTime($tplan_mgr,
																				 		                                     $items2use,$args->tplan_id);
         		
				$doc_data->statistics['real_execution'] = getStatsRealExecTime($tplan_mgr,$items2use,$args->tplan_id,$decode);

 			} // if ($printingOptions['metrics'])
    break;
}


// ----- rendering logic -----
$topText = renderHTMLHeader($doc_info->type.' '.$doc_info->title,$_SESSION['basehref'],$doc_info->type);
$topText .= renderFirstPage($doc_info);

// Init table of content (TOC) data
renderTOC($printingOptions);
$tocPrefix = null;
if( ($showPlatforms = !isset($treeForPlatform[0]) ? true : false) )
{
	$tocPrefix = 0;
}

if ($treeForPlatform)
{
	foreach ($treeForPlatform as $platform_id => $tree2work)            
	{
		if($tree2work)
		{
			$tree2work['name'] = $args->tproject_name;
			$tree2work['id'] = $args->tproject_id;
			$tree2work['node_type_id'] = $decode['node_descr_id']['testproject'];
			switch ($doc_info->type)
			{
				case DOC_REQ_SPEC:
					$docText .= renderReqSpecTreeForPrinting($db, $tree2work, $printingOptions, 
					                                         null, 0, 1, $args->user_id,0,$args->tproject_id);
				break;
				
				case DOC_TEST_SPEC:
					$docText .= renderSimpleChapter(lang_get('scope'), $doc_info->tproject_scope);
					$docText .= renderTestSpecTreeForPrinting($db, $tree2work, $doc_info->content_range,
					            $printingOptions, null, 0, 1, $args->user_id,0,null,$args->tproject_id,$platform_id);
				break;
			
				case DOC_TEST_PLAN:
					if ($printingOptions['testplan'])
					{
						$docText .= renderSimpleChapter(lang_get('scope'), $doc_info->testplan_scope);
					}
						
				case DOC_TEST_REPORT:
				    $tocPrefix++;
			    	if ($showPlatforms)
					{
						$docText .= renderPlatformHeading($tocPrefix, $platform_id, $platforms[$platform_id], 
						                                  $printingOptions);
					}
					$docText .= renderTestPlanForPrinting($db, $tree2work, $doc_info->content_range, 
					                                      $printingOptions, $tocPrefix, 0, 1, $args->user_id,
					                                      $args->tplan_id, $args->tproject_id, $platform_id);

					if (($doc_info->type == DOC_TEST_REPORT) && ($printingOptions['metrics']))
					{
						$docText .= buildTestPlanMetrics($doc_data->statistics,$platform_id);
					}	
				break;
			}
		}
	}
}
$docText .= renderEOF();

// Needed for platform feature
if ($printingOptions['toc'])
{
	$printingOptions['tocCode'] .= '</div>';
	$topText .= $printingOptions['tocCode'];
}
$docText = $topText . $docText;


// add application header to HTTP 
if (($args->format == FORMAT_ODT) || ($args->format == FORMAT_MSWORD))
{
	flushHttpHeader($args->format, $doc_info->type);
}

// send out the data
echo $docText;


/** 
 * Process input data
 * @return singleton list of input parameters 
 **/
function init_args()
{
	$args = new stdClass();
	$args->doc_type = $_REQUEST['type'];
	$args->level = isset($_REQUEST['level']) ?  $_REQUEST['level'] : null;
	$args->format = isset($_REQUEST['format']) ? $_REQUEST['format'] : null;
	$args->itemID = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
	$args->tplan_id = isset($_REQUEST['docTestPlanId']) ? $_REQUEST['docTestPlanId'] : 0;
	
	
	$args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
	$args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : 'xxx';
	$args->user_id = isset($_SESSION['userID']) ? intval($_SESSION['userID']) : null;


	return $args;
}


/** 
 * 
 * 
 **/
function initPrintOpt(&$UIhash,&$docInfo)
{
	// Elements in this array must be updated if $arrCheckboxes, in printDocOptions.php is changed.
	$pOpt = array ( 'toc' => 0,'body' => 0,'summary' => 0, 'header' => 0,'headerNumbering' => 1,
			        'passfail' => 0, 'author' => 0, 'notes' => 0, 'requirement' => 0, 'keyword' => 0, 
			        'cfields' => 0, 'testplan' => 0, 'metrics' => 0,
			        'req_spec_scope' => 0,'req_spec_author' => 0,
			        'req_spec_overwritten_count_reqs' => 0,'req_spec_type' => 0,
			        'req_spec_cf' => 0,'req_scope' => 0,'req_author' => 0,
			        'req_status' => 0,'req_type' => 0,'req_cf' => 0,'req_relations' => 0,
			        'req_linked_tcs' => 0,'req_coverage' => 0,'displayVersion' => 0);
	
	foreach($pOpt as $opt => $val)
	{
		$pOpt[$opt] = (isset($UIhash[$opt]) && ($UIhash[$opt] == 'y'));
	}					
	$pOpt['docType'] = $docInfo->type;
	$pOpt['tocCode'] = ''; // to avoid warning because of undefined index

	return $pOpt;
}


/** 
 * 
 * 
 **/
function getDecode(&$treeMgr)
{

	$resultsCfg = config_get('results');

	$dcd = array();
	$dcd['node_descr_id'] = $treeMgr->get_available_node_types();
	$dcd['node_id_descr'] = array_flip($dcd['node_descr_id']);

	$dcd['status_descr_code'] = $resultsCfg['status_code'];
	$dcd['status_code_descr'] = array_flip($dcd['status_descr_code']);

	return $dcd;
}

/** 
 * 
 * @internal revisions:
 * 20121017 - TICKET 5288 - print importance/priority when printing test specification/plan
 **/
function initEnv(&$dbHandler,&$argsObj,&$tprojectMgr,$userID)
{

	$my = array();
	$doc = new stdClass(); 

	$my['options'] = array(	'recursive' => true, 'prepareNode' => null,
							'order_cfg' => array("type" =>'spec_order') );
	$my['filters'] = array(	'exclude_node_types' =>  array(	'testplan'=>'exclude me', 
                                                      		'requirement_spec'=>'exclude me', 
					                                  		'requirement'=>'exclude me'),
							'exclude_children_of' => array(	'testcase'=>'exclude my children',
                              		                        'requirement_spec'=> 'exclude my children'));     

	$lblKey	= array(DOC_TEST_SPEC => 'title_test_spec', DOC_TEST_PLAN => 'test_plan',
					DOC_TEST_REPORT => 'test_report', DOC_REQ_SPEC => 'req_spec');


	$doc->content_range = $argsObj->level;
	$doc->type = $argsObj->doc_type;
	$doc->type_name = lang_get($lblKey[$doc->type]);
	$doc->author = '';
	$doc->title = '';
	 
	switch ($doc->type)
	{
		case DOC_TEST_PLAN: 
			$my['options']['order_cfg'] = array("type" =>'exec_order',"tplan_id" => $argsObj->tplan_id);
			break;
		
		case DOC_TEST_REPORT: 
			$my['options']['order_cfg'] = array("type" =>'exec_order',											
												"tplan_id" => $argsObj->tplan_id);
			$my['options']['prepareNode'] = array('viewType' => 'executionTree');												
			break;
			
		case DOC_REQ_SPEC:
			$my['filters'] = array(	'exclude_node_types' =>  array(	'testplan'=>'exclude me', 
                                                      			  	'testsuite'=>'exclude me',
					                                  				'testcase'=>'exclude me'),
                       				'exclude_children_of' => array(	'testcase'=>'exclude my children',
		                            				        		'testsuite'=> 'exclude my children',
		                            				        		'requirement'=>'exclude my children'));
			break;
	}


	$user = tlUser::getById($dbHandler,$userID);
	if ($user)
	{
		$doc->author = htmlspecialchars($user->getDisplayName());
	}
	unset($user);

	$dummy = $tprojectMgr->get_by_id($argsObj->tproject_id);
	$doc->tproject_name = htmlspecialchars($dummy['name']);
	$doc->tproject_scope = $dummy['notes'];

    // TICKET 5288 - print importance/priority when printing test specification/plan
    $doc->test_priority_enabled = $dummy['opt']->testPriorityEnabled;

	return array($doc,$my);
}


/** 
 * 
 * 
 **/
function getStatsEstimatedExecTime(&$tplanMgr,&$items2use,$tplanID)
{

	$min = array();
 	$stat = null;        
	if( is_null($items2use) )
	{
		// will work on all test cases present on Test Project.
		// these IDs will be searched inside get_estimated_execution_time()
		$min = $tplanMgr->get_estimated_execution_time($tplanID);
	}
	else
	{	
		$min['totalMinutes'] = 0;
		$min['totalTestCases'] = 0;
		$min['platform'] = array();
		foreach( $items2use as $platID => $itemsForPlat )
		{	
			if( !is_null($itemsForPlat) )
			{	
				$tmp = $tplanMgr->get_estimated_execution_time($tplanID,$itemsForPlat,$platID);
				$min['platform'][$platID] = $tmp['platform'][$platID];
				$min['totalMinutes'] += $tmp['totalMinutes']; 
				$min['totalTestCases'] += $tmp['totalTestCases']; 
			}
		}		
	}
	
	if ($min['totalMinutes'] != "0")
	{
		$stat['minutes'] = $min['totalMinutes']; 
		$stat['tcase_qty'] = $min['totalTestCases']; 
	
		foreach($min['platform'] as $platformID => $elem)
		{
			$stat['platform'][$platformID] = $elem; 		 
		}	
	}
	
 	return $stat;        
}


/** 
 * 
 * 
 **/
function getStatsRealExecTime(&$tplanMgr,&$lastExecBy,$tplanID,$decode)
{
   	$min = array();
	$stat = null;
	$executed_qty = 0;
	$items2use = array();
	
	if( count($lastExecBy) > 0 )
    {
		// divide execution by Platform ID
		$p2loop = array_keys($lastExecBy);
   	 	foreach($p2loop as $platfID)
   	 	{
			$i2loop = array_keys($lastExecBy[$platfID]);
   	 		foreach($i2loop as $xdx)
   	 		{
   	 			$info = &$lastExecBy[$platfID][$xdx];
    	        if( $info['exec_status'] != $decode['status_descr_code']['not_run'] )
        	    {  
            		$items2use[$platfID][] = $info['exec_id'];
                	$executed_qty++;
	            }    
		    }	
		}
		
		if( $executed_qty > 0)
	    { 
			$min['totalMinutes'] = 0;
			$min['totalTestCases'] = 0;
			$min['platform'] = array();
			
			foreach( $items2use as $platID => $itemsForPlat )
			{	
				if( !is_null($itemsForPlat) )
				{	
					$tmp = $tplanMgr->get_execution_time($tplanID,$itemsForPlat,$platID);
					$min['platform'][$platID] = $tmp['platform'][$platID];
					$min['totalMinutes'] += $tmp['totalMinutes']; 
					$min['totalTestCases'] += $tmp['totalTestCases']; 

				}
			}		
		}
	}
	else
   	{
		$min = $tplanMgr->get_execution_time($tplanID);
	}

	// ----------------------------------------------------------
	// Arrange data for caller
	if ($min['totalMinutes'] != "0")
	{
		$stat['minutes'] = $min['totalMinutes']; 
		$stat['tcase_qty'] = $min['totalTestCases']; 
	
		foreach($min['platform'] as $platformID => $elem)
		{
			$stat['platform'][$platformID] = $elem; 		 
		}	
	}
	// ----------------------------------------------------------

	return $stat;        
}


?>