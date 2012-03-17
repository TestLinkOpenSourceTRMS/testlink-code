<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Get list of users with a project right
 * 
 * @filesource	getissuetrackercfgtemplate.php
 * @package 	TestLink
 * @author 		Francisco Mancardi - francisco.mancardi@gmail.com
 * @copyright 	2012, TestLink community 
 *
 * @internal revisions
 * @since 1.9.4
 * 20120311 - franciscom - TICKET 4904: integrate with ITS on test project basis
 **/

require_once('../../config.inc.php');
require_once('common.php');
testlinkInitPage($db);

$info = array('sucess' => true, 'cfg' => '');
$type = intval($_REQUEST['type']);
$itemMgr = new tlIssueTracker($db);
$itt = $itemMgr->getTypes();
if( isset($itt[$type]) )
{
	unset($itt);
	$iname = $itemMgr->getImplementationForType($type);
	$info['cfg'] = stream_resolve_include_path($iname . '.class.php');

	// Notes for developers
	// Trying to use try/catch to manage missing interface file, results on nothing good.
	// This way worked.
	if( stream_resolve_include_path($iname . '.class.php') !== FALSE )
	{
		$info['cfg'] = '<pre><xmp>' . $iname::getCfgTemplate() . '</xmp></pre>';
	}
	else
	{
		$info['cfg'] = sprintf(lang_get('issuetracker_interface_not_implemented'),$iname);
	}	
}
else
{
	$info['cfg'] = sprintf(lang_get('issuetracker_invalid_type'),$type);
}
echo json_encode($info);
?>