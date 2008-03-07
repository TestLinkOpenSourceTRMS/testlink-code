<?php
/*
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * $Id: APIErrors.php,v 1.3 2008/03/07 22:51:15 franciscom Exp $
 */

/** 
 * Error codes for the TestlinkXMLRPCServer
 * 
 * @author 		Asiel Brumfield <asielb@users.sourceforge.net>
 * @package 	TestlinkAPI
 * @link        http://testlink.org/api/
 */
 
 /**
  * general config file gives us lang_get access
  */
require_once(dirname(__FILE__) . "/../../config.inc.php");

/**#@+
 * Constants
 */
/**
 * a catch all generic error
 */
define('GENERAL_ERROR_CODE', -1);

define('GENERAL_SUCCESS_CODE', 1);
define('GENERAL_SUCCESS_STR', lang_get('API_GENERAL_SUCCESS'));

define('NOT_YET_IMPLEMENTED', 50);
define('NOT_YET_IMPLEMENTED_STR', lang_get('API_NOT_YET_IMPLEMENTED'));
/**
 * Error codes below 1000 are system level
 */
define('NO_DEV_KEY', 100);
define('NO_DEV_KEY_STR', lang_get('API_NO_DEV_KEY'));

define('NO_TCASEID', 110);
define('NO_TCASEID_STR', lang_get('API_NO_TCASEID'));

define('NO_TCASEEXTERNALID', 110);
define('NO_TCASEEXTERNALID_STR', lang_get('API_NO_TCASEEXTERNALID'));


define('NO_TPLANID', 120);
define('NO_TPLANID_STR', lang_get('API_NO_TPLANID'));

define('NO_BUILDID', 130);
define('NO_BUILDID_STR', lang_get('API_NO_BUILDID'));

define('NO_TEST_MODE', 140);
define('NO_TEST_MODE_STR', lang_get('API_NO_TEST_MODE'));

define('NO_STATUS', 150);
define('NO_STATUS_STR', lang_get('API_NO_STATUS'));

define('NO_TESTPROJECTID', 160);
define('NO_TESTPROJECTID_STR', lang_get('API_NO_TESTPROJECTID'));

define('NO_TESTCASENAME', 170);
define('NO_TESTCASENAME_STR', lang_get('API_NO_TESTCASENAME'));

define('NO_TESTSUITEID', 180);
define('NO_TESTSUITEID_STR', lang_get('API_NO_TESTSUITEID'));




/**
 * 2000 level - authentication errors
 */
define('INVALID_AUTH', 2000);
define('INVALID_AUTH_STR', lang_get('API_INVALID_AUTH'));

/**
 * 3000 level - Test Plan errors
 */
define('INVALID_TPLANID', 3000);
define('INVALID_TPLANID_STR', lang_get('API_INVALID_TPLANID'));
define('TPLANID_NOT_INTEGER', 3010);
define('TPLANID_NOT_INTEGER_STR', lang_get('API_TPLANID_NOT_INTEGER'));
define('NO_BUILD_FOR_TPLANID', 3020);
define('NO_BUILD_FOR_TPLANID_STR', lang_get('API_NO_BUILD_FOR_TPLANID'));
define('TCASEID_NOT_IN_TPLANID', 3030);
define('TCASEID_NOT_IN_TPLANID_STR', lang_get('API_TCASEID_NOT_IN_TPLANID'));

/**
 * 4000 level - Build errors
 */
define('INVALID_BUILDID', 4000);
define('INVALID_BUILDID_STR', lang_get('API_INVALID_BUILDID'));
define('BUILDID_NOT_INTEGER', 4010);
define('BUILDID_NOT_INTEGER_STR', lang_get('API_BUILDID_NOT_INTEGER'));
define('BUILDID_NOGUESS', 4020);
define('BUILDID_NOGUESS_STR', lang_get('API_BUILDID_NOGUESS'));


/**
 * 5000 level - Test Case errors
 */
define('INVALID_TCASEID', 5000);
define('INVALID_TCASEID_STR' , lang_get('API_INVALID_TCASEID'));
define('TCASEID_NOT_INTEGER', 5010);
define('TCASEID_NOT_INTEGER_STR', lang_get('API_TCASEID_NOT_INTEGER'));
define('TESTCASENAME_NOT_STRING', 5020);
define('TESTCASENAME_NOT_STRING_STR', lang_get('API_TESTCASENAME_NOT_STRING'));
define('NO_TESTCASE_BY_THIS_NAME', 5030);
define('NO_TESTCASE_BY_THIS_NAME_STR', lang_get('API_NO_TESTCASE_BY_THIS_NAME'));
define('INVALID_TESTCASE_EXTERNAL_ID', 5040);
define('INVALID_TESTCASE_EXTERNAL_ID_STR', lang_get('API_INVALID_TESTCASE_EXTERNAL_ID'));



/**
 * 6000 level - Status errors
 */
define('INVALID_STATUS', 6000);
define('INVALID_STATUS_STR' , lang_get('API_INVALID_STATUS'));

/**
 * 7000 level - Test Project errors
 */
define('INVALID_TESTPROJECTID', 7000);
define('INVALID_TESTPROJECTID_STR' , lang_get('API_INVALID_TESTPROJECTID'));

/**
 * 8000 level - Test Suite errors
 */
define('INVALID_TESTSUITEID', 8000);
define('INVALID_TESTSUITEID_STR', lang_get('API_INVALID_TESTSUITEID'));

?>
