{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: inc_result_tproject_tplan.tpl,v 1.2 2008/09/20 21:02:52 schlundus Exp $ *}
<table>
	<tr>
		<td>{lang_get s="testproject"}</td><td>{$smarty.const.TITLE_SEP}</td>
		<td>
			<span style="color:black; font-weight:bold; text-decoration: underline;">{$arg_tproject_name|escape}</span>
		</td>
	</tr>
	<tr>
		<td>{lang_get s="testplan"}</td><td>{$smarty.const.TITLE_SEP}</td>
		<td> 
			<span style="color:black; font-weight:bold; text-decoration:underline;">{$arg_tplan_name|escape}</span>
		</td>
	</tr>
</table>