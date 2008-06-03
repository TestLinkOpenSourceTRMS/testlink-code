<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: config.inc.php,v $
 * @version $Revision: 1.180 $
 * @modified $Date: 2008/06/03 09:22:47 $ by $Author: havlat $
 *
 * SCOPE:
 * 		Constants and configuration parameters used throughout TestLink 
 * 
 * 		There are included extra files: 
 * 			- your settings - custom_config.inc.php
 * 			- DB access - config_db.inc.php
 * 			- constants - const.inc.php
 * 			- basic checking - configCheck.php
 * 
 * IMPORTANT: 
 * 		To adapt values in the file but create/modify custom_config.inc.php and   
 * 		replace values of TestLink configuration variables. It saves your changes
 * 		for the next upgrade in one extra file.
 * 
 * 
 *  -----------------------------------------------------------------------------
 *
 * Revisions:
 * 
 *     20080525 - franciscom - added spectreemenu_type (temporary solution)
 *     20080504 - franciscom - removed gui->enable_custom_fields
 * 		 20080419 - havlatm - documentation update; minor refactorization
 *     20080418 - franciscom -  new document_generation
 *     20080330 - franciscom -  
 *     20080326 - franciscom - restored configuration parameters removed without reasons.
 *
 * 	   20080423 - havlatm - added $tlCfg as standard configuration holder
 *     20080322 - franciscom - $g_exec_cfg->edit_notes
 *     20080308 - franciscom - changed initial values for email settings
 *     20080304 - franciscom - $g_exec_cfg->show_testsuite_contents
 *     20080208 - franciscom - added contribution seapine BTS (BUGID 1371)
 *     20080124 - franciscom - $g_dashboard_precision
 *     20080118 - franciscom - $g_api_id_format
 * 		 20081011 - asielb	   - $g_api_ui_show
 *     20080110 - franciscom - $g_tree_show_testcase_id
 *     20080109 - franciscom - $g_sort_table_engine
 *     20080105 - franciscom - $g_testsuite_template
 *     20080102 - franciscom - new default for $g_log_path
 *     20071229 - franciscom - $g_exec_cfg->enable_tree_testcase_counters
 *                             $g_exec_cfg->enable_tree_colouring;
 *
 *
 *     20071227 - franciscom - now default is theme_m2
 *     20071130 - franciscom - $g_gui->webeditor (work in progress)
 *     20071113 - franciscom - $g_exec_cfg->show_history_all_builds
 *     20071112 - franciscom - config changes due to upgrade of Smarty
 *     20071106 - franciscom - BUGID 1165 - $g_testcase_template
 *
 *     20071104 - franciscom - $g_exec_cfg->enable_test_automation
 *                             $g_gui->tprojects_combo_order_by (BUGID 498)
 *     20071006 - franciscom - $g_use_ext_js_library
 *     20070930 - franciscom - BUGID 1086 - configure order by in attachment
 *     20070910 - franciscom - removed MAIN_PAGE_METRICS_ENABLED
 *     20070819 - franciscom - $g_default_roleid
 *     20070706 - franciscom - $g_exec_cfg->user_filter_default
 *     20070706 - franciscom - $g_exec_cfg->view_mode->tester
 *                             $g_exec_cfg->exec_mode->tester
 *
 *     20070523 - franciscom - $g_user_login_valid_regex
 *     20070523 - franciscom - $g_main_menu_item_bullet_img
 *     20070505 - franciscom - following mantis bug tracking style, if file
 *                             custom_config.inc.php exists, il will be included
 *                             allowing users to customize TL configurations
 *                             managed using global variables, without need
 *                             of changing this file.
 *                             
 *     20070429 - franciscom - added contribution by Seweryn Plywaczyk
 *                             text area custom field
 *
 *     20070415 - franciscom -  added config for drag and drop feature
 *     20070301 - franciscom - 
 *     BUGID 695 - $g_user_self_signup (fawel contribute)
 *
 **/

// ----------------------------------------------------------------------------
/** [INITIALIZATION] - DO NOT CHANGE THE SECTION */

/** @global array Global configuration class */
$tlCfg = new stdClass();
$tlCfg->gui = new stdClass();
$tlCfg->exec_cfg = new stdClass();
$tlCfg->testcase_cfg=new stdClass();
$tlCfg->document_generator = new stdClass();

/** Include database access definition (generated automatically by TL installer) */ 
@include_once('config_db.inc.php');

/** The root dir for the testlink installation with trailing slash */
define('TL_ABS_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);

/** Include constants and magic numbers (users should not change it)*/
require_once(TL_ABS_PATH . 'cfg' . DIRECTORY_SEPARATOR . 'const.inc.php');


// ----------------------------------------------------------------------------
/** [LOCALIZATION] */

/** Default localization for users */
// The value must be available in $g_locales (see cfg/const.inc.php).
// Note: An attempt will be done to establish the default locale 
// automatically using $_SERVER['HTTP_ACCEPT_LANGUAGE']
$tlCfg->default_language = 'en_GB'; 

/** 
 * Charset 'UTF-8' is only officially supported charset (Require MySQL version >= 4.1)
 * 'ISO-8859-1' or another Charset could be set for backward compatability by experienced 
 * users. However we have not resources to support such patches.
 **/
$tlCfg->charset = 'UTF-8';

/** characters used to surround a description in the user interface (for example role)*/
$tlCfg->gui->role_separator_open =  '[';
$tlCfg->gui->role_separator_close = ']';

/** Title separators are used when componing an title using several strings */
$tlCfg->gui_title_separator_1 = ' : '; // object : name (Test Specification : My best product)
$tlCfg->gui_title_separator_2 = ' - '; // parent - child



// ----------------------------------------------------------------------------
/** [SERVER ENVIRONMENT] */

/** Error reporting - do we want php errors to show up for users */
error_reporting(E_ALL);

/** Set the session timeout value (in minutes).
 * This will prevent sessions timing out after very short periods of time 
 * Warning: your server could block this settings
 **/
//ini_set('session.cache_expire',900);

/**
 * Set the session garbage collection timeout value (in seconds)
 * The default session garbage collection in php is set to 1440 seconds (24 minutes)
 * If you want sessions to last longer this must be set to a higher value.
 * You may need to set this in your global php.ini if the settings don't take effect.
 */
//ini_set('session.gc_maxlifetime', 54000)


// ----------------------------------------------------------------------------
/** [LOGGING] */

/** Default level of logging (NONE, ERROR, INFO, DEBUG, EXTENDED) */
$tlCfg->log_level = 'ERROR';

/** show smarty debug window */
$tlCfg->smarty_debug = false;

/** Path to store logs */
$tlCfg->log_path = TL_ABS_PATH . 'logs' . DIRECTORY_SEPARATOR ;

/** 
 * Configure if individual logging data stores are enabled of disabled
 * Possibile values to identify loggers: 'db','file'
 *		$g_loggerCfg=null; all loggers enabled (default) 
 * 		$g_loggerCfg['db']['enabled']=true/false;
 * 		$g_loggerCfg['file']['enabled']=true/false;
 */
$g_loggerCfg = null;


// ----------------------------------------------------------------------------
/** [Bug Tracking systems] */
/** 
 * TestLink uses bugtracking systems to check if displayed bugs resolved, verified, 
 * and closed bugs. If they are it will strike through them
 *
 * @var STRING g_interface_bugs = [
 * 'NO'        : no bug tracking system integration (DEFAULT)
 * 'BUGZILLA'  : edit configuration in TL_ABS_PATH/cfg/bugzilla.cfg.php
 * 'MANTIS'    : edit configuration in TL_ABS_PATH/cfg/mantis.cfg.php
 * 'JIRA'      : edit configuration in TL_ABS_PATH/cfg/jira.cfg.php
 * 'TRACKPLUS' : edit configuration in TL_ABS_PATH/cfg/trackplus.cfg.php
 * 'EVENTUM'   : edit configuration in TL_ABS_PATH/cfg/eventum.cfg.php
 * 'SEAPINE'   : edit configuration in TL_ABS_PATH/cfg/seapine.cfg.php
 * ]
 */
$g_interface_bugs = 'NO';



// ----------------------------------------------------------------------------
/** [SMTP] */

// SMTP server Configuration ("localhost" is enough in the most cases)
$g_smtp_host        = '[smtp_host_not_configured]';  # SMTP server MUST BE configured  

# Configure using custom_config.inc.php
$g_tl_admin_email     = '[testlink_sysadmin_email_not_configured]'; # for problem/error notification 
$g_from_email         = '[from_email_not_configured]';  # email sender
$g_return_path_email  = '[return_path_email_not_configured]';

# Urgent = 1, Not Urgent = 5, Disable = 0
$g_mail_priority = 5;   

# Taken from mantis for phpmailer config
define ("SMTP_SEND",2);
$g_phpMailer_method = SMTP_SEND;

// Configure only if SMTP server requires authentication
$g_smtp_username    = '';  # user  
$g_smtp_password    = '';  # password 



// ----------------------------------------------------------------------------
/** [User Authentication] */                 

/** 
 * Login authentication method:
 * 		'MD5' => use password stored on db
 *      'LDAP'      => use password from LDAP Server
 */ 
$g_login_method				= 'MD5';

/** LDAP authentication credentials */
$g_ldap_server			= 'localhost';
$g_ldap_port			= '389';
$g_ldap_root_dn			= 'dc=mycompany,dc=com';
$g_ldap_organization	= '';    # e.g. '(organizationname=*Traffic)'
$g_ldap_uid_field		= 'uid'; # Use 'sAMAccountName' for Active Directory
$g_ldap_bind_dn			= ''; // 'my_bind_user' / Left empty if you LDAP server allows anonymous binding 
$g_ldap_bind_passwd	= ''; // 'my_bind_password' / Left empty if you LDAP server allows anonymous binding 

/** Regular expression to use when validating new user login names */
// This default regular expression: '/^[\w \-]+$/'
// allows a-z, A-z, 0-9, as well as space and underscore.  
// IMPORTANT: If you change this, you may want to update the $TLS_valid_user_name_format
//            string in the language files to explain the rules you are using on your site
// Reused MANTIS BTS code
$g_user_login_valid_regex='/^[\w \-]+$/';

/** Enable/disable Users to create accounts with default role by "new user" link on login page */
// TRUE => allow feature [STANDARD BEHAVIOUR]
$g_user_self_signup = TRUE; 



// ----------------------------------------------------------------------------
/** [API] */

/** SOAP API availability (disabled by default) */ 
$tlCfg->api_enabled = FALSE; 

// used to display API ID info in the *View pages 
$tlCfg->api_id_format = "[ID: %s ]";



// ----------------------------------------------------------------------------
/** [GUI LAYOUT] */

/** GUI themes (base for CSS and images)- modify if you create own one */
$tlCfg->theme_dir = 'gui/themes/default/';

/** Company logo (used by navigation bar and login page page) */
//$tlCfg->company_logo = '<img alt="TestLink" title="TestLink" style="width: 115px; height: 53px;" src="' . 
//                          $tlCfg->theme_dir . 'images/company_logo.png" />';
$tlCfg->company_logo = 'company_logo.png';

/** Image for main menu item bullet (just filename) */
$tlCfg->bullet_image = 'slide_gripper.gif';  // = [arrow_org.gif, slide_gripper.gif]

/** Availability of Test Project specific background colour */
// 'background'  -> standard behaviour for 1.6.x you can have a different
//                  background colour for every test project.
// 'none'        -> new behaviour no background color change 
// $tlCfg->gui->testproject_coloring = 'background'; // Francisco, do not change it!
$tlCfg->gui->testproject_coloring = 'none'; // I'm sorry default is not coloring using coloring is a pain
                                            // and useless
// @TODO Ok, then merge these two attributes into one                                           
/** default background color */
$tlCfg->gui->background_color = '#9BD';

/** 
 * Display name and surname in all user lists using a format defined in $g_username_format
 * TRUE - build a human readable display 
 * FALSE - show login name
 * @TODO - remove these useless parameter and use a format only (via $tlCfg->username_format)
 */ 
$tlCfg->show_realname = FALSE;

/** Display name definition (used to build a human readable display name for users) */
// '%first% %last%'          -> John Cook
// '%last%, %first%'          -> John Cook
// '%first% %last% %login%'    -> John Cook [ux555]
$tlCfg->username_format = '%login%';

/** Configure the frame frmWorkArea navigator width */
$tlCfg->frame_workarea_default_width = "30%";

/** true => icon edit will be added into <a href> as indication an edit features */
$tlCfg->gui->show_icon_edit=false;

/** Order to use when building a testproject combobox (value must be SQL compliant)*/
// 'ORDER BY name'
// 'ORDER_BY nodes_hierarchy.id DESC' -> similar effect to order last created firts
$tlCfg->gui->tprojects_combo_order_by='ORDER BY nodes_hierarchy.id DESC';

// used to round percentages on metricsDashboard.php
$tlCfg->dashboard_precision = 2;

/** Choose what kind of webeditor you want to use.
 * 'fckeditor'
 * 'tinymce'
 * 'none' -> use plain text area input field
 */
$tlCfg->gui_text_editor = 'fckeditor';

/** fckeditor Toolbar 
 * modify which icons will be available in html edit pages
 * refer to fckeditor configuration file 
 **/
$tlCfg->fckeditor_default_toolbar = "TL_Medium_2";



// ----------------------------------------------------------------------------
/** [GUI: TREE] */

/** 
 * TREE MENU 
 *	Definition of tree menu component: dTree, jTree or phplayersmenu.
 *	jTree has the best performance but others have a better functionality  
 *	[LAYERSMENU, DTREE, JTREE]
 */
$tlCfg->treemenu_type = 'JTREE';

// 20080525 - franciscom
// To allow two different type of tree menu engine
// while testing EXT JS.
// if = '' => $tlCfg->treemenu_type will be used
// MHT: This is temporary parameter and will be removed 
$tlCfg->spectreemenu_type = 'EXTJS';
 
/** Default ordering value for new Test Suites and Test Cases to separate them */
$tlCfg->treemenu_default_testsuite_order = 1;
$tlCfg->treemenu_default_testcase_order = 100;

/** show/hide testcase id on tree menu */
$tlCfg->treemenu_show_testcase_id = TRUE;


// ----------------------------------------------------------------------------
/** [GUI: Javascript libraries] */

/** ENABLED -> use EXT JS library; DISABLED - simple html */
$g_use_ext_js_library = ENABLED;

// May be in future another table sort engine will be better
// kryogenix.org -> Stuart Langridge sortTable
// '' (empty string) -> disable table sorting feature
$g_sort_table_engine='kryogenix.org';



// ----------------------------------------------------------------------------
/** [GENERATED DOCUMENTATION] */

/**
 * Texts and settings for printed documents
 * Leave text values empty if you would like to disable it.
 */
$tlCfg->document_generator->company_name = 'Testlink Community [configure using $tlCfg->company->name]';

/** Image is expected in directory <testlink_root>/gui/themes/<your_theme>/images/ */
$tlCfg->document_generator->company_copyright = '2008 (c) Testlink Community';
$tlCfg->document_generator->confidential_msg = '';

/** CSS used in printed html documents */
$tlCfg->document_generator->css_template = $tlCfg->theme_dir . 'css/tl_documents.css';

/** Misc settings */
$tlCfg->document_generator->tc_version_enabled = FALSE;



// ----------------------------------------------------------------------------
/** [Test Executions] */

// ASCending   -> last execution at bottom
// DESCending  -> last execution on top      [STANDARD BEHAVIOUR]
$tlCfg->exec_cfg->history_order = 'DESC';

// TRUE  -> the whole execution history for the choosen build will be showed
// FALSE -> just last execution for the choosen build will be showed [STANDARD BEHAVIOUR]
$tlCfg->exec_cfg->history_on = FALSE;


// TRUE  ->  test case VERY LAST (i.e. in any build) execution status will be displayed
// FALSE -> only last result on current build.  [STANDARD BEHAVIOUR]
$tlCfg->exec_cfg->show_last_exec_any_build = FALSE;

// TRUE  ->  History for all builds will be shown
// FALSE ->  Only history of the current build will be shown  [STANDARD BEHAVIOUR]
$tlCfg->exec_cfg->show_history_all_builds = FALSE;

// different models for the attachments management on execution page
// $att_model_m1 ->  shows upload button and title 
// $att_model_m2 ->  hides upload button and title
$tlCfg->exec_cfg->att_model = $att_model_m2;   //defined in const.inc.php

// ENABLED -> User can delete an execution result
// DISABLED -> User can not.  [STANDARD BEHAVIOUR]
$tlCfg->exec_cfg->can_delete_execution = DISABLED;

// ENABLED -> enable XML-RPC calls to external test automation server
//      new buttons will be displayed on execution pages
// DISABLED -> disable
$tlCfg->exec_cfg->enable_test_automation = DISABLED;

// ENABLED -> enable testcase counters by status on tree
$tlCfg->exec_cfg->enable_tree_testcase_counters = ENABLED;

// ENABLED -> test cases and test case counters will be coloured according to test case status
$tlCfg->exec_cfg->enable_tree_colouring = ENABLED;

// 20080303 - franciscom
// This can help to avoid performance problems.
// Controls what happens on right frame when user clicks on a testsuite on tree.
// ENABLED -> show all test cases presents on test suite and children test suite.
// DISABLED -> nothing happens, to execute a test case you need to click on test case
$tlCfg->exec_cfg->show_testsuite_contents = DISABLED;

// 1 -> user can edit execution notes, on old executions (Attention: user must have test case execution right)
// DISABLED -> no edit allowed [STANDARD BEHAVIOUR]
$tlCfg->exec_cfg->edit_notes = DISABLED;

// Filter Test cases a user with tester role can VIEW depending on
// test execution assignment.
// all: all test cases.
// assigned_to_me: test cases assigned to logged user.
// assigned_to_me_or_free: test cases assigned to logged user or not assigned
$tlCfg->exec_cfg->view_mode->tester='assigned_to_me';

// Filter Test cases a user with tester role can EXECUTE depending on
// test execution assignment.
// all: all test cases.
// assigned_to_me: test cases assigned to logged user.
// assigned_to_me_or_free: test cases assigned to logged user or not assigned
$tlCfg->exec_cfg->exec_mode->tester='assigned_to_me';

/** User filter in Test Execution navigator - default value */
// logged_user -> combo will be set to logged user
// none        -> no filter applied by default 
$tlCfg->exec_cfg->user_filter_default='none';



// ----------------------------------------------------------------------------
/** [Test Specification] */

// 'horizontal' ->  step and results on the same row
// 'vertical'   ->  steps on one row, results in the row bellow
$g_spec_cfg->steps_results_layout = 'vertical';

// 1 -> User will see a test suite filter while creating test specification
// 0 -> no filter available
$g_spec_cfg->show_tsuite_filter = 1;

// 1 -> every time user do some operation on test specification
//      tree is updated on screen.
// 0 -> tree will not be updated, user can update it manually.
$g_spec_cfg->automatic_tree_refresh = 1;

// 1 -> user can edit executed tc versions
// 0 -> editing of executed tc versions is blocked.  [STANDARD BEHAVIOUR]
$tlCfg->testcase_cfg->can_edit_executed = 0;

// used to create full external id in this way:
// testCasePrefix . g_testcase_cfg->glue_character . external_id
// CAN NOT BE EMPTY
$tlCfg->testcase_cfg->glue_character = '-';

/** Used when creating a Test Suite using copy 
   and you have choose  $g_action_on_duplicate_name = 'generate_new'
   if the name exist.
 */
$g_prefix_name_for_copy = strftime("%Y%m%d-%H:%M:%S", time());
        
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
$g_testcase_template->summary->type = 'none';
$g_testcase_template->summary->value = '';


$g_testcase_template->steps->type = 'none';
$g_testcase_template->steps->value = '';

$g_testcase_template->expected_results->type = 'none';
$g_testcase_template->expected_results->value = '';


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
$g_testsuite_template->details->type = 'none';
$g_testsuite_template->details->value = '';

/** 
BUGID 0000086: Using "|" in the testsuite name causes malformed URLs
regexp used to check for chars not allowed in:
test project, test suite and testcase names.
*/
$g_ereg_forbidden = "[|]";



// ----------------------------------------------------------------------------
/** [ATTACHMENTS] */

/** Attachment feature availability */
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
$g_repositoryPath = TL_ABS_PATH . "upload_area" . DIRECTORY_SEPARATOR;

/** 
 * compression used within the repository 
 * TL_REPOSITORY_COMPRESSIONTYPE_NONE => no compression
 * TL_REPOSITORY_COMPRESSIONTYPE_GZIP => gzip compression
 */
$g_repositoryCompressionType = TL_REPOSITORY_COMPRESSIONTYPE_NONE;

// the maximum allowed file size for each repository entry, default 1MB.
// Also check your PHP settings (default is usually 2MBs)
$tlCfg->repository_max_filesize = 1; //MB

// TRUE -> when you upload a file you can give no title
$g_attachments->allow_empty_title = TRUE;

// $g_attachments->allow_empty_title == TRUE, you can ask the system
// to do something 
// 
// 'none'         -> just write on db an empty title
// 'use_filename' -> use filename as title
//$g_attachments->action_on_save_empty_title='use_filename';
//
$g_attachments->action_on_save_empty_title = 'none';

// Remember that title is used as link description for download
// then if title is empty, what the system has to do when displaying ?
// 'show_icon'  -> the $g_attachments->access_icon will be used.
// 'show_label' -> the value of $g_attachments->access_string will be used .
$g_attachments->action_on_display_empty_title = 'show_icon';

$g_attachments->access_icon = '<img src="' . $tlCfg->theme_dir . 'images/new_f2_16.png" style="border:none" />';
$g_attachments->access_string = "[*]";

// Set display order of uploaded files - BUGID 1086
$g_attachments->order_by = " ORDER BY date_added DESC ";



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



// ----------------------------------------------------------------------------
/** [MISC FUNCTIONALITY] */

/** Maximum uploadfile size to importing stuff in TL */
// Also check your PHP settings (default is usually 2MBs)
$tlCfg->import_max_size = '204800'; // in bytes

/** Maximum line size of the imported file */
$tlCfg->import_max_row = '10000'; // in chars

/** Set the default role used for new users */
// - created from the login page.
// - created using user management features
$tlCfg->default_roleid = TL_ROLES_GUEST;

/** Allow/disallow to have Test Plans without dependency to Test Project.
 * TRUE  => allow Test Plan over all Test Projects (TL 1.5 compatibility)
 * FALSE => all Test Plans should have own Test Project   [STANDARD BEHAVIOUR]
 * This behaviour is obsolete and could not be supported in future
 **/
$g_show_tp_without_tproject_id = FALSE;

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

// TRUE  -> you can create multiple time the same keyword 
//           for the same product (term used on TL < 1.7) / test project (term used on TL>= 1.7) 
// FALSE ->   [STANDARD BEHAIVOUR]
// @TODO havlatm: remove the possibility duplicate it (have no sense)
$g_allow_duplicate_keywords = FALSE;

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

// Set this flag to automatically convert www URLs and
// email adresses into clickable links (Taken from Mantis BTS) 
$g_html_make_links = ON;

// These are the valid html tags for multi-line fields (e.g. description)
// do NOT include href or img tags here
// do NOT include tags that have parameters (eg. <font face="arial">)
$g_html_valid_tags = 'p, li, ul, ol, br, pre, i, b, u, em';

// These are the valid html tags for single line fields (e.g. issue summary).
// do NOT include href or img tags here
// do NOT include tags that have parameters (eg. <font face="arial">)
$g_html_valid_tags_single_line = 'i, b, u, em';





// ----- End of Config ------------------------------------------------
// --------------------------------------------------------------------
// DO NOT CHANGE NOTHING BELOW
// --------------------------------------------------------------------

// havlatm: @TODO move the next code out of config - configCheck.php -> included via common.php
/** Functions for check request status */
require_once('configCheck.php');

/** root of testlink directory location seen through the web server */
// @TODO: basehref should be defined by installation script or stored from login in $_SESSION
/*  20070106 - franciscom - this statement it's not 100% right      
    better use $_SESSION['basehref'] in the scripts. */      
define('TL_BASE_HREF', get_home_url()); 


clearstatcache();
if ( file_exists( TL_ABS_PATH . 'custom_config.inc.php' ) ) 
  require_once( TL_ABS_PATH . 'custom_config.inc.php' ); 

/** Support for localization */
// martin: @TODO move the code out of config
$serverLanguage = false;
// check for !== false because getenv() returns false on error
if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
	$serverLanguage = getenv($_SERVER['HTTP_ACCEPT_LANGUAGE']);
	
if(false !== $serverLanguage)
{
	if (array_key_exists($serverLanguage,$g_locales))
		$tlCfg->default_language = $serverLanguage;
}
define ('TL_DEFAULT_LOCALE', $tlCfg->default_language);

require_once(TL_ABS_PATH . 'cfg/userRightMatrix.php');


// --------------------------------------------------------------------
/** Converted and derived variables (Users should not modify this section) */
define('REFRESH_SPEC_TREE',$g_spec_cfg->automatic_tree_refresh ? 'yes' : 'no');
define('TL_SORT_TABLE_ENGINE',$g_sort_table_engine);
define("TL_REPOSITORY_MAXFILESIZE", 1024*1024*$tlCfg->repository_max_filesize); 

define('TL_XMLEXPORT_HEADER', "<?xml version=\"1.0\" encoding=\"" . $tlCfg->charset . "\"?>\n");

define('TL_THEME_BASE_DIR', $tlCfg->theme_dir);
define('TL_THEME_IMG_DIR', $tlCfg->theme_dir . 'images/');
define('TL_THEME_CSS_DIR', $tlCfg->theme_dir . 'css/');
define('TL_TESTLINK_CSS', TL_THEME_CSS_DIR . TL_CSS_MAIN);
define('TL_PRINT_CSS', TL_THEME_CSS_DIR . TL_CSS_PRINT);
define('TL_TREEMENU_CSS', TL_THEME_CSS_DIR . TL_CSS_TREEMENU);

// when a role is deleted, a new role must be assigned to all users
// having role to be deleted
// A right choice seems to be using $g_default_roleid.
// You can change this adding a config line in custom_config.inc.php
$g_role_replace_for_deleted_roles=$tlCfg->default_roleid;


/** 
 * @TODO remove from TL - unfinished refactorization; 
 * use $tlCfg instead of old variables and constants
 */
define('TL_IMPORT_LIMIT', $tlCfg->import_max_size); 
define('TL_IMPORT_ROW_MAX', $tlCfg->import_max_row); 
define('TL_JOMLA_1_CSS', TL_THEME_CSS_DIR . 'jos_template_css.css');	// @TODO move layout to main CSS
define('TL_ITEM_BULLET_IMG', TL_THEME_IMG_DIR . $g_main_menu_item_bullet_img);
define('TL_TREE_KIND', $tlCfg->treemenu_type);
define('USE_EXT_JS_LIBRARY', $g_use_ext_js_library);
define('WEBEDITOR',$tlCfg->gui_text_editor);
define('TL_TPL_CHARSET', $tlCfg->charset);
define('TITLE_SEP',$tlCfg->gui_title_separator_1);
define('TITLE_SEP_TYPE2',$tlCfg->gui_title_separator_2);
define('TITLE_SEP_TYPE3',$tlCfg->gui_title_separator_2); // obsolete: use type 1,2
define('TL_FRMWORKAREA_LEFT_FRAME_WIDTH', $tlCfg->frame_workarea_default_width); 
define('TL_API_ID_FORMAT',$tlCfg->api_id_format);
$g_log_level=$tlCfg->log_level;
$g_show_realname = $tlCfg->show_realname;
$g_username_format = $tlCfg->username_format;
$g_dashboard_precision = $tlCfg->dashboard_precision;
$g_fckeditor_toolbar = $tlCfg->fckeditor_default_toolbar;
$g_main_menu_item_bullet_img = $tlCfg->bullet_image; 
$g_api_id_format = $tlCfg->api_id_format;
$g_tree_show_testcase_id = $tlCfg->treemenu_show_testcase_id;
$g_tree_node_ordering->default_testcase_order  = $tlCfg->treemenu_default_testcase_order;
$g_tree_node_ordering->default_testsuite_order = $tlCfg->treemenu_default_testsuite_order;
$g_show_tp_without_prodid = $g_show_tp_without_tproject_id; // obsolete (use $g_show_tp_without_tproject_id)
$tlCfg->document_generator->company_logo = $tlCfg->company_logo; 
$g_tc_status = $tlCfg->results['status_code'];
$g_tc_status_css = $tlCfg->results['code_status'];
$g_tc_status_verbose_labels = $tlCfg->results['status_label'];
$g_tc_status_for_ui = $tlCfg->results['status_label']; 
$g_tc_status_for_ui_default = $tlCfg->results['default_status'];


// ----- END OF FILE --------------------------------------------------
?>