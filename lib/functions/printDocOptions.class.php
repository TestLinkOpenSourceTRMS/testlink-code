<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later.
 *  
 */
class printDocOptions {

  protected $doc;
  protected $reqSpec;
  protected $testSpec;
  protected $exec;

  /**
   *
   */
  function __construct() {

    $this->doc = array();

    // element format
    // 
    // 'value' => 'toc','description' => 'opt_show_toc','checked' => 'n'
    // 'value': will be used to get the value
    // 'description': label id, to be used for localization
    //
    // if checked is not present => 'checked' => 'n'
    //
    $this->doc[] = array( 'value' => 'toc','description' => 'opt_show_toc');
    $this->doc[] = array( 'value' => 'headerNumbering','description' => 'opt_show_hdrNumbering');

    // Specific for Documents regarding Requirement Specifications
    $this->reqSpec = array();
    $key2init = array('req_spec_scope','req_spec_author',
                      'req_spec_overwritten_count_reqs',
                      'req_spec_type','req_spec_cf','req_scope',
                      'req_author','req_status',
                      'req_type','req_cf','req_relations',
                      'req_linked_tcs','req_coverage','displayVersion');

    $yes = array('req_spec_scope' => 'y','req_scope' => 'y');
    foreach($key2init as $key) {
      $yn = isset($key2init2yes[$key]) ? $key2init2yes[$key] : 'n';
      $this->reqSpec[] = array('value' => $key,'checked' => $yn,
                               'description' => 'opt_' . $key);
    } 

    $this->testSpec = array();
    $this->testSpec[] = array('value' => 'header','description' => 'opt_show_suite_txt');
    $this->testSpec[] = array('value' => 'summary','description' => 'opt_show_tc_summary','checked' => 'y');
    $this->testSpec[] = array('value' => 'body','description' => 'opt_show_tc_body');
    $this->testSpec[] = array('value' => 'author','description' => 'opt_show_tc_author');
    $this->testSpec[] = array('value' => 'keyword','description' => 'opt_show_tc_keys');
    $this->testSpec[] = array('value' => 'cfields','description' => 'opt_show_cfields');
    $this->testSpec[] = array( 'value' => 'requirement','description' => 'opt_show_tc_reqs');

    $this->exec = array(); 
    $this->exec[] = array( 'value' => 'execResultsByCFOnExecCombination','description' => 'opt_cfexec_comb');

    $this->exec[] = array('value' => 'notes', 'description' => 'opt_show_tc_notes');
        
    $this->exec[] = array('value' => 'step_exec_notes', 'description' => 'opt_show_tcstep_exec_notes');

    $this->exec[] = array('value' => 'passfail','description' => 'opt_show_passfail','checked' => 'y');
        
    $this->exec[] = array('value' => 'step_exec_status','description' => 'opt_show_tcstep_exec_status','checked' => 'y');
        
    $this->exec[] = array('value' => 'build_cfields','description' => 'opt_show_build_cfields','checked' => 'n');
    $this->exec[] = array('value' => 'metrics','description' => 'opt_show_metrics');

  }

  /**
   *
   */
  function getDocOpt() {
    return $this->doc;
  }

  /**
   *
   */
  function getTestSpecOpt() {
    return $this->testSpec;
  }

  /**
   *
   */
  function getReqSpecOpt() {
    return $this->reqSpec;
  }


  /**
   *
   */
  function getExecOpt() {
    return $this->exec;
  }


  /**
   *
   */
  function getAllOptVars() {

    $ov = array();
    $prop = array('doc','testSpec','reqSpec','exec');
    foreach($prop as $pp) {
      foreach($this->$pp as $ele) {
        $ov[$ele['value']] = isset($ele['checked']) ? $ele['checked'] : 'n';
        $ov[$ele['value']] = ($ov[$ele['value']] == 'y') ? 1 : 0; 
      }      
    }

    return $ov;
  }

  /**
   *
   */
  function getJSPrintPreferences() {

    $ov = array();
    $prop = array("doc","testSpec","reqSpec","exec");
    foreach($prop as $pp) {
      foreach($this->$pp as $ele) {
        $ov[] = $ele['value']; 
      }      
    }
    return implode(',',$ov);
  }






}
