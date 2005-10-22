<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: config.inc.php,v $
 *
 * @version $Revision: 1.26 $
 * @modified $Date: 2005/10/22 09:58:34 $ by $Author: franciscom $
 *
 *
 * Constants and configuration parameters used throughout TestLink 
 * are defined within this file they should be changed for your environment
 *
 * Revisions:
 *
 * @author Francisco Mancardi - 20051022
 * added french locale and translations - thanks grdscarabe@grdscarabe.net
 * added portuguese locale and translations - thanks Leonardo Molinari
 *
 * 
 * @author Francisco Mancardi - 20051011
 * New constant to configure CSS files
 * Boolean values managed with TRUE/FALSE instead of 1/0 .
 *
 * @author Francisco Mancardi - 20051005
 * new config structures to manage L18N for date and time format
 * $g_locales_date_format, $g_locales_timestamp_format
 * 
 * @author Francisco Mancardi - 20051004 
 * $g_allow_duplicate_keywords
 *
 * @author Francisco Mancardi - 20051002 
 * - Test Plan filtering by product related configuration parameters
 *   $g_ui_show_check_filter_tp_by_product
 *
 * - New configuration parameters for Requirements Functionality
 *
 * @author Francisco Mancardi - 20050919 - g_timestamp_format
 * @author Francisco Mancardi - 20050915 - from 1.6.Beta1 to 1.6.RC1
 *
 * @author Francisco Mancardi - 20050908
 * New configuration parameters:
 * $g_check_names_for_duplicates
 * $g_action_on_duplicate_name
 * $g_prefix_name_for_copy
 *
 * Fixed BUGID 0000086: Using "|" in the component or category name causes malformed URLs
 *
 * @author Francisco Mancardi - 20050904 
 * added $g_show_tp_without_prodid to manage TL 1.5.1 compatibility.
 *
 * @author Francisco Mancardi - 20050827 
 * changes in $g_tc_status, $g_tc_sd_color
 * new config parameters: $g_date_format, $g_fckeditor_toolbar
 *
 * @author Francisco Mancardi - 20050822 - $tpl -> $g_tpl
 * 
 * @author Francisco Mancardi - 20050821
 * template configuration/customization
 *
 * @author Francisco Mancardi - 20050806 
 * Changes to support the installer
 * 
 * 
**/

// with the use of the installer, the file config_db.inc.php
// will be generated automatically.
// The following parameters regarding the TestLink Database (DB) 
// are defined in config_db.inc.php
//
// DB user and password to use for connecting to the testlink db  
// (DB_USER, DB_PASS)
//
// DB host to use when connecting to the testlink db 
// (DB_HOST)
//
// Name of the database that contains the testlink tables
// (DB_NAME);
//
// DB type being used by testlink (only mysql currently supported)
// (DB_TYPE)
//
require_once('config_db.inc.php');


/** root of testlink directory location seen through the web server */
define('TL_BASE_HREF', get_home_url()); 

/** Set this to TRUE if your MySQL DB supports UTF8 (MySQL version >= 4.1) */
define('DB_SUPPORTS_UTF8', TRUE);

/** Don't change these values or you will have problems! */
/* CHARSET */
define('TL_TPL_CHARSET', DB_SUPPORTS_UTF8  ? 'UTF-8' : 'ISO-8859-1');
/* Directory separator */
define('DS', DIRECTORY_SEPARATOR);

/** set the delimeter properly for the include_path */
define('DELIM', (PHP_OS == "WIN32" || PHP_OS == "WINNT") ? ';' : ':');

/** The root dir for the testlink installation with trailing slash */
define('TL_ABS_PATH', dirname(__FILE__) . DS);

/** The temporary dir for temporary files */
define('TL_TEMP_PATH', TL_ABS_PATH . 'gui'.DS.'templates_c'.DS);

/** Logging  @see logging.inc.php for more */
/** path for testlink logs; e.g. /tmp */
define('TL_LOG_PATH', TL_TEMP_PATH );


/** Default level of logging (NONE, ERROR, INFO, DEBUG, EXTENDED) */
define('TL_LOG_LEVEL_DEFAULT', 'NONE');
require_once(TL_ABS_PATH.'/lib/functions/logging.inc.php');

/** Is the metrics table displayed on the main page enabled? Accepts TRUE or FALSE values */
define('MAIN_PAGE_METRICS_ENABLED', 'FALSE');

/** some maxmima related to importing stuff in TL */
/** maximum uploadfile size */
define('TL_IMPORT_LIMIT', '200000'); // in bytes
/** maximum line size of the imported file */
define('TL_IMPORT_ROW_MAX', '10000'); // in chars


/** Bug Tracking systems */////////////////////////////////////////////////////
/** 
* TestLink uses bugtracking systems to check if displayed bugs resolved, verified, 
* and closed bugs. If they are it will strike through them
*/

/** 
* @var STRING TL_INTERFACE_BUGS = ['NO', 'BUGZILLA','MANTIS']
* BUGZILLA: edit configuration in TL_ABS_PATH/cfg/bugzilla.cfg.php
* MANTIS: edit configuration in TL_ABS_PATH/cfg/mantis.cfg.php
*/
define('TL_INTERFACE_BUGS', 'NO');
require_once(TL_ABS_PATH . 'lib/bugtracking/int_bugtracking.php');

/** Setting up the global include path for testlink */
ini_set('include_path', '.' . DELIM . TL_ABS_PATH . 'lib' . DS . 'functions' . DS . DELIM);

/**
* Set the session timeout value (in seconds).
* This will prevent sessions timing out after very short periods of time
*/
//ini_set('session.cache_expire',900);

/** Error reporting - do we want php errors to show up for users */
error_reporting(E_ALL & ~E_NOTICE);
 
/** GUI related constants *///////////////////////////////////////////////////

/* CVS will not released, MUST BE changed at the release day */
define('TL_VERSION', '1.6.RC2'); 
define('TL_BACKGROUND_DEFAULT', "#9BD");

/** 
*	Definition of tree menu component: dTree, jTree or phplayersmenu.
*	jTree has the best performance but others have a better functionality  
*	@varstatic string TL_TREE_KIND = [LAYERSMENU, DTREE, JTREE]
*/
define('TL_TREE_KIND', 'LAYERSMENU');

/* Some defines for I18N,L10N, don't touch */
define('TL_LOCALE_PATH',TL_ABS_PATH . 'locale/');
define('TL_HELP_RPATH','gui/help/');
define('TL_INSTRUCTIONS_RPATH','gui/help/');


/* Configure frmWorkArea frameset */
define('TL_FRMWORKAREA_LEFT_FRAME_WIDTH', "30%"); 


/* CSS configuration */
/* Standard */
define('TL_LOGIN_CSS','gui/css/tl_login.css');
define('TL_TESTLINK_CSS','gui/css/testlink.css');
define('TL_DOC_BASIC_CSS','gui/css/tl_doc_basic_css');

/* An example
define('TL_LOGIN_CSS','gui/css/theme0/tl_login.css');
define('TL_TESTLINK_CSS','gui/css/theme0/testlink.css');
define('TL_DOC_BASIC_CSS','gui/css/theme0/tl_doc_basic_css');
*/




/* TRUE -> Check if:
           a. Product Name                   is unique
           b. Component Name Inside Product  is unique
           c. Category Name Inside Component is unique
           d. Test Case Name inside Category is unique 
   FALSE -> don't check
*/
//$g_check_names_for_duplicates=FALSE;
$g_check_names_for_duplicates=TRUE;

/* 
if you have choose to check for unique names, what to do
when a duplicate name is found

'allow_repeat': allow the name to be repeated (backward compatibility)
'generate_new': generate a new name using $g_prefix_name_for_copy
'block'       : return with an error 

*/    
$g_action_on_duplicate_name='allow_repeat';

/*  */
$g_prefix_name_for_copy= strftime("%Y%m%d-%H:%M:%S", time());
        
/* 
BUGID 0000086: Using "|" in the component or category name causes malformed URLs
regexp used to check for chars not allowed in product, component , category name, 
and testcase title 
*/
$g_ereg_forbidden ="[|]";

/* TRUE -> TL 1.5.1 compatibility, get also Test Plans without product id. */
$g_show_tp_without_prodid=TRUE;


/* 
20051002 - fm
New Feature
TRUE -> user can enable/disable test plan filter by product 
        At user interface level a check box is displayed over
        the test plan combo box.
     
FALSE -> user can do nothing, no changes at UI.
         Test Plan always filtered by product
*/
$g_ui_show_check_filter_tp_by_product = TRUE;


/* TRUE -> you can create multiple time the same keyword for the same product */
$g_allow_duplicate_keywords=FALSE;

/*
Requirements - 

Test Case generation from Requirement
- use_req_spec_as_category_name
  FALSE -> test cases are created and assigned 
           to a category with name $g_req_cfg->default_category_name
  
  TRUE  -> REQuirement Specification Title is used a category name     
       
*/
$g_req_cfg->default_category_name="TODO";
$g_req_cfg->objective_for_category="Category/Test Cases generated from Requirements";

$g_req_cfg->default_component_name="Component Created by Requirement - Auto";
$g_req_cfg->scope_for_component="Component/Category/Test Cases generated from Requirements";

$g_req_cfg->use_req_spec_as_category_name=TRUE;



/*
20051002 - fm
Must be changed if Table definition changes
*/
$g_field_size->category_name=100;


/* fckeditor Toolbar */
//$g_fckeditor_toolbar = "TL_Medium";
$g_fckeditor_toolbar = "TL_Medium_2";



/* fr_FR -> thanks to grdscarabe@grdscarabe.net */
/* These are the supported locales */
$g_locales = array('en_GB' => 'English (UK)',
				           'it_IT' => 'Italian',
				           'es_AR' => 'Spanish (Argentine)',
				           'es_ES' => 'Spanish',
				           'de_DE' => 'German',
                   'fr_FR' => 'Fran&ccedil;ais',
                   'pt_BR' => 'Portuguese (Brazil)'
				          );

// ----------------------------------------------------------------------------
// 20051005 - francisco.mancardi@gruppotesi.com
// see strftime() in PHP manual
//
// Very IMPORTANT: 
// setting according local is done in testlinkInitPage() using set_dt_formats()
//
// Default values
$g_date_format ="%d/%m/%Y";
$g_timestamp_format = "%d/%m/%Y %H:%M:%S";

$g_locales_date_format = array('en_GB' => "%d/%m/%Y",
				                       'it_IT' => "%d/%m/%Y",
				                       'es_AR' => "%d/%m/%Y",
				                       'es_ES' => "%d/%m/%Y",
				                       'de_DE' => "%d.%m.%Y",
				                       'fr_FR' => "%d/%m/%Y",
				                       'pt_BR' => "%d/%m/%Y"
				                       ); 

$g_locales_timestamp_format = array('en_GB' => "%d/%m/%Y %H:%M:%S",
				                            'it_IT' => "%d/%m/%Y %H:%M:%S",
				                            'es_AR' => "%d/%m/%Y %H:%M:%S",
				                            'es_ES' => "%d/%m/%Y %H:%M:%S",
				                            'de_DE' => "%d.%m.%Y %H:%M:%S",
				                            'fr_FR' => "%d/%m/%Y %H:%M:%S",
				                            'pt_BR' => "%d/%m/%Y %H:%M:%S",
				                           ); 
// ----------------------------------------------------------------------------



/* Set this to your default locale, this must be one of $g_locales */
define('TL_DEFAULT_LOCALE','en_GB');


/* These are the possible TestCase statuses */
$g_tc_status = array ( "failed"        => 'f',
                       "blocked"       => 'b',
                       "passed"        => 'p',
                       "not_run"       => 'n',
                       "not_available" => 'x',
                       "unknown"       => 'u',
                       "all"           => 'all'
                      ); 

/* 20050508 - fm - enhancement */
/* TestCase Status Description -> color */
$g_tc_sd_color = array ( "failed"        => 'red',
                         "blocked"       => 'blue',
                         "passed"        => 'green',
                         "not_run"       => 'black',
                         "not_available" => 'yellow',
                         "unknown"       => 'black',
                         "all"           => 'cyan'
                       ); 


/** 
* Testlink Smarty class sets up the default smarty settings for testlink
*/
require_once(TL_ABS_PATH . 'third_party/smarty/Smarty.class.php'); 
require_once(TL_ABS_PATH . 'lib/general/tlsmarty.inc.php'); 

/** 
 * Next constants are used in printed documents.
 * Leave them empty if you would not to use.
 */ 
define('TL_COMPANY', '');
define('TL_DOC_COPYRIGHT', '');
define('TL_DOC_CONFIDENT', '');

/*
20050821 - fm
configurable templates
This help is you want to use a non standard template 
*/
$g_tpl=array();

// Standard
$g_tpl['tcView'] = "tcView.tpl";
$g_tpl['tcSearchView'] = "tcSearchView.tpl";
$g_tpl['tcEdit'] = "tcEdit.tpl";
$g_tpl['tcNew'] = "tcNew.tpl";
$g_tpl['execSetResults'] = "execSetResults.tpl";


// Custom
$g_tpl['tcView'] = "tcView.tpl";
$g_tpl['tcSearchView'] = $g_tpl['tcView'];

// ----- End of Config ------------------------------------------------


// -------------------------------------------------------------------
function get_home_url()
{
if ( isset ( $_SERVER['PHP_SELF'] ) ) {
	$t_protocol = 'http';
	if ( isset( $_SERVER['HTTPS'] ) && ( strtolower( $_SERVER['HTTPS'] ) != 'off' ) ) {
		$t_protocol = 'https';
	}

	// $_SERVER['SERVER_PORT'] is not defined in case of php-cgi.exe
	if ( isset( $_SERVER['SERVER_PORT'] ) ) {
		$t_port = ':' . $_SERVER['SERVER_PORT'];
		if ( ( ':80' == $t_port && 'http' == $t_protocol )
		  || ( ':443' == $t_port && 'https' == $t_protocol )) {
			$t_port = '';
		}
	} else {
		$t_port = '';
	}

	if ( isset( $_SERVER['HTTP_HOST'] ) ) {
		$t_host = $_SERVER['HTTP_HOST'];
	} else if ( isset( $_SERVER['SERVER_NAME'] ) ) {
		$t_host = $_SERVER['SERVER_NAME'] . $t_port;
	} else if ( isset( $_SERVER['SERVER_ADDR'] ) ) {
		$t_host = $_SERVER['SERVER_ADDR'] . $t_port;
	} else {
		$t_host = 'www.example.com';
	}

	$t_path = dirname( $_SERVER['PHP_SELF'] );
	if ( '/' == $t_path || '\\' == $t_path ) {
		$t_path = '';
	}

	$t_url	= $t_protocol . '://' . $t_host . $t_path.'/';
	
	return ($t_url);
}

}



//includes needed for userright checking
require_once(TL_ABS_PATH . 'lib/functions/getRights.php');
require_once(TL_ABS_PATH . 'cfg/userrightmatrix.php');
?>
