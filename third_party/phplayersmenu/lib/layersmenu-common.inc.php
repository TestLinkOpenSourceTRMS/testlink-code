<?php
// PHP Layers Menu 3.2.0-rc (C) 2001-2004 Marco Pratesi - http://www.marcopratesi.it/

/**
* This file contains the code of the LayersMenuCommon class.
* @package PHPLayersMenu
*/

/**
* This is the "common" class of the PHP Layers Menu library.
*
* You need to include PEAR.php and DB.php if (and only if) you want to use the DB support provided by ths class.
*
* @version 3.2.0-rc
* @package PHPLayersMenu
*/
class LayersMenuCommon
{

/**
* The name of the package
* @access private
* @var string
*/
var $_packageName;
/**
* The version of the package
* @access private
* @var string
*/
var $version;
/**
* The copyright of the package
* @access private
* @var string
*/
var $copyright;
/**
* The author of the package
* @access private
* @var string
*/
var $author;

/**
* URL to be prepended to the menu hrefs
* @access private
* @var string
*/
var $prependedUrl = '';
/**
* Do you want that code execution halts on error?
* @access private
* @var string
*/
var $haltOnError = 'yes';

/**
* The base directory where the package is installed
* @access private
* @var string
*/
var $dirroot;
/**
* The "libjs" directory of the package
* @access private
* @var string
*/
var $libjsdir;
/**
* The directory where images related to the menu can be found
* @access private
* @var string
*/
var $imgdir;
/**
* The http path corresponding to imgdir
* @access private
* @var string
*/
var $imgwww;
/**
* The directory where icons of menu items can be found
* @access private
* @var string
*/
var $icondir;
/**
* The http path corresponding to icondir
* @access private
* @var string
*/
var $iconwww;
/**
* This array may contain width and height of all icons
* @access private
* @var integer
*/
var $iconsize = array();
/**
* If this var is false, width and height of icons have to be detected; if this var is true, width and height of icons are not detected and are retrieved from the iconsize array
* @access private
* @var boolean
*/
var $issetIconsize = false;
/**
* The directory where templates can be found
* @access private
* @var string
*/
var $tpldir;
/**
* The string containing the menu structure
* @access private
* @var string
*/
var $menuStructure;

/**
* It counts nodes for all menus
* @access private
* @var integer
*/
var $_nodesCount;
/**
* A multi-dimensional array to store informations for each menu entry
* @access private
* @var array
*/
var $tree;
/**
* A multi-dimensional array used only with the DB support; for each $menu_name, it stores the $cnt associated to each item id
*
* This array is needed for selection of the current item
* through the corresponding id (see the DB table structure)
* as, internally, items are stored, sorted and addressed in terms of $cnt
*
* @access private
* @var array
*/
var $treecnt;
/**
* The maximum hierarchical level of menu items
* @access private
* @var integer
*/
var $_maxLevel;
/**
* An array that counts the number of first level items for each menu
* @access private
* @var array
*/
var $_firstLevelCnt;
/**
* An array containing the number identifying the first item of each menu
* @access private
* @var array
*/
var $_firstItem;
/**
* An array containing the number identifying the last item of each menu
* @access private
* @var array
*/
var $_lastItem;

/**
* Data Source Name: the connection string for PEAR DB
* @access private
* @var string
*/
var $dsn = 'pgsql://dbuser:dbpass@dbhost/dbname';
//var $dsn = 'mysql://root@localhost/phplayersmenu';
/**
* DB connections are either persistent or not persistent
* @access private
* @var boolean
*/
var $persistent = false;
/**
* Name of the table storing data describing the menu
* @access private
* @var string
*/
var $tableName = 'phplayersmenu';
/**
* Name of the i18n table corresponding to $tableName
* @access private
* @var string
*/
var $tableName_i18n = 'phplayersmenu_i18n';
/**
* Names of fields of the table storing data describing the menu
*
* default field names correspond to the same field names foreseen
* by the menu structure format
*
* @access private
* @var array
*/
var $tableFields = array(
	'id'		=> 'id',
	'parent_id'	=> 'parent_id',
	'text'		=> 'text',
	'href'		=> 'href',
	'title'		=> 'title',
	'icon'		=> 'icon',
	'target'	=> 'target',
	'orderfield'	=> 'orderfield',
	'expanded'	=> 'expanded'
);
/**
* Names of fields of the i18n table corresponding to $tableName
* @access private
* @var array
*/
var $tableFields_i18n = array(
	'language'	=> 'language',
	'id'		=> 'id',
	'text'		=> 'text',
	'title'		=> 'title'
);
/**
* A temporary array to store data retrieved from the DB and to perform the depth-first search
* @access private
* @var array
*/
var $_tmpArray = array();

/**
* The constructor method; it initializates the menu system
* @return void
*/
function LayersMenuCommon()
{
	$this->_packageName = 'PHP Layers Menu';
	$this->version = '3.2.0-rc';
	$this->copyright = '(C) 2001-2004';
	$this->author = 'Marco Pratesi - http://www.marcopratesi.it/';

	$this->prependedUrl = '';

	//I had to comment this dirroot out because it was screwing up my tree.. 
	//$this->dirroot = './';
	$this->libjsdir = './libjs/';
	$this->imgdir = './menuimages/';
	$this->imgwww = 'menuimages/';
	$this->icondir = './menuicons/';
	$this->iconwww = 'menuicons/';
	$this->tpldir = './templates/';
	$this->menuStructure = '';
	$this->separator = '|';

	$this->_nodesCount = 0;
	$this->tree = array();
	$this->treecnt = array();
	$this->_maxLevel = array();
	$this->_firstLevelCnt = array();
	$this->_firstItem = array();
	$this->_lastItem = array();
}

/**
* The method to set the prepended URL
* @access public
* @return boolean
*/
function setPrependedUrl($prependedUrl)
{
	// We do not perform any check
	$this->prependedUrl = $prependedUrl;
	return true;
}

/**
* The method to set the dirroot directory
* @access public
* @return boolean
*/
function setDirrootCommon($dirroot)
{
	if (!is_dir($dirroot)) {
		$this->error("setDirroot: $dirroot is not a directory.");
		return false;
	}
	if (substr($dirroot, -1) != '/') {
		$dirroot .= '/';
	}
	$oldlength = strlen($this->dirroot);
	$foobar = strpos($this->libjsdir, $this->dirroot);
	if (!($foobar === false || $foobar != 0)) {
		$this->libjsdir = $dirroot . substr($this->libjsdir, $oldlength);
	}
	$foobar = strpos($this->imgdir, $this->dirroot);
	if (!($foobar === false || $foobar != 0)) {
		$this->imgdir = $dirroot . substr($this->imgdir, $oldlength);
	}
	$foobar = strpos($this->icondir, $this->dirroot);
	if (!($foobar === false || $foobar != 0)) {
		$this->icondir = $dirroot . substr($this->icondir, $oldlength);
	}
	$foobar = strpos($this->tpldir, $this->dirroot);
	if (!($foobar === false || $foobar != 0)) {
		$this->tpldir = $dirroot . substr($this->tpldir, $oldlength);
	}
	$this->dirroot = $dirroot;
	return true;
}

/**
* The method to set the libjsdir directory
* @access public
* @return boolean
*/
function setLibjsdir($libjsdir)
{
	if ($libjsdir != '' && substr($libjsdir, -1) != '/') {
		$libjsdir .= '/';
	}
	if ($libjsdir == '' || substr($libjsdir, 0, 1) != '/') {
		$foobar = strpos($libjsdir, $this->dirroot);
		if ($foobar === false || $foobar != 0) {
			$libjsdir = $this->dirroot . $libjsdir;
		}
	}
	if (!is_dir($libjsdir)) {
		$this->error("setLibjsdir: $libjsdir is not a directory.");
		return false;
	}
	$this->libjsdir = $libjsdir;
	return true;
}

/**
* The method to set the imgdir directory
* @access public
* @return boolean
*/
function setImgdir($imgdir)
{
	if ($imgdir != '' && substr($imgdir, -1) != '/') {
		$imgdir .= '/';
	}
	if ($imgdir == '' || substr($imgdir, 0, 1) != '/') {
		$foobar = strpos($imgdir, $this->dirroot);
		if ($foobar === false || $foobar != 0) {
			$imgdir = $this->dirroot . $imgdir;
		}
	}
	if (!is_dir($imgdir)) {
		$this->error("setImgdir: $imgdir is not a directory.");
		return false;
	}
	$this->imgdir = $imgdir;
	return true;
}

/**
* The method to set imgwww
* @access public
* @return void
*/
function setImgwww($imgwww)
{
	if ($imgwww != '' && substr($imgwww, -1) != '/') {
		$imgwww .= '/';
	}
	$this->imgwww = $imgwww;
}

/**
* The method to set the icondir directory
* @access public
* @return boolean
*/
function setIcondir($icondir)
{
	if ($icondir != '' && substr($icondir, -1) != '/') {
		$icondir .= '/';
	}
	if ($icondir == '' || substr($icondir, 0, 1) != '/') {
		$foobar = strpos($icondir, $this->dirroot);
		if ($foobar === false || $foobar != 0) {
			$icondir = $this->dirroot . $icondir;
		}
	}
	if (!is_dir($icondir)) {
		$this->error("setIcondir: $icondir is not a directory.");
		return false;
	}
	$this->icondir = $icondir;
	return true;
}

/**
* The method to set iconwww
* @access public
* @return void
*/
function setIconwww($iconwww)
{
	if ($iconwww != '' && substr($iconwww, -1) != '/') {
		$iconwww .= '/';
	}
	$this->iconwww = $iconwww;
}

/**
* The method to set the iconsize array
* @access public
* @return void
*/
function setIconsize($width, $height)
{
	$this->iconsize['width'] = ($width == (int) $width) ? $width : 0;
	$this->iconsize['height'] = ($height == (int) $height) ? $height : 0;
	$this->issetIconsize = true;
}

/**
* The method to unset the iconsize array
* @access public
* @return void
*/
function unsetIconsize()
{
	unset($this->iconsize['width']);
	unset($this->iconsize['height']);
	$this->issetIconsize = false;
}

/**
* The method to set the tpldir directory
* @access public
* @return boolean
*/
function setTpldirCommon($tpldir)
{
	if ($tpldir != '' && substr($tpldir, -1) != '/') {
		$tpldir .= '/';
	}
	if ($tpldir == '' || substr($tpldir, 0, 1) != '/') {
		$foobar = strpos($tpldir, $this->dirroot);
		if ($foobar === false || $foobar != 0) {
			$tpldir = $this->dirroot . $tpldir;
		}
	}
	if (!is_dir($tpldir)) {
		$this->error("setTpldir: $tpldir is not a directory.");
		return false;
	}
	$this->tpldir = $tpldir;
	return true;
}

/**
* The method to read the menu structure from a file
* @access public
* @param string $tree_file the menu structure file
* @return boolean
*/
function setMenuStructureFile($tree_file)
{
	if (!($fd = fopen($tree_file, 'r'))) {
		$this->error("setMenuStructureFile: unable to open file $tree_file.");
		return false;
	}
	$this->menuStructure = '';
	while ($buffer = fgets($fd, 4096)) {
		$buffer = ereg_replace(chr(13), '', $buffer);	// Microsoft Stupidity Suppression
		$this->menuStructure .= $buffer;
	}
	fclose($fd);
	if ($this->menuStructure == '') {
		$this->error("setMenuStructureFile: $tree_file is empty.");
		return false;
	}
	return true;
}

/**
* The method to set the menu structure passing it through a string
* @access public
* @param string $tree_string the menu structure string
* @return boolean
*/
function setMenuStructureString($tree_string)
{
	$this->menuStructure = ereg_replace(chr(13), '', $tree_string);	// Microsoft Stupidity Suppression
	if ($this->menuStructure == '') {
		$this->error('setMenuStructureString: empty string.');
		return false;
	}
	return true;
}

/**
* The method to set the value of separator
* @access public
* @return void
*/
function setSeparator($separator)
{
	$this->separator = $separator;
}

/**
* The method to set parameters for the DB connection
* @access public
* @param string $dns Data Source Name: the connection string for PEAR DB
* @param bool $persistent DB connections are either persistent or not persistent
* @return boolean
*/
function setDBConnParms($dsn, $persistent=false)
{
	if (!is_string($dsn)) {
		$this->error('initdb: $dsn is not an string.');
		return false;
	}
	if (!is_bool($persistent)) {
		$this->error('initdb: $persistent is not a boolean.');
		return false;
	}
	$this->dsn = $dsn;
	$this->persistent = $persistent;
	return true;
}

/**
* The method to set the name of the table storing data describing the menu
* @access public
* @param string
* @return boolean
*/
function setTableName($tableName)
{
	if (!is_string($tableName)) {
		$this->error('setTableName: $tableName is not a string.');
		return false;
	}
	$this->tableName = $tableName;
	return true;
}

/**
* The method to set the name of the i18n table corresponding to $tableName
* @access public
* @param string
* @return boolean
*/
function setTableName_i18n($tableName_i18n)
{
	if (!is_string($tableName_i18n)) {
		$this->error('setTableName_i18n: $tableName_i18n is not a string.');
		return false;
	}
	$this->tableName_i18n = $tableName_i18n;
	return true;
}

/**
* The method to set names of fields of the table storing data describing the menu
* @access public
* @param array
* @return boolean
*/
function setTableFields($tableFields)
{
	if (!is_array($tableFields)) {
		$this->error('setTableFields: $tableFields is not an array.');
		return false;
	}
	if (count($tableFields) == 0) {
		$this->error('setTableFields: $tableFields is a zero-length array.');
		return false;
	}
	reset ($tableFields);
	while (list($key, $value) = each($tableFields)) {
		$this->tableFields[$key] = ($value == '') ? "''" : $value;
	}
	return true;
}

/**
* The method to set names of fields of the i18n table corresponding to $tableName
* @access public
* @param array
* @return boolean
*/
function setTableFields_i18n($tableFields_i18n)
{
	if (!is_array($tableFields_i18n)) {
		$this->error('setTableFields_i18n: $tableFields_i18n is not an array.');
		return false;
	}
	if (count($tableFields_i18n) == 0) {
		$this->error('setTableFields_i18n: $tableFields_i18n is a zero-length array.');
		return false;
	}
	reset ($tableFields_i18n);
	while (list($key, $value) = each($tableFields_i18n)) {
		$this->tableFields_i18n[$key] = ($value == '') ? "''" : $value;
	}
	return true;
}

/**
* The method to parse the current menu structure and correspondingly update related variables
* @access public
* @param string $menu_name the name to be attributed to the menu
*   whose structure has to be parsed
* @return void
*/
function parseStructureForMenu(
	$menu_name = ''	// non consistent default...
	)
{
	$this->_maxLevel[$menu_name] = 0;
	$this->_firstLevelCnt[$menu_name] = 0;
	$this->_firstItem[$menu_name] = $this->_nodesCount + 1;
	$cnt = $this->_firstItem[$menu_name];
	$menuStructure = $this->menuStructure;

	/* *********************************************** */
	/* Partially based on a piece of code taken from   */
	/* TreeMenu 1.1 - Bjorge Dijkstra (bjorge@gmx.net) */
	/* *********************************************** */

	while ($menuStructure != '') {
		$before_cr = strcspn($menuStructure, "\n");
		$buffer = substr($menuStructure, 0, $before_cr);
		$menuStructure = substr($menuStructure, $before_cr+1);
		if (substr($buffer, 0, 1) == '#') {
			continue;	// commented item line...
		}
		$tmp = rtrim($buffer);
		$node = explode($this->separator, $tmp);
		for ($i=count($node); $i<=6; $i++) {
			$node[$i] = '';
		}
		$this->tree[$cnt]['level'] = strlen($node[0]);
		$this->tree[$cnt]['text'] = $node[1];
		$this->tree[$cnt]['href'] = $node[2];
		$this->tree[$cnt]['title'] = $node[3];
		$this->tree[$cnt]['icon'] = $node[4];
		$this->tree[$cnt]['target'] = $node[5];
		$this->tree[$cnt]['expanded'] = $node[6];
		$cnt++;
	}

	/* *********************************************** */

	$this->_lastItem[$menu_name] = count($this->tree);
	$this->_nodesCount = $this->_lastItem[$menu_name];
	$this->tree[$this->_lastItem[$menu_name]+1]['level'] = 0;
	$this->_postParse($menu_name);
}

/**
* The method to parse the current menu table and correspondingly update related variables
* @access public
* @param string $menu_name the name to be attributed to the menu
*   whose structure has to be parsed
* @param string $language i18n language; either omit it or pass
*   an empty string ('') if you do not want to use any i18n table
* @return void
*/
function scanTableForMenu(
	$menu_name = '', // non consistent default...
	$language = ''
	)
{
	$this->_maxLevel[$menu_name] = 0;
	$this->_firstLevelCnt[$menu_name] = 0;
	unset($this->tree[$this->_nodesCount+1]);
	$this->_firstItem[$menu_name] = $this->_nodesCount + 1;
/* BEGIN BENCHMARK CODE
$time_start = $this->_getmicrotime();
/* END BENCHMARK CODE */
	$db = DB::connect($this->dsn, $this->persistent);
	if (DB::isError($db)) {
		$this->error('scanTableForMenu: ' . $db->getMessage());
	}
	$dbresult = $db->query('
		SELECT ' .
			$this->tableFields['id'] . ' AS id, ' .
			$this->tableFields['parent_id'] . ' AS parent_id, ' .
			$this->tableFields['text'] . ' AS text, ' .
			$this->tableFields['href'] . ' AS href, ' .
			$this->tableFields['title'] . ' AS title, ' .
			$this->tableFields['icon'] . ' AS icon, ' .
			$this->tableFields['target'] . ' AS target, ' .
			$this->tableFields['expanded'] . ' AS expanded
		FROM ' . $this->tableName . '
		WHERE ' . $this->tableFields['id'] . ' <> 1
		ORDER BY ' . $this->tableFields['orderfield'] . ', ' . $this->tableFields['text'] . ' ASC
	');
	$this->_tmpArray = array();
	while ($dbresult->fetchInto($row, DB_FETCHMODE_ASSOC)) {
		$this->_tmpArray[$row['id']]['parent_id'] = $row['parent_id'];
		$this->_tmpArray[$row['id']]['text'] = $row['text'];
		$this->_tmpArray[$row['id']]['href'] = $row['href'];
		$this->_tmpArray[$row['id']]['title'] = $row['title'];
		$this->_tmpArray[$row['id']]['icon'] = $row['icon'];
		$this->_tmpArray[$row['id']]['target'] = $row['target'];
		$this->_tmpArray[$row['id']]['expanded'] = $row['expanded'];
	}
	if ($language != '') {
		$dbresult = $db->query('
			SELECT ' .
				$this->tableFields_i18n['id'] . ' AS id, ' .
				$this->tableFields_i18n['text'] . ' AS text, ' .
				$this->tableFields_i18n['title'] . ' AS title
			FROM ' . $this->tableName_i18n . '
			WHERE ' . $this->tableFields_i18n['id'] . ' <> 1
				AND ' . $this->tableFields_i18n['language'] . ' = ' . "'$language'" . '
		');
		while ($dbresult->fetchInto($row, DB_FETCHMODE_ASSOC)) {
			if (isset($this->_tmpArray[$row['id']])) {
				$this->_tmpArray[$row['id']]['text'] = $row['text'];
				$this->_tmpArray[$row['id']]['title'] = $row['title'];
			}
		}
	}
	unset($dbresult);
	unset($row);
	$this->_depthFirstSearch($menu_name, $this->_tmpArray, 1, 1);
/* BEGIN BENCHMARK CODE
$time_end = $this->_getmicrotime();
$time = $time_end - $time_start;
print "TIME ELAPSED = $time\n<br>";
/* END BENCHMARK CODE */
	$this->_lastItem[$menu_name] = count($this->tree);
	$this->_nodesCount = $this->_lastItem[$menu_name];
	$this->tree[$this->_lastItem[$menu_name]+1]['level'] = 0;
	$this->_postParse($menu_name);
}

function _getmicrotime()
{
	list($usec, $sec) = explode(' ', microtime());
	return ((float) $usec + (float) $sec);
}

/**
* Recursive method to perform the depth-first search of the tree data taken from the current menu table
* @access private
* @param string $menu_name the name to be attributed to the menu
*   whose structure has to be parsed
* @param array $tmpArray the temporary array that stores data to perform
*   the depth-first search
* @param integer $parent_id id of the item whose children have
*   to be searched for
* @param integer $level the hierarchical level of children to be searched for
* @return void
*/
function _depthFirstSearch($menu_name, $tmpArray, $parent_id=1, $level=1)
{
	reset ($tmpArray);
	while (list($id, $foobar) = each($tmpArray)) {
		if ($foobar['parent_id'] == $parent_id) {
			unset($tmpArray[$id]);
			unset($this->_tmpArray[$id]);
			$cnt = count($this->tree) + 1;
			$this->tree[$cnt]['level'] = $level;
			$this->tree[$cnt]['text'] = $foobar['text'];
			$this->tree[$cnt]['href'] = $foobar['href'];
			$this->tree[$cnt]['title'] = $foobar['title'];
			$this->tree[$cnt]['icon'] = $foobar['icon'];
			$this->tree[$cnt]['target'] = $foobar['target'];
			$this->tree[$cnt]['expanded'] = $foobar['expanded'];
			$this->treecnt[$menu_name][$id] = $cnt;
			unset($foobar);
			if ($id != $parent_id) {
				$this->_depthFirstSearch($menu_name, $this->_tmpArray, $id, $level+1);
			}
		}
	}
}

/**
* A method providing parsing needed after both file/string parsing and DB table parsing
* @access private
* @param string $menu_name the name of the menu for which the parsing
*   has to be performed
* @return void
*/
function _postParse(
	$menu_name = ''	// non consistent default...
	)
{
	for ($cnt=$this->_firstItem[$menu_name]; $cnt<=$this->_lastItem[$menu_name]; $cnt++) {	// this counter scans all nodes of the new menu
		$this->tree[$cnt]['child_of_root_node'] = ($this->tree[$cnt]['level'] == 1);
		$this->tree[$cnt]['parsed_text'] = stripslashes($this->tree[$cnt]['text']);
		$this->tree[$cnt]['parsed_href'] = (ereg_replace(' ', '', $this->tree[$cnt]['href']) == '') ? '#' : $this->prependedUrl . $this->tree[$cnt]['href'];
		$this->tree[$cnt]['parsed_title'] = ($this->tree[$cnt]['title'] == '') ? '' : ' title="' . stripslashes($this->tree[$cnt]['title']) . '"';
		$fooimg = $this->icondir . $this->tree[$cnt]['icon'];
		if ($this->tree[$cnt]['icon'] != '' && (substr($this->tree[$cnt]['icon'], 0, 7) == 'http://' || substr($this->tree[$cnt]['icon'], 0, 8) == 'https://')) {
			$this->tree[$cnt]['parsed_icon'] = $this->tree[$cnt]['icon'];
			if ($this->issetIconsize) {
				$this->tree[$cnt]['iconwidth'] = $this->iconsize['width'];
				$this->tree[$cnt]['iconheight'] = $this->iconsize['height'];
			} else {
				$foobar = getimagesize($this->tree[$cnt]['icon']);
				$this->tree[$cnt]['iconwidth'] = $foobar[0];
				$this->tree[$cnt]['iconheight'] = $foobar[1];
			}
		} elseif ($this->tree[$cnt]['icon'] != '' && file_exists($fooimg)) {
			$this->tree[$cnt]['parsed_icon'] = $this->iconwww . $this->tree[$cnt]['icon'];
			if ($this->issetIconsize) {
				$this->tree[$cnt]['iconwidth'] = $this->iconsize['width'];
				$this->tree[$cnt]['iconheight'] = $this->iconsize['height'];
			} else {
				$foobar = getimagesize($fooimg);
				$this->tree[$cnt]['iconwidth'] = $foobar[0];
				$this->tree[$cnt]['iconheight'] = $foobar[1];
			}
		} else {
			$this->tree[$cnt]['parsed_icon'] = '';
		}
		$this->tree[$cnt]['parsed_target'] = ($this->tree[$cnt]['target'] == '') ? '' : ' target="' . $this->tree[$cnt]['target'] . '"';
//		$this->tree[$cnt]['expanded'] = ($this->tree[$cnt]['expanded'] == '') ? 0 : $this->tree[$cnt]['expanded'];
		$this->_maxLevel[$menu_name] = max($this->_maxLevel[$menu_name], $this->tree[$cnt]['level']);
		if ($this->tree[$cnt]['level'] == 1) {
			$this->_firstLevelCnt[$menu_name]++;
		}
	}
}

/**
* A method to replace strings in all URLs (hrefs) of a menu
* @access public
* @param string $menu_name the name of the menu for which the replacement
*   has to be performed
* @param string $string the string to be replaced
* @param string $value the replacement string
* @return void
*/
function replaceStringInUrls($menu_name, $string, $value)
{
	for ($cnt=$this->_firstItem[$menu_name]; $cnt<=$this->_lastItem[$menu_name]; $cnt++) {	// this counter scans all nodes of the new menu
		$this->tree[$cnt]['parsed_href'] = str_replace($string, $value, $this->tree[$cnt]['parsed_href']);
	}
}

/**
* A method to set the same target for all links of a menu
* @access public
* @param string $menu_name the name of the menu for which the targets
*   have to be set
* @param string $target the target to be set for all links
*   of the $menu_name menu
* @return void
*/
function setLinksTargets($menu_name, $target)
{
	for ($cnt=$this->_firstItem[$menu_name]; $cnt<=$this->_lastItem[$menu_name]; $cnt++) {	// this counter scans all nodes of the new menu
		$this->tree[$cnt]['parsed_target'] = ' target="' . $target . '"';
	}
}

/**
* A method to select the current item of $menu_name in terms of $cnt, i.e., very likely, in terms of its line number in the corresponding menu structure file (excluding from the count commented out lines, if any)
* @access public
* @param string $menu_name the name of the menu for which the current item
*   has to be selected
* @param integer $count the line number of the current item
*   in the corresponding menu structure file
*   (excluding from the count commented out lines, if any)
* @return void
*/
function setSelectedItemByCount($menu_name, $count)
{
	if ($count < 1) {
		$this->error("setSelectedItemByCount: the \$count argument is $count, but \$count can not be lower than 1");
		return;
	}
	if ($count > $this->_lastItem[$menu_name] - $this->_firstItem[$menu_name] + 1) {
		$this->error("setSelectedItemByCount: the \$count argument is $count and is larger than the number of items of the '$menu_name' menu");
		return;
	}
	$cnt = $this->_firstItem[$menu_name] + $count - 1;
	$this->tree[$cnt]['selected'] = true;
}

/**
* A method to select the current item of $menu_name in terms of the corresponding id (see the DB table structure); obviously, this method can be used only together with the DB support
* @access public
* @param string $menu_name the name of the menu for which the current item
*   has to be selected
* @param integer $id the id of the current item in the corresponding DB table
* @return void
*/
function setSelectedItemById($menu_name, $id)
{
	if (!isset($this->treecnt[$menu_name][$id])) {
		$this->error("setSelectedItemById: there is not any item with \$id = $id in the '$menu_name' menu");
		return;
	}
	$cnt = $this->treecnt[$menu_name][$id];
	$this->tree[$cnt]['selected'] = true;
}

/**
* A method to select the current item of $menu_name specifying a string that occurs in the current URL
* @access public
* @param string $menu_name the name of the menu for which the current item
*   has to be selected
* @param string $url a string that occurs in the current URL
* @return void
*/
function setSelectedItemByUrl($menu_name, $url)
{
	for ($cnt=$this->_firstItem[$menu_name]; $cnt<=$this->_lastItem[$menu_name]; $cnt++) {  // this counter scans all nodes of the new menu
		if (!(strpos($this->tree[$cnt]['parsed_href'], $url) === false)) {
			$this->tree[$cnt]['selected'] = true;
			break;
		}
	}
}

/**
* A method to select the current item of $menu_name specifying a regular expression that matches (a substring of) the current URL; just the same as the setSelectedItemByUrl() method, but using eregi() instead of strpos()
* @access public
* @param string $menu_name the name of the menu for which the current item
*   has to be selected
* @param string $url_eregi the regular expression that matches
*   (a substring of) the current URL
* @return void
*/
function setSelectedItemByUrlEregi($menu_name, $url_eregi)
{
	for ($cnt=$this->_firstItem[$menu_name]; $cnt<=$this->_lastItem[$menu_name]; $cnt++) {  // this counter scans all nodes of the new menu
		if (eregi($url_eregi, $this->tree[$cnt]['parsed_href'])) {
			$this->tree[$cnt]['selected'] = true;
			break;
		}
	}
}

/**
* Method to handle errors
* @access private
* @param string $errormsg the error message
* @return void
*/
function error($errormsg)
{
	print "<b>LayersMenu Error:</b> $errormsg<br />\n";
	if ($this->haltOnError == 'yes') {
		die("<b>Halted.</b><br />\n");
	}
}

} /* END OF CLASS */

?>
