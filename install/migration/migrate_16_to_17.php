<?php
/* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: migrate_16_to_17.php,v 1.1 2006/07/26 08:27:49 franciscom Exp $ 

*/
echo <<<THIS_TEXT
<pre>
Migration Process

Migration is only supported from version 1.6.2.
a new database with the 1.7 will be created   (target database). 
no changes will be made to the 1.6.2 database (source database).
Keyword ID   will be preserved.
Test case ID will be preserved.

Test cases added to a test plan, but without corresponding 
Test case specification (i.e. the spec has been deleted) WILL BE LOST.
</pre>
THIS_TEXT;



require_once( dirname(__FILE__). '/../../lib/functions/database.class.php' );

require_once(dirname(__FILE__) . "/../../lib/functions/common.php");

// Get list of products
//
// Loop over the list creating 
// 1. the nodes in the new nodes_hierarchy table and test projects table 
// 2. cross reference structure  1.6_product_id <-> 1.7_test_project_id
// 
//
// -----------------------------------------------------------------------------------
// Connect to 1.6 db
$db_cfg['source']=array('db_type' => 'mysql',
                        'db_server' => 'localhost',
                        'db_name'   => 'tl_16_agos',
                        'db_admin_name' => 'root',
                        'db_admin_pass' => 'mysqlroot');
                        
$db_cfg['target']=array('db_type' => 'mysql',
                        'db_server' => 'it-bra-l0042',
                        'db_name'   => 'tl_migra',
                        'db_admin_name' => 'root',
                        'db_admin_pass' => 'mysqlroot');

echo '<span>Connecting to Testlink 1.6 (source) database.</span>';
$source_db = connect_2_db($db_cfg['source']);
                        
echo '<span>Connecting to Testlink 1.7 (target) database.</span>';
$target_db = connect_2_db($db_cfg['target']);




// migrate
// [id] => 1
// [name] => AGOS_ARPA
// [color] => #FFFFCC
// [option_reqs] => 1
// [option_priority] => 1
// [active] => 1
//
$tproject_mgr=New testproject($target_db);
$ts_mgr=New testsuite($target_db);
$tc_mgr=New testcase($target_db);
$tree_mgr=New tree($target_db);


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
$a_sql[]="TRUNCATE TABLE nodes_hierarchy";
$a_sql[]="TRUNCATE TABLE testprojects";
$a_sql[]="TRUNCATE TABLE testsuites";
$a_sql[]="TRUNCATE TABLE tcversions";
$a_sql[]="TRUNCATE TABLE keywords";
$a_sql[]="TRUNCATE TABLE users";
$a_sql[]="TRUNCATE TABLE testplans";
$a_sql[]="TRUNCATE TABLE testcase_keywords";
$a_sql[]="TRUNCATE TABLE testplan_tcversions";


foreach($a_sql as $elem) {$target_db->exec_query($elem);}
//exit();
// -----------------------------------------------------------------------------------


// -----------------------------------------------------------------------------------
// To preserve test case ID, I will create first all test cases.
//
$sql="SELECT * FROM mgttestcase ORDER BY id";
$tc_specs=$source_db->fetchRowsIntoMap($sql,'id');
if(is_null($tc_specs)) 
{
		echo "<span class='notok'>There are no test case to be migrated!</span></b>";
}
else
{
  migrate_tc_specs($source_db,$target_db,$tc_specs);
}
//exit();
// -----------------------------------------------------------------------------------


echo "<pre> Users migration </pre>";
// Get list of 1.6 users
$sql="SELECT * FROM user";

$users=$source_db->fetchRowsIntoMap($sql,'login');
if(!is_null($users)) 
{
  migrate_users($target_db,$users);
}
else
{
  echo "<pre> Ooops! no users to migrate !!!! </pre>";
}  
echo "<pre> ----------------------------------------------------------- </pre>";


echo "<pre> ----------------------------------------------------------- </pre>";
echo "<pre> Products, Component & Category migration </pre>";
//
// Get list of 1.6 Products
$sql="SELECT * FROM mgtproduct";

$products=$source_db->fetchRowsIntoMap($sql,'id');
if(is_null($products)) 
{
		echo "<span class='notok'>Failed!</span></b> - Getting products:" .
	  $source_db->error_msg() ."<br>";
}
migrate_cc_specs($source_db,$target_db,$products,$old_new);
echo "<pre> ----------------------------------------------------------- </pre>";

echo "<pre> Keywords migration </pre>";
migrate_keywords($source_db,$target_db,$products,$old_new);
echo "<pre> ----------------------------------------------------------- </pre>";


echo "<pre> ----------------------------------------------------------- </pre>";
echo "<pre> Test case parent update</pre>";
update_tc_specs_parents($source_db,$target_db,$tc_specs,$old_new);
echo "<pre> ----------------------------------------------------------- </pre>";


echo "<pre> ----------------------------------------------------------- </pre>";
echo "<pre> Test plans</pre>";
$sql="SELECT * FROM project";
$tplans=$source_db->fetchRowsIntoMap($sql,'id');
if(is_null($tplans)) 
{
		echo "<span class='notok'>There are no test plans to be migrated!</span></b>";
}
else
{
  migrate_test_plans($source_db,$target_db,$tplans,$old_new);
}
echo "<pre> ----------------------------------------------------------- </pre>";

echo "<pre> ----------------------------------------------------------- </pre>";
echo "<pre> Builds </pre>";
$sql="SELECT * FROM build";
$builds=$source_db->fetchRowsIntoMap($sql,'id');
if(is_null($tplans)) 
{
		echo "<span class='notok'>There are no builds to be migrated!</span></b>";
}
else
{
  migrate_builds($source_db,$target_db,$builds,$old_new);
}
echo "<pre> ----------------------------------------------------------- </pre>";



// -----------------------------------------------------------------------------------
// To preserve test case ID, I will create first all test cases.
//
/*
$sql="SELECT * " .
     "FROM mgttestcase MGTTC,testcase TC";
     "WHERE MGTTC.ID=TC.MGTTCID ORDER BY id";
$tc_run_w_specs=$source_db->fetchRowsIntoMap($sql,'id');
if(is_null($tc_specs)) 
{
		echo "<span class='notok'>There are no test case to be migrated!</span></b>";
}
else
{
  migrate_tc_runs($source_db,$target_db,$tc_specs);
}
*/
//exit();
// -----------------------------------------------------------------------------------


?>




<?php

//cfg =array('db_type' => 'mysql',
//           'db_server' => 'localhost',
//           'db_admin_name' => 'root',
//           'db_admin_pass' => 'mysqlroot');
//
function connect_2_db($cfg)
{
$db = new database($cfg['db_type']);
define('NO_DSN',FALSE);
@$conn_result = $db->connect(NO_DSN,$cfg['db_server'], 
                                    $cfg['db_admin_name'], $cfg['db_admin_pass'],$cfg['db_name']); 

if( $conn_result['status'] == 0 ) 
{
	echo '<span class="notok">Failed!</span><p />Please check the database login details and try again.';
	echo '<br>Database Error Message: ' . $db->error_msg() . "<br>";
} 
else 
{
	echo "<span class='ok'>OK!</span><p />";
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
function migrate_tc_specs(&$source_db,&$target_db,&$items)
{
  $first_version=1;
  $tc_mgr=New testcase($target_db);
  foreach($items as $item_id => $idata)
  {
     echo "Migrating Test Cases - Part I - TCID:{$item_id} - {$idata['title']}<br>";
     $tc_mgr->create_tcase_only(0,$idata['title'],$idata['TCorder'],$item_id);  
  }
 
  
  // Now create the TC version
  echo "<br>";
  foreach($items as $item_id => $idata)
  {
     echo "Migrating Test Cases - Part II - TCID:{$item_id} - {$idata['title']}<br>";
     $tc_mgr->create_tcversion($item_id,$first_version,
                               $idata['summary'],$idata['steps'],
                               $idata['exresult'],0);
  }

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


  echo "<pre><font color='red'>Product {$pd['name']} has become a test project!</font></pre>";

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
    // 20060725 - francisco.mancardi@gruppotesi.com
    $tree_mgr->change_order_bulk($hash_node_id, $hash_node_order) ;
  }  
}

} // end function



// 20060725 - francisco.mancardi@gruppotesi.com
function update_tc_specs_parents(&$source_db,&$target_db,&$tc_specs,&$old_new)
{
  $tree_mgr=New tree($target_db);
  //echo "<pre>debug 20060725 \$old_new " . __FUNCTION__ . " --- "; print_r($old_new); echo "</pre>";

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


// 20060725 - francisco.mancardi@gruppotesi.com
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
  }
} // end function


// 20060725 - francisco.mancardi@gruppotesi.com
//
//
function migrate_builds(&$source_db,&$target_db,&$builds,&$old_new)
{
  foreach($builds as $item_id => $idata)
  {
    $tplan_id=$old_new['tplan'][intval($idata['projid'])];
    $old_new['build'][$item_id]=create_build($target_db,$idata['name'],$tplan_id,$idata['notes']);
  }
} // end function





// 20060725 - francisco.mancardi@gruppotesi.com
//
//
function create_build(&$db,$buildName,$testplanID,$notes = '')
{
	$sql = " INSERT INTO builds (testplan_id,name,notes) " .
	       " VALUES ('". $testplanID . "','" . $db->prepare_string($buildName) . "','" . 
	       $db->prepare_string($notes) . "')";
	       
	$new_build_id = 0;
	$result = $db->exec_query($sql);
	if ($result)
	{
		$new_build_id = $db->insert_id('builds');
	}
	
	return $new_build_id;
}

?>