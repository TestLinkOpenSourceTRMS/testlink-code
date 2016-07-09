<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @internal  filename: tcPrint.php 
 * @package   TestLink
 * @author    Francisco Mancardi - francisco.mancardi@gmail.com
 * @copyright 2005-2014, TestLink community 
 * @link      http://www.testlink.org
 *
 *
 * @internal revisions
 * @since 1.9.13
 */

require_once("../../config.inc.php");
require_once("../../cfg/reports.cfg.php"); 
require_once("print.inc.php"); 
require_once("common.php");
testlinkInitPage($db);
$templateCfg = templateConfiguration();

$tree_mgr = new tree($db);
$args = init_args();
$node = $tree_mgr->get_node_hierarchy_info($args->tcase_id);
$node['tcversion_id'] = $args->tcversion_id;
$gui = initializeGui($args,$node);


// Struture defined in printDocument.php  
$printingOptions = array('toc' => 0,'body' => 1,'summary' => 1, 'header' => 0,'headerNumbering' => 0,
                         'passfail' => 0, 'author' => 1, 'notes' => 0, 'requirement' => 1, 'keyword' => 1, 
                         'cfields' => 1, 'displayVersion' => 1, 'displayDates' => 1, 'docType' => SINGLE_TESTCASE,
                         'importance' => 1);

$level = 0;
$tplanID = 0;
$prefix = null;
$text2print = '';
$text2print .= renderHTMLHeader($gui->page_title,$_SESSION['basehref'],
                                SINGLE_TESTCASE,array('gui/javascript/testlink_library.js'));

$env = new stdClass();
$env->base_href = $_SESSION['basehref'];
$env->reportType = $printingOptions['docType'];

$text2print .= renderTestCaseForPrinting($db,$node,$printingOptions,$env,
                                         array('level' => $level,'tplan_id' => $tplanID,
                                               'tproject_id' => $args->tproject_id,'prefix' => $prefix),$level);

echo $text2print;

/*
  function: init_args

  args:
  
  returns: 

*/
function init_args()
{
  $_REQUEST = strings_stripSlashes($_REQUEST);

  $args = new stdClass();
  $args->tcase_id = intval(isset($_REQUEST['testcase_id']) ? intval($_REQUEST['testcase_id']) : 0);
  $args->tcversion_id = intval(isset($_REQUEST['tcversion_id']) ? intval($_REQUEST['tcversion_id']) : 0);
  $args->tproject_id = intval(isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0);

  $args->tproject_name = $_SESSION['testprojectName'];
  $args->goback_url=isset($_REQUEST['goback_url']) ? $_REQUEST['goback_url'] : null;


  $ofd = array('HTML' => lang_get('format_html'),'ODT' => lang_get('format_odt'), 
               'MSWORD' => lang_get('format_msword'));
  $args->outputFormat = isset($_REQUEST['outputFormat']) ? $_REQUEST['outputFormat'] : null;
  $args->outputFormat = isset($ofd[$args->outputFormat]) ? $ofd[$args->outputFormat] : null;
  
  $args->outputFormatDomain = array('NONE' => '') + $ofd;
  return $args;
}

/**
 *
 */
function initializeGui(&$argsObj,&$node)
{
  $guiObj = new stdClass();
  $guiObj->outputFormatDomain = $argsObj->outputFormatDomain;
  $guiObj->object_name='';
  $guiObj->goback_url = !is_null($argsObj->goback_url) ? $argsObj->goback_url : ''; 
  $guiObj->object_name = $node['name'];
  $guiObj->page_title = sprintf(lang_get('print_testcase'),$node['name']);
  $guiObj->tproject_name=$argsObj->tproject_name;
  $guiObj->tproject_id=$argsObj->tproject_id;
  $guiObj->tcase_id=$argsObj->tcase_id; 
  $guiObj->tcversion_id=$argsObj->tcversion_id;

  return $guiObj;
}

