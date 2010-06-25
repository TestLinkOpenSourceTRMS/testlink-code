<?php

/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @package    TestLink
 * @author     Andreas Simon
 * @copyright  2006-2010, TestLink community
 * @version    CVS: $Id: tlTestCaseFilterControl.class.php,v 1.4 2010/06/25 14:48:06 asimon83 Exp $
 * @link       http://www.teamst.org/index.php
 * @filesource http://testlink.cvs.sourceforge.net/viewvc/testlink/testlink/lib/functions/tlTestCaseFilterControl.class.php?view=markup
 *
 * This class extends tlFilterPanel for the specific use with test case tree.
 * It holds the logic to be used at GUI level to manage a common set of settings and filters for test cases.
 *
 * @internal Revisions:
 *
 * 20100517 - asimon - first implementation of filter control class hierarchy
 *                     to simplify/generalize filter panel handling for test cases and requirements
 */

/**
 * This class extends tlFilterPanel for the specific use with the testcase tree.
 * It contains logic to be used at GUI level to manage
 * a common set of settings and filters for testcases.
 *
 * @author Andreas Simon
 * @package TestLink
 * @uses testproject
 * @uses testplan
 * @uses exec_cf_mgr
 * @uses tlPlatform
 * @uses testcase
 **/
class tlTestCaseFilterControl extends tlFilterControl {

	/**
	 * class constant for exec mode
	 * @var int
	 */
	const EXECUTION_MODE = 'execution_mode';

	/**
	 * class constant for edit mode
	 * @var string
	 */
	const EDIT_MODE = 'edit_mode';

	/**
	 * class constant for plan mode
	 * @var int
	 */
	const PLAN_MODE = 'plan_mode';

	/**
	 * class constant for plan add mode
	 * @var int
	 */
	const PLAN_ADD_MODE = 'plan_add_mode';

	/**
	 * class constant for the filter testcase name
	 * @var string
	 */
	const FILTER_TESTCASE_NAME = 'filter_testcase_name';

	/**
	 * class constant for the filter toplevel testsuite
	 * @var string
	 */
	const FILTER_TOPLEVEL_TESTSUITE = 'filter_toplevel_testsuite';

	/**
	 * class constant for the filter for keywords
	 * @var string
	 */
	const FILTER_KEYWORDS = 'filter_keywords';

	/**
	 * class constant for the filter type used when filtering keywords
	 * @var string
	 */
	const FILTER_KEYWORDS_FILTER_TYPE = 'filter_keywords_filter_type';
	
	/**
	 * class constant for the filter priority
	 * @var string
	 */
	const FILTER_PRIORITY = 'filter_priority';

	/**
	 * class constant for the filter execution type
	 * @var string
	 */
	const FILTER_EXECUTION_TYPE = 'filter_execution_type';

	/**
	 * class constant for the filter assigned user
	 * @var string
	 */
	const FILTER_ASSIGNED_USER = 'filter_assigned_user';

	/**
	 * Class constant for the option to include unassigned testcases.
	 * @var string
	 */
	const FILTER_ASSIGNED_USER_INCLUDE_UNASSIGNED = 'filter_assigned_user_include_unassigned';
	
	/**
	 * class constant for the filter custom fields
	 * @var string
	 */
	const FILTER_CUSTOM_FIELDS = 'filter_custom_fields';

	/**
	 * class constant for the filter results
	 * @var string
	 */
	const FILTER_RESULT = 'filter_result';

	/**
	 * class constant for the results in results filter
	 * @var string
	 */
	const FILTER_RESULT_RESULT = 'filter_result_result';
	
	/**
	 * class constant for the method in results filter
	 * @var string
	 */
	const FILTER_RESULT_METHOD = 'filter_result_method';
	
	/**
	 * class constant for the build in results filter
	 * @var string
	 */
	const FILTER_RESULT_BUILD = 'filter_result_build';
	
	/**
	 * class constant for the setting testplan
	 * @var string
	 */
	const SETTING_TESTPLAN = 'setting_testplan';

	/**
	 * class constant for the setting platform
	 * @var string
	 */
	const SETTING_PLATFORM = 'setting_platform';

	/**
	 * class constant for the setting build
	 * @var string
	 */
	const SETTING_BUILD = 'setting_build';

	/**
	 * Testcase manager object.
	 * Initialized not in constructor, only on first use to save resources.
	 * @var testcase
	 */
	private $tc_mgr = null;
	
	/**
	 * Platform manager object.
	 * Initialized not in constructor, only on first use to save resources.
	 * @var tlPlatform
	 */
	private $platform_mgr = null;
	
	/**
	 * Custom field manager object.
	 * Initialized not in constructor, only on first use to save resources.
	 * @var exec_cf_mgr
	 */
	private $exec_cf_mgr = null;
	
	/**
	 * Testplan manager object.
	 * Initialized not in constructor, only on first use to save resources.
	 * @var testplan
	 */
	private $testplan_mgr = null;
	
	/**
	 * This array contains all possible filters.
	 * It is used as a helper to iterate over all the filters in some loops.
	 * It also sets options how and from where to load the parameters with
	 * input fetching functions in init_args()-method.
	 * Its keys are the names of the settings (class constants are used),
	 * its values are the arrays for the input parser.
	 * @var array
	 * TODO use correct constants/settings here for the length of parameters instead of magic numbers
	 */
	private $all_filters = array(self::FILTER_TESTCASE_NAME => array("POST", tlInputParameter::STRING_N, 0, 100),
	                             self::FILTER_TOPLEVEL_TESTSUITE => array("POST", tlInputParameter::STRING_N, 0, 100),
	                             self::FILTER_KEYWORDS => array("POST", tlInputParameter::ARRAY_INT),
	                             self::FILTER_PRIORITY => array("POST", tlInputParameter::INT_N),
	                             self::FILTER_EXECUTION_TYPE => array("POST", tlInputParameter::INT_N),
	                             self::FILTER_ASSIGNED_USER => array("POST", tlInputParameter::ARRAY_INT),
	                             self::FILTER_CUSTOM_FIELDS => array("POST", tlInputParameter::ARRAY_STRING_N),
	                             //self::FILTER_RESULT => array("POST", tlInputParameter::STRING_N, 1, 50));
	                             self::FILTER_RESULT => null);

	/**
	 * This array is used as an additional security measure. It maps all available
	 * filters to the mode in which they can be used. If a user tries to
	 * enable filters in config.inc.php which are not defined inside this array,
	 * this will be simply ignored instead of trying to initialize the filter
	 * no matter wether it has been implemented or not.
	 * The keys inside this array are the modes defined above as class constants.
	 * So it can be checked if a filter is available in a given mode without
	 * relying only on the config parameter.
	 * @var array
	 */
	private $mode_filter_mapping = array(self::EDIT_MODE => array(self::FILTER_TESTCASE_NAME,
	                                                              self::FILTER_TOPLEVEL_TESTSUITE,
	                                                              self::FILTER_KEYWORDS,
	                                                              self::FILTER_EXECUTION_TYPE,
	                                                              self::FILTER_CUSTOM_FIELDS),
	                                     self::EXECUTION_MODE => array(self::FILTER_TESTCASE_NAME,
	                                                                   self::FILTER_TOPLEVEL_TESTSUITE,
	                                                                   self::FILTER_KEYWORDS,
	                                                                   self::FILTER_PRIORITY,
	                                                                   self::FILTER_EXECUTION_TYPE,
	                                                                   self::FILTER_ASSIGNED_USER,
	                                                                   self::FILTER_CUSTOM_FIELDS,
	                                                                   self::FILTER_RESULT),
	                                     self::PLAN_MODE => array(self::FILTER_TESTCASE_NAME,
	                                                              self::FILTER_TOPLEVEL_TESTSUITE,
	                                                              self::FILTER_KEYWORDS,
	                                                              self::FILTER_PRIORITY,
	                                                              self::FILTER_EXECUTION_TYPE,
	                                                              self::FILTER_CUSTOM_FIELDS,
	                                                              self::FILTER_RESULT),
	                                     self::PLAN_ADD_MODE => array(self::FILTER_TESTCASE_NAME,
	                                                                  self::FILTER_TOPLEVEL_TESTSUITE,
	                                                                  self::FILTER_KEYWORDS,
	                                                                  self::FILTER_PRIORITY,
	                                                                  self::FILTER_EXECUTION_TYPE,
	                                                                  self::FILTER_CUSTOM_FIELDS));

	/**
	 * This array contains all possible settings. It is used as a helper
	 * to later iterate over all possibilities in loops.
	 * Its keys are the names of the settings, its values the arrays for the input parser.
	 * @var array
	 */
	private $all_settings = array(self::SETTING_TESTPLAN => array("POST", tlInputParameter::INT_N),
	                              self::SETTING_BUILD => array("POST", tlInputParameter::INT_N),
	                              self::SETTING_PLATFORM => array("POST", tlInputParameter::INT_N),
	                              self::SETTING_REFRESH_TREE_ON_ACTION => array("POST", tlInputParameter::CB_BOOL));

	/**
	 * This array is used to map the modes to their available settings.
	 * @var array
	 */
	private $mode_setting_mapping = array(self::EDIT_MODE => array(self::SETTING_REFRESH_TREE_ON_ACTION),
	                                      self::EXECUTION_MODE => array(self::SETTING_TESTPLAN,
	                                                                    self::SETTING_BUILD,
	                                                                    self::SETTING_PLATFORM,
	                                                                    self::SETTING_REFRESH_TREE_ON_ACTION),
	                                      self::PLAN_MODE => array(self::SETTING_TESTPLAN,
	                                                               //self::SETTING_BUILD,
	                                                               //self::SETTING_PLATFORM,
	                                                               self::SETTING_REFRESH_TREE_ON_ACTION),
	                                      self::PLAN_ADD_MODE => array(self::SETTING_TESTPLAN,
	                                                                   //self::SETTING_BUILD,
	                                                                   //self::SETTING_PLATFORM,
	                                                                   self::SETTING_REFRESH_TREE_ON_ACTION));

	/**
	 * The mode used. Depending on the feature for which this class will be instantiated.
	 * This mode defines which filter configuration will be loaded from config.inc.php
	 * and therefore which filters will be loaded and used for the templates.
	 * Value has to be one of the class constants for mode, default is edit mode.
	 * @var int
	 */
	private $mode = self::EDIT_MODE;

	/**
	 *
	 * @param database $dbHandler
	 * @param string $mode
	 */
	public function __construct(&$dbHandler, $mode = self::EDIT_MODE) {

		// set mode to define further actions before calling parent constructor
		if ($mode == self::EXECUTION_MODE || $mode == self::EDIT_MODE
		|| $mode == self::PLAN_MODE || $mode == self::PLAN_ADD_MODE) {
			$this->mode = $mode;
		} else {
			$this->mode = self::EDIT_MODE;
		}

		// Call to constructor of parent class tlFilterControl.
		// This already loads configuration and user input
		// and does all the remaining necessary method calls,
		// so no further method call is required here for initialization.
		parent::__construct($dbHandler);

		// delete any filter settings that may be left from previous calls in session
		$this->delete_own_session_data();
		$this->delete_old_session_data();
		
		$this->save_session_data();
	}

	public function __destruct() {
		parent::__destruct();
		
		// destroy member objects
		unset($this->tc_mgr);
		unset($this->testplan_mgr);
		unset($this->platform_mgr);
		unset($this->exec_cf_mgr);
	}

	/**
	 * Reads the configuration from the configuration file specific for test cases,
	 * additionally to those parts of the config which were already loaded by parent class.
	 *
	 */
	protected function read_config() {

		// some configuration reading already done in parent class
		parent::read_config();

		// load configuration for active mode only
		$this->configuration = config_get('tree_filter_cfg')->testcases->{$this->mode};

		// load also exec config - it is not only needed in exec mode
		$this->configuration->exec_cfg = config_get('exec_cfg');

		// some additional testcase configuration
		$this->configuration->tc_cfg = config_get('testcase_cfg');
		
		// is choice of advanced filter mode enabled?
    	if ($this->configuration->advanced_filter_mode_choice) {
    		$this->filter_mode_choice_enabled = true;
    	} else {
    		$this->filter_mode_choice_enabled = false;
    	}
		
		return tl::OK;
	} // end of method

	/**
	 * Does what init_args() usually does in all scripts: Reads the user input
	 * from request ($_GET and $_POST). Later configuration,
	 * settings and filters get modified according to that user input.
	 */
	protected function init_args() {
		
		// some common user input is already read in parent class
		parent::init_args();

		// add settings and filters to parameter info array for request parsers
		$params = array();
		foreach ($this->all_settings as $name => $info) {
			if (is_array($info)) {
				$params[$name] = $info;
			}
		}
		foreach ($this->all_filters as $name => $info) {
			if (is_array($info)) {
				$params[$name] = $info;
			}
		}
		I_PARAMS($params, $this->args);

		$type = self::FILTER_KEYWORDS_FILTER_TYPE;
		$this->args->{$type} = (isset($_REQUEST[$type])) ? trim($_REQUEST[$type]) : self::STR_OR;

		$extra_keys = array(self::FILTER_RESULT_RESULT,
		                    self::FILTER_RESULT_METHOD,
		                    self::FILTER_RESULT_BUILD);

		foreach ($extra_keys as $ek) {
			$this->args->{$ek} = (isset($_REQUEST[$ek])) ? $_REQUEST[$ek] : null;
		}

		$this->args->{self::FILTER_ASSIGNED_USER_INCLUDE_UNASSIGNED} = 
			isset($_REQUEST[self::FILTER_ASSIGNED_USER_INCLUDE_UNASSIGNED]) ? 1 : 0;
			
		// got session token sent by form or do we have to generate a new one?
		$sent_token = null;
		$this->args->form_token = null;
		if (isset($_REQUEST['form_token'])) {
			// token got sent
			$sent_token = $_REQUEST['form_token'];
		}
		if (!is_null($sent_token) && isset($_SESSION[$this->mode][$sent_token])) {
			// sent token is valid
			$this->form_token = $sent_token;
			$this->args->form_token = $sent_token;
		} else {
			$this->generate_form_token();
		}
		
		// "feature" is needed for plan and edit modes
		$this->args->feature = isset($_REQUEST['feature']) ? trim($_REQUEST['feature']) : null;
		
		switch ($this->mode) {
			
			case self::PLAN_MODE:
				switch($this->args->feature) {
					case 'planUpdateTC':
					case 'test_urgency':
					case 'tc_exec_assignment':
						// feature OK
					break;
				
					default:
						// feature not OK
						tLog("Wrong or missing GET argument 'feature'.", 'ERROR');
						exit();
					break;
				}
			break;
			
			case self::EDIT_MODE:
				switch($this->args->feature) {
					case 'edit_tc':
					case 'keywordsAssign':
					case 'assignReqs':
						// feature OK
					break;
				
					default:
						// feature not OK
						tLog("Wrong or missing GET argument 'feature'.", 'ERROR');
						exit();
					break;
				}
			break;
		}
	    
	} // end of method

	/**
	 * Initializes all settings.
	 * Iterates through all available settings and adds an array to $this->settings
	 * for the active ones, sets the rest to false so this can be
	 * checked from templates and elsewhere.
	 * Then calls the initializing method for each still active setting.
	 */
	protected function init_settings() {
		$at_least_one_active = false;

		foreach ($this->all_settings as $name => $info) {
			$init_method = "init_$name";
			if (in_array($name, $this->mode_setting_mapping[$this->mode])
			&& method_exists($this, $init_method)) {
				// is valid, configured, exists and therefore can be used, so initialize this setting
				$this->$init_method();
				$at_least_one_active = true;
			} else {
				// is not needed, simply deactivate it by setting it to false in main array
				$this->settings[$name] = false;
			}
		}

		// if at least one active setting is left to display, switch settings panel on
		if ($at_least_one_active) {
			$this->display_settings = true;
		}
	}

	/**
	 * Initialize all filters.
	 * I'm double checking here with loaded configuration _and_ additional array
	 * $mode_filter_mapping, set according to defined mode, because this can avoid errors in case
	 * when users try to enable a filter in config that doesn't exist for a mode.
	 * Effect: Only existing and implemented filters can be activated in config file.
	 */
	protected function init_filters() {
		
		// In resulting data structure, all values have to be defined (at least initialized),
		// no matter wether they are wanted for filtering or not.
		$additional_filters_to_init = array(self::FILTER_KEYWORDS_FILTER_TYPE,
		                                    self::FILTER_RESULT_RESULT,
		                                    self::FILTER_RESULT_METHOD,
		                                    self::FILTER_RESULT_BUILD,
		                                    self::FILTER_ASSIGNED_USER_INCLUDE_UNASSIGNED);
		
		// now nullify them
		foreach ($additional_filters_to_init as $filtername) {
			$this->active_filters[$filtername] = null;
		}
		
		$at_least_one_active = false;

		// iterate through all filters and activate the needed ones
		foreach ($this->all_filters as $name => $info) {
			$init_method = "init_$name";
			if (in_array($name, $this->mode_filter_mapping[$this->mode])
			&& method_exists($this, $init_method)
			&& $this->configuration->{$name} == ENABLED) {
				// valid
				$this->$init_method();
				$at_least_one_active = true;
			} else {
				// is not needed, deactivate filter by setting it to false in main array
				// and of course also in active filters array
				$this->filters[$name] = false;
				$this->active_filters[$name] = null;
			}
		}

		// add the important settings to active filter array
		foreach ($this->all_settings as $name => $info) {
			if ($this->settings[$name]) {
				$this->active_filters[$name] = $this->settings[$name][self::STR_SELECTED];
			} else {
				$this->active_filters[$name] = null;
			}
		}
				
		// if at least one filter item is left to display, switch panel on
		if ($at_least_one_active) {
			$this->display_filters = true;
		}
	} // end of method

	/**
	 * This method returns an object or array, containing all selections chosen
	 * by the user for filtering.
	 * 
	 * @return mixed $value Return value is either an array or stdClass object,
	 * depending on active mode. It contains all filter values selected by the user.
	 */
	protected function get_active_filters() {
		static $value = null; // serves as a kind of cache
		                      // if method is called more than once
				
		// convert array to stcClass if needed
		if (!$value) {
			switch ($this->mode) {
				case self::EXECUTION_MODE:
				case self::PLAN_MODE:
					// these features are generating an exec tree,
					// they need the filters as a stdClass object
					$value = (object) $this->active_filters;
					break;
				
				default:
					// otherwise simply return the array as-is
					$value = $this->active_filters;
					break;
			}
		}
		
		return $value;
	} // end of method

	public function set_testcases_to_show($testcases_to_show = null) {
		// update active_filters
		if (!is_null($testcases_to_show)) {
			$this->active_filters['testcases_to_show'] = $testcases_to_show;
		}
		
		// Since a new filter in active_filters has been set from outside class after
		// saving of session data has already happened in constructor, 
		// we explicitly update data in session after this change here.
		$this->save_session_data();
	}
	
	/**
	 * Active filters will be saved to $_SESSION. 
	 * If there already is data for the active mode and token, it will be overwritten.
	 * This data will be read from pages in the right frame.
	 * This solves the problems with too long URLs.
	 * See issue 3516 in Mantis for a little bit more information/explanation.
	 * The therefore caused new problem that would arise now if
	 * a user uses the same feature simultaneously in multiple browser tabs
	 * is solved be the additional measure of using a form token.
	 * 
	 * @author Andreas Simon
	 * @return $tl::OK
	 */
	public function save_session_data() {		
		if (!isset($_SESSION[$this->mode]) || is_null($_SESSION[$this->mode]) || !is_array($_SESSION[$this->mode])) {
			$_SESSION[$this->mode] = array();
		}
		
		$_SESSION[$this->mode][$this->form_token] = $this->active_filters;
		$_SESSION[$this->mode][$this->form_token]['timestamp'] = time();
		
		return tl::OK;
	}
	
	/**
	 * Old filter data for active mode will be deleted from $_SESSION.
	 * It happens automatically after a session has expired and a user therefore
	 * has to log in again, but here we can configure an additional time limit
	 * only for this special filter part in session data.
	 * 
	 * @author Andreas Simon
	 * @param int $token_validity_duration data older than given timespan will be deleted
	 */
	public function delete_old_session_data($token_validity_duration = 0) {
		// TODO this duration could maybe also be configured in config/const.inc.php
		
		// how long shall the data remain in session before it will be deleted?
		if (!is_numeric($token_validity_duration) || $token_validity_duration <= 0) {
			$token_validity_duration = 60 * 60 * 1; // one hour as default
		}
		
		// delete all tokens from session that are older than given age
		if (is_array($_SESSION[$this->mode])) {
			foreach ($_SESSION[$this->mode] as $token => $data) {
				if ($data['timestamp'] < (time() - $token_validity_duration)) {
					// too old, delete!
					unset($_SESSION[$this->mode][$token]);
				}
			}
		}
	}
	
	public function delete_own_session_data() {
		if (isset($_SESSION[$this->mode]) && isset($_SESSION[$this->mode][$this->form_token])) {
			unset($_SESSION[$this->mode][$this->form_token]);
		}
	}
	
	/**
	 * Generates a form token, which will be used to identify the relationship
	 * between left navigator-frame with its settings and right frame.
	 */
	protected function generate_form_token() {
		// Notice: I am just generating an integer here for the token.
		// Since this is not any security relevant stuff like a password hash or similar,
		// but only a means to separate multiple tabs a single user opens, this should suffice.
		// If we should some day decide that an integer is not enough,
		// we just have to change this one method and everything will still work.
		
		$min = 1234567890; // not magic, just some large number so the tokens don't get too short 
		$max = mt_getrandmax();
		$token = 0;
		
		// generate new tokens until we find one that doesn't exist yet
		do {
			$token = mt_rand($min, $max);
		} while (isset($_SESSION[$this->mode][$token]));
		
		$this->form_token = $token;
	}
	
	/**
	 * Active filters will be formatted as a GET-argument string.
	 * 
	 * @return string $string the formatted string with active filters
	 */
	public function get_argument_string() {
		static $string = null; // cache for repeated calls of this method
		
		if (!$string) {
			$string = '';

			// important: the token with which page in right frame can access data in session
			$string .= '&form_token=' . $this->form_token;
			
			if ($this->settings[self::SETTING_BUILD]) {
				$string .= '&' . self::SETTING_BUILD . '=' . 
				           $this->settings[self::SETTING_BUILD][self::STR_SELECTED];
			}
			
			if ($this->settings[self::SETTING_PLATFORM]) {
				$string .= '&' . self::SETTING_PLATFORM . '=' . 
				           $this->settings[self::SETTING_PLATFORM][self::STR_SELECTED];
			}
			
			$keyword_list = null;
			if (is_array($this->active_filters[self::FILTER_KEYWORDS])) {
				$keyword_list = implode(',', $this->active_filters[self::FILTER_KEYWORDS]);
			} else if ($this->active_filters[self::FILTER_KEYWORDS]) {
				$keyword_list = $this->active_filters[self::FILTER_KEYWORDS];
			}			
			if ($keyword_list) {
				$string .= '&' . self::FILTER_KEYWORDS . '=' . $keyword_list . 
				           '&' . self::FILTER_KEYWORDS_FILTER_TYPE . '=' . 
				           $this->active_filters[self::FILTER_KEYWORDS_FILTER_TYPE];
			}
			
			if ($this->active_filters[self::FILTER_PRIORITY] > 0) {
				$string .= '&' . self::FILTER_PRIORITY . '=' . $this->active_filters[self::FILTER_PRIORITY];
			}
						
			if ($this->active_filters[self::FILTER_ASSIGNED_USER]) {
				$string .= '&' . self::FILTER_ASSIGNED_USER . '='. 
				           serialize($this->active_filters[self::FILTER_ASSIGNED_USER]) .
				           '&' . self::FILTER_ASSIGNED_USER_INCLUDE_UNASSIGNED . '=' .
				           $this->active_filters[self::FILTER_ASSIGNED_USER_INCLUDE_UNASSIGNED] ?
				           '1' : '0';
			}
			
			if ($this->active_filters[self::FILTER_RESULT_RESULT]) {
				$string .= '&' . self::FILTER_RESULT_RESULT . '=' .
				           serialize($this->active_filters[self::FILTER_RESULT_RESULT]) .
				           '&' . self::FILTER_RESULT_METHOD . '=' .
				           $this->active_filters[self::FILTER_RESULT_METHOD] .
				           '&' . self::FILTER_RESULT_BUILD . '=' .
				           $this->active_filters[self::FILTER_RESULT_BUILD];
			}
		}
		
		return $string;
	}
	
	/**
	 * Build the tree menu for generation of JavaScript test case tree.
	 * Depending on mode and user's selections in user interface, 
	 * either a completely filtered tree will be build and returned,
	 * or only the minimal necessary data to "lazy load" 
	 * the objects in the tree by later Ajax calls.
	 * No return value - all variables will be stored in gui object
	 * which is passed by reference.
	 * 
	 * @author Andreas Simon
	 * @param object $gui Reference to GUI object (data will be written to it)
	 */
	public function build_tree_menu(&$gui) {
		
		$tree_menu = null;
		$filters = $this->get_active_filters();
		$additional_info = null;
		$options = null;
		$loader = '';
		$children = "[]";
		$cookie_prefix = '';
		
		// by default, disable drag and drop, then later enable if needed
		$drag_and_drop = new stdClass();
		$drag_and_drop->enabled = false;
		$drag_and_drop->BackEndUrl = '';
		$drag_and_drop->useBeforeMoveNode = FALSE;
				
		if (!$this->testproject_mgr) {
			$this->testproject_mgr = new testproject($this->db);
		}
		
		$tc_prefix = $this->testproject_mgr->getTestCasePrefix($this->args->testproject_id);
					
		switch ($this->mode) {
			
			case self::PLAN_MODE:
				// No lazy loading here.
					
				$additional_info = new stdClass();
				$additional_info->useCounters = CREATE_TC_STATUS_COUNTERS_OFF;
				$additional_info->useColours = COLOR_BY_TC_STATUS_OFF;
				$additional_info->testcases_colouring_by_selected_build = DISABLED;
				
				$filters->show_testsuite_contents = 1;
				$filters->hide_testcases = 0;
	
				if ($this->args->feature == 'test_urgency') {
					$filters->hide_testcases = 1;
				}
				
				list($tree_menu, $testcases_to_show) = generateExecTree($this->db,
		                                                       $gui->menuUrl,
		                                                       $this->args->testproject_id,
		                                                       $this->args->testproject_name,
		                                                       $this->args->testplan_id,
		                                                       $this->args->testplan_name,
		                                                       $filters,
		                                                       $additional_info);
				
				$this->set_testcases_to_show($testcases_to_show);
				
				$root_node = $tree_menu->rootnode;
				$children = $tree_menu->menustring ? $tree_menu->menustring : "[]";
				$cookie_prefix = $this->args->feature;
			break;
			
			case self::EDIT_MODE:
				
				if ($gui->tree_drag_and_drop_enabled[$this->args->feature]) {
					$drag_and_drop->enabled = true;
					$drag_and_drop->BackEndUrl = $this->args->basehref . 
					                             'lib/ajax/dragdroptprojectnodes.php';
					$drag_and_drop->useBeforeMoveNode = false;
				}
									
				if ($this->do_filtering) {
					$options = array('forPrinting' => NOT_FOR_PRINTING,
					                 'hideTestCases' => SHOW_TESTCASES,
						             'tc_action_enabled' => DO_ON_TESTCASE_CLICK,
						             'ignore_inactive_testcases' => DO_NOT_FILTER_INACTIVE_TESTCASES,
					                 'exclude_branches' => null);
				    
					$tree_menu = generateTestSpecTree($this->db, $this->args->testproject_id,
					                                  $this->args->testproject_name,
					                                  $gui->menuUrl, $filters, $options);
					
					$root_node = $tree_menu->rootnode;
					$children = $tree_menu->menustring ? $tree_menu->menustring : "[]";
					$cookie_prefix = $this->args->feature;
				} else {
					$loader = $this->args->basehref . 'lib/ajax/gettprojectnodes.php?' .
					          "root_node={$this->args->testproject_id}&" .
					          "tcprefix=" . urlencode($tc_prefix .
					          $this->configuration->tc_cfg->glue_character);
					
					$tcase_qty = $this->testproject_mgr->count_testcases($this->args->testproject_id);
					
					$root_node = new stdClass();
					$root_node->href = "javascript:EP({$this->args->testproject_id})";
					$root_node->id = $this->args->testproject_id;
					$root_node->name = $this->args->testproject_name . " ($tcase_qty)";
					$root_node->testlink_node_type='testproject';
													
					$cookie_prefix = 'tproject_' . $root_node->id . "_";					
				}
			break;
			
			case self::PLAN_ADD_MODE:
				
				$cookie_prefix = "planaddtc_{$this->args->testproject_id}_{$this->args->user_id}_";
				
				if ($this->do_filtering) {
					$options = array('forPrinting' => NOT_FOR_PRINTING,
					                 'hideTestCases' => HIDE_TESTCASES,
					                 'tc_action_enabled' => ACTION_TESTCASE_DISABLE,
					                 'ignore_inactive_testcases' => IGNORE_INACTIVE_TESTCASES,
					                 'viewType' => 'testSpecTreeForTestPlan');
			
					$tree_menu = generateTestSpecTree($this->db,
					                                  $this->args->testproject_id,
					                                  $this->args->testproject_name,
					                                  $gui->menuUrl,
					                                  $filters,
					                                  $options);
					
					$root_node = $tree_menu->rootnode;
				    $children = $tree_menu->menustring ? $tree_menu->menustring : "[]";
				} else {
					$loader = $this->args->basehref . 'lib/ajax/gettprojectnodes.php?' .
					                    "root_node={$this->args->testproject_id}&show_tcases=0";
				
					$root_node = new stdClass();
					$root_node->href = "javascript:EP({$this->args->testproject_id})";
					$root_node->id = $this->args->testproject_id;
					$root_node->name = $this->args->testproject_name;
					$root_node->testlink_node_type = 'testproject';
				}
			break;
			
			case self::EXECUTION_MODE:
			default:
				// No lazy loading here.
				// Filtering is always done in execution mode, no matter if user enters data or not,
				// since the user should usually never see the whole tree here.
				$additional_info = new stdClass();
				$filters->hide_testcases = false;
				$filters->tc_id = false; //filtering by testcase id could be implemented again...
				$filters->show_testsuite_contents = $this->configuration->exec_cfg->show_testsuite_contents;
				$additional_info->useCounters = $this->configuration->exec_cfg->enable_tree_testcase_counters;
				
				$additional_info->useColours = new stdClass();
				$additional_info->useColours->testcases = 
					$this->configuration->exec_cfg->enable_tree_testcases_colouring;
				$additional_info->useColours->counters = 
					$this->configuration->exec_cfg->enable_tree_counters_colouring;
				$additional_info->testcases_colouring_by_selected_build =
					$this->configuration->exec_cfg->testcases_colouring_by_selected_build; 
					
				list($tree_menu, $testcases_to_show) = generateExecTree($this->db,
				                                                        $gui->menuUrl,
				                                                        $this->args->testproject_id,
				                                                        $this->args->testproject_name,
				                                                        $this->args->testplan_id,
				                                                        $this->args->testplan_name,
				                                                        $filters,
				                                                        $additional_info);
					
				$this->set_testcases_to_show($testcases_to_show);
				
				$root_node = $tree_menu->rootnode;
				$children = $tree_menu->menustring ? $tree_menu->menustring : "[]";
				$cookie_prefix = 'exec_tplan_id_' . $this->args->testplan_id;
			break;
		}
		
		$gui->tree = $tree_menu;
		
		$gui->ajaxTree = new stdClass();
		$gui->ajaxTree->loader = $loader;
		$gui->ajaxTree->root_node = $root_node;
		$gui->ajaxTree->children = $children;
		$gui->ajaxTree->cookiePrefix = $cookie_prefix;
		$gui->ajaxTree->dragDrop = $drag_and_drop;
	} // end of method
	
	private function init_setting_refresh_tree_on_action() {

		$key = self::SETTING_REFRESH_TREE_ON_ACTION;
		$hidden_key = 'hidden_' . self::SETTING_REFRESH_TREE_ON_ACTION;
		$value = 0;

		$this->settings[$key] = array();
		$this->settings[$key][$hidden_key] = false;

		// look where we can find the setting - POST, SESSION, config?
		if (isset($this->args->{$key})) {
			$value = $this->args->{$key};
		} else if (isset($this->args->form_token)) {
			// value not sent by POST but form sent - checkbox got disabled!
			$value = 0;
		} else if (isset($this->args->{$hidden_key})) {
			$value = $this->args->{$hidden_key};
		} else if (isset($_SESSION[$key])) {
			$value = $_SESSION[$key];
		} else {
			$spec_cfg = config_get('spec_cfg');
			$value = ($spec_cfg->automatic_tree_refresh > 0) ? 1 : 0;
		}
		
		$this->settings[$key][self::STR_SELECTED] = $value;
		$this->settings[$key][$hidden_key] = $value;
		$_SESSION[$key] = $value;		
	} // end of method

	private function init_setting_build() {

		$key = self::SETTING_BUILD;
		if (is_null($this->testplan_mgr)) {
			$this->testplan_mgr = new testplan($this->db);
		}

		$tp_id = $this->settings[self::SETTING_TESTPLAN][self::STR_SELECTED];

		$this->settings[$key][self::STR_ITEMS] =
			$this->testplan_mgr->get_builds_for_html_options($tp_id,
			                                                 testplan::GET_ACTIVE_BUILD,
			                                                 testplan::GET_OPEN_BUILD);

		// if no build has been chosen by user, select newest build by default
		$newest_build_id = $this->testplan_mgr->get_max_build_id($tp_id,
		                                                         testplan::GET_ACTIVE_BUILD,
		                                                         testplan::GET_OPEN_BUILD);
		$this->args->{$key} = $this->args->{$key} > 0 ? $this->args->{$key} : $newest_build_id;
		$this->settings[$key][self::STR_SELECTED] = $this->args->{$key};

		// still no build selected? take first one from selection.
		if (!$this->settings[$key][self::STR_SELECTED]
		&& sizeof($this->settings[$key][self::STR_ITEMS])) {
			$this->settings[$key][self::STR_SELECTED] = key($this->settings[$key][self::STR_ITEMS]);
		}
	} // end of method

	private function init_setting_testplan() {

		if (is_null($this->testplan_mgr)) {
			$this->testplan_mgr = new testplan($this->db);
		}
		
		$key = self::SETTING_TESTPLAN;

		$testplans = $this->user->getAccessibleTestPlans($this->db, $this->args->testproject_id);

		if (isset($_SESSION['testplanID'])
		&& $_SESSION['testplanID'] != $this->args->{$key}) {
			// testplan was changed, we need to reset all filters
			// --> they were chosen for another testplan, not this one!
			$this->args->reset_filters = true;

			// check if user is allowed to set chosen testplan before changing
			foreach ($testplans as $plan) {
				if ($plan['id'] == $this->args->{$key}) {
					setSessionTestPlan($plan);
				}
			}
		}

		// now load info from session
		$info = $this->testplan_mgr->get_by_id($_SESSION['testplanID']);
		$this->args->testplan_name = $info['name'];
		$this->args->testplan_id = $info['id'];
		$this->args->{$key} = $info['id'];
		$this->settings[$key][self::STR_SELECTED] = $info['id'];

		// Now get all selectable testplans for the user to display.
		// But don't take testplans into selection which have no (active/open) builds!
		foreach ($testplans as $plan) {
			$builds = $this->testplan_mgr->get_builds($plan['id'],
			                                          testplan::GET_ACTIVE_BUILD,
			                                          testplan::GET_OPEN_BUILD);
			if (is_array($builds) && count($builds)) {
				$this->settings[$key][self::STR_ITEMS][$plan['id']] = $plan['name'];
			}
		}
	} // end of method

	private function init_setting_platform() {
		if (!$this->platform_mgr) {
			$this->platform_mgr = new tlPlatform($this->db);
		}
		$key = self::SETTING_PLATFORM;

		$this->settings[$key] = array(self::STR_ITEMS => null,
		                              self::STR_SELECTED => $this->args->{$key});

		$testplan_id = $this->settings[self::SETTING_TESTPLAN][self::STR_SELECTED];

		$this->settings[$key][self::STR_ITEMS] =
			$this->platform_mgr->getLinkedToTestplanAsMap($testplan_id);

		if (!isset($this->settings[$key][self::STR_ITEMS])
		|| !is_array($this->settings[$key][self::STR_ITEMS])) {
			$this->settings[$key] = false;
		} else if (isset($this->settings[$key][self::STR_ITEMS])
		       && is_array($this->settings[$key][self::STR_ITEMS])
		       && is_null($this->settings[$key][self::STR_SELECTED])) {
			// platforms exist, but none has been selected yet, so select first one
			$this->settings[$key][self::STR_SELECTED] =
				key($this->settings[$key][self::STR_ITEMS]);
		}
	} // end of method

	private function init_filter_testcase_name() {
		$key = self::FILTER_TESTCASE_NAME;
		$selection = $this->args->{$key};
		
		if (!$selection || $this->args->reset_filters) {
			$selection = null;
		} else {
			$this->do_filtering = true;
		}
		
		$this->filters[$key] = array(self::STR_SELECTED => $selection);
		$this->active_filters[$key] = $selection;
	} // end of method

	private function init_filter_toplevel_testsuite() {
		if (!$this->testproject_mgr) {
			$this->testproject_mgr = new testproject($this->db);
		}
		$key = self::FILTER_TOPLEVEL_TESTSUITE;
			
		$first_level_suites = $this->testproject_mgr->get_first_level_test_suites($this->args->testproject_id,
		                                                                          'smarty_html_options');
		
		$selection = $this->args->{$key};
		if (!$selection || $this->args->reset_filters) {
			$selection = null;
		} else {
			$this->do_filtering = true;
		}
		
		// this filter should only be visible if there are any top level testsuites
		if ($first_level_suites) {			
			$this->filters[$key] = array(self::STR_ITEMS => array(0 => ''),
			                             self::STR_SELECTED => $selection,
			                             'exclude_branches' => array());
		
			foreach ($first_level_suites as $suite_id => $suite_name) {
				$this->filters[$key][self::STR_ITEMS][$suite_id] = $suite_name;
								
				if ($selection && $suite_id != $selection) {
					$this->filters[$key]['exclude_branches'][$suite_id] = 'exclude_me';
				}
			}
			
			// Important: This is the only case in which active_filters contains the items
			// which have to be deleted from tree, instead of the other way around.
			$this->active_filters[$key] = $this->filters[$key]['exclude_branches'];
		} else {
			$this->active_filters[$key] = null;
		}		
	} // end of method

	private function init_filter_keywords() {
		$key = self::FILTER_KEYWORDS;
		$type = self::FILTER_KEYWORDS_FILTER_TYPE;
		$this->filters[$key] = false;
		$keywords = null;

		switch ($this->mode) {
			case self::EDIT_MODE:
				// in edit mode, we need the keywords for the whole testproject
				if (!$this->testproject_mgr) {
					$this->testproject_mgr = new testproject($this->db);
				}
				$keywords = $this->testproject_mgr->get_keywords_map($this->args->testproject_id);
				break;

			default:
				// otherwise (not in edit mode), we want only keywords assigned to testplan
				if (!$this->testplan_mgr) {
					$this->testplan_mgr = new testplan($this->db);
				}
				$tp_id = $this->settings[self::SETTING_TESTPLAN][self::STR_SELECTED];
				$keywords = $this->testplan_mgr->get_keywords_map($tp_id, ' ORDER BY keyword ');
				break;
		}

		$selection = $this->args->{$key};
		$type_selection = $this->args->{$type};
		
		// are there any keywords?
		if (!is_null($keywords) && count($keywords)) {
			$this->filters[$key] = array();

			if (!$selection || !$type_selection || $this->args->reset_filters) {
				// default values for filter reset
				$selection = null;
				$type_selection = self::STR_OR;
			} else {
				$this->do_filtering = true;
			}
			
			// data for the keywords themselves
			$this->filters[$key][self::STR_ITEMS] = array($this->option_strings['any']) + $keywords;
			$this->filters[$key][self::STR_SELECTED] = $selection;
			$this->filters[$key]['size'] = min(count($this->filters[$key][self::STR_ITEMS]),
			                                   self::ADVANCED_FILTER_ITEM_QUANTITY);

			// additional data for the filter type (logical and/or)
			$this->filters[$key][$type] = array();
			$this->filters[$key][$type][self::STR_ITEMS] = array(self::STR_OR => lang_get('logical_or'),
			                                                     self::STR_AND => lang_get('logical_and'));
			$this->filters[$key][$type][self::STR_SELECTED] = $type_selection;
		}
		
		// set the active value to filter
		// delete keyword filter if "any" (0) is part of the selection - regardless of filter mode
		if (is_array($this->filters[$key][self::STR_SELECTED])
		&& in_array(0, $this->filters[$key][self::STR_SELECTED])) {
			$this->active_filters[$key] = null;
		} else {
			$this->active_filters[$key] = $this->filters[$key][self::STR_SELECTED];
		}
		$this->active_filters[$type] = $selection ? $type_selection : null;
	} // end of method

	private function init_filter_priority() {
		// This is a special case of filter: the menu items don't get initialized here,
		// they are available as a global smarty variable. So the only thing to be managed
		// here is the selection by user.
		$key = self::FILTER_PRIORITY;

		// default value and filter reset
		$selection = $this->args->{$key};
		if (!$selection || $this->args->reset_filters) {
			$selection = null;
		} else {
			$this->do_filtering = true;
		}

		$this->filters[$key] = array(self::STR_SELECTED => $selection);
		$this->active_filters[$key] = $selection;
	} // end of method

	private function init_filter_execution_type() {
		if (!$this->tc_mgr) {
			$this->tc_mgr = new testcase($this->db);
		}
		$key = self::FILTER_EXECUTION_TYPE;

		$selection = $this->args->{$key};
		// handle filter reset
		if (!$selection || $this->args->reset_filters) {
			$selection = null;
		} else {
			$this->do_filtering = true;
		}
		
		$this->filters[$key] = array(self::STR_ITEMS => array(), self::STR_SELECTED => $selection);

		// load available execution types
		$this->filters[$key][self::STR_ITEMS] = $this->tc_mgr->get_execution_types();
		// add "any" string to these types at index 0 as default selection
		$this->filters[$key][self::STR_ITEMS] = array(0 => $this->option_strings['any'])
		                                      + $this->filters[$key][self::STR_ITEMS];
		
		$this->active_filters[$key] = $selection;
	} // end of method

	private function init_filter_assigned_user() {
		$key = self::FILTER_ASSIGNED_USER;
		$unassigned_key = self::FILTER_ASSIGNED_USER_INCLUDE_UNASSIGNED;
		$tp_id = $this->settings[self::SETTING_TESTPLAN][self::STR_SELECTED];

		// set selection to default (any), only change if value is sent by user and reset is not requested
		$selection = $this->args->{$key};
		if (!$selection || $this->args->reset_filters) {
			$selection = null;
		} else {
			$this->do_filtering = true;
		}
		
		$all_testers = getTestersForHtmlOptions($this->db, $tp_id, $this->args->testproject_id, null,
			                                    array(TL_USER_ANYBODY => $this->option_strings['any'],
			                                          TL_USER_NOBODY => $this->option_strings['none'],
			                                          TL_USER_SOMEBODY => $this->option_strings['somebody']),
			                                    'any');
		$visible_testers = $all_testers;
		
		// in execution mode the rights of the user have to be regarded
		if ($this->mode == self::EXECUTION_MODE) {
			$role = $this->user->getEffectiveRole($this->db, 
			                                $this->args->testproject_id,
			                                $tp_id);
			
			$simple_tester_roles = array_flip($this->configuration->exec_cfg->simple_tester_roles);
			
			// check the user's rights to see what he may do
			$right_to_execute = $role->hasRight('testplan_execute');
			$right_to_manage = $role->hasRight('testplan_planning');
			
			$simple = false;
			if (isset($simple_tester_roles[$role->dbID]) || ($right_to_execute && !$right_to_manage)) {
				// user is only simple tester and may not see/execute everything
				$simple = true;
			}
			
			$view_mode = $simple ? $this->configuration->exec_cfg->view_mode->tester : 'all';
			
			if ($view_mode != 'all') {
				$visible_testers = (array)$this->user->getDisplayName();
				$selection = (array)$this->user->dbID;
			}
		}
		
		$this->filters[$key] = array(self::STR_ITEMS => $visible_testers,
		                             self::STR_SELECTED => $selection,
		                             $unassigned_key => $this->args->{$unassigned_key});
		
		// which value shall be passed to tree generation class?
		
		if ((is_array($selection) && in_array(TL_USER_ANYBODY, $selection))
		|| ($selection == TL_USER_ANYBODY)) {
			// delete user assignment filter if "any user" is part of the selection
			$this->active_filters[$key] = null;
			$this->active_filters[$unassigned_key] = 0;
		}
		
		if (is_array($selection)) {
			// get keys of the array as values
			$this->active_filters[$key] = array_flip($selection);
			foreach ($this->active_filters[$key] as $user_key => $user_value) {
				$this->active_filters[$key][$user_key] = $user_key;
			}
			$this->active_filters[$unassigned_key] = $this->filters[$key][$unassigned_key];
		}
	} // end of method

	private function init_filter_custom_fields() {
		$key = self::FILTER_CUSTOM_FIELDS;
		if (!$this->exec_cf_mgr) {
			$this->exec_cf_mgr = new exec_cfield_mgr($this->db, $this->args->testproject_id);
		}

		$menu = $this->exec_cf_mgr->html_table_of_custom_field_inputs(self::CF_INPUT_SIZE);
		$selection = $this->exec_cf_mgr->get_set_values();
		if ($this->args->reset_filters) {
			// handle filter reset button
			$selection = null;
		} else {
			$this->do_filtering = true;
		}
		
		if (isset($selection) && is_array($selection) && count($selection)) {
			// BUGID 3414:
			// Insert values chosen by user into html select menu by regex.
			// The $menu string contains lines of which each looks like this:
			// <tr><td class="labelHolder">cflabel</td><td><input type="text" name="custom_field_0_1" 
			// id="custom_field_0_1" size="32"  maxlength="255" value=""></input></td></tr>
			// For each sent value, search the value="" part there and
			// then insert the real value into the empty "".
			$field_names = $this->exec_cf_mgr->field_names();
			// no magic number: 1 because of course only one replacement per value shall be done
			$limit = 1;
			foreach ($selection as $cf_id => $value) {
				$cf_html_name = $field_names[$cf_id]['cf_name'];
				$pattern = '/(.*name="' . $cf_html_name . '".*value=")(".*)/';
				// 1 and 2 stand for the first and second pair of braces in above statement
				$replacement = '${1}' . $value . '${2}';
				$menu = preg_replace($pattern, $replacement, $menu, $limit);
			}
		}
		
		$this->filters[$key] = array(self::STR_ITEMS => $menu, self::STR_SELECTED => $selection);
		$this->active_filters[$key] = $selection;
	} // end of method

	private function init_filter_result() {
		$key = self::FILTER_RESULT;
		$result_key = self::FILTER_RESULT_RESULT;
		$method_key = self::FILTER_RESULT_METHOD;
		$build_key = self::FILTER_RESULT_BUILD;
		
		if (is_null($this->testplan_mgr)) {
			$this->testplan_mgr = new testplan($this->db);
		}
		$tp_id = $this->settings[self::SETTING_TESTPLAN][self::STR_SELECTED];

		$this->configuration->results = config_get('results');

		// determine, which config to load and use for filter methods - depends on mode!
		$cfg = ($this->mode == self::EXECUTION_MODE) ? 
		       'execution_filter_methods' : 'execution_assignment_filter_methods';
		$this->configuration->filter_methods = config_get($cfg);

		// values selected by user
		$result_selection = $this->args->{$result_key};
		$method_selection = $this->args->{$method_key};
		$build_selection = $this->args->{$build_key};
		
		// default values
		$default_filter_method = $this->configuration->filter_methods['default_type'];
		$any_result_key = $this->configuration->results['status_code']['all'];
		$newest_build_id = $this->testplan_mgr->get_max_build_id($tp_id, testplan::GET_ACTIVE_BUILD);
		
		if (is_null($result_selection) || is_null($method_selection) || $this->args->reset_filters) {
			// no selection yet or filter reset requested
			$result_selection = $any_result_key;
			$method_selection = $default_filter_method;
			$build_selection = $newest_build_id;
		} else {
			$this->do_filtering = true;
		}
		
		// init array structure
		$this->filters[$key] = array($result_key => array(self::STR_ITEMS => null,
		                                                  self::STR_SELECTED => $result_selection),
		                             $method_key => array(self::STR_ITEMS => array(),
		                                                  self::STR_SELECTED => $method_selection),
		                             $build_key => array(self::STR_ITEMS => null,
		                                                 self::STR_SELECTED => $build_selection));

		// init menu for result selection by function from exec.inc.php
		$this->filters[$key][$result_key][self::STR_ITEMS] = createResultsMenu();
		$this->filters[$key][$result_key][self::STR_ITEMS][$any_result_key] = $this->option_strings['any'];

		// init menu for filter method selection
		foreach ($this->configuration->filter_methods['status_code'] as $statusname => $statusshortcut) {
			$code = $this->configuration->filter_methods['status_code'][$statusname];
			$this->filters[$key][$method_key][self::STR_ITEMS][$code] =
				lang_get($this->configuration->filter_methods['status_label'][$statusname]);
		}
		
		// init menu for build selection
		$this->filters[$key][$build_key][self::STR_ITEMS] =
			$this->testplan_mgr->get_builds_for_html_options($tp_id, testplan::GET_ACTIVE_BUILD);
		
		// if "any" is selected, nullify the active filters
		if ((is_array($result_selection) && in_array($any_result_key, $result_selection))
		|| $result_selection == $any_result_key) {
			$this->active_filters[$result_key] = null;
			$this->active_filters[$method_key] = null;
			$this->active_filters[$build_key] = null;
		} else {
			$this->active_filters[$result_key] = $result_selection;
			$this->active_filters[$method_key] = $method_selection;
			$this->active_filters[$build_key] = $build_selection;
		}
	} // end of method
} // end of class tlTestCaseFilterControl
?>