<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Get list of users with a project right
 * 
 * @filesource	getcodetrackercfgtemplate.php
 * @package 	TestLink
 * @author 	Uwe Kirst - uwe_kirst@mentor.com
 **/

require_once('../../config.inc.php');
require_once('common.php');
testlinkInitPage($db);

$info = array('sucess' => true, 'cfg' => '');
$type = intval($_REQUEST['type']);
$itemMgr = new tlCodeTracker($db);
$ctt = $itemMgr->getTypes();
if( isset($ctt[$type]) )
{
	unset($ctt);
	$cname = $itemMgr->getImplementationForType($type);
	$info['cfg'] = stream_resolve_include_path($cname . '.class.php');

	// Notes for developers
	// Trying to use try/catch to manage missing interface file, results on nothing good.
	// This way worked.
	if( stream_resolve_include_path($cname . '.class.php') !== FALSE )
	{
		$info['cfg'] = '<pre><xmp>' . $cname::getCfgTemplate() . '</xmp></pre>';
	}
	else
	{
		$info['cfg'] = sprintf(lang_get('codetracker_interface_not_implemented'),$cname);
	}	
}
else
{
	$info['cfg'] = sprintf(lang_get('codetracker_invalid_type'),$type);
}
echo json_encode($info);
?>
