<?php
/*
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * $Id: TestlinkXMLRPCServerErrors.php,v 1.2 2007/11/25 18:56:18 franciscom Exp $
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
require_once(dirname(__FILE__) . "/../config.inc.php");

/**#@+
 * Constants
 */
/**
 * a catch all generic error
 */
define('GENERAL_ERROR_CODE', -1);

define('GENERAL_SUCCESS_CODE', 1);
define('GENERAL_SUCCESS_STR', lang_get('API_GENERAL_SUCCESS'));

/**
 * Error codes below 1000 are system level
 */
define('NO_DEV_KEY', 100);
define('NO_DEV_KEY_STR', lang_get('API_NO_DEV_KEY'));

define('NO_TCID', 110);
define('NO_TCID_STR', lang_get('API_NO_TCID'));

define('NO_TPLANID', 120);
define('NO_TPLANID_STR', lang_get('API_NO_TPLANID'));

define('NO_BUILDID', 130);
define('NO_BUILDID_STR', lang_get('API_NO_BUILDID'));

define('NO_TEST_MODE', 140);
define('NO_TEST_MODE_STR', lang_get('API_NO_TEST_MODE'));

define('NO_STATUS', 150);
define('NO_STATUS_STR', lang_get('API_NO_STATUS'));

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
define('TPLANID_NOT_INTEGER_STR', lang_get('API_TPID_NOT_INTEGER'));
define('NO_BUILD_FOR_TPLANID', 3020);
define('NO_BUILD_FOR_TPLANID_STR', lang_get('API_NO_BUILD_FOR_TPLANID'));
define('TCID_NOT_IN_TPLANID', 3030);
define('TCID_NOT_IN_TPLANID_STR', lang_get('API_TCID_NOT_IN_TPLANID'));

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
define('INVALID_TCID', 5000);
define('INVALID_TCID_STR' , lang_get('API_INVALID_TCID'));
define('TCID_NOT_INTEGER', 5010);
define('TCID_NOT_INTEGER_STR', lang_get('API_TCID_NOT_INTEGER'));

/**
 * 6000 level - Status errors
 */
define('INVALID_STATUS', 6000);
define('INVALID_STATUS_STR' , lang_get('API_INVALID_STATUS'));

?>
