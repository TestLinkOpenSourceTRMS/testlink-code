{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: inc_comp_viewer_ro.tpl,v 1.1 2005/08/29 06:45:54 franciscom Exp $ *}
{* Purpose: smarty template - create containers *}
{* I18N: 20050528 - fm *}
	<table class="simple" style="width: 90%">
	<tr><th>{lang_get s='component'}: {$container_data.name|escape}</th></tr>
	
	<tr><td>
	<fieldset><legend class="legend_container">{lang_get s='introduction'}</legend>
	{$container_data.intro}
	</td></tr>
	<tr><td class="bold">&nbsp;</td></tr>
	{* ---------------------------------------------------------------------- *}
	
	{* ---------------------------------------------------------------------- *}
	<tr><td>
	<fieldset><legend class="legend_container">{lang_get s='scope'}</legend>
	{if $container_data.scope ne ''}
	    {$container_data.scope}
	{else}
	    {lang_get s='not_defined'}
  {/if}
  </fieldset>  
  </td></tr>
  <tr><td class="bold">&nbsp;</td></tr>
  {* ---------------------------------------------------------------------- *}  
	
	{* ---------------------------------------------------------------------- *}  
	<tr><td>
	<fieldset><legend class="legend_container">{lang_get s='references'}</legend>
	{$container_data.ref}
	</fieldset>  
  </td></tr>
  <tr><td class="bold">&nbsp;</td></tr>
  {* ---------------------------------------------------------------------- *}  
	
  {* ---------------------------------------------------------------------- *}  
	<tr><td>
	<fieldset><legend class="legend_container">{lang_get s='methodology'}</legend>
  {$container_data.method}
	</fieldset>  
  </td></tr>
  <tr><td class="bold">&nbsp;</td></tr>
  {* ---------------------------------------------------------------------- *}  

  {* ---------------------------------------------------------------------- *}  
	<tr><td>
	<fieldset><legend class="legend_container">{lang_get s='limitations'}</legend>
  {$container_data.lim}
  </fieldset>  
  </td></tr>
  {* ---------------------------------------------------------------------- *}  
	</table>
