<?php
/*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: migrate_16_to_17.php,v 1.24 2010/09/12 09:59:58 franciscom Exp $ 

20070515 - franciscom - 
improved controls on source db version

20070317 - franciscom - BUGID 738

*/
require_once(dirname(__FILE__) . "/../../config.inc.php");
require_once(dirname(__FILE__) . '/../../lib/functions/database.class.php' );
require_once(dirname(__FILE__) . "/../../lib/functions/common.php");
require_once(dirname(__FILE__) . "/../../lib/functions/assignment_mgr.class.php");
require_once("../installUtils.php");
require_once("migrate_16_to_17_functions.php");
require_once("Migrator.php");

define('ADODB_ERROR_LOG_TYPE',3); 

// 20080114 - asielb - fix for bug 1244
// 200804 - havlatm fixed wrong compare
if (strtolower(substr(PHP_OS, 0, 3)) == "win")
{
	define('ADODB_ERROR_LOG_DEST','C:/testlink_errors.log');
}
else
{
	 define('ADODB_ERROR_LOG_DEST','/tmp/testlink_errors.log');
}
require_once(dirname(__FILE__) . '/../../third_party/adodb/adodb-errorhandler.inc.php'); 


// over this qty, the process will take a lot of time
define('CRITICAL_TC_SPECS_QTY',5000);
define('FEEDBACK_STEP',2500);

define('FULL_FEEDBACK',FALSE);

if( !isset($_SESSION) )
{ 
  session_start();
}

// assure that no timeout happens for large data
$tlCfg->sessionInactivityTimeout = 300;
ini_set('session.cache_expire',900); // min
ini_set('session.gc_maxlifetime', 18000); // sec
set_time_limit(0); // set_time_limit(t) -> t in seconds; 0 = not used

$inst_type = $_SESSION['installationType'];
$tl_and_version = "TestLink {$_SESSION['testlink_version']}";
?>

<html>
<head>
<title><?php echo $tl_and_version ?></title>
        <style type="text/css">
             @import url('../css/style.css');
        </style>

<script type="text/javascript">
// This code has been obtained from backbase examples pages
//
var DetailController = {
	storedDetail : '',

	toggle : function(id){
		if(this.storedDetail && this.storedDetail != id) 
		{
		  document.getElementById(this.storedDetail).style.display = 'none';
		}
		this.storedDetail = id;
		var style = document.getElementById(id).style;
		if(style.display == 'block') 
		{
		  style.display = 'none';
		}
		else
		{
		  style.display = 'block';
		} 
		return false;
	}
};
</script>
</head>
<body>
<table border="0" cellpadding="0" cellspacing="0" class="mainTable">
  <tr class="fancyRow">
    <td><span class="headers">&nbsp;<img src="./img/dot.gif" alt="" style="margin-top: 1px;" />&nbsp;<?php echo $tl_and_version?></span></td>
    <td align="right"><span class="headers">Installation - <?php echo $inst_type; ?> </span></td>
  </tr>
  <tr class="fancyRow2">
    <td colspan="2" class="border-top-bottom smallText" align="right">&nbsp;</td>
  </tr>
  <tr align="left" valign="top">
    <td colspan="2"><table width="100%"  border="0" cellspacing="0" cellpadding="1">
      <tr align="left" valign="top">
        <td class="pad" id="content" colspan="2">


<?php
// -----------------------------------------------------------------------------------
$db_cfg['source']=array('db_type' => 'mysql',
                        'db_server' => $_SESSION['databasehost'],
                        'db_name'   => $_SESSION['source_databasename'],
                        'db_admin_name' => $_SESSION['databaseloginname'],
                        'db_admin_pass' => $_SESSION['databaseloginpassword']);
                        
$db_cfg['target']=array('db_type' => 'mysql',
                        'db_server' => $_SESSION['databasehost'],
                        'db_name'   => $_SESSION['target_databasename'],
                        'db_admin_name' => $_SESSION['databaseloginname'],
                        'db_admin_pass' => $_SESSION['databaseloginpassword']);
// SOURCE DB
echo '<span>Connecting to Testlink 1.6 (source) database. - ' .
     $db_cfg['source']['db_name'] . ' - </span>';

$source_db = connect_2_db($db_cfg['source']);

// TARGET DB                        
echo '<span>Connecting to Testlink 1.7 (target) database. - ' .
     $db_cfg['target']['db_name'] . ' - </span>';
$target_db = connect_2_db($db_cfg['target']);

// make sure we have a connection to both dbs
if( is_null($source_db) || is_null($target_db) )
{
  echo "<p>FATAL ERROR: Could not connect to either source or target db!</p>";
  exit();
}
// -----------------------------------------------------------------------------------


$tproject_mgr=New testproject($target_db);
$ts_mgr=New testsuite($target_db);
$tc_mgr=New testcase($target_db);
$tree_mgr=New tree($target_db);
$assignment_mgr=New assignment_mgr($target_db);

$assignment_types=$assignment_mgr->get_available_types(); 
$assignment_status=$assignment_mgr->get_available_status();

define('EMPTY_NOTES','');

// all the tables that will be truncated in the 1.7 db
$a_sql=array();
$a_sql[]="TRUNCATE TABLE attachments";
$a_sql[]="TRUNCATE TABLE builds";
$a_sql[]="TRUNCATE TABLE cfield_node_types";
$a_sql[]="TRUNCATE TABLE cfield_testprojects";
$a_sql[]="TRUNCATE TABLE cfield_design_values";
$a_sql[]="TRUNCATE TABLE cfield_execution_values";
$a_sql[]="TRUNCATE TABLE custom_fields";

$a_sql[]="TRUNCATE TABLE executions";
$a_sql[]="TRUNCATE TABLE execution_bugs";

$a_sql[]="TRUNCATE TABLE keywords";
$a_sql[]="TRUNCATE TABLE milestones";
$a_sql[]="TRUNCATE TABLE nodes_hierarchy";
$a_sql[]="TRUNCATE TABLE priorities";

$a_sql[]="TRUNCATE TABLE req_coverage";
$a_sql[]="TRUNCATE TABLE req_specs";
$a_sql[]="TRUNCATE TABLE requirements";

$a_sql[]="TRUNCATE TABLE risk_assignments";

$a_sql[]="TRUNCATE TABLE testprojects";
$a_sql[]="TRUNCATE TABLE testsuites";
$a_sql[]="TRUNCATE TABLE tcversions";
$a_sql[]="TRUNCATE TABLE testplans";
$a_sql[]="TRUNCATE TABLE testcase_keywords";
$a_sql[]="TRUNCATE TABLE testplan_tcversions";

$a_sql[]="TRUNCATE TABLE users";
$a_sql[]="TRUNCATE TABLE user_assignments";
$a_sql[]="TRUNCATE TABLE user_testproject_roles";
$a_sql[]="TRUNCATE TABLE user_testplan_roles";


// -------------------------------------------------------------------------------
// 20070515 - franciscom 
// Give warning to user if version of source db is not ok to be migrated
// $my_ado=$source_db->get_dbmgr_object();
// $the_version_table=$my_ado->MetaTables('TABLES',false,'db_version');
// if( count($the_version_table) == 0 )
// {
//    echo "<br>You are trying to migrate from a TestLink pre 1.6.x" .
//         "<br>this kind of upgrade is NOT AVAILABLE"; 	
//          close_html_and_exit();          
// }
// 
// $the_cols=$my_ado->MetaColumns('db_version');
// 
// // why I'm using upper case? because ado returns upper case.
// if(isset($the_cols['UPGRADE_TS']) )
// {
//   echo "<br>You are trying to migrate from a TestLink version 1.7 or greater" .
//        "<br>this kind of upgrade is NOT AVAILABLE"; 	
//   close_html_and_exit();          
// }
// 
// if(isset($the_cols['UPGRADE_DATE']) )
// {
//   $sql=" SELECT * from db_version ORDER by upgrade_date DESC";
//   $version_arr=$source_db->get_recordset($sql);
// 
//   $version=trim($version_arr[0]['version']);
//   if( $version !== '1.6.2' )
//   {
//      echo "<br>You are trying to migrate from TestLink version {$version} " .
//           "<br>this kind of upgrade is NOT AVAILABLE"; 	
//            close_html_and_exit();          
//   }
// }
// else
// {
//   echo "<br>Structure of your db_version table seems not OK" .
//        "<br>we are unable to continue"; 	
//   close_html_and_exit();          
// }
// // -------------------------------------------------------------------------------     


// Create our Migrator Object
$migrate = new Migrator($source_db, $target_db);

if(false == determine_mysql_version($source_db) || false == determine_mysql_version($target_db))
{
	echo "<br /><b>You appear to be using a version of mysql older than version 5 this may not work!</b><br />";
}
else
{
	echo "<br />mysql version looks ok<br />";
}


// ---TRUNCATE TABLES---
echo '<span>Truncating tables in Testlink 1.7 (target) database. - ' .
     $db_cfg['target']['db_name'] . ' - </span>';
  
foreach($a_sql as $elem) 
{
	echo "<br />executing query $elem";
	$target_db->exec_query($elem);
}
echo '<br />finished truncating tables';
echo "<P><hr>";

// ---STARTING MIGRATION---
echo "<p><b>Please be patient this may take some time!</b><br /><hr /></p>";

// Do the full migration
$migrate->migrate_all();

//---FINISHED WITH MIGRATION---
?>
  </td>
  </tr>
  </td>
  </tr>
  <tr class="fancyRow2">
    <td class="border-top-bottom">
    <?php
    echo '<span class="headers">Migration process finished! :: ' . date("H:i:s"). "</span></b>";
    
    if( isset($_SESSION['basehref']) )
    {
     echo '<p><span class="headers">' .
          '<a href="' . $_SESSION['basehref'] . '">Click Here to login</span></b>';
    }
    else
    {
      echo '<p><span class="headers">Use your browser to point to your TestLink home page</span></b>';
    }
    ?>
    </td>
    <td class="border-top-bottom smallText" align="right">&nbsp;</td>
  </tr>
</table>
</body>
</html>