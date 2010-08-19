<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @package TestLink
 * @author Erik Eloff
 * @copyright 2009, TestLink community 
 * @version CVS: $Id: exttable.class.php,v 1.14 2010/08/19 11:47:27 mx-julian Exp $
 * @filesource http://testlink.cvs.sourceforge.net/viewvc/testlink/testlink/lib/functions/exttable.class.php?view=markup
 * @link http://www.teamst.org
 * @since 1.9
 *
 * @internal Revision:
 *  20100819 - Julian - MultiSort, default Values for Grid Settings, more Grid Settings
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
	 * Array of multiSort Buttons.
	 * @see addMultiSortButton($field,$direction)
	 */
	
	public $multiSortButtons = array();

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
	 * defaults grid parameters used by getGridSettings()
	 */
	
	public $title = null;
	
	public $width = null;
	
	public $height = 500;

	public $autoHeight = true;
	
	public $collapsible = false;
	
	public $frame = true;

	/**
	 * 20100816 - asimon - enable sorting by a default column.
	 * If set to a positive integer value, use this column for sorting. 
	 * User can choose to sort by other columns, this is just the default column.
	 * If you activate this, you can also set $sortDirection if you don't want descending sorting.
	 */
	public $sortByColumn = -1;
	
	/**
	 * 20100816 - asimon - enable sorting by a default column and with configurable direction.
	 * If $sortByColumn is used, this will be used as the sort direction, default is descending (DESC).
	 */
	public $sortDirection = 'DESC';
	
    /**
     * If true shows a toolbar in the table header.
     */
    public $show_toolbar = true;
    
    /**
     * If true shows "expand/collapse groups" toolbutton in toolbar.
     */
    public $toolbar_expand_collapse_groups_button = true;
    
    /**
     * If true shows "show all columns" toolbutton in toolbar.
     */
    public $toolbar_show_all_columns_button = true;

	/**
	 * Creates a helper object to render a table to a EXT-JS GridPanel.
	 * For use of column['type'] see $this->customTypes
	 *
	 * @see tlTable::__construct($columns, $data)
	 * @see addCustomBehaviour($type,$behaviour)
	 */
	public function __construct($columns, $data, $tableID)
	{
		parent::__construct($columns, $data, $tableID);
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
	
	public function addMultiSortButton($field, $direction)
	{
		$this->multiSortButtons[] = array('field' => $field , 'direction' => $direction);
	}

	/**
	 * Build a JS structure that contains the tables content
	 * @return string [["first row, first column", "first row second column"],
	 *                 ["2nd row, first col", "2nd row, 2nd col"]];
	 */
	function buildContent()
	{
		$s = '[';
		foreach ($this->data as $row => $rowData) {
			$row_string = '[';
			foreach ($rowData as $key => $val) 
			{
				// Escape data
				if (is_string($val)) 
				{
					$row_string .= json_encode($val) . ',';
				} 
				else if (is_array($val)) 
				{
					// BUGID 3419
					if( is_string($val[0]) )
					{
						$row_string .= "'" . $val[0] . "',";
					}
					else
					{
						// 20100503 - franciscom
						// Do not understand why need to use " as part of value
						$row_string .= "\"{$val[0]}\",";
					}
				} 
				else 
				{
					$row_string .= "{$val},";
				}
			}
			$row_string = trim($row_string,",");
			$row_string .= '],'."\n";
			$s .= $row_string;
		}
		$s = trim($s,",\n");
		$s .= '];';
		return $s;
	}

	/**
	 * Build a JS object to describe the columns needed be EXT-JS GridPanel. This
	 * is supposed to be used as columnData.
	 *
	 * @return string [{header: "Test Suite", sortable: true, dataIndex: 'idx0'},
	 *                 {header: "Test Case", dataIndex: 'idx1',width: 350},
	 *                 {header: "Version", dataIndex: 'idx2'}];
	 */
	function buildColumns()
	{
		$s = '[';
		$n_columns = sizeof($this->columns);
		for ($i=0;$i<$n_columns; $i++) {
			$column = $this->columns[$i];
			$title = is_array($column) ? $column['title'] : $column;
			$s .= "{header: \"{$title}\", sortable: true, dataIndex: 'idx$i'";
			if (is_array($column)) {
				if (isset($column['width'])) {
					$s .= ",width: {$column['width']}";
				}
				if( isset($column['type']) && isset($this->customBehaviour[$column['type']]) &&
					isset($this->customBehaviour[$column['type']]['render']) ) 
				{
					// Attach a custom renderer
					$s .= ",renderer: {$this->customBehaviour[$column['type']]['render']}";
				}
			}
			$s .= "},\n";
		}
		$s = trim($s,",\n");
		$s .= '];';
		return $s;
	}

	/**
	 * Build a JS object to describe the fields needed be EXT-JS ArrayStore. This
	 * is supposed to be used as columnData.
	 *
	 * @return string [{name: 'idx0'},
	 *                 {name: 'idx1'},
	 *                 {name: 'idx2'}];
	 */
	function buildFields()

	{
		$s = '[';
		$n_columns = sizeof($this->columns);
		for ($i=0; $i < $n_columns; $i++) {
			$column = $this->columns[$i];
			$s .= "{name: 'idx$i'";
			if (is_array($column)) 
			{
				if(	isset($column['type']) &&
					isset($this->customBehaviour[$column['type']]) &&
					isset($this->customBehaviour[$column['type']]['sort']) )
				{	
					$s .= ", sortType: {$this->customBehaviour[$column['type']]['sort']}";
				}
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
		$settings = array('title', 'width', 'height', 'autoHeight', 'collapsible', 'frame');
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

}
