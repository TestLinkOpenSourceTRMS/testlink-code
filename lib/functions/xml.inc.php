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
?>