{* 
Testlink: smarty template - 
$Id: cfields_view.tpl,v 1.2 2007/01/06 15:14:35 franciscom Exp $ 
*}
{include file="inc_head.tpl"}

<body>

<h1>{lang_get s='title_cfields_mgmt'}</h1>

<div class="workBack">

{if $cf_map neq '' }
  <table class="simple" style="width: 90%">
  	<tr>
  		<th>{lang_get s='name'}</th>
  		<th>{lang_get s='label'}</th>
  		<th>{lang_get s='type'}</th>
  		<th>{lang_get s='show_on_design'}</th>
  		<th>{lang_get s='enable_on_design'}</th>
  		<th>{lang_get s='show_on_exec'}</th>
  		<th>{lang_get s='enable_on_exec'}</th>
  		<th>{lang_get s='available_on'}</th>
  	</tr>
  
   	{foreach key=cf_id item=cf_def from=$cf_map}
   	<tr>
   	<td class="bold"><a href="lib/cfields/cfields_edit.php?action=edit&id={$cf_def.id}"
   	                    title="{lang_get s='manage_cfield'}">{$cf_def.name|escape}</a></td>
   	<td>{$cf_def.label|escape}</td>
   	<td>{$cf_types[$cf_def.type]}</td>
   	<td align="center">{if $cf_def.show_on_design eq 1}<img src="icons/apply_f2_16.png">{/if} </td>
   	<td align="center">{if $cf_def.enable_on_design eq 1}<img src="icons/apply_f2_16.png">{/if} </td>
   	<td align="center">{if $cf_def.show_on_execution eq 1}<img src="icons/apply_f2_16.png">{/if} </td>
   	<td align="center">{if $cf_def.enable_on_execution eq 1}<img src="icons/apply_f2_16.png">{/if} </td>
   	<td>{lang_get s=$cf_def.node_description}</td>
   	
   	</tr>
   	{/foreach}
  </table>
{/if} {* $cf_map neq '' *}
  
  <div class="groupBtn">
    <form method="post" action="lib/cfields/cfields_edit.php?action=create">
      <input type="submit" name="create_cfield" value="{lang_get s='btn_cfields_create'}" />
    </form>
  </div>

</div>
</body>
</html>