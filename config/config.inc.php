<?

///////////////////////////////////////////////
//
// Globals you should change for your environment
//
///////////////////////////////////////////////

$dbhost     = "localhost"; //the host name for the server. Use either localhost,server name, or IP
$dbuser     = ""; //the mysql user
$dbpasswd   = ""; //the mysql password
$dbname     = "testlink"; //the name of the testlink database

///////////////////////////////////////////////
//
// Globals setup for your server
//
///////////////////////////////////////////////

//Sets the basehref variable which is your testlink directory location seen through your websever. So, if your testlink is installed in htdocs/testlink on your local host you would use the variable that is there by default

$basehref = "http://" . $_SERVER[SERVER_NAME] . "/testlink/";

//echo $basehref;

//print_r($_ENV);

//TODO: replace above line with this constant
//define("_BASE_HREF", $_ENV[SERVER_NAME] . "/testlink/");

//$basehref   = "http://localhost/testlink/"; 

//Important to note that a forward slash "/" is needed in the end of the basehref

$loginurl = $basehref . "login.php";
//TODO: replace above line with this constant
//define("_LOGIN_URL", _BASE_HREF . "login.php");


define("_ROOT_PATH", $_SERVER[DOCUMENT_ROOT] . "/testlink/");

///////////////////////////////////////////////
//
// Globals setup for the javascript tree
//
///////////////////////////////////////////////

define("_TREE_DIR_PATH", _ROOT_PATH . "third_party/phplayersmenu/");

//echo _TREE_DIR_PATH;

define("_TREE_WWW_PATH", $basehref . "third_party/phplayersmenu/");


//$myDirPath = '../third_party/phplayersmenu/';
//$myWwwPath =  'third_party/phplayersmenu/';

///////////////////////////////////////////////
//
//Bug Tracking systems
//
///////////////////////////////////////////////

//Currently the only bug tracking system I allow is bugzilla. 
//TestLink uses bugzilla to check if displayed bugs resolved, verified, and closed bugs. If they are it will strike through them

$bugzillaOn = false; // To turn on bugzilla. By default this is false.

if($bugzillaOn == true) //if the user wants to use bugzilla
{
	//Set the bug tracking system info
			
	$bzHost= "pesky.good.com"; //bugzilla host
	$bzUser= "dvanhorn"; //bugzilla user
	$bzPasswd= "dvanhorn"; //bugzilla password
	$bzName = "bugs"; //bugzilla default db

	//$dbPesky = mysql_connect($bzHost, $bzUser , $bzPasswd); //connect to bugzilla

	//mysql_select_db($bzName,$dbPesky); //use the bugs DB
	
	$bzUrl = "http://box.good.com/bugzilla/show_bug.cgi?id="; //this line creates the link to bugzilla

}

///////////////////////////////////////////////
//
//Other stuff.. Some you may want to change
//
///////////////////////////////////////////////

//I've seen some serious include path weirdness when trying to install on a couple servers
//It seems that setting the include path variable (at least locally) stops this problem from happening

ini_set('include_path', '.');

//Not sure if this works or not.. A lot of servers have a default session expire of like 3 minutes. This can be agrivating to users. Users can set their servers cache expire here

//ini_set('session.cache_expire',900);

///////////////////////////////////////////////
//
//TestLink version number. Don't change.. Well you can but why would you?
//
///////////////////////////////////////////////

$TLVersion = "v1.0.4";

?>