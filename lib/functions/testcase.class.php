<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource  testcase.class.php
 * @package     TestLink
 * @author      Francisco Mancardi (francisco.mancardi@gmail.com)
 * @copyright   2005-2018, TestLink community
 * @link        http://www.testlink.org/
 *
 */

/** related functionality */
require_once( dirname(__FILE__) . '/requirement_mgr.class.php' );
require_once( dirname(__FILE__) . '/assignment_mgr.class.php' );
require_once( dirname(__FILE__) . '/attachments.inc.php' );
require_once( dirname(__FILE__) . '/users.inc.php' );
require_once( dirname(__FILE__) . '/event_api.php');

/** list of supported format for Test case import/export */
$g_tcFormatStrings = array ("XML" => lang_get('the_format_tc_xml_import'));

/**
 * class for Test case CRUD
 * @package   TestLink
 */
class testcase extends tlObjectWithAttachments
{
  const AUTOMATIC_ID=0;
  const DEFAULT_ORDER=0;
  const ALL_VERSIONS=0;
  const LATEST_VERSION=-1;
  const AUDIT_OFF=0;
  const AUDIT_ON=1;
  const CHECK_DUPLICATE_NAME=1;
  const DONT_CHECK_DUPLICATE_NAME=0;
  const ENABLED=1;
  const ALL_TESTPLANS=null;
  const ANY_BUILD=null;
  const GET_NO_EXEC=1;
  const ANY_PLATFORM=null;
  const NOXMLHEADER=true;
  const EXECUTION_TYPE_MANUAL = 1;
  const EXECUTION_TYPE_AUTO = 2;

  const NAME_PHOPEN = '[[';
  const NAME_PHCLOSE = ']]';
  const NAME_DIVIDE = '::';
  const GHOSTMASK = '[ghost]"TestCase":"%s","Version":"%s"[/ghost]';

  /** @var database handler */
  var $db;
  var $tree_manager;
  var $tproject_mgr;

  var $node_types_descr_id;
  var $node_types_id_descr;
  var $my_node_type;

  var $assignment_mgr;
  var $assignment_types;
  var $assignment_status;

  var $cfield_mgr;

  var $import_file_types = array("XML" => "XML");
  var $export_file_types = array("XML" => "XML");
  var $execution_types = array();
  var $cfg;
  var $debugMsg;
  var $layout;
  var $XMLCfg;

  /**
   * testplan class constructor
   *
   * @param resource &$db reference to database handler
   */
  function __construct(&$db) {
    $this->db = &$db;
    $this->tproject_mgr = new testproject($this->db);
    $this->tree_manager = &$this->tproject_mgr->tree_manager;

    $this->node_types_descr_id=$this->tree_manager->get_available_node_types();
    $this->node_types_id_descr=array_flip($this->node_types_descr_id);
    $this->my_node_type=$this->node_types_descr_id['testcase'];

    $this->assignment_mgr=New assignment_mgr($this->db);
    $this->assignment_types=$this->assignment_mgr->get_available_types();
    $this->assignment_status=$this->assignment_mgr->get_available_status();

    $this->cfield_mgr = new cfield_mgr($this->db);

    $this->execution_types = $this->getExecutionTypes();

    $this->layout = $this->getLayout();

    $this->cfg = new stdClass();
    $this->cfg->testcase = config_get('testcase_cfg');
    $this->cfg->execution = config_get('exec_cfg');
    $this->cfg->cfield = config_get('custom_fields');

    $this->debugMsg = ' Class:' . __CLASS__ . ' - Method: ';


    $this->XMLCfg = new stdClass();
    $this->XMLCfg->att = $this->getAttXMLCfg();
    $this->XMLCfg->req = $this->getReqXMLCfg();

    // ATTENTION:
    // second argument is used to set $this->attachmentTableName,property that this calls
    // get from his parent
    // ORIGINAL
    // parent::__construct($this->db,"nodes_hierarchy");
    parent::__construct($this->db,"tcversions");

  }


  /**
   *
   */
  static function getExecutionTypes() {
    $stdSet = array(self::EXECUTION_TYPE_MANUAL => lang_get('manual'),
                    self::EXECUTION_TYPE_AUTO => lang_get('automated'));

    if( !is_null($customSet = config_get('custom_execution_types')) )
    {
      foreach($customSet as $code => $lbl)
      {
        $stdSet[$code] = lang_get($lbl);
      }
    }
    return $stdSet;
  }


  /**
   *
   */
  function getName($tcase_id) {
    $info  = $this->tree_manager->get_node_hierarchy_info($tcase_id);
    return $info['name'];
  }
  /**
   *
   */
  function getFileUploadRelativeURL($identity) {
    $url = "lib/testcases/tcEdit.php?doAction=fileUpload&" . 
           "&tcase_id=" . intval($identity->tcase_id) .
           "&tcversion_id=" . intval($identity->tcversion_id) .
           "&tproject_id=" . intval($identity->tproject_id);
    return $url;
  }

  /**
   *
   */
  function getDeleteAttachmentRelativeURL($identity) {
    $url = "lib/testcases/tcEdit.php?doAction=deleteFile&tcase_id=" . intval($identity->tcase_id) .
           "&tcversion_id=" . intval($identity->tcversion_id) .
           "&tproject_id=" . intval($identity->tproject_id) . "&file_id=" ;

    return $url;
  }

  /*
    function: get_export_file_types
              getter

    args: -

    returns: map
             key: export file type code
             value: export file type verbose description

  */
  function get_export_file_types()
  {
    return $this->export_file_types;
  }

  /*
    function: get_impor_file_types
              getter

    args: -

    returns: map
             key: import file type code
             value: import file type verbose description

  */
  function get_import_file_types()
  {
    return $this->import_file_types;
  }

  /*
     function: get_execution_types
               getter

     args: -

     returns: map
              key: execution type code
              value: execution type verbose description

  */
  function get_execution_types()
  {
    return $this->execution_types;
  }


  /**
   *  just a wrapper
   *
   */
  function createFromObject($item) {
    static $wkfstatus;

    if(is_null($wkfstatus)) {
      $wkfstatus = config_get('testCaseStatus');
    }
    $options = array('check_duplicate_name' => self::CHECK_DUPLICATE_NAME,
                     'action_on_duplicate_name' => 'block',
                     'estimatedExecDuration' => 0,
                     'status' => $wkfstatus['draft'], 'importLogic' => null);

    if(property_exists($item, 'estimatedExecDuration')) {
      $options['estimatedExecDuration'] = floatval($item->estimatedExecDuration);
    }

    if(property_exists($item, 'status')) {
      $options['status'] = intval($item->status);
    }


    if(property_exists($item, 'importLogic')) {
      $options['importLogic'] = $item->importLogic;
    }

    $ret = $this->create($item->testSuiteID,$item->name,$item->summary,$item->preconditions,
                         $item->steps,$item->authorID,'',$item->order,self::AUTOMATIC_ID,
                         $item->executionType,$item->importance,$options);
    return $ret;
  }

  /**
   * create a test case
   *
   */
  function create($parent_id,$name,$summary,$preconditions,$steps,$author_id,
                  $keywords_id='',$tc_order=self::DEFAULT_ORDER,$id=self::AUTOMATIC_ID,
                  $execution_type = TESTCASE_EXECUTION_TYPE_MANUAL,
                  $importance=2,$options=null)
  {
    $status_ok = 1;

    $my['options'] = array( 'check_duplicate_name' => self::DONT_CHECK_DUPLICATE_NAME,
                            'action_on_duplicate_name' => 'generate_new',
                            'estimatedExecDuration' => null,'status' => null,'active' => null,'is_open' => null,
                            'importLogic' => null);

    $my['options'] = array_merge($my['options'], (array)$options);

    if( trim($summary) != '' ) {
      if( strpos($summary,self::NAME_PHOPEN) !== FALSE && strpos($summary,self::NAME_PHCLOSE) !== FALSE ) {
        $name = $this->buildTCName($name,$summary);
      }
    }

    if( trim($preconditions) != '' ) {
      if( strpos($preconditions,self::NAME_PHOPEN) !== FALSE && 
          strpos($preconditions,self::NAME_PHCLOSE) !== FALSE ) {
        $name = $this->buildTCName($name,$preconditions);
      }
    }

    $ret = $this->create_tcase_only($parent_id,$name,$tc_order,$id,$my['options']);


    $tcase_id = $ret['id'];
    $ix = new stdClass();

    if($ret["status_ok"]) {
      $ix->version = 1;
      if(isset($ret['version_number']) && $ret['version_number'] < 0) {
        // We are in the special situation we are only creating a new version,
        // useful when importing test cases. Need to get last version number.
        // I do not use create_new_version() because it does a copy ot last version
        // and do not allow to set new values in different fields while doing this operation.
        $last_version_info = $this->get_last_version_info($ret['id'],array('output' => 'minimun'));

        $ix->version = $last_version_info['version']+1;
        $ret['msg'] = sprintf($ret['msg'],$ix->version);
        $ret['version_number'] = $ix->version;
      }

      // Multiple Test Case Steps Feature
      $version_number = $ret['version_number'];

      $ix->id = $tcase_id;
      $ix->externalID = $ret['external_id'];
      $ix->summary = $summary;
      $ix->preconditions = $preconditions;
      $ix->steps = $steps;
      $ix->authorID = $author_id;
      $ix->executionType = $execution_type;
      $ix->importance = $importance;
      $ix->status = $my['options']['status'];
      $ix->active = $my['options']['active'];
      $ix->is_open = $my['options']['is_open'];
      $ix->estimatedExecDuration = $my['options']['estimatedExecDuration'];


      $op = $this->createVersion($ix);
      if(trim($keywords_id) != "") {
        $a_keywords = explode(",",$keywords_id);
        $auditContext = array('on' => self::AUDIT_ON, 
                              'version' => $version_number);
        $this->addKeywords($tcase_id,$op['id'],$a_keywords,$auditContext);
      }

      if($ret['update_name']) {
        $sql = " UPDATE {$this->tables['nodes_hierarchy']} SET name='" .
               $this->db->prepare_string($name) . "' WHERE id= " . intval($ret['id']);
        $this->db->exec_query($sql);
      }

      $ret['msg'] = $op['status_ok'] ? $ret['msg'] : $op['msg'];
      $ret['tcversion_id'] = $op['status_ok'] ? $op['id'] : -1;

      $ctx = array('test_suite_id' => $parent_id,'id' => $id,'name' => $name,
                   'summary' => $summary,'preconditions' => $preconditions,
                   'steps' => $steps,'author_id' => $author_id,
                   'keywords_id' => $keywords_id,
                   'order' => $tc_order, 'exec_type' => $execution_type,
                   'importance' => $importance,'options' => $options);
      event_signal('EVENT_TEST_CASE_CREATE', $ctx);
    }
    return $ret;
  }

  /*
    [$check_duplicate_name]
    [$action_on_duplicate_name]
    [$order]

    [$id]
         0 -> the id will be assigned by dbms
         x -> this will be the id
              Warning: no check is done before insert => can got error.

  return:
         $ret['id']
         $ret['external_id']
         $ret['status_ok']
         $ret['msg'] = 'ok';
         $ret['new_name']

  rev:

  */
  function create_tcase_only($parent_id,$name,$order=self::DEFAULT_ORDER,$id=self::AUTOMATIC_ID,
                             $options=null)
  {
    $dummy = config_get('field_size');
    $name_max_len = $dummy->testcase_name;
    $name = trim($name);
    $originalNameLen = tlStringLen($name);

    $getOptions = array();
    $ret = array('id' => -1,'external_id' => 0, 'status_ok' => 1,'msg' => 'ok',
                 'new_name' => '', 'version_number' => 1, 'has_duplicate' => false,
                 'external_id_already_exists' => false, 'update_name' => false);

    $my['options'] = array('check_duplicate_name' => self::DONT_CHECK_DUPLICATE_NAME,
                           'action_on_duplicate_name' => 'generate_new',
                           'external_id' => null, 'importLogic' => null);

    $my['options'] = array_merge($my['options'], (array)$options);

    $doCreate=true;
    $forceGenerateExternalID = false;

    $algo_cfg = config_get('testcase_cfg')->duplicated_name_algorithm;
    $getDupOptions['check_criteria'] = ($algo_cfg->type == 'counterSuffix') ? 'like' : '=';
    $getDupOptions['access_key'] = ($algo_cfg->type == 'counterSuffix') ? 'name' : 'id';



    // If external ID has been provided, check if exists.
    // If answer is yes, then
    // 1. collect current info
    // 2. if $my['options']['check_duplicate_name'] is create new version
    //    change to BLOCK
    //
    if( !is_null($my['options']['importLogic']) )
    {
      $doQuickReturn = false;
      switch($my['options']['importLogic']['hitCriteria'])
      {
        case 'externalID':
          if( ($sf = intval($my['options']['external_id'])) > 0 )
          {
            // check if already exists a test case with this external id
            $info = $this->get_by_external($sf, $parent_id);
            if( !is_null($info))
            {
              if( count($info) > 1)
              {
                // abort
                throw new Exception("More than one test case with same external ID");
              }

              $doQuickReturn = true;
              $ret['id'] = key($info);
              $ret['external_id'] = $sf;
              $ret['version_number'] = -1;
              $ret['external_id_already_exists'] = true;
            }
          }

          switch($my['options']['importLogic']['actionOnHit'])
          {
            case 'create_new_version':
              if($doQuickReturn)
              {
                // I this situation we will need to also update test case name, if user
                // has provided one on import file.
                // Then we need to check that new name will not conflict with an existing one
                $doCreate = false;
                if( strcmp($info[key($info)]['name'],$name) != 0)
                {
                  $itemSet = $this->getDuplicatesByName($name,$parent_id,$getDupOptions);
                  if( is_null($itemSet) )
                  {
                    $ret['name'] = $name;
                    $ret['update_name'] = true;
                  }
                }
                return $ret;
              }
            break;

            case 'generate_new':
              // on GUI => create a new test case with a different title
              // IMPORTANT:
              // if name provided on import file does not hit an existent one
              // then I'm going to use it, instead of generating a NEW NAME
              $forceGenerateExternalID = true;
            break;
          }
        break;
      }
    }


    if ($my['options']['check_duplicate_name'])
    {
      $itemSet = $this->getDuplicatesByName($name,$parent_id,$getDupOptions);

      if( !is_null($itemSet) && ($siblingQty=count($itemSet)) > 0 )
      {
        $ret['has_duplicate'] = true;

        switch($my['options']['action_on_duplicate_name'])
        {
            case 'block':
              $doCreate=false;
              $ret['status_ok'] = 0;
              $ret['msg'] = sprintf(lang_get('testcase_name_already_exists'),$name);
            break;

            case 'generate_new':
              $doCreate=true;

              // TICKET 5159: importing duplicate test suites
              // Need to force use of generated External ID
              // (this seems the best alternative)
              $my['options']['external_id'] = null;

              switch($algo_cfg->type)
              {
                case 'stringPrefix':
                  $doIt = true;
                  while($doIt)
                  {
                    if( $doIt = !is_null($itemSet) )
                    {
                      $prefix = strftime($algo_cfg->text,time());
                      $target = $prefix . " " . $name ;
                      $final_len = strlen($target);
                      if( $final_len > $name_max_len)
                      {
                        $target = substr($target,0,$name_max_len);
                      }

                      // Check new generated name
                      $itemSet = $this->getDuplicatesByName($target,$parent_id,$getDupOptions);
                    }
                  }
                  $name = $target;
                break;

                case 'counterSuffix':
                  $mask =  !is_null($algo_cfg->text) ? $algo_cfg->text : '#%s';
                  $nameSet = array_flip(array_keys($itemSet));

                  // 20110109 - franciscom
                  // does not understand why I've choosen time ago
                  // to increment $siblingQty before using it
                  // This way if TC X exists on target parent
                  // I will create TC X [2] insteand of TC X [1]
                  // Anyway right now I will not change.
                  $target = $name . ($suffix = sprintf($mask,++$siblingQty));
                  $final_len = strlen($target);
                  if( $final_len > $name_max_len)
                  {
                    $target = substr($target,strlen($suffix),$name_max_len);
                  }

                  // Need to recheck if new generated name does not crash with existent name
                  // why? Suppose you have created:
                  // TC [1]
                  // TC [2]
                  // TC [3]
                  // Then you delete TC [2].
                  // When I got siblings  il will got 2 siblings, if I create new progressive using next,
                  // it will be 3 => I will get duplicated name.
                  while( isset($nameSet[$target]) )
                  {
                    $target = $name . ($suffix = sprintf($mask,++$siblingQty));
                    $final_len = strlen($target);
                    if( $final_len > $name_max_len)
                    {
                      $target = substr($target,strlen($suffix),$name_max_len);
                    }
                  }
                  $name = $target;
                break;
              }

              $ret['status_ok'] = 1;
              $ret['new_name'] = $name;
              $ret['msg'] = sprintf(lang_get('created_with_title'),$name);
            break;

            case 'create_new_version':
              $doCreate=false;

              // If we found more that one with same name and same parent,
              // will take the first one.
              $xx = current($itemSet);
              $ret['id'] = $xx['id'];
              $ret['external_id']=$xx['tc_external_id'];
              $ret['status_ok'] = 1;
              $ret['new_name'] = $name;
              $ret['version_number'] = -1;
              $ret['msg'] = lang_get('create_new_version');
            break;

            default:
            break;
        }
      }
    }

    // 20120822 - think we have potencial issue, because we never check if
    // duplicated EXTERNAL ID exists.
    // Right now there is no time to try a fix
    if( $ret['status_ok'] && $doCreate)
    {

      $safeLenName = tlSubStr($name, 0, $name_max_len);

      // Get tproject id
      $path2root = $this->tree_manager->get_path($parent_id);
      $tproject_id = $path2root[0]['parent_id'];

      $tcase_id = $this->tree_manager->new_node($parent_id,$this->my_node_type,$safeLenName,$order,$id);
      $ret['id'] = $tcase_id;

      $generateExtID = false;
      if( $forceGenerateExternalID || is_null($my['options']['external_id']) )
      {
        $generateExtID = true;
      }
      else
      {
        // this need more work and checks (20140209)
        $sf = intval($my['options']['external_id']);
        if( is_null($this->get_by_external($sf, $parent_id)) )
        {
          $ret['external_id'] = $sf;

          // CRITIC: setTestCaseCounter() will update only if new provided value > current value
          $this->tproject_mgr->setTestCaseCounter($tproject_id,$ret['external_id']);

        }
        else
        {
          $generateExtID = true;
        }

      }
      if( $generateExtID )
      {
        $ret['external_id'] = $this->tproject_mgr->generateTestCaseNumber($tproject_id);
      }

      if( !$ret['has_duplicate'] && ($originalNameLen > $name_max_len) )
      {
        $ret['new_name'] = $safeLenName;
        $ret['msg'] = sprintf(lang_get('testcase_name_length_exceeded'),$originalNameLen,$name_max_len);
      }
    }

    return $ret;
  }


  /**
   *  trying to solve <body id="cke_pastebin" issues
   *
   */
 private function createVersion($item) {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $tcase_version_id = $this->tree_manager->new_node($item->id,
                          $this->node_types_descr_id['testcase_version']);

    $this->CKEditorCopyAndPasteCleanUp($item,array('summary','preconditions')); 

    $sql = "/* $debugMsg */ INSERT INTO {$this->tables['tcversions']} " .
           " (id,tc_external_id,version,summary,preconditions," .
           "  author_id,creation_ts,execution_type,importance ";

    $sqlValues = " VALUES({$tcase_version_id},{$item->externalID},{$item->version},'" .
                 $this->db->prepare_string($item->summary) . "','" .
                 $this->db->prepare_string($item->preconditions) . "'," .
                 $this->db->prepare_int($item->authorID) . "," . $this->db->db_now() .
                 ", {$item->executionType},{$item->importance} ";


    if( !is_null($item->status) ) {
      $wf = intval($item->status);
      $sql .= ',status';
      $sqlValues .= ",{$wf}";
    }

    if( !is_null($item->estimatedExecDuration) ) {
      $v = trim($item->estimatedExecDuration);
      if($v != '') {
        $sql .= ", estimated_exec_duration";
        $sqlValues .= "," . floatval($v);
      }
    }

    if( property_exists($item,'active') && !is_null($item->active) ) {
      $v = intval($item->active) > 0 ? 1 : 0;
      $sql .= ", active";
      $sqlValues .= "," . $v;
    }
	  
    if( property_exists($item,'is_open') && !is_null($item->is_open) ) {
      $v = intval($item->is_open) > 0 ? 1 : 0;
      $sql .= ", is_open";
      $sqlValues .= "," . $v;
    }

    $sql .= " )" . $sqlValues . " )";



    $result = $this->db->exec_query($sql);

    $ret['msg']='ok';
    $ret['id']=$tcase_version_id;
    $ret['status_ok']=1;

    if ($result && ( !is_null($item->steps) && is_array($item->steps) ) ) {
      $steps2create = count($item->steps);
      $op['status_ok'] = 1;

      // need to this to manage call to this method for REST API.
      $stepIsObject =  is_object($item->steps[0]);
      for($jdx=0 ; ($jdx < $steps2create && $op['status_ok']); $jdx++)
      {
        if($stepIsObject)
        {
          $item->steps[$jdx] = (array)$item->steps[$jdx];
        }

        $op = $this->create_step($tcase_version_id,
                                 $item->steps[$jdx]['step_number'],
                                 $item->steps[$jdx]['actions'],
                                 $item->steps[$jdx]['expected_results'],
                                 $item->steps[$jdx]['execution_type']);
      }
    }

    if (!$result)
    {
      $ret['msg'] = $this->db->error_msg();
      $ret['status_ok']=0;
      $ret['id']=-1;
    }

    return $ret;
  }


  /*
    function: getDuplicatesByname

    args: $name
          $parent_id

    returns: hash
  */
  function getDuplicatesByName($name, $parent_id, $options=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $my['options'] = array( 'check_criteria' => '=', 'access_key' => 'id', 'id2exclude' => null);
    $my['options'] = array_merge($my['options'], (array)$options);

    $target = $this->db->prepare_string($name);
    switch($my['options']['check_criteria'])
    {
      case '=':
      default:
        $check_criteria = " AND NHA.name = '{$target}' ";
      break;

      case 'like':
        $check_criteria = " AND NHA.name LIKE '{$target}%' ";
      break;

    }

    $sql = " SELECT DISTINCT NHA.id,NHA.name,TCV.tc_external_id" .
           " FROM {$this->tables['nodes_hierarchy']} NHA, " .
           " {$this->tables['nodes_hierarchy']} NHB, {$this->tables['tcversions']} TCV  " .
           " WHERE NHA.node_type_id = {$this->my_node_type} " .
           " AND NHB.parent_id=NHA.id " .
           " AND TCV.id=NHB.id " .
           " AND NHB.node_type_id = {$this->node_types_descr_id['testcase_version']} " .
           " AND NHA.parent_id=" . $this->db->prepare_int($parent_id) . " {$check_criteria}";

    if( !is_null($my['options']['id2exclude']) )
    {
      $sql .= " AND NHA.id <> " . intval($my['options']['id2exclude']);
    }

    $rs = $this->db->fetchRowsIntoMap($sql,$my['options']['access_key']);
    if( is_null($rs) || count($rs) == 0 )
    {
      $rs=null;
    }
    return $rs;
  }




  /*
    function: get_by_name

    args: $name
          [$tsuite_name]: name of parent test suite
          [$tproject_name]

    returns: hash

    @internal revisions
    20100831 - franciscom - BUGID 3729

  */
  function get_by_name($name, $tsuite_name = '', $tproject_name = '')
  {

    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $recordset = null;
    $filters_on = array('tsuite_name' => false, 'tproject_name' => false);
    $field_size = config_get('field_size');
    $tsuite_name = tlSubStr(trim($tsuite_name),0, $field_size->testsuite_name);
    $tproject_name = tlSubStr(trim($tproject_name),0,$field_size->testproject_name);
    $name = tlSubStr(trim($name), 0, $field_size->testcase_name);

    $sql = "/* $debugMsg */ " .
           " SELECT DISTINCT NH_TCASE.id,NH_TCASE.name,NH_TCASE_PARENT.id AS parent_id," .
           " NH_TCASE_PARENT.name AS tsuite_name, TCV.tc_external_id " .
           " FROM {$this->tables['nodes_hierarchy']} NH_TCASE, " .
           " {$this->tables['nodes_hierarchy']} NH_TCASE_PARENT, " .
           " {$this->tables['nodes_hierarchy']} NH_TCVERSIONS," .
           " {$this->tables['tcversions']}  TCV  " .
           " WHERE NH_TCASE.node_type_id = {$this->my_node_type} " .
           " AND NH_TCASE.name = '{$this->db->prepare_string($name)}' " .
           " AND TCV.id=NH_TCVERSIONS.id " .
           " AND NH_TCVERSIONS.parent_id=NH_TCASE.id " .
           " AND NH_TCASE_PARENT.id=NH_TCASE.parent_id ";

    if($tsuite_name != "")
    {
      $sql .= " AND NH_TCASE_PARENT.name = '{$this->db->prepare_string($tsuite_name)}' " .
              " AND NH_TCASE_PARENT.node_type_id = {$this->node_types_descr_id['testsuite']} ";
    }
    $recordset = $this->db->get_recordset($sql);
    if(count($recordset) && $tproject_name != "")
    {
      list($tproject_info)=$this->tproject_mgr->get_by_name($tproject_name);
      foreach($recordset as $idx => $tcase_info)
      {
        if( $this->get_testproject($tcase_info['id']) != $tproject_info['id'] )
        {
          unset($recordset[$idx]);
        }
      }
    }
    return $recordset;
  }


  /*
  get array of info for every test case
  without any kind of filter.
  Every array element contains an assoc array with testcase info

  */
  function get_all() {
    $sql = " SELECT nodes_hierarchy.name, nodes_hierarchy.id
             FROM  {$this->tables['nodes_hierarchy']} nodes_hierarchy
             WHERE nodes_hierarchy.node_type_id={$my_node_type}";
    $recordset = $this->db->get_recordset($sql);

    return $recordset;
  }


  /**
   * Show Test Case
   *
   *
   */
  function show(&$smarty,$guiObj,$identity,$grants,$opt=null) {
    static $cfg;
    static $reqMgr;

    if(!$cfg) {
      $cfg = config_get('spec_cfg');
      $reqMgr = new requirement_mgr($this->db);
    }

    $status_ok = ($identity->id > 0);
    if( !$status_ok ) {
      throw new Exception(__METHOD__ . ' EXCEPTION: Test Case ID is invalid ( <= 0)' );
    }
    
    $my = array('opt' => array('getAttachments' => false));
    $my['opt'] = array_merge($my['opt'],(array)$opt);

    $id = $identity->id;
    $idSet = (array)$id;

    $idCard = new stdClass();
    $idCard->tcase_id = intval($id);
    $idCard->tproject_id = intval($identity->tproject_id);

    $idCard->tcversion_id = isset($identity->version_id) ? $identity->version_id : self::ALL_VERSIONS;

    $getVersionID = $idCard->tcversion_id;

    $gui = $this->initShowGui($guiObj,$grants,$idCard);

    $userIDSet = array();

    if($status_ok && sizeof($idSet)) {

      $cfPlaces = $this->buildCFLocationMap();

      $gui->linked_versions = null;

      $gopt = array('renderGhost' => true, 'withGhostString' => true,
                    'renderImageInline' => true, 'renderVariables' => true,
                    'renderSpecialKW' => true,
                    'caller' => 'show()');

      $cfx = 0;
      $gui->otherVersionsKeywords = array();

      foreach($idSet as $key => $tc_id) {

        $gui->fileUploadURL = $_SESSION['basehref'];
        $gui->delAttachmentURL = $_SESSION['basehref'];

        // IMPORTANT NOTICE
        // Deep Analysis is need to understand if there is an use case
        // where this method really receive an array of test case ID.
        // 
        // using an specific value for test case version id has sense 
        // only when we are working on ONE SPECIFIC Test Case.
        // 
        // if we are working on a set of test cases, because this method 
        // does not manage in input couple of (test case, versio id),
        // the only chance is to get ALL VERSIONS
        //
        if( !$tcvSet = $this->get_by_id($tc_id,$getVersionID,null,$gopt) ) {
          continue;
        }

        if($cfg->show_tplan_usage) {
          $gui->linked_versions[] = $this->get_linked_versions($tc_id);
        }

        // Position 0 is latest active version
        $tcvSet[0]['tc_external_id'] = $gui->tcasePrefix . $tcvSet[0]['tc_external_id'];
        $tcvSet[0]['ghost'] = sprintf(self::GHOSTMASK,$tcvSet[0]['tc_external_id'],$tcvSet[0]['version']);

        // status quo of execution and links of tc versions
        $gui->status_quo[] = $this->get_versions_status_quo($tc_id);

        // Logic on Current/Latest Test Case Version
        $tc_current = $tcvSet[0];
        $currentVersionID = $tc_current['id'];

        $io = $idCard;
        $io->tcversion_id = $currentVersionID;
        $gui->fileUploadURL .= $this->getFileUploadRelativeURL($io);
        $gui->delAttachmentURL .= $this->getDeleteAttachmentRelativeURL($io);

        $gui->tc_current_version[] = array($tc_current);

        //  
        // REFACTORING - Following code uses tc_current!!!
        //

        // Get UserID and Updater ID for current Version
        $userIDSet[$tc_current['author_id']] = null;
        $userIDSet[$tc_current['updater_id']] = null;

        $gui->req4current_version = 
          $reqMgr->getGoodForTCVersion($currentVersionID);

        
        $gui->currentVersionKeywords = $this->getKeywords($tc_id,$currentVersionID);

        if( $my['opt']['getAttachments'] ) {
          $gui->attachments[$currentVersionID] = 
            getAttachmentInfosFrom($this,$currentVersionID);
        }

        // get linked testcase scripts
        if($gui->codeTrackerEnabled) {
          $scripts = $this->getScriptsForTestCaseVersion($gui->cts,$currentVersionID);
          if(!is_null($scripts)) {
            $gui->scripts[$currentVersionID] = $scripts;
          }
        }

        if($this->cfg->testcase->relations->enable) {
          $xm = array('tcase_id' => $tc_id, 'tcversion_id' => $currentVersionID);
          $gui->relationSet[] = $this->getTCVersionRelations($xm);
        }

        $cfCtx = array('scope' => 'design','tproject_id' => $gui->tproject_id,'link_id' => $tc_current['id']);
        foreach($cfPlaces as $cfpKey => $cfpFilter) {
          $gui->cf_current_version[$cfx][$cfpKey] =
            $this->htmlTableOfCFValues($tc_id,$cfCtx,$cfpFilter);
        }

        // Other versions (if exists)
        if(count($tcvSet) > 1) {
          $gui->testcase_other_versions[] = array_slice($tcvSet,1);
          $target_idx = count($gui->testcase_other_versions) - 1;
          $loop2do = count($gui->testcase_other_versions[$target_idx]);

          $cfCtx = array('scope' => 'design','tproject_id' => $gui->tproject_id);

          $ref = &$gui->testcase_other_versions[$target_idx];
          for($qdx=0; $qdx < $loop2do; $qdx++) {
            
            $ref[$qdx]['ghost'] = sprintf(self::GHOSTMASK,$tcvSet[0]['tc_external_id'],
                                          $ref[$qdx]['version']);

            $cfCtx['link_id'] = $gui->testcase_other_versions[$target_idx][$qdx]['id'];
            foreach($cfPlaces as $locKey => $locFilter) {
              $gui->cf_other_versions[$cfx][$qdx][$locKey] =
                  $this->htmlTableOfCFValues($tc_id,$cfCtx,$locFilter);
            }
          }
        }
        else {
          $gui->testcase_other_versions[] = null;
          $gui->otherVersionsRelations[] = null;
          $gui->cf_other_versions[$cfx]=null;
        }

        $cfx++;

        if ($gui->testcase_other_versions[0]) {
          
          // Get author and updater id for each version
          foreach($gui->testcase_other_versions[0] as $key => $version) {

            $userIDSet[$version['author_id']] = null;
            $userIDSet[$version['updater_id']] = null;

            if($this->cfg->testcase->relations->enable) {
              $xm = array('tcase_id' => $version['testcase_id'], 
                          'tcversion_id' => $version['id'],
                          'other' => 'other');
              $gui->otherVersionsRelations[] = $this->getTCVersionRelations($xm);
            }

            // get linked testcase scripts
            if($gui->codeTrackerEnabled) {
              $scripts = $this->getScriptsForTestCaseVersion($gui->cts,$version['id']);
              if(!is_null($scripts)) {
                $gui->scripts[$version['id']] = $scripts;
              }
            }

            if( $my['opt']['getAttachments'] ) {
              $gui->attachments[$version['id']] = 
                getAttachmentInfosFrom($this,$version['id']);
            }

            // Requirements
            $gui->req4OtherVersions[] = 
              $reqMgr->getGoodForTCVersion($version['id']);
            

            $gui->otherVersionsKeywords[] = 
              $this->getKeywords($version['testcase_id'],$version['id']);
          }
        } // Other versions exist
      }
    }

    $gui->relations = $gui->relationSet;
    $gui->relation_domain = '';
    if($this->cfg->testcase->relations->enable) {
      $gui->relation_domain = $this->getRelationTypeDomainForHTMLSelect();
    }

    // Removing duplicate and NULL id's
    unset($userIDSet['']);
    $gui->users = tlUser::getByIDs($this->db,array_keys($userIDSet));
    $gui->cf = null;

    $this->initShowGuiActions($gui);
    $tplCfg = templateConfiguration('tcView');

    $smarty->assign('gui',$gui);
    $smarty->display($tplCfg->template_dir . $tplCfg->default_template);
  }



  /**
   * update test case specification
   *
   * @param integer $id Test case unique identifier (node_hierarchy table)
   * @param integer $tcversion_id Test Case Version unique ID (node_hierarchy table)
   * @param string $name name/title
   * @param string $summary
   * @param string $preconditions
   * @param array $steps steps + expected results
   * @param integer $user_id who is doing the update
   * @param string $keywords_id optional list of keyword id to be linked to test case
   *         this list will override previous keyword links (delete + insert).
   *
   * @param integer $tc_order optional order inside parent test suite
   * @param integer $execution_type optional
   * @param integer $importance optional
   *
   *
   *
   */
  function update($id,$tcversion_id,$name,$summary,$preconditions,$steps,
                  $user_id,$keywords_id='',$tc_order=self::DEFAULT_ORDER,
                  $execution_type=TESTCASE_EXECUTION_TYPE_MANUAL,$importance=2,
                  $attr=null,$opt=null)
  {
    $ret['status_ok'] = 1;
    $ret['msg'] = '';
    $ret['reason'] = '';

    $my['opt'] = array('blockIfExecuted' => false);
    $my['opt'] = array_merge($my['opt'],(array)$opt);


    $attrib = array('status' => null, 'is_open' => null, 'active' => null, 'estimatedExecDuration' => null);
    $attrib = array_merge($attrib,(array)$attr);


    tLog("TC UPDATE ID=($id): exec_type=$execution_type importance=$importance");

    if( trim($summary) != '' ) {
      if( strpos($summary,self::NAME_PHOPEN) !== FALSE && strpos($summary,self::NAME_PHCLOSE) !== FALSE ) {
        $name = $this->buildTCName($name,$summary);
      }
    }
    
    // Check if new name will be create a duplicate testcase under same parent
    if( ($checkDuplicates = config_get('check_names_for_duplicates')) )
    {
      // get my parent
      $mi = $this->tree_manager->get_node_hierarchy_info($id);
      $itemSet = $this->getDuplicatesByName($name,$mi['parent_id'],array('id2exclude' => $id));

      if(!is_null($itemSet))
      {
        $ret['status_ok'] = false;
        $ret['msg'] = sprintf(lang_get('name_already_exists'),$name);
        $ret['reason'] = 'already_exists';
        $ret['hit_on'] = current($itemSet);
      }


      if( $ret['status_ok'] == false )
      {
        // get more info for feedback

      }
    }

    if($ret['status_ok']) {
      if($my['opt']['blockIfExecuted']) {
        // When tcversion is updated on test plan after an executio exists
        // execution tcversion_number keeps the version of test case executed
        // will EX.tcversion_id is updated with id requested by user.
        // That's why when importing we need to check HUMAN READEABLE version numbers.
        $sql = " SELECT EX.id, EX.tcversion_number,TCV.version " .  
               " FROM {$this->tables['executions']} EX " .
               " JOIN {$this->tables['tcversions']} TCV " .
               " ON TCV.id = EX.tcversion_id " .
               " WHERE tcversion_id=" . $this->db->prepare_int($tcversion_id);

        $rs = $this->db->get_recordset($sql);
        if(!is_null($rs))
        {
          foreach($rs as $rwx)
          {
            if( $rwx['tcversion_number'] == $rwx['version'] )
            {
              $ret['status_ok'] = false;
              $ret['msg'] = lang_get('block_ltcv_hasbeenexecuted');
              $ret['reason'] = 'blockIfExecuted';
              return $ret;
            }
          }  
        }
      }

      $sql=array();
      $sql[] = " UPDATE {$this->tables['nodes_hierarchy']} SET name='" .
               $this->db->prepare_string($name) . "' WHERE id= {$id}";


      $k2e = array('summary','preconditions');
      $item = new stdClass();
      $item->summary = $summary;
      $item->preconditions = $preconditions;
      $this->CKEditorCopyAndPasteCleanUp($item,$k2e); 
      
      $dummy = " UPDATE {$this->tables['tcversions']} " .
               " SET summary='" . 
                 $this->db->prepare_string($item->summary) . "'," .
               " updater_id=" . $this->db->prepare_int($user_id) . ", " .
               " modification_ts = " . $this->db->db_now() . "," .
               " execution_type=" . $this->db->prepare_int($execution_type) . ", " .
               " importance=" . $this->db->prepare_int($importance) . "," .
               " preconditions='" . 
                 $this->db->prepare_string($item->preconditions) . "' ";


      if( !is_null($attrib['status']) )
      {
        $dummy .= ", status=" . intval($attrib['status']);
      }

	    if( !is_null($attrib['is_open']) )    
      {
        $dummy .= ", is_open=" . intval($attrib['is_open']); 
      }
	  
	    if( !is_null($attrib['active']) )    
      {
        $dummy .= ", active=" . intval($attrib['active']); 
      }

      if( !is_null($attrib['estimatedExecDuration']) )
      {
        $dummy .= ", estimated_exec_duration=";
        $v = trim($attrib['estimatedExecDuration']);

        $dummy .= ($v == '') ? "NULL" : floatval($v);
      }

      $dummy .= " WHERE id = " . $this->db->prepare_int($tcversion_id);
      $sql[] = $dummy;


      foreach($sql as $stm)
      {
        $result = $this->db->exec_query($stm);
        if( !$result )
        {
          $ret['status_ok'] = 0;
          $ret['msg'] = $this->db->error_msg;
          break;
        }
      }

      if( $ret['status_ok'] && !is_null($steps) )
      {
        $this->update_tcversion_steps($tcversion_id,$steps);
      }

      if( $ret['status_ok'] )
      {
        
        $idCard = array('id' => $id, 'version_id' => $tcversion_id,
                        'version' => $this->getVersionNumber($tcversion_id));

        $this->updateKeywordAssignment($idCard,$keywords_id);
      }

      $ctx = array('id' => $id,'version_id' => $tcversion_id,'name' => $name,
                   'summary' => $summary,'preconditions' => $preconditions,
                   'steps' => $steps,'user_id' => $user_id,
                   'keywords_id' => $keywords_id,'order' => $tc_order,
                   'exec_type' => $execution_type, 'importance' => $importance,
                   'attr' => $attr,'options' => $opt);
      event_signal('EVENT_TEST_CASE_UPDATE', $ctx);
    }

    return $ret;
  }


  /**
   * used when updating a test case
   *
   */
  private function updateKeywordAssignment($idCard,$keywords_id) {
    // To avoid false loggings, check is delete is needed
    $id = intval($idCard['id']);
    $version_id = intval($idCard['version_id']);
    $version = intval($idCard['version']);

    $items = array();
    $items['stored'] = $this->get_keywords_map($id,$version_id);
    if (is_null($items['stored'])) {
      $items['stored'] = array();
    }

    $items['requested'] = array();
    if(trim($keywords_id) != "")
    {
      $a_keywords = explode(",",trim($keywords_id));
      $sql = " SELECT id,keyword " .
             " FROM {$this->tables['keywords']} " .
             " WHERE id IN (" . implode(',',$a_keywords) . ")";

      $items['requested'] = $this->db->fetchColumnsIntoMap($sql,'id','keyword');
    }

    $items['common'] = array_intersect_assoc($items['stored'],$items['requested']);
    $items['new'] = array_diff_assoc($items['requested'],$items['common']);
    $items['todelete'] = array_diff_assoc($items['stored'],$items['common']);

    $auditContext = array('on' => self::AUDIT_ON, 'version' => $version);
    
    if(!is_null($items['todelete']) && count($items['todelete'])) {
      $this->deleteKeywords($id,$version_id,array_keys($items['todelete']),$auditContext);
    }

    if(!is_null($items['new']) && count($items['new']))
    {
      $this->addKeywords($id,$version_id,array_keys($items['new']),$auditContext);
    }
  }


  /*
    function: logKeywordChanges

    args:

    returns:

  */
  function logKeywordChanges($old,$new)
  {

     // try to understand the really new

  }







  /*
    function: check_link_and_exec_status
              Fore every version of testcase (id), do following checks:

              1. testcase is linked to one of more test plans ?
              2. if anwser is yes then,check if has been executed => has records on executions table

    args : id: testcase id

    returns: string with following values:
             no_links: testcase is not linked to any testplan
             linked_but_not_executed: testcase is linked at least to a testplan
                                      but has not been executed.

             linked_and_executed: testcase is linked at least to a testplan and
                                  has been executed => has records on executions table.


  */
  function check_link_and_exec_status($id)
  {
    $status = 'no_links';

    // get linked versions
    // ATTENTION TO PLATFORMS
    $linked_tcversions = $this->get_linked_versions($id);
    $has_links_to_testplans = is_null($linked_tcversions) ? 0 : 1;

    if($has_links_to_testplans)
    {
      // check if executed
      $linked_not_exec = $this->get_linked_versions($id,array('exec_status' => 'NOT_EXECUTED'));

      $status='linked_and_executed';
      if(count($linked_tcversions) == count($linked_not_exec))
      {
        $status = 'linked_but_not_executed';
      }
    }
    return $status;
  }


  /**
   *
   */
  function delete($id,$version_id = self::ALL_VERSIONS) {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $children=null;
    $do_it=true;

    // I'm trying to speedup the next deletes
    $sql = "/* $debugMsg */ " .
           " SELECT NH_TCV.id AS tcversion_id, NH_TCSTEPS.id AS step_id " .
           " FROM {$this->tables['nodes_hierarchy']} NH_TCV " .
           " LEFT OUTER JOIN {$this->tables['nodes_hierarchy']} NH_TCSTEPS " .
           " ON NH_TCSTEPS.parent_id = NH_TCV.id ";

    $parent = (array)($id);
    $sql .= " WHERE NH_TCV.parent_id IN (" .implode(',',$parent) . ") ";
    if($version_id != self::ALL_VERSIONS) {
      $sql .= " AND NH_TCV.id = {$version_id}";
    }

    $children_rs = $this->db->get_recordset($sql);
    $do_it = !is_null($children_rs);
    if($do_it) {
      foreach($children_rs as $value) {
        $children['tcversion'][]=$value['tcversion_id'];
        $children['step'][]=$value['step_id'];
      }
      $this->_execution_delete($id,$version_id,$children);
      $this->deleteAllTestCaseRelations($id);
      $this->_blind_delete($id,$version_id,$children);
    }

    $ctx = array('id' => $id);
    event_signal('EVENT_TEST_CASE_DELETE', $ctx);

    return 1;
  }

  /*
    function: get_linked_versions
              For a test case get information about versions linked to testplans.
              Filters can be applied on:
                                        execution status
                                        active status

    args : id: testcase id
           [filters]
            [exec_status]: default: ALL, range: ALL,EXECUTED,NOT_EXECUTED
            [active_status]: default: ALL, range: ALL,ACTIVE,INACTIVE
            [tplan_id]
            [platform_id]

           [options]
            [output] 'full', 'nosteps', 'simple' (no info about steps)

      returns: map.
             key: version id
             value: map with following structure:
                    key: testplan id
                    value: map with following structure:

                    testcase_id
                    tcversion_id
                    id -> tcversion_id (node id)
                    version
                    summary
                    importance
                    author_id
                    creation_ts
                    updater_id
                    modification_ts
                    active
                    is_open
                    testplan_id
                    tplan_name
  */
  function get_linked_versions($id,$filters=null,$options=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $my['filters'] = array( 'exec_status' => "ALL", 'active_status' => 'ALL',
                            'tplan_id' => null, 'platform_id' => null);
    $my['filters'] = array_merge($my['filters'], (array)$filters);

    // 'output' => 'full', 'nosteps', 'simple' (no info about steps)
    //
    $my['options'] = array('output' => "full");
    $my['options'] = array_merge($my['options'], (array)$options);


    $exec_status = strtoupper($my['filters']['exec_status']);
    $active_status = strtoupper($my['filters']['active_status']);
    $tplan_id = $my['filters']['tplan_id'];
    $platform_id = $my['filters']['platform_id'];

    $active_filter='';
    if($active_status !='ALL')
    {
      $active_filter=' AND tcversions.active=' . $active_status=='ACTIVE' ? 1 : 0;
    }

    $fields2get = 'tc_external_id,version,status,importance,active, is_open,execution_type,';

    switch($my['options']['output'])
    {
      case 'full':
      case 'nosteps':
      $fields2get .=  'layout,summary,preconditions,tcversions.author_id,tcversions.creation_ts,' .
                      'tcversions.updater_id,tcversions.modification_ts,';
      break;

      case 'simple':
      break;

      case 'feature_id':
        $fields2get .=  'TTC.id AS feature_id,';
      break;

    }

    switch ($exec_status)
    {
      case "ALL":
            $sql = "/* $debugMsg */ " .
               " SELECT NH.parent_id AS testcase_id, TTC.tcversion_id, TTC.testplan_id,  TTC.platform_id," .
               " tcversions.id, {$fields2get} " .
             " NHB.name AS tplan_name " .
             " FROM   {$this->tables['nodes_hierarchy']} NH," .
             " {$this->tables['tcversions']} tcversions," .
             " {$this->tables['testplan_tcversions']} TTC, " .
             " {$this->tables['nodes_hierarchy']} NHB    " .
             " WHERE  TTC.tcversion_id = tcversions.id {$active_filter} " .
             " AND    tcversions.id = NH.id " .
             " AND    NHB.id = TTC.testplan_id " .
             " AND    NH.parent_id = {$id}";

            if(!is_null($tplan_id))
            {
                $sql .= " AND TTC.testplan_id = {$tplan_id} ";
            }

            if(!is_null($platform_id))
            {
                $sql .= " AND TTC.platform_id = {$platform_id} ";
            }

            $recordset = $this->db->fetchMapRowsIntoMap($sql,'tcversion_id','testplan_id',database::CUMULATIVE);

        if( !is_null($recordset) )
        {
          // changes third access key from sequential index to platform_id
          foreach ($recordset as $accessKey => $testplan)
          {
            foreach ($testplan as $tplanKey => $testcases)
            {
              // Use a temporary array to avoid key collisions
              $newArray = array();
              foreach ($testcases as $elemKey => $element)
              {
                $platform_id = $element['platform_id'];
                $newArray[$platform_id] = $element;
              }
              $recordset[$accessKey][$tplanKey] = $newArray;
              }
          }
        }
      break;

      case "EXECUTED":
      case "NOT_EXECUTED":
        $getFilters = array('exec_status' => $exec_status,'active_status' => $active_status,
                            'tplan_id' => $tplan_id, 'platform_id' => $platform_id);
        $recordset=$this->get_exec_status($id,$getFilters);
      break;
    }

    // Multiple Test Case Steps
    if( !is_null($recordset) && ($my['options']['output'] == 'full') ) {
      $version2loop = array_keys($recordset);
      foreach( $version2loop as $accessKey) {
        // no options => will renderd Ghost Steps
        $step_set = $this->get_steps($accessKey);
        $tplan2loop = array_keys($recordset[$accessKey]);
        foreach( $tplan2loop as $tplanKey) {
          $elem2loop = array_keys($recordset[$accessKey][$tplanKey]);
          foreach( $elem2loop as $elemKey) {
            $recordset[$accessKey][$tplanKey][$elemKey]['steps'] = $step_set;
          }
        }

      }
    }

    return $recordset;
  }

  /*
    Delete the following info:
    req_coverage
    risk_assignment
    custom fields
    keywords
    links to test plans
    tcversions
    nodes from hierarchy
    testcase_script_links

  */
  function _blind_delete($id,$version_id=self::ALL_VERSIONS,$children=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $sql = array();

    $destroyTC = false;
    $item_id = $version_id;
    $tcversion_list = $version_id;
    $target_nodes = $version_id;
    if( $version_id == self::ALL_VERSIONS)
    {
      $destroyTC = true;
      $item_id = $id;
      $tcversion_list = implode(',',$children['tcversion']);
      $target_nodes = $children['tcversion'];
    }

    $this->cfield_mgr->remove_all_design_values_from_node($target_nodes);

    $sql[] = "/* $debugMsg */ DELETE FROM {$this->tables['user_assignments']} " .
             " WHERE feature_id in (" .
             " SELECT id FROM {$this->tables['testplan_tcversions']}  " .
             " WHERE tcversion_id IN ({$tcversion_list}))";

    $sql[]="/* $debugMsg */ DELETE FROM {$this->tables['testplan_tcversions']}  " .
           " WHERE tcversion_id IN ({$tcversion_list})";

    // Multiple Test Case Steps Feature
    if( !is_null($children['step']) )
    {
      // remove null elements
      foreach($children['step'] as $key => $value)
      {
        if(is_null($value))
        {
          unset($children['step'][$key]);
        }
      }

      if( count($children['step']) > 0)
      {
        $step_list=trim(implode(',',$children['step']));
        $sql[]="/* $debugMsg */ DELETE FROM {$this->tables['tcsteps']}  " .
               " WHERE id IN ({$step_list})";
      }
    }
    $sql[]="/* $debugMsg */ DELETE FROM {$this->tables['tcversions']}  " .
           " WHERE id IN ({$tcversion_list})";

    $sql[]="/* $debugMsg */ DELETE FROM {$this->tables['testcase_script_links']}  " .
           " WHERE tcversion_id IN ({$tcversion_list})";

    foreach ($sql as $the_stm)
    {
      $result = $this->db->exec_query($the_stm);
    }

    if( !$destroyTC ) {
      $toloop = array( $version_id );
      foreach( $toloop as $nu ) {
        $this->deleteAttachments($nu);
      }
    }

    if($destroyTC)
    {
      // Remove data that is related to Test Case => must be deleted when there is no more trace
      // of test case => when all version are deleted
      $sql = null;
      $sql[]="/* $debugMsg */ DELETE FROM {$this->tables['testcase_keywords']} WHERE testcase_id = {$id}";
      $sql[]="/* $debugMsg */ DELETE FROM {$this->tables['req_coverage']}  WHERE testcase_id = {$id}";

      foreach ($sql as $the_stm)
      {
        $result = $this->db->exec_query($the_stm);
      }

      // 2018
      // $this->deleteAttachments($id);
      if( $version_id == self::ALL_VERSIONS ) {
        $toloop = explode(',',$tcversion_list);
      } 
      foreach( $toloop as $nu ) {
        $this->deleteAttachments($nu);
      }
    }

    // Attention:
    // After addition of test case steps feature, a test case version can be root of
    // a subtree that contains the steps.
    $this->tree_manager->delete_subtree($item_id);
  }


  /*
    Delete the following info:
    bugs
    executions
    cfield_execution_values
  */
  function _execution_delete($id,$version_id=self::ALL_VERSIONS,$children=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $sql = array();

    if( $version_id == self::ALL_VERSIONS )
    {
      $tcversion_list=implode(',',$children['tcversion']);

      $sql[]="/* $debugMsg */ DELETE FROM {$this->tables['execution_bugs']} " .
             " WHERE execution_id IN (SELECT id FROM {$this->tables['executions']} " .
             " WHERE tcversion_id IN ({$tcversion_list}))";

      $sql[]="/* $debugMsg */ DELETE FROM {$this->tables['cfield_execution_values']}  " .
             " WHERE tcversion_id IN ({$tcversion_list})";

      $sql[]="/* $debugMsg */ DELETE FROM {$this->tables['executions']}  " .
             " WHERE tcversion_id IN ({$tcversion_list})";

      }
      else
      {
      $sql[]="/* $debugMsg */  DELETE FROM {$this->tables['execution_bugs']} " .
             " WHERE execution_id IN (SELECT id FROM {$this->tables['executions']} " .
             " WHERE tcversion_id = {$version_id})";

      $sql[]="/* $debugMsg */ DELETE FROM {$this->tables['cfield_execution_values']} " .
             " WHERE tcversion_id = {$version_id}";

      $sql[]="/* $debugMsg */ DELETE FROM {$this->tables['executions']} " .
             " WHERE tcversion_id = {$version_id}";
      }

      foreach ($sql as $the_stm)
      {
          $result = $this->db->exec_query($the_stm);
      }
  }


  /*
    function: formatTestCaseIdentity

    args: id: testcase id
          external_id

    returns: testproject id

  */
  function formatTestCaseIdentity($id,$external_id=null)
  {
    $path2root = $this->tree_manager->get_path($tc_id);
    $tproject_id = $path2root[0]['parent_id'];
    $tcasePrefix = $this->tproject_mgr->getTestCasePrefix($tproject_id);
  }


  /*
    function: getPrefix

    args: id: testcase id
          [$tproject_id]

    returns: array(prefix,testproject id)

  */
  function getPrefix($id, $tproject_id=null)
  {
    $root = $tproject_id;
    if( is_null($root) )
    {
      $path2root=$this->tree_manager->get_path($id);
      $root=$path2root[0]['parent_id'];
    }
    $tcasePrefix = $this->tproject_mgr->getTestCasePrefix($root);
    return array($tcasePrefix,$root);
  }





  /*
    @internal revisions
  */
  function copy_to($id,$parent_id,$user_id,$options=null,$mappings=null) {
    $newTCObj = array('id' => -1, 'status_ok' => 0, 'msg' => 'ok', 'mappings' => null);
    $my['options'] = array('check_duplicate_name' => self::DONT_CHECK_DUPLICATE_NAME,
                           'action_on_duplicate_name' => 'generate_new',
                           'use_this_name' => null,
                           'copy_also' => null, 'preserve_external_id' => false,
                           'renderGhostSteps' => false, 'stepAsGhost' => false);


    // needed when Test Case is copied to a DIFFERENT Test Project,
    // added during Test Project COPY Feature implementation
    $my['mappings']['keywords'] = null;
    $my['mappings']['requirements'] = null;

    $my['mappings'] = array_merge($my['mappings'], (array)$mappings);
    $my['options'] = array_merge($my['options'], (array)$options);


    if( is_null($my['options']['copy_also']) ) {
      $my['options']['copy_also'] = array('keyword_assignments' => true,'requirement_assignments' => true);
    }

    $copyKW = ( isset($my['options']['copy_also']['keyword_assignments']) &&
                $my['options']['copy_also']['keyword_assignments'] );

    $uglyKey = 'requirement_assignments';
    $copyReqLinks = ( isset($my['options']['copy_also'][$uglyKey]) &&
                      $my['options']['copy_also'][$uglyKey]);
    // ==================================================================


    $tcVersionID = $my['options']['stepAsGhost'] ? self::LATEST_VERSION : self::ALL_VERSIONS;
    $tcase_info = $this->get_by_id($id,$tcVersionID);
    if ($tcase_info) {
      $callme = !is_null($my['options']['use_this_name']) ? $my['options']['use_this_name'] : $tcase_info[0]['name'];
      $callme = $this->trim_and_limit($callme);

      $newTCObj = $this->create_tcase_only($parent_id,$callme,$tcase_info[0]['node_order'],self::AUTOMATIC_ID,
                                           $my['options']);
      $ix = new stdClass();
      $ix->authorID = $user_id;
      $ix->status = null;
      $ix->steps = null;

      if($newTCObj['status_ok']) {

        $ret['status_ok']=1;
        $newTCObj['mappings'][$id] = $newTCObj['id'];

        $ix->id = $newTCObj['id'];
        $ix->externalID = $newTCObj['external_id'];
        if( $my['options']['preserve_external_id'] ) {
          $ix->externalID = $tcase_info[0]['tc_external_id'];
        }

        
        foreach($tcase_info as $tcversion) {

          // IMPORTANT NOTICE:
          // In order to implement COPY to another test project, WE CAN NOT ASK
          // to method create_tcversion() to create inside itself THE STEPS.
          // Passing NULL as steps we instruct create_tcversion() TO DO NOT CREATE STEPS

          $ix->executionType = $tcversion['execution_type'];
          $ix->importance = $tcversion['importance'];
          $ix->version = $tcversion['version'];
          $ix->status = $tcversion['status'];
          $ix->estimatedExecDuration = $tcversion['estimated_exec_duration'];
          $ix->is_open = $tcversion['is_open'];

          // Further processing will be needed to manage inline 
          // image attachments
          // updateSimpleFields() will be used.
          $ix->summary = $tcversion['summary'];
          $ix->preconditions = $tcversion['preconditions'];

          $op = $this->createVersion($ix);

          if( $op['status_ok'] ) {
              $alienTCV = $newTCObj['mappings'][$tcversion['id']] = $op['id'];

              // 2018
              $inlineImg = null;
              $attNewRef = $this->copy_attachments($tcversion['id'],$alienTCV);      
              if(!is_null($attNewRef)) {
                // get all attachments, then check is there are images
                $att = $this->attachmentRepository->getAttachmentInfosFor(
                         $alienTCV,$this->attachmentTableName,'id');
                foreach($attNewRef as $oid => $nid) {
                  if($att[$nid]['is_image']) {
                    $needle = str_replace($nid,$oid,$att[$nid]['inlineString']);
                    $inlineImg[] = 
                      array('needle' => $needle, 'rep' => $att[$nid]['inlineString']);
                  }
                }
              }

              $doInline = !is_null($inlineImg);
              if($doInline) {
                foreach($inlineImg as $elem) {
                  $ix->summary = str_replace($elem['needle'],$elem['rep'],
                                             $ix->summary);
                  $ix->preconditions = str_replace($elem['needle'],$elem['rep'],
                                                   $ix->preconditions);
                }
                // updateSimpleFields() will be used.
                $usf = array('summary' => $ix->summary,
                             'preconditions' => $ix->preconditions);

                $this->updateSimpleFields($alienTCV,$usf);
              }        

              // ATTENTION:  NEED TO UNDERSTAND HOW TO MANAGE COPY TO OTHER TEST PROJECTS
              $this->copy_cfields_design_values(
                array('id' => $id, 'tcversion_id' => $tcversion['id']),
                array('id' => $newTCObj['id'], 'tcversion_id' => $op['id']));

              // Need to get all steps
              $stepsSet = $this->get_steps($tcversion['id'],0,$my['options']);

              $to_tcversion_id = $op['id'];
              if( !is_null($stepsSet) ) {

                // not elegant but ...
                if($my['options']['stepAsGhost']) {
                  $pfx = $this->getPrefix($id);
                  $pfx = $pfx[0] . $this->cfg->testcase->glue_character . $tcversion['tc_external_id'];
                  foreach($stepsSet as $key => $step) {
                    $act = "[ghost]\"Step\":{$step['step_number']}," .
                           '"TestCase"' .':"' . $pfx . '",' .
                           "\"Version\":{$tcversion['version']}[/ghost]";
                    $op = $this->create_step($to_tcversion_id,
                                             $step['step_number'],$act,$act,
                                             $step['execution_type']);
                  }
                } else {
                  foreach($stepsSet as $key => $step) {
                    $op = $this->create_step($to_tcversion_id,
                                             $step['step_number'],
                                             $step['actions'],
                                             $step['expected_results'],
                                             $step['execution_type']);
                  }
                }
              }
          }

          // Conditional copies
          if( $op['status_ok'] ) {
            $source = array('id' => $id, 'version_id' => $tcversion['id']);
            $dest = array('id' => $newTCObj['id'], 'version_id' => $op['id'] ,
                          'version' => $tcversion['version']);
          }
            
          if( $op['status_ok'] && $copyKW ) {
            $this->copyKeywordsTo($source,$dest,$my['mappings']['keywords']);
          }

          if( $op['status_ok'] && $copyReqLinks ) {
            $this->copyReqVersionLinksTo($source,$dest,
              $my['mappings']['requirements'],$ix->authorID);
          }
        }  // foreach($tcase_info ...
      } // $newTCObj['status_ok']
    }

    return $newTCObj ;
  }


  /*
    function: create_new_version()
              create a new test case version,
              doing a copy of source test case version


    args : $id: testcase id
           $user_id: who is doing this operation.
           [$source_version_id]: default null -> source is LATEST TCVERSION

    returns:
            map:  id: node id of created tcversion
                  version: version number (i.e. 5)
                  msg

  */
  function create_new_version($id,$user_id,$source_version_id=null, $options=null) {

    $reqTCLinksCfg = config_get('reqTCLinks');    
    $freezeLinkOnNewTCVersion = $reqTCLinksCfg->freezeLinkOnNewTCVersion;
    $freezeLinkedRequirements = $freezeLinkOnNewTCVersion && 
      $reqTCLinksCfg->freezeBothEndsOnNewTCVersion;


    $now = $this->db->db_now();
    $opt = array('is_open' => 1, 
                 'freezeLinkedRequirements' => $freezeLinkedRequirements,
                 'freezeLinkOnNewTCVersion' => $freezeLinkOnNewTCVersion);

    $opt = array_merge($opt,(array)$options);

    $tcversion_id = $this->tree_manager->new_node($id,$this->node_types_descr_id['testcase_version']);

    // get last version for this test case (need to get new version number)
    $last_version_info =  $this->get_last_version_info($id, array('output' => 'minimun'));

    $from = $source_version_id;
    if( is_null($source_version_id) || $source_version_id <= 0) {
      $from = $last_version_info['id'];
    }
    $this->copy_tcversion($id,$from,$tcversion_id,$last_version_info['version']+1,$user_id);

    $ret['id'] = $tcversion_id;
    $ret['version'] = $last_version_info['version']+1;
    $ret['msg'] = 'ok';

    $this->setIsOpen(null,$tcversion_id,$opt['is_open']);

    // Keywords managed @version level.
    $source = array('id' => $id, 'version_id' => $from);
    $dest = array('id' => $id, 'version_id' => $tcversion_id);
    $auditContext = array('on' => self::AUDIT_OFF);
    
    $this->copyKeywordsTo($source,$dest,null,$auditContext,array('delete' => false));
    $this->copy_attachments($source['version_id'],$dest['version_id']);
    $this->copyTCVRelations($source['version_id'],$dest['version_id']);

    if( $this->cfg->testcase->relations->enable ) {
      $oldVerRel = $this->getTCVRelationsRaw($source['version_id']);
      if( null != $oldVerRel && count($oldVerRel) > 0 ) {
        $i2c = array_keys($oldVerRel);
        $this->closeOpenTCVRelation($i2c,LINK_TC_RELATION_CLOSED_BY_NEW_TCVERSION);
      }
    }


    if( $opt['freezeLinkedRequirements'] ) {
       $this->closeOpenReqVersionOnOpenLinks($source['version_id']);
    }

    $signature = array('user_id' => $user_id, 'when' => $now);
    $link = array('source' => $source['version_id'],
                  'dest' => $dest['version_id']);
    $optUC = array('freezePrevious' => $opt['freezeLinkOnNewTCVersion']);
    $this->updateCoverage($link,$signature,$optUC);


    $ret['id'] = $tcversion_id;
    $ret['version'] = $last_version_info['version']+1;
    $ret['msg'] = 'ok';
    
    return $ret;
  }



  /*
    function: get_last_version_info
              Get information about last version (greater number) of a testcase.

    args : id: testcase id
           [options]

    returns: map with keys  that depends of options['output']:

             id -> tcversion_id
             version
             summary
             importance
             author_id
             creation_ts
             updater_id
             modification_ts
             active
             is_open
    @internal revisions
    @since 1.9.9
    'active' => values 1,0, null => do not apply filter
  */
  function get_last_version_info($id,$options=null) {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $my['options'] = array( 'get_steps' => false, 'output' => 'full','active' => null);
    $my['options'] = array_merge($my['options'], (array)$options);
    $tcInfo = null;
    switch($my['options']['output']) {

      case 'thin':
        $fields2get = " TCV.id AS tcversion_id";
      break;

      case 'minimun':
        $fields2get = " TCV.id, TCV.id AS tcversion_id, TCV.version, TCV.tc_external_id,NH_TC.name ";
      break;

      case 'full':
      default:
        $fields2get = " TCV.*,TCV.id AS tcversion_id, NH_TC.name ";
      break;
    }


    $sql = "/* $debugMsg */ SELECT MAX(version) AS version " .
           " FROM {$this->tables['tcversions']} TCV " .
           " JOIN {$this->tables['nodes_hierarchy']} NH_TCV ON NH_TCV.id = TCV.id ".
           " WHERE NH_TCV.parent_id = {$id} ";

    if( !is_null($my['options']['active']) ) {
      $sql .= " AND TCV.active=" . (intval($my['options']['active']) > 0 ? 1 : 0);
    }

    $max_version = $this->db->fetchFirstRowSingleColumn($sql,'version');

    $tcInfo = null;
    if ($max_version) {
      $sql = " SELECT {$fields2get} FROM {$this->tables['tcversions']} TCV " .
             " JOIN {$this->tables['nodes_hierarchy']} NH_TCV ON NH_TCV.id = TCV.id ".
             " JOIN {$this->tables['nodes_hierarchy']} NH_TC ON NH_TC.id = NH_TCV.parent_id ".
             " WHERE TCV.version = {$max_version} ".
             " AND NH_TCV.parent_id = {$id}";

      $tcInfo = $this->db->fetchFirstRow($sql);
    }

    // Multiple Test Case Steps Feature
    if( !is_null($tcInfo) && $my['options']['get_steps'] ) {
      $step_set = $this->get_steps($tcInfo['id']);
      $tcInfo['steps'] = $step_set;
    }

    return $tcInfo;
  }


  /*
    function: copy_tcversion

    args:

    returns:

    rev:

  */
  function copy_tcversion($id,$from_tcversion_id,$to_tcversion_id,$as_version_number,$user_id)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $now = $this->db->db_now();
    $sql = "/* $debugMsg */ " .
           " INSERT INTO {$this->tables['tcversions']} " .
           " (id,version,tc_external_id,author_id,creation_ts,summary, " .
           "  importance,execution_type,preconditions,estimated_exec_duration) " .
           " SELECT {$to_tcversion_id} AS id, {$as_version_number} AS version, " .
           "        tc_external_id, " .
           "        {$user_id} AS author_id, {$now} AS creation_ts," .
           "        summary,importance,execution_type, preconditions,estimated_exec_duration " .
           " FROM {$this->tables['tcversions']} " .
           " WHERE id={$from_tcversion_id} ";
    $result = $this->db->exec_query($sql);

    // copy custom fields values JUST DESIGN AREA
    $this->copy_cfields_design_values(array('id' => $id, 'tcversion_id' => $from_tcversion_id),
                                      array('id' => $id, 'tcversion_id' => $to_tcversion_id));


    // Need to get all steps
    $gso = array('renderGhostSteps' => false, 'renderImageInline' => false);
    $stepsSet = $this->get_steps($from_tcversion_id,0,$gso);
    if( !is_null($stepsSet) && count($stepsSet) > 0) {
      foreach($stepsSet as $key => $step) {
        $op = $this->create_step($to_tcversion_id,$step['step_number'],
                                 $step['actions'],$step['expected_results'],
                                 $step['execution_type']);
      }
    }
  }


  /*
    function: get_by_id_bulk

              IMPORTANT CONSIDERATION:
              how may elements can be used in an SQL IN CLAUSE?
              Think there is a limit ( on MSSQL 1000 ?)

    args :

    returns:

  */
  function get_by_id_bulk($id,$version_id=self::ALL_VERSIONS, $get_active=0, $get_open=0)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $where_clause="";
    $where_clause_names="";
    $tcid_list ="";
    $tcversion_id_filter="";
    $sql = "";
    $the_names = null;
    if( is_array($id) ) {
      $tcid_list = implode(",",$id);
      $where_clause = " WHERE nodes_hierarchy.parent_id IN ($tcid_list) ";
      $where_clause_names = " WHERE nodes_hierarchy.id IN ($tcid_list) ";
    }
    else
    {
      $where_clause = " WHERE nodes_hierarchy.parent_id = {$id} ";
      $where_clause_names = " WHERE nodes_hierarchy.id = {$id} ";
    }
      if( $version_id != self::ALL_VERSIONS )
      {
          $tcversion_id_filter=" AND tcversions.id IN (" . implode(",",(array)$version_id) . ") ";
      }

    $sql = " /* $debugMsg */ SELECT nodes_hierarchy.parent_id AS testcase_id, ".
           " tcversions.*, users.first AS author_first_name, users.last AS author_last_name, " .
           " '' AS updater_first_name, '' AS updater_last_name " .
           " FROM {$this->tables['nodes_hierarchy']} nodes_hierarchy " .
           " JOIN {$this->tables['tcversions']} tcversions ON nodes_hierarchy.id = tcversions.id " .
             " LEFT OUTER JOIN {$this->tables['users']} users ON tcversions.author_id = users.id " .
             " {$where_clause} {$tcversion_id_filter} ORDER BY tcversions.version DESC";
    $recordset = $this->db->get_recordset($sql);

    if($recordset)
    {
       // get the names
     $sql = " /* $debugMsg */ " .
            " SELECT nodes_hierarchy.id AS testcase_id, nodes_hierarchy.name " .
            " FROM {$this->tables['nodes_hierarchy']} nodes_hierarchy {$where_clause_names} ";

     $the_names = $this->db->get_recordset($sql);
       if($the_names)
       {
          foreach ($recordset as  $the_key => $row )
          {
              reset($the_names);
              foreach($the_names as $row_n)
              {
                  if( $row['testcase_id'] == $row_n['testcase_id'])
                  {
                    $recordset[$the_key]['name']= $row_n['name'];
                    break;
                  }
              }
          }
       }


     $sql = " /* $debugMsg */ " .
            " SELECT updater_id, users.first AS updater_first_name, users.last  AS updater_last_name " .
            " FROM {$this->tables['nodes_hierarchy']} nodes_hierarchy " .
            " JOIN {$this->tables['tcversions']} tcversions ON nodes_hierarchy.id = tcversions.id " .
              " LEFT OUTER JOIN {$this->tables['users']} users ON tcversions.updater_id = users.id " .
              " {$where_clause} and tcversions.updater_id IS NOT NULL ";

      $updaters = $this->db->get_recordset($sql);

      if($updaters)
      {
        reset($recordset);
        foreach ($recordset as  $the_key => $row )
        {
          if ( !is_null($row['updater_id']) )
          {
            foreach ($updaters as $row_upd)
            {
              if ( $row['updater_id'] == $row_upd['updater_id'] )
              {
                $recordset[$the_key]['updater_last_name'] = $row_upd['updater_last_name'];
                $recordset[$the_key]['updater_first_name'] = $row_upd['updater_first_name'];
                break;
              }
            }
          }
        }
      }

    }


    return($recordset ? $recordset : null);
  }




  /*
    function: get_by_id

    args : id: can be a single testcase id or an array od testcase id.

           [version_id]: default self::ALL_VERSIONS => all versions
                         can be an array.
                         Useful to retrieve only a subset of versions.
                         null => means use version_number argument

       [filters]:
                [active_status]: default 'ALL', range: 'ALL','ACTIVE','INACTIVE'
                                 has effect for the following version_id values:
                                 self::ALL_VERSIONS,TC_LAST_VERSION, version_id is NOT an array

                [open_status]: default 'ALL'
                               currently not used.

                [version_number]: default 1, version number displayed at User Interface

       [options]:
                [output]: default 'full'
          domain 'full','essential','full_without_steps','full_without_users'

    returns: array

  */
  function get_by_id($id,$version_id = self::ALL_VERSIONS, $filters = null, $options=null) {
    $my['filters'] = array('active_status' => 'ALL', 'open_status' => 'ALL', 'version_number' => 1);
    $my['filters'] = array_merge($my['filters'], (array)$filters);

    $my['options'] = array('output' => 'full', 'access_key' => 'tcversion_id', 
                           'getPrefix' => false,
                           'order_by' => null, 'renderGhost' => false, 
                           'withGhostString' => false,
                           'renderImageInline' => false, 
                           'renderVariables' => false,
                           'renderSpecialKW' => false);

    $my['options'] = array_merge($my['options'], (array)$options);

    $tcid_list = null;
    $where_clause = '';
    $active_filter = '';
    $versionSQLOp = ' AND ';

    if( ($accessByVersionID = is_null($id) && !is_null($version_id)) ) {
      $versionSQLOp = ' WHERE ';
    }
    else if(is_array($id)) {
      $tcid_list = implode(",",$id);
      $where_clause = " WHERE NHTCV.parent_id IN ({$tcid_list}) ";
    }
    else {
      $where_clause = " WHERE NHTCV.parent_id = {$id} ";
    }

    if( ($version_id_is_array=is_array($version_id)) ) {
        $versionid_list = implode(",",$version_id);
        $where_clause .= $versionSQLOp . " TCV.id IN ({$versionid_list}) ";
    }
    else {
      if( is_null($version_id) ) {
          // when tcase ID has not been provided this can not be used
          // will not do any check => leave it CRASH
            $where_clause .= " AND TCV.version = {$my['filters']['version_number']} ";
      }
      else {
        if($version_id != self::ALL_VERSIONS && $version_id != self::LATEST_VERSION) {
          $where_clause .= $versionSQLOp .  " TCV.id = {$version_id} ";
        }
      }

      $active_status = strtoupper($my['filters']['active_status']);
      if($active_status != 'ALL') {
        $active_filter =' AND TCV.active=' . ($active_status=='ACTIVE' ? 1 : 0) . ' ';
      }
    }

    switch($my['options']['output']) {
      case 'full':
      case 'full_without_steps':
        $sql = "SELECT UA.login AS updater_login,UB.login AS author_login,
                NHTC.name,NHTC.node_order,NHTC.parent_id AS testsuite_id,
                NHTCV.parent_id AS testcase_id, TCV.*,
                UB.first AS author_first_name,UB.last AS author_last_name,
                UA.first AS updater_first_name,UA.last AS updater_last_name
                FROM {$this->tables['nodes_hierarchy']} NHTCV
                JOIN {$this->tables['nodes_hierarchy']} NHTC ON NHTCV.parent_id = NHTC.id
                JOIN {$this->tables['tcversions']} TCV ON NHTCV.id = TCV.id
                LEFT OUTER JOIN {$this->tables['users']} UB ON TCV.author_id = UB.id
                LEFT OUTER JOIN {$this->tables['users']} UA ON TCV.updater_id = UA.id
                $where_clause $active_filter";

            if(is_null($my['options']['order_by'])) {
              $sql .= " ORDER BY TCV.version DESC";
            }
            else {
              $sql .= $my['options']['order_by'];
            }
            break;

      case 'full_without_users':
        $tcversionFields = 'TCV.id,TCV.tc_external_id,TCV.version,TCV.status,TCV.active,TCV.is_open,' .
                           'TCV.execution_type,TCV.importance';

        // ATTENTION:
        // Order is critical for functions that use this recordset
        // (see specview.php).
        //
        $sql = "SELECT NHTC.name,NHTC.node_order,NHTC.parent_id AS testsuite_id,
                NHTCV.parent_id AS testcase_id, {$tcversionFields}
                FROM {$this->tables['nodes_hierarchy']} NHTCV
                JOIN {$this->tables['nodes_hierarchy']} NHTC ON NHTCV.parent_id = NHTC.id
                JOIN {$this->tables['tcversions']} TCV ON NHTCV.id = TCV.id
                $where_clause $active_filter";

            if(is_null($my['options']['order_by'])) {
              $sql .= " ORDER BY NHTC.node_order, NHTC.name, TCV.version DESC ";
            }
            else {
              $sql .= $my['options']['order_by'];
            }
            break;

      case 'essential':
        $sql = " SELECT NHTC.name,NHTC.node_order,NHTCV.parent_id AS testcase_id, " .
               " NHTC.parent_id AS testsuite_id, " .
               " TCV.version, TCV.id, TCV.tc_external_id " .
               " FROM {$this->tables['nodes_hierarchy']} NHTCV " .
               " JOIN {$this->tables['nodes_hierarchy']} NHTC ON NHTCV.parent_id = NHTC.id " .
               " JOIN {$this->tables['tcversions']} TCV ON NHTCV.id = TCV.id " .
               " {$where_clause} {$active_filter} ";

        if(is_null($my['options']['order_by'])) {
          $sql .= " ORDER BY TCV.version DESC ";
        } else {
          $sql .= $my['options']['order_by'];
        }
      break;
    }

    $render = array();
    $render['ghost'] = false;
    $render['ghostSteps'] = false;
    $render['imageInline'] = $my['options']['renderImageInline'];
    $render['variables'] = $my['options']['renderVariables'];
    $render['specialKW'] = $my['options']['renderSpecialKW'];

    switch($my['options']['output']) {
      case 'full':
      case 'full_without_users':
        $render['ghost'] = $my['options']['renderGhost'];
        $render['ghostSteps'] = true;
      break;

      case 'full_without_steps':
        $render['ghost'] = $my['options']['renderGhost'];
        $render['ghostSteps'] = false;
      break;

      case 'essential':
        $render['imageInline'] = false;
        $render['variables'] = false;
      break;
    }

    $recordset = null;

    // Control improvements
    if( !$version_id_is_array && $version_id == self::LATEST_VERSION) {
      // But, how performance wise can be do this, instead of using MAX(version)
      // and a group by?
      //
      // if $id was a list then this will return something USELESS
      //
      if( is_null($tcid_list) ) {
        $recordset = array($this->db->fetchFirstRow($sql));
      }
      else {
        // Write to event viewer ???
        // throw exception ??
      }
    }
    else {
      $recordset = $this->db->get_recordset($sql);
    }

    $canProcess = !is_null($recordset);

    if( $canProcess && $render['variables'] ) {
      $key2loop = array_keys($recordset);
      foreach( $key2loop as $accessKey) {
        $this->renderVariables($recordset[$accessKey]);
      }
      reset($recordset);
    }

    if( $canProcess && $render['specialKW'] ) {
      $key2loop = array_keys($recordset);
      foreach( $key2loop as $accessKey) {
        $this->renderSpecialTSuiteKeywords($recordset[$accessKey]);
      }
      reset($recordset);
    }


    // ghost on preconditions and summary
    if( $canProcess && $my['options']['renderGhost'] ) {
      $key2loop = array_keys($recordset);
      foreach( $key2loop as $accessKey) {
        $this->renderGhost($recordset[$accessKey]);
      }
      reset($recordset);
    }

    if( $canProcess && $render['imageInline']) {
      $key2loop = array_keys($recordset);
      foreach( $key2loop as $accessKey) {
        $pVersion = $recordset[$accessKey]['id'];
        $this->renderImageAttachments($pVersion,$recordset[$accessKey]);
      }
      reset($recordset);
    }


    // Multiple Test Case Steps
    if( $canProcess && $my['options']['output'] == 'full') {
      $gsOpt['renderGhostSteps'] = $my['options']['renderGhost'];

      $key2loop = array_keys($recordset);
      foreach( $key2loop as $accessKey) {
        $step_set = $this->get_steps($recordset[$accessKey]['id'],0,$gsOpt);
        if($my['options']['withGhostString']) {
          // need to get test case prefix test project info
          $pfx = $this->getPrefix($id);
          $pfx = $pfx[0] . $this->cfg->testcase->glue_character . $recordset[$accessKey]['tc_external_id'];

          $k2l = array_keys((array)$step_set);
          foreach($k2l as $kx) {
            $step_set[$kx]['ghost_action'] = "[ghost]\"Step\":{$step_set[$kx]['step_number']}," .
                                             '"TestCase"' .':"' . $pfx . '",' .
                                             "\"Version\":{$recordset[$accessKey]['version']}[/ghost]";
            $step_set[$kx]['ghost_result'] = $step_set[$kx]['ghost_action'];
          }
        }
        $recordset[$accessKey]['steps'] = $step_set;
      }
    }

    if( $canProcess && $my['options']['getPrefix'] ) {
      $pfx = $this->getPrefix($id);
      $key2loop = array_keys($recordset);
      foreach( $key2loop as $accessKey) {
        $recordset[$accessKey]['fullExternalID'] =  $pfx[0] . $this->cfg->testcase->glue_character .
                                                    $recordset[$accessKey]['tc_external_id'];
      }
    }

    return ($recordset ? $recordset : null);
  }


  /*
    function: get_versions_status_quo
              Get linked and executed status quo.

              IMPORTANT:
              NO INFO SPECIFIC TO TESTPLAN ITEMS where testacase can be linked to
              is returned.


    args : id: test case id
           [tcversion_id]: default: null -> get info about all versions.
                           can be a single value or an array.


           [testplan_id]: default: null -> all testplans where testcase is linked,
                                           are analised to generate results.

                          when not null, filter for testplan_id, to analise for
                          generating results.



    returns: map.
             key: tcversion_id.
             value: map with the following keys:

             tcversion_id, linked , executed

             linked field: will take the following values
                           if $testplan_id == null
                              NULL if the tc version is not linked to ANY TEST PLAN
                              tcversion_id if linked

                           if $testplan_id != null
                              NULL if the tc version is not linked to $testplan_id


             executed field: will take the following values
                             if $testplan_id == null
                                NULL if the tc version has not been executed in ANY TEST PLAN
                                tcversion_id if has executions.

                             if $testplan_id != null
                                NULL if the tc version has not been executed in $testplan_id

  rev :

  */
  function get_versions_status_quo($id, $tcversion_id=null, $testplan_id=null) {
    $testplan_filter='';
    $tcversion_filter='';
    if(!is_null($tcversion_id)) {
      if(is_array($tcversion_id)) {
         $tcversion_filter=" AND NH.id IN (" . implode(",",$tcversion_id) . ") ";
      } else {
         $tcversion_filter=" AND NH.id={$tcversion_id} ";
      }
    }

    $testplan_filter='';
    if(!is_null($testplan_id)){
      $testplan_filter=" AND E.testplan_id = {$testplan_id} ";
    }
    $execution_join=" LEFT OUTER JOIN {$this->tables['executions']} E " .
                    " ON (E.tcversion_id = NH.id {$testplan_filter})";

    $sqlx=  " SELECT TCV.id,TCV.version  
              FROM {$this->tables['nodes_hierarchy']} NHA
              JOIN {$this->tables['nodes_hierarchy']} NHB 
              ON NHA.parent_id = NHB.id
              JOIN {$this->tables['tcversions']}  TCV ON NHA.id = TCV.id 
              WHERE  NHA.parent_id = " . intval($id);

    $version_id = $this->db->fetchRowsIntoMap($sqlx,'version');

    $sql="SELECT DISTINCT NH.id AS tcversion_id,T.tcversion_id AS linked, " .
         " E.tcversion_id AS executed,E.tcversion_number,TCV.version " .
         " FROM   {$this->tables['nodes_hierarchy']} NH " .
           " JOIN {$this->tables['tcversions']} TCV ON (TCV.id = NH.id ) " .
         " LEFT OUTER JOIN {$this->tables['testplan_tcversions']} T ON T.tcversion_id = NH.id " .
         " {$execution_join} WHERE  NH.parent_id = {$id} {$tcversion_filter} ORDER BY executed DESC";

    $rs = $this->db->get_recordset($sql);

    $recordset=array();
    $template=array('tcversion_id' => '','linked' => '','executed' => '');
    foreach($rs as $elem) {
      $recordset[$elem['tcversion_id']]=$template;
      $recordset[$elem['tcversion_id']]['tcversion_id']=$elem['tcversion_id'];
      $recordset[$elem['tcversion_id']]['linked']=$elem['linked'];
      $recordset[$elem['tcversion_id']]['version']=$elem['version'];
    }

    foreach($rs as $elem) {
      $tcvid=null;
      if( $elem['tcversion_number'] != $elem['version']) {
        if( !is_null($elem['tcversion_number']) ) {
          $tcvid=$version_id[$elem['tcversion_number']]['id'];
        }
      } else {
        $tcvid=$elem['tcversion_id'];
      }
      
      if( !is_null($tcvid) ) {
        $recordset[$tcvid]['executed']=$tcvid;
        $recordset[$tcvid]['version']=$elem['tcversion_number'];
      }
    }

    return $recordset;
  }



  /*
    function: get_exec_status
              Get information about executed and linked status in
              every testplan, a testcase is linked to.

    args : id : testcase id
           [exec_status]: default: ALL, range: ALL,EXECUTED,NOT_EXECUTED
           [active_status]: default: ALL, range: ALL,ACTIVE,INACTIVE


    returns: map
             key: tcversion_id
             value: map:
                    key: testplan_id
                    value: map with following keys:

                    tcase_id
                    tcversion_id
                    version
                    testplan_id
                    tplan_name
                    linked         if linked to  testplan -> tcversion_id
                    executed       if executed in testplan -> tcversion_id
                    exec_on_tplan  if executed in testplan -> testplan_id


    rev:
         20100908 - franciscom - added platform name in output recordset

         20080531 - franciscom
         Because we allow people to update test case version linked to test plan,
         and to do this we update tcversion_id on executions to new version
         maintaining the really executed version in tcversion_number (version number displayed
         on User Interface) field we need to change algorithm.
  */
  function get_exec_status($id,$filters=null, $options=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $my = array();
    $my['filters'] = array( 'exec_status' => "ALL", 'active_status' => 'ALL',
                'tplan_id' => null, 'platform_id' => null);
    $my['options'] = array('addExecIndicator' => false);

    $my['filters'] = array_merge($my['filters'], (array)$filters);
    $my['options'] = array_merge($my['options'], (array)$options);


    $active_status = strtoupper($my['filters']['active_status']);
    $exec_status = strtoupper($my['filters']['exec_status']);
    $tplan_id = $my['filters']['tplan_id'];
    $platform_id = $my['filters']['platform_id'];

    // Get info about tcversions of this test case
    $sqlx = "/* $debugMsg */ " .
            " SELECT TCV.id,TCV.version,TCV.active" .
            " FROM {$this->tables['nodes_hierarchy']} NHA " .
            " JOIN {$this->tables['nodes_hierarchy']} NHB ON NHA.parent_id = NHB.id " .
            " JOIN {$this->tables['tcversions']}  TCV ON NHA.id = TCV.id ";

    $where_clause = " WHERE  NHA.parent_id = " . $this->db->prepare_int($id);

    if(!is_null($tplan_id))
    {
          $sqlx .= " JOIN {$this->tables['testplan_tcversions']}  TTCV ON TTCV.tcversion_id = TCV.id ";
          $where_clause .= " AND TTCV.tplan_id = " . $this->db->prepare_int($tplan_id);
    }
    $sqlx .= $where_clause;
    $version_id = $this->db->fetchRowsIntoMap($sqlx,'version');

    $sql = "/* $debugMsg */ " .
           " SELECT DISTINCT NH.parent_id AS tcase_id, NH.id AS tcversion_id, " .
           " T.tcversion_id AS linked, T.platform_id, TCV.active, E.tcversion_id AS executed, " .
           " E.testplan_id AS exec_on_tplan, E.tcversion_number, " .
           " T.testplan_id, NHB.name AS tplan_name, TCV.version, PLAT.name AS platform_name " .
           " FROM   {$this->tables['nodes_hierarchy']} NH " .
           " JOIN {$this->tables['testplan_tcversions']}  T ON T.tcversion_id = NH.id " .
           " JOIN {$this->tables['tcversions']}  TCV ON T.tcversion_id = TCV.id " .
           " JOIN {$this->tables['nodes_hierarchy']} NHB ON T.testplan_id = NHB.id " .
           " LEFT OUTER JOIN {$this->tables['platforms']} PLAT " .
           " ON T.platform_id = PLAT.id " .
           " LEFT OUTER JOIN {$this->tables['executions']} E " .
           " ON (E.tcversion_id = NH.id AND E.testplan_id=T.testplan_id AND E.platform_id=T.platform_id ) " .
           " WHERE  NH.parent_id = " . $this->db->prepare_int($id);

    if(!is_null($tplan_id))
    {
      $sql .= " AND T.tplan_id = " . $this->db->prepare_int($tplan_id);
    }
    if(!is_null($platform_id))
    {
      $sql .= " AND T.platform_id = " . $this->db->prepare_int($platform_id);
    }

    $sql .= " ORDER BY version,tplan_name";
    $rs = $this->db->get_recordset($sql);

    // set right tcversion_id, based on tcversion_number,version comparison
    $item_not_executed = null;
    $item_executed = null;
    $link_info = null;
    $in_set = null;

    if (sizeof($rs))
    {
      foreach($rs as $idx => $elem)
      {
        if( $elem['tcversion_number'] != $elem['version'])
        {
          // Save to generate record for linked but not executed if needed
          // (see below fix not executed section)
          // access key => (version,test plan, platform)
          $link_info[$elem['tcversion_id']][$elem['testplan_id']][$elem['platform_id']]=$elem;

          // We are working with a test case version, that was used in a previous life of this test plan
          // information about his tcversion_id is not anymore present in tables:
          //
          // testplan_tcversions
          // executions
          // cfield_execution_values.
          //
          // if has been executed, but after this operation User has choosen to upgrade tcversion
          // linked to testplan to a different (may be a newest) test case version.
          //
          // We can get this information using table tcversions using tcase id and version number
          // (value displayed at User Interface) as search key.
          //
          // Important:
          // executions.tcversion_number:  maintain info about RIGHT TEST case version executed
          // executions.tcversion_id    :  test case version linked to test plan.
          //
          //
          if( is_null($elem['tcversion_number']) )
          {
            // Not Executed
            $rs[$idx]['executed']=null;
            $rs[$idx]['tcversion_id']=$elem['tcversion_id'];
            $rs[$idx]['version']=$elem['version'];
            $rs[$idx]['linked']=$elem['tcversion_id'];
            $item_not_executed[]=$idx;
          }
          else
          {
            // Get right tcversion_id
            $rs[$idx]['executed']=$version_id[$elem['tcversion_number']]['id'];
            $rs[$idx]['tcversion_id']=$rs[$idx]['executed'];
            $rs[$idx]['version']=$elem['tcversion_number'];
            $rs[$idx]['linked']=$rs[$idx]['executed'];
            $item_executed[]=$idx;
          }
          $version=$rs[$idx]['version'];
          $rs[$idx]['active']=$version_id[$version]['active'];
        }
        else
        {
          $item_executed[]=$idx;
        }

        // needed for logic to avoid miss not executed (see below fix not executed)
        $in_set[$rs[$idx]['tcversion_id']][$rs[$idx]['testplan_id']][$rs[$idx]['platform_id']]=$rs[$idx]['tcversion_id'];
      }
    }
    else
    {
      $rs = array();
    }

    // fix not executed
    //
    // need to add record for linked but not executed, that due to new
    // logic to upate testplan-tcversions link can be absent
    if(!is_null($link_info))
    {
      foreach($link_info as $tcversion_id => $elem)
      {
        foreach($elem as $testplan_id => $platform_link)
        {
          foreach($platform_link as $platform_id => $value)
          {
            if( !isset($in_set[$tcversion_id][$testplan_id][$platform_id]) )
            {
              // missing record
              $value['executed']=null;
              $value['exec_on_tplan']=null;
              $value['tcversion_number']=null;
              $rs[]=$value;

              // Must Update list of not executed
              $kix=count($rs);
              $item_not_executed[]=$kix > 0 ? $kix-1 : $kix;
            }

          }
        }
      }
    }

    // Convert to result map.
    switch ($exec_status)
    {
      case 'NOT_EXECUTED':
        $target=$item_not_executed;
      break;

      case 'EXECUTED':
        $target=$item_executed;
      break;

      default:
        $target = array_keys($rs);
      break;
    }

    $recordset = null;

    if( !is_null($target) )
    {
      foreach($target as $idx)
      {
        $wkitem=$rs[$idx];
        if( $active_status=='ALL' ||
            $active_status='ACTIVE' && $wkitem['active'] ||
            $active_status='INACTIVE' && $wkitem['active']==0 )
        {
          $recordset[$wkitem['tcversion_id']][$wkitem['testplan_id']][$wkitem['platform_id']]=$wkitem;

          if( $my['options']['addExecIndicator'] )
          {
            if( !isset($recordset['executed']) )
            {
              $recordset['executed'] = 0;
            }

            if( $recordset['executed'] == 0 )
            {
              if( !is_null($wkitem['executed']) )
              {
                $recordset['executed'] = 1;
              }
            }
          }
        }
      }
    }

    if( !is_null($recordset) )
    {
      // Natural name sort
      ksort($recordset);
    }
    return $recordset;
  }
  // -------------------------------------------------------------------------------


  /**
   * @param string stringID external test case ID
   *        a string on the form XXXXXGNN where:
   *        XXXXX: test case prefix, exists one for each test project
   *        G: glue character
   *        NN: test case number (generated using testprojects.tc_counter field)
   *
   * @return internal id (node id in nodes_hierarchy)
   *         0 -> test case prefix OK, but external id does not exists
   *         1 -> test case prefix KO
   *
   * 20080818 - franciscom - Dev Note
   * I'm a feeling regarding performance of this function.
   * Surelly adding a new column to tcversions (prefix) will simplify a lot this function.
   * Other choice (that I refuse to implement time ago) is to add prefix field
   * as a new nodes_hierarchy column.
   * This must be discussed with dev team if we got performance bottleneck trying
   * to get internal id from external one.
   *
   * @internal revisions
   */
  function getInternalID($stringID,$opt = null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $internalID = 0;
    $my['opt'] = array('glue' => $this->cfg->testcase->glue_character,
                       'tproject_id' => null, 'output' => null);
    $my['opt'] = array_merge($my['opt'], (array)$opt);

    $status_ok = false;
    $tproject_info = null;

    // When using this method on a context where caller certifies that
    // test project is OK, we will skip this check.
    $tproject_id = $my['opt']['tproject_id'];
    if( !is_null($tproject_id) && !is_null($my['opt']['output']) )
    {
      $sql = " SELECT id,is_public  FROM {$this->tables['testprojects']} " .
             " WHERE id = " . intval($tproject_id);
      $tproject_info = $this->db->get_recordset($sql);
      if( !is_null($tproject_info) )
      {
        $tproject_info = current($tproject_info);
      }
    }


    // Find the last glue char
    $gluePos = strrpos($stringID, $my['opt']['glue']);
    $isFullExternal = ($gluePos !== false);
    if($isFullExternal)
    {
      $rawTestCasePrefix = substr($stringID, 0, $gluePos);
      $rawExternalID = substr($stringID, $gluePos+1);
      $status_ok = ($externalID = is_numeric($rawExternalID) ?  intval($rawExternalID) : 0) > 0;
    }
    else
    {
      $status_ok = (($externalID = intval($stringID)) > 0);
    }

    if( $status_ok && is_null($tproject_id) )
    {
      $status_ok = false;
      if($isFullExternal)
      {
        // Check first if Test Project prefix is valid, if not abort
        $testCasePrefix = $this->db->prepare_string($rawTestCasePrefix);
        $sql = "SELECT id,is_public  FROM {$this->tables['testprojects']} " .
               "WHERE prefix = '" . $this->db->prepare_string($testCasePrefix) . "'";
        $tproject_info = $this->db->get_recordset($sql);
        if( $status_ok = !is_null($tproject_info) )
        {
          $tproject_info = current($tproject_info);
          $tproject_id = $tproject_info['id'];
          // $tproject_id = $tproject_info[0]['id'];
        }
      }
      else
      {
        throw new Exception(__METHOD__ .
                           ' EXCEPTION: When using just numeric part of External ID, test project ID, is mandatory');
      }
    }

    if( $status_ok )
    {
      $internalID = 0;

      // get all test cases with requested external ID on all test projects.
      // we do not have way to work only on one test project.
      $sql = " SELECT DISTINCT NHTCV.parent_id AS tcase_id" .
             " FROM {$this->tables['tcversions']} TCV " .
             " JOIN {$this->tables['nodes_hierarchy']} NHTCV " .
             " ON TCV.id = NHTCV.id " .
             " WHERE  TCV.tc_external_id = " . intval($externalID);

      $testCases = $this->db->fetchRowsIntoMap($sql,'tcase_id');
      if(!is_null($testCases))
      {
        foreach($testCases as $tcaseID => $value)
        {
          $path2root = $this->tree_manager->get_path($tcaseID);
          if($tproject_id == $path2root[0]['parent_id'])
          {
            $internalID = $tcaseID;
            break;
          }
        }
      }
    }
    return is_null($my['opt']['output']) ? $internalID :
           array('id' => $internalID,'tproject' => $tproject_info);
  }

  /*
    function: filterByKeyword
              given a test case id (or an array of test case id)
              and a keyword filter, returns for the test cases given in input
              only which pass the keyword filter criteria.


    args :

    returns:

  */
  function filterByKeyword($id,$keyword_id=0, $keyword_filter_type='OR')
  {
      $keyword_filter= '' ;
      $subquery='';

      // test case filter
      if( is_array($id) )
      {
          $testcase_filter = " AND testcase_id IN (" . implode(',',$id) . ")";
      }
      else
      {
          $testcase_filter = " AND testcase_id = {$id} ";
      }

      if( is_array($keyword_id) )
      {
          $keyword_filter = " AND keyword_id IN (" . implode(',',$keyword_id) . ")";

          if($keyword_filter_type == 'AND')
          {
              $subquery = "AND testcase_id IN (" .
                          " SELECT MAFALDA.testcase_id FROM
                            ( SELECT COUNT(testcase_id) AS HITS,testcase_id
                              FROM {$this->tables['keywords']} K, {$this->tables['testcase_keywords']}
                              WHERE keyword_id = K.id
                              {$keyword_filter}
                              GROUP BY testcase_id ) AS MAFALDA " .
                          " WHERE MAFALDA.HITS=" . count($keyword_id) . ")";

              $keyword_filter ='';
          }
      }
      else if( $keyword_id > 0 )
      {
          $keyword_filter = " AND keyword_id = {$keyword_id} ";
      }

      $map_keywords = null;
      $sql = " SELECT testcase_id,keyword_id,keyword
               FROM {$this->tables['keywords']} K, {$this->tables['testcase_keywords']}
               WHERE keyword_id = K.id
               {$testcase_filter}
               {$keyword_filter} {$subquery}
               ORDER BY keyword ASC ";

      // $map_keywords = $this->db->fetchRowsIntoMap($sql,'testcase_id');
      $map_keywords = $this->db->fetchMapRowsIntoMap($sql,'testcase_id','keyword_id');

      return($map_keywords);
  } //end function



  // ------------------------------------------------------------------------
  //                            Keyword related methods
  // ------------------------------------------------------------------------
  /*
    function: getKeywords

    args :

    returns:

  */
  function getKeywords($tcID,$versionID,$kwID = null,$opt = null) {
    $my['opt'] = array('accessKey' => 'keyword_id', 'fields' => null, 
                       'orderBy' => null);

    $my['opt'] = array_merge($my['opt'],(array)$opt);

    $f2g = is_null($my['opt']['fields']) ?
           ' TCKW.id AS tckw_link,keyword_id,KW.keyword,KW.notes,
             testcase_id,tcversion_id ' :
           $my['opt']['fields'];

    $sql = " SELECT {$f2g}
             FROM {$this->tables['testcase_keywords']} TCKW
             JOIN {$this->tables['keywords']} KW
             ON keyword_id = KW.id ";
             
    $sql .=  " WHERE testcase_id = " . intval($tcID) . 
             " AND tcversion_id=" . intval($versionID);

    if (!is_null($kwID)) {
      $sql .= " AND keyword_id = " . intval($kwID);
    }

    if (!is_null($my['opt']['orderBy'])) {
      $sql .= ' ' . $my['opt']['orderBy'];
    }

    switch( $my['opt']['accessKey'] ) {
      case 'testcase_id,tcversion_id';
        $items = $this->db->fetchMapRowsIntoMap($sql,'testcase_id','tcversion_id',database::CUMULATIVE);
      break;

      default:
        $items = $this->db->fetchRowsIntoMap($sql,$my['opt']['accessKey']);
      break;
    }

    return $items;
  }

  /**
   *
   */
  function getKeywordsByIdCard($idCard,$opt=null) {
    return $this->get_keywords_map($idCard['tcase_id'],$idCard['tcversion_id'],$opt);
  }



  /*
    function: get_keywords_map

    args: id: testcase id
          version_id
          opt: 'orderByClause' => '' -> no order choosen
                                  must be an string with complete clause,
                                  i.e. 'ORDER BY keyword'

               'output' => null => array[keyword_id] = keyword
                           'kwfull' =>
                              array[keyword_id] = array('keyword_id' => value,
                                                        'keyword' => value,
                                                        'notes' => value)

    returns: map with keywords information


  */
  function get_keywords_map($id,$version_id,$opt=null) {
    $my['opt'] = array('orderByClause' => '', 'output' => null);
    $my['opt'] = array_merge($my['opt'], (array)$opt);


    switch($my['opt']['output']) {
      case 'kwfull':
        $sql = "SELECT TCKW.keyword_id,KW.keyword,KW.notes";
      break;

      default:
        $sql = "SELECT TCKW.keyword_id,KW.keyword";
      break;
    }
    $sql .= " FROM {$this->tables['testcase_keywords']} TCKW, " .
            " {$this->tables['keywords']} KW WHERE keyword_id = KW.id ";

    $sql .= " AND TCKW.testcase_id = " . intval($id) . 
            " AND TCKW.tcversion_id = " . intval($version_id); 

    $sql .= $my['opt']['orderByClause'];


    switch($my['opt']['output']) {
      case 'kwfull':
        $map_keywords = $this->db->fetchRowsIntoMap($sql,'keyword_id');
      break;

      default:
        $map_keywords = $this->db->fetchColumnsIntoMap($sql,'keyword_id','keyword');
      break;
    }

    return $map_keywords;
  }

  /**
   * add keywords without checking if exist.
   *
   */
  function addKeywords($id,$version_id,$kw_ids,$audit=null) {

    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $adt = array('on' => self::AUDIT_ON, 'version' => null);
    $adt = array_merge($adt, ($audit));

    if( count($kw_ids) == 0 ) {
      return true;
    }

    $safeID = array('tc' => intval($id), 'tcv' => intval($version_id));

    // Firts check if records exist    
    $sql = "/* $debugMsg */
            SELECT keyword_id FROM {$this->tables['testcase_keywords']} 
            WHERE testcase_id = {$safeID['tc']} 
            AND tcversion_id = {$safeID['tcv']} 
            AND keyword_id IN (" . implode(',',$kw_ids) . ")";

    $kwCheck = $this->db->fetchRowsIntoMap($sql,'keyword_id');

    $sql = "/* $debugMsg */" .
           " INSERT INTO {$this->tables['testcase_keywords']} " .
           " (testcase_id,tcversion_id,keyword_id) VALUES ";

    $dummy = array();
    foreach( $kw_ids as $kiwi ) {
      if( !isset($kwCheck[$kiwi]) ) {
        $dummy[] = "($id,$version_id,$kiwi)";
      }
    }

    if( count($dummy) <= 0 ) {
      return;
    }

    // Go ahead
    $sql .= implode(',', $dummy);
    $this->db->exec_query($sql);
 
    // Now AUDIT
    if ( $adt['on'] == self::AUDIT_ON ) {

      // Audit Context
      $tcPath = $this->getPathName( $id );
      $kwOpt = array('cols' => 'id,keyword', 
                     'accessKey' => 'id', 'kwSet' => $kw_ids);
      $keywordSet = tlKeyword::getSimpleSet($this->db,$kwOpt);

      foreach($keywordSet as $elem ) {
        logAuditEvent(TLS("audit_keyword_assigned_tc",$elem['keyword'],
                      $tcPath,$adt['version']), 
                      "ASSIGN",$version_id,"nodes_hierarchy");
      }
    }
      
    return true;
  }




  /*
    function: set's the keywords of the given testcase to the passed keywords

    args :

    returns:

  */
  function setKeywords($id,$version_id,$kw_ids,$audit = self::AUDIT_ON) {

    if( null == $version_id) {

    }

    $result = $this->deleteKeywords($id,$version_id);
    if ($result && sizeof($kw_ids)) {
      $result = $this->addKeywords($id,$version_id,$kw_ids);
    }
    return $result;
  }

  /**
   *
   * mappings is only useful when source_id and target_id do not belong 
   * to same Test Project.
   * Because keywords are defined INSIDE a Test Project, 
   * ID will be different for same keyword
   * in a different Test Project.
   *
   */
  function copyKeywordsTo($source,$dest,$kwMappings,$auditContext=null,$opt=null) {

    $adt = array('on' => self::AUDIT_ON);
    if( isset($dest['version']) ) {
      $adt['version'] = $dest['version'];
    }
    $adt = array_merge($adt,(array)$auditContext);

    $what = array('delete' => true);
    $what = array_merge($what,(array)$opt);

    // Not sure that this delete is needed (@20180610)
    if( $what['delete'] ) {
      $this->deleteKeywords($dest['id'],$dest['version_id'],null,$auditContext);
    }

    $sourceKW = $this->getKeywords($source['id'],$source['version_id']);

    if( !is_null($sourceKW) ) {
      
      // build item id list
      $keySet = array_keys($sourceKW);
      if( null != $kwMappings ) {
        foreach($keySet as $itemPos => $itemID) {
          if( isset($mappings[$itemID]) ) {
            $keySet[$itemPos] = $mappings[$itemID];
          } 
        }        
      }

      $this->addKeywords($dest['id'],$dest['version_id'],$keySet,$adt);
    }

    return true;
  }

  /*
    function:

    args :

    returns:

  */
  function deleteKeywords($tcID,$versionID,$kwID = null,$audit=null) {

    $sql = " DELETE FROM {$this->tables['testcase_keywords']} " .
           " WHERE testcase_id = " . intval($tcID) . 
           " AND tcversion_id = " . intval($versionID);

    $adt = array('on' => self::AUDIT_ON);
    $adt = array_merge($adt,(array)$audit);

    if (!is_null($kwID)) {
      if(is_array($kwID)) {
          $sql .= " AND keyword_id IN (" . implode(',',$kwID) . ")";
          $key4log=$kwID;
      }
      else {
          $sql .= " AND keyword_id = {$kwID}";
          $key4log = array($kwID);
      }
    }
    else {
      $key4log = array_keys((array)$this->get_keywords_map($tcID,$versionID));
    }

    $result = $this->db->exec_query($sql);
    if ($result) {
      $tcInfo = $this->tree_manager->get_node_hierarchy_info($tcID);
      if ($tcInfo && $key4log) {
        foreach($key4log as $key2get) {

          $keyword = tlKeyword::getByID($this->db,$key2get);
          if ($keyword && $adt['on']==self::AUDIT_ON) {
            logAuditEvent(TLS("audit_keyword_assignment_removed_tc",$keyword->name,$tcInfo['name']),
                          "ASSIGN",$tcID,"nodes_hierarchy");
          }
        }
      }
    }

    return $result;
  }


  /**
   *
   */
  function deleteKeywordsByLink($tcID, $tckwLinkID, $audit=null) {
    
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;   
    $safeTCID = intval($tcID); 

    $links = (array)$tckwLinkID;
    $inClause = implode(',',$links);

    $sql = " /* $debugMsg */ 
             SELECT TCKW.tcversion_id, TCKW.keyword_id
             FROM {$this->tables['testcase_keywords']} TCKW
             WHERE TCKW.testcase_id = {$safeTCID}
             AND TCKW.id IN ($inClause) ";


    $rs = $this->db->get_recordset($sql);
    
    foreach($rs as $link) {
      $this->deleteKeywords($safeTCID, $link['tcversion_id'], $link['keyword_id'],$audit);
    }  

  }


  /**
   *
   */
  function getKeywordsAllTCVersions($id,$opt=null) {
    $my['opt'] = array('orderBy' => null);
    $my['opt'] = array_merge($my['opt'],(array)$opt);

    $f2g = ' keyword_id,KW.keyword,KW.notes,' .
           ' testcase_id,tcversion_id ';

    $sql = " SELECT {$f2g}
             FROM {$this->tables['testcase_keywords']} TCKW
             JOIN {$this->tables['keywords']} KW
             ON keyword_id = KW.id ";
             
    $sql .=  " WHERE testcase_id = " . intval($id);

    if (!is_null($my['opt']['orderBy']))
    {
      $sql .= ' ' . $my['opt']['orderBy'];
    }

    $items = $this->db->fetchMapRowsIntoMap($sql,
      'testcase_id','tcversion_id',database::CUMULATIVE);
    return $items;
  }


  // -------------------------------------------------------------------------------
  //                            END Keyword related methods
  // -------------------------------------------------------------------------------

  /*
    function: get_executions
              get information about all executions for a testcase version,
              on a testplan, platform, build.
              Execution results are ordered by execution timestamp.

              Is possible to filter certain executions
              Is possible to choose Ascending/Descending order of results. (order by exec timestamp).

    @used-by execSetResults.php

    args : id: testcase (node id) - can be single value or array.
           version_id: tcversion id (node id) - can be single value or array.
           tplan_id: testplan id
           build_id:    if null -> do not filter by build_id
           platform_id: if null -> do not filter by platform_id
             options: default null, map with options.
                    [exec_id_order] default: 'DESC' - range: ASC,DESC
                    [exec_to_exclude]: default: null -> no filter
                                      can be single value or array, this exec id will be EXCLUDED.


    returns: map
             key: tcversion id
             value: array where every element is a map with following keys

                    name: testcase name
                    testcase_id
                    id: tcversion_id
                    version
                    summary: testcase spec. summary
                    steps: testcase spec. steps
                    expected_results: testcase spec. expected results
                    execution_type: see const.inc.php TESTCASE_EXECUTION_TYPE_ constants
                    importance
                    author_id: tcversion author
                    creation_ts: timestamp of creation
                    updater_id: last updater of specification
                    modification_ts:
                    active: tcversion active status
                    is_open: tcversion open status
                    tester_login
                    tester_first_name
                    tester_last_name
                    tester_id
                    execution_id
                    status: execution status
                    execution_notes
                    execution_ts
                    execution_run_type: see const.inc.php TESTCASE_EXECUTION_TYPE_ constants
                    build_id
                    build_name
                    build_is_active
                    build_is_open
                    platform_id
                    platform_name

  */
  function get_executions($id,$version_id,$tplan_id,$build_id,$platform_id,$options=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
      $my['options'] = array('exec_id_order' => 'DESC', 'exec_to_exclude' => null);
      $my['options'] = array_merge($my['options'], (array)$options);

    $filterKeys = array('build_id','platform_id');
        foreach($filterKeys as $key)
        {
          $filterBy[$key] = '';
          if( !is_null($$key) )
          {
            $itemSet = implode(',', (array)$$key);
            $filterBy[$key] = " AND e.{$key} IN ({$itemSet}) ";
          }
        }

    // --------------------------------------------------------------------
    if( is_array($id) )
    {
      $tcid_list = implode(",",$id);
      $where_clause = " WHERE NHA.parent_id IN ({$tcid_list}) ";
    }
    else
    {
      $where_clause = " WHERE NHA.parent_id = {$id} ";
    }

    if( is_array($version_id) )
    {
        $versionid_list = implode(",",$version_id);
        $where_clause  .= " AND tcversions.id IN ({$versionid_list}) ";
    }
    else
    {
        if($version_id != self::ALL_VERSIONS)
        {
          $where_clause  .= " AND tcversions.id = {$version_id} ";
        }
    }

    if( !is_null($my['options']['exec_to_exclude']) )
    {

        if( is_array($my['options']['exec_to_exclude']))
        {
            if(count($my['options']['exec_to_exclude']) > 0 )
            {
              $exec_id_list = implode(",",$my['options']['exec_to_exclude']);
                $where_clause  .= " AND e.id NOT IN ({$exec_id_list}) ";
              }
        }
        else
        {
            $where_clause  .= " AND e.id <> {$exec_id_list} ";
        }
    }
    // --------------------------------------------------------------------
    // 20090517 - to manage deleted users i need to change:
    //            users.id AS tester_id => e.tester_id AS tester_id
    // 20090214 - franciscom - e.execution_type -> e.execution_run_type
    //
    $sql="/* $debugMsg */ SELECT NHB.name,NHA.parent_id AS testcase_id, tcversions.*,
          users.login AS tester_login,
          users.first AS tester_first_name,
          users.last AS tester_last_name,
        e.tester_id AS tester_id,
          e.id AS execution_id, e.status,e.tcversion_number,
          e.notes AS execution_notes, e.execution_ts, e.execution_type AS execution_run_type,
          e.build_id AS build_id,
          b.name AS build_name, b.active AS build_is_active, b.is_open AS build_is_open,
            e.platform_id,p.name AS platform_name
        FROM {$this->tables['nodes_hierarchy']} NHA
          JOIN {$this->tables['nodes_hierarchy']} NHB ON NHA.parent_id = NHB.id
          JOIN {$this->tables['tcversions']} tcversions ON NHA.id = tcversions.id
          JOIN {$this->tables['executions']} e ON NHA.id = e.tcversion_id
                                               AND e.testplan_id = {$tplan_id}
                                               {$filterBy['build_id']} {$filterBy['platform_id']}
          JOIN {$this->tables['builds']}  b ON e.build_id=b.id
          LEFT OUTER JOIN {$this->tables['users']} users ON users.id = e.tester_id
          LEFT OUTER JOIN {$this->tables['platforms']} p ON p.id = e.platform_id
          $where_clause
          ORDER BY NHA.node_order ASC, NHA.parent_id ASC, execution_id {$my['options']['exec_id_order']}";


    $recordset = $this->db->fetchArrayRowsIntoMap($sql,'id');
    return($recordset ? $recordset : null);
  }


  /*
    function: get_last_execution

    args :


    returns: map:
             key: tcversions.id
             value: map with following keys:
                    execution_id
                    status: execution status
                    execution_type: see const.inc.php TESTCASE_EXECUTION_TYPE_ constants
                    name: testcase name
                    testcase_id
                    tsuite_id: parent testsuite of testcase (node id)
                    id: tcversion id (node id)
                    version
                    summary: testcase spec. summary
                    steps: testcase spec. steps
                    expected_results: testcase spec. expected results
                    execution_type: type of execution desired
                    importance
                    author_id: tcversion author
                    creation_ts: timestamp of creation
                    updater_id: last updater of specification.
                    modification_ts
                    active: tcversion active status
                    is_open: tcversion open status
                    tester_login
                    tester_first_name
                    tester_last_name
                    tester_id
                    execution_notes
                    execution_ts
                    execution_run_type:  how the execution was really done
                    build_id
                    build_name
                    build_is_active
                    build_is_open

  */
  function get_last_execution($id,$version_id,$tplan_id,$build_id,$platform_id,$options=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $resultsCfg = config_get('results');
    $status_not_run = $resultsCfg['status_code']['not_run'];

    $filterKeys = array('build_id','platform_id');
    foreach($filterKeys as $key) {
      $filterBy[$key] = '';
      if( !is_null($$key) ) {
        $itemSet = implode(',', (array)$$key);
        $filterBy[$key] = " AND e.{$key} IN ({$itemSet}) ";
      }
    }

    $where_clause_1 = '';
    $where_clause_2 = '';
    $add_columns='';
    $add_groupby='';
    $cumulativeMode=0;
    $group_by = '';

    // getNoExecutions: 1 -> if testcase/version_id has not been executed return anyway
    //                       standard return structure.
    //                  0 -> default
    //
    // groupByBuild: 0 -> default, get last execution on ANY BUILD, then for a testcase/version_id
    //                    only a record will be present on return struture.
    //                    GROUP BY must be done ONLY BY tcversion_id
    //
    //               1 -> get last execution on EACH BUILD.
    //                    GROUP BY must be done BY tcversion_id,build_id
    //
    $localOptions=array('getNoExecutions' => 0, 'groupByBuild' => 0, 'getSteps' => 1,
                        'getStepsExecInfo' => 0, 'output' => 'std');
    if(!is_null($options) && is_array($options)) {
      $localOptions=array_merge($localOptions,$options);
    }

    if( is_array($id) ) {
      $tcid_list = implode(",",$id);
      $where_clause = " WHERE NHA.parent_id IN ({$tcid_list}) ";
    } else {
      $where_clause = " WHERE NHA.parent_id = {$id} ";
    }

    if( is_array($version_id) ) {
        $versionid_list = implode(",",$version_id);
        $where_clause_1 = $where_clause . " AND NHA.id IN ({$versionid_list}) ";
        $where_clause_2 = $where_clause . " AND tcversions.id IN ({$versionid_list}) ";
    } else {
      if($version_id != self::ALL_VERSIONS) {
        $where_clause_1 = $where_clause . " AND NHA.id = {$version_id} ";
        $where_clause_2 = $where_clause . " AND tcversions.id = {$version_id} ";
      }
    }

    // This logic (is mine - franciscom) must be detailed better!!!!!
    $group_by = ' GROUP BY tcversion_id ';
    $add_fields = ', e.tcversion_id AS tcversion_id';
    if( $localOptions['groupByBuild'] ) {
      $add_fields .= ', e.build_id';
      $group_by .= ', e.build_id';
      $cumulativeMode = 1;

      // Hummm!!! I do not understand why this can be needed
      $where_clause_1 = $where_clause;
      $where_clause_2 = $where_clause;
    }

    // we may be need to remove tcversion filter ($set_group_by==false)
    // $add_field = $set_group_by ? ', e.tcversion_id AS tcversion_id' : '';
    // $add_field = $localOptions['groupByBuild'] ? '' : ', e.tcversion_id AS tcversion_id';
    // $where_clause_1 = $localOptions['groupByBuild'] ? $where_clause :  $where_clause_1;
    // $where_clause_2 = $localOptions['groupByBuild'] ? $where_clause : $where_clause_2;

    // get list of max exec id, to be used filter in next query
    // Here we can get:
    // a) one record for each tcversion_id (ignoring build)
    // b) one record for each tcversion_id,build
    //

    // 20101212 - franciscom - may be not the best logic but ...
    $where_clause_1 = ($where_clause_1 == '') ? $where_clause : $where_clause_1;
    $where_clause_2 = ($where_clause_2 == '') ? $where_clause : $where_clause_2;

    $sql="/* $debugMsg */ " .
         " SELECT COALESCE(MAX(e.id),0) AS execution_id {$add_fields}" .
         " FROM {$this->tables['nodes_hierarchy']} NHA " .
         " JOIN {$this->tables['executions']} e ON NHA.id = e.tcversion_id AND e.testplan_id = {$tplan_id} " .
         " {$filterBy['build_id']} {$filterBy['platform_id']}" .
         " AND e.status IS NOT NULL " .
         " $where_clause_1 {$group_by}";

    $recordset = $this->db->fetchColumnsIntoMap($sql,'execution_id','tcversion_id');
    $and_exec_id='';
    if( !is_null($recordset) && count($recordset) > 0) {
      $the_list = implode(",", array_keys($recordset));
      if($the_list != '') {
        if( count($recordset) > 1 ) {
          $and_exec_id = " AND e.id IN ($the_list) ";
        } else {
          $and_exec_id = " AND e.id = $the_list ";
        }
      }
    }

    $executions_join = 
      " JOIN {$this->tables['executions']} e ON NHA.id = e.tcversion_id " .
      " AND e.testplan_id = {$tplan_id} {$and_exec_id} {$filterBy['build_id']} " .
      " {$filterBy['platform_id']} ";

    if( $localOptions['getNoExecutions'] ) {
       $executions_join = " LEFT OUTER " . $executions_join;
    } else {
       // @TODO understand if this condition is really needed - 20090716 - franciscom
       $executions_join .= " AND e.status IS NOT NULL ";
    }

    //
    switch ($localOptions['output']) {
      case 'timestamp':
        $sql= "/* $debugMsg */ SELECT e.id AS execution_id, " .
              " COALESCE(e.status,'{$status_not_run}') AS status, " .
              " e.execution_ts, e.build_id,e.tcversion_number," .
              " FROM {$this->tables['nodes_hierarchy']} NHA" .
              " JOIN {$this->tables['nodes_hierarchy']} NHB ON NHA.parent_id = NHB.id" .
              " JOIN {$this->tables['tcversions']} tcversions ON NHA.id = tcversions.id" .
              " {$executions_join}" .
              " $where_clause_2" .
              " ORDER BY NHB.parent_id ASC, NHA.parent_id ASC, execution_id DESC";
      break;

      case 'std':
      default:
        $sql= "/* $debugMsg */ SELECT e.id AS execution_id, " .
              " COALESCE(e.status,'{$status_not_run}') AS status, " .
              " e.execution_type AS execution_run_type,e.execution_duration, " .
              " NHB.name,NHA.parent_id AS testcase_id, NHB.parent_id AS tsuite_id," .
              " tcversions.id,tcversions.tc_external_id,tcversions.version,tcversions.summary," .
              " tcversions.preconditions," .
              " tcversions.importance,tcversions.author_id," .
              " tcversions.creation_ts,tcversions.updater_id,tcversions.modification_ts,tcversions.active," .
              " tcversions.is_open,tcversions.execution_type," .
              " tcversions.estimated_exec_duration,tcversions.status AS wkfstatus," .
              " users.login AS tester_login,users.first AS tester_first_name," .
              " users.last AS tester_last_name, e.tester_id AS tester_id," .
              " e.notes AS execution_notes, e.execution_ts, e.build_id,e.tcversion_number," .
              " builds.name AS build_name, builds.active AS build_is_active, builds.is_open AS build_is_open," .
              " e.platform_id,p.name AS platform_name" .
              " FROM {$this->tables['nodes_hierarchy']} NHA" .
              " JOIN {$this->tables['nodes_hierarchy']} NHB ON NHA.parent_id = NHB.id" .
              " JOIN {$this->tables['tcversions']} tcversions ON NHA.id = tcversions.id" .
              " {$executions_join}" .
              " LEFT OUTER JOIN {$this->tables['builds']} builds ON builds.id = e.build_id" .
              "                 AND builds.testplan_id = {$tplan_id}" .
              " LEFT OUTER JOIN {$this->tables['users']} users ON users.id = e.tester_id " .
              " LEFT OUTER JOIN {$this->tables['platforms']} p ON p.id = e.platform_id" .
              " $where_clause_2" .
              " ORDER BY NHB.parent_id ASC, NHA.node_order ASC, NHA.parent_id ASC, execution_id DESC";
      break;
    }

    $recordset = $this->db->fetchRowsIntoMap($sql,'id',$cumulativeMode);

    // Multiple Test Case Steps Feature
    if( !is_null($recordset) && $localOptions['getSteps'] ) {
      $xx = null;
      if( $localOptions['getStepsExecInfo'] && 
          ($this->cfg->execution->steps_exec_notes_default == 'latest' ||
           $this->cfg->execution->steps_exec_status_default == 'latest') 
        ) {
        $tg = current($recordset);
        $xx = $this->getStepsExecInfo($tg['execution_id']);
      }

      $itemSet = array_keys($recordset);
      foreach( $itemSet as $sdx) {
        $step_set = $this->get_steps($recordset[$sdx]['id']);
        if($localOptions['getStepsExecInfo']) {
          if(!is_null($step_set)) {
            $key_set = array_keys($step_set);
            foreach($key_set as $kyx) {
              $step_set[$kyx]['execution_notes'] = '';
              $step_set[$kyx]['execution_status'] = '';
         
              if( isset($xx[$step_set[$kyx]['id']]) ) {
                if($this->cfg->execution->steps_exec_notes_default == 'latest') {
                  $step_set[$kyx]['execution_notes'] = 
                     $xx[$step_set[$kyx]['id']]['notes'];
                }

                if($this->cfg->execution->steps_exec_status_default == 'latest') {
                  $step_set[$kyx]['execution_status'] = 
                     $xx[$step_set[$kyx]['id']]['status'];
                }
              }
            }
          }
        }
        $recordset[$sdx]['steps'] = $step_set;
      }

    }

    // ghost Test Case processing in summary & preconditions
    if( !is_array($id) ) {
      if(!is_null($recordset)) {
        $key2loop = array_keys($recordset);
        foreach( $key2loop as $accessKey) {
          $this->renderGhost($recordset[$accessKey]);
          $this->renderVariables($recordset[$accessKey]);
          $this->renderSpecialTSuiteKeywords($recordset[$accessKey]);
          $this->renderImageAttachments($id,$recordset[$accessKey]);

          // render exec variables only if we have just one build
          if( intval($build_id) > 0 && intval($tplan_id) >0 ) {
            $context = array('tplan_id' => $tplan_id, 'build_id' => $build_id);
            $this->renderBuildExecVars($context,$recordset[$accessKey]);
          }
        }
        reset($recordset);
      }
    }

    return($recordset ? $recordset : null);
  }



  /*
    function: exportTestCaseDataToXML

    args :

      $tcversion_id: can be testcase::LATEST_VERSION

    returns:


  */
  function exportTestCaseDataToXML($tcase_id,$tcversion_id,$tproject_id=null,
                                   $bNoXMLHeader = false,$optExport = array()) {
    static $reqMgr;
    static $keywordMgr;
    static $cfieldMgr;
    if( is_null($reqMgr) ) {
      $reqMgr = new requirement_mgr($this->db);
      $keywordMgr = new tlKeyword();
      $cfieldMgr = new cfield_mgr($this->db);
    }

    // Useful when you need to get info but do not have tcase id
    $tcase_id = intval((int)($tcase_id));
    $tcversion_id = intval((int)($tcversion_id));
    if( $tcase_id <= 0 && $tcversion_id > 0) {
      $info = $this->tree_manager->get_node_hierarchy_info($tcversion_id);
      $tcase_id = $info['parent_id'];
    }

    $opt = array('getPrefix' => false);
    if(!isset($optExport['EXTERNALID']) || $optExport['EXTERNALID']) {
      $opt = array('getPrefix' => (isset($optExport['ADDPREFIX']) && $optExport['ADDPREFIX']));
    }
    $tc_data = $this->get_by_id($tcase_id,$tcversion_id,null,$opt);

    $testCaseVersionID = $tc_data[0]['id'];
    if (!$tproject_id) {
      $tproject_id = $this->getTestProjectFromTestCase($tcase_id);
    }

    if (isset($optExport['CFIELDS']) && $optExport['CFIELDS']) {
      $cfMap = $this->get_linked_cfields_at_design($tcase_id,$testCaseVersionID,null,null,$tproject_id);

      // ||yyy||-> tags,  {{xxx}} -> attribute
      // tags and attributes receive different treatment on exportDataToXML()
      //
      // each UPPER CASE word in this map KEY, MUST HAVE AN OCCURENCE on $elemTpl
      // value is a key inside $tc_data[0]
      //
      if( !is_null($cfMap) && count($cfMap) > 0 ) {
        $tc_data[0]['xmlcustomfields'] = $cfieldMgr->exportValueAsXML($cfMap);
      }
    }

    if (isset($optExport['KEYWORDS']) && $optExport['KEYWORDS']) {
      // 20180610 - Will export Only for latest version?
      $keywords = $this->getKeywords($tcase_id,$testCaseVersionID);
      if(!is_null($keywords)) {
        $xmlKW = "<keywords>" . $keywordMgr->toXMLString($keywords,true) . 
                 "</keywords>";
        $tc_data[0]['xmlkeywords'] = $xmlKW;
      }
    }

    if (isset($optExport['REQS']) && $optExport['REQS']) {
      
      // $requirements = $reqMgr->get_all_for_tcase($tcase_id);
      // Need to get only for test case version
      
      $req4version = $reqMgr->getGoodForTCVersion($tcversion_id);

      if( !is_null($req4version) && count($req4version) > 0 ) {
        $tc_data[0]['xmlrequirements'] = 
          exportDataToXML($req4version,$this->XMLCfg->req->root,
                          $this->XMLCfg->req->elemTPL,$this->XMLCfg->req->decode,true);
      }
    }
	
	  if (isset($optExport['ATTACHMENTS']) && $optExport['ATTACHMENTS']) {

		  $attachments=null;
	
  		$library = $this->attachmentRepository->getAttachmentInfosFor($tcversion_id,$this->attachmentTableName,'id');
  	  
  		// get all attachments content and encode it in base64	  
  		if ($library) {
  			foreach ($library as $file) {
  				$aID = $file["id"];
  				$content = $this->attachmentRepository->getAttachmentContent($aID, $file);
  				
  				if ($content != null) {
  					$attachments[$aID]["id"] = $aID;
  					$attachments[$aID]["name"] = $file["file_name"];
  					$attachments[$aID]["file_type"] = $file["file_type"];
  					$attachments[$aID]["file_size"] = $file["file_size"];
  					$attachments[$aID]["title"] = $file["title"];
  					$attachments[$aID]["date_added"] = $file["date_added"];
  					$attachments[$aID]["content"] = base64_encode($content);
  				}
  			}
  	  }
	  
		  if( !is_null($attachments) && count($attachments) > 0 ) {
        $tc_data[0]['xmlattachments'] = 
          exportDataToXML($attachments,$this->XMLCfg->att->root,
                          $this->XMLCfg->att->elemTPL,$this->XMLCfg->att->decode,true);
      }
    }

    // -----------------------------------------------------------------------------
    if(!isset($optExport['TCSTEPS']) || $optExport['TCSTEPS']) {
      $stepRootElem = "<steps>{{XMLCODE}}</steps>";
      $stepTemplate = "\n" . '<step>' . "\n" .
                      "\t<step_number><![CDATA[||STEP_NUMBER||]]></step_number>\n" .
                      "\t<actions><![CDATA[||ACTIONS||]]></actions>\n" .
                      "\t<expectedresults><![CDATA[||EXPECTEDRESULTS||]]></expectedresults>\n" .
                      "\t<execution_type><![CDATA[||EXECUTIONTYPE||]]></execution_type>\n" .
                      "</step>\n";
      $stepInfo = array("||STEP_NUMBER||" => "step_number", "||ACTIONS||" => "actions",
                        "||EXPECTEDRESULTS||" => "expected_results","||EXECUTIONTYPE||" => "execution_type" );

      $stepSet = $tc_data[0]['steps'];
      $xmlsteps = exportDataToXML($stepSet,$stepRootElem,$stepTemplate,$stepInfo,true);
      $tc_data[0]['xmlsteps'] = $xmlsteps;
    }
    // --------------------------------------------------------------------------------


    $tc_data[0]['xmlrelations'] = null;
    $addElemTpl = '';

    // When exporting JUST a test case, exporting relations can be used
    // as documentation.
    // When exporting a Test Suite, format can be different as has been done
    // with requirements.
    // While ideas become clear , i prefer to add this option for testing
    if( isset($optExport['RELATIONS']) &&  $optExport['RELATIONS'] ) {
      $xmlRel = null;
      $addElemTpl .= "||RELATIONS||";
      $relSet = $this->getRelations($tcase_id);
      if($relSet['num_relations'] > 0 ) {
        foreach($relSet['relations'] as $rk => $rv) {
          $xmlRel .= $this->exportRelationToXML($rv,$relSet['item']);
        }
        $tc_data[0]['xmlrelations'] = $xmlRel;
      }
    }

    $rootElem = "{{XMLCODE}}";
    if (isset($optExport['ROOTELEM'])) {
      $rootElem = $optExport['ROOTELEM'];
    }
    $elemTpl = "\n".'<testcase internalid="{{TESTCASE_ID}}" name="{{NAME}}">' . "\n" .
               "\t<node_order><![CDATA[||NODE_ORDER||]]></node_order>\n";


    // Export the Execution Order in a TestPlan for each testcase
    if(isset($optExport['EXEC_ORDER'])) {
      $elemTpl .= "\t<exec_order><![CDATA[||EXEC_ORDER||]]></exec_order>\n";

      $tc_data[0]['exec_order'] = $optExport['EXEC_ORDER'];
    }

    // Export assigned_users into "Export Test Plan" XML content
    // table with all users assigned to an execution
    if(isset($optExport['ASSIGNED_USER'])) {
  	  $elemTpl .= "\t<assigned_users>\n";
  	  foreach ($optExport['ASSIGNED_USER'] as $key => $username){
  		$elemTpl .= "\t\t<assigned_user><![CDATA[".$username."]]></assigned_user>\n";
  	  }
  	  $elemTpl .= "\t</assigned_users>\n";
    }

    if(!isset($optExport['EXTERNALID']) || $optExport['EXTERNALID']) {
      $elemTpl .= "\t<externalid><![CDATA[||EXTERNALID||]]></externalid>\n";
    }

	  if(!isset($optExport['ADDPREFIX']) || $optExport['ADDPREFIX']) {
      $elemTpl .= "\t<fullexternalid><![CDATA[||FULLEXTERNALID||]]></fullexternalid>\n";
    }

    $optElem = '';
    if( !isset($optExport['TCSUMMARY']) || $optExport['TCSUMMARY'] ) {
      $optElem .= "\t<summary><![CDATA[||SUMMARY||]]></summary>\n";
    }
    if( !isset($optExport['TCPRECONDITIONS']) || $optExport['TCPRECONDITIONS'] ) {
      $optElem .= "\t<preconditions><![CDATA[||PRECONDITIONS||]]></preconditions>\n";
    }

    $elemTpl .= "\t<version><![CDATA[||VERSION||]]></version>\n" .
                $optElem .
                "\t<execution_type><![CDATA[||EXECUTIONTYPE||]]></execution_type>\n" .
                "\t<importance><![CDATA[||IMPORTANCE||]]></importance>\n" .
                "\t<estimated_exec_duration>||ESTIMATED_EXEC_DURATION||</estimated_exec_duration>\n" .
                "\t<status>||STATUS||</status>\n" .
                "\t<is_open>||ISOPEN||</is_open>\n" .
                "\t<active>||ACTIVE||</active>\n" .
                "||STEPS||\n" .
                "||KEYWORDS||||CUSTOMFIELDS||||REQUIREMENTS||||ATTACHMENTS||{$addElemTpl}</testcase>\n";


      // ||yyy||-> tags,  {{xxx}} -> attribute
      // tags and attributes receive different treatment on exportDataToXML()
      //
      // each UPPER CASE word in this map KEY, MUST HAVE AN OCCURENCE on $elemTpl
      // value is a key inside $tc_data[0]
      //
      $info = array("{{TESTCASE_ID}}" => "testcase_id",
                    "{{NAME}}" => "name",
                    "||NODE_ORDER||" => "node_order",
                    "||EXEC_ORDER||" => "exec_order",
                    "||EXTERNALID||" => "tc_external_id",
                    "||FULLEXTERNALID||" => "fullExternalID",
                    "||VERSION||" => "version",
                    "||SUMMARY||" => "summary",
                    "||PRECONDITIONS||" => "preconditions",
                    "||EXECUTIONTYPE||" => "execution_type",
                    "||IMPORTANCE||" => "importance",
                    "||ESTIMATED_EXEC_DURATION||" => "estimated_exec_duration",
                    "||STATUS||" => "status",
                    "||ISOPEN||" => "is_open",
                    "||ACTIVE||" => "active",
                    "||STEPS||" => "xmlsteps",
                    "||KEYWORDS||" => "xmlkeywords",
                    "||CUSTOMFIELDS||" => "xmlcustomfields",
                    "||REQUIREMENTS||" => "xmlrequirements",
                    "||ATTACHMENTS||" => "xmlattachments",
                    "||RELATIONS||" => "xmlrelations");


      $xmlTC = exportDataToXML($tc_data,$rootElem,$elemTpl,$info,$bNoXMLHeader);
      return $xmlTC;
  }


  /*
    function: get_version_exec_assignment
              get information about user that has been assigned
              test case version for execution on a testplan

    args : tcversion_id: test case version id
           tplan_id



    returns: map
             key: tcversion_id
             value: map with following keys:
                    tcversion_id
                    feature_id: identifies row on table testplan_tcversions.


                    user_id:  user that has reponsibility to execute this tcversion_id.
                              null/empty string is nodoby has been assigned

                    type    type of assignment.
                            1 -> testcase_execution.
                            See assignment_types tables for updated information
                            about other types of assignemt available.

                    status  assignment status
                            See assignment_status tables for updated information.
                            1 -> open
                            2 -> closed
                            3 -> completed
                            4 -> todo_urgent
                            5 -> todo

                    assigner_id: who has assigned execution to user_id.



  */
  function get_version_exec_assignment($tcversion_id, $tplan_id, $build_id)
  {
    // 20110622 - asimon - TICKET 4600: Blocked execution of testcases
    $sql =  "SELECT T.tcversion_id AS tcversion_id,T.id AS feature_id,T.platform_id, " .
            "       UA.user_id,UA.type,UA.status,UA.assigner_id ".
            " FROM {$this->tables['testplan_tcversions']}  T " .
            " LEFT OUTER JOIN {$this->tables['user_assignments']}  UA ON UA.feature_id = T.id " .
            " WHERE T.testplan_id={$tplan_id} AND UA.build_id = {$build_id} " .
            " AND   T.tcversion_id = {$tcversion_id} " .
            " AND   (UA.type=" . $this->assignment_types['testcase_execution']['id'] .
            "        OR UA.type IS NULL) ";


    // $recordset = $this->db->fetchRowsIntoMap($sql,'tcversion_id');
    $recordset = $this->db->fetchMapRowsIntoMap($sql,'tcversion_id','platform_id',database::CUMULATIVE);

    return $recordset;
  }


  /**
   * get_assigned_to_user()
   * Given a user and a tesplan id, get all test case version id linked to
   * test plan, that has been assigned for execution to user.
   *
   * @param int user_id
   *
   * @param mixed tproject_id list of test project id to search.
   *                          int or array
   *
   * @param array [tplan_id] list of test plan id to search.
   *                         null => all test plans
   *
   * @param map [options] mode='full_path'
   *                         testcase name full path will be returned
   *                         Only available when acces_keys ='testplan_testcase'
   *
   *                      access_keys
   *                         possible values: 'testplan_testcase','testcase_testplan'
   *                         changes access key in result map of maps.
   *                         if not defined or null -> 'testplan_testcase'
   *
   * @param map [filters] 'tplan_status' => 'active','inactive','all'
   *
   *
   * @return map key: (test plan id or test case id depending on options->access_keys,
   *                   default is test plan).
   *
   *             value: map key: (test case id or test plan id depending on options->access_keys,
   *                              default is test case).
   *                        value:
   *
   * @internal revision
   */
  function get_assigned_to_user($user_id,$tproject_id,$tplan_id=null,$options=null, $filters=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $my['opt'] = array('mode' => null, 'order_by' => '',
                       'access_keys' => 'testplan_testcase');
    $my['opt'] = array_merge($my['opt'],(array)$options);

    $my['filters'] = array( 'tplan_status' => 'all');
    $my['filters'] = array_merge($my['filters'], (array)$filters);

    // to load assignments for all users OR one given user
    $user_sql = ($user_id != TL_USER_ANYBODY) ? " AND UA.user_id = {$user_id} " : "";

    $filters = "";
    $access_key=array('testplan_id','testcase_id');

    $sql="/* $debugMsg */ SELECT TPROJ.id as testproject_id,TPTCV.testplan_id,TPTCV.tcversion_id, " .
           " TCV.version,TCV.tc_external_id, NHTC.id AS testcase_id, NHTC.name, TPROJ.prefix, " .
           " UA.creation_ts ,UA.deadline_ts, UA.user_id as user_id, " .
           " COALESCE(PLAT.name,'') AS platform_name, COALESCE(PLAT.id,0) AS platform_id, " .
           " (TPTCV.urgency * TCV.importance) AS priority, BUILDS.name as build_name, " .
           " BUILDS.id as build_id " .
           " FROM {$this->tables['user_assignments']} UA " .
           " JOIN {$this->tables['testplan_tcversions']} TPTCV ON TPTCV.id = UA.feature_id " .
           " JOIN {$this->tables['tcversions']} TCV ON TCV.id=TPTCV.tcversion_id " .
           " JOIN {$this->tables['nodes_hierarchy']} NHTCV ON NHTCV.id = TCV.id " .
           " JOIN {$this->tables['nodes_hierarchy']} NHTC ON NHTC.id = NHTCV.parent_id " .
           " JOIN {$this->tables['nodes_hierarchy']} NHTPLAN ON  NHTPLAN.id=TPTCV.testplan_id " .
           " JOIN {$this->tables['testprojects']} TPROJ ON  TPROJ.id = NHTPLAN.parent_id " .
           " JOIN {$this->tables['testplans']} TPLAN ON  TPLAN.id = TPTCV.testplan_id " .
           " JOIN {$this->tables['builds']} BUILDS ON  BUILDS.id = UA.build_id " .
           " LEFT OUTER JOIN {$this->tables['platforms']} PLAT ON  PLAT.id = TPTCV.platform_id " .
           " WHERE UA.type={$this->assignment_types['testcase_execution']['id']} " .
           " {$user_sql} " .
           " AND TPROJ.id IN (" . implode(',', array($tproject_id)) .") " ;

    if( !is_null($tplan_id) )
    {
      $filters .= " AND TPTCV.testplan_id IN (" . implode(',',$tplan_id) . ") ";
    }

    if (isset($my['filters']['build_id']))
    {
      $filters .= " AND UA.build_id = {$my['filters']['build_id']} ";
    }

    switch($my['filters']['tplan_status'])
    {
      case 'all':
      break;

      case 'active':
        $filters .= " AND TPLAN.active = 1 ";
      break;

      case 'inactive':
        $filters .= " AND TPLAN.active = 0 ";
      break;
    }

    if(isset($my['filters']['build_status']))
    {
      switch($my['filters']['build_status'])
      {
        case 'open':
          $filters .= " AND BUILDS.is_open = 1 ";
        break;

        case 'closed':
          $filters .= " AND BUILDS.is_open = 0 ";
        break;

        case 'all':
        default:
        break;
      }
    }

    $sql .= $filters;

    if( isset($my['opt']['access_keys']) )
    {
      switch($my['opt']['access_keys'])
      {
        case 'testplan_testcase':
        break;

        case 'testcase_testplan':
          $access_key=array('testcase_id','testplan_id');
        break;
      }
    }

    $sql .= $my['opt']['order_by'];

    $rs = $this->db->fetchMapRowsIntoMap($sql,$access_key[0],$access_key[1],database::CUMULATIVE);

    if( !is_null($rs) )
    {
      if( !is_null($my['opt']['mode']) )
      {
        switch($my['opt']['mode'])
        {
          case 'full_path':
            if($my['opt']['access_keys'] == 'testplan_testcase')
            {
              $tcaseSet=null;
              $main_keys = array_keys($rs);

              foreach($main_keys as $maccess_key)
              {
                $sec_keys = array_keys($rs[$maccess_key]);
                foreach($sec_keys as $saccess_key)
                {
                  // is enough I process first element
                  $item = $rs[$maccess_key][$saccess_key][0];
                  if(!isset($tcaseSet[$item['testcase_id']]))
                  {
                    $tcaseSet[$item['testcase_id']]=$item['testcase_id'];
                  }
                }
              }

              $path_info = $this->tree_manager->get_full_path_verbose($tcaseSet);

              // Remove test project piece and convert to string
              $flat_path=null;
              foreach($path_info as $tcase_id => $pieces)
              {
                unset($pieces[0]);
                // 20100813 - asimon - deactivated last slash on path
                // to remove it from test suite name in "tc assigned to user" tables
                $flat_path[$tcase_id]=implode('/',$pieces);
              }
              $main_keys = array_keys($rs);

              foreach($main_keys as $idx)
              {
                $sec_keys = array_keys($rs[$idx]);
                foreach($sec_keys as $jdx)
                {
                  $third_keys = array_keys($rs[$idx][$jdx]);
                  foreach($third_keys as $tdx)
                  {
                    $fdx = $rs[$idx][$jdx][$tdx]['testcase_id'];
                    $rs[$idx][$jdx][$tdx]['tcase_full_path']=$flat_path[$fdx];
                  }
                }
              }
            }
          break;
        }
      }
    }

    return $rs;
  }



  /*
    function: update_active_status

    args : id: testcase id
           tcversion_id
           active_status: 1 -> active / 0 -> inactive

    returns: 1 -> everything ok.
             0 -> some error
    rev:
  */
  function update_active_status($id,$tcversion_id,$active_status) {

    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $sql =  " /* $debugMsg */ UPDATE {$this->tables['tcversions']} 
              SET active={$active_status}
              WHERE id = {$tcversion_id} ";

    $result = $this->db->exec_query($sql);
    return $result ? 1: 0;
  }

  /*
    function: update_order

    args : id: testcase id
           order

    returns: -

  */
  function update_order($id,$order)
  {
      $result=$this->tree_manager->change_order_bulk(array($order => $id));
    return $result ? 1: 0;
  }


  /*
    function: update_external_id

    args : id: testcase id
           external_id

    returns: -

  */
  function update_external_id($id,$external_id)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $sql =  "/* $debugMsg */ UPDATE {$this->tables['tcversions']} " .
        " SET tc_external_id={$external_id} " .
        " WHERE id IN (" .
        " SELECT id FROM {$this->tables['nodes_hierarchy']} WHERE parent_id={$id} ) ";

      $result=$this->db->exec_query($sql);
    return $result ? 1: 0;
  }


  /**
   * Copy attachments from source testcase to target testcase
   *
   **/
  function copy_attachments($source_id,$target_id) {
    return $this->attachmentRepository->copyAttachments($source_id,$target_id,$this->attachmentTableName);
  }


  /**
   * copyReqAssignmentTo
   * copy requirement assignments for $from test case id to $to test case id
   *
   * mappings is only useful when source_id and target_id do not belong to same Test Project.
   *
   *
   */
  function copyReqAssignmentTo($from,$to,$mappings,$userID) {
    static $req_mgr;
    if( is_null($req_mgr) ) {
      $req_mgr=new requirement_mgr($this->db);
    }

    $itemSet=$req_mgr->get_all_for_tcase($from);
    if( !is_null($itemSet) ) {
      $loop2do=count($itemSet);
      for($idx=0; $idx < $loop2do; $idx++) {
        if( isset($mappings[$itemSet[$idx]['id']]) ) {
          $items[$idx]=$mappings[$itemSet[$idx]['id']];
        } else {
          $items[$idx]=$itemSet[$idx]['id'];
        }
      }
      $req_mgr->assign_to_tcase($items,$to,$userID);
    }
  }

  /**
   *
   *
   */
  private function getShowViewerActions($mode) {
    // fine grain control of operations
    $viewerActions= new stdClass();
    $viewerActions->edit='no';
    $viewerActions->delete_testcase='no';
    $viewerActions->delete_version='no';
    $viewerActions->deactivate='no';
    $viewerActions->create_new_version='no';
    $viewerActions->export='no';
    $viewerActions->move='no';
    $viewerActions->copy='no';
    $viewerActions->add2tplan='no';
    $viewerActions->freeze='no';

    switch ($mode)
    {
      case 'editOnExec':
        $viewerActions->edit='yes';
      break;

      case 'editDisabled':
      break;

      default:
        foreach($viewerActions as $key => $value)
        {
          $viewerActions->$key='yes';
        }
      break;
    }
    return $viewerActions;
  }

  /**
     * given an executio id delete execution and related data.
     *
     */
    function deleteExecution($executionID)
    {
        $whereClause = " WHERE execution_id = {$executionID} ";
    $sql = array("DELETE FROM {$this->tables['execution_bugs']} {$whereClause} ",
                 "DELETE FROM {$this->tables['cfield_execution_values']} {$whereClause} ",
                 "DELETE FROM {$this->tables['executions']} WHERE id = {$executionID}" );

    foreach ($sql as $the_stm)
    {
      $result = $this->db->exec_query($the_stm);
      if (!$result)
      {
        break;
      }
    }
    }




  // ---------------------------------------------------------------------------------------
  // Custom field related functions
  // ---------------------------------------------------------------------------------------

  /*
    function: get_linked_cfields_at_design
              Get all linked custom fields that must be available at design time.
              Remember that custom fields are defined at system wide level, and
              has to be linked to a testproject, in order to be used.

    args: id: testcase id
        tcversion_id: testcase version id  ---- BUGID 3431
          [parent_id]: node id of parent testsuite of testcase.
                       need to understand to which testproject the testcase belongs.
                       this information is vital, to get the linked custom fields.
                       Presence /absence of this value changes starting point
                       on procedure to build tree path to get testproject id.

                       null -> use testcase_id as starting point.
                       !is_null -> use this value as starting point.

          [$filters]:default: null

                     map with keys:

                     [show_on_execution]: default: null
                                          1 -> filter on field show_on_execution=1
                                               include ONLY custom fields that can be viewed
                                               while user is execution testcases.

                                          0 or null -> don't filter

                     [show_on_testplan_design]: default: null
                                                1 -> filter on field show_on_testplan_design=1
                                                     include ONLY custom fields that can be viewed
                                                     while user is designing test plan.

                                                0 or null -> don't filter

                     [location] new concept used to define on what location on screen
                            custom field will be designed.
                            Initally used with CF available for Test cases, to
                            implement pre-requisites.
                                  null => no filtering


                     More comments/instructions on cfield_mgr->get_linked_cfields_at_design()

    returns: map/hash
             key: custom field id
             value: map with custom field definition and value assigned for choosen testcase,
                    with following keys:

                    id: custom field id
                    name
                    label
                    type: custom field type
                    possible_values: for custom field
                    default_value
                    valid_regexp
                    length_min
                    length_max
                    show_on_design
                    enable_on_design
                    show_on_execution
                    enable_on_execution
                    display_order
                    value: value assigned to custom field for this testcase
                           null if for this testcase custom field was never edited.

                    node_id: testcase id
                             null if for this testcase, custom field was never edited.


    rev :
         20070302 - check for $id not null, is not enough, need to check is > 0

  */
  function get_linked_cfields_at_design($id,$tcversion_id,$parent_id=null,$filters=null,$tproject_id = null)
  {
    if (!$tproject_id)
    {
      $tproject_id = $this->getTestProjectFromTestCase($id,$parent_id);
    }

    $cf_map = $this->cfield_mgr->get_linked_cfields_at_design($tproject_id,
                                                              self::ENABLED,$filters,'testcase',$tcversion_id);
    return $cf_map;
  }



  /*
    function: getTestProjectFromTestCase

    args: id: testcase id
          [parent_id]: node id of parent testsuite of testcase.
                       need to understand to which testproject the testcase belongs.
                       this information is vital, to get the linked custom fields.
                       Presence /absence of this value changes starting point
                       on procedure to build tree path to get testproject id.

                       null -> use testcase_id as starting point.
                       !is_null -> use this value as starting point.
  */
  function getTestProjectFromTestCase($id,$parent_id)
  {
    $the_path = $this->tree_manager->get_path( (!is_null($id) && $id > 0) ? $id : $parent_id);
    $path_len = count($the_path);
    $tproject_id = ($path_len > 0)? $the_path[0]['parent_id'] : $parent_id;

    return $tproject_id;
  }

  /*
    function: get_testproject
              Given a testcase id get node id of testproject to which testcase belongs.
    args :id: testcase id

    returns: testproject id
  */
  function get_testproject($id)
  {
    $a_path = $this->tree_manager->get_path($id);
    return ($a_path[0]['parent_id']);
  }


  /*
    function: html_table_of_custom_field_inputs
              Return html code, implementing a table with custom fields labels
              and html inputs, for choosen testcase.
              Used to manage user actions on custom fields values.


    args: $id: IMPORTANT:
               we can receive 0 in this arguments and THERE IS NOT A problem
               if parent_id arguments has a value.
               Because argument id or parent_id are used to understand what is
               testproject where test case belong, in order to get custom fields
               assigned/linked to test project.


          [parent_id]: node id of parent testsuite of testcase.
                       need to undertad to which testproject the testcase belongs.
                       this information is vital, to get the linked custom fields.
                       Presence /absence of this value changes starting point
                       on procedure to build tree path to get testproject id.

                       null -> use testcase_id as starting point.
                       !is_null -> use this value as starting point.

          [$scope]: 'design' -> use custom fields that can be used at design time (specification)
                    'execution' -> use custom fields that can be used at execution time.

          [$name_suffix]: must start with '_' (underscore).
                          Used when we display in a page several items
                          example:
                                  during test case execution, several test cases
                                  during testplan design (assign test case to testplan).

                          that have the same custom fields.
                          In this kind of situation we can use the item id as name suffix.

          [link_id]: default null
                     scope='testplan_design'.
                     link_id=testplan_tcversions.id this value is also part of key
                     to access CF values on new table that hold values assigned
                     to CF used on the 'tesplan_design' scope.

                     scope='execution'
                     link_id=execution id

                     BUGID 3431
                     scope='design'
                     link_id=tcversion id


          [tplan_id]: default null
                      used when scope='execution' and YOU NEED to get input with value
                      related to link_id

          [tproject_id]: default null
                         used to speedup feature when this value is available.


    returns: html string

    rev: 20080811 - franciscom - BUGID 1650 (REQ)

  BUGID 3431 -

  */
  function html_table_of_custom_field_inputs($id,$parent_id=null,$scope='design',$name_suffix='',
                                             $link_id=null,$tplan_id=null,
                                             $tproject_id = null,$filters=null, $input_values = null)
  {
    $cf_smarty = '';
    $cf_scope=trim($scope);
    $method_name='get_linked_cfields_at_' . $cf_scope;

    switch($cf_scope)
    {
      case 'testplan_design':
        $cf_map = $this->$method_name($id,$parent_id,null,$link_id,null,$tproject_id);
      break;

      case 'design':
        $cf_map = $this->$method_name($id,$link_id,$parent_id,$filters,$tproject_id);
      break;

      case 'execution':
        $cf_map = $this->$method_name($id,$parent_id,null,$link_id,$tplan_id,$tproject_id);
      break;

    }

    if(!is_null($cf_map))
    {
      $cf_smarty = $this->cfield_mgr->html_table_inputs($cf_map,$name_suffix,$input_values);
    }
    return $cf_smarty;
  }

  /**
   * Just a Wrapper to improve, sometimes code layout
   */
  function htmlTableOfCFValues($id,$context,$filters=null,$formatOptions=null) {

    // $context
    $ctx = array('scope' => 'design', 'execution_id' => null,
                 'testplan_id' => null,'tproject_id' => null,'link_id' => null);

    $ctx = array_merge($ctx,$context);
    extract($ctx);

    return $this->html_table_of_custom_field_values($id,$scope,$filters,$execution_id,
              $testplan_id,$tproject_id,$formatOptions,$link_id);

  }  


  /*
    function: html_table_of_custom_field_values
              Return html code, implementing a table with custom fields labels
              and custom fields values, for choosen testcase.
              You can think of this function as some sort of read only version
              of html_table_of_custom_field_inputs.


    args: $id: Very Important!!!
               scope='design'    -> this is a testcase id
               scope='execution' -> this is a testcase VERSION id
               scope='testplan_design' -> this is a testcase VERSION id

          [$scope]: 'design' -> use custom fields that can be used at design time (specification)
                    'execution' -> use custom fields that can be used at execution time.
                    'testplan_design'

          [$filters]:default: null

                     map with keys:

                     [show_on_execution]: default: null
                                          1 -> filter on field show_on_execution=1
                                               include ONLY custom fields that can be viewed
                                               while user is execution testcases.

                                          0 or null -> don't filter

                     [show_on_testplan_design]: default: null
                                                1 -> filter on field show_on_testplan_design=1
                                                     include ONLY custom fields that can be viewed
                                                     while user is designing test plan.

                                                0 or null -> don't filter

                     [location] new concept used to define on what location on screen
                            custom field will be designed.
                            Initally used with CF available for Test cases, to
                            implement pre-requisites.
                                  null => no filtering

                     More comments/instructions on cfield_mgr->get_linked_cfields_at_design()


          [$execution_id]: null -> get values for all executions availables for testcase
                           !is_null -> only get values or this execution_id

          [$testplan_id]: null -> get values for any tesplan to with testcase is linked
                          !is_null -> get values only for this testplan.

          [$tproject_id]
          [$formatOptions]
          [$link_id]: default null
                     scope='testplan_design'.
                     link_id=testplan_tcversions.id this value is also part of key
                     to access CF values on new table that hold values assigned
                     to CF used on the 'tesplan_design' scope.

             BUGID 3431
             scope='design'.
                     link_id=tcversion_id




    returns: html string

  */
  function html_table_of_custom_field_values($id,$scope='design',$filters=null,
                                             $execution_id=null,
                                             $testplan_id=null,$tproject_id = null,
                                             $formatOptions=null,$link_id=null)
  {
    $label_css_style = ' class="labelHolder" ';
    $value_css_style = ' ';

    $add_table=true;
    $table_style='';
    if( !is_null($formatOptions) )
    {
      $label_css_style = isset($formatOptions['label_css_style']) ?
                         $formatOptions['label_css_style'] : $label_css_style;
      $value_css_style = isset($formatOptions['value_css_style']) ?
                         $formatOptions['value_css_style'] : $value_css_style;

      $add_table = isset($formatOptions['add_table']) ? $formatOptions['add_table'] : true;
      $table_style = isset($formatOptions['table_css_style']) ? $formatOptions['table_css_style'] : $table_style;
    }

    $cf_smarty = '';

    $location=null; // no filter
    $filterKey='location';
    if( isset($filters[$filterKey]) && !is_null($filters[$filterKey]) )
    {
      $location = $filters[$filterKey];
    }

    switch($scope)
    {
      case 'design':
        $cf_map = $this->get_linked_cfields_at_design($id,$link_id,null,$filters,$tproject_id);
      break;

      case 'testplan_design':
        $cf_map = $this->get_linked_cfields_at_testplan_design($id,null,$filters,$link_id,
                                                               $testplan_id,$tproject_id);
      break;

      case 'execution':
        $cf_map = $this->get_linked_cfields_at_execution($id,null,$filters,$execution_id,
                                                         $testplan_id,$tproject_id,$location);
      break;
    }

    if(!is_null($cf_map))
    {
      foreach($cf_map as $cf_id => $cf_info)
      {
        // if user has assigned a value, then node_id is not null
        if(isset($cf_info['node_id']) ||
           $this->cfg->cfield->show_custom_fields_without_value)
        {
          // true => do not create input in audit log
          $label = str_replace(TL_LOCALIZE_TAG,'',lang_get($cf_info['label'],null,true));

          $cf_smarty .= "<tr><td {$label_css_style}> " .  htmlspecialchars($label) . ":</td>" .
                        "<td {$value_css_style}>" .
                        $this->cfield_mgr->string_custom_field_value($cf_info,$id) .
                        "</td></tr>\n";
        }
      }

      if( (trim($cf_smarty) != "") && $add_table)
      {
        $cf_smarty = "<table {$table_style}>" . $cf_smarty . "</table>";
      }
    }
    return $cf_smarty;
  } // function end


  /*
    function: get_linked_cfields_at_execution


    args: $id
          [$parent_id]
          [$show_on_execution]: default: null
                                1 -> filter on field show_on_execution=1
                                0 or null -> don't filter
                                //@TODO - 20090718 - franciscom
                                // this filter has any sense ?
                                // review and remove if needed


          [$execution_id]: null -> get values for all executions availables for testcase
                           !is_null -> only get values or this execution_id

          [$testplan_id]: null -> get values for any tesplan to with testcase is linked
                          !is_null -> get values only for this testplan.

          [$tproject_id]:

    returns: hash
             key: custom field id
             value: map with custom field definition, with keys:

                    id: custom field id
        name
        label
        type
        possible_values
        default_value
        valid_regexp
        length_min
        length_max
        show_on_design
        enable_on_design
        show_on_execution
        enable_on_execution
        display_order

  */
  function get_linked_cfields_at_execution($id,$parent_id=null,$show_on_execution=null,
                                           $execution_id=null,$testplan_id=null,
                                           $tproject_id = null, $location=null)
  {
    $thisMethod=__FUNCTION__;
    if (!$tproject_id)
    {
      $tproject_id = $this->getTestProjectFromTestCase($id,$parent_id);
    }

    // VERY IMPORTANT WARNING:
    // I'm setting node type to test case, but $id is the tcversion_id, because
    // execution data is related to tcversion NO testcase
    //
    $cf_map = $this->cfield_mgr->$thisMethod($tproject_id,self::ENABLED,'testcase',
                                             $id,$execution_id,$testplan_id,'id',
                                             $location);
    return $cf_map;
  }


  /*
    function: copy_cfields_design_values
              Get all cfields linked to any testcase of this testproject
              with the values presents for $from_id, testcase we are using as
              source for our copy.

    args: source: map('id' => testcase id, 'tcversion_id' => testcase id)
          destination: map('id' => testcase id, 'tcversion_id' => testcase id)

    returns: -


  */
  function copy_cfields_design_values($source,$destination) {
    // Get all cfields linked to any testcase of this test project
    // with the values presents for $from_id, testcase we are using as
    // source for our copy
    $cfmap_from = $this->get_linked_cfields_at_design($source['id'],$source['tcversion_id']);

    $cfield=null;
    if( !is_null($cfmap_from) ) {
      foreach($cfmap_from as $key => $value) {
        $cfield[$key]=array("type_id"  => $value['type'], "cf_value" => $value['value']);
      }
    }
    $this->cfield_mgr->design_values_to_db($cfield,$destination['tcversion_id'],null,'tcase_copy_cfields');
  }


  /*
    function: get_linked_cfields_at_testplan_design


    args: $id
          [$parent_id]

          [$filters]:default: null

                     map with keys:

                     [show_on_execution]: default: null
                                          1 -> filter on field show_on_execution=1
                                               include ONLY custom fields that can be viewed
                                               while user is execution testcases.

                                          0 or null -> don't filter

                     [show_on_testplan_design]: default: null
                                                1 -> filter on field show_on_testplan_design=1
                                                     include ONLY custom fields that can be viewed
                                                     while user is designing test plan.

                                                0 or null -> don't filter

                     More comments/instructions on cfield_mgr->get_linked_cfields_at_design()

          [$link_id]:

          [$testplan_id]: null -> get values for any tesplan to with testcase is linked
                          !is_null -> get values only for this testplan.

    returns: hash
             key: custom field id
             value: map with custom field definition, with keys:

                    id: custom field id
        name
        label
        type
        possible_values
        default_value
        valid_regexp
        length_min
        length_max
        show_on_design
        enable_on_design
        show_on_execution
        enable_on_execution
        display_order


  */
  function get_linked_cfields_at_testplan_design($id,$parent_id=null,$filters=null,
                                                 $link_id=null,$testplan_id=null,$tproject_id = null)
  {
    if (!$tproject_id)
    {
      $tproject_id = $this->getTestProjectFromTestCase($id,$parent_id);
    }

    // Warning:
    // I'm setting node type to test case, but $id is the tcversion_id, because
    // link data is related to tcversion NO testcase
    //
    $cf_map = $this->cfield_mgr->get_linked_cfields_at_testplan_design($tproject_id,self::ENABLED,'testcase',
                                                                       $id,$link_id,$testplan_id);
    return $cf_map;
  }


  /**
   * returns map with key: verbose location (see custom field class $locations
   *                  value: array with fixed key 'location'
   *                         value: location code
   *
   */
  function buildCFLocationMap()
  {
    $ret = $this->cfield_mgr->buildLocationMap('testcase');
    return $ret;
  }


  /**
   * given a set of test cases, will return a map with
   * test suites name that form test case path to root test suite.
   *
   *                  example:
   *
   *                  communication devices [ID 4]
   *                      |__ Subspace channels [ID 20]
   *                             |
   *                             |__ TestCase100
   *                             |
   *                             |__ short range devices [ID 21]
   *                                      |__ TestCase1
   *                                      |__ TestCase2
   *
   * if test case set: TestCase100,TestCase1
   *
   *   4  Communications
   *  20  Communications/Subspace channels
   *  21  Communications/Subspace channels/short range devices
   *
   *
   * returns map with key: test suite id
   *                  value: test suite path to root
   *
   *
   */
  function getPathLayered($tcaseSet, $opt=null) {

    static $tsuiteMgr;
    if( !$tsuiteMgr ) {
      $tsuiteMgr = new testsuite($this->db);
    }

    $xtree=null;
    
    $options = array('getTSuiteKeywords' => false);
    $options = array_merge($options, (array)$opt);
   
    $idSet = (array)$tcaseSet;
    foreach($idSet as $item) {
      $path_info = $this->tree_manager->get_path($item);
      $testcase = end($path_info);

      // This check is useful when you have several test cases with same parent test suite
      if( !isset($xtree[$testcase['parent_id']]['value']) ) {
        $level=0;
   
        foreach($path_info as $elem) {
          $level++;
          $prefix = isset($xtree[$elem['parent_id']]['value']) ? ($xtree[$elem['parent_id']]['value'] . '/') : '';
          if( $elem['node_table'] == 'testsuites' ) {
            $xtree[$elem['id']]['value'] = $prefix . $elem['name'];
            $xtree[$elem['id']]['level']=$level;
            $xtree[$elem['id']]['data_management'] = null;
          }
        }
      }

      if( null != $xtree && $options['getTSuiteKeywords'] ) {
        $tsSet = array_keys($xtree);
        $opkw = array('output' => 'kwname');
        $fkw = array('keywordsLikeStart' => '@#');
        $iset = (array) $tsuiteMgr->getTSuitesFilteredByKWSet($tsSet,$opkw,$fkw);

        foreach( $iset as $tsuite_id => $elem ) {
          foreach( $elem as $e ) {
            if( null != $e ) {
              $xtree[$tsuite_id]['data_management'][$e['keyword']] = $e['dyn_string'];              
            }
          }  
        }
      } 
    }
    return $xtree;
  } // getPathLayered($tcaseSet)



  /**
   *
   *
   */
  function getPathTopSuite($tcaseSet)
  {
    $xtmas=null;
    foreach($tcaseSet as $item)
    {
      $path_info = $this->tree_manager->get_path($item);
      $top = current($path_info);
      $xtmas[$item] = array( 'name' => $top['name'], 'id' => $top['id']);
    }
    return $xtmas;
  } // getPathTopSuite($tcaseSet)



    /*
    function: getByPathName
              pathname format
              Test Project Name::SuiteName::SuiteName::...::Test case name

    args: $pathname
    returns: hash
  */
  function getByPathName($pathName,$pathSeparator='::')
  {
      $recordset = null;
    $retval=null;

        // First get root -> test project name and leaf => test case name
      $parts = explode($pathSeparator,$pathName);
      $partsQty = count($parts);
      $tprojectName = $parts[0];
      $tsuiteName = $parts[$partsQty-2];
      $tcaseName = end($parts);

      // get all testcases on test project with this name and parent test suite
        $recordset = $this->get_by_name($tcaseName, $tsuiteName ,$tprojectName);
        if( !is_null($recordset) && count($recordset) > 0 )
        {
          foreach($recordset as $value)
          {
            $dummy = $this->tree_manager->get_full_path_verbose($value['id']);
            $sx = implode($pathSeparator,current($dummy)) . $pathSeparator . $tcaseName;
            if( strcmp($pathName,$sx ) == 0 )
            {
              $retval = $value;
              break;
            }
          }
        }
      return $retval;
  }

  /**
   *
   *
   */
  function buildDirectWebLink($base_href,$id,$tproject_id=null)
  {
    list($external_id,$prefix,$glue,$tc_number) = $this->getExternalID($id,$tproject_id);

    $dl = $base_href . 'linkto.php?tprojectPrefix=' . urlencode($prefix) .
          '&item=testcase&id=' . urlencode($external_id);
    return $dl;
  }

  /**
   *
   *
   */
  function getExternalID($id,$tproject_id=null,$prefix=null)
  {
    static $root;
    static $tcase_prefix;

    if( is_null($prefix) )
    {
      if( is_null($root) ||  ($root != $tproject_id) )
      {
        list($tcase_prefix,$root) = $this->getPrefix($id,$tproject_id);
      }
    }
    else
    {
      $tcase_prefix = $prefix;
    }
    $info = $this->get_last_version_info($id, array('output' => 'minimun'));
    $external = $info['tc_external_id'];
    $identity = $tcase_prefix . $this->cfg->testcase->glue_character . $external;
    return array($identity,$tcase_prefix,$this->cfg->testcase->glue_character,$external);
  }


    /**
   * returns just name, tc_external_id, version.
   * this info is normally enough for user feednack.
   *
   * @param int $id test case id
   * @param array $accessVersionBy 'number'   => contains test case version number
   *                 'id'     => contains test case version ID
   *
   * @return array with one element with keys: name,version,tc_external_id
     */
  function get_basic_info($id,$accessVersionBy)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $sql = "/* $debugMsg */ " .
           " SELECT NH_TCASE.id, NH_TCASE.name, TCV.version, TCV.tc_external_id, " .
           " TCV.id AS tcversion_id, TCV.status " .
           " FROM {$this->tables['nodes_hierarchy']} NH_TCASE " .
           " JOIN {$this->tables['nodes_hierarchy']} NH_TCV ON NH_TCV.parent_id = NH_TCASE.id" .
           " JOIN {$this->tables['tcversions']} TCV ON  TCV.id = NH_TCV.id ";

    $accessBy = array('number' => 'version', 'id' => 'id');
    $where_clause = '';
    foreach( $accessBy as $key => $field)
    {
      if( isset($accessVersionBy[$key]) )
      {
          $where_clause = " WHERE TCV.{$field} = " . intval($accessVersionBy[$key]) ;
          break;
      }
    }
    $where_clause .= " AND NH_TCASE .id = {$id} ";
    $sql .= $where_clause;
    $result = $this->db->get_recordset($sql);
    return $result;
  }



  /**
     *
     *
     */
  function create_step($tcversion_id,$step_number,$actions,$expected_results,
                         $execution_type=TESTCASE_EXECUTION_TYPE_MANUAL)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $ret = array();

    // defensive programming
    $dummy = $this->db->prepare_int($execution_type);
    $dummy = (isset($this->execution_types[$dummy])) ? $dummy : TESTCASE_EXECUTION_TYPE_MANUAL;

    $item_id = $this->tree_manager->new_node($tcversion_id,$this->node_types_descr_id['testcase_step']);

    $k2e = array('actions','expected_results');
    $item = new stdClass();
    $item->actions = $actions;
    $item->expected_results = $expected_results;
    $this->CKEditorCopyAndPasteCleanUp($item,$k2e); 

    $sql = "/* $debugMsg */ INSERT INTO {$this->tables['tcsteps']} " .
           " (id,step_number,actions,expected_results,execution_type) " .
           " VALUES({$item_id},{$step_number},'" . 
           $this->db->prepare_string($item->actions) . "','" .
           $this->db->prepare_string($item->expected_results) . "', " . 
           $this->db->prepare_int($dummy) . ")";

    $result = $this->db->exec_query($sql);
    $ret = array('msg' => 'ok', 'id' => $item_id, 'status_ok' => 1, 
                 'sql' => $sql);
    if (!$result)
    {
      $ret['msg'] = $this->db->error_msg();
      $ret['status_ok']=0;
      $ret['id']=-1;
    }
    return $ret;
  }

  /**
   *
   *
   *  @internal revisions
   */
  function get_steps($tcversion_id,$step_number=0,$options=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $my['options'] = array( 'fields2get' => '*', 'accessKey' => null,
                            'renderGhostSteps' => true, 'renderImageInline' => true);

    $my['options'] = array_merge($my['options'], (array)$options);

    $step_filter = $step_number > 0 ? " AND step_number = {$step_number} " : "";
    $safe_tcversion_id = $this->db->prepare_int($tcversion_id);

    // build
    $f2g = "TCSTEPS.{$my['options']['fields2get']}";
    if($my['options']['fields2get'] != '*')
    {
      $sof = explode(',',$my['options']['fields2get']);
      foreach($sof as &$ele)
      {
        $ele = 'TCSTEPS.' . $ele;
      }
      $f2g = implode(',',$sof);
    }
    $sql = "/* $debugMsg */ " .
           " SELECT {$f2g} " .
           " FROM {$this->tables['tcsteps']} TCSTEPS " .
           " JOIN {$this->tables['nodes_hierarchy']} NH_STEPS " .
           " ON NH_STEPS.id = TCSTEPS.id " .
           " WHERE NH_STEPS.parent_id = {$safe_tcversion_id} {$step_filter} ORDER BY step_number";

    if( is_null($my['options']['accessKey']) )
    {
      $result = $this->db->get_recordset($sql);
    }
    else
    {
      $result = $this->db->fetchRowsIntoMap($sql,$my['options']['accessKey']);
    }

    if(!is_null($result) && $my['options']['renderGhostSteps'])
    {
      $this->renderGhostSteps($result);
    }

    if(!is_null($result) && $my['options']['renderImageInline'])
    {
      // for attachments we need main entity => Test case
      $tcvnode = $this->tree_manager->get_node_hierarchy_info($tcversion_id);
      $k2l = count($result);
      $gaga = array('actions','expected_results');
      for($idx=0; $idx < $k2l; $idx++)
      {
        $this->renderImageAttachments($tcvnode['parent_id'],$result[$idx],$gaga);
      }
    }

    return $result;
  }

  /**
   *
   */
  function getStepsSimple($tcversion_id,$step_number=0,$options=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $my['options'] = array('fields2get' => 'TCSTEPS.*', 'accessKey' => null,
                           'renderGhostSteps' => true, 'renderImageInline' => true);
    $my['options'] = array_merge($my['options'], (array)$options);

    $step_filter = $step_number > 0 ? " AND step_number = {$step_number} " : "";
    $safe_tcversion_id = $this->db->prepare_int($tcversion_id);

    $sql = "/* $debugMsg */ " .
           " SELECT {$my['options']['fields2get']} " .
           " FROM {$this->tables['tcsteps']} TCSTEPS " .
           " JOIN {$this->tables['nodes_hierarchy']} NH_STEPS " .
           " ON NH_STEPS.id = TCSTEPS.id " .
           " WHERE NH_STEPS.parent_id = {$safe_tcversion_id} {$step_filter} ORDER BY step_number";

    if( is_null($my['options']['accessKey']) )
    {
      $result = $this->db->get_recordset($sql);
    }
    else
    {
      $result = $this->db->fetchRowsIntoMap($sql,$my['options']['accessKey']);
    }

    return $result;
  }



  /**
     *
     *
     */
  function get_step_by_id($step_id)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $sql = "/* $debugMsg */ " .
           " SELECT TCSTEPS.* FROM {$this->tables['tcsteps']} TCSTEPS " .
           " JOIN {$this->tables['nodes_hierarchy']} NH_STEPS " .
           " ON NH_STEPS.id = TCSTEPS.id " .
           " WHERE TCSTEPS.id = {$step_id} ";
    $result = $this->db->get_recordset($sql);

    return is_null($result) ? $result : $result[0];
  }


  function get_step_numbers($tcversion_id)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $sql = "/* $debugMsg */ " .
           " SELECT TCSTEPS.id, TCSTEPS.step_number FROM {$this->tables['tcsteps']} TCSTEPS " .
           " JOIN {$this->tables['nodes_hierarchy']} NH_STEPS " .
           " ON NH_STEPS.id = TCSTEPS.id " .
           " WHERE NH_STEPS.parent_id = {$tcversion_id} ORDER BY step_number";

    $result = $this->db->fetchRowsIntoMap($sql,'step_number');
    return $result;
  }



  /**
     *
     *
     */
  function get_latest_step_number($tcversion_id)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $sql = "/* $debugMsg */ " .
           " SELECT MAX(TCSTEPS.step_number) AS max_step FROM {$this->tables['tcsteps']} TCSTEPS " .
           " JOIN {$this->tables['nodes_hierarchy']} NH_STEPS " .
           " ON NH_STEPS.id = TCSTEPS.id " .
           " WHERE NH_STEPS.parent_id = {$tcversion_id} ";

    $result = $this->db->get_recordset($sql);
    $max_step = (!is_null($result) && isset($result[0]['max_step']) )? $result[0]['max_step'] : 0;
    return $max_step;
  }


  /**
     *
     *
     *  @internal Revisions
     *  20100821 - franciscom - $step_id can be an array
     */
  function delete_step_by_id($step_id)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $sql = array();
    $whereClause = " WHERE id IN (" . implode(',',(array)$step_id) . ")";

    $sqlSet[] = "/* $debugMsg */ DELETE FROM {$this->tables['tcsteps']} {$whereClause} ";
    $sqlSet[] = "/* $debugMsg */ DELETE FROM {$this->tables['nodes_hierarchy']} " .
                " {$whereClause} AND node_type_id = " .
                $this->node_types_descr_id['testcase_step'];

    foreach($sqlSet as $sql)
    {
      $this->db->exec_query($sql);
    }
  }


  /**
     *
     *
     * @internal revision
     * BUGID 4207 - MSSQL
     */
  function set_step_number($step_number)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

        foreach($step_number as $step_id => $value)
        {
          $sql = "/* $debugMsg */ UPDATE {$this->tables['tcsteps']} " .
               " SET step_number = {$value} WHERE id = {$step_id} ";
          $this->db->exec_query($sql);
        }

  }

  /**
     *
     *
     */
  function update_step($step_id,$step_number,$actions,$expected_results,$execution_type)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $ret = array();

    $k2e = array('actions','expected_results');
    $item = new stdClass();
    $item->actions = $actions;
    $item->expected_results = $expected_results;
    $this->CKEditorCopyAndPasteCleanUp($item,$k2e); 

    $sql = "/* $debugMsg */ UPDATE {$this->tables['tcsteps']} " .
           " SET step_number=" . $this->db->prepare_int($step_number) . "," .
           " actions='" . $this->db->prepare_string($item->actions) . "', " .
           " expected_results='" . 
           $this->db->prepare_string($item->expected_results) . "', " .
           " execution_type = " . $this->db->prepare_int($execution_type)  .
           " WHERE id = " . $this->db->prepare_int($step_id);

    $result = $this->db->exec_query($sql);
    $ret = array('msg' => 'ok', 'status_ok' => 1, 'sql' => $sql);
    if (!$result)
    {
      $ret['msg'] = $this->db->error_msg();
      $ret['status_ok']=0;
    }
    return $ret;
  }

  /**
   * get by external id
   *
   * @param mixed filters:
   */
  function get_by_external($external_id, $parent_id,$filters=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $recordset = null;

    $my = array();
    $my['filters'] = array('version' => null);
    $my['filters'] = array_merge($my['filters'], (array)$filters);

    $sql = "/* $debugMsg */ " .
           " SELECT DISTINCT NH_TCASE.id,NH_TCASE.name,NH_TCASE_PARENT.id AS parent_id," .
           " NH_TCASE_PARENT.name AS tsuite_name, TCV.tc_external_id " .
           " FROM {$this->tables['nodes_hierarchy']} NH_TCASE, " .
           " {$this->tables['nodes_hierarchy']} NH_TCASE_PARENT, " .
           " {$this->tables['nodes_hierarchy']} NH_TCVERSIONS," .
           " {$this->tables['tcversions']}  TCV  " .
           " WHERE NH_TCVERSIONS.id=TCV.id " .
           " AND NH_TCVERSIONS.parent_id=NH_TCASE.id " .
           " AND NH_TCASE_PARENT.id=NH_TCASE.parent_id " .
           " AND NH_TCASE.node_type_id = {$this->my_node_type} " .
           " AND TCV.tc_external_id=$external_id ";

    $add_filters = ' ';
    foreach($my['filters'] as $field => $value)
    {
      switch($my['filters'])
      {
        case 'version':
        if( !is_null($value) )
        {
          $add_filters .= ' AND TCV.version = intval($value) ';
        }
      }
    }

    $sql .= $add_filters;
    $sql .= " AND NH_TCASE_PARENT.id = {$parent_id}" ;
    $recordset = $this->db->fetchRowsIntoMap($sql,'id');
    return $recordset;
  }


  /**
   * for a given set of test cases, search on the ACTIVE version set, 
   * and returns for each test case,
   * a map with: the corresponding MAX(version number), other info
   *
   * @param mixed $id: test case id can be an array
   * @param map $filters OPTIONAL - now only 'cfields' key is supported
   * @param map $options OPTIONAL
   *
   */
  function get_last_active_version($id,$filters=null,$options=null) {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $recordset = null;
    $itemSet = implode(',',(array)$id);

    $my = array();
    $my['filters'] = array( 'cfields' => null);
    $my['filters'] = array_merge($my['filters'], (array)$filters);

    $my['options'] = array( 'max_field' => 'tcversion_id', 'access_key' => 'tcversion_id');
    $my['options'] = array_merge($my['options'], (array)$options);



    switch($my['options']['max_field']) {
      case 'version':
        $maxClause = " SELECT MAX(TCV.version) AS version ";
        $selectClause = " SELECT TCV.version AS version ";
      break;

      case 'tcversion_id':
        $maxClause = " SELECT MAX(TCV.id) AS tcversion_id ";
        $selectClause = " SELECT TCV.id AS tcversion_id ";
      break;
    }

    $sql = "/* $debugMsg */ " .
           " {$maxClause}, NH_TCVERSION.parent_id AS testcase_id " .
           " FROM {$this->tables['tcversions']} TCV " .
           " JOIN {$this->tables['nodes_hierarchy']} NH_TCVERSION " .
           " ON NH_TCVERSION.id = TCV.id AND TCV.active=1 " .
           " AND NH_TCVERSION.parent_id IN ({$itemSet}) " .
           " GROUP BY NH_TCVERSION.parent_id " .
           " ORDER BY NH_TCVERSION.parent_id ";

    $recordset = $this->db->fetchRowsIntoMap($sql,'tcversion_id');

    $cfSelect = '';
    $cfJoin = '';
    $cfQuery = '';
    $cfQty = 0;

    if( !is_null($recordset) ) {
      $or_clause = '';
      $cf_query = '';

      if( !is_null($my['filters']['cfields']) ) {
        $cf_hash = &$my['filters']['cfields'];
        $cfQty = count($cf_hash);
        $countmain = 1;

        // Build custom fields filter
        // do not worry!! it seems that filter criteria is OR, 
        // but really is an AND,
        // OR is needed to do a simple query.
        // with processing on recordset becomes an AND
        foreach ($cf_hash as $cf_id => $cf_value) {
          if ( $countmain != 1 ) {
            $cfQuery .= " OR ";
          }
          if (is_array($cf_value)) {
            $count = 1;

            foreach ($cf_value as $value) {
              if ($count > 1) {
                $cfQuery .= " AND ";
              }
              $cfQuery .=  " ( CFDV.value LIKE '%{$value}%' AND CFDV.field_id = {$cf_id} )";
              $count++;
            }
          }
          else {
            $cfQuery .=  " ( CFDV.value LIKE '%{$cf_value}%' AND CFDV.field_id = {$cf_id} )";
          }
          $countmain++;
        }
        $cfSelect = ", CFDV.field_id, CFDV.value ";
        $cfJoin = " JOIN {$this->tables['cfield_design_values']} CFDV ON CFDV.node_id = TCV.id ";
        $cfQuery = " AND ({$cfQuery}) ";
      }

      $keySet = implode(',',array_keys($recordset));
      $sql = "/* $debugMsg */ " .
             " {$selectClause}, NH_TCVERSION.parent_id AS testcase_id, " .
             " TCV.version,TCV.execution_type,TCV.importance,TCV.status {$cfSelect} " .
             " FROM {$this->tables['tcversions']} TCV " .
             " JOIN {$this->tables['nodes_hierarchy']} NH_TCVERSION " .
             " ON NH_TCVERSION.id = TCV.id {$cfJoin} " .
             " AND NH_TCVERSION.id IN ({$keySet}) {$cfQuery}";

      $recordset = $this->db->fetchRowsIntoMap($sql,$my['options']['access_key'],database::CUMULATIVE);

      // now loop over result,
      // Processing has to be done no matter value of cfQty
      // (not doing this has produced in part TICKET 4704,4708)
      // entries whose count() < number of custom fields has to be removed
      if( !is_null($recordset) ) {
        $key2loop = array_keys($recordset);
        if($cfQty > 0) {
          foreach($key2loop as $key) {
            if( count($recordset[$key]) < $cfQty) {
              unset($recordset[$key]);
            }
            else {
              $recordset[$key] = $recordset[$key][0];
              unset($recordset[$key]['value']);
              unset($recordset[$key]['field_id']);
            }
          }
        }
        else {
          foreach($key2loop as $key) {
            $recordset[$key] = $recordset[$key][0];
          }
        }

        if( count($recordset) <= 0 ) {
          $recordset = null;
        }
      }
    }

    return $recordset;
  }


  /**
   *
   */
  function filter_tcversions_by_exec_type($tcversion_id,$exec_type,$options=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
      $recordset = null;
      $itemSet = implode(',',(array)$tcversion_id);

      $my['options'] = array( 'access_key' => 'tcversion_id');
      $my['options'] = array_merge($my['options'], (array)$options);



    $sql = "/* $debugMsg */ " .
         " SELECT TCV.id AS tcversion_id, NH_TCVERSION.parent_id AS testcase_id, TCV.version " .
         " FROM {$this->tables['tcversions']} TCV " .
         " JOIN {$this->tables['nodes_hierarchy']} NH_TCVERSION " .
         " ON NH_TCVERSION.id = TCV.id AND TCV.execution_type={$exec_type}" .
         " AND NH_TCVERSION.id IN ({$itemSet}) ";

    $recordset = $this->db->fetchRowsIntoMap($sql,$my['options']['access_key']);
      return $recordset;
  }

  /**
   *
   *
   */
  function filter_tcversions($tcversion_id,$filters,$options=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
      $recordset = null;
      $itemSet = implode(',',(array)$tcversion_id);

      $my['options'] = array( 'access_key' => 'tcversion_id');
      $my['options'] = array_merge($my['options'], (array)$options);

    $sql = "/* $debugMsg */ " .
         " SELECT TCV.id AS tcversion_id, NH_TCVERSION.parent_id AS testcase_id, TCV.version " .
         " FROM {$this->tables['tcversions']} TCV " .
         " JOIN {$this->tables['nodes_hierarchy']} NH_TCVERSION " .
         " ON NH_TCVERSION.id = TCV.id ";

    if ( !is_null($filters) )
    {
      foreach($filters as $key => $value)
      {
        if( !is_null($value) )
        {
          $sql .= " AND TCV.{$key}={$value} "; // Hmmm some problems coming with strings
        }
      }
    }
    $sql .= " AND NH_TCVERSION.id IN ({$itemSet}) ";

    $recordset = $this->db->fetchRowsIntoMap($sql,$my['options']['access_key']);
      return $recordset;
  }



  /**
   * given a test case version id, the provided steps will be analized in order
   * to update whole steps/expected results structure for test case version.
   * This can result in some step removed, other updated and other new created.
   *
   */
  function update_tcversion_steps($tcversion_id,$steps)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    // delete all current steps (if any exists)
    // Attention:
    // After addition of test case steps feature, a test case version 
    // can be root of a subtree that contains the steps.
    // Remember we are using (at least on Postgres FK => we need to delete 
    // in a precise order.

    $stepSet = $this->get_steps($tcversion_id,0,
                        array('fields2get' => 'id', 'accessKey' => 'id'));
    if( count($stepSet) > 0 )
    {
      $this->delete_step_by_id(array_keys($stepSet));
    }

    // Now insert steps
    $loop2do = count($steps);
    for($idx=0; $idx < $loop2do; $idx++)
    {
      $this->create_step($tcversion_id,$steps[$idx]['step_number'],
                         $steps[$idx]['actions'],
                         $steps[$idx]['expected_results'],
                         $steps[$idx]['execution_type']);
    }
  }

  /**
   * update_last_modified
   *
   * @internal revision
   * 20101016 - franciscom - refixing of BUGID 3849
   */
  function update_last_modified($tcversion_id,$user_id,$time_stamp=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $changed_ts = !is_null($time_stamp) ? $time_stamp : $this->db->db_now();
    $sql = " UPDATE {$this->tables['tcversions']} " .
           " SET updater_id=" . $this->db->prepare_int($user_id) . ", " .
         " modification_ts = " . $changed_ts .
           " WHERE id = " . $this->db->prepare_int($tcversion_id);
    $this->db->exec_query($sql);
  }


  /**
   * Given a tcversion set, returns a modified set, where only tcversion id
   * that has requested values on Custom fields are returned.
   *
   * @param mixed tcversion_id: can be a single value or an array
   * @param map cf_hash: custom fields id plus values
   * @param map options: OPTIONAL
   *
   * @return map key: tcversion_id , 
   *         element: array numerical index with as much element as custom fields
   *
   * @20170325: Ay! this search on EXACT VALUE not LIKE!
   *            changed!
   */
  function filter_tcversions_by_cfields($tcversion_id,$cf_hash,$options=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $recordset = null;
    $itemSet = implode(',',(array)$tcversion_id);

    $my['options'] = array( 'access_key' => 'tcversion_id');
    $my['options'] = array_merge($my['options'], (array)$options);

    $or_clause = '';
    $cf_query = '';
    $cf_qty = count($cf_hash);

    // do not worry!! it seems that filter criteria is OR, but really is an AND,
    // OR is needed to do a simple query.
    // with processing on recordset becomes an AND
    foreach ($cf_hash as $cf_id => $cf_value)
    {
      $cf_query .= $or_clause . " (CFDV.field_id=" . $cf_id . 
                   " AND CFDV.value LIKE '%{$cf_value}%') ";
      $or_clause = ' OR ';
    }

    $sql = "/* $debugMsg */ " .
           " SELECT TCV.id AS tcversion_id, " . 
           " NH_TCVERSION.parent_id AS testcase_id, TCV.version," .
           " CFDV.field_id,CFDV.value " .
           " FROM {$this->tables['tcversions']} TCV " .
           " JOIN {$this->tables['nodes_hierarchy']} NH_TCVERSION " . 
           " ON NH_TCVERSION.id = TCV.id " .
           " JOIN {$this->tables['cfield_design_values']} CFDV " . 
           " ON CFDV.node_id = TCV.id " .
           " AND NH_TCVERSION.id IN ({$itemSet}) AND ({$cf_query}) ";

    $recordset = $this->db->fetchRowsIntoMap($sql,$my['options']['access_key'],database::CUMULATIVE);

    // now loop over result, entries whose count() < number of custom fields has to be removed
    if( !is_null($recordset) )
    {
      $key2loop = array_keys($recordset);
      foreach($key2loop as $key)
      {
        if( count($recordset[$key]) < $cf_qty)
        {
          unset($recordset[$key]);
        }
      }
      if( count($recordset) <= 0 )
      {
        $recordset = null;
      }
    }
    return $recordset;
  }

  /**
   *
   * @used-by execSetResults.php
   */
  function getExecutionSet($id,$version_id=null,$filters=null,$options=null)
  {
    // need to understand if possibility of choosing order by
    // allow us to replace completely code that seems duplicate
    // get_executions.
    //
    // NHA.node_order ASC, NHA.parent_id ASC, execution_id DESC

    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    // IMPORTANT NOTICE: keys are field names of executions tables
    $my['filters'] = array('tcversion_id' => null,'testplan_id' => null,
                           'platform_id' => null, 'build_id' => null);


    $my['filters'] = array_merge($my['filters'], (array)$filters);

    $my['options'] = array('exec_id_order' => 'DESC');
    $my['options'] = array_merge($my['options'], (array)$options);

    $filterBy = array();
    $filterKeys = array('build_id','platform_id','testplan_id','tcversion_id');
    foreach($filterKeys as $fieldName)
    {
      $filterBy[$fieldName] = '';
      if( !is_null($my['filters'][$fieldName]) )
      {
        $itemSet = implode(',', (array)($my['filters'][$fieldName]));
        $filterBy[$fieldName] = " AND E.{$fieldName} IN ({$itemSet}) ";
      }
    }


    // --------------------------------------------------------------------
    if( is_array($id) )
    {
      $tcid_list = implode(",",$id);
      $where_clause = " WHERE NHTCV.parent_id IN ({$tcid_list}) ";
    }
    else
    {
      $where_clause = " WHERE NHTCV.parent_id = {$id} ";
    }

    if(!is_null($version_id))
    {
      if( is_array($version_id) )
      {
        foreach($version_id as &$elem)
        {
          $elem = intval($elem);
        }
          $where_clause  .= ' AND TCV.id IN (' . implode(",",$version_id) . ') ';
      }
      else
      {
        if($version_id != self::ALL_VERSIONS)
        {
          $where_clause  .= ' AND TCV.id = ' .intval($version_id);
        }
      }
    }




    $sql = "/* $debugMsg */ SELECT NHTC.name,NHTCV.parent_id AS testcase_id, NHTCV.id AS tcversion_id, " .
         " TCV.*, " .
       " U.login AS tester_login, U.first AS tester_first_name, U.last AS tester_last_name," .
       " E.tester_id AS tester_id,E.id AS execution_id, E.status,E.tcversion_number," .
       " E.notes AS execution_notes, E.execution_ts, E.execution_type AS execution_run_type," .
       " E.execution_duration," .
       " E.build_id AS build_id, B.name AS build_name, B.active AS build_is_active, " .
       " B.is_open AS build_is_open,E.platform_id, PLATF.name AS platform_name," .
       " E.testplan_id,NHTPLAN.name AS testplan_name,TPTCV.id AS feature_id " .
       " FROM {$this->tables['nodes_hierarchy']} NHTCV " .
       " JOIN {$this->tables['nodes_hierarchy']} NHTC ON NHTC.id = NHTCV.parent_id " .
       " JOIN {$this->tables['tcversions']} TCV ON TCV.id = NHTCV.id  " .

       " JOIN {$this->tables['executions']} E " .
       " ON E.tcversion_id = NHTCV.id " .
       $filterBy['testplan_id'] . $filterBy['build_id'] .
       $filterBy['platform_id'] . $filterBy['tcversion_id'] .

       " /* To get build name */ " .
       " JOIN {$this->tables['builds']} B ON B.id=E.build_id " .

       " /* To get test plan name */ " .
       // " JOIN {$this->tables['testplans']} TPLAN ON TPLAN.id = E.testplan_id " .
       " JOIN {$this->tables['nodes_hierarchy']} NHTPLAN ON NHTPLAN.id = E.testplan_id " .

       " JOIN {$this->tables['testplan_tcversions']} TPTCV " .
       " ON  TPTCV.testplan_id = E.testplan_id " .
       " AND TPTCV.tcversion_id = E.tcversion_id " .
       " AND TPTCV.platform_id = E.platform_id " .
       " LEFT OUTER JOIN {$this->tables['users']} U ON U.id = E.tester_id " .
       " LEFT OUTER JOIN {$this->tables['platforms']} PLATF ON PLATF.id = E.platform_id  " .
       $where_clause .
       " ORDER BY execution_id {$my['options']['exec_id_order']} ";

    $recordset = $this->db->fetchArrayRowsIntoMap($sql,'id');
    return($recordset ? $recordset : null);
  }



  /**
   * for test case id and filter criteria return set with platforms
   * where test case has a version that has been executed.
   *
   */
  function getExecutedPlatforms($id,$filters=null,$options=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

      $my['filters'] = array( 'version_id' => null,'tplan_id' => null,
      'platform_id' => null, 'build_id' => null);
      $my['filters'] = array_merge($my['filters'], (array)$filters);

      $my['options'] = array('exec_id_order' => 'DESC');
      $my['options'] = array_merge($my['options'], (array)$options);

    $filterKeys = array('build_id','platform_id','tplan_id');
        foreach($filterKeys as $key)
        {
          $filterBy[$key] = '';
          if( !is_null($my['filters'][$key]) )
          {
            $itemSet = implode(',', (array)$$key);
            $filterBy[$key] = " AND e.{$key} IN ({$itemSet}) ";
          }
        }

    // --------------------------------------------------------------------
    if( is_array($id) )
    {
      $tcid_list = implode(",",$id);
      $where_clause = " WHERE NHTCV.parent_id IN ({$tcid_list}) ";
    }
    else
    {
      $where_clause = " WHERE NHTCV.parent_id = {$id} ";
    }

    // if( is_array($version_id) )
    // {
    //     $versionid_list = implode(",",$version_id);
    //     $where_clause  .= " AND tcversions.id IN ({$versionid_list}) ";
    // }
    // else
    // {
    //    if($version_id != self::ALL_VERSIONS)
    //    {
    //      $where_clause  .= " AND tcversions.id = {$version_id} ";
    //    }
    // }

    $sql = "/* $debugMsg */ SELECT DISTINCT e.platform_id,p.name " .
         " FROM {$this->tables['nodes_hierarchy']} NHTCV " .
           " JOIN {$this->tables['tcversions']} tcversions ON NHTCV.id = tcversions.id " .
           " JOIN {$this->tables['executions']} e ON NHTCV.id = e.tcversion_id " .
           " {$filterBy['tplan_id']} {$filterBy['build_id']} {$filterBy['platform_id']} " .
           " JOIN {$this->tables['builds']}  b ON e.build_id=b.id " .
           " LEFT OUTER JOIN {$this->tables['platforms']} p ON p.id = e.platform_id " .
           $where_clause;

    $recordset = $this->db->fetchRowsIntoMap($sql,'platform_id');
    return($recordset ? $recordset : null);
  }



  /**
   *
   * Solve point to my self
   *
   * <p> </p> added by web rich editor create some layout issues
   */
  function renderGhostSteps(&$steps2render) {
    $warningRenderException = lang_get('unable_to_render_ghost');
    $loop2do = count($steps2render);

    $tlBeginMark = '[ghost]';
    $tlEndMark = '[/ghost]';
    $tlEndMarkLen = strlen($tlEndMark);

    $key2check = array('actions','expected_results');

    // I've discovered that working with Web Rich Editor generates
    // some additional not wanted entities, that disturb a lot
    // when trying to use json_decode().
    // Hope this set is enough.
    $replaceSet = array($tlEndMark, '</p>', '<p>','&nbsp;');
    $replaceSetWebRichEditor = array('</p>', '<p>','&nbsp;');

    $rse = &$steps2render;
    for($gdx=0; $gdx < $loop2do; $gdx++)
    {
      foreach($key2check as $item_key)
      {
        $deghosted = false;
        $start = FALSE;

        if(isset($rse[$gdx][$item_key]))
        {
          $start = strpos($rse[$gdx][$item_key],$tlBeginMark);
          $ghost = $rse[$gdx][$item_key];
        }

        if($start !== FALSE)
        {
          $xx = explode($tlBeginMark,$rse[$gdx][$item_key]);
          $xx2do = count($xx);
          $ghost = '';
          $deghosted = false;
          for($xdx=0; $xdx < $xx2do; $xdx++)
          {
            try
            {
              if( ($cutting_point = strpos($xx[$xdx],$tlEndMark)) !== FALSE)
              {
                // here I've made a mistake
                // Look at this situation:
                //
                // ** Original String
                // [ghost]"Step":1,"TestCase":"BABA-1","Version":1[/ghost] RIGHT
                //
                // ** $xx[$xdx]
                // "Step":1,"TestCase":"BABA-1","Version":1[/ghost] RIGHT
                // Then $ydx = trim(str_replace($replaceSet,'',$xx[$xdx]));
                //
                // WRONG!!! => "Step":1,"TestCase":"BABA-1","Version":1
                //
                // Need to CUT WHERE I have found $tlEndMark
                //
                $leftside = trim(substr($xx[$xdx],0,$cutting_point));
                $rightside = trim(substr($xx[$xdx],$cutting_point+$tlEndMarkLen));
                $dx = '{' . html_entity_decode(trim($leftside,'\n')) . '}';
                $dx = json_decode($dx,true);

                if(isset($dx['Step']))
                {
                  if( ($xid = $this->getInternalID($dx['TestCase'])) > 0 )
                  {
                    // Start looking initially just for ACTIVE Test Case Versions
                    $vn = isset($dx['Version']) ? intval($dx['Version']) : 0;
                    if($vn == 0)
                    {
                      // User wants to follow latest ACTIVE VERSION
                      $yy = $this->get_last_version_info($xid,array('output' => 'full','active' => 1));
                      if(is_null($yy))
                      {
                        // seems all versions are inactive, in this situation will get latest
                        $yy = $this->get_last_version_info($xid,array('output' => 'full'));
                      }
                      $vn = intval($yy['version']);
                    }

                    $fi = $this->get_basic_info($xid,array('number' => $vn));
                    if(!is_null($fi))
                    {
                      if(intval($dx['Step']) > 0)
                      {
                        $deghosted = true;
                        $stx = $this->get_steps($fi[0]['tcversion_id'],$dx['Step']);
                        $ghost .= str_replace($replaceSetWebRichEditor,'',$stx[0][$item_key]) . $rightside;
                      }
                    }
                  }
                }
                else
                {
                  // seems we have found a ghost test case INSTEAD OF a GHOST test case STEP
                  // Then I do a trick creating an artificial 'summary' member
                  $zorro = array('summary' => $tlBeginMark . $leftside . $tlEndMark);
                  $this->renderGhost($zorro);
                  $deghosted = true;
                  $ghost .= $zorro['summary'] . $rightside;
                }
              }
              else
              {
                $ghost = $xx[$xdx]; // 20131022
              }
            }
            catch (Exception $e)
            {
              $deghosted = true;
              $ghost .= $warningRenderException . $rse[$gdx][$item_key];
            }
          }
        } // $start

        if($deghosted)
        {
          $rse[$gdx][$item_key] = $ghost;
        }

      }
    }
  }

  /**
   * Gets test cases created per user. The test cases are restricted to a
   * test plan of a test project. This method performs a query to database
   * using the given arguments.
   *
   * Optional values may be passed in the options array. These optional
   * values include tplan_id - Test plan ID.
   *
   * @param integer $user_id User ID
   * @param integer $tproject_id Test Project ID
   * @param mixed $options Optional array of options
   * @return mixed Array of test cases created per user
   */
  function get_created_per_user($user_id, $tproject_id, $options)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $has_options=!is_null($options);

      $sql = "/* $debugMsg */ SELECT ".
        "TPROJ.id AS testproject_id, TPTCV.testplan_id, TCV.id AS tcversion_id," .
        "TCV.version, TCV.tc_external_id, NHTC.id  AS testcase_id, NHTC.name, ".
        "TCV.creation_ts, TCV.modification_ts, TPROJ.prefix, U.first  AS first_name,".
        "U.last AS last_name, U.login, (TPTCV.urgency * TCV.importance) AS priority " .
          "FROM testprojects TPROJ, users U JOIN tcversions TCV ON U.id = TCV.author_id " .
          "JOIN nodes_hierarchy NHTCV ON TCV.id = NHTCV.id " .
          "JOIN nodes_hierarchy NHTC ON NHTCV.parent_id = NHTC.id " .
        "LEFT OUTER JOIN testplan_tcversions TPTCV ON TCV.id = TPTCV.tcversion_id " .
        "LEFT OUTER JOIN testplans TPLAN ON TPTCV.testplan_id = TPLAN.id " .
        "LEFT OUTER JOIN testprojects TPROJ_TPLAN ON TPLAN.testproject_id = TPROJ_TPLAN.id " .
        "WHERE TPROJ.id = {$tproject_id}";

      if($user_id !== 0) {
        $sql .= " AND U.id = {$user_id}";
      }

    if( $has_options && isset($options->tplan_id)) {
      $sql .= " AND TPTCV.testplan_id = {$options->tplan_id}";
    }

    if( $has_options && isset($options->startTime) ) {
      $sql .= " AND TCV.creation_ts >= '{$options->startTime}'";
    }

    if( $has_options && isset($options->endTime) ) {
      $sql .= " AND TCV.creation_ts <= '{$options->endTime}'";
    }

      $access_key=array('testplan_id','testcase_id');
      if( $has_options && isset($options->access_keys) )
      {
          switch($options->access_keys)
          {
              case 'testplan_testcase':
                $access_key=array('testplan_id','testcase_id');
              break;

              case 'testcase_testplan':
                  $access_key=array('testcase_id','testplan_id');
              break;

              default:
                $access_key=array('testplan_id','testcase_id');
              break;
          }
      }

      $rs=$this->db->fetchMapRowsIntoMap($sql,$access_key[0],$access_key[1],database::CUMULATIVE);

      if( $has_options && !is_null($rs)) // TBD: Check if we can remove it
      {
      if( !isset($options->access_keys) ||
      (is_null($options->access_keys) || $options->access_keys='testplan_testcase') )
      {
        $tcaseSet=null;
        $main_keys = array_keys($rs);
        foreach($main_keys as $maccess_key)
        {
          $sec_keys = array_keys($rs[$maccess_key]);
          foreach($sec_keys as $saccess_key)
          {
            // is enough I process first element
            $item = $rs[$maccess_key][$saccess_key][0];
            if(!isset($tcaseSet[$item['testcase_id']]))
            {
              $tcaseSet[$item['testcase_id']]=$item['testcase_id'];
            }
          }
        }

        $path_info = $this->tree_manager->get_full_path_verbose($tcaseSet);

        // Remove test project piece and convert to string
        $flat_path=null;
        foreach($path_info as $tcase_id => $pieces)
        {
          unset($pieces[0]);
          $flat_path[$tcase_id]=implode('/',$pieces);
        }
        $main_keys = array_keys($rs);

        foreach($main_keys as $idx)
        {
          $sec_keys = array_keys($rs[$idx]);
          foreach($sec_keys as $jdx)
          {
            $third_keys = array_keys($rs[$idx][$jdx]);
            foreach($third_keys as $tdx)
            {
              $fdx = $rs[$idx][$jdx][$tdx]['testcase_id'];
              $rs[$idx][$jdx][$tdx]['tcase_full_path']=$flat_path[$fdx];
            }
          }
          break;
        }
      }
      }

      return $rs;
  }

  /**
   *
   *
   */
  function setExecutionType($tcversionID,$value,$opt=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    
    $my['opt'] = array('updSteps' => false);
    $my['opt'] = array_merge($my['opt'],(array)$opt);

    $execType = $this->db->prepare_int(intval($value));
    $safeTCVID = $this->db->prepare_int($tcversionID);

    $sql = "/* $debugMsg */ " .
           " UPDATE {$this->tables['tcversions']} " .
           " SET execution_type={$execType} WHERE id = {$safeTCVID} ";
    $this->db->exec_query($sql);

    if( $my['opt']['updSteps'] )
    {
      $opx = array('fields2get' => 'id');
      $stepIDSet = $this->get_steps($safeTCVID,null,$opx);
      
      if( !is_null($stepIDSet) )
      {
        $target = array();
        foreach($stepIDSet as $elem )
        {
          $target[] = $elem['id'];
        }  
        $inClause = implode(',',$target);
        $sqlX = " UPDATE {$this->tables['tcsteps']} " .
                " SET execution_type={$execType} WHERE id IN (" . $inClause . ")";
        $this->db->exec_query($sqlX);
      }  
    }    

    return array($value,$execType,$sql);
  }


  /**
   *
   *
   */
  function setEstimatedExecDuration($tcversionID,$value)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $safe = trim($value);
    $safe = is_numeric($safe) ? $safe : null;

    $sql = "/* $debugMsg */ " .
           " UPDATE {$this->tables['tcversions']} " .
           " SET estimated_exec_duration=" . ((is_null($safe) || $safe == '') ? 'NULL' : $safe) .
           " WHERE id = " . $this->db->prepare_int($tcversionID);
    $this->db->exec_query($sql);
    return array($value,$safe,$sql);
  }



  /**
   * @param map $identity: id, version_id
   * @param map $execContext: tplan_id, platform_id,build_id
   * @internal revisions
   *
   * @since 1.9.4
   **/
  function getLatestExecSingleContext($identity,$execContext,$options=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $cfg = config_get('results');
    $status_not_run = $cfg['status_code']['not_run'];

    $my = array('opt' => array('output' => 'full'));
    $my['opt'] = array_merge($my['opt'],(array)$options);
    $safeContext = $execContext;
    $safeIdentity = $identity;
    foreach($safeContext as &$ele)
    {
      $ele = intval($ele);
    }
    foreach($safeIdentity as &$ele)
    {
      $ele = intval($ele);
    }

    // dammed names!!!
    $safeContext['tplan_id'] = isset($safeContext['tplan_id']) ? $safeContext['tplan_id'] : $safeContext['testplan_id'];


    // we have to manage following situations
    // 1. we do not know test case version id.
    if($safeIdentity['version_id'] > 0)
    {
      $addJoinLEX = '';
      $addWhereLEX = " AND EE.tcversion_id = " . $safeIdentity['version_id'];
      $addWhere = " AND TPTCV.tcversion_id = " . $safeIdentity['version_id'];
    }
    else
    {
      $addJoinLEX = " JOIN {$this->tables['nodes_hierarchy']} H2O " .
                    " ON H2O.id = EE.tcversion_id ";
      $addWhereLEX = " AND H2O.parent_id = " . $safeIdentity['id'];
      $addWhere = " AND NHTC.id = " . $safeIdentity['id'];
    }

    $sqlLEX = ' SELECT EE.tcversion_id,EE.testplan_id,EE.platform_id,EE.build_id,' .
              ' MAX(EE.id) AS id ' .
              " FROM {$this->tables['executions']} EE " .
              $addJoinLEX .
              ' WHERE EE.testplan_id = ' . $safeContext['tplan_id'] .
              ' AND EE.platform_id = ' . $safeContext['platform_id'] .
              ' AND EE.build_id = ' . $safeContext['build_id'] .
              $addWhereLEX .
              ' GROUP BY EE.tcversion_id,EE.testplan_id,EE.platform_id ,EE.build_id ';

    $out = null;
    switch($my['opt']['output'])
    {
      case 'exec_id':
        $dummy = $this->db->get_recordset($sqlLEX);
        $out = (!is_null($dummy) ? $dummy[0]['id'] : null);
      break;

      case 'timestamp':
            $sql= "/* $debugMsg */ SELECT E.id AS execution_id, " .
              " COALESCE(E.status,'{$status_not_run}') AS status," .
              " NHTC.id AS testcase_id, TCV.id AS tcversion_id, E.execution_ts" .
              " FROM {$this->tables['nodes_hierarchy']} NHTCV " .
              " JOIN {$this->tables['testplan_tcversions']} TPTCV ON TPTCV.tcversion_id = NHTCV.id" .
              " JOIN {$this->tables['nodes_hierarchy']} NHTC ON NHTC.id = NHTCV.parent_id " .
              " JOIN {$this->tables['tcversions']} TCV ON TCV.id = NHTCV.id " .

              " LEFT OUTER JOIN ({$sqlLEX}) AS LEX " .
              " ON  LEX.testplan_id = TPTCV.testplan_id " .
              " AND LEX.platform_id = TPTCV.platform_id " .
              " AND LEX.tcversion_id = TPTCV.tcversion_id " .
              " AND LEX.build_id = {$safeContext['build_id']} " .

              " LEFT OUTER JOIN {$this->tables['executions']} E " .
              " ON E.id = LEX.id " .

              " WHERE TPTCV.testplan_id = {$safeContext['tplan_id']} " .
              " AND TPTCV.platform_id = {$safeContext['platform_id']} " .
              $addWhere .
              " AND (E.build_id = {$safeContext['build_id']} OR E.build_id IS NULL)";

              // using database::CUMULATIVE is just a trick to return data structure
              // that will be liked on execSetResults.php
              $out = $this->db->fetchRowsIntoMap($sql,'testcase_id',database::CUMULATIVE);
      break;


      case 'full':
      default:
          $sql= "/* $debugMsg */ SELECT E.id AS execution_id, " .
                " COALESCE(E.status,'{$status_not_run}') AS status, E.execution_type AS execution_run_type," .
                " NHTC.name, NHTC.id AS testcase_id, NHTC.parent_id AS tsuite_id," .
                " TCV.id AS tcversion_id,TCV.tc_external_id,TCV.version,TCV.summary," .
                " TCV.preconditions,TCV.importance,TCV.author_id," .
                " TCV.creation_ts,TCV.updater_id,TCV.modification_ts,TCV.active," .
                " TCV.is_open,TCV.execution_type," .
                " U.login AS tester_login,U.first AS tester_first_name," .
                " U.last AS tester_last_name, E.tester_id AS tester_id," .
                " E.notes AS execution_notes, E.execution_ts, E.build_id,E.tcversion_number," .
                " B.name AS build_name, B.active AS build_is_active, B.is_open AS build_is_open," .
                " COALESCE(PLATF.id,0) AS platform_id,PLATF.name AS platform_name, TPTCV.id AS feature_id " .
                " FROM {$this->tables['nodes_hierarchy']} NHTCV " .
                " JOIN {$this->tables['testplan_tcversions']} TPTCV ON TPTCV.tcversion_id = NHTCV.id" .
                " JOIN {$this->tables['nodes_hierarchy']} NHTC ON NHTC.id = NHTCV.parent_id " .
                " JOIN {$this->tables['tcversions']} TCV ON TCV.id = NHTCV.id " .

                " LEFT OUTER JOIN ({$sqlLEX}) AS LEX " .
                " ON  LEX.testplan_id = TPTCV.testplan_id " .
                " AND LEX.platform_id = TPTCV.platform_id " .
                " AND LEX.tcversion_id = TPTCV.tcversion_id " .
                " AND LEX.build_id = {$safeContext['build_id']} " .

                " LEFT OUTER JOIN {$this->tables['executions']} E " .
                " ON E.id = LEX.id " .

                " JOIN {$this->tables['builds']} B ON B.id = {$safeContext['build_id']} " .
                " LEFT OUTER JOIN {$this->tables['users']} U ON U.id = E.tester_id " .
                " LEFT OUTER JOIN {$this->tables['platforms']} PLATF ON PLATF.id = {$safeContext['platform_id']} " .
                " WHERE TPTCV.testplan_id = {$safeContext['tplan_id']} " .
                " AND TPTCV.platform_id = {$safeContext['platform_id']} " .
                $addWhere .
                " AND (E.build_id = {$safeContext['build_id']} OR E.build_id IS NULL)";

                // using database::CUMULATIVE is just a trick to return data structure
                // that will be liked on execSetResults.php
                $out = $this->db->fetchRowsIntoMap($sql,'testcase_id',database::CUMULATIVE);
      break;
    }
    return $out;
  }

  /**
   *
   * DBExec means we do not considered NOT RUN, because are not written to DB.
   * @param map $identity: id, version_id
   * @param map $execContext: tplan_id, platform_id
   * @internal revisions
   *
   * @since 1.9.4
   **/
  function getLatestDBExecPlatformContext($identity,$execContext,$options=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $cfg = config_get('results');
    $status_not_run = $cfg['status_code']['not_run'];

    $my = array('opt' => array('output' => 'full'));
    $my['opt'] = array_merge($my['opt'],(array)$options);
    $safeContext = $execContext;
    $safeIdentity = $identity;
    foreach($safeContext as &$ele)
    {
      $ele = intval($ele);
    }
    foreach($safeIdentity as &$ele)
    {
      $ele = intval($ele);
    }

    // we have to manage following situations
    // 1. we do not know test case version id.
    if($safeIdentity['version_id'] > 0)
    {
      $addJoinLEX = '';
      $addWhereLEX = " AND EE.tcversion_id = " . $safeIdentity['version_id'];
      $addWhere = " AND TPTCV.tcversion_id = " . $safeIdentity['version_id'];
    }
    else
    {
      $addJoinLEX = " JOIN {$this->tables['nodes_hierarchy']} H2O " .
                " ON H2O.id = EE.tcversion_id ";
      $addWhereLEX = " AND H2O.parent_id = " . $safeIdentity['id'];
      $addWhere = " AND NHTC.id = " . $safeIdentity['id'];
    }

    $sqlLEX = ' SELECT EE.tcversion_id,EE.testplan_id,EE.platform_id,' .
          ' MAX(EE.id) AS id ' .
          " FROM {$this->tables['executions']} EE " .
          $addJoinLEX .
          ' WHERE EE.testplan_id = ' . $safeContext['tplan_id'] .
          ' AND EE.platform_id = ' . $safeContext['platform_id'] .
          $addWhereLEX .
          ' GROUP BY EE.tcversion_id,EE.testplan_id,EE.platform_id';

    $out = null;
    switch($my['opt']['output'])
    {
      case 'exec_id':
          $dummy = $this->db->get_recordset($sqlLEX);
          $out = (!is_null($dummy) ? $dummy[0]['id'] : null);
      break;

      case 'full':
      default:
        $sql= "/* $debugMsg */ SELECT E.id AS execution_id, " .
              " COALESCE(E.status,'{$status_not_run}') AS status, E.execution_type AS execution_run_type," .
              " NHTC.name, NHTC.id AS testcase_id, NHTC.parent_id AS tsuite_id," .
              " TCV.id AS tcversion_id,TCV.tc_external_id,TCV.version,TCV.summary," .
              " TCV.preconditions,TCV.importance,TCV.author_id," .
              " TCV.creation_ts,TCV.updater_id,TCV.modification_ts,TCV.active," .
              " TCV.is_open,TCV.execution_type," .
              " U.login AS tester_login,U.first AS tester_first_name," .
              " U.last AS tester_last_name, E.tester_id AS tester_id," .
              " E.notes AS execution_notes, E.execution_ts, E.build_id,E.tcversion_number," .
              " B.name AS build_name, B.active AS build_is_active, B.is_open AS build_is_open," .
              " COALESCE(PLATF.id,0) AS platform_id,PLATF.name AS platform_name, TPTCV.id AS feature_id " .
              " FROM {$this->tables['nodes_hierarchy']} NHTCV " .
              " JOIN {$this->tables['testplan_tcversions']} TPTCV ON TPTCV.tcversion_id = NHTCV.id" .
              " JOIN {$this->tables['nodes_hierarchy']} NHTC ON NHTC.id = NHTCV.parent_id " .
              " JOIN {$this->tables['tcversions']} TCV ON TCV.id = NHTCV.id " .

              " JOIN ({$sqlLEX}) AS LEX " .
              " ON  LEX.testplan_id = TPTCV.testplan_id " .
              " AND LEX.platform_id = TPTCV.platform_id " .
              " AND LEX.tcversion_id = TPTCV.tcversion_id " .

              " JOIN {$this->tables['executions']} E " .
              " ON E.id = LEX.id " .

              " JOIN {$this->tables['builds']} B ON B.id = E.build_id " .
              " JOIN {$this->tables['users']} U ON U.id = E.tester_id " .

              " /* Left outer on Platforms because Test plan can have NO PLATFORMS */ " .
              " LEFT OUTER JOIN {$this->tables['platforms']} PLATF " .
              " ON PLATF.id = {$safeContext['platform_id']} " .
              " WHERE TPTCV.testplan_id = {$safeContext['tplan_id']} " .
              " AND TPTCV.platform_id = {$safeContext['platform_id']} " .
              $addWhere;

              // using database::CUMULATIVE is just a trick to return data structure
              // that will be liked on execSetResults.php
              $out = $this->db->fetchRowsIntoMap($sql,'testcase_id',database::CUMULATIVE);
      break;
    }

    return $out;
  }

  /**
   *
   */
  function getExecution($execID,$tcversionID) {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $sql = "/* $debugMsg */ SELECT NHTC.name,NHTCV.parent_id AS testcase_id, NHTCV.id AS tcversion_id, " .
           " TCV.*, " .
           " U.login AS tester_login, U.first AS tester_first_name, U.last AS tester_last_name," .
           " E.tester_id AS tester_id,E.id AS execution_id, E.status,E.tcversion_number," .
           " E.notes AS execution_notes, E.execution_ts, E.execution_type AS execution_run_type," .
           " E.build_id AS build_id, B.name AS build_name, B.active AS build_is_active, " .
           " B.is_open AS build_is_open,E.platform_id, PLATF.name AS platform_name," .
           " E.testplan_id,NHTPLAN.name AS testplan_name,TPTCV.id AS feature_id " .
           " FROM {$this->tables['nodes_hierarchy']} NHTCV " .
           " JOIN {$this->tables['nodes_hierarchy']} NHTC ON NHTC.id = NHTCV.parent_id " .
           " JOIN {$this->tables['tcversions']} TCV ON TCV.id = NHTCV.id  " .
           " JOIN {$this->tables['executions']} E " .
           " ON E.tcversion_id = NHTCV.id " .
           " /* To get build name */ " .
           " JOIN {$this->tables['builds']} B ON B.id=E.build_id " .
           " /* To get test plan name */ " .
           " JOIN {$this->tables['nodes_hierarchy']} NHTPLAN ON NHTPLAN.id = E.testplan_id " .
           " JOIN {$this->tables['testplan_tcversions']} TPTCV " .
           " ON  TPTCV.testplan_id = E.testplan_id " .
           " AND TPTCV.tcversion_id = E.tcversion_id " .
           " AND TPTCV.platform_id = E.platform_id " .
           " LEFT OUTER JOIN {$this->tables['users']} U ON U.id = E.tester_id " .
           " LEFT OUTER JOIN {$this->tables['platforms']} PLATF ON PLATF.id = E.platform_id  " .
           " WHERE E.id = " . intval($execID) . " AND E.tcversion_id = " . intval($tcversionID);
    $rs = $this->db->get_recordset($sql);
    return ($rs ? $rs : null);
  }


  /**
   *
   */
  public function getAuditSignature($context,$options = null) {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $key2check = array('tcversion_id','id');
    $safeID = array();
    foreach($key2check as $idx => $key ) {
      if( property_exists($context, $key) ) {
        $safeID[$key] = intval($context->$key);
      }
      else {
        $safeID[$key] = -1;
      }  
    }

    if( $safeID['id'] <= 0  && $safeID['tcversion_id'] > 0 ) {
      $node = $this->tree_manager->get_node_hierarchy_info($safeID['tcversion_id']);
      $safeID['id'] = $node['parent_id'];
    }

    // we need:
    // Test Case External ID
    // Test Case Name
    // Test Case Path
    // What about test case version ID ? => only if argument provided
    //
    $pathInfo = $this->tree_manager->get_full_path_verbose($safeID['id'],array('output_format' => 'id_name'));
    $pathInfo = current($pathInfo);
    $path = '/' . implode('/',$pathInfo['name']) . '/';
    $tcase_prefix = $this->getPrefix($safeID['id'], $pathInfo['node_id'][0]);
    $info = $this->get_last_version_info($safeID['id'], array('output' => 'medium'));
    $signature = $path . $tcase_prefix[0] . $this->cfg->testcase->glue_character .
                 $info['tc_external_id'] . ':' . $info['name'];

    return $signature;
  }

  /**
   *
   */
  public function getTestSuite($id)
  {
    $dummy = $this->tree_manager->get_node_hierarchy_info($id);
    return $dummy['parent_id'];
  }


  function getIdCardByStepID($step_id)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $sql = "/* $debugMsg */ " .
           " SELECT NH_TCV.parent_id AS tcase_id, NH_STEPS.parent_id AS tcversion_id" .
           " FROM {$this->tables['nodes_hierarchy']} NH_STEPS " .
           " JOIN {$this->tables['nodes_hierarchy']} NH_TCV ON NH_TCV.id = NH_STEPS.parent_id " .
           " WHERE NH_STEPS.id = " . intval($step_id);
    $rs = $this->db->get_recordset($sql);
    return is_null($rs) ? $rs : $rs[0];
  }


  /**
   *
   */
  private function initShowGui($guiObj,$grantsObj,$idCard) {
    $id = $idCard->tcase_id;
    $goo = is_null($guiObj) ? new stdClass() : $guiObj;

    $goo->execution_types = $this->execution_types;
    $goo->tcase_cfg = $this->cfg->testcase;
    $goo->import_limit = TL_REPOSITORY_MAXFILESIZE;
    $goo->msg = '';
    $goo->fileUploadMsg = '';

    $goo->requirement_mgmt = property_exists($grantsObj, 'mgt_modify_req' ) ? $grantsObj->mgt_modify_req : null;
    if( is_null($goo->requirement_mgmt)) {
      $goo->requirement_mgmt = property_exists($grantsObj, 'requirement_mgmt' ) ? $grantsObj->requirement_mgmt : 0;
    }

    // some config options have been migrated to rights
    // In order to refactor less code, we will remap to OLD config options present on config file.
    $goo->tcase_cfg->can_edit_executed = $grantsObj->testproject_edit_executed_testcases == 'yes' ? 1 : 0;
    $goo->tcase_cfg->can_delete_executed = $grantsObj->testproject_delete_executed_testcases == 'yes' ? 1 : 0;
    $goo->view_req_rights = property_exists($grantsObj, 'mgt_view_req') ? $grantsObj->mgt_view_req : 0;
    $goo->assign_keywords = property_exists($grantsObj, 'keyword_assignment') ? $grantsObj->keyword_assignment : 0;
	  $goo->req_tcase_link_management = property_exists($grantsObj, 'req_tcase_link_management') ? $grantsObj->req_tcase_link_management : 0;

    $goo->parentTestSuiteName = '';
    $goo->tprojectName = '';
    $goo->submitCode = "";
    $goo->dialogName = '';
    $goo->bodyOnLoad = "";
    $goo->bodyOnUnload = "storeWindowSize('TCEditPopup')";


    $goo->tableColspan = $this->layout->tableToDisplayTestCaseSteps->colspan;

    $goo->tc_current_version = array();
    $goo->status_quo = array();
    $goo->keywords_map = array();
    $goo->arrReqs = array();

    $goo->cf_current_version = null;
    $goo->cf_other_versions = null;
    $goo->linked_versions=null;
    $goo->platforms = null;


    // add_relation_feedback_msg @used-by testcaseCommands.class.php:doAddRelation()
    $viewer_defaults = array('title' => lang_get('title_test_case'),'show_title' => 'no',
                             'action' => '', 'msg_result' => '','user_feedback' => '',
                             'refreshTree' => 1, 'disable_edit' => 0,
                             'display_testproject' => 0,'display_parent_testsuite' => 0,
                             'hilite_testcase_name' => 0,'show_match_count' => 0,
                             'add_relation_feedback_msg' => '');

    $viewer_defaults = array_merge($viewer_defaults, (array)$guiObj->viewerArgs);

    $goo->display_testproject = $viewer_defaults['display_testproject'];
    $goo->display_parent_testsuite = $viewer_defaults['display_parent_testsuite'];
    $goo->show_title = $viewer_defaults['show_title'];
    $goo->hilite_testcase_name = $viewer_defaults['hilite_testcase_name'];
    $goo->action = $viewer_defaults['action'];
    $goo->user_feedback = $viewer_defaults['user_feedback'];
    $goo->add_relation_feedback_msg = $viewer_defaults['add_relation_feedback_msg'];


    $goo->pageTitle = $viewer_defaults['title'];
    $goo->display_testcase_path = !is_null($goo->path_info);
    $goo->show_match_count = $viewer_defaults['show_match_count'];
    if($goo->show_match_count && $goo->display_testcase_path ) {
      $goo->pageTitle .= '-' . lang_get('match_count') . ':' . ($goo->match_count = count($goo->path_info));
    }

    $goo->refreshTree = isset($goo->refreshTree) ? $goo->refreshTree : $viewer_defaults['refreshTree'];
    $goo->sqlResult = $viewer_defaults['msg_result'];


    // fine grain control of operations
    if( $viewer_defaults['disable_edit'] == 1 || ($grantsObj->mgt_modify_tc == false) )
    {
      $goo->show_mode = 'editDisabled';
    }
    else if( !is_null($goo->show_mode) && $goo->show_mode == 'editOnExec' )
    {
      // refers to two javascript functions present in testlink_library.js
      // and logic used to refresh both frames when user call this
      // method to edit a test case while executing it.
      $goo->dialogName='tcview_dialog';
      $goo->bodyOnLoad="dialog_onLoad($guiObj->dialogName)";
      $goo->bodyOnUnload="dialog_onUnload($guiObj->dialogName)";
      $goo->submitCode="return dialog_onSubmit($guiObj->dialogName)";
    }

    $dummy = getConfigAndLabels('testCaseStatus','code');
    $goo->domainTCStatus = $dummy['lbl'];

    $goo->can_do = $this->getShowViewerActions($goo->show_mode);
    $key = 'testcase_freeze';
    if(property_exists($grantsObj, $key)) {
      $goo->can_do->freeze = $grantsObj->$key;
    }

    $path2root = $this->tree_manager->get_path($id);
    $goo->tproject_id = $path2root[0]['parent_id'];
    $info = $this->tproject_mgr->get_by_id($goo->tproject_id);
    $goo->requirementsEnabled = $info['opt']->requirementsEnabled;

    if( $goo->display_testproject ) {
      $goo->tprojectName = $info['name'];
    }

    if( $goo->display_parent_testsuite ) {
      $parent = count($path2root)-2;
      $goo->parentTestSuiteName = $path2root[$parent]['name'];
    }


    $testplans = $this->tproject_mgr->get_all_testplans($goo->tproject_id,array('plan_status' =>1) );
    $goo->has_testplans = !is_null($testplans) && count($testplans) > 0 ? 1 : 0;


    $platformMgr = new tlPlatform($this->db,$goo->tproject_id);
    $goo->platforms = $platformMgr->getAllAsMap();

    $goo->tcasePrefix = $this->tproject_mgr->getTestCasePrefix($goo->tproject_id) . $this->cfg->testcase->glue_character;

    $goo->scripts = null;
    $goo->tcase_id = $idCard->tcase_id;
    $goo->tcversion_id = $idCard->tcversion_id;
    $goo->allowStepAttachments = false;
    $designEditorCfg = getWebEditorCfg('design');
    $goo->designEditorType = $designEditorCfg['type'];
    $stepDesignEditorCfg = getWebEditorCfg('steps_design');
    $goo->stepDesignEditorType = $stepDesignEditorCfg['type'];

    // Add To Testplan button will be disabled if the testcase doesn't belong to the current selected testproject
    $goo->can_do->add2tplan = 'no';
    if($idCard->tproject_id == $goo->tproject_id) {
      $goo->can_do->add2tplan = ($goo->can_do->add2tplan == 'yes') ? $grantsObj->testplan_planning : 'no';
    }

    return $goo;
  }

  /**
   *
   */
  private function initShowGuiActions(&$gui)
  {

    $gui->deleteStepAction = "lib/testcases/tcEdit.php?tproject_id=$gui->tproject_id&show_mode=$gui->show_mode" .
                             "&doAction=doDeleteStep&step_id=";

    $gui->tcExportAction = "lib/testcases/tcExport.php?tproject_id=$gui->tproject_id&show_mode=$gui->show_mode";
    $gui->tcViewAction = "lib/testcases/archiveData.php?tproject_id={$gui->tproject_id}" .
                         "&show_mode=$gui->show_mode&tcase_id=";

    $gui->printTestCaseAction = "lib/testcases/tcPrint.php?tproject_id=$gui->tproject_id&show_mode=$gui->show_mode";


    $gui->keywordsViewHREF = "lib/keywords/keywordsView.php?tproject_id={$gui->tproject_id} " .
                             ' target="mainframe" class="bold" title="' . lang_get('menu_manage_keywords') . '"';


    $gui->reqSpecMgmtHREF = "lib/general/frmWorkArea.php?tproject_id={$gui->tproject_id}&feature=reqSpecMgmt";
    $gui->reqMgmtHREF = "lib/requirements/reqView.php?tproject_id={$gui->tproject_id}" .
                        "&showReqSpecTitle=1&requirement_id=";

    $gui->addTc2TplanHREF = "lib/testcases/tcAssign2Tplan.php?tproject_id={$gui->tproject_id}";


  }


  /**
   * render Ghost Test Case
   */
  function renderGhost(&$item2render) {
    $versionTag = '[version:%s]';
    $hint = "(link%s";

    // $href = '<a href="Javascript:openTCW(\'%s\',%s);">%s:%s' . " $versionTag (link)<p></a>";
    // second \'%s\' needed if I want to use Latest as indication, need to understand
    // Javascript instead of javascript, because CKeditor sometimes complains
    $href = '<a href="Javascript:openTCW(\'%s\',\'%s\');">%s:%s' . " $versionTag $hint<p></a>";
    $tlBeginMark = '[ghost]';
    $tlEndMark = '[/ghost]';
    $tlEndMarkLen = strlen($tlEndMark);

    $key2check = array('summary','preconditions');

    // I've discovered that working with Web Rich Editor generates
    // some additional not wanted entities, that disturb a lot
    // when trying to use json_decode().
    // Hope this set is enough.
    // 20130605 - after algorithm change, this seems useless
    //$replaceSet = array($tlEndMark, '</p>', '<p>','&nbsp;');
    // $replaceSetWebRichEditor = array('</p>', '<p>','&nbsp;');


    $rse = &$item2render;
    foreach($key2check as $item_key)
    {
      $start = strpos($rse[$item_key],$tlBeginMark);
      $ghost = $rse[$item_key];

      // There is at least one request to replace ?
      if($start !== FALSE)
      {
        $xx = explode($tlBeginMark,$rse[$item_key]);

        // How many requests to replace ?
        $xx2do = count($xx);
        $ghost = '';
        for($xdx=0; $xdx < $xx2do; $xdx++)
        {
          $isTestCaseGhost = true;

          // Hope was not a false request.
          // if( strpos($xx[$xdx],$tlEndMark) !== FALSE)
          if( ($cutting_point = strpos($xx[$xdx],$tlEndMark)) !== FALSE)
          {
            // Separate command string from other text
            // Theorically can be just ONE, but it depends
            // is user had not messed things.
            $yy = explode($tlEndMark,$xx[$xdx]);

            if( ($elc = count($yy)) > 0)
            {
              $dx = $yy[0];

              // trick to convert to array
              $dx = '{' . html_entity_decode(trim($dx,'\n')) . '}';
              $dx = json_decode($dx,true);

              try
              {
                $xid = $this->getInternalID($dx['TestCase']);
                if( $xid > 0 )
                {
                  $linkFeedback=")";
                  $addInfo="";
                  $vn = isset($dx['Version']) ? intval($dx['Version']) : 0;
                  if($vn == 0)
                  {
                    // User wants to follow latest ACTIVE VERSION
                    $zorro = $this->get_last_version_info($xid,array('output' => 'full','active' => 1));
                    $linkFeedback=" to Latest ACTIVE Version)";
                    if(is_null($zorro))
                    {
                      // seems all versions are inactive, in this situation will get latest
                      $zorro = $this->get_last_version_info($xid,array('output' => 'full'));
                      $addInfo = " - All versions are inactive!!";
                      $linkFeedback=" to Latest Version{$addInfo})";
                    }
                    $vn = intval($zorro['version']);
                  }

                  $fi = $this->get_basic_info($xid,array('number' => $vn));
                  if(!is_null($fi))
                  {
                    if( isset($dx['Step']) )
                    {
                      $isTestCaseGhost = false;

                      // ghost for rendering Test Case Step (string display)
                      // [ghost]"Step":1,"TestCase":"MOK-2","Version":1[/ghost]
                      //
                      // ATTENTION REMEMBER THAT ALSO CAN BE:
                      // [ghost]"Step":1,"TestCase":"MOK-2","Version":""[/ghost]
                      // [ghost]"Step":1,"TestCase":"MOK-2"[/ghost]
                      //
                      if(intval($dx['Step']) > 0)
                      {
                        $deghosted = true;
                        $rightside = trim(substr($xx[$xdx],$cutting_point+$tlEndMarkLen));
                        $stx = $this->get_steps($fi[0]['tcversion_id'],$dx['Step']);

                        $ghost .= $stx[0]['actions'] . $rightside;
                      }
                    }
                    else
                    {
                      // ghost for rendering Test Case (create link)
                      $ghost .= sprintf($href,$dx['TestCase'],$vn,$dx['TestCase'],$fi[0]['name'],$vn,$linkFeedback);
                    }
                  }
                }

                if($isTestCaseGhost)
                {
                  $lim = $elc-1;
                  for($cpx=1; $cpx <= $lim; $cpx++)
                  {
                    $ghost .= $yy[$cpx];
                  }
                }
              }
              catch (Exception $e)
              {
                $ghost .= $rse[$item_key];
              }
            }

          }
          else
          {
            $ghost .= $xx[$xdx];
          }
        }
      }

      if($ghost != '')
      {
        $rse[$item_key] = $ghost;
      }
    }
  }

  /**
   *
   *
   *
   */
  function setImportance($tcversionID,$value)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $sql = " UPDATE {$this->tables['tcversions']} " .
           " SET importance=" . $this->db->prepare_int($value) .
           " WHERE id = " . $this->db->prepare_int($tcversionID);
    $this->db->exec_query($sql);
  }

  /**
   *
   *
   *
   */
  function setStatus($tcversionID,$value)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $sql = " UPDATE {$this->tables['tcversions']} " .
           " SET status=" . $this->db->prepare_int($value) .
           " WHERE id = " . $this->db->prepare_int($tcversionID);
    $this->db->exec_query($sql);
  }


  /**
   * updateSimpleFields
   * used to update fields of type int, string on test case version
   *
   * @param int $tcversionID  item ID to update
   * @param hash  $fieldsValues key DB field to update
   *              supported fields:
   *              summary,preconditions,execution_type,importance,status,
   *              updater_id,estimated_exec_duration
   *
   * @internal revisions
   *
   */
  function updateSimpleFields($tcversionID,$fieldsValues)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $fieldsConvertions = array('summary' => 'prepare_string','preconditions' => 'prepare_string',
                               'execution_type' => 'prepare_int', 'importance' => 'prepare_int',
                               'status' => 'prepare_int', 'estimated_exec_duration' => null,
                               'updater_id' => null);
    $dummy = null;
    $sql = null;
    $ddx = 0;
    foreach($fieldsConvertions as $fkey => $fmethod)
    {
      if( isset($fieldsValues[$fkey]) )
      {
        $dummy[$ddx] = $fkey . " = ";
        if( !is_null($fmethod) )
        {
          $sep = ($fmethod == 'prepare_string') ? "'" : "";
          $dummy[$ddx] .= $sep . $this->db->$fmethod($fieldsValues[$fkey]) . $sep;
        }
        else
        {
          $dummy[$ddx] .= $fieldsValues[$fkey];
        }
        $ddx++;
      }
    }
    if( !is_null($dummy) )
    {
      $sqlSET = implode(",",$dummy);
      $sql = "/* {$debugMsg} */ UPDATE {$this->tables['tcversions']} " .
             "SET {$sqlSET} WHERE id={$tcversionID}";

      $this->db->exec_query($sql);
    }
    return $sql;
  }


  /**
   * updateName
   * check for duplicate name under same parent
   *
   * @param int  $id test case id
   * @param string $name
   *
   * @internal revisions
   *
   */
  function updateName($id,$name)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $ret['status_ok'] = true;
    $ret['msg'] = 'ok';
    $ret['debug'] = '';
    $ret['API_error_code'] = 0;

    $field_size = config_get('field_size');
    $new_name = trim($name);

    if( ($nl = strlen($new_name)) <= 0 )
    {
      $ret['status_ok'] = false;
      $ret['API_error_code'] = 'TESTCASE_EMPTY_NAME';
      $ret['msg'] = lang_get('API_' . $ret['API_error_code']);
    }

    if( $ret['status_ok'] && $nl > $field_size->testcase_name)
    {
      $ret['status_ok'] = false;
      $ret['API_error_code'] = 'TESTCASE_NAME_LEN_EXCEEDED';
      $ret['msg'] = sprintf(lang_get('API_' . $ret['API_error_code']),$nl,$field_size->testcase_name);
    }

    if( $ret['status_ok'] )
    {
      // Go ahead
      $check = $this->tree_manager->nodeNameExists($name,$this->my_node_type,$id);
      $ret['status_ok'] = !$check['status'];
      $ret['API_error_code'] = 'TESTCASE_SIBLING_WITH_SAME_NAME_EXISTS';
      $ret['msg'] = sprintf(lang_get('API_' . $ret['API_error_code']),$name);
      $ret['debug'] = '';
    }

    if($ret['status_ok'])
    {

      $rs = $this->tree_manager->get_node_hierarchy_info($id);
      if( !is_null($rs) && $rs['node_type_id'] == $this->my_node_type)
      {
        $sql = "/* {$debugMsg} */ UPDATE {$this->tables['nodes_hierarchy']} " .
               " SET name='" . $this->db->prepare_string($name) . "' " .
               " WHERE id= {$id}";
        $this->db->exec_query($sql);
        $ret['debug'] = "Old name:{$rs['name']} - new name:{$name}";
      }
    }
    return $ret;
  }

  function getAttachmentTable() {
    return $this->attachmentTableName;
  }

  /**
   *
   */
  function updateChangeAuditTrial($tcversion_id,$user_id)
  {
    $sql = " UPDATE {$this->tables['tcversions']} " .
           " SET updater_id=" . $this->db->prepare_int($user_id) . ", " .
           " modification_ts = " . $this->db->db_now() .
           " WHERE id = " . $this->db->prepare_int(intval($tcversion_id));
    $this->db->exec_query($sql);
  }

  /**
   *
   */
  function getStepsExecInfo($execution_id)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $sql = "/* {$debugMsg} */ " .
           " SELECT id, execution_id,tcstep_id,notes,status FROM {$this->tables['execution_tcsteps']} " .
           " WHERE execution_id = " . intval($execution_id);

    $rs = $this->db->fetchRowsIntoMap($sql,'tcstep_id');
    return $rs;
  }

  /**
   *
   */
  function getWorkFlowStatusDomain() {
    $dummy = getConfigAndLabels('testCaseStatus','code');
    return $dummy['lbl'];
  }


  /**
   *
   */
  public function getRelations($id) {
    $debugMsg = "/* {$this->debugMsg}" . __FUNCTION__ . ' */';

    $safeID = intval($id);

    $relSet = array();
    $relSet['num_relations'] = 0;

    $dummy = $this->get_by_id($id,self::LATEST_VERSION,null,
                              array('output' => 'essential','getPrefix' => true,
                                    'caller' => __FUNCTION__));

    $relSet['item'] = (null != $dummy) ? current($dummy) : null;
    $relSet['relations'] = array();

    $tproject_mgr = new testproject($this->db);

    $sql = " $debugMsg SELECT id, source_id, destination_id, relation_type, author_id, creation_ts " .
           " FROM {$this->tables['testcase_relations']} " .
           " WHERE source_id=$safeID OR destination_id=$safeID " .
           " ORDER BY id ASC ";

    $relSet['relations']= $this->db->get_recordset($sql);

    if( !is_null($relSet['relations']) && count($relSet['relations']) > 0 ) {
      $labels = $this->getRelationLabels();
      $label_keys = array_keys($labels);
      foreach($relSet['relations'] as $key => $rel)
      {
        // is this relation type is configured?
        if( ($relTypeAllowed = in_array($rel['relation_type'],$label_keys)) )
        {
            $relSet['relations'][$key]['source_localized'] = $labels[$rel['relation_type']]['source'];
            $relSet['relations'][$key]['destination_localized'] = $labels[$rel['relation_type']]['destination'];

            $type_localized = 'destination_localized';
            $other_key = 'source_id';
            if ($id == $rel['source_id'])
            {
              $type_localized = 'source_localized';
              $other_key = 'destination_id';
            }
            $relSet['relations'][$key]['type_localized'] = $relSet['relations'][$key][$type_localized];
            $otherItem = $this->get_by_id($rel[$other_key],self::LATEST_VERSION,null,
                                          array('output' => 'full_without_users','getPrefix' => true));


            // only add it, if either interproject linking is on or if it is in the same project
            $relTypeAllowed = true;
            $relSet['relations'][$key]['related_tcase'] = $otherItem[0];

            $user = tlUser::getByID($this->db,$rel['author_id']);
            $relSet['relations'][$key]['author'] = $user->getDisplayName();
          }

          if( !$relTypeAllowed )
          {
            unset($relSet['relations'][$key]);
          }

        } // end foreach

        $relSet['num_relations'] = count($relSet['relations']);
    }

    return $relSet;
  }


  /**
   * idCard['tcase_id']
   * idCard['tcversion_id']
   */
  public function getTCVersionRelations($idCard) {
    $debugMsg = "/* {$this->debugMsg}" . __FUNCTION__ . ' */';

    $safeID = $idCard;
    foreach($safeID as $prop => $val) {
      $safeID[$prop] = intval($val);
    }

    $getOpt = array('output' => 'essential','getPrefix' => true,
                    'caller' => __FUNCTION__); 
    $relSet = array('num_relations' => 0, 'relations' => array());


    $relSet['item'] = current($this->get_by_id($safeID['tcase_id'],
                                     $safeID['tcversion_id'],null,$getOpt));

    $sql = " $debugMsg " . 
           " SELECT TR.id, source_id, destination_id, relation_type, " .
           " TR.author_id, TR.creation_ts,TR.link_status, " . 
           " NHTCV_S.parent_id AS tcase_source, " .
           " NHTCV_D.parent_id AS tcase_destination " .
           " FROM {$this->tables['testcase_relations']} TR " .
           " JOIN {$this->tables['nodes_hierarchy']} AS NHTCV_D " .
           " ON NHTCV_D.id = destination_id " . 
           " JOIN {$this->tables['nodes_hierarchy']} AS NHTCV_S " .
           " ON NHTCV_S.id = source_id " . 
           " WHERE source_id = {$safeID['tcversion_id']} OR " . 
           " destination_id = {$safeID['tcversion_id']} " .
           " ORDER BY id ASC ";

    $relSet['relations']= $this->db->get_recordset($sql);

    if( !is_null($relSet['relations']) && count($relSet['relations']) > 0 ) {
      $labels = $this->getRelationLabels();
      $label_keys = array_keys($labels);

      foreach($relSet['relations'] as $key => $rel) {
        // is this relation type is configured?
        if( ($relTypeAllowed = in_array($rel['relation_type'],$label_keys)) ) {
            $relSet['relations'][$key]['source_localized'] = $labels[$rel['relation_type']]['source'];
            $relSet['relations'][$key]['destination_localized'] = $labels[$rel['relation_type']]['destination'];

            $type_localized = 'destination_localized';
            $oKeyTCVID = 'source_id';
            $oKeyTCID = 'tcase_source';
            if( $safeID['tcversion_id'] == $rel['source_id'] ) {
              $type_localized = 'source_localized';
              $oKeyTCVID = 'destination_id';
              $oKeyTCID = 'tcase_destination';
            } 
            $otherTCID = $rel[$oKeyTCID];
            $otherTCVID = $rel[$oKeyTCVID];

            $relSet['relations'][$key]['type_localized'] = $relSet['relations'][$key][$type_localized];

            $otherItem = $this->get_by_id($otherTCID,$otherTCVID,null,
                                          array('output' => 'full_without_users','getPrefix' => true));


            // only add it to output set, if either interproject linking is on 
            // or if it is in the same project
            $relTypeAllowed = true;
            $relSet['relations'][$key]['related_tcase'] = $otherItem[0];

            $user = tlUser::getByID($this->db,$rel['author_id']);
            $relSet['relations'][$key]['author'] = $user->getDisplayName();
          }

          if( !$relTypeAllowed ) {
            unset($relSet['relations'][$key]);
          }

        } // end foreach

        $relSet['num_relations'] = count($relSet['relations']);
    }

    return $relSet;
  }

  /**
   *
   */
  public function getTCVRelationsRaw($tcversionID, $opt=null) {
    $debugMsg = "/* {$this->debugMsg}" . __FUNCTION__ . ' */';

    $safeID['tcversion_id'] =  intval($tcversionID);

    $my = array('opt' => array('side' => null));
    $my['opt'] = array_merge($my['opt'],(array)$opt);

    $sql = " $debugMsg " . 
           " SELECT TR.id, source_id, destination_id, relation_type, " .
           " TR.author_id, TR.creation_ts,TR.link_status, " . 
           " NHTCV_S.parent_id AS tcase_source, " .
           " NHTCV_D.parent_id AS tcase_destination " .
           " FROM {$this->tables['testcase_relations']} TR " .
           " JOIN {$this->tables['nodes_hierarchy']} AS NHTCV_D " .
           " ON NHTCV_D.id = destination_id " . 
           " JOIN {$this->tables['nodes_hierarchy']} AS NHTCV_S " .
           " ON NHTCV_S.id = source_id ";

    switch($my['opt']['side']) {
      case 'source':
        $where = " WHERE source_id = {$safeID['tcversion_id']} "; 
      break;

      case 'destination':
      case 'dest':
        $where = " WHERE destination_id = {$safeID['tcversion_id']} ";
      break;

      default:
        $where = " WHERE source_id = {$safeID['tcversion_id']} OR " . 
                 " destination_id = {$safeID['tcversion_id']} ";
      break;
    }       
           
    $sql .= $where;       
    $relSet= $this->db->fetchRowsIntoMap($sql,'id');

    return $relSet;
  }

  /**
   *
   */
  public static function getRelationLabels() {
    $cfg = config_get('testcase_cfg');
    $labels = $cfg->relations->type_labels;
    foreach ($labels as $key => $label) {
      $labels[$key] = init_labels($label);
    }
    return $labels;
  }


  /**
   *
   */
  public function deleteAllTestCaseRelations($id) {
    $debugMsg = "/* {$this->debugMsg}" . __FUNCTION__ . ' */';
    
    $tcaseSet = (array)$id;
    array_walk($tcaseSet,'intval');

    // @since 1.9.18 
    // Relations on test case versions
    $tcVIDSet = $this->getAllVersionsID($tcaseSet);
    $inValues = implode(',', $tcVIDSet); 
    $sql = " $debugMsg DELETE FROM {$this->tables['testcase_relations']} " .
           " WHERE source_id IN ($inValues) OR " . 
           " destination_id IN ($inValues) ";
    $this->db->exec_query($sql);
  }



  /**
   * checks if there is a relation of a given type between two requirements
   *
   * @author Andreas Simon
   *
   * @param integer $first_id   ID to check
   * @param integer $second_id  ID to check
   * @param integer $rel_type_id relation type ID to check
   *
   * @return true, if relation already exists, false if not
   */
  public function relationExits($first_id, $second_id, $rel_type_id)
  {
    $debugMsg = "/* {$this->debugMsg}" . __FUNCTION__ . ' */';

    $safe_first_id = intval($first_id);
    $safe_second_id = intval($second_id);

    $sql = " $debugMsg SELECT COUNT(0) AS qty " .
           " FROM {$this->tables['testcase_relations']} " .
           " WHERE ((source_id=" . $safe_first_id . " AND destination_id=" . $safe_second_id . ") " .
           " OR (source_id=" . $safe_second_id . " AND destination_id=" . $safe_first_id .  ")) " .
           " AND relation_type=" . intval($rel_type_id);

    $rs = $this->db->get_recordset($sql);
    return($rs[0]['qty'] > 0);
  }

  /**
   * Get count of all relations, no matter if it is source or destination
   * or what type of relation it is.
   *
   * @param integer $id requirement ID to check
   *
   * @return integer $count
   */
  public function getRelationsCount($id) {
    $debugMsg = "/* {$this->debugMsg}" . __FUNCTION__ . ' */';
    $safeID = intval($id);
    $sql = " $debugMsg SELECT COUNT(*) AS qty " .
           " FROM {$this->tables['testcase_relations']} " .
           " WHERE source_id=$safeID OR destination_id=$safeID ";
    $rs = $this->db->get_recordset($sql);
    return($rs[0]['qty']);
  }

  /**
   * add a relation of a given type between Test Case Versions
   * 
   * @param integer $source_id: 
   *                ID of source test case version or test case.
   *                If test case is provided, latest active version
   *                will be used.
   *
   * @param integer $destination_id:
   *                ID of destination test case version or test case
   *                If test case is provided, latest active version
   *                will be used.
   *
   * @param integer $type_id relation type ID to set
   * @param integer $author_id user's ID
   */
  public function addRelation($source_id, $destination_id, $type_id, $author_id, $ts=null) {
    $debugMsg = "/* {$this->debugMsg}" . __FUNCTION__ . ' */';

    // Check if items are test cases or test case versions
    // Will check only source
    $safeID = array('s' => intval($source_id), 
                    'd' => intval($destination_id));

    $extr = array($safeID['s']);
    $sql = " SELECT node_type_id,id " . 
           " FROM {$this->tables['nodes_hierarchy']} " .
           " WHERE id IN(" . implode(',', $extr) . ")";

    $nu = current($this->db->get_recordset($sql));
    
    if( $nu['node_type_id'] == $this->node_types_descr_id['testcase'] ) {
      // Need to get latest active version for source and dest
      $tcvSet = $this->get_last_active_version( 
        array($safeID['s'],$safeID['d']), null,
        array('access_key' => 'testcase_id'));

      // Overwrite
      $safeID['s'] = intval($tcvSet[$safeID['s']]['tcversion_id']);
      $safeID['d'] = intval($tcvSet[$safeID['d']]['tcversion_id']);
    }    

    // check if exists before trying to add
    if( !$this->relationExits($source_id, $destination_id, $type_id) ) {
      // check if related testcase is open
      $dummy = $this->get_by_id($destination_id,self::LATEST_VERSION);
      if(($dummy[0]['is_open']) == 1){
        $time = is_null($ts) ? $this->db->db_now() : $ts;
        $sql = " $debugMsg INSERT INTO {$this->tables['testcase_relations']} "  .
           " (source_id, destination_id, relation_type, author_id, creation_ts) " .
           " values ({$safeID['s']},{$safeID['d']}, $type_id, $author_id, $time)";
        $this->db->exec_query($sql);
        $ret = array('status_ok' => true, 'msg' => 'relation_added');
      } else {
        $ret = array('status_ok' => false, 'msg' => 'related_tcase_not_open');
      }
    } else {
      $ret = array('status_ok' => false, 'msg' => 'relation_already_exists');
    }

    return $ret;
  }





  /**
   * delete an existing relation
   *
   * @author Andreas Simon
   *
   * @param int $id relation id
   */
  public function deleteRelationByID($relID) {
    $debugMsg = "/* {$this->debugMsg}" . __FUNCTION__ . ' */';
    $sql = " $debugMsg DELETE FROM {$this->tables['testcase_relations']} WHERE id=" . intval($relID);
    $this->db->exec_query($sql);
  }

  /**
   *
   * @return array $htmlSelect info needed to create select box on multiple templates
   */
  function getRelationTypeDomainForHTMLSelect() {
    $htmlSelect = array('items' => array(), 'selected' => null, 'equal_relations' => array());
    $labels = $this->getRelationLabels();

    foreach ($labels as $key => $lab)
    {
      $htmlSelect['items'][$key . "_source"] = $lab['source'];
      if ($lab['source'] != $lab['destination'])
      {
        // relation is not equal as labels for source and dest are different
        $htmlSelect['items'][$key . "_destination"] = $lab['destination'];
      }
      else
      {
        // mark this as equal relation - no parent/child, makes searching simpler
        $htmlSelect['equal_relations'][] = $key . "_source";
      }
    }

    // set "related to" as default preselected value in forms
    if (defined('TL_REL_TYPE_RELATED') && isset($htmlSelect[TL_REL_TYPE_RELATED . "_source"]))
    {
      $selected_key = TL_REL_TYPE_RELATED . "_source";
    }
    else
    {
      // "related to" is not configured, so take last element as selected one
      $keys = array_keys($htmlSelect['items']);
      $selected_key = end($keys);
    }
    $htmlSelect['selected'] = $selected_key;

    return $htmlSelect;
  }

  /**
   * exportRelationToXML
   *
   * Function to export a test case relation to XML.
   *
   * @param  int $relation relation data array
   * @param  string $troject_id
   *
   * @return  string with XML code
   *
   * <relation>
   *   <source>testcase external id</source>
   *   <source_project>prj</source_project>
   *   <destination>doc2_id</destination>
   *   <destination_project>testcase external id</destination_project>
   *   <type>0</type>
   * </relation>
   *
   * @internal revisions
   *
   */
  function exportRelationToXML($relation,$item)
  {
    $xmlStr = '';

    if(!is_null($relation))
    {
      // need to understand if swap is needed, this happens when
      // relation type is
      // - child_of
      // - depends_on
      // where item is DESTINATION and NOT SOURCE
      if( $relation['source_id'] == $item['testcase_id'])
      {
        $ele['source_ext_id'] = $item['fullExternalID'];
        $ele['destination_ext_id'] = $relation['related_tcase']['fullExternalID'];
      }
      else
      {
        // SWAP
        $ele['source_ext_id'] = $relation['related_tcase']['fullExternalID'];
        $ele['destination_ext_id'] = $item['fullExternalID'];
      }
      $ele['relation_type'] = $relation['relation_type'];

      $info = array("||SOURCE||" => "source_ext_id","||DESTINATION||" => "destination_ext_id",
                    "||TYPE||" => "relation_type");

      $elemTpl = "\t" .   "<relation>" . "\n\t\t" . "<source>||SOURCE||</source>" ;
      $elemTpl .= "\n\t\t" . "<destination>||DESTINATION||</destination>";
      $elemTpl .=  "\n\t\t" . "<type>||TYPE||</type>" . "\n\t" . "</relation>" . "\n";

      $work[] = $ele;
      $xmlStr = exportDataToXML($work,"{{XMLCODE}}",$elemTpl,$info,true);
    }

    return $xmlStr;
  }


  /**
   * Will do analisys IGNORING test plan, platform and build
   * get info of execution WRITTEN to DB.
   *
   */
  function getSystemWideLastestExecutionID($tcversion_id)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $sql = "/* $debugMsg */ " .
           " SELECT MAX(e.id) AS execution_id " .
           " FROM {$this->tables['executions']} e " .
           " WHERE e.tcversion_id = " . intval($tcversion_id);


    $rs = $this->db->get_recordset($sql);
    return intval($rs[0]['execution_id']);
  }


  /**
   * render Image Attachments INLINE
   *
   */
  private function renderImageAttachments($id,&$item2render,$key2check=array('summary','preconditions'),$basehref=null) {

    static $attSet;
    static $beginTag;
    static $endTag;

    if(!$attSet || !isset($attSet[$id]))
    {
      $attSet[$id] = $this->attachmentRepository->getAttachmentInfosFor($id,$this->attachmentTableName,'id');
      $beginTag = '[tlInlineImage]';
      $endTag = '[/tlInlineImage]';
    }

    if(is_null($attSet[$id]))
    {
      return;
    }

    // $href = '<a href="Javascript:openTCW(\'%s\',%s);">%s:%s' . " $versionTag (link)<p></a>";
    // second \'%s\' needed if I want to use Latest as indication, need to understand
    // Javascript instead of javascript, because CKeditor sometimes complains
    //
    // CRITIC: skipCheck is needed to render OK when creating report on Pseudo-Word format.
    $bhref = is_null($basehref) ? $_SESSION['basehref'] : $basehref;
    $img = '<p><img src="' . $bhref . '/lib/attachments/attachmentdownload.php?skipCheck=%sec%&id=%id%"></p>';

    $rse = &$item2render;
    foreach($key2check as $item_key)
    {
      $start = strpos($rse[$item_key],$beginTag);
      $ghost = $rse[$item_key];

      // There is at least one request to replace ?
      if($start !== FALSE)
      {
        $xx = explode($beginTag,$rse[$item_key]);

        // How many requests to replace ?
        $xx2do = count($xx);
        $ghost = '';
        for($xdx=0; $xdx < $xx2do; $xdx++)
        {
          // Hope was not a false request.
          if( strpos($xx[$xdx],$endTag) !== FALSE)
          {
            // Separate command string from other text
            // Theorically can be just ONE, but it depends
            // is user had not messed things.
            $yy = explode($endTag,$xx[$xdx]);
            if( ($elc = count($yy)) > 0)
            {
              $atx = $yy[0];
              try
              {
                if(isset($attSet[$id][$atx]) && $attSet[$id][$atx]['is_image'])
                {
                  $sec = hash('sha256', $attSet[$id][$atx]['file_name']);
                  $ghost .= str_replace(array('%id%','%sec%'),array($atx,$sec),$img);
                }
                $lim = $elc-1;
                for($cpx=1; $cpx <= $lim; $cpx++)
                {
                  $ghost .= $yy[$cpx];
                }
              }
              catch (Exception $e)
              {
                $ghost .= $rse[$item_key];
              }
            }
          }
          else
          {
            // nothing to do
            $ghost .= $xx[$xdx];
          }
        }
      }

      // reconstruct field contents
      if($ghost != '')
      {
        $rse[$item_key] = $ghost;
      }
    }
  }


  /**
   *
   */
  function trim_and_limit($s, $len = 100)
  {
    $s = trim($s);
    if (tlStringLen($s) > $len)
    {
      $s = tlSubStr($s, 0, $len);
    }

    return $s;
  }

  /**
   *
   */
  function generateTimeStampName($name)
  {
    return strftime("%Y%m%d-%H:%M:%S", time()) . ' ' . $name;
  }

  /**
   *
   */
  static function getLayout()
  {
    $ly = new stdClass();
    $ly->tableToDisplayTestCaseSteps = new stdClass();

    // MAGIC: columns are:
    //column for reorder, action, expected results, exec type, delete, insert
    $ly->tableToDisplayTestCaseSteps->colspan = 6;

    return $ly;
  }

  /**
   *
   */
  function setIntAttrForAllVersions($id,$attr,$value,$forceFrozenVersions=false)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
//    $children =


    $sql = " UPDATE {$this->tables['tcversions']} " .
           " SET {$attr} = " . $this->db->prepare_int($value) ;
		   
	if($forceFrozenVersions==false){
	  $sql .= " WHERE is_open=1 AND ";
	}
	else{
	  $sql .= " WHERE ";
	}
	$sql .= " id IN (" .
           "  SELECT NHTCV.id FROM {$this->tables['nodes_hierarchy']} NHTCV " .
           "  WHERE NHTCV.parent_id = " . intval($id) . ")";
    $this->db->exec_query($sql);
  }



  /**
   *
   */
  function getTcSearchSkeleton($userInput=null)
  {
    $sk = new stdClass();

    $sk->creation_date_from = null;
    $sk->creation_date_to = null;
    $sk->modification_date_from = null;
    $sk->modification_date_to = null;
    $sk->search_important_notice = '';
    $sk->design_cf = '';
    $sk->keywords = '';
    $sk->filter_by['design_scope_custom_fields'] = false;
    $sk->filter_by['keyword'] = false;
    $sk->filter_by['requirement_doc_id'] = false;
    $sk->option_importance = array(0 => '',HIGH => lang_get('high_importance'),MEDIUM => lang_get('medium_importance'),
                                           LOW => lang_get('low_importance'));

    $dummy = getConfigAndLabels('testCaseStatus','code');
    $sk->domainTCStatus = array(0 => '') + $dummy['lbl'];
    $sk->importance = null;
    $sk->status = null;
    $sk->tcversion = null;
    $sk->tcasePrefix = '';
    $sk->targetTestCase = '';

    $txtin = array("created_by","edited_by","jolly");
    $jollyKilled = array("summary","steps","expected_results","preconditions","name");
    $txtin = array_merge($txtin, $jollyKilled);

    foreach($txtin as $key )
    {
      $sk->$key = !is_null($userInput) ? $userInput->$key : '';
    }

    if(!is_null($userInput) && $userInput->jolly != '')
    {
      foreach($jollyKilled as $key)
      {
        $sk->$key = '';
      }
    }

    return $sk;
  }

  /**
   *
   *
   */
  function setIsOpen($id,$tcversion_id,$value)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $bv = (intval($value) > 0) ? 1 : 0;
    $sql =  " /* $debugMsg */ UPDATE {$this->tables['tcversions']} " .
            " SET is_open = {$bv}" .
            " WHERE id = " . intval($tcversion_id) ;

    $this->db->exec_query($sql);
  }

 /**
  * render CF values
  *
  * <p> </p> added by web rich editor create some layout issues
  */
  function renderVariables(&$item2render) {
    $tcase_id = $item2render['testcase_id'];
    $tcversion_id = $item2render['id'];
    $cfSet = $this->get_linked_cfields_at_design($tcase_id,$tcversion_id);

    if( is_null($cfSet) ) {
      return;
    }

    $key2check = array('summary','preconditions');
    $tlBeginTag = '[tlVar]';
    $tlEndTag = '[/tlVar]';
    $tlEndTagLen = strlen($tlEndTag);

    // I've discovered that working with Web Rich Editor generates
    // some additional not wanted entities, that disturb a lot
    // when trying to use json_decode().
    // Hope this set is enough.
    // $replaceSet = array($tlEndTag, '</p>', '<p>','&nbsp;');
    // $replaceSetWebRichEditor = array('</p>', '<p>','&nbsp;');


    $rse = &$item2render;
    foreach($key2check as $item_key) {
      $start = strpos($rse[$item_key],$tlBeginTag);
      $ghost = $rse[$item_key];

      // There is at least one request to replace ?
      if($start !== FALSE) {
        // This way remove may be the <p> that webrich editor adds
        $play = substr($rse[$item_key],$start);
        $xx = explode($tlBeginTag,$play);

        // How many requests to replace ?
        $xx2do = count($xx);
        $ghost = '';
        for($xdx=0; $xdx < $xx2do; $xdx++) {

          // Hope was not a false request.
          if( ($es=strpos($xx[$xdx],$tlEndTag)) !== FALSE) {
            // Separate command string from other text
            // Theorically can be just ONE, but it depends
            // is user had not messed things.
            $yy = explode($tlEndTag,$xx[$xdx]);
            if( ($elc = count($yy)) > 0) {
              $cfname = trim($yy[0]);
              try {
                // look for the custom field
                foreach ($cfSet as $cfID => $cfDef) {
                  if( $cfDef['name'] === $cfname ) {
                    $ghost .=
                    $this->cfield_mgr->string_custom_field_value($cfDef,$tcversion_id);
                  }
                }

                // reconstruct the contect with the other pieces
                $lim = $elc-1;
                for($cpx=1; $cpx <= $lim; $cpx++) {
                  $ghost .= $yy[$cpx];
                }
              } catch (Exception $e) {
                $ghost .= $rse[$item_key];
              }
            }
          } else {
            $ghost .= $xx[$xdx];
          }
        }
      }

      // reconstruct field contents
      if($ghost != '') {
        $rse[$item_key] = $ghost;
      }
    }
  }

 /**
   * get data about code links from external tool
   * 
   * @param resource &$db reference to database handler
   * @param object &$code_interface reference to instance of bugTracker class
   * @param integer $tcversion_id Identifier of test case version
   * 
   * @return array list of 'script_name' with values: link_to_cts,
   * project_key, repository_name, code_path, branch_name
   */
  function getScriptsForTestCaseVersion(&$code_interface,$tcversion_id) {
    $tables = tlObjectWithDB::getDBTables(array('tcversions','testcase_script_links'));
    $script_list=array();

    $debugMsg = 'FILE:: ' . __FILE__ . ' :: FUNCTION:: ' . __FUNCTION__;
    if( is_object($code_interface) )
    {

      $sql =  "/* $debugMsg */ SELECT TSL.*,TCV.version " .
              " FROM {$tables['testcase_script_links']} TSL, {$tables['tcversions']} TCV " .
              " WHERE TSL.tcversion_id = " . intval($tcversion_id) .
              " AND   TSL.tcversion_id = TCV.id " .
              " ORDER BY TSL.code_path";

      $map = $this->db->get_recordset($sql);
      if( !is_null($map) )
      {
        $opt = array();
        foreach($map as $elem)
        {
          $script_id = $elem['project_key'].'&&'.$elem['repository_name'].'&&'.$elem['code_path'];
          if(!isset($script_list[$script_id]))
          {
            $opt['branch'] = $elem['branch_name'];
            $opt['commit_id'] = $elem['commit_id'];
            $dummy = $code_interface->buildViewCodeLink($elem['project_key'],
                     $elem['repository_name'],$elem['code_path'],$opt);
            $script_list[$script_id]['link_to_cts'] = $dummy->link;
            $script_list[$script_id]['project_key'] = $elem['project_key'];
            $script_list[$script_id]['repository_name'] = $elem['repository_name'];
            $script_list[$script_id]['code_path'] = $elem['code_path'];
            $script_list[$script_id]['branch_name'] = $elem['branch_name'];
            $script_list[$script_id]['commit_id'] = $elem['commit_id'];
            $script_list[$script_id]['tcversion_id'] = $elem['tcversion_id'];
          }
          unset($dummy);
        }
      }
    }

    if(count($script_list) === 0)
    {
      $script_list = null;
    }
    return($script_list);
  }


  /**
   *
   */
  function CKEditorCopyAndPasteCleanUp(&$items,$keys)
  {
    $offending = array('<body id="cke_pastebin"','</body>');
    $good = array('&lt;body id="cke_pastebin"','&lt;/body&gt;');
    foreach($keys as $fi)
    {
      $items->$fi = str_ireplace($offending,$good,$items->$fi);
    } 
  }

  /**
   *
   *
   */
  function getPathName($tcase_id) {

    $pathInfo = $this->tree_manager->get_full_path_verbose($tcase_id,array('output_format' => 'id_name'));
    $pathInfo = current($pathInfo);
    $path = '/' . implode('/',$pathInfo['name']) . '/';

    $pfx = $this->tproject_mgr->getTestCasePrefix($pathInfo['node_id'][0]);

    $info = $this->get_last_version_info($tcase_id, array('output' => 'medium'));

    $path .= $pfx . $this->cfg->testcase->glue_character .
             $info['tc_external_id'] . ':' . $info['name'];

    return $path;
  }



  /**
   * build Test Case Name getting information
   * from special marked text inside string
   *
   * string can be test case summary or test case precondition
   */
  function buildTCName($name, $text2scan) {
      
    $taglen = strlen(self::NAME_PHOPEN);
    $whomai = array('l' => '','r' => '');

    $where['open'] = strpos($name, self::NAME_PHOPEN);
    $where['close'] = strpos($name, self::NAME_PHCLOSE);

    if( FALSE !== $where['open'] ) {
      $whoami['l'] = substr($name, 0, $where['open']);
      $meat = substr($name,$where['open']+$taglen, ($where['close'] - $where['open']-$taglen) );  


      $dummy = strstr($name,self::NAME_PHCLOSE);
      $whoami['r'] = '';   
      if( $dummy !== FALSE ) {
        $whoami['r'] = ltrim($dummy,self::NAME_PHCLOSE);   
      }    

      $dm = explode(self::NAME_DIVIDE, $meat);
      $name = $whoami['l'] . self::NAME_PHOPEN; 

      $juice = $this->orangeJuice($text2scan);
      $name .=  ( count($dm) > 0 ) ? $dm[0] : $meat;
      $name .= self::NAME_DIVIDE . $juice . self::NAME_PHCLOSE . $whoami['r']; 
    }
    return $name;
  }

  /**
   *
   */
  function orangeJuice($str) {
  
    $juice = '';
    $taglen = strlen(self::NAME_PHOPEN);

    $where['open'] = strpos($str, self::NAME_PHOPEN);
    $where['close'] = strpos($str, self::NAME_PHCLOSE);

    if( FALSE !== $where['open'] ) {
      $juice = substr($str,$where['open']+$taglen, ($where['close'] - $where['open']-$taglen) );  
    }
    return $juice;
  }

  /**
   *
   */
  function getVersionNumber($version_id) {

    $sql = " SELECT version FROM {$this->tables['tcversions']} " .
           " WHERE id=" . intval($version_id);

    $rs = $this->db->get_recordset($sql);
    
    return $rs[0]['version'];
  }  

  /**
   *
   */
  function getAllVersionsID( $id ) {

    $debugMsg = "/* {$this->debugMsg}" . __FUNCTION__ . ' */ ';

    $target = (array)$id;
    array_walk($target,'intval');
    
    $sql = $debugMsg . 
           " SELECT id AS tcversion_id " . 
           " FROM {$this->tables['nodes_hierarchy']} NHTCV " .
           " WHERE NHTCV.parent_id =" . implode(',',$target) .
           " AND NHTCV.node_type_id = " . 
           $this->node_types_descr_id['testcase_version'];

    $xx = $this->db->fetchRowsIntoMap( $sql, 'tcversion_id' );

    if( null != $xx && count($xx) > 0 ) {
      return array_keys($xx);
    } 

    return null;      
  }


  /**
   *
   */
  function getAttXMLCfg() {
    $attXML = new stdClass();

    $attXML->root = "\t<attachments>\n{{XMLCODE}}\t</attachments>\n";
    $attXML->elemTPL = "\t\t<attachment>\n" .
                       "\t\t\t<id><![CDATA[||ATTACHMENT_ID||]]></id>\n" .
                       "\t\t\t<name><![CDATA[||ATTACHMENT_NAME||]]></name>\n" .
                       "\t\t\t<file_type><![CDATA[||ATTACHMENT_FILE_TYPE||]]></file_type>\n" .
                      "\t\t\t<file_size><![CDATA[||ATTACHMENT_FILE_SIZE||]]></file_size>\n" .
                      "\t\t\t<title><![CDATA[||ATTACHMENT_TITLE||]]></title>\n" .
                      "\t\t\t<date_added><![CDATA[||ATTACHMENT_DATE_ADDED||]]></date_added>\n" .
                      "\t\t\t<content><![CDATA[||ATTACHMENT_CONTENT||]]></content>\n" .
                      "\t\t</attachment>\n";

    $attXML->decode = array ("||ATTACHMENT_ID||" => "id", 
                             "||ATTACHMENT_NAME||" => "name",
                             "||ATTACHMENT_FILE_TYPE||" => "file_type",
                             "||ATTACHMENT_FILE_SIZE||" => "file_size", 
                             "||ATTACHMENT_TITLE||" => "title",
                             "||ATTACHMENT_DATE_ADDED||" => "date_added", 
                             "||ATTACHMENT_CONTENT||" => "content");

    return $attXML;  
  }


  /**
   *
   */
  function closeOpenTCVRelation( $relationID, $reason ) {

    $debugMsg = "/* {$this->debugMsg}" . __FUNCTION__ . ' */ ';
    $sql = " $debugMsg UPDATE {$this->tables['testcase_relations']} " .
           " SET link_status = " . intval($reason) .
           " WHERE id IN(" . implode(',',$relationID) . ")" . 
           " AND link_status = " . LINK_TC_RELATION_OPEN; 

    $this->db->exec_query($sql);

    // No audit yet
  }


  /**
   *
   **/
  function copyTCVRelations($source_id,$dest_id) {

    // Step 1 - get existent relations
    $relSource = $this->getTCVRelationsRaw($source_id,array('side' => 'source'));
    $relDest = $this->getTCVRelationsRaw($source_id,array('side' => 'dest'));

    $ins = "(source_id,destination_id,relation_type," .
           " link_status,author_id) ";

    $values = array();
    if( null != $relSource && count($relSource) > 0) {
      foreach ($relSource as $key => $elem) {
        $stm = "($dest_id,{$elem['destination_id']}," .
               "{$elem['relation_type']},{$elem['link_status']}," .
               "{$elem['author_id']})";
        $values[] = $stm;        
      } 
    }

    if( null != $relDest && count($relDest) > 0) {
      foreach ($relDest as $key => $elem) {
        $stm = "({$elem['source_id']},$dest_id," .
               "{$elem['relation_type']},{$elem['link_status']}," .
               "{$elem['author_id']})";
        $values[] = $stm;        
      } 
    }

    if( count($values) > 0 ) {
      $sql = 'INSERT INTO ' . $this->tables['testcase_relations'] .
             $ins . ' VALUES ' . implode(',',$values);

      $this->db->exec_query($sql);
    }


    // public function addRelation($source_id, $destination_id, $type_id, $author_id, $ts=null) {

  }


 /**
  *
  */
  function updateCoverage($link,$whoWhen,$opt=null) {

    // Set coverage for previous version to FROZEN & INACTIVE
    // Create coverage for NEW Version
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $options = array('freezePrevious' => true);
    $options = array_merge($options,(array)$opt);
    $safeF = intval($link['source']);
    $safeT = intval($link['dest']);

    // Set coverage for previous version to FROZEN & INACTIVE ?
    if( $options['freezePrevious'] ) {
      $sql = " /* $debugMsg */ " .
             " UPDATE {$this->tables['req_coverage']} " .
             " SET link_status = " . LINK_TC_REQ_CLOSED_BY_NEW_TCVERSION . "," .
             "     is_active=0 " .
             " WHERE tcversion_id=" . $safeF; 
      $this->db->exec_query($sql);      
    }

    // Create coverage for NEW Version
    $sql = "/* $debugMsg */ " .
           " INSERT INTO {$this->tables['req_coverage']} " .
           " (req_id,req_version_id,testcase_id,tcversion_id," .
           "  author_id,creation_ts) " .

           " SELECT req_id,req_version_id,testcase_id, " .
           "        {$safeT} AS tcversion_id," .
           "        {$whoWhen['user_id']} AS author_id, " .
           "        {$whoWhen['when']} AS creation_ts" .
           " FROM {$this->tables['req_coverage']} " .
           " WHERE tcversion_id=" . $safeF;
   
    $this->db->exec_query($sql);

  }

  /**
   *
   */
  function closeOpenReqLinks( $tcversion_id, $reason, $opt=null ) {

    $debugMsg = "/* {$this->debugMsg}" . __FUNCTION__ . ' */ ';

    $options = array('freeze_req_version' => false);
    $options = array_merge($options,(array)$opt);

    $commonWhere = " WHERE tcversion_id = " . intval($tcversion_id) .
                   " AND link_status = " . LINK_TC_REQ_OPEN; 


    // This has to be done BEFORE changing link_status
    if( $options['freeze_req_version'] ) {

     // https://stackoverflow.com/questions/11369757/postgres-wont-accept-table-alias-before-column-name
     $sql = " $debugMsg UPDATE {$this->tables['req_versions']}
               SET is_open = 0
               WHERE id IN (
                 SELECT req_version_id 
                 FROM {$this->tables['req_coverage']}
                 $commonWhere
               ) ";

      $this->db->exec_query($sql);
    }


    // Work on Coverage
    $sql = " $debugMsg UPDATE {$this->tables['req_coverage']} " .
           " SET link_status = " . intval($reason) . $commonWhere;
    $this->db->exec_query($sql);

    // No audit yet
  }

  /**
   *
   */
  function closeOpenReqVersionOnOpenLinks( $tcversion_id ) {

    $debugMsg = "/* {$this->debugMsg}" . __FUNCTION__ . ' */ ';

    $commonWhere = " WHERE tcversion_id = " . intval($tcversion_id) .
                   " AND link_status = " . LINK_TC_REQ_OPEN; 

    // https://stackoverflow.com/questions/11369757/postgres-wont-accept-table-alias-before-column-name
    $sql = " $debugMsg UPDATE {$this->tables['req_versions']}
             SET is_open = 0
             WHERE id IN (
                 SELECT req_version_id 
                 FROM {$this->tables['req_coverage']} 
                 $commonWhere
             ) AND is_open = 1";

    $this->db->exec_query($sql);
  }


 /**
   * copyReqVersionLinksTo
   * $source test case info.
   * $dest test case info.
   *
   */
  function copyReqVersionLinksTo($source,$dest,$mappings,$userID) {

    static $reqMgr;
    if( is_null($reqMgr) ) {
      $reqMgr=new requirement_mgr($this->db);
    }

    $itemSet = $reqMgr->getGoodForTCVersion($source['version_id']);
    
    if( !is_null($itemSet) ) {

      $reqSet = null;
      $reqVerSet = null; 

      $loop2do=count($itemSet);
      for($idx=0; $idx < $loop2do; $idx++) {

        $reqID = $itemSet[$idx]['req_id'];
        $reqVerID = $itemSet[$idx]['req_version_id'];

        if( isset($mappings['req'][$reqID]) ) {
          $reqSet[$idx] = $mappings['req'][$reqID];
          $reqVerSet[$idx] = $mappings['req_version'][$reqVerID];          
        } else {
          $reqSet[$idx] = $reqID;
          $reqVerSet[$idx] = $reqVerID;                    
        }

        $reqIdCard = array('id' => $reqSet[$idx], 
                           'version_id' => $reqVerSet[$idx]);
        $reqMgr->assignReqVerToTCVer($reqIdCard, $dest, $userID);
      }
    }
  }


  /**
   *
   */
  function getReqXMLCfg() {

    $cfgXML = new stdClass();

    $cfgXML->root = "\t<requirements>\n{{XMLCODE}}\t</requirements>\n";
    $cfgXML->elemTPL = 
      "\t\t<requirement>\n" .
      "\t\t\t<req_spec_title><![CDATA[||REQ_SPEC_TITLE||]]></req_spec_title>\n" .
      "\t\t\t<doc_id><![CDATA[||REQ_DOC_ID||]]></doc_id>\n" .
      "\t\t\t<version><![CDATA[||REQ_VERSION||]]></version>\n" .
      "\t\t\t<title><![CDATA[||REQ_TITLE||]]></title>\n" .
      "\t\t</requirement>\n";

    $cfgXML->decode = array( "||REQ_SPEC_TITLE||" => "req_spec_title",
                             "||REQ_DOC_ID||" => "req_doc_id",
                             "||REQ_VERSION||" => "version",
                             "||REQ_TITLE||" => "title");

    return $cfgXML;  
  }



  /**
   *
   */
  function getLatestVersionID($tcaseID) {
    $sql = "SELECT LTCV.tcversion_id 
            FROM {$this->views['latest_tcase_version_id']} LTCV
            WHERE LTCV.testcase_id=" . intval($tcaseID);

    $rs = current($this->db->get_recordset($sql));
            
    return $rs['tcversion_id'];
  }
  
 /**
  * render CF BUILD values with a defined name prefix
  *
  * <p> </p> added by web rich editor create some layout issues
  */
  function renderBuildExecVars($context,&$item2render) {

    static $execVars;
    
    $tplan_id = $context['tplan_id'];
    $build_id = $context['build_id'];

    $sql = " SELECT parent_id FROM {$this->tables['nodes_hierarchy']} NHTP
             WHERE NHTP.id = $tplan_id 
             AND NHTP.node_type_id = {$this->node_types_descr_id['testplan']} ";
    $dummy = current($this->db->get_recordset($sql));

    $tproject_id = $dummy['parent_id'];

    if( !($execVars) ) {
      $cfx = array('tproject_id' => $tproject_id, 'node_type' => 'build',
                   'node_id' => $build_id);
      $CFSet = 
        $this->cfield_mgr->getLinkedCfieldsAtDesign($cfx);

      $execVars = array();
      foreach($CFSet as $cfDef) {
        $execVars[$cfDef['name']] = 
          $this->cfield_mgr->string_custom_field_value($cfDef,$build_id);
      } 
    }

    $tcase_id = $item2render['testcase_id'];
    $tcversion_id = $item2render['id'];
    if( is_null($execVars) ) {
      return;
    }

    $key2check = array('summary','preconditions');
    $tlBeginTag = '[tlExecVar]';
    $tlEndTag = '[/tlExecVar]';
    $tlEndTagLen = strlen($tlEndTag);

    // I've discovered that working with Web Rich Editor generates
    // some additional not wanted entities, that disturb a lot
    // when trying to use json_decode().
    // Hope this set is enough.
    // $replaceSet = array($tlEndTag, '</p>', '<p>','&nbsp;');
    // $replaceSetWebRichEditor = array('</p>', '<p>','&nbsp;');

    $rse = &$item2render;
    foreach($key2check as $item_key) {
      $start = strpos($rse[$item_key],$tlBeginTag);
      $ghost = $rse[$item_key];

      // There is at least one request to replace ?
      if($start !== FALSE) {
        // This way remove may be the <p> that webrich editor adds
        $play = $rse[$item_key];
        $xx = explode($tlBeginTag,$play);

        // How many requests to replace ?
        $xx2do = count($xx);
        $ghost = '';
        for($xdx=0; $xdx < $xx2do; $xdx++) {

          // Hope was not a false request.
          if( ($es=strpos($xx[$xdx],$tlEndTag)) !== FALSE) {
            // Separate command string from other text
            // Theorically can be just ONE, but it depends
            // is user had not messed things.
            $yy = explode($tlEndTag,$xx[$xdx]);
            if( ($elc = count($yy)) > 0) {
              $cfname = trim($yy[0]);
              try {
                // look for the custom field
                foreach ($execVars as $cfn => $cfv ) {
                  if( $cfn === $cfname ) {
                    $ghost .= $execVars[$cfname];
                  }
                }

                // reconstruct the contect with the other pieces
                $lim = $elc-1;
                for($cpx=1; $cpx <= $lim; $cpx++) {
                  $ghost .= $yy[$cpx];
                }
              } catch (Exception $e) {
                $ghost .= $rse[$item_key];
              }
            }
          } else {
            $ghost .= $xx[$xdx];
          }
        }
      }

      // reconstruct field contents
      if($ghost != '') {
        $rse[$item_key] = $ghost;
      }
    }
  }

 /**
  * render Special Test Suite Keywords
  *
  * <p> </p> added by web rich editor create some layout issues
  */
  function renderSpecialTSuiteKeywords(&$item2render) {

    static $skwSet;
    static $key2check;

    $tcase_id = $item2render['testcase_id'];
    $tcversion_id = $item2render['id'];

    if( !$key2check ) {
      $key2check = array('summary','preconditions');
    }

    if( !$skwSet[$tcase_id] ) {
      $optSKW = array('getTSuiteKeywords' => true);
      $skwSet[$tcase_id] = $this->getPathLayered($tcase_id,$optSKW);      
    }

    if( is_null($skwSet) ) {
      return;
    }

    $rse = &$item2render;

    // From PHP Documentation
    // $phrase  = "You should eat fruits, vegetables, and fiber every day.";
    // $healthy = array("fruits", "vegetables", "fiber");
    // $yummy   = array("pizza", "beer", "ice cream");
    // 
    // $newphrase = str_replace($healthy, $yummy, $phrase);
    // Provides: You should eat pizza, beer, and ice cream every day
    //
    $searchSet = null;
    $replaceSet = null;
    foreach($skwSet as $xdx => $eSet ) {
      foreach($eSet as $dm) {
        if( null != $dm['data_management'] ) {
          foreach($dm['data_management'] as $search => $replace ) {
            $searchSet[] = $search;
            $replaceSet[] = $replace;
          }
        }
      }
    }

    foreach($key2check as $item_key) {       
      $rse[$item_key] = str_replace($searchSet,$replaceSet,$rse[$item_key]);
    }

  } 

}  // Class end
