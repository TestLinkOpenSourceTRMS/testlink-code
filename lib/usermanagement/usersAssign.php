<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Allows assigning users roles to testplans or testprojects
 *
 * Development note:
 * - Assign Test plans user roles do not allow Test Project selection,
 *   then to change Test Project user need to use main 
 *   Test Project Combo, or to select a 
 * 
 * @package 	  TestLink
 * @copyright   2005-2019, TestLink community
 * @filesource  usersAssign.php
 * @link 		    http://www.testlink.org
 *
 *
 */

require_once('../../config.inc.php');
require_once('users.inc.php');
testlinkInitPage($db,false,false,"checkRights");

$smarty = new TLSmarty();
$imgSet = $smarty->getImages();

$templateCfg = templateConfiguration();

$assignRolesFor = null;
$featureMgr = null;
$userFeatureRoles = null;
$doInitGui = true;

$tprojectMgr = new testproject($db);
$tplanMgr = new testplan($db);

$args = init_args($tprojectMgr);
$gui = initializeGui($db,$args);

$lbl = initLabels();

$target = new stdClass();
$target->testprojectID = null;
$target->testplanID = null;


switch($args->featureType) {
  case "testproject":
  	$gui->highlight->assign_users_tproject = 1;
  	$gui->roles_updated = $lbl["test_project_user_roles_updated"];
  	$gui->not_for_you = $lbl["testproject_roles_assign_disabled"];
  	$gui->main_title = $lbl["assign_tproject_roles"];

    $assignRolesFor = $args->featureType;
  	$target->testprojectID = $args->featureID > 0 ? $args->featureID : null;
  	$featureMgr = &$tprojectMgr;
  break;
  
  case "testplan":
    $gui->highlight->assign_users_tplan = 1;
  	$gui->roles_updated = lang_get("test_plan_user_roles_updated");
  	$gui->not_for_you = lang_get("testplan_roles_assign_disabled");
    $gui->main_title = $lbl["assign_tplan_roles"];

  	$assignRolesFor = $args->featureType;
  	$target->testprojectID = $args->testprojectID;
  	$featureMgr = &$tplanMgr;

    $accessKey = 'private';
    if( $tprojectMgr->getPublicAttr($args->testprojectID) )
    {
      $accessKey = 'public';
    }  
    $gui->tprojectAccessTypeImg = '<img src="' . $imgSet[$accessKey] . 
                                  '" title="' . lang_get('access_' . $accessKey) . '" >';
  break;
}

if ($args->featureID && $args->doUpdate && $featureMgr) {
    if(checkRightsForUpdate($db,$args->user,$args->testprojectID,$args->featureType,$args->featureID)) {
      doUpdate($db,$args,$featureMgr);
      if( $gui->user_feedback == '' ) {
        $gui->user_feedback = $gui->roles_updated;
    	}
    }
}
// ------------------------------------------------------------------
// Important: 
// Must be done here after having done update, to get current information.
// MantisBT-style UX: only the filtered/paged subset of users is loaded
// and rendered (see getFilteredUsersPage()).
$usersPage = getFilteredUsersPage($db,$args);
$gui->users = $usersPage['users'];
$gui->pager = $usersPage['pager'];
checkSessionValid($db);
$args->user = $_SESSION['currentUser'];
// ------------------------------------------------------------------

switch($assignRolesFor) {
  case 'testproject':
    $info = getTestProjectEffectiveRoles($db,$tprojectMgr,$args,$gui->users);
    list($gui->userFeatureRoles,$gui->features,$gui->featureID) = $info;
    $target->testprojectID = $gui->featureID;
  break;
        
  case 'testplan':
    $info = getTestPlanEffectiveRoles($db,$tplanMgr,$tprojectMgr,$args,$gui->users);
		if( is_null($info) ) {
			$gui->user_feedback = lang_get('no_test_plans_available');
		}
    list($gui->userFeatureRoles,$gui->features,$gui->featureID)=$info;
  break;

}


$gui->grants = getGrantsForUserMgmt($db,$args->user,$target->testprojectID,-1);
$gui->accessTypeImg = '';


if(is_null($gui->features) || count($gui->features) == 0) {
  $gui->features = null;
  if( $gui->user_feedback == '' ) {
		$gui->user_feedback = $gui->not_for_you;
	}
} else {
  $accessKey = 'vorsicht';
  if( isset($gui->features[$gui->featureID]) ) {
    $accessKey = $gui->features[$gui->featureID]['is_public'] ? 'public' : 'private';
    $gui->accessTypeImg = '<img src="' . $imgSet[$accessKey] . '" title="' . lang_get('access_' . $accessKey) . '" >';
  }  
  $gui->accessTypeImg = '<img src="' . $imgSet[$accessKey] . '" title="' . lang_get('access_' . $accessKey) . '" >';
}

$gui->hintImg = '<img src="' . $imgSet['heads_up'] . '" title="' . 
                lang_get('system_design_blocks_global_admin_change') . '" >';

$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/**
 * getFilteredUsersPage
 *
 * MantisBT-style (manage_user_page.php) server-side user listing:
 * limited number of records per page + filter by initial letter
 * and/or user name (login LIKE search).
 *
 * @param object &$db adodb handler
 * @param object $args args object (page_number, filter, search, per_page)
 * @return array with keys:
 *   - users: array of tlUser objects (page subset)
 *   - pager: stdClass model for the template
 */
function getFilteredUsersPage(&$db,$args) {
  $tables = tlObject::getDBTables('users');
  $where = ' WHERE active = 1 ';

  // initial-letter filter: login starts with <letter>
  if ($args->filter != 'ALL') {
    $where .= " AND login LIKE '" . $db->prepare_string($args->filter) . "%' ";
  }

  // username search: login contains <term>
  // (% and _ in user input are escaped so they act as literals)
  $searchTerm = $args->search;
  if ($searchTerm != '') {
    $searchTerm = str_replace(array('!','%','_'),
                              array('!!','!%','!_'),
                              $db->prepare_string($searchTerm));
    $where .= " AND login LIKE '%" . $searchTerm . "%' ESCAPE '!' ";
  }

  $countSql = " SELECT COUNT(id) AS total FROM {$tables['users']} " . $where;
  $rs = $db->get_recordset($countSql);
  $total = (is_array($rs) && isset($rs[0])) ? intval($rs[0]['total']) : 0;

  $perPage = intval($args->per_page);
  $pageNumber = intval($args->page_number);
  $pageCount = 1;
  $offset = 0;

  if ($perPage > 0) {
    $pageCount = intval(ceil($total / $perPage));
    if ($pageCount < 1) {
      $pageCount = 1;
    }
    // keep page number inside [1..page_count], as in MantisBT
    if ($pageNumber > $pageCount) {
      $pageNumber = $pageCount;
    }
    if ($pageNumber < 1) {
      $pageNumber = 1;
    }
    $offset = ($pageNumber - 1) * $perPage;
  }

  $sql = " SELECT id FROM {$tables['users']} " . $where . " ORDER BY login ";
  if ($perPage > 0) {
    $sql .= " LIMIT " . $perPage . " OFFSET " . $offset . " ";
  }

  $users = tlDBObject::createObjectsFromDBbySQL($db,$sql,'id','tlUser',true,
                                                tlUser::TLOBJ_O_GET_DETAIL_MINIMUM);
  $users = is_null($users) ? array() : $users;

  $pager = new stdClass();
  $pager->enabled = ($perPage > 0);
  $pager->per_page = $perPage;
  $pager->page_number = $pager->enabled ? $pageNumber : 1;
  $pager->page_count = $pager->enabled ? $pageCount : 1;
  $pager->total_users = $total;
  $pager->filter = $args->filter;
  $pager->search = $args->search;
  $pager->first_row = $total > 0 ? ($offset + 1) : 0;
  $pager->last_row = min($total, $offset + count($users));

  // per-initial-letter counts for the MantisBT-style filter chips
  // (letter counts respect the active search term, as in the main listing)
  $letterCounts = array();
  if ($perPage > 0) {
    $chipsSql = " SELECT LEFT(login,1) AS ch, COUNT(id) AS n FROM {$tables['users']} " . $where .
                  " GROUP BY LEFT(login,1) ";
    $rs = $db->get_recordset($chipsSql);
    if (is_array($rs)) {
      foreach ($rs as $row) {
        $letterCounts[strtoupper($row['ch'])] = intval($row['n']);
      }
    }
  }
  $pager->letter_counts = $letterCounts;
  $pager->all_count = $total;

  // windowed page links: 1 ... n-2 n-1 n n+1 n+2 ... last
  $pageLinks = array();
  if ($pager->enabled && $pageCount > 1) {
    $start = max(1, $pageNumber - 2);
    $end = min($pageCount, $pageNumber + 2);
    if ($start > 1) {
      $pageLinks[] = array('page' => 1, 'label' => '1', 'active' => ($pageNumber == 1));
      if ($start > 2) {
        $pageLinks[] = array('page' => null, 'label' => '&hellip;', 'active' => false, 'ellipsis' => true);
      }
    }
    for ($pn = $start; $pn <= $end; $pn++) {
      if ($pn == 1 && $start > 1) {
        continue; // page 1 already added above
      }
      $pageLinks[] = array('page' => $pn, 'label' => (string)$pn, 'active' => ($pn == $pageNumber));
    }
    if ($end < $pageCount) {
      if ($end < $pageCount - 1) {
        $pageLinks[] = array('page' => null, 'label' => '&hellip;', 'active' => false, 'ellipsis' => true);
      }
      $pageLinks[] = array('page' => $pageCount, 'label' => (string)$pageCount, 'active' => ($pageNumber == $pageCount));
    }
  }
  $pager->page_links = $pageLinks;
  $pager->has_prev = $pager->enabled && $pageNumber > 1;
  $pager->has_next = $pager->enabled && $pageNumber < $pageCount;

  return array('users' => $users, 'pager' => $pager);
}


/**
 * 
 *
 */
function init_args(&$tprojMgr) {
	$iParams = array(
			"featureType" => array(tlInputParameter::STRING_N,0,100),
			"featureID" => array(tlInputParameter::INT_N),
			"userRole" => array(tlInputParameter::ARRAY_INT),
			"do_update" => array(tlInputParameter::STRING_N,0,100),
			"page_number" => array(tlInputParameter::INT_N),
			"filter" => array(tlInputParameter::STRING_N,0,100),
			"search" => array(tlInputParameter::STRING_N,0,100),
			"per_page" => array(tlInputParameter::INT_N)
	);

	$pParams = R_PARAMS($iParams);
    
	$args = new stdClass();
	$args->featureType = $pParams["featureType"];
  $args->featureID = $pParams["featureID"];
  $args->map_userid_roleid = $pParams["userRole"];
  $args->doUpdate = ($pParams["do_update"] != "") ? 1 : 0;

  // MantisBT-style paging/filtering parameters.
  // per_page = 0 means "no limit" (legacy behaviour, all active users).
  $args->page_number = $pParams["page_number"] > 0 ? $pParams["page_number"] : 1;
  $args->per_page = $pParams["per_page"] > 0 ? min($pParams["per_page"], 1000) : 0;
  $filterUpper = strtoupper(trim($pParams["filter"]));
  $validFilters = array_merge(array('ALL'),
                  array_map('strtoupper', array_merge(range('A','Z'), range('0','9'))));
  $args->filter = in_array($filterUpper, $validFilters) ? $filterUpper : 'ALL';
  $args->search = trim($pParams["search"]);

  // Default per-page, reusing the existing usersAssign pagination config block
  $guiCfg = config_get('gui');
  $defaultPerPage = 20;
  $paginationEnabled = true;
  if (isset($guiCfg->usersAssign->pagination->enabled)) {
    $paginationEnabled = (bool)$guiCfg->usersAssign->pagination->enabled;
  }
  if (isset($guiCfg->usersAssign->pagination->length)) {
    if (preg_match('/\[\s*(\d+)/', $guiCfg->usersAssign->pagination->length, $m)) {
      $defaultPerPage = $m[1] + 0;
    }
  }
  if (!$paginationEnabled) {
    $defaultPerPage = 0;
  }
  if ($args->per_page == 0) {
    $args->per_page = $defaultPerPage;
  }

  $args->testprojectID = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0;

  $args->testplanID = 0;

  switch( $args->featureType ) {
    case 'testproject':
    break;

    case 'testplan':
      if( $args->testprojectID <= 0 ) {
        throw new Exception("INVALID Test Project ID", 1);
      }
      break;
  }
  
  if( ($fid = intval($args->featureID)) > 0 ) {
    $prop = $args->featureType .'ID';
    $args->$prop = $fid;
  }


  $prjInfo = $tprojMgr->get_by_id($args->testprojectID,array('output' => 'name')); 

  $args->testprojectName = $prjInfo['name'];
 
  $args->user = $_SESSION['currentUser'];
  $args->userID = $args->user->dbID;
    
  return $args;
}


/**
 * checks runned when tyring to run this page, 
 * to be sure user has rights to use it.
 *
 */
function checkRights(&$db,&$user) {
	$result = false;

  $tprojMgr = new testproject($db);
  $args = init_args($tprojMgr);
  $answers = new stdClass();
  $answers->role_management = $user->hasRight($db,"role_management");
  
  // Two checks needed:
  // First on current test project without using test plan rights
  // if this fails then check again adding current test plan
  $answers->testplan_user_role_assignment = $user->hasRight($db,"testplan_user_role_assignment",$args->testprojectID,-1);
  if($answers->testplan_user_role_assignment != "yes") {
      $targetTestPlanID = null;
      if($args->featureType == 'testplan') {
        $targetTestPlanID = $args->featureID;
      }
      $answers->testplan_user_role_assignment = $user->hasRight($db,"testplan_user_role_assignment",null,$targetTestPlanID);
  }
  
  $answers->user_role_assignment = $user->hasRight($db,"user_role_assignment",null,-1);
  
  foreach($answers as $key => $value) {
    $answers->$key = $value == "yes" ? true : false;
    $result = $result | $answers->$key; 
  }
	
	if(!$result && ($args->featureType == 'testproject')) {
	  $feature2check = $args->featureID;
	  if($args->featureID == 0 || is_null($args->featureID)) {
	    $feature2check = $args->testprojectID; 
	  } 
    if($user->hasRight($db,"testproject_user_role_assignment",$feature2check,-1) == "yes") 	    
    {
      $result = true;
    }
	}

	return $result;
}

/**
 * checkRightsForUpdate
 *
 */
function checkRightsForUpdate(&$dbHandler,&$user,$testprojectID,$featureType,$featureID)
{
    $yes_no = "no";
    switch($featureType)
    {
        case 'testproject':
            if($user->hasRight($dbHandler,"user_role_assignment",$featureID) == "yes" ||
                $user->hasRight($dbHandler,"testproject_user_role_assignment",$featureID,-1,true) == "yes")
            {         
                $yes_no = "yes";
            }
        break;
            
        case 'testplan':
            $yes_no = $user->hasRight($dbHandler,"testplan_user_role_assignment",
                                       $testprojectID,$featureID);
        break;
    }

    return ($yes_no == 'yes');
}


/**
 * getTestProjectEffectiveRoles
 *
 */
function getTestProjectEffectiveRoles($dbHandler,&$objMgr,&$argsObj,$users) {
  $features = null;
	$gui_cfg = config_get('gui');

  // Accessible means user has a role on test project ?
  $opt = array('output' => 'map_of_map_full', 'order_by' => $gui_cfg->tprojects_combo_order_by);
	$testprojects = $objMgr->get_accessible_for_user($argsObj->userID,$opt);
 	

   // We need to populate the combo box with test project where current logged user ($argsObj->userID)
  // has right enough to assign user role.
  //
  $features = array();
  $idSet = $key2loop = array_keys($testprojects);
  $rolesCache = null;
  foreach($idSet as $tk) {
    // $rolesCache[$testprojects[$tk]['effective_role']][] = $tk;  
    if(!isset($rolesCache[$testprojects[$tk]['effective_role']])) {
      $rolesCache[$testprojects[$tk]['effective_role']] = new tlRole($testprojects[$tk]['effective_role']);
      $rolesCache[$testprojects[$tk]['effective_role']]->readFromDB($dbHandler);
    }  
  }  

  foreach($key2loop as $idx) {
    $answer = $rolesCache[$testprojects[$idx]['effective_role']]->hasRight("user_role_assignment");
    if($answer == false) {
      $answer = $rolesCache[$testprojects[$idx]['effective_role']]->hasRight("testproject_user_role_assignment");  
    }  

    if($answer == true) {
      $features[$idx] = $testprojects[$idx];
    } 
  }

	// If have no a test project ID, try to figure out which test project to show
	// Try with session info, if failed go to first test project available.
	if (!$argsObj->featureID) {
		if ($argsObj->testprojectID) {
			$argsObj->featureID = $argsObj->testprojectID;
		} else if (sizeof($features)) {
		  $xx = current($features);
			$argsObj->featureID = $xx['id'];
		}	
	}
	
	// get private/public status for feature2check
	$featureIsPublic = 1;
	$key2loop = array_keys($testprojects);
  foreach($key2loop as $ppx) {
		if( $testprojects[$ppx]['id'] == $argsObj->featureID ) {
			$featureIsPublic = $testprojects[$ppx]['is_public'];
			break;
		}
	}
	
	foreach($users as &$user) {
		$user->readTestProjectRoles($dbHandler,$argsObj->featureID);
	}
	$effectiveRoles = get_tproject_effective_role($dbHandler,
    array('id' => $argsObj->featureID, 
	       'is_public' => $featureIsPublic),null,$users);

	return array($effectiveRoles,$features,$argsObj->featureID);
}



/**
 * getTestPlanEffectiveRoles
 *
 */
function getTestPlanEffectiveRoles(&$dbHandler,&$tplanMgr,$tprojectMgr,&$argsObj,&$users) {
  $features = array();
  $activeTestplans = $tprojectMgr->get_all_testplans($argsObj->testprojectID, array('plan_status' => 1));

  
  $ret = null;
  $status_ok = !is_null($activeTestplans);
  if($status_ok) {
    $myAccessibleSet = $argsObj->user->getAccessibleTestPlans($dbHandler,
      $argsObj->testprojectID,null,array('output' =>'map'));
                                                              
    // we want to change map key, from testplan id to a sequential index
    // to maintain old logic
    $activeKeys = array_keys($activeTestplans);
    $myKeys = array_keys((array)$myAccessibleSet);
    $key2remove = $key2remove_diff = array_diff($activeKeys,$myKeys);
     if( !is_null($key2remove) ) {
      foreach($key2remove as $target) {
        unset($activeTestplans[$target]);
      }
    }
    
  
  
    if($argsObj->user->hasRight($dbHandler,"mgt_users")) {
      $features = $activeTestplans;
    } else {
      $features = array();
      $key2loop = array_keys($activeTestplans);
      foreach($key2loop as $idx) {
        if($argsObj->user->hasRight($dbHandler,"testplan_user_role_assignment",null,$activeTestplans[$idx]['id']) == "yes") {
          $features[$idx] = $activeTestplans[$idx];
        } 
      }
    }
      
    // if nothing special was selected, 
    // use the one in the session or the first
    if (!$argsObj->featureID) {
      if (sizeof($features)) {
        if ($argsObj->testplanID) {
          $key2loop = array_keys($features);
          foreach($key2loop as $idx) {
            if ($argsObj->testplanID == $features[$idx]['id']) {
              $argsObj->featureID = $argsObj->testplanID;
            } 
          }
        }
        if (!$argsObj->featureID) {
          $xx = current($features);
          $argsObj->featureID = $xx['id'];
        } 
      }
    }

    foreach($users as &$user) {
      $user->readTestProjectRoles($dbHandler,$argsObj->testprojectID);
      $user->readTestPlanRoles($dbHandler,$argsObj->featureID);
    }
      
    $tproject_info = $tprojectMgr->get_by_id($argsObj->testprojectID);
    $effectiveRoles = get_tplan_effective_role($dbHandler,$argsObj->featureID,$tproject_info,null,$users);
    $ret = array($effectiveRoles,$features,$argsObj->featureID);
  }
  return $ret;
}



/**
 * getTestPlanEffectiveRoles
 *
 */
function getTestPlanEffectiveRolesNEW(&$dbHandler,&$tplanMgr,$tprojectMgr,&$argsObj,&$users)
{
	$features = array();
	$activeTestplans = $tprojectMgr->get_all_testplans($argsObj->testprojectID, array('plan_status' => 1));

 	
	$ret = null;
	$status_ok = !is_null($activeTestplans);
	if($status_ok)
	{
  	$myAccessibleSet = $argsObj->user->getAccessibleTestPlans($dbHandler,$argsObj->testprojectID,null,
	                                                            array('output' =>'map'));
	                                                            

    //echo __LINE__;
    //echo __FUNCTION__;
    //new dBug($myAccessibleSet);
   
    // we want to change map key, from testplan id to a sequential index to maintain old logic
    $activeKeys = array_keys($activeTestplans);
    $myKeys = array_keys((array)$myAccessibleSet);
	  $key2remove = $key2remove_diff = array_diff($activeKeys,$myKeys);
    if( !is_null($key2remove) )
    {
      foreach($key2remove as $target)
      {
        unset($activeTestplans[$target]);
      }
    }
    
	  // $activeTestplans = array_values($activeTestplans);
    //new dBug($activeTestplans);  
    	  
    // 2013-04-01
    // now is not clear why this logic is right  
    //
    // analisys has to go from detail (test plan) to general
    // Step 1 - check if user has specific role on test plan
    // Step 2 - If Step 1 fails
    //          check if user has specific role on test project
    //          that contains the test project
    // Step 3 - If Step 2 fails
    //          check Global Role.
    // 

		if($argsObj->user->hasRight($dbHandler,"mgt_users"))
		{
			$features = $activeTestplans;
		}
		else
		{
		  //$loop2do = sizeof($activeTestplans);
			//for($idx = 0; $idx < $loop2do; $idx++)
			$features = array();
		  $key2loop = array_keys($activeTestplans);
			foreach($key2loop as $idx)
			{
        // Humm!!, think we need to check testplan_user_role_assignment and not "testplan_planning"
				if($argsObj->user->hasRight($dbHandler,"testplan_user_role_assignment",null,
				                            $activeTestplans[$idx]['id']) == "yes")
				{
					$features[$idx] = $activeTestplans[$idx];
				}	
			}
		}
    	
		//if nothing special was selected, use the one in the session or the first
		if (!$argsObj->featureID)
		{
			if (sizeof($features))
			{
				if ($argsObj->testplanID)
				{
				  // $loop2do = sizeof($features);
					// for($idx = 0; $idx < $loop2do; $idx++)
					$key2loop = array_keys($features);
					foreach($key2loop as $idx)
					{
						if ($argsObj->testplanID == $features[$idx]['id'])
						{
							$argsObj->featureID = $argsObj->testplanID;
						}	
					}
				}
				if (!$argsObj->featureID)
				{
				  $xx = current($features);
					$argsObj->featureID = $xx['id'];
				}	
			}
		}

		foreach($users as &$user)
		{
			$user->readTestProjectRoles($dbHandler,$argsObj->testprojectID);
			$user->readTestPlanRoles($dbHandler,$argsObj->featureID);
		}
    	
		$tproject_info = $tprojectMgr->get_by_id($argsObj->testprojectID);
      
		$effectiveRoles = get_tplan_effective_role($dbHandler,$argsObj->featureID,$tproject_info,null,$users);

    // it seems that here is the best place to check if current logged user
    // can manege roles on current selected test plan.
    // why I did not find this before ???
    $features = array();
    $key2loop = array_keys($activeTestplans);
    foreach($key2loop as $idx)
    {
      $answer = $rolesCache[$testprojects[$idx]['effective_role']]->hasRight("user_role_assignment");
      //echo 'Question is: user_role_assignment - ANSWER IS:' . $answer . '<br>';
    
      if($answer == false)
      {
        $answer = $rolesCache[$testprojects[$idx]['effective_role']]->hasRight("testproject_user_role_assignment");  
        // echo 'Question is: testproject_user_role_assignment - ANSWER IS:' . $answer . '<br>';
      }  

      if($answer == true)
      {
        $features[$idx] = $testprojects[$idx];
      } 


        // Humm!!, think we need to check testplan_user_role_assignment and not "testplan_planning"
        if($argsObj->user->hasRight($dbHandler,"testplan_user_role_assignment",null,
                                    $activeTestplans[$idx]['id']) == "yes")
        {
          $features[$idx] = $activeTestplans[$idx];
        } 
    }



 		$ret = array($effectiveRoles,$features,$argsObj->featureID);
	}
	return $ret;
}


function doUpdate(&$dbHandler,&$argsObj,&$featureMgr)
{
	$featureMgr->deleteUserRoles($argsObj->featureID,
                               array_keys($argsObj->map_userid_roleid));
	foreach($argsObj->map_userid_roleid as $user_id => $role_id)
	{
		if ($role_id)
		{
			$featureMgr->addUserRole($user_id,$argsObj->featureID,$role_id);
		}	
	}
}

function initializeGui(&$dbHandler,$argsObj)
{
  $gui = new stdClass();
  
  $gui->highlight = initialize_tabsmenu();
  $gui->user_feedback = '';
  $gui->no_features = '';
  $gui->roles_updated = '';
  $gui->tproject_name = $argsObj->testprojectName;
  $gui->featureType = $argsObj->featureType;
  $gui->optRights = tlRole::getAll($dbHandler,null,null,null,tlRole::TLOBJ_O_GET_DETAIL_MINIMUM);
  $gui->features = null;
  $gui->featureID = null;
  $gui->role_colour = null;
  $gui->tprojectAccessTypeImg = '';

  $guiCfg = config_get('gui');
  if($guiCfg->usersAssignGlobalRoleColoring == ENABLED) 
  {
  	$gui->role_colour = tlRole::getRoleColourCfg($dbHandler);
  }
  $gui->pager = null;
  return $gui;
}

/**
 *
 */
function initLabels()
{
  $tg = array('test_project_user_roles_updated' => null,
              'testproject_roles_assign_disabled' => null,
              'assign_tproject_roles' => null,
              'assign_tplan_roles' => null);
  $labels = init_labels($tg);
  return $labels;
}
