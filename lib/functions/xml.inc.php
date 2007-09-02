<?php
function exportDataToXML($items,$rootTpl,$elemTpl,$elemInfo,$bNoXMLHeader = false)
{
	if (!$items)
		return;
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
		$result .= TL_XMLEXPORT_HEADER."\n";
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
?>