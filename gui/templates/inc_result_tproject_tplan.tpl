{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource inc_result_tproject_tplan.tpl

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

  {if (isset($arg_build_set) && $arg_build_set != '') }
	{foreach key=idx item=name from=$arg_build_set}
	<tr>
		{if $idx == 0}
		<td>{lang_get s="builds"}</td><td>{$smarty.const.TITLE_SEP}</td>
		{else}
		<td>&nbsp;</td><td>&nbsp;</td>
		{/if}
		<td> 
			<span style="color:black; font-weight:bold; text-decoration:underline;">{$name|escape}</span>
		</td>
	</tr>
	{/foreach}
  {/if}

</table>