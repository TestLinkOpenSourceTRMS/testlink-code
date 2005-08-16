<?php
// PHP Layers Menu 3.2.0-rc (C) 2001-2004 Marco Pratesi - http://www.marcopratesi.it/

/**
* This file contains the code of the LayersMenu class.
* @package PHPLayersMenu
*/

/**
* This is the LayersMenu class of the PHP Layers Menu library.
*
* This class depends on the LayersMenuCommon class and on the PEAR conforming version of the PHPLib Template class, i.e. on HTML_Template_PHPLIB
*
* @version 3.2.0-rc
* @package PHPLayersMenu
*/
class LayersMenu extends LayersMenuCommon
{

/**
* The template to be used for the first level menu of a horizontal menu.
*
* The value of this variable is significant only when preparing
* a horizontal menu.
*
* @access private
* @var string
*/
var $horizontalMenuTpl;
/**
* The template to be used for the first level menu of a vertical menu.
*
* The value of this variable is significant only when preparing
* a vertical menu.
*
* @access private
* @var string
*/
var $verticalMenuTpl;
/**
* The template to be used for submenu layers
* @access private
* @var string
*/
var $subMenuTpl;

/**
* A string containing the header needed to use the menu(s) in the page
* @access private
* @var string
*/
var $header;
/**
* This var tells if the header has been made or not
* @access private
* @var boolean
*/
var $_headerHasBeenMade = false;
/**
* The JS vector to list layers
* @access private
* @var string
*/
var $listl;
/**
* The JS vector of keys to know the father of each layer
* @access private
* @var string
*/
var $father_keys;
/**
* The JS vector of vals to know the father of each layer
* @access private
* @var string
*/
var $father_vals;
/**
* The JS function to set initial positions of all layers
* @access private
* @var string
*/
var $moveLayers;
/**
* An array containing the code related to the first level menu of each menu
* @access private
* @var array
*/
var $_firstLevelMenu;
/**
* A string containing the footer needed to use the menu(s) in the page
* @access private
* @var string
*/
var $footer;
/**
* This var tells if the footer has been made or not
* @access private
* @var boolean
*/
var $_footerHasBeenMade = false;

/**
* The image used for forward arrows.
* @access private
* @var string
*/
var $forwardArrowImg;
/**
* The image used for down arrows.
* @access private
* @var string
*/
var $downArrowImg;
/**
* A 1x1 transparent icon.
* @access private
* @var string
*/
var $transparentIcon;
/**
* An array to keep trace of layers containing / not containing icons
* @access private
* @var array
*/
var $_hasIcons;
/**
* Top offset for positioning of sub menu layers
* @access private
* @var integer
*/
var $menuTopShift;
/**
* Right offset for positioning of sub menu layers
* @access private
* @var integer
*/
var $menuRightShift;
/**
* Left offset for positioning of sub menu layers
* @access private
* @var integer
*/
var $menuLeftShift;
/**
* Threshold for vertical repositioning of a layer
* @access private
* @var integer
*/
var $thresholdY;
/**
* Step for the left boundaries of layers
* @access private
* @var integer
*/
var $abscissaStep;

/**
* The constructor method; it initializates the menu system
* @return void
*/
function LayersMenu(
	$menuTopShift = 6,	// Gtk2-like
	$menuRightShift = 7,	// Gtk2-like
	$menuLeftShift = 2,	// Gtk2-like
	$thresholdY = 5,
	$abscissaStep = 140
	)
{
	$this->LayersMenuCommon();

	$this->horizontalMenuTpl = $this->tpldir . 'layersmenu-horizontal_menu.ihtml';
	$this->verticalMenuTpl = $this->tpldir . 'layersmenu-vertical_menu.ihtml';
	$this->subMenuTpl = $this->tpldir . 'layersmenu-sub_menu.ihtml';

	$this->header = '';
	$this->listl = '';
	$this->father_keys = '';
	$this->father_vals = '';
	$this->moveLayers = '';
	$this->_firstLevelMenu = array();
	$this->footer = '';

	$this->transparentIcon = 'transparent.png';
	$this->_hasIcons = array();
	$this->forwardArrowImg['src'] = 'forward-arrow.png';
	$this->forwardArrowImg['width'] = 4;
	$this->forwardArrowImg['height'] = 7;
	$this->downArrowImg['src'] = 'down-arrow.png';
	$this->downArrowImg['width'] = 9;
	$this->downArrowImg['height'] = 5;
	$this->menuTopShift = $menuTopShift;
	$this->menuRightShift = $menuRightShift;
	$this->menuLeftShift = $menuLeftShift;
	$this->thresholdY = $thresholdY;
	$this->abscissaStep = $abscissaStep;
}

/**
* The method to set the value of menuTopShift
* @access public
* @return void
*/
function setMenuTopShift($menuTopShift)
{
	$this->menuTopShift = $menuTopShift;
}

/**
* The method to set the value of menuRightShift
* @access public
* @return void
*/
function setMenuRightShift($menuRightShift)
{
	$this->menuRightShift = $menuRightShift;
}

/**
* The method to set the value of menuLeftShift
* @access public
* @return void
*/
function setMenuLeftShift($menuLeftShift)
{
	$this->menuLeftShift = $menuLeftShift;
}

/**
* The method to set the value of thresholdY
* @access public
* @return void
*/
function setThresholdY($thresholdY)
{
	$this->thresholdY = $thresholdY;
}

/**
* The method to set the value of abscissaStep
* @access public
* @return void
*/
function setAbscissaStep($abscissaStep)
{
	$this->abscissaStep = $abscissaStep;
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
	$foobar = strpos($this->horizontalMenuTpl, $oldtpldir);
	if (!($foobar === false || $foobar != 0)) {
		$this->horizontalMenuTpl = $this->tpldir . substr($this->horizontalMenuTpl, $oldlength);
	}
	$foobar = strpos($this->verticalMenuTpl, $oldtpldir);
	if (!($foobar === false || $foobar != 0)) {
		$this->verticalMenuTpl = $this->tpldir . substr($this->verticalMenuTpl, $oldlength);
	}
	$foobar = strpos($this->subMenuTpl, $oldtpldir);
	if (!($foobar === false || $foobar != 0)) {
		$this->subMenuTpl = $this->tpldir . substr($this->subMenuTpl, $oldlength);
	}
}

/**
* The method to set horizontalMenuTpl
* @access public
* @return boolean
*/
function setHorizontalMenuTpl($horizontalMenuTpl)
{
	if (str_replace('/', '', $horizontalMenuTpl) == $horizontalMenuTpl) {
		$horizontalMenuTpl = $this->tpldir . $horizontalMenuTpl;
	}
	if (!file_exists($horizontalMenuTpl)) {
		$this->error("setHorizontalMenuTpl: file $horizontalMenuTpl does not exist.");
		return false;
	}
	$this->horizontalMenuTpl = $horizontalMenuTpl;
	return true;
}

/**
* The method to set verticalMenuTpl
* @access public
* @return boolean
*/
function setVerticalMenuTpl($verticalMenuTpl)
{
	if (str_replace('/', '', $verticalMenuTpl) == $verticalMenuTpl) {
		$verticalMenuTpl = $this->tpldir . $verticalMenuTpl;
	}
	if (!file_exists($verticalMenuTpl)) {
		$this->error("setVerticalMenuTpl: file $verticalMenuTpl does not exist.");
		return false;
	}
	$this->verticalMenuTpl = $verticalMenuTpl;
	return true;
}

/**
* The method to set subMenuTpl
* @access public
* @return boolean
*/
function setSubMenuTpl($subMenuTpl)
{
	if (str_replace('/', '', $subMenuTpl) == $subMenuTpl) {
		$subMenuTpl = $this->tpldir . $subMenuTpl;
	}
	if (!file_exists($subMenuTpl)) {
		$this->error("setSubMenuTpl: file $subMenuTpl does not exist.");
		return false;
	}
	$this->subMenuTpl = $subMenuTpl;
	return true;
}

/**
* A method to set transparentIcon
* @access public
* @param string $transparentIcon a transparentIcon filename (without the path)
* @return void
*/
function setTransparentIcon($transparentIcon)
{
	$this->transparentIcon = $transparentIcon;
}

/**
* The method to set an image to be used for the forward arrow
* @access public
* @param string $forwardArrowImg the forward arrow image filename
* @return boolean
*/
function setForwardArrowImg($forwardArrowImg)
{
	if (!file_exists($this->imgdir . $forwardArrowImg)) {
		$this->error('setForwardArrowImg: file ' . $this->imgdir . $forwardArrowImg . ' does not exist.');
		return false;
	}
	$foobar = getimagesize($this->imgdir . $forwardArrowImg);
	$this->forwardArrowImg['src'] = $forwardArrowImg;
	$this->forwardArrowImg['width'] = $foobar[0];
	$this->forwardArrowImg['height'] = $foobar[1];
	return true;
}

/**
* The method to set an image to be used for the down arrow
* @access public
* @param string $downArrowImg the down arrow image filename
* @return boolean
*/
function setDownArrowImg($downArrowImg)
{
	if (!file_exists($this->imgdir . $downArrowImg)) {
		$this->error('setDownArrowImg: file ' . $this->imgdir . $downArrowImg . ' does not exist.');
		return false;
	}
	$foobar = getimagesize($this->imgdir . $downArrowImg);
	$this->downArrowImg['src'] = $downArrowImg;
	$this->downArrowImg['width'] = $foobar[0];
	$this->downArrowImg['height'] = $foobar[1];
	return true;
}

/**
* A method providing parsing needed both for horizontal and vertical menus; it can be useful also with the ProcessLayersMenu extended class
* @access public
* @param string $menu_name the name of the menu for which the parsing
*   has to be performed
* @return void
*/
function parseCommon(
	$menu_name = ''	// non consistent default...
	)
{
	$this->_hasIcons[$menu_name] = false;
	for ($cnt=$this->_firstItem[$menu_name]; $cnt<=$this->_lastItem[$menu_name]; $cnt++) {	// this counter scans all nodes of the new menu
		$this->_hasIcons[$cnt] = false;
		$this->tree[$cnt]['layer_label'] = "L$cnt";
		$current_node[$this->tree[$cnt]['level']] = $cnt;
		if (!$this->tree[$cnt]['child_of_root_node']) {
			$this->tree[$cnt]['father_node'] = $current_node[$this->tree[$cnt]['level']-1];
			$this->father_keys .= ",'L$cnt'";
			$this->father_vals .= ",'" . $this->tree[$this->tree[$cnt]['father_node']]['layer_label'] . "'";
		}
		$this->tree[$cnt]['not_a_leaf'] = ($this->tree[$cnt+1]['level']>$this->tree[$cnt]['level'] && $cnt<$this->_lastItem[$menu_name]);
		// if the above condition is true, the node is not a leaf,
		// hence it has at least a child; if it is false, the node is a leaf
		if ($this->tree[$cnt]['not_a_leaf']) {
			// initialize the corresponding layer content trought a void string
			$this->tree[$cnt]['layer_content'] = '';
			// the new layer is accounted for in the layers list
			$this->listl .= ",'" . $this->tree[$cnt]['layer_label'] . "'";
		}
/*
		if ($this->tree[$cnt]['not_a_leaf']) {
			$this->tree[$cnt]['parsed_href'] = '#';
		}
*/
		if ($this->tree[$cnt]['parsed_icon'] == '') {
			$this->tree[$cnt]['iconsrc'] = $this->imgwww . $this->transparentIcon;
			$this->tree[$cnt]['iconwidth'] = 16;
			$this->tree[$cnt]['iconheight'] = 16;
			$this->tree[$cnt]['iconalt'] = ' ';
		} else {
			if ($this->tree[$cnt]['level'] > 1) {
				$this->_hasIcons[$this->tree[$cnt]['father_node']] = true;
			} else {
				$this->_hasIcons[$menu_name] = true;
			}
			$this->tree[$cnt]['iconsrc'] = $this->tree[$cnt]['parsed_icon'];
			$this->tree[$cnt]['iconalt'] = 'O';
		}
	}
}

/**
* A method needed to update the footer both for horizontal and vertical menus
* @access private
* @param string $menu_name the name of the menu for which the updating
*   has to be performed
* @return void
*/
function _updateFooter(
	$menu_name = ''	// non consistent default...
	)
{
	$t = new Template_PHPLIB();
	$t->setFile('tplfile', $this->subMenuTpl);
	$t->setBlock('tplfile', 'template', 'template_blck');
	$t->setBlock('template', 'sub_menu_cell', 'sub_menu_cell_blck');
	$t->setVar('sub_menu_cell_blck', '');
	$t->setBlock('template', 'separator', 'separator_blck');
	$t->setVar('separator_blck', '');
	$t->setVar('abscissaStep', $this->abscissaStep);

	for ($cnt=$this->_firstItem[$menu_name]; $cnt<=$this->_lastItem[$menu_name]; $cnt++) {
		if ($this->tree[$cnt]['not_a_leaf']) {
			$t->setVar(array(
				'layer_label'		=> $this->tree[$cnt]['layer_label'],
				'layer_title'		=> $this->tree[$cnt]['text'],
				'sub_menu_cell_blck'	=> $this->tree[$cnt]['layer_content']
			));
			$this->footer .= $t->parse('template_blck', 'template');
		}
	}
}

/**
* Method to preparare a horizontal menu.
*
* This method processes items of a menu to prepare the corresponding
* horizontal menu code updating many variables; it returns the code
* of the corresponding _firstLevelMenu
*
* @access public
* @param string $menu_name the name of the menu whose items have to be processed
* @return string
*/
function newHorizontalMenu(
	$menu_name = ''	// non consistent default...
	)
{
	if (!isset($this->_firstItem[$menu_name]) || !isset($this->_lastItem[$menu_name])) {
		$this->error("newHorizontalMenu: the first/last item of the menu '$menu_name' is not defined; please check if you have parsed its menu data.");
		return 0;
	}

	$this->parseCommon($menu_name);

	$t = new Template_PHPLIB();
	$t->setFile('tplfile', $this->horizontalMenuTpl);
	$t->setBlock('tplfile', 'template', 'template_blck');
	$t->setBlock('template', 'horizontal_menu_cell', 'horizontal_menu_cell_blck');
	$t->setVar('horizontal_menu_cell_blck', '');
	$t->setBlock('horizontal_menu_cell', 'cell_link', 'cell_link_blck');
	$t->setVar('cell_link_blck', '');
	$t->setBlock('cell_link', 'cell_icon', 'cell_icon_blck');
	$t->setVar('cell_icon_blck', '');
	$t->setBlock('cell_link', 'cell_arrow', 'cell_arrow_blck');
	$t->setVar('cell_arrow_blck', '');

	$t_sub = new Template_PHPLIB();
	$t_sub->setFile('tplfile', $this->subMenuTpl);
	$t_sub->setBlock('tplfile', 'sub_menu_cell', 'sub_menu_cell_blck');
	$t_sub->setVar('sub_menu_cell_blck', '');
	$t_sub->setBlock('sub_menu_cell', 'cell_icon', 'cell_icon_blck');
	$t_sub->setVar('cell_icon_blck', '');
	$t_sub->setBlock('sub_menu_cell', 'cell_arrow', 'cell_arrow_blck');
	$t_sub->setVar('cell_arrow_blck', '');
	$t_sub->setBlock('tplfile', 'separator', 'separator_blck');
	$t_sub->setVar('separator_blck', '');

	$this->_firstLevelMenu[$menu_name] = '';

	$foobar = $this->_firstItem[$menu_name];
	$this->moveLayers .= "\tvar " . $menu_name . "TOP = getOffsetTop('" . $menu_name . "L" . $foobar . "');\n";
	$this->moveLayers .= "\tvar " . $menu_name . "HEIGHT = getOffsetHeight('" . $menu_name . "L" . $foobar . "');\n";

	for ($cnt=$this->_firstItem[$menu_name]; $cnt<=$this->_lastItem[$menu_name]; $cnt++) {	// this counter scans all nodes of the new menu
		if ($this->tree[$cnt]['not_a_leaf']) {
			// geometrical parameters are assigned to the new layer, related to the above mentioned children
			if ($this->tree[$cnt]['child_of_root_node']) {
				$this->moveLayers .= "\tsetTop('" . $this->tree[$cnt]['layer_label'] . "', "  . $menu_name . "TOP + " . $menu_name . "HEIGHT);\n";
				$this->moveLayers .= "\tmoveLayerX1('" . $this->tree[$cnt]['layer_label'] . "', '" . $menu_name . "');\n";
			}
		}

		if ($this->tree[$cnt]['child_of_root_node']) {
			if ($this->tree[$cnt]['text'] == '---') {
				continue;
			}
			if ($this->tree[$cnt]['not_a_leaf']) {
				$this->tree[$cnt]['onmouseover'] = ' onmouseover="moveLayerX1(' . "'" . $this->tree[$cnt]['layer_label'] . "', '" . $menu_name . "') ; LMPopUp('" . $this->tree[$cnt]['layer_label'] . "'" . ', false);"';
			} else {
				$this->tree[$cnt]['onmouseover'] = ' onmouseover="shutdown();"';
			}
			$t->setVar(array(
				'menu_layer_label'	=> $menu_name . $this->tree[$cnt]['layer_label'],
				'imgwww'		=> $this->imgwww,
				'transparent'		=> $this->transparentIcon,
				'href'			=> $this->tree[$cnt]['parsed_href'],
				'onmouseover'		=> $this->tree[$cnt]['onmouseover'],
				'title'			=> $this->tree[$cnt]['parsed_title'],
				'target'		=> $this->tree[$cnt]['parsed_target'],
				'text'			=> $this->tree[$cnt]['text'],
				'downsrc'		=> $this->downArrowImg['src'],
				'downwidth'		=> $this->downArrowImg['width'],
				'downheight'		=> $this->downArrowImg['height']
			));
			if ($this->tree[$cnt]['parsed_icon'] != '') {
				$t->setVar(array(
					'iconsrc'	=> $this->tree[$cnt]['iconsrc'],
					'iconwidth'	=> $this->tree[$cnt]['iconwidth'],
					'iconheight'	=> $this->tree[$cnt]['iconheight'],
					'iconalt'	=> $this->tree[$cnt]['iconalt'],
				));
				$t->parse('cell_icon_blck', 'cell_icon');
			} else {
				$t->setVar('cell_icon_blck', '');
			}
			if ($this->tree[$cnt]['not_a_leaf']) {
				$t->parse('cell_arrow_blck', 'cell_arrow');
			} else {
				$t->setVar('cell_arrow_blck', '');
			}
			$foobar = $t->parse('cell_link_blck', 'cell_link');
			$t->setVar(array(
				'cellwidth'		=> $this->abscissaStep,
				'cell_link_blck'	=> $foobar
			));
			$t->parse('horizontal_menu_cell_blck', 'horizontal_menu_cell', true);
		} else {
			if ($this->tree[$cnt]['text'] == '---') {
				$this->tree[$this->tree[$cnt]['father_node']]['layer_content'] .= $t_sub->parse('separator_blck', 'separator');
				continue;
			}
			if ($this->tree[$cnt]['not_a_leaf']) {
				$this->tree[$cnt]['onmouseover'] = ' onmouseover="moveLayerX(' . "'" . $this->tree[$cnt]['layer_label'] . "') ; moveLayerY('" . $this->tree[$cnt]['layer_label'] . "') ; LMPopUp('" . $this->tree[$cnt]['layer_label'] . "'". ', false);"';
			} else {
				$this->tree[$cnt]['onmouseover'] = ' onmouseover="LMPopUp(' . "'" . $this->tree[$this->tree[$cnt]['father_node']]['layer_label'] . "'" . ', true);"';
			}
			$t_sub->setVar(array(
				'imgwww'	=> $this->imgwww,
				'transparent'	=> $this->transparentIcon,
				'href'		=> $this->tree[$cnt]['parsed_href'],
				'refid'		=> 'ref' . $this->tree[$cnt]['layer_label'],
				'onmouseover'	=> $this->tree[$cnt]['onmouseover'],
				'title'		=> $this->tree[$cnt]['parsed_title'],
				'target'	=> $this->tree[$cnt]['parsed_target'],
				'text'		=> $this->tree[$cnt]['text'],
				'arrowsrc'	=> $this->forwardArrowImg['src'],
				'arrowwidth'	=> $this->forwardArrowImg['width'],
				'arrowheight'	=> $this->forwardArrowImg['height']
			));
			if ($this->_hasIcons[$this->tree[$cnt]['father_node']]) {
				$t_sub->setVar(array(
					'iconsrc'	=> $this->tree[$cnt]['iconsrc'],
					'iconwidth'	=> $this->tree[$cnt]['iconwidth'],
					'iconheight'	=> $this->tree[$cnt]['iconheight'],
					'iconalt'	=> $this->tree[$cnt]['iconalt']
				));
				$t_sub->parse('cell_icon_blck', 'cell_icon');
			} else {
				$t_sub->setVar('cell_icon_blck', '');
			}
			if ($this->tree[$cnt]['not_a_leaf']) {
				$t_sub->parse('cell_arrow_blck', 'cell_arrow');
			} else {
				$t_sub->setVar('cell_arrow_blck', '');
			}
			$this->tree[$this->tree[$cnt]['father_node']]['layer_content'] .= $t_sub->parse('sub_menu_cell_blck', 'sub_menu_cell');
		}
	}	// end of the "for" cycle scanning all nodes

	$foobar = $this->_firstLevelCnt[$menu_name] * $this->abscissaStep;
	$t->setVar('menuwidth', $foobar);
	$t->setVar(array(
		'layer_label'	=> $menu_name,
		'menubody'	=> $this->_firstLevelMenu[$menu_name]
	));
	$this->_firstLevelMenu[$menu_name] = $t->parse('template_blck', 'template');

	$this->_updateFooter($menu_name);

	return $this->_firstLevelMenu[$menu_name];
}

/**
* Method to preparare a vertical menu.
*
* This method processes items of a menu to prepare the corresponding
* vertical menu code updating many variables; it returns the code
* of the corresponding _firstLevelMenu
*
* @access public
* @param string $menu_name the name of the menu whose items have to be processed
* @return string
*/
function newVerticalMenu(
	$menu_name = ''	// non consistent default...
	)
{
	if (!isset($this->_firstItem[$menu_name]) || !isset($this->_lastItem[$menu_name])) {
		$this->error("newVerticalMenu: the first/last item of the menu '$menu_name' is not defined; please check if you have parsed its menu data.");
		return 0;
	}

	$this->parseCommon($menu_name);

	$t = new Template_PHPLIB();
	$t->setFile('tplfile', $this->verticalMenuTpl);
	$t->setBlock('tplfile', 'template', 'template_blck');
	$t->setBlock('template', 'vertical_menu_box', 'vertical_menu_box_blck');
	$t->setVar('vertical_menu_box_blck', '');
	$t->setBlock('vertical_menu_box', 'vertical_menu_cell', 'vertical_menu_cell_blck');
	$t->setVar('vertical_menu_cell_blck', '');
	$t->setBlock('vertical_menu_cell', 'cell_icon', 'cell_icon_blck');
	$t->setVar('cell_icon_blck', '');
	$t->setBlock('vertical_menu_cell', 'cell_arrow', 'cell_arrow_blck');
	$t->setVar('cell_arrow_blck', '');
	$t->setBlock('vertical_menu_box', 'separator', 'separator_blck');
	$t->setVar('separator_blck', '');

	$t_sub = new Template_PHPLIB();
	$t_sub->setFile('tplfile', $this->subMenuTpl);
	$t_sub->setBlock('tplfile', 'sub_menu_cell', 'sub_menu_cell_blck');
	$t_sub->setVar('sub_menu_cell_blck', '');
	$t_sub->setBlock('sub_menu_cell', 'cell_icon', 'cell_icon_blck');
	$t_sub->setVar('cell_icon_blck', '');
	$t_sub->setBlock('sub_menu_cell', 'cell_arrow', 'cell_arrow_blck');
	$t_sub->setVar('cell_arrow_blck', '');
	$t_sub->setBlock('tplfile', 'separator', 'separator_blck');
	$t_sub->setVar('separator_blck', '');

	$this->_firstLevelMenu[$menu_name] = '';

	$this->moveLayers .= "\tvar " . $menu_name . "TOP = getOffsetTop('" . $menu_name . "');\n";
	$this->moveLayers .= "\tvar " . $menu_name . "LEFT = getOffsetLeft('" . $menu_name . "');\n";
	$this->moveLayers .= "\tvar " . $menu_name . "WIDTH = getOffsetWidth('" . $menu_name . "');\n";

	for ($cnt=$this->_firstItem[$menu_name]; $cnt<=$this->_lastItem[$menu_name]; $cnt++) {	// this counter scans all nodes of the new menu
		if ($this->tree[$cnt]['not_a_leaf']) {
			// geometrical parameters are assigned to the new layer, related to the above mentioned children
			if ($this->tree[$cnt]['child_of_root_node']) {
				$this->moveLayers .= "\tsetLeft('" . $this->tree[$cnt]['layer_label'] . "', " . $menu_name . "LEFT + " . $menu_name . "WIDTH - menuRightShift);\n";
			}
		}

		if ($this->tree[$cnt]['child_of_root_node']) {
			if ($this->tree[$cnt]['text'] == '---') {
				$this->_firstLevelMenu[$menu_name] .= $t->parse('separator_blck', 'separator');
				continue;
			}
			if ($this->tree[$cnt]['not_a_leaf']) {
				$this->tree[$cnt]['onmouseover'] = ' onmouseover="moveLayerX(' . "'" . $this->tree[$cnt]['layer_label'] . "') ; moveLayerY('" . $this->tree[$cnt]['layer_label'] . "') ; LMPopUp('" . $this->tree[$cnt]['layer_label'] . "'" . ', false);"';
			} else {
				$this->tree[$cnt]['onmouseover'] = ' onmouseover="shutdown();"';
			}
			$t->setVar(array(
				'imgwww'	=> $this->imgwww,
				'transparent'	=> $this->transparentIcon,
				'href'		=> $this->tree[$cnt]['parsed_href'],
				'refid'		=> 'ref' . $this->tree[$cnt]['layer_label'],
				'onmouseover'	=> $this->tree[$cnt]['onmouseover'],
				'title'		=> $this->tree[$cnt]['parsed_title'],
				'target'	=> $this->tree[$cnt]['parsed_target'],
				'text'		=> $this->tree[$cnt]['text'],
				'arrowsrc'	=> $this->forwardArrowImg['src'],
				'arrowwidth'	=> $this->forwardArrowImg['width'],
				'arrowheight'	=> $this->forwardArrowImg['height']
			));
			if ($this->_hasIcons[$menu_name]) {
				$t->setVar(array(
					'iconsrc'	=> $this->tree[$cnt]['iconsrc'],
					'iconwidth'	=> $this->tree[$cnt]['iconwidth'],
					'iconheight'	=> $this->tree[$cnt]['iconheight'],
					'iconalt'	=> $this->tree[$cnt]['iconalt']
				));
				$t->parse('cell_icon_blck', 'cell_icon');
			} else {
				$t->setVar('cell_icon_blck', '');
			}
			if ($this->tree[$cnt]['not_a_leaf']) {
				$t->parse('cell_arrow_blck', 'cell_arrow');
			} else {
				$t->setVar('cell_arrow_blck', '');
			}
			$this->_firstLevelMenu[$menu_name] .= $t->parse('vertical_menu_cell_blck', 'vertical_menu_cell');
		} else {
			if ($this->tree[$cnt]['text'] == '---') {
				$this->tree[$this->tree[$cnt]['father_node']]['layer_content'] .= $t_sub->parse('separator_blck', 'separator');
				continue;
			}
			if ($this->tree[$cnt]['not_a_leaf']) {
				$this->tree[$cnt]['onmouseover'] = ' onmouseover="moveLayerX(' . "'" . $this->tree[$cnt]['layer_label'] . "') ; moveLayerY('" . $this->tree[$cnt]['layer_label'] . "') ; LMPopUp('" . $this->tree[$cnt]['layer_label'] . "'" . ', false);"';
			} else {
				$this->tree[$cnt]['onmouseover'] = ' onmouseover="LMPopUp(' . "'" . $this->tree[$this->tree[$cnt]['father_node']]['layer_label'] . "'" . ', true);"';
			}
			$t_sub->setVar(array(
				'imgwww'	=> $this->imgwww,
				'transparent'	=> $this->transparentIcon,
				'href'		=> $this->tree[$cnt]['parsed_href'],
				'refid'		=> 'ref' . $this->tree[$cnt]['layer_label'],
				'onmouseover'	=> $this->tree[$cnt]['onmouseover'],
				'title'		=> $this->tree[$cnt]['parsed_title'],
				'target'	=> $this->tree[$cnt]['parsed_target'],
				'text'		=> $this->tree[$cnt]['text'],
				'arrowsrc'	=> $this->forwardArrowImg['src'],
				'arrowwidth'	=> $this->forwardArrowImg['width'],
				'arrowheight'	=> $this->forwardArrowImg['height']
			));
			if ($this->_hasIcons[$this->tree[$cnt]['father_node']]) {
				$t_sub->setVar(array(
					'iconsrc'	=> $this->tree[$cnt]['iconsrc'],
					'iconwidth'	=> $this->tree[$cnt]['iconwidth'],
					'iconheight'	=> $this->tree[$cnt]['iconheight'],
					'iconalt'	=> $this->tree[$cnt]['iconalt']
				));
				$t_sub->parse('cell_icon_blck', 'cell_icon');
			} else {
				$t_sub->setVar('cell_icon_blck', '');
			}
			if ($this->tree[$cnt]['not_a_leaf']) {
				$t_sub->parse('cell_arrow_blck', 'cell_arrow');
			} else {
				$t_sub->setVar('cell_arrow_blck', '');
			}
			$this->tree[$this->tree[$cnt]['father_node']]['layer_content'] .= $t_sub->parse('sub_menu_cell_blck', 'sub_menu_cell');
		}
	}	// end of the "for" cycle scanning all nodes

	$t->setVar(array(
		'menu_name'			=> $menu_name,
		'vertical_menu_cell_blck'	=> $this->_firstLevelMenu[$menu_name],
		'separator_blck'		=> ''
	));
	$this->_firstLevelMenu[$menu_name] = $t->parse('vertical_menu_box_blck', 'vertical_menu_box');
	$t->setVar('abscissaStep', $this->abscissaStep);
	$t->setVar(array(
		'layer_label'			=> $menu_name,
		'vertical_menu_box_blck'	=> $this->_firstLevelMenu[$menu_name]
	));
	$this->_firstLevelMenu[$menu_name] = $t->parse('template_blck', 'template');

	$this->_updateFooter($menu_name);

	return $this->_firstLevelMenu[$menu_name];
}

/**
* Method to prepare the header.
*
* This method obtains the header using collected informations
* and the suited JavaScript template; it returns the code of the header
*
* @access public
* @return string
*/
function makeHeader()
{
	$t = new Template_PHPLIB();
	$this->listl = 'listl = [' . substr($this->listl, 1) . '];';
	$this->father_keys = 'father_keys = [' . substr($this->father_keys, 1) . '];';
	$this->father_vals = 'father_vals = [' . substr($this->father_vals, 1) . '];';
	$t->setFile('tplfile', $this->libjsdir . 'layersmenu-header.ijs');
	$t->setVar(array(
		'packageName'	=> $this->_packageName,
		'version'	=> $this->version,
		'copyright'	=> $this->copyright,
		'author'	=> $this->author,
		'menuTopShift'	=> $this->menuTopShift,
		'menuRightShift'=> $this->menuRightShift,
		'menuLeftShift'	=> $this->menuLeftShift,
		'thresholdY'	=> $this->thresholdY,
		'abscissaStep'	=> $this->abscissaStep,
		'listl'		=> $this->listl,
		'nodesCount'	=> $this->_nodesCount,
		'father_keys'	=> $this->father_keys,
		'father_vals'	=> $this->father_vals,
		'moveLayers'	=> $this->moveLayers
	));
	$this->header = $t->parse('out', 'tplfile');
	$this->_headerHasBeenMade = true;
	return $this->header;
}

/**
* Method that returns the code of the header
* @access public
* @return string
*/
function getHeader()
{
	if (!$this->_headerHasBeenMade) {
		$this->makeHeader();
	}
	return $this->header;
}

/**
* Method that prints the code of the header
* @access public
* @return void
*/
function printHeader()
{
	print $this->getHeader();
}

/**
* Method that returns the code of the requested _firstLevelMenu
* @access public
* @param string $menu_name the name of the menu whose _firstLevelMenu
*   has to be returned
* @return string
*/
function getMenu($menu_name)
{
	return $this->_firstLevelMenu[$menu_name];
}

/**
* Method that prints the code of the requested _firstLevelMenu
* @access public
* @param string $menu_name the name of the menu whose _firstLevelMenu
*   has to be printed
* @return void
*/
function printMenu($menu_name)
{
	print $this->_firstLevelMenu[$menu_name];
}

/**
* Method to prepare the footer.
*
* This method obtains the footer using collected informations
* and the suited JavaScript template; it returns the code of the footer
*
* @access public
* @return string
*/
function makeFooter()
{
	$t = new Template_PHPLIB();
	$t->setFile('tplfile', $this->libjsdir . 'layersmenu-footer.ijs');
	$t->setVar(array(
		'packageName'	=> $this->_packageName,
		'version'	=> $this->version,
		'copyright'	=> $this->copyright,
		'author'	=> $this->author,
		'footer'	=> $this->footer
		
	));
	$this->footer = $t->parse('out', 'tplfile');
	$this->_footerHasBeenMade = true;
	return $this->footer;
}

/**
* Method that returns the code of the footer
* @access public
* @return string
*/
function getFooter()
{
	if (!$this->_footerHasBeenMade) {
		$this->makeFooter();
	}
	return $this->footer;
}

/**
* Method that prints the code of the footer
* @access public
* @return void
*/
function printFooter()
{
	print $this->getFooter();
}

} /* END OF CLASS */

?>
