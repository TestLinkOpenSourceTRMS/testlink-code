<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * This file acts as the base file for all Testlink plugins
 *
 *
 * @filesource  tlPlugin.php
 * @package     TestLink
 * @Copyright   2005-2016 TestLink community
 * @link        http://www.testlink.org
 *
 */

require_once('plugin_api.php');

/**
 * Base class for TestLink Plugins
 */
abstract class TestlinkPlugin extends tlObjectWithDB
{

  /**
   * name - Your plugin's full name. Required value.
   */
  public $name = null;
  /**
   * description - A full description of your plugin.
   */
  public $description = null;
  /**
   * version - Your plugin's version string. Required value.
   */
  public $version = null;
  /**
   * author - Your name, or an array of names.
   */
  public $author = null;
  /**
   * contact - An email address where you can be contacted.
   */
  public $contact = null;
  /**
   * url - A web address for your plugin.
   */
  public $url = null;

  var $db = null;

  /**
   * this function registers your plugin - must set at least name and version
   */
  abstract public function register();

  /**
   * this function allows your plugin to set itself up, include any necessary API's, declare or hook events, etc.
   * This is also where you would initialize support classes (setting include_path etc to allow other classes to be loaded
   */
  public function init()
  {
  }

  /**
   * This function allows for the plugins to do any specific install activities.
   * Return false if u want to stop installation
   */
  public function install()
  {
    return true;
  }

  /**
   * This function allows for the plugins to do any specific uninstall activities that maybe required to cleanup
   */
  public function uninstall()
  {
    
  }
  
  /**
   * return an array of default configuration name/value pairs
   */
  public function config()
  {
    return array();
  }

  /**
   * Defines the hooks exposed by the plugins. See events_inc.php for a list of hooks
   */
  public function hooks()
  {
    return array();
  }

  ### Core plugin functionality ###
  public $basename = null;

  final public function __construct(&$db, $p_basename)
  {
    $this->db = $db;
    parent::__construct($this->db);

    $this->basename = $p_basename;
    $this->register();
  }

  final public function __init()
  {
    plugin_config_defaults($this->config());
    plugin_event_hook_many($this->hooks());

    $this->init();
  }

}
