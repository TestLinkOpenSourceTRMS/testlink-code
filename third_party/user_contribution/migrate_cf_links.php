<?php
/** TestLink Open Source Project - http://testlink.sourceforge.net/
 *
 * @filesource $RCSfile: migrate_cf_links.php,v $
 * @version $Revision: 1.4 $
 * @modified $Date: 2010/09/17 18:26:09 $  $Author: franciscom $
 * @author Francisco Mancardi - francisco.mancardi@gmail.com
 *
 * Migrate Custom field data from item to item version (1.9 RC1 and up)
*/

require_once("../../config.inc.php");
require_once("common.php");

testlinkInitPage($db);

// Check if user has right role to execute
if( !($_SESSION['currentUser']->globalRole->name=='admin') )
{
	echo 'You need to have admin role in order to use this page <b> ';
	die();
}

$treeMgr = new tree($db);
$nodeTypes = $treeMgr->get_available_node_types();
unset($treeMgr);


$tprojectMgr = new testproject($db);
$tables = $tprojectMgr->getDBTables(array('nodes_hierarchy','cfield_design_values'));
unset($tprojectSet);

$sql = " SELECT CFDV.*, NHITEM.node_type_id, NHVERSION.id AS version_node_id" .
       " FROM {$tables['cfield_design_values']} CFDV " .
       " JOIN {$tables['nodes_hierarchy']} NHITEM ON NHITEM.id = CFDV.node_id " .
       " JOIN {$tables['nodes_hierarchy']} NHVERSION ON NHVERSION.parent_id = NHITEM.id " .
       " WHERE NHITEM.node_type_id IN ({$nodeTypes['testcase']},{$nodeTypes['requirement']}) ";

$workingSet = $db->get_recordset($sql);

echo 'Records to process: '.count($workingSet).'<br>';
if( !is_null($workingSet) )
{
	foreach($workingSet as $target)
	{
		$values[] = "( {$target['field_id']}, {$target['version_node_id']}, '{$target['value']}' )";
		$victims[$target['node_id']] = $target['node_type_id'];
	}

	$sql = " INSERT INTO {$tables['cfield_design_values']} (field_id,node_id,value) VALUES ";
	$vSet = implode(',',$values);
	
	$sql .= $vSet;
	$db->exec_query($sql);
	foreach($victims as $node_id => $node_type_id)
	{
		$sql = " DELETE FROM {$tables['cfield_design_values']} WHERE node_id = $node_id "; 
	    $db->exec_query($sql);
	}
}

/**
 * Checks the user rights for accessing the page
 * 
 * used as parameter in testlinkInitPage
 *
 * @param $db resource the database connection handle
 * @param $user tlUser the object of the current user
 *
 * @return boolean return true if the page can be viewed, false if not
 */
function checkRights(&$db,&$user)
{
	$hasRights = ($user->globalRole->name=='admin');
	return $hasRights;
}
?>