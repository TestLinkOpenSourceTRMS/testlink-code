{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: inc_testsuite_viewer_ro.tpl,v 1.4 2006/12/31 16:21:45 franciscom Exp $

20060805 - franciscom - added keywords management
*}
<table class="simple" style="width: 90%">
	<tr>
		<th>{lang_get s='component'}: {$container_data.name|escape}</th>
	</tr>
	<tr>
		<td>
			<fieldset><legend class="legend_container">{lang_get s='details'}</legend>
			{$container_data.details}
			</fieldset>
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	
	<tr>
	  <td>
  	{$cf}
  	</td>
	</tr>
	
	
	{* ------------------------------------------- *}
	{* 20060805 - franciscom - keywords management *}
	<tr>
	  <td>
    	<table cellpadding="0" cellspacing="0" style="font-size:100%;">
    	  <tr>
    	  	<td width="35%"><a href={$gsmarty_href_keywordsView}>{lang_get s='keywords'}</a>: &nbsp;
    			</td>
    		<td>
    		  	{foreach item=keyword_item from=$keywords_map}
    			    {$keyword_item|escape}
    			    <br />
    			{/foreach}
    		</td>
    	</tr>
    	</table>
	  </td>	
	</tr>
	{* ------------------------------------------- *}

</table>
