<?php
/*
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * $Id: APIErrors.php,v 1.16 2009/03/05 07:32:36 franciscom Exp $
 */

/** 
 * Error codes for the TestlinkXMLRPCServer
 * 
 * @author 		Asiel Brumfield <asielb@users.sourceforge.net>
 * @package 	TestlinkAPI
 * @link      http://testlink.org/api/
 *
 * rev: 20090304 - BUGID 2191 - franciscom 
 *
 *      20080518 - franciscom - TestLink Development team - www.teamst.org
 *      suppress log for missing localization strings.
 */
 
 /**
  * general config file gives us lang_get access
  */
require_once(dirname(__FILE__) . "/../../config.inc.php");
require_once dirname(__FILE__) . '/../functions/lang_api.php';

/**#@+
 * Constants
 */


/**
 * a catch all generic error
 */
define('GENERAL_ERROR_CODE', -1);

define('GENERAL_SUCCESS_CODE', 1);

// IMPORTANT:
//           lang_get('API_GENERAL_SUCCESS',null,1)
//           null -> use user locale
//           1 -> do not log on audit system if localized string do not exist
//
define('GENERAL_SUCCESS_STR', lang_get('API_GENERAL_SUCCESS',null,1));

define('NOT_YET_IMPLEMENTED', 50);
define('NOT_YET_IMPLEMENTED_STR', lang_get('API_NOT_YET_IMPLEMENTED',null,1));
/**
 * Error codes below 1000 are system level
 */
define('NO_DEV_KEY', 100);
define('NO_DEV_KEY_STR', lang_get('API_NO_DEV_KEY',null,1));

define('NO_TCASEID', 110);
define('NO_TCASEID_STR', lang_get('API_NO_TCASEID',null,1));

define('NO_TCASEEXTERNALID', 110);
define('NO_TCASEEXTERNALID_STR', lang_get('API_NO_TCASEEXTERNALID',null,1));


define('NO_TPLANID', 120);
define('NO_TPLANID_STR', lang_get('API_NO_TPLANID',null,1));

define('NO_BUILDID', 130);
define('NO_BUILDID_STR', lang_get('API_NO_BUILDID',null,1));

define('NO_TEST_MODE', 140);
define('NO_TEST_MODE_STR', lang_get('API_NO_TEST_MODE',null,1));

define('NO_STATUS', 150);
define('NO_STATUS_STR', lang_get('API_NO_STATUS',null,1));

define('NO_TESTPROJECTID', 160);
define('NO_TESTPROJECTID_STR', lang_get('API_NO_TESTPROJECTID',null,1));

define('NO_TESTCASENAME', 170);
define('NO_TESTCASENAME_STR', lang_get('API_NO_TESTCASENAME',null,1));

define('NO_TESTSUITEID', 180);
define('NO_TESTSUITEID_STR', lang_get('API_NO_TESTSUITEID',null,1));

define('MISSING_REQUIRED_PARAMETER', 200);
define('MISSING_REQUIRED_PARAMETER_STR', lang_get('API_MISSING_REQUIRED_PARAMETER',null,1));

define('PARAMETER_NOT_INT',210);
define('PARAMETER_NOT_INT_STR', lang_get('API_PARAMETER_NOT_INT',null,1));


/**
 * 2000 level - authentication errors
 */
define('INVALID_AUTH', 2000);
define('INVALID_AUTH_STR', lang_get('API_INVALID_AUTH',null,1));
define('INSUFFICIENT_RIGHTS', 2010);
define('INSUFFICIENT_RIGHTS_STR', lang_get('INSUFFICIENT_RIGHTS',null,1));


/**
 * 3000 level - Test Plan errors
 */
define('INVALID_TPLANID', 3000);
define('INVALID_TPLANID_STR', lang_get('API_INVALID_TPLANID',null,1));
define('TPLANID_NOT_INTEGER', 3010);
define('TPLANID_NOT_INTEGER_STR', lang_get('API_TPLANID_NOT_INTEGER',null,1));
define('NO_BUILD_FOR_TPLANID', 3020);
define('NO_BUILD_FOR_TPLANID_STR', lang_get('API_NO_BUILD_FOR_TPLANID',null,1));
define('TCASEID_NOT_IN_TPLANID', 3030);
define('TCASEID_NOT_IN_TPLANID_STR', lang_get('API_TCASEID_NOT_IN_TPLANID',null,1));

define('TPLAN_HAS_NO_BUILDS',3031);
define('TPLAN_HAS_NO_BUILDS_STR', lang_get('API_TPLAN_HAS_NO_BUILDS',null,1));

define('BAD_BUILD_FOR_TPLAN', 3032);
define('BAD_BUILD_FOR_TPLAN_STR', lang_get('API_BAD_BUILD_FOR_TPLAN',null,1));


/**
 * 4000 level - Build errors
 */
define('INVALID_BUILDID', 4000);
define('INVALID_BUILDID_STR', lang_get('API_INVALID_BUILDID',null,1));
define('BUILDID_NOT_INTEGER', 4010);
define('BUILDID_NOT_INTEGER_STR', lang_get('API_BUILDID_NOT_INTEGER',null,1));
define('BUILDID_NOGUESS', 4020);
define('BUILDID_NOGUESS_STR', lang_get('API_BUILDID_NOGUESS',null,1));


/**
 * 5000 level - Test Case errors
 */
define('INVALID_TCASEID', 5000);
define('INVALID_TCASEID_STR' , lang_get('API_INVALID_TCASEID',null,1));
define('TCASEID_NOT_INTEGER', 5010);
define('TCASEID_NOT_INTEGER_STR', lang_get('API_TCASEID_NOT_INTEGER',null,1));
define('TESTCASENAME_NOT_STRING', 5020);
define('TESTCASENAME_NOT_STRING_STR', lang_get('API_TESTCASENAME_NOT_STRING',null,1));
define('NO_TESTCASE_BY_THIS_NAME', 5030);
define('NO_TESTCASE_BY_THIS_NAME_STR', lang_get('API_NO_TESTCASE_BY_THIS_NAME',null,1));
define('INVALID_TESTCASE_EXTERNAL_ID', 5040);
define('INVALID_TESTCASE_EXTERNAL_ID_STR', lang_get('API_INVALID_TESTCASE_EXTERNAL_ID',null,1));
define('INVALID_TESTCASE_VERSION_NUMBER', 5050);
define('INVALID_TESTCASE_VERSION_NUMBER_STR', lang_get('API_INVALID_TESTCASE_VERSION_NUMBER',null,1));
define('TCASE_VERSION_NUMBER_KO',5051);
define('TCASE_VERSION_NUMBER_KO_STR', lang_get('API_TCASE_VERSION_NUMBER_KO',null,1));

define('VERSION_NOT_VALID',5052);
define('VERSION_NOT_VALID_STR', lang_get('API_VERSION_NOT_VALID',null,1));

/**
 * 6000 level - Status errors
 */
define('INVALID_STATUS', 6000);
define('INVALID_STATUS_STR' , lang_get('API_INVALID_STATUS',null,1));

/**
 * 7000 level - Test Project errors
 */
define('INVALID_TESTPROJECTID', 7000);
define('INVALID_TESTPROJECTID_STR' , lang_get('API_INVALID_TESTPROJECTID',null,1));

define('TESTPROJECTNAME_SINTAX_ERROR', 7001);
define('TESTPROJECTNAME_EXISTS', 7002);
define('TESTPROJECT_TESTCASEPREFIX_EXISTS', 7003);
define('TESTPROJECT_TESTCASEPREFIX_IS_EMPTY', 7004);
define('TESTPROJECT_TESTCASEPREFIX_IS_TOO_LONG', 7005);

define('TPLAN_TPROJECT_KO',7006);
define('TPLAN_TPROJECT_KO_STR',lang_get('API_TPLAN_TPROJECT_KO',null,1));

define('TCASE_TPROJECT_KO',7007);
define('TCASE_TPROJECT_KO_STR',lang_get('API_TCASE_TPROJECT_KO',null,1));

define('TPROJECT_IS_EMPTY',7008);
define('TPROJECT_IS_EMPTY_STR',lang_get('API_TPROJECT_IS_EMPTY',null,1));

define('TPROJECT_PREFIX_ALREADY_EXISTS',7009);
define('TPROJECT_PREFIX_ALREADY_EXISTS_STR',
       lang_get('API_TPROJECT_PREFIX_ALREADY_EXISTS',null,1));

define('REQSPEC_TPROJECT_KO',7010);
define('REQSPEC_TPROJECT_KO_STR',lang_get('API_REQSPEC_TPROJECT_KO',null,1));


/**
 * 8000 level - Test Suite errors
 */
define('INVALID_TESTSUITEID', 8000);
define('INVALID_TESTSUITEID_STR', lang_get('API_INVALID_TESTSUITEID',null,1));

/**
 * 9000 level - Custom Fields
 */
define('NO_CUSTOMFIELD_BY_THIS_NAME', 9000);
define('NO_CUSTOMFIELD_BY_THIS_NAME_STR', lang_get('API_NO_CUSTOMFIELD_BY_THIS_NAME',null,1));

define('CUSTOMFIELD_NOT_APP_FOR_NODE_TYPE',9001);
define('CUSTOMFIELD_NOT_APP_FOR_NODE_TYPE_STR', lang_get('API_CUSTOMFIELD_NOT_APP_FOR_NODE_TYPE',null,1));


/**
 * 10000 level - User
 */
define('NO_USER_BY_THIS_LOGIN', 10000);
define('NO_USER_BY_THIS_LOGIN_STR', lang_get('API_NO_USER_BY_THIS_LOGIN',null,1));


/**
 * 11000 level - Requirements
 */
define('REQSPEC_KO', 11000);
define('REQSPEC_KO_STR', lang_get('API_REQSPEC_KO',null,1));

define('REQSPEC_IS_EMPTY', 11001);
define('REQSPEC_IS_EMPTY_STR', lang_get('API_REQSPEC_IS_EMPTY',null,1));

define('REQ_REQSPEC_KO', 11002);
define('REQ_REQSPEC_KO_STR', lang_get('API_REQ_REQSPEC_KO',null,1));

define('REQ_KO', 11003);
define('REQ_KO_STR', lang_get('API_REQ_KO',null,1));





?>
