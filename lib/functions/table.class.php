<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @package TestLink
 * @author Erik Eloff
 * @copyright 2009, TestLink community 
 * @version CVS: $Id: table.class.php,v 1.9 2010/08/30 21:11:29 erikeloff Exp $
 *
 * @filesource http://testlink.cvs.sourceforge.net/viewvc/testlink/testlink/lib/functions/table.class.php?view=markup
 * @link http://www.teamst.org
 * @since 1.9
 *
 * @internal Revision:
 *  20100828 - eloff - Changed format on status column
 *  20100823 - eloff - Always store column config in full format(array-of-arrays)
 *  20100719 - eloff - Pass $tableID via constructor
 **/


/**
 * Abstract base class for rendering tables
 */
abstract class tlTable
{
	/**
	 * @var array that holds the columns of the table
	 *            Every column is an array with at least a title attribute,
	 *            other attributes are optional.
	 *            $column = array(
	 *              'title' => 'My column',
	 *              'width' => 150,
	 *              'type' => 'status'
	 *            );
	 *            It is up to the derived class to use this information on
	 *            rendering.
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

	/**
	 * @param $columns is either an array of column titles
	 *        (i.e. array('Title 1', 'Title 2')) or an array where each value
	 *        is an array('title' => 'Column title 1',
	 *                    'type' => 'string',
	 *                    'width => 150);
	 *        Internally the columns will always be saved in the second format.
	 *        @see tlTable::$columns
	 *        @see tlTable::$data
	 */
	public function __construct($columns, $data, $tableID)
	{
		// Expand the simple column format (array-of-titles) to full array-of-arrays
		$this->columns = array();
		foreach ($columns as $column) {
			if (is_array($column)) {
				$this->columns[] = $column;
			}
			else if (is_string($column)) {
				$this->columns[] = array('title' => $column);
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
}
