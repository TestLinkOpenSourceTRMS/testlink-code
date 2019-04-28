<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource  tcExport.php
 *
 * Scope: test case and test suites export
 *
 * @internal revisions
 * @since 1.9.15
 *
 */
require_once("../../config.inc.php");
require_once("../functions/common.php");
require_once("../functions/xml.inc.php");
testlinkInitPage($db);
$templateCfg = templateConfiguration();

$tcase_mgr = null;
$tree_mgr = new tree($db);

$args = init_args($db);
$gui = initializeGui($args);

$node_id = $args->container_id;
$check_children = 0;



if($args->useRecursion)
{
  // Exporting situations:
  // All test suites in test project
  // One test suite
  // $dummy = array_flip($tree_mgr->get_available_node_types());
  $node_info = $tree_mgr->get_node_hierarchy_info($node_id);
  $gui->export_filename = $node_info['name'];

  $gui->page_title=lang_get('title_tsuite_export');

  $dummy = '.testsuite-deep.xml';
  if($node_id == $args->tproject_id)
  {
    $gui->page_title = lang_get('title_tsuite_export_all');
    $dummy = '.testproject-deep.xml';
    $check_children=1;
    $gui->nothing_todo_msg=lang_get('no_testsuites_to_export');
  }
  $gui->export_filename .= $dummy;

}
else
{
  // Exporting situations:
  // All test cases in test suite.
  // One test case.
  if($gui->oneTestCaseExport)
  {
    $tcase_mgr = new testcase($db);
    $tcinfo = $tcase_mgr->get_by_id($args->tcase_id,$args->tcversion_id,null,array('output' => 'essential'));
    $tcinfo = $tcinfo[0];
    $node_id = $args->tcase_id;
    $gui->export_filename = $tcinfo['name'] . '.version' . $tcinfo['version'] . '.testcase.xml';
    $gui->page_title = lang_get('title_tc_export');
  }
  else
  {
    $check_children = 1;
    $node_info = $tree_mgr->get_node_hierarchy_info($args->container_id);
    $gui->export_filename = $node_info['name'] . '.testsuite-children-testcases.xml';
    $gui->page_title = lang_get('title_tc_export_all');
    $gui->nothing_todo_msg = lang_get('no_testcases_to_export');
  }
}
$gui->export_filename = is_null($args->export_filename) ? $gui->export_filename : $args->export_filename;


if( $check_children )
{
  // Check if there is something to export
  $children=$tree_mgr->get_children($node_id,
                                    array("testplan" => "exclude_me",
                                          "requirement_spec" => "exclude_me",
                                          "requirement" => "exclude_me"));

  $gui->nothing_todo_msg='';
  if(count($children)==0)
  {
    $gui->do_it = 0 ;
  }
}
$node = $tree_mgr->get_node_hierarchy_info($node_id);


if ($args->doExport)
{
  if( is_null($tcase_mgr) )
  {
    $tcase_mgr = new testcase($db);
  }
  $tsuite_mgr = new testsuite($db);

  $pfn = null;
  switch($args->exportType)
  {
    case 'XML':
      $pfn = 'exportTestSuiteDataToXML';
      if ($gui->oneTestCaseExport)
      {
        $pfn = 'exportTestCaseDataToXML';
      }
      break;
  }

  if ($pfn)
  {
    if ($gui->oneTestCaseExport)
    {
      $args->optExport['RELATIONS'] = true;
      $args->optExport['ROOTELEM'] = "<testcases>{{XMLCODE}}</testcases>";
      $content = $tcase_mgr->$pfn($args->tcase_id,$args->tcversion_id,$args->tproject_id,null,$args->optExport);
    }
    else
    {
      $content = TL_XMLEXPORT_HEADER;
      $content .= $tsuite_mgr->$pfn($args->container_id,$args->tproject_id,$args->optExport);
    }

    downloadContentsToFile($content,$gui->export_filename);
    exit();
  }
}

$gui->object_name=$node['name'];
$gui->tproject_name=$args->tproject_name;
$gui->tproject_id=$args->tproject_id;
$gui->tcID=$args->tcase_id;
$gui->useRecursion=$args->useRecursion ? 1 : 0;
$gui->tcVersionID=$args->tcversion_id;
$gui->containerID=$args->container_id;

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/*
  function: init_args

  args:

  returns:

*/
function init_args(&$dbHandler)
{
  $_REQUEST = strings_stripSlashes($_REQUEST);

  $args = new stdClass();
  $args->doExport = isset($_REQUEST['export']) ? $_REQUEST['export'] : null;

  $k2l = array('useRecursion','exportReqs','exportCFields','exportKeywords',
               'exportTestCaseExternalID','exportTCSummary','exportTCPreconditions',
               'exportTCSteps', 'exportAttachments');
  foreach ($k2l as $key)
  {
    $args->$key = isset($_REQUEST[$key]) ? intval($_REQUEST[$key]) : 0;
  }

  $args->addPrefix = 0;
  if($args->exportTestCaseExternalID)
  {
    $args->addPrefix = isset($_REQUEST['addPrefix']) ? 1 : 0;
  }

  $args->optExport = array('REQS' => $args->exportReqs, 'CFIELDS' => $args->exportCFields,
                           'KEYWORDS' => $args->exportKeywords,
                           'EXTERNALID' => $args->exportTestCaseExternalID,
                           'ADDPREFIX' => $args->addPrefix,
                           'RECURSIVE' => $args->useRecursion,
                           'TCSUMMARY' => $args->exportTCSummary,
                           'TCPRECONDITIONS' => $args->exportTCPreconditions,
                           'ATTACHMENTS' => $args->exportAttachments,
                           'TCSTEPS' => $args->exportTCSteps);
    
  
  $omgr = $args->useRecursion ? new testsuite($dbHandler) : new testcase($dbHandler); 
  $args->exportTypes = $omgr->get_export_file_types();
  $args->exportType = null;
  if( isset($_REQUEST['exportType']) )
  {
    $xd = strtoupper(trim($_REQUEST['exportType']));
    $args->exportType = isset($args->exportTypes[$xd]) ? $args->exportTypes[$xd] : null;
  }  

  $args->export_filename=isset($_REQUEST['export_filename']) ? $_REQUEST['export_filename'] : null;

  $args->tcase_id = isset($_REQUEST['testcase_id']) ? intval($_REQUEST['testcase_id']) : 0;
  $args->tcversion_id = isset($_REQUEST['tcversion_id']) ? intval($_REQUEST['tcversion_id']) : 0;
  $args->container_id = isset($_REQUEST['containerID']) ? intval($_REQUEST['containerID']) : 0;

  // To be replaced with $_REQUEST value
  $args->tproject_id = intval(isset($_REQUEST['tproject_id']) ? $_REQUEST['tproject_id'] : 0);
  if($args->tproject_id > 0)
  {
    $dummy = $omgr->tree_manager->get_node_hierarchy_info($args->tproject_id);
    if(!is_null($dummy))
    {
      $args->tproject_name = $dummy['name'];
    }  
    else
    {
      throw new Exception("BAD Test Project ID={$args->tproject_id}", 1);
    }  
  }  
  else
  {
    throw new Exception("Test Project ID=0", 1);
  }  

  $args->goback_url=isset($_REQUEST['goback_url']) ? $_REQUEST['goback_url'] : null;

  unset($omgr);
  return $args;
}


function initializeGui($argsObj)
{
  $guiObj = new stdClass();
  $guiObj->do_it = 1;
  $guiObj->nothing_todo_msg = '';
  $guiObj->export_filename = '';
  $guiObj->page_title = '';
  $guiObj->object_name = '';
  $guiObj->exportTypes = $argsObj->exportTypes;
  $guiObj->tproject_id = $argsObj->tproject_id;

  
  $guiObj->goback_url = !is_null($argsObj->goback_url) ? $argsObj->goback_url : '';
  $guiObj->oneTestCaseExport = ($argsObj->tcase_id && $argsObj->tcversion_id);

  if($argsObj->useRecursion || !$guiObj->oneTestCaseExport)
  {
    $guiObj->cancelActionJS = 'location.href=fRoot+' . "'" . "lib/testcases/archiveData.php?" .
                              'edit=testsuite&id=' . intval($argsObj->container_id) . "'";
  }
  else
  {
    $guiObj->cancelActionJS = 'location.href=fRoot+' . "'" . "lib/testcases/archiveData.php?" .
                              'edit=testcase&id=' . intval($argsObj->tcase_id) . "'";
  }
  return $guiObj;
}
