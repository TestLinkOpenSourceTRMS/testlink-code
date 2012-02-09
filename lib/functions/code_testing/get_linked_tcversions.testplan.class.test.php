<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource	get_linked_tcversions.testplan.class.test.php
 * @author Francisco Mancardi
 * 
 * @internal revisions
 */

require_once('../../../config.inc.php');
require_once('common.php');
testlinkInitPage($db);

if( !defined('DBUG_ON') )
{
	define('DBUG_ON',1);
}

$object_item="Testplan Manager";
$object_class="testplan";
$obj_mgr=new $object_class($db);


$tplan_id = 32674;

// ------------------------------------------------------------------------------------
/*
$descr='Get all linked test case versions WITHOUT any kind of filter and/or option';
$lt=$obj_mgr->get_linked_tcversions($tplan_id);
echo '<hr>';
echo $descr . '<br>';
echo 'Qta records:' . count($lt)  . '<br>';
new dBug($lt);
*/
// ------------------------------------------------------------------------------------

// ------------------------------------------------------------------------------------
$descr='Get all linked test case versions';

$filters = array('tcase_id' => 32614);
$opt = array('exclude_info' => array('exec_info','assigned_on_build','priority'));
$lt=$obj_mgr->get_linked_tcversions($tplan_id,$filters,$opt);
echo '<hr>';
echo $descr . '<br>';

if( !is_null($filters) )
{
	echo 'Filters:';
	new dBug($filters);
}

if( !is_null($opt) )
{
	echo 'Options:';
	new dBug($opt);
}
echo 'Qta records:' . count($lt)  . '<br>';
new dBug($lt);
// ------------------------------------------------------------------------------------

// ------------------------------------------------------------------------------------
$descr='Get all linked test case versions';

$filters = array('tcase_id' => 32614, 'assigned_on_build' => 5);
$opt = array('exclude_info' => array('exec_info','priority'));
$lt=$obj_mgr->get_linked_tcversions($tplan_id,$filters,$opt);
echo '<hr>';echo $descr . '<br>';

if( !is_null($filters) )
{
	echo 'Filters:';
	new dBug($filters);
}

if( !is_null($opt) )
{
	echo 'Options:';
	new dBug($opt);
}
echo 'Qta records:' . count($lt)  . '<br>';
new dBug($lt);
// ------------------------------------------------------------------------------------

// ------------------------------------------------------------------------------------
$descr='Get all linked test case versions';

$filters = array('tcase_id' => 32614, 'assigned_on_build' => 5555);
$opt = array('exclude_info' => array('exec_info','priority'));
$lt=$obj_mgr->get_linked_tcversions($tplan_id,$filters,$opt);
echo '<hr>';echo $descr . '<br>';

if( !is_null($filters) )
{
	echo 'Filters:';
	new dBug($filters);
}

if( !is_null($opt) )
{
	echo 'Options:';
	new dBug($opt);
}
echo 'Qta records:' . count($lt)  . '<br>';
new dBug($lt);
// ------------------------------------------------------------------------------------

// ------------------------------------------------------------------------------------
$descr='Get all linked test case versions';

$filters = array('platform_id' => 2, 'assigned_on_build' => 5555);
$opt = array('exclude_info' => array('exec_info','priority'));
$lt=$obj_mgr->get_linked_tcversions($tplan_id,$filters,$opt);
echo '<hr>';echo $descr . '<br>';

if( !is_null($filters) )
{
	echo 'Filters:';
	new dBug($filters);
}

if( !is_null($opt) )
{
	echo 'Options:';
	new dBug($opt);
}
echo 'Qta records:' . count($lt)  . '<br>';
new dBug($lt);
// ------------------------------------------------------------------------------------


// ------------------------------------------------------------------------------------
$tplan_id = 32676;
$descr='Get all linked test case versions';

$filters = array('platform_id' => 9, 'assigned_on_build' => 5555);
$opt = array('exclude_info' => array('exec_info','priority'));
$lt=$obj_mgr->get_linked_tcversions($tplan_id,$filters,$opt);
echo '<hr>';echo $descr . '<br>';

if( !is_null($filters) )
{
	echo 'Filters:';
	new dBug($filters);
}

if( !is_null($opt) )
{
	echo 'Options:';
	new dBug($opt);
}
echo 'Qta records:' . count($lt)  . '<br>';
new dBug($lt);
// ------------------------------------------------------------------------------------



?>