<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Shows all users
 *
 * @package     TestLink
 * @author      Francisco Mancardi
 * @copyright   2012,2017 TestLink community 
 * @filesource  usersViewNew.php
 * @link        http://www.testlink.org/
 *
 * 
 */
require_once("../../config.inc.php");            
require_once('exttable.class.php');
require_once("users.inc.php");
testlinkInitPage($db,false,false,"checkRights");

$smarty = new TLSmarty();


list($args,$gui) = initEnv($db);

switch($args->operation)
{
	case 'disable':
		// user cannot disable => inactivate itself
		if ($args->user_id != $args->currentUserID)
		{
			$user = new tlUser($args->user_id);
			$gui->result = $user->readFromDB($db);
			if ($gui->result >= tl::OK)
			{
				$gui->result = $user->setActive($db,0);
				if ($gui->result >= tl::OK)
				{
					logAuditEvent(TLS("audit_user_disabled",$user->login),"DISABLE",$args->user_id,"users");
					$gui->user_feedback = sprintf(lang_get('user_disabled'),$user->login);
				}
			}
		}
		if ($gui->result != tl::OK)
		{
			$gui->user_feedback = lang_get('error_user_not_disabled');
    }
	break;
		
	default:
	break;
}

$gui->matrix = getAllUsersForGrid($db);
$gui->tableSet[] = buildMatrix($gui, $args);
$gui->images = $smarty->getImages();

$tplCfg = templateConfiguration();
$smarty->assign('gui',$gui);
$smarty->display($tplCfg->tpl);


/**
 *
 */
function initEnv(&$dbHandler)
{
  $_REQUEST=strings_stripSlashes($_REQUEST);

  // input from GET['HelloString3'], 
  // type: string,  
  // minLen: 1, 
  // maxLen: 15,
  // regular expression: null
  // checkFunction: applys checks via checkFooOrBar() to ensure its either 'foo' or 'bar' 
  // normalization: done via  normFunction() which replaces ',' with '.' 
  // "HelloString3" => array("GET",tlInputParameter::STRING_N,1,15,'checkFooOrBar','normFunction'),
  //
  $iParams = array("operation" => array(tlInputParameter::STRING_N,0,50),
                   "user" => array(tlInputParameter::INT_N));
  
  $pParams = R_PARAMS($iParams);
  $args = new stdClass();
  $args->operation = $pParams["operation"];
  $args->user_id = $pParams['user'];
  
  $args->currentUser = $_SESSION['currentUser'];
  $args->currentUserID = $_SESSION['currentUser']->dbID;
  $args->basehref =  $_SESSION['basehref'];
  

  $gui = new stdClass();
  $gui->grants = getGrantsForUserMgmt($dbHandler,$args->currentUser);
  $gui->main_title = lang_get('title_user_mgmt');
  $gui->result = null;
  $gui->action = null;
  $gui->user_feedback = '';
  $gui->update_title_bar = 0;
  $gui->reload = 0;

  $gui->basehref = $args->basehref; 

  $gui->highlight = initialize_tabsmenu();
  $gui->highlight->view_users = 1;

  return array($args,$gui);
}

/*
  function: getRoleColourCfg
            using configuration parameter ($g_role_colour)
            creates a map with following structure:
            key: role name
            value: colour

            If name is not defined on $g_role_colour (this normally
            happens for user defined roles), will be added with '' as colour (means default colour).

  args: db: reference to db object

  returns: map

*/
function getRoleColourCfg(&$db)
{
  $role_colour = config_get('role_colour');
  $roles = tlRole::getAll($db,null,null,null,tlRole::TLOBJ_O_GET_DETAIL_MINIMUM);
  unset($roles[TL_ROLES_UNDEFINED]);
  foreach($roles as $roleObj)
  {
    if(!isset($role_colour[$roleObj->name]))
    {
      $role_colour[$roleObj->name] = '';
    }
  }
  return $role_colour;
}


/**
 * Builds ext-js rich table to display matrix results
 *
 *
 * return tlExtTable
 *
 */
function buildMatrix(&$guiObj,&$argsObj)
{
  // th_first_name,th_last_name,th_email
  // IMPORTANT DEVELOPER NOTICE
  // Column order is same that present on query on getAllUsersForGrid()
  //
  // Where col_id is not specified, col_id will be generated this way: 'id_' . $v['title_key'].
  // Example: id_th_first_name.
  // 
  // 'tlType' => TestLinkType: will be analized and mapped accordingly on tlExtTable::buildColumns()
  //
  $columns = array(array('title_key' => 'th_login', 'col_id' => 'handle', 'width' => 100),
                   array('title_key' => 'th_first_name', 'width' => 150),
                   array('title_key' => 'th_last_name', 'width' => 150),
                   array('title_key' => 'th_email', 'width' => 150),
                   array('title_key' => 'th_role', 'width' => 150),
                   array('title_key' => 'th_locale', 'width' => 150),
                   array('title_key' => 'th_active', 'type' => 'oneZeroImage', 'width' => 50),
                   array('title_key' => 'expiration', 'width' => 50),
                   array('title' => 'disableUser', 'tlType' => 'disableUser', 'width' => 150),
                   array('hidden' => true, 'title' => 'hidden_role_id', 'col_id' => 'role_id'),
                   array('hidden' => true, 'title' => 'hidden_user_id', 'col_id' => 'user_id'),
                   array('hidden' => true, 'title' => 'hidden_login', 'col_id' => 'login'),
                   array('hidden' => true, 'title' => 'hidden_is_special', 'col_id' => 'is_special'));

  $lbl = init_labels(array('th_login' => null,'th_first_name' => null,
                           'th_last_name' => null,'expiration' => null,
                           'th_email' => null));

  $loop2do = count($guiObj->matrix);
 
  // login added as workaround for SORTING, because the whole string is used then user_id
  // in url takes precedence over the login displayed 
  $actionUrl = '<a href="' . $argsObj->basehref .  
               'lib/usermanagement/usersEdit.php?doAction=edit&' .
               'loginJustToFixSort=';

  for($zdx = 0; $zdx < $loop2do; $zdx++)
  {
    $guiObj->matrix[$zdx]['handle'] = $actionUrl . 
      urlencode($guiObj->matrix[$zdx]['login']) . '&user_id=' .
      $guiObj->matrix[$zdx]['user_id'] . '">' . $guiObj->matrix[$zdx]['login'] . 
      "</a>";
  }
  

  $matrix = new tlExtTable($columns, $guiObj->matrix, 'tl_users_list');
  
  // => addCustomBehaviour(columnType, );
  $matrix->addCustomBehaviour('oneZeroImage', array('render' => 'oneZeroImageRenderer'));
  $matrix->moreViewConfig = " ,getRowClass: function(record, index) {" .
                            " var x = record.get('role_id');" .
                            " return('roleCode'+x); " .
                            " } " ;
  
  $matrix->setImages($guiObj->images);
  $matrix->allowMultiSort = false;
  $matrix->sortDirection = 'DESC';
  $matrix->showToolbar = true;
  $matrix->toolbarShowAllColumnsButton = true;
  unset($columns);
  
  return $matrix;
}



/**
 * check function for tlInputParameter user_order_by
 *
 */
function checkUserOrderBy($input)
{
	$domain = array_flip(array('order_by_role','order_by_login'));
	
	$status_ok = isset($domain[$input]) ? true : false;
	return $status_ok;
}

/**
 *
 */
function getAllUsersForGrid(&$dbHandler)
{
  $tables = tlObject::getDBTables(array('users','roles'));
  
  // Column extraction order is CRITIC for correct behaviour of Ext-JS
  $sql = " SELECT '' AS handle,U.first,U.last,U.email,R.description," .
         " U.locale,U.active,U.expiration_date," .
         " /* this columns will not visible on GUI */ " .
         " '' AS place_holder,R.id AS role_id,U.id AS user_id,U.login, 0 AS is_special " . 
         " FROM {$tables['users']} U " .
         " JOIN {$tables['roles']} R ON U.role_id = R.id  ORDER BY U.login ";

  $users = $dbHandler->get_recordset($sql);

  // because we need to render this on EXT-JS, we have issues with <no rights> role
  // due to <, then we are going to escape values in description column
  $loop2do = count($users);
  $dummy = '';
  for($idx=0; $idx < $loop2do; $idx++)
  {
    $users[$idx]['description'] = htmlentities($users[$idx]['description']);    

    // localize dates
    $ed = trim($users[$idx]['expiration_date']);
    if($ed != '')
    {
      $users[$idx]['expiration_date'] = 
        localize_dateOrTimeStamp(null,$dummy,'date_format',$ed);
    }  
  }  


  // Still need to understand why, but with MSSQL we use on ADODB 
  // fetch mode = ADODB_FETCH_BOTH, this generates numeric AND literal keys
  // on row maps => for each column on result set we get to elements on row map.
  // example 0,handle,1,first, and so on.
  // This drives crazy EXT-JS grid 
  if(!is_null($users) && $dbHandler->dbType == 'mssql')
  {
    $clean = array();
    foreach($users as $row)
    {
      $cr = array();
      $elem = array_keys($row);
      foreach($elem as $accessKey)
      {
        if(!is_numeric($accessKey))
        {
          $cr[$accessKey] = $row[$accessKey];
        }
      }
      $clean[] = $cr;
    }
    $users = $clean;
  }
 
	if( config_get('demoMode') )
	{
  	$loop2do = count($users);
	  $specialK = array_flip((array)config_get('demoSpecialUsers'));
  	for($idx=0; $idx < $loop2do; $idx++)
	  {
		  $users[$idx]['is_special'] = isset($specialK[$users[$idx]['login']]) ? 1 : 0;
	  }
  }

  return $users;
}



function checkRights(&$db,&$user)
{
	return $user->hasRight($db,'mgt_users');
}