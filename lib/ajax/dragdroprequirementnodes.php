<?php
/** 
* 	TestLink Open Source Project - http://testlink.sourceforge.net/
* 
* 	@version 	$Id: dragdroprequirementnodes.php,v 1.3 2009/12/08 14:41:57 franciscom Exp $
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

        // 20080831 - franciscom
        // This will be removed in future, when parent / child relationship
        // will be manage only on nodes_hierarchy table
        // NEED to talk Martin ASAP
        $sql=" UPDATE " . DB_TABLE_PREFIX . "requirements " .
             " SET srs_id={$args->newparentid} " .
             " WHERE id={$args->nodeid}";
        //file_put_contents('d:\dragdroprequirementnodes.php.txt', $sql);                                 
        $db->exec_query($sql);
             
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