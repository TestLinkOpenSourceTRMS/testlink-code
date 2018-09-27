<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filename  reqPrint.php
 * @package   TestLink
 * @author    Francisco Mancardi - francisco.mancardi@gmail.com
 * @copyright 2005-2018, TestLink community
 * @link      http://www.testlink.org/
 *
 * create printer friendly information for ONE requirement
 *
 */

require_once("../../config.inc.php");
require_once("../../cfg/reports.cfg.php"); 
require_once("print.inc.php"); 
require_once("common.php");
testlinkInitPage($db);
$templateCfg = templateConfiguration();

$tree_mgr = new tree($db);
$args = init_args();
$node = $tree_mgr->get_node_hierarchy_info($args->req_id);
$node['version_id'] = $args->req_version_id;
$node['revision'] = $args->req_revision;

$gui = new stdClass();
$gui->object_name='';
$gui->object_name = $node['name'];
$gui->page_title = sprintf(lang_get('print_requirement'),$node['name']);
$gui->tproject_name=$args->tproject_name;
$gui->tproject_id=$args->tproject_id;
$gui->req_id=$args->req_id; 
$gui->req_version_id=$args->req_version_id;
$gui->req_revision=$args->req_revision;


// Struture defined in printDocument.php	
$options = array('toc' => 0,              
                 'req_linked_tcs' => 1, 'req_cf' => 1,
                 'req_scope' => 1, 'req_relations' => 1, 'req_coverage' => 1,
                 'req_status' => 1, 'req_type' => 1,'req_author'=> 1,
                 'displayVersion' => 1, 'displayDates' => 1, 
                 'displayLastEdit' => 1, 'docType' => SINGLE_REQ);

$text2print = '';
$text2print .= 
  renderHTMLHeader($gui->page_title,$_SESSION['basehref'],SINGLE_REQ);

$text2print .= 
  renderReqForPrinting($db,$node,$options,null,0,$args->tproject_id);

echo $text2print;

/*
  function: init_args

  args:
  
  returns: 

*/
function init_args() {
  $_REQUEST = strings_stripSlashes($_REQUEST);

  $args = new stdClass();
  $args->req_id = isset($_REQUEST['req_id']) ? intval($_REQUEST['req_id']) : 0;
  $args->req_version_id = isset($_REQUEST['req_version_id']) ? intval($_REQUEST['req_version_id']) : 0;
  $args->req_revision = isset($_REQUEST['req_revision']) ? intval($_REQUEST['req_revision']) : 0;

  $args->tproject_id = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0;
  $args->tproject_name = $_SESSION['testprojectName'];

  return $args;
}

