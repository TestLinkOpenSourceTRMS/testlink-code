<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @package     TestLink
 * @author      Francisco Mancardi
 * @copyright   2006-2009, TestLink community
 * @version     CVS: $Id: tlControlPanel.class.php,v 1.17 2010/06/02 09:11:55 franciscom Exp $
 * @link        http://www.teamst.org/index.php
 *
 * Give common logic to be used at GUI level to manage common set of settings and filters
 * used when filters/subviews of test case tree is needed.
 *
 * 
 * @internal Revision:
 *
 */

// ***********************************************************************************************************
// TODO asimon refactoring
// IMPORTANT: following line is only a temporary, quick and dirty fix to prevent test spec tree from crashing.
// This class will be completely removed/refactored by me, please do not change anything in here meanwhile.
require_once('exec.inc.php');
// ***********************************************************************************************************

/**
 * 
 * @author franciscom
 **/
class tlControlPanel extends tlObjectWithDB
{
	public $strOption;
	public $filters;
	public $settings;

	public $treeColored;
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
		$this->chooseFilterModeEnabled = false;
		
		$key = 'treeColored';
		$p2check = 'treeColored';
		$this->$key = property_exists($userChoice,$p2check) ? $userChoice->$p2check : null;
		
		
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
		
		$key = 'refreshTreeOnActionChecked';
		$this->settings['refreshTreeOnActionChecked'] = '';
		if( property_exists($userChoice,$key) && $userChoice->$key == 'yes' )
		{
			$this->settings['refreshTreeOnActionChecked'] = ' checked ';
		}
			
		
		
		$this->filters = array();
		$itemsKeys = array('testPlans','builds','platforms','testSuites','execStatus','execTypes','execStatusOnBuildMethods');
		foreach($itemsKeys as $key)
		{
			$this->filters[$key]['items'] = array();
			$this->filters[$key]['selected'] = 0;
			$this->displayFilter[$key] = 0;
		}
		

		$key = 'testSuites';
		$p2check = 'panelFiltersTestSuite';
		$this->filters[$key]['selected'] = property_exists($userChoice,$p2check) ? $userChoice->$p2check : 0;
		$this->filters[$key]['items'] = null;

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
		$this->filters['keywordsFilterTypes'] = new stdClass();
    	$this->filters['keywordsFilterTypes']->options = array('OR' => 'Or' , 'AND' =>'And'); 
    	
    	$key = 'panelFiltersKeywordsFilterType';
    	$this->filters['keywordsFilterTypes']->selected = property_exists($userChoice,$key) ? $userChoice->$key : 0;
    	$this->filters['keywordsFilterTypes']->size = 0;
    	$this->filters['keywordsFilterTypes']->displayStyle = '';



		$key = 'keywords';
        $this->filters[$key]['items'] = array();
		$this->filters[$key]['size'] = 0;
        if( isset($initValues[$key]) )
        {
        	if( !is_array($initValues[$key]) )
        	{
        		$dummy = explode(',',$initValues[$key]);
        		switch($dummy[0])
        		{
        			case 'testproject':
        				$objMgr = new $dummy[0]($dbHandler);
        				$initValues[$key] = $objMgr->get_keywords_map($dummy[1]);
        			break;

        			case 'testplan':
        				$objMgr = new $dummy[0]($dbHandler);
        				$initValues[$key] = $objMgr->get_keywords_map($dummy[1],' order by keyword ');
        			break;
        				
        			unset($objMgr);
        		}
        	}
        }
        $this->filters['keywords']['items'] = isset($initValues['keywords']) ? $initValues['keywords'] : array();
    	$pkey = 'panelFiltersKeyword';
        $this->filters['keywords']['selected'] = property_exists($userChoice,$pkey) ? $userChoice->$pkey : 0;
    	if(!is_null($this->filters['keywords']['items']) && count($this->filters['keywords']['items']) > 0)
    	{
    	    $this->filters['keywords']['items'] = array(0 => $this->strOption['any']) + $this->filters['keywords']['items'];
    		$this->filters['keywords']['size'] = min(count($this->filters['keywords']['items']),3);
    	}


		$key = 'execTypes';
        $this->filters[$key]['items'] = array();
        if( isset($initValues[$key]) )
        {
        	if( !is_array($initValues[$key]) )
        	{
        		$tcaseMgr = new testcase($dbHandler);
        		$initValues[$key] = $tcaseMgr->get_execution_types(); 		
        		unset($tcaseMgr);
        	}
        }
        $this->filters[$key]['items'] = isset($initValues[$key]) ? $initValues[$key] : null;
    	$prop = 'panelFiltersExecType';
        $this->filters[$key]['selected'] = property_exists($userChoice,$prop) ? $userChoice->$prop : 0;
    	if(!is_null($this->filters[$key]['items']))
    	{
    	    $this->filters[$key]['items'] = array(0 => $this->strOption['any']) + $this->filters[$key]['items'];
    	}


        $cfg_results = config_get('results');
   	 	$this->filters['execStatus']['items'] = createResultsMenu();
    	$this->filters['execStatus']['items'][$cfg_results['status_code']['all']] = $this->strOption['any'];

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