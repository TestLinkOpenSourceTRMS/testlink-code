<?php
/*
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: migrate_17_to_18_functions.php,v 1.2 2008/02/20 07:49:13 franciscom Exp $ 

Support function for migration from 1.7.2 to 1.8.0

Author: franciscom
*/
?>

<?php
/*
  function: 

  args:
  
  returns: 

*/
function reqSpecMigration(&$source_db,&$treeMgr)
{
	$hhmmss=date("H:i:s");
	$msg_click_to_show="click to show";
	echo "<a onclick=\"return DetailController.toggle('details-req_spec_table')\" href=\"tplan/\">
	<img src='../../img/icon-foldout.gif' align='top' title='show/hide'> Requirement Specification: {$msg_click_to_show} {$hhmmss}</a>";
	echo '<div class="detail-container" id="details-req_spec_table" style="display: none;">';
	
  $sql="SELECT * from req_specs";
	$rspec=$source_db->fetchRowsIntoMap($sql,'id');
	if(is_null($rspec)) 
	{
		echo "<span class='notok'>There are no req specs to be migrated!</span></b>";
	}
	else
	{
	  $mapping_old_new->req_spec=migrateReqSpecs($source_db,$treeMgr,$rspec);
	}
	echo "</div><p>";
	return $mapping_old_new;
}

/*
  function: migrateReqSpecs

  args:
  
  returns: 

*/
function migrateReqSpecs(&$source_db,&$treeMgr,&$rspec)
{
    $oldNewMapping=array();
    $mappingDescrID=$treeMgr->get_available_node_types();
    $counter=0;
    $rspec_qty=count($rspec);
    echo "<pre>Number of Requirements Specifications (SRS): " . $rspec_qty; echo "</pre>";
        
    foreach($rspec as $req_id => $rdata)
    {
        $nodeID=$treeMgr->new_node($rdata['testproject_id'],
                                   $mappingDescrID['requirement_spec'],$rdata['title']);
        $oldNewMapping[$req_id]=$nodeID;
    }
    
    return $oldNewMapping;  
} // end function


/*
  function: requirementsMigration

  args:
  
  returns: 

*/
function requirementsMigration(&$source_db,&$treeMgr,&$oldNewMapping)
{
  	$msg_click_to_show="click to show";
	  $hhmmss=date("H:i:s");
	  echo "<a onclick=\"return DetailController.toggle('details-reqtable')\" href=\"tplan/\">
	  <img src='../../img/icon-foldout.gif' align='top' title='show/hide'> Requirements: {$msg_click_to_show} {$hhmmss}</a>";
	  echo '<div class="detail-container" id="details-reqtable" style="display: none;">';
    
	  $sql="SELECT * from requirements";
	  $req=$source_db->fetchRowsIntoMap($sql,'id');
	  if(is_null($req)) 
	  {
	  	echo "<span class='notok'>There are no requirements to be migrated!</span></b>";
	  }
	  else
	  {
	    $oldNewMapping=migrateRequirements($source_db,$treeMgr,$req,$oldNewMapping);
	  }
	  echo "</div><p>";
	  
	  return $oldNewMapping;
}

/*
  function: migrateRequirements

  args:
  
  returns: 

*/
function migrateRequirements(&$source_db,&$treeMgr,&$req,&$oldNewMapping)
{
  
    $mappingDescrID=$treeMgr->get_available_node_types();
    $req_qty=count($req);
    echo "<pre>Number of requirements: " . $req_qty; echo "</pre>";
       
    foreach($req as $req_id => $rdata)
    {
        $parentID=$oldNewMapping->req_spec[$rdata['srs_id']];
        $nodeID=$treeMgr->new_node($parentID,
                                   $mappingDescrID['requirement'],
                                   $rdata['title'],$rdata['node_order']);
        $oldNewMapping->req[$req_id]=$nodeID;
    }
    return $oldNewMapping;  
} // end function


/*
  function: updateReqInfo

  args:
  
  returns: 

*/
function updateReqInfo(&$source_db,&$treeMgr,&$oldNewMapping)
{

    $sql="SELECT id,srs_id FROM requirements ";
	  $requirements=$source_db->fetchRowsIntoMap($sql,'id');

    // Update ID in descending order to avoid wrong replacement
    // because we can not be certain that new generated ID will
    // be crash with old IDs.
    //
    // krsort
    krsort($oldNewMapping->req_spec);
    krsort($oldNewMapping->req);

    foreach($oldNewMapping->req_spec as $oldID => $newID)
    {
        $sql="UPDATE req_specs " .
             "SET id={$newID} WHERE id={$oldID}";
        $source_db->exec_query($sql);       
    } 

    foreach($oldNewMapping->req as $oldID => $newID)
    {
        $parentID=$oldNewMapping->req_spec[$requirements[$oldID]['srs_id']];
        
        $sql="UPDATE requirements " .
             " SET id={$newID}, srs_id={$parentID} " .
             " WHERE id={$oldID}";
        $source_db->exec_query($sql);       
        
        $sql="UPDATE req_coverage " .
             " SET req_id={$newID} " .
             " WHERE req_id={$oldID}";
        $source_db->exec_query($sql);       
        
    } 
        
  
}


/*
  function: initNewTProjectProperties

  args:
  
  returns: 

*/
function updateTProjectInfo($source_db,&$tprojectMgr)
{
    $all_tprojects=$tprojectMgr->get_all();
    echo "<pre>debug 20080219 - \$all_tprojects - " . __FUNCTION__ . " --- "; print_r($all_tprojects); echo "</pre>"; 
    
    if( !is_null($all_tprojects) )
    {
        initNewTProjectProperties($all_tprojects,$tprojectMgr);  
    }
  
}


/*
  function: initNewTProjectProperties

  args:
  
  returns: 

*/
function initNewTProjectProperties(&$tprojectMap,&$tprojectMgr)
{
    if( !is_null($tprojectMap) )
    {
        foreach($tprojectMap as $key => $value)
        {
            $tcPrefix=substr($value['name'],0,5) . " (ID={$value['id']}) " ; 
            echo "<pre>debug 20080219 - \ - " . __FUNCTION__ . " --- "; print_r($value); echo "</pre>";
            echo "<pre>debug 20080219 - \ - " . __FUNCTION__ . " --- "; print_r($tcPrefix); echo "</pre>";
             
        }  
    }
}



?>