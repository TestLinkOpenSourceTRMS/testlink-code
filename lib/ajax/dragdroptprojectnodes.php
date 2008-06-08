<?php
/** 
* 	TestLink Open Source Project - http://testlink.sourceforge.net/
* 
* 	@version 	$Id: dragdroptprojectnodes.php,v 1.1 2008/06/08 09:28:47 franciscom Exp $
* 	@author 	Francisco Mancardi
* 
*   manage drag and drop on test project tree
*
*   rev: 20080605 - franciscom
*        
*/
require_once('../../config.inc.php');
require_once('common.php');
testlinkInitPage($db);


$exclude_node_types=array('testplan' => 1, 'requirement' => 1, 'requirement_spec' => 1);

$args=init_args();
$treeMgr = new tree($db);

echo $args->doAction;
switch($args->doAction)
{
    case 'changeParent':
        $treeMgr->change_parent($args->nodeid,$args->newparentid);
    break;

    case 'doReorder':
        echo 'args->doAction';
        $dummy=explode(',',$args->nodelist);
        $treeMgr->change_order_bulk($dummy);
    break;
}




function init_args()
{
    $args=new stdClass();
    
    $key2loop=array('nodeid','newparentid','doAction',
                    'top_or_bottom','nodeorder','nodelist');
    
    foreach($key2loop as $key)
    {
        $args->$key=isset($_REQUEST[$key]) ? $_REQUEST[$key] : null;   
    }
    return $args;
}
?>

                                                                                              