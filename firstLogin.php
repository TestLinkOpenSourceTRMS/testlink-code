<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource  firstLogin.php
 * @package     TestLink
 * @copyright   2004-2016, TestLink community 
 * @link        http://www.testlink.org
 *
 *
 */
require_once('config.inc.php');
require_once('common.php');
require_once('users.inc.php');
require_once('email_api.php');
require_once('Zend/Validate/EmailAddress.php');

$templateCfg = templateConfiguration();

$args = init_args();
$gui = $args;

if (!config_get('user_self_signup'))
{
  $smarty = new TLSmarty();
  $smarty->assign('title', lang_get('fatal_page_title'));
  $smarty->assign('content', lang_get('error_self_signup_disabled'));
  $smarty->assign('link_to_op', "login.php");
  $smarty->assign('hint_text', lang_get('link_back_to_login'));
  $smarty->display('workAreaSimple.tpl');
  exit();
}
doDBConnect($db,database::ONERROREXIT);

$message = ''; //lang_get('your_info_please');
if( !is_null($args->doEditUser) )
{
  if(strcmp($args->password,$args->password2))
  {
    $message = lang_get('passwd_dont_match');
  }
  else
  {
    $user = new tlUser(); 
    $rx = $user->checkPasswordQuality($args->password);
    if( $rx['status_ok'] >= tl::OK )
    {
      $result = $user->setPassword($args->password);
      if ($result >= tl::OK)
      {
        $user->login = $args->login;
        $user->emailAddress = $args->email;
        $user->firstName = $args->firstName;
        $user->lastName = $args->lastName;
        $result = $user->writeToDB($db);

        $cfg = config_get('notifications');
        if($cfg->userSignUp->enabled)
        {  
          notifyGlobalAdmins($db,$user);
        }
        logAuditEvent(TLS("audit_users_self_signup",$args->login),"CREATE",$user->dbID,"users");
        
        $url2go = "login.php?viewer={$gui->viewer}&note=first";
        redirect(TL_BASE_HREF . $url2go);
        exit();
      }
      else 
      {
        $message = getUserErrorMessage($result);
      } 
    }  
    else
    {
      $message = $rx['msg'];
    }  
  }
}

$smarty = new TLSmarty();

// we get info about THE DEFAULT AUTHENTICATION METHOD
$gui->external_password_mgmt = tlUser::isPasswordMgtExternal(); 
$gui->message = $message;
$smarty->assign('gui',$gui);

$tpl = str_replace('.php','.tpl',basename($_SERVER['SCRIPT_NAME']));
if( $args->viewer == 'new' )
{
  $tpl='firstLogin-model-marcobiedermann.tpl';
}  

$smarty->display($tpl);


/**
 * get input from user and return it in some sort of namespace
 *
 */
function init_args()
{
  $args = new stdClass();
  $args->pwdInputSize = config_get('loginPagePasswordSize');
 
  $iParams = array("doEditUser" => array('POST',tlInputParameter::STRING_N,0,1),
                   "login" => array('POST',tlInputParameter::STRING_N,0,30),
                   "password" => array('POST',tlInputParameter::STRING_N,0,$args->pwdInputSize),
                   "password2" => array('POST',tlInputParameter::STRING_N,0,$args->pwdInputSize),
                   "firstName" => array('POST',tlInputParameter::STRING_N,0,30),
                   "lastName" => array('POST',tlInputParameter::STRING_N,0,30),
                   "email" => array('POST',tlInputParameter::STRING_N,0,100),
                   "viewer" => array('GET',tlInputParameter::STRING_N, 0, 3),
                   );
  I_PARAMS($iParams,$args);

  return $args;
}

/**
 * send mail to administrators (users that have default role = administrator) 
 * to warn about new user created.
 *
 */
function notifyGlobalAdmins(&$dbHandler,&$userObj)
{
  // Get email addresses for all users that have default role = administrator
 
  $cfg = config_get('notifications');
  if( !is_null($cfg->userSignUp->to->roles) )
  {
    $opt = array('active' => 1);
    foreach($cfg->userSignUp->to->roles as $roleID)
    {
      $roleMgr = new tlRole($roleID);
      $userSet = $roleMgr->getUsersWithGlobalRole($dbHandler,$opt);
      $key2loop = array_keys($userSet);
      foreach($key2loop as $userID)
      {
        if(!isset($mail['to'][$userID]))
        {
          $mail['to'][$userID] = $userSet[$userID]->emailAddress; 
        }  
      }
    }  
  }  
  if( !is_null($cfg->userSignUp->to->users) )
  {
    // Brute force query
    $tables = tlObject::getDBTables('users');
    $sql = " SELECT id,email FROM {$tables['users']} " .
           " WHERE login IN('" . implode("','", $cfg->userSignUp->to->users) . "')";
    $userSet = $dbHandler->fetchRowsIntoMap($sql,'id');
    if(!is_null($userSet))
    {
      foreach($userSet as $userID => $elem)
      {
        if(!isset($mail['to'][$userID]))
        {
          $mail['to'][$userID] = $elem['email'];
        }  
      }
    }  
  }

  if($mail['to'] != '')
  {
    $dest = null;  
    $validator = new Zend_Validate_EmailAddress();
    foreach($mail['to'] as $mm)
    {
      $ema = trim($mm);
      if($ema == '' || !$validator->isValid($ema))
      {
        continue;
      }  
      $dest[] = $ema;
    }  
    $mail['to'] = implode(',',$ema); // email_api uses ',' as list separator
    $mail['subject'] = lang_get('new_account');
    $mail['body'] = lang_get('new_account') . "\n";
    $mail['body'] .= " user:$userObj->login\n"; 
    $mail['body'] .= " first name:$userObj->firstName surname:$userObj->lastName\n";
    $mail['body'] .= " email:{$userObj->emailAddress}\n";
      
    // silence errors
    if(!is_null($dest))
    {
      @email_send(config_get('from_email'), $mail['to'], $mail['subject'], $mail['body']);
    }  
  }  
}