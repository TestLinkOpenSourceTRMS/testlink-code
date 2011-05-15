{*
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource	resultsTCVSV.tpl
@author		Francisco Mancardi

*}
{lang_get var="labels" s='build,platform,submit_query'}


{$cfg_section=$smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$cfg_section}

{include file="inc_head.tpl"}
{include file="inc_ext_js.tpl" bResetEXTCss=1}

<body>
<h1 class="title"> {$labels.query_metrics_report}</h1>
<div class="workBack">
{include file="inc_result_tproject_tplan.tpl" 
         arg_tproject_name=$gui->tproject_name arg_tplan_name=$gui->tplan_name}	
<form action="lib/results/resultsTCCSV.php?format={$gui->report_type}" method="post">
  <input type="hidden" id="tplan_id" name="tplan_id" value="{$gui->tplan_id}" />
  <input type="hidden" id="tproject_id" name="tproject_id" value="{$gui->tproject_id}" />
  <div>
	<table style="text-align: center; margin-left: 0px;">

		{if $gui->show_platforms}			
		<tr>
			<td>{$labels.platform}</td>
			<td>
				<select name="platform_id" id="platform_id">
					{foreach key=platformid item=platform from=$gui->platformSet}
						<option value="{$platformid}" 
								selected="selected">{$platform|escape}</option>
					{/foreach}
				</select>
            </td>
		</tr>
		{else}
  			<input type="hidden" id="platform_id" name="platform_id" value="0" />
		{/if}			

		<tr>
			<td>{$labels.build}</td>
			<td>
				<select name="build_id" id="build_id">
					{foreach key=row item=buildid from=$gui->buildSet}
						<option value="{$gui->buildSet[$row].id}" 
								selected="selected">{$gui->buildSet[$row].name|escape}</option>
					{/foreach}
				</select>
            </td>
		</tr>
		<tr>
		<td>
			<input type="submit" id="doReport" name="doReport" value="{$labels.submit_query}"/>
		</td>
		</tr>

	    </table>
    </div>
</div>
</form>
</div>
</body>
</html>