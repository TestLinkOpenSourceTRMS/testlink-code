<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Filename $RCSfile: tree.class.test.php,v $
 *
 * @version $Revision: 1.4 $
 * @modified $Date: 2010/01/02 16:54:34 $ by $Author: franciscom $
 * @author Francisco Mancardi
 *
 * 
 *
 * rev :
*/

require_once('../../../config.inc.php');
require_once('common.php');
require_once('tree.class.php');


testlinkInitPage($db);

echo "<pre> tree - constructor - tree(&\$db)";echo "</pre>";
$tree_mgr=new tree($db);
new dBug($tree_mgr);

echo "<pre> tree - get_available_node_types()";echo "</pre>";
$available_node_types = $tree_mgr->get_available_node_types();
new dBug($available_node_types);

echo "<pre> tree - get_node_hierarchy_info(\$node_id)";echo "</pre>";
$node_id=1;
echo "<pre> get_node_hierarchy_info($node_id)";echo "</pre>";
$node_hierachy_info = $tree_mgr->get_node_hierarchy_info($node_id);
new dBug($node_hierachy_info);

echo "<pre> tree - get_subtree(\$node_id)";echo "</pre>";
echo "<pre> get_subtree($node_id)";echo "</pre>";
$subtree = $tree_mgr->get_subtree($node_id);
new dBug($subtree);


echo "<pre> tree - get_subtree(\$node_id,\$exclude_node_types=null," . "<br>" .
"                              \$exclude_children_of=null,\$exclude_branches=null," . "<br>" .
"                              \$and_not_in_clause='',\$bRecursive = false)";echo "</pre>";

echo "<pre> get_subtree($node_id,null,null,null,'',false)";echo "</pre>";
$subtree = $tree_mgr->get_subtree($node_id,null,null,null,'',false);
new dBug($subtree);


echo "<pre> get_subtree($node_id,null,null,null,'',true)";echo "</pre>";
$subtree = $tree_mgr->get_subtree($node_id,null,null,null,'',true);
new dBug($subtree);


echo "<pre> tree - get_subtree_list(\$node_id)";echo "</pre>";
echo "<pre> get_subtree_list($node_id)";echo "</pre>";
$subtree_list = $tree_mgr->get_subtree_list($node_id);
new dBug($subtree_list);

$path_begin_node_id=285;
$path_end_node_id=2;
define('TREE_ROOT',null);
define('FORMAT_FULL','full');
define('FORMAT_SIMPLE','simple');

echo "<pre> tree - get_path(\$node_id,\$to_node_id = null,\$format = 'full') ";echo "</pre>";
echo "<pre> tree - get_path($path_begin_node_id) ";echo "</pre>";
$path=$tree_mgr->get_path($path_begin_node_id); 
new dBug($path);


echo "<pre> tree - get_path(\$node_id,\$to_node_id = null,\$format = 'full') ";echo "</pre>";
echo "<pre> tree - get_path($path_begin_node_id,TREE_ROOT,FORMAT_FULL) ";echo "</pre>";
$path=$tree_mgr->get_path($path_begin_node_id,TREE_ROOT,FORMAT_FULL); 
new dBug($path);

echo "<pre> tree - get_path($path_begin_node_id,TREE_ROOT,FORMAT_SIMPLE) ";echo "</pre>";
$path=$tree_mgr->get_path($path_begin_node_id,TREE_ROOT,FORMAT_SIMPLE); 
new dBug($path);


echo "<pre> tree - get_path($path_begin_node_id,$path_end_node_id,FORMAT_FULL) ";echo "</pre>";
$path=$tree_mgr->get_path($path_begin_node_id,$path_end_node_id,FORMAT_FULL); 
new dBug($path);

$node_id=1;
echo "<pre> tree - get_children(\$node_id)";echo "</pre>";
echo "<pre> get_children($node_id)";echo "</pre>";
$children = $tree_mgr->get_children($node_id);
new dBug($children);


echo "<pre> tree - get_node_hierarchy_info(\$node_id) ";echo "</pre>";
echo "<pre> get_node_hierarchy_info($node_id) ";echo "</pre>";
$node_hierachy_info=$tree_mgr->get_node_hierarchy_info($node_id) ;
new dBug($node_hierachy_info);


/*
function tree(&$db) 
function get_available_node_types() 
function new_root_node($name = '') 
function new_node($parent_id,$node_type_id,$name='',$node_order=0,$node_id=0) 
function get_node_hierarchy_info($node_id) 
function get_subtree_list($node_id)
function _get_subtree_list($node_id,&$node_list)
function delete_subtree($node_id)
function get_path($node_id,$to_node_id = null,$format = 'full') 
function _get_path($node_id,&$node_list,$to_node_id=null,$format='full') 
function change_parent($node_id, $parent_id) 
function get_children($id,$exclude_node_types=null) 
function change_order_bulk($hash_node_id, $hash_node_order) 
function change_order_bulk_new($nodes) 
function get_subtree($node_id,$exclude_node_types=null,
function _get_subtree($node_id,&$node_list,$and_not_in_clause='',
function _get_subtree_rec($node_id,&$pnode,$and_not_in_clause = '',
*/





?>
