<?php
// PHP Layers Menu 3.2.0-rc (C) 2001-2004 Marco Pratesi - http://www.marcopratesi.it/

/**
* This file contains the code of the ProcessLayersMenu class.
* @package PHPLayersMenu
*/

/**
* This is an extension of the "common" class of the PHP Layers Menu library.
*
* It provides methods useful to process/convert menus data, e.g. to output a menu structure and a DB SQL dump corresponding to already parsed data and hence also to convert a menu structure file to a DB SQL dump and viceversa
*
* @version 3.2.0-rc
* @package PHPLayersMenu
*/
class ProcessLayersMenu extends LayersMenuCommon
{

/**
* The constructor method
* @return void
*/
function ProcessLayersMenu()
{
	$this->LayersMenuCommon();
}

/**
* The method to set the dirroot directory
* @access public
* @return boolean
*/
function setDirroot($dirroot)
{
	return $this->setDirrootCommon($dirroot);
}

/**
* Method to output a menu structure corresponding to items of a menu
* @access public
* @param string $menu_name the name of the menu for which a menu structure
*   has to be returned
* @param string $separator the character used in the menu structure format
*   to separate fields of each item
* @return string
*/
function getMenuStructure(
	$menu_name = '',	// non consistent default...
	$separator = '|'
	)
{
	$menuStructure = '';
	for ($cnt=$this->_firstItem[$menu_name]; $cnt<=$this->_lastItem[$menu_name]; $cnt++) {	// this counter scans all nodes of the menu
		$menuStructure .= str_repeat('.', $this->tree[$cnt]['level']);
		$menuStructure .= $separator;
		$menuStructure .= $this->tree[$cnt]['text'];
		$menuStructure .= $separator;
		$menuStructure .= $this->tree[$cnt]['href'];
		$menuStructure .= $separator;
		$menuStructure .= $this->tree[$cnt]['title'];
		$menuStructure .= $separator;
		$menuStructure .= $this->tree[$cnt]['icon'];
		$menuStructure .= $separator;
		$menuStructure .= $this->tree[$cnt]['target'];
		$menuStructure .= $separator;
		$menuStructure .= $this->tree[$cnt]['expanded'];
		$menuStructure .= "\n";
	}
	return $menuStructure;
}

/**
* Method to output a DB SQL dump corresponding to items of a menu
* @access public
* @param string $menu_name the name of the menu for which a DB SQL dump
*   has to be returned
* @param string $db_type the type of DB to dump for;
*   leave it either empty or not specified if you are using PHP < 5,
*   as sqlite_escape_string() has been added in PHP 5;
*   it has to be specified and set to 'sqlite' only if the dump
*   has to be prepared for SQLite; it is not significant if != 'sqlite'
* @return string
*/
function getSQLDump(
	$menu_name = '',	// non consistent default...
	$db_type = ''
	)
{
	$SQLDump = '';
	for ($cnt=$this->_firstItem[$menu_name]; $cnt<=$this->_lastItem[$menu_name]; $cnt++) {	// this counter scans all nodes of the menu
		$current_node[$this->tree[$cnt]['level']] = $cnt;
		if (!$this->tree[$cnt]['child_of_root_node']) {
			$this->tree[$cnt]['father_node'] = $current_node[$this->tree[$cnt]['level']-1];
		}
		$VALUES = '';
		$SQLDump .= 'INSERT INTO ';
		$SQLDump .= $this->tableName;
		$SQLDump .= ' (';
		$SQLDump .= $this->tableFields['id'] . ', ';
		$VALUES .= "'" . 10*$cnt . "', ";
		$SQLDump .= $this->tableFields['parent_id'] . ', ';
		if (isset($this->tree[$cnt]['father_node']) && $this->tree[$cnt]['father_node'] != 0) {
			$VALUES .= "'" . 10*$this->tree[$cnt]['father_node'] . "', ";
		} else {
			$VALUES .= "'1', ";
		}
		$SQLDump .= $this->tableFields['text'] . ', ';
		$foobar = $this->tree[$cnt]['text'];
		if ($foobar != '') {
			if ($db_type != 'sqlite') {
				$foobar = addslashes($foobar);
			} else {
				$foobar = sqlite_escape_string($foobar);
			}
		}
		$VALUES .= "'$foobar', ";
		$SQLDump .= $this->tableFields['href'] . ', ';
		$VALUES .= "'" . $this->tree[$cnt]['href'] . "', ";
		if ($this->tableFields['title'] != "''") {
			$SQLDump .= $this->tableFields['title'] . ', ';
			$foobar = $this->tree[$cnt]['title'];
			if ($foobar != '') {
				if ($db_type != 'sqlite') {
					$foobar = addslashes($foobar);
				} else {
					$foobar = sqlite_escape_string($foobar);
				}
			}
			$VALUES .= "'$foobar', ";
		}
		if ($this->tableFields['icon'] != "''") {
			$SQLDump .= $this->tableFields['icon'] . ', ';
			$VALUES .= "'" . $this->tree[$cnt]['icon'] . "', ";
		}
		if ($this->tableFields['target'] != "''") {
			$SQLDump .= $this->tableFields['target'] . ', ';
			$VALUES .= "'" . $this->tree[$cnt]['target'] . "', ";
		}
		if ($this->tableFields['orderfield'] != "''") {
			$SQLDump .= $this->tableFields['orderfield'] . ', ';
			$VALUES .= "'" . 10*$cnt . "', ";
		}
		if ($this->tableFields['expanded'] != "''") {
			$SQLDump .= $this->tableFields['expanded'] . ', ';
			$this->tree[$cnt]['expanded'] = (int) $this->tree[$cnt]['expanded'];
			$VALUES .= "'" . $this->tree[$cnt]['expanded'] . "', ";
		}
		$SQLDump = substr($SQLDump, 0, -2);
		$VALUES = substr($VALUES, 0, -2);
		$SQLDump .= ") VALUES ($VALUES);\n";
	}
	return $SQLDump;
}

} /* END OF CLASS */

?>
