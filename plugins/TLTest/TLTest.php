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

require_once(TL_ABS_PATH . '/lib/functions/tlPlugin.class.php');

/**
 * Sample Testlink Plugin class that registers itself with the system and provides 
 * UI hooks for 
 * Left Top, Left Bottom, Right Top and Right Bottom screens.
 * 
 * This also listens to testsuite creation and echoes out for example. 
 * 
 * Class TLTestPlugin
 */
class TLTestPlugin extends TestlinkPlugin
{
  function _construct()
  {

  }

  function register()
  {
    $this->name = 'TLTest';
    $this->description = 'Test Plugin';

    $this->version = '1.0';

    $this->author = 'Testlink';
    $this->contact = 'raja@star-systems.in';
    $this->url = 'http://www.collab.net';
  }

  function config()
  {
    return array(
      'config1' => '',
      'config2' => 0
    );
  }

  function hooks()
  {
    $hooks = array(
      'EVENT_TEST_SUITE_CREATE' => 'testsuite_create',
      'EVENT_TEST_PROJECT_CREATE' => 'testproject_create',
      'EVENT_TEST_PROJECT_UPDATE' => 'testproject_update',
      'EVENT_EXECUTE_TEST'  => 'testExecute',
      'EVENT_LEFTMENU_TOP' => 'top_link',
      'EVENT_LEFTMENU_BOTTOM' => 'bottom_link',
      'EVENT_RIGHTMENU_TOP' => 'right_top_link',
      'EVENT_RIGHTMENU_BOTTOM' => 'right_bottom_link'
    );
    return $hooks;
  }

  function testsuite_create($args)
  {
    $arg = func_get_args();   // To get all the arguments
    $db = $this->db;      // To show how to get a Database Connection
    echo plugin_lang_get("testsuite_display_message");
    tLog("Im in testsuite create", "WARNING");
  }

  function testproject_create() 
  {
    $arg = func_get_args();   // To get all the arguments
    tLog("In TestProject Create with id: " . $arg[1] . ", name: " . $arg[2] . ", prefix: " . $arg[3], "WARNING");
  }

  function testproject_update() 
  {
    $arg = func_get_args();   // To get all the arguments
    tLog("In TestProject Update with id: " . $arg[1] . ", name: " . $arg[2] . ", prefix: " . $arg[3], "WARNING");
  }

  function testExecute() {
    $arg = func_get_args();   // To get all the arguments
    tLog("In TestRun with planid: " . $arg[1] . ", buildid: " . $arg[2] . ", testcaseid: " . $arg[3] . ", Notes: " . $arg[4] . ", Status: " . $arg[5], "WARNING");
  }

  function bottom_link()
  {
    return "<a href=''>".plugin_lang_get('left_bottom_link')."</a>";
  }

  function top_link()
  {
    return "<a href='" . plugin_page('config.php') . "'>".plugin_lang_get('config')."</a>";
  }

  function right_top_link()
  {
    return "<a href=''>".plugin_lang_get('right_top_link')."</a>";
  }

  function right_bottom_link()
  {
    return "<a href=''>".plugin_lang_get('right_bottom_link')."</a>";
  }

}
