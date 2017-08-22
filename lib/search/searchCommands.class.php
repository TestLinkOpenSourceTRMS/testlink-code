<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * testcases commands
 *
 * @filesource  searchCommands.class.php
 * @package     TestLink
 * @author      Francisco Mancardi - francisco.mancardi@gmail.com
 * @copyright   2007-2017, TestLink community 
 * @link        http://testlink.sourceforge.net/
 *
 *
 **/

class searchCommands
{
  private $db;
  private $tcaseMgr;
  private $tprojectMgr;
  private $cfieldMgr;

  private $args;
  private $gui;
  private $filters;
  private $tables;
  private $views;
  private $likeOp;

  private $reqSpecCfg;
  private $tcaseCfg;


  

  /**
   *
   */
  function __construct(&$db)
  {
    $this->db = $db;
    $this->tcaseMgr = new testcase($this->db);
    $this->tprojectMgr = new testproject($this->db);
    $this->cfieldMgr = &$this->tprojectMgr->cfield_mgr;
    $this->reqSpecMgr = new requirement_spec_mgr($this->db);

    $this->tcaseCfg = config_get('testcase_cfg');
    $dbt = strtolower($this->db->db->databaseType);
    
    $this->likeOp = 'LIKE';
    if(stristr($dbt, 'postgres') !== FALSE)
    {
      $this->likeOp = 'I' . $this->likeOp;
    }  
  }


  /**
   *
   */
  function isReqFeatureEnabled($tproject_id)
  {
    $info = $this->tprojectMgr->get_by_id($tproject_id);
    return isset($info['opt']->requirementsEnabled) 
            ? $info['opt']->requirementsEnabled : 0;
  }


  /**
   *
   */
  function getTestCaseIDSet($tproject_id)
  {
    $items = array();
    $this->tprojectMgr->get_all_testcases_id($tproject_id,$items);
    return $items;
  }

  /**
   *
   */
  function getTestSuiteIDSet($tproject_id)
  {
    $nt2ex = array('testcase' => 'exclude_me','testplan' => 'exclude_me',
                   'requirement_spec'=> 'exclude_me',
                   'requirement'=> 'exclude_me');

    $nt2exchi = array('testcase' => 'exclude_my_children',
                      'requirement_spec'=> 'exclude_my_children');

    $opt = array('recursive' => 0, 'output' => 'id');
    $filters = array('exclude_node_types' => $nt2ex,
                     'exclude_children_of' => $nt2exchi);
    
    $items = $this->tprojectMgr->tree_manager->get_subtree($tproject_id,$filters,$opt);

    return $items;
   }

  /**
   *
   */
  function getReqSpecIDSet($tproject_id)
  {
    $items = array();

    $opt = array('output' => 'id');
    $items = $this->reqSpecMgr->get_all_id_in_testproject($tproject_id);
    return $items;
  }

  /**
   *
   */
  function getReqIDSet($tproject_id)
  {
    $items = array();
    $items = $this->tprojectMgr->get_all_requirement_ids($tproject_id);
    return $items;
  }


  /**
   *
   */
  function getArgs()
  {
    return $this->args;
  }

  /**
   *
   */
  function getGui()
  {
    return $this->gui;
  }

  /**
   *
   */
  function getFilters()
  {
    return $this->filters;
  }

  /**
   *
   */
  function getTables()
  {
    return $this->tables;
  }

  /**
   *
   */
  function getViews()
  {
    return $this->views;
  }



  /**
   *
   */
  function initEnv()
  {
    $this->initArgs();
    $this->initGui();
    $this->initSearch();
  }


  /**
   *
   */
  function initSchema()
  {
    $this->tables = tlObjectWithDB::getDBTables(array('cfield_design_values',
                                                'nodes_hierarchy',
                                                'requirements','tcsteps',
                                                'testcase_keywords',
                                                'req_specs_revisions',
                                                'req_versions',
                                                'testsuites','tcversions',
                                                'users',
                                                'object_keywords'));
                                  
    $this->views = tlObjectWithDB::getDBViews(array('latest_rspec_revision',
                                               'latest_req_version',
                                               'latest_tcase_version_number'));
  }


  /**
   *
   */
  function initArgs()
  {
    $cb = array("rq_scope" => array(tlInputParameter::CB_BOOL),
                "rq_title" => array(tlInputParameter::CB_BOOL),
                "rq_doc_id" => array(tlInputParameter::CB_BOOL),
                "rs_scope" => array(tlInputParameter::CB_BOOL),
                "rs_title" => array(tlInputParameter::CB_BOOL),
                "tc_summary" => array(tlInputParameter::CB_BOOL),
                "tc_title" => array(tlInputParameter::CB_BOOL),
                "tc_steps" => array(tlInputParameter::CB_BOOL),
                "tc_expected_results" => array(tlInputParameter::CB_BOOL),
                "tc_preconditions" => array(tlInputParameter::CB_BOOL),
                "tc_id" => array(tlInputParameter::CB_BOOL),
                "ts_summary" => array(tlInputParameter::CB_BOOL),
                "ts_title" => array(tlInputParameter::CB_BOOL));


    $strIn = array("tcWKFStatus" => array(tlInputParameter::STRING_N,0,1),
                   "reqStatus" => array(tlInputParameter::STRING_N,0,1),
                   "reqType" => array(tlInputParameter::STRING_N),
                   "created_by" => array(tlInputParameter::STRING_N,0,50),
                   "edited_by" => array(tlInputParameter::STRING_N,0,50),
                   "creation_date_from" => array(tlInputParameter::STRING_N),
                   "creation_date_to" => array(tlInputParameter::STRING_N),
                   "modification_date_from" => array(tlInputParameter::STRING_N),
                   "modification_date_to" => array(tlInputParameter::STRING_N),
                   "and_or" => array(tlInputParameter::STRING_N,2,3) );

    $numIn = array("keyword_id" => array(tlInputParameter::INT_N),
                   "custom_field_id" => array(tlInputParameter::INT_N));

    $iParams = array("target" => array(tlInputParameter::STRING_N),
                     "doAction" => array(tlInputParameter::STRING_N,0,10),
                     "custom_field_value" => array(tlInputParameter::STRING_N,0,20),
                     "tproject_id" => array(tlInputParameter::INT_N));
                   
    $this->args = new stdClass();
    $args = &$this->args;

    $iParams = $iParams + $cb + $strIn + $numIn;

    R_PARAMS($iParams,$this->args);

    // At least one checkbox need to be checked
    $args->oneCheck = false;
    foreach($cb as $key => $vx)
    {
      $args->oneCheck = $args->$key;
      if($args->oneCheck)
      {
        break;
      }       
    } 

    $args->oneValueOK = false; 
    foreach($numIn as $key => $vx)
    {
      $args->oneValueOK = (intval($args->$key) > 0);
      if($args->oneValueOK)
      {
        break;
      }  
    } 

    if($args->oneValueOK == false)
    {
      foreach($strIn as $key => $vx)
      {
        $args->oneValueOK = (trim($args->$key) != ''); 
        if($args->oneValueOK)
        {
          break;
        }  
      }     
    }  

    // try to sanitize target against XSS
    // remove all blanks
    // remove some html entities
    // remove ()
    // Need to give a look
    //$tt = array('<','>','(',')');
    //$args->target = str_replace($tt,'',$args->target);
    $ts = preg_replace("/ {2,}/", " ", $args->target);
    $args->target = trim($ts);

    $args->userID = intval(isset($_SESSION['userID']) ? $_SESSION['userID'] : 0);

    if(is_null($args->tproject_id) || intval($args->tproject_id) <= 0)
    {
      $args->tprojectID = intval(isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : 0);
      $args->tprojectName = isset($_SESSION['testprojectName']) ? $_SESSION['testprojectName'] : 0;
    }  
    else
    {
      $args->tprojectID = intval($args->tproject_id);
      $info = $this->tprojectMgr->get_by_id($args->tprojectID);
      $args->tprojectName = $info['name'];
    }  

    if($args->tprojectID <= 0)
    {
      throw new Exception("Error Processing Request - Invalid Test project id " . __FILE__);
    }   

    // convert according local

    // convert "creation date from" to iso format for database usage
    $k2w = array('creation_date_from' => '','creation_date_to' => " 23:59:59",
                 'modification_date_from' => '', 'modification_date_to' => " 23:59:59");

    $k2f = array('creation_date_from' => ' creation_ts >= ',
                 'creation_date_to' => 'creation_ts <= ',
                 'modification_date_from' => ' modification_ts >= ', 
                 'modification_date_to' => ' modification_ts <= ');


    $dateFormat = config_get('date_format');
    $filter['dates4tc'] = null;
    $filter['dates4rq'] = null;
    foreach($k2w as $key => $value)
    {
      $lk = 'loc_' . $key;
      $args->$lk = '';
      
      if (isset($args->$key) && $args->$key != '') 
      {
        $da = split_localized_date($args->$key, $dateFormat);
        if ($da != null) 
        {
          $args->$key = $da['year'] . "-" . $da['month'] . "-" . $da['day'] . $value; // set date in iso format
          $this->filters['dates4tc'][$key] = " AND TCV.{$k2f[$key]} '{$args->$key}' ";
          $this->filters['dates4rq'][$key] = " AND RQV.{$k2f[$key]} '{$args->$key}' ";

          $args->$lk = implode("/",$da);
        }
      }
    } 

    // $args->and_or = isset($_REQUEST['and_or']) ? $_REQUEST['and_or'] : 'or';   
    $args->user = $_SESSION['currentUser'];

    $args->canAccessTestSpec = $args->user->hasRight($this->db,'mgt_view_tc',$args->tproject_id);

    $args->canAccessReqSpec = $args->user->hasRight($this->db,'mgt_view_req',$args->tproject_id);

  }


  /**
   * 
   *
   */
  function initGui()
  {
    $this->gui = new stdClass();

    $this->gui->caller = 'search.php';

    $this->gui->tcasePrefix = $this->tprojectMgr->getTestCasePrefix($this->args->tprojectID);
    $this->gui->tcasePrefix .= $this->tcaseCfg->glue_character;



    $this->gui->reqType = $this->args->reqType;
    $this->gui->reqStatus = $this->args->reqStatus;
    $this->gui->tcWKFStatus = $this->args->tcWKFStatus;

    $this->gui->pageTitle = lang_get('multiple_entities_search');
    $this->gui->warning_msg = '';
    $this->gui->path_info = null;
    $this->gui->resultSet = null;
    $this->gui->tableSet = null;
    $this->gui->bodyOnLoad = null;
    $this->gui->bodyOnUnload = null;
    $this->gui->refresh_tree = false;
    $this->gui->hilite_testcase_name = false;
    $this->gui->show_match_count = false;
    $this->gui->row_qty = 0;
    $this->gui->doSearch = ($this->args->doAction == 'doSearch');
    $this->gui->tproject_id = intval($this->args->tprojectID);
    
    // ----------------------------------------------------
    $this->gui->mainCaption = lang_get('testproject') . " " . $this->args->tprojectName;
    
    $this->gui->search_important_notice = sprintf(lang_get('search_important_notice'),$this->args->tprojectName);

    // need to set values that where used on latest search (if any was done)
    // $this->gui->importance = config_get('testcase_importance_default');

    $this->gui->tc_steps = $this->args->tc_steps;
    $this->gui->tc_title = $this->args->tc_title;
    $this->gui->tc_summary = $this->args->tc_summary;
    $this->gui->tc_preconditions = $this->args->tc_preconditions;
    $this->gui->tc_expected_results = $this->args->tc_expected_results;
    $this->gui->tc_id = $this->args->tc_id;

    $this->gui->ts_title = $this->args->ts_title;
    $this->gui->ts_summary = $this->args->ts_summary;

    $this->gui->rs_title = $this->args->rs_title;
    $this->gui->rs_scope = $this->args->rs_scope;

    $this->gui->rq_title = $this->args->rq_title;
    $this->gui->rq_scope = $this->args->rq_scope;
    $this->gui->rq_doc_id = $this->args->rq_doc_id;


    $this->gui->custom_field_id = $this->args->custom_field_id;
    $this->gui->custom_field_value = $this->args->custom_field_value;
    $this->gui->creation_date_from = $this->args->loc_creation_date_from;
    $this->gui->creation_date_to = $this->args->loc_creation_date_to;
    $this->gui->modification_date_from = $this->args->loc_modification_date_from;
    $this->gui->modification_date_to = $this->args->loc_modification_date_to;

    $this->gui->created_by = trim($this->args->created_by);
    $this->gui->edited_by =  trim($this->args->edited_by);
    $this->gui->keyword_id = intval($this->args->keyword_id);


    $this->gui->forceSearch = false;
    
    $this->gui->and_selected = $this->gui->or_selected = '';
    switch($this->args->and_or)
    {
      case 'and':
        $this->gui->and_selected = ' selected ';
      break;

      case 'or':
      default:
        $this->gui->or_selected = ' selected ';
      break;
    }

    $reqCfg = config_get('req_cfg');
    $this->gui->reqStatusDomain = init_labels($reqCfg->status_labels);

    $this->gui->reqTypes = array_flip(init_labels($reqCfg->type_labels));
    foreach ($this->gui->reqTypes as $key => $value) 
    {
      $this->gui->reqTypes[$key] = 'RQ' . $value;  
    }
    $this->gui->reqTypes = array_flip($this->gui->reqTypes);
    $this->gui->tcWKFStatusDomain = $this->getTestCaseWKFStatusDomain();
  }


  /**
   *
   */
  function initSearch()
  {

    $this->gui->reqEnabled = $this->isReqFeatureEnabled($this->args->tproject_id);


    $this->gui->cf = null;
    $this->gui->design_cf_req = null;

    $this->gui->design_cf_tc = $this->cfieldMgr->get_linked_cfields_at_design(
                            $this->args->tproject_id,cfield_mgr::ENABLED,null,'testcase');

    if($this->gui->reqEnabled)
    {
      $this->gui->design_cf_req = $this->cfieldMgr->get_linked_cfields_at_design(
                              $this->args->tproject_id,
                              cfield_mgr::ENABLED,null,'requirement');
    }  

    if(!is_null($this->gui->design_cf_tc))
    {
      $this->gui->cf = $this->gui->design_cf_tc;
    }  
    
    if(!is_null($this->gui->design_cf_req))
    {
      if(is_null($this->gui->cf))
      {
        $this->gui->cf = $this->gui->design_cf_req;
      }  
      else
      {
        $this->gui->cf += $this->gui->design_cf_req;        
      }  
    }

    $this->gui->filter_by['custom_fields'] = !is_null($this->gui->cf) && count($this->gui->cf) > 0;

    $this->gui->keywords = $this->tprojectMgr->getKeywordSet($this->args->tproject_id);
    $this->gui->filter_by['keyword'] = !is_null($this->gui->keywords);
   
    $reqSpecSet = $this->tprojectMgr->genComboReqSpec($this->args->tprojectID);
    $this->gui->filter_by['requirement_doc_id'] = !is_null($reqSpecSet);
    $reqSpecSet = null; 

    $this->gui->status = isset($this->args->status) ? intval($this->args->status) : '';
    $this->gui->target = $this->args->target;
  }

  /** 
   *
   */
  function searchReqSpec($targetSet,$canUseTarget)
  {
    // shotcuts
    $args = &$this->args;
    $db = &$this->db;

    $mapRSpec = null;
    $sql = "SELECT RSRV.name, RSRV.scope, LRSR.req_spec_id, RSRV.id," .
           "LRSR.revision " . 
           "FROM {$this->views['latest_rspec_revision']} LRSR " .
           "JOIN {$this->tables['req_specs_revisions']} RSRV " .
           "ON RSRV.parent_id = LRSR.req_spec_id " .
           "AND RSRV.revision = LRSR.revision " .
           "WHERE LRSR.testproject_id = " . $args->tproject_id;

    //DEBUGecho __FUNCTION__ . ' SQL Line:' . __LINE__ . $sql .'<br>';
    $doFilter = true;
    
    /*
    if(!is_null($args->rspecType))
    {
      $doFilter = true;
      $sql .= " AND RSRV.type='" . $db->prepare_string($args->rspecType) . "' ";

      //DEBUGecho __FUNCTION__ . ' SQL Line:' . __LINE__ . $sql .'<br>';
    } 
    */


    $filterRS = null;
    if( $canUseTarget )
    {
      $doFilter = true;
      $filterRS['tricky'] = " 1=0 ";
      
      $filterRS['scope'] = ' OR ( ';
      $filterRS['scope'] .= $args->and_or == 'or' ? ' 1=0 ' : ' 1=1 ';
      foreach($targetSet as $target)
      {
        $filterRS['scope'] .= $args->and_or . " UDFStripHTMLTags(RSRV.scope) $this->likeOp '%{$target}%' ";  
      }  
      $filterRS['scope'] .= ')';
  
      $filterRS['name'] = ' OR ( ';
      $filterRS['name'] .= $args->and_or == 'or' ? ' 1=0 ' : ' 1=1 ';
      foreach($targetSet as $trgt)
      {
        $target = trim($trgt);
        $filterRS['name'] .= $args->and_or . " RSRV.name $this->likeOp '%{$target}%' ";  
      }  
      $filterRS['name'] .= ')';
    }  

    $otherFRS = '';  
    if(!is_null($filterRS))
    {
      $otherFRS = " AND (" . implode("",$filterRS) . ")";
    }  

    $sql .= $otherFRS;
    //DEBUGecho __FUNCTION__ . ' SQL Line:' . __LINE__ . $sql .'<br>';

    if($doFilter)
    {
      $mapRSpec = $db->fetchRowsIntoMap($sql,'req_spec_id'); 
    }  
    return $mapRSpec;
  } 

  /**
   *
   */
  function searchReq($targetSet,$canUseTarget,$req_cf_id)
  {
    // shortcuts
    $args = &$this->args;
    $gui = &$this->gui;
    $db = &$this->db;
    $tables = &$this->tables;
    $views = &$this->views;

    $reqSet = $this->getReqIDSet($args->tproject_id);

    $noItems = is_null($reqSet) || count($reqSet) == 0;
    $bye = $noItems || (!$canUseTarget && $req_cf_id <= 0); 
    if( $bye )
    {  
      return null;
    }

    // OK go ahead
    $doSql = true;
    $doFilter = false;
    $fi = null;
    $from['by_custom_field'] = ''; 


    if($req_cf_id >0)
    {      
      $cf_def = $gui->design_cf_rq[$req_cf_id];

      $from['by_custom_field']= " JOIN {$tables['cfield_design_values']} CFD " .
                                " ON CFD.node_id=RQV.id ";
      $fi['by_custom_field'] = " AND CFD.field_id=" . intval($req_cf_id);

      switch($gui->cf_types[$cf_def['type']])
      {
        case 'date':
          $args->custom_field_value = $this->cfieldMgr->cfdate2mktime($args->custom_field_value);
          
          $fi['by_custom_field'] .= " AND CFD.value = {$args->custom_field_value}";
        break;

        default:
          $args->custom_field_value = $db->prepare_string($args->custom_field_value);
          $fi['by_custom_field'] .= " AND CFD.value $this->likeOp '%{$args->custom_field_value}%' ";
        break;
      }
    }  

    $args->created_by = trim($args->created_by);
    $from['users'] = '';
    if($args->created_by != '' )
    {
      $doFilter = true;
      $from['users'] .= " JOIN {$tables['users']} RQAUTHOR ON RQAUTHOR.id = RQV.author_id ";
      $fi['author'] = " AND ( RQAUTHOR.login $this->likeOp '%{$args->created_by}%' OR " .
                      "       RQAUTHOR.first $this->likeOp '%{$args->created_by}%' OR " .
                      "       RQAUTHOR.last $this->likeOp '%{$args->created_by}%') ";
    }  
  
    $args->edited_by = trim($args->edited_by);
    if( $args->edited_by != '' )
    {
      $doFilter = true;
      $from['users'] .= " JOIN {$tables['users']} UPDATER ON UPDATER.id = RQV.modifier_id ";
      $fi['modifier'] = " AND ( UPDATER.login $this->likeOp '%{$args->edited_by}%' OR " .
                            "       UPDATER.first $this->likeOp '%{$args->edited_by}%' OR " .
                            "       UPDATER.last $this->likeOp '%{$args->edited_by}%') ";
    }  

    if( $doSql )
    {  
      $doFilter = true;
  
      $sql = " /* " . __LINE__ . " */ " . 
             " SELECT RQ.id AS req_id, RQV.scope,RQ.req_doc_id,NHRQ.name  " .
             " FROM {$tables['nodes_hierarchy']} NHRQV " .
             " JOIN {$views['latest_req_version']} LV on LV.req_id = NHRQV.parent_id " .
             " JOIN {$tables['req_versions']} RQV on NHRQV.id = RQV.id AND RQV.version = LV.version " .
             " JOIN {$tables['nodes_hierarchy']} NHRQ on NHRQ.id = LV.req_id " .
             " JOIN {$tables['requirements']} RQ on RQ.id = LV.req_id " .
             $from['users'] . $from['by_custom_field'] .
             " WHERE RQ.id IN(" . implode(',', $reqSet) . ")";

      //DEBUGecho __FUNCTION__ . ' SQL Line:' . __LINE__ . $sql .'<br>';
 

      if(!is_null($args->reqType))
      {
        $doFilter = true;
        $sql .= " AND RQV.type ='" . $db->prepare_string($args->reqType) . "' ";

        //DEBUGecho __FUNCTION__ . ' SQL Line:' . __LINE__ . $sql .'<br>';
      }  

      if($args->reqStatus != '')
      {
        $doFilter = true;
        $sql .= " AND RQV.status='" . $db->prepare_string($args->reqStatus) . "' ";

        //DEBUGecho __FUNCTION__ . ' SQL Line:' . __LINE__ . $sql .'<br>';
      }  

      $filterRQ = null;
      if( $canUseTarget )
      {
        $doFilter = true;
        $filterRQ['tricky'] = " 1=0 ";

        if( $args->rq_scope )
        {
          $filterRQ['scope'] = ' OR ( ';
          $filterRQ['scope'] .= $args->and_or == 'or' ? ' 1=0 ' : ' 1=1 ';
          foreach($targetSet as $target)
          {
            $filterRQ['scope'] .= $args->and_or . " UDFStripHTMLTags(RQV.scope) $this->likeOp '%{$target}%' "; 
          }  
          $filterRQ['scope'] .= ')';
        }  

        if( $args->rq_title )
        {
          $filterRQ['name'] = ' OR ( ';
          $filterRQ['name'] .= $args->and_or == 'or' ? ' 1=0 ' : ' 1=1 ';

          foreach($targetSet as $target)
          {
            $filterRQ['name'] .= $args->and_or . " NHRQ.name $this->likeOp '%{$target}%' "; 
          }  
          $filterRQ['name'] .= ')';
        }  

        if( $args->rq_doc_id )
        {
          $filterRQ['req_doc_id'] = ' OR ( ';
          $filterRQ['req_doc_id'] .= $args->and_or == 'or' ? ' 1=0 ' : ' 1=1 ';
          foreach($targetSet as $target)
          {
            $filterRQ['req_doc_id'] .= $args->and_or . " RQ.req_doc_id $this->likeOp '%{$target}%' ";  
          }  
          $filterRQ['req_doc_id'] .= ')';
        } 
      } 

      $otherFRQ = '';  
      if(!is_null($filterRQ))
      {
        $otherFRQ = " AND (" . implode("",$filterRQ) . ")";
      }  

      $xfil = ''; 
      if(!is_null($fi))
      {
        $xfil = implode("",$fi);
      }  

      $sql .= $xfil . $otherFRQ;
      if( $doFilter ) 
      {
        //DEBUGecho __FUNCTION__ . ' SQL Line:' . __LINE__ . $sql .'<br>';
        $mapRQ = $db->fetchRowsIntoMap($sql,'req_id'); 
      }

      return $mapRQ;
    }  
  }


  /**
   *
   */
  function searchTestSuites($targetSet,$canUseTarget)
  {

    // shortcuts
    $args = &$this->args;
    $gui = &$this->gui;
    $db = &$this->db;
    $tables = &$this->tables;
    $views = &$this->views;

    $mapTS = null;
    $tsuiteSet = $this->getTestSuiteIDSet($args->tproject_id);
    if(is_null($tsuiteSet) || count($tsuiteSet) == 0)
    {
      return null;
    }  

    $filterSpecial = null;
    $filterSpecial['tricky'] = " 1=0 ";

    if( ($doIt = $args->ts_summary && $canUseTarget) )
    {
      $filterSpecial['ts_summary'] = ' OR ( ';
      $filterSpecial['ts_summary'] .= $args->and_or == 'or' ? ' 1=0 ' : ' 1=1 ';
      
      foreach($targetSet as $target)
      {
        $filterSpecial['ts_summary'] .= $args->and_or . 
          " UDFStripHTMLTags(TS.details) $this->likeOp '%{$target}%' ";
      }  
      $filterSpecial['ts_summary'] .= ')';
    }  

    if( ($doIt = $args->ts_title && $canUseTarget) )
    {
      $filterSpecial['ts_title'] = ' OR ( ';
      $filterSpecial['ts_title'] .= $args->and_or == 'or' ? ' 1=0 ' : ' 1=1 ';

      foreach($targetSet as $target)
      {
        $filterSpecial['ts_title'] .= $args->and_or . " NH_TS.name $this->likeOp '%{$target}%' ";
      }  
      $filterSpecial['ts_title'] .= ')';
    }  

    $otherFilters = '';  
    if(!is_null($filterSpecial))
    {
      $otherFilters = " AND (" . implode("",$filterSpecial) . ")";
    }  

    if($args->ts_title || $args->ts_summary)
    {
      $fromTS['by_keyword_id']= '';
      $filterTS['by_keyword_id']='';
      if($args->keyword_id)
      {
       $fromTS['by_keyword_id'] = " JOIN {$tables['object_keywords']} KW ON KW.fk_id = NH_TS.id ";
       $filterTS['by_keyword_id'] = " AND KW.keyword_id  = " . $args->keyword_id; 
      }  
    
      $sqlFields = " SELECT NH_TS.name, TS.id, TS.details " .
                   " FROM {$tables['nodes_hierarchy']} NH_TS " .
                   " JOIN {$tables['testsuites']} TS ON TS.id = NH_TS.id " .
                   $fromTS['by_keyword_id'] .
                   " WHERE TS.id IN (" . implode(',', $tsuiteSet) . ")";
      
      $sql = $sqlFields . $filterTS['by_keyword_id'] . $otherFilters;
      $mapTS = $db->fetchRowsIntoMap($sql,'id'); 

      //DEBUGecho 'DEBUG===' . $sql;
    }

    return $mapTS;
  }  


  /**
   *
   */
  function searchTestCases($tcaseSet,$targetSet,$canUseTarget,$tc_cf_id)
  {
    // shortcuts
    $args = &$this->args;
    $gui = &$this->gui;
    $db = &$this->db;
    $tables = &$this->tables;
    $views = &$this->views;


    $from['tc_steps'] = "";
    $from['users'] = "";
    $from['by_keyword_id'] = "";
    $from['by_custom_field'] = "";

    $filter = null;
    $filterSpecial = null;


    if( is_null($tcaseSet) || count($tcaseSet) == 0)
    {
      return null;
    }  


    $filter['by_tc_internal_id'] = " AND NH_TCV.parent_id IN (" . 
                          implode(",",$tcaseSet) . ") ";


    $filterSpecial['tricky'] = " 1=0 ";

    if($args->tc_id)
    {
      $filterSpecial['by_tc_id'] = '';

      // Remember that test case id is a number!
      foreach($targetSet as $tgx)
      {
        $target = trim($tgx);
        if( is_numeric($target) )
        {
          $filterSpecial['by_tc_id'] .= $args->and_or . 
                                      " TCV.tc_external_id = $target ";  
        }  
      }  
    }  

    $doFilter = false;
    $doFilter = ($args->tc_summary || $args->tc_title || $args->tc_id);

    if($tc_cf_id > 0)
    {
      $cf_def = $gui->design_cf_tc[$tc_cf_id];

      $from['by_custom_field']= " JOIN {$tables['cfield_design_values']} CFD " .
                                " ON CFD.node_id=NH_TCV.id ";
      $filter['by_custom_field'] = " AND CFD.field_id=" . intval($tc_cf_id);

      switch($gui->cf_types[$cf_def['type']])
      {
        case 'date':
          $args->custom_field_value = $cfieldMgr->cfdate2mktime($args->custom_field_value);
          
          $filter['by_custom_field'] .= " AND CFD.value = {$args->custom_field_value}";
        break;

        default:
          $args->custom_field_value = $db->prepare_string($args->custom_field_value);
          $filter['by_custom_field'] .= " AND CFD.value $this->likeOp '%{$args->custom_field_value}%' ";
        break;
      }
    }

    if($args->tc_steps || $args->tc_expected_results)
    {
      $doFilter = true;
      $from['tc_steps'] = " LEFT OUTER JOIN {$tables['nodes_hierarchy']} " .
                          " NH_TCSTEPS ON NH_TCSTEPS.parent_id = NH_TCV.id " .
                          " LEFT OUTER JOIN {$tables['tcsteps']} TCSTEPS " .
                          " ON NH_TCSTEPS.id = TCSTEPS.id  ";
    }

    if($args->tc_steps && $canUseTarget)
    {
      $filterSpecial['by_steps'] = ' OR ( ';
      $filterSpecial['by_steps'] .= $args->and_or == 'or' ? ' 1=0 ' : ' 1=1 ';
      
      foreach($targetSet as $target)
      {
        $filterSpecial['by_steps'] .= $args->and_or . 
          " UDFStripHTMLTags(TCSTEPS.actions) $this->likeOp '%{$target}%' ";  
      }  
      $filterSpecial['by_steps'] .= ')';
    }    

    if($args->tc_expected_results && $canUseTarget)
    {
      $filterSpecial['by_expected_results'] = ' OR ( ';
      $filterSpecial['by_expected_results'] .= $args->and_or == 'or' ? ' 1=0 ' : ' 1=1 ';
      
      foreach($targetSet as $target)
      {
        $filterSpecial['by_expected_results'] .= $args->and_or . 
          " UDFStripHTMLTags(TCSTEPS.expected_results) $this->likeOp '%{$target}%' "; 
      }  
      $filterSpecial['by_expected_results'] .= ')';
    }    

    if($canUseTarget)
    {
      $k2w = array('name' => 'NH_TC', 'summary' => 'TCV', 'preconditions' => 'TCV');
      $i2s = array('name' => 'tc_title', 'summary' => 'tc_summary', 
                   'preconditions' => 'tc_preconditions');
      foreach($k2w as $kf => $alias)
      {
        $in = $i2s[$kf];
        if($args->$in)
        {
          $doFilter = true;
   
          $filterSpecial[$kf] = ' OR ( ';
          $filterSpecial[$kf] .= $args->and_or == 'or' ? ' 1=0 ' : ' 1=1 ';
     
          foreach($targetSet as $target)
          {
            $filterSpecial[$kf] .= " {$args->and_or} ";
            $xx = "{$alias}.{$kf}";
            switch($kf)
            {
              case 'summary':
              case 'preconditions':
                $xx = " UDFStripHTMLTags(" . $xx . ") ";
              break;
            }
            $filterSpecial[$kf] .= "{$xx} {$this->likeOp}  '%{$target}%' "; 
          }  
          $filterSpecial[$kf] .= ' )';
        }
      }     
    } 


    $otherFilters = '';  
    if(!is_null($filterSpecial) && count($filterSpecial) > 1)
    {
      $otherFilters = " AND (/* filterSpecial */ " . 
                      implode("",$filterSpecial) . ")";
    }  

    // Search on latest test case version using view    
    $sqlFields = " SELECT LVN.testcase_id, NH_TC.name, TCV.id AS tcversion_id," .
                 " TCV.summary, TCV.version, TCV.tc_external_id "; 
    
    if($doFilter)
    {
      if($args->tcWKFStatus > 0)       
      {
        $tg = intval($args->tcWKFStatus);
        $filter['by_tcWKFStatus'] = " AND TCV.status = {$tg} "; 
      }

      if($args->keyword_id)       
      {
         $from['by_keyword_id'] = " JOIN {$tables['testcase_keywords']} KW ON KW.testcase_id = NH_TC.id ";
         $filter['by_keyword_id'] = " AND KW.keyword_id  = " . $args->keyword_id; 
      }

      $created_by_on_tc = $args->created_by = trim($args->created_by);
      $from['users'] = '';
      if( $created_by_on_tc != '' )
      {
        $doFilter = true;
        $from['users'] .= " JOIN {$tables['users']} AUTHOR ON AUTHOR.id = TCV.author_id ";
        $filter['author'] = " AND ( AUTHOR.login $this->likeOp '%{$args->created_by}%' OR " .
                            "       AUTHOR.first $this->likeOp '%{$args->created_by}%' OR " .
                            "       AUTHOR.last $this->likeOp '%{$args->created_by}%') ";
      }  
    
      $edited_by_on_tc = $args->edited_by = trim($args->edited_by);
      if( $edited_by_on_tc != '' )
      {
        $doFilter = true;
        $from['users'] .= " JOIN {$tables['users']} UPDATER ON UPDATER.id = TCV.updater_id ";
        $filter['modifier'] = " AND ( UPDATER.login $this->likeOp '%{$args->edited_by}%' OR " .
                            "         UPDATER.first $this->likeOp '%{$args->edited_by}%' OR " .
                            "         UPDATER.last $this->likeOp '%{$args->edited_by}%') ";
      }  
    }


    // search fails if test case has 0 steps - Added LEFT OUTER
    $sqlPart2 = " FROM {$views['latest_tcase_version_number']} LVN " .
                " JOIN {$tables['nodes_hierarchy']} NH_TC ON NH_TC.id = LVN.testcase_id " .
                " JOIN {$tables['nodes_hierarchy']} NH_TCV ON NH_TCV.parent_id = NH_TC.id  " .
                " JOIN {$tables['tcversions']} TCV ON NH_TCV.id = TCV.id " .
                " AND TCV.version = LVN.version " . 
                $from['tc_steps'] . $from['users'] . $from['by_keyword_id'] .
                $from['by_custom_field'] .
                " WHERE LVN.testcase_id IN (" . implode(',', $tcaseSet) . ")";


    $mapTC = NULL;
    if($doFilter)
    {
      $mixedFilter = $this->getFilters();
      if ($filter)
      {
        $sqlPart2 .= implode("",$filter);
      }
      
      if ($mixedFilter['dates4tc'])
      {
        $sqlPart2 .= implode("",$mixedFilter['dates4tc']);
      }
   
      $sql = $sqlFields . $sqlPart2 . $otherFilters;

      //DEBUGecho __FUNCTION__ . '-' . __LINE__ . '-' . $sql .'<br>';
      $mapTC = $db->fetchRowsIntoMap($sql,'testcase_id'); 
    }  

    return $mapTC;
  }

  /**
   *
   */
  static function getTestCaseWKFStatusDomain()
  {
    $cv = array_flip(config_get('testCaseStatus'));
    foreach($cv as $cc => $vv)
    {
      $lbl = lang_get('testCaseStatus_' . $vv);
      $cv[$cc] = lang_get('testCaseStatus_' . $vv);
    }  
    return $cv;
  }


} // end class  
