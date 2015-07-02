<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource  tree.class.test.php
 * @author Francisco Mancardi
 *
 * 
 *
 * @internal revisions
 *
 */

require_once('../../../config.inc.php');
require_once('common.php');
require_once('tree.class.php');


testlinkInitPage($db);

echo "<pre> tree - constructor - tree(&\$db)";echo "</pre>";
$tree_mgr=new tree($db);
new dBug($tree_mgr);

echo "<pre> tree - getNodeByAttributes()";echo "</pre>";
$xx = $tree_mgr->getNodeByAttributes(array('type' => 'testproject','name' => 'ISSUE-5429'));
new dBug($xx);

$xx = $tree_mgr->getNodeByAttributes(array('type' => 'testplan','name' => 'AKA','parent_id' => 5675));
new dBug($xx);

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
?>