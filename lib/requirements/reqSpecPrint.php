<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource	reqSpecPrint.php
 * @package		TestLink
 * @author		Francisco Mancardi - francisco.mancardi@gmail.com
 * @copyright 	2005-2011, TestLink community
 * @link 		http://www.teamst.org/index.php
 *
 * create printer friendly information for ONE requirement
 *
 * @internal revisions:
 * 20110319 - franciscom - BUGID 4321: Requirement Spec - add option to print single Req Spec
 */

require_once("../../config.inc.php");
require_once("../../cfg/reports.cfg.php"); 
require_once("print.inc.php"); 
require_once("common.php");
testlinkInitPage($db);
$templateCfg = templateConfiguration();
$req_cfg = config_get('req_cfg');

$tree_mgr = new tree($db);
$reqspec_mgr = new requirement_spec_mgr($db);
$args = init_args();

$target_id = $args->reqspec_revision_id;
$target_id = ($target_id <= 0) ? $args->reqspec_id : $target_id;

// $node = $tree_mgr->get_node_hierarchy_info($args->reqspec_id);
$node = $tree_mgr->get_node_hierarchy_info($target_id);

$gui = new stdClass();
$gui->object_name='';
$gui->object_name = $node['name'];
$gui->page_title = sprintf(lang_get('print_requirement_specification'),$node['name']);
$gui->tproject_name=$args->tproject_name;
$gui->tproject_id=$args->tproject_id;
$gui->reqspec_id=$args->reqspec_id; 


// Struture defined in printDocument.php	
$options = array('toc' => 0, 'req_spec_scope' => 1, 'req_spec_author' => 1,'req_spec_type' =>1,
				 'req_spec_cf' => 1,'req_spec_overwritten_count_reqs' => 1,
				 'headerNumbering' => 0, 'docType' => SINGLE_REQSPEC);
            
$text2print = '';
$text2print .= renderHTMLHeader($gui->page_title,$_SESSION['basehref'],SINGLE_REQSPEC) . '<body>' ;
//$text2print .= '<div><h2>' . lang_get('req_specification') . '</h2></div>';

$text2print .= renderReqSpecNodeForPrinting($db, $node, $options,null,0,$args->tproject_id); 

// now get all it's children (just requirements).
$childrenReq = $reqspec_mgr->get_requirements($args->reqspec_id);
if( !is_null($childrenReq) && $req_cfg->show_child_reqs_on_reqspec_print_view)
{
	// IMPORTANT NOTICE:
	// 'docType' => 'SINGLE_REQ' among other things remove the indent on req table
	// that is present by default.
	// That's why we need to pass any other value.
	$reqPrintOpts = array('toc' => 0, 'req_linked_tcs' => 1, 'req_cf' => 1,
                 		  'req_scope' => 1, 'req_relations' => 1, 'req_coverage' => 1,
                 		  'req_status' => 1, 'req_type' => 1,'req_author'=> 1,
                 		  'displayVersion' => 1, 'displayDates' => 1, 
                 		  'displayLastEdit' => 1, 'docType' => SINGLE_REQ);

	$text2print .= '<div><h2>' . lang_get('reqs') . '</h2></div>';
	$loop2do = count($childrenReq);
	for($rdx=0; $rdx < $loop2do; $rdx++)
	{
		$text2print .= renderReqForPrinting($db,$childrenReq[$rdx],$reqPrintOpts,
											null,0,$args->tproject_id);
	}	
}
$text2print .= renderEOF();
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
    $args->reqspec_id = isset($_REQUEST['reqspec_id']) ? intval($_REQUEST['reqspec_id']) : 0;
    $args->reqspec_revision_id = isset($_REQUEST['reqspec_revision_id']) ? intval($_REQUEST['reqspec_revision_id']) : 0;
    $args->tproject_id = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0;
    $args->tproject_name = $_SESSION['testprojectName'];

    return $args;
}
?>