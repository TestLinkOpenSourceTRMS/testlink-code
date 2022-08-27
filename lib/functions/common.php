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
 * @Copyright   2005,2020 TestLink community 
 * @link        http://www.testlink.org
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
if( !defined('TL_APICALL') ) {
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
function tlAutoload($class_name)  {

  // exceptions
  // 1. remove prefix and convert lower case
  $tlClasses = null;
  $tlClassPrefixLen = 2;
  $classFileName = $class_name;

   
  // 2. add a lower case directory 
  $addDirToInclude = [];

  // this way Zend_Loader_Autoloader will take care of these classes.
  // Needed in order to make work bugzillaxmlrpc interface
  if( strstr($class_name,'Zend_') !== FALSE ) {
    return false;
  }

  // Workaround
  // https://github.com/smarty-php/smarty/issues/344 
  // https://github.com/smarty-php/smarty/pull/345
  if( strpos($class_name,'Smarty_Internal_Compile_') !== FALSE ) {
    return false;
  }

  if (isset($tlClasses[$classFileName])) {
    $len = tlStringLen($classFileName) - $tlClassPrefixLen;
    $classFileName = strtolower(tlSubstr($classFileName,$tlClassPrefixLen,$len));
  }
  
  if (isset($addDirToInclude[$class_name])) {
    $classFileName = strtolower($class_name) . "/" . $class_name;
  }  

  // Plugin special processing, class name ends with Plugin (see plugin_register())
  // Does not use autoload
  if( preg_match('/Plugin$/', $class_name) == 1 ) {
    return;
  }  


  // fix provided by BitNami for:
  // Reason: We had a problem integrating TestLink with other apps. 
  // You can reproduce it installing ThinkUp and TestLink applications in the same stack. 

  try {
      include_once $classFileName . '.class.php';
  } 
  catch (Exception $e) {
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
function doDBConnect(&$db,$onErrorExit=false) {
  global $g_tlLogger;
  
  $charSet = config_get('charset');
  $result = array('status' => 1, 'dbms_msg' => 'ok');

  switch(DB_TYPE) {
    case 'mssql':
      $dbDriverName = 'mssqlnative';    
    break;

    default:
      $dbDriverName = DB_TYPE;
    break;  
  }

  $db = new database($dbDriverName);
  $result = $db->connect(DSN, DB_HOST, DB_USER, DB_PASS, DB_NAME);

  if (!$result['status']) {
    echo $result['dbms_msg'];
    $result['status'] = 0;
    $search = array('<b>','</b>','<br>');
    $replace = array('',''," :: ");
    $logtext = ' Connect to database <b>' . DB_NAME . '</b> on Host <b>' . DB_HOST . '</b> fails <br>';
    $logtext .= 'DBMS Error Message: ' . $result['dbms_msg'];
    
    $logmsg  = $logtext . ($onErrorExit ? '<br>Redirection to connection fail screen.' : '');
    tLog(str_replace($search,$replace,$logmsg), 'ERROR');
    if( $onErrorExit ) {
      $smarty = new TLSmarty();
      $smarty->assign('title', lang_get('fatal_page_title'));
      $smarty->assign('content', $logtext);
      $smarty->assign('link_to_op', null);
      $smarty->display('workAreaSimple.tpl'); 
      exit();
    }
  }
  else {
    if((DB_TYPE == 'mysql') && ($charSet == 'UTF-8')) {
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
 * Set home URL path
 * @internal revisions
 */
function setPaths() 
{
  if (!isset($_SESSION['basehref'])) {
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
  if (isset($_SESSION['userID']) && $_SESSION['userID'] > 0) {
    $now = time();
    if (($now - $_SESSION['lastActivity']) <= (config_get("sessionInactivityTimeout") * 60)) {
      $_SESSION['lastActivity'] = $now;
      $user = new tlUser($_SESSION['userID']);
      $user->readFromDB($db);
      $_SESSION['currentUser'] = $user;
      $isValidSession = true;
    }
  }

  if (!$isValidSession && $redirect) {
    tLog('Invalid session from ' . $_SERVER["REMOTE_ADDR"] . 
         '. Redirected to login page.', 'INFO');
    
    $fName = "login.php";
    $baseDir = dirname($_SERVER['SCRIPT_FILENAME']);
        
    while (!file_exists($baseDir . DIRECTORY_SEPARATOR . $fName)) {
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
function doSessionStart($setPaths=false) {

  if( PHP_SESSION_NONE == session_status() ) {
    session_set_cookie_params(99999);
  }
  
  if(!isset($_SESSION)) {
    session_start();
  }
  
  if($setPaths) {
    unset($_SESSION['basehref']);
    setPaths();
  }
}


/**
 * Initialize structure of top menu for the user and the project.
 * 
 * @param integer $db DB connection identifier
 * @param hash $context
 */
function initTopMenu(&$db,$context,$tprojOpt) {

  $navBarMenu = '';

  // check if Project is available
  if( $context->tproject_id > 0) {
    $imageSet = TLSmarty::getImageSet();
    
    $tprojID = intval($context->tproject_id);
    $tplanID = intval($context->tplan_id);

    $menuCfg = config_get('guiTopMenu');
    $idx = 1; 
    foreach ($menuCfg as $ele) {
      // check if Test Plan is available

      if ((!isset($ele['condition'])) || ($ele['condition'] == '') ||
        (($ele['condition'] == 'TestPlanAvailable') && $tplanID > 0) ||
        (($ele['condition'] == 'ReqMgmtEnabled') && 
          isset($tprojOpt->requirementsEnabled) && 
          $tprojOpt->requirementsEnabled)) {
        // (is_null($ele['right']) => no right needed => display always

        $addItem = is_null($ele['right']);
        if(!$addItem) {
          if( is_array($ele['right'])) {
            foreach($ele['right'] as $rg) {
              if( $addItem = (has_rights($db,$rg) == "yes") ) {
                break;
              }   
            }  
          } else {
            $addItem = (has_rights($db,$ele['right']) == "yes");   
          } 
        } 

        if( $addItem ) {
          $url = $ele['url'];
          if( strpos($url, '?') === false ) {
            $url .= "?";  
          } else {
            $url .= "&";  
          }
          $url .= "tproject_id=$tprojID&tplan_id=$tplanID";

          $tg = $ele['target'];

          $navBarMenu .= 
            "<a href=\"$url\" target=\"$tg\" accesskey=\"{$ele['shortcut']}\"
             tabindex=\"" . $idx++ . '">';

          if( isset($ele['imgKey']) ) {
            $navBarMenu .= '<img src="' . 
              $imageSet[$ele['imgKey']] . '"' .
              ' title="' . lang_get($ele['label']) . '">'; 
          } else {
            $navBarMenu .= lang_get($ele['label']); 
          }  

          $navBarMenu .= "</a>&nbsp;&nbsp;&nbsp;";
        }
      }
    }
    $navBarMenu .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
  }

  return $navBarMenu;
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
  if( isset($_SESSION['locale']) && !is_null($_SESSION['locale']) ) {
    setDateTimeFormats($_SESSION['locale']);
  } 
  doDBConnect($db);
  
  if (!$pageStatistics && (config_get('log_level') == 'EXTENDED')) {
    $pageStatistics = new tlPageStatistics($db);
  }
  
  if (!$dontCheckSession) {
    checkSessionValid($db);
  }
  
  if ($userRightsCheckFunction) {
    checkUserRightsFor($db,$userRightsCheckFunction,$onFailureGoToLogin);
  }
   
   
  // used to disable the attachment feature if there are problems with repository path
  /** @TODO this check should not be done anytime but on login and using */
  global $g_repositoryPath;
  global $g_repositoryType;
  global $tlCfg;
  $tlCfg->attachments->disabled_msg = "";
  if($g_repositoryType == TL_REPOSITORY_TYPE_FS) {
    $ret = checkForRepositoryDir($g_repositoryPath);
    if(!$ret['status_ok']) {
      $tlCfg->attachments->enabled = FALSE;
      $tlCfg->attachments->disabled_msg = $ret['msg'];
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
function config_get($config_id, $default=null) {
  $t_value = (null == $default) ? '' : $default;  
  $t_found = false;  
  $logInfo = array('msg' => "config option not available: {$config_id}", 'level' => 'WARNING');
  if(!$t_found) {
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
 * @param array $tlInfo testlink info regarding checks on upload
 * @return string containing an error message (if any)
 */
function getFileUploadErrorMessage($fInfo,$tlInfo=null)
{
  $msg = null;
  if (isset($fInfo['error'])) {
    switch($fInfo['error']) {
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

  if (null == $msg && null != $tlInfo && $tlInfo->statusOK == false) {
    $msg = lang_get('FILE_UPLOAD_' . $tlInfo->statusCode);
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
  if( is_null($access_key) ) {
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
function getItemTemplateContents($itemTemplate, $webEditorName, $defaultText='') {
    $editorTemplate = config_get($itemTemplate);
    $value=$defaultText;
    if( !is_null($editorTemplate) ) {
      if (property_exists($editorTemplate, $webEditorName)) {
        switch($editorTemplate->$webEditorName->type) {
          case 'string':
            $value = $editorTemplate->$webEditorName->value;
          break;
             
          case 'string_id':
            $value = lang_get($editorTemplate->$webEditorName->value);
          break;
             
          case 'file':
            $value = getFileContents($editorTemplate->$webEditorName->value);
            if (is_null($value)) {
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
  foreach ($env as $key => $val) {
    $env[$key] = intval($val);
  }  
  
  if( $doExit = (is_null($env) || $env['tproject_id'] == 0) ) {
    logAuditEvent(TLS("audit_security_no_environment",$script), $action,$userObj->dbID,"users");
  }
   
  if( !$doExit ) {
    foreach($rightsToCheck->items as $verboseRight) {
      $status = $userObj->hasRight($dbHandler,$verboseRight,
                  $env['tproject_id'],$env['tplan_id'],true);
      if( ($doExit = !$status) && ($rightsToCheck->mode == 'and')) {
        $action = 'any';
        logAuditEvent(TLS("audit_security_user_right_missing",
          $userObj->login,$script,$action),
          $action,$userObj->dbID,"users");
        break;
      }
    }
  }

  if ($doExit) {   
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

/**
 *
 */
function tlSetCookie($ckObj) {
  $stdCk = config_get('cookie');

  foreach($ckObj as $prop => $value) {
    $stdCk->$prop = $value;
  }
  
  setcookie($stdCk->name, $stdCk->value, $stdCk->expire,$stdCk->path,
            $stdCk->domain,$stdCk->secure,$stdCk->httponly);
}


/**
 *
 * $opt: skip map each element can be a map
 *         tplanForInit 
 *         tplanToGetEffectiveRole
 */
function initUserEnv(&$dbH, $context, $opt=null) {
  $args = new stdClass();
  $gui = new stdClass();

  $optDeep = array('skip' => array('tplanForInit' => false,
                                   'tplanToGetEffectiveRole' => false));
  $options = array('forceCreateProj' => false,
                   'initNavBarMenu' => false,
                   'caller' => 'not provided');
  if (null != $opt) {
    if( isset($opt['skip']) ) {
      $optDeep['skip'] = array_merge($optDeep['skip'],$opt['skip']);
    }

    foreach ($options as $key => $defa) {
      if (isset($opt[$key])) {
        $options[$key] = $opt[$key];
      }
    }
  }

  $args->user = $_SESSION['currentUser'];
  $k2l = array( 'tproject_id' => 0, 
                'current_tproject_id' => 0,
                'tplan_id' => 0);

  foreach($k2l as $pp => $vv) {
    $args->$pp = $vv;  
    if( isset($_REQUEST[$pp]) ) {
      $args->$pp = intval($_REQUEST[$pp]);
    } else if (null != $context && property_exists($context, $pp)) {
      $args->$pp = intval($context->$pp);      
    }
  } 
  $tprjMgr = new testproject($dbH);
  $guiCfg = config_get("gui");
  $opx = array('output' => 'map_name_with_inactive_mark',
               'field_set' => $guiCfg->tprojects_combo_format,
               'order_by' => $guiCfg->tprojects_combo_order_by);

  $gui->prjSet = $tprjMgr->get_accessible_for_user($args->user->dbID, $opx);
  $gui->prjQtyWholeSystem = $tprjMgr->getItemCount();
  $gui->zeroTestProjects = ($gui->prjQtyWholeSystem == 0);
  $args->zeroTestProjects = $gui->zeroTestProjects; 

  $args->userIsBlindFolded = 
    (is_null($gui->prjSet) || count($gui->prjSet) == 0) 
    && $gui->prjQtyWholeSystem > 0;
  if( $args->userIsBlindFolded ) {
    $args->current_tproject_id = 0;
    $args->tproject_id = 0;
    $args->tplan_id = 0;
  }


  // It's ok to get testproject context if 
  // we have the testplan id?
  // Can this be a potential security issue?
  // Can this create a non coherent situation on GUI?  
  if( $args->tproject_id == 0 ) {
    $args->tproject_id = key($gui->prjSet);
  }

  if( $args->current_tproject_id == 0 ) {
    $args->current_tproject_id = $args->tproject_id;
  }

  $gui->caller = isset($_REQUEST['caller']) ? trim($_REQUEST['caller']) : '';
  $gui->tproject_id = intval($args->tproject_id);
  $gui->current_tproject_id = intval($args->current_tproject_id);
  $gui->tplan_id = intval($args->tplan_id);

  if( $gui->tproject_id > 0 ) {
    // Force to avoid lot of processing
    $gui->hasTestCases = $gui->hasKeywords = true;

    $gui->num_active_tplans = $tprjMgr->getActiveTestPlansCount($args->tproject_id);

    // get Test Plans available for the user 
    // $gpOpt = array('output' => 'map');
    $gpOpt = null;
    $gui->tplanSet = (array)$args->user->getAccessibleTestPlans($dbH,$args->tproject_id,$gpOpt);
    $gui->countPlans = count($gui->tplanSet);
  
    /* 20191212 - will remove because have created issues
       with IVU, and I not sure anymore of usefulness 
    if (false == $optDeep['skip']['tplanForInit'] || $args->tplan_id <= 0) {
      $gui->tplan_id = $args->tplan_id = (int)doTestPlanSetup($gui);
    }
    */
    if ($args->tplan_id <= 0) {
      $gui->tplan_id = $args->tplan_id = (int)doTestPlanSetup($gui);
    }
  } 

  $doInitUX = ($args->tproject_id > 0) || $options['forceCreateProj']; 
  $gui->grants = null;
  $gui->access = null;
  $gui->showMenu = null;
  $gui->activeMenu = setSystemWideActiveMenuOFF();

  /*
  if ($options['caller'] != 'not provided') {
    echo '<br> 1509 - caller => ' . $options['caller'] . '<br>';  
  }
  */

  if( $doInitUX ) {
    // echo 'doInitUX<br>';
    $gui->grants = getGrantSetWithExit($dbH,$args,$tprjMgr,$options);
    $gui->access = getAccess($gui);
    $gui->showMenu = getMenuVisibility($gui);

  /*
  if ($options['caller'] != 'not provided') {
    echo '<br> 1509 - caller => ' . $options['caller'] . '<br>';  
  }
  */
  }
  
  // Get Role Description to display.
  // This means get Effective Role that has to be calculated
  // using current test project & current test plan
  //
  // SKIP is useful if you want to consider role only
  // at test project level.
  //
  $tplan_id = $gui->tplan_id;
  if( $optDeep['skip']['tplanToGetEffectiveRole'] ) {
    $tplan_id = null;      
  } 

  $eRoleObj = $args->user->getEffectiveRole($dbH,$gui->tproject_id,$tplan_id);
  
  $cfg = config_get('gui');
  $gui->whoami = $args->user->getDisplayName() . ' ' . 
                 $cfg->role_separator_open . 
                 $eRoleObj->getDisplayName() . 
                 $cfg->role_separator_close;

  $gui->launcher = $_SESSION['basehref'] . 
    'lib/general/frmWorkArea.php';

  $gui->docs = config_get('userDocOnDesktop') ? getUserDocumentation() : null;

  $secCfg = config_get('config_check_warning_frequence');
  $gui->securityNotes = '';
  if( (strcmp($secCfg, 'ALWAYS') == 0) || 
        (strcmp($secCfg, 'ONCE_FOR_SESSION') == 0 && !isset($_SESSION['getSecurityNotesOnMainPageDone'])) ) {
    $_SESSION['getSecurityNotesOnMainPageDone'] = 1;
    $gui->securityNotes = getSecurityNotes($dbH);
  }  
  
  $gui->tprojOpt = $tprjMgr->getOptions($args->tproject_id);
  $gui->opt_requirements = 
    isset($gui->tprojOpt->requirementsEnabled) ? 
    $gui->tprojOpt->requirementsEnabled : null; 

  getActions($gui,$_SESSION['basehref']);

  if( $gui->current_tproject_id == null || 
    trim($gui->current_tproject_id) == '' ) {
  }

  $gui->logo = $_SESSION['basehref'] . TL_THEME_IMG_DIR .
               config_get('logo_navbar');
        

  $ft = 'form_token';             
  $gui->$ft = isset($args->$ft) ? $args->$ft : 0;
  if ($gui->$ft == 0 && isset($_REQUEST[$ft])) {
    $gui->$ft = $_REQUEST[$ft];
  }               
  $gui->treeFormToken = $gui->form_token;

  return array($args,$gui,$tprjMgr);
}

/**
 *
 * Actions for left side menu
 *
 */
function getActions(&$gui,$baseURL) {
  $bb = "{$baseURL}lib";

  $tproject_id = 0;
  if( property_exists($gui,'tproject_id')) {
    $tproject_id = intval($gui->tproject_id);
  }
  $ctx = "tproject_id={$tproject_id}";

  $tplan_id = 0;
  if( property_exists($gui,'tplan_id')) {
    $tplan_id = intval($gui->tplan_id);
  }
  $ctx .= "&tplan_id={$tplan_id}";

  $actions = new stdClass();

  $actions->events = "$bb/events/eventviewer.php?{$ctx}";
  $actions->usersAssign = "$bb/usermanagement/usersAssign.php?{$ctx}&featureType=testproject&featureID=" . intval($gui->tproject_id);

  $actions->userMgmt = "$bb/usermanagement/usersView.php?{$ctx}" . 
                       intval($gui->tproject_id);

  $actions->userInfo = "$bb/usermanagement/userInfo.php?{$ctx}";
  $actions->projectView = "$bb/project/projectView.php?{$ctx}";

  $actions->cfAssignment = "$bb/cfields/cfieldsTprojectAssign.php?{$ctx}";
  $actions->cfieldsView = "$bb/cfields/cfieldsView.php?{$ctx}";  

  $actions->keywordsView = "$bb/keywords/keywordsView.php?{$ctx}";
  $actions->platformsView = "$bb/platforms/platformsView.php?{$ctx}";
  $actions->issueTrackerView = "$bb/issuetrackers/issueTrackerView.php?{$ctx}";
  $actions->codeTrackerView = "$bb/codetrackers/codeTrackerView.php?{$ctx}";
  $actions->reqOverView = "$bb/requirements/reqOverview.php?{$ctx}";
  $actions->reqMonOverView = "$bb/requirements/reqMonitorOverview.php?{$ctx}";
  $actions->tcSearch = "$bb/testcases/tcSearch.php?doAction=userInput&{$ctx}";
  $actions->tcCreatedUser = "$bb/results/tcCreatedPerUserOnTestProject.php?do_action=uinput&{$ctx}";
  $actions->assignReq = "$bb/general/frmWorkArea.php?feature=assignReqs&{$ctx}";
  $actions->inventoryView = "$bb/inventory/inventoryView.php?{$ctx}";

  $actions->fullTextSearch = "$bb/search/searchMgmt.php?{$ctx}";

  $actions->metrics_dashboard =  
    "$bb/results/metricsDashboard.php?{$ctx}";


  $pp = $bb . '/plan';
  $actions->planView = "$pp/planView.php?{$ctx}";

  $actions->buildView = null;
  $actions->mileView = null;
  $actions->platformAssign = null;
  $actions->milestonesView = null;
  $actions->testcase_assignments = null;
  if ($tplan_id >0) {
    $actions->buildView = "$pp/buildView.php?{$ctx}";
    $actions->mileView = "$pp/planMilestonesView.php?{$ctx}";
    $actions->platformAssign = "$bb/platforms/platformsAssign.php?{$ctx}";
    $actions->milestonesView = "$bb/plan/planMilestonesView.php?{$ctx}";
    $actions->testcase_assignments =  
      "$bb/testcases/tcAssignedToUser.php?{$ctx}";
  }

  $launcher = $_SESSION['basehref'] . 
    "lib/general/frmWorkArea.php?feature=";

  $gui->workArea = new stdClass();
  $gui->workArea->testSpec = "editTc&{$ctx}";
  $gui->workArea->keywordsAssign = "keywordsAssign&{$ctx}";
  
  $gui->workArea->planAddTC = null;
  $gui->workArea->executeTest = null;
  $gui->workArea->setTestUrgency = null;
  $gui->workArea->planUpdateTC = null;
  $gui->workArea->showNewestTCV = null;
  $gui->workArea->assignTCVExecution = null;
  $gui->workArea->showMetrics = null;
  
  if ($tplan_id >0) {
    $gui->workArea->planAddTC = "planAddTC&{$ctx}";
    $gui->workArea->executeTest = "executeTest&{$ctx}";
    $gui->workArea->setTestUrgency = "test_urgency&{$ctx}";
    $gui->workArea->planUpdateTC = "planUpdateTC&{$ctx}";
    $gui->workArea->showNewestTCV = "newest_tcversions&{$ctx}";
    $gui->workArea->assignTCVExecution = "tc_exec_assignment&{$ctx}";
    $gui->workArea->showMetrics = "showMetrics&{$ctx}";
  }

  $gui->workArea->reqSpecMgmt = "reqSpecMgmt&{$ctx}";
  $gui->workArea->printReqSpec = "printReqSpec&{$ctx}";
  $gui->workArea->searchReq = "searchReq&{$ctx}";
  $gui->workArea->searchReqSpec = "searchReqSpec&{$ctx}";

  $wprop = get_object_vars($gui->workArea);
  foreach ($wprop as $wp => $wv) {
    if (null != $gui->workArea->$wp) {
      $gui->workArea->$wp = $launcher . $gui->workArea->$wp;
    }
    $actions->$wp = $gui->workArea->$wp;
  }

  $gui->uri = $actions;
  $p2l = get_object_vars($actions);
  foreach( $p2l as $pp => $val) {
    $gui->$pp = $actions->$pp;
  }
}


/**
 *
 */
function getGrantSetWithExit(&$dbHandler,&$argsObj,&$tprojMgr,$opt=null) {

  /** redirect admin to create testproject if not found */
  $options = array('forceCreateProj' => true);
  $options = array_merge($options,(array)$opt);

  if ($options['forceCreateProj'] && $argsObj->zeroTestProjects) {
    if ($argsObj->user->hasRight($dbHandler,'mgt_modify_product')) {
      redirect($_SESSION['basehref'] . 
        'lib/project/projectEdit.php?doAction=create');
      exit();
    }
  }

  // User has test project rights
  // This talks about Default/Global
  //
  // key: more or less verbose
  // value: string present on rights table

  $systemWideRights = 
    array(
      'project_edit' => 'mgt_modify_product',
      'configuration' => "system_configuraton",
      'usergroups' => "mgt_view_usergroups",
      'event_viewer' => "events_mgt",
      'user_mgmt' => "mgt_users"
    );
      
  $r2cTranslate = 
    array(
      'reqs_view' => "mgt_view_req", 
      'monitor_req' => "monitor_requirement", 
      'reqs_edit' => "mgt_modify_req",
      'keywords_view' => "mgt_view_key",
      'keywords_edit' => "mgt_modify_key",
      'view_tc' => "mgt_view_tc",
      'view_testcase_spec' => "mgt_view_tc",
      'modify_tc' => 'mgt_modify_tc',
      'testplan_create' => 'mgt_testplan_create');

  $r2cSame = array (
    'req_tcase_link_management','keyword_assignment',
    'issuetracker_management','issuetracker_view',
    'codetracker_management','codetracker_view',
    'platform_management','platform_view',
    'cfield_management',
    'cfield_view','cfield_assignment',
    'project_inventory_view','project_inventory_management',
    'testplan_unlink_executed_testcases',
    'testproject_delete_executed_testcases',
    'mgt_testplan_create',
    'testplan_execute','testplan_create_build',
    'testplan_metrics','testplan_planning',
    'testplan_user_role_assignment',
    'testplan_add_remove_platforms',
    'testplan_update_linked_testcase_versions',
    'testplan_set_urgent_testcases',
    'testplan_show_testcases_newest_versions',
    'testplan_milestone_overview',
    'exec_edit_notes','exec_delete','exec_ro_access',
    'exec_testcases_assigned_to_me','exec_assign_testcases');

  if( ($forceToNo = $argsObj->userIsBlindFolded) ) {
    $tr = array_merge($systemWideRights, $r2cTranslate);
    $grants = array_fill_keys(array_keys($tr), 'no');

    foreach($r2cSame as $rr) {
      $grants[$rr] = 'no';
    }
    return (object)$grants;      
  }  
  
  // Go ahead, continue with the analysis
  // First get system wide rights
  foreach ($systemWideRights as $humankey => $right) {
    $grants[$humankey] = $argsObj->user->hasRight($dbHandler,$right); 
  }

   
  foreach ($r2cTranslate as $humankey => $right) {
    $grants[$humankey] = 
      $argsObj->user->hasRight($dbHandler,$right,$argsObj->tproject_id,$argsObj->tplan_id); 
  }


  foreach ($r2cSame as $right) {
    $grants[$right] = 
      $argsObj->user->hasRight($dbHandler,$right,$argsObj->tproject_id,$argsObj->tplan_id); 
  }

  // check right ONLY if option is enabled
  $tprojOpt = $tprojMgr->getOptions($argsObj->tproject_id);
  if( property_exists($tprojOpt, 'inventoryEnabled') 
      && $tprojOpt->inventoryEnabled) {
    $invr = array('project_inventory_view','project_inventory_management');
    foreach($invr as $r){
      $grants[$r] = ($argsObj->user->hasRight($dbHandler,$r) == 'yes') ? 1 : 0;
    }
  }

  $grants['tproject_user_role_assignment'] = "no";
  if( $argsObj->user->hasRight($dbH,"testproject_user_role_assignment",
    $argsObj->tproject_id,-1) == "yes" ||
      $argsObj->user->hasRight($db,"user_role_assignment",null,-1) == "yes" ) {
      $grants['tproject_user_role_assignment'] = "yes";
  }
  return (object)$grants;  
}

/**
 *
 */
function getAccess(&$gui) {
  $k2l = array('codetracker','issuetracker','platform');
  foreach($k2l as $ak) {
    $access[$ak] = 'no';
    $p_m = $ak . '_management';
    $p_v = $ak . '_view';
    if( 'yes' == $gui->grants->$p_m || 
        'yes' == $gui->grants->$p_v ) {
      $access[$ak] = 'yes';
    }
  }
  return $access;
}


/**
 *
 *
 */
function getMenuVisibility(&$gui) 
{
  $showMenu = getFirstLevelMenuStructure();

  if($gui->tproject_id > 0  && 
     (   $gui->grants->view_tc == "yes" 
      || $gui->grants->reqs_view == "yes" 
      || $gui->grants->reqs_edit == "yes") ) {
    $showMenu['search'] = true;
  }

  //var_dump(__FUNCTION__,$gui->tproject_id,$gui->tplan_id); 
  if($gui->tproject_id > 0  && 
     ($gui->grants->cfield_assignment == "yes" ||
      $gui->grants->cfield_management == "yes" || 
      $gui->grants->issuetracker_management == "yes" || 
      $gui->grants->codetracker_management == "yes" || 
      $gui->grants->issuetracker_view == "yes" ||
      $gui->grants->codetracker_view == "yes") ) {
    $showMenu['system'] = true;
  }

  if($gui->tproject_id > 0  && 
     ($gui->grants->project_edit == "yes" || 
      $gui->grants->tproject_user_role_assignment == "yes" ||
      $gui->grants->cfield_management == "yes" || 
      $gui->grants->platform_management == "yes" || 
      $gui->grants->keywords_view == "yes") ) {
    $showMenu['projects'] = true;
  }

  if ( $gui->tproject_id > 0  && 
       //$gui->opt_requirements == true && TO REACTIVATE
       ($gui->grants->reqs_view == "yes" || 
        $gui->grants->reqs_edit == "yes" ||
        $gui->grants->monitor_req == "yes" || 
        $gui->grants->req_tcase_link_management == "yes") ) {
    $showMenu['requirements_design'] = true;
  }

  if($gui->tproject_id > 0  && 
     ($gui->grants->view_tc == "yes") ) {
    $showMenu['tests_design'] = true;
  }

  if($gui->tproject_id > 0  && 
     ($gui->grants->testplan_planning == "yes" ||
      $gui->grants->mgt_testplan_create == "yes" || 
      $gui->grants->testplan_user_role_assignment == "yes" ||
      $gui->grants->testplan_create_build == "yes") ) {
    $showMenu['plans'] = true;
  }

  if ($gui->tproject_id > 0  
      && $gui->tplan_id > 0 
      && ($gui->grants->testplan_execute == "yes" || 
          $gui->grants->exec_ro_access == "yes") ) {
    $showMenu['execution'] = true;
  }

  if($gui->tproject_id > 0 && $gui->tplan_id > 0) { 
    $showMenu['reports'] = true;
  }

  return $showMenu;
}

/**
 *
 */
function setSystemWideActiveMenuOFF() 
{
  $items = getFirstLevelMenuStructure();
  foreach( $items as $ky => $dm) {
    $items[$ky] = '';
  }
  return $items;
}

/**
 *
 */
function getFirstLevelMenuStructure() 
{
  return array('dashboard' => false,
               'search'=> false,    
               'system'=> false,
               'projects' => false,
               'requirements_design' => false,
               'tests_design' => false,
               'plans' => false,
               'execution' => false,
               'reports' => false,
              );
}


/**
 *
 *
 */
function doTestPlanSetup(&$gui) {
  $loop2do = count($gui->tplanSet);
  if( $loop2do == 0 ) {
    return $gui->tplan_id;
  }

  $index = 0;
  $found = 0;
  for($idx = 0; $idx < $loop2do; $idx++) {
    if( $gui->tplanSet[$idx]['id'] == $gui->tplan_id ) {
      $found = 1;
      $index = $idx;
      break;
    }
  }

  if( $found == 0 ) {
    $index = 0;
    $gui->tplan_id = $gui->tplanSet[$index]['id'];
  } 

  $gui->tplanSet[$index]['selected']=1;

  return $gui->tplan_id;
}

/**
 *
 */
function initContext()
{
  $_REQUEST = strings_stripSlashes($_REQUEST);
  $context = new stdClass();
  $env = '';
  $k2ctx = array('tproject_id' => 0,
                 'tplan_id' => 0,
                 'form_token' => 0);
  foreach ($k2ctx as $prop => $defa) {
    $context->$prop = isset($_REQUEST[$prop]) ? $_REQUEST[$prop] : $defa;
    if( is_numeric($defa) ) {
      $context->$prop = intval($context->$prop);    
    } 
    if ($env != '') {
      $env .= "&";
    }
    $env .= "$prop=" . $context->$prop;
  }

  // User is part of context, when _SESSION exists
  $context->userID = 0; 
  $context->user = null;

  if (isset($_SESSION['userID'])) {
    $context->userID = intval($_SESSION['userID']); 
  }

  if (isset($_SESSION['currentUser'])) {
    $context->user = $_SESSION['currentUser'];
  }

  return array($context,$env);
}
