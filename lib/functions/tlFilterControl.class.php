<?php

/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @package     TestLink
 * @author      Andreas Simon
 * @copyright   2006-2013, TestLink community
 * @filesource  tlFilterControl.class.php
 * @link        http://www.teamst.org/index.php
 *
 * Common logic to be used at GUI level to manage a common set of settings and filters.
 * It is used when filters or subviews of the test case or requirement tree are needed.
 * It is extended by the subclasses tlRequirementFilterPanel and tlTestCaseFilterPanel,
 * which hold specific changes for each of these object types.
 * Main class is abstract because it shall not be used/instantiated directly.
 *
 * @internal revisions
 * @since 1.9.9
 * 20130926 - franciscom - TICKET 5937: (Required) Custom Fields become mandatory in Filters Section
 *
 */

/**
 * Common logic to be used at GUI level to manage a common set of settings and filters.
 * It is used when filters or subviews of the test case or requirement tree are needed.
 * It is extended by the subclasses tlRequirementFilterPanel and tlTestCaseFilterPanel,
 * which hold specific changes and methods for each of these object types.
 * This main class is abstract because it shall not be used/instantiated directly.
 *
 * @author Andreas Simon
 * @package TestLink
 * @uses testproject
 */
abstract class tlFilterControl extends tlObjectWithDB 
{
  /**
   * Label (and name) for the button to enable simple filter mode. 
   * @var string
   */
  const SIMPLE_FILTER_BUTTON_LABEL = "btn_simple_filters";
  
  /**
   * Label (and name) for the button to enable advanced filter mode. 
   * @var string
   */
  const ADVANCED_FILTER_BUTTON_LABEL = "btn_advanced_filters";

  /**
   * how many filter items will be displayed in a multiselect box in advanced filter mode?
   * @var int
   */
  const ADVANCED_FILTER_ITEM_QUANTITY = 4;

  /**
   * how many filter items will be displayed in a select box in simple filter mode?
   * @var int
   */
  const SIMPLE_FILTER_ITEM_QUANTITY = 1;

  /**
   * Length of custom field inputs in filter form.
   * @var int
   */
  const CF_INPUT_SIZE = 32;
  
  /**
   * Value of [ANY]-selection in advanced filter mode.
   * @var int
   */
  const ANY = 0;
  
  /**
   * defines, wether the button to unassign all test cases from test plan shall be drawn on template
   * @var bool
   */
  public $draw_tc_unassign_button = false;

  /**
   * defines, wether the button to update all linked test cases to their newest version
   * shall be drawn on template
   * @var bool
   */
  public $draw_bulk_update_button = false;
  
  /**
   * defines, wether the button to export test plan tree shall be drawn on template
   * @var bool
   */
  public $draw_export_testplan_button = false;  // BUGID 3270 - Export Test Plan in XML Format
    

  /**
   * @var bool
   */
  public $draw_import_xml_results_button = false;


  public $draw_tc_assignment_bulk_copy_button = false;

  /**
   * will hold the localized string options (any/none/somebody/...)
   * @var array
   */
  public $option_strings = array();

  /**
   * holds the configuration that will be read from config file
   * @var stdClass
   */
  public $configuration = null;

  /**
   * holds the user input read from request
   * @var stdClass
   */
  public $args = null;

  /**
   * Will hold the configuration of filters (which ones are to be shown) and their values,
   * that can be selected on GUI, if active.
   * @var array
   */
  public $filters = array();

  /**
   * This array holds only the user selected values of active filters. It will be passed
   * to the underlying tree filter functions to set the values which are to be filtered.
   * @var array
   */
  protected $active_filters = array();
  
  /**
   * will hold the configuration about settings (which ones are to be shown) and their values
   * @var array
   */
  public $settings = array();

  /**
   * is advanced filter mode active?
   * @var bool
   */
  public $advanced_filter_mode = false;

  /**
   * if true, settings panel will be displayed, if false it will not be visible
   * @var bool
   */
  public $display_settings = false;

  /**
   * if true, filter panel will be displayed, if false it will not be visible
   * @var bool
   */
  public $display_filters = false;

  /**
   * If set to true, settings panel for requirements will be displayed.
   * @var bool
   */
  public $display_req_settings = false;
  
  /**
   * If set to true, filter panel for requirements will be displayed.
   * @var bool
   */
  public $display_req_filters = false;
  
  /**
   * Is it allowed to choose advanced filter mode?
   * @var bool
   */
  public $filter_mode_choice_enabled = true;

  /**
   * Holds the label for the button used to switch between filter modes (simple and advanced).
   * @var string
   */
  public $filter_mode_button_label = '';

  /**
   * Holds the filter item quantity (size of user inputs) for some of the menus.
   * @var int
   */
  public $filter_item_quantity = 0;

  /**
   * This variable marks wether filtering on the tree has to be done in PHP or if lazy loading
   * can be done in Javascript. It is TRUE, when user has sent data with filter/settings forms,
   * and filtering on tree has to be done. Otherwise (e.g. on first opening of forms) it is FALSE.
   * Value is always FALSE by default and after filter reset.
   * When one of the init_filter_* methods gets a selected value it then sets it to TRUE.
   * @var bool
   */
  public $do_filtering = false;


  // used by derived classes
  public $cfieldsCfg = null;
  
  /**
   * Testproject manager object.
   * Initialized not in constructor, only on first use to save resources.
   * @var testproject
   */
  public $testproject_mgr = null;

  // used by derived classes
  protected $cfield_mgr = null;

  /**
   *
   * @param database $dbHandler reference to database object
   */
  public function __construct(&$dbHandler) 
  {
    // call to constructor of parent class tlObjectWithDB
    parent::__construct($dbHandler);

    // Here comes all initializing work: First read the config, then user input.
    // According to these inputs all filters which are not needed will not be used.
    // Then initialize and use only the remaining filters.
    $this->read_config();
    $this->init_args();

    // set filter mode to advanced or simple
    $this->init_advanced_filter_mode();
   
    // init button labels
    if ($this->advanced_filter_mode) 
    {
      $label = self::SIMPLE_FILTER_BUTTON_LABEL;
      $qty = self::ADVANCED_FILTER_ITEM_QUANTITY;
    } 
    else 
    {
      $label = self::ADVANCED_FILTER_BUTTON_LABEL;
      $qty = self::SIMPLE_FILTER_ITEM_QUANTITY;
    }
    
    $this->filter_mode_button_label = lang_get($label);
    $this->filter_mode_button_name = $label;
    $this->filter_item_quantity = $qty;

    $this->init_settings();

  } // end of method

  /**
   * Destructor: deletes all member object which have to be deleted after use.
   * 
   */
  public function __destruct() {
    // delete member objects
    unset($this->testproject_mgr);
  } // end of method
  
  /**
   * Reads the configuration from the configuration file, which is not dependent on type of objects in tree.
   * This function has to be implemented and extended also in extending classes to read specialized config
   * for either test cases or requirements.
   * Function has protected (in subclasses private) visibility because it will only be called by __construct().
   * @return bool
   */
  protected function read_config() 
  {
    // opening and closing brackets
    $gui_open = config_get('gui_separator_open');
    $gui_close = config_get('gui_separator_close');

    // configure string options for select inputs
    $this->option_strings['any'] = $gui_open . lang_get('any') . $gui_close;
    $this->option_strings['none'] = $gui_open . lang_get('nobody') . $gui_close;
    $this->option_strings['somebody'] = $gui_open . lang_get('filter_somebody') . $gui_close;
    $this->option_strings['without_keywords'] = $gui_open . lang_get('without_keywords') . $gui_close;

    return tl::OK;
  } // end of method

  /**
   * Does what init_args() usually does in scripts: Reads the user input
   * from request ($_GET and $_POST). Then it modifies configuration,
   * settings and filters according to that user input.
   * While the implementation here loads generic input (unrelated to choice of
   * test case or requirements for the tree), it will be extended by
   * child classes to load input specific for requirements and test cases.
   */
  protected function init_args() 
  {

    $this->args = new stdClass();

    $this->args->basehref = $_SESSION['basehref'];
    
    // get user's data
    $this->user = $_SESSION['currentUser'];
    $this->args->user_id = $this->user->dbID;
    $this->args->user_name = $this->user->getDisplayName();
    
    $this->args->testproject_id = intval(isset($_SESSION['testprojectID']) ?
                                  $_SESSION['testprojectID'] : 0);
    $this->args->testproject_name = isset($_SESSION['testprojectName']) ?
                                    $_SESSION['testprojectName'] : 0;
    
    $params = array();
    $params['setting_refresh_tree_on_action'] = array("POST", tlInputParameter::CB_BOOL);
    $params['hidden_setting_refresh_tree_on_action'] =
           array("POST", tlInputParameter::INT_N);

    I_PARAMS($params, $this->args);

    // was a filter reset requested?
    $this->args->reset_filters = false;
    if (isset($_REQUEST['btn_reset_filters'])) {
      $this->args->reset_filters = true; // mark filter reset in args
      $this->do_filtering = false; // mark that no filtering has to be done after reset
    }
    
    // what filter mode has been chosen?
    $this->args->simple_filter_mode = 
      isset($_REQUEST[self::SIMPLE_FILTER_BUTTON_LABEL]) ? true : false;
    $this->args->advanced_filter_mode = 
      isset($_REQUEST[self::ADVANCED_FILTER_BUTTON_LABEL]) ? true : false;  

    $this->args->loadExecDashboard = true;
    if( isset($_REQUEST['loadExecDashboard']) )
    {
      $this->args->loadExecDashboard = intval($_REQUEST['loadExecDashboard']);
    }  

  } // end of method


  /**
   *
   */
  protected function init_filter_custom_fields($application_areas=null) 
  {
    $key = 'filter_custom_fields';
    $locale = (isset($_SESSION['locale'])) ? $_SESSION['locale'] : 'en_GB';
    $localesDateFormat = config_get('locales_date_format');
    $date_format = str_replace('%', '', $localesDateFormat[$locale]);
    
    $collapsed = isset($_SESSION['cf_filter_collapsed']) ? $_SESSION['cf_filter_collapsed'] : 0;
    $collapsed = isset($_REQUEST['btn_toggle_cf']) ? !$collapsed : $collapsed;
    $_SESSION['cf_filter_collapsed'] = $collapsed;  
    $btn_label = $collapsed ? lang_get('btn_show_cf') : lang_get('btn_hide_cf');
    
    $cfields = $this->getCustomFields($application_areas);
    $cf_prefix = $this->cfield_mgr->name_prefix;

    $cf_html_code = "";
    $selection = array();

    $this->filters[$key] = false;
    $this->active_filters[$key] = null;

    if (!is_null($cfields)) 
    {
      $cfInputOpt = array('name_suffix' => '', 'field_size' => self::CF_INPUT_SIZE, 
                          'show_on_filters' => true, 'remove_required' => true);

      foreach ($cfields as $cf_id => $cf) 
      {
        // has a value been selected?
        $id = $cf['id'];
        $type = $cf['type'];
        $verbose_type = trim($this->cfield_mgr->custom_field_types[$type]);
        $cf_input_name = "{$cf_prefix}{$type}_{$id}";

        // set special size for list inputs
        if ($verbose_type == 'list' || $verbose_type == 'multiselection list') 
        {
          $cfInputOpt['field_size'] = 3;
        }
        
        // custom fields on test spec did not retain value after apply
        // IMPORTANT/CRITIC issue:  trim() on array makes array = null !!!
        $value = isset($_REQUEST[$cf_input_name]) ? $_REQUEST[$cf_input_name] : null;

        if ($this->args->reset_filters) 
        {
          $value = null;
        }
        else
        {
          if ($verbose_type == 'datetime') 
          {
            // convert the three given values to unixtime format
            if (isset($_REQUEST[$cf_input_name . '_input']) && $_REQUEST[$cf_input_name . '_input'] != '' && 
                isset($_REQUEST[$cf_input_name . '_hour']) && $_REQUEST[$cf_input_name . '_hour'] != '' && 
                isset($_REQUEST[$cf_input_name . '_minute']) && $_REQUEST[$cf_input_name . '_minute'] != '' && 
                isset($_REQUEST[$cf_input_name . '_second']) && $_REQUEST[$cf_input_name . '_second'] != '') 
            {
              $date = $_REQUEST[$cf_input_name . '_input'];
              $hour = $_REQUEST[$cf_input_name . '_hour'];
              $minute = $_REQUEST[$cf_input_name . '_minute'];
              $second = $_REQUEST[$cf_input_name . '_second'];
                  
              $date_array = split_localized_date($date, $date_format);
              $value = mktime($hour, $minute, $second, $date_array['month'], $date_array['day'], $date_array['year']);
            }
          }
                  
          if ($verbose_type == 'date') 
          {
            // convert the three given values to unixtime format, only set values if different from 0
            if (isset($_REQUEST[$cf_input_name . '_input']) && $_REQUEST[$cf_input_name . '_input'] != '') 
            {
              $date = $_REQUEST[$cf_input_name . '_input'];           
              $date_array = split_localized_date($date, $date_format);
              $value = mktime(0, 0, 0, $date_array['month'], $date_array['day'], $date_array['year']);
            }
          }
        }
        
        $value2display = $value;
        if (!is_null($value2display) && is_array($value2display))
        {
          $value2display = implode("|", $value2display);
        }
        else 
        {
          $value = trim($value);
          $value2display = $value;
        }
        $cf['value'] = $value2display;

        if (!is_null($value) && $value !='') 
        {
          $this->do_filtering = true;
          $selection[$id] = $value;
        }

        $label = str_replace(TL_LOCALIZE_TAG, '', lang_get($cf['label'], null, LANG_GET_NO_WARNING));
        
        // don't show textarea inputs here, they are too large for filterpanel
        if ($verbose_type != 'text area') 
        {
          $cf_html_code .= '<tr class="cfRow"><td>' . htmlspecialchars($label) . '</td><td>' .
                           $this->cfield_mgr->string_custom_field_input($cf,$cfInputOpt) .
                           '</td></tr>';
        }
      }

      // show/hide CF
      $this->filters[$key] = array('items' => $cf_html_code,'btn_label' => $btn_label,'collapsed' => $collapsed);
      $this->active_filters[$key] = count($selection) ? $selection : null;
    }
  } // end of method


  /**
   *
   */
  protected function init_advanced_filter_mode() 
  {
    $this->advanced_filter_mode = ($this->filter_mode_choice_enabled && 
                                   $this->args->advanced_filter_mode && 
                                   !$this->args->simple_filter_mode);
 
  } // end of method


  
  /**
   * Initializes the class member array for settings 
   * according to the data loaded from database and user input.
   * Only initializes active settings, for a better performance.
   * Abstract: has to be implemented in any child class.
   */
  protected abstract function init_settings();

  /**
   * Initializes the class member array for filters 
   * according to the data loaded from database and user input.
   * Only initializes filters which are still enabled and active, for a better performance.
   * Abstract: has to be implemented in each child class.
   */
  protected abstract function init_filters();

  /**
   * Returns the filter array with necessary data,
   * ready to be processed/used by underlying filter functions in
   * test spec/exec/requirement tree generator functions.
   * Has to be implemented in child class.
   */
  protected abstract function get_active_filters();
  
  /**
   * Build the tree menu for generation of JavaScript tree of either test cases or requirements.
   * Depending on user selections in user interface, 
   * either a completely filtered tree will be build and returned,
   * or only the minimal necessary data to "lazy load" the objects in tree by later Ajax calls.
   * @param object $gui Reference to GUI object (information will be written to it)
   * @return object $tree_menu Tree object for display of JavaScript tree menu.
   */
  public abstract function build_tree_menu(&$gui);


  protected abstract function getCustomFields();


} // end of class