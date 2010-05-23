<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @package     TestLink
 * @author      Francisco Mancardi
 * @copyright   2006-2009, TestLink community
 * @version     CVS: $Id: tlTestCaseFilterPanel.class.php,v 1.1 2010/05/23 09:53:12 franciscom Exp $
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
class tlTestCaseFilterPanel extends tlObjectWithDB
{
	public $strOption;
	public $itemSet;

	/**
	 * @param $dbHandler database object
	 * @param $tproject_id to work on. If null (default) the project in session
	 *                     is used
     * DO NOT USE this kind of code is not accepted have this kind of global coupling
     * for lazy users
	 */
	public function __construct(&$dbHandler)
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

	
		$this->itemSet = array();
		$itemsKeys = array('testPlans','builds','platforms','execStatus','execStatusOnBuildMethods');
		foreach($itemsKeys as $key)
		{
			$this->itemSet[$key]['items'] = array();
			$this->itemSet[$key]['selected'] = 0;
		}
		$this->itemSet['execStatus']['size'] = 0;


		return $this;
	}


} // class end