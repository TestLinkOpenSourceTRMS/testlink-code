<?php
/** 
* 	TestLink Open Source Project - http://testlink.sourceforge.net/
* 
* 	@version 	$Id: dragdroptreenodes.php,v 1.2 2009/12/08 14:43:05 franciscom Exp $
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
    
    $key2loop=array('nodeid','newparentid','doAction',
                    'top_or_bottom','nodeorder','nodelist');
    
    foreach($key2loop as $key)
    {
        $args->$key=isset($_REQUEST[$key]) ? $_REQUEST[$key] : null;   
    }
    return $args;
}
?>                                                                                              