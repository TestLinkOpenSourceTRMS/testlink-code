<?php

/**
 * 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @package     TestLink
 * @author      Leon Jordans
 * @copyright   2016 TestLink community
 * @filesource  reqManageSubs.php
 * 
 *    
 */

require_once("../../config.inc.php");
require_once("common.php");
require_once('exttable.class.php');
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();
$tproject_mgr = new testproject($db);
$req_mgr = new requirement_mgr($db);

$cfg = getCfg();
$args = init_args($tproject_mgr);

$gui = init_gui($args);
$gui->reqIDs = $tproject_mgr->get_all_requirement_ids($args->tproject_id);

$smarty = new TLSmarty();
if(count($gui->reqIDs) > 0) 
{
  $chronoStart = microtime(true);

  $pathCache = null;
  $imgSet = $smarty->getImages();

  // get type and status labels
  $type_labels = init_labels($cfg->req->type_labels);
  $status_labels = init_labels($cfg->req->status_labels);
  
  $labels2get = array('no' => 'No', 'yes' => 'Yes', 'not_aplicable' => null,'never' => null,
                      'req_spec_short' => null,'title' => null, 'version' => null, 'th_coverage' => null,
                      'frozen' => null, 'type'=> null,'status' => null,'th_relations' => null, 'requirements' => null,
                      'number_of_reqs' => null, 'number_of_versions' => null, 'requirement' => null, 'subscribtion_state' => null,
                      'version_revision_tag' => null, 'week_short' => 'calendar_week_short');
          
  $labels = init_labels($labels2get);

  $version_option = ($args->all_versions) ? requirement_mgr::ALL_VERSIONS : requirement_mgr::LATEST_VERSION; 
  if( $version_option == requirement_mgr::LATEST_VERSION )
  {
    $reqSet = $req_mgr->getByIDBulkLatestVersionRevision($gui->reqIDs,array('outputFormat' => 'mapOfArray'));
  }
  else
  {
    $reqSet = $req_mgr->get_by_id($gui->reqIDs, $version_option,null,array('output_format' => 'mapOfArray'));
    // new dBug($reqSet);
  }

  // array to gather table data row per row
  $rows = array();    
 
  $subscribedReqs = $req_mgr->getAllReqSubscribed($args->tproject_id, $_SESSION["userID"]);
  foreach($gui->reqIDs as $id) 
  {
    $req = $reqSet[$id];

    // create the link to display
    $title = htmlentities($req[0]['req_doc_id'], ENT_QUOTES, $cfg->charset) . $cfg->glue_char . 
             htmlentities($req[0]['title'], ENT_QUOTES, $cfg->charset);
    
    // reqspec-"path" to requirement
    if( !isset($pathCache[$req[0]['srs_id']]) )
    {
      $path = $req_mgr->tree_mgr->get_path($req[0]['srs_id']);
      foreach ($path as $key => $p) 
      {
        $path[$key] = $p['name'];
      }
      $pathCache[$req[0]['srs_id']] = htmlentities(implode("/", $path), ENT_QUOTES, $cfg->charset);
    }         

    foreach($req as $version) 
    {
      // get content for each row to display
      $result = array();
        
      /**
        * IMPORTANT: 
        * the order of following items in this array has to be
        * the same as column headers are below!!!
        * 
        * should be:
    * 1. path
    * 2. title
    * 3. created_on
    * 4. subscribed
        */
        
      $result[] = $pathCache[$req[0]['srs_id']];
        
      $edit_link = '<a href="javascript:openLinkedReqVersionWindow(' . $id . ',' . $version['version_id'] . ')">' . 
                   '<img title="' .$labels['requirement'] . '" src="' . $imgSet['edit'] . '" /></a> ';
      
      $result[] =  '<!-- ' . $title . ' -->' . $edit_link . $title;
          
      // use html comment to sort properly by this columns (extjs)
      $result[] = "<!--{$version['creation_ts']}-->" . localizeTimeStamp($version['creation_ts'],$cfg->datetime) . 
                    " ({$version['author']})";
    
    $isReqSubscribed = false;
      foreach($subscribedReqs as $req) {
    if($version["id"] == $req["reqID"]) {
      $isReqSubscribed = true;
      break;
    }
    }
    if($isReqSubscribed) {
    $result[] = "<!--subscribed-->".lang_get("req_already_subscribed");
    }
    else {
    $result[] = "<!--subscribed-->".lang_get("req_not_subscribed_yet");  
    }
    
      $rows[] = $result;
    }
  }
   
  // -------------------------------------------------------------------------------------------------- 
  // Construction of EXT-JS table starts here    
  if(($gui->row_qty = count($rows)) > 0 ) 
  {
    $version_string = ($args->all_versions) ? $labels['number_of_versions'] : $labels['number_of_reqs'];
    $gui->pageTitle .= " - " . $version_string . ": " . $gui->row_qty;
       
    /**
     * get column header titles for the table
     * 
     * IMPORTANT: 
     * the order of following items in this array has to be
     * the same as row content above!!!
     * 
     * should be:
     * 1. path
     * 2. title
     * 3. created_on
  * 4. subscribed
     */
    $columns = array();
    $columns[] = array('title_key' => 'req_spec_short', 'width' => 150);
    $columns[] = array('title_key' => 'title', 'width' => 150);
    $columns[] = array('title_key' => 'created_on', 'width' => 100);
	$columns[] = array('title_key' => 'subscribtion_state', 'width' => 100);

    // create table object, fill it with columns and row data and give it a title
    $matrix = new tlExtTable($columns, $rows, 'tl_table_req_overview');
    $matrix->title = $labels['requirements'];
        
    // group by Req Spec
    $matrix->setGroupByColumnName($labels['req_spec_short']);
        
    // sort by coverage descending if enabled, otherwise by status
    $sort_name = ($cfg->req->expected_coverage_management) ? $labels['th_coverage'] : $labels['status'];
    $matrix->setSortByColumnName($sort_name);
    $matrix->sortDirection = 'DESC';
        
    // define toolbar
    $matrix->showToolbar = true;
    $matrix->toolbarExpandCollapseGroupsButton = true;
    $matrix->toolbarShowAllColumnsButton = true;
    $matrix->toolbarRefreshButton = true;
    $matrix->showGroupItemsCount = true;
    
    // show custom field content in multiple lines
    $matrix->addCustomBehaviour('text', array('render' => 'columnWrap'));
    $gui->tableSet= array($matrix);
  }

  $chronoStop = microtime(true);
} 


$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/**
 * initialize user input
 * 
 * @param resource &$tproject_mgr reference to testproject manager
 * @return array $args array with user input information
 */
function init_args(&$tproject_mgr)
{
  $args = new stdClass();
  
  $args->tproject_id = intval(isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0);
  $args->tproject_name = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : '';
  if($args->tproject_id > 0) 
  {
    $tproject_info = $tproject_mgr->get_by_id($args->tproject_id);
    $args->tproject_name = $tproject_info['name'];
    $args->tproject_description = $tproject_info['notes'];
  }
  
  return $args;
}


/**
 * initialize GUI
 * 
 * @param stdClass $argsObj reference to user input
 * @return stdClass $gui gui data
 */
function init_gui(&$argsObj) 
{
  $gui = new stdClass();
  
  $gui->pageTitle = lang_get('caption_req_overview');
  $gui->tproject_name = $argsObj->tproject_name;
  $gui->tableSet = null;
  
  return $gui;
}


/**
 *
 */
function getCfg()
{
  $cfg = new stdClass();
  $cfg->glue_char = config_get('gui_title_separator_1');
  $cfg->charset = config_get('charset');
  $cfg->req = config_get('req_cfg');
  $cfg->date = config_get('date_format');
  $cfg->datetime = config_get('timestamp_format');

  // on requirement creation motification timestamp is set to default value "0000-00-00 00:00:00"
  $cfg->neverModifiedTS = "0000-00-00 00:00:00";

  // $cfg->req->expected_coverage_management = FALSE;   // FORCED FOR TEST

  return $cfg;
}


/*
 * rights check function for testlinkInitPage()
 */
function checkRights(&$db, &$user)
{
  return $user->hasRight($db,'mgt_view_req');
}

