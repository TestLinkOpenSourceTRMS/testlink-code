<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *  
 * @filesource $RCSfile: print.inc.php,v $
 * @version $Revision: 1.2 $
 * @modified $Date: 2005/08/16 18:00:55 $
 *
 * @author	Martin Havlat <havlat@users.sourceforge.net>
 * 
 * Functions for support printing of documents. 
 * 
 */
/** @return string First + Last name */
function getAuthor()
{
    $sql = "select first,last from user where id=" . $_SESSION['userID'];
    $result = do_mysql_query($sql);
    $myrow = mysql_fetch_row($result);
    
    if ($myrow[1])
    	return $myrow[0] . ' ' . $myrow[1];
    else
    	return $_SESSION['user'];
}

/** 
 * print HTML header 
 * Standard: HTML 4.01 trans (because is more flexible to bugs in user data)
 */
function printHeader($title, $cssTemplate = 'gui/css/tl_doc_basic.css')
{
	$output = "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>\n";
	$output .= "<html>\n<head>\n";
	$output .= '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
	$output .= '<title>' . $title. "</title>\n";
	$output .= '<link type="text/css" rel="stylesheet" href="' . $_SESSION['basehref'] . $cssTemplate . '" />';
	$output .= "\n</head>\n<body>\n";

	return $output;
}

/** print HTML - initial page of document */
function printFirstPage($title)
{
	$output = '<div class="pageheader">';
	$output .= '<span style="float: right;">'. htmlspecialchars($_SESSION['productName']) ."</span>";
	if (TL_COMPANY != '') {
		$output .= '<span>'. htmlspecialchars(TL_COMPANY) ."</span>\n";
	}
	$output .= "</div>\n";
	$output .= '<h1>'.$title."</h1>\n";
	$output .= "<div style='margin: 50px;'>" .
			"<p>Product: " . htmlspecialchars($_SESSION['productName']) . "</p>" .
			"<p>Author: " . htmlspecialchars(getAuthor()) . "</p>" .
			"<p>Printed by TestLink on " . date('Y-m-d H:i:s', time()) . "</p></div>";
	if (TL_DOC_COPYRIGHT != '') {
		$output .= '<div class="pagefooter">'.htmlspecialchars(TL_DOC_COPYRIGHT)."</div>\n";
	}
	if (TL_DOC_CONFIDENT != '') {
		$output .= '<div class="pagefooter">'.htmlspecialchars(TL_DOC_CONFIDENT)."</div>\n";
	}

	return $output;
}
?>