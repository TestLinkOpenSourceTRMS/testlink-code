<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @package 		TestLink
 * @author 			asimon
 * @copyright 	2005-2013, TestLink community 
 * @filesource	tcCompareVersions.php
 * @link 				http://www.teamst.org/index.php
 *
 * Compares selected testcase versions with each other.
 *
 * @internal revisions
 * @since 1.9.6
 *
 */

require_once("../../config.inc.php");
require_once("common.php");
require('../../third_party/diff/diff.php');
require('../../third_party/daisydiff/src/HTMLDiff.php');

$templateCfg = templateConfiguration();
testlinkInitPage($db);
$smarty = new TLSmarty();

$args = init_args();
$gui = initializeGUI($db,$args);


//if already two versions are selected, display diff
//else display template with versions to select
if ($args->compare_selected_versions) 
{
  $diffEngine = $args->use_daisydiff ? new HTMLDiffer() : new diff();
  $attributes = buildDiff($gui->tc_versions,$args);
  foreach($attributes as $key => $val) 
  {
	$gui->diff[$key]['count'] = 0;
    $gui->diff[$key]['heading'] = lang_get($key);
    
    $val['left'] = isset($val['left']) ? $val['left'] : '';
    $val['right'] = isset($val['right']) ? $val['right'] : ''; 
		
	if($args->use_daisydiff)
    {
		if ($gui->tcType == 'none')
		{
			list($gui->diff[$key]['diff'], $gui->diff[$key]['count']) = $diffEngine->htmlDiff(nl2br($val['left']), nl2br($val['right']));
		}
		else
		{
			list($gui->diff[$key]['diff'], $gui->diff[$key]['count']) = $diffEngine->htmlDiff($val['left'], $val['right']);
		}
	}
    else
    {
			// insert line endings so diff is better readable and makes sense (not everything in one line)
			// then transform into array with \n as separator => diffEngine needs that.
      //
			$gui->diff[$key]['left'] = explode("\n", str_replace("</p>", "</p>\n", $val['left']));
			$gui->diff[$key]['right'] = explode("\n", str_replace("</p>", "</p>\n", $val['right']));
		
		  $gui->diff[$key]['diff'] = $diffEngine->inline($gui->diff[$key]['left'], $gui->leftID, 
			                                               $gui->diff[$key]['right'], $gui->rightID,$args->context);
			$gui->diff[$key]['count'] = count($diffEngine->changes);
		}
		
		// are there any changes? then display! if not, nothing to show here
		$msgKey = ($gui->diff[$key]['count'] > 0) ? 'num_changes' : 'no_changes';
    $gui->diff[$key]['message'] = sprintf($gui->labels[$msgKey], $gui->diff[$key]['heading'],
                                          $gui->diff[$key]['count']);
	}
} 
$smarty = new TLSmarty();
$smarty->assign('gui', $gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


function init_args()
{
	$args = new stdClass();
  $args->use_daisydiff = isset($_REQUEST['use_html_comp']);
	

  $args->tcase_id = isset($_REQUEST['testcase_id']) ? $_REQUEST['testcase_id'] : 0;

  $key2set = array('compare_selected_versions' => 0,'version_left' => '','version_right' => '');
  foreach($key2set as $tk => $value)
  {
    $args->$tk = isset($_REQUEST[$tk]) ? $_REQUEST[$tk] : $value;
  } 
	
	
	if (isset($_REQUEST['context_show_all'])) 
  {
		$args->context = null;
	} 
  else 
  {
	  $diffEngineCfg = config_get("diffEngine");
  	$args->context = (isset($_REQUEST['context']) && is_numeric($_REQUEST['context'])) ? 
											$_REQUEST['context'] : $diffEngineCfg->context;	
	}
	
	return $args;
}

function initializeGUI(&$dbHandler,$argsObj)
{
  $gui = new stdClass();

  $gui->tc_id = $argsObj->tcase_id;
  $gui->compare_selected_versions = $argsObj->compare_selected_versions;
  $gui->context = $argsObj->context;

  $tcaseMgr = new testcase($dbHandler); 
  $gui->tc_versions = $tcaseMgr->get_by_id($argsObj->tcase_id);
  $gui->tcaseName = $gui->tc_versions[0]['name'];
  unset($tcaseMgr);

  $lblkeys = array('num_changes' => null,'no_changes' => null, 'version_short' => null,'diff_subtitle_tc' => null);
  $gui->labels = init_labels($lblkeys);
  $gui->version_short = $gui->labels['version_short'];


  $gui->subtitle = sprintf($gui->labels['diff_subtitle_tc'], 
                           $argsObj->version_left,$argsObj->version_left, 
                           $argsObj->version_right,$argsObj->version_right, $gui->tcaseName);

  $gui->leftID = "v{$argsObj->version_left}";
  $gui->rightID = "v{$argsObj->version_right}";
  $tcCfg = getWebEditorCfg('design');
  $gui->tcType = $tcCfg['type'];
  return $gui;
}

function buildDiff($items,$argsObj)
{

  $panel = array('left','right');

  $attrKeys = array();  
  $attrKeys['simple'] = array('summary','preconditions');
  $attrKeys['complex'] = array('steps' => 'actions', 'expected_results' => 'expected_results');
  $dummy = array_merge($attrKeys['simple'],array_keys($attrKeys['complex'])); 
  foreach($dummy as $gx)
  {
    foreach($panel as $side)
    {
      $diff[$gx][$side] = null;
    }  
  } 

  foreach($items as $tcase) 
  {   
    foreach($panel as $side)
    {
      $tk = 'version_' . $side;
      if ($tcase['version'] == $argsObj->$tk) 
      {
        foreach($attrKeys['simple'] as $attr)
        {
          $diff[$attr][$side] = $tcase[$attr];
        }

        // Steps & Expected results, have evolved from ONE FIXED SET of two simple fields
        // to a dynamic SET (array?) of two simple fields.
        // Our choice in order to find differences, is to transform the dynamic set
        // again on a ONE FIXED SET.
        // That's why we need to do this kind of special processing.
        if(is_array($tcase['steps']))     // some magic, I'm Sorry 
        {
          foreach($tcase['steps'] as $step) 
          {
            foreach($attrKeys['complex'] as $attr => $key2read)
            {
              $diff[$attr][$side] .= str_replace("</p>", "</p>\n", $step[$key2read])."<br />"."<br />"; // insert lines between each steps and between each expected results so diff is better readable and makes sense (not everything in one line)
            }    
          }
        }  
      }
    }  // foreach panel 
  }
  return $diff;
}
?>