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
      'EVENT_RIGHTMENU_BOTTOM' => 'right_bottom_link',
      'EVENT_TESTRUN_DISPLAY' => 'testrun_display_block'
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
    tLog("In TestRun with testrunid: " . $arg[1] . ", planid: " . $arg[2] . ", buildid: " . $arg[3] . ", testcaseid: " . $arg[4] . ", Notes: " . $arg[5] . ", Status: " . $arg[6], "WARNING");
  }

  function testrun_display_block() {
    $args = func_get_args();
    // $args details: $arg[1] -> Testplan Id, $arg[2] -> Build Id, $arg[3] ->TestCase Id, $arg[4] -> TestCase Version Id
    return '<img src="http://www.testingexcellence.com/wp-content/uploads/2010/01/testlink-open-source-test-management-tool.jpg" />';
  }

  function bottom_link()
  {
	$tLink['href'] = '';
	$tLink['label'] = plugin_lang_get('left_bottom_link');
    return $tLink;
  }

  function top_link()
  {
	$tLink['href'] = plugin_page('config.php');
	$tLink['label'] = plugin_lang_get('config');
    return $tLink;
  }

  function right_top_link()
  {
	$tLink['href'] = '';
	$tLink['label'] = plugin_lang_get('right_top_link');
    return $tLink;
  }

  function right_bottom_link()
  {
	$tLink['href'] = '';
	$tLink['label'] = plugin_lang_get('right_bottom_link');
    return $tLink;
  }

}
