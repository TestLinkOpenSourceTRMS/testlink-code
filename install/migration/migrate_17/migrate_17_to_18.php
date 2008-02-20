<?php
/*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: migrate_17_to_18.php,v 1.2 2008/02/20 07:49:13 franciscom Exp $ 

Migrate from 1.7.2 to 1.8.0

Author: franciscom

tasks:
- create records on node_hierarchy for requirement specs, and requirements,
  getting new IDs.
  
- Update IDs on requirement tables on with new ids
  

*/
require_once(dirname(__FILE__) . "/../../../config.inc.php");
require_once(dirname(__FILE__) . '/../../../lib/functions/database.class.php' );
require_once(dirname(__FILE__) . "/../../../lib/functions/common.php");
require_once("../../installUtils.php");
require_once("../migrate_16_to_17_functions.php");
require_once("migrate_17_to_18_functions.php");

// over this qty, the process will take a lot of time
define('CRITICAL_TC_SPECS_QTY',5000);
define('FEEDBACK_STEP',2500);

define('FULL_FEEDBACK',FALSE);

if( !isset($_SESSION) )
{ 
  session_start();
}

set_time_limit(60*40); // set_time_limit(t) -> t in seconds
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
// 20070515 - franciscom 
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


// ---STARTING MIGRATION---

if( checkPreconditions($source_db,$tree_mgr) )
{
    echo "<p><b>Please be patient this may take some time!</b><br /><hr /></p>";
    $oldNew=reqSpecMigration($source_db,$tree_mgr);
    $oldNew=requirementsMigration($source_db,$tree_mgr,$oldNew);
    updateReqInfo($source_db,$tree_mgr,$oldNew);
}   
else
{
  $last_message='Requirements Migration NOT REQUIRED';  
}

updateTProjectInfo($source_db,$tproject_mgr);
$last_message="Migration process finished! :: " . date("H:i:s");


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
function checkPreconditions(&$source_db,&$tree_mgr)
{
  $do_action=1; 
  $sql="SELECT count(NH.parent_id) AS qta_req_spec " .
       " FROM nodes_hierarchy NH,node_types NT" .
       " WHERE NH.node_type_id=NT.id " .
       " AND NT.description='requirement_spec' ";
       
  $rs=$source_db->get_recordset($sql);
  $qta_req_spec=$rs[0]['qta_req_spec'];
  
  $sql="SELECT count(NH.parent_id) AS qta_req " .
       " FROM nodes_hierarchy NH,node_types NT" .
       " WHERE NH.node_type_id=NT.id " .
       " AND NT.description='requirement' ";
       
  $rs=$source_db->get_recordset($sql);
  $qta_req=$rs[0]['qta_req'];
  
  
  if($qta_req==0 and $qta_req_spec==0)
  {
    $do_action=0;
  }
  return $do_action;     
}
?>