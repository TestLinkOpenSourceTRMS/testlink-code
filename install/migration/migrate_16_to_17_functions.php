<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @package 	TestLink
 * @copyright 	2007, TestLink community 
 * @version    	CVS: $Id: migrate_16_to_17_functions.php,v 1.11 2009/08/29 23:57:28 havlat Exp $
 *
 * @internal Revisions:
 * 		20080830 - havlatm - 0002699: Update of 1.7 to 1.8 fails because connect_2_db() is MySQL specific
 *      20071103 - franciscom - BUGID 771 - utf-8 issue - contributed by eagleas
 *
 *      20071008 - asielb - fixed bug 1110, keywords migration
 *      20070829 - jbarchibald - fixed bug 1010, results Migration
 */

/**
 * Tries to connect to a database, displaying ALWAYS an status message.
 */
// args   :
//         cfg=array('db_type' => 'mysql',
//                   'db_server' => 'localhost',
//                   'db_admin_name' => 'root',
//                   'db_admin_pass' => 'mysqlroot',
//                   'log_level' => );
// returns: 
//          if connection OK -> a database object
//          if connection KO -> null
//
// rev :
//      20080219 - franciscom - added log_level
//      20061203 - franciscom - removed warning due to constant redefinition
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
			if (!isset($cfg['db_type']) || strtolower($cfg['db_type']) == 'mysql') 
			{
			    // 20071103 - BUGID 771 eagleas
    			$db->exec_query("SET CHARACTER SET utf8;");
	    		$db->exec_query("SET collation_connection = 'utf8_general_ci';");
			}
			echo "<span class='ok'>OK!</span><p />";
		}
	}

	return ($db);
}

// determine if the version of the db being used is > 5.x
function determine_mysql_version($db_connection_obj)
{
	$query="SELECT VERSION() as ver";
	$source_version = $db_connection_obj->fetchFirstRow($query);
	// searching for MySQL version 5 or higher should have String like one of the following
	// 5.0.41-community-nt || 5.0.22-Debian_0ubuntu6.06.3-log
	return preg_match("/^5.\d+.\d+-/", $source_version["ver"]);		
}

//
function migrate_keywords(&$source_db,&$target_db,&$products,&$prod_keyword_tc,&$old_new)
{
  
  
  foreach($products as $prod_id => $pd)
  {

    // 20070208 - franciscom
    $keyword_tc = null;
    $link_kw_tc_exists=0;
    if( isset($prod_keyword_tc[$prod_id]) )
    {
      $keyword_tc=$prod_keyword_tc[$prod_id];
      $link_kw_tc_exists=1;  
    }
        
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
        $the_kw=trim($value['keyword']);
        if( strlen($the_kw) > 0 )
        {
          $sql="INSERT INTO keywords (id,keyword,testproject_id,notes) " .
               " VALUES({$value['id']}," .
               "'" . $target_db->prepare_string($the_kw) . "',{$tproject_id}," .
               "'" . $target_db->prepare_string(trim($value['notes'])) . "')";
          $target_db->exec_query($sql);     
    
 
          // Keyword - TC - Assignment 
          if( $link_kw_tc_exists && isset($keyword_tc[$the_kw])) 
          {
            foreach($keyword_tc[$the_kw] as $tcid)
            {
              $xsql="SELECT count(*) AS cnt FROM testcase_keywords WHERE keyword_id={$value['id']} AND testcase_id={$tcid}";
              $asgn_exist = $target_db->fetchFirstRowSingleColumn($xsql,'cnt');     
			  if( $asgn_exist )
			  {
	          	echo "<pre>   Skipped doubled keywords assignment {$value['id']} to TC {$tcid}</pre>";
			  } else {
              	$xsql="INSERT INTO testcase_keywords (keyword_id,testcase_id) " .
                    " VALUES({$value['id']},{$tcid}) ";
              	$target_db->exec_query($xsql);
	          	echo "<pre>   Added keywords assignment {$value['id']} to TC {$tcid}</pre>";
			  }     
            }
          }
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

// print messages according to whether FULL_FEEDBACK is set
function print_msg($msg, $print_level='NORMAL')
{
	if($print_level == 'FULL_FEEDBACK')
	{
		//if(FULL_FEEDBACK)
		//{
			echo $msg;			
		//}
	}
	else
	{
		echo $msg;		
	}
	// always flush the output buffer and send it
	flush();
	@ob_flush();
}

// To return a byte value for the memory available
// Used from: http://us.php.net/manual/en/function.ini-get.php
function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val{strlen($val)-1});
    switch($last) {
        // The 'G' modifier is available since PHP 5.1.0
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }

    return $val;
}

// This should increase memory to a sufficient amount for most cases if necessary
// or error out before we are in the middle of converting tcs
function check_memory($tc_count)
{ 
  $memory_available = return_bytes(ini_get("memory_limit"));
  // 67108864 = 64M
  if ($memory_available <= 67108864 && $tc_count > 5000)
  {
  	// should be >= 128M
  	$new_limit = "384M";
  	// attempt to set the memory_limit to $new_limit to avoid problems
  	if (false == ini_set("memory_limit", $new_limit))
  	{
	  	print_msg("</div><h1><center>You have $tc_count testcases and a memory limit of " .
	  			ini_get("memory_limit") . " <br />" .
	  			"Try increasing your memory_limit to " .
	  			"at least 128M in your php.ini</center></h1>");
	  	// this is pretty harsh but it has been shown to not work so its probably better than 
	  	//blowing up in the middle of the conversion process. Remove at your own risk!
	  	exit(1);
  	}
  	else
  	{
  		print_msg("Increased your memory_limit to $new_limit");
  	}
  }
}

// 20070119 - franciscom - found bug due to missing where condition
//
// 20061208 - franciscom
// When the amount of feedback is high (greater than 5000 rows), 
// old plain series of echo are not good
// 20070808 - asielb
// modified to handle a much larger number of test cases
function migrate_tc_specs(&$source_db,&$target_db,&$tmp_table_name,&$users,&$migrator)
{
  $first_version=1;
  $tc_mgr=New testcase($target_db);
  $map_tc_tcversion=array();
  $admin_id=1;
  $items_processed=0;
  // how many records are we going to tackle at a time 
  // with basic benchmarking roughly 500 seemed to be slightly faster
  $step_amt=500;
  print_msg("<b><center>Migrating Test Cases - Part I - " . date("H:i:s") . " -</center></b><br>");
  
  $tc_count = $migrator->get_tmp_table_count();
  
  // Make sure we have enough memory to do what we are about to do
  check_memory($tc_count);
    
  // testcase nodes have to be done separately from tcversions because the version 
  // inserts a new node
  while($items_processed < $tc_count)
  {	 
  	$query = "SELECT * FROM $tmp_table_name ORDER BY id LIMIT $items_processed,$step_amt";  
  	$items = $source_db->fetchRowsIntoMap($query, 'id');    
    
	  foreach($items as $item_id => $idata)
	  {
	     // 20061208 - franciscom - 
	     // added abs()
	     // added htmlentities()
	     print_msg("TCID:{$item_id} - " . htmlentities($idata['title']) ."<br>");
	     
	     $status = $tc_mgr->create_tcase_only(0,$idata['title'],abs($idata['TCorder']),$item_id);	     
	     	       	
	     ++$items_processed;
	     if( ($items_processed % FEEDBACK_STEP)== 0 )
	     {
			print_msg('<br><span class="processed">Part I - Processed: ' . $items_processed . " - " . date("H:i:s") . "</span><br>","FULL_FEEDBACK");
	     }
	      
	  }
	  print_msg("<br><span class='processed'>Going to process another $step_amt items</span>", "FULL_FEEDBACK");
  }  
  print_msg("Finished Part I -" . date("H:i:s"));
  
  //---------------------------------------------------------------------------
  // now loop through again and do the versions... this is highly inefficient 
  // to loop through the dataset another time but it works without big changes 
  //---------------------------------------------------------------------------
  print_msg("<br> <b><center>Migrating Test Cases - Part II - " . date("H:i:s") . " -</center></b><br>");
  $items_processed=0;
  while($items_processed < $tc_count)
  {	
  	$query = "SELECT * FROM $tmp_table_name ORDER BY id LIMIT $items_processed,$step_amt";
  	$items = $source_db->fetchRowsIntoMap($query, 'id');

	  foreach($items as $item_id => $idata)
	  {
		 // Now create the TC version
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
	     // 20070119 - franciscom - very big bug - missing where clause
	     $sql .=" WHERE tcversions.id={$x['id']} ";
	     //echo "<br>update query is $sql";
	     $target_db->exec_query($sql);
	      
	     $map_tc_tcversion[$item_id]= $x['id'];
	     
	     // 20061208 - franciscom
		 print_msg("TCID:{$item_id} - " . htmlentities($idata['title']) . " - TCVERSION_ID:{$x['id']}<br>", "FULL_FEEDBACK");
	    
	     ++$items_processed; 
	     if( ($items_processed % FEEDBACK_STEP)== 0 )
	     {
	       print_msg('<br><span class="processed">Part II - Processed: ' . $items_processed . " - " . date("H:i:s") . "</span><br><br>");	      
	     } 
	  }
	  print_msg("<br><span class='processed'>Going to process another $step_amt items</span>", "FULL_FEEDBACK");
  }	
	    
  print_msg("Test Case Specifications MIGRATION ENDED ::: " . date("H:i:s") . "<br>");
  return($map_tc_tcversion);
} // end function




// 20060725
// migrate components and categories (cc)
//
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
  $old_new['product'][$prod_id]=@$tproject_mgr->create($pd['name'],
                                                       $pd['color'],
                                                       $pd['option_reqs'],
                                                       EMPTY_NOTES,$pd['active']);


  echo "<pre><font color='blue'>Product {$pd['name']} has became a test project!</font></pre>";
  flush();
  
  $tproject_id=$old_new['product'][$prod_id];
  
  $sql="SELECT * FROM mgtcomponent WHERE prodid={$prod_id}";
  $comp=$source_db->fetchRowsIntoMap($sql,'id');

  // for change_order_bulk($hash_node_id, $hash_node_order) 
  // $hash_node_id=array(10=>10, 23=>23, 30=>30);
  // $hash_node_order=array(10=>3, 23=>1, 30=>2);
  // $hash_node_id=array();
  // $hash_node_order=array();
  
  // 20071021 - francisco.mancardi@gruppotesi.com
  //
  // $hash_order_node_order=array(3=>10, 1=>23, 2=>30);
  // means:  node_id: 10 order:3
  //         node_id: 23 order:1
  //         node_id: 30 order:2
  $hash_order_node_id=array();


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
        echo "<pre>Component " . htmlentities($cod['name']) . " Migrated<br></pre>";
        flush();
          
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
            echo "<pre>    Category " . htmlentities($cad['name']) . " Migrated<br></pre>";  
            flush();
            
            $mgtcat_id=$ret['id'];
            $old_new['mgtcat'][$caid]=$mgtcat_id;

            if( $cad['CATorder'] != 0 )
            {
               // 20071021 - franciscom
               // $hash_node_id[$mgtcat_id]=$mgtcat_id;
               // $hash_node_order[$mgtcat_id]=$cad['CATorder'];
               $node_id=$mgtcat_id;
               $node_order=$cad['CATorder'];
               $hash_order_node_id[$node_order]=$node_id;

            }
          }  
          // ----------------------------------------------------------------------------------
        }
      }   
    }  
    // 20060725 - franciscom
    // $tree_mgr->change_order_bulk($hash_node_id, $hash_node_order) ;
    $tree_mgr->change_order_bulk($hash_order_node_id) ;
  }  
}

} // end function


// 20060725 - franciscom
function update_tc_specs_parents(&$source_db,&$target_db,&$tmp_table_name,&$old_new,&$migrator)
{
	$tree_mgr=New tree($target_db);
	$items_processed=0;
	// how many records are we going to tackle at a time 
	$step_amt=500;
	print_msg("<b><center>Migrating Test Cases - Part I - " . date("H:i:s") . " -</center></b><br>");

	$tc_count = $migrator->get_tmp_table_count();  

	// Make sure we have enough memory to do what we are about to do
	check_memory($tc_count);

	while($items_processed < $tc_count)
  	{
		$item_limit = $items_processed+$step_amt;
  		$query = "SELECT * FROM $tmp_table_name ORDER BY id LIMIT $items_processed,$step_amt";  
  		$tc_specs = $source_db->fetchRowsIntoMap($query, 'id');      
    
  		echo "<pre>   Number of items to update: " . $tc_count . "</pre>";
  
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
	    	$items_processed++;
	  	}
	  	echo "<br>have now processed $items_processed items";
  	}
} // end function


// 20060725 - franciscom
//
//
function migrate_test_plans(&$source_db,&$target_db,&$tplans,&$old_new)
{
  
  $tplan_mgr=New testplan($target_db);
  $tplan_qty=count($tplans);
  echo "<pre>   Test plans to migrate: " . $tplan_qty; echo "</pre>";
  
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

    // 20070130 - jbarchibald
    if( intval($idata['active']) == 0)
     {
         $sql = "UPDATE testplans SET active=0 WHERE testplans.id={$old_new['tplan'][$item_id]}";
         $target_db->exec_query($sql);
     }
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



/*
  function: migrate_results

  args :
  
  returns: 

*/
function migrate_results(&$source_db,&$target_db,&$tmp_table_name,&$builds,&$users,&$tc_tcversion,&$old_new,&$migrator)
{
  // $map_tc_status = config_get('tc_status');
  $resultsCfg = config_get('results');
  $map_tc_status = $resultsCfg['status_code'];

  
  $db_now = $target_db->db_now();
  $admin_id=1;
  $items_processed=0;
  // how many records are we going to tackle at a time
  // with basic benchmarking roughly 500 seemed to be slightly faster
  $step_amt=500;

  // 20070829 - jbarchibald - bug#1010
  $results_count = $migrator->get_tmp_table_count("results");

	echo "Quantity of results to migrate: $results_count<br>";
  // Make sure we have enough memory to do what we are about to do
  check_memory($results_count);
  while($items_processed < $results_count)
  {
	echo "<br>items processed is $items_processed<br>";
    $query = "SELECT * FROM $tmp_table_name ORDER BY tcid,build_id LIMIT $items_processed,$step_amt";
    echo "<br>using query $query<br>";
    $execs = $source_db->get_recordset($query);

		foreach($execs as $idata)
		{
                  $old_tplan_id=$builds[$idata['build_id']]['projid'];
                  $tplan_id=intval($old_new['tplan'][intval($old_tplan_id)]);
                  $build_id=$old_new['build'][$idata['build_id']];
                  $has_been_executed = ($idata['status'] != $map_tc_status['not_run'] ? TRUE : FALSE);
		  $tcversion_id=$tc_tcversion[$idata['mgttcid']];

	    if($build_id != '' && $tplan_id > 0 )
	    	{
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
	  	  		else
	  	  		{
	  	    		echo "<pre>Not migrated ";  
	  	    		echo("status=" . $idata['status'] . " TCID/mgttcid=" . $idata['TCID'] . "/" . $idata['mgttcid']); echo "</pre><br>";  
	  	  		}
		  	}
		  	else
		  	{
				echo "<pre>Not migrated ";  
			echo("TCID/mgttcid=" . $idata['TCID'] . "/" . $idata['mgttcid']);
	  	    	echo("BUILDID tx=" . $idata['build_id']); 
	  	    	echo "</pre><br>";  
	   		}
	   		++$items_processed;
		}
	}
} // end function


/*
  function: migrate_tplan_contents
            migrate Test plan contents

  args :
  
  returns: 

*/
function migrate_tplan_contents(&$source_db,&$target_db,&$tmp_table_name,&$tc_tcversion,&$old_new,&$migrator)
{
	$items_processed=0;
	// how many records are we going to tackle at a time 
	// with basic benchmarking roughly 500 seemed to be slightly faster
	$step_amt=500;
	$tc_count = $migrator->get_tmp_table_count("tp");
	print_msg("<b><center>Migrating Test Plans - " . date("H:i:s") . " -</center></b><br />");
  	print_msg("Total number of TC in ALL TPlan: {$tc_count}<br />");
  	
  
  	// Make sure we have enough memory to do what we are about to do
  	check_memory($tc_count);
      
  	while($items_processed < $tc_count)
  	{
		// make sure nothing is in tplan_elems
		$tplan_elems = null;
		
		echo "<br>items processed is $items_processed<br>";
  		$query = "SELECT * FROM $tmp_table_name ORDER BY projid LIMIT $items_processed,$step_amt";  
  		echo "<br>using query $query<br>";
  		$tplan_elems = $source_db->get_recordset($query);
   		
   		//echo "<br>tpaln elems<br>";
   		//print_r($tplan_elems);
   		
   		//echo "<br>tmp_table name is $tmp_table_name";
   		echo "<br>there are " . count($tplan_elems) . " in tpalnelems<br>";
   		
  		foreach($tplan_elems as $idata)
  		{
    		$tplan_id=$old_new['tplan'][intval($idata['projid'])];
	  		$tcversion_id=$tc_tcversion[$idata['mgttcid']];
    		$sql = "INSERT INTO testplan_tcversions " .
           		"(testplan_id,tcversion_id) " .
           		"VALUES({$tplan_id},{$tcversion_id})";
			$target_db->exec_query($sql);
			++$items_processed;
  		}  	  
  	}
  	echo "<br>finished migrating test plan assignments<br>";
} // end function


/*
  function: migrate_tesplan_assignments
            

  args :
  
  returns: 
  
  rev :
        20070317 - franciscom - BUGID 738
*/
function migrate_tesplan_assignments(&$source_db,&$target_db,&$old_new)
{
  define('NO_RIGHTS',3);

  
  $counter=0;
   
   
  $sql="SELECT * FROM user";
  $users=$source_db->fetchRowsIntoMap($sql,'id');
  
  $sql="SELECT * FROM project ORDER BY ID";
  $tplans=$source_db->fetchRowsIntoMap($sql,'id');

  // 20070317 - franciscom - BUGID 738
  $sql="SELECT * from projrights ORDER BY userid";
  $user_tplans=$source_db->fetchMapRowsIntoMap($sql,'userid','projid');
  $do_check = !is_null($user_tplans);
  
  
  foreach($tplans as $item_id => $idata)
  {
    $old_tplan_id=intval($item_id);
    $tplan_id=$old_new['tplan'][$old_tplan_id];
    foreach($users as $user_id => $udata)
    {
      // user id still exists ?
      if( isset($users[$user_id]) )
      {
        if( $do_check && isset($user_tplans[$user_id][$old_tplan_id]) )
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
        $counter++;
      }  
    }
  }
  echo "<pre> Number of user/test plan assignments migrated: " . $counter; echo "</pre>";
  
  
} // end function



/*
  function: migrate_prules
            migrate Priority rules

  args :
  
  returns: 

  rev  :
         20070204 - francisco.mancardi@gruppotesi.com
         
*/
function migrate_prules(&$source_db,&$target_db,&$prules,&$old_new)
{
  $prules_qty=count($prules);
  echo "<pre>Number of rules: " . $prules_qty; echo "</pre>";
  foreach($prules as $item_id => $idata)
  {
    $tplan_id=$old_new['tplan'][intval($item_id)];
    
    $risk=substr($idata['riskImp'],0,1);
    $importance=substr($idata['riskImp'],1,1);
    $priority=$idata['priority'];
    
    $sql="INSERT INTO priorities " .
         "(testplan_id,risk,importance,priority) " .
         "VALUES({$tplan_id},'{$risk}','{$importance}','{$priority}')";

    $target_db->exec_query($sql);
  }
} // end function


/*
  function: 

  args :
  
  returns: 

*/
function migrate_milestones(&$source_db,&$target_db,&$ms,&$old_new)
{
  $ms_qty=count($ms);
  echo "<pre>Number of milestones: " . $ms_qty; echo "</pre>";
  foreach($ms as $item_id => $idata)
  {
    $tplan_id=$old_new['tplan'][intval($item_id)];
    $sql="INSERT INTO milestones " .
         "(testplan_id,target_date,A,B,C,name) " .
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
  
  $bug_qty=count($bugs);
  if( $bug_qty > 0 )
  {  
    echo "<pre>   Number of bugs: " . $bug_qty; echo "</pre>";
    foreach($bugs as $bdata)
    {
       $tcversion_id=$map_tc_tcversion[$bdata['mgttcid']];
       $sql="SELECT id FROM executions " .
            "WHERE tcversion_id={$tcversion_id} " .
            "AND   build_id={$bdata['build_id']}";
       $exec_id=$target_db->fetchFirstRowSingleColumn($sql,'id');
    
       if( intval($exec_id) > 0 )
       {
          $sql="INSERT INTO execution_bugs " .
               "(execution_id,bug_id) " .
               "VALUES({$exec_id}, '{$bdata['bug']}') ";
          $target_db->exec_query($sql);
       }
    } //foreach
    
  }
  else
  {
      echo "<pre>   Nothing to do </pre>";
  }
  
} // end function


// 20060803 - franciscom
function migrate_requirements(&$source_db,&$target_db,&$req,&$old_new)
{
  
  $req_qty=count($req);
  echo "<pre>Number of requirements: " . $req_qty; echo "</pre>";
  
  foreach($req as $req_id => $rdata)
  {
    $sql="INSERT INTO requirements " .
         "(id,srs_id,req_doc_id,title,scope,status,type,author_id,creation_ts";
     
    // 20070103 - franciscom
    // some sanity checks
    $req_doc_id=trim($rdata['req_doc_id']);
    
    if(strlen($req_doc_id)==0)
    {
       $req_doc_id="NO_DOC_ID-" . $rdata['id']; 
    }
    
    $create_date = "'" . $rdata['create_date'] . "'";;
    $values=" VALUES({$rdata['id']},{$rdata['id_srs']}," . 
            "'" . $target_db->prepare_string($req_doc_id) . "'," .
            "'" . $target_db->prepare_string($rdata['title']) . "',"  .
            "'" . $target_db->prepare_string($rdata['scope']) . "',"  .
            "'" . $rdata['status'] . "','" . $rdata['type'] . "',"  .
                  intval($rdata['id_author']) . "," . $create_date ;

    if( strlen(trim($rdata['id_modifier'])) )
    {
       $sql .= ",modifier_id,modification_ts";
       $values .= "," . intval($rdata['id_modifier']) . "," . "'" . $rdata['modified_date'] ."'";
    }
    $sql .=") " . $values . ")";
    $exec_id=$target_db->exec_query($sql);
  }
  
} // end function


/*
  function: 

  args :
  
  returns: 

*/
function migrate_req_specs(&$source_db,&$target_db,&$rspec,&$old_new)
{
  $counter=0;
  $rspec_qty=count($rspec);
  echo "<pre>Number of Requirements Specifications (SRS): " . $rspec_qty; echo "</pre>";
     
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
      $counter++;
    }
    else
    {
      echo "<font color='red'>Problems migrating REQ_SPEC ID: " .
           "{$rdata['id']} - Product ID:{$rdata['id_product']}</font><br>";
    }
  }
  
} // end function


/*
  function: 
  args :
  returns: N/A

  rev :
        20060803 - franciscom
		20071213 - havlatm - Bug 0001112: Requirements coverage migration from 1.6.3 to 1.7.0 final
*/
function migrate_req_coverage(&$source_db,&$target_db,&$req_cov,&$old_new)
{
  $req_cov_qty=count($req_cov);
  echo "<pre>Number of relationships: " . $req_cov_qty; echo "</pre>";
  $i=0;

  foreach($req_cov as $req_id => $rdata)  
  {
	$reqid=$req_cov[$i]['id_req'];
	$tcid=$req_cov[$i]['id_tc'];

	$ssql="SELECT COUNT(*) as count FROM req_coverage where req_id= {$reqid} and testcase_id = {$tcid}";
	$tcreq=$target_db->get_recordset($ssql);
	 
	if ( $tcreq[0]['count'] == 0 )
	 {

		$sql="INSERT INTO req_coverage (req_id,testcase_id) VALUES({$reqid},{$tcid})";
		$exec_id=$target_db->exec_query($sql);

		echo "<pre><b><u>Requirement id:{$reqid} for testcase id: {$tcid}  MIGRATED</u></b></pre>";	
	 
	 }else
	 {
		echo "<pre>Requirement id :{$reqid} for testcase id: {$tcid} already existing</pre>";
	 }
  
  $i++;

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

  $qty_item=count($rs);
  echo "<pre>   Number of ownership assignments to update: " . $qty_item; echo "</pre>";
  
  
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

function extract_kw_tc_links($source_db,$target_db,$tmp_table_name,&$migrator)
{	
	$tc_count = $migrator->get_tmp_table_count();
  	$map_prod_kw_tc=null;
  	$step_amt=500;
  	$items_processed=0;
  	
	// Make sure we have enough memory to do what we are about to do
	check_memory($tc_count);
    
	// testcase nodes have to be done separately from tcversions because the version 
	// inserts a new node
	while($items_processed < $tc_count)
  	{		  	
  		$query = "SELECT * FROM $tmp_table_name ORDER BY id LIMIT $items_processed,$step_amt";  
  		$items = $source_db->fetchRowsIntoMap($query, 'id');    
      
	  	foreach($items as $tcid => $value)
	  	{
    		$the_kw=trim($value['keywords']);
    		if(strlen($the_kw) > 0)
    		{
      			$akeywords=explode(',',$the_kw);
      			foreach($akeywords as $vkw)
      			{
        			$the_vkm=trim($vkw);
        			if(strlen($the_vkm) > 0)
        			{
						// 20070208 - francisco.mancardi@gruppotesi.com
						$map_prod_kw_tc[$value['prodid']][$the_vkm][]=$tcid;  
						//$map_kw_tc[$the_vkm][$tcid]=$value['prodid'];  
        			}  
      			}
    		}
    		++$items_processed;  
  		}  		
	}
	return $map_prod_kw_tc;
}

?>