<?php
/*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: migrate_17_to_18.php,v 1.9 2009/06/30 10:59:23 havlat Exp $ 

Migrate from 1.7.2 to 1.8.0

Author: franciscom

tasks:
- create records on node_hierarchy for requirement specs, and requirements,
  getting new IDs.
  
- Update IDs on requirement tables on with new ids

- Update executiosn.tcversion_number updateExecutionsTCVersionInfo()

rev: 20090127 - franciscom - BUGID - adding new checks 
     20081108 - franciscom - 
     fixed wrong control on requirements that do not create req nodes on node_hierarchy
      
*/
require_once(dirname(__FILE__) . "/../../../config.inc.php");
require_once(dirname(__FILE__) . '/../../../lib/functions/database.class.php' );
require_once(dirname(__FILE__) . "/../../../lib/functions/common.php");
require_once("../../installUtils.php");
require_once("../migrate_16_to_17_functions.php");
require_once("migrate_17_to_18_functions.php");

// over this qty, the process will take a lot of time
define('CRITICAL_TC_SPECS_QTY',2000);
define('FEEDBACK_STEP',2500);
define('FULL_FEEDBACK',FALSE);
define('DBVERSION4MIG', 'DB 1.2');

$show_memory=true;
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
             @import url('../../css/style.css');
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
    <td><span class="headers">&nbsp;<img src="../../img/dot.gif" alt="" style="margin-top: 1px;" />&nbsp;<?php echo $tl_and_version?></span></td>
    <td align="right"><span class="headers"><?php echo $inst_type; ?> </span></td>
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
$db_cfg['source']=array('db_type' => $_SESSION['databasetype'],
                        'db_server' => $_SESSION['databasehost'],
                        'db_name'   => $_SESSION['source_databasename'],
                        'db_admin_name' => $_SESSION['databaseloginname'],
                        'db_admin_pass' => $_SESSION['databaseloginpassword'],
                        'log_level' => 'NONE');
                        
echo '<span>Connecting to Testlink 1.7.2 (source) database. - ' .
     $db_cfg['source']['db_name'] . ' - </span>';

$source_db = connect_2_db($db_cfg['source']);


// make sure we have a connection to both dbs
if( is_null($source_db) )
{
  echo "<p>FATAL ERROR: Could not connect to source db!</p>";
  exit();
}
// -----------------------------------------------------------------------------------

$tree_mgr=New tree($source_db);
$tproject_mgr=New testproject($source_db);
define('EMPTY_NOTES','');
$a_sql=array();


// -------------------------------------------------------------------------------
// Give warning to user if version of source db is not ok to be migrated
$my_ado=$source_db->get_dbmgr_object();
$the_version_table=$my_ado->MetaTables('TABLES',false,'db_version');
$do_it=0;
if( count($the_version_table) == 0 )
{
   echo "<br>You are trying to migrate from a TestLink pre 1.6.x" .
        "<br>this kind of upgrade is NOT AVAILABLE"; 	
         close_html_and_exit();          
}

$the_cols=$my_ado->MetaColumns('db_version');

// why I'm using upper case? because ado returns upper case.
if(isset($the_cols['UPGRADE_TS']) )
{
    $do_it=1;
    $sql=" SELECT * from db_version ORDER by upgrade_ts DESC";
    $version_arr=$source_db->get_recordset($sql);
    $version=trim($version_arr[0]['version']);
}

if( $do_it== 0 )
{
    if(isset($the_cols['UPGRADE_DATE']) )
    {
      $sql=" SELECT * from db_version ORDER by upgrade_date DESC";
      $version_arr=$source_db->get_recordset($sql);
    
      $version=trim($version_arr[0]['version']);
      if( $version !== '1.6.2' )
      {
         echo "<br>You are trying to migrate from TestLink version {$version} " .
              "<br>this kind of upgrade is NOT AVAILABLE"; 	
               close_html_and_exit();          
      }
    }
    else
    {
      echo "<br>Structure of your db_version table seems not OK" .
           "<br>we are unable to continue"; 	
      close_html_and_exit();          
    }
}
// -------------------------------------------------------------------------------     

// 20090127 - franciscom
// new check.
// . check db version
// To allow Migration must be DB 1.2

if( $version !== DBVERSION4MIG)
{
      echo "<br>Your DB version ({$version}) seems not good, it must be " . DBVERSION4MIG .
           "<br>we are unable to continue"; 	
      close_html_and_exit();          
}

// . check for needed new fields
// . Add here if you add new columns on alter sentences
$tableChecks=array('testprojects' => array('prefix','tc_counter','option_automation'),
                   'executions' => array('tcversion_number','execution_type'),
                   'db_version' => array('notes'),
                   'users' => array('script_key'),
                   'custom_fields' => array('show_on_testplan_design','enable_on_testplan_design'),
                   'testplan_tcversions' => array('node_order','urgency'),
                   'tcversions' => array('execution_type','tc_external_id'));
foreach($tableChecks as $table_name => $fields2check)
{
    list($status_ok,$message)=checkTableFields($my_ado,$table_name,$fields2check);
    if( !$status_ok )
    {
          echo "<br>{$message} <br>we are unable to continue"; 	
          close_html_and_exit();          
    }
}

// ---STARTING MIGRATION---
// -----------------------------------------------------------------------------------------------
// How many test cases ?
$sql="SELECT count(NH.parent_id) AS qta_nodes " .
     " FROM nodes_hierarchy NH,node_types NT" .
     " WHERE NH.node_type_id=NT.id " .
     " AND NT.description='testcase' ";
     
$rs=$source_db->get_recordset($sql);
$qta_nodes=$rs[0]['qta_nodes'];

if( $qta_nodes >= CRITICAL_TC_SPECS_QTY)
{
    $start_message="Due to total test cases quantity Migration process will take at least 15 min";
    echo '<span class="headers">' . $start_message . "</span></b><br><br>";
    ob_flush();flush();
}
// -----------------------------------------------------------------------------------------------

if( function_exists('memory_get_usage') && function_exists('memory_get_peak_usage') && $show_memory)
{
   echo "(Memory Usage: ".memory_get_usage() . " | Peak: " . memory_get_peak_usage() . ")<br><br>";
}

$start_message="Migration process STARTED :: " . date("H:i:s");
echo '<span class="headers">' . $start_message . "</span></b><br><br>";
ob_flush();flush();


$last_message='';
if( checkReqMigrationPreconditions($source_db,$tree_mgr) )
{
    echo "<p><b>Please be patient this may take some time!</b><br /><hr /></p>";
    $oldNew=reqSpecMigration($source_db,$tree_mgr);
    $oldNew=requirementsMigration($source_db,$tree_mgr,$oldNew);
    updateReqInfo($source_db,$tree_mgr,$oldNew);
}   
else
{
  $last_message='Requirements Migration NOT REQUIRED<br>';  
}

updateTProjectInfo($source_db,$tproject_mgr);

// 20080627 - franciscom
updateExecutionsTCVersionInfo($source_db);
$last_message .= "Migration process finished! :: " . date("H:i:s");


//---FINISHED WITH MIGRATION---
?>
  </td>
  </tr>
  </td>
  </tr>
  <tr class="fancyRow2">
    <td class="border-top-bottom">
    <?php
    echo '<span class="headers">' . $last_message . "</span></b>";
    
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

<?php
/*
function: checkReqMigrationPreconditions
          check is we have req spec to migrate

*/
function checkReqMigrationPreconditions(&$source_db,&$tree_mgr)
{
     
  $sql="SELECT * from req_specs";                   
  $rspec=$source_db->fetchRowsIntoMap($sql,'id');
  $sql="SELECT * from requirements";
  $req=$source_db->fetchRowsIntoMap($sql,'id');
  $do_action= ( is_null($req) && is_null($req_spec) ) ? 0 : 1; 

  return $do_action;     
}
?>