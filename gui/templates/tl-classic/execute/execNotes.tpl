{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: execNotes.tpl,v 1.3 2008/08/27 06:20:06 franciscom Exp $
Purpose: smarty template - template for show execution notes 

CHECK NEEDED - Probably this code is OBSOLETE and not used anymore.
*}
{include file="inc_head.tpl" editorType=$editorType}
<body>
<h1 class="title">{lang_get s='title_execution_notes'}</h1>
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