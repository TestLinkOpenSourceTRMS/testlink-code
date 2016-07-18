<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Enable/Disable/Show plugins
 *
 * @filesource  pluginView.php
 * @package     TestLink
 * @copyright   2015-2016, TestLink community
 * @link        http://www.testlink.org/
 *
 */
require_once('../../config.inc.php');
require_once('common.php');
require_once('users.inc.php');
require_once('exttable.class.php');
require_once("plugin_api.php");
testlinkInitPage($db,false,false,"checkRights");

$smarty = new TLSmarty();

$templateCfg = templateConfiguration();


list($args,$gui) = initEnv($db);

switch($args->operation)
{
    case 'install':
        if ($args->pluginName)
        {
            $p_plugin = plugin_register($args->pluginName, true);
            plugin_init($args->pluginName);
            plugin_install($p_plugin);
            $feedback = sprintf(lang_get('plugin_installed'), $args->pluginName);
        }
        break;
	case 'uninstall':
	    if ($args->pluginId)
        {
            $t_basename = plugin_uninstall($args->pluginId);
            $feedback = sprintf(lang_get('plugin_uninstalled'), $t_basename);
        }  
  	    break;
		
	default:
	    break;
} 

$gui->main_title = lang_get('title_plugin_mgmt');
$gui->installed_plugins = get_all_installed_plugins();
$gui->available_plugins = get_all_available_plugins($gui->installed_plugins);

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
if ($feedback) {
  $smarty->assign('user_feedback', $feedback);
}
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


function initEnv(&$dbHandler)
{
  $_REQUEST=strings_stripSlashes($_REQUEST);

  $iParams = array("operation" => array(tlInputParameter::STRING_N,0,50),
                   "pluginId" => array(tlInputParameter::INT_N),
                   "pluginName" => array(tlInputParameter::STRING_N,0,50));

  $args = new stdClass();
  $pParams = R_PARAMS($iParams,$args);

  $args->currentUser = $_SESSION['currentUser'];
  $args->currentUserID = $_SESSION['currentUser']->dbID;
  $args->basehref =  $_SESSION['basehref'];
  
  $gui = new stdClass();
  $gui->grants = getGrantsForUserMgmt($dbHandler,$args->currentUser);
  $gui->feedback = '';
  $gui->basehref = $args->basehref;

  return array($args,$gui);
}

function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'mgt_plugins');
}