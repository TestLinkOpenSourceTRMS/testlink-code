<?php
/* TestLink Open Source Project - http://testlink.sourceforge.net/ */
/* $Id: containerCat.inc.php,v 1.1 2005/08/29 06:40:57 franciscom Exp $ */
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
function viewer_edit_new_cat($amy_keys, $oFCK, $action, $componentID, $id=null)
{
 $a_tpl = array('editCat' => 'containerEdit.tpl',
                'newCAT'  => 'containerNew.tpl');
                
 $the_tpl = $a_tpl[$action];

 $the_data = array();
 $category_name ='';


 $smarty = new TLSmarty;
 $smarty->assign('sqlResult', null);
 $smarty->assign('containerID',$componentID);	 
  
 if ($action ==  'editCat' )
 {
  // 20050824 - fm - fckeditor
  $the_data = getCategory($id);
  $category_name = $the_data['name'];
  $smarty->assign('containerID',$id);
 }
 else
 {
   // init
   foreach ($amy_keys as $key)
   {
 	   $the_data[$key] = "";
 	 }
 }

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
	
	$smarty->assign('level', 'category');
	$smarty->assign('name', $category_name);
	$smarty->assign('container_data',$the_data);

	$smarty->display($the_tpl);
}


//
function copy_or_move_cat( $action, $catID, $hash)
{
	
$update = null;
$result = 0;	
$dest_compID    = isset($_POST['containerID']) ? intval($_POST['containerID']) : 0;
$old_compID = isset($_POST['old_containerID']) ? intval($_POST['old_containerID']): 0;
	
	
if ($action == 'categoryCopy')
{	
  $update ='update';
  $nested    = isset($_POST['nested']) ? $_POST['nested'] : "no";
  if ($dest_compID)
  {
  		$result = copyCategoryToComponent($dest_compID, $catID, $nested);
  }
}
else if( $action == 'categoryMove')
{
	if ($dest_compID)
	{
		$result = moveCategoryToComponent($dest_compID, $catID);
	}	
}

showComponent($old_compID, $result,$update,$dest_compID);
}
?>