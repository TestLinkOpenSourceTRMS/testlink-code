<?

////////////////////////////////////////////////////////////////////////////////
//File:     refreshLeft.php
//Author:   Chad Rosen
//Purpose:  This function forces a refresh on the left frame.
////////////////////////////////////////////////////////////////////////////////

function refreshFrame($page)
{


	echo "<script language='javascript'>";
	
	echo "if(parent.left){";

	echo "parent.left.window.location.href='" . $page . "';";

	echo "}";

	echo "</script>";


}

?>
