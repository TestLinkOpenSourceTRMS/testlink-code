<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @package TestLink
 * @author Erik Eloff
 * @copyright 2009, TestLink community
 * @version CVS: $Id: tlHTMLTable.class.php,v 1.1 2009/12/23 13:42:41 erikeloff Exp $
 * @filesource http://testlink.cvs.sourceforge.net/viewvc/testlink/testlink/lib/functions/tlHTMLTable.class.php?view=markup
 * @link http://www.teamst.org
 * @since 1.9
 *
 * @internal Revision:
 *  20091223 - eloff - created class
 *
 **/

require_once('table.class.php');

/**
 * Helper class used to generate HTML-tables. Used to output tables meant for
 * documents and spreadsheets where EXT-tables don't work.
 */
class tlHTMLTable extends tlTable
{
	public function __construct($columns, $data)
	{
		// Save those for faster access in renderStatus() and renderPriority()
		parent::__construct($columns, $data);
		$resultsCfg = config_get('results');
		$this->code_status = $resultsCfg['code_status'];
		$this->status_color = $resultsCfg['charts']['status_colour'];
		$urgencyCfg = config_get('urgency');
		$this->prio_code_label = $urgencyCfg['code_label'];
	}

	/**
	 * Does nothing. All rendering is contained in renderBodySection()
	 */
	public function renderCommonGlobals()
	{
		return '';
	}

	/**
	 * Does nothing. All rendering is contained in renderBodySection()
	 */
	public function renderHeadSection($tableID)
	{
		return '';
	}

	/**
	 * Renders a HTML table with css class "simple" and given id
	 */
	public function renderBodySection($tableID)
	{
		$s = '<table class="simple" style="width: 100%; margin-left: 0px;">';
		// Render columns
		$s .= '<tr>';
		foreach ($this->columns as $column) {
			$title = is_array($column) ? $column['title'] : $column;
			$s .= "<th>{$title}</th>";
		}
		$s .= '</tr>';
		foreach ($this->data as $rowData) {
			$s .= '<tr>';
			foreach ($rowData as $colIndex => $value) {
				if ($this->columns[$colIndex]['type'] == 'priority') {
					$value = $this->renderPriority($value);
				}
				if ($this->columns[$colIndex]['type'] == 'status') {
					$value = $this->renderStatus($value);
				}
				$s .= "<td>{$value}</td>";
			}
			$s .= '</tr>';
		}
		$s .= '</table>';
		return $s;
	}

	public function renderStatus($value)
	{
		$status = $this->code_status[$value[0]];
		$color = $this->status_color[$status];
		return "<span style=\"color: #{$color}\">{$value[1]}</span>";
	}

	public function renderPriority($prio)
	{
		$label = lang_get($this->prio_code_label[$prio]);
		return $label;
	}
}
