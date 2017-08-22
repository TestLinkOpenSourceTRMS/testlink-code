<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Allows editing a user
 *
 * @package     TestLink
 * @copyright   2005-2017, TestLink community
 * @filesource  usersEdit.php
 * @link        http://www.testlink.org
 *
 */
require_once('../../config.inc.php');
require_once('users.inc.php');
require_once('email_api.php');
require_once('Zend/Validate/Hostname.php');

testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();
$args = init_args();
$gui = initializeGui($db,$args);
$lbl = initLabels();

$highlight = initialize_tabsmenu();

$actionOperation = array('create' => 'doCreate', 'edit' => 'doUpdate',
                         'doCreate' => 'doCreate', 'doUpdate' => 'doUpdate',
                         'resetPassword' => 'doUpdate',
                         'genAPIKey' => 'doUpdate');

switch($args->doAction)
{
  case "edit":
    $highlight->edit_user = 1;
  break;
  
  case "doCreate":
    $highlight->create_user = 1;
    $gui->op = doCreate($db,$args);
    $gui->user = $gui->op->user;
    $templateCfg->template = $gui->op->template;
    $gui->main_title = $lbl['action_create_user'];
  break;
  
  case "doUpdate":
    $highlight->edit_user = 1;
    $sessionUserID = $_SESSION['currentUser']->dbID;
    $gui->op = doUpdate($db,$args,$sessionUserID);
    $gui->user = $gui->op->user;
    $gui->main_title = $lbl['action_edit_user'];
  break;

  case "resetPassword":
    $highlight->edit_user = 1;
    $passwordSendMethod = config_get('password_reset_send_method');
    $gui->op = createNewPassword($db,$args,$gui->user,$passwordSendMethod);
    $gui->main_title = $lbl['action_edit_user'];
  break;
  
  case "genAPIKey":
    $highlight->edit_user = 1;
    $gui->op = createNewAPIKey($db,$args,$gui->user);
    $gui->main_title = $lbl['action_edit_user'];
  break;

  case "create":
  default:
    $highlight->create_user = 1;
    $gui->user = new tlUser();
    $gui->main_title = $lbl['action_create_user'];
  break;
}

$gui->op->operation = $actionOperation[$args->doAction];
$roles = tlRole::getAll($db,null,null,null,tlRole::TLOBJ_O_GET_DETAIL_MINIMUM);
unset($roles[TL_ROLES_UNDEFINED]);

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);

$smarty->assign('highlight',$highlight);
$smarty->assign('operation',$gui->op->operation);
$smarty->assign('user_feedback',$gui->op->user_feedback);
$smarty->assign('external_password_mgmt', tlUser::isPasswordMgtExternal($gui->user->authentication));
$smarty->assign('optRights',$roles);
renderGui($smarty,$args,$templateCfg);


/**
 * 
 *
 */
function init_args()
{
  $_REQUEST=strings_stripSlashes($_REQUEST);
  $iParams = array("delete" => array(tlInputParameter::INT_N),
                   "user" => array(tlInputParameter::INT_N),
                   "user_id" => array(tlInputParameter::INT_N),
                   "rights_id" => array(tlInputParameter::INT_N),
                   "doAction" => array(tlInputParameter::STRING_N,0,30),
                   "firstName" => array(tlInputParameter::STRING_N,0,50),
                   "lastName" => array(tlInputParameter::STRING_N,0,50),
                   "emailAddress" => array(tlInputParameter::STRING_N,0,100),
                   "locale" => array(tlInputParameter::STRING_N,0,10),
                   "login" => array(tlInputParameter::STRING_N,0,100),
                   "password" => array(tlInputParameter::STRING_N,0,32),
                   "authentication" => array(tlInputParameter::STRING_N,0,10),
                   "user_is_active" => array(tlInputParameter::CB_BOOL));

  $args = new stdClass();
  R_PARAMS($iParams,$args);
 
  $date_format = config_get('date_format');

  // convert expiration date to ISO format to write to db
  $dk = 'expiration_date';
  $args->$dk = null;
  if (isset($_REQUEST[$dk]) && $_REQUEST[$dk] != '') 
  {
    $da = split_localized_date($_REQUEST[$dk], $date_format);
    if ($da != null) 
    {
      // set date in iso format
      $args->$dk = $da['year'] . "-" . $da['month'] . "-" . $da['day'];
    }
  }

  $args->user = $_SESSION['currentUser'];
  return $args;
}

/*
  function: doCreate

  args:

  returns: object with following members
           user: tlUser object
           status:
           template: will be used by viewer logic.
                     null -> viewer logic will choose template
                     other value -> viever logic will use this template.



*/
function doCreate(&$dbHandler,&$argsObj)
{
  $op = new stdClass();
  $op->user = new tlUser();
  $op->status = $op->user->setPassword($argsObj->password);
  $op->template = 'usersEdit.tpl';
  $op->operation = '';

  $statusOk = false;
  if ($op->status >= tl::OK)
  {
    initializeUserProperties($op->user,$argsObj);
    $op->status = $op->user->writeToDB($dbHandler);
    if($op->status >= tl::OK)
    {
      tlUser::setExpirationDate($dbHandler,$op->user->dbID,$argsObj->expiration_date);

      $statusOk = true;
      $op->template = null;
      logAuditEvent(TLS("audit_user_created",$op->user->login),"CREATE",$op->user->dbID,"users");
      $op->user_feedback = sprintf(lang_get('user_created'),$op->user->login);
    }
  }

  if (!$statusOk)
  {
    $op->operation = 'create';
    $op->user_feedback = getUserErrorMessage($op->status);
  }

  return $op;
}

/**
 *
 */ 
function doUpdate(&$dbHandler,&$argsObj,$sessionUserID)
{
  $op = new stdClass();
  $op->user_feedback = '';
  $op->user = new tlUser($argsObj->user_id);
  $op->status = $op->user->readFromDB($dbHandler);
  if ($op->status >= tl::OK)
  {
    initializeUserProperties($op->user,$argsObj);
    $op->status = $op->user->writeToDB($dbHandler);
    if ($op->status >= tl::OK)
    {
      tlUser::setExpirationDate($dbHandler,$op->user->dbID,$argsObj->expiration_date);

      logAuditEvent(TLS("audit_user_saved",$op->user->login),"SAVE",$op->user->dbID,"users");

      if ($sessionUserID == $argsObj->user_id)
      {
        $_SESSION['currentUser'] = $op->user;
        setUserSession($dbHandler,$op->user->login, $argsObj->user_id,
                       $op->user->globalRoleID, $op->user->emailAddress, $op->user->locale);
  
        if (!$argsObj->user_is_active)
        {
          header("Location: ../../logout.php");
          exit();
        }
      }
    }
    $op->user_feedback = getUserErrorMessage($op->status);
  }
  return $op;
}

/**
 * 
 */
function createNewPassword(&$dbHandler,&$argsObj,&$userObj,$newPasswordSendMethod)
{
  $op = new stdClass();
  $op->user_feedback = '';
  $op->new_password = '';
  
  // Try to validate mail configuration
  //
  // From Zend Documentation
  // You may find you also want to match IP addresses, Local hostnames, or a combination of all allowed types. 
  // This can be done by passing a parameter to Zend_Validate_Hostname when you instantiate it. 
  // The paramter should be an integer which determines what types of hostnames are allowed. 
  // You are encouraged to use the Zend_Validate_Hostname constants to do this.
  // The Zend_Validate_Hostname constants are: ALLOW_DNS to allow only DNS hostnames, ALLOW_IP to allow IP addresses, 
  // ALLOW_LOCAL to allow local network names, and ALLOW_ALL to allow all three types. 
  // 
  $validator = new Zend_Validate_Hostname(Zend_Validate_Hostname::ALLOW_ALL);
  $smtp_host = config_get( 'smtp_host' );
  
  $password_on_screen = ($newPasswordSendMethod == 'display_on_screen');
  if( $validator->isValid($smtp_host) || $password_on_screen )
  {
    $dummy = resetPassword($dbHandler,$argsObj->user_id,$newPasswordSendMethod);

    $op->user_feedback = $dummy['msg'];
    $op->status = $dummy['status'];
    $op->new_password = $dummy['password'];
    if ($op->status >= tl::OK)
    {
      logAuditEvent(TLS("audit_pwd_reset_requested",$userObj->login),"PWD_RESET",$argsObj->user_id,"users");
      $op->user_feedback = lang_get('password_reseted');
      if( $password_on_screen )
      {
        $op->user_feedback = lang_get('password_set') . $dummy['password'];     
      }
    }
    else
    {
      $op->user_feedback = sprintf(lang_get('password_cannot_be_reseted_reason'),$op->user_feedback);
    }
  }
  else
  {
    $op->status = tl::ERROR;
    $op->user_feedback = lang_get('password_cannot_be_reseted_invalid_smtp_hostname');
  }
  return $op;
}

/**
 * 
 */
function createNewAPIKey(&$dbHandler,&$argsObj,&$userObj)
{
  $op = new stdClass();
  $op->user_feedback = '';
  
  // Try to validate mail configuration
  //
  // From Zend Documentation
  // You may find you also want to match IP addresses, Local hostnames, or a combination of all allowed types. 
  // This can be done by passing a parameter to Zend_Validate_Hostname when you instantiate it. 
  // The paramter should be an integer which determines what types of hostnames are allowed. 
  // You are encouraged to use the Zend_Validate_Hostname constants to do this.
  // The Zend_Validate_Hostname constants are: ALLOW_DNS to allow only DNS hostnames, ALLOW_IP to allow IP addresses, 
  // ALLOW_LOCAL to allow local network names, and ALLOW_ALL to allow all three types. 
  // 
  $validator = new Zend_Validate_Hostname(Zend_Validate_Hostname::ALLOW_ALL);
  $smtp_host = config_get( 'smtp_host' );
  $op->status = tl::ERROR;

  // We need to validate at least that user mail is NOT EMPTY
  if( $validator->isValid($smtp_host) )
  {
    $APIKey = new APIKey();
    if ($APIKey->addKeyForUser($argsObj->user_id) >= tl::OK)
    {
      logAuditEvent(TLS("audit_user_apikey_set",$userObj->login),"CREATE",
                    $userObj->login,"users");
      $op->user_feedback = lang_get('apikey_by_mail');
      $op->status = tl::OK;

      // now send by mail
      $ak = $APIKey->getAPIKey($argsObj->user_id);
      $msgBody = lang_get('your_apikey_is') . "\n\n" . $ak . 
                 "\n\n" . lang_get('contact_admin');
      $mail_op = @email_send(config_get('from_email'), 
                             $userObj->emailAddress,lang_get('mail_apikey_subject'),$msgBody);
    }
  }
  else
  {
    $op->status = tl::ERROR;
    $op->user_feedback = lang_get('apikey_cannot_be_reseted_invalid_smtp_hostname');
  }
  return $op;
}



/*
  function: initializeUserProperties
            initialize members for a user object.

  args: userObj: data read from DB
        argsObj: data entry from User Interface

  returns: -

*/
function initializeUserProperties(&$userObj,&$argsObj)
{
  if (!is_null($argsObj->login))
  {
      $userObj->login = $argsObj->login;
  }
  $userObj->emailAddress = $argsObj->emailAddress;

  // The Black List - Jon Bokenkamp
  $reddington = array('/','\\',':','*','?','<','>','|');
  $userObj->firstName = str_replace($reddington,'',$argsObj->firstName);
  $userObj->lastName = str_replace($reddington,'',$argsObj->lastName);

  $userObj->globalRoleID = $argsObj->rights_id;
  $userObj->locale = $argsObj->locale;
  $userObj->isActive = $argsObj->user_is_active;
  $userObj->authentication = trim($argsObj->authentication);
}

function decodeRoleId(&$dbHandler,$roleID)
{
    $roleInfo = tlRole::getByID($dbHandler,$roleID);
    return $roleInfo->name;
}

function renderGui(&$smartyObj,&$argsObj,$templateCfg)
{
  $doRender = false;
  switch($argsObj->doAction)
  {
    case "edit":
    case "create":
    case "resetPassword":
    case "genAPIKey":
      $doRender = true;
      $tpl = $templateCfg->default_template;
    break;

    case "doCreate":
    case "doUpdate":
      if(!is_null($templateCfg->template))
      {
        $doRender = true;
        $tpl = $templateCfg->template;
      }
      else
      {
        header("Location: usersView.php");
        exit();
      }
    break;

  }

  if($doRender)
  {
    $smartyObj->display($templateCfg->template_dir . $tpl);
  }    
}


/**
 *
 */
function initializeGui(&$dbHandler,&$argsObj)
{
  $userObj = &$argsObj->user;

  $guiObj = new stdClass(); 

  $guiObj->user = null;
  switch($argsObj->doAction)
  {
    case 'edit': 
      // Because we can arrive with login, we need to check if we can get
      // id from login
      if(strlen(trim($argsObj->login)) > 0)
      {
        $argsObj->user_id = tlUser::doesUserExist($dbHandler,$argsObj->login);
      }

      if( is_null($argsObj->user_id) || intval($argsObj->user_id) <= 0)
      {
        // need to manage some sort of error message
        $guiObj->op = new stdClass();
        $guiObj->op->status = tl::ERROR;
        $guiObj->op->user_feedback = 
          sprintf(lang_get('login_does_not_exist'),$argsObj->login);
      }  
      else
      {  
        $guiObj->user = new tlUser(intval($argsObj->user_id));
        $guiObj->user->readFromDB($dbHandler);
      }  
      $guiObj->main_title = lang_get("action_{$argsObj->doAction}_user");
    break;

    case "resetPassword":
    case "genAPIKey":
      $guiObj->user = new tlUser($argsObj->user_id);
      $guiObj->user->readFromDB($dbHandler);
    break;
  }
  
  $guiObj->op = new stdClass();
  $guiObj->op->user_feedback = '';
  $guiObj->op->status = tl::OK;

  $guiObj->authCfg = config_get('authentication');
  $guiObj->auth_method_opt = array(lang_get('default_auth_method') . 
                             "(" . $guiObj->authCfg['domain'][$guiObj->authCfg['method']]['description'] . ")" => '');

  $dummy = array_keys($guiObj->authCfg['domain']);
  foreach($dummy as $xc)
  {
    // description => html option value
    $guiObj->auth_method_opt[$xc] = $xc;  
  }  

  $guiObj->auth_method_opt = array_flip($guiObj->auth_method_opt);

  $guiObj->optLocale = config_get('locales');

  $guiObj->grants = getGrantsForUserMgmt($dbHandler,$userObj);

  $guiObj->grants->mgt_view_events = 
    $userObj->hasRight($dbHandler,"mgt_view_events");

  $guiObj->expiration_date = $argsObj->expiration_date;

  $noExpirationUsers = array_flip(config_get('noExpDateUsers'));
  $guiObj->expDateEnabled = true;
  if( !is_null($guiObj->user) )
  {
    $guiObj->expDateEnabled = !isset($noExpirationUsers[$guiObj->user->login]);
  } 

  return $guiObj;  
}

/**
 *
 */
function initLabels()
{
  $tg = array('action_create_user' => null,'action_edit_user' => null);
  $labels = init_labels($tg);
  return $labels;
}

function checkRights(&$db,&$user)
{
  return $user->hasRight($db,'mgt_users');
}