<?

////////////////////////////////////////////////////////////////////////////////
//File:     refreshLeft.php
//Author:   Chad Rosen
//Purpose:  This function forces a refresh on the left frame.
////////////////////////////////////////////////////////////////////////////////

function refreshFrame($page)
{



echo <<<END

<script language='javascript'>

if(parent.left)
{

parent.left.window.location.href='$page';

}
</script>

END;

}

?>
