<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later.
 *  
 * support for XML
 * 
 * @package 	TestLink
 * @copyright 	2004-2009, TestLink community 
 * @version    	CVS: $Id: xml.inc.php,v 1.13 2010/02/10 19:21:00 franciscom Exp $
 * @link 		http://www.teamst.org/index.php
 *
 * @internal Revisions:
 * 
 *	20081027 - martin - exportKeywordDataToXML moved here
 *
 */


function exportDataToXML($items,$rootTpl,$elemTpl,$elemInfo,$bNoXMLHeader = false)
{

    // echo __FUNCTION__; echo 'items <br>'; new dBug($items);
	if (!$items)
	{
		return;
	}

	$xmlCode = '';
	reset($items);
	while($item = each($items))
	{
		$item = $item[1];
		$xmlElemCode = $elemTpl;
		
		// REMEMBER YOU NEED TO USE XMP TO DEBUG
		// echo '$xmlElemCode'; echo "<xmp>$xmlElemCode)</xmp>";
		// echo '$elemInfo'; new dBug($elemInfo);
		
		foreach($elemInfo as $subject => $replacement)
		{
			$fm = substr($subject,0,2);
			$content = isset($item[$replacement]) ? $item[$replacement] : null;
			switch($fm)
			{
				case '||':
					break;

				case '{{':
				default:
					$content = htmlspecialchars($content);
					break;
			}
			
			$xmlElemCode = str_replace($subject,$content,$xmlElemCode);
			// echo '$subject:' . $subject . '<br>';
			// echo '$replacement key:' . $replacement . '<br>';
            // echo "<xmp>$xmlElemCode)</xmp>";
		}
		$xmlCode .= $xmlElemCode;
	}
	reset($items);
	
	$result = null;
	if (!$bNoXMLHeader)
	{
		$result .= TL_XMLEXPORT_HEADER."\n";
	}
	
	$result .= str_replace("{{XMLCODE}}",$xmlCode,$rootTpl);
	return $result;
}


function getNodeContent(&$node,$tag)
{
	if (!$node)
		return null;
	$nodes = $node->get_elements_by_tagname($tag);
	if ($nodes)
	{
		return $nodes[0]->get_content();
	}
	return null;
}


/**
 * Exports the given keywords to a XML file
 * 
 * @param type $keywords the keywords to export in the form
 * 				 keywordData[$i]['keyword'] => the keyword itself
 * 				 keywordData[$i]['notes'] => the notes of keyword
 *
 * @return strings the generated XML Code
 * @todo SCHLUNDUS: will soon be removed
 * 		martin: moved from deleted keywords.inc.php; not used anywhere; I guess it could be removed
 */
// function exportKeywordDataToXML($keywords,$bNoHeader = false)
// {
// 	$keywordRootElem = "<keywords>{{XMLCODE}}</keywords>";
// 	$keywordElemTpl = "\t".'<keyword name="{{NAME}}"><notes><![CDATA['."\n||NOTES||\n]]>".'</notes></keyword>'."\n";
// 	$keywordInfo = array("{{NAME}}" => "keyword","||NOTES||" => "notes");
// 	return exportDataToXML($keywords,$keywordRootElem,$keywordElemTpl,$keywordInfo,$bNoHeader);
// }
?>