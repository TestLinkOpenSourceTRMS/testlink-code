<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/ */
/* $Id: containerComp.inc.php,v 1.1 2005/08/29 06:40:57 franciscom Exp $ */
/* Purpose:  This page manages all the editing of test specification containers. */
/*
 *
 * @author: francisco mancardi - 20050825
 * fckeditor
 * refactoring
 *
 * @author: francisco mancardi - 20050820
 * added missing control con category name length
 *
 * @author: francisco mancardi - 20050810
 * deprecated $_SESSION['product'] removed
*/
function viewer_edit_new_com($amy_keys, $oFCK, $action, $productID, $id=null)
{

 $a_tpl = array( 'editCOM' => 'containerEdit.tpl',
                 'newCOM'  => 'containerNew.tpl');
                 
 $the_tpl = $a_tpl[$action];
              
 
 $component_name='';
 $smarty = new TLSmarty;
 $smarty->assign('sqlResult', null);
 
 $smarty->assign('containerID',$productID);	 
 
 if ($action ==  'editCOM' )
 {
  $the_data = getComponent($id);
  $component_name=$the_data['name'];
  $smarty->assign('containerID',$id);	
 }

 // fckeditor 
 foreach ($amy_keys as $key)
 {
  	// Warning:
  	// the data assignment will work while the keys in $the_data are identical
  	// to the keys used on $oFCK.
  	// side note: I love associative arrays !!!!! (fm)
  	//
  	$of = &$oFCK[$key];
  	$of->Value = $the_data[$key];
  	$smarty->assign($key, $of->CreateHTML());
	}
	
	$smarty->assign('level', 'component');
	$smarty->assign('name',$component_name);
	$smarty->assign('container_data',$the_data);

	$smarty->display($the_tpl);

}


// 
function copy_or_move_comp( $action, $compID, $prodID ,$hash)
{


$dest_prodID = isset($hash['containerID']) ? intval($hash['containerID']) : 0;
$result = 0;
$update = null;	

echo "<pre>\$hash"; print_r($hash); echo "</pre>";

echo "<pre>\$prodID"; print_r($prodID); echo "</pre>";
echo "<pre>\$compID"; print_r($compID); echo "</pre>";
echo "<pre>\$dest_prodID"; print_r($dest_prodID); echo "</pre>";


if( $action == 'componentCopy')
{
	$update = 'update';
	$nested = isset($hash['nested']) ? $hash['nested'] : "no";
	if ($dest_prodID)
	{
		$result = copyComponentToProduct($dest_prodID, $compID, $nested);
	}
}
else if( $action == 'componentMove')
{
	if ($dest_prodID)
	{
		$result = moveComponentToProduct($dest_prodID, $compID);
	}	
}

showProduct($prodID, $result,$update,$dest_prodID);
}
?>