{* TestLink Open Source Project - http://testlink.sourceforge.net/ *}
{* $Id: inc_cat_viewer_ro_m0.tpl,v 1.1 2005/08/29 06:45:54 franciscom Exp $ *}
{* Purpose: smarty template - create containers *}
{* I18N: 20050528 - fm *}

	<table class="simple" style="width: 90%">
	<tr><th>{lang_get s='category'}: {$container_data.name|escape}</th></tr>
	
		{* ---------------------------------------------------------------------- *}
	<tr><td>
	<fieldset><legend class="legend_container">{lang_get s='cat_scope'}</legend>
	{if $container_data.objective ne ''}
	    {$container_data.objective}
	{else}
	    {lang_get s='not_defined'}
  {/if}
  </fieldset>  
  </td></tr>
  <tr><td class="bold">&nbsp;</td></tr>
  {* ---------------------------------------------------------------------- *}  
	
	{* ---------------------------------------------------------------------- *}  
	<tr><td>
	<fieldset><legend class="legend_container">{lang_get s='configuration'}</legend>
	{$container_data.config}
	</fieldset>  
  </td></tr>
  <tr><td class="bold">&nbsp;</td></tr>
  {* ---------------------------------------------------------------------- *}  
	
  {* ---------------------------------------------------------------------- *}  
	<tr><td>
	<fieldset><legend class="legend_container">{lang_get s='data'}</legend>
  {$container_data.data}
	</fieldset>  
  </td></tr>
  <tr><td class="bold">&nbsp;</td></tr>
  {* ---------------------------------------------------------------------- *}  

  {* ---------------------------------------------------------------------- *}  
	<tr><td>
	<fieldset><legend class="legend_container">{lang_get s='tools'}</legend>
  {$container_data.tools}
  </fieldset>  
  </td></tr>
  {* ---------------------------------------------------------------------- *}  
	</table>
