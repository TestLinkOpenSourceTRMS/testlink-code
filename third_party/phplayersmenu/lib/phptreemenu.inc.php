<?php
// PHP Layers Menu 3.2.0-rc (C) 2001-2004 Marco Pratesi - http://www.marcopratesi.it/

/**
* This file contains the code of the PHPTreeMenu class.
* @package PHPLayersMenu
*/

/**
* This is the PHPTreeMenu class of the PHP Layers Menu library.
*
* This class depends on the LayersMenuCommon class.  It provides "server-side" (PHP-based) tree menus, that to do not require JavaScript to work.
*
* @version 3.2.0-rc
* @package PHPLayersMenu
*/
class PHPTreeMenu extends LayersMenuCommon
{

/**
* The character used for the PHP Tree Menu in the query string to separate items ids
* @access private
* @var string
*/
var $phpTreeMenuSeparator;
/**
* The default value of the expansion string for the PHP Tree Menu
* @access private
* @var string
*/
var $phpTreeMenuDefaultExpansion;
/**
* Type of images used for the Tree Menu
* @access private
* @var string
*/
var $phpTreeMenuImagesType;
/**
* Prefix for filenames of images of a theme
* @access private
* @var string
*/
var $phpTreeMenuTheme;
/**
* An array where we store the PHP Tree Menu code for each menu
* @access private
* @var array
*/
var $_phpTreeMenu;

/**
* The constructor method; it initializates some variables
* @return void
*/
function PHPTreeMenu()
{
	$this->LayersMenuCommon();

	$this->phpTreeMenuSeparator = '|';
	$this->phpTreeMenuDefaultExpansion = '';
	$this->phpTreeMenuImagesType = 'png';
	$this->phpTreeMenuTheme = '';
	$this->_phpTreeMenu = array();
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
* The method to set the value of separator for the Tree Menu query string
* @access public
* @return void
*/
function setPHPTreeMenuSeparator($phpTreeMenuSeparator)
{
	$this->phpTreeMenuSeparator = $phpTreeMenuSeparator;
}

/**
* The method to set the default value of the expansion string for the PHP Tree Menu
* @access public
* @return void
*/
function setPHPTreeMenuDefaultExpansion($phpTreeMenuDefaultExpansion)
{
	$this->phpTreeMenuDefaultExpansion = $phpTreeMenuDefaultExpansion;
}

/**
* The method to set the type of images used for the Tree Menu
* @access public
* @return void
*/
function setPHPTreeMenuImagesType($phpTreeMenuImagesType)
{
	$this->phpTreeMenuImagesType = $phpTreeMenuImagesType;
}

/**
* The method to set the prefix for filenames of images of a theme
* @access public
* @return void
*/
function setPHPTreeMenuTheme($phpTreeMenuTheme)
{
	$this->phpTreeMenuTheme = $phpTreeMenuTheme;
}

/**
* Method to prepare a new PHP Tree Menu.
*
* This method processes items of a menu and parameters submitted
* through GET (i.e. nodes to be expanded) to prepare and return
* the corresponding Tree Menu code.
*
* @access public
* @param string $menu_name the name of the menu whose items have to be processed
* @return string
*/
function newPHPTreeMenu(
	$menu_name = ''	// non consistent default...
	)
{
	$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://';
	$this_host = (isset($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
	if (isset($_SERVER['SCRIPT_NAME'])) {
		$me = $_SERVER['SCRIPT_NAME'];
	} elseif (isset($_SERVER['REQUEST_URI'])) {
		$me = $_SERVER['REQUEST_URI'];
	} elseif (isset($_SERVER['PHP_SELF'])) {
		$me = $_SERVER['PHP_SELF'];
	} elseif (isset($_SERVER['PATH_INFO'])) {
		$me = $_SERVER['PATH_INFO'];
	}
	$url = $protocol . $this_host . $me;
	$query = '';
	reset($_GET);
	while (list($key, $value) = each($_GET)) {
		if ($key != 'p' && $value != '') {
			$query .= '&amp;' . $key . '=' . $value;
		}
	}
	if ($query != '') {
		$query = '?' . substr($query, 5) . '&amp;p=';
	} else {
		$query = '?p=';
	}
	$p = (isset($_GET['p'])) ? $_GET['p'] : $this->phpTreeMenuDefaultExpansion;

/* ********************************************************* */
/* Based on TreeMenu 1.1 by Bjorge Dijkstra (bjorge@gmx.net) */
/* ********************************************************* */
	$this->_phpTreeMenu[$menu_name] = '';

	$img_collapse			= $this->imgwww . $this->phpTreeMenuTheme . 'tree_collapse.' . $this->phpTreeMenuImagesType;
	$alt_collapse			= '--';
	$img_collapse_corner		= $this->imgwww . $this->phpTreeMenuTheme . 'tree_collapse_corner.' . $this->phpTreeMenuImagesType;
	$alt_collapse_corner		= '--';
	$img_collapse_corner_first	= $this->imgwww . $this->phpTreeMenuTheme . 'tree_collapse_corner_first.' . $this->phpTreeMenuImagesType;
	$alt_collapse_corner_first	= '--';
	$img_collapse_first		= $this->imgwww . $this->phpTreeMenuTheme . 'tree_collapse_first.' . $this->phpTreeMenuImagesType;
	$alt_collapse_first		= '--';
	$img_corner			= $this->imgwww . $this->phpTreeMenuTheme . 'tree_corner.' . $this->phpTreeMenuImagesType;
	$alt_corner			= '`-';
	$img_expand			= $this->imgwww . $this->phpTreeMenuTheme . 'tree_expand.' . $this->phpTreeMenuImagesType;
	$alt_expand			= '+-';
	$img_expand_corner		= $this->imgwww . $this->phpTreeMenuTheme . 'tree_expand_corner.' . $this->phpTreeMenuImagesType;
	$alt_expand_corner		= '+-';
	$img_expand_corner_first	= $this->imgwww . $this->phpTreeMenuTheme . 'tree_expand_corner_first.' . $this->phpTreeMenuImagesType;
	$alt_expand_corner_first	= '+-';
	$img_expand_first		= $this->imgwww . $this->phpTreeMenuTheme . 'tree_expand_first.' . $this->phpTreeMenuImagesType;
	$alt_expand_first		= '+-';
	$img_folder_closed		= $this->imgwww . $this->phpTreeMenuTheme . 'tree_folder_closed.' . $this->phpTreeMenuImagesType;
	$alt_folder_closed		= '->';
	$img_folder_open		= $this->imgwww . $this->phpTreeMenuTheme . 'tree_folder_open.' . $this->phpTreeMenuImagesType;
	$alt_folder_open		= '->';
	$img_leaf			= $this->imgwww . $this->phpTreeMenuTheme . 'tree_leaf.' . $this->phpTreeMenuImagesType;
	$alt_leaf			= '->';
	$img_space			= $this->imgwww . $this->phpTreeMenuTheme . 'tree_space.' . $this->phpTreeMenuImagesType;
	$alt_space			= '  ';
	$img_split			= $this->imgwww . $this->phpTreeMenuTheme . 'tree_split.' . $this->phpTreeMenuImagesType;
	$alt_split			= '|-';
	$img_split_first		= $this->imgwww . $this->phpTreeMenuTheme . 'tree_split_first.' . $this->phpTreeMenuImagesType;
	$alt_split_first		= '|-';
	$img_vertline			= $this->imgwww . $this->phpTreeMenuTheme . 'tree_vertline.' . $this->phpTreeMenuImagesType;
	$alt_vertline			= '| ';

	for ($i=$this->_firstItem[$menu_name]; $i<=$this->_lastItem[$menu_name]; $i++) {
		$expand[$i] = 0;
		$visible[$i] = 0;
		$this->tree[$i]['last_item'] = 0;
	}
	for ($i=0; $i<=$this->_maxLevel[$menu_name]; $i++) {
		$levels[$i] = 0;
	}

	// Get numbers of nodes to be expanded
	if ($p != '') {
		$explevels = explode($this->phpTreeMenuSeparator, $p);
		$explevels_count = count($explevels);
		for ($i=0; $i<$explevels_count; $i++) {
			$expand[$explevels[$i]] = 1;
		}
	}

	// Find last nodes of subtrees
	$last_level = $this->_maxLevel[$menu_name];
	for ($i=$this->_lastItem[$menu_name]; $i>=$this->_firstItem[$menu_name]; $i--) {
		if ($this->tree[$i]['level'] < $last_level) {
			for ($j=$this->tree[$i]['level']+1; $j<=$this->_maxLevel[$menu_name]; $j++) {
				$levels[$j] = 0;
			}
		}
		if ($levels[$this->tree[$i]['level']] == 0) {
			$levels[$this->tree[$i]['level']] = 1;
			$this->tree[$i]['last_item'] = 1;
		} else {
			$this->tree[$i]['last_item'] = 0;
		}
		$last_level = $this->tree[$i]['level'];
	}

	// Determine visible nodes
	// all root nodes are always visible
	for ($i=$this->_firstItem[$menu_name]; $i<=$this->_lastItem[$menu_name]; $i++) {
		if ($this->tree[$i]['level'] == 1) {
			$visible[$i] = 1;
		}
	}
	if (isset($explevels)) {
		for ($i=0; $i<$explevels_count; $i++) {
			$n = $explevels[$i];
			if ($n >= $this->_firstItem[$menu_name] && $n <= $this->_lastItem[$menu_name] && $visible[$n] == 1 && $expand[$n] == 1) {
				$j = $n + 1;
				while ($j<=$this->_lastItem[$menu_name] && $this->tree[$j]['level']>$this->tree[$n]['level']) {
					if ($this->tree[$j]['level'] == $this->tree[$n]['level']+1) {
						$visible[$j] = 1;
					}
					$j++;
				}
			}
		}
	}

	// Output nicely formatted tree
	for ($i=0; $i<$this->_maxLevel[$menu_name]; $i++) {
		$levels[$i] = 1;
	}
	$max_visible_level = 0;
	for ($cnt=$this->_firstItem[$menu_name]; $cnt<=$this->_lastItem[$menu_name]; $cnt++) {
		if ($visible[$cnt]) {
			$max_visible_level = max($max_visible_level, $this->tree[$cnt]['level']);
		}
	}
	for ($cnt=$this->_firstItem[$menu_name]; $cnt<=$this->_lastItem[$menu_name]; $cnt++) {
		if ($this->tree[$cnt]['text'] == '---') {
			continue;	// separators are significant only for layers-based menus
		}

		if (isset($this->tree[$cnt]['selected']) && $this->tree[$cnt]['selected']) {
			$linkstyle = 'phplmselected';
		} else {
			$linkstyle = 'phplm';
		}

		if ($visible[$cnt]) {
			$this->_phpTreeMenu[$menu_name] .= '<div class="treemenudiv">' . "\n"; 

			// vertical lines from higher levels
			for ($i=0; $i<$this->tree[$cnt]['level']-1; $i++) {
				if ($levels[$i] == 1) {
					$img = $img_vertline;
					$alt = $alt_vertline;
				} else {
					$img = $img_space;
					$alt = $alt_space;
				}
				$this->_phpTreeMenu[$menu_name] .= '<img align="top" border="0" class="imgs" src="' . $img . '" alt="' . $alt . '" />';
			}

			$not_a_leaf = $cnt<$this->_lastItem[$menu_name] && $this->tree[$cnt+1]['level']>$this->tree[$cnt]['level'];

			if ($not_a_leaf) {
				// Create expand/collapse parameters
				$params = '';
				for ($i=$this->_firstItem[$menu_name]; $i<=$this->_lastItem[$menu_name]; $i++) {
					if ($expand[$i] == 1 && $cnt!= $i || ($expand[$i] == 0 && $cnt == $i)) {
						$params .= $this->phpTreeMenuSeparator . $i;
					}
				}
				if ($params != '') {
					$params = substr($params, 1);
				}
			}

			if ($this->tree[$cnt]['last_item'] == 1) {
			// corner at end of subtree or t-split
				if ($not_a_leaf) {
					if ($expand[$cnt] == 0) {
						if ($cnt == $this->_firstItem[$menu_name]) {
							$img = $img_expand_corner_first;
							$alt = $alt_expand_corner_first;
						} else {
							$img = $img_expand_corner;
							$alt = $alt_expand_corner;
						}
					} else {
						if ($cnt == $this->_firstItem[$menu_name]) {
							$img = $img_collapse_corner_first;
							$alt = $alt_collapse_corner_first;
						} else {
							$img = $img_collapse_corner;
							$alt = $alt_collapse_corner;
						}
					}
					$this->_phpTreeMenu[$menu_name] .= '<a name="' . $cnt . '" class="' . $linkstyle . '" href="' . $url . $query . $params . '#' . $cnt . '"><img align="top" border="0" class="imgs" src="' . $img . '" alt="' . $alt . '" /></a>';
				} else {
					$this->_phpTreeMenu[$menu_name] .= '<img align="top" border="0" class="imgs" src="' . $img_corner . '" alt="' . $alt_corner . '" />';
				}
				$levels[$this->tree[$cnt]['level']-1] = 0;
			} else {
				if ($not_a_leaf) {
					if ($expand[$cnt] == 0) {
						if ($cnt == $this->_firstItem[$menu_name]) {
							$img = $img_expand_first;
							$alt = $alt_expand_first;
						} else {
							$img = $img_expand;
							$alt = $alt_expand;
						}
					} else {
						if ($cnt == $this->_firstItem[$menu_name]) {
							$img = $img_collapse_first;
							$alt = $alt_collapse_first;
						} else {
							$img = $img_collapse;
							$alt = $alt_collapse;
						}
					}
					$this->_phpTreeMenu[$menu_name] .= '<a name="' . $cnt . '" class="' . $linkstyle . '" href="' . $url . $query . $params . '#' . $cnt . '"><img align="top" border="0" class="imgs" src="' . $img . '" alt="' . $alt . '" /></a>';
				} else {
					if ($cnt == $this->_firstItem[$menu_name]) {
						$img = $img_split_first;
						$alt = $alt_split_first;
					} else {
						$img = $img_split;
						$alt = $alt_split;
					}
					$this->_phpTreeMenu[$menu_name] .= '<img align="top" border="0" class="imgs" src="' . $img . '" alt="' . $alt . '" />';
				}
				$levels[$this->tree[$cnt]['level']-1] = 1;
			}

			if ($this->tree[$cnt]['parsed_href'] == '' || $this->tree[$cnt]['parsed_href'] == '#') {
				$a_href_open_img = '';
				$a_href_close_img = '';
				$a_href_open = '<a class="phplmnormal">';
				$a_href_close = '</a>';
			} else {
				$a_href_open_img = '<a href="' . $this->tree[$cnt]['parsed_href'] . '"' . $this->tree[$cnt]['parsed_title'] . $this->tree[$cnt]['parsed_target'] . '>';
				$a_href_close_img = '</a>';
				$a_href_open = '<a href="' . $this->tree[$cnt]['parsed_href'] . '"' . $this->tree[$cnt]['parsed_title'] . $this->tree[$cnt]['parsed_target'] . ' class="' . $linkstyle . '">';
				$a_href_close = '</a>';
			}

			if ($not_a_leaf) {
				if ($expand[$cnt] == 1) {
					$img = $img_folder_open;
					$alt = $alt_folder_open;
				} else {
					$img = $img_folder_closed;
					$alt = $alt_folder_closed;
				}
				$this->_phpTreeMenu[$menu_name] .= $a_href_open_img . '<img align="top" border="0" class="imgs" src="' . $img . '" alt="' . $alt . '" />' . $a_href_close_img;
			} else {
				if ($this->tree[$cnt]['parsed_icon'] != '') {
					$this->_phpTreeMenu[$menu_name] .= $a_href_open_img . '<img align="top" border="0" src="' . $this->tree[$cnt]['parsed_icon'] . '" width="' . $this->tree[$cnt]['iconwidth'] . '" height="' . $this->tree[$cnt]['iconheight'] . '" alt="' . $alt_leaf . '" />' . $a_href_close_img;
				} else {
					$this->_phpTreeMenu[$menu_name] .= $a_href_open_img . '<img align="top" border="0" class="imgs" src="' . $img_leaf . '" alt="' . $alt_leaf . '" />' . $a_href_close_img;
				}
			}

			// output item text
			$foobar = $max_visible_level - $this->tree[$cnt]['level'] + 1;
			if ($foobar > 1) {
				$colspan = ' colspan="' . $foobar . '"';
			} else {
				$colspan = '';
			}
			$this->_phpTreeMenu[$menu_name] .= '&nbsp;' . $a_href_open . $this->tree[$cnt]['parsed_text'] . $a_href_close . "\n";
			$this->_phpTreeMenu[$menu_name] .= '</div>' . "\n";
		}
	}
/* ********************************************************* */

/*
	$this->_phpTreeMenu[$menu_name] =
	'<div class="phplmnormal">' . "\n" .
	$this->_phpTreeMenu[$menu_name] .
	'</div>' . "\n";
*/
	// Some (old) browsers do not support the "white-space: nowrap;" CSS property...
	$this->_phpTreeMenu[$menu_name] =
	'<table cellspacing="0" cellpadding="0" border="0">' . "\n" .
	'<tr>' . "\n" .
	'<td class="phplmnormal" nowrap="nowrap">' . "\n" .
	$this->_phpTreeMenu[$menu_name] .
	'</td>' . "\n" .
	'</tr>' . "\n" .
	'</table>' . "\n";

	return $this->_phpTreeMenu[$menu_name];
}

/**
* Method that returns the code of the requested PHP Tree Menu
* @access public
* @param string $menu_name the name of the menu whose PHP Tree Menu code
*   has to be returned
* @return string
*/
function getPHPTreeMenu($menu_name)
{
	return $this->_phpTreeMenu[$menu_name];
}

/**
* Method that prints the code of the requested PHP Tree Menu
* @access public
* @param string $menu_name the name of the menu whose PHP Tree Menu code
*   has to be printed
* @return void
*/
function printPHPTreeMenu($menu_name)
{
	print $this->_phpTreeMenu[$menu_name];
}

} /* END OF CLASS */

?>
