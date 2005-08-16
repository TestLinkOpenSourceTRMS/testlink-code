<?php
// PHP Layers Menu 3.2.0-rc (C) 2001-2004 Marco Pratesi - http://www.marcopratesi.it/

/**
* This file contains the code of the PlainMenu class.
* @package PHPLayersMenu
*/

/**
* This is the PlainMenu class of the PHP Layers Menu library.
*
* This class depends on the LayersMenuCommon class and on the PEAR conforming version of the PHPLib Template class, i.e. on HTML_Template_PHPLIB.  It provides plain menus, that to do not require JavaScript to work.
*
* @version 3.2.0-rc
* @package PHPLayersMenu
*/
class PlainMenu extends LayersMenuCommon
{

/**
* The template to be used for the Plain Menu
*/
var $plainMenuTpl;
/**
* An array where we store the Plain Menu code for each menu
* @access private
* @var array
*/
var $_plainMenu;

/**
* The template to be used for the Horizontal Plain Menu
*/
var $horizontalPlainMenuTpl;
/**
* An array where we store the Horizontal Plain Menu code for each menu
* @access private
* @var array
*/
var $_horizontalPlainMenu;

/**
* The constructor method; it initializates some variables
* @return void
*/
function PlainMenu()
{
	$this->LayersMenuCommon();

	$this->plainMenuTpl = $this->tpldir . 'layersmenu-plain_menu.ihtml';
	$this->_plainMenu = array();

	$this->horizontalPlainMenuTpl = $this->tpldir . 'layersmenu-horizontal_plain_menu.ihtml';
	$this->_horizontalPlainMenu = array();
}

/**
* The method to set the dirroot directory
* @access public
* @return boolean
*/
function setDirroot($dirroot)
{
	$oldtpldir = $this->tpldir;
	if ($foobar = $this->setDirrootCommon($dirroot)) {
		$this->updateTpldir($oldtpldir);
	}
	return $foobar;
}

/**
* The method to set the tpldir directory
* @access public
* @return boolean
*/
function setTpldir($tpldir)
{
	$oldtpldir = $this->tpldir;
	if ($foobar = $this->setTpldirCommon($tpldir)) {
		$this->updateTpldir($oldtpldir);
	}
	return $foobar;
}

/**
* The method to update the templates directory path to the new tpldir
* @access private
* @return void
*/
function updateTpldir($oldtpldir)
{
	$oldlength = strlen($oldtpldir);
	$foobar = strpos($this->plainMenuTpl, $oldtpldir);
	if (!($foobar === false || $foobar != 0)) {
		$this->plainMenuTpl = $this->tpldir . substr($this->plainMenuTpl, $oldlength);
	}
	$foobar = strpos($this->horizontalPlainMenuTpl, $oldtpldir);
	if (!($foobar === false || $foobar != 0)) {
		$this->horizontalPlainMenuTpl = $this->tpldir . substr($this->horizontalPlainMenuTpl, $oldlength);
	}
}

/**
* The method to set plainMenuTpl
* @access public
* @return boolean
*/
function setPlainMenuTpl($plainMenuTpl)
{
	if (str_replace('/', '', $plainMenuTpl) == $plainMenuTpl) {
		$plainMenuTpl = $this->tpldir . $plainMenuTpl;
	}
	if (!file_exists($plainMenuTpl)) {
		$this->error("setPlainMenuTpl: file $plainMenuTpl does not exist.");
		return false;
	}
	$this->plainMenuTpl = $plainMenuTpl;
	return true;
}

/**
* Method to prepare a new Plain Menu.
*
* This method processes items of a menu to prepare and return
* the corresponding Plain Menu code.
*
* @access public
* @param string $menu_name the name of the menu whose items have to be processed
* @return string
*/
function newPlainMenu(
	$menu_name = ''	// non consistent default...
	)
{
	$plain_menu_blck = '';
	$t = new Template_PHPLIB();
	$t->setFile('tplfile', $this->plainMenuTpl);
	$t->setBlock('tplfile', 'template', 'template_blck');
	$t->setBlock('template', 'plain_menu_cell', 'plain_menu_cell_blck');
	$t->setVar('plain_menu_cell_blck', '');
	for ($cnt=$this->_firstItem[$menu_name]; $cnt<=$this->_lastItem[$menu_name]; $cnt++) {
		if ($this->tree[$cnt]['text'] == '---') {
			continue;	// separators are significant only for layers-based menus
		}
		$nbsp = '';
		for ($i=1; $i<$this->tree[$cnt]['level']; $i++) {
			$nbsp .= '&nbsp;&nbsp;&nbsp;';
		}
		$t->setVar(array(
			'nbsp'		=> $nbsp,
			'href'		=> $this->tree[$cnt]['parsed_href'],
			'title'		=> $this->tree[$cnt]['parsed_title'],
			'target'	=> $this->tree[$cnt]['parsed_target'],
			'text'		=> $this->tree[$cnt]['parsed_text']
		));
		$plain_menu_blck .= $t->parse('plain_menu_cell_blck', 'plain_menu_cell', false);
	}
	$t->setVar('plain_menu_cell_blck', $plain_menu_blck);
	$this->_plainMenu[$menu_name] = $t->parse('template_blck', 'template');

	return $this->_plainMenu[$menu_name];
}

/**
* Method that returns the code of the requested Plain Menu
* @access public
* @param string $menu_name the name of the menu whose Plain Menu code
*   has to be returned
* @return string
*/
function getPlainMenu($menu_name)
{
	return $this->_plainMenu[$menu_name];
}

/**
* Method that prints the code of the requested Plain Menu
* @access public
* @param string $menu_name the name of the menu whose Plain Menu code
*   has to be printed
* @return void
*/
function printPlainMenu($menu_name)
{
	print $this->_plainMenu[$menu_name];
}

/**
* The method to set horizontalPlainMenuTpl
* @access public
* @return boolean
*/
function setHorizontalPlainMenuTpl($horizontalPlainMenuTpl)
{
	if (str_replace('/', '', $horizontalPlainMenuTpl) == $horizontalPlainMenuTpl) {
		$horizontalPlainMenuTpl = $this->tpldir . $horizontalPlainMenuTpl;
	}
	if (!file_exists($horizontalPlainMenuTpl)) {
		$this->error("setHorizontalPlainMenuTpl: file $horizontalPlainMenuTpl does not exist.");
		return false;
	}
	$this->horizontalPlainMenuTpl = $horizontalPlainMenuTpl;
	return true;
}

/**
* Method to prepare a new Horizontal Plain Menu.
*
* This method processes items of a menu to prepare and return
* the corresponding Horizontal Plain Menu code.
*
* @access public
* @param string $menu_name the name of the menu whose items have to be processed
* @return string
*/
function newHorizontalPlainMenu(
	$menu_name = ''	// non consistent default...
	)
{
	$horizontal_plain_menu_blck = '';
	$t = new Template_PHPLIB();
	$t->setFile('tplfile', $this->horizontalPlainMenuTpl);
	$t->setBlock('tplfile', 'template', 'template_blck');
	$t->setBlock('template', 'horizontal_plain_menu_cell', 'horizontal_plain_menu_cell_blck');
	$t->setVar('horizontal_plain_menu_cell_blck', '');
	$t->setBlock('horizontal_plain_menu_cell', 'plain_menu_cell', 'plain_menu_cell_blck');	
	$t->setVar('plain_menu_cell_blck', '');
	for ($cnt=$this->_firstItem[$menu_name]; $cnt<=$this->_lastItem[$menu_name]; $cnt++) {
		if ($this->tree[$cnt]['text'] == '---') {
			continue;	// separators are significant only for layers-based menus
		}
		if ($this->tree[$cnt]['level'] == 1 && $cnt > $this->_firstItem[$menu_name]) {
			$t->parse('horizontal_plain_menu_cell_blck', 'horizontal_plain_menu_cell', true);
			$t->setVar('plain_menu_cell_blck', '');
		}
		$nbsp = '';
		for ($i=1; $i<$this->tree[$cnt]['level']; $i++) {
			$nbsp .= '&nbsp;&nbsp;&nbsp;';
		}
		$t->setVar(array(
			'nbsp'		=> $nbsp,
			'href'		=> $this->tree[$cnt]['parsed_href'],
			'title'		=> $this->tree[$cnt]['parsed_title'],
			'target'	=> $this->tree[$cnt]['parsed_target'],
			'text'		=> $this->tree[$cnt]['parsed_text']
		));
		$t->parse('plain_menu_cell_blck', 'plain_menu_cell', true);
	}
	$t->parse('horizontal_plain_menu_cell_blck', 'horizontal_plain_menu_cell', true);
	$this->_horizontalPlainMenu[$menu_name] = $t->parse('template_blck', 'template');

	return $this->_horizontalPlainMenu[$menu_name];
}

/**
* Method that returns the code of the requested Horizontal Plain Menu
* @access public
* @param string $menu_name the name of the menu whose Horizontal Plain Menu code
*   has to be returned
* @return string
*/
function getHorizontalPlainMenu($menu_name)
{
	return $this->_horizontalPlainMenu[$menu_name];
}

/**
* Method that prints the code of the requested Horizontal Plain Menu
* @access public
* @param string $menu_name the name of the menu whose Horizontal Plain Menu code
*   has to be printed
* @return void
*/
function printHorizontalPlainMenu($menu_name)
{
	print $this->_horizontalPlainMenu[$menu_name];
}

} /* END OF CLASS */

?>
