{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: inc_testsuite_viewer_ro.tpl,v 1.4 2010/01/02 18:19:34 franciscom Exp $

20060805 - franciscom - added keywords management
20080606 - havlatm - refactorization
*}
<table class="simple">
	<tr>
		<th colspan="2">{$labels.test_suite}{$tlCfg->gui_title_separator_1}{$gui->container_data.name|escape}</th>
	</tr>
	<tr>
		<td colspan="2">
			<fieldset class="x-fieldset x-form-label-left">
			<legend class="legend_container">{$labels.details}</legend>
			{$gui->container_data.details}
			</fieldset>
		</td>
	</tr>
		
	{* ----- keywords -------------------------------------- *}
	<tr>
	  	<td style="width: 20%">
    		<a href={$gsmarty_href_keywordsView}>{$labels.keywords}</a>{$tlCfg->gui_title_separator_1}
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
