<?php

/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource  events_inc.php
 * @package     TestLink
 * @copyright   2015-2016, TestLink community
 * @link        http://www.testlink.org/
 *
 **/

// Declare supported plugin events.
event_declare_many(array(
  // Test Project related events
  'EVENT_TEST_PROJECT_CREATE' => EVENT_TYPE_CREATE,
  'EVENT_TEST_PROJECT_UPDATE' => EVENT_TYPE_UPDATE,

  // Test Suite related events.
  'EVENT_TEST_SUITE_CREATE' => EVENT_TYPE_CREATE,
  'EVENT_TEST_SUITE_UPDATE' => EVENT_TYPE_UPDATE,
  'EVENT_TEST_SUITE_DELETE' => EVENT_TYPE_DELETE,

  // Test Case related events.
  'EVENT_TEST_CASE_CREATE' => EVENT_TYPE_CREATE,
  'EVENT_TEST_CASE_UPDATE' => EVENT_TYPE_UPDATE,
  'EVENT_TEST_CASE_DELETE' => EVENT_TYPE_DELETE,

  // Test Event related events
  'EVENT_EXECUTE_TEST' => EVENT_TYPE_CREATE,

  // UI Related Elements,
  'EVENT_TITLE_BAR' => EVENT_TYPE_OUTPUT,
  'EVENT_LEFTMENU_TOP' => EVENT_TYPE_OUTPUT,
  'EVENT_LEFTMENU_BOTTOM' => EVENT_TYPE_OUTPUT,
  'EVENT_RIGHTMENU_TOP' => EVENT_TYPE_OUTPUT,
  'EVENT_RIGHTMENU_BOTTOM' => EVENT_TYPE_OUTPUT,
  'EVENT_TESTRUN_DISPLAY' => EVENT_TYPE_OUTPUT,
));


?>
