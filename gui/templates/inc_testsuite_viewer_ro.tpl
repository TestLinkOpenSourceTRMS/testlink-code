{* 
TestLink Open Source Project - http://testlink.sourceforge.net/ 
$Id: inc_testsuite_viewer_ro.tpl,v 1.1 2006/02/27 07:45:14 franciscom Exp $

20060225 - franciscom 
*}
<table class="simple" style="width: 90%">
<tr><th>{lang_get s='component'}: {$container_data.name|escape}</th></tr>

<tr><td>
<fieldset><legend class="legend_container">{lang_get s='details'}</legend>
{$container_data.details}
</td></tr>
<tr><td class="bold">&nbsp;</td></tr>
{* ---------------------------------------------------------------------- *}
</table>
