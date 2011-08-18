<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource	reqSpecCompareRevisions.php
 * @package 	TestLink
 * @author		franciscom
 * @copyright 	2011, TestLink community 
 * @link 		http://www.teamst.org/index.php
 *
 * Compares selected requirements spec revisions with each other.
 *
 * @internal revisions
 */

require_once("../../config.inc.php");
require_once("common.php");
require('../../third_party/diff/diff.php');
require('../../third_party/daisydiff/src/HTMLDiff.php');

$templateCfg = templateConfiguration();
testlinkInitPage($db);
$smarty = new TLSmarty();

$labels = init_labels(array("num_changes" => null,"no_changes" => null, 
					  		"version_short" => null,"diff_details_rev" => null,
					  		"type" => null, "status" => null, "name" => "title",
					  		"doc_id" => null,"revision_short" => null, "revision" => null) );



$itemMgr = new requirement_spec_mgr($db);
$differ = new diff();
$args = init_args();
$gui = initializeGui($db,$args,$labels,$itemMgr);

// if already two revisions are selected, display diff
// else display template with versions to select
if ($args->doCompare) 
{
	// Side By Side
	$sbs = getItemsToCompare($args->left_item_id,$args->right_item_id,$gui->items);
	prepareUserFeedback($db,$gui,$args->req_spec_id,$labels,$sbs);
	
	$gui->attrDiff = getAttrDiff($sbs['left_item'],$sbs['right_item'],$labels);
	
	$cfields = getCFToCompare($sbs,$args->tproject_id,$itemMgr);
	$gui->cfieldsDiff = null;
	if( !is_null($cfields) )
	{
		$gui->cfieldsDiff = getCFDiff($cfields,$itemMgr);
	}

	$gui->diff = array("scope" => array());
	foreach($gui->diff as $key => $val) 
	{
		if ($args->useDaisyDiff) 
		{
			$diff = new HTMLDiffer();
			list($differences, $diffcount) = $diff->htmlDiff($sbs['left_item'][$key], $sbs['right_item'][$key]);
			$gui->diff[$key]["diff"] = $differences;
			$gui->diff[$key]["count"] = $diffcount;
		} else {
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
		$gui->diff[$key]["message"] = sprintf($labels[$msg_key], $key, $additional);
	}

}

$smarty->assign('gui', $gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/**
 * 
 *
 */
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
function getCFToCompare($sides,$tprojectID,&$itemMgr)
{
	$cfields = array('left_side' => array('key' => 'left_item', 'value' => null), 
					 'right_side' => array('key' => 'right_item', 'value' => null));


	$who = array('parent_id' => null, 'item_id' => 0, 'tproject_id' => $tprojectID);
	foreach($cfields as $item_side => $dummy)
	{
		$target_id = $sides[$dummy['key']];
		// $target_id = $target_id['item_id'];
		// $cfields[$item_side]['value'] = $itemMgr->get_linked_cfields(null,$target_id,$tprojectID);
		$who['item_id'] = $target_id['item_id'];
		$cfields[$item_side]['value'] = $itemMgr->get_linked_cfields($who);
	}
	return $cfields;	
}


/**
 * 
 *
 * @internal revisions
 * 20101211 - franciscom -  use show_custom_fields_without_value
 */
function getCFDiff($cfields,&$itemMgr)
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
		$type_code = $itemMgr->cfield_mgr->get_available_types();
		$key2convert = array('lvalue','rvalue');
		
		$formats = array('date' => config_get( 'date_format'));
		$cfg = config_get('gui');
		$cfCfg = config_get('custom_fields');
		foreach($key2loop as $cf_key)
		{
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
					$t_date_format = str_replace("%","",$formats['date']); // must remove %
					foreach($key2convert as $fx)
					{
						if( ($doIt = ($cmp[$cf_key][$fx] != null)) )
						{
							switch($type_code[$cfieldsLeft[$cf_key]['type']])
							{
								case 'datetime':
    	    				            $t_date_format .= " " . $cfg->custom_fields->time_format;
								break ;
							}
						}	                       
						if( $doIt )
						{
						  	$cmp[$cf_key][$fx] = date($t_date_format,$cmp[$cf_key][$fx]);
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
	$_REQUEST=strings_stripSlashes($_REQUEST);
	

	$args = new stdClass();
	$args->req_spec_id = isset($_REQUEST['req_spec_id']) ? $_REQUEST['req_spec_id'] : 0;
	$args->doCompare = isset($_REQUEST['doCompare']) ? true : false;
	$args->left_item_id = isset($_REQUEST['left_item_id']) ? intval($_REQUEST['left_item_id']) : -1;
	$args->right_item_id = isset($_REQUEST['right_item_id']) ? intval($_REQUEST['right_item_id']) :  -1;
	$args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
	$args->useDaisyDiff = (isset($_REQUEST['diff_method']) && ($_REQUEST['diff_method'] == 'htmlCompare')) ? 1 : 0;
	

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
function initializeGui(&$dbHandler,&$argsObj,$lbl,&$itemMgr)
{
	$reqSpecCfg = config_get('req_spec_cfg');
	$guiObj = new stdClass();
    $guiObj->items = $itemMgr->get_history($argsObj->req_spec_id,array('output' => 'array','decode_user' => true));
	
	// Truncate log message
	if( $reqSpecCfg->log_message_len > 0 )
	{	
		$loop2do = count($guiObj->items);
		for($idx=0; $idx < $loop2do; $idx++)
		{
			if( strlen($guiObj->items[$idx]['log_message']) > $reqSpecCfg->log_message_len )
			{
				$guiObj->items[$idx]['log_message'] = substr($guiObj->items[$idx]['log_message'],0,$reqSpecCfg->log_message_len) . '...';
			}
			$guiObj->items[$idx]['log_message'] = htmlspecialchars($guiObj->items[$idx]['log_message']);
		}
	} 
	$guiObj->req_spec_id = $argsObj->req_spec_id;
	$guiObj->doCompare = $argsObj->doCompare;
	$guiObj->context = $argsObj->context;
	$guiObj->version_short = $lbl['version_short'];
	$guiObj->diff = null;
	return $guiObj;
}

/**
 * 
 *
 */
function prepareUserFeedback(&$dbHandler,&$guiObj,$itemID,$labels,$sbs)
{	
	$guiObj->leftID = $labels['revision'] . ':' . $sbs['left_item']['revision'];
	$guiObj->rightID = $labels['revision'] . ':' . $sbs['right_item']['revision'];
	$guiObj->subtitle = sprintf($labels['diff_details_rev'], 
							 	$sbs['left_item']['revision'],$sbs['left_item']['revision'],  
							 	$sbs['right_item']['revision'],$sbs['right_item']['revision']);

	
}



/**
 * 
 *
 */
function getAttrDiff($leftSide,$rightSide,$labels)
{
	$req_spec_cfg = config_get('req_spec_cfg');
	
	// attribute => label definition on TL configuration (just if NOT NULL)
	// order in this array will drive display order
	// $key2loop = array('doc_id' => null,'status' => 'status_labels','type' => 'type_labels');
	$key2loop = array('doc_id' => null,'name' => null,'type' => 'type_labels');
	foreach($key2loop as $fkey => $lkey)
	{
		// Need to decode
		$cmp[$fkey] = array('label' => htmlspecialchars($labels[$fkey]),
		                    'lvalue' => $leftSide[$fkey],'rvalue' => $rightSide[$fkey],
		                    'changed' => $leftSide[$fkey] != $rightSide[$fkey]);
		             
		if( !is_null($lkey) )
		{
			$decode = $req_spec_cfg->$lkey;
			$cmp[$fkey]['lvalue'] = lang_get($decode[$cmp[$fkey]['lvalue']]);
			$cmp[$fkey]['rvalue'] = lang_get($decode[$cmp[$fkey]['rvalue']]);
		}                   
	}		
	return $cmp;	
}
?>