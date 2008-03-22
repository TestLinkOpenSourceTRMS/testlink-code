{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: execNotes.tpl,v 1.1 2008/03/22 15:48:09 franciscom Exp $
Purpose: smarty template - template for show execution notes 
*}
{include file="inc_head.tpl"}
<body>
<h1>{lang_get s='title_execution_notes'}</h1>
<div class="workBack">
	<form method="post">
	<table border="0" width="100%">
		<tr>
			<td rowspan="2" align="center">
      {$notes}
			</td>
		</tr>	
	</table>
		<div class="groupBtn">
		  <input type="hidden" name="doAction" value="doUpdate">
			<input type="submit" value="{lang_get s='btn_save'}"/>
			<input type="button" value="{lang_get s='btn_close'}" onclick="window.close()" />
		</div>
	</form>
</div>
</body>
</html>