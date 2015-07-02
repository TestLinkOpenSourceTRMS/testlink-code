<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource  eventviewer.php
 *
 * @internal revisions
 *
**/
require_once("../../config.inc.php");
require_once("common.php");
require_once("users.inc.php");
require_once('exttable.class.php');
testlinkInitPage($db,false,false,"checkRights");
$date_format_cfg = config_get('date_format');

$templateCfg = templateConfiguration();
$args = init_args();
$gui = initializeGui($db,$args);
$filters = getFilters();
$show_icon = TL_THEME_IMG_DIR . "plus.gif";
$charset = config_get('charset');

switch($args->doAction)
{
  case 'clear':
    // Ability to delete events from selected class from event logs 
    $g_tlLogger->deleteEventsFor($args->logLevel);
    if( is_null($args->logLevel) )
    {
      logAuditEvent(TLS("audit_all_events_deleted",$args->currentUser->login),"DELETE",null,"events");
    }
    else
    {
      $logLevelVerbose = null;
      foreach( $args->logLevel as $code )
      {
        $logLevelVerbose[] = $gui->logLevels[$code];  
      }
      $logLevelVerbose = implode(',',$logLevelVerbose);
      logAuditEvent(TLS("audit_events_with_level_deleted",$args->currentUser->login,$logLevelVerbose),"DELETE",null,"events");
    }
      
    // reset filters after clearing events
    $args->logLevel = null;
    $gui->selectedLogLevels = array();
    $gui->selectedTesters = array();
    $gui->startDate = null;
    $gui->endDate = null;
  break;
    
  case 'filter':
  default:
    $filters = getFilters($args,$date_format_cfg);
  break;
}

$gui->events = $g_tlLogger->getEventsFor($args->logLevel,$args->object_id ? $args->object_id : null,
                                         $args->object_type ? $args->object_type : null,null,500,$filters->startTime,
                                         $filters->endTime,$filters->users);

if (count($gui->events) > 0) 
{
  $table = buildExtTable($gui, $show_icon, $charset);
  if (!is_null($table)) 
  {
    $gui->tableSet[] = $table;
  }
} 
else 
{
  $gui->warning_msg = lang_get("no_events");
}

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);

/**
 * 
 * @return object returns the arguments of the page
 */
function init_args()
{
  $iParams = array("startDate" => array("POST",tlInputParameter::STRING_N,0,10),
                   "endDate" => array("POST",tlInputParameter::STRING_N,0,10),
                   "doAction" => array("POST",tlInputParameter::STRING_N,0,100),
                   "object_id" => array("REQUEST",tlInputParameter::INT_N),
                   "object_type" => array("REQUEST",tlInputParameter::STRING_N,0,15),
                   "logLevel" => array("POST",tlInputParameter::ARRAY_INT),
                   "testers" => array("REQUEST",tlInputParameter::ARRAY_INT));

  $args = new stdClass();
  I_PARAMS($iParams,$args);
  $args->currentUser = $_SESSION['currentUser'];
  return $args;
}

/**
 * Checks the user rights for viewing the page
 * 
 * @param $db resource the database connection handle
 * @param $user tlUser the object of the current user
 *
 * @return boolean return true if the page can be viewed, false if not
 */
function checkRights(&$db,&$user,$action)
{
  $checkStatus = $user->hasRight($db,"mgt_view_events");
  if( !$checkStatus )
  {
    $iParams = array("doAction" => array(tlInputParameter::STRING_N,0,100));
    $rParams = R_PARAMS($iParams);
    if ($rParams["doAction"] == 'clear')
    {
      $checkStatus = $user->hasRight($db,'events_mgt');
    }
  }
  return $checkStatus;
}


/**
 * 
 *
 */
function initializeGui(&$dbHandler,&$argsObj)
{
  $gui = new stdClass();
  $gui->logLevels = array(tlLogger::AUDIT => lang_get("log_level_AUDIT"),
                          tlLogger::ERROR => lang_get("log_level_ERROR"),
                          tlLogger::WARNING => lang_get("log_level_WARNING"),
                          tlLogger::INFO => lang_get("log_level_INFO"),
                          tlLogger::DEBUG => lang_get("log_level_DEBUG"),
                          tlLogger::L18N => lang_get("log_level_L18N"));
  
  $gui->allusers = tlUser::getAll($dbHandler);   // THIS IS AN OVERKILL because get ALL USER OBJECTS
  $gui->testers = getUsersForHtmlOptions($dbHandler,null,null,true,$gui->allusers);
  $gui->users = getUsersForHtmlOptions($dbHandler);
  $gui->users[0] = false;

  $gui->startDate=$argsObj->startDate;
  $gui->endDate=$argsObj->endDate;
  $gui->object_id=$argsObj->object_id;
  $gui->object_type=$argsObj->object_type;

  $gui->selectedLogLevels = ($argsObj->logLevel ? array_values($argsObj->logLevel) : array());
  $gui->selectedTesters = ($argsObj->testers ? array_values($argsObj->testers) : array());

  $gui->canDelete = $argsObj->currentUser->hasRight($dbHandler,"events_mgt");
    
  $gui->warning_msg = "";
  $gui->tableSet = null;
    
  return $gui;
}


/**
 * 
 *
 */
function getFilters(&$argsObj=null,$dateFormat=null)
{
  $filters = new stdClass();
  $filters->startTime = null;
  $filters->endTime = null;
  $filters->users = null;

  if( !is_null($argsObj) )
  {
    if ($argsObj->startDate != "")
    {
      $date_array = split_localized_date($argsObj->startDate, $dateFormat);
      if ($date_array != null) {
        // convert localized date to date that strtotime understands -> en_US: m/d/Y: 
        $filters->startTime = strToTime($date_array['month'] . "/" . $date_array['day']. "/" .$date_array['year']);
      }
      if ($filters->startTime == "")
      {
        $filters->startTime = null;
      }
    }
    
    if ($argsObj->endDate != "")
    {
      $date_array = split_localized_date($argsObj->endDate, $dateFormat);
      if ($date_array != null) {
        // convert localized date to date that strtotime understands -> en_US: m/d/Y:
        // end time must end at selected day at 23:59:59
        $filters->endTime = strToTime($date_array['month'] . "/" . $date_array['day']. "/" . 
                                      $date_array['year'] . ", 23:59:59");
      }
      if (!$filters->endTime)
      {
        $filters->endTime = null;
      }
    }
      
    if (!is_null($argsObj->testers))
    {
        $filters->users = implode(",",$argsObj->testers);
        if (!$filters->users)
        {
        $filters->users = null;
      } 
    }
  }
  
  return $filters;
}

/**
 * 
 *
 */
function buildExtTable($gui,$show_icon,$charset)
{
  $table = null;
  if(count($gui->events) > 0) 
  {
    $columns = array();
    $columns[] = array('title_key' => 'th_timestamp', 'width' => 15);
    $columns[] = array('title_key' => 'th_loglevel', 'width' => 15);
    $columns[] = array('title_key' => 'th_user', 'width' => 15);
    $columns[] = array('title_key' => 'th_event_description','type' => 'text');
    $columns[] = array('title_key' => 'th_transaction', 'width' => 15, 'hidden' => 'true');
  
    // Extract the relevant data and build a matrix
    $matrixData = array();

    foreach ($gui->events as $event_key => $event)
    {
      $transactionID = $event->transactionID;
      
      $rowData = array();

      // necessary as localize_dateOrTimeStamp expects 2nd parameter to pass by reference
      $dummy = null; 
      // use html comment to sort properly by timestamp
      $rowData[] = "<!--{$event->timestamp}-->" .
                   localize_dateOrTimeStamp(null, $dummy, 'timestamp_format',$event->timestamp);
                   
      $rowData[] = $event->getlogLevel();
      
      if (isset($event->userID) && $event->userID != false && isset($gui->users[$event->userID])) {
        $rowData[] = $gui->users[$event->userID];
      } else {
        $rowData[] = lang_get("not_aplicable");
      }
      $description = htmlentities($event->description, ENT_QUOTES, $charset);
      $rowData[] = "<!--" . $description . "-->" .
                   "<a onClick=\"showEventDetails({$event->dbID});\" style=\"cursor: hand; cursor: pointer;\">" . 
                   "<img title=\"" . lang_get("show_eventdetails") ."\" src=\"{$show_icon}\" /> </a>" .
                   $description;
                   
      $rowData[] = $event->transactionID;
      
      $matrixData[] = $rowData;
    }
    
    $table = new tlExtTable($columns, $matrixData, 'tl_table_eventviewer');

    $table->addCustomBehaviour('text', array('render' => 'columnWrap'));
    
    $table->setGroupByColumnName(lang_get('th_loglevel'));
    $table->setSortByColumnName(lang_get('th_timestamp'));
    $table->sortDirection = 'DESC';
    
    $table->showToolbar = true;
    $table->toolbarExpandCollapseGroupsButton = true;
    $table->toolbarShowAllColumnsButton = true;
  }
  return($table);
}
