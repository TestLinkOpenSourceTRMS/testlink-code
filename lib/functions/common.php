<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 * 
 * Load core functions for TestLink GUI
 * Common functions: database connection, session and data initialization,
 * maintain $_SESSION data, redirect page, log, etc.
 * 
 * Note: this file must uses only globally used functionality and cannot include 
 * a feature specific code because of performance and readability reasons
 *
 * @filesource  common.php
 * @package     TestLink
 * @author      TestLink community
 * @Copyright   2005,2016 TestLink community 
 * @link        http://www.testlink.org
 * @since       1.5
 *
 * @internal revisions
 * @since 1.9.16
 *
 */

/** core and parenthal classes */
require_once('object.class.php');
require_once('metastring.class.php');

/** Testlink Plugin API helper methods, must be included before lang_api.php */
require_once('plugin_api.php');

/** library for localization */
require_once('lang_api.php');

/** logging functions */
require_once('logging.inc.php');
require_once('logger.class.php');
require_once('pagestatistics.class.php');

/** library of database wrapper */
require_once('database.class.php');

/** user right checking */
require_once('roles.inc.php');

/** Testlink Smarty class wrapper sets up the default smarty settings for testlink */
require_once('tlsmarty.inc.php');

/** Initialize the Event System */
require_once('event_api.php' );

// Needed to avoid problems with Smarty 3
spl_autoload_register('tlAutoload');

/** CSRF security functions. */
/** TL_APICALL => TICKET 0007190 */
if( !defined('TL_APICALL') )
{
  require_once("csrf.php");
}  

/** Input data validation */
require_once("inputparameter.inc.php");

/** @TODO use the next include only if it is used -> must be removed */
// require_once("testproject.class.php"); 
require_once("treeMenu.inc.php");


// 20130526 checks need to be done in order to understand if this class is really needed
require_once("exec_cfield_mgr.class.php");   

/**
 * Automatic loader for PHP classes
 * See PHP Manual for details 
 */
function tlAutoload($class_name) 
{

  // exceptions
  // 1. remove prefix and convert lower case
  $tlClasses = null;
  $tlClassPrefixLen = 2;
  $classFileName = $class_name;
   
  // 2. add a lower case directory 
  $addDirToInclude = array('Kint' => true);

  // this way Zend_Loader_Autoloader will take care of these classes.
  // Needed in order to make work bugzillaxmlrpc interface
  if( strstr($class_name,'Zend_') !== FALSE )
  {
    return false;
  }
    
  if (isset($tlClasses[$classFileName]))
  {
    $len = tlStringLen($classFileName) - $tlClassPrefixLen;
    $classFileName = strtolower(tlSubstr($classFileName,$tlClassPrefixLen,$len));
  }
  
  if (isset($addDirToInclude[$class_name]))
  {
    $classFileName = strtolower($class_name) . "/" . $class_name;
  }  

  // Plugin special processing, class name ends with Plugin (see plugin_register())
  // Does not use autoload
  if( preg_match('/Plugin$/', $class_name) == 1 )
  {
    return;
  }  


  // fix provided by BitNami for:
  // Reason: We had a problem integrating TestLink with other apps. 
  // You can reproduce it installing ThinkUp and TestLink applications in the same stack.  
  try 
  {
    include_once $classFileName . '.class.php';
  } 
  catch (Exception $e)
  {
  }  
  
}


// ----- End of loading and begin functions ---------------------------------------------

/** @var integer global main DB connection identifier */
$db = 0;


/**
 * TestLink connects to the database
 *
 * @param &$db reference to resource, here resource pointer will be returned.
 * @param $onErrorExit default false, true standard page will be displayed
 *
 * @return array
 *         aa['status'] = 1 -> OK , 0 -> KO
 *         aa['dbms_msg''] = 'ok', or $db->error_msg().
 */
function doDBConnect(&$db,$onErrorExit=false)
{
  global $g_tlLogger;
  
  $charSet = config_get('charset');
  $result = array('status' => 1, 'dbms_msg' => 'ok');

  $db = new database(DB_TYPE);
  $result = $db->connect(DSN, DB_HOST, DB_USER, DB_PASS, DB_NAME);

  if (!$result['status'])
  {
    echo $result['dbms_msg'];
    $result['status'] = 0;
    $search = array('<b>','</b>','<br>');
    $replace = array('',''," :: ");
    $logtext = ' Connect to database <b>' . DB_NAME . '</b> on Host <b>' . DB_HOST . '</b> fails <br>';
    $logtext .= 'DBMS Error Message: ' . $result['dbms_msg'];
    
    $logmsg  = $logtext . ($onErrorExit ? '<br>Redirection to connection fail screen.' : '');
    tLog(str_replace($search,$replace,$logmsg), 'ERROR');
    if( $onErrorExit )
    {
      $smarty = new TLSmarty();
      $smarty->assign('title', lang_get('fatal_page_title'));
      $smarty->assign('content', $logtext);
      $smarty->assign('link_to_op', null);
      $smarty->display('workAreaSimple.tpl'); 
      exit();
    }
  }
  else
  {
    if((DB_TYPE == 'mysql') && ($charSet == 'UTF-8'))
    {
      $db->exec_query("SET CHARACTER SET utf8");
      $db->exec_query("SET collation_connection = 'utf8_general_ci'");
    }
  }
  
  // if we establish a DB connection, we reopen the session, 
  // to attach the db connection
  $g_tlLogger->endTransaction();
  $g_tlLogger->startTransaction();
  
  return $result;
}


/**
 * Set session data related to the current test plan
 * and saves a cookie with current testplan id
 * 
 * @param array $tplan_info result of DB query
 */
function setSessionTestPlan($tplan_info)
{
  if ($tplan_info)
  {
    $_SESSION['testplanID'] = $tplan_info['id'];
    $_SESSION['testplanName'] = $tplan_info['name'];

    // Save testplan id for next session
    $cookie_path = config_get('cookie_path');
    setcookie('TL_lastTestPlanForUserID_' . 1, $tplan_info['id'], TL_COOKIE_KEEPTIME, $cookie_path);

    tLog("Test Plan was adjusted to '" . $tplan_info['name'] . "' ID(" . $tplan_info['id'] . ')', 'INFO');
  }
  else
  {
    unset($_SESSION['testplanID']);
    unset($_SESSION['testplanName']);
  }
}


/**
 * Set home URL path
 * @internal revisions
 */
function setPaths()
{
  if (!isset($_SESSION['basehref']))
  {
    $_SESSION['basehref'] = get_home_url(array('force_https' => config_get('force_https')));
  } 
}


/** 
 * Verify if user is log in. Redirect to login page if not.
 * 
 * @param integer $db DB identifier 
 * @param boolean $redirect if true (default) redirects user to login page, otherwise returns true/false as login status
 **/
function checkSessionValid(&$db, $redirect=true)
{
  $isValidSession = false;
  if (isset($_SESSION['userID']) && $_SESSION['userID'] > 0)
  {
    $now = time();
    if (($now - $_SESSION['lastActivity']) <= (config_get("sessionInactivityTimeout") * 60))
    {
      $_SESSION['lastActivity'] = $now;
      $user = new tlUser($_SESSION['userID']);
      $user->readFromDB($db);
      $_SESSION['currentUser'] = $user;
      $isValidSession = true;
    }
  }
  if (!$isValidSession && $redirect)
  {
    tLog('Invalid session from ' . $_SERVER["REMOTE_ADDR"] . '. Redirected to login page.', 'INFO');
    
    $fName = "login.php";
    $baseDir = dirname($_SERVER['SCRIPT_FILENAME']);
        
    while(!file_exists($baseDir . DIRECTORY_SEPARATOR . $fName))
    {
      $fName = "../" . $fName;
    }
    $destination = "&destination=" . urlencode($_SERVER['REQUEST_URI']);
    redirect($fName . "?note=expired" . $destination,"top.location");
    exit();
  }
  return $isValidSession;
}


/**
 * Start session
 */
function doSessionStart($setPaths=false)
{
  session_set_cookie_params(99999);
  if(!isset($_SESSION))
  {
    session_start();
    if(defined('KINT_ON') && KINT_ON)
    {
      Kint::enabled(true);      
    }  
    else
    {
      Kint::enabled(false);      
    }  
  }
  
  if($setPaths)
  {
    unset($_SESSION['basehref']);
    setPaths();
  }
}


/**
 * Initialize structure of top menu for the user and the project.
 * 
 * @param integer $db DB connection identifier
 * @uses $_SESSION Requires initialized project, test plan and user data.
 * @since 1.9
 *
 * @internal revisions
 */
function initTopMenu(&$db)
{
  $_SESSION['testprojectTopMenu'] = '';
  $guiTopMenu = config_get('guiTopMenu');

  $imageSet = TLSmarty::getImageSet();

  // check if Project is available
  if (isset($_SESSION['testprojectID']) && $_SESSION['testprojectID'] > 0)
  {
    $idx = 1; 
    foreach ($guiTopMenu as $element)
    {
      // check if Test Plan is available
      if ((!isset($element['condition'])) || ($element['condition'] == '') ||
        (($element['condition'] == 'TestPlanAvailable') && 
          isset($_SESSION['testplanID']) && $_SESSION['testplanID'] > 0) ||
        (($element['condition'] == 'ReqMgmtEnabled') && 
          isset($_SESSION['testprojectOptions']->requirementsEnabled) && 
            $_SESSION['testprojectOptions']->requirementsEnabled))
      {
        // (is_null($element['right']) => no right needed => display always

        $addItem = is_null($element['right']);
        if(!$addItem)
        {
          if( is_array($element['right']))
          {
            foreach($element['right'] as $rg)
            {
              if( $addItem = (has_rights($db,$rg) == "yes") )
              {
                break;
              }   
            }  
          } 
          else
          {
            $addItem = (has_rights($db,$element['right']) == "yes");   
          } 
        } 

        if( $addItem )
        {
          $_SESSION['testprojectTopMenu'] .= "<a href='{$element['url']}' " .
          "target='{$element['target']}' accesskey='{$element['shortcut']}'" .
          "tabindex=''" . $idx++ . "''>";

          if( isset($element['imgKey']) )
          {
           $_SESSION['testprojectTopMenu'] .= '<img src="' . $imageSet[$element['imgKey']] . '"' .
                                              ' title="' . lang_get($element['label']) . '">'; 
          }  
          else
          {
           $_SESSION['testprojectTopMenu'] .= lang_get($element['label']); 
          }  

          $_SESSION['testprojectTopMenu'] .= "</a>&nbsp;&nbsp;&nbsp;";
        }
      }
    }
    $_SESSION['testprojectTopMenu'] .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
  }
}


/**
 * Update Project and Test Plan data on Project change or startup
 * Data are stored in $_SESSION array
 * 
 * If we receive TestPlan ID in the _SESSION then do some checks and if everything OK
 * Update this value at Session Level, to set it available in other pieces of the application
 * 
 * @param integer $db DB connection identifier
 * @param array $hash_user_sel input data for the page ($_REQUEST)
 * 
 * @uses initMenu() 
 * @internal revisions
 **/
function initProject(&$db,$hash_user_sel)
{
  $tproject = new testproject($db);
  $user_sel = array("tplan_id" => 0, "tproject_id" => 0 );
  $user_sel["tproject_id"] = isset($hash_user_sel['testproject']) ? intval($hash_user_sel['testproject']) : 0;
  $user_sel["tplan_id"] = isset($hash_user_sel['testplan']) ? intval($hash_user_sel['testplan']) : 0;

  $tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;

  // test project is Test Plan container, then we start checking the container
  if( $user_sel["tproject_id"] != 0 )
  {
    $tproject_id = $user_sel["tproject_id"];
  }
  // We need to do checks before updating the SESSION to cover the case that not defined but exists
  if (!$tproject_id)
  {
    $all_tprojects = $tproject->get_all();
    if ($all_tprojects)
    {
      $tproject_data = $all_tprojects[0];
      $tproject_id = $tproject_data['id'];
    }
  }
  $tproject->setSessionProject($tproject_id);
  
  // set a Test Plan
  // Refresh test project id after call to setSessionProject
  $tproject_id = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;
  $tplan_id = isset($_SESSION['testplanID']) ? $_SESSION['testplanID'] : null;
  // Now we need to validate the TestPlan
  // dolezalz, havlatm: added remember the last selection by cookie
  $cookieName = "TL_user${_SESSION['userID']}_proj${tproject_id}_testPlanId";
  if($user_sel["tplan_id"] != 0)
  {
    $tplan_id = $user_sel["tplan_id"];
  
    $cookie_path = config_get('cookie_path');  
    setcookie($cookieName, $tplan_id, time()+60*60*24*90, $cookie_path);
  } 
  elseif (isset($_COOKIE[$cookieName])) 
  {
    $tplan_id = intval($_COOKIE[$cookieName]);
  }
  
  // check if the specific combination of testprojectid and testplanid is valid
  $tplan_data = $_SESSION['currentUser']->getAccessibleTestPlans($db,$tproject_id,$tplan_id);
  if(is_null($tplan_data))
  {
    // Need to get first accessible test plan for user, if any exists.
    $tplan_data = $_SESSION['currentUser']->getAccessibleTestPlans($db,$tproject_id);
  }
  
  if(!is_null($tplan_data) && is_array($tplan_data))
  {
    $tplan_data = $tplan_data[0];
    setSessionTestPlan($tplan_data);
  }
  
  // initialize structure of top menu for the user and the project
  initTopMenu($db);   
}


/**
 * General GUI page initialization procedure
 * - init session
 * - init database
 * - check rights
 * - initialize project data (if requested)
 * 
 * @param integer $db DB connection identifier
 * @param boolean $initProject (optional) Set true if adjustment of Test Project or  
 *                      Test Plan is required; default is FALSE
 * @param boolean $dontCheckSession (optional) Set to true if no session should be started
 * @param string $userRightsCheckFunction (optional) name of function used to check user right needed
 *                           to execute the page
 */
function testlinkInitPage(&$db, $initProject = FALSE, $dontCheckSession = false,
                          $userRightsCheckFunction = null, $onFailureGoToLogin = false)
{
  static $pageStatistics = null;

  doSessionStart();
  setPaths();
  if( isset($_SESSION['locale']) && !is_null($_SESSION['locale']) )
  {
    setDateTimeFormats($_SESSION['locale']);
  } 
  doDBConnect($db);
  
  if (!$pageStatistics && (config_get('log_level') == 'EXTENDED'))
  {
    $pageStatistics = new tlPageStatistics($db);
  }
  
  if (!$dontCheckSession)
  {
    checkSessionValid($db);
  }
  
  if ($userRightsCheckFunction)
  {
    checkUserRightsFor($db,$userRightsCheckFunction,$onFailureGoToLogin);
  }
   
  // Init plugins
  plugin_init_installed();
   
  // adjust Product and Test Plan to $_SESSION
  if ($initProject)
  {
    initProject($db,$_REQUEST);
  }
   
  // used to disable the attachment feature if there are problems with repository path
  /** @TODO this check should not be done anytime but on login and using */
  global $g_repositoryType;
  global $g_attachments;
  global $g_repositoryPath;
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
}


/**
 * Redirect page to another one
 *
 * @param   string   URL of required page
 * @param   string   Browser location - use for redirection or refresh of another frame
 *                   Default: 'location'
 */
function redirect($url, $level = 'location')
{
  // XSS Attack - 06486: Cross-Site Scripting on login page
  $safeUrl = addslashes($url);
  echo "<html><head></head><body>";
  echo "<script type='text/javascript'>";
  echo "$level.href='$safeUrl';";
  echo "</script></body></html>";
  
  exit;
}


/**
 * Security parser for input strings
 * 
 * @param string $parameter
 * @return string cleaned parameter
 */
function strings_stripSlashes($parameter,$bGPC = true)
{
  if ($bGPC && !ini_get('magic_quotes_gpc'))
  { 
    return $parameter;
  }

  if (is_array($parameter))
  {
    $retParameter = null;
    if (sizeof($parameter))
    {
      foreach($parameter as $key=>$value)
      {
        if (is_array($value))
        {  
          $retParameter[$key] = strings_stripSlashes($value,$bGPC);
        }
        else
        {  
          $retParameter[$key] = stripslashes($value);
        }  
      }
    }
    return $retParameter;
  }
  else
  {  
    return stripslashes($parameter);
  }  
}


function to_boolean($alt_boolean)
{
  $the_val = 1;

  if (is_numeric($alt_boolean) && !intval($alt_boolean))
  {
    $the_val = 0;
  }
  else
  {
    $a_bool = array ("on" => 1, "y" => 1, "off" => 0, "n" => 0);
    $alt_boolean = strtolower($alt_boolean);
    if(isset($a_bool[$alt_boolean]))
    {
      $the_val = $a_bool[$alt_boolean];
    }
  }

  return $the_val;
}


/**
 * Validate string by relular expression
 *
 * @param string $str2check
 * @param string $regexp_forbidden_chars Regular expression (perl format)
 *
 * @return boolean 1: check ok, 0:check KO
 * 
 * @todo havlatm: remove as obsolete or move to inputparam.inc.php
 */
function check_string($str2check, $regexp_forbidden_chars)
{
  $status_ok = 1;

  if( $regexp_forbidden_chars != '' && !is_null($regexp_forbidden_chars))
  {
    if (preg_match($regexp_forbidden_chars, $str2check))
    {
      $status_ok=0;
    }
  }
  return $status_ok;
}


/**
 * Load global configuration to function
 * 
 * @param string $config_id key for identification of configuration parameter
 * @return mixed the configuration parameter(s)
 * 
 * @internal Revisions
 */
function config_get($config_id)
{
  $t_value = '';  
  $t_found = false;  
  $logInfo = array('msg' => "config option not available: {$config_id}", 'level' => 'WARNING');
  if(!$t_found)
  {
    $my = "g_" . $config_id;
    if( ($t_found = isset($GLOBALS[$my])) )
    {
      $t_value = $GLOBALS[$my];
    }
    else
    {
      $cfg = $GLOBALS['tlCfg'];
      if( ($t_found = property_exists($cfg,$config_id)) )
      {
        $t_value = $cfg->$config_id;
      }
    }
    
    if( $t_found )
    {
      $logInfo['msg'] = "config option: {$config_id} is " . 
                ((is_object($t_value) || is_array($t_value)) ? serialize($t_value) : $t_value);
      $logInfo['level'] = 'INFO';
    }
  }
  
  tLog($logInfo['msg'],$logInfo['level']);
  return $t_value;
}


/**  
 * @return boolean Return true if the parameter is an empty string or a string
 * containing only whitespace, false otherwise
 * @author Copyright (C) 2000 - 2004  Mantis Team, Kenzaburo Ito
 */ 
function is_blank( $p_var ) 
{
  $p_var = trim( $p_var );
  $str_len = strlen( $p_var );
  if ( 0 == $str_len ) {
    return true;
  }
  return false;
}


/**
 * Builds the header needed to make the content available for downloading
 *
 * @param string $content the content which should be downloaded
 * @param string $fileName the filename
 **/
function downloadContentsToFile($content,$fileName,$opt=null)
{
  $my = array();
  $my['opt'] = array('Content-Type' => 'text/plain');
  $my['opt'] = array_merge($my['opt'], (array)$opt);
  $charSet = config_get('charset');

  ob_get_clean();
  header('Pragma: public' );
  header('Content-Type: ' . $my['opt']['Content-Type'] . "; charset={$charSet}; name={$fileName}" );
  header('Content-Transfer-Encoding: BASE64;' );
  header('Content-Disposition: attachment; filename="' . $fileName .'"');
  echo $content;
}


/**
 * helper function for performance timing
 * 
 * @TODO havlatm: Andreas, move to logger?
 * returns: ?
 */
function microtime_float()
{
   list($usec, $sec) = explode(" ", microtime());
   return ((float)$usec + (float)$sec);
}


/**
 * Converts a priority weight (urgency * importance) to HIGH, MEDUIM or LOW
 *
 * @return integer HIGH, MEDUIM or LOW
 */
function priority_to_level($priority) {
  $urgencyImportance = config_get('urgencyImportance');
  
  if ($priority >= $urgencyImportance->threshold['high']) {
    return HIGH;
  } else if ($priority < $urgencyImportance->threshold['low']) {
    return LOW;
  } else {
    return MEDIUM;
  }
}


/**
 * Get the named php ini variable but return it as a bool
 * 
 * @author Copyright (C) 2000 - 2004  Mantis Team, Kenzaburo Ito
 */
function ini_get_bool( $p_name ) {
  $result = ini_get( $p_name );

  if ( is_string( $result ) ) {
    switch ( $result ) {
      case 'off':
      case 'false':
      case 'no':
      case 'none':
      case '':
      case '0':
        return false;
        break;
      case 'on':
      case 'true':
      case 'yes':
      case '1':
        return true;
        break;
    }
  } else {
    return (bool)$result;
  }
}


/**
 * Trim string and limit to N chars
 * 
 * @param string
 * @param int [len]: how many chars return
 *
 * @return string trimmed string
 *
 * @author Francisco Mancardi - 20050905 - refactoring
 */
function trim_and_limit($s, $len = 100)
{
  $s = trim($s);
  if (tlStringLen($s) > $len) {
    $s = tlSubStr($s, 0, $len);
  }

  return $s;
}


/** @todo havlatm - 20100207 - what's that? and why here. Remove' */
// nodes_order format:  NODE_ID-?,NODE_ID-?
// 2-0,10-0,3-0
function transform_nodes_order($nodes_order,$node_to_exclude=null)
{
  $fa = explode(',',$nodes_order);

  foreach($fa as $key => $value)
  {
  // $value= X-Y
  $fb = explode('-',$value);

  if( is_null($node_to_exclude) || $fb[0] != $node_to_exclude)
  {
     $nodes_id[]=$fb[0];
  }
  }

  return $nodes_id;
}


/**
 * Checks $_FILES for errors while uploading
 * 
 * @param array $fInfo an array used by uploading files ($_FILES)
 * @return string containing an error message (if any)
 */
function getFileUploadErrorMessage($fInfo)
{
  $msg = null;
  if (isset($fInfo['error']))
  {
    switch($fInfo['error'])
    {
      case UPLOAD_ERR_INI_SIZE:
        $msg = lang_get('error_file_size_larger_than_maximum_size_check_php_ini');
      break;
      
      case UPLOAD_ERR_FORM_SIZE:
        $msg = lang_get('error_file_size_larger_than_maximum_size');
      break;
      
      case UPLOAD_ERR_PARTIAL:
      case UPLOAD_ERR_NO_FILE:
        $msg = lang_get('error_file_upload');
      break;
    }
  }
  return $msg;
}


/**
 * Redirect to a page with static html defined in locale/en_GB/texts.php
 * 
 * @param string $key keyword for finding exact html text in definition array
 */
function show_instructions($key, $refreshTree=0)
{
    $myURL = $_SESSION['basehref'] . "lib/general/staticPage.php?key={$key}";
    
    if( $refreshTree )
    {
        $myURL .= "&refreshTree=1";  
    }
    redirect($myURL);
}


/**
 * @TODO: franciscom - 20091003 - document return value
 */
function templateConfiguration($template2get=null)
{
  $custom_templates = config_get('tpl');
  $access_key = $template2get;
  if( is_null($access_key) )
  {
    $access_key = str_replace('.php','',basename($_SERVER['SCRIPT_NAME']));
  }
  
  $path_parts=explode("/",dirname($_SERVER['SCRIPT_NAME']));
  $last_part=array_pop($path_parts);
  $tcfg = new stdClass();
  $tcfg->template_dir = "{$last_part}/";
  $tcfg->default_template = isset($custom_templates[$access_key]) ? $custom_templates[$access_key] : ($access_key . '.tpl');
  $tcfg->template = null;
  $tcfg->tpl = $tcfg->template_dir . $tcfg->default_template;
  return $tcfg;
}


/**
 * Check if an string is a valid ISO date/time
 *          accepted format: YYYY-MM-DD HH:MM:SS
 * 
 * @param string $ISODateTime datetime to check
 * @return boolean True if string has correct format
 * 
 * @internal   
 * rev: 20080907 - franciscom - Code taked form PHP manual
 */
function isValidISODateTime($ISODateTime)
{
   $dateParts=array('YEAR' => 1, 'MONTH' => 2 , 'DAY' => 3);
   
   $matches=null;
   $status_ok=false;
   if (preg_match("/^(\d{4})-(\d{2})-(\d{2}) ([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/", $ISODateTime, $matches)) 
   {
       $status_ok=checkdate($matches[$dateParts['MONTH']],$matches[$dateParts['DAY']],$matches[$dateParts['YEAR']]);
   }
   return $status_ok;
}

/**
 * Check if a localized timestamp is valid
 * uses split_localized_date()
 *
 */
function is_valid_date($timestamp, $dateFormat) {
  $date_array = split_localized_date($timestamp,$dateFormat);
  
  $status_ok = false;
  if ($date_array != null) {
    $status_ok = checkdate($date_array['month'],$date_array['day'],$date_array['year']);
  }
  
  return $status_ok;
}

/**
 * Returns array containing date pieces for a given timestamp according to dateFormat
 */

function split_localized_date($timestamp,$dateFormat) 
{
  if(strlen(trim($timestamp)) == 0)
  {
    return null;
  }
  
  $splitChar = null;
  $needle = array(".","-","/","%");
  foreach($needle as $target)
  {
    if (strpos($timestamp,$target) !== false) 
    {
      $splitChar = $target;
      break;
    }
  }
  // put each char of strippedDateFormat into an Array Element
  $strippedDateFormat = str_replace($needle,"",$dateFormat);
  $format = preg_split('//', $strippedDateFormat, -1, PREG_SPLIT_NO_EMPTY);
  $pieces = explode($splitChar,$timestamp);
  $result = array();
  if( count($pieces) == 3 )  // MAGIC ALLOWED 
  {
    $k2t = array('Y' => 'year', 'm' => 'month', 'd' => 'day');
    foreach ($format as $idx => $access) 
    {
      $result[$k2t[$access]] = $pieces[$idx];
    }
  }
  return $result;
}


/**
 * 
 *
 */
function checkUserRightsFor(&$db,$pfn,$onFailureGoToLogin=false)
{
  $script = basename($_SERVER['PHP_SELF']);
  $currentUser = $_SESSION['currentUser'];
  $doExit = false;
  $action = null;

  $m2call = $pfn;
  $arguments = null;
  if( is_object($pfn) )
  {
    $m2call = $pfn->method;
    $arguments = $pfn->args;
  }
  
  
  if (!$m2call($db,$currentUser,$arguments,$action))
  {
    if (!$action)
    {
      $action = "any";
    }
    logAuditEvent(TLS("audit_security_user_right_missing",$currentUser->login,$script,$action),
                  $action,$currentUser->dbID,"users");
    $doExit = true;
  }
  
  if($doExit)
  {   
    $myURL = $_SESSION['basehref'];
    if($onFailureGoToLogin)
    {
      unset($_SESSION['currentUser']);
      redirect($myURL ."login.php");
    }
    else
    {   
      redirect($myURL,"top.location");
    }
    exit();
  }
}


function tlStringLen($str)
{
  $charset = config_get('charset'); 
  $nLen = iconv_strlen($str,$charset);
  if ($nLen === false)
  {
    throw new Exception("Invalid UTF-8 Data detected!");
  }
  return $nLen; 
}


function tlSubStr($str,$start,$length = null)
{
  $charset = config_get('charset');
  if ($length === null)
  {
    $length = iconv_strlen($str,$charset);
  } 
  // BUGID 3951: replaced iconv_substr() by mb_substr()
  $function_call = "mb_substr";
  if (function_exists('iconv_substr') && version_compare(PHP_VERSION, '5.2.0') >= 0) {
    $function_call = "iconv_substr";
  }
  return $function_call($str,$start,$length,$charset);
}

/**
 * Get text from a configured item template for editor objects
 * 
 * @param $itemTemplate identifies a TestLink item that can have
 *        templates that can be loaded when creating an item to semplify
 *        or guide user's work.
 *        $itemTemplate is a property (of type stdClass) of $tlCfg configuration object.
 *
 *        supported values:
 *        testcase_template
 *
 * @param $webEditorName webeditor name, that identifies a propety of $tlCfg->$itemTemplate
 *        that holds input tenmplate configuration
 * 
 * @param $defaultText text to use if:
 *        $tlCfg->itemTemplate OR $tlCfg->itemTemplate->$webEditorName 
 *        does not exists.
 *
 */
function getItemTemplateContents($itemTemplate, $webEditorName, $defaultText='') 
{
    $editorTemplate = config_get($itemTemplate);
    $value=$defaultText;
    if( !is_null($editorTemplate) )
    {
      if (property_exists($editorTemplate, $webEditorName)) 
      {
        switch($editorTemplate->$webEditorName->type)
        {
          case 'string':
            $value = $editorTemplate->$webEditorName->value;
          break;
             
          case 'string_id':
            $value = lang_get($editorTemplate->$webEditorName->value);
          break;
             
          case 'file':
            $value = getFileContents($editorTemplate->$webEditorName->value);
            if (is_null($value))
            {
              $value = lang_get('problems_trying_to_access_template') . 
                       " {$editorTemplate->$webEditorName->value} ";
            } 
          break;
             
          default:
            $value = '';
          break;
        }
      }
    }
    return $value; 
}


/**
 * Builds a string $testCasePrefix . $glueChar . $external_id
 *
 * @param string $testCasePrefix prefix for the project without glue character
 * @param mixed $external_id
 */
function buildExternalIdString($testCasePrefix, $external_id)
{
  static $glueChar;
  if (!$glueChar) {
    $glueChar = config_get('testcase_cfg')->glue_character;
  }
  return $testCasePrefix . $glueChar . $external_id;

}

/**
 * 
 *
 */
function displayMemUsage($msg='')
{
  $dx = date('l jS \of F Y h:i:s A');
  echo "<br>{$msg} :: <b>{$dx}</b> <br>";       
  ob_flush();flush();
  echo "memory:" . memory_get_usage() . " - PEAK -> " . memory_get_peak_usage() .'<br>';
  ob_flush();flush();
}

/**
 *
 */
function setUpEnvForRemoteAccess(&$dbHandler,$apikey,$rightsCheck=null,$opt=null)
{
  $my = array('opt' => array('setPaths' => false,'clearSession' => false));
  $my['opt'] = array_merge($my['opt'],(array)$opt);

  if($my['opt']['clearSession'])
  {
    $_SESSION = null;
  }

  doSessionStart($my['opt']['setPaths']);
  if( isset($_SESSION['locale']) && !is_null($_SESSION['locale']) )
  {
    setDateTimeFormats($_SESSION['locale']);
  } 
  doDBConnect($dbHandler);

  $user = tlUser::getByAPIKey($dbHandler,$apikey);
  if( count($user) == 1 )
  {
    $_SESSION['lastActivity'] = time();
    $userObj = new tlUser(key($user));
    $userObj->readFromDB($dbHandler);
    $_SESSION['currentUser'] = $userObj;
    $_SESSION['userID'] = $userObj->dbID;
    $_SESSION['locale'] = $userObj->locale;

    // if user do this:
    // 1. login to test link
    // 2. get direct link and open in new tab or new window while still logged 
    // 3. logout
    // If user refresh tab / window open on (2), because on (3) we destroyed
    // session we have loose basehref, and we are not able to recreate it.
    // Without basehref we are not able to get CSS, JS, etc.
    // In this situation we destroy session, this way user is forced to login
    // again in one of two ways
    // a. using the direct link
    // b. using traditional login
    // In both way we assure that behaivour will be OK.
    //
    if(!isset($_SESSION['basehref']))
    {
      session_unset();
      session_destroy();
      if(property_exists($rightsCheck, 'redirect_target') && !is_null($rightsCheck->redirect_target))
      {
        redirect($rightsCheck->redirect_target);  
      } 
      else
      {
        // best guess for all features that live on ./lib/results/
        redirect("../../login.php?note=logout");  
      } 
        
      exit();
    }  
 


    if(!is_null($rightsCheck))
    {
      checkUserRightsFor($dbHandler,$rightsCheck,true);
    }
  }
}



/*
  returns map with config values and strings translated (using lang_get()) 
  to be used on user interface  for a Test link configuration option that 
  is structure in this way:
    config_option = array( string_value => any_value, ...)

    All this works if TL_ strings defined on strings.txt follows this naming standard.  

    For a config option like:
    $tlCfg->workflowStatus=array('draft' => 1, 'review' => 2);
 

    will exists:  $TL_workflowStatus_draft='...';
                  $TL_workflowStatus_review='...';

    @param string configKey: valus used on call to standard test link
                             method to get configuration option

    @param string accessMode: two values allowed 'key', 'code'
                              indicates how the returned map must be indexed.

                              'key' => will be indexed by string                          
                                       value that is key of config option

                              'code' => will be indexed by value of config option         

  @example

   $tlCfg->workflowStatus=array('draft' => 1, 'review' => 2);
   $i18nlabels = getLabels('workflowStatus','key');
   array_keys($i18nlabels) will return array('draft','review');
 

   $tlCfg->workflowStatus=array('draft' => 1, 'review' => 2);
   $i18nlabels = getLabels('workflowStatus','code');
   array_keys($i18nlabels) will return array(1,2);

   @internal revisions
   @since 1.9.7
*/

function getConfigAndLabels($configKey,$accessMode='key')
{
  $stringKeyCode = config_get($configKey);
  $labels=null;
  foreach( $stringKeyCode as $accessKey => $code )
  {
    $index = ($accessMode == 'key') ? $accessKey : $code;
    $labels[$index] = lang_get($configKey . '_' . $accessKey);
  }
  return array('cfg' => $stringKeyCode, 'lbl' => $labels); 
}


function setDateTimeFormats($locale)
{
  global $tlCfg;

  if($tlCfg->locales_date_format[$locale])
  {
    $tlCfg->date_format = $tlCfg->locales_date_format[$locale];
  }

  if($tlCfg->locales_timestamp_format[$locale])
  {
    $tlCfg->timestamp_format = $tlCfg->locales_timestamp_format[$locale];
  }
}

/**
 * windowCloseAndOpenerReload()
 * will close a popup window and reload caller contents.
 */
function windowCloseAndOpenerReload()
{
  echo "<html><head></head><body>";
  echo "<script type='text/javascript'>";
  echo "window.opener.location.reload(true);";
  echo "window.close();";
  echo "</script></body></html>";
  exit;
}


/**
 *
 */
function setUpEnvForAnonymousAccess(&$dbHandler,$apikey,$rightsCheck=null,$opt=null)
{
  $my = array('opt' => array('setPaths' => false,'clearSession' => false));
  $my['opt'] = array_merge($my['opt'],(array)$opt);

  if($my['opt']['clearSession'])
  {
    $_SESSION = null;
  }

  doSessionStart($my['opt']['setPaths']);
  if( isset($_SESSION['locale']) && !is_null($_SESSION['locale']) )
  {
    setDateTimeFormats($_SESSION['locale']);
  } 
  doDBConnect($dbHandler);

  // @since 1.9.14
  $checkMode = 'paranoic'; 
  if(property_exists($rightsCheck->args, 'envCheckMode'))
  {
    $checkMode = $rightsCheck->args->envCheckMode;
  }

  switch($checkMode)
  {
    case 'hippie':
      $tk = array('testplan','testproject');
    break;

    default:
      $tk[] = (intval($rightsCheck->args->tplan_id) != 0) ? 'testplan' : 'testproject';
    break;
  }

  foreach($tk as $ak)
  {
    $item = getEntityByAPIKey($dbHandler,$apikey,$ak);
    if(!is_null($item))
    {
      break;
    }  
  }  

  $status_ok = false;
  if( !is_null($item) )
  {
    $_SESSION['lastActivity'] = time();
    $userObj = new tlUser();
    $_SESSION['currentUser'] = $userObj;
    $_SESSION['userID'] = -1;
    $_SESSION['locale'] = config_get('default_language');

    // if user do this:
    // 1. login to test link
    // 2. get direct link and open in new tab or new window while still logged 
    // 3. logout
    // If user refresh tab / window open on (2), because on (3) we destroyed
    // session we have loose basehref, and we are not able to recreate it.
    // Without basehref we are not able to get CSS, JS, etc.
    // In this situation we destroy session, this way user is forced to login
    // again in one of two ways
    // a. using the direct link
    // b. using traditional login
    // In both way we assure that behaivour will be OK.
    //
    if(!isset($_SESSION['basehref']))
    {
      // echo $rightsCheck->redirect_target;
      session_unset();
      session_destroy();
      if(property_exists($rightsCheck, 'redirect_target') && !is_null($rightsCheck->redirect_target))
      {
        redirect($rightsCheck->redirect_target);  
      } 
      else
      {
        // best guess for all features that live on ./lib/results/
        redirect("../../login.php?note=logout");  
      } 
      exit();
    }  

    if(!is_null($rightsCheck->method))
    {
      checkUserRightsFor($dbHandler,$rightsCheck->method,true);
    }
    $status_ok = true;
  }

  return $status_ok;
}

/**
 *
 */
function getEntityByAPIKey(&$dbHandler,$apiKey,$type)
{
  $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
  
  $tables = tlObjectWithDB::getDBTables(array('testprojects','testplans'));
  switch ($type) 
  {
    case 'testproject':
      $target = $tables['testprojects'];
    break;
    
    case 'testplan':
      $target = $tables['testplans'];
    break;

    default:
      throw new Exception("Aborting - Bad type", 1);
    break;
  }
  
  $sql = "/* $debugMsg */ " .
         " SELECT id FROM {$target} " .
         " WHERE api_key = '" . 
         $dbHandler->prepare_string($apiKey) . "'";
 
  $rs = $dbHandler->get_recordset($sql);
  return ($rs ? $rs[0] : null);
}

/**
 *
 *
 */
function checkAccess(&$dbHandler,&$userObj,$context,$rightsToCheck)
{
  // name of caller script
  $script = basename($_SERVER['PHP_SELF']); 
  $doExit = false;
  $action = 'any';
  $env = array('tproject_id' => 0, 'tplan_id' => 0);
  $env = array_merge($env, $context);
  foreach($env as $key => $val)
  {
    $env[$key] = intval($val);
  }  
  
  if( $doExit = (is_null($env) || $env['tproject_id'] == 0) )
  {
    logAuditEvent(TLS("audit_security_no_environment",$script), $action,$userObj->dbID,"users");
  }
   
  if( !$doExit )
  {
    foreach($rightsToCheck->items as $verboseRight)
    {
      $status = $userObj->hasRight($dbHandler,$verboseRight,
                  $env['tproject_id'],$env['tplan_id'],true);
      if( ($doExit = !$status) && ($rightsToCheck->mode == 'and'))
      { 
        $action = 'any';
        logAuditEvent(TLS("audit_security_user_right_missing",$userObj->login,$script,$action),
                  $action,$userObj->dbID,"users");
        break;
      }
    }
  }

  if ($doExit)
  {   
    redirect($_SESSION['basehref'],"top.location");
    exit();
  }
}

/*
  function: getWebEditorCfg

  args:-

  returns:

*/
function getWebEditorCfg($feature='all')
{
  $cfg = config_get('gui');
  $defaultCfg = $cfg->text_editor['all'];
  $webEditorCfg = isset($cfg->text_editor[$feature]) ? $cfg->text_editor[$feature] : $defaultCfg;
  
  foreach($defaultCfg as $key => $value)
  {
    if(!isset($webEditorCfg[$key]))
    {
      $webEditorCfg[$key] = $defaultCfg[$key];
    }   
  } 
  return $webEditorCfg;
}

/**
 *
 */
function downloadXls($fname,$xlsType,$gui,$filePrefix)
{
  $sets = array();
  $sets['Excel2007'] = array('ext' => '.xlsx', 
                             'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
  $sets['Excel5'] = array('ext' => '.xls', 
                          'Content-Type' => 'application/vnd.ms-excel');


  $dct = array('Content-Type' =>  $sets[$xlsType]['Content-Type']);
  $content = file_get_contents($fname);
  $f2d = $filePrefix . $gui->tproject_name . '_' . $gui->tplan_name . 
         $sets[$xlsType]['ext'];

  downloadContentsToFile($content,$f2d,$dct);
  unlink($fname);
  exit();    
}

/**
 * POC on papertrailapp.com
 */
function syslogOnCloud($message, $component = "web", $program = "TestLink") 
{
  $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
  foreach(explode("\n", $message) as $line) 
  {
    $syslog_message = "<22>" . date('M d H:i:s ') . $program . ' ' . 
                      $component . ': ' . $line;
    socket_sendto($sock, $syslog_message, strlen($syslog_message), 0,
                  'logs5.papertrailapp.com', 11613);
  }
  socket_close($sock);
}

/**
 *
 */
function getSSODisable()
{
  return isset($_REQUEST['ssodisable']) ? 1 : 0;
}
