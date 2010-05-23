<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @package     TestLink
 * @author      Francisco Mancardi
 * @copyright   2006-2009, TestLink community
 * @version     CVS: $Id: tlControlPanel.class.php,v 1.1 2010/05/23 15:16:15 franciscom Exp $
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
	public $itemSet;
	public $filters;
	public $settings;

	public $advancedFilterMode;
	public $display;

	/**
	 * @param $dbHandler database object
	 * @param $tproject_id to work on. If null (default) the project in session
	 *                     is used
     * DO NOT USE this kind of code is not accepted have this kind of global coupling
     * for lazy users
	 */
	public function __construct(&$dbHandler,$userChoice)
	{
		parent::__construct($dbHandler);

		$cfgObj = new stdClass();
    	$gui_open = config_get('gui_separator_open');
    	$gui_close = config_get('gui_separator_close');

		$settings = array('tcSpecRefreshOnAction' => true);
    	
		$this->strOption = array();
		$this->strOption['any'] = $gui_open . lang_get('any') . $gui_close;
    	$this->strOption['none'] = $gui_open . lang_get('nobody') . $gui_close;
    	$this->strOption['somebody'] = $gui_open . lang_get('filter_somebody') . $gui_close;

	
		$this->filters = array();
		$this->setting = array();
		$this->display = array();
		$itemsKeys = array('testPlans','builds','platforms','execStatus','execTypes','execStatusOnBuildMethods');
		foreach($itemsKeys as $key)
		{
			$this->itemSet[$key]['items'] = array();
			$this->itemSet[$key]['selected'] = 0;
			$this->display[$key] = 0;
		}
		$this->itemSet['execStatus']['size'] = 0;


		$key = 'advancedFilterMode'
		$p2check = 'filterPanel' . $key;
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