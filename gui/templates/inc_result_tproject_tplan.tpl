{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
$Id: inc_result_tproject_tplan.tpl,v 1.3 2008/11/09 16:25:05 franciscom Exp $ 

rev: 20081109 - franciscom - added logic to hide testplan name if empty
*}
<table>
	<tr>
		<td>{lang_get s="testproject"}</td><td>{$smarty.const.TITLE_SEP}</td>
		<td>
			<span style="color:black; font-weight:bold; text-decoration: underline;">{$arg_tproject_name|escape}</span>
		</td>
	</tr>
  {if $arg_tplan_name != ''}
	<tr>
		<td>{lang_get s="testplan"}</td><td>{$smarty.const.TITLE_SEP}</td>
		<td> 
			<span style="color:black; font-weight:bold; text-decoration:underline;">{$arg_tplan_name|escape}</span>
		</td>
	</tr>
	{/if}
</table>