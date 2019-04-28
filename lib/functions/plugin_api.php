<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Load core functions for TestLink GUI
 * Common functions: database connection, session and data initialization,
 * maintain $_SESSION data, redirect page, log, etc.
 *
 * Note: this file must uses only globally used functionality and cannot include
 * a feature specific code because of performance and readability reasons
 *
 * @filesource  plugin_api.php
 * @package     TestLink
 * @Copyright   2005-2016 TestLink community
 * @link        http://www.testlink.org
 *
 */

/**
 * requires TestlinkPlugin.class
 */
require_once('tlPlugin.class.php');
require_once('event_api.php');

# Cache variables #####
$g_plugin_cache = array();
$g_plugin_current = array();

# Public API #####
/**
 * Get the currently executing plugin's basename.
 * @return string Plugin basename, or null if no current plugin
 */
function plugin_get_current()
{
  global $g_plugin_current;
  return (isset($g_plugin_current[0]) ? $g_plugin_current[0] : null);
}

/**
 * Add the current plugin to the stack
 * @param string Plugin basename
 */
function plugin_push_current($p_basename)
{
  global $g_plugin_current;
  array_unshift($g_plugin_current, $p_basename);
}

/**
 * Remove the current plugin from the stack
 * @return string Plugin basename, or null if no current plugin
 */
function plugin_pop_current()
{
  global $g_plugin_current;
  return (isset($g_plugin_current[0]) ? array_shift($g_plugin_current) : null);
}

/**
 * Get the URL to the plugin wrapper page.
 * @param string Page name
 * @param string Plugin basename (defaults to current plugin)
 */
function plugin_page($page, $basename = null)
{
  $current = is_null($basename) ? plugin_get_current() : $basename;
  return 'plugin.php?page=' . $current . '/' . $page;
}

/**
 * Return a path to a plugin file.
 * @param string File name
 * @param string Plugin basename
 * @return mixed File path or false if FNF
 */
function plugin_file_path($filename, $folderpath = null, $basename = null)
{
  $t_file_path = TL_PLUGIN_PATH;

  $basename = ($basename != null) ? $basename : plugin_get_current();
  $folderpath = ($folderpath != null) ? $folderpath : 'pages';
  $t_file_path .= $basename . DIRECTORY_SEPARATOR;
  $t_file_path .= $folderpath . DIRECTORY_SEPARATOR . $filename;

  return (is_file($t_file_path) ? $t_file_path : false);
}

/**
 * Get a plugin configuration option.
 *
 * If the project is not specified, then the global configuration value for that key is returned.
 * If the project is specified, then the local value is returned.
 *
 *
 * @param string Configuration option name
 * @param multi Default option value
 * @param int Project ID
 */
function plugin_config_get($option, $default = null, $project = TL_ANY_PROJECT)
{
  $debugMsg = "Function: " . __FUNCTION__;

  doDBConnect($dbHandler);
  $tables = tlObjectWithDB::getDBTables(array('plugins_configuration'));
  $target = $tables['plugins_configuration'];

  $basename = plugin_get_current();
  $full_option = 'plugin_' . $basename . '_' . $option;
  $full_option = $dbHandler->prepare_string($full_option);

  $sql = "/* $debugMsg */ " .
         " SELECT config_value FROM " . $tables['plugins_configuration'] . 
         " where config_key = '" . $full_option . "' AND  testproject_id = ";

  $value = $dbHandler->fetchOneValue($sql . intval($project));

  if (is_null($value) && $project != TL_ANY_PROJECT) 
  {
    // Check if its in the Global Project
    $value = $dbHandler->fetchOneValue($sql . TL_ANY_PROJECT);
  }

  if (is_null($value)) 
  {
    // Fetch from the Global list, and if not, fetch from default value
    global $g_plugin_config_cache;
    $value = array_key_exists($full_option, $g_plugin_config_cache) ? $g_plugin_config_cache[$full_option] : $default;
  }
  return $value;
}

/**
 * Set a plugin configuration option in the database.
 * @param string Configuration option name
 * @param multi Option value
 * @param int User ID
 * @param int Project ID
 * @param int Access threshold
 */
function plugin_config_set($option, $value, $project = TL_ANY_PROJECT)
{
  doDBConnect($dbHandler);
  $tables = tlObjectWithDB::getDBTables(array('plugins_configuration'));
  $plugin_config_table = $tables['plugins_configuration'];

  $basename = plugin_get_current();
  $full_option = 'plugin_' . $basename . '_' . $option;

  if (is_array($value) || is_object($value)) 
  {
    $config_type = CONFIG_TYPE_COMPLEX;
    $value = serialize($value);
  } 
  else if (is_float($value)) 
  {
    $config_type = CONFIG_TYPE_FLOAT;
    $value = (float)$value;
  } 
  else if (is_int($value) || is_numeric($value)) 
  {
    $config_type = CONFIG_TYPE_INT;
    $value = $dbHandler->prepare_int($value);
  } 
  else 
  {
    $config_type = CONFIG_TYPE_STRING;
  }

 
  $safe_id = intval($project); 
  $sql = " SELECT COUNT(*) from $plugin_config_table " .
         " WHERE config_key = '" . $dbHandler->prepare_string($full_option) . "' " .
         " AND testproject_id = {$safe_id} ";
  $rows_exist = $dbHandler->fetchOneValue($sql);

  if ($rows_exist > 0) 
  {
    // Update the existing record
    $sql = " UPDATE $plugin_config_table " .
           " SET config_value = '" . $dbHandler->prepare_string($value) . "'," .
           " config_type = " . $config_type . 
           " WHERE config_key = '" . $dbHandler->prepare_string($full_option) . "' " .
           " AND testproject_id = {$safe_id} ";
  } 
  else 
  {
    // Insert new config value
    $sql = " INSERT INTO $plugin_config_table " .
           " (config_key, config_type, config_value, testproject_id, author_id) " .
           " VALUES (" .
           "'" . $dbHandler->prepare_string($full_option) . "', " . 
           $config_type . "," . 
           "'" . $dbHandler->prepare_string($value) . "', " .
           $safe_id . ", " . $_SESSION['currentUser']->dbID . ")";
  }
  $dbHandler->exec_query($sql);
}

/**
 * Set plugin default values to global values without overriding anything.
 * @param array Array of configuration option name/value pairs.
 */
function plugin_config_defaults($options)
{
  global $g_plugin_config_cache;
  if (!is_array($options)) 
  {
    return;
  }

  $basename = plugin_get_current();
  $option_base = 'plugin_' . $basename . '_';

  foreach ($options as $option => $value) 
  {
    $full_option = $option_base . $option;
    $g_plugin_config_cache[$full_option] = $value;
  }
}

/**
 * Get a language string for the plugin.
 * Automatically prepends plugin_<basename> to the string requested.
 * @param string Language string name
 * @param string Plugin basename
 * @return string Language string
 */
function plugin_lang_get($p_name, $p_basename = null)
{
  if (!is_null($p_basename)) 
  {
    plugin_push_current($p_basename);
  }

  $t_basename = plugin_get_current();
  $t_name = 'plugin_' . $t_basename . '_' . $p_name;
  $t_string = lang_get($t_name);

  if (!is_null($p_basename)) 
  {
    plugin_pop_current();
  }
  return $t_string;
}

/**
 * Hook a plugin's callback function to an event.
 * @param string Event name
 * @param string Callback function
 */
function plugin_event_hook($p_name, $p_callback)
{
  $t_basename = plugin_get_current();
  event_hook($p_name, $p_callback, $t_basename);
}

/**
 * Hook multiple plugin callbacks at once.
 * @param array Array of event name/callback key/value pairs
 */
function plugin_event_hook_many($p_hooks)
{
  if (!is_array($p_hooks)) 
  {
    return;
  }

  $t_basename = plugin_get_current();

  foreach ($p_hooks as $t_event => $t_callbacks) 
  {
    if (!is_array($t_callbacks)) 
    {
      event_hook($t_event, $t_callbacks, $t_basename);
      continue;
    }

    foreach ($t_callbacks as $t_callback) 
    {
      event_hook($t_event, $t_callback, $t_basename);
    }
  }
}

# ## Plugin Management Helpers

/**
 * Checks if a given plugin has been registered and initialized,
 * and returns a boolean value representing the "loaded" state.
 * @param string Plugin basename
 * @return boolean Plugin loaded
 */
function plugin_is_loaded($p_basename)
{
  global $g_plugin_cache_init;

  return (isset($g_plugin_cache_init[$p_basename]) && $g_plugin_cache_init[$p_basename]);
}

# ## Plugin management functions
/**
 * Determine if a given plugin is installed.
 * @param string Plugin basename
 * @return boolean True if plugin is installed
 */
function plugin_is_installed($p_basename)
{
  doDBConnect($dbHandler);
  $tables = tlObjectWithDB::getDBTables(array('plugins'));

  $sql = " SELECT COUNT(*) count FROM {$tables['plugins']} " . 
         " WHERE basename='" . $dbHandler->prepare_string($p_basename) . "'";

  $t_result = $dbHandler->fetchFirstRow($sql);
  return (0 < $t_result['count']);
}

/**
 * Install a plugin to the database.
 * @param string Plugin basename
 */
function plugin_install($p_plugin)
{
  $debugMsg = "Function: " . __FUNCTION__;

  if (plugin_is_installed($p_plugin->basename)) 
  {
    trigger_error('Plugin ' . $p_plugin->basename . ' already installed', E_USER_WARNING);
    return null;
  }

  plugin_push_current($p_plugin->basename);

  if (!$p_plugin->install())
  {
    plugin_pop_current($p_plugin->basename);
    return null;
  }

  doDBConnect($dbHandler);
  $tables = tlObjectWithDB::getDBTables(array('plugins'));
  $sql = "/* $debugMsg */ INSERT INTO {$tables['plugins']} (basename,enabled) " . 
         " VALUES ('" . $dbHandler->prepare_string($p_plugin->basename) . "',1)";
  $dbHandler->exec_query($sql);

  plugin_pop_current();
}

/**
 * Uninstall a plugin from the database.
 * @param string Plugin basename
 */
function plugin_uninstall($plugin_id)
{
  global $g_plugin_cache;
  $debugMsg = "Function: " . __FUNCTION__;

  doDBConnect($dbHandler);
  $tables = tlObjectWithDB::getDBTables(array('plugins'));
  $sql = "/* debugMsg */ " .
      " SELECT basename FROM {$tables['plugins']} WHERE id=" . $plugin_id;

  $t_row = $dbHandler->fetchFirstRow($sql);

  // Check that teh plugin is actually available and loaded
  if (!$t_row)
  {
    return;
  }
  $t_basename = $t_row['basename'];

  $sql = "/* $debugMsg */ DELETE FROM {$tables['plugins']} " .
         " WHERE id=" . $plugin_id;
  $dbHandler->exec_query($sql);

  $p_plugin = $g_plugin_cache[$t_basename];
  $p_plugin->uninstall();
  return $t_basename;
}

# ## Core usage only.
/**
 * Search the plugins directory for plugins.
 * @return array Plugin basename/info key/value pairs.
 */
function plugin_find_all()
{
  $t_plugin_path = TL_PLUGIN_PATH;

  if ($t_dir = opendir($t_plugin_path)) 
  {
    while (($t_file = readdir($t_dir)) !== false) 
    {
      if ('.' == $t_file || '..' == $t_file) 
      {
        continue;
      }
      if (is_dir($t_plugin_path . $t_file)) 
      {
        $t_plugin = plugin_register($t_file, true);

        if (!is_null($t_plugin)) 
        {
          $t_plugins[$t_file] = $t_plugin;
        }
      }
    }
    closedir($t_dir);
  }
  return $t_plugins;
}

/**
 * Load a plugin's core class file.
 * @param string Plugin basename
 */
function plugin_include($p_basename)
{
  $t_plugin_file = TL_PLUGIN_PATH . $p_basename . DIRECTORY_SEPARATOR . $p_basename . '.php';

  $t_included = false;
  if (is_file($t_plugin_file)) 
  {
    include_once($t_plugin_file);
    $t_included = true;
  }

  return $t_included;
}

/**
 * Register a plugin with TestLink.
 * The plugin class must already be loaded before calling.
 * @param string Plugin classname without 'Plugin' postfix
 */
function plugin_register($p_basename, $p_return = false)
{
  global $g_plugin_cache;

  if (!isset($g_plugin_cache[$p_basename])) 
  {
    $t_classname = $p_basename . 'Plugin';

    # Include the plugin script if the class is not already declared.
    if (!class_exists($t_classname)) 
    {
      if (!plugin_include($p_basename)) 
      {
        return null;
      }
    }

    # Make sure the class exists and that it's of the right type.
    if (class_exists($t_classname) && is_subclass_of($t_classname, 'TestlinkPlugin')) 
    {
      plugin_push_current($p_basename);

      doDBConnect($dbHandler);
      $t_plugin = new $t_classname($dbHandler, $p_basename);

      plugin_pop_current();

      # Final check on the class
      if (is_null($t_plugin->name) || is_null($t_plugin->version)) 
      {
        return null;
      }

      if ($p_return) 
      {
        return $t_plugin;
      } 
      else 
      {
        $g_plugin_cache[$p_basename] = $t_plugin;
      }
    }
  }

  return $g_plugin_cache[$p_basename];
}

/**
 * Find and register all installed plugins.
 */
function plugin_register_installed()
{
  doDBConnect($dbHandler);
  $tables = tlObjectWithDB::getDBTables(array('plugins'));
  $sql = "/* debugMsg */ " .
         " SELECT basename FROM {$tables['plugins']} WHERE enabled=1 ";

  $t_result = $dbHandler->exec_query($sql);
  while ($t_row = $dbHandler->fetch_array($t_result)) 
  {
    $t_basename = $t_row['basename'];
    plugin_register($t_basename);
  }
}

/**
 * Initialize all installed plugins.
 */
function plugin_init_installed()
{

  global $g_plugin_cache, $g_plugin_current, $g_plugin_cache_init;
  $g_plugin_cache = array();
  $g_plugin_current = array();
  $g_plugin_cache_init = array();

  plugin_register_installed();

  $t_plugins = array_keys($g_plugin_cache);

  foreach ($t_plugins as $t_basename) 
  {
    plugin_init($t_basename);
  }

}

/**
 * Initialize a single plugin.
 * @param string Plugin basename
 * @return boolean True if plugin initialized, false otherwise.
 */
function plugin_init($p_basename)
{
  global $g_plugin_cache, $g_plugin_cache_init;

  $ret = false;
  if (isset($g_plugin_cache[$p_basename])) 
  {
    $t_plugin = $g_plugin_cache[$p_basename];

    plugin_push_current($p_basename);

    # finish initializing the plugin
    $t_plugin->__init();
    $g_plugin_cache_init[$p_basename] = true;

    plugin_pop_current();
    $ret = true;
  } 
  return $ret;
}

function get_all_installed_plugins()
{
  doDBConnect($dbHandler);

  // Store all the available plugins (Enabled + Disabled + Just Available)
  $installed_plugins = array();

  $tables = tlObjectWithDB::getDBTables(array('plugins'));
  $sql = "/* debugMsg */ " .
      " SELECT id, basename, enabled FROM {$tables['plugins']}";

  $t_result = $dbHandler->exec_query($sql);
  while ($t_row = $dbHandler->fetch_array($t_result)) {
    $t_basename = $t_row['basename'];
    $t_enabled = $t_row['enabled'];
    $t_pluginid = $t_row['id'];

    if (plugin_include($t_basename)) {
      $t_classname = $t_basename . 'Plugin';
      $t_plugin = new $t_classname($dbHandler, $t_basename);

      $installed_plugins[] = array(
          'id' => $t_pluginid,
          'name' => $t_basename,
          'enabled' => $t_enabled,
          'description' => $t_plugin->description,
          'version' => $t_plugin->version);
    }
  }

  return $installed_plugins;
}

function get_plugin_name($arr)
{
  if (array_key_exists('name', $arr))
  {
    return $arr['name'];
  }
  return false;
}

function get_all_available_plugins($existing_plugins)
{
  $registered_plugin_names = array_map("get_plugin_name", $existing_plugins);
  $available_plugins = array();
  // Find all plugins that are newly available (And not already registered)
  if ($t_dir = opendir(TL_PLUGIN_PATH))
  {
    while (($t_file = readdir($t_dir)) !== false)
    {
      if ('.' == $t_file || '..' == $t_file)
      {
        continue;
      }
      if (!in_array($t_file, $registered_plugin_names) &&
          is_dir(TL_PLUGIN_PATH. $t_file) && plugin_include($t_file))
      {
        $t_classname = $t_file . 'Plugin';
        if (class_exists($t_classname) && is_subclass_of($t_classname, 'TestlinkPlugin'))
        {
          $t_plugin = new $t_classname($dbHandler, $t_file);

          $available_plugins[] = array('name' => $t_plugin->name,
              'enabled' => 0,
              'description' => $t_plugin->description,
              'version' => $t_plugin->version);
        }
      }
    }
    closedir($t_dir);
  }

  return $available_plugins;
}
