<?php
/**
* TestLink Open Source Project - http://testlink.sourceforge.net/
* This script is distributed under the GNU General Public License 2 or later.
*
* Filename $RCSfile: usersEdit.php,v $
*
* @version $Revision: 1.27 $
* @modified $Date: 2008/10/21 20:23:06 $ $Author: schlundus $
*
* rev:
*     fixed missing checks on doCreate()
*     BUGID 918
*     20070829 - jbarchibald - fix bug 1000 - Testplan role assignments
*
* Allows editing a user
*/
require_once('../../config.inc.php');
require_once('testproject.class.php');
require_once('users.inc.php');
require_once('email_api.php');
testlinkInitPage($db);

$templateCfg = new stdClass();
$templateCfg->template_dir = 'usermanagement/';
$templateCfg->default_template = str_replace('.php','.tpl',basename($_SERVER['SCRIPT_NAME']));
$templateCfg->template = null;

$args = init_args();
$user_id = $args->user_id;

$op = new stdClass();
$highlight = initialize_tabsmenu();
$op->user_feedback = '';

$actionOperation = array('create' => 'doCreate', 'edit' => 'doUpdate',
                       'doCreate' => 'doCreate', 'doUpdate' => 'doUpdate',
                       'resetPassword' => 'doUpdate');

// echo "<pre>debug 20080821 - \ - " . __FUNCTION__ . " --- "; print_r($args); echo "</pre>";

switch($args->doAction)
{
	case "edit":
		$highlight->edit_user = 1;
		$user = new tlUser($args->user_id);
		$user->readFromDB($db);
		break;
	
	case "doCreate":
		$highlight->create_user = 1;
		$op = doCreate($db,$args);
		$user = $op->user;
		$templateCfg->template = $op->template;
		break;
	
	case "doUpdate":
		$highlight->edit_user = 1;
		$sessionUserID = $_SESSION['currentUser']->dbID;
		$op = doUpdate($db,$args,$sessionUserID);
		$user = $op->user;
		break;

	case "resetPassword":
		$highlight->edit_user = 1;
		$user = new tlUser($args->user_id);
		$user->readFromDB($db);
		$op = createNewPassword($db,$args,$user);
		break;
	
	case "create":
		default:
		$highlight->create_user = 1;
		$user = new tlUser();
		break;
}

$op->operation = $actionOperation[$args->doAction];

$roles = tlRole::getAll($db,null,null,null,tlRole::TLOBJ_O_GET_DETAIL_MINIMUM);
unset($roles[TL_ROLES_UNDEFINED]);

$smarty = new TLSmarty();
$smarty->assign('highlight',$highlight);
$smarty->assign('operation',$op->operation);
$smarty->assign('user_feedback',$op->user_feedback);
$smarty->assign('external_password_mgmt', tlUser::isPasswordMgtExternal());
$smarty->assign('mgt_view_events',$_SESSION['currentUser']->hasRight($db,"mgt_view_events"));
$smarty->assign('grants',getGrantsForUserMgmt($db,$_SESSION['currentUser']));
$smarty->assign('optRights',$roles);
$smarty->assign('userData', $user);

renderGui($smarty,$args,$templateCfg);


/*
  function:

  args:

  returns:

*/
function init_args()
{
  	$args = new stdClass();
	$_REQUEST = strings_stripSlashes($_REQUEST);

	$intval_keys = array('delete' => 0, 'user' => 0,'user_id' => 0, 'rights_id' => TL_ROLES_GUEST);
	foreach ($intval_keys as $key => $value)
	{
		$args->$key = isset($_REQUEST[$key]) ? intval($_REQUEST[$key]) : $value;
	}

	$nullable_keys = array('doAction','firstName','lastName','emailAddress','locale','login','password');
	foreach ($nullable_keys as $value)
	{
		$args->$value = isset($_REQUEST[$value]) ? trim($_REQUEST[$value]) : null;
	}

 	$checkbox_keys = array('user_is_active');
	foreach ($checkbox_keys as $value)
	{
		$args->$value = isset($_REQUEST[$value]) ? 1 : 0;
	}

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

    $statusOk=false;
	if ($op->status >= tl::OK)
	{
	  	initializeUserProperties($op->user,$argsObj);
		$op->status = $op->user->writeToDB($dbHandler);
		if($op->status >= tl::OK)
		 {
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




/*
  function: doUpdate

  args:

  returns:

*/
function doUpdate(&$dbHandler,&$argsObj,$sessionUserID)
{
    $op=new stdClass();
    $op->user_feedback = '';
    $op->user = new tlUser($argsObj->user_id);
	$op->status = $op->user->readFromDB($dbHandler);
	if ($op->status >= tl::OK)
	{
		$changes=checkUserPropertiesChanges($dbHandler,$op->user,$argsObj);

		initializeUserProperties($op->user,$argsObj);
		$op->status = $op->user->writeToDB($dbHandler);
		if ($op->status >= tl::OK)
		{
			logAuditEvent(TLS("audit_user_saved",$op->user->login),"SAVE",$op->user->dbID,"users");
			/*
		  	foreach($changes as $key => $value)
			{
			    logAuditEvent($value['msg'],$value['activity'],$op->user->dbID,"users");
			}
			*/
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


/*
  function: createNewPassword

  args :

  returns: -

*/
function createNewPassword(&$dbHandler,&$argsObj,&$userObj)
{
	$op = new stdClass();
	$op->user_feedback = '';
	$op->status = resetPassword($dbHandler,$argsObj->user_id,$op->user_feedback);
	if ($op->status >= tl::OK)
	{
		logAuditEvent(TLS("audit_pwd_reset_requested",$userObj->login),"PWD_RESET",$argsObj->user_id,"users");
		$op->user_feedback = lang_get('password_reseted');
	}
	
	echo "<pre>debug 20080821 - \ - " . __FUNCTION__ . " --- "; print_r($op); echo "</pre>";
	return $op;
	
}





/*
  function: checkUserPropertiesChanges
            do checks on selected properties and return information
            about changed members useful for audit log porpuses.

  args: dbHandler
        userObj: data read from DB
        argsObj: data entry from User Interface

  returns: null or array where each element is a map with following structure:

           ['property']= property name, just for debug usage
           ['msg']= message for logAudit call
           ['activity']= activityCode for logAudit call

*/
function checkUserPropertiesChanges(&$dbHandler,&$userObj,&$argsObj)
{

  $idx=0;
  $key2compare=array();
  $key2compare['numeric'][]=array('old' => 'globalRoleID',
                                  'new' => 'rights_id',
                                  'decode' => 'decodeRoleId',
                                  'label' => 'audit_user_role_changed');

  $key2compare['numeric'][]=array('old' => 'bActive',
                                  'new' => 'user_is_active',
                                  'label' => 'audit_user_active_status_changed');


  foreach($key2compare['numeric'] as $key => $value)
  {
      $old=$value['old'];
      $new=$value['new'];
      $oldValue=$userObj->$old;
      $newValue=$argsObj->$new;

      if( $oldValue != $newValue )
      {
          if( isset($value['decode']) )
          {
              $oldValue=$value['decode']($dbHandler,$userObj->$old);
              $newValue=$value['decode']($dbHandler,$argsObj->$new);
          }
          $changes[$idx]['property']=$old;
          $changes[$idx]['msg']=TLS($value['label'],$userObj->login,$oldValue,$newValue);
          $changes[$idx]['activity']='CHANGE';
          $idx++;
      }
  }

	// Add general message only if no important change registered
	if($idx == 0)
	{
		$changes[$idx]['property']='general';
		$changes[$idx]['msg']=TLS('audit_user_saved',$userObj->login);
		$changes[$idx]['activity']='SAVE';
	}

	return $changes;
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
    	$userObj->login = $argsObj->login;

	$userObj->emailAddress = $argsObj->emailAddress;
	$userObj->firstName = $argsObj->firstName;
	$userObj->lastName = $argsObj->lastName;
	$userObj->globalRoleID = $argsObj->rights_id;
	$userObj->locale = $argsObj->locale;
	$userObj->bActive = $argsObj->user_is_active;
}


/*
  function:

  args:

  returns:

*/
function decodeRoleId(&$dbHandler,$roleID)
{
    $roleInfo = tlRole::getByID($dbHandler,$roleID);
    return $roleInfo->name;
}

/*
  function: renderGui

  args :

  returns:

*/
function renderGui(&$smartyObj,&$argsObj,$templateCfg)
{
    $doRender=false;
    switch($argsObj->doAction)
    {
        case "edit":
        case "create":
        case "resetPassword":
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
        $smartyObj->display($templateCfg->template_dir . $tpl);
}
?>