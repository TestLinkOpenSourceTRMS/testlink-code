<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @package     TestLink
 * @author      Erik Eloff
 * @copyright   2009,2012 TestLink community 
 * @filesource  exttable.class.php
 * @link        http://www.teamst.org
 * @since       1.9
 *
 * @internal revisions
 * @since 1.9.7
 * 20130320 - franciscom - TICKET 5577: Requirements based Report is empty (buildColumns())
 * 
 **/

require_once('table.class.php');

/**
 * Helper class used for EXT-JS tables. 
 * There is an option to use custom type in order to use custom rendering and sorting if needed.
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
  
  public $imgSet;
  public $moreViewConfig = '';


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
    $this->addCustomBehaviour('status', array('render' => 'statusRenderer',
                                              'sort' => 'statusCompare',
                                              'filter' => 'Status'
                                             ));

    $this->showExportButton = config_get('enableTableExportButton');
  }

  /**
   * Adds behaviour for type that will be available to custom rendering and/or sorting
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
    static $l18n;
    
    if(is_null($l18n))
    {
      $l18n = init_labels(array('warning_disable_user' => null,'disable' => null));  
    }
    
    $s = '[';
    $n_columns = sizeof($this->columns);
    $options = array('width','hidden','groupable','hideable');

    for ($i=0; $i<$n_columns; $i++) 
    {
      $column = $this->columns[$i];

      // new dBug($column);
      // Because sometimes a column can be made HIDDEN but used to generate a group
      // (this happens in the requirements based report), if we remove ths column
      // only checking for 'hidden' attribute, we will generate an issue
      //
      $isGroupable = isset($column['groupable']) ? $column['groupable'] : false;
	    if( (isset($column['hidden']) && $column['hidden']) && !$isGroupable )
      {
      	continue;  // just an experiment
      }
      

      if( isset($column['tlType']) )
      {
        switch($column['tlType'])
        { 
          case 'disableUser':
            $s .= $this->getDisableUserJS();
          break;
        }
        continue;  // Bye!!
      }
      
      if( isset($column['title']) )
      {
        $s .= "{header: \"{$column['title']}\", dataIndex: '{$column['col_id']}'";
      }
  
      if (isset($column['filter']))
      {
        if( isset($column['filterOptions']) )
        {
          // filter and filterOptions is set
          // for example list filter needs options
          $s .= ",filter: {type: '{$column['filter']}',options: ['";
          $s .= implode("','",$column['filterOptions']);
          $s .= "']}";
        }
        else
        {
          $s .= ",filter: {type: '{$column['filter']}'}";
        }
      } 
      else if (isset($column['type']) && isset($this->customBehaviour[$column['type']]['filter'])) 
      {
        // do not define a filter in this case. Special filters are applied later
      } 
      else 
      {
        // if no filter is specified use string filter
        // string filter is the most "basic" filter
        $s .= ",filter: {type: 'string'}";
      }

      foreach($options as $opt_str)
      {
        if (isset($column[$opt_str])) 
        {
          $s .= ",$opt_str: {$column[$opt_str]}";
        }
      }

      if( isset($column['type']) && isset($this->customBehaviour[$column['type']]))
      {
        $customBehaviour = $this->customBehaviour[$column['type']];
        $filterSet = array('Status','Priority','Importance');
        foreach($filterSet as $target)
        {
          if (isset($customBehaviour['filter']) && $customBehaviour['filter'] == $target)
          {
            $method = 'build' . $target . 'FilterOptions';
            $s .= ",filter: " . $this->$method();
          }
        } 
        if (isset($customBehaviour['render']) )
        {
          // Attach a custom renderer
          $s .= ",renderer: {$customBehaviour['render']}";
        }
      }
      $s .= ",sortable: " . (isset($column['sortable']) ? $column['sortable'] : 'true'); 
      $s .= "},\n";      
    } // loop on columns
    $s = trim($s,",\n") . '];';
    return $s;
  }

  /**
   * Build a JS object to describe the fields needed by EXT-JS ArrayStore. 
   * This is supposed to be used as columnData.
   *
   * @return string in the following format
   *                 [{name: 'id_TestSuite'},
   *                 {name: 'id_TestCase'},
   *                 {name: 'id_Status', sortType: statusCompare}];
   *
   *         ATTENTION: if col_id is provided when configuring columns,
   *                    then it will be used instead of automatically generated.
   */
  function buildFields()
  {
    $s = '[';
    $n_columns = sizeof($this->columns);
    for ($i=0; $i < $n_columns; $i++) {
      $column = $this->columns[$i];
      $s .= "{name: '{$column['col_id']}'";
      if(  isset($column['type']) &&
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
    $cfg = config_get('results');
    $s = "status_code_label = new Array();\n";
    foreach ($cfg["status_label"] as $status => $label)
    {
      $code = $cfg['status_code'][$status];
      $s .= "status_code_label.$code = '" . lang_get($label) . "';\n";
    }
    
    // 20121223 - franciscom - 
    // do not understand why this is working because priorities are computed
    // urgency => test plan attribute * importance => test spec attribute
    // See $tlCfg->urgencyImportance
    $cfg = config_get('urgency');
    $s .= "prio_code_label = new Array();\n";
    foreach ($cfg['code_label'] as $code => $label) 
    {
      $s .= "prio_code_label[$code] = '" . lang_get($label) . "';\n";
    } 
    
    $cfg = config_get('importance');
    $s .= "importance_code_label = new Array();\n";
    foreach ($cfg['code_label'] as $code => $label) 
    {
      $s .= "importance_code_label[$code] = '" . lang_get($label) . "';\n";
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
    // $s .= "alert(importance_code_label);";
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

  function buildStatusFilterOptions() 
  {
    $resultsCfg = config_get('results');
    $statuses = array();
    foreach ($resultsCfg["status_label"] as $status => $label) {
      $code = $resultsCfg['status_code'][$status];
      $statuses[] = array($code, lang_get($label));
    }
    return "{type: 'Status', options: " . json_encode($statuses) . "}";
  }
  
  
  // CRITIC
  // a companion method has to exists on ext_extensions.js or rendering will fail.
  // Ext.ux.grid.filter.PriorityFilter()
  function buildPriorityFilterOptions() 
  {
    $cfg = config_get('urgency');
    $items = array();
    foreach ($cfg['code_label'] as $code => $label) 
    {
      $items[] = array("$code", lang_get($label));
    }
    return "{type: 'Priority', options: " . json_encode($items) . "}";
  }

  // CRITIC
  // a companion method has to exists on ext_extensions.js or rendering will fail.
  // Ext.ux.grid.filter.ImportanceFilter()
  function buildImportanceFilterOptions() 
  {
    $cfg = config_get('importance');
    $items = array();
    foreach ($cfg['code_label'] as $code => $label) 
    {
      $items[] = array("$code", lang_get($label));
    }
    return "{type: 'Importance', options: " . json_encode($items) . "}";
  }

  function setImages($v)
  {
    $this->imgSet = $v;  
  }


  function getGridViewConfig()
  {
    $s = 'forceFit: true' . $this->moreViewConfig;
    $s .= ',hideGroupedColumn:' . ($this->hideGroupedColumn ? 'true' : 'false');
    if( $this->showGroupItemsCount )
    {
      $s .= ",groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? \"Items\" : \"Item\"]})'";
    }
    return $s;
  }

  function getDisableUserJS()
  {
    static $l18n;
    if(is_null($l18n))
    {
      $l18n = init_labels(array('warning_disable_user' => null,'disable' => null));  
    }

    $js = "{xtype: 'actioncolumn',width: 50, hideable: false,sortable: false,groupable: false," .
          " items: [{tooltip: 'hhh'," .
          " handler: function(grid, rowIndex, colIndex) \n" .
          "          { \n" .
          "           var rec = store['" . $this->tableID ."'].getAt(rowIndex); \n" .
          "           if( rec.get('is_special') == 0 ) \n" .
          "           { \n" .
          "             delete_confirmation(rec.get('user_id'),rec.get('login')," .
                                            "'" . $l18n['disable'] . "','" . 
                                            $l18n['warning_disable_user'] . "'); \n" .
          "           } \n" .
          "           } /* end handler function() */" .
          ", getClass: function(v, meta, rec){" . 
          " if(rec.get('is_special') == 1){" . 
          " /* 0 points to the the FIRST (and only) item */ " . 
          " this.items[0].tooltip = 'Demo mode => you can not disable me!'; return 'special_user';} " . 
          " else { this.items[0].tooltip = 'Disable User'; return 'normal_user';} " . 
          " } /* end getClass() */" .         
          "}]";
    $js .= "},\n";
    
    return $js;
   }

  
}
?>