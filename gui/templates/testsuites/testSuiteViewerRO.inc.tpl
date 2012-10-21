{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
@filesource testSuiteViewerRO.tpl
*}
{lang_get var='labelsRO' 
          s='details,keywords,none'}

<table class="simple">
	<tr>
		<th colspan="2">{$gui->viewerTitle|escape}</th>
	</tr>
	<tr>
		<td colspan="2">
			<fieldset class="x-fieldset x-form-label-left">
			<legend class="legend_container">{$labelsRO.details}</legend>
			{$gui->tsuite.details}
			</fieldset>
		</td>
	</tr>
		
	<tr>
	  	<td style="width: 20%">
    		<a href={$gui->keywordsViewHREF}>{$labelsRO.keywords}</a>{$tlCfg->gui_title_separator_1}
    	</td>
    	<td>
    	{foreach item=keyword_item from=$gui->keywords_map}
    	  {$keyword_item|escape}<br />
      {foreachelse}
    	  {$labelsRO.none}
    	{/foreach}
    	</td>
	</tr>

	<tr>
	  <td colspan="2">
  	{$gui->cf}
  	  </td>
	</tr>
</table>