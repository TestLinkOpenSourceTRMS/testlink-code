<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @package TestLink
 * @author Erik Eloff
 * @copyright 2009,2021 TestLink community
 * @since 1.9
 *
 *
 **/

require_once('table.class.php');

/**
 * Helper class used to generate HTML-tables. 
 * Used to output tables meant for documents and spreadsheets 
 * where EXT-tables don't work.
 */
class tlHTMLTable extends tlTable
{
	public function __construct($columns, $data, $tableID='tlHTMLTable')
	{
		// Save those for faster access in renderStatus() and renderPriority()
		parent::__construct($columns, $data,  $tableID);
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
	public function renderHeadSection()
	{
		return '';
	}

	/**
	 * Renders a HTML table with css class "simple" and given id
	 */
	public function renderBodySection()
	{
		$s = '<table class="simple" style="width: 100%; margin-left: 0px;">';
		// Render columns
		$s .= '<tr>';
		foreach ($this->columns as $column) {
			$title = is_array($column) ? $column['title'] : $column;
			$s .= "<th>{$title}</th>";
		}
		$s .= '</tr>';
		foreach ($this->data as $rowData) 
		{
			$s .= '<tr>';
			foreach ($rowData as $colIndex => $value) 
			{
				if( isset($this->columns[$colIndex]['type']) )
				{
					if ($this->columns[$colIndex]['type'] == 'priority') {
						$value = $this->renderPriority($value);
					}
					if ($this->columns[$colIndex]['type'] == 'status') {
						$value = $this->renderStatus($value);
					}
				}
				$s .= "<td>{$value}</td>";
			}
			$s .= '</tr>';
		}
		$s .= '</table>';
		return $s;
	}

	// BUGID 3418
	public function renderStatus($item)
	{
		return "<span class=\"{$item['cssClass']}\">{$item['text']}</span>";
	}

	public function renderPriority($prio)
	{
		$label = lang_get($this->prio_code_label[$prio]);
		return $label;
	}
}
