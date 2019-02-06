{*
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource pluginView.tpl
Purpose: smarty template - Manage plugins

@internal revisions
@since 1.9.15

*}
{lang_get var="labels"
          s="btn_create,title_plugin_mgmt,th_plugin,th_plugin_description,th_plugin_version,
             installed_plugins,available_plugins,actions"}

{include file="inc_head.tpl" openHead="yes" jsValidate="yes" enableTableSorting="yes"}
{include file="bootstrap.inc.tpl"}
{include file="inc_ext_js.tpl"}

{lang_get s='confirm_install_header' var="install_header"}
{lang_get s='confirm_install_text' var="install_text"}
{lang_get s='confirm_uninstall_header' var="uninstall_header"}
{lang_get s='confirm_uninstall_text' var="uninstall_text"}

</head>

<body {$body_onload}>

{include file="inc_update.tpl"}

{* Form to submit the values back to the caller *}
<form name="pluginForm" action="lib/plugins/pluginView.php" method="POST">
  <input type="hidden" name="pluginId" />
  <input type="hidden" name="pluginName" />
  <input type="hidden" name="operation" />
</form>

{if $gui->installed_plugins|@count ne 0}
  <h1 class="title">{$labels.installed_plugins}</h1>
  <div class="workBack">
    <table class="common sortable" width="100%">
      <tr>
        <th width="30%">{$tlImages.sort_hint}{$labels.th_plugin}</th>
        <th width="40%" class="{$noSortableColumnClass}">{$labels.th_plugin_description}</th>
        <th width="20%" class="icon_cell">{$labels.th_plugin_version}</th>
        <th class="icon_cell">{$labels.actions}</th>
      </tr>
      {foreach from=$gui->installed_plugins item=plugin}
      <tr>
        <td>{$plugin.name}</td>
        <td>{$plugin.description}</td>
        <td>{$plugin.version}</td>
        <td align="center">[<a href="#" onClick="return uninstallPlugin({$plugin.id})">Uninstall</a>]</td>
      </tr>
    {/foreach}
  </table>
</div>
{/if}

{if $gui->available_plugins|@count ne 0}
  <br>
  <h1 class="title">{$labels.available_plugins}</h1>
  <div class="workBack">
    <table class="common sortable" width="100%">
      <tr>
        <th width="30%">{$tlImages.sort_hint}{$labels.th_plugin}</th>
        <th width="40%" class="{$noSortableColumnClass}">{$labels.th_plugin_description}</th>
        <th width="20%" class="icon_cell">{$labels.th_plugin_version}</th>
        <th class="icon_cell">{$labels.actions}</th>
      </tr>
      {foreach from=$gui->available_plugins item=plugin}
      <tr>
        <td>{$plugin.name}</td>
        <td>{$plugin.description}</td>
        <td>{$plugin.version}</td>
        <td align="center">[<a href="#" onClick="return installPlugin('{$plugin.name}')">Install</a>]</td>
      </tr>
      {/foreach}
    </table>
  </div>
{/if}

<script type="text/javascript">
  function uninstallPlugin(id)
  {
    Ext.Msg.confirm("{$uninstall_header}", "{$uninstall_text}",
            function(btn, text)
            {
              if( btn == 'yes' )
              {
                document.forms['pluginForm'].elements['pluginId'].value = id;
                document.forms['pluginForm'].elements['operation'].value = 'uninstall';
                document.forms['pluginForm'].submit();
              }
            }
    );
    return false;
  }
  function installPlugin(name)
  {
    Ext.Msg.confirm("{$install_header}", "{$install_text}",
            function(btn, text)
            {
              if (btn == 'yes')
              {
                document.forms['pluginForm'].elements['pluginName'].value = name;
                document.forms['pluginForm'].elements['operation'].value = 'install';
                document.forms['pluginForm'].submit();
              }
            }
    );
    return false;
  }
</script>
</body>