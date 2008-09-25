<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: const.inc.php,v $
 *
 * @version $Revision: 1.83 $
 * @modified $Date: 2008/09/25 20:20:29 $ by $Author: franciscom $
 * @author Martin Havlat
 *
 * SCOPE:
 * Global Constants used throughout TestLink 
 * The script is included via config.inc.php
 * 
 * 
 * No revisions logged here but each parameter must be described!
 *
 * ----------------------------------------------------------------------------------- */

/** [GLOBAL SETTINGS] */

/** TestLink Release (MUST BE changed before the release day) */
define('TL_VERSION', '1.8.0 DEV FALL 2008'); 

// needed to avoid problems in install scripts that do not include config.inc.php
// want to point to root install dir, need to remove fixed part
if (!defined('TL_ABS_PATH')) 
    define('TL_ABS_PATH', str_replace('cfg','',dirname(__FILE__)));

/** Setting up the global include path for testlink */
ini_set('include_path',ini_get('include_path') . PATH_SEPARATOR . 
        '.' . PATH_SEPARATOR . TL_ABS_PATH . 'lib' . DIRECTORY_SEPARATOR . 'functions' . DIRECTORY_SEPARATOR);

/** Other TestLink file paths */
define('TL_LOCALE_PATH', TL_ABS_PATH . 'locale/');



// --------------------------------------------------------------------------------------
/** [GENERAL MAGIC NUMBERS] */

// Basicly true/false
define('ENABLED', 	1 );
define('DISABLED', 	0 );
define('ON',		1 );
define('OFF',		0 );
define('ACTIVE',	1 );
define('INACTIVE',	0 );
define('OPEN',		1 );
define('CLOSED',	0 );
define('OK',		1 );
define('ERROR',		0 );
define('HIGH',		3 );
define('MEDIUM', 	2 );
define('LOW', 		1 );


// used in several functions instead of MAGIC NUMBERS - Don't change 
define('ALL_PRODUCTS', 0);
define('TP_ALL_STATUS', null);
define('FILTER_BY_PRODUCT', 1);
define('FILTER_BY_TESTPROJECT', FILTER_BY_PRODUCT);
define('TP_STATUS_ACTIVE', 1);

define('DO_LANG_GET',1 );
define('DONT_DO_LANG_GET',0 );

define('DSN', FALSE);  // for method connect() of database.class
define('ANY_BUILD', null);
define('GET_NO_EXEC', 1);

// planTCNavigator.php
define('FILTER_BY_BUILD_OFF', 0);
define('FILTER_BY_OWNER_OFF', 0);
define('FILTER_BY_TC_STATUS_OFF', null);
define('FILTER_BY_KEYWORD_OFF', null);
define('FILTER_BY_ASSIGNED_TO_OFF', 0);
define('SEARCH_BY_CUSTOM_FIELDS_OFF', null);
define('COLOR_BY_TC_STATUS_OFF', 0);
define('CREATE_TC_STATUS_COUNTERS_OFF', 0);

// moved from testSetRemove.php
define('WRITE_BUTTON_ONLY_IF_LINKED', 1);

// moved from tc_exec_assignment.php
define('FILTER_BY_TC_OFF', null); 
define('FILTER_BY_EXECUTE_STATUS_OFF', null); 
define('ALL_USERS_FILTER', null); 
define('ADD_BLANK_OPTION', true); 

// 
define('FILTER_BY_SHOW_ON_EXECUTION', 1);

define('GET_ALSO_NOT_EXECUTED', null);
define('GET_ONLY_EXECUTED', 'executed');

// generateTestSpecTree()
define('FOR_PRINTING', 1);
define('NOT_FOR_PRINTING', 0);

define('HIDE_TESTCASES', 1);
define('SHOW_TESTCASES', 0);
define('FILTER_INACTIVE_TESTCASES', 1);
define('DO_NOT_FILTER_INACTIVE_TESTCASES', 0);

define('ACTION_TESTCASE_DISABLE', 0);
define('IGNORE_INACTIVE_TESTCASES', 1);

define('DO_ON_TESTCASE_CLICK', 1);
define('NO_ADDITIONAL_ARGS', '');
define('NO_KEYWORD_ID_TO_FILTER', 0);


define('RECURSIVE_MODE', TRUE);
define('NO_NODE_TYPE_TO_FILTER', null);
define('ANY_OWNER', null);

define('ALL_BUILDS', 'a');
define('ALL_TEST_SUITES', 'all');

define('GET_ACTIVE_BUILD', 1);
define('GET_INACTIVE_BUILD', 0);
define('GET_OPEN_BUILD', 1);
define('GET_CLOSED_BUILD', 0);

define('AUTOMATIC_ID', 0);
define('NO_FILTER_SHOW_ON_EXEC', null);
define('DONT_REFRESH', 'no');
define('DEFAULT_TC_ORDER', 0);

// bug_interface->buildViewBugLink()
define('GET_BUG_SUMMARY', true);

// gen_spec_view()
define('DO_PRUNE', 1);

// executeTestCase()
define('AUTOMATION_RESULT_KO', -1);
define('AUTOMATION_NOTES_KO', -1);

// testcase.class.php
define('TESTCASE_EXECUTION_TYPE_MANUAL', 1);
define('TESTCASE_EXECUTION_TYPE_AUTO', 2);



// --------------------------------------------------------------------------------------
/** [GUI] */

// havlatm: @todo remove (must be solved via css)
// planAddTC_m1-tpl
define('TL_STYLE_FOR_ADDED_TC', "background-color:yellow;");

/** default filenames of CSS files of current GUI theme */
define('TL_CSS_MAIN', 'testlink.css');
define('TL_CSS_PRINT', 'tl_print.css');
define('TL_CSS_DOCUMENTS', 'tl_documents.css');
define('TL_CSS_TREEMENU', 'tl_treemenu.css');


define('TL_COOKIE_KEEPTIME', (time()+60*60*24*30)); // 30 days

/** Configurable templates this can help if you want to use a non standard template.
 * i.e. you want to develop a new one without loosing the original template.
 */
$g_tpl = array(
	'tcView' 		=> "tcView.tpl",
	'tcSearchView' 	=> "tcSearchView.tpl",
	'tcEdit' 		=> "tcEdit.tpl",
	'tcNew' 		=> "tcNew.tpl",
	// 'execSetResults' => "execSetResults.tpl",
	'tcSearchView' 	=> "tcView.tpl",
	'usersview' 	=> "usersView.tpl"
);

// needed for drap and drop feature
define('TL_DRAG_DROP_DIR', 'gui/drag_and_drop/');
define('TL_DRAG_DROP_JS_DIR', TL_DRAG_DROP_DIR. 'js/');
define('TL_DRAG_DROP_FOLDER_CSS', TL_DRAG_DROP_DIR . 'css/drag-drop-folder-tree.css');
define('TL_DRAG_DROP_CONTEXT_MENU_CSS', TL_DRAG_DROP_DIR . 'css/context-menu.css');



// --------------------------------------------------------------------------------------
/** [LOCALIZATION] */

/** String that will used as prefix, to generate an string when a label to be localized
 * is passed to lang_get() to be translated, by the label is not present in the strings file.
 * The resulting string will be:  TL_LOCALIZE_TAG . label
 * Example:   code specifies the key of string: lang_get('hello') -> shows "LOCALIZE: Hello"
 */
define('TL_LOCALIZE_TAG',"LOCALIZE: ");

/** 
 * List of supported localizations (used in user preferences to choose one)
 **/
// Please mantain the alphabetical order when adding new locales.
$g_locales = array(	
	'cs_CZ' => 'Czech',
	'de_DE' => 'German',
	'en_GB' => 'English (UK)',
	'en_US' => 'English (US)',
	'es_AR' => 'Spanish (Argentine)',
	'es_ES' => 'Spanish',
	'fr_FR' => 'Fran&ccedil;ais',
	'it_IT' => 'Italian',
	'ja_JP' => 'Japanese',
	'pl_PL' => 'Polski',
	'pt_BR' => 'Portuguese (Brazil)',
	'ru_RU' => 'Russian',
	'zh_CN' => 'Chinese Simplified'
);

/** 
 * Format of date - see strftime() in PHP manual
 * NOTE: setting according local is done in testlinkInitPage() using set_dt_formats()
 */

/** Default format of date */
$g_date_format ="%d/%m/%Y";
$g_timestamp_format = "%d/%m/%Y %H:%M:%S";

/** localized format of date */
$g_locales_date_format = array(
	'cs_CZ' => "%d.%m.%Y",
	'de_DE' => "%d.%m.%Y",
	'en_GB' => "%d/%m/%Y",
	'en_US' => "%m/%d/%Y",
	'es_AR' => "%d/%m/%Y",
	'es_ES' => "%d/%m/%Y",
	'fr_FR' => "%d/%m/%Y",
	'it_IT' => "%d/%m/%Y",
	'ja_JP' => "%Y/%m/%d",
	'pl_PL' => "%d.%m.%Y",
	'pt_BR' => "%d/%m/%Y",
	'ru_RU' => "%d/%m/%Y",
	'zh_CN' => "%Y-%m-%d"
); 

/** localized format of full timestamp */
$g_locales_timestamp_format = array(
	'cs_CZ' => "%d.%m.%Y %H:%M:%S",
	'de_DE' => "%d.%m.%Y %H:%M:%S",
	'en_GB' => "%d/%m/%Y %H:%M:%S",
	'en_US' => "%m/%d/%Y %H:%M:%S",
	'es_AR' => "%d/%m/%Y %H:%M:%S",
	'es_ES' => "%d/%m/%Y %H:%M:%S",
	'fr_FR' => "%d/%m/%Y %H:%M:%S",
	'it_IT' => "%d/%m/%Y %H:%M:%S",
	'ja_JP' => "%Y/%m/%d %H:%M:%S",
	'pl_PL' => "%d.%m.%Y %H:%M:%S",
	'pt_BR' => "%d/%m/%Y %H:%M:%S",
	'ru_RU' => "%d/%m/%Y %H:%M:%S",
	'zh_CN' => "%Y-%m-%d %H:%M:%S"
); 

/** localized date format for smarty templates (html_select_date function) */
$g_locales_html_select_date_field_order = array(
	'cs_CZ' => "dmY",
	'de_DE' => "dmY",
	'en_GB' => "dmY",
	'en_US' => "mdY",
	'es_AR' => "dmY",
	'es_ES' => "dmY",
	'fr_FR' => "dmY",
	'it_IT' => "dmY",
	'ja_JP' => "Ymd",
	'pl_PL' => "dmY",
	'pt_BR' => "dmY",
	'ru_RU' => "dmY",
	'zh_CN' => "Ymd"
); 





// --------------------------------------------------------------------------------------
/** ATTACHMENTS */

/** Attachment key constants (do not change) */
define("TL_REPOSITORY_TYPE_DB",1);
define("TL_REPOSITORY_TYPE_FS",2);

define("TL_REPOSITORY_COMPRESSIONTYPE_NONE",1);
define("TL_REPOSITORY_COMPRESSIONTYPE_GZIP",2);


// Two models to manage attachment interface in the execution screen
// $att_model_m1 ->  shows upload button and title 
//
$att_model_m1 = new stdClass();
$att_model_m2 = new stdClass();

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


// --------------------------------------------------------------------------------------
/** [Test execution status] */
/** 
 * Note: do not change existing values (you can enhance arrays of course more into custom_config)
 *           If you add new statuses, please use custom_strings.txt to add your localized strings
 */

/** List of Test Case execution results */
// Note: for GUI use rather lang_get($g_tc_status_verbose_labels["passed"]);        
//
// Do not do localisation here, i.e do not change "passed"
//           with the corresponding word in you national language.
//           These strings ARE NOT USED at User interface level.
//
//           Labels showed to users will be created using lang_get()
//           function, getting key from:
//                                      $g_tc_status_verbose_labels
//           example:
//                   lang_get($g_tc_status_verbose_labels["passed"]);
//
$tlCfg->results['status_code'] = array (
	"failed"        => 'f',
	"blocked"       => 'b',
	"passed"        => 'p',
	"not_run"       => 'n',
	"not_available" => 'x',
	"unknown"       => 'u',
	"all"           => 'a'
); 


/** 
 * Used to get localized string to show to users
 * Order is important, because this will be display order on GUI
 * key: status
 * value: id to use with lang_get() to get the string, from strings.txt (or custom_strings.txt)
 */
$tlCfg->results['status_label'] = array(
	"not_run"  		=> "test_status_not_run",
	"passed"   		=> "test_status_passed",
	"failed"   		=> "test_status_failed",
	"blocked"  		=> "test_status_blocked"
// "all"      		=> "test_status_all_status",
//	"not_available" => "test_status_not_available",
//	"unknown"       => "test_status_unknown"
);

// Is RIGHT to have this DIFFERENT from $tlCfg->results['status_label'],
// because you must choose to not allow some of previous status be available
// on execution page.
// See this as a subset of $tlCfg->results['status_label']
//
// Used to generate radio and buttons at user interface level.
// Order is important, because this will be display order on User Interface
//
// key   => verbose status as defined in $g_tc_status
// value => string id defined in the strings.txt file, 
//          used to localize the strings.
//
$tlCfg->results['status_label_for_exec_ui'] = array(
	"not_run"  		=> "test_status_not_run",
	"passed"  		=> "test_status_passed",
	"failed"  		=> "test_status_failed",
	"blocked" 		=> "test_status_blocked"
);

/** Selected execution result by default. Values is key from $tlCfg->results['status_label'] */
$tlCfg->results['default_status'] = "not_run";


// Status colours for charts - no way to use verbose just RGB
$tlCfg->results['charts']=array();
$tlCfg->results['charts']['status_colour']=array(
  	"not_run"  		=> "000000",
	"passed"   		=> "006400",
	"failed"   		=> "B22222",
	"blocked"  		=> "00008B"
);



// --------------------------------------------------------------------------------------
/** [Reports] */

/** Displayed execution statuses to use on reports (ordered). */
// Note: report generation must be changed to manage new statuses
$tlCfg->reportsCfg=new stdClass();

$tlCfg->reportsCfg->exec_status = array(
    "passed"  => "test_status_passed",
    "failed"  => "test_status_failed",
    "blocked" => "test_status_blocked",
    "not_run" => "test_status_not_run"
);


// Offset in seconds, to substract from current date to create start date on
// reports that have start / end dates
$tlCfg->reportsCfg->start_date_offset = (7*24*60*60); // one week


// --------------------------------------------------------------------------------------
/** [Users & Roles] */
define("TL_ROLES_TESTER", 7);
define("TL_ROLES_GUEST", 5);
define("TL_ROLES_NO_RIGHTS", 3);
define("TL_ROLES_NONE", 3); // obsolete, use TL_ROLES_NO_RIGHTS

define("TL_ROLES_UNDEFINED", 0);
define("TL_ROLES_INHERITED", 0);

// Roles with id > to this role can be deleted from user interface
define("TL_LAST_SYSTEM_ROLE", 9);


// used on user management page to give different colour 
// to different roles.
// If you don't want use colouring then configure in this way
// $g_role_colour = array ( );
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


// --------------------------------------------------------------------------------------
/** [LDAP authentication errors */
 
// Based on mantis issue tracking system code
// ERROR_LDAP_*
define( 'ERROR_LDAP_AUTH_FAILED',				1400 );
define( 'ERROR_LDAP_SERVER_CONNECT_FAILED',		1401 );
define( 'ERROR_LDAP_UPDATE_FAILED',				1402 );
define( 'ERROR_LDAP_USER_NOT_FOUND',			1403 );
define( 'ERROR_LDAP_BIND_FAILED',				1404 );


// --------------------------------------------------------------------------------------
/** [Priority, Urgency, Importance] */
// Priority = Importance x Urgency(Risk)
$tlCfg->priority_levels = array( 
	  HIGH => 6, // high priority include 6 and more
    MEDIUM => 3,
    LOW => 1
);

$tlCfg->testcase_importance_default = MEDIUM;
$tlCfg->testcase_urgency_default = MEDIUM;



/** 
 * Used to get localized string to show to users
 * key: numeric code
 * value: id to use with lang_get() to get the string, from strings.txt (or custom_strings.txt)
 */
$tlCfg->urgency['code_label'] = array(
HIGH => 'high',
MEDIUM => 'medium',
LOW => 'low'
);


// --------------------------------------------------------------------------------------
/** [MISC] */

// used to mark up inactive objects (test projects, etc)
define("TL_INACTIVE_MARKUP", "* ");

// used when created a test suite path, concatenating test suite names
$g_testsuite_sep='/';

// using niftycorners
// martin: @TODO remove to smarty
define('MENU_ITEM_OPEN', '<div class="menu_bubble">');
define('MENU_ITEM_CLOSE', '</div><br />');

/** Bug tracking objects - unknown meaning (do not change)*/
// @TODO move to appropriate file - not configuration
$g_bugInterfaceOn = false;
$g_bugInterface = null;


// --------------------------------------------------------------------------------------
/** [Requirements] */
// martin: @TODO statuses should be the same for both REQ and TC
define('TL_REQ_STATUS_VALID', 		'V');
define('TL_REQ_STATUS_NOT_TESTABLE', 'N');
define('TL_REQ_STATUS_DRAFT', 		'D');
define('TL_REQ_STATUS_APPROVED', 	'A');
define('TL_REQ_STATUS_OBSOLETE', 	'O');
define('TL_REQ_STATUS_TODO', 		'T');
define('TL_REQ_STATUS_CHANGED', 	'M');

// key: status
// value: text label
$g_req_status=array(TL_REQ_STATUS_VALID => 'req_status_valid', 
					TL_REQ_STATUS_NOT_TESTABLE => 'req_status_not_testable',
					TL_REQ_STATUS_DRAFT => 'req_status_draft',
					TL_REQ_STATUS_APPROVED => 'req_status_approved',
					TL_REQ_STATUS_OBSOLETE => 'req_status_obsolete', 
					TL_REQ_STATUS_TODO => 'req_status_todo',
					TL_REQ_STATUS_CHANGED => 'req_status_changed');

// 20071117 - franciscom
// need ask Martin what are possible types
// MHT: the later solution could include status: draft, valid (=final,reviewed and approved), obsolete, future
//	so REQ review process could be apllied. The current solution is simple, but enough from testing point of view
// havlatm 200804: need to simplify the next three definitions into one
define('TL_REQ_TYPE_1', 'V');
define('TL_REQ_TYPE_2', 'N');

define('NON_TESTABLE_REQ', 'n');
define('VALID_REQ', 'v');



// havlatm: @TODO remove
define( 'PARTIAL_URL_TL_FILE_FORMATS_DOCUMENT',	'docs/tl-file-formats.pdf');

// Used to force the max len of this field, during the automatic creation of requirements
// havlatm: @TODO move to smarty config
$g_field_size = new stdClass();
$g_field_size->testsuite_name = 100;
// requirements and req_spec tables field sizes
$g_field_size->req_docid = 32;
$g_field_size->req_title = 100;
$g_field_size->requirement_title = 100;


// --------------------------------------------------------------------------------------
/** [MISC] */

// Applied to HTML inputs created to get/show custom field contents
// For string,numeric,float,email: size & maxlenght of the input type text.
// For list,email size of the select input.
$tlCfg->gui->custom_fields->sizes = array( 
	'string' => 50,
	'numeric'=> 10,
	'float'  => 10,
	'email'  => 50,
	'list'   => 1,
	'multiselection list' => 5,
	'text area' => array('cols' => 40, 'rows' => 6)
);


// 20080815 - franciscom
// Use this variable (on custom_config.inc.php) to define new Custom Field types.
// IMPORTANT:
//           check $custom_field_types property on cfield_mgr.class.php 
//           to avoid overwrite of standard types.
//
$tlCfg->gui->custom_fields->types = null;

// Use this variable (on custom_config.inc.php)
// to define possible values behaviour for new Custom Field types.
//
// IMPORTANT:
//           check $possible_values_cfg property on cfield_mgr.class.php 
//           to avoid overwrite of standard values.
//
$tlCfg->gui->custom_fields->possible_values_cfg = null;

// Format string follows date() spec - see PHP Manual
// We can not use $g_timestamp_format, because format strings for date() and strftime() 
// uses same LETTER with different meanings (Bad Luck!)
$tlCfg->gui->custom_fields->time_format = "H:i:s";                                                       
                                                       

// ----- END ----------------------------------------------------------------------------
?>