<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Filename $RCSfile: reqExport.php,v $
 *
 * @version $Revision: 1.7 $
 * @modified $Date: 2009/03/23 08:10:18 $ by $Author: franciscom $
 *
 * This page this allows users to export requirements.
 *
**/
require_once("../../config.inc.php");
require_once("csv.inc.php");
require_once("xml.inc.php");
require_once("common.php");
require_once("requirements.inc.php");

testlinkInitPage($db,false,false,"checkRights");
$templateCfg = templateConfiguration();
$req_spec_mgr = new requirement_spec_mgr($db);

$args=init_args();
$gui=initializeGui($args,$req_spec_mgr);

switch($args->doAction)
{
    case 'export':
        $smarty = new TLSmarty();
        $smarty->assign('gui', $gui);
        $smarty->display($templateCfg->template_dir . $templateCfg->default_template);
    break;
    
    case 'doExport':
        doExport($args,$req_spec_mgr);
    break;
      
}


/**
 * checkRights
 *
 */
function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'mgt_view_req');
}


/**
 * init_args
 *
 */
function init_args()
{
   $_REQUEST = strings_stripSlashes($_REQUEST);
   $args = new stdClass();
   $args->doAction = isset($_REQUEST['doAction']) ? $_REQUEST['doAction'] : 'export';
   $args->exportType = isset($_REQUEST['exportType']) ? $_REQUEST['exportType'] : null;
   $args->req_spec_id = isset($_REQUEST['req_spec_id']) ? $_REQUEST['req_spec_id'] : null;
   $args->export_filename = isset($_REQUEST['export_filename']) ? $_REQUEST['export_filename'] : "";
 	 $args->tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;

   return $args;  
}


/**
 * initializeGui
 *
 */
function initializeGui(&$argsObj,&$req_spec_mgr)
{
   $gui=new stdClass();
   $gui->req_spec = $req_spec_mgr->get_by_id($argsObj->req_spec_id);
   $gui->exportTypes = $req_spec_mgr->get_export_file_types();
   $gui->exportType = $argsObj->exportType; 
   $gui->req_spec_id = $argsObj->req_spec_id;
   $gui->export_filename = trim($argsObj->export_filename);
   if( strlen($gui->export_filename) == 0 )
   {
       $gui->export_filename=$gui->req_spec['title'] . '-req.xml';
   }
   return $gui;  
}



/**
 * doExport
 *
 */
function doExport(&$argsObj,&$req_spec_mgr)
{
	$pfn = null;
	switch($argsObj->exportType)
	{
		case 'csv':
	    $requirements_map = $req_spec_mgr->get_requirements($argsObj->req_spec_id);
			$pfn = "exportReqDataToCSV";
			$fileName = 'reqs.csv';
 		  $content = $pfn($requirements_map);
			break;

		case 'XML':
			$pfn = "exportReqSpecToXML";
			$fileName = 'reqs.xml';
  		$content = TL_XMLEXPORT_HEADER;
  		$content .= "<requirement-specification>\n";
		  $content .= $req_spec_mgr->$pfn($argsObj->req_spec_id,$argsObj->tproject_id);
			$content .= "</requirement-specification>\n";
			break;
	}

	if ($pfn)
	{
	  $fileName = is_null($argsObj->export_filename) ? $fileName : $argsObj->export_filename;
		downloadContentsToFile($content,$fileName);
		exit();
	}
  return;
}


?>