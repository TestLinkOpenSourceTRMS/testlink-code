<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * This file is used to load pages that are inside of the plugin directory. This
 * provides context and helper functions so that they are used inside of the plugin
 *
 * @filesource  plugin.php
 * @package     TestLink
 * @Copyright   2005-2016 TestLink community
 * @link        http://www.testlink.org
 *
 */

require_once('config.inc.php');
require_once('common.php');

// Init all plugins
plugin_init_installed();

$page = $_GET['page'];
$t_matches = array();

if (!preg_match('/^([a-zA-Z0-9_-]+)\/([a-zA-Z0-9_-]+[\/a-zA-Z0-9_-]*)/', $page, $t_matches)) {
  trigger_error('Error finding page', E_USER_NOTICE);
  return;
}

$plugin_name = $t_matches[1];
$plugin_page = $t_matches[2];

global $g_plugin_cache;

$plugin_page_qualified = TL_PLUGIN_PATH . $plugin_name . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR . $plugin_page . '.php';

if (!is_file($plugin_page_qualified)) {
  trigger_error('Cannot find plugin page: ' . $plugin_page_qualified, E_USER_NOTICE);
}

plugin_push_current($plugin_name);
include($plugin_page_qualified);

