{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: inc_testsuite_viewer_ro.tpl,v 1.3 2008/06/12 11:57:03 havlat Exp $

20060805 - franciscom - added keywords management
20080606 - havlatm - refactorization
*}
<table class="simple" style="width: 90%">
	<tr>
		<th colspan="2">{lang_get s='test_suite'}{$tlCfg->gui_title_separator_1}{$container_data.name|escape}</th>
	</tr>
	<tr>
		<td colspan="2">
			<fieldset class="x-fieldset x-form-label-left">
			<legend class="legend_container">{lang_get s='details'}</legend>
			{$container_data.details}
			</fieldset>
		</td>
	</tr>
		
	{* ----- keywords -------------------------------------- *}
	<tr>
	  	<td style="width: 20%">
    		<a href={$gsmarty_href_keywordsView}>{lang_get s='keywords'}</a>{$tlCfg->gui_title_separator_1}
    	</td>
    	<td>
    	  	{foreach item=keyword_item from=$keywords_map}
    		    {$keyword_item|escape}<br />
    		{foreachelse}
    		    {lang_get s='none'}
    		{/foreach}
    	</td>
	</tr>

	{* ------ custom fields ------------------------------------- *}
	<tr>
	  <td colspan="2">
  	{$cf}
  	  </td>
	</tr>

</table>
