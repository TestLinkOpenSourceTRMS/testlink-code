{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: editExecution.tpl,v 1.3 2010/10/08 12:33:28 asimon83 Exp $
Authot: francisco.mancardi@gmail.com

Purpose:  
*}
{include file="inc_head.tpl" editorType=$gui->editorType}
<body onUnload="storeWindowSize('ExecEditPopup')">
<h1 class="title">{lang_get s='title_execution_notes'}</h1>
<div class="workBack">
	<form method="post">
		{* memory *}
		<input type="hidden" name="tplan_id" value="{$gui->tplan_id}">
		<input type="hidden" name="tproject_id" value="{$gui->tproject_id}">
		<input type="hidden" name="exec_id" value="{$gui->exec_id}">
		<input type="hidden" name="tcversion_id" value="{$gui->tcversion_id}">
		
		<table width="100%">
		<tr>
			<td>
	      		{$gui->notes}
			</td>
		</tr>	
	    {if $gui->cfields_exec neq ''}
	  	<tr>
	  	  	<td colspan="2">
	  	  		<div id="cfields_exec" class="custom_field_container" 
	  	  			style="background-color:#dddddd;">{$gui->cfields_exec}
	  	  		</div>
	  	  	</td>
	  	</tr>
	    {/if}
		
		</table>
		<div class="groupBtn">
			<input type="hidden" name="doAction" value="doUpdate" />
			<input type="submit" value="{lang_get s='btn_save'}" />
			<input type="button" value="{lang_get s='btn_close'}" onclick="window.close()" />
		</div>
	</form>
</div>
</body>
</html>