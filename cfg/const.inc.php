<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: const.inc.php,v $
 *
 * @version $Revision: 1.21 $
 * @modified $Date: 2007/04/02 07:02:59 $ by $Author: franciscom $
 * @author Martin Havlát
 *
 * SCOPE:
 * Global Constants used throughout TestLink 
 * Script is included via config.inc.php
 * There should be changed for your environment
 * 
 *-------------------------------------------------------------------
 * Revisions:
 *
 *-------------------------------------------------------------------
**/

// ----------------------------------------------------------------------------
/** [GLOBAL] */

/** Directory separator */
define('DS', DIRECTORY_SEPARATOR);

/** set the delimeter properly for the include_path */
define('DELIM', (PHP_OS == "WIN32" || PHP_OS == "WINNT") ? ';' : ':');

/** The temporary dir for temporary files */
define('TL_TEMP_PATH', TL_ABS_PATH . 'gui'.DS.'templates_c'.DS);



// ----------------------------------------------------------------------------
/** [GUI] */

/* Release MUST BE changed at the release day */
define('TL_VERSION', '1.7.0 - RC1'); 
define('TL_BACKGROUND_DEFAULT', "#9BD"); // default color

// planAddTC_m1-tpl
define('TL_STYLE_FOR_ADDED_TC', "background-color:yellow;");


define('TL_COOKIE_KEEPTIME', (time()+60*60*24*30)); // 30 days

/* Some defines for I18N,L10N, don't touch */
define('TL_LOCALE_PATH',TL_ABS_PATH . 'locale/');
define('TL_HELP_RPATH','gui/help/');
define('TL_INSTRUCTIONS_RPATH','gui/help/');

// Configurable templates this can help if you want to use a non standard template.
// i.e. you want to develop a new one without loosing the original template.
// 
$g_tpl = array(
	'tcView' 		=> "tcView.tpl",
	'tcSearchView' 	=> "tcSearchView.tpl",
	'tcEdit' 		=> "tcEdit.tpl",
	'tcNew' 		=> "tcNew.tpl",
	'execSetResults' => "execSetResults.tpl",
	'tcView' 		=> "tcView.tpl",
	'tcSearchView' 	=> "tcView.tpl",
	'usersview' 	=> "usersview.tpl"
);



// -------------------------------------------------------------------
/** [LDAP authentication errors */
// 
// Based on mantis issue tracking system code
// ERROR_LDAP_*
define( 'ERROR_LDAP_AUTH_FAILED',				1400 );
define( 'ERROR_LDAP_SERVER_CONNECT_FAILED',		1401 );
define( 'ERROR_LDAP_UPDATE_FAILED',				1402 );
define( 'ERROR_LDAP_USER_NOT_FOUND',			1403 );
define( 'ERROR_LDAP_BIND_FAILED',				1404 );



// ----------------------------------------------------------------------------
/** [LOCALIZATION] */

// These are the supported locales.
// This array will be used to create combo box at user interface.
// Please mantain the alphabetical order when adding new locales.
// Attention:
//           The locale selected by default in the combo box when
//           creating a new user WILL BE fixed by the value of the default locale,
//           NOT by the order of the elements in this array.
//
$g_locales = array(	
	'zh_CN' => 'Chinese Simplified',
	'en_GB' => 'English (UK)',
	'en_US' => 'English (US)',
	'fr_FR' => 'Fran&ccedil;ais',
	'de_DE' => 'German',
	'it_IT' => 'Italian',
	'pl_PL' => 'Polski',
	'pt_BR' => 'Portuguese (Brazil)',
	'es_AR' => 'Spanish (Argentine)',
	'es_ES' => 'Spanish'
);

// see strftime() in PHP manual
// Very IMPORTANT: 
// setting according local is done in testlinkInitPage() using set_dt_formats()
// Default values
$g_date_format ="%d/%m/%Y";
$g_timestamp_format = "%d/%m/%Y %H:%M:%S";

$g_locales_date_format = array(
	'en_GB' => "%d/%m/%Y",
	'en_US' => "%m/%d/%Y",
	'it_IT' => "%d/%m/%Y",
	'es_AR' => "%d/%m/%Y",
	'es_ES' => "%d/%m/%Y",
	'de_DE' => "%d.%m.%Y",
	'pl_PL' => "%d.%m.%Y",
	'fr_FR' => "%d/%m/%Y",
	'pt_BR' => "%d/%m/%Y",
	'zh_CN' => "%Y��%m��%d��"
); 

$g_locales_timestamp_format = array(
	'en_GB' => "%d/%m/%Y %H:%M:%S",
	'en_US' => "%m/%d/%Y %H:%M:%S",
	'it_IT' => "%d/%m/%Y %H:%M:%S",
	'es_AR' => "%d/%m/%Y %H:%M:%S",
	'es_ES' => "%d/%m/%Y %H:%M:%S",
	'de_DE' => "%d.%m.%Y %H:%M:%S",
	'pl_PL' => "%d.%m.%Y %H:%M:%S",
	'fr_FR' => "%d/%m/%Y %H:%M:%S",
	'pt_BR' => "%d/%m/%Y %H:%M:%S",
	'zh_CN' => "%Y��%m��%d�� %Hʱ%M��%S��"
); 

// -------------------------------------------------------------------
/** ATTACHMENTS */

/* some attachment related defines, no need to modify them */
define("TL_REPOSITORY_TYPE_DB",1);
define("TL_REPOSITORY_TYPE_FS",2);

define("TL_REPOSITORY_COMPRESSIONTYPE_NONE",1);
define("TL_REPOSITORY_COMPRESSIONTYPE_GZIP",2);


// Two models to manage attachment interface in the execution screen
// $att_model_m1 ->  shows upload button and title 
//
$att_model_m1->show_upload_btn = true;
$att_model_m1->show_title = true;
$att_model_m1->num_cols = 4;
$att_model_m1->show_upload_column = false;

// $att_model_m2 ->  hides upload button and title
// 
$att_model_m2->show_upload_btn = false;
$att_model_m2->show_title = false;
$att_model_m2->num_cols = 5;
$att_model_m2->show_upload_column = true;


// -------------------------------------------------------------------
/** [MISC] */

// These are the possible Test Case statuses
// See also $g_tc_status_for_ui
//
$g_tc_status = array (
	"failed"        => 'f',
	"blocked"       => 'b',
	"passed"        => 'p',
	"not_run"       => 'n',
	"not_available" => 'x',
	"unknown"       => 'u',
	"all"           => 'all'
); 

// Please if you add an status you need to add a corresponding CSS Class
// in the CSS files (see the gui directory)
$g_tc_status_css = array_flip($g_tc_status);


// Used to generate radio and buttons at user interface level.
// Order is important, because this will be display order on User Interface
//
// key   => verbose status as defined in $g_tc_status
// value => string id defined in the strings.txt file, 
//          used to localize the strings.
//
// $g_tc_status_for_ui = array(
// 	"not_run" => "test_status_not_run",
// 	"passed"  => "test_status_passed",
// 	"failed"  => "test_status_failed",
// 	"blocked" => "test_status_blocked"
// );

$g_tc_status_for_ui = array(
	"passed"  => "test_status_passed",
	"failed"  => "test_status_failed",
	"blocked" => "test_status_blocked"
);

// radio button selected by default
$g_tc_status_for_ui_default="blocked";



/*
$g_tc_status_for_ui = array(
	"not_run" => "test_status_not_run",
	"not_available" => "test_status_not_available",
	"passed"  => "test_status_passed",
	"failed"  => "test_status_failed",
	"blocked" => "test_status_blocked"
);
*/

$g_tc_status_verbose_labels = array(
  "all"      => "test_status_all_status",
	"not_run"  => "test_status_not_run",
	"passed"   => "test_status_passed",
	"failed"   => "test_status_failed",
	"blocked"  => "test_status_blocked"
);



define("TL_ROLES_GUEST",5);
define("TL_ROLES_NONE",3);
define("TL_ROLES_NONE_DESC","<no rights>");
define("TL_ROLES_UNDEFINED",0);
define("TL_ROLES_UNDEFINED_DESC","<inherited>");
define("TL_DEFAULT_ROLEID",7);

// used on User Interface whiel showing roles
define("TL_ROLES_OPEN_CHAR","[");
define("TL_ROLES_CLOSE_CHAR","]");

// used to mark up inactive objects (test projects, etc)
define("TL_INACTIVE_MARKUP","* ");


// used on user management page to give different colour 
// to different roles.
// If you don't want use colouring then configure in this way
// $g_role_colour = array ( );
//
$g_role_colour = array ( 
	"admin"         => 'white',
	"tester"        => 'wheat',
	'leader'        => 'acqua',
	'senior tester' => '#FFA',
	'guest'         => 'pink',
	'test designer' => 'cyan',
	'<no rights>'   => 'salmon',
	'<inherited>'   => 'seashell' 
);


$g_tc_risks = array('L1', 'L2', 'L3','M1', 'M2', 'M3','H1', 'H2', 'H3');

// 
// [FUNCTION MAGIC NUMBERS] [DON'T BOTHER ABOUT]
// used in several functions instead of MAGIC NUMBERS - Don't change 
define('ALL_PRODUCTS',0);
define('TP_ALL_STATUS',null);
define('FILTER_BY_PRODUCT',1);
define('FILTER_BY_TESTPROJECT',FILTER_BY_PRODUCT);
define('TP_STATUS_ACTIVE',1);
define('NON_TESTABLE_REQ','n');
define('VALID_REQ','v');

define('DSN',FALSE);  // for method connect() of database.class
define('ANY_BUILD',null);
define('GET_NO_EXEC',1);


define('ACTIVE',1);
define('INACTIVE',0);
define('OPEN',1);
define('CLOSED',0);

// moved from testSetNavigator.php
define('FILTER_BY_BUILD_OFF',0);
define('FILTER_BY_OWNER_OFF',0);
define('FILTER_BY_TC_STATUS_OFF',null);

// moved from testSetRemove.php
define('WRITE_BUTTON_ONLY_IF_LINKED',1);

// moved from tc_exec_assignment.php
define('FILTER_BY_TC_OFF',null); 
define('ALL_USERS_FILTER',null); 
define('ADD_BLANK_OPTION',true); 

// moved from requirements.inc.php
define('TL_REQ_STATUS_VALID', 'V');
define('TL_REQ_STATUS_NOT_TESTABLE', 'N');


define('DO_LANG_GET',1);
define('DONT_DO_LANG_GET',0);

// 
define('FILTER_BY_SHOW_ON_EXECUTION',1);

define('GET_ALSO_NOT_EXECUTED',null);
define('GET_ONLY_EXECUTED','executed');

// generateTestSpecTree()
define('HIDE_TESTCASES',1);
define('SHOW_TESTCASES',0);
define('FILTER_INACTIVE_TESTCASES',1);
define('DO_NOT_FILTER_INACTIVE_TESTCASES',0);

define('DO_ON_TESTCASE_CLICK',1);
define('NO_ADDITIONAL_ARGS','');
define('NO_KEYWORD_ID_TO_FILTER',0);


define('RECURSIVE_MODE',TRUE);
define('NO_NODE_TYPE_TO_FILTER',null);
define('ANY_OWNER',null);
// -------------------------------------------------------------------
?>