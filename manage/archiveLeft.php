<?

////////////////////////////////////////////////////////////////////////////////
//File:     archiveLeft.php
//Author:   Chad Rosen
//Purpose:  This page is the left frame of the execution pages. It builds the
//	    javascript trees that allow the user to jump around to any point
//	    on the screen
////////////////////////////////////////////////////////////////////////////////


require_once("../functions/header.php");
doSessionStart();
doDBConnect();
doHeader();

require_once("../functions/stripTree.php"); //require_once the function that strips the javascript tree

?>

<head>

<script language='JavaScript' src='jtree/tree.js'></script>
<script language='JavaScript' src='jtree/tree_tpl.js'></script>
<link rel="stylesheet" href="jtree/tree.css">

</head>

<?

$product = $_SESSION['product'];

if($product)
{

	genManageTree($product, "manage/archiveData.php", 0,1);


}

?>

