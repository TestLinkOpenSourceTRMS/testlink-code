<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource  TLTest.php
 * @copyright   2005-2016, TestLink community
 * @link        http://www.testlink.org/
 *
 */

$smarty = new TLSmarty();
$gui = new stdClass();

if ($_POST['submit']) {   // Check if the form is submitted

  plugin_config_set('config1', $_POST['config1'], $_SESSION['testprojectID']);
  plugin_config_set('config2', $_POST['config2'], $_SESSION['testprojectID']);

  $gui->message = "Configuration Saved";    // Confirm message

  // Assign to Smarty
  $smarty->assign('gui',$gui);
  $smarty->display(plugin_file_path('config.tpl'));
  return;
}

$gui->headerMessage = "Sample Configuration";
$gui->config1 = plugin_config_get('config1', '', $_SESSION['testprojectID']);
$gui->config2 = plugin_config_get('config2', '', $_SESSION['testprojectID']);

$smarty->assign('gui',$gui);
$smarty->display(plugin_file_path('config.tpl'));