<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @package     TestLink
 * @author      Francisco Mancardi
 * @copyright   2006-2009, TestLink community
 * @version     CVS: $Id: tlControlPanel.class.php,v 1.4 2010/05/23 17:41:38 franciscom Exp $
 * @link        http://www.teamst.org/index.php
 *
 * Give common logic to be used at GUI level to manage common set of settings and filters
 * used when filters/subviews of test case tree is needed.
 *
 * 
 * @internal Revision:
 *
 */


/**
 * 
 * @author franciscom
 **/
class tlControlPanel extends tlObjectWithDB
{
	public $strOption;
	public $filters;
	public $settings;

	public $advancedFilterMode;
	public $displaySetting;
	public $displayFilter;
	public $controls;

	/**
	 * @param $dbHandler database object
	 * @param $tproject_id to work on. If null (default) the project in session
	 *                     is used
     * DO NOT USE this kind of code is not accepted have this kind of global coupling
     * for lazy users
	 */
	public function __construct(&$dbHandler,$userChoice,$initValues)
	{
		parent::__construct($dbHandler);

		$cfgObj = new stdClass();
    	$gui_open = config_get('gui_separator_open');
    	$gui_close = config_get('gui_separator_close');

		$this->drawTCUnassignButton = false;
		$this->drawBulkUpdateButton = false;
		
		$this->strOption = array();
		$this->strOption['any'] = $gui_open . lang_get('any') . $gui_close;
    	$this->strOption['none'] = $gui_open . lang_get('nobody') . $gui_close;
    	$this->strOption['somebody'] = $gui_open . lang_get('filter_somebody') . $gui_close;

	
		$this->setting = array();
		$this->display = array();
		$itemsKeys = array('testPlans','builds','platforms');
		foreach($itemsKeys as $key)
		{
			$this->settings[$key]['items'] = array();
			$this->settings[$key]['selected'] = 0;
			$this->displaySetting[$key] = 0;
		}
		
		$this->settings['refreshTreeOnActionChecked'] = '';
		if( $userChoice->refreshTreeOnAction == 'yes' )
		{
			$this->settings['refreshTreeOnActionChecked'] = ' checked ';
		}
			
		
		
		$this->filters = array();
		$this->itemSet['execStatus']['size'] = 0;

		$itemsKeys = array('testPlans','builds','platforms','testSuites','execStatus','execTypes','execStatusOnBuildMethods');
		foreach($itemsKeys as $key)
		{
			$this->filters[$key]['items'] = array();
			$this->filters[$key]['selected'] = 0;
			$this->displayFilter[$key] = 0;
		}
		
		$this->filters['testSuites']['selected'] = $userChoice->panelFiltersTestSuite;


		// Miscelaneous
		$key = 'advancedFilterMode';
		$p2check = 'panelFilters' . $key;
		$this->$key = property_exists($userChoice,$p2check) ? $userChoice->$p2check : 0;


    	if($this->advancedFilterMode)
    	{
    	    $label = 'btn_simple_filters';
    	    $qty = 4; // Standard: not run,passed,failed,blocked
    	}
    	else
    	{
    	    $label = 'btn_advanced_filters';
    	    $qty = 1;
    	}


		// Filters
		$this->filters->keywordsFilterTypes = new stdClass();
    	$this->filters->keywordsFilterTypes->options = array('OR' => 'Or' , 'AND' =>'And'); 
    	$this->filters->keywordsFilterTypes->selected = $userChoice->panelFiltersKeywordsFilterType;
    	$this->filters->keywordsFilterTypes->size = 0;
    	$this->filters->keywordsFilterTypes->displayStyle = '';


        $this->filters->keywords = array();
        $this->filters->keywords['items'] = isset($initValues['keywords']) ? $initValues['keywords'] : array();
        $this->filters->keywords['selected'] = $userChoice->xxx;  // NEED WORK
    	if(!is_null($this->filters->keywords['items']))
    	{
    	    $this->filters->keywords['items'] = array(0 => $gui->strOptionAny) + $this->filters->keywords['items'];
    		$this->filters->keywords['size'] = min(count($this->filters->keywords['items']),3);
    	}

		return $this;
	}

	/**
	 * initExecStatusOnBuildMethods
	 *
	 */
	function initExecStatusOnBuildMethods() 
	{
		$cfg = config_get('execution_filter_methods');
		$items = array();
		foreach($cfg['status_code'] as $status => $label) 
		{
			$code = $cfg['status_code'][$status];
			$items[$code] = lang_get($filter_cfg['status_label'][$status]);
		}
		return $items;
	}


} // class end