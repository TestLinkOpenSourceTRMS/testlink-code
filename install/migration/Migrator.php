<?php
/*
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * $Id: Migrator.php,v 1.5 2008/01/25 14:49:53 franciscom Exp $
 *
 * BUGID 1328 - LEFT without AS
 */

require_once("migrate_16_to_17_functions.php");

class Migrator
{
	// tmp table for test cases
	private $tmp_table_count = 0;
	private $tmp_table_name = "tc_tmp_table";
	// tmp table for test plans
	private $tp_tmp_table_name = "tp_tmp_table";
	private $tp_tmp_table_count = 0;
	// tmp table for results
	private $results_tmp_table_name = "results_tmp_table";
	private $results_tmp_table_count = 0;

	private $source_db;
	private $target_db;
	private $users = null;
	private $old_new = array();
	private $msg_click_to_show=" [Click to show details] ";
	private $products;
	private $map_tc_tcversion;
	private $builds;

	function __construct($source_db, $target_db)
	{
		$this->source_db = $source_db;
		$this->target_db = $target_db;
		$this->get_users();
		$this->get_old_new();
	}
	
	// Migrate everything in the correct order
	public function migrate_all()
	{
		$this->select_testcases();
		$this->select_testplans();
		$this->select_results();
		$this->testcases_migration();
		$this->users_migration();
		$this->products_components_categories_migration();
		$this->keywords_migration();
		$this->builds_migration();
		$this->testplan_migration();
		$this->results_migration();
		$this->bugs_migration();
		$this->users_test_plan_assignments_migration();
		$this->milestones_migration();
		$this->risk_migration();
		$this->requirements_specification_migration();
		$this->requirements_migration();
		$this->requirements_coverage_migration();
	}

	// get the value of the users var from the db
	function get_users()
	{
		// only do a query if the variable hasn't already been set
		if ($this->users == null)
		{
			// Get list of 1.6 users
			$sql="SELECT * FROM user";
			$this->users=$this->source_db->fetchRowsIntoMap($sql,'login');			
		}
		return $this->users;
	}

	function get_old_new()
	{
		if (count($this->old_new) == 0)
		{
			$this->old_new['product']=array();
			$this->old_new['tplan']=array();
			$this->old_new['mgtcomp']=array();
			$this->old_new['mgtcat']=array();
			$this->old_new['mgttc']=array();
			$this->old_new['build']=array();
			$this->old_new['bug']=array();
			$this->old_new['result']=array();
		}
		return $this->old_new;
	}

	// determine the number of test cases we are dealing with in the tmp table
	function get_tmp_table_count($table_name=null)
	{	
		if(null==$table_name)
		{
			echo "<br>getting normal tmp_table count<br>";
			if ($this->tmp_table_count == 0)
			{
				$sql = "SELECT COUNT(*) as count FROM " . $this->tmp_table_name;
				$this->tmp_table_count = $this->source_db->fetchFirstRowSingleColumn($sql, 'count');
			}
			return $this->tmp_table_count;
		}
		else if("tp"==$table_name)
		{
			echo "<br>getting count for tp_tmp_table<br>";
			if ($this->tp_tmp_table_count == 0)
			{
				$sql = "SELECT COUNT(*) as count FROM " . $this->tp_tmp_table_name;
				$this->tp_tmp_table_count = $this->source_db->fetchFirstRowSingleColumn($sql, 'count');
			}
			return $this->tp_tmp_table_count;
		}
		else if("results"==$table_name)
		{
			echo "<br>getting count for results_tmp_table<br>";
			if ($this->results_tmp_table_count == 0)
			{
				$sql = "SELECT COUNT(*) as count FROM " . $this->results_tmp_table_name;
				$this->results_tmp_table_count = $this->source_db->fetchFirstRowSingleColumn($sql, 'count');
			}
			return $this->results_tmp_table_count;
		}
	}

	// TODO: create a separate function to print the section header that is copied and 
	// used in most of the functions
	function print_section_header()
	{
	}

	function select_testcases()
	{
		// -----------------------------------------------------------------------------------
		// To preserve test case ID, I will create first all test cases.
		// Using all these joins we will considered only well formed tc =>
		// no dangling records.
		//
		// 20070103 - franciscom - added prodid column in results record set.
		// 
		// 20070807 - asielb - store results in temporary table to avoid requiring 
		// 		excessive amounts of memory for php
		// 20071212 - asielb = select only first 100 chars of title fixing bug 1125
		$tmp_table_name = "tmp_good_testcases";
		$sql="CREATE TEMPORARY TABLE " . $this->tmp_table_name . " " .
			 "SELECT mtc.id, " .
			 "LEFT(mtc.title,100) AS title," .
			 "mtc.steps," .
			 "mtc.exresult," .
			 "mtc.keywords," .
			 "mtc.catid," .
			 "mtc.version," .
			 "mtc.summary," .
			 "mtc.author," .
			 "mtc.create_date," .
			 "mtc.reviewer," .
			 "mtc.modified_date," .
			 "mtc.TCorder," .
			 "mc.prodid " .
		     " FROM mgtproduct mp, mgtcomponent mc, mgtcategory mk, mgttestcase mtc " .
		     " WHERE mc.prodid=mp.id " .
		     " AND   mk.compid=mc.id " .
		     " AND   mtc.catid=mk.id " .
		     " ORDER BY mtc.id";
		
		echo "<br />creating temporary table for good testcases<br />";
		//echo "<br />using sql: $sql";
		$this->source_db->exec_query($sql);
		
		return $this->get_tmp_table_count();
	}

	function select_testplans()
	{
		// use a temporary table to avoid requiring excessive amounts of memory for php
		$sql="CREATE TEMPORARY TABLE " . $this->tp_tmp_table_name . " SELECT tplan.name " .
			 "AS tplan_name,tplan.id AS projid,k.compid,tc.mgttcid AS mgttcid " .
		     "FROM component c,category k,testcase tc," .
		     "     mgtcomponent mc, mgtcategory mk,mgttestcase mtc,project tplan " .
		     "where c.id=k.compid " .
		     "AND   k.id=tc.catid " .
		     "AND k.mgtcatid = mk.id " .
		     "AND c.mgtcompid = mc.id " .
		     "AND tc.mgttcid=mtc.id " .
		     "AND c.projid = tplan.id " .
		     "ORDER BY projid ";
		   
		echo "<br />creating temporary table for test plans<br />";
		//echo "testplan sql is $sql";
		
		$this->source_db->exec_query($sql);
		
		return $this->get_tmp_table_count("tp");
	}

	function select_results()
	{
		// 20070120 - franciscom
		// added join with build, to filter out results records that belong to deleted builds
		//
		// 20070113 - franciscom
		// added filter on status, because executions with NOT RUN will not be migrated
		// 
		$sql="CREATE TEMPORARY TABLE " . $this->results_tmp_table_name .
			 " SELECT MGT.id as mgttcid, R.tcid, R.build_id,R.daterun," .
		     " R.runby,R.notes,R.status " .
		     " FROM mgttestcase MGT,testcase TC,results R, build B " .
		     " WHERE TC.mgttcid=MGT.id " .
		     " AND   TC.id=R.tcid  AND R.status <> 'n'" .
		     " AND   B.id=R.build_id " .
		     " ORDER BY tcid,build_id";
		
		echo "<br />creating temporary table for results";    
		$this->source_db->exec_query($sql);
		
		return $this->get_tmp_table_count("results");
	}
	
	function users_migration()
	{
		$msg='Users: ';
		$hhmmss=date("H:i:s");
		
		if(!is_null($this->get_users())) 
		{
			$users_qty=count($this->get_users());  
			$msg .= " (Found " . $users_qty . " users to migrate) ";
		}
		else
		{
			$msg .= " Ooops! no users to migrate !!!! ";
		}
		
		echo "<a onclick=\"return DetailController.toggle('details-users')\" href=\"users/\">
		<img src='../img/icon-foldout.gif' align='top' title='show/hide'>{$msg} {$this->msg_click_to_show} {$hhmmss}</a>";
		echo '<div class="detail-container" id="details-users" style="display: none;">';
		if(!is_null($this->get_users())) 
		{
			migrate_users($this->target_db,$this->get_users());
		}
		echo "</div><p>";
	}

	function testcases_migration()
	{
		$tcspecs_msg="Test Case Specifications: (Found " . $this->get_tmp_table_count() . 
				" test cases to migrate)";

		$msg=$tcspecs_msg;
		$hhmmss=date("H:i:s");
		echo "<a onclick=\"return DetailController.toggle('details-tcspecs')\" href=\"tcspecs/\">
		<img src='../img/icon-foldout.gif' align='top' title='show/hide'>{$msg} {$this->msg_click_to_show} {$hhmmss}</a>";
		echo '<div class="detail-container" id="details-tcspecs" style="display: none;">';
		if(!$this->get_tmp_table_count() > 0) 
		{
			echo "<span class='notok'>There are no test cases to be migrated!</span></b>";
		}
		else
		{
		  $this->map_tc_tcversion=migrate_tc_specs($this->source_db,$this->target_db,$this->tmp_table_name,$this->users,$this);
		}
		echo "</div><p>";
	}

	function products_components_categories_migration()
	{
		$hhmmss=date("H:i:s");
		echo "<a onclick=\"return DetailController.toggle('details-pcc')\" href=\"pcc/\">
		<img src='../img/icon-foldout.gif' align='top' title='show/hide'>
		Products, Components & Categories migration: {$this->msg_click_to_show} {$hhmmss}</a>";
		echo '<div class="detail-container" id="details-pcc" style="display: none;">';

		// Get list of 1.6 Products
		$sql="SELECT * FROM mgtproduct";
		
		$this->products=$this->source_db->fetchRowsIntoMap($sql,'id');
		if(is_null($this->products)) 
		{
			echo "<span class='notok'>Failed!</span></b> - Getting products:" .
				$source_db->error_msg() ."<br>";
		}
		migrate_cc_specs($this->source_db,$this->target_db,$this->products,$this->old_new);
		echo "</div><p>";
	}

	function keywords_migration()
	{
		$hhmmss=date("H:i:s");
		echo "<a onclick=\"return DetailController.toggle('details-kw')\" href=\"kw/\">
		<img src='../img/icon-foldout.gif' align='top' title='show/hide'>Keywords migration: {$this->msg_click_to_show} {$hhmmss}</a>";
		echo '<div class="detail-container" id="details-kw" style="display: none;">';
		
		$prod_keyword_tc=extract_kw_tc_links($this->source_db,$this->target_db,$this->tmp_table_name,$this);
		migrate_keywords($this->source_db,$this->target_db,$this->products,$prod_keyword_tc,$this->old_new);
		echo "</div><p>";
		
		$hhmmss=date("H:i:s");
		echo "<a onclick=\"return DetailController.toggle('details-tcpu')\" href=\"tcpu/\">
		<img src='../img/icon-foldout.gif' align='top' title='show/hide'>Test case parent update: {$this->msg_click_to_show} {$hhmmss}</a>";
		echo '<div class="detail-container" id="details-tcpu" style="display: none;">';
		// asielb - modified to use the tmp_table
		update_tc_specs_parents($this->source_db,$this->target_db,$this->tmp_table_name,$this->old_new, $this);
		echo "</div><p>";
		
		$hhmmss=date("H:i:s");
		echo "<a onclick=\"return DetailController.toggle('details-tplan')\" href=\"tplan/\">
		<img src='../img/icon-foldout.gif' align='top' title='show/hide'>Test plans: {$this->msg_click_to_show} {$hhmmss}</a>";
		echo '<div class="detail-container" id="details-tplan" style="display: none;">';
		$sql="SELECT * FROM project ORDER BY ID";
		$tplans=$this->source_db->fetchRowsIntoMap($sql,'id');
		if(is_null($tplans)) 
		{
			echo "<span class='notok'>There are no test plans to be migrated!</span></b>";
		}
		else
		{
		  migrate_test_plans($this->source_db,$this->target_db,$tplans,$this->old_new);
		}
		echo "</div><p>";
	}

	function builds_migration()
	{
		$hhmmss=date("H:i:s");
		echo "<a onclick=\"return DetailController.toggle('details-builds')\" href=\"tplan/\">
		<img src='../img/icon-foldout.gif' align='top' title='show/hide'>Builds: {$this->msg_click_to_show} {$hhmmss}</a>";
		echo '<div class="detail-container" id="details-builds" style="display: none;">';
		
		// find the number of duplicate build names that will get blown away
		$sql = "SELECT COUNT(*) - COUNT(DISTINCT build.name, build.projid) AS duplicate_names FROM build";

		$duplicate_builds = $this->source_db->fetchFirstRowSingleColumn($sql, "duplicate_names");
		
		echo "will be deleting $duplicate_builds duplicate builds"; 

		// select everything but the duplicates that will violate the unique constraint in 1.7
		$sql="SELECT build.*, project.name AS TPLAN_NAME FROM build,project WHERE build.projid=project.id" .
				" GROUP BY build.name, build.projid HAVING COUNT(*)=1 ORDER BY project.id";

		$this->builds=$this->source_db->fetchRowsIntoMap($sql,'id');

		if(is_null($this->builds)) 
		{
			echo "<span class='notok'>There are no builds to be migrated!</span></b>";
		}
		else
		{
		  migrate_builds($this->source_db,$this->target_db,$this->builds,$this->old_new);
		}
		echo "</div><p>";
	}

	function testplan_migration()
	{
		$hhmmss=date("H:i:s");
		echo "<a onclick=\"return DetailController.toggle('details-tctpa')\" href=\"tplan/\">		
		<img src='../img/icon-foldout.gif' align='top' title='show/hide'>Test case -> test plan assignments: {$this->msg_click_to_show} {$hhmmss}</a>";
		echo '<div class="detail-container" id="details-tctpa" style="display: none;">';

		$count=$this->get_tmp_table_count("tp");
		if (0==$count) 
		{
			echo "<br>somehow count was 0 for tp_tmp_table<br>";
			$count=$this->select_testplans();
		}

		// check if the count still is 0
		if (0==$count)
		{
			echo "<span class='notok'>All test plans are empty!</span></b>";
		}
		else
		{
			//migrate_tplan_contents(&$source_db,&$target_db,&$tmp_table_name,&$tc_tcversion,&$old_new,&$migrator)
			migrate_tplan_contents($this->source_db,$this->target_db,$this->tp_tmp_table_name,
					$this->map_tc_tcversion,$this->old_new,$this);
		}
		echo "</div><p>";
	}

	function results_migration()
	{
		$hhmmss=date("H:i:s");
		echo "<a onclick=\"return DetailController.toggle('details-results')\" href=\"tplan/\">
		<img src='../img/icon-foldout.gif' align='top' title='show/hide'>Executions results: {$this->msg_click_to_show} {$hhmmss}</a>";
		echo '<div class="detail-container" id="details-results" style="display: none;">';

		$count=$this->get_tmp_table_count("results");
		if (0==$count)
		{
			$count=$this->select_results();
		}

		// if count is still 0 we have a problem
		if (0==$count)
		{
			echo "<span class='notok'>There are no results to migrate!</span></b>";
		}
		else
		{
			migrate_results($this->source_db,$this->target_db,$this->results_tmp_table_name,
					$this->builds,$this->get_users(),$this->map_tc_tcversion,$this->old_new, $this);			
		}

		echo "<br />Finished migrating execution results";
		echo "</div><p>";
	}

	function bugs_migration()
	{
		$hhmmss=date("H:i:s");
		echo "<a onclick=\"return DetailController.toggle('details-bugs')\" href=\"tplan/\">
		<img src='../img/icon-foldout.gif' align='top' title='show/hide'>Executions bugs: {$this->msg_click_to_show} {$hhmmss}</a>";
		echo '<div class="detail-container" id="details-bugs" style="display: none;">';
		$sql="SELECT bugs.tcid,bugs.build_id,bugs.bug,mgt.id AS mgttcid " .
			"FROM bugs,mgttestcase mgt,testcase t " .
			"WHERE bugs.tcid=t.id " .
			"AND   t.mgttcid=mgt.id";

		$bugs=$this->source_db->get_recordset($sql);
		if(is_null($bugs)) 
		{
			echo "<span class='notok'>There are no bugs to be migrated!</span></b>";
		}
		else
		{
			migrate_bugs($this->source_db,$this->target_db,$bugs,$this->builds,$this->map_tc_tcversion,$this->old_new);
		}
		echo "</div><p>";
	}

	function users_test_plan_assignments_migration()
	{
		//---USERS TEST PLAN ASSIGNMENTS MIGRATION---
		$hhmmss=date("H:i:s");
		echo "<a onclick=\"return DetailController.toggle('details-user_tpa')\" href=\"tplan/\">
		<img src='../img/icon-foldout.gif' align='top' title='show/hide'>Users - Test plan assignments: {$this->msg_click_to_show} {$hhmmss}</a>";
		echo '<div class="detail-container" id="details-user_tpa" style="display: none;">';

		// 20070317 - BUGID 738
		migrate_tesplan_assignments($this->source_db,$this->target_db,$this->old_new);
		echo "</div><p>";

		//---PRIORITY RULES MIGRATION---
		$hhmmss=date("H:i:s");
		echo "<a onclick=\"return DetailController.toggle('details-prior')\" href=\"tplan/\">
		<img src='../img/icon-foldout.gif' align='top' title='show/hide'>Priority Rules: {$this->msg_click_to_show} {$hhmmss}</a>";
		echo '<div class="detail-container" id="details-prior" style="display: none;">';

		$sql="SELECT * from priority";
		$prules=$this->source_db->fetchRowsIntoMap($sql,'projid');
		if(is_null($prules)) 
		{
			echo "<span class='notok'>There are no priority rules to be migrated!</span></b>";
		}
		else
		{
			migrate_prules($this->source_db,$this->target_db,$prules,$this->old_new);
		}
		echo "</div><p>";
	}

	function milestones_migration()
	{
		$hhmmss=date("H:i:s");
		echo "<a onclick=\"return DetailController.toggle('details-Milestones')\" href=\"tplan/\">
		<img src='../img/icon-foldout.gif' align='top' title='show/hide'>Milestones: {$this->msg_click_to_show} {$hhmmss}</a>";
		echo '<div class="detail-container" id="details-Milestones" style="display: none;">';

		$sql="SELECT * from milestone";
		$ms=$this->source_db->fetchRowsIntoMap($sql,'projid');
		if(is_null($ms)) 
		{
			echo "<span class='notok'>There are no results to be migrated!</span></b>";
		}
		else
		{
			migrate_milestones($this->source_db,$this->target_db,$ms,$this->old_new);
		}
		echo "</div><p>";
	}

	function risk_migration()
	{
		echo "<a onclick=\"return DetailController.toggle('details-risk')\" href=\"tplan/\">
		<img src='../img/icon-foldout.gif' align='top' title='show/hide'>Risk TO BE DONE - No data wll be migrated:</a>";
		echo '<div class="detail-container" id="details-risk" style="display: none;">';
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
		$tp4risk_own=$this->source_db->get_recordset($sql);
		
		
		$hhmmss=date("H:i:s");
		echo "<a onclick=\"return DetailController.toggle('details-own')\" href=\"tplan/\">
		<img src='../img/icon-foldout.gif' align='top' title='show/hide'>Ownership (becomes user assignment=test_execution): {$this->msg_click_to_show} {$hhmmss}</a>";
		echo '<div class="detail-container" id="details-own" style="display: none;">';
		
		if(is_null($tp4risk_own)) 
		{
			echo "<span class='notok'>There are no data to be migrated!</span></b>";
		}
		else
		{
			migrate_ownership($this->source_db,$this->target_db,$tp4risk_own,$this->map_tc_tcversion,$this->old_new);
		}
		echo "</div><p>";
	}

	function requirements_specification_migration()
	{
		$hhmmss=date("H:i:s");
		echo "<a onclick=\"return DetailController.toggle('details-req_spec_table')\" href=\"tplan/\">
		<img src='../img/icon-foldout.gif' align='top' title='show/hide'> Requirement Specification: {$this->msg_click_to_show} {$hhmmss}</a>";
		echo '<div class="detail-container" id="details-req_spec_table" style="display: none;">';
		
		$sql="SELECT * from req_spec";
		$rspec=$this->source_db->fetchRowsIntoMap($sql,'id');
		if(is_null($rspec)) 
		{
			echo "<span class='notok'>There are no req specs to be migrated!</span></b>";
		}
		else
		{
		  migrate_req_specs($this->source_db,$this->target_db,$rspec,$this->old_new);
		}
		echo "</div><p>";
	}

	function requirements_migration()
	{
		$hhmmss=date("H:i:s");
		echo "<a onclick=\"return DetailController.toggle('details-reqtable')\" href=\"tplan/\">
		<img src='../img/icon-foldout.gif' align='top' title='show/hide'> Requirements: {$this->msg_click_to_show} {$hhmmss}</a>";
		echo '<div class="detail-container" id="details-reqtable" style="display: none;">';

		// 20070103 - franciscom - added filter on NULL
		// select everything but the duplicates that will violate the unique constraint in 1.7
		$sql="SELECT * from requirements " . 
		     " WHERE req_doc_id <> NULL OR req_doc_id <> '' " .
		     " GROUP BY id_srs, req_doc_id HAVING COUNT(*)=1";
		$req=$this->source_db->fetchRowsIntoMap($sql,'id');
		if(is_null($req)) 
		{
			echo "<span class='notok'>There are no requirements to be migrated!</span></b>";
		}
		else
		{
		  migrate_requirements($this->source_db,$this->target_db,$req,$this->old_new);
		}
		echo "</div><p>";
	}

function requirements_coverage_migration()
	{
		$hhmmss=date("H:i:s");
		echo "<a onclick=\"return DetailController.toggle('details-req_coverage')\" href=\"tplan/\">
		<img src='../img/icon-foldout.gif' align='top' title='show/hide'> Req. Coverage (requirement / test case relationship): {$this->msg_click_to_show} {$hhmmss}</a>";
		echo '<div class="detail-container" id="details-req_coverage" style="display: none;">';

		$sql="SELECT * from req_coverage";

		// 20061203 - franciscom - id (wrong) -> id_req
		//$req_cov=$this->source_db->fetchRowsIntoMap($sql,'id_req');   //970 items no duplicate ids
	
		// 20071213 - havlatm:   	 0001112: Requirements coverage migration from 1.6.3 to 1.7.0 final
		$result = $this->source_db->get_recordset($sql);	
		
		if(is_null($result))
		{
			echo "<span class='notok'>There are no req specs to be migrated!</span></b>";
		}
		else
		{
			// 20071213 - havlatm:   	 0001112: Requirements coverage migration from 1.6.3 to 1.7.0 final
			migrate_req_coverage($this->source_db,$this->target_db,$result,$this->old_new);
		}
		echo "</div><p>";
	}

}
?>