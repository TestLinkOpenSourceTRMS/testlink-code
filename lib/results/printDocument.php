<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * SCOPE:
 * Generate documentation Test report based on Test plan data.
 *
 * @filesource  printDocument.php
 * @author      Martin Havlat
 * @copyright   2007-2013, TestLink community 
 * @link        http://www.teamst.org/index.php
 *
 *
 * @internal revisions
 * @since 1.9.10
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

list($args,$tproject_mgr,$decode) = init_args($db);
$tree_manager = &$tproject_mgr->tree_manager;
list($doc_info,$my) = initEnv($db,$args,$tproject_mgr,$args->user_id);

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
    
  case DOC_TEST_PLAN_DESIGN:
    $printingOptions['metrics'] = true; // FORCE
  
  case DOC_TEST_PLAN_EXECUTION:
  case DOC_TEST_PLAN_EXECUTION_ON_BUILD:
    $tplan_mgr = new testplan($db);
    $tplan_info = $tplan_mgr->get_by_id($args->tplan_id);

    if($args->build_id > 0)
    {
      $xx = $tplan_mgr->get_builds($args->tplan_id,null,null,array('buildID' => $args->build_id));
      $doc_info->build_name = htmlspecialchars($xx[$args->build_id]['name']);
    }  


    $doc_info->testplan_name = htmlspecialchars($tplan_info['name']);
    $doc_info->testplan_scope = $tplan_info['notes'];
    $doc_info->title = $doc_info->testplan_name;

    // Changed to get ALL platform attributes.
    $getOpt = array('outputFormat' => 'mapAccessByID', 'addIfNull' => true);
    $platforms = $tplan_mgr->getPlatforms($args->tplan_id,$getOpt);   
    $platformIDSet = array_keys($platforms);

    $printingOptions['priority'] = $doc_info->test_priority_enabled;
    $items2use = (object) array('estimatedExecTime' => null,'realExecTime' => null);
    $treeForPlatform = array();
      
    $filters = null;
    switch($doc_info->content_range)
    {
      case 'testproject':
        $treeForPlatform = buildContentForTestPlan($db,$subtree,$args->tplan_id,$platformIDSet,
                                                   $decode,$tplan_mgr,$filters);
      break;
             
      case 'testsuite':
        list($treeForPlatform,$items2use) = buildContentForTestPlanBranch($db,$subtree,$args->itemID,$args->tplan_id,
                                                                          $platformIDSet,$doc_info,$decode,$tplan_mgr,
                                                                          $my['options']['prepareNode']);
      break;
    }
         
    // Create list of execution id, that will be used to compute execution time if
    // CF_EXEC_TIME custom field exists and is linked to current testproject
    $doc_data->statistics = null;                                            
    if ($printingOptions['metrics'])
    {
      $doc_data->statistics = timeStatistics($items2use,$args->tplan_id,$decode,$tplan_mgr);
    }
  break;
}


// ----- rendering logic -----
$topText = renderHTMLHeader($doc_info->type . ' ' . $doc_info->title,$_SESSION['basehref'],$doc_info->type);
$topText .= renderFirstPage($doc_info);

// Init table of content (TOC) data
renderTOC($printingOptions);  // @TODO check if is really useful


$tocPrefix = null;
if( ($showPlatforms = !isset($treeForPlatform[0]) ? true : false) )
{
  $tocPrefix = 0;
}

if ($treeForPlatform)
{
  // Things that have to be printed just once
  // 
  switch ($doc_info->type)
  {
    case DOC_TEST_PLAN_DESIGN:
      $printingOptions['metrics'] = true; // FORCED

    case DOC_TEST_PLAN_EXECUTION:
    case DOC_TEST_PLAN_EXECUTION_ON_BUILD:
      $docText .= renderTestProjectItem($doc_info);
      $docText .= renderTestPlanItem($doc_info);
      $cfieldFormatting=array('table_css_style' => 'class="cf"');
      if ($printingOptions['cfields'])
      {
        $cfields = $tplan_mgr->html_table_of_custom_field_values($args->tplan_id,'design',null,$cfieldFormatting);
        $docText .= '<p>' . $cfields . '</p>';
      }
    break;        
  }

  foreach ($treeForPlatform as $platform_id => $tree2work)            
  {
    if(isset($tree2work['childNodes']) && sizeof($tree2work['childNodes']) > 0)
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
          $docText .= renderTestSpecTreeForPrinting($db, $_SESSION['basehref'], $tree2work, $doc_info->content_range,
                                                    $printingOptions, null, 0, 1, $args->user_id,0,null,
                                                    $args->tproject_id,$platform_id);
        break;
      
        case DOC_TEST_PLAN_DESIGN:
        case DOC_TEST_PLAN_EXECUTION:
        case DOC_TEST_PLAN_EXECUTION_ON_BUILD:
          $tocPrefix++;
          if ($showPlatforms)
          {
            $printingOptions['showPlatformNotes'] = true;
            $docText .= renderPlatformHeading($tocPrefix,$platforms[$platform_id],$printingOptions);
          }
          $docText .= renderTestPlanForPrinting($db, $_SESSION['basehref'],$tree2work, $doc_info->content_range, 
                                                $printingOptions, $tocPrefix, 0, 1, $args->user_id,
                                                $args->tplan_id, $args->tproject_id, 
                                                $platform_id,$args->build_id);

          if( $printingOptions['metrics'] )
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
 * 
 **/
function init_args(&$dbHandler)
{
  $iParams = array("apikey" => array(tlInputParameter::STRING_N,32,64),
                   "tproject_id" => array(tlInputParameter::INT_N), 
                   "tplan_id" => array(tlInputParameter::INT_N),  
                   "build_id" => array(tlInputParameter::INT_N),  
                   "docTestPlanId" => array(tlInputParameter::INT_N),  
                   "id" => array(tlInputParameter::INT_N),
                   "type" => array(tlInputParameter::STRING_N,0,20),
                   "format" => array(tlInputParameter::INT_N),
                   "level" => array(tlInputParameter::STRING_N,0,32));

  $args = new stdClass();
  $pParams = R_PARAMS($iParams,$args);

  // really UGLY HACK
  $typeDomain = array('test_plan' => 'testplan','test_report' => 'testreport');
  $args->type = isset($typeDomain[$args->type]) ? $typeDomain[$args->type] : $args->type;
  
  if( !is_null($args->apikey) )
  {
    $cerbero = new stdClass();
    $cerbero->args = new stdClass();
    $cerbero->args->tproject_id = $args->tproject_id;
    $cerbero->args->tplan_id = $args->tplan_id;

    if(strlen($args->apikey) == 32)
    {
      $cerbero->args->getAccessAttr = true;
      $cerbero->method = 'checkRights';
      $cerbero->redirect_target = "../../login.php?note=logout";
      setUpEnvForRemoteAccess($dbHandler,$args->apikey,$cerbero);
    }
    else
    {
      $args->addOpAccess = false;
      $cerbero->method = null;
      setUpEnvForAnonymousAccess($dbHandler,$args->apikey,$cerbero);
    }  
    $args->itemID = $args->tproject_id;
  }
  else
  {
    testlinkInitPage($dbHandler,false,false,"checkRights");  
    
    $args->tproject_id = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0;
    $args->tplan_id = isset($_REQUEST['docTestPlanId']) ? intval($_REQUEST['docTestPlanId']) : 0;
    $args->itemID = $args->id;
  }

  $tproject_mgr = new testproject($dbHandler);

  if($args->tproject_id > 0) 
  {
    $dummy = $tproject_mgr->get_by_id($args->tproject_id);
    $args->tproject_name = $dummy['name'];
  }
  else
  {
    $msg = __FILE__ . '::' . __FUNCTION__ . " :: Invalid Test Project ID ({$args->tproject_id})";
    throw new Exception($msg);
  }

  $args->doc_type = $args->type;
  $args->user_id = isset($_SESSION['userID']) ? intval($_SESSION['userID']) : null;


  $resultsCfg = config_get('results');
  $dcd = array();
  $dcd['node_descr_id'] = $tproject_mgr->tree_manager->get_available_node_types();
  $dcd['node_id_descr'] = array_flip($dcd['node_descr_id']);

  $dcd['status_descr_code'] = $resultsCfg['status_code'];
  $dcd['status_code_descr'] = array_flip($dcd['status_descr_code']);

  return array($args,$tproject_mgr,$dcd);
}


/** 
 * 
 * 
 **/
function initPrintOpt(&$UIhash,&$docInfo)
{
  // Elements in this array must be updated if $arrCheckboxes, in printDocOptions.php is changed.
  $pOpt = array( 'toc' => 0,'body' => 0,'summary' => 0, 'header' => 0,'headerNumbering' => 1,
                 'passfail' => 0, 'author' => 0, 'notes' => 0, 'requirement' => 0, 'keyword' => 0, 
                 'cfields' => 0, 'testplan' => 0, 'metrics' => 0, 'assigned_to_me' => 0, 
                 'req_spec_scope' => 0,'req_spec_author' => 0,'build_cfields' => 0,
                 'req_spec_overwritten_count_reqs' => 0,'req_spec_type' => 0,
                 'req_spec_cf' => 0,'req_scope' => 0,'req_author' => 0,
                 'req_status' => 0,'req_type' => 0,'req_cf' => 0,'req_relations' => 0,
                 'req_linked_tcs' => 0,'req_coverage' => 0,'displayVersion' => 0);
  
  $lightOn = isset($UIhash['allOptionsOn']);
  foreach($pOpt as $opt => $val)
  {
    $pOpt[$opt] = $lightOn || (isset($UIhash[$opt]) && ($UIhash[$opt] == 'y'));
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
 * 
 **/
function initEnv(&$dbHandler,&$argsObj,&$tprojectMgr,$userID)
{

  $my = array();
  $doc = new stdClass(); 

  $my['options'] = array('recursive' => true, 'prepareNode' => null,
                         'order_cfg' => array("type" =>'spec_order') );
  $my['filters'] = array('exclude_node_types' =>  array('testplan'=>'exclude me', 
                                                        'requirement_spec'=>'exclude me', 
                                                        'requirement'=>'exclude me'),
                         'exclude_children_of' => array('testcase'=>'exclude my children',
                                                        'requirement_spec'=> 'exclude my children'));     

  $lblKey  = array(DOC_TEST_SPEC => 'title_test_spec', DOC_TEST_PLAN_DESIGN => 'report_test_plan_design',
                   DOC_TEST_PLAN_EXECUTION => 'report_test_plan_execution', 
                   DOC_TEST_PLAN_EXECUTION_ON_BUILD => 'report_test_plan_execution_on_build', 
                   DOC_REQ_SPEC => 'req_spec');


  $doc->content_range = $argsObj->level;
  $doc->type = $argsObj->doc_type;
  $doc->type_name = lang_get($lblKey[$doc->type]);
  $doc->author = '';
  $doc->title = '';

   
  switch ($doc->type)
  {
    case DOC_TEST_PLAN_DESIGN: 
      $my['options']['order_cfg'] = array("type" =>'exec_order',"tplan_id" => $argsObj->tplan_id);
      break;
    
    case DOC_TEST_PLAN_EXECUTION: 
    case DOC_TEST_PLAN_EXECUTION_ON_BUILD: 
      $my['options']['order_cfg'] = array("type" =>'exec_order',                      
                                          "tplan_id" => $argsObj->tplan_id);
      $my['options']['prepareNode'] = array('viewType' => 'executionTree');                        
      break;
      
    case DOC_REQ_SPEC:
      $my['filters'] = array(  'exclude_node_types' =>  array(  'testplan'=>'exclude me', 
                                                                'testsuite'=>'exclude me',
                                                    'testcase'=>'exclude me'),
                               'exclude_children_of' => array(  'testcase'=>'exclude my children',
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
    // will work on all test cases present on Test Plan.
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
      $items2use[$platfID] = null;
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
        $min['platform'][$platID] = null;
        if( !is_null($itemsForPlat) )
        {  
          $tmp = $tplanMgr->get_execution_time($tplanID,$itemsForPlat,$platID);
          $min['platform'][$platID] = $tmp['platform'][$platID];
          $min['totalMinutes'] += isset($tmp['totalMinutes']) ? $tmp['totalMinutes'] : 0; 
          $min['totalTestCases'] += $tmp['totalTestCases']; 
        }
      } 
    }
  }
  else
  {
    $min = $tplanMgr->get_execution_time($tplanID);
  }

  // Arrange data for caller
  if ($min['totalMinutes'] != 0)
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
 */ 
function buildContentForTestPlan(&$dbHandler,$itemsTree,$tplanID,$platformIDSet,$decode,&$tplanMgr,$pnFilters=null)
{
  $linkedBy = array();
  $contentByPlatform = array();

  // due to Platforms we need to use 'viewType' => 'executionTree',
  // if not we get ALWAYS the same set of test cases linked to test plan
  // for each platform -> WRONG 
  $pnOptions =  array('hideTestCases' => 0, 'showTestCaseID' => 1,
                      'viewType' => 'executionTree',
                      'getExternalTestCaseID' => 0, 'ignoreInactiveTestCases' => 0);
  
  foreach($platformIDSet as $platform_id)  
  {
    $filters = array('platform_id' => $platform_id);  
    $linkedBy[$platform_id] = $tplanMgr->getLinkedStaticView($tplanID,$filters);

    // IMPORTANT NOTE:
    // We are in a loop and we use tree on prepareNode, that changes it,
    // then we can not use anymore a reference BUT WE NEED A COPY.
    $tree2work = $itemsTree;
    if (!$linkedBy[$platform_id])
    {
      $tree2work['childNodes'] = null;
    }
  
    $dummy4reference = null;
    prepareNode($dbHandler,$tree2work,$decode,$dummy4reference,$dummy4reference,
                $linkedBy[$platform_id],$pnFilters,$pnOptions);
  
    $contentByPlatform[$platform_id] = $tree2work; 
  }
  return $contentByPlatform;
}


/**
 *
 */
function buildContentForTestPlanBranch(&$dbHandler,$itemsTree,$branchRoot,$tplanID,$platformIDSet,
                                       &$docInfo,$decode,&$tplanMgr,$options=null)
{
  $linkedBy = array();
  $branch_tsuites = null;
  $contentByPlatform = array();  

  $pnOptions = array('hideTestCases' => 0);
  $pnOptions = array_merge($pnOptions, (array)$options);


  $tsuite = new testsuite($dbHandler);
  $tInfo = $tsuite->get_by_id($branchRoot);
  $tInfo['node_type_id'] = $decode['node_descr_id']['testsuite'];
  $docInfo->title = htmlspecialchars(isset($tInfo['name']) ? $tInfo['name'] : $docInfo->testplan_name);

  $children_tsuites = $tsuite->tree_manager->get_subtree_list($branchRoot,$decode['node_descr_id']['testsuite']);
  if( !is_null($children_tsuites) and trim($children_tsuites) != "")
  {
    $branch_tsuites = explode(',',$children_tsuites);
  }
  $branch_tsuites[]=$branchRoot;

  $metrics = (object) array('estimatedExecTime' => null,'realExecTime' => null);
  $filters = array( 'tsuites_id' => $branch_tsuites);
  foreach($platformIDSet as $platform_id)  
  {
    // IMPORTANTE NOTICE:
    // This need to be initialized on each iteration because prepareNode() make changes on it.
    $tInfo['childNodes'] = isset($itemsTree['childNodes']) ? $itemsTree['childNodes'] : null;
    
    $filters['platform_id'] = $platform_id;
    $metrics->estimatedExecTime[$platform_id] = null;      
    $metrics->realExecTime[$platform_id] = null;
    
    $avalon = $tplanMgr->getLTCVNewGeneration($tplanID, $filters, array('addExecInfo' => true)); 
    $k2l = array_keys($avalon);
    foreach($k2l as $key)
    {
      $linkedBy[$platform_id][$key] = $avalon[$key][$platform_id];
    } 
  
    // After architecture changes on how CF design values for Test Cases are
    // managed, we need the test case version ID and not test case ID
    // In addition if we loop over Platforms we need to save this set each time!!!
    $items2loop = !is_null($linkedBy[$platform_id]) ? array_keys($linkedBy[$platform_id]) : null;
    if( !is_null($items2loop) )
    { 
      foreach($items2loop as $rdx)
      {  
        $metrics->estimatedExecTime[$platform_id][] = $linkedBy[$platform_id][$rdx]['tcversion_id'];
      }    
    }

    // Prepare Node -> pn
    $pnFilters = null;
    $dummy4reference = null;
    $contentByPlatform[$platform_id]['childNodes'] = array();
    if(!is_null($linkedBy[$platform_id]))
    {
      prepareNode($dbHandler,$tInfo,$decode,$dummy4reference,$dummy4reference,
                  $linkedBy[$platform_id],$pnFilters,$pnOptions);
    
      $contentByPlatform[$platform_id]['childNodes'] = array($tInfo);   
    }
  }
  $metrics->realExecTime = $linkedBy;
  return array($contentByPlatform,$metrics);
}    

/**
 *
 */
function timeStatistics($items,$tplanID,$decode,$tplanMgr)
{
  $stats = array();
  $stats['estimated_execution'] = getStatsEstimatedExecTime($tplanMgr,$items->estimatedExecTime,$tplanID);
  $stats['real_execution'] = getStatsRealExecTime($tplanMgr,$items->realExecTime,$tplanID,$decode);
  return $stats;
}



/*
 * rights check function for testlinkInitPage()
 */
function checkRights(&$db,&$user,$context = null)
{
  if(is_null($context))
  {
    $context = new stdClass();
    $context->tproject_id = $context->tplan_id = null;
    $context->getAccessAttr = false; 
  }

  $check = $user->hasRight($db,'testplan_metrics',$context->tproject_id,$context->tplan_id,$context->getAccessAttr);
  return $check;
}
?>