<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: keywordsView.php,v $
 *
 * @version $Revision: 1.22 $
 * @modified $Date: 2008/03/18 20:11:26 $ by $Author: franciscom $
 *
 * allows users to manage keywords. 
 */
require_once("../../config.inc.php");
require_once("common.php");
require_once("keyword.class.php");
testlinkInitPage($db);

$template_dir = 'keywords/';
$default_template = str_replace('.php','.tpl',basename($_SERVER['SCRIPT_NAME']));

$args = init_args();

$tlKeyword = new tlKeyword();
$exportTypes = $tlKeyword->getSupportedSerializationInterfaces();

$tproject = new testproject($db);
$keywords = $tproject->getKeywords($args->testproject_id);

$smarty = new TLSmarty();
$smarty->assign('action',null);
$smarty->assign('sqlResult',null);
$smarty->assign('keywords', $keywords);
$smarty->assign('canManage',has_rights($db,"mgt_modify_key"));
$smarty->assign('name',$args->keyword);
$smarty->assign('keyword',$args->keyword);
$smarty->assign('notes',$args->notes);
$smarty->assign('keywordID',$args->keywordID);
$smarty->assign('exportTypes',$exportTypes);
$smarty->display($template_dir . $default_template);


/*
  function: init_args

  args:
  
  returns: 

*/
function init_args()
{
	$args = new stdClass();
	$args->testproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
	
	$args->keywordID = isset($_REQUEST['id']) ? $_REQUEST['id'] : null;
  $args->keyword = isset($_REQUEST['keyword']) ? $_REQUEST['keyword'] : null;
  $args->notes = isset($_REQUEST['notes']) ? $_REQUEST['notes'] : null;

	return $args;
}
?>