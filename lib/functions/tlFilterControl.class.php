<?php

/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @package    TestLink
 * @author     Andreas Simon
 * @copyright  2006-2010, TestLink community
 * @version    CVS: $Id: tlFilterControl.class.php,v 1.2 2010/06/24 17:25:53 asimon83 Exp $
 * @link       http://www.teamst.org/index.php
 * @filesource http://testlink.cvs.sourceforge.net/viewvc/testlink/testlink/lib/functions/tlFilterControl.class.php?view=markup
 *
 * This class holds common logic to be used at GUI level to manage a common set of settings and filters.
 * It is used when filters or subviews of the test case or requirement tree are needed.
 * It is extended by the subclasses tlRequirementFilterPanel and tlTestCaseFilterPanel,
 * which hold specific changes for each of these object types.
 * Main class is abstract because it shall not be used/instantiated directly.
 *
 * @internal Revisions:
 *
 * 20100610 - asimon - first implementation of filter panel class hierarchy
 *                     to simplify/generalize filter panel handling for test cases and requirements
 */

/**
 * This class holds common logic to be used at GUI level to manage a common set of settings and filters.
 * It is used when filters or subviews of the test case or requirement tree are needed.
 * It is extended by the subclasses tlRequirementFilterPanel and tlTestCaseFilterPanel,
 * which hold specific changes and methods for each of these object types.
 * This main class is abstract because it shall not be used/instantiated directly.
 *
 * @author Andreas Simon
 * @package TestLink
 */
abstract class tlFilterControl extends tlObjectWithDB {

	/**
	 * The string used for identifying the element in an array which holds the selectable items.
	 * @var string
	 */
	const STR_ITEMS = 'items';

	/**
	 * The string which identifies the part of an array in which the (by user) selected element is saved.
	 * @var string
	 */
	const STR_SELECTED = 'selected';

	/**
	 * String constant for logical "or".
	 * @var string
	 */
	const STR_OR = 'Or';

	/**
	 * String constant for logical "and".
	 * @var string
	 */
	const STR_AND = 'And';
	
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
	 * class constant for the setting tree refresh on action
	 * @var string
	 */
	const SETTING_REFRESH_TREE_ON_ACTION = 'setting_refresh_tree_on_action';

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
	 * The token that will be used to identify the relationship between left frame
	 * (with navigator) and right frame (which displays execution of test case e.g.) in session.
	 * @var string
	 */
	public $form_token = null;
	
	/**
	 * will hold the localized string options (any/none/somebody/...)
	 * @var array
	 */
	public $option_strings = array();

	/**
	 * holds the configuration that will be read from config file
	 * @var array
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
	 * @var unknown_type
	 */
	private $active_filters = array();
	
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
	
	/**
	 * Testproject manager object.
	 * Initialized not in constructor, only on first use to save resources.
	 * @var testproject
	 */
	public $testproject_mgr = null;
	
	/**
	 *
	 * @param database $dbHandler reference to database object
	 */
	public function __construct(&$dbHandler) {

		// call to constructor of parent class tlObjectWithDB
		parent::__construct($dbHandler);

		// Here comes all initializing work: First read the config, then user input.
		// According to these inputs all filters which are not needed will not be used.
		// Then initialize and use only the remaining filters.
		$this->read_config();
		$this->init_args();

		// set filter mode to advanced or simple
		$this->advanced_filter_mode = false;
		if ($this->filter_mode_choice_enabled && $this->args->advanced_filter_mode) {
			$this->advanced_filter_mode = true;
		}
		
		// init button labels
    	if ($this->advanced_filter_mode) {
    	    $label = self::SIMPLE_FILTER_BUTTON_LABEL;
    	    $qty = self::ADVANCED_FILTER_ITEM_QUANTITY;
    	} else {
    	    $label = self::ADVANCED_FILTER_BUTTON_LABEL;
    	    $qty = self::SIMPLE_FILTER_ITEM_QUANTITY;
    	}
		$this->filter_mode_button_label = lang_get($label);
		$this->filter_mode_button_name = $label;
		$this->filter_item_quantity = $qty;

		$this->init_settings();
		$this->init_filters();
	} // end of method

	public function __destruct() {
		// delete member objects
		unset($this->testproject_mgr);
	}
	
	/**
	 * Reads the configuration from the configuration file, which is not dependent on type of objects in tree.
	 * This function has to be implemented and extended also in extending classes to read specialized config
	 * for either test cases or requirements.
	 * Function has protected (in subclasses private) visibility because it will only be called by __construct().
	 */
	protected function read_config() {
		// opening and closing brackets
		$gui_open = config_get('gui_separator_open');
    	$gui_close = config_get('gui_separator_close');

		// configure string options for select inputs
		$this->option_strings['any'] = $gui_open . lang_get('any') . $gui_close;
    	$this->option_strings['none'] = $gui_open . lang_get('nobody') . $gui_close;
    	$this->option_strings['somebody'] = $gui_open . lang_get('filter_somebody') . $gui_close;

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
	protected function init_args() {

		$this->args = new stdClass();

		$this->args->basehref = $_SESSION['basehref'];
		
		// get user's data
		$this->user = $_SESSION['currentUser'];
		$this->args->user_id = $this->user->dbID;
		$this->args->user_name = $this->user->getDisplayName();
		
		$this->args->testproject_id = isset($_SESSION['testprojectID']) ?
		                              $_SESSION['testprojectID'] : 0;
		$this->args->testproject_name = isset($_SESSION['testprojectName']) ?
		                                $_SESSION['testprojectName'] : 0;
		
		$params = array();

		$params[self::SETTING_REFRESH_TREE_ON_ACTION] =
			array("POST", self::SETTING_REFRESH_TREE_ON_ACTION, tlInputParameter::CB_BOOL);
		$params['hidden_' . self::SETTING_REFRESH_TREE_ON_ACTION] =
			array("POST", 'hidden_' . self::SETTING_REFRESH_TREE_ON_ACTION,
			      tlInputParameter::INT_N);

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
	} // end of method

	/**
	 * Generates a form token, which will be used to identify the relationship
	 * between left navigator-frame with its settings and right frame.
	 * This solves the problems that can otherwise arise if
	 * a user uses the same feature simultaneously in multiple browser tabs.
	 * See issue 3516 in Mantis for a little bit more information.
	 * Abstract, since the check for already existing tokens can be
	 * different for test cases and requirements.
	 */
	protected abstract function generate_form_token();
	
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
	 * Build the tree menu for generation of JavaScript tree of either
	 * test cases or requirements.
	 * Depending on user selections in user interface, 
	 * either a completely filtered tree will be build and returned,
	 * or only the minimal necessary data to "lazy load" the objects in tree by later Ajax calls.
	 * @param object $gui Reference to GUI object (information will be written to it)
	 * @return object $tree_menu Tree object for display of JavaScript tree menu.
	 */
	public abstract function build_tree_menu(&$gui);
} // end of class
?>