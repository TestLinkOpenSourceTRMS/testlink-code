{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: inc_testsuite_viewer_ro.tpl,v 1.2 2006/04/29 19:32:54 schlundus Exp $

20060225 - franciscom 
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
</table>
