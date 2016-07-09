<?php
/**
 * 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @package     TestLink
 * @author      Leon Jordans
 * @copyright   2016 TestLink community
 * @filesource  reqMonitorOverview.php
 * 
 *    
 */

require_once("../../config.inc.php");
require_once("common.php");
require_once('exttable.class.php');
require_once('requirements.inc.php');
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();
$tproject_mgr = new testproject($db);
$req_mgr = new requirement_mgr($db);

$args = init_args($tproject_mgr);
$gui = initializeGui($args,$tproject_mgr);

$cfg = getCfg();

// manageUserSubscribtion($db,$args);
$smarty = new TLSmarty();

if(count($gui->reqIDSet) > 0) 
{
  $pathCache = null;
  $imgSet = $smarty->getImages();

  // get type and status labels
  $lbl = getLabels($cfg->req);
  $reqSet = $req_mgr->getByIDBulkLatestVersionRevision($gui->reqIDSet,array('outputFormat' => 'mapOfArray'));
  $onClick = buildOnClick($args,$lbl['mixed'],$imgSet);

  if( $args->req_id > 0 )
  {
    $vk = array_flip(array('on','off'));
    if( isset($vk[$args->action]) )
    {
      $m2c = 'monitor' . ucfirst($args->action);
      $req_mgr->$m2c($args->req_id,$args->userID,$args->tproject_id);
    }  
  }  

  // array to gather table data row per row
  $rows = array();    
 
  $monitoredSet = $req_mgr->getMonitoredByUser($args->userID,$args->tproject_id);

  foreach($gui->reqIDSet as $id) 
  {
    $req = $reqSet[$id][0];
   
    // create the link to display
    $title = htmlentities($req['req_doc_id'], ENT_QUOTES, $cfg->charset) . $cfg->glue_char . 
             htmlentities($req['title'], ENT_QUOTES, $cfg->charset);
    
    // reqspec-"path" to requirement
    if( !isset($pathCache[$req['srs_id']]) )
    {
      $path = $req_mgr->tree_mgr->get_path($req['srs_id']);
      foreach ($path as $key => $p) 
      {
        $path[$key] = $p['name'];
      }
      $pathCache[$req['srs_id']] = htmlentities(implode("/", $path), ENT_QUOTES, $cfg->charset);
    }         

    // get content for each row to display
    $result = array();
    $result[] = $pathCache[$req['srs_id']];
        
    $edit_link = '<a href="javascript:openLinkedReqVersionWindow(' . $id . ',' . $req['version_id'] . ')">' . 
                 '<img title="' .$lbl['mixed']['requirement'] . '" src="' . $imgSet['edit'] . '" /></a> ';
      
    $result[] =  '<!-- ' . $title . ' -->' . $edit_link . $title;
          
    // use html comment to sort properly by this columns (extjs)
    $result[] = "<!--{$req['creation_ts']}-->" . 
                localizeTimeStamp($req['creation_ts'],$cfg->datetime) . " ({$req['author']})";
    
    $action = 'on';
    foreach($monitoredSet as $monReqID => $dummy) 
    {
      if($req["id"] == $monReqID) 
      {
        $action = 'off';
        break;
      }
    }
		$result[] = $onClick[$action]['open'] . $req["id"] . 
                $onClick[$action]['close'];
	 
    $rows[] = $result;
  }

   



  // -------------------------------------------------------------------------------------------------- 
  // Construction of EXT-JS table starts here    
  if(($gui->row_qty = count($rows)) > 0 ) 
  {
       
    /**
     * get column header titles for the table
     * 
     * IMPORTANT: 
     * the order of following items in this array has to be
     * the same as row content above!!!
     * 
     * should be:
     * 1. path, 2. title, 3. created_on, 4. monitor
     */
    $columns = array();
    $columns[] = array('title_key' => 'req_spec_short', 'width' => 150);
    $columns[] = array('title_key' => 'title', 'width' => 150);
    $columns[] = array('title_key' => 'created_on', 'width' => 100);
	  $columns[] = array('title_key' => 'monitor', 'width' => 100);

    // create table object, fill it with columns and row data and give it a title
    $matrix = new tlExtTable($columns, $rows, 'tl_table_req_overview');
    $matrix->title = $lbl['mixed']['requirements'];
        
    // group by Req Spec
    $matrix->setGroupByColumnName($lbl['mixed']['req_spec_short']);
        
    // sort by coverage descending if enabled, otherwise by status
    $sort_name = ($cfg->req->expected_coverage_management) ?$lbl['mixed']['th_coverage'] : $lbl['mixed']['status'];
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

  $i2get = array("tproject_id" => array(tlInputParameter::INT_N),
                 "req_id" => array(tlInputParameter::INT_N),
                 "action" => array(tlInputParameter::STRING_N,2,3));

  $args = new stdClass();
  R_PARAMS($i2get,$args);

  if( $args->tproject_id <= 0 )
  {
    throw new Exception("Test project is mandatory", 1);
  } 
   
  $item = $tproject_mgr->get_by_id($args->tproject_id);
  $args->tproject_name = $item['name'];


  $args->req_id = intval($args->req_id);
  $args->userID = $_SESSION['currentUser']->dbID;
 
  return $args;
}


/**
 * initialize GUI
 * 
 * @param stdClass $argsObj reference to user input
 * @return stdClass $gui gui data
 */
function initializeGui(&$argsObj,&$tprojectMgr) 
{
  $gui = new stdClass();
  
  $gui->pageTitle = lang_get('caption_req_monitor_overview');
  $gui->tproject_name = $argsObj->tproject_name;
  $gui->tableSet = null;
  $gui->reqIDSet = $tprojectMgr->get_all_requirement_ids($argsObj->tproject_id);

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

  return $cfg;
}


/**
 *
 */
function getLabels($reqCfg)
{
  $lbl = array();

  $l2get = array('no' => 'No', 'yes' => 'Yes', 
                 'not_aplicable' => null,'never' => null,
                 'req_spec_short' => null,'title' => null, 
                 'version' => null, 'th_coverage' => null,
                 'frozen' => null, 'type'=> null,
                 'status' => null,'th_relations' => null, 
                 'requirements' => null,'number_of_reqs' => null, 
                 'number_of_versions' => null, 
                 'requirement' => null, 'monitor' => null,
                 'version_revision_tag' => null, 
                 'week_short' => 'calendar_week_short',
                 'on2off' => 'on_turn_off', 'off2on' => 'off_turn_on');
  
  $lbl['mixed'] = init_labels($l2get); 
  $lbl['type'] = init_labels($reqCfg->type_labels);
  $lbl['status'] = init_labels($reqCfg->status_labels);

  return $lbl;
}
  
/**
 *
 */  
function buildOnClick($args,$lbl,$imgSet)
{
  $ret = array();
  $ret['off']['open'] = '<!--monitored--><form method="POST" action="lib/requirements/reqMonitorOverview.php' .
                        "?action=off&tproject_id={$args->tproject_id}&req_id=";
  $ret['off']['close'] = '"><input type="image" name="monitor_on" ' .
                         ' title="'. $lbl['on2off']. '"' .
                         ' src="' . $imgSet['on'] . '"/></form>';

  $ret['on']['open'] = str_replace('=off','=on',$ret['off']['open']);
  $ret['on']['close'] = '"><input type="image" name="monitor_off" ' .
                         ' title="'. $lbl['off2on']. '"' .
                         ' src="' . $imgSet['off'] . '"/></form>';
 

  return $ret;
}

/*
 * rights check function for testlinkInitPage()
 */
function checkRights(&$db, &$user)
{
  return $user->hasRight($db,'mgt_view_req');
}

