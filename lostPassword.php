<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @internal revisions
 * @since 1.9.15
**/
require_once('config.inc.php');
require_once('common.php');
require_once('users.inc.php');
require_once('email_api.php');
$templateCfg = templateConfiguration();

$args = init_args();
$gui = new stdClass();

$gui->external_password_mgmt = 0;
$gui->page_title = lang_get('page_title_lost_passwd');
$gui->note = lang_get('your_info_for_passwd');
$gui->password_mgmt_feedback = '';
$gui->login = $args->login;
$gui->viewer = $args->viewer;

$op = doDBConnect($db,database::ONERROREXIT);

$userID = false;
if ($args->login != "")
{
  $userID = tlUser::doesUserExist($db,$args->login);
  if (!$userID)
  {
    $gui->note = lang_get('bad_user');
  }
  else
  {
    // need to know if auth method for user allows reset
    $user = new tlUser(intval($userID));
    $user->readFromDB($db);
    if(tlUser::isPasswordMgtExternal($user->authentication,$user->authentication))
    {
      $gui->external_password_mgmt = 1;
      $gui->password_mgmt_feedback = sprintf(lang_get('password_mgmt_feedback'),trim($args->login));
    }  
  }
}

if(!$gui->external_password_mgmt && $userID)
{
  $result = resetPassword($db,$userID);
  $gui->note = $result['msg'];
  if ($result['status'] >= tl::OK)
  {
    $user = new tlUser($userID);
    if ($user->readFromDB($db) >= tl::OK)
    {
      logAuditEvent(TLS("audit_pwd_reset_requested",$user->login),"PWD_RESET",$userID,"users");
    }
    redirect(TL_BASE_HREF ."login.php?note=lost&viewer={$args->viewer}");
    exit();
  }
  else if ($result['status'] == tlUser::E_EMAILLENGTH)
  {
    $gui->note = lang_get('mail_empty_address');
  } 
  else if ($note != "")
  {
    $gui->note = getUserErrorMessage($result['status']);
  } 
}

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);

$tpl = str_replace('.php','.tpl',basename($_SERVER['SCRIPT_NAME']));
if( $args->viewer == 'new' )
{
  $tpl = 'lostPassword-model-marcobiedermann.tpl';
}  

$smarty->display($tpl);

/**
 *
 */
function init_args()
{
  $iParams = array("login" => array('POST',tlInputParameter::STRING_N,0,30),
                   "viewer" => array('GET',tlInputParameter::STRING_N, 0, 3));
  
  $args = new stdClass();
  I_PARAMS($iParams,$args);
  return $args;
}
?>