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
			$xmlElemCode = str_replace($subject,htmlspecialchars($item[$replacement]),$xmlElemCode);	 
		}
		$xmlCode .= $xmlElemCode;
	}
	$xmlCode = TL_XMLEXPORT_HEADER."\n".str_replace("{{XMLCODE}}",$xmlCode,$rootTpl);
	
	return $xmlCode;
}
?>