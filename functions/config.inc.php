<?

///////////////////////////////////////////////
//
// DB Globals you should change for your environment
//
///////////////////////////////////////////////

$dbhost     = "localhost"; //the host name for the server. Use either localhost,server name, or IP
$dbuser     = "root"; //the mysql user
$dbpasswd   = "root"; //the mysql password
$dbname     = "testlink"; //the name of the testlink database

//TODO:replace above values with constants

//to use mysql
define(_TESTLINK_DB_TYPE, "mysql");

//db connection info
define(_TESTLINK_DB_HOST, "localhost");
define(_TESTLINK_DB_USER, "root");
define(_TESTLINK_DB_PASSWORD, "root");
define(_TESTLINK_DB_NAME, "testlink");

///////////////////////////////////////////////
//
// TestLink Globals you should change for your environment
//
///////////////////////////////////////////////

/*
	This is the location of your testlink setup. By default I set the location off of your 
	server's doc root. You'll want to change this if you want to set the tool 
	up somewhere else
*/

define("_LOCATION_OF_TESTLINK_SETUP", $_SERVER[DOCUMENT_ROOT] . "/testlink/");

///////////////////////////////////////////////
//
// Globals setup for your server
//
///////////////////////////////////////////////

//Sets the basehref variable which is your testlink directory location seen through your websever. So, if your testlink is installed in htdocs/testlink on your local host you would use the variable that is there by default

$basehref = "http://" . $_SERVER[SERVER_NAME] . "/testlink/";

//TODO: replace above line with this constant
define("_BASE_HREF", "http://" . $_SERVER[SERVER_NAME] . "/testlink/");

//$basehref   = "http://localhost/testlink/"; 

//Important to note that a forward slash "/" is needed in the end of the basehref

$loginurl = $basehref . "login.php";
//TODO: replace above line with this constant
define("_LOGIN_URL", _BASE_HREF . "login.php");

//This is the root path of the server. It is used whenever we need an absolute path
define("_ROOT_PATH", _LOCATION_OF_TESTLINK_SETUP);

///////////////////////////////////////////////
//
// Globals setup for the javascript tree
//
///////////////////////////////////////////////

//directory path of the tree

define("_TREE_DIR_PATH", _ROOT_PATH . "third_party/phplayersmenu/");

//www path of the tree

define("_TREE_WWW_PATH", $basehref . "third_party/phplayersmenu/");

/*

	The user can also choose to set every single tree menu to compile 
	on the server (PHP) or clientside (Javascript) by setting the variable below.

	To change the menu across the to compile on the server change the define value below to SERVER.

	NOTE: If the variable is set to anything other than SERVER it will be compiled on the client
*/

define("_MENU_COMPILE_SOURCE", "CLIENT");


///////////////////////////////////////////////
//
//Bug Tracking systems
//
///////////////////////////////////////////////

//Currently the only bug tracking system I allow is bugzilla. 
//TestLink uses bugzilla to check if displayed bugs resolved, verified, and closed bugs. If they are it will strike through them

$bugzillaOn = true; // To turn on bugzilla. By default this is false.

//TODO: go through app and set this
//define("_BUG_TRACKING_SYSTEM", "BUGZILLA");

//bug tracking system configuration

//if(_BUG_TRACKING_SYSTEM == "BUGZILLA")
//if(_BUG_TRACKING_SYSTEM == "MANTIS")

if($bugzillaOn == true) //if the user wants to use bugzilla
{
	//Set the bug tracking system info
			
	$bzHost= "pesky.good.com"; //bugzilla host
	$bzUser= "dvanhorn"; //bugzilla user
	$bzPasswd= "dvanhorn"; //bugzilla password
	$bzName = "bugs"; //bugzilla default db

	//TODO: replace above values
	
	define(_BUG_DB_HOST, "localhost");
	define(_BUG_DB_USER, "");
	define(_BUG_DB_PASSWORD, "");
	define(_BUG_DB_NAME, "");

	//TODO: Figure out why i have these here
	//$dbPesky = mysql_connect($bzHost, $bzUser , $bzPasswd); //connect to bugzilla

	//mysql_select_db($bzName,$dbPesky); //use the bugs DB
	
	$bzUrl = "http://box.good.com/bugzilla/show_bug.cgi?id="; //this line creates the link to bugzilla
	
	//TODO: Replace the string above
	define(_BUG_TRACKING_SYSTEM_BUG_VIEW, "http://box.good.com/bugzilla/show_bug.cgi?id=");

}

///////////////////////////////////////////////
//
//Other stuff.. Some you may want to change
//
///////////////////////////////////////////////

//setting the include path so that it automatically 

// Include path seperator different on Windows
if (strtoupper(substr(PHP_OS, 0,3) == 'WIN'))
{
    define(_PATH_SEPERATOR, ';');
} 
else 
{
    define(_PATH_SEPERATOR, ':');
}


$includePath = '.';
$includePath = $includePath . _PATH_SEPERATOR . _ROOT_PATH . "src/beans/";
$includePath = $includePath . _PATH_SEPERATOR . _ROOT_PATH . "src/businessLogic";
$includePath = $includePath . _PATH_SEPERATOR . _ROOT_PATH . "src/actions/";
$includePath = $includePath . _PATH_SEPERATOR . _ROOT_PATH . "src/util/";
$includePath = $includePath . _PATH_SEPERATOR . _ROOT_PATH . "config/";

$includePath = $includePath . _PATH_SEPERATOR . _ROOT_PATH . "third_party/DB-1.6.8/";
$includePath = $includePath . _PATH_SEPERATOR . _ROOT_PATH . "third_party/PEAR-1.3.3/";
$includePath = $includePath . _PATH_SEPERATOR . _ROOT_PATH . "third_party/Smarty-2.6.6/libs/";

//ini_set('include_path', $includePath);
set_include_path($includePath); 

//Not sure if this works or not.. A lot of servers have a default session expire of like 3 minutes. This can be agrivating to users. Users can set their servers cache expire here

//ini_set('session.cache_expire',900);

///////////////////////////////////////////////
//
//Smarty Template setup
//
///////////////////////////////////////////////

define(_SMARTY_TEMPLATE_DIR, _ROOT_PATH . "src/smarty/templates/");
define(_SMARTY_COMPILE_DIR, _ROOT_PATH . "src/smarty/templates_c/");
define(_SMARTY_CONFIG_DIR, _ROOT_PATH . "src/smarty/configs/");
define(_SMARTY_CACHE_DIR, _ROOT_PATH . "src/smarty/cache/");

///////////////////////////////////////////////
//
//TestLink version number. Don't change.. Well you can but why would you?
//
///////////////////////////////////////////////

$TLVersion = "v1.0.4";

//TODO: replace string version
define(_TESTLINK_VERSION, "1.0.5")


?>