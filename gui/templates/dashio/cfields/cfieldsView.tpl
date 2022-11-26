{* 
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource cfieldsView.tpl
*}
{$tplBN=$smarty.template|basename|replace:".tpl":""}
{config_load file="input_dimensions.conf" section=$tplBN}


{$tproject_id=$gui->tproject_id}
{$tplan_id=$gui->tplan_id}
{$cfCreateAction="lib/cfields/cfieldsEdit.php?do_action=create&tproject_id=$tproject_id&&tplan_id=$tplan_id"}

{* ------------------------------------------------------------------------------- *}
{$cfViewAction="lib/cfields/cfieldsView.php"}
{$cfImportAction="lib/cfields/cfieldsImport.php?tproject_id=$tproject_id&&tplan_id=$tplan_id&goback_url="}
{$importCfieldsAction="$basehref$cfImportAction$basehref$cfViewAction"}

{$cfExportAction="lib/cfields/cfieldsExport.php?tproject_id=$tproject_id&&tplan_id=$tplan_id&goback_url="}
{$exportCfieldsAction="$basehref$cfExportAction$basehref$cfViewAction"}
{* ------------------------------------------------------------------------------- *}


{lang_get var="labels"
          s="name,label,type,title_cfields_mgmt,manage_cfield,btn_cfields_create,
             btn_export,btn_import,btn_goback,sort_table_by_column,
             enabled_on_context,
             display_on_exec,available_on,context"}

{include file="inc_head.tpl" enableTableSorting="yes" openHead="yes"}
{include file="bootstrap.inc.tpl"}

</head>
<body style="background-color: #eaeaea">
{include file="aside.tpl"}  
<div id="main-content">
<h1 class="{#TITLE_CLASS#}">{$labels.title_cfields_mgmt}</h1>

{if $gui->cf_map != '' && $gui->drawControlsOnTop}
  {include file="cfields/{$tplBN}Controls.inc.tpl" suffix="Top"}
{/if}

{if $gui->cf_map != '' }
  <table class="{#item_view_table#}" id="item_view">
    <thead class="{#item_view_thead#}">
      <tr>
        <th width="5%">{$labels.name}</th>
        <th>{$labels.label}</th>
        <th>{$labels.type}</th>
        <th>{$labels.enabled_on_context}</th>
        <th width="5%">{$labels.display_on_exec}</th>
        <th>{$labels.available_on}</th>
      </tr>
    </thead>

    <tbody>
    {foreach key=cf_id item=cf_def from=$gui->cf_map}
      <tr>
      <td width="5%" class="bold"><a href="lib/cfields/cfieldsEdit.php?do_action=edit&cfield_id={$cf_def.id}"
                          title="{$labels.manage_cfield}">{$cf_def.name|escape}</a></td>
      <td width="10%">{$cf_def.label|escape}</td>
      <td width="5%">{$gui->cf_types[$cf_def.type]}</td>
      <td width="5%">{$cf_def.enabled_on_context}</td>
      <td align="center" width="5%">{if $cf_def.show_on_execution}{$tlIMGTags.displayOnExec}{/if}  </td>
      <td width="5%">{lang_get s=$cf_def.node_description}</td>
      
      </tr>
    {/foreach}
    </tbody>
  </table>
{/if}  

{include file="cfields/{$tplBN}Controls.inc.tpl" suffix="Bottom"}

</div>
{include file="supportJS.inc.tpl"}
</body>
</html>