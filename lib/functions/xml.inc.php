<?php
function exportDataToXML($items,$rootTpl,$elemTpl,$elemInfo)
{
	$xmlCode = '';
	for($i = 0;$i < sizeof($items);$i++)
	{
		$item = $items[$i];
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
	$xmlCode = TL_XMLEXPORT_HEADER."\n".str_replace("{{XMLCODE}}",$xmlCode,$rootTpl);
	return $xmlCode;
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