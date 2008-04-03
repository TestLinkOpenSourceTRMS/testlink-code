<?php
/** TestLink Open Source Project - http://testlink.sourceforge.net/
 *
 * @filesource $RCSfile: testplan.class.php,v $
 * @version $Revision: 1.62 $
 * @modified $Date: 2008/04/03 22:07:56 $ $Author: franciscom $
 * @author franciscom
 *
 * Manages test plan operations and related items like Custom fields.
 *
 *
 *
 * rev:
 *     20080403 - franciscom - setExecutionOrder()
 *     20080310 - sbouffard - contribution added NHB.name to recordset (useful for API methods).  
 *     20080224 - franciscom - get_linked_tcversions() interface changes
 *     20080217 - franciscom - interface changes - check_build_name_existence()
 *     20080119 - franciscom - get_linked_and_newest_tcversions() (support for external id)
 *     20080119 - franciscom - improved logic in copy_as to avoid bug due to
 *                             missing methods.
 *     20080114 - franciscom - get_linked_tcversions()
 *     20071205 - franciscom - copy_as() - added reactored code from contribution
 *
 *     20071010 - franciscom - BUGID     MSSQL reserved word problem - open
 *     20070927 - franciscom - BUGID 1069
 *                             added _natsort_builds() (see natsort info on PHP manual).
 *                             get_builds() add call to _natsort_builds()
 *                             get_builds_for_html_options() add call to natsort()
 *
 *
 *     20070917 - franciscom - get_linked_tcversions() added version on recordset
 *     20070630 - franciscom - get_linked_tcversions() changed ORDER BY CLAUSE
 *     20070630 - franciscom - get_linked_tcversions(), added active column
 *                             in output recordset.
 *
 *                             html_table_of_custom_field_values()
 *
 *     20070519 - franciscom - added Class milestone_mgr
 *
 *     copy_milestones()- changed date to target_date, because date
 *                        is an Oracle reverved word.
 *
 *     20070501 - franciscom - added localization of custom field labels
 *                             added use of htmlspecialchars() on labels
 *     20070425 - franciscom - added get_linked_and_newest_tcversions()
 *     20070310 - franciscom - BUGID 731
 *     20070306 - franciscom -
 *     BUGID 705 - changes in get_linked_tcversions()
 *
 *     20070127 - franciscom - added insert_default_priorities()
 *     20070127 - franciscom - custom field management
 *     20070120 - franciscom - added Class build_mgr
 *
 *     20070120 - franciscom - added active and open argument
 *                             to build functions
 *                             get_builds_for_html_options()
 *                             get_builds()
 *
*/

require_once( dirname(__FILE__). '/tree.class.php' );
require_once( dirname(__FILE__) . '/assignment_mgr.class.php' );
require_once( dirname(__FILE__) . '/attachments.inc.php' );

class testplan extends tlObjectWithAttachments
{
	var $db;
	var $tree_manager;
	var $assignment_mgr;
  var $cfield_mgr;
  var $builds_table="builds";
  var $testplan_tcversions_table="testplan_tcversions";

	var $assignment_types;
	var $assignment_status;


  /*
   function: testplan
             constructor

   args: db [reference] db object

   returns:

  */
	function testplan(&$db)
	{
		$this->db = &$db;
		$this->tree_manager = New tree($this->db);

		$this->assignment_mgr=New assignment_mgr($this->db);
		$this->assignment_types=$this->assignment_mgr->get_available_types();
		$this->assignment_status=$this->assignment_mgr->get_available_status();

  		$this->cfield_mgr=new cfield_mgr($this->db);

		tlObjectWithAttachments::__construct($this->db,'testplans');
	}


/*
  function: create
            creates a tesplan on Database, for a testproject.

  args: name: testplan name
        notes: testplan notes
        testproject_id: testplan parent.

  returns: id: if everything ok -> id of new testplan (node id).
               if problems -> 0.

*/
function create($name,$notes,$testproject_id)
{
	$node_types=$this->tree_manager->get_available_node_types();
	$tplan_id = $this->tree_manager->new_node($testproject_id,$node_types['testplan'],$name);

	$sql = "INSERT INTO testplans (id,notes,testproject_id)
	        VALUES ( {$tplan_id} " . ", '" .
	                 $this->db->prepare_string($notes) . "'," .
	                 $testproject_id .")";
	$result = $this->db->exec_query($sql);
	$id = 0;
	if ($result)
	{
		$id = $tplan_id;
	}
	return $id;
}


/*
  function: update testplan information

  args: id: testplan id
        name:
        notes:
        is_active: active status

  returns: 1 -> ok
           0 -> ko

*/
function update($id,$name,$notes,$is_active)
{
	$do_update = 1;
	$result = null;
	$active = to_boolean($is_active);
	$name = trim($name);

	// two tables to update and we have no transaction yet.
	$rsa = $this->get_by_id($id);
	$duplicate_check = (strcmp($rsa['name'],$name) != 0 );

	if($duplicate_check)
	{
		$rs = $this->get_by_name($name,$rsa['parent_id']);
		$do_update = is_null($rs);
	}

	if($do_update)
	{
		// Update name
		$sql = "UPDATE nodes_hierarchy " .
				"SET name='" . $this->db->prepare_string($name) . "'" .
				"WHERE id={$id}";
		$result = $this->db->exec_query($sql);

		if($result)
		{
			$sql = "UPDATE testplans " .
					"SET active={$active}," .
					"notes='" . $this->db->prepare_string($notes). "' " .
					"WHERE id=" . $id;
			$result = $this->db->exec_query($sql);
		}
	}
	return ($result ? 1 : 0);
}


/*
  function: get_by_name
            get information about a testplan using name as access key.
            Search can be narrowed, givin a testproject id as filter criteria.

  args: name: testplan name
        [tproject_id]: default:0 -> system wide search i.e. inside all testprojects

  returns: if nothing found -> null
           if found -> array where every element is a map with following keys:
                       id: testplan id
                       notes:
                       active: active status
                       is_open: open status
                       name: testplan name
                       testproject_id



*/
function get_by_name($name,$tproject_id = 0)
{
	$sql = " SELECT testplans.*, NH.name " .
	       " FROM testplans, nodes_hierarchy NH" .
	       " WHERE testplans.id=NH.id " .
	       " AND NH.name = '" . $this->db->prepare_string($name) . "'";

	if($tproject_id > 0 )
	{
		$sql .= " AND NH.parent_id={$tproject_id}";
	}

	$recordset = $this->db->get_recordset($sql);
	return($recordset);
}

/*
  function: get_by_id

  args : id: testplan id

  returns: map with following keys:
           id: testplan id
           name: testplan name
           notes: testplan notes
           testproject_id
           active
           is_open
           parent_id


*/
function get_by_id($id)
{
	$sql = " SELECT testplans.*,NH.name,NH.parent_id
	         FROM testplans, nodes_hierarchy NH
	         WHERE testplans.id = NH.id
	         AND   testplans.id = {$id}";
	$recordset = $this->db->get_recordset($sql);
	return($recordset ? $recordset[0] : null);
}


/*
  function: get_all
            get array of info for every test plan,
            without considering Test Project and any other kind of filter.
            Every array element contains an assoc array


  args : -

  returns: array, every element is a  map with following keys:
           id: testplan id
           name: testplan name
           notes: testplan notes
           testproject_id
           active
           is_open
           parent_id
*/
function get_all()
{
	$sql = " SELECT testplans.*, nodes_hierarchy.name
	         FROM testplans, nodes_hierarchy
	         WHERE testplans.id=nodes_hierarchy.id";
	$recordset = $this->db->get_recordset($sql);
	return $recordset;
}



/*
  function: count_testcases
            get number of testcases linked to a testplan

  args: id: testplan id

  returns: number

*/
function count_testcases($id)
{
	$sql = "SELECT COUNT(testplan_id) AS qty FROM testplan_tcversions
	        WHERE testplan_id={$id}";
	$recordset = $this->db->get_recordset($sql);
	$qty = 0;
	if(!is_null($recordset))
	{
		$qty = $recordset[0]['qty'];
	}
	return $qty;
}

/*
  function: link_tcversions
            associates version of different test cases to a test plan.
            this is the way to populate a test plan


  args :
        $id: test plan id
        $items_to_link: assoc array key=tc_id value=tcversion_id
                        passed by reference for speed

  returns: -

*/
function link_tcversions($id,&$items_to_link)
{
	$sql = "INSERT INTO testplan_tcversions (testplan_id,tcversion_id) VALUES ({$id},";

	foreach($items_to_link as $tc => $tcversion)
	{
		$result = $this->db->exec_query($sql . "{$tcversion})");
		if ($result)
			logAuditEvent(TLS("audit_tc_added_to_testplan",$tcversion),"ASSIGN",$id,"testplans");
	}
}


/*
  function: setExecutionOrder


  args :
        $id: test plan id
        $executionOrder: assoc array key=tcversion_id value=order
                         passed by reference for speed

  returns: -

*/
function setExecutionOrder($id,&$executionOrder)
{
  foreach($executionOrder as $tcVersionID => $execOrder)
  {
      $execOrder=intval($execOrder);
      $sql="UPDATE {$this->testplan_tcversions_table} " .
           "SET node_order={$execOrder} " .
           "WHERE testplan_id={$id} " .
           "AND tcversion_id={$tcVersionID}";
		  
		    echo "<br>debug - <b><i>" . __FUNCTION__ . "</i></b><br><b>" . $sql . "</b><br>";

		  $result = $this->db->exec_query($sql);
  }
}




/*
  function: get_linked_tcversions
            get information about testcases linked to a testplan.

  args :
         id: testplan id
         [tcase_id]: default null => get any testcase
                     numeric      => just get info for this testcase

         [keyword_id]: default 0 => do not filter by keyword id
                       numeric   => filter by keyword id

         [executed]: default NULL => get executed and NOT executed
                                     get only executed tcversions

         [assigned_to]: default NULL => do not filter by user assign.
                        numeric > 0     => filter by user id

         [exec_status]: default NULL => do not filter by execution status
                        character    => filter by execution status=character

         [build_id]: default 0 => do not filter by build id
                     numeric   => filter by build id

         [cf_hash]: default null => do not filter by Custom Fields values

         [include_unassigned]: has effects only if [assigned_to] <> null.
                               default: false
                               true: also testcase not assigned will be retreived


  returns: map
           key: testcase id
           value: map with following keys:

           Notice:
           executed field: will take the following values
                           NULL if the tc version has not been executed in THIS test plan
                           tcversion_id if has executions

 rev :
       20080309 - sbouffard - added NHB.name to recordset
       20080114 - franciscom - added external_id in output
     	 20070825 - franciscom - added NHB.node_order on ORDER BY
       20070630 - franciscom - added active tcversion status in output recorset
       20070306 - franciscom - BUGID 705

*/
function get_linked_tcversions($id,$tcase_id=null,$keyword_id=0,$executed=null,
                               $assigned_to=null,$exec_status=null,$build_id=0,
                               $cf_hash = null, $include_unassigned=false)
{
  $tc_status=config_get('tc_status');
  $status_not_run=$tc_status['not_run'];

	$keywords_join = " ";
	$keywords_filter = " ";
	$tc_id_filter = " ";
	$executions_join = " ";
	$executions_filter=" ";
	$sql_subquery='';
  $build_filter = " ";


	if($keyword_id > 0)
	{
	    $keywords_join = " JOIN testcase_keywords TK ON NHA.parent_id = TK.testcase_id ";
	    $keywords_filter = " AND TK.keyword_id = {$keyword_id} ";
	}
	if (!is_null($tcase_id) )
	{
	   if( is_array($tcase_id) )
	   {

	   }
	   else if ($tcase_id > 0 )
	   {
	      $tc_id_filter = " AND NHA.parent_id = {$tcase_id} ";
	   }
	}



	// --------------------------------------------------------------
	if(!is_null($exec_status) )
	{
	    if( $exec_status == $status_not_run)
	    {
	      $executions_filter=" AND E.status IS NULL ";
	    }
	    else
	    {
	      $executions_filter=" AND E.status='" . $exec_status . "' ";
	      $sql_subquery=" AND E.id IN ( SELECT MAX(id) " .
                      "               FROM  executions " .
                      "               WHERE testplan_id={$id} " .
                      "               GROUP BY tcversion_id,testplan_id )";
	    }

	}
	// --------------------------------------------------------------

	// --------------------------------------------------------------
	if( $build_id > 0 )
	{
      $build_filter = " AND E.build_id={$build_id} ";
	}

	if(is_null($executed))
	{
     $executions_join = " LEFT OUTER ";
	}
	$executions_join .= " JOIN executions E ON " .
	                    " (NHA.id = E.tcversion_id AND " .
	                    "  E.testplan_id=T.testplan_id {$build_filter}) ";

	// --------------------------------------------------------------



	// missing condition on testplan_id between execution and testplan_tcversions
	// added tc_id in order clause to maintain same order that navigation tree

	// 20080114 - franciscom - added tc_external_id
	//
	// 20070106 - franciscom
	// Postgres does not like Column alias without AS, and (IMHO) he is right
	//
	// 20070917 - added version
	//
	// 20080331 - added T.node_order
	//
	$sql = " SELECT NHB.parent_id AS testsuite_id, " .
	     "        NHA.parent_id AS tc_id, NHB.node_order AS z, NHB.name," .
	     "        T.tcversion_id AS tcversion_id, T.id AS feature_id, T.node_order AS execution_order," .
	     "        TCV.version AS version, TCV.active,TCV.tc_external_id AS external_id," .
	     "        E.id AS exec_id, " .
	     "        E.tcversion_id AS executed, E.testplan_id AS exec_on_tplan, " .
	     "        UA.user_id,UA.type,UA.status,UA.assigner_id, " .
	     "        COALESCE(E.status,'" . $status_not_run . "') AS exec_status ".
	     " FROM nodes_hierarchy NHA " .
	     " JOIN nodes_hierarchy NHB ON NHA.parent_id = NHB.id " .
	     " JOIN testplan_tcversions T ON NHA.id = T.tcversion_id " .
	     " JOIN tcversions TCV ON NHA.id = TCV.id " .
	     " {$executions_join} " .
	     " {$keywords_join} " .
	     " LEFT OUTER JOIN user_assignments UA ON UA.feature_id = T.id " .
	     " WHERE T.testplan_id={$id} {$keywords_filter} {$tc_id_filter} " .
	     " AND (UA.type=" . $this->assignment_types['testcase_execution']['id'] .
	     "      OR UA.type IS NULL) " . $executions_filter;


	if (!is_null($assigned_to) && $assigned_to > 0)
	{
    // 20080224 - franciscom
	  $sql .= " AND ";
	  $sql_unassigned="";
	  if( $include_unassigned )
	  {
		    $sql .= "(";
		    $sql_unassigned=" OR UA.user_id IS NULL)";
		}
		$sql .= " UA.user_id = {$assigned_to} " . $sql_unassigned;
	}

	$sql .=$sql_subquery;
	// $sql .= " ORDER BY testsuite_id,tc_id,E.id ASC";
	//
	// BUGID 989 -
	// added NHB.node_order
	$sql .= " ORDER BY testsuite_id,NHB.node_order,tc_id,E.id ASC";

  //  echo "<br>debug - <b><i>" . __FUNCTION__ . "</i></b><br><b>" . $sql . "</b><br>";

	$recordset = $this->db->fetchRowsIntoMap($sql,'tc_id');

   // 20070913 - jbarchibald
   // here we add functionality to filter out the custom field selections
    if (!is_null($cf_hash)) {
        $recordset = $this->filter_cf_selection($recordset, $cf_hash);
    }

	return $recordset;
}


/*
  function: get_linked_and_last_tcversions
            returns for every test case in a test plan
            the tc version linked and the newest available version

  args: id: testplan id
        [tcase_id]: default null => all testcases linked to testplan

  returns: map key: testcase internal id
           values: map with following keys:

            [name]
            [tc_id] (internal id)
            [tcversion_id]
            [newest_tcversion_id]
            [tc_external_id]
            [version] (for humans)
            [newest_version] (for humans)


  rev:
      20080126 - franciscom - added tc_external_id on results

*/
function get_linked_and_newest_tcversions($id,$tcase_id=null)
{
  $tc_id_filter = " ";
	if (!is_null($tcase_id) )
	{
	   if( is_array($tcase_id) )
	   {
        // ??? implement as in ?
	   }
	   else if ($tcase_id > 0 )
	   {
	      $tc_id_filter = " AND NHA.parent_id = {$tcase_id} ";
	   }
	}
	$sql = " SELECT MAX(NHB.id) AS newest_tcversion_id, " .
	       "        NHA.parent_id AS tc_id, NHC.name, " .
	       "        T.tcversion_id AS tcversion_id," .
	       "        TCVA.tc_external_id AS tc_external_id," .
	       "        TCVA.version AS version" .
	       " FROM nodes_hierarchy NHA " .
	       " JOIN nodes_hierarchy NHB ON NHA.parent_id = NHB.parent_id " .
	       " JOIN nodes_hierarchy NHC ON NHA.parent_id = NHC.id " .
	       " JOIN testplan_tcversions T ON NHA.id = T.tcversion_id " .
	       " JOIN tcversions TCVA ON T.tcversion_id = TCVA.id " .
	       " JOIN tcversions TCVB ON NHB.id = TCVB.id AND TCVB.active=1 " .
	       " WHERE T.testplan_id={$id} AND NHB.id > NHA.id" . $tc_id_filter .
	       " GROUP BY NHA.parent_id, NHC.name, tcversion_id, TCVA.version  ";

	$sql2 = " SELECT SUBQ.name, SUBQ.newest_tcversion_id, SUBQ.tc_id, " .
	        " SUBQ.tcversion_id, SUBQ.version, SUBQ.tc_external_id, " .
	        " TCV.version AS newest_version" .
	        " FROM tcversions TCV, ( $sql ) AS SUBQ" .
	        " WHERE SUBQ.newest_tcversion_id = TCV.id ";



	$sql2 .= " ORDER BY SUBQ.tc_id";
	$recordset = $this->db->fetchRowsIntoMap($sql2,'tc_id');

	return $recordset;
}






// $id   : test plan id
// $items: assoc array key=tc_id value=tcversion_id
//
//
// 20060910 - franciscom
// added remove of records from user_assignments table
//
function unlink_tcversions($id,&$items)
{
	if(!is_null($items))
	{
		$idList = implode(",",$items);
	    $in_clause = " AND tcversion_id IN (" . $idList . ")";

      // Need to remove all related info:
      // execution_bugs - to be done
      // executions

      // First get the executions id if any exist
      $sql=" SELECT id AS execution_id
             FROM executions
             WHERE testplan_id = {$id} ${in_clause}";
      $exec_ids = $this->db->fetchRowsIntoMap($sql,'execution_id');

      if( !is_null($exec_ids) and count($exec_ids) > 0 )
      {
          // has executions
          $exec_ids = array_keys($exec_ids);
          $exec_id_where= " WHERE execution_id IN (" . implode(",",$exec_ids) . ")";

          // Remove bugs if any exist
          $sql=" DELETE FROM execution_bugs {$exec_id_where} ";
          $result = $this->db->exec_query($sql);

          // now remove executions
          $sql=" DELETE FROM executions
                 WHERE testplan_id = {$id} ${in_clause}";
          $result = $this->db->exec_query($sql);
      }

      // ----------------------------------------------------------------
      // 20060910 - franciscom
      // to remove the assignment to users (if any exists)
      // we need the list of id
      $sql=" SELECT id AS link_id FROM testplan_tcversions
             WHERE testplan_id={$id} {$in_clause} ";
	    // $link_id = $this->db->get_recordset($sql);
	    $link_ids = $this->db->fetchRowsIntoMap($sql,'link_id');
	    $features = array_keys($link_ids);
	    if( count($features) == 1)
	    {
	      $features=$features[0];
	    }
	    $this->assignment_mgr->delete_by_feature_id($features);
	    // ----------------------------------------------------------------

      // Delete from link table
      $sql=" DELETE FROM testplan_tcversions
             WHERE testplan_id={$id} {$in_clause} ";
	    $result = $this->db->exec_query($sql);

		logAuditEvent(TLS("audit_tc_removed_from_testplan",$idList),"UNASSIGN",$id,"testplans");
	}
} // end function


// 20060430 - franciscom
function get_keywords_map($id,$order_by_clause='')
{
  $map_keywords=null;

  // keywords are associated to testcase id, then first
  // we need to get the list of testcases linked to the testplan
  $linked_items = $this->get_linked_tcversions($id);
  if( !is_null($linked_items) )
  {
     $tc_id_list = implode(",",array_keys($linked_items));

  	 $sql = "SELECT DISTINCT keyword_id,keywords.keyword
	           FROM testcase_keywords,keywords
	           WHERE keyword_id = keywords.id
	           AND testcase_id IN ( {$tc_id_list} )
	           {$order_by_clause}";
	   $map_keywords = $this->db->fetchColumnsIntoMap($sql,'keyword_id','keyword');
  }
  return ($map_keywords);
} // end function


function get_keywords_tcases($id,$keyword_id=0)
{
  $map_keywords=null;

  // keywords are associated to testcase id, then first
  // we need to get the list of testcases linked to the testplan
  $linked_items = $this->get_linked_tcversions($id);
  if( !is_null($linked_items) )
  {
     $keyword_filter= '' ;
     if( $keyword_id > 0 )
     {
       $keyword_filter = " AND keyword_id = {$keyword_id} ";
     }
     $tc_id_list = implode(",",array_keys($linked_items));

  	 $sql = "SELECT DISTINCT testcase_id,keyword_id,keyword
	           FROM testcase_keywords,keywords
	           WHERE keyword_id = keywords.id
	           AND testcase_id IN ( {$tc_id_list} )
 		         {$keyword_filter}
			       ORDER BY keyword ASC ";
		$map_keywords = $this->db->fetchRowsIntoMap($sql,'testcase_id');
  }
  return ($map_keywords);
} // end function
// -------------------------------------------------------------------------------

/*
  function: copy_as
            creates a new test plan using an existent one as source.



  args: id: source testplan id
        new_tplan_id: destination
        [tplan_name]: default null.
                      != null => set this as the new name

        [tproject_id]: default null.
                       != null => set this as the new testproject for the testplan
                                  this allow us to copy testplans to differents test projects.
        [copy_options]: default null
                        null: do a deep copy => copy following test plan child elements:
                              builds,linked tcversions,milestones,
                              user_roles,priorities.
                        != null, a map with keys that controls what child elements to copy

        [tcversion_type]:  default null -> use same version present on source testplan
                          'lastest' -> for every testcase linked to source testplan
                                      use lastest available version

  returns:

*/
function copy_as($id,$new_tplan_id,$tplan_name=null,
                 $tproject_id=null,$copy_options=null,$tcversion_type=null)
{
  $cp_options = array('copy_tcases' => 1,'copy_test_urgency' => 1,
	                    'copy_milestones' => 1, 'copy_user_roles' => 1, 'copy_builds' => 1);

  $cp_methods = array('copy_tcases' => 'copy_linked_tcversions',
                      'copy_test_urgency' => 'copy_test_urgency',
	                    'copy_milestones' => 'copy_milestones',
	                    'copy_user_roles' => 'copy_user_roles',
	                    'copy_builds' => 'copy_builds');


  if( !is_null($copy_options) )
  {
    $cp_options=$copy_options;
  }

  // get source testplan general info
  $rs_source=$this->get_by_id($id);

  if(!is_null($tplan_name))
  {
    $sql="UPDATE nodes_hierarchy " .
         "SET name='" . $this->db->prepare_string(trim($tplan_name)) . "' " .
         "WHERE id={$new_tplan_id}";
    $this->db->exec_query($sql);
  }

  if(!is_null($tproject_id))
  {
    $sql="UPDATE testplans " .
         "SET testproject_id={$tproject_id} " .
         "WHERE id={$new_tplan_id}";
    $this->db->exec_query($sql);
  }


  foreach( $cp_options as $key => $do_copy )
  {
    if( $do_copy )
    {
      if( isset($cp_methods[$key]) )
      {
          $copy_method=$cp_methods[$key];
          $this->$copy_method($id,$new_tplan_id,$tcversion_type);
      }
    }
  }

} // end function


// $id: source testplan id
// $new_tplan_id: destination
//
function copy_builds($id,$new_tplan_id)
{
  $rs=$this->get_builds($id);

  if(!is_null($rs))
  {
    foreach($rs as $build)
    {
      $sql="INSERT {$this->builds_table} (name,notes,testplan_id) " .
           "VALUES ('" . $this->db->prepare_string($build['name']) ."'," .
           "'" . $this->db->prepare_string($build['notes']) ."',{$new_tplan_id})";

      $this->db->exec_query($sql);
    }
  }
}


/*
  function: copy_linked_tcversions

  args: id: source testplan id
        new_tplan_id: destination
        [tcversion_type]: default null -> use same version present on source testplan
                          'lastest' -> for every testcase linked to source testplan
                                      use lastest available version

  returns:

*/
function copy_linked_tcversions($id,$new_tplan_id,$tcversion_type=null)
{
  $sql="SELECT * FROM testplan_tcversions WHERE testplan_id={$id} ";

  $rs=$this->db->get_recordset($sql);

  if(!is_null($rs))
  {
   	$tcase_mgr = new testcase($this->db);

    foreach($rs as $elem)
    {
      $tcversion_id = $elem['tcversion_id'];

  		if( !is_null($tcversion_type) )
		  {
			  $sql="SELECT * FROM nodes_hierarchy WHERE id={$tcversion_id} ";
			  $rs2=$this->db->get_recordset($sql);
			  $last_version_info = $tcase_mgr->get_last_version_info($rs2[0]['parent_id']);
			  $tcversion_id = $last_version_info ? $last_version_info['id'] : $tcversion_id ;
		  }

      $sql="INSERT INTO testplan_tcversions " .
           "(testplan_id,tcversion_id) " .
           "VALUES({$new_tplan_id},{$tcversion_id})";
      $this->db->exec_query($sql);
    }
  }
}


/*
  function: copy_milestones

  args: id: source testplan id
        new_tplan_id: destination

  returns:

  rev : 20070519 - franciscom
        changed date to target_date, because date is an Oracle reverved word.

*/
function copy_milestones($id,$new_tplan_id)
{
  $sql="SELECT * FROM milestones WHERE testplan_id={$id} ";
  $rs=$this->db->get_recordset($sql);

  if(!is_null($rs))
  {
    foreach($rs as $mstone)
    {
      $sql="INSERT milestones (name,A,B,C,target_date,testplan_id) " .
           "VALUES ('" . $this->db->prepare_string($mstone['name']) ."'," .
           $mstone['A'] . "," . $mstone['B'] . "," . $mstone['C'] . "," .
           "'" . $mstone['target_date'] . "',{$new_tplan_id})";

      $this->db->exec_query($sql);
    }
  }
}

/*
  function:

  args :

  returns:

*/
function get_milestones($id)
{
  $sql="SELECT * FROM milestones WHERE testplan_id={$id} ORDER BY target_date";
  $rs=$this->db->get_recordset($sql);
  return $rs;
}




// $id: source testplan id
// $new_tplan_id: destination
//
function copy_user_roles($id,$new_tplan_id)
{
  $sql="SELECT * FROM user_testplan_roles WHERE testplan_id={$id} ";

  $rs=$this->db->get_recordset($sql);

  if(!is_null($rs))
  {
    foreach($rs as $elem)
    {
      $sql="INSERT INTO user_testplan_roles " .
           "(testplan_id,user_id,role_id) " .
           "VALUES({$new_tplan_id}," . $elem['user_id'] ."," . $elem['role_id'] . ")";
      $this->db->exec_query($sql);
    }
  }
}

/**
 * Copy definition about test urgency to a new Test Plan
 */
function copy_test_urgency($id,$new_tplan_id)
{
  $sql="SELECT * FROM test_urgency WHERE testplan_id={$id} ";
  $rs=$this->db->get_recordset($sql);
  if(!is_null($rs))
  {
    foreach($rs as $pr)
    {
      $sql="INSERT test_urgency (urgency,node_id,testplan_id) VALUES (" .
            $pr['urgency'] . "," . $pr['node_id'] . ",{$new_tplan_id})";

      $this->db->exec_query($sql);
    }
  }
}

/**
 * Gets all testplan related user assignments
 *
 * @param int $testPlanID the testplan id
 * @return array assoc map with keys taken from the user_id column
 **/
	function getUserRoleIDs($testPlanID)
	{
		$query = "SELECT user_id,role_id FROM user_testplan_roles WHERE testplan_id = {$testPlanID}";
		$roles = $this->db->fetchRowsIntoMap($query,'user_id');
		return $roles;
	}


/**
 * Inserts a testplan related role for a given user
 *
 * @param int $userID the id of the user
 * @param int $testPlanID the testplan id
 * @param int $roleID the role id
 * @return returns tl::OK on success, tl::ERROR else
 **/

function addUserRole($userID,$testPlanID,$roleID)
{
	$query = "INSERT INTO user_testplan_roles (user_id,testplan_id,role_id) VALUES ({$userID},{$testPlanID},{$roleID})";
	if ($this->db->exec_query($query))
	{
		$testPlan = $this->get_by_id($testPlanID);
		$role = tlRole::getByID($this->db,$roleID,tlRole::TLOBJ_O_GET_DETAIL_MINIMUM);
		$user = tlUser::getByID($this->db,$userID,tlUser::TLOBJ_O_GET_DETAIL_MINIMUM);
		if ($user && $testPlan && $role)
			logAuditEvent(TLS("audit_users_roles_added_testplan",$user->getDisplayName(),$testPlan['name'],$role->name),"ASSIGN",$testPlanID,"testplans");
		return tl::OK;
	}
	return tl::ERROR;
}

/**
 * Deletes all testplan related role assignments for a given testplan
 *
 * @param int $testPlanID the testplan id
 * @return tl::OK  on success, tl::FALSE else
 **/
function deleteUserRoles($testPlanID)
{
	$query = "DELETE FROM user_testplan_roles WHERE testplan_id = {$testPlanID}";
	if ($this->db->exec_query($query))
	{
		$testPlan = $this->get_by_id($testPlanID);
		if ($testPlan)
			logAuditEvent(TLS("audit_all_user_roles_removed_testplan",$testPlan['name']),"ASSIGN",$testPlanID,"testplans");
		return tl::OK;
	}
	return tl::ERROR;

}

/*
  function:

  args :

  returns:

  rev :
        20070129 - franciscom - added custom field management
*/
function delete($id)
{

  $the_sql=array();
  $main_sql=array();

  $this->deleteUserRoles($id);
  $the_sql[]="DELETE FROM milestones WHERE testplan_id={$id}";
  $the_sql[]="DELETE FROM testplan_tcversions WHERE testplan_id={$id}";
  $the_sql[]="DELETE FROM {$this->builds_table} WHERE testplan_id={$id}";
  $the_sql[]="DELETE FROM test_urgency WHERE testplan_id={$id}";
  $the_sql[]="DELETE FROM cfield_execution_values WHERE testplan_id={$id}";

  // When deleting from executions, we need to clean related tables
  $the_sql[]="DELETE FROM execution_bugs WHERE execution_id ".
             "IN (SELECT id from executions WHERE testplan_id={$id})";
  $the_sql[]="DELETE FROM executions WHERE testplan_id={$id}";


  foreach($the_sql as $sql)
  {
    $this->db->exec_query($sql);
  }

  $this->deleteAttachments($id);

  $this->cfield_mgr->remove_all_design_values_from_node($id);
  // ------------------------------------------------------------------------

  // Finally delete from main table
  $main_sql[]="DELETE FROM testplans WHERE id={$id}";
  $main_sql[]="DELETE FROM nodes_hierarchy WHERE id={$id}";

  foreach($main_sql as $sql)
  {
    $this->db->exec_query($sql);
  }
} // end delete()



// -----------------------------------------------------------------------------
// Build related methods
// -----------------------------------------------------------------------------

/*
  function: get_builds_for_html_options()


  args :
        $id     : test plan id.
        [active]: default:null -> all, 1 -> active, 0 -> inactive BUILDS
        [open]  : default:null -> all, 1 -> open  , 0 -> closed/completed BUILDS

  returns:

  rev :
        20070129 - franciscom - order to ASC
        20070120 - franciscom
        added active, open
*/
function get_builds_for_html_options($id,$active=null,$open=null)
{
	$sql = " SELECT id, name " .
	       " FROM {$this->builds_table} WHERE testplan_id = {$id} ";

	// 20070120 - franciscom
 	if( !is_null($active) )
 	{
 	   $sql .= " AND active=" . intval($active) . " ";
 	}
 	if( !is_null($open) )
 	{
 	   $sql .= " AND is_open=" . intval($open) . " ";
 	}

  $sql .= " ORDER BY name ASC";


	// BUGID
	$recordset=$this->db->fetchColumnsIntoMap($sql,'id','name');
	if( !is_null($recordset) )
	{
	  natsort($recordset);
	}

	return $recordset;

}

/*
  function: get_max_build_id

  args :
        $id     : test plan id.

  returns:

  rev :
*/
function get_max_build_id($id,$active = null,$open = null)
{
	$sql = " SELECT MAX(id) AS maxbuildid " .
	       " FROM {$this->builds_table} " .
	       " WHERE testplan_id = {$id}";

 	if(!is_null($active))
 	{
 	   $sql .= " AND active = " . intval($active) . " ";
 	}
 	if( !is_null($open) )
 	{
 	   $sql .= " AND is_open = " . intval($open) . " ";
 	}

	$recordset = $this->db->get_recordset($sql);
	$maxBuildID = 0;
	if ($recordset)
		$maxBuildID = intval($recordset[0]['maxbuildid']);

	return $maxBuildID;
}



/*
  function: get_builds
            get info about builds defined for a testlan.
            Build can be filtered by active and open status.

  args :
        id: test plan id.
        [active]: default:null -> all, 1 -> active, 0 -> inactive BUILDS
        [open]: default:null -> all, 1 -> open  , 0 -> closed/completed BUILDS

  returns: map, where elements are ordered by build name, using variant of nasort php function.
           key: build id
           value: map with following keys
                  id: build id
                  name: build name
                  notes: build notes
                  active: build active status
                  is_open: build open status
                  testplan_id


  rev :
        20070120 - franciscom
        added active, open
*/
function get_builds($id,$active=null,$open=null)
{
	$sql = " SELECT id,testplan_id, name, notes, active, is_open " .
	       " FROM {$this->builds_table} WHERE testplan_id = {$id} " ;

 	if( !is_null($active) )
 	{
 	   $sql .= " AND active=" . intval($active) . " ";
 	}
 	if( !is_null($open) )
 	{
 	   $sql .= " AND is_open=" . intval($open) . " ";
 	}

	$sql .= "  ORDER BY name ASC";

 	$recordset = $this->db->fetchRowsIntoMap($sql,'id');

  if( !is_null($recordset) )
  {
    $recordset = $this->_natsort_builds($recordset);
  }

	return $recordset;
}


/*
  function:

  args:

  returns:

*/
function get_build_by_name($id,$build_name)
{
  $safe_build_name=$this->db->prepare_string(trim($build_name));

	$sql = " SELECT id,testplan_id, name, notes, active, is_open " .
	       " FROM {$this->builds_table} " .
	       " WHERE testplan_id = {$id} AND name='{$safe_build_name}'";


 	$recordset = $this->db->get_recordset($sql);
  $rs=null;
  if( !is_null($recordset) )
  {
     $rs=$recordset[0];
  }
  return $rs;
}



/*
  function:

  args :

  returns:

*/
function _natsort_builds($builds_map)
{
  // BUGID - sort in natural order (see natsort in PHP manual)
  foreach($builds_map as $key => $value)
  {
    $vk[$value['name']]=$key;
    $build_names[$key]=$value['name'];
  }

  natsort($build_names);
  $build_num=count($builds_map);
  foreach($build_names as $key => $value)
  {
    $dummy[$key]=$builds_map[$key];
  }
  return $dummy;
}



/*
  function: check_build_name_existence

  args:
       tplan_id: test plan id.
       build_name
      [build_id}: default: null
                  when is not null we add build_id as filter, this is useful
                  to understand if is really a duplicate when using this method
                  while managing update operations via GUI

  returns: 1 => name exists

  rev: 20080217 - franciscom - added build_id argument

*/
function check_build_name_existence($tplan_id,$build_name,$build_id=null,$case_sensitive=0)
{
 	$sql = " SELECT id, name, notes " .
	       " FROM {$this->builds_table} " .
	       " WHERE testplan_id = {$tplan_id} ";


	if($case_sensitive)
	{
	    $sql .= " AND name=";
	}
	else
	{
      $build_name=strtoupper($build_name);
	    $sql .= " AND UPPER(name)=";
	}
	$sql .= "'" . $this->db->prepare_string($build_name) . "'";

	if( !is_null($build_id) )
	{
	    $sql .= " AND id <> " . $this->db->prepare_int($build_id);
	}


  $result = $this->db->exec_query($sql);
  $status= $this->db->num_rows($result) ? 1 : 0;

	return($status);
}



/*
  function: create_build

  args :
        $tplan_id
        $name
        $notes
        [$active]: default: 1
        [$open]: default: 1



  returns:

  rev :
*/
function create_build($tplan_id,$name,$notes = '',$active=1,$open=1)
{
	$sql = " INSERT INTO {$this->builds_table} (testplan_id,name,notes,active,is_open) " .
	       " VALUES ('". $tplan_id . "','" .
	                     $this->db->prepare_string($name) . "','" .
	                     $this->db->prepare_string($notes) . "'," .
	                     "{$active},{$open})";

	$new_build_id = 0;
	$result = $this->db->exec_query($sql);
	if ($result)
	{
		$new_build_id = $this->db->insert_id($this->builds_table);
	}

	return $new_build_id;
}

// -------------------------------------------------------------------------------
// Custom field related methods
// -------------------------------------------------------------------------------
/*
  function: get_linked_cfields_at_design


  args: $id
        [$parent_id]:
        [$show_on_execution]: default: null
                              1 -> filter on field show_on_execution=1
                              0 or null -> don't filter


  returns: hash

  rev :
        20061231 - franciscom - added $parent_id
*/
function get_linked_cfields_at_design($id,$parent_id=null,$show_on_execution=null)
{
  $enabled=1;
  $tproject_mgr= new testproject($this->db);
  $the_path=$this->tree_manager->get_path(!is_null($id) ? $id : $parent_id);
  $path_len=count($the_path);
  $tproject_id=($path_len > 0)? $the_path[$path_len-1]['parent_id'] : $parent_id;

  $cf_map=$this->cfield_mgr->get_linked_cfields_at_design($tproject_id,$enabled,
                                                          $show_on_execution,'testplan',$id);

  return($cf_map);
}

/*
  function: get_linked_cfields_at_execution


  args: $id
        [$parent_id]
        [$show_on_execution]: default: null
                              1 -> filter on field show_on_execution=1
                              0 or null -> don't filter


  returns: hash

  rev :
        20061231 - franciscom - added $parent_id
*/
function get_linked_cfields_at_execution($id,$parent_id=null,$show_on_execution=null)
{
  $enabled=1;
  $tproject_mgr= new testproject($this->db);

  $the_path=$this->tree_manager->get_path(!is_null($id) ? $id : $parent_id);
  $path_len=count($the_path);
  $tproject_id=($path_len > 0)? $the_path[$path_len-1]['parent_id'] : $parent_id;

  $cf_map=$this->cfield_mgr->get_linked_cfields_at_design($tproject_id,$enabled,
                                                          $show_on_execution,'testplan',$id);
  return($cf_map);
}




/*
  function: html_table_of_custom_field_inputs


  args: $id
        [$parent_id]: need when you call this method during the creation
                      of a test suite, because the $id will be 0 or null.

        [$scope]: 'design','execution'

  returns: html string

*/
function html_table_of_custom_field_inputs($id,$parent_id=null,$scope='design')
{
  $cf_smarty='';

  if( $scope=='design' )
  {
    $cf_map=$this->get_linked_cfields_at_design($id,$parent_id);
  }
  else
  {
    $cf_map=$this->get_linked_cfields_at_execution($id,$parent_id);
  }

  if( !is_null($cf_map) )
  {
    foreach($cf_map as $cf_id => $cf_info)
    {
      // 20070501 - franciscom
      $label=str_replace(TL_LOCALIZE_TAG,'',lang_get($cf_info['label']));
      $cf_smarty .= '<tr><td class="labelHolder">' . htmlspecialchars($label) . "</td><td>" .
                    $this->cfield_mgr->string_custom_field_input($cf_info) .
                    "</td></tr>\n";
    } //foreach($cf_map
  }


  if($cf_smarty != '')
  {
    $cf_smarty = "<table>" . $cf_smarty . "</table>";
  }
  return($cf_smarty);
}

/*
  function: html_table_of_custom_field_values


  args: $id
        [$scope]: 'design','execution'
        [$show_on_execution]: default: null
                              1 -> filter on field show_on_execution=1
                              0 or null -> don't filter

  returns: html string

  rev :
       20070701 - franciscom - fixed return string when there are no custom fields.

*/
function html_table_of_custom_field_values($id,$scope='design',$show_on_execution=null)
{
  $cf_smarty='';
  $parent_id=null;

  if( $scope=='design' )
  {
    $cf_map=$this->get_linked_cfields_at_design($id,$parent_id,$show_on_execution);
  }
  else
  {
    $cf_map=$this->get_linked_cfields_at_execution($id);
  }

  if( !is_null($cf_map) )
  {
    foreach($cf_map as $cf_id => $cf_info)
    {
      // if user has assigned a value, then node_id is not null
      if($cf_info['node_id'])
      {
        // 20070501 - franciscom
        $label=str_replace(TL_LOCALIZE_TAG,'',lang_get($cf_info['label']));
        $cf_smarty .= '<tr><td class="labelHolder">' . htmlspecialchars($label) . "</td><td>" .
                      $this->cfield_mgr->string_custom_field_value($cf_info,$id) .
                      "</td></tr>\n";
      }
    }
  }

  if($cf_smarty != '')
  {
    $cf_smarty = "<table>" . $cf_smarty . "</table>";
  }
  return($cf_smarty);
} // function end





/*
  function:

  args :

  returns:

*/
/*
function insert_default_priorities($tplan_id)
{
  $risk_range=array_keys(config_get('risk'));
  $importance_range=array_keys(config_get('importance'));

  foreach($risk_range as $risk)
  {
    foreach($importance_range as $importance)
    {
	    $sql = "INSERT into priorities (testplan_id,risk,importance) " .
	           " VALUES ({$tplan_id},'{$risk}', '{$importance}')";
	    $result = $this->db->exec_query($sql);
    }
  }
}
*/

/*
  function:

  args :

  returns:

*/
/*
function get_priority_rules($tplan_id,$do_lang_get=0)
{
	$sql = "SELECT * FROM priorities " .
	       " WHERE testplan_id = {$tplan_id}" .
	       " ORDER BY risk,importance";

	$rs=$this->db->get_recordset($sql);

	if($do_lang_get)
	{
	  $risk_range=config_get('risk');
	  $importance_range=config_get('importance');

    foreach($rs as $key => $row )
    {
      $rs[$key]['risk_verbose']='';
      $rs[$key]['importance_verbose']='';

      if(isset($risk_range[$row['risk']]))
      {
        $rs[$key]['risk_verbose']=lang_get($risk_range[$row['risk']]);
      }

      if(isset($importance_range[$row['importance']]))
      {
        $rs[$key]['importance_verbose']=lang_get($importance_range[$row['importance']]);
      }

    }
	}

	return($rs);
}
*/

/**
 * Set rules for priority within actual Plan
 *
 * @param hash with key  : priority id on priorities table.
 *                  value: priority value
 *        Example:
 *                [priority] => Array
 *                (
 *                 [10] => b
 *                 [11] => b
 *                 [12] => a
 *                 [13] => b
 *                 [14] => b
 *                 [15] => b
 *                 [16] => b
 *                 [17] => b
 *                 [18] => b
 *                )
 *
 *        Important: priority ID is system wide, can not be found in more
 *                   than one test plan, then passing test plan id seems
 *                   superflous. Anyway we use it.
 *
 *
 */
/*
function set_priority_rules($tplan_id,$priority_hash)
{
	foreach($priority_hash as $priID => $priority)
	{
			$sql = "UPDATE priorities " .
			       " SET priority ='{$priority}' " .
			       " WHERE id = {$priID} AND testplan_id={$tplan_id}";
			$result = $this->db->exec_query($sql);
	}
}
*/

  /*
    function: filter_cf_selection

    args :
          $tp_tcs - this comes from get_linked_tcversion
          $cf_hash [cf_id] = value of cfields to filter by.

    returns: array filtered by selected custom fields.

    rev :
  */
    function filter_cf_selection ($tp_tcs, $cf_hash)
    {
        $new_tp_tcs = null;

        foreach ($tp_tcs as $tc_id => $tc_value)
        {

            foreach ($cf_hash as $cf_id => $cf_value)
            {
                $passed = 0;
                // there will never be more than one record that has a field_id / node_id combination
                $sql = "SELECT value FROM cfield_design_values " .
                        "WHERE field_id = $cf_id " .
                        "AND node_id = $tc_id ";

                $result = $this->db->exec_query($sql);
    			$myrow = $this->db->fetch_array($result);

                // push both to arrays so we can compare
                $possibleValues = explode ('|', $myrow['value']);
                $valuesSelected = explode ('|', $cf_value);

                // we want to match any selected item from list and checkboxes.
                if ( count($valuesSelected) ) {
                    foreach ($valuesSelected as $vs_id => $vs_value) {
                        $found = array_search($vs_value, $possibleValues);
                        if (is_int($found)) {
                            $passed = 1;
                        } else {
                            $passed = 0;
                            break;
                        }
                    }
                }
                // if we don't match, fall out of the foreach.
                // this gives a "and" search for all cf's, if this is removed then it responds
                // as an "or" search
                // perhaps this could be parameterized.
                if ($passed == 0) {
                    break;
                }
            }
            if ($passed) {
                $new_tp_tcs[$tc_id] = $tp_tcs[$tc_id];
            }
        }
        return ($new_tp_tcs);
    }



} // end class testplan
// ##################################################################################







// ##################################################################################
//
// Build Manager Class
//
// ##################################################################################
class build_mgr
{
	var $db;

  /*
   function:

   args :

   returns:

  */
	function build_mgr(&$db)
	{
		$this->db = &$db;
	}


  /*
    function: create

    args :
          $tplan_id
          $name
          $notes
          [$active]: default: 1
          [$open]: default: 1



    returns:

    rev :
  */
  function create($tplan_id,$name,$notes = '',$active=1,$open=1)
  {
  	$sql = " INSERT INTO builds (testplan_id,name,notes,active,is_open) " .
  	       " VALUES ('". $tplan_id . "','" .
  	                     $this->db->prepare_string($name) . "','" .
  	                     $this->db->prepare_string($notes) . "'," .
  	                     "{$active},{$open})";

  	$new_build_id = 0;
  	$result = $this->db->exec_query($sql);
  	if ($result)
  	{
  		$new_build_id = $this->db->insert_id('builds');
  	}

  	return $new_build_id;
  }


  /*
    function: update

    args :
          $id
          $name
          $notes
          [$active]: default: 1
          [$open]: default: 1



    returns:

    rev :
  */
  function update($id,$name,$notes,$active=null,$open=null)
  {
  	$sql = " UPDATE builds " .
  	       " SET name='" . $this->db->prepare_string($name) . "'," .
  	       "     notes='" . $this->db->prepare_string($notes) . "'";

  	if( !is_null($active) )
  	{
  	   $sql .=" , active=" . intval($active);
  	}

  	if( !is_null($open) )
  	{
  	   $sql .=" , is_open=" . intval($open);
  	}


  	$sql .= " WHERE id={$id}";

  	$result = $this->db->exec_query($sql);
  	return $result ? 1 : 0;
  }





  /*
    function: delete

    args :
          $id


    returns:

    rev :
  */
  function delete($id)
  {

    //
  	$sql = " DELETE FROM executions " .
  	       " WHERE build_id={$id}";

  	$result=$this->db->exec_query($sql);

  	//
  	$sql = " DELETE FROM builds " .
  	       " WHERE id={$id}";

  	$result=$this->db->exec_query($sql);
  	return $result ? 1 : 0;
  }


  /*
    function: get_by_id
              get information about a build

    args : id: build id

    returns: map with following keys
             id: build id
             name: build name
             notes: build notes
             active: build active status
             is_open: build open status
             testplan_id

    rev :
  */
  function get_by_id($id)
  {
  	$sql = "SELECT * FROM builds WHERE id = {$id}";
  	$result = $this->db->exec_query($sql);
  	$myrow = $this->db->fetch_array($result);
  	return $myrow;
  }


} // end class build_mgr


// ##################################################################################
//
// Milestone Manager Class
//
// ##################################################################################
class milestone_mgr
{
	var $db;

  /*
   function:

   args :

   returns:

  */
	function milestone_mgr(&$db)
	{
		$this->db = &$db;
	}


  /*
    function: create()

    args :
            $tplan_id
            $name
            $target_date
            $A: percentage
            $B: percentage
            $C: percentage

    returns:

  */
  function create($tplan_id,$name,$date,$A,$B,$C)
  {
    $new_milestone_id=0;
  	$sql = "INSERT INTO milestones (testplan_id,name,target_date,A,B,C) " .
  	       " VALUES (" . $tplan_id . ",'" .
  	       $this->db->prepare_string($name) . "','" .
  	       $this->db->prepare_string($date) . "'," . $A . "," .  $B . "," . $C . ")";
  	$result = $this->db->exec_query($sql);

  	if ($result)
    {
    		$new_milestone_id = $this->db->insert_id('milestones');
    }

    return $new_milestone_id;
  }

  /*
    function: update

    args :
          $id
          $name
          $notes
          [$active]: default: 1
          [$open]: default: 1



    returns:

    rev :
  */
  function update($id,$name,$date,$A,$B,$C)
  {
	  $sql = "UPDATE milestones SET name='" . $this->db->prepare_string($name) . "', " .
	         " target_date='" . $this->db->prepare_string($date) . "', " .
	         " A=" . $A . ", B=" . $B . ", C=" . $C . " WHERE id=" . $id;
	  $result = $this->db->exec_query($sql);
	  return $result ? 1 : 0;
  }



  /*
    function: delete

    args :
          $id


    returns:

  */
  function delete($id)
  {
  	$sql = "DELETE FROM milestones WHERE id=" . $id;
  	$result=$this->db->exec_query($sql);
  	return $result ? 1 : 0;
  }


  /*
    function: get_by_id

    args :
          $id


    returns:

    rev :
  */
  function get_by_id($id)
  {
  	$sql = "SELECT * FROM milestones WHERE id = {$id}";
  	$myrow = $this->db->fetchRowsIntoMap($sql,'id');
  	return $myrow;
  }


  /*
    function: get_all_by_testplan
              get info about all milestones defined for a testlan
    args :
          tplan_id


    returns:

    rev :
  */
  function get_all_by_testplan($tplan_id)
  {
    $sql="SELECT * FROM milestones WHERE testplan_id={$tplan_id} ORDER BY target_date";
    $rs=$this->db->get_recordset($sql);
    return $rs;
  }


} // end class milestone_mgr

?>
