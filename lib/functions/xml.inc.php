<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later.
 *  
 * Filename $RCSfile: xml.inc.php,v $
 *
 * @version $Revision: 1.10 $
 * @modified $Date: 2009/01/06 15:34:06 $ by $Author: franciscom $
 *
 * Scope: support for XML
 * 
 * Revisions:
 *	20081027 - martin - exportKeywordDataToXML moved here
 *
 * ----------------------------------------------------------------------------------- */

/*
  function: 

  args:
  
  returns: 

*/
function exportDataToXML($items,$rootTpl,$elemTpl,$elemInfo,$bNoXMLHeader = false)
{
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

/*
  function: 

  args:
  
  returns: 

*/
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
 *
 * @param type $keywords the keywords to export in the form
 * 				 keywordData[$i]['keyword'] => the keyword itself
 * 				 keywordData[$i]['notes'] => the notes of keyword
 *
 * @return strings the generated XML Code
 **/
//SCHLUNDUS: will soon be removed
// martin: moved from deleted keywords.inc.php; not used anywhere; I guess it could be removed
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