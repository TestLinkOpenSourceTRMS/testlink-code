<?php
/** 
* TestLink Open Source Project - http://testlink.sourceforge.net/ 
* This script is distributed under the GNU General Public License 2 or later. 
*
* Filename $RCSfile: keywords.inc.php,v $
* 
* @version $Id: keywords.inc.php,v 1.34 2007/12/05 21:25:15 schlundus Exp $
* @modified $Date: 2007/12/05 21:25:15 $ by $Author: schlundus $
*
* Functions for support keywords management. 
**/
/**
 * Exports the given keywords to a XML file
 *
 * @param type $keywords the keywords to export in the form
 * 				 keywordData[$i]['keyword'] => the keyword itself
 * 				 keywordData[$i]['notes'] => the notes of keyword
 *
 * @return strings the generated XML Code
 **/
//SCHLUNDUS: will soon be removed
function exportKeywordDataToXML($keywords,$bNoHeader = false)
{
	$keywordRootElem = "<keywords>{{XMLCODE}}</keywords>";
	$keywordElemTpl = "\t".'<keyword name="{{NAME}}"><notes><![CDATA['."\n||NOTES||\n]]>".'</notes></keyword>'."\n";
	$keywordInfo = array (
							"{{NAME}}" => "keyword",
							"||NOTES||" => "notes",
						);
	return exportDataToXML($keywords,$keywordRootElem,$keywordElemTpl,$keywordInfo,$bNoHeader);
}
?>
