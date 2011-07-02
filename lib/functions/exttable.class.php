<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @package TestLink
 * @author Erik Eloff
 * @copyright 2009, TestLink community 
 * @version CVS: $Id: exttable.class.php,v 1.41 2010/11/07 09:44:59 mx-julian Exp $
 * @filesource http://testlink.cvs.sourceforge.net/viewvc/testlink/testlink/lib/functions/exttable.class.php?view=markup
 * @link http://www.teamst.org
 * @since 1.9
 *
 * @internal Revision:
 *  20101022 - Julian - BUGID 3979 - Use grid filters for exttables
 *  20100924 - asimon - made export ("download") button configurable
 *  20100921 - Julian - added stripeRows setting to getGridSettings(), default: enabled
 *	20100921 - eloff - refactor column index names
 *	20100830 - franciscom - buildColumns() refactored
 *							buildContent() minor refactored to avoid warnings on event viewer
 *	20100828 - eloff - Refactored rendering of status
 *	                   Add status behaviour as default
 *	20100826 - Julian - BUGID 3714 - new attribute $storeTableState
 *	20100824 - Julian - new attribute $toolbarRefreshButton
 *	20100823 - franciscom - getColumnIdxByName() - minor refactoring forcing exit with break
 *  20100823 - eloff - Add convinience methods setSortByColumnName and setGroupByColumnName
 *  				   Always store column config in full format(array-of-arrays)
 *  20100822 - asimon - new function getColumnIdxByName() to make sorting by column name possible
 *  20100819 - asimon - additional parameters (hidden, hideable, groupable) for req based report and other tables
 *  20100819 - Julian - MultiSort (BUGID 3694), default Values for Grid Settings, more Grid Settings
 * 	20100817 - Julian - default toolbar items, hideGroupedColumn
 *  20100816 - asimon - enable sorting by a default column via $sortByColumn
 *	20100719 - eloff - Pass $tableID via constructor
 *	20100719 - franciscom - changing default value for $groupByColumn
 *	20100716 - eloff - Allow grouping on any column
 *	20100715 - eloff - Add option for grouping on first column
 *	20100503 - franciscom - BUGID 3419 In "Test result matrix", tests statuses or not colorized
 *	20100423 - franciscom - refactoring to allow more flexibility
 *	20090909 - franciscom - changed to allow multiple tables
 *	new method renderCommonGlobals()
 * 
 **/

require_once('table.class.php');

/**
 * Helper class used for EXT-JS tables. There is an option to use custom type
 * in order to use custom rendering and sorting if needed.
 */
class tlExtTable extends tlTable
{
	/**
	 * Array of custom behaviour indexed by column type.
	 * Behaviour means custom rendering and/or sorting available.
	 * @see addCustomBehaviour($type,$behaviour)
	 */
	protected $customBehaviour = array();
	
	/**
	 * if true toolbar offers multisort feature.
	 */
	
	public $allowMultiSort = true;

	/**
	 * If set to an POSITIVE INTEGER VALUE, use this column for grouping. 
	 * Rows with same value will be placed in a collapsible group. 
	 * User can choose to group on other columns, this is just the default column.
	 */
	public $groupByColumn = -1;
	
	/**
	 * If true the column the table is grouped by will not be shown
	 */
	public $hideGroupedColumn = true;
	
	/**
	 * If true count of group items will be shown
	 */
	public $showGroupItemsCount = true;
	
	/**
	 * default grid parameters used by getGridSettings()
	 */
	
	public $title = null;
	
	public $width = null;
	
	public $height = 500;

	public $autoHeight = true;
	
	public $collapsible = false;
	
	public $frame = false;
	
	// x-grid3-row-alt of extjs css file is responsible for color
	public $stripeRows = true;
	
	/**
	 * 20100816 - asimon - enable sorting by a default column.
	 * If set (via setSortByColumnName), use this column for sorting. The value must be in the format from titleToColumnName.
	 * User can choose to sort by other columns, this is just the default column.
	 * If you activate this, you can also set $sortDirection if you don't want descending sorting.
	 * @see tlTable::titleToColumnName()
	 */
	public $sortByColumn = null;
	
	/**
	 * 20100816 - asimon - enable sorting by a default column and with configurable direction.
	 * If $sortByColumn is used, this will be used as the sort direction, default is descending (DESC).
	 */
	public $sortDirection = 'DESC';

    /**
     * If true shows a toolbar in the table header.
     */
    public $showToolbar = true;

    /**
     * If true shows "expand/collapse groups" toolbutton in toolbar.
     */
    public $toolbarExpandCollapseGroupsButton = true;
    
    /**
     * If true shows "show all columns" toolbutton in toolbar.
     */
    public $toolbarShowAllColumnsButton = true;

	/**
	 * If true show "reset to default settings" button
	 */
	public $toolbarDefaultStateButton = true;
	
	/**
	 * If true show "refresh" button
	 */
	public $toolbarRefreshButton = true;
	
	/**
	 * If true show "Reset Filters" button
	 */
	public $toolbarResetFiltersButton = true;

	/**
	 * If true, show export button in table toolbar.
	 * @var bool
	 */
	public $showExportButton = false;

	/**
	 * If true save table state to cookie
	 * see BUGID 3714 for information about problems
	 */
	public $storeTableState = true;

	/**
	 * Creates a helper object to render a table to a EXT-JS GridPanel.
	 * For use of column['type'] see $this->customTypes
	 * @param string $tableID tableID is used to create a store for
	 *                        table settings. tableID should be unique for
	 *                        each table occurence in each project.
	 *
	 * @see tlTable::__construct($columns, $data)
	 * @see addCustomBehaviour($type,$behaviour)
	 */
	public function __construct($columns, $data, $tableID)
	{
		parent::__construct($columns, $data, $tableID);
		$this->addCustomBehaviour('status', array(
			'render' => 'statusRenderer',
			'sort' => 'statusCompare',
			'filter' => 'Status'
		));

		$this->showExportButton = config_get('enableTableExportButton');
	}

	/**
	 * Adds behaivour for type that will be available to custom rendering and/or sorting
	 *
	 * By adding a behaivour for new type you must also make sure that the related JS-function exists.
	 * For example if you add the type "color", you also need to create a
	 * JS-funtion "colorRendererMethod(val)" that creates custom markup for rendering.
	 *
	 * To enable this type 'color' call:
	 * $table->addType('color', array('render' => 'colorRendererMethod'))
	 *
	 * @param string $type new type.
	 * @param map $behaviour the custom things to enable for this type
	 **/
	public function addCustomBehaviour($type, $behaviour)
	{
		$this->customBehaviour[$type] = $behaviour;
	}

	/**
	 * Build a JS structure that contains the tables content
	 * @return string [["first row, first column", "first row second column"],
	 *                 ["2nd row, first col", "2nd row, 2nd col"]];
	 */
	function buildContent()
	{
		if( !is_null($this->data) ) // to avoid warnings on foreach
		{
			foreach ($this->data as &$row) {
				// Use only column values from each row (makes every index numeric)
				// This makes sure a js array is created, if named keys are used
				// json_encode will create a js object instead.
				$row = array_values($row);
			}
		}
		return json_encode($this->data);
	}

	/**
	 * Build a JS object to describe the columns needed be EXT-JS GridPanel. This
	 * is supposed to be used as columnData.
	 *
	 * @return string [{header: "Test Suite", sortable: true, dataIndex: 'id_TestSuite'},
	 *                 {header: "Test Case", dataIndex: 'id_TestCase',width: 350},
	 *                 {header: "Version", dataIndex: 'id_Version'}];
	 */
	function buildColumns()
	{
		$s = '[';
		$n_columns = sizeof($this->columns);
		$options = array('width','hidden','groupable','hideable');

		for ($i=0; $i<$n_columns; $i++) {
			$column = $this->columns[$i];
			$s .= "{header: \"{$column['title']}\", dataIndex: '{$column['col_id']}'";
			
			// filter is set, but no filterOptions
			if (isset($column['filter']) && !isset($column['filterOptions'])) {
				$s .= ",filter: {type: '{$column['filter']}'}";
			} else if (isset($column['filter']) && isset($column['filterOptions'])) {
				// filter and filterOptions is set
				// for example list filter needs options
				$s .= ",filter: {type: '{$column['filter']}',options: ['";
				$s .= implode("','",$column['filterOptions']);
				$s .= "']}";
			} else if (!isset($this->customBehaviour[$column['type']]['filter'])) {
				// if no filter is specified use string filter
				// string filter is the most "basic" filter
				$s .= ",filter: {type: 'string'}"; 
			}

            foreach($options as $opt_str)
            {
				if (isset($column[$opt_str])) {
					$s .= ",$opt_str: {$column[$opt_str]}";
				}
			}

			if( isset($column['type']) && isset($this->customBehaviour[$column['type']]))
			{
				// BUGID 4125
				$customBehaviour = $this->customBehaviour[$column['type']];
				if (isset($customBehaviour['filter']) && $customBehaviour['filter'] == 'Status')
				{
					$s .= ",filter: " . $this->buildStatusFilterOptions();
				}
				if (isset($customBehaviour['filter']) && $customBehaviour['filter'] == 'Priority')
				{
					$s .= ",filter: " . $this->buildPriorityFilterOptions();
				}
				if (isset($customBehaviour['render']) )
				{
					// Attach a custom renderer
					$s .= ",renderer: {$customBehaviour['render']}";
				}
			}

			$sortable = 'true';
			if(isset($column['sortable'])){
				$sortable = $column['sortable'];
			}
			$s .= ",sortable: {$sortable}";

			$s .= "},\n";
		}
		$s = trim($s,",\n") . '];';
		return $s;
	}

	/**
	 * Build a JS object to describe the fields needed be EXT-JS ArrayStore. This
	 * is supposed to be used as columnData.
	 *
	 * @return string in the following format
	 *                 [{name: 'id_TestSuite'},
	 *                 {name: 'id_TestCase'},
	 *                 {name: 'id_Status', sortType: statusCompare}];
	 */
	function buildFields()
	{
		$s = '[';
		$n_columns = sizeof($this->columns);
		for ($i=0; $i < $n_columns; $i++) {
			$column = $this->columns[$i];
			$s .= "{name: '{$column['col_id']}'";
			if(	isset($column['type']) &&
				isset($this->customBehaviour[$column['type']]) &&
				isset($this->customBehaviour[$column['type']]['sort']) )
			{
				$s .= ", sortType: {$this->customBehaviour[$column['type']]['sort']}";
			} else if (isset($column['sortType'])) {
				$s .= ", sortType: '{$column['sortType']}'";
			}
			
			$s .= "},\n";
		}
		$s = trim($s,",\n");
		$s .= '];';
		return $s;

	}

	/**
	 * Build a JS assoc array to translate status and priorities codes to
	 * localized strings. This is done in JS because we need the short codes
	 * when sorting the table. The codes are translated to text only when
	 * rendering.
	 *
	 * What will happen if user add new status ?????
	 *
	 * @return string status_code_label = new Array();
	 *                status_code_label.f = 'Failed';
	 *                status_code_label.b = 'Blocked';
	 *                status_code_label.p = 'Passed';
	 *                status_code_label.n = 'Not Run';
	 *                prio_code_label = new Array();
	 *                prio_code_label[3] = 'High';
	 *                prio_code_label[2] = 'Medium';
	 *                prio_code_label[1] = 'Low';
	 */
	function buildCodeLabels()
	{
		$resultsCfg = config_get('results');
		$s = "status_code_label = new Array();\n";
        		
		foreach ($resultsCfg["status_label"] as $status => $label)
		{
			$code = $resultsCfg['status_code'][$status];
		    // echo 'code:' . $code . '<br>';
			$s .= "status_code_label.$code = '" . lang_get($label) . "';\n";
		}

		$urgencyCfg = config_get('urgency');
		$s .= "prio_code_label = new Array();\n";
		foreach ($urgencyCfg['code_label'] as $prio => $label) {
			$s .= "prio_code_label[$prio] = '" . lang_get($label) . "';\n";
		}
		return $s;
	}

	/**
	 * Outputs all js that is needed to render the table. This inlcludes
	 * rendering of "inc_ext_table.tpl"
	 */
	public function renderHeadSection()
	{
		$s = '<script type="text/javascript">' . "\n\n";
		$s .= "tableData['{$this->tableID}'] = " . $this->buildContent() . "\n\n";
		$s .= "fields['{$this->tableID}'] = " . $this->buildFields() . "\n\n";
		$s .= "columnData['{$this->tableID}'] = " . $this->buildColumns() . "\n\n";
		$s .= '</script>' . "\n\n";
		return $s;
	}

	/**
	 * Outputs all js that is common.
	 */
	public function renderCommonGlobals()
	{
		$s = '<script type="text/javascript">'. "\n\n";
		$s .= $this->buildCodeLabels() . "\n\n";
		$s .= $this->buildCfg() . "\n\n";
		$s .= "var store = new Array()\n\n";
		$s .= "var grid = new Array()\n\n";
		$s .= "var tableData = new Array()\n\n";
		$s .= "var fields = new Array()\n\n";
		$s .= "var columnData = new Array()\n\n";
		$s .= '</script>' . "\n\n";

		return $s;
	}


	/**
	 * Build a string with the extra settings passed to the GridPanel
	 * constructor. Important: The string returned starts with a comma.
	 *
	 * @return string on the form: ,title: "My table", autoHeight: true
	 */
	function getGridSettings()
	{
		$s = '';
		$settings = array('title', 'width', 'height', 'autoHeight', 'collapsible', 'frame', 'stripeRows');
		foreach ($settings as $setting) {
			$value = $this->{$setting};
			if (!is_null($value)){
				if (is_int($value)) {
					$s .= ", {$setting}: {$value}";
				} else if (is_bool($value)) {
					$s .= ", {$setting}: " . ($value ? 'true' : 'false');
				} else {
					$s .= ", {$setting}: \"{$value}\"";
				}
			}
		}
		return $s;
	}

	/**
	 * Outputs the div tag to hold the table.
	 */
	public function renderBodySection()
	{
		return '<div id="' . $this->tableID . '_target"></div>';
	}


	/**
	 * Build a JS 
	 *
	 * @return 
	 */
	function buildCfg()
	{
		$resultsCfg = config_get('results');
		$jsCode = "status_code_order = new Array();\n";
        
        $verboseStatusOrder=array_keys($resultsCfg["status_label_for_exec_ui"]);
        foreach( $verboseStatusOrder as $order => $status )
        {
        	$code = $resultsCfg['status_code'][$status];
			$jsCode .= "status_code_order.$code = " . $order . ";\n";
        }
        return $jsCode;
	}

	/**
	 * Get the index of a column by (localized) name of the column.
	 * 
	 * @author Andreas Simon
	 * @param string $name
	 * @return int $column_idx
	 */
	protected function getColumnIdxByName($name) {
		$column_idx = 0;
		foreach ($this->columns as $key => $column) {
			if ($name == $column['title']) {
				$column_idx = $key;
				break;
			}
		}
		return $column_idx;
	}

	/**
	 * Convinience function to group by column name.
	 * @param string $name column name to group by
	 */
	function setGroupByColumnName($name) {
		$idx = $this->getColumnIdxByName($name);
		$this->groupByColumn = $this->columns[$idx]['col_id'];
	}

	/**
	 * Convinience function to sort on column name.
	 * @param string $name column name to sort on
	 */
	function setSortByColumnName($name) {
		$idx = $this->getColumnIdxByName($name);
		$this->sortByColumn = $this->columns[$idx]['col_id'];
	}

	function buildStatusFilterOptions() {
		$resultsCfg = config_get('results');
		$statuses = array();
		foreach ($resultsCfg["status_label"] as $status => $label) {
			$code = $resultsCfg['status_code'][$status];
			$statuses[] = array($code, lang_get($label));
		}
		return "{type: 'Status', options: " . json_encode($statuses) . "}";
	}
	
	function buildPriorityFilterOptions() {
		$urgencyCfg = config_get('urgency');
		$priorities = array();
		foreach ($urgencyCfg['code_label'] as $prio => $label) {
			$priorities[] = array($prio, lang_get($label));
		}
		return "{type: 'Priority', options: " . json_encode($priorities) . "}";
	}
}
