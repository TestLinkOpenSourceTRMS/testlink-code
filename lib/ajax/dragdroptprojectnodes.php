<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/
* 
* @filesource dragdroptprojectnodes.php
* @author 	  Francisco Mancardi
* 
* manage drag and drop on test project tree
*
* Development Notes:
* This code is called by javascript function writeNodePositionToDB() present
* in javascript file with all function used to manage EXTJS tree.
* This means that when this code is called ALL NEEDED CHECKS to understand
* if operation is allowed HAVE BEEN DONE (at least in theory)
*        
*/
require_once('../../config.inc.php');
require_once('common.php');
testlinkInitPage($db);


// check if this is really needed
$exclude_node_types=array('testplan' => 1, 'requirement' => 1, 'requirement_spec' => 1);

$args=init_args();
$treeMgr = new tree($db);

switch($args->doAction)
{
    case 'changeParent':
        $treeMgr->change_parent($args->nodeid,$args->newparentid);
    break;

    case 'doReorder':
        $dummy=explode(',',$args->nodelist);
        $treeMgr->change_order_bulk($dummy);
    break;
}

function init_args()
{
    $args=new stdClass();
    
    $key2loop=array('nodeid','newparentid','doAction','top_or_bottom','nodeorder','nodelist');
    foreach($key2loop as $key)
    {
      $args->$key=isset($_REQUEST[$key]) ? $_REQUEST[$key] : null;   
    }
    return $args;
}
?>                                                                                             