<?php
require_once( dirname(__FILE__). '/../../lib/functions/database.class.php' );
require_once(dirname(__FILE__) . "/../../lib/functions/common.php");
require_once(dirname(__FILE__) . "/../../lib/functions/assignment_mgr.class.php");
require_once("../installUtils.php");

session_start();
set_time_limit(60*30); // set_time_limit(t) -> t in seconds
$inst_type = $_SESSION['installationType'];
$tl_and_version = "TestLink {$_SESSION['testlink_version']} ";
?>

<html>
<head>
<!--
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: migrate_16_to_17.php,v 1.5 2006/12/05 08:20:19 franciscom Exp $ 
-->
<title><?php echo $tl_and_version ?>Installer</title>
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
/*
require_once( dirname(__FILE__). '/../../lib/functions/database.class.php' );
require_once(dirname(__FILE__) . "/../../lib/functions/common.php");
require_once("../installUtils.php");

session_start();
set_time_limit(300); // set_time_limit(t) -> t in seconds
$inst_type = $_SESSION['installationType'];
*/

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

echo '<span>Connecting to Testlink 1.6 (source) database. - ' .
     $db_cfg['source']['db_name'] . ' - </span>';

$source_db = connect_2_db($db_cfg['source']);
                        
echo '<span>Connecting to Testlink 1.7 (target) database. - ' .
     $db_cfg['target']['db_name'] . ' - </span>';
$target_db = connect_2_db($db_cfg['target']);

if( is_null($source_db) || is_null($target_db) )
{
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
$old_new=array();
$old_new['product']=array();
$old_new['tplan']=array();
$old_new['mgtcomp']=array();
$old_new['mgtcat']=array();
$old_new['mgttc']=array();
$old_new['build']=array();
$old_new['bug']=array();
$old_new['result']=array();


$a_sql=array();
$a_sql[]="TRUNCATE TABLE attachments";
$a_sql[]="TRUNCATE TABLE builds";
$a_sql[]="TRUNCATE TABLE cfield_node_types";
$a_sql[]="TRUNCATE TABLE cfield_testprojects";
$a_sql[]="TRUNCATE TABLE cfield_design_values";
$a_sql[]="TRUNCATE TABLE cfield_execution_values";
$a_sql[]="TRUNCATE TABLE custom_fields";

$a_sql[]="TRUNCATE TABLE db_version";
$a_sql[]="TRUNCATE TABLE executions";
$a_sql[]="TRUNCATE TABLE execution_bugs";

$a_sql[]="TRUNCATE TABLE keywords";
$a_sql[]="TRUNCATE TABLE milestones";
$a_sql[]="TRUNCATE TABLE nodes_hierarchy";
// $a_sql[]="TRUNCATE TABLE priorities";

$a_sql[]="TRUNCATE TABLE req_coverage";
$a_sql[]="TRUNCATE TABLE req_specs";
$a_sql[]="TRUNCATE TABLE requirements";

// $a_sql[]="TRUNCATE TABLE rights";
$a_sql[]="TRUNCATE TABLE risk_assignments";
//$a_sql[]="TRUNCATE TABLE role_rights";
//$a_sql[]="TRUNCATE TABLE roles";

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


echo '<span>Truncating tables in Testlink 1.7 (target) database. - ' .
     $db_cfg['target']['db_name'] . ' - </span>';
     
foreach($a_sql as $elem) {$target_db->exec_query($elem);}

//exit();
// -----------------------------------------------------------------------------------


echo "<P><hr>";
// -----------------------------------------------------------------------------------
// Get list of 1.6 users
$sql="SELECT * FROM user";
$users=$source_db->fetchRowsIntoMap($sql,'login');
echo "<a onclick=\"return DetailController.toggle('details-users')\" href=\"users/\">
<img src='../img/icon-foldout.gif' align='top' title='show/hide'>Users:</a>";
echo '<div class="detail-container" id="details-users" style="display: block;">';
if(!is_null($users)) 
{
  migrate_users($target_db,$users);
}
else
{
  echo "<pre> Ooops! no users to migrate !!!! </pre>";
}  
echo "</div><p>";
// -----------------------------------------------------------------------------------

// -----------------------------------------------------------------------------------
// To preserve test case ID, I will create first all test cases.
// Using all these joins we will considered only well formed tc =>
// no dangling records.
//
$sql="SELECT mtc.* " .
     "FROM mgtproduct mp, mgtcomponent mc, mgtcategory mk,mgttestcase mtc " .
     "where mc.prodid=mp.id " .
     "AND   mk.compid=mc.id " .
     "AND   mtc.catid=mk.id " .
     "ORDER BY mtc.id";

$tc_specs=$source_db->fetchRowsIntoMap($sql,'id');
echo "<a onclick=\"return DetailController.toggle('details-tcspecs')\" href=\"tcspecs/\">
<img src='../img/icon-foldout.gif' align='top' title='show/hide'>Test Case Specifications:</a>";
echo '<div class="detail-container" id="details-tcspecs" style="display: block;">';
if(is_null($tc_specs)) 
{
		echo "<span class='notok'>There are no test case to be migrated!</span></b>";
}
else
{
  $map_tc_tcversion=migrate_tc_specs($source_db,$target_db,$tc_specs,$users);
}
echo "</div><p>";

// -----------------------------------------------------------------------------------
echo "<a onclick=\"return DetailController.toggle('details-pcc')\" href=\"pcc/\">
<img src='../img/icon-foldout.gif' align='top' title='show/hide'>
Products, Components & Categories migration:</a>";
echo '<div class="detail-container" id="details-pcc" style="display: block;">';

// Get list of 1.6 Products
$sql="SELECT * FROM mgtproduct";

$products=$source_db->fetchRowsIntoMap($sql,'id');
if(is_null($products)) 
{
		echo "<span class='notok'>Failed!</span></b> - Getting products:" .
	  $source_db->error_msg() ."<br>";
}
migrate_cc_specs($source_db,$target_db,$products,$old_new);
echo "</div><p>";

echo "<a onclick=\"return DetailController.toggle('details-kw')\" href=\"kw/\">
<img src='../img/icon-foldout.gif' align='top' title='show/hide'>Keywords migration:</a>";
echo '<div class="detail-container" id="details-kw" style="display: block;">';
migrate_keywords($source_db,$target_db,$products,$old_new);
echo "</div><p>";

echo "<a onclick=\"return DetailController.toggle('details-tcpu')\" href=\"tcpu/\">
<img src='../img/icon-foldout.gif' align='top' title='show/hide'>Test case parent update:</a>";
echo '<div class="detail-container" id="details-tcpu" style="display: block;">';
update_tc_specs_parents($source_db,$target_db,$tc_specs,$old_new);
echo "</div><p>";


echo "<a onclick=\"return DetailController.toggle('details-tplan')\" href=\"tplan/\">
<img src='../img/icon-foldout.gif' align='top' title='show/hide'>Test plans:</a>";
echo '<div class="detail-container" id="details-tplan" style="display: block;">';
$sql="SELECT * FROM project ORDER BY ID";
$tplans=$source_db->fetchRowsIntoMap($sql,'id');
if(is_null($tplans)) 
{
		echo "<span class='notok'>There are no test plans to be migrated!</span></b>";
}
else
{
  migrate_test_plans($source_db,$target_db,$tplans,$old_new);
}
echo "</div><p>";

echo "<a onclick=\"return DetailController.toggle('details-builds')\" href=\"tplan/\">
<img src='../img/icon-foldout.gif' align='top' title='show/hide'>Builds:</a>";
echo '<div class="detail-container" id="details-builds" style="display: block;">';

// 20060908 - franciscom
$sql="SELECT build.*,project.name AS TPLAN_NAME " .
     "FROM build,project WHERE build.projid=project.id ORDER BY project.id";
$builds=$source_db->fetchRowsIntoMap($sql,'id');

if(is_null($builds)) 
{
		echo "<span class='notok'>There are no builds to be migrated!</span></b>";
}
else
{
  migrate_builds($source_db,$target_db,$builds,$old_new);
}
echo "</div><p>";

echo "<a onclick=\"return DetailController.toggle('details-tctpa')\" href=\"tplan/\">
<img src='../img/icon-foldout.gif' align='top' title='show/hide'>Test case -> test plan assignments:</a>";
echo '<div class="detail-container" id="details-tctpa" style="display: block;">';

$sql="SELECT tplan.name as tplan_name,tplan.id as projid,k.compid,tc.mgttcid AS MGTTCID " .
     "FROM component c,category k,testcase tc," .
     "     mgtcomponent mc, mgtcategory mk,mgttestcase mtc,project tplan " .
     "where c.id=k.compid " .
     "AND   k.id=tc.catid " .
     "AND k.mgtcatid = mk.id " .
     "AND c.mgtcompid = mc.id " .
     "AND tc.mgttcid=mtc.id " .
     "AND c.projid = tplan.id " .
     "ORDER BY projid ";

$tplan_elems=$source_db->get_recordset($sql);
if(is_null($tplan_elems)) 
{
		echo "<span class='notok'>All test plans seems to be empty!</span></b>";
}
else
{
  migrate_tplan_contents($source_db,$target_db,$tplan_elems,$map_tc_tcversion,$old_new);
}
echo "</div><p>";



echo "<a onclick=\"return DetailController.toggle('details-results')\" href=\"tplan/\">
<img src='../img/icon-foldout.gif' align='top' title='show/hide'>Executions results:</a>";
echo '<div class="detail-container" id="details-results" style="display: block;">';
$sql="SELECT MGT.id as MGTTCID, R.tcid, R.build_id,R.daterun," .
     "R.runby,R.notes,R.status " .
     "FROM mgttestcase MGT,testcase TC,results R " .
     "WHERE TC.mgttcid=MGT.id " .
     "AND   TC.id=R.tcid ";
$execs=$source_db->fetchRowsIntoMap($sql,'MGTTCID');
if(is_null($execs)) 
{
		echo "<span class='notok'>There are no results to be migrated!</span></b>";
}
else
{
  migrate_results($source_db,$target_db,$execs,$builds,$users,$map_tc_tcversion,$old_new);
}
echo "</div><p>";



echo "<a onclick=\"return DetailController.toggle('details-bugs')\" href=\"tplan/\">
<img src='../img/icon-foldout.gif' align='top' title='show/hide'>Executions bugs:</a>";
echo '<div class="detail-container" id="details-bugs" style="display: block;">';
$sql="SELECT bugs.tcid,bugs.build_id,bugs.bug,mgt.id AS MGTTCID " .
     "FROM bugs,mgttestcase mgt,testcase t " .
     "WHERE bugs.tcid=t.id " .
     "AND   t.mgttcid=mgt.id";
     
$bugs=$source_db->get_recordset($sql);
if(is_null($bugs)) 
{
	echo "<span class='notok'>There are no results to be migrated!</span></b>";
}
else
{
  migrate_bugs($source_db,$target_db,$bugs,$builds,$map_tc_tcversion,$old_new);
}
echo "</div><p>";


echo "<a onclick=\"return DetailController.toggle('details-user_tpa')\" href=\"tplan/\">
<img src='../img/icon-foldout.gif' align='top' title='show/hide'>Users - Test plan assignments:</a>";
echo '<div class="detail-container" id="details-user_tpa" style="display: block;">';
$sql="SELECT * from projrights ORDER BY userid";
$user_tplans=$source_db->fetchRowsIntoMap($sql,'userid');
if(is_null($user_tplans)) 
{
		echo "<span class='notok'>There are no Users - Test plan assignments to be migrated!</span></b>";
}
else
{
  migrate_tesplan_assignments($source_db,$target_db,$user_tplans,$old_new);
}
echo "</div><p>";

echo "<a onclick=\"return DetailController.toggle('details-prior')\" href=\"tplan/\">
<img src='../img/icon-foldout.gif' align='top' title='show/hide'>Priority Rules:</a>";
echo '<div class="detail-container" id="details-prior" style="display: block;">';

$sql="SELECT * from priority";
$prules=$source_db->fetchRowsIntoMap($sql,'projid');
if(is_null($prules)) 
{
		echo "<span class='notok'>There are no results to be migrated!</span></b>";
}
else
{
  migrate_prules($source_db,$target_db,$prules,$old_new);
}
echo "</div><p>";

echo "<a onclick=\"return DetailController.toggle('details-Milestones')\" href=\"tplan/\">
<img src='../img/icon-foldout.gif' align='top' title='show/hide'>Milestones:</a>";
echo '<div class="detail-container" id="details-Milestones" style="display: block;">';

$sql="SELECT * from milestone";
$ms=$source_db->fetchRowsIntoMap($sql,'projid');
if(is_null($ms)) 
{
		echo "<span class='notok'>There are no results to be migrated!</span></b>";
}
else
{
  migrate_milestones($source_db,$target_db,$ms,$old_new);
}
echo "</div><p>";



echo "<a onclick=\"return DetailController.toggle('details-risk')\" href=\"tplan/\">
<img src='../img/icon-foldout.gif' align='top' title='show/hide'>Risk TO BE DONE:</a>";
echo '<div class="detail-container" id="details-risk" style="display: block;">';
echo "</div><p>";

$sql="SELECT tplan.name as tplan_name,tplan.id as projid," .
     "       k.mgtcatid,k.risk,k.importance,k.owner,tc.mgttcid " .
     "FROM   component c,category k,testcase tc," .
     "       mgtcomponent mc, mgtcategory mk,mgttestcase mtc,project tplan " .
     "WHERE c.id=k.compid " .
     "AND k.id=tc.catid " .
     "AND k.mgtcatid = mk.id " .
     "AND c.mgtcompid = mc.id " .
     "AND tc.mgttcid=mtc.id " .
     "AND c.projid = tplan.id " .
     "ORDER BY projid ";
$tp4risk_own=$source_db->get_recordset($sql);


echo "<a onclick=\"return DetailController.toggle('details-own')\" href=\"tplan/\">
<img src='../img/icon-foldout.gif' align='top' title='show/hide'>Ownership (becomes user assignment=test_execution):</a>";
echo '<div class="detail-container" id="details-own" style="display: block;">';

if(is_null($tp4risk_own)) 
{
		echo "<span class='notok'>There are no data to be migrated!</span></b>";
}
else
{
  migrate_ownership($source_db,$target_db,$tp4risk_own,$map_tc_tcversion,$old_new);
}
echo "</div><p>";


echo "<a onclick=\"return DetailController.toggle('details-reqtable')\" href=\"tplan/\">
<img src='../img/icon-foldout.gif' align='top' title='show/hide'> Requirements Table:</a>";
echo '<div class="detail-container" id="details-reqtable" style="display: block;">';

$sql="SELECT * from requirements";
$req=$source_db->fetchRowsIntoMap($sql,'id');
if(is_null($req)) 
{
		echo "<span class='notok'>There are no requirements to be migrated!</span></b>";
}
else
{
  migrate_requirements($source_db,$target_db,$req,$old_new);
}
echo "</div><p>";

echo "<a onclick=\"return DetailController.toggle('details-req_spec_table')\" href=\"tplan/\">
<img src='../img/icon-foldout.gif' align='top' title='show/hide'> req_spec Table:</a>";
echo '<div class="detail-container" id="details-req_spec_table" style="display: block;">';

$sql="SELECT * from req_spec";
$rspec=$source_db->fetchRowsIntoMap($sql,'id');
if(is_null($rspec)) 
{
		echo "<span class='notok'>There are no req specs to be migrated!</span></b>";
}
else
{
  migrate_req_specs($source_db,$target_db,$rspec,$old_new);
}
echo "</div><p>";

echo "<a onclick=\"return DetailController.toggle('details-req_coverage')\" href=\"tplan/\">
<img src='../img/icon-foldout.gif' align='top' title='show/hide'> Req. Coverage (requirement / test case relationship Table):</a>";
echo '<div class="detail-container" id="details-req_coverage" style="display: block;">';

$sql="SELECT * from req_coverage";

// 20061203 - franciscom - id (wrong) -> id_req
$req_cov=$source_db->fetchRowsIntoMap($sql,'id_req');
if(is_null($req_cov)) 
{
		echo "<span class='notok'>There are no req specs to be migrated!</span></b>";
}
else
{
  migrate_req_coverage($source_db,$target_db,$req_cov,$old_new);
}
echo "</div><p>";


echo "<span class='ok'>Migration process finished!</span></b>";

?>
  </td>
  </tr>
  </td>
  </tr>
  <tr class="fancyRow2">
    <td class="border-top-bottom smallText">&nbsp;</td>
    <td class="border-top-bottom smallText" align="right">&nbsp;</td>
  </tr>
</table>
</body>
</html>





<?php
// -----------------------------------   Auxiliary Functions -------------------------
//
//
//
// Tries to connect to a database, displaying ALWAYS an status message.
//
// args   :
//         cfg=array('db_type' => 'mysql',
//                   'db_server' => 'localhost',
//                   'db_admin_name' => 'root',
//                   'db_admin_pass' => 'mysqlroot');
//
//
// returns: 
//          if connection OK -> a database object
//          if connection KO -> null
//
// rev :
//      20061203 - franciscom
//      removed warning due to constant redefinition
//
function connect_2_db($cfg)
{
  
if( !defined('NO_DSN') )
{
  define('NO_DSN',FALSE);
}  

if(strlen(trim($cfg['db_name']))== 0)
{
	echo '<span class="notok">Failed!</span><p />Database Name is empty';
	$db=null;
}
else
{  
  $db = new database($cfg['db_type']);
  @$conn_result = $db->connect(NO_DSN,$cfg['db_server'], 
                                      $cfg['db_admin_name'], $cfg['db_admin_pass'],$cfg['db_name']); 
  
  if( $conn_result['status'] == 0 ) 
  {
  	echo '<span class="notok">Failed!</span><p />Please check the database login details and try again.';
  	echo '<br>Database Error Message: ' . $db->error_msg() . "<br>";
  	$db=null;
  } 
  else 
  {
  	echo "<span class='ok'>OK!</span><p />";
  }
}

return ($db);
}



// 20060712 
function migrate_keywords(&$source_db,&$target_db,&$products,&$old_new)
{
  
foreach($products as $prod_id => $pd)
{
  
  echo "<pre>Processing Test project: " . $pd['name']; echo "</pre>";
  $tproject_id=$old_new['product'][$prod_id];
  $sql="SELECT * FROM keywords WHERE prodid={$prod_id}";
  $kw=$source_db->fetchRowsIntoMap($sql,'id');

  $kw_qty=count($kw);
  if( $kw_qty > 0 )
  {  
    echo "<pre>   Number of keywords: " . $kw_qty; echo "</pre>";
    
    foreach($kw as $key => $value)
    {
      if( strlen(trim($value['keyword'])) > 0 )
      {
        $sql="INSERT INTO keywords (id,keyword,testproject_id,notes) " .
             " VALUES({$value['id']}," .
             "'" . $target_db->prepare_string($value['keyword']) . "',{$tproject_id}," .
             "'" . $target_db->prepare_string($value['notes']) . "')";
        $target_db->exec_query($sql);     
     
        echo "<pre>   {$value['keyword']} migrated</pre>";
      }
      else
      {
        echo "<pre>   Empty keyword for id: {$value['id']} - no migrated</pre>";
      }
    }

  }
  else
  {
    echo "<pre>   There are no keywords defined for this product</pre>";
  }
}

} // function end



// 20060712 
//  `id` int(10) unsigned NOT NULL auto_increment,
//  `login` varchar(30) NOT NULL default '',
//  `password` varchar(32) NOT NULL default '',
//  `role_id` tinyint(3) unsigned NOT NULL default '0',
//  `email` varchar(100) NOT NULL default '',
//  `first` varchar(30) NOT NULL default '',
//  `last` varchar(30) NOT NULL default '',
//  `locale` varchar(10) NOT NULL default 'en_US',
//  `default_testproject_id` int(10) default NULL,
//  `active` tinyint(1) NOT NULL default '1',

// 1.6
//  `password` varchar(32) NOT NULL default '',
//  `login` varchar(30) NOT NULL default '',
//  `id` int(10) unsigned NOT NULL auto_increment,
//  `rightsid` tinyint(3) unsigned NOT NULL default '0',
//  `email` varchar(100) NOT NULL default '',
//  `first` varchar(30) NOT NULL default '',
//  `last` varchar(30) NOT NULL default '',
//  `locale` varchar(10) NOT NULL default 'en_US',
//  `default_product` int(10) default NULL,


function migrate_users(&$target_db,&$users)
{
  
$users_qty=count($users);  
echo "<pre>   Number of users: " . $users_qty; echo "</pre>";
 echo "<pre>";

foreach($users as $login => $the_u)
{
   
 echo"   Migrating user: " . $the_u['login'] . 
      "(" . $the_u['first'] . " " . $the_u['last'] . ")<br>" ;  

  $sql="INSERT INTO users (id,login,password,role_id,email,first,last,locale) " .
       " VALUES({$the_u['id']}," .
       "'" . $target_db->prepare_string($the_u['login']) . "'," .
       "'" . $target_db->prepare_string($the_u['password']) . "'," .
             $the_u['rightsid'] . "," .
       "'" . $target_db->prepare_string($the_u['email']) . "'," .
       "'" . $target_db->prepare_string($the_u['first']) . "'," .
       "'" . $target_db->prepare_string($the_u['last']) . "'," .
       "'" . $target_db->prepare_string($the_u['locale']) . "')";
  $target_db->exec_query($sql);     

    
}
echo "</pre>";

} // function end





// 20060725
//
//
//
function migrate_tc_specs(&$source_db,&$target_db,&$items,&$users)
{
  $first_version=1;
  $tc_mgr=New testcase($target_db);
  $map_tc_tcversion=array();
  $admin_id=1;
  
  echo "Migrating Test Cases - Part I - <br>";
  foreach($items as $item_id => $idata)
  {
     echo "TCID:{$item_id} - {$idata['title']}<br>";
     $tc_mgr->create_tcase_only(0,$idata['title'],$idata['TCorder'],$item_id);  
  }
 
  
  // Now create the TC version
  echo "<br>";
  echo "Migrating Test Cases - Part II - <br>";
  foreach($items as $item_id => $idata)
  {
     $author_id=intval(isset($users[$idata['author']]) ? $users[$idata['author']]['id'] : $admin_id);  
     $x=$tc_mgr->create_tcversion($item_id,$first_version,
                                  $idata['summary'],$idata['steps'],
                                  $idata['exresult'],$author_id);
  

     $sql="UPDATE tcversions SET creation_ts='" . $idata['create_date'] . "'";

     // update reviewer & review date
     $reviewer_id=intval(isset($users[$idata['reviewer']]) ? $users[$idata['reviewer']]['id'] : -1);  
     if($reviewer_id > 0)
     {
       $sql .=",updater_id={$reviewer_id}". 
              ",modification_ts='" . $idata['modified_date'] . "'";
     }
     $target_db->exec_query($sql);
      
     $map_tc_tcversion[$item_id]= $x['id'];
     echo "TCID:{$item_id} - {$idata['title']} - TCVERSION_ID:{$x['id']}<br>";

  }

  echo "Test Case Specifications MIGRATION ENDED<br>";
  return($map_tc_tcversion);
} // end function




// 20060725
function migrate_cc_specs(&$source_db,&$target_db,&$items,&$old_new)
{

$mgtcom_keys=array('intro' => 'introduction',
                  'scope' => 'scope',
                  'ref'   => 'references',
                  'method' => 'methodology',
                  'lim'    => 'limitations');

$mgtcat_keys=array('objective' => 'objective',
                  'config'    => 'configuration',
                  'data'      => 'data',
                  'tools'     => 'tools');


$tproject_mgr=New testproject($target_db);
$ts_mgr=New testsuite($target_db);
$tree_mgr=New tree($target_db);



foreach($items as $prod_id => $pd)
{
  $old_new['product'][$prod_id]=$tproject_mgr->create($pd['name'],
                                                     $pd['color'],
                                                     $pd['option_reqs'],
                                                     EMPTY_NOTES,$pd['active']);


  echo "<pre><font color='blue'>Product {$pd['name']} has become a test project!</font></pre>";

  $tproject_id=$old_new['product'][$prod_id];
  
  $sql="SELECT * FROM mgtcomponent WHERE prodid={$prod_id}";
  $comp=$source_db->fetchRowsIntoMap($sql,'id');

  // for change_order_bulk($hash_node_id, $hash_node_order) 
  // $hash_node_id=array(10=>10, 23=>23, 30=>30);
  // $hash_node_order=array(10=>3, 23=>1, 30=>2);
  $hash_node_id=array();
  $hash_node_order=array();


  if( count($comp) > 0 )
  {  
    foreach($comp as $coid => $cod)
    {
      $details='';
      foreach($mgtcom_keys as $key => $val)
      {
        $details .= $val . ": <br>" . $cod[$key] . "<p>";
      }
      
      $ret=$ts_mgr->create($tproject_id,$cod['name'],$details);
      if( $ret['status_ok'] )
      {
        echo "<pre>Component {$cod['name']} Migrated<br></pre>";  
        $mgtcomp_id=$ret['id'];
        $old_new['mgtcomp'][$coid]=$mgtcomp_id;
      }
      
      // ----------------------------------------------------------------------------------
      $sql="SELECT * FROM mgtcategory WHERE compid={$coid}";
      $cat=$source_db->fetchRowsIntoMap($sql,'id');
      
      if( count($cat) > 0 )
      {  
        foreach($cat as $caid => $cad)
        {
          // ----------------------------------------------------------------------------------
          $details='';
          foreach($mgtcat_keys as $key => $val)
          {
            $details .= $val . ": <br>" . $cad[$key] . "<p>";
          }
          // ----------------------------------------------------------------------------------
      
          $ret=$ts_mgr->create($mgtcomp_id,$cad['name'],$details);
          if( $ret['status_ok'] )
          {
            echo "<pre>    Category {$cad['name']} Migrated<br></pre>";  
            $mgtcat_id=$ret['id'];
            $old_new['mgtcat'][$caid]=$mgtcat_id;

            if( $cad['CATorder'] != 0 )
            {
               $hash_node_id[$mgtcat_id]=$mgtcat_id;
               $hash_node_order[$mgtcat_id]=$cad['CATorder'];
            }
          }  
          // ----------------------------------------------------------------------------------
        }
      }   
    }  
    // 20060725 - franciscom
    $tree_mgr->change_order_bulk($hash_node_id, $hash_node_order) ;
  }  
}

} // end function



// 20060725 - franciscom
function update_tc_specs_parents(&$source_db,&$target_db,&$tc_specs,&$old_new)
{
  $tree_mgr=New tree($target_db);
  foreach($tc_specs as $item_id => $idata)
  {
    // change_parent($node_id, $parent_id)
    $parent_id=$old_new['mgtcat'][$idata['catid']];

    if(intval($parent_id) == 0 )
    {     
      echo '<pre> <font style="color:white;background-color:red;">' . 
           "Error TCID:{$item_id} {$idata['title']} has no parent</font></pre>";
    }
    else
    {
      $tree_mgr->change_parent($item_id, $parent_id);
    }
  }
} // end function


// 20060725 - franciscom
//
//
function migrate_test_plans(&$source_db,&$target_db,&$tplans,&$old_new)
{
  $tplan_mgr=New testplan($target_db);
  foreach($tplans as $item_id => $idata)
  {
    $old_prodid=intval($idata['prodid']);
    $tproj_id=0;
    if( $old_prodid > 0 )
    {
      $tproj_id=$old_new['product'][$old_prodid];
    }
    $old_new['tplan'][$item_id]=$tplan_mgr->create($idata['name'],$idata['notes'],$tproj_id);
    //echo "OLD TPlan ID {$item_id} {$idata['name']} -> {$old_new['tplan'][$item_id]} <br>";
  }
} // end function


// 20060725 - franciscom
//
//
function migrate_builds(&$source_db,&$target_db,&$builds,&$old_new)
{
  $pivot_name="";
  echo "   <b>Total number of builds: " . count($builds) . "</b><br>" ;  
  foreach($builds as $item_id => $idata)
  {
    if( strcmp($idata['TPLAN_NAME'],$pivot_name) != 0)
    {
      echo "   <br>Migrating Builds for TestPlan: " . $idata['TPLAN_NAME'] . "<br>" ;  
      $pivot_name=$idata['TPLAN_NAME'];
    }
    echo "             Build: " . $idata['name'] . "<br>" ;  

    $tplan_id=$old_new['tplan'][intval($idata['projid'])];
    create_build($target_db,$item_id,$idata['name'],$tplan_id,$idata['note']);
    $old_new['build'][$item_id]=$item_id;
  }
} // end function


// 20060725 - franciscom
//
//
function create_build(&$db,$build_id,$buildName,$testplanID,$notes = '')
{
	$sql = " INSERT INTO builds (testplan_id,name,notes,id) " .
	       " VALUES (". $testplanID . ",'" . $db->prepare_string($buildName) . "','" . 
	       $db->prepare_string($notes) . "',{$build_id})";
	       
	$result = $db->exec_query($sql);
} // end function


// 20060726 - franciscom
function migrate_results(&$source_db,&$target_db,&$execs,&$builds,&$users,&$tc_tcversion,&$old_new)
{
	$map_tc_status = config_get('tc_status');
	$db_now = $target_db->db_now();
	$admin_id=1;
 
  foreach($execs as $item_id => $idata)
  {
    $old_tplan_id=$builds[$idata['build_id']]['projid'];
    $tplan_id=$old_new['tplan'][intval($old_tplan_id)];
    $build_id=$old_new['build'][$idata['build_id']];
    $has_been_executed = ($idata['status'] != $map_tc_status['not_run'] ? TRUE : FALSE);
	  $tcversion_id=$tc_tcversion[$idata['MGTTCID']];
   
	  if($has_been_executed)
	  { 
	    $user_id=intval(isset($users[$idata['runby']]) ? $users[$idata['runby']]['id'] : $admin_id);  
			$my_notes = $target_db->prepare_string(trim($idata['notes']));		
			$sql = "INSERT INTO executions ".
				     "(build_id,tester_id,status,testplan_id,tcversion_id,execution_ts,notes)".
				     " VALUES ( {$build_id}, {$user_id}, '" . $idata['status'] . "',".
				     "{$tplan_id}, {$tcversion_id},'" . $idata['daterun'] . "','{$my_notes}')";
			$target_db->exec_query($sql);  	     
	  }
 }
} // end function

// 20060727 - franciscom
function migrate_tplan_contents(&$source_db,&$target_db,&$tplan_elems,&$tc_tcversion,&$old_new)
{
  $qta_loops=count($tplan_elems);
  echo "Total number of TC in ALL TPlan: {$qta_loops}<br>";
   
  foreach($tplan_elems as $idata)
  {
    $tplan_id=$old_new['tplan'][intval($idata['projid'])];
	  $tcversion_id=$tc_tcversion[$idata['MGTTCID']];
    $sql = "INSERT INTO testplan_tcversions " .
           "(testplan_id,tcversion_id) " .
           "VALUES({$tplan_id},{$tcversion_id})";
		$target_db->exec_query($sql);  	     
  }
} // end function


// 20060730 - franciscom
function migrate_tesplan_assignments(&$source_db,&$target_db,&$user_tplans,&$old_new)
{
  define('NO_RIGHTS',3);

  $sql="SELECT * FROM user";
  $users=$source_db->fetchRowsIntoMap($sql,'id');
  
  $sql="SELECT * FROM project ORDER BY ID";
  $tplans=$source_db->fetchRowsIntoMap($sql,'id');

  foreach($tplans as $item_id => $idata)
  {
    $tplan_id=$old_new['tplan'][intval($item_id)];
    foreach($users as $user_id => $udata)
    {
      // user id still exists ?
      if( isset($users[$user_id]) )
      {
        if( isset($user_tplans[$user_id]) )
        {
           $user_role=$users[$user_id]['rightsid'];
        }
        else
        {
           $user_role=NO_RIGHTS;
        }
        $sql="INSERT INTO user_testplan_roles " .
             "(user_id,testplan_id,role_id) " .
             "VALUES({$user_id},{$tplan_id},{$user_role})";
        $target_db->exec_query($sql);  	     
      }  
    }
  }
  
} // end function


function migrate_prules(&$source_db,&$target_db,&$prules,&$old_new)
{
  foreach($prules as $item_id => $idata)
  {
    $tplan_id=$old_new['tplan'][intval($item_id)];
    $sql="INSERT INTO priorities " .
         "(testplan_id,risk_importance,priority) " .
         "VALUES({$tplan_id},'" . $idata['riskImp'] . "','" .
         $idata['priority'] . "')";

    $target_db->exec_query($sql);  	     
  }
} // end function


function migrate_milestones(&$source_db,&$target_db,&$ms,&$old_new)
{
  foreach($ms as $item_id => $idata)
  {
    $tplan_id=$old_new['tplan'][intval($item_id)];
    $sql="INSERT INTO milestones " .
         "(testplan_id,date,A,B,C,name) " .
         "VALUES({$tplan_id},'" . $idata['date'] . "'," .
         intval($idata['A']) . "," . 
         intval($idata['B']) . "," .
         intval($idata['C']) . "," .
         "'" . $target_db->prepare_string($idata['name']) . "')";
    $target_db->exec_query($sql);  	     
  }
} // end function

//
function migrate_bugs($source_db,$target_db,$bugs,$builds,$map_tc_tcversion,$old_new)
{
  foreach($bugs as $bdata)
  {
     $tcversion_id=$map_tc_tcversion[$bdata['MGTTCID']];
     $sql="SELECT id FROM executions " .
          "WHERE tcversion_id={$tcversion_id} " .
          "AND   build_id={$bdata['build_id']}";
     $exec_id=$target_db->fetchFirstRowSingleColumn($sql,'id');
  
     if( intval($exec_id) > 0 )
     {
        $sql="INSERT INTO execution_bugs " .
             "(execution_id,bug_id) " .
             "VALUES({$exec_id}, {$bdata['bug']}) ";
        $target_db->exec_query($sql);
        //echo "<br>debug - <b><i>" . __FUNCTION__ . "</i></b><br><b>" . $sql . "</b><br>";
     }
  }
} // end function


// 20060803 - franciscom
function migrate_requirements(&$source_db,&$target_db,&$req,&$old_new)
{
  foreach($req as $req_id => $rdata)
  {
    $sql="INSERT INTO requirements " .
         "(id,srs_id,req_doc_id,title,scope,status,type,author_id,creation_ts";
     
    $values=" VALUES({$rdata['id']},{$rdata['id_srs']}," . "'" . $rdata['req_doc_id'] . "'," .
            "'" . $target_db->prepare_string($rdata['title']) . "',"  .
            "'" . $target_db->prepare_string($rdata['scope']) . "',"  .
            "'" . $rdata['status'] . "','" . $rdata['type'] . "',"  .
                  $rdata['id_author'] . ",'" . $rdata['create_date'] . "'";

    if( strlen(trim($rdata['id_modifier'])) )
    {
       $sql .= ",modifier_id,modification_ts";
       $values .= ",{$rdata['id_modifier']}," . "'" . $rdata['modified_date'] ."'";
    }
    $sql .=") " . $values . ")";
    $exec_id=$target_db->exec_query($sql);
 
  }
  
} // end function


// 20060803 - franciscom
function migrate_req_specs(&$source_db,&$target_db,&$rspec,&$old_new)
{
  foreach($rspec as $req_id => $rdata)
  {

    $sql="INSERT INTO req_specs " .
         "(id,testproject_id,title,scope,total_req,type,author_id,creation_ts";

    // ----------------------------------------------------------------------------
    // 20061203 - franciscom     
    $tproject_id=-1;
    if(isset($old_new['product'][$rdata['id_product']]) ) 
    {
      $tproject_id=$old_new['product'][$rdata['id_product']];
    }
    // ----------------------------------------------------------------------------
    
    if( intval($tproject_id) > 0 )
    {
      $values=" VALUES({$rdata['id']},{$tproject_id}," . 
              "'" . $target_db->prepare_string($rdata['title']) . "',"  .
              "'" . $target_db->prepare_string($rdata['scope']) . "',"  .
                    intval($rdata['total_req']) . ",'" . $rdata['type'] . "',"  .
                    $rdata['id_author'] . ",'" . $rdata['create_date'] . "'";
  
      if( strlen(trim($rdata['id_modifier'])) )
      {
         $sql .= ",modifier_id,modification_ts";
         $values .= ",{$rdata['id_modifier']}," . "'" . $rdata['modified_date'] ."'";
      }
      $sql .=") " . $values . ")";
      $exec_id=$target_db->exec_query($sql);
    }
    else
    {
      echo "<font color='red'>Problems migrating REQ_SPEC ID: " .
           "{$rdata['id']} - Product ID:{$rdata['id_product']}</font><br>";
    }
    
    
  }
} // end function


// 20060803 - franciscom
function migrate_req_coverage(&$source_db,&$target_db,&$req_cov,&$old_new)
{
  foreach($req_cov as $req_id => $rdata)
  {
    $sql="INSERT INTO req_coverage " .
         "(req_id,testcase_id) " .
         " VALUES({$rdata['id_req']},{$rdata['id_tc']})";
    $exec_id=$target_db->exec_query($sql);
  }
} // end function



// 20060908 - franciscom
// I will assign ownership testcase by testcase inside of every testplan
function migrate_ownership(&$source_db,&$target_db,&$rs,&$map_tc_tcversion,&$old_new)
{
  $db_now = $target_db->db_now();
  $assignment_mgr=New assignment_mgr($target_db);
	$assignment_types=$assignment_mgr->get_available_types(); 
	$assignment_status=$assignment_mgr->get_available_status();
  
  
  $sql="SELECT * FROM user";
  $users=$source_db->fetchRowsIntoMap($sql,'login');

  foreach($rs as $rid => $rdata)
  {
     
     $tcversion_id=$map_tc_tcversion[$rdata['mgttcid']];
     $tplan_id=intval($rdata['projid']);
     $sql=" SELECT id FROM testplan_tcversions " .
          " WHERE testplan_id=" . $old_new['tplan'][$tplan_id] . 
          " AND tcversion_id=" . $tcversion_id;
     $feature_row=$target_db->get_recordset($sql);
     $feature_id=$feature_row[0]['id'];   
  
     $owner_login=$rdata['owner'];
     $user_id = isset($users[$owner_login]) ? $users[$owner_login]['id'] : 0;
     if( $user_id > 0 && $feature_id)
     {
      $sql="INSERT INTO user_assignments " .
           "(feature_id,user_id,creation_ts,type,status) " .
           " VALUES({$feature_id},{$user_id},{$db_now}," .
           $assignment_types['testcase_execution']['id'] . "," .
           $assignment_status['open']['id'] . ")";
      $exec_id=$target_db->exec_query($sql);
     }
  }
}
?>

