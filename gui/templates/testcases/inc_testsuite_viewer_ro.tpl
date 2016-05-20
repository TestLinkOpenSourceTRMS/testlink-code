{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
@filesource inc_testsuite_viewer_ro.tpl
*}

{$kwView = $gsmarty_href_keywordsView|replace:'%s%':$gui->tproject_id}

<table class="simple">
	<tr>
		<th colspan="2">{$labels.test_suite}{$tlCfg->gui_title_separator_1}{$gui->container_data.name|escape}</th>
	</tr>
	<tr>
		<td colspan="2">
			<fieldset class="x-fieldset x-form-label-left">
			<legend class="legend_container">{$labels.details}</legend>
			{if $gui->testDesignEditorType == 'none'}{$gui->container_data.details|nl2br}{else}{$gui->container_data.details}{/if}
			
			</fieldset>
		</td>
	</tr>
		
	{* ----- keywords -------------------------------------- *}
	<tr>
	  	<td style="width: 20%">
    		<a href={$kwView}>{$labels.keywords}</a>{$tlCfg->gui_title_separator_1}
    	</td>
    	<td>
    	  	{foreach item=keyword_item from=$gui->keywords_map}
    		    {$keyword_item|escape}<br />
    		{foreachelse}
    		    {$labels.none}
    		{/foreach}
    	</td>
	</tr>

	{* ------ custom fields ------------------------------------- *}
	<tr>
	  <td colspan="2">
  	{$gui->cf}
  	  </td>
	</tr>

</table>
