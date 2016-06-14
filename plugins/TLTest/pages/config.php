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

  $gui->message = plugin_lang_get('config_page_saved');    // Confirm message

  // Assign to Smarty
  $smarty->assign('gui',$gui);
  $smarty->display(plugin_file_path('config.tpl'));
  return;
}

$gui->headerMessage = plugin_lang_get('config_page_header_message');
$gui->title = plugin_lang_get('config_page_title');
$gui->labelConfig1 = plugin_lang_get('config_label_config1');
$gui->labelConfig2 = plugin_lang_get('config_label_config2');
$gui->config1 = plugin_config_get('config1', '', $_SESSION['testprojectID']);
$gui->config2 = plugin_config_get('config2', '', $_SESSION['testprojectID']);
$gui->labelSaveConfig = plugin_lang_get('config_label_save_button');

$smarty->assign('gui',$gui);
$smarty->display(plugin_file_path('config.tpl'));