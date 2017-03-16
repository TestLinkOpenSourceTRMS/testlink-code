<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *
 * @filesource  tcCreatedPerUserOnTestProject.php
 * @package     TestLink
 * @copyright   2005,2011 TestLink community 
 * @author      Francisco Mancardi - francisco.mancardi@gmail.com
 * @link        http://www.teamst.org/index.php
 * @since       1.9.6
 * 
 * @internal important development notice
 * Because we use ext-js grid is important/critic that you consider
 * interaction bewteen:
 *                      exttable.class.php
 *                      ext_extensions.js
 *                      inc_ext_table.tpl
 *
 * in order to avoid 'surprises' with filter behaivour
 *  
 * Generates report of test cases created per user within a project. 
 * 
 * @internal revisions
 *                                                                   breaks filter behaivour
 */
require_once("../../config.inc.php");
require_once("common.php");
require_once('users.inc.php');
require_once('displayMgr.php');
require_once('exttable.class.php');

$smarty = new TLSmarty();
$imgSet = $smarty->getImages();
$templateCfg = templateConfiguration();
$args = init_args($db);
$gui = initializeGui($db,$args,$imgSet);
$tpl = $templateCfg->default_template;

$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $tpl);

/**
* initialize Gui
*/
function initializeGui(&$dbHandler,&$args,$images)
{
  $gui = new stdClass();
  $gui->images = $images;
  $gui->glueChar = config_get('testcase_cfg')->glue_character;
  $gui->tproject_id = $args->tproject_id;
  $gui->tproject_name = $args->tproject_name;
  $gui->warning_msg = '';
  $gui->tableSet = null;
  
  $gui->l18n = init_labels(array('tcversion_indicator' => null,'goto_testspec' => null, 'version' => null, 
                                 'testplan' => null, 'assigned_tc_overview' => null,'testcases_created_per_user' => null,
                                 'design' => null, 'execution' => null, 'execution_history' => null,
                                 'testproject' => null,'generated_by_TestLink_on' => null,'no_records_found' => null, 
                                 'low' => null, 'medium' => null, 'high' => null));
  
  $gui->pageTitle = sprintf($gui->l18n['testcases_created_per_user'],$gui->tproject_name);
  $gui->context = $gui->l18n['testproject'] . ': ' . $args->tproject_name;
  switch($args->do_action)
  {
    case 'uinput':
    default:
      initializeGuiForInput($dbHandler,$args,$gui);
    break;
    
    case 'result':
      initializeGuiForInput($dbHandler,$args,$gui);
      initializeGuiForResult($dbHandler,$args,$gui);
    break;
  }

  return $gui;
}


/**
 *
 */
function initializeGuiForResult(&$dbHandler,$argsObj,&$guiObj)
{
  $rcfg = config_get('results');
  $map_status_code = $rcfg['status_code'];
  $map_code_status = $rcfg['code_status'];
  $map_status_label = $rcfg['status_label'];
  $map_statuscode_css = array();
  foreach($map_code_status as $code => $status) 
  {
    if (isset($map_status_label[$status])) 
    {
      $label = $map_status_label[$status];
      $map_statuscode_css[$code] = array();
      $map_statuscode_css[$code]['translation'] = lang_get($label);
      $map_statuscode_css[$code]['css_class'] = $map_code_status[$code] . '_text';
    }
  }
  
  $options = array();

  // convert starttime to iso format for database usage
  $dateFormat = config_get('date_format');
  $k2l = array('selected_start_date' => 'startTime','selected_end_date' => 'endTime');
  foreach($k2l as $in => $opt)
  {
    if (isset($argsObj->$in) && sizeof($argsObj->$in) > 0) 
    {
      $dd = split_localized_date(current($argsObj->$in), $dateFormat);
      if ($dd != null) 
      {
        $options[$opt] = $dd['year'] . "-" . $dd['month'] . "-" . $dd['day'];
      }
    }
  }
  
  $options['startTime'] .= " " . (isset($argsObj->start_Hour) ? $argsObj->start_Hour : "00") . ":00:00";
  $options['endTime'] .= " " . (isset($argsObj->end_Hour) ? $argsObj->end_Hour : "00") . ":59:59";

  $mgr = new testproject($dbHandler);
  $guiObj->searchDone = 1;
  $guiObj->resultSet = $mgr->getTestCasesCreatedByUser($argsObj->tproject_id,$argsObj->user_id,$options);
  if(!is_null($guiObj->resultSet)) 
  { 
    // test case can exist multiple times, due to versions
    $rows = array();
    foreach ($guiObj->resultSet as $idx => $itemInfo) 
    {
      list($columns, $sortByColumn) = getColumnsDefinition();
      foreach($itemInfo as $tcase)
      {
        $current_row = array();
        $tcase_id = $tcase['tcase_id'];
        $tcversion_id = $tcase['tcversion_id'];
        $current_row[] = htmlspecialchars($tcase['login']);
        $current_row[] = htmlspecialchars($tcase['path']);
        
        // Create linked icons
        $edit_link = "<a href=\"javascript:openTCEditWindow({$tcase_id},{$tcversion_id});\">" .
                     "<img title=\"{$guiObj->l18n['design']}\" src=\"{$guiObj->images['edit']}\" /></a> ";
            
        $current_row[] = "<!-- " . sprintf("%010d", $tcase['external_id']) . " -->" .
                             $edit_link . htmlspecialchars($tcase['external_id']) . " : " . 
                             htmlspecialchars($tcase['tcase_name']) .
                             sprintf($guiObj->l18n['tcversion_indicator'],$tcase['version']);

        $current_row[] = $tcase['importance'];
        $current_row[] = $tcase['creation_ts'];
        $current_row[] = $tcase['modification_ts'];
        $rows[] = $current_row;
      }
    }
      
    // Different table ID for different reports:
    $table_id = "tl_table_tc_created_per_user_";
  
    // Add test plan ID to table ID
    $table_id .= $guiObj->tproject_id;
    
    $matrix = new tlExtTable($columns, $rows, $table_id);
    $matrix->title = $guiObj->l18n['testproject'] . ": " . htmlspecialchars($guiObj->tproject_name);
    // 
    // @TODO how this work ?
    // $matrix->addCustomBehaviour(arg1, arg2)
    // arg1: type that can be user defined, here we use 'importance'.
    // arg2: array with methods
    //       'render' => javascript render method (has to be present on inc_ext_table.tpl).
    //       'filter' => piece of name used on several files
    //                   1. on exttable.class.php is used on buildColumns() to call build{piece}FilterOptions()
    //                   2. on ext_extensions a method named Ext.ux.grid.filter.{piece}Filter 
    //                      has to exists or rendering will fail
    //          
    $matrix->addCustomBehaviour('importance', array('render' => 'importanceRenderer', 'filter' => 'Importance'));
    
    // Default grouping by first column, which is user for overview, build otherwise
    $matrix->setGroupByColumnName(lang_get($columns[0]['title_key']));
    
    // Define toolbar
    $matrix->showToolbar = true;
    $matrix->toolbarExpandCollapseGroupsButton = true;
    $matrix->toolbarShowAllColumnsButton = true;

    // TICKET 5562: Test Cases created per User - toolbar refresh button breaks filter behaivour
    $matrix->toolbarDefaultStateButton = false;
    $matrix->toolbarRefreshButton = false;


    $matrix->setSortByColumnName($sortByColumn);
    $matrix->sortDirection = 'DESC';
    
    $guiObj->tableSet[$guiObj->tproject_id] = $matrix;
  }
}


/**
 *
 */
function initializeGuiForInput(&$dbHandler,$argsObj,&$guiObj)
{
  
	$room = config_get('gui_room');
	$guiObj->str_option_any = sprintf($room,lang_get('any'));
	$guiObj->str_option_none = sprintf($room,lang_get('nobody'));
  $guiObj->warning_msg = '';
  $guiObj->searchDone = 0;
  
	$guiObj->users = new stdClass();
	$guiObj->users->items = getUsersForHtmlOptions($dbHandler, ALL_USERS_FILTER,
									                               array(TL_USER_ANYBODY => $guiObj->str_option_any) );

  $guiObj->user_id = intval($argsObj->user_id);

  $dateFormat = config_get('date_format');
  $cfg = config_get('reportsCfg');
  $now = time();

  if(is_null($argsObj->selected_start_date))
  {
    $guiObj->selected_start_date = strftime($dateFormat, $now - ($cfg->start_date_offset));
    $guiObj->selected_start_time = $cfg->start_time;
    
    $guiObj->selected_end_date = strftime($dateFormat, $now);
    $guiObj->selected_end_time = null;
  }  
  else
  {
    $guiObj->selected_start_date = $argsObj->selected_start_date[0];
    $guiObj->selected_end_date = $argsObj->selected_end_date[0];

    // we are using html_select_time (provided by Smarty Templates)
    // then we need to provide selected in a format she likes.
    $guiObj->selected_start_time = sprintf('%02d:00',$argsObj->start_Hour);
    $guiObj->selected_end_time = sprintf('%02d:59',$argsObj->end_Hour);
  } 


}

/**
 * Gets the arguments used to create the report. 
 * 
 * Some of these arguments are set in the $_REQUEST, and some in $_SESSION. 
 * Having these arguments in hand, the init_args method will use TestLink objects, 
 * such as a Test Project Manager (testproject class) to retrieve other information 
 * that is displayed on the screen (e.g.: project name).
 * 
 * @param $dbHandler handler to TestLink database
 * 
 * @return object of stdClass
 */
function init_args(&$dbHandler)
{
  $args = new stdClass();

  $iParams = array("apikey" => array(tlInputParameter::STRING_N,32,32),
                   "do_action" => array(tlInputParameter::STRING_N,6,6),
                   "tproject_id" => array(tlInputParameter::INT_N),
                   "user_id" => array(tlInputParameter::INT_N),
                   "selected_start_date" => array(tlInputParameter::ARRAY_STRING_N),
                   "selected_end_date" => array(tlInputParameter::ARRAY_STRING_N),
                   "start_Hour" => array(tlInputParameter::INT_N),
                   "end_Hour" => array(tlInputParameter::INT_N));
  
  $_REQUEST=strings_stripSlashes($_REQUEST);
  R_PARAMS($iParams,$args);
  
  if( !is_null($args->apikey) )
  {
    $args->show_only_active = true;
    $cerbero = new stdClass();
    $cerbero->args = new stdClass();
    $cerbero->args->tproject_id = $args->tproject_id;
    $cerbero->args->tplan_id = null;
    $cerbero->args->getAccessAttr = true;
    $cerbero->method = 'checkRights';
    setUpEnvForRemoteAccess($dbHandler,$args->apikey,$cerbero);  
  }
  else
  {
    testlinkInitPage($dbHandler,false,false,"checkRights");  
  }

  if($args->tproject_id < 0)
  {
    throw new Exception('Test project id can not be empty'); 
  }
  $mgr = new testproject($dbHandler);
  $info = $mgr->get_by_id($args->tproject_id);
  $args->tproject_name = $info['name'];
  

  // Sanitize a little bit better
  //$args->selected_end_date = '--></style></scRipt><scRipt>alert(0x008360)</scRipt>';
  sanitizeDates($args);

  return $args;
}

/**
 *
 * @link http://stackoverflow.com/questions/
 *              9293483/regular-expression-help-for-date-validation-dd-mm-yyyy-php
 */
function sanitizeDates(&$obj)
{
  $validLenght = strlen('MM/DD/YYYY');
  $validFormat = '#^[0-9]{1,2}/[0-9]{1,2}/[0-9]{4}$#';

  $p2check = array('selected_end_date','selected_start_date');
  foreach($p2check as $prop)
  {
    if(!is_null($obj->$prop))
    {
      // lenght check
      $val = $obj->$prop;
      $val = $val[0];

      if( strlen($val) != $validLenght)
      {
        $obj->$prop = null;
      }
      else
      {
        // check if format is valid
        if(preg_match($validFormat, $val) === 0) 
        {
          $obj->$prop = null;
        }
      }  
    } 
  } // foreach 
}   



/**
 * Gets the columns definitions used in the report table.
 * 
 * @return array containing columns and sort information
 */
function getColumnsDefinition()
{
  static $labels;
  if( is_null($labels) )
  {
    $lbl2get = array('user' => null, 'testsuite' => null,'testcase' => null,'importance' => null,'status' => null,
                     'version' => null,'title_created' => null,
                     'low' => null,'medium' => null, 'high' => null);
    $labels = init_labels($lbl2get);
  }

  $colDef = array();
  $sortByCol = $labels['testsuite'];
  $colDef[] = array('title_key' => '', 'width' => 80);
  $colDef[] = array('title_key' => 'testsuite', 'width' => 130);
  $colDef[] = array('title_key' => 'testcase', 'width' => 130);

  // render and filter will be managed using customBehaviour (see $matrix->addCustomBehaviour())
  $colDef[] = array('title_key' => 'importance', 'width' => 50, 'type' => 'importance');
    
  $colDef[] = array('title_key' => 'title_created', 'width' => 75);
  $colDef[] = array('title_key' => 'title_last_mod', 'width' => 75);

  return array($colDef, $sortByCol);
}

function checkRights(&$db,&$user)
{
  return $user->hasRight($db,'testplan_metrics');
}
?>
