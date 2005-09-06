<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *  
 * @filesource $RCSfile: print.inc.php,v $
 * @version $Revision: 1.4 $
 * @modified $Date: 2005/09/06 06:44:07 $ by $Author: franciscom $
 *
 * @author	Martin Havlat <havlat@users.sourceforge.net>
 * 
 * Functions for support printing of documents. 
 *
 * 20050830 - fm - refactoring
 * 
 */
/** 

@parameter $userID
@return string First + Last name 
*/
function getAuthor($userID)
{
    $sql = "select first,last,login from user where id=" . $userID;
    $result = do_mysql_query($sql);
    $myrow = mysql_fetch_assoc($result);
    
    $ret_val = $myrow['first'] . ' ' . $myrow['last'];
    if (strlen(trim($ret_val)) == 0 )
    {
    	
    	$ret_val = $myrow['login'];
    }	
    return($ret_val); 
}

/** 
 * print HTML header 
 * Standard: HTML 4.01 trans (because is more flexible to bugs in user data)
 *
 * 20050905 - fm - added argument base_href
 */
function printHeader($title, $base_href, $cssTemplate = 'gui/css/tl_doc_basic.css')
{
	$output = "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>\n";
	$output .= "<html>\n<head>\n";
	$output .= '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
	$output .= '<title>' . $title. "</title>\n";
	$output .= '<link type="text/css" rel="stylesheet" href="' . $base_href . $cssTemplate . '" />';
	$output .= "\n</head>\n<body>\n";

	return $output;
}

/** 
  print HTML - initial page of document 

  20050830 - fm
  added $g_date_format
*/
function printFirstPage($title, $prodName, $userID)
{
	global $g_date_format;
	
	$the_prodName = htmlspecialchars($prodName);
	$output = '<div class="pageheader">';
	$output .= '<span style="float: right;">'. $the_prodName ."</span>";
	if (TL_COMPANY != '') {
		$output .= '<span>'. htmlspecialchars(TL_COMPANY) ."</span>\n";
	}
	$output .= "</div>\n";
	$output .= '<h1>'.$title."</h1>\n";
	$output .= "<div style='margin: 50px;'>" .
			"<p>Product: " . $the_prodName . "</p>" .
			"<p>Author: " . htmlspecialchars(getAuthor($userID)) . "</p>" .
			"<p>Printed by TestLink on " . 	strftime($g_date_format, time()) . "</p></div>";
	if (TL_DOC_COPYRIGHT != '') {
		$output .= '<div class="pagefooter">'.htmlspecialchars(TL_DOC_COPYRIGHT)."</div>\n";
	}
	if (TL_DOC_CONFIDENT != '') {
		$output .= '<div class="pagefooter">'.htmlspecialchars(TL_DOC_CONFIDENT)."</div>\n";
	}

	return $output;
}
?>