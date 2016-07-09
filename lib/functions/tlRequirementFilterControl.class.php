<?php

/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @package    TestLink
 * @author     Andreas Simon
 * @copyright  2006-2014, TestLink community
 * @filesource tlRequirementFilterControl.class.php
 * @link       http://www.testlink.org/
 *
 * Extends tlFilterPanel for the specific use with requirement tree.
 * It holds logic to be used at GUI level to manage a common set of settings and filters for requirements.
 * 
 * @internal revisions
 * @since 1.9.11
 */

/**
 * Extends tlFilterPanel for the specific use with requirement tree.
 * It holds logic to be used at GUI level to manage a common set of settings and filters for requirements.
 * 
 * @author Andreas Simon
 * 
 * @package TestLink
 **/
class tlRequirementFilterControl extends tlFilterControl 
{

  public $req_mgr = null;
  
  /**
   * This array contains all possible filters.
   * It is used as a helper to iterate over all the filters in some loops.
   * It also sets options how and from where to load the parameters with
   * input fetching functions in init_args()-method.
   * Its keys are the names of the settings (class constants are used),
   * its values are the arrays for the input parser.
   * @var array
   */
  private $all_filters = array('filter_doc_id' => array("POST", tlInputParameter::STRING_N),
                               'filter_title' => array("POST", tlInputParameter::STRING_N),
                               'filter_status' => array("POST", tlInputParameter::ARRAY_STRING_N),
                               'filter_type' => array("POST", tlInputParameter::ARRAY_INT),
                               'filter_spec_type' => array("POST", tlInputParameter::ARRAY_INT),
                               'filter_coverage' => array("POST", tlInputParameter::INT_N),
                               'filter_relation' => array("POST", tlInputParameter::ARRAY_STRING_N),
                               'filter_tc_id' => array("POST", tlInputParameter::STRING_N),
                               'filter_custom_fields' => null, 'filter_result' => false);
  
  /**
   * This array contains all possible settings. It is used as a helper
   * to later iterate over all possibilities in loops.
   * Its keys are the names of the settings, its values the arrays for the input parser.
   * @var array
   */
  private $all_settings = array('setting_refresh_tree_on_action' => 
                                array("POST", tlInputParameter::CB_BOOL));
  


  /**
   *
   */
  public function __construct(&$dbHandler) 
  {
    // Call to constructor of parent class tlFilterControl.
    // This already loads configuration and user input
    // and does all the remaining necessary method calls,
    // so no further method call is required here for initialization.
    parent::__construct($dbHandler);
    $this->req_mgr = new requirement_mgr($this->db);

    // ATTENTION if you do not see it when debugging, can be because
    // has been declared as PROTECTED
    $this->cfield_mgr = &$this->req_mgr->cfield_mgr;  

    // moved here from parent::__constructor() to be certain that 
    // all required objects has been created
    $this->init_filters();
  }


  public function __destruct() 
  {
    parent::__destruct(); //destroys testproject manager
    
    // destroy member objects
    unset($this->req_mgr);
  }

  protected function read_config() 
  {
    // some configuration reading already done in parent class
    parent::read_config();

    // load configuration for requirement filters
    $this->configuration = config_get('tree_filter_cfg')->requirements;

    // load req and req spec config (for types, filters, status, ...)
    $this->configuration->req_cfg = config_get('req_cfg');
    $this->configuration->req_spec_cfg = config_get('req_spec_cfg');
    
    // is choice of advanced filter mode enabled?
    $this->filter_mode_choice_enabled = false;
    if ($this->configuration->advanced_filter_mode_choice) 
    {
      $this->filter_mode_choice_enabled = true;
    }
    
    return tl::OK;
  }
  
  protected function init_args() 
  {
    // some common user input is already read in parent class
    parent::init_args();

    // add settings and filters to parameter info array for request parsers
    $params = array();
    foreach ($this->all_settings as $name => $info) 
    {
      if (is_array($info)) 
      {
        $params[$name] = $info;
      }
    }

    foreach ($this->all_filters as $name => $info) 
    {
      if (is_array($info)) 
      {
        $params[$name] = $info;
      }
    }
    I_PARAMS($params, $this->args);
  } // end of method

  /**
   * Initializes the class member array for settings 
   * according to the data loaded from database and user input.
   * Only initializes active settings, for a better performance.
   * If no settings are active, the complete panel will be disabled and not be displayed.
   */
  protected function init_settings() 
  {
    // $at_least_one_active = false;

    foreach ($this->all_settings as $name => $info) 
    {
      $init_method = "init_$name";
      if (method_exists($this, $init_method)) 
      {
        // is valid, configured, exists and therefore can be used, so initialize this setting
        $this->$init_method();
        // $at_least_one_active = true;
        $this->display_req_settings = true;
      } 
      else
      {
        // is not needed, simply deactivate it by setting it to false in main array
        $this->settings[$name] = false;
      }
    }

    // add the important settings to active filter array
    foreach ($this->all_settings as $name => $info) 
    {
      if ($this->settings[$name]) 
      {
        $this->active_filters[$name] = $this->settings[$name]['selected'];
      } 
      else 
      {
        $this->active_filters[$name] = null;
      }
    }

  } // end of method

  /**
   * Initializes the class member array for filters 
   * according to the data loaded from database and user input.
   * Only initializes filters which are still enabled and active, for a better performance.
   * If no filters are active at all, the filters panel will be disabled and not displayed.
   */
  protected function init_filters() 
  {
    // iterate through all filters and activate the needed ones
    if ($this->configuration->show_filters == ENABLED) 
    {
      foreach ($this->all_filters as $name => $info) 
      {
        $init_method = "init_$name";
        if (method_exists($this, $init_method) && $this->configuration->{$name} == ENABLED) 
        {
          $this->$init_method();
          $this->display_req_filters = true;
        } 
        else 
        {
          // is not needed, deactivate filter by setting it to false in main array
          // and of course also in active filters array
          $this->filters[$name] = false;
          $this->active_filters[$name] = null;
        }
      }
    } 
    else 
    {
      $this->display_req_filters = false;
    }
  } // end of method

  /**
   * Returns the filter array with necessary data,
   * ready to be processed/used by underlying filter functions in
   * requirement tree generator function.
   */
  protected function get_active_filters() 
  {
    return $this->active_filters;
  }
  
  /**
   * Build the tree menu for generation of JavaScript tree of requirements.
   * Depending on user selections in graphical user interface, 
   * either a completely filtered tree will be built and returned,
   * or only the minimal necessary data to "lazy load" the objects in tree by later Ajax calls.
   * @param object $gui Reference to GUI object (information will be written to it)
   * @return object $tree_menu Tree object for display of JavaScript tree menu.
   */
  public function build_tree_menu(&$gui) 
  {
    $tree_menu = null;
    $filters = $this->get_active_filters();
    $additional_info = null;
    $options = null;
    $loader = '';
    $children = "[]";
    
    // enable drag and drop
    $drag_and_drop = new stdClass();
    $drag_and_drop->enabled = true;
    $drag_and_drop->BackEndUrl = $gui->basehref . 'lib/ajax/dragdroprequirementnodes.php';
    $drag_and_drop->useBeforeMoveNode = TRUE;
        
    if (!$this->testproject_mgr) 
    {
      $this->testproject_mgr = new testproject($this->db);
    }
    
    // when we use filtering, the tree will be statically built,
    // otherwise it will be lazy loaded
    if ($this->do_filtering) 
    {
      $options = array('for_printing' => NOT_FOR_PRINTING,'exclude_branches' => null);
        
      $tree_menu = generate_reqspec_tree($this->db, $this->testproject_mgr,
                                         $this->args->testproject_id,
                                         $this->args->testproject_name,
                                         $filters, $options);
      
      $root_node = $tree_menu->rootnode;
      $root_node->name .= " ({$root_node->total_req_count})";
      $children = $tree_menu->menustring ? $tree_menu->menustring : "[]";
      $drag_and_drop->enabled = false;
    } 
    else 
    {
      $loader = $gui->basehref . 'lib/ajax/getrequirementnodes.php?mode=reqspec&' .
                                 "root_node={$this->args->testproject_id}";
      
      $req_qty = count($this->testproject_mgr->get_all_requirement_ids($this->args->testproject_id));
    
      $root_node = new stdClass();
      $root_node->href = "javascript:TPROJECT_REQ_SPEC_MGMT({$this->args->testproject_id})";
      $root_node->id = $this->args->testproject_id;
      $root_node->name = $this->args->testproject_name . " ($req_qty)";
      $root_node->testlink_node_type = 'testproject';
    } 
  
    $gui->ajaxTree = new stdClass();
    $gui->ajaxTree->loader = $loader;
    $gui->ajaxTree->root_node = $root_node;
    $gui->ajaxTree->children = $children;
    $gui->ajaxTree->dragDrop = $drag_and_drop;
    $gui->ajaxTree->cookiePrefix = 'req_specification_tproject_id_' . $root_node->id . "_" ;
  }
  
  /**
   *
   */
  private function init_setting_refresh_tree_on_action() 
  {
    $key = 'setting_refresh_tree_on_action';
    $hidden_key = 'hidden_setting_refresh_tree_on_action';
    $selection = 0;

    $this->settings[$key] = array();
    $this->settings[$key][$hidden_key] = 0;

    // look where we can find the setting - POST, SESSION, config?
    if (isset($this->args->{$key})) 
    {
      $selection = 1;
    } 
    else if (isset($this->args->{$hidden_key})) 
    {
      $selection = 0;
    } 
    else if (isset($_SESSION[$key])) 
    {
      $selection = $_SESSION[$key];
    } 
    else 
    {
      $selection = ($this->configuration->automatic_tree_refresh == ENABLED) ? 1 : 0;
    }
    
    $this->settings[$key]['selected'] = $selection;
    $this->settings[$key][$hidden_key] = $selection;
    $_SESSION[$key] = $selection;
  }
  

  /**
   *
   */
  private function init_filter_doc_id() 
  {
    $key = 'filter_doc_id';
    $selection = $this->args->{$key};
    
    if (!$selection || $this->args->reset_filters) 
    {
      $selection = null;
    } 
    else 
    {
      $this->do_filtering = true;
    }
    
    $this->filters[$key] = array('selected' => $selection);
    $this->active_filters[$key] = $selection;
  } // end of method
  

  private function init_filter_title() 
  {
    $key = 'filter_title';
    $selection = $this->args->{$key};
    
    if (!$selection || $this->args->reset_filters) {
      $selection = null;
    } else {
      $this->do_filtering = true;
    }
    
    $this->filters[$key] = array('selected' => $selection);
    $this->active_filters[$key] = $selection;
  } // end of method
  
  private function init_filter_status() {
    $key = 'filter_status';
    $selection = $this->args->{$key};
    
    // get configured statuses and add "any" string to menu
    $items = array(self::ANY => $this->option_strings['any']) + 
             (array) init_labels($this->configuration->req_cfg->status_labels);

    // BUGID 3852
    if (!$selection || $this->args->reset_filters
    || (is_array($selection) && in_array('0', $selection, true))) {
      $selection = null;
    } else {
      $this->do_filtering = true;
    }
    
    $this->filters[$key] = array('selected' => $selection, 'items' => $items);
    $this->active_filters[$key] = $selection;
  } // end of method

  /**
   *
   */ 
  private function init_filter_type() {
    $key = 'filter_type';
    $selection = $this->args->{$key};
    
    // get configured types and add "any" string to menu
    $items = array(self::ANY => $this->option_strings['any']) + 
             (array) init_labels($this->configuration->req_cfg->type_labels);
  
    if (!$selection || $this->args->reset_filters
    || (is_array($selection) && in_array(self::ANY, $selection))) {
      $selection = null;
    } else {
      $this->do_filtering = true;
    }
    
    $this->filters[$key] = array('selected' => $selection, 'items' => $items);
    $this->active_filters[$key] = $selection;
  } // end of method
  
  /**
   *
   */ 
  private function init_filter_spec_type() {
    $key = 'filter_spec_type';
    $selection = $this->args->{$key};
    
    // get configured types and add "any" string to menu
    $items = array(self::ANY => $this->option_strings['any']) + 
             (array) init_labels($this->configuration->req_spec_cfg->type_labels);
    
    if (!$selection || $this->args->reset_filters
    || (is_array($selection) && in_array(self::ANY, $selection))) {
      $selection = null;
    } else {
      $this->do_filtering = true;
    }
    
    $this->filters[$key] = array('selected' => $selection, 'items' => $items);
    $this->active_filters[$key] = $selection;
  } // end of method
  
  /**
   *
   */ 
  private function init_filter_coverage() {
    
    $key = 'filter_coverage';
    $this->filters[$key] = false;
    $this->active_filters[$key] = null;
    
    // is coverage management enabled?
    if ($this->configuration->req_cfg->expected_coverage_management) {
      $selection = $this->args->{$key};
    
      if (!$selection || !is_numeric($selection) || $this->args->reset_filters) {
        $selection = null;
      } else {
        $this->do_filtering = true;
      }
      
      $this->filters[$key] = array('selected' => $selection);
      $this->active_filters[$key] = $selection;
    }
  } // end of method
  
  /**
   *
   */ 
  private function init_filter_relation() {
    
    $key = 'filter_relation';
  
    // are relations enabled?
    if ($this->configuration->req_cfg->relations->enable) {
      $selection = $this->args->{$key};
      
      if (!$this->req_mgr) {
        $this->req_mgr = new requirement_mgr($this->db);
      }
      
      $req_relations = $this->req_mgr->init_relation_type_select();
      
      // special case here:
      // for equal type relations (where it doesn't matter if we find source or destination)
      // we have to remove the source identficator from the array key
      foreach ($req_relations['equal_relations'] as $array_key => $old_key) 
      {
        // set new key in array and delete old one
        $new_key = (int) str_replace("_source", "", $old_key);
        $req_relations['items'][$new_key] = $req_relations['items'][$old_key];
        unset($req_relations['items'][$old_key]);
      }
      
      $items = array(self::ANY => $this->option_strings['any']) + 
               (array) $req_relations['items'];

      if (!$selection || $this->args->reset_filters || 
          (is_array($selection) && in_array(self::ANY, $selection))) 
      {
        $selection = null;
      } 
      else 
      {
        $this->do_filtering = true;
      }
      
      $this->filters[$key] = array('selected' => $selection, 
                                   'items' => $items);
      $this->active_filters[$key] = $selection;
    } else {
      // not enabled, just nullify
      $this->filters[$key] = false;
      $this->active_filters[$key] = null;
    }   
  } // end of method
  
  /**
   *
   */ 
  private function init_filter_tc_id() 
  {
    $key = 'filter_tc_id';
    $selection = $this->args->{$key};
    
    if (!$this->testproject_mgr) {
      $this->testproject_mgr = new testproject($this->db);
    }
    
    $tc_cfg = config_get('testcase_cfg');
    $tc_prefix = $this->testproject_mgr->getTestCasePrefix($this->args->testproject_id);
    $tc_prefix .= $tc_cfg->glue_character;
    
    if (!$selection || $selection == $tc_prefix || $this->args->reset_filters) {
      $selection = null;
    } else {
      $this->do_filtering = true;
    }
    
    $this->filters[$key] = array('selected' => $selection ? $selection : $tc_prefix);
    $this->active_filters[$key] = $selection;
  } // end of method

  
  /**
   *
   */ 
  protected function getCustomFields()
  {
    if (!$this->req_mgr) 
    {
      $this->req_mgr = new requirement_mgr($this->db);
      $this->cfield_mgr = &$this->req_mgr->cfield_mgr;
    }
  
    $cfields = $this->req_mgr->get_linked_cfields(null, null, $this->args->testproject_id);
    return $cfields;
  }

} // end of class