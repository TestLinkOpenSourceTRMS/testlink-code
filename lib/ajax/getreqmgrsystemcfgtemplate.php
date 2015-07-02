<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Get list of users with a project right
 * 
 * @package     TestLink
 * @filesource  getreqmgrsystemcfgtemplate.php
 * @since       1.9.6
 * @author      Francisco Mancardi - francisco.mancardi@gmail.com
 * @copyright   2013, TestLink community 
 *
 * @internal revisions
 * @since 1.9.6
 **/

require_once('../../config.inc.php');
require_once('common.php');
testlinkInitPage($db);

$info = array('sucess' => true, 'cfg' => '');
$type = intval($_REQUEST['type']);
$mgr = new tlReqMgrSystem($db);
$itt = $mgr->getTypes();
if( isset($itt[$type]) )
{
  unset($itt);
  $iname = $mgr->getImplementationForType($type);
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
    $info['cfg'] = sprintf(lang_get('reqmgrsystem_interface_not_implemented'),$iname);
  }  
}
else
{
  $info['cfg'] = sprintf(lang_get('reqmgrsystem_invalid_type'),$type);
}
echo json_encode($info);
?>