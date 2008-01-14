<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: config.inc.php,v $
 *
 * @version $Revision: 1.150 $
 * @modified $Date: 2008/01/14 21:43:23 $ by $Author: franciscom $
 *
 * SCOPE:
 * Constants and configuration parameters used throughout TestLink 
 * are defined within this file.
 * To adapt it to your environment you can made changes here (not recomended)
 * or create custom_config.inc.php and reassign there the configuration
 * variables you want change.
 *-----------------------------------------------------------------------------
 *
 * Revisions:
 * 			     20081011 - asielb	   - $g_api_ui_show
 *           20080110 - franciscom - $g_tree_show_testcase_id
 *           20080109 - franciscom - $g_sort_table_engine
 *           20080105 - franciscom - $g_testsuite_template
 *           20080102 - franciscom - new default for $g_log_path
 *           20071229 - franciscom - $g_exec_cfg->enable_tree_testcase_counters
 *                                   $g_exec_cfg->enable_tree_colouring;
 *
 *
 *           20071227 - franciscom - now default is theme_m2
 *           20071130 - franciscom - $g_gui->webeditor (work in progress)
 *           20071113 - franciscom - $g_exec_cfg->show_history_all_builds
 *           20071112 - franciscom - config changes due to upgrade of Smarty
 *           20071106 - franciscom - BUGID 1165 - $g_testcase_template
 *
 *           20071104 - franciscom - $g_exec_cfg->enable_test_automation
 *                                   $g_gui->tprojects_combo_order_by (BUGID 498)
 *           20071006 - franciscom - $g_use_ext_js_library
 *           20070930 - franciscom - BUGID 1086 - configure order by in attachment
 *           20070910 - franciscom - removed MAIN_PAGE_METRICS_ENABLED
 *           20070819 - franciscom - $g_default_roleid
 *           20070706 - franciscom - $g_exec_cfg->user_filter_default
 *           20070706 - franciscom - $g_exec_cfg->view_mode->tester
 *                                   $g_exec_cfg->exec_mode->tester
 *
 *           20070523 - franciscom - $g_user_login_valid_regex
 *           20070523 - franciscom - $g_main_menu_item_bullet_img
 *           20070505 - franciscom - following mantis bug tracking style, if file
 *                                   custom_config.inc.php exists, il will be included
 *                                   allowing users to customize TL configurations
 *                                   managed using global variables, without need
 *                                   of changing this file.
 *                                   
 *           20070429 - franciscom - added contribution by Seweryn Plywaczyk
 *                                   text area custom field
 *
 *           20070415 - franciscom -  added config for drag and drop feature
 *           20070301 - franciscom - 
 *           BUGID 695 - $g_user_self_signup (fawel contribute)
 *
 *-----------------------------------------------------------------------------
 **/

// ----------------------------------------------------------------------------
/** [INITIALIZATION] - DO NOT CHANGE THE SECTION */
/** The root dir for the testlink installation with trailing slash */
define('TL_ABS_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);
define('DS', DIRECTORY_SEPARATOR);

/** Dir for temporary files and compiled templates */
define('TL_TEMP_PATH', TL_ABS_PATH . 'gui' . DS . 'templates_c' . DS);

/** Include constants */
require_once(dirname(__FILE__) . DS . 'cfg' . DS . 'const.inc.php');

/** Setting up the global include path for testlink */
ini_set('include_path',ini_get('include_path') . PATH_SEPARATOR . 
        '.' . PATH_SEPARATOR . TL_ABS_PATH . 'lib' . DS . 'functions' . DS);

/** Include database consts (the file is generated automatically by TL installer) */ 
require_once('config_db.inc.php');

/** Functions for check request status */
require_once('configCheck.php');


/** load the php4 to php5 domxml wrapper if the php5 is used and the domxml extension is not loaded **/
if (version_compare(PHP_VERSION,'5','>=') && !extension_loaded("domxml"))
	require_once(dirname(__FILE__) . '/third_party/domxml-php4-to-php5.php');


// ----------------------------------------------------------------------------
/** [LOCALIZATION] */
define('TL_LOCALE_PATH',TL_ABS_PATH . 'locale/');
define('TL_HELP_RPATH','gui/help/');
define('TL_INSTRUCTIONS_RPATH','gui/help/');


// Your first/suggested choice for default locale.
// This must be one of $g_locales (see cfg/const.inc.php).
// An attempt will be done to stablish the default locale 
// automatically using $_SERVER['HTTP_ACCEPT_LANGUAGE']
$g_default_language = 'en_GB'; 


/** root of testlink directory location seen through the web server */
/*  20070106 - franciscom - this statement it's not 100% right      
    better use $_SESSION['basehref'] in the scripts. */      
define('TL_BASE_HREF', get_home_url()); 

// ----------------------------------------------------------------------------
/** [GLOBAL] */

/** Error reporting - do we want php errors to show up for users */
error_reporting(E_ALL);

/** Set the session timeout value (in minutes).
 * This will prevent sessions timing out after very short periods of time */
//ini_set('session.cache_expire',900);

/**
 * Set the session garbage collection timeout value (in seconds)
 * The default session garbage collection in php is set to 1440 seconds (24 minutes)
 * If you want sessions to last longer this must be set to a higher value.
 * You may need to set this in your global php.ini if the settings don't take effect.
 */
//ini_set('session.gc_maxlifetime', 54000)

// ----------------------------------------------------------------------------
/** [CHARSET] */

/** Set this to TRUE if your DB supports UTF8 (For MySQL version >= 4.1) */
define('DB_SUPPORTS_UTF8', TRUE);

/** CHARSET - UTF-8 is only officially supported charset */
// ISO-8859-1 is there for backward compatability
$g_defaultCharset =  DB_SUPPORTS_UTF8  ? 'UTF-8' : 'ISO-8859-1';
define('TL_TPL_CHARSET', $g_defaultCharset);
define('TL_XMLEXPORT_HEADER', "<?xml version=\"1.0\" encoding=\"" . TL_TPL_CHARSET . "\"?>\n");



// ----------------------------------------------------------------------------
/** [LOGGING] */
/** @see logging.inc.php for more information */
$g_log_path=TL_ABS_PATH . 'logs' . DS ;

/** Default level of logging (NONE, ERROR, INFO, DEBUG, EXTENDED) */
$g_log_level='NONE';

// ----------------------------------------------------------------------------
/** [Bug Tracking systems] */
/** 
* TestLink uses bugtracking systems to check if displayed bugs resolved, verified, 
* and closed bugs. If they are it will strike through them
*
* @var STRING g_interface_bugs = ['NO', 'BUGZILLA', 'MANTIS', 'JIRA','TRACKPLUS', 'EVENTUM']
* NO        : no bug tracking system integration 
* BUGZILLA  : edit configuration in TL_ABS_PATH/cfg/bugzilla.cfg.php
* MANTIS    : edit configuration in TL_ABS_PATH/cfg/mantis.cfg.php
* JIRA      : edit configuration in TL_ABS_PATH/cfg/jira.cfg.php
* TRACKPLUS : edit configuration in TL_ABS_PATH/cfg/trackplus.cfg.php
* EVENTUM : edit configuration in TL_ABS_PATH/cfg/eventum.cfg.php
*/
$g_interface_bugs = 'NO';

// ----------------------------------------------------------------------------
/** [authentication] */                 

/** Login authentication
 * possible values: '' or 'MD5' => use password stored on db
 *                   'LDAP'      => use password from LDAP Server
 */ 
$g_login_method				= 'MD5';

// LDAP authentication are developed by mantis project (www.mantisbt.org)
// Example: 
//	$g_ldap_bind_dn			= 'my_bind_user';
//	$g_ldap_bind_passwd	= 'my_bind_password';

$g_ldap_server			= 'localhost';
$g_ldap_port			= '389';
$g_ldap_root_dn			= 'dc=mycompany,dc=com';
$g_ldap_organization	= '';    # e.g. '(organizationname=*Traffic)'
$g_ldap_uid_field		= 'uid'; # Use 'sAMAccountName' for Active Directory
$g_ldap_bind_dn			= ''; // Left empty if you LDAP server allows anonymous binding 
$g_ldap_bind_passwd	= ''; // Left empty if you LDAP server allows anonymous binding 



// ----------------------------------------------------------------------------
/** [GUI] */

/** some maxima related to importing stuff in TL */
// Maximum uploadfile size 
// Also check your PHP settings (default is usually 2MBs)
define('TL_IMPORT_LIMIT', '204800'); // in bytes

/** maximum line size of the imported file */
define('TL_IMPORT_ROW_MAX', '10000'); // in chars

/** Configure frmWorkArea navigator width */
define('TL_FRMWORKAREA_LEFT_FRAME_WIDTH', "30%"); 

/** CSS themes - modify if you create own*/
define('TL_THEME_BASE_DIR','gui/themes/theme_m2/');

define('TL_THEME_CSS_DIR',TL_THEME_BASE_DIR . 'css/');
define('TL_TESTLINK_CSS',TL_THEME_CSS_DIR . 'testlink.css');
define('TL_LOGIN_CSS', TL_TESTLINK_CSS);
define('TL_PRINT_CSS',TL_THEME_CSS_DIR . 'tl_print.css');
define('TL_JOMLA_1_CSS', TL_THEME_CSS_DIR . 'jos_template_css.css');
define('TL_TREEMENU_CSS', TL_THEME_CSS_DIR . 'tl_treemenu.css');

// needed for drap and drop feature
define('TL_DRAG_DROP_DIR', 'gui/drag_and_drop/');
define('TL_DRAG_DROP_JS_DIR', TL_DRAG_DROP_DIR. 'js/');
define('TL_DRAG_DROP_FOLDER_CSS', TL_DRAG_DROP_DIR . 'css/drag-drop-folder-tree.css');
define('TL_DRAG_DROP_CONTEXT_MENU_CSS', TL_DRAG_DROP_DIR . 'css/context-menu.css');


// path to IMAGE directory - DO NOT ADD FINAL /
define('TL_THEME_IMG_DIR',TL_THEME_BASE_DIR . 'images');

// logo for login page
$g_logo_login_page='<img alt="TestLink" title="TestLink" src="' . 
                    TL_THEME_IMG_DIR . '/company_logo.png" />';

// logo for navbar page
$g_logo_navbar= '<img alt="TestLink" title="TestLink" src="' . 
                 TL_THEME_IMG_DIR . '/company_logo.png" />';

// image for main menu item bullet (just filename)
// $g_main_menu_item_bullet_img='arrow_org.gif';
$g_main_menu_item_bullet_img='slide_gripper.gif';

// 'background'  -> standard behaviour for 1.6.x you can have a different
//                  background colour for every test project.
//
// 'none'        -> new behaviour no background color change 
//
// define('TL_TESTPROJECT_COLORING','background');
define('TL_TESTPROJECT_COLORING','none');

/** fckeditor Toolbar 
 * modify which icons will be available in html edit pages
 * refer to fckeditor configuration file 
 **/
$g_fckeditor_toolbar = "TL_Medium_2";

/* 
TRUE -> user can enable/disable test plan filter by 
        product (term used on TL < 1.7) / test project (term used on TL>= 1.7)
        At user interface level a check box is displayed over
        the test plan combo box.
FALSE -> user can do nothing, no changes at UI.
         Test Plan always filtered by product
*/
$g_ui_show_check_filter_tp_by_testproject = FALSE;

// Display name and surename in all user lists 
// $g_show_realname=TRUE; -> build a human readable displayname
//                           using $g_username_format
$g_show_realname = FALSE;

// used to build a human readable display name for users
// example: user ux555, real name= John Cook
// '%first% %last%'          -> John Cook
// '%last%, %first%'          -> John Cook
// '%first% %last% %login%'    -> John Cook [ux555]
$g_username_format = '%first% %last% [%login%]';

/** characters used to surround the role description in the user interface */
$g_role_separator->open='[';
$g_role_separator->close=']';

/** true => icon edit will be added into <a href> as indication an edit features */
$g_gui->show_icon_edit=false;

// Order to use when building a testproject combobox
// 'ORDER BY name'
// 'ORDER_BY BY nodes_hierarchy.id DESC' -> similar effect to order last created firts
// 
$g_gui->tprojects_combo_order_by='ORDER BY nodes_hierarchy.id DESC';


// 20071130 - franciscom 
//
// 'fckeditor'
// 'tinymce'
// 'none' -> use plan html textarea input field
//
$g_gui->webeditor='fckeditor';

// 20080111 - asielb
// Is API related functionality visible in the UI
$g_api_ui_show = TRUE; 

// ----------------------------------------------------------------------------
/** [GUI: TREE] */

/** 
 * TREE MENU 
 *	Definition of tree menu component: dTree, jTree or phplayersmenu.
 *	jTree has the best performance but others have a better functionality  
 *	[LAYERSMENU, DTREE, JTREE]
 */

// can be redefined using custom_config.inc.php
$g_tree_type='JTREE';
 

// When creating an node in the tree, you can choose if:
//
// Any node added independent of the type is added with order 0,
// then the initial display order will be by node id.
//
// An useful alternative is maintain, inside of a container two groups:
// one for test cases, and one for test suites.
// This can be achived assigned a default order different for every type of node.
//                 
// These values must be >= 0
//
$g_tree_node_ordering->default_testcase_order=100;
$g_tree_node_ordering->default_testsuite_order=1;


// 20080110 - franciscom
// 0 -> do not show testcase id on tree
$g_tree_show_testcase_id=1;

// ----------------------------------------------------------------------------
/** [GUI: Javascript libraries] */
/* 1 -> use EXT JS library , GUI widgets */
$g_use_ext_js_library=1;

// May be in future another table sort engine will be better
// kryogenix.org -> Stuart Langridge sortTable
// '' (empty string) -> disable table sorting feature
$g_sort_table_engine='kryogenix.org';

// ----------------------------------------------------------------------------
/** [GENERATED DOCUMENTATION] */
// Constants used in printed documents.
define('TL_DOC_BASIC_CSS', TL_THEME_CSS_DIR . 'tl_doc_basic.css');

// ----------------------------------------------------------------------------
/** [ATTACHMENTS] */

// TRUE: attachment feature available
//
$g_attachments->enabled = TRUE;

/** the type of the repository can be database or filesystem
 * TL_REPOSITORY_TYPE_DB => database
 * TL_REPOSITORY_TYPE_FS => filesystem
 **/
$g_repositoryType = TL_REPOSITORY_TYPE_FS;

/** 
 * TL_REPOSITORY_TYPE_FS: the where the filesystem repository should be located
 * We recommend to change the directory for security reason. 
 **/
$g_repositoryPath = TL_ABS_PATH . "upload_area" . DS;

/** 
 * compression used within the repository 
 * TL_REPOSITORY_COMPRESSIONTYPE_NONE => no compression
 * TL_REPOSITORY_COMPRESSIONTYPE_GZIP => gzip compression
 */
$g_repositoryCompressionType = TL_REPOSITORY_COMPRESSIONTYPE_NONE;

// the maximum allowed file size for each repository entry, default 1MB.
// Also check your PHP settings (default is usually 2MBs)
define("TL_REPOSITORY_MAXFILESIZE_MB", 1);
define("TL_REPOSITORY_MAXFILESIZE", 1024*1024*TL_REPOSITORY_MAXFILESIZE_MB); // don't change


// TRUE -> when you upload a file you can give no title
$g_attachments->allow_empty_title = TRUE;

// $g_attachments->allow_empty_title == TRUE, you can ask the system
// to do something 
// 
// 'none'         -> just write on db an empty title
// 'use_filename' -> use filename as title
//$g_attachments->action_on_save_empty_title='use_filename';
//
$g_attachments->action_on_save_empty_title='none';

// Remember that title is used as link description for download
// then if title is empty, what the system has to do when displaying ?
// 'show_icon'  -> the $g_attachments->access_icon will be used.
// 'show_label' -> the value of $g_attachments->access_string will be used .
$g_attachments->action_on_display_empty_title='show_icon';

$g_attachments->access_icon='<img src="' . TL_THEME_IMG_DIR . '/new_f2_16.png" style="border:none">';
$g_attachments->access_string="[*]";

// Set display order of uploaded files - BUGID 1086
$g_attachments->order_by=" ORDER BY date_added DESC ";

// ----------------------------------------------------------------------------
/** [Requirements] */

/** Test Case generation from Requirements

	- use_req_spec_as_testsuite_name
  	FALSE -> test cases are created and assigned to a test suite 
  	         with name $g_req_cfg->default_testsuite_name
  	         
  	TRUE  -> REQuirement Specification Title is used as testsuite name     
*/
$g_req_cfg->use_req_spec_as_testsuite_name = TRUE;
$g_req_cfg->default_testsuite_name = "Test suite created by Requirement - Auto";
$g_req_cfg->testsuite_details = "<b>Test suite/Test Cases generated from Requirements</b>";
$g_req_cfg->testcase_summary_prefix = "<b>Test Case generated from Requirement</b><br>";


// true : you want req_doc_id UNIQUE IN THE WHOLE DB (system_wide)
// false: you want req_doc_id UNIQUE INSIDE a SRS
//
$g_req_cfg->reqdoc_id->is_system_wide=false;

$g_req_cfg->module='lib/requirements/';

// Used to force the max len of this field, during the automatic creation
// of requirements
$g_field_size->testsuite_name = 100;

// requirements and req_spec tables field sizes
$g_field_size->req_docid=32;
$g_field_size->req_title=100;
$g_field_size->requirement_title=100;


// ----------------------------------------------------------------------------
/** [SMTP] */

# Taken from mantis for phpmailer config
define ("SMTP_SEND",2);
$g_phpMailer_method = SMTP_SEND;

$g_tl_admin_email     = 'tl_admin@127.0.0.1';         # for problem/error notification 
$g_from_email         = 'testlink_system@127.0.0.1';  # email sender
$g_return_path_email  = 'no_replay@127.0.0.1';

# Urgent = 1, Not Urgent = 5, Disable = 0
$g_mail_priority = 5;   

// SMTP Configuration
$g_smtp_host        = 'localhost';  # SMTP server MUST BE configured  

// Configure only if SMTP server requires authentication
$g_smtp_username    = '';  # user  
$g_smtp_password    = '';  # password 



// ----------------------------------------------------------------------------
/** [MISC] */

/** Check unique titles of Test Project, Test Suite and Test Case
 *  TRUE  => Check              [STANDARD BEHAVIOUR]
 *  FALSE => don't check
 **/
$g_check_names_for_duplicates = TRUE;

/** 
 * Action for duplication check (only if $g_check_names_for_duplicates=TRUE)
 * 'allow_repeat' => allow the name to be repeated (backward compatibility)
 * 'generate_new' => generate a new name using $g_prefix_name_for_copy
 * 'block'        => return with an error 
 **/    
$g_action_on_duplicate_name = 'generate_new';

/** Used when creating a Test Suite using copy 
   and you have choose  $g_action_on_duplicate_name = 'generate_new'
   if the name exist.
 */
$g_prefix_name_for_copy = strftime("%Y%m%d-%H:%M:%S", time());
        
/** 
BUGID 0000086: Using "|" in the testsuite name causes malformed URLs
regexp used to check for chars not allowed in:
test project, test suite and testcase names.
*/
$g_ereg_forbidden = "[|]";


// Get from MANTIS Bugtracking system
// Regular expression to use when validating new user login names
// This default regular expression: '/^[\w \-]+$/'
// allows a-z, A-z, 0-9, as well as space and underscore.  
// IMPORTANT: If you change this, you may want to update the
//            $TLS_valid_user_name_format 
//            string in the language files to explain the rules you are using on your site
//
$g_user_login_valid_regex='/^[\w \-]+$/';

/** Allow/disallow to have Test Plans without dependency to Test Project.
 * TRUE  => allow Test Plan over all Test Projects (TL 1.5 compatibility)
 * FALSE => all Test Plans should have own Test Project   [STANDARD BEHAVIOUR]
 **/
$g_show_tp_without_tproject_id = FALSE;

// obsolete (use $g_show_tp_without_tproject_id)
$g_show_tp_without_prodid = $g_show_tp_without_tproject_id;

// TRUE  -> you can create multiple time the same keyword 
//           for the same product (term used on TL < 1.7) / test project (term used on TL>= 1.7) 
// FALSE ->   [STANDARD BEHAIVOUR]
$g_allow_duplicate_keywords = FALSE;


// TRUE   -> custom field logic will be executed  [STANDARD BEHAVIOUR]
// FALSE  -> no possibility to use custom fields
$g_gui->enable_custom_fields = TRUE;

// Applied to HTML inputs created to get/show custom field contents
// 
// For string,numeric,float,email: size & maxlenght of the input type text.
// For list,email size of the select input.
//
$g_gui->custom_fields->sizes = array( 
	'string' => 50,
	'numeric'=> 10,
	'float'  => 10,
	'email'  => 50,
	'list'   => 1,
	'multiselection list' => 5,
	'text area' => array('cols' => 40, 'rows' => 6)
);


// Taken from Mantis
// Set this flag to automatically convert www URLs and
// email adresses into clickable links
$g_html_make_links = ON;

// These are the valid html tags for multi-line fields (e.g. description)
// do NOT include href or img tags here
// do NOT include tags that have parameters (eg. <font face="arial">)
$g_html_valid_tags = 'p, li, ul, ol, br, pre, i, b, u, em';

// These are the valid html tags for single line fields (e.g. issue summary).
// do NOT include href or img tags here
// do NOT include tags that have parameters (eg. <font face="arial">)
$g_html_valid_tags_single_line = 'i, b, u, em';




/** [Risk, Priority, Importance] */
// item_id => item_label (must be defined in strings.txt file)
$g_risk=array( '1' => 'high_risk',
               '2' => 'medium_risk',
               '3' => 'low_risk');

$g_importance=array( 'H' => 'high_importance',
                     'M' => 'medium_importance',
                     'L' => 'low_importance');

$g_priority=array( 'A' => 'high_priority',
                   'B' => 'medium_priority',
                   'C' => 'low_priority');



/** [Test case] */

// 1 -> user can edit executed tc versions
// 0 -> editing of executed tc versions is blocked.  [STANDARD BEHAVIOUR]
$g_testcase_cfg->can_edit_executed=0;

//
// used to create full external id in this way:
// testCasePrefix . g_testcase_cfg->glue_character . external_id
// CAN NOT BE EMPTY
$g_testcase_cfg->glue_character='-';

/** [Test Plan] */
// TRUE  -> standard behaviour, user can remove assigned test cases
//          using the assign/add page.
//
// FALSE -> user need to use the remove page
//
$g_testplan_cfg->can_remove_tc_on_add=TRUE;  // To be developed


/** [Executions] */

// ASCending   -> last execution at bottom
// DESCending  -> last execution on top      [STANDARD BEHAVIOUR]
$g_exec_cfg->history_order='DESC';

// TRUE  -> the whole execution history for the choosen build will be showed
// FALSE -> just last execution for the choosen build will be showed [STANDARD BEHAVIOUR]
$g_exec_cfg->history_on=FALSE;


// TRUE  ->  test case VERY LAST (i.e. in any build) execution status 
//           will be displayed
// FALSE -> only last result on current build.  [STANDARD BEHAVIOUR]
$g_exec_cfg->show_last_exec_any_build=FALSE;


// TRUE  ->  History for all builds will be shown
// FALSE ->  Only history of the current build will be shown  [STANDARD BEHAVIOUR]
$g_exec_cfg->show_history_all_builds=FALSE;



// different models for the attachments management on execution page
// $att_model_m1 ->  shows upload button and title 
//   
// $att_model_m2 ->  hides upload button and title
//
$g_exec_cfg->att_model = $att_model_m2;   //defined in const.inc.php


// 1 -> User can delete an execution result
// 0 -> User can not.  [STANDARD BEHAVIOUR]
$g_exec_cfg->can_delete_execution=0;


// 1 -> enable XML-RPC calls to external test automation server
//      new buttons will be displayed on execution pages
//
// 0 -> disable
$g_exec_cfg->enable_test_automation=0;



// 1 -> enable testcase counters by status on tree
$g_exec_cfg->enable_tree_testcase_counters=1;

// 1 -> test cases and test case counters will be coluored
//      according to test case status
$g_exec_cfg->enable_tree_colouring=1;


/** [Test case specification] */

// 'horizontal' ->  step and results on the same row
// 'vertical'   ->  steps on one row, results in the row bellow
//                   
$g_spec_cfg->steps_results_layout='horizontal';
//$g_spec_cfg->steps_results_layout='vertical';

// 1 -> User will see a test suite filter while creating test specification
// 0 -> no filter available
$g_spec_cfg->show_tsuite_filter=1;


// 1 -> every time user do some operation on test specification
//      tree is updated on screen.
// 0 -> tree will not be updated, user can update it manually.
//
$g_spec_cfg->automatic_tree_refresh=1;

// Filter Test cases a user with tester role can VIEW depending on
// test execution assignment.
// all: all test cases.
// assigned_to_me: test cases assigned to logged user.
// assigned_to_me_or_free: test cases assigned to logged user or not assigned
//
// Important: 
//            this setting has effects and precedence over 
//            $g_exec_cfg->restrictions->tester->exec_mode.
//
$g_exec_cfg->view_mode->tester='assigned_to_me';

// Filter Test cases a user with tester role can EXECUTE depending on
// test execution assignment.
// all: all test cases.
// assigned_to_me: test cases assigned to logged user.
// assigned_to_me_or_free: test cases assigned to logged user or not assigned
$g_exec_cfg->exec_mode->tester='assigned_to_me';


// logged_user -> combo will be set to logged user
// none        -> no filter applied by default 
$g_exec_cfg->user_filter_default='logged_user';


// 20071105 - franciscom
// Important
// object members has SAME NAME that FCK editor objects.
// the logic present on tcEdit.php is dependent of this rule.
// 
// summary
// steps
// expected_results
//
// every member contains an object with following members:
// type
// value
// 
// Possible values for type member: 
// none: template will not be used, default will be a clean editor screen.
//
// string: value of value member is assigned to FCK object
//
// string_id: value member is used in a lang_get() call, and return value 
//            is assigned to FCK object.
//            Configure string_id on custom_strings.txt            
//
// file: value member is used as file name.
//       file is readed and it's contains assigned to FCK object
//
// any other value for type, results on '' assigned to FCK object
//        
//
$g_testcase_template->summary->type='none';
$g_testcase_template->summary->value='';


$g_testcase_template->steps->type='none';
$g_testcase_template->steps->value='';

$g_testcase_template->expected_results->type='none';
$g_testcase_template->expected_results->value='';


// Important
// object members has SAME NAME that FCK editor objects.
// the logic present on tcEdit.php is dependent of this rule.
// 
// every member contains an object with following members:
// type
// value
// 
// Possible values for type member: 
// none: template will not be used, default will be a clean editor screen.
//
// string: value of value member is assigned to FCK object
//
// string_id: value member is used in a lang_get() call, and return value 
//            is assigned to FCK object.
//            Configure string_id on custom_strings.txt            
//
// file: value member is used as file name.
//       file is readed and it's contains assigned to FCK object
//       example:
//               $g_testsuite_template->details->type='file';
//               $g_testsuite_template->details->value='D:\w3\tl\head_20080103\logs\tsuite.txt';
//
// any other value for type, results on '' assigned to Web Editor object.
//        
//
$g_testsuite_template->details->type='none';
$g_testsuite_template->details->value='';


//
// [LOGIN]
//
// Allow Users to create their own accounts by new user link on login page
// TRUE => allow [STANDARD BEHAVIOUR]
// FALSE => disallow
//
$g_user_self_signup = TRUE; 
// ----- End of Config ------------------------------------------------

// --------------------------------------------------------------------
// DO NOT CHANGE NOTHING BELOW
// --------------------------------------------------------------------
$custom_config_file = TL_ABS_PATH . 'custom_config.inc.php';
clearstatcache();
if ( file_exists( $custom_config_file ) ) 
{
  require_once( $custom_config_file ); 
}

define('USE_EXT_JS_LIBRARY', $g_use_ext_js_library);

define('TL_TREE_KIND', $g_tree_type);

// use when componing an title using several strings
define('TITLE_SEP',$g_title_sep);
define('TITLE_SEP_TYPE2',$g_title_sep_type2);
define('TITLE_SEP_TYPE3',$g_title_sep_type3);
define('TITLE_SEP_TYPE4',$g_title_sep_type4);


define('TL_ITEM_BULLET_IMG', TL_THEME_IMG_DIR . "/" .$g_main_menu_item_bullet_img);
define('REFRESH_SPEC_TREE',$g_spec_cfg->automatic_tree_refresh ? 'yes' : 'no');

define("TL_DEFAULT_ROLEID",$g_default_roleid);

// added check to avoid notice message in the migration pages
if(!defined('TL_LOG_LEVEL_DEFAULT'))
{
	define('TL_LOG_LEVEL_DEFAULT', $g_log_level);
}



// For printing documents
define('TL_DOC_COMPANY', $g_company_name);
define('TL_DOC_COMPANY_LOGO', $g_company_logo);
define('TL_DOC_COPYRIGHT', $g_copyright);
define('TL_DOC_CONFIDENT', $g_confidential);

define('TL_SORT_TABLE_ENGINE',$g_sort_table_engine);

/** Support for localization */
$serverLanguage = false;
// check for !== false because getenv() returns false on error
if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
	$serverLanguage = getenv($_SERVER['HTTP_ACCEPT_LANGUAGE']);
	
if(false !== $serverLanguage)
{
	if (array_key_exists($serverLanguage,$g_locales))
		$g_default_language = $serverLanguage;
}

define ('TL_DEFAULT_LOCALE', $g_default_language);
require_once("lang_api.php");

// used to disable the attachment feature if there are problems with repository path
$g_attachments->disabled_msg = "";
if($g_repositoryType == TL_REPOSITORY_TYPE_FS)
{
  $ret = checkForRepositoryDir($g_repositoryPath);
  if(!$ret['status_ok'])
  {
	  $g_attachments->enabled = FALSE;
	  $g_attachments->disabled_msg = $ret['msg'];
  }
}

// 20071130 - franciscom
// simplifies use on smarty template
define('WEBEDITOR',$g_gui->webeditor);

// 20071118 - franciscom
define('REQ_MODULE',$g_req_cfg->module);
// define('USERMANAGEMENT_MODULE','lib/usermanagement');


// logo for login page, if not defined nothing happens
define('LOGO_LOGIN_PAGE',$g_logo_login_page);

// logo for navbar page
define('LOGO_NAVBAR',$g_logo_navbar);

/** Bug tracking include */
$g_bugInterfaceOn = false;
$g_bugInterface = null;
if ($g_interface_bugs != 'NO')
  require_once(TL_ABS_PATH . 'lib/bugtracking/int_bugtracking.php');
// --------------------------------------------------------------------


/** Testlink Smarty class sets up the default smarty settings for testlink */
require_once(TL_ABS_PATH . 'third_party/smarty/libs/Smarty.class.php'); 
require_once(TL_ABS_PATH . 'lib/general/tlsmarty.inc.php'); 

/** logging functions */
require_once('logging.inc.php');

/** user right checking */
require_once(TL_ABS_PATH . 'lib/functions/roles.inc.php');
require_once(TL_ABS_PATH . 'cfg/userRightMatrix.php');
// ----- END OF FILE --------------------------------------------------
?>