{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: show_exec_notes.tpl,v 1.1 2006/12/31 18:20:49 franciscom Exp $
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
				<textarea disabled="disabled" class="tcDesc" name='notes' 
					cols=50 rows=10>{$notes|escape}</textarea>			
			</td>
		</tr>	
	</table>
		<div class="groupBtn">
			<input type="button" value="{lang_get s='btn_close'}" onclick="window.close()" />
		</div>
	</form>
</div>

</body>
</html>