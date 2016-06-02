<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @package   TestLink
 * @author      asimon
 * @copyright   2005-2012, TestLink community 
 * @filesource  reqCompareVersions.php
 * @link    http://www.testlink.org/
 *
 * Compares selected requirements versions with each other.
 *
 * @internal revisions
 * @since 1.9.15
 */

require_once("../../config.inc.php");
require_once("common.php");
require('../../third_party/diff/diff.php');
require('../../third_party/daisydiff/src/HTMLDiff.php');

$templateCfg = templateConfiguration();
testlinkInitPage($db);
$smarty = new TLSmarty();

$labels = init_labels(array("num_changes" => null,"no_changes" => null, 
                "diff_subtitle_req" => null, "version_short" => null,
                "diff_details_req" => null,"type" => null, "status" => null,
                "expected_coverage" => null,
                "revision_short" => null, "version_revision" => null) );



$reqMgr = new requirement_mgr($db);
$differ = new diff();
$args = init_args();
$gui = initializeGui($db,$args,$labels,$reqMgr);


// if already two versions are selected, display diff
// else display template with versions to select
if ($args->compare_selected_versions) 
{
  // Side By Side
  $sbs = getItemsToCompare($args->left_item_id,$args->right_item_id,$gui->items);
  prepareUserFeedback($db,$gui,$args->req_id,$labels,$sbs);
  
  $gui->attrDiff = getAttrDiff($sbs['left_item'],$sbs['right_item'],$labels);
  
  $cfields = getCFToCompare($sbs,$args->tproject_id,$reqMgr);
  $gui->cfieldsDiff = null;
  if( !is_null($cfields) )
  {
    $gui->cfieldsDiff = getCFDiff($cfields,$reqMgr);
  }

  $gui->diff = array("scope" => array());
  foreach($gui->diff as $key => $val) 
  {
    if ($args->use_daisydiff) 
    {
	  // using daisydiff as diffing engine
	  $diff = new HTMLDiffer();
	  if ($gui->reqType == 'none'){
		list($differences, $diffcount) = $diff->htmlDiff(nl2br($sbs['left_item'][$key]), nl2br($sbs['right_item'][$key]));
	  }
	  else{
		list($differences, $diffcount) = $diff->htmlDiff($sbs['left_item'][$key], $sbs['right_item'][$key]);
	  }
	  $gui->diff[$key]["diff"] = $differences;
	  $gui->diff[$key]["count"] = $diffcount;
	}
    else
    {
      // insert line endings so diff is better readable and makes sense (not everything in one line)
      // then cast to array with \n as separating character, differ needs that
	  $gui->diff[$key]["left"] = explode("\n", str_replace("</p>", "</p>\n", $sbs['left_item'][$key]));
      $gui->diff[$key]["right"] = explode("\n", str_replace("</p>", "</p>\n", $sbs['right_item'][$key]));
	  $gui->diff[$key]["diff"] = $differ->inline($gui->diff[$key]["left"], $gui->leftID, 
                                                  $gui->diff[$key]["right"], $gui->rightID,$args->context);
      $gui->diff[$key]["count"] = count($differ->changes);
    }
    
    $gui->diff[$key]["heading"] = lang_get($key);
  
    // are there any changes? then display! if not, nothing to show here
    $additional = '';
    $msg_key = "no_changes";
    if ($gui->diff[$key]["count"] > 0) 
    {
      $msg_key = "num_changes";
      $additional = $gui->diff[$key]["count"];
    }
    $gui->diff[$key]["message"] = (sprintf($labels[$msg_key], $key, $additional));
  }
}

$smarty->assign('gui', $gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/**
 * 
 *
 */
function getBareBonesReq($dbHandler,$reqID)
{
  $debugMsg = ' Function: ' . __FUNCTION__;
  $tables = tlObjectWithDB::getDBTables(array('requirements','nodes_hierarchy'));
  $sql =  " /* $debugMsg */ SELECT REQ.req_doc_id, NH_REQ.name " .
      " FROM {$tables['requirements']} REQ " .
      " JOIN {$tables['nodes_hierarchy']} NH_REQ  ON  NH_REQ.id = REQ.id " .
      " WHERE REQ.id = " . intval($reqID);
      
  $bones = $dbHandler->get_recordset($sql);   

  return $bones[0];
}

/**
 * 
 *
 */
function getItemsToCompare($leftSideID,$rightSideID,&$itemSet)
{

  $ret = array();
  foreach($itemSet as $item) 
  {
    if ($item['item_id'] == $leftSideID) 
    {
      $ret['left_item'] = $item;
    }
    if ($item['item_id'] == $rightSideID) 
    {
      $ret['right_item'] = $item;
    }
    
    if( count($ret) == 2 )
    {
      break;
    }
  }
  return $ret;
}


/**
 * 
 *
 */
function getCFToCompare($sides,$tprojectID,&$reqMgr)
{
  $cfields = array('left_side' => array('key' => 'left_item', 'value' => null), 
           'right_side' => array('key' => 'right_item', 'value' => null));

  foreach($cfields as $item_side => $dummy)
  {
    $target_id = $sides[$dummy['key']];
    $target_id = $target_id['item_id'];
    $cfields[$item_side]['value'] = $reqMgr->get_linked_cfields(null,$target_id,$tprojectID);
  }
  return $cfields;  
}


/**
 * 
 */
function getCFDiff($cfields,&$reqMgr)
{
  $cmp = null;
  
  // Development Note
  // All versions + revisions (i.e. child items) have the same qty of linked CF
  // => both arrays will have same size()
  //
  // This is because to get cfields we look only to CF enabled for node type.
  $cfieldsLeft = $cfields['left_side']['value'];
  $cfieldsRight = $cfields['right_side']['value'];

  if( !is_null($cfieldsLeft) )
  {
    $key2loop = array_keys($cfieldsLeft);
    $cmp = array();
    $type_code = $reqMgr->cfield_mgr->get_available_types();
    $key2convert = array('lvalue','rvalue');
    

    $cfg = config_get('gui');
    $cfCfg = config_get('custom_fields');    

    $formats = array('date' => config_get( 'date_format'));
    $t_date_format = str_replace("%","",$formats['date']); // must remove %
    $t_datetime_format = $t_date_format . ' ' . $cfg->custom_fields->time_format;
 
    foreach($key2loop as $cf_key)
    {
      $dt_format = $t_date_format;
      
      // $cfg->show_custom_fields_without_value 
      // false => At least one value has to be <> NULL to include on comparsion results
      // 
      if( $cfCfg->show_custom_fields_without_value == true ||
          ($cfCfg->show_custom_fields_without_value == false &&
           ( (!is_null($cfieldsRight) && !is_null($cfieldsRight[$cf_key]['value'])) ||
             (!is_null($cfieldsLeft) && !is_null($cfieldsLeft[$cf_key]['value'])) )
            ) 
          )    
      {   
        $cmp[$cf_key] = array('label' => htmlspecialchars($cfieldsLeft[$cf_key]['label']),
                              'lvalue' => $cfieldsLeft[$cf_key]['value'],
                              'rvalue' => !is_null($cfieldsRight) ? $cfieldsRight[$cf_key]['value'] : null,
                              'changed' => $cfieldsLeft[$cf_key]['value'] != $cfieldsRight[$cf_key]['value']);
 
        if($type_code[$cfieldsLeft[$cf_key]['type']] == 'date' ||
           $type_code[$cfieldsLeft[$cf_key]['type']] == 'datetime') 
        {
          foreach($key2convert as $fx)
          {
            if( ($doIt = ($cmp[$cf_key][$fx] != null)) )
            {
              switch($type_code[$cfieldsLeft[$cf_key]['type']])
              {
                case 'datetime':
                  $dt_format = $t_datetime_format;
                break ;
              }
            }                        
            if( $doIt )
            {
              $cmp[$cf_key][$fx] = date($dt_format,$cmp[$cf_key][$fx]);
            }
          }
        } 
      } // mega if
    }  // foraeach    
  }
  return count($cmp) > 0 ? $cmp : null; 
}



/**
 * 
 *
 */
function init_args()
{
  $args = new stdClass();

  $args->req_id = isset($_REQUEST['requirement_id']) ? $_REQUEST['requirement_id'] : 0;
  $args->compare_selected_versions = isset($_REQUEST['compare_selected_versions']);
  $args->left_item_id = isset($_REQUEST['left_item_id']) ? intval($_REQUEST['left_item_id']) : -1;
  $args->right_item_id = isset($_REQUEST['right_item_id']) ? intval($_REQUEST['right_item_id']) :  -1;
    $args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;

  $args->use_daisydiff = isset($_REQUEST['use_html_comp']);

  $diffEngineCfg = config_get("diffEngine");
  $args->context = null;
  if( !isset($_REQUEST['context_show_all'])) 
  {
    $args->context = (isset($_REQUEST['context']) && is_numeric($_REQUEST['context'])) ? $_REQUEST['context'] : $diffEngineCfg->context;
  }
  
  return $args;
}

/**
 * 
 *
 */
function initializeGui(&$dbHandler,&$argsObj,$lbl,&$reqMgr)
{
  $reqCfg = config_get('req_cfg');
  $guiObj = new stdClass();
  $guiObj->items = $reqMgr->get_history($argsObj->req_id,array('output' => 'array','decode_user' => true));
  
  
  // Truncate log message
  if( $reqCfg->log_message_len > 0 )
  { 
    $loop2do = count($guiObj->items);
    for($idx=0; $idx < $loop2do; $idx++)
    {
      if( strlen($guiObj->items[$idx]['log_message']) > $reqCfg->log_message_len )
      {
        $guiObj->items[$idx]['log_message'] = substr($guiObj->items[$idx]['log_message'],0,$reqCfg->log_message_len) . '...';
      }
      $guiObj->items[$idx]['log_message'] = htmlspecialchars($guiObj->items[$idx]['log_message']);
    }
  } 
  $guiObj->req_id = $argsObj->req_id;
  $guiObj->compare_selected_versions = $argsObj->compare_selected_versions;
  $guiObj->context = $argsObj->context;
  $guiObj->version_short = $lbl['version_short'];
  $guiObj->diff = null;
  $reqCfg = getWebEditorCfg('requirement');
  $guiObj->reqType = $reqCfg['type'];
  
  return $guiObj;
}

/**
 * 
 *
 */
function prepareUserFeedback(&$dbHandler,&$guiObj,$reqID,$labels,$sbs)
{ 
  $guiObj->leftID = sprintf($labels['version_revision'],$sbs['left_item']['version'],$sbs['left_item']['revision']);
  $guiObj->rightID = sprintf($labels['version_revision'],$sbs['right_item']['version'],$sbs['right_item']['revision']);
  $mini_me = getBareBonesReq($dbHandler,$reqID);
  $guiObj->subtitle = sprintf($labels['diff_details_req'], 
                $sbs['left_item']['version'],$sbs['left_item']['revision'],
                $sbs['left_item']['version'],$sbs['left_item']['revision'],  
                $sbs['right_item']['version'],$sbs['right_item']['revision'],
                $sbs['right_item']['version'],$sbs['right_item']['revision'],  
                            $mini_me['req_doc_id'] . config_get('gui_title_separator_1') . $mini_me['name']);
}

/**
 * 
 *
 */
function getAttrDiff($leftSide,$rightSide,$labels)
{
  $req_cfg = config_get('req_cfg'); 
  $key2loop = array('status' => 'status_labels','type' => 'type_labels','expected_coverage' => null);
  foreach($key2loop as $fkey => $lkey)
  {
    // Need to decode
    $cmp[$fkey] = array('label' => htmlspecialchars($labels[$fkey]),
                       'lvalue' => $leftSide[$fkey],'rvalue' => $rightSide[$fkey],
                       'changed' => $leftSide[$fkey] != $rightSide[$fkey]);
                 
    if( !is_null($lkey) )
    {
      $decode = $req_cfg->$lkey;
      
      $cmp[$fkey]['lvalue'] = lang_get($decode[$cmp[$fkey]['lvalue']]);
      $cmp[$fkey]['rvalue'] = lang_get($decode[$cmp[$fkey]['rvalue']]);
    }                   
  }   
  return $cmp;  
}