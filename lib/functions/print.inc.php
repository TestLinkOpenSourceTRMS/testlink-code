<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *  
 * @filesource $RCSfile: print.inc.php,v $
 * @version $Revision: 1.12 $
 * @modified $Date: 2006/04/07 20:15:26 $ by $Author: schlundus $
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
function getAuthor(&$db,$userID)
{
    $sql = "SELECT first,last,login FROM users WHERE id=" . $userID;
    $result = $db->exec_query($sql);
    $myrow = $db->fetch_array($result);
    
    $ret_val = $myrow['first'] . ' ' . $myrow['last'];
    if (strlen(trim($ret_val)) == 0 )
    {
    	$ret_val = $myrow['login'];
    }	
    return $ret_val; 
}

/** 
 * print HTML header 
 * Standard: HTML 4.01 trans (because is more flexible to bugs in user data)
 *
 * 20050905 - fm - added argument base_href
 */
function printHeader($title, $base_href, $cssTemplate = TL_DOC_BASIC_CSS)
{
	$output = "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>\n";
	$output .= "<html>\n<head>\n";
	$output .= '<meta http-equiv="Content-Type" content="text/html; charset='.TL_TPL_CHARSET.'" />';
	$output .= '<title>' . htmlspecialchars($title). "</title>\n";
	$output .= '<link type="text/css" rel="stylesheet" href="' . $base_href . $cssTemplate . '" />';
	$output .= "\n</head>\n<body>\n";

	return $output;
}

/** 
  print HTML - initial page of document 

  20060102 - fm - product notes
*/
function printFirstPage(&$db,$title, $prodName, $prodNotes, $userID)
{
	$g_date_format = config_get('date_format');
	$prodName = htmlspecialchars($prodName);
	$author = htmlspecialchars(getAuthor($db,$userID));
	$title = htmlspecialchars($title);
	
	$output = '<div class="pageheader">';
	$output .= '<span style="float: right;">'. $prodName ."</span>";
	if (TL_COMPANY != '')
		$output .= '<span>'. htmlspecialchars(TL_COMPANY) ."</span>\n";
	
	$output .= "</div>\n";
	$output .= "<h1>".$title."</h1>\n";
	$output .= "<div style='margin: 50px;'>" .
		       "<p>". lang_get('product').": " . $prodName . "</p>";
	if (strlen($prodNotes))
		$output .= "<p>". $prodNotes . "</p>";
		       
	$output .= "<p>".lang_get('author').": " . $author . "</p>" .
		       "<p>".lang_get('printed_by_TestLink_on')." ". strftime($g_date_format, time()) . "</p></div>";

	if (TL_DOC_COPYRIGHT != '')
		$output .= '<div class="pagefooter">'.htmlspecialchars(TL_DOC_COPYRIGHT)."</div>\n";
	if (TL_DOC_CONFIDENT != '')
		$output .= '<div class="pagefooter">'.htmlspecialchars(TL_DOC_CONFIDENT)."</div>\n";

	return $output;
}
?>