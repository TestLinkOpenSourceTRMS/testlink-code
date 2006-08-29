<?php
function exportDataToXML($items,$rootTpl,$elemTpl,$elemInfo,$bNoXMLHeader = false)
{
	$xmlCode = '';
	reset($items);
	while($item = each($items))
	{
		$item = $item[1];
		$xmlElemCode = $elemTpl;
		foreach($elemInfo as $subject => $replacement)
		{
			$fm = substr($subject,0,2);
			$content = $item[$replacement];
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
	$nodes = $node->get_elements_by_tagname($tag);
	if ($nodes)
	{
		$tnode = $nodes[0]->first_child();
		if ($tnode)
			return $tnode->node_value();
	}
	return null;
}
?>