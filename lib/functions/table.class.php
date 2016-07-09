<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource  table.class.php
 * @package TestLink
 * @author Erik Eloff
 * @copyright 2009,2012 TestLink community 
 *
 * @link http://www.teamst.org
 * @since 1.9
 *
 * @internal revisions
 **/


/**
 * Abstract base class for rendering tables
 */
abstract class tlTable
{
  /**
   * @var array that holds the columns of the table
   *            Every column is an array with at least a title and col_id attribute,
   *            other attributes are optional.
   *            $column = array(
   *              'title' => 'My column',
   *              'col_id' => 'id_Mycolumn',
   *              'width' => 150,
   *              'type' => 'status'
   *            );
   *            It is up to the derived class to use this information on
   *            rendering. The col_id key is used to identify the data
   *            field when using ext js.
   *            @see tlTable::titleToColumnName()
   */
  protected $columns;

  /**
   * @var array that holds the row data to be displayed. Every row is
   *      an array with the column data as describled in $columns.
   *      If the data type is status the value should be an array like
   *      array('value' => 'f', 'text' => 'Failed', 'cssClass' => 'failed_text')
   *      to allow coloring and sorting in table.
   */
  protected $data;

  /**
   * A unique id that is used to render the table and to remember state via
   * cookie (requires CookieProvider to be set in Ext.onReady);
   */
  public $tableID = null;

  /** @var The title header for the whole table. Default: null (no title) */
  public $title = null;

  /** @var Width of the table. Default: null (full width) */
  public $width = null;

  /** @var Height of the table. Default: null
   * @see $autoHeight
   */
  public $height = null;

  /** @var autoHeight determines if the table should have a fixed height, or
   *       if the height depends on the content.
   *       Default: true (height = height of content)
   */
  public $autoHeight = true;

  /*
   * Used by titleToColumnName() to create unique column identifiers.
   */
  protected $usedNames = array();

  /**
   * @param $columns is either an array of column titles
   *        (i.e. array('Title 1', 'Title 2')) or an array where each value
   *        is an array('title' => 'Column title 1',
   *                    'type' => 'string',
   *                    'width' => 150);
   *
   *        Let the constructor do localization:
   *        array('title_key' => 'title_test_case_title', 'width' => 150)
   *
   *        Explicitly set column id:
   *        array('title' => '[%]', 'col_id' => 'passed_percent')
   *
   *        It is possible to set title_key instead of title: this will mean
   *        the localization is done within the constructor and that title_key
   *        can be used as column id. If title key is not given then create a
   *        column id based on the localized title.
   *
   *        It is possible to override the generated column id by passing a
   *        value as col_id.
   *
   *        Internally the columns will always be saved in the full format
   *        (array-of-arrays).
   *
   *        @see tlTable::$columns
   *        @see tlTable::$data
   */
  public function __construct($columns, $data, $tableID)
  {
    // Expand the simple column format (array-of-titles) to full
    // array-of-arrays and compute js friendly column names.
    $this->columns = array();
    foreach ($columns as $column) {
      if (is_array($column)) {
        if (isset($column['title_key'])) {
          if (isset($column['title'])) {
            throw new Exception("Both title and title_key are set: use only one of them");
          }
          $column['title'] = lang_get($column['title_key']);
        }

        // If $title_key was given, use that for col_id, otherwise use $title
        if (!isset($column['col_id'])) {
          $key = $column['title'];
          if (isset($column['title_key'])) {
            $key = $column['title_key'];
          }
          $column['col_id'] = $this->titleToColumnName($key);
        }
        $this->columns[] = $column;
      }
      else if (is_string($column)) {
        $this->columns[] = array(
          'title' => $column,
          'col_id' => $this->titleToColumnName($column)
        );
      }
      else {
        throw new Exception("Invalid column header: " . $column);
      }
    }
    $this->data = $data;
    $this->tableID = $tableID;
  }

  /**
   * Outputs the code that all tables shares
   */
  public abstract function renderCommonGlobals();

  /**
   * Outputs the code that should be in <head>
   */
  public abstract function renderHeadSection();

  /**
   * Outputs the code that should be in <body>
   */
  public abstract function renderBodySection();


  /**
   * Transforms a column title (localized string) to a unique valid
   * js identifier by removing all invalid chars.
   *
   * Note: The result is unique so passing the same $title twice will
   * return different column ids. Only meant to be called from constructor.
   */
  private function titleToColumnName($title) {
    static $allowedChars = "_abcdefghijklmnopqrstuvwxyz0123456789";
    // always start with this to avoid number in beginning
    $js_safe = 'id_';
    $chars = str_split($title);
    foreach ($chars as $char) {
      if (stripos($allowedChars, $char) !== FALSE) {
        $js_safe .= $char;
      }
    }
    // If the name is already used append a number
    if (in_array($js_safe, $this->usedNames)) {
      $i = 1;
      // Find next available number
      while (in_array($js_safe . $i, $this->usedNames)) {
        $i++;
      }
      $js_safe .= $i;
    }
    $this->usedNames[] = $js_safe;
    return $js_safe;
  }
}
