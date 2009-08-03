<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @package TestLink
 * @author Erik Eloff
 * @copyright 2009, TestLink community 
 * @version CVS: $Id $
 * @filesource http://testlink.cvs.sourceforge.net/viewvc/testlink/testlink/lib/functions/table.class.php?view=markup
 * @link http://www.teamst.org
 * @since 1.9
 * 
 **/


/**
 * Abstract base class for rendering tables
 */
abstract class tlTable
{
	/** @var array that holds the columns of the table */
	protected $columns;
	/**
	 * @var array that holds the row data to be displayed. Every row is
	 *      an array with the column data as describled in $columns.
	 */
	protected $data;

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
	 */
	public function __construct($columns, $data)
	{
		$this->columns = $columns;
		$this->data = $data;
	}

	/**
	 * Outputs the code that should be in <head>
	 */
	public abstract function renderHeadSection();

	/**
	 * Outputs the code that should be in <body>
	 */
	public abstract function renderBodySection();
}
