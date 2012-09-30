<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @package TestLink
 * @author Erik Eloff
 * @copyright 2009,2012 TestLink community 
 * @filesource exttable.class.php
 * @link http://www.teamst.org
 * @since 1.9
 *
 * @internal revisions
 * @since 2.0
 *
 **/

//require_once('table.class.php');

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
	 * Enable sorting by a default column.
	 * If set (via setSortByColumnName), use this column for sorting. 
	 * The value must be in the format from titleToColumnName.
	 * User can choose to sort by other columns, this is just the default column.
	 * If you activate this, you can also set $sortDirection if you don't want descending sorting.
	 * @see tlTable::titleToColumnName()
	 */
	public $sortByColumn = null;
	
	/**
	 * enable sorting by a default column and with configurable direction.
	 * If $sortByColumn is used, this will be used as the sort direction, default is descending (DESC).
	 */
	public $sortDirection = 'DESC';

  public $toolbar = null;
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
		$this->addCustomBehaviour('status', 
		                          array('render' => 'statusRenderer','sort' => 'statusCompare',
		                                'filter' => 'Status'));


	  $this->multiSortEnabled = true;

    $this->toolbar = new stdClass();
	  $this->toolbar->show = true;

    $this->toolbar->showButton = new stdClass();
    $this->toolbar->showButton->showAllColumns = true;
    $this->toolbar->showButton->expandCollapseGroups = true;
    $this->toolbar->showButton->defaultState = true;
    $this->toolbar->showButton->refresh = true;
    $this->toolbar->showButton->resetFilters = true;
    $this->toolbar->showButton->export = config_get('enableTableExportButton');
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
			foreach ($this->data as &$row) 
			{
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

		for ($idx = 0; $idx < $n_columns; $idx++) 
		{
			$column = $this->columns[$idx];
			$s .= "{header: \"{$column['title']}\", dataIndex: '{$column['col_id']}'";

      foreach($options as $opt_str)
      {
				if (isset($column[$opt_str])) 
				{
					$s .= ",$opt_str: {$column[$opt_str]}";
				}
			}
			
			if ( isset($column['filter']) ) 
			{
				$s .= ",filter: {type: '{$column['filter']}'";
			  if( isset($column['filterOptions']) )
			  {
  				$s .= ",options: ['" . implode("','",$column['filterOptions']) . "']";
			  }
				$s .= "}";
			} 
      else if (isset($column['type']) && isset($this->customBehaviour[$column['type']]['filter'])) 
      {
				$customBehaviour = $this->customBehaviour[$column['type']];
				if (isset($customBehaviour['filter']) && isset($customBehaviour['filterMethod']) )
				{
					$s .= ",filter: " . $this->$customBehaviour['filterMethod']();
				}
				
				if (isset($customBehaviour['render']) )
				{
					// Attach a custom renderer
					$s .= ",renderer: {$customBehaviour['render']}";
				}
			}
			else 
			{
				// if no filter is specified use string filter, that is the most "basic" filter
				$s .= ",filter: {type: 'string'}";
			}

			$dummy = isset($column['sortable']) ? $column['sortable'] : 'true';
			$s .= ",sortable: {$dummy}";
			$s .= "},\n";
		}
		$s = trim($s,",\n") . '];';
		return $s;
	}

	/**
	 * Build a JS object to describe the fields needed be EXT-JS ArrayStore. 
	 * This is supposed to be used as columnData.
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
		for ($idx=0; $idx < $n_columns; $idx++) 
		{
			$column = $this->columns[$idx];
			$s .= "{name: '{$column['col_id']}'";
			if(	isset($column['type']) &&
				isset($this->customBehaviour[$column['type']]) &&
				isset($this->customBehaviour[$column['type']]['sort']) )
			{
				$s .= ", sortType: {$this->customBehaviour[$column['type']]['sort']}";
			} 
			else if (isset($column['sortType'])) 
			{
				$s .= ", sortType: '{$column['sortType']}'";
			}
			
			$s .= "},\n";
		}
		$s = trim($s,",\n");
		$s .= '];';
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
		foreach ($settings as $setting) 
		{
			$value = $this->{$setting};
			if (!is_null($value))
			{
				if (is_int($value)) 
				{
					$s .= ", {$setting}: {$value}";
				} 
				else if (is_bool($value)) 
				{
					$s .= ", {$setting}: " . ($value ? 'true' : 'false');
				} 
				else 
				{
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
	 * Get the index of a column by (localized) name of the column.
	 * 
	 * @author Andreas Simon
	 * @param string $name
	 * @return int $column_idx
	 */
	protected function getColumnIdxByName($name) 
	{
		$idx = 0;
		foreach ($this->columns as $key => $column) 
		{
			if ($name == $column['title']) 
			{
				$idx = $key;
				break;
			}
		}
		return $idx;
	}

	/**
	 * Convinience function to group by column name.
	 * @param string $name column name to group by
	 */
	function setGroupByColumnName($name) 
	{
		$idx = $this->getColumnIdxByName($name);
		$this->groupByColumn = $this->columns[$idx]['col_id'];
	}

	/**
	 * Convinience function to sort on column name.
	 * @param string $name column name to sort on
	 */
	function setSortByColumnName($name) 
	{
		$idx = $this->getColumnIdxByName($name);
		$this->sortByColumn = $this->columns[$idx]['col_id'];
	}


  
  /**
   * Following methods are generic, is simple to develop if the live here.
   * 
   */

  /**
   * 
   * 
   */
	function buildStatusFilterOptions() 
	{
		$cfg = config_get('results');
		$options = array();
		foreach ($cfg["status_label"] as $status => $label) 
		{
			$code = $cfg['status_code'][$status];
			$options[] = array($code, lang_get($label));
		}
		return "{type: 'Status', options: " . json_encode($options) . "}";
	}
	
  /**
   * 
   * 
   */
	function buildPriorityFilterOptions() 
	{
		$cfg = config_get('urgency');
		$options = array();
		foreach ($cfg['verbose_code'] as $verbose => $code) 
		{
			$options[] = array("$code", lang_get($cfg['verbose_label'][$verbose]));
		}
		return "{type: 'Priority', options: " . json_encode($options) . "}";
	}
}
?>